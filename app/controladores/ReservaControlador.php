<?php
/**
 * WashFlow - Controlador de Reservas y Asistente de Turnos (Diagramas 02, 05, 10, 12)
 */

declare(strict_types=1);

namespace WashFlow\Controladores;

use Throwable;
use WashFlow\Core\Controlador;
use WashFlow\Core\Sesion;
use WashFlow\Core\Validador;
use WashFlow\Modelos\ModeloVehiculo;
use WashFlow\Modelos\PrecioServicio;
use WashFlow\Modelos\Reserva;
use WashFlow\Modelos\Servicio;
use WashFlow\Modelos\TipoVehiculo;
use WashFlow\Modelos\Usuario;
use WashFlow\Modelos\Vehiculo;
use WashFlow\Servicios\DisponibilidadServicio;

class ReservaControlador extends Controlador {
    /**
     * Asistente de Reserva en 4 pasos (Diagrama 10 de Actividades)
     */
    public function mostrarAsistente(): void {
        $this->requerirRol('cliente');
        $clienteId = (int)$this->getUsuarioId();

        $vehiculos = Vehiculo::listarPorCliente($clienteId, true);
        $servicios = Servicio::listarActivos();

        $this->render('cliente/reservas/asistente', [
            'titulo' => 'Reservar Turno de Lavado - WashFlow',
            'vehiculos' => $vehiculos,
            'servicios' => $servicios
        ]);
    }

    public function guardarReserva(): void {
        $this->requerirRol('cliente');
        $this->validarCsrf();
        $clienteId = (int)$this->getUsuarioId();

        $validador = new Validador($_POST);
        $validador->validar([
            'vehiculo_id' => 'requerido|entero',
            'servicio_id' => 'requerido|entero',
            'fecha' => 'requerido|fecha',
            'hora_inicio' => 'requerido|hora'
        ]);

        if ($validador->tieneErrores()) {
            Sesion::setFlash('error', 'Por favor complete todos los datos requeridos para reservar.');
            $this->redirect('/cliente/reservar');
            return;
        }

        $vehiculoId = (int)$validador->getDato('vehiculo_id');
        $servicioId = (int)$validador->getDato('servicio_id');
        $fecha = (string)$validador->getDato('fecha');
        $horaInicio = (string)$validador->getDato('hora_inicio');

        // Validar que el vehiculo pertenezca al cliente
        $vehiculo = Vehiculo::buscarPorId($vehiculoId);
        if (!$vehiculo || (int)$vehiculo['cliente_id'] !== $clienteId) {
            $this->mostrarError403('El vehiculo seleccionado no pertenece a su cuenta.');
        }

        try {
            $reservaId = Reserva::crear($vehiculoId, $servicioId, $fecha, $horaInicio, $clienteId);
            Sesion::setFlash('exito', "Su reserva #{$reservaId} ha sido confirmada exitosamente.");
            $this->redirect('/cliente/reservas');
        } catch (Throwable $e) {
            Sesion::setFlash('error', 'No se pudo completar la reserva: ' . $e->getMessage());
            $this->redirect('/cliente/reservar');
        }
    }

    public function misReservas(): void {
        $this->requerirRol('cliente');
        $clienteId = (int)$this->getUsuarioId();
        $reservas = Reserva::listarPorCliente($clienteId);

        $this->render('cliente/reservas/index', [
            'titulo' => 'Mis Reservas - WashFlow',
            'reservas' => $reservas
        ]);
    }

    public function mostrarModificar(string $id): void {
        $this->requerirRol('cliente');
        $clienteId = (int)$this->getUsuarioId();
        $reserva = Reserva::buscarPorId((int)$id);

        if (!$reserva || (int)$reserva['cliente_id'] !== $clienteId) {
            $this->mostrarError404('Reserva no encontrada.');
        }

        if ($reserva['estado'] !== 'reservada') {
            Sesion::setFlash('error', 'Solo se pueden modificar reservas que se encuentren activas.');
            $this->redirect('/cliente/reservas');
        }

        // Regla de 2 horas (Diagrama 02 y 09)
        if (!Reserva::calcularPermiteCambioDirecto($reserva['fecha'], $reserva['hora_inicio'])) {
            Sesion::setFlash('aviso', 'Faltan menos de 2 horas para su turno. Debe enviar una solicitud de cambio para ser evaluada.');
            $this->redirect("/cliente/reservas/{$id}/solicitar");
        }

        $this->render('cliente/reservas/modificar', [
            'titulo' => 'Modificar Fecha y Horario - WashFlow',
            'reserva' => $reserva
        ]);
    }

    public function reprogramarDirecta(string $id): void {
        $this->requerirRol('cliente');
        $this->validarCsrf();
        $clienteId = (int)$this->getUsuarioId();
        $reserva = Reserva::buscarPorId((int)$id);

        if (!$reserva || (int)$reserva['cliente_id'] !== $clienteId) {
            $this->mostrarError404('Reserva no encontrada.');
        }

        if (!Reserva::calcularPermiteCambioDirecto($reserva['fecha'], $reserva['hora_inicio'])) {
            $this->redirect("/cliente/reservas/{$id}/solicitar");
        }

        $validador = new Validador($_POST);
        $validador->validar([
            'fecha' => 'requerido|fecha',
            'hora_inicio' => 'requerido|hora'
        ]);

        if ($validador->tieneErrores()) {
            Sesion::setFlash('error', 'Debe seleccionar una nueva fecha y horario validos.');
            $this->redirect("/cliente/reservas/{$id}/modificar");
            return;
        }

        $nuevaFecha = (string)$validador->getDato('fecha');
        $nuevaHora = (string)$validador->getDato('hora_inicio');

        try {
            Reserva::reprogramar((int)$id, $nuevaFecha, $nuevaHora, $clienteId);
            Sesion::setFlash('exito', 'Su turno ha sido reprogramado exitosamente.');
            $this->redirect('/cliente/reservas');
        } catch (Throwable $e) {
            Sesion::setFlash('error', 'No se pudo reprogramar el turno: ' . $e->getMessage());
            $this->redirect("/cliente/reservas/{$id}/modificar");
        }
    }

    public function cancelarDirecta(string $id): void {
        $this->requerirRol('cliente');
        $this->validarCsrf();
        $clienteId = (int)$this->getUsuarioId();
        $reserva = Reserva::buscarPorId((int)$id);

        if (!$reserva || (int)$reserva['cliente_id'] !== $clienteId) {
            $this->mostrarError404('Reserva no encontrada.');
        }

        if ($reserva['estado'] !== 'reservada') {
            Sesion::setFlash('error', 'La reserva no se encuentra activa.');
            $this->redirect('/cliente/reservas');
        }

        // Regla de 2 horas
        if (!Reserva::calcularPermiteCambioDirecto($reserva['fecha'], $reserva['hora_inicio'])) {
            Sesion::setFlash('aviso', 'Faltan menos de 2 horas para su turno. Debe enviar una solicitud de cancelacion para ser aprobada por el personal.');
            $this->redirect("/cliente/reservas/{$id}/solicitar");
            return;
        }

        Reserva::cancelar((int)$id, $clienteId, 'Cancelada directamente por el cliente con anticipacion debida.');
        Sesion::setFlash('exito', 'La reserva ha sido cancelada exitosamente.');
        $this->redirect('/cliente/reservas');
    }

    /**
     * API JSON: Consulta de disponibilidad en tiempo real para franjas horarias
     */
    public function apiDisponibilidad(): void {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $duracion = (int)($_GET['duracion'] ?? 60);
        $excluirId = isset($_GET['excluir_id']) ? (int)$_GET['excluir_id'] : null;

        $mapa = DisponibilidadServicio::obtenerMapaDisponibilidad($fecha, $duracion, $excluirId);
        $this->json($mapa);
    }

    /**
     * API JSON: Consulta de precio congelado para un vehiculo y servicio
     */
    public function apiPrecio(): void {
        $servicioId = (int)($_GET['servicio_id'] ?? 0);
        $vehiculoId = (int)($_GET['vehiculo_id'] ?? 0);

        $vehiculo = Vehiculo::buscarPorId($vehiculoId);
        if (!$vehiculo) {
            $this->json(['error' => 'Vehiculo no encontrado'], 404);
        }

        $precio = PrecioServicio::obtenerPrecioVigente($servicioId, (int)$vehiculo['tipo_vehiculo_id']);
        $this->json([
            'servicio_id' => $servicioId,
            'vehiculo_id' => $vehiculoId,
            'precio' => $precio,
            'duracion' => (int)$vehiculo['duracion_minutos']
        ]);
    }

    /**
     * API JSON: Busqueda inteligente de modelos de autos en Argentina
     */
    public function apiModelos(): void {
        $q = trim((string)($_GET['q'] ?? ''));
        $modelos = ModeloVehiculo::buscar($q, 15);
        $this->json(['modelos' => $modelos]);
    }

    /**
     * Asistente de Reserva Publica en 5 pasos sin requerir registro (Requisito 3.1)
     */
    public function mostrarReservarPublica(): void {
        $tiposVehiculo = TipoVehiculo::listarTodos();
        $servicios = Servicio::listarActivos();
        $tarifas = PrecioServicio::listarPreciosVigentes();

        $precios = [];
        foreach ($tarifas as $t) {
            $precios[$t['servicio_id']][$t['tipo_vehiculo_id']] = [
                'precio' => (float)$t['precio_vigente'],
                'duracion' => (int)$t['duracion_minutos']
            ];
        }

        $this->render('reservas/publica', [
            'titulo' => 'Reservar Turno Online — WashFlow',
            'tiposVehiculo' => $tiposVehiculo,
            'servicios' => $servicios,
            'tarifas' => $tarifas,
            'precios' => $precios
        ], null);
    }

    public function guardarReservaPublica(): void {
        $this->validarCsrf();

        try {
            $resultado = Reserva::crearPublica($_POST);

            if ($resultado['codigo'] === 'WF-00000') {
                // Honeypot disparado
                $this->redirect('/');
                return;
            }

            Sesion::setFlash('exito', 'Su reserva ha sido confirmada con exito.');
            $this->redirect("/reserva/{$resultado['codigo']}/{$resultado['token']}");
        } catch (Throwable $e) {
            Sesion::setFlash('error', $e->getMessage());
            Sesion::setFlash('form_data', $_POST);
            $this->redirect('/reservar');
        }
    }

    public function mostrarDetallePublico(string $codigo, string $token): void {
        $reserva = Reserva::buscarPorCodigoYToken($codigo, $token);
        if (!$reserva) {
            $this->mostrarError404('La reserva solicitada no existe o el enlace ha caducado.');
            return;
        }

        $db = \WashFlow\Core\BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('SELECT * FROM solicitudes_reserva WHERE reserva_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$reserva['id']]);
        $solicitudPendiente = $stmt->fetch();

        $this->render('reservas/detalle_publico', [
            'titulo' => "Turno {$reserva['codigo']} — WashFlow",
            'reserva' => $reserva,
            'solicitudPendiente' => $solicitudPendiente
        ], null);
    }

    public function reprogramarPublica(string $codigo, string $token): void {
        $this->validarCsrf();
        $nuevaFecha = trim((string)($_POST['nueva_fecha'] ?? ''));
        $nuevaHora = trim((string)($_POST['nueva_hora'] ?? ''));
        $motivo = trim((string)($_POST['motivo'] ?? ''));

        if (empty($nuevaFecha) || empty($nuevaHora)) {
            Sesion::setFlash('error', 'Debe seleccionar una nueva fecha y horario.');
            $this->redirect("/reserva/{$codigo}/{$token}");
            return;
        }

        try {
            $res = Reserva::reprogramarPublica($codigo, $token, $nuevaFecha, $nuevaHora, $motivo);
            if ($res['tipo'] === 'directo') {
                Sesion::setFlash('exito', $res['mensaje']);
            } else {
                Sesion::setFlash('aviso', $res['mensaje']);
            }
        } catch (Throwable $e) {
            Sesion::setFlash('error', 'No se pudo reprogramar: ' . $e->getMessage());
        }

        $this->redirect("/reserva/{$codigo}/{$token}");
    }

    public function cancelarPublica(string $codigo, string $token): void {
        $this->validarCsrf();
        $motivo = trim((string)($_POST['motivo'] ?? ''));

        try {
            $res = Reserva::cancelarPublica($codigo, $token, $motivo);
            if ($res['tipo'] === 'directo') {
                Sesion::setFlash('exito', $res['mensaje']);
            } else {
                Sesion::setFlash('aviso', $res['mensaje']);
            }
        } catch (Throwable $e) {
            Sesion::setFlash('error', 'No se pudo cancelar: ' . $e->getMessage());
        }

        $this->redirect("/reserva/{$codigo}/{$token}");
    }

    public function mostrarBuscarPublica(): void {
        $this->render('reservas/buscar_publico', [
            'titulo' => 'Consultar Mi Reserva — WashFlow'
        ], null);
    }

    public function buscarPublica(): void {
        $codigo = trim((string)($_POST['codigo'] ?? ''));
        $telefono = trim((string)($_POST['telefono'] ?? ''));

        if (empty($codigo) || empty($telefono)) {
            Sesion::setFlash('error', 'Por favor ingrese el codigo de reserva y el telefono de contacto.');
            $this->redirect('/mi-reserva');
            return;
        }

        $reserva = Reserva::buscarPorCodigoYTelefono($codigo, $telefono);
        if (!$reserva || empty($reserva['token_publico'])) {
            Sesion::setFlash('error', 'No encontramos ninguna reserva activa con ese codigo y telefono. Verifique los datos ingresados.');
            $this->redirect('/mi-reserva');
            return;
        }

        $this->redirect("/reserva/{$reserva['codigo']}/{$reserva['token_publico']}");
    }

    public function crearCuentaDesdePublica(string $codigo, string $token): void {
        $this->validarCsrf();
        $reserva = Reserva::buscarPorCodigoYToken($codigo, $token);
        if (!$reserva) {
            $this->mostrarError404('Reserva no encontrada.');
            return;
        }

        $correo = trim((string)($_POST['correo'] ?? ''));
        $contrasena = trim((string)($_POST['contrasena'] ?? ''));

        if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            Sesion::setFlash('error', 'Ingrese un correo electronico valido.');
            $this->redirect("/reserva/{$codigo}/{$token}");
            return;
        }

        if (strlen($contrasena) < 8) {
            Sesion::setFlash('error', 'La contrasena debe tener al menos 8 caracteres.');
            $this->redirect("/reserva/{$codigo}/{$token}");
            return;
        }

        // Verificar si ya existe el correo
        $existente = Usuario::buscarPorCorreo($correo);
        if ($existente && (int)$existente['id'] !== (int)$reserva['cliente_id']) {
            Sesion::setFlash('error', 'Ya existe una cuenta con este correo electronico. Inicie sesion con su cuenta existente.');
            $this->redirect("/reserva/{$codigo}/{$token}");
            return;
        }

        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        Usuario::actualizar((int)$reserva['cliente_id'], [
            'correo' => $correo,
            'contrasena_hash' => $hash,
            'es_ocasional' => 0,
            'identidad_verificada' => 1
        ]);

        Sesion::setFlash('exito', 'Su cuenta ha sido creada exitosamente. Ahora puede iniciar sesion para ver todo su historial de lavados.');
        $this->redirect('/login');
    }
}
