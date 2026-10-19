<?php
/**
 * WashFlow - Controlador del Modulo Empleado y Operaciones (Diagramas 01, 02, 03, 06, 07, 08, 12)
 * El Administrador hereda automaticamente todas las capacidades operativas.
 */

declare(strict_types=1);

namespace WashFlow\Controladores;

use Throwable;
use WashFlow\Core\Controlador;
use WashFlow\Core\Sesion;
use WashFlow\Core\Validador;
use WashFlow\Modelos\Lavado;
use WashFlow\Modelos\PrecioServicio;
use WashFlow\Modelos\Reserva;
use WashFlow\Modelos\Servicio;
use WashFlow\Modelos\SolicitudReserva;
use WashFlow\Modelos\TipoVehiculo;
use WashFlow\Modelos\Usuario;
use WashFlow\Modelos\Vehiculo;
use WashFlow\Servicios\DisponibilidadServicio;

class EmpleadoControlador extends Controlador {
    public function dashboard(): void {
        $this->requerirRol(['empleado', 'administrador']);
        $hoy = date('Y-m-d');

        $reservasHoy = Reserva::listarPorFecha($hoy);
        $lavadosHoy = Lavado::listarPorFecha($hoy);
        $solicitudes = SolicitudReserva::listarPendientes();

        $pendientesLavado = array_filter($lavadosHoy, fn($l) => $l['estado'] === 'pendiente');
        $terminadosLavado = array_filter($lavadosHoy, fn($l) => $l['estado'] === 'terminado');
        $lavadosSinReserva = array_filter($lavadosHoy, fn($l) => empty($l['reserva_id']));

        $this->render('empleado/dashboard', [
            'titulo' => 'Panel Operativo - WashFlow',
            'reservasHoy' => $reservasHoy,
            'lavadosHoy' => $lavadosHoy,
            'pendientesLavado' => $pendientesLavado,
            'terminadosLavado' => $terminadosLavado,
            'lavadosSinReserva' => $lavadosSinReserva,
            'solicitudes' => $solicitudes
        ]);
    }

    public function agenda(): void {
        $this->requerirRol(['empleado', 'administrador']);
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        $reservas = Reserva::listarPorFecha($fecha);
        $lavados = Lavado::listarPorFecha($fecha);
        $mapa = DisponibilidadServicio::obtenerMapaDisponibilidad($fecha, 30);
        $tipos = TipoVehiculo::listarTodos();

        $this->render('empleado/agenda', [
            'titulo' => 'Agenda y Calendario de Turnos - WashFlow',
            'fecha' => $fecha,
            'reservas' => $reservas,
            'lavados' => $lavados,
            'mapa' => $mapa,
            'tipos' => $tipos
        ]);
    }

    public function buscar(): void {
        $this->requerirRol(['empleado', 'administrador']);
        $criterio = trim((string)($_GET['q'] ?? ''));
        $resultados = [];

        if ($criterio !== '') {
            $resultados = Usuario::buscarGeneral($criterio);
        }

        $this->render('empleado/buscar', [
            'titulo' => 'Buscar Cliente o Patente - WashFlow',
            'criterio' => $criterio,
            'resultados' => $resultados
        ]);
    }

    public function registrarLlegada(): void {
        $this->requerirRol(['empleado', 'administrador']);
        $this->validarCsrf();
        $empleadoId = (int)$this->getUsuarioId();
        $reservaId = (int)($_POST['reserva_id'] ?? 0);

        try {
            $lavadoId = Lavado::registrarLlegada($reservaId, $empleadoId);
            Sesion::setFlash('exito', "Llegada registrada. Se inicio el Lavado #{$lavadoId} (estado: Pendiente).");
        } catch (Throwable $e) {
            Sesion::setFlash('error', 'Error al registrar llegada: ' . $e->getMessage());
        }

        $this->redirect('/empleado/agenda');
    }

    public function marcarLavadoTerminado(): void {
        $this->requerirRol(['empleado', 'administrador']);
        $this->validarCsrf();
        $empleadoId = (int)$this->getUsuarioId();
        $lavadoId = (int)($_POST['lavado_id'] ?? 0);

        try {
            Lavado::marcarTerminado($lavadoId, $empleadoId);
            Sesion::setFlash('exito', "Lavado #{$lavadoId} marcado como Terminado. Se notifico al cliente.");
        } catch (Throwable $e) {
            Sesion::setFlash('error', 'Error al actualizar lavado: ' . $e->getMessage());
        }

        $this->redirect('/empleado/agenda');
    }

    public function registrarAusencia(): void {
        $this->requerirRol(['empleado', 'administrador']);
        $this->validarCsrf();
        $empleadoId = (int)$this->getUsuarioId();
        $reservaId = (int)($_POST['reserva_id'] ?? 0);

        try {
            Reserva::marcarAusente($reservaId, $empleadoId);
            Sesion::setFlash('aviso', "Se registro la ausencia para el turno #{$reservaId}.");
        } catch (Throwable $e) {
            Sesion::setFlash('error', 'Error al registrar ausencia: ' . $e->getMessage());
        }

        $this->redirect('/empleado/agenda');
    }

    /**
     * Confirma o corrige la categoria de un vehiculo (Secciones 13.2 y 14)
     */
    public function corregirCategoriaVehiculo(string $id): void {
        $this->requerirRol(['empleado', 'administrador']);
        $this->validarCsrf();
        $usuarioId = (int)$this->getUsuarioId();
        $reservaId = (int)$id;

        $nuevoTipoId = (int)($_POST['tipo_vehiculo_id'] ?? 0);
        $patente = !empty($_POST['patente']) ? trim((string)$_POST['patente']) : null;
        $avisoClienteConfirmado = !empty($_POST['aviso_cliente_confirmado']);
        $motivo = trim((string)($_POST['motivo'] ?? ''));

        try {
            $resultado = Reserva::corregirCategoriaVehiculo(
                $reservaId,
                $nuevoTipoId,
                $patente,
                $avisoClienteConfirmado,
                $motivo,
                $usuarioId
            );

            if ($resultado['cambio_precio']) {
                Sesion::setFlash('exito', "Categoria actualizada a nueva tarifa de \${$resultado['nuevo_precio']}. Notificacion interna generada para administracion.");
            } else {
                Sesion::setFlash('exito', "Categoria confirmada exitosamente.");
            }
        } catch (Throwable $e) {
            Sesion::setFlash('error', 'Error al corregir categoria: ' . $e->getMessage());
        }

        $this->redirect('/empleado/agenda');
    }

    public function mostrarLavadoDirecto(): void {
        $this->requerirRol(['empleado', 'administrador']);
        $tipos = TipoVehiculo::listarTodos();
        $servicios = Servicio::listarActivos();

        $valores = [
            'nombre_cliente' => $_GET['nombre'] ?? '',
            'telefono' => $_GET['telefono'] ?? '',
            'patente' => $_GET['patente'] ?? '',
            'marca' => $_GET['marca'] ?? '',
            'modelo' => $_GET['modelo'] ?? '',
            'tipo_vehiculo_id' => $_GET['tipo_id'] ?? ''
        ];

        $this->render('empleado/lavado_directo', [
            'titulo' => 'Registrar Lavado Sin Reserva - WashFlow',
            'tipos' => $tipos,
            'servicios' => $servicios,
            'valores' => $valores
        ]);
    }

    public function guardarLavadoDirecto(): void {
        $this->requerirRol(['empleado', 'administrador']);
        $this->validarCsrf();
        $empleadoId = (int)$this->getUsuarioId();

        $validador = new Validador($_POST);
        $validador->validar([
            'nombre_cliente' => 'requerido|min:2|max:100',
            'patente' => 'requerido|patente|min:6|max:20',
            'tipo_vehiculo_id' => 'requerido|entero',
            'marca' => 'requerido|min:2|max:80',
            'modelo' => 'requerido|min:1|max:80',
            'servicio_id' => 'requerido|entero'
        ]);

        if ($validador->tieneErrores()) {
            $tipos = TipoVehiculo::listarTodos();
            $servicios = Servicio::listarActivos();
            $this->render('empleado/lavado_directo', [
                'titulo' => 'Registrar Lavado Sin Reserva - WashFlow',
                'tipos' => $tipos,
                'servicios' => $servicios,
                'errores' => $validador->getErrores(),
                'valores' => $validador->getDatos()
            ]);
            return;
        }

        $patente = strtoupper(trim((string)$validador->getDato('patente')));
        $nombreCliente = (string)$validador->getDato('nombre_cliente');
        $telefono = (string)$validador->getDato('telefono');
        $tipoId = (int)$validador->getDato('tipo_vehiculo_id');
        $marca = (string)$validador->getDato('marca');
        $modelo = (string)$validador->getDato('modelo');
        $servicioId = (int)$validador->getDato('servicio_id');

        try {
            // 1. Buscar vehiculo existente o asociar a cliente
            $vehiculo = Vehiculo::buscarPorPatente($patente);
            if (!$vehiculo) {
                // Buscar cliente existente por telefono o crear ocasional
                $cliente = $telefono ? Usuario::buscarPorTelefono($telefono) : null;
                $clienteId = $cliente ? (int)$cliente['id'] : Usuario::crearClienteOcasional($nombreCliente, $telefono);

                $vehiculoId = Vehiculo::crear([
                    'cliente_id' => $clienteId,
                    'tipo_vehiculo_id' => $tipoId,
                    'patente' => $patente,
                    'marca' => $marca,
                    'modelo' => $modelo
                ]);
            } else {
                $vehiculoId = (int)$vehiculo['id'];
            }

            // 2. Registrar lavado sin reserva con validacion de capacidad (max 3 simultaneos)
            $lavadoId = Lavado::registrarSinReserva($vehiculoId, $servicioId, $empleadoId);

            Sesion::setFlash('exito', "Lavado sin reserva #{$lavadoId} registrado exitosamente.");
            $this->redirect('/empleado/agenda');
        } catch (Throwable $e) {
            $tipos = TipoVehiculo::listarTodos();
            $servicios = Servicio::listarActivos();
            $this->render('empleado/lavado_directo', [
                'titulo' => 'Registrar Lavado Sin Reserva - WashFlow',
                'tipos' => $tipos,
                'servicios' => $servicios,
                'errorGeneral' => $e->getMessage(),
                'valores' => $validador->getDatos()
            ]);
        }
    }

    public function solicitudes(): void {
        $this->requerirRol(['empleado', 'administrador']);
        $pendientes = SolicitudReserva::listarPendientes();

        $this->render('empleado/solicitudes', [
            'titulo' => 'Solicitudes Fuera de Plazo - WashFlow',
            'solicitudes' => $pendientes
        ]);
    }
}
