<?php
/**
 * WashFlow - Controlador del Modulo Administrador (Diagramas 01, 03, 04a, 04b, 12)
 * Autorizacion estricta exclusiva para el rol 'administrador'.
 */

declare(strict_types=1);

namespace WashFlow\Controladores;

use Throwable;
use WashFlow\Core\Controlador;
use WashFlow\Core\Sesion;
use WashFlow\Core\Validador;
use WashFlow\Modelos\BloqueoHorario;
use WashFlow\Modelos\Galeria;
use WashFlow\Modelos\ModeloVehiculo;
use WashFlow\Modelos\PrecioServicio;
use WashFlow\Modelos\PreguntaFrecuente;
use WashFlow\Modelos\RegistroOperacion;
use WashFlow\Modelos\Reserva;
use WashFlow\Modelos\Lavado;
use WashFlow\Modelos\Servicio;
use WashFlow\Modelos\TipoVehiculo;
use WashFlow\Modelos\Usuario;
use WashFlow\Servicios\ReporteServicio;

class AdminControlador extends Controlador {
    public function dashboard(): void {
        $this->requerirRol('administrador');

        $mesInicio = date('Y-m-01');
        $hoy = date('Y-m-d');

        $totalClientes = Usuario::contar('cliente');
        $totalEmpleados = Usuario::contar('empleado');
        $totalReservasMes = Reserva::contarTodas(['fecha_desde' => $mesInicio, 'fecha_hasta' => $hoy]);
        $lavadosHoy = Lavado::listarPorFecha($hoy);
        $lavadosEnCurso = count(array_filter($lavadosHoy, fn($l) => $l['estado'] === 'pendiente'));

        $this->render('admin/dashboard', [
            'titulo' => 'Panel de Administracion - WashFlow',
            'totalClientes' => $totalClientes,
            'totalEmpleados' => $totalEmpleados,
            'totalReservasMes' => $totalReservasMes,
            'lavadosEnCurso' => $lavadosEnCurso
        ]);
    }

    public function usuarios(): void {
        $this->requerirRol('administrador');
        $rol = trim((string)($_GET['rol'] ?? ''));
        $busqueda = trim((string)($_GET['q'] ?? ''));

        $usuarios = Usuario::listar($rol, $busqueda, 100);
        $total = Usuario::contar($rol, $busqueda);

        $this->render('admin/usuarios/index', [
            'titulo' => 'Gestion de Usuarios - WashFlow',
            'usuarios' => $usuarios,
            'rolFiltro' => $rol,
            'busqueda' => $busqueda,
            'total' => $total
        ]);
    }

    public function mostrarCrearUsuario(): void {
        $this->requerirRol('administrador');
        $this->render('admin/usuarios/crear', [
            'titulo' => 'Alta de Usuario - WashFlow'
        ]);
    }

    public function guardarUsuario(): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();
        $adminId = (int)$this->getUsuarioId();

        $validador = new Validador($_POST);
        $validador->validar([
            'rol' => 'requerido|in:cliente,empleado',
            'nombre' => 'requerido|min:2|max:100',
            'apellido' => 'requerido|min:2|max:100',
            'telefono' => 'max:30',
            'correo' => 'requerido|correo|max:150',
            'contrasena' => 'requerido|min:8'
        ]);

        $correo = (string)$validador->getDato('correo');
        if (Usuario::buscarPorCorreo($correo)) {
            $this->render('admin/usuarios/crear', [
                'titulo' => 'Alta de Usuario - WashFlow',
                'errorGeneral' => 'Ya existe un usuario registrado con este correo electronico.',
                'valores' => $validador->getDatos()
            ]);
            return;
        }

        if ($validador->tieneErrores()) {
            $this->render('admin/usuarios/crear', [
                'titulo' => 'Alta de Usuario - WashFlow',
                'errores' => $validador->getErrores(),
                'valores' => $validador->getDatos()
            ]);
            return;
        }

        $hash = password_hash((string)$validador->getDato('contrasena'), PASSWORD_BCRYPT);
        $rol = (string)$validador->getDato('rol');

        $nuevoId = Usuario::crear([
            'rol' => $rol,
            'nombre' => (string)$validador->getDato('nombre'),
            'apellido' => (string)$validador->getDato('apellido'),
            'telefono' => (string)$validador->getDato('telefono'),
            'correo' => $correo,
            'contrasena_hash' => $hash,
            'activo' => 1,
            'es_ocasional' => 0,
            'identidad_verificada' => 1
        ]);

        RegistroOperacion::registrar(
            $adminId,
            'ALTA_USUARIO',
            'Usuario',
            $nuevoId,
            "Administrador dio de alta al usuario {$rol}: {$correo} (ID #{$nuevoId})."
        );

        Sesion::setFlash('exito', "Usuario creado exitosamente con rol {$rol}.");
        $this->redirect('/admin/usuarios');
    }

    public function mostrarEditarUsuario(string $id): void {
        $this->requerirRol('administrador');
        $usuario = Usuario::buscarPorId((int)$id);

        if (!$usuario) {
            $this->mostrarError404('Usuario no encontrado.');
        }

        $this->render('admin/usuarios/editar', [
            'titulo' => 'Editar Usuario - WashFlow',
            'usuario' => $usuario
        ]);
    }

    public function actualizarUsuario(string $id): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();
        $adminId = (int)$this->getUsuarioId();
        $usuario = Usuario::buscarPorId((int)$id);

        if (!$usuario) {
            $this->mostrarError404('Usuario no encontrado.');
        }

        $validador = new Validador($_POST);
        $validador->validar([
            'nombre' => 'requerido|min:2|max:100',
            'apellido' => 'requerido|min:2|max:100',
            'telefono' => 'max:30',
            'correo' => 'requerido|correo|max:150'
        ]);

        $correo = (string)$validador->getDato('correo');
        $existente = Usuario::buscarPorCorreo($correo);
        if ($existente && (int)$existente['id'] !== (int)$id) {
            $this->render('admin/usuarios/editar', [
                'titulo' => 'Editar Usuario - WashFlow',
                'usuario' => $usuario,
                'errorGeneral' => 'El correo electronico ya se encuentra en uso por otra cuenta.'
            ]);
            return;
        }

        $datosActualizar = [
            'nombre' => (string)$validador->getDato('nombre'),
            'apellido' => (string)$validador->getDato('apellido'),
            'telefono' => (string)$validador->getDato('telefono'),
            'correo' => $correo
        ];

        // Cambio opcional de clave
        $nuevaClave = trim((string)($_POST['nueva_contrasena'] ?? ''));
        if ($nuevaClave !== '') {
            if (strlen($nuevaClave) < 8) {
                $this->render('admin/usuarios/editar', [
                    'titulo' => 'Editar Usuario - WashFlow',
                    'usuario' => $usuario,
                    'errorGeneral' => 'La nueva contrasena debe tener al menos 8 caracteres.'
                ]);
                return;
            }
            $datosActualizar['contrasena_hash'] = password_hash($nuevaClave, PASSWORD_BCRYPT);
        }

        Usuario::actualizar((int)$id, $datosActualizar);

        RegistroOperacion::registrar(
            $adminId,
            'MODIFICAR_USUARIO',
            'Usuario',
            (int)$id,
            "Datos del usuario #{$id} actualizados por administrador."
        );

        Sesion::setFlash('exito', 'Usuario actualizado correctamente.');
        $this->redirect('/admin/usuarios');
    }

    public function desactivarUsuario(string $id): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();
        $adminId = (int)$this->getUsuarioId();

        // Evitar que el administrador se auto-desactive
        if ((int)$id === $adminId) {
            Sesion::setFlash('error', 'No puede desactivar su propia cuenta de administrador.');
            $this->redirect('/admin/usuarios');
            return;
        }

        Usuario::desactivar((int)$id);

        RegistroOperacion::registrar(
            $adminId,
            'DESACTIVAR_USUARIO',
            'Usuario',
            (int)$id,
            "Usuario #{$id} desactivado (bloqueo logico)."
        );

        Sesion::setFlash('exito', 'Usuario desactivado exitosamente.');
        $this->redirect('/admin/usuarios');
    }

    public function activarUsuario(string $id): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();
        $adminId = (int)$this->getUsuarioId();

        Usuario::activar((int)$id);

        RegistroOperacion::registrar(
            $adminId,
            'ACTIVAR_USUARIO',
            'Usuario',
            (int)$id,
            "Usuario #{$id} reactivado."
        );

        Sesion::setFlash('exito', 'Usuario reactivado exitosamente.');
        $this->redirect('/admin/usuarios');
    }

    public function vinculaciones(): void {
        $this->requerirRol('administrador');
        $ocasionales = Usuario::listar('cliente', '', 100);
        $ocasionales = array_filter($ocasionales, fn($u) => !empty($u['es_ocasional']) && empty($u['usuario_vinculado_id']));

        $clientesRegistrados = Usuario::listar('cliente', '', 100);
        $clientesRegistrados = array_filter($clientesRegistrados, fn($u) => empty($u['es_ocasional']) && (int)$u['activo'] === 1);

        $this->render('admin/usuarios/vinculacion', [
            'titulo' => 'Vinculacion de Clientes Ocasionales - WashFlow',
            'ocasionales' => $ocasionales,
            'clientesRegistrados' => $clientesRegistrados
        ]);
    }

    public function ejecutarVinculacion(): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();
        $adminId = (int)$this->getUsuarioId();

        $ocasionalId = (int)($_POST['ocasional_id'] ?? 0);
        $destinoId = (int)($_POST['cuenta_destino_id'] ?? 0);

        if ($ocasionalId <= 0 || $destinoId <= 0 || $ocasionalId === $destinoId) {
            Sesion::setFlash('error', 'Seleccione tanto el cliente ocasional como la cuenta registrada destino.');
            $this->redirect('/admin/vinculaciones');
            return;
        }

        try {
            Usuario::vincularOcasionalACuenta($ocasionalId, $destinoId);

            RegistroOperacion::registrar(
                $adminId,
                'VINCULAR_CLIENTE_OCASIONAL',
                'Usuario',
                $ocasionalId,
                "Historial de cliente ocasional #{$ocasionalId} vinculado a la cuenta #{$destinoId} tras verificacion de identidad."
            );

            Sesion::setFlash('exito', 'Historial y vehiculos vinculados exitosamente a la cuenta del cliente verificado.');
        } catch (Throwable $e) {
            Sesion::setFlash('error', 'Error al realizar la vinculacion: ' . $e->getMessage());
        }

        $this->redirect('/admin/vinculaciones');
    }

    public function precios(): void {
        $this->requerirRol('administrador');

        $tarifas = PrecioServicio::listarPreciosVigentes();
        $tipos = TipoVehiculo::listarTodos();
        $servicios = Servicio::listarActivos();
        $historial = PrecioServicio::historialPrecios();

        $this->render('admin/precios/index', [
            'titulo' => 'Configuracion de Precios y Duraciones - WashFlow',
            'tarifas' => $tarifas,
            'tipos' => $tipos,
            'servicios' => $servicios,
            'historial' => array_slice($historial, 0, 20)
        ]);
    }

    public function modificarPrecio(): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();
        $adminId = (int)$this->getUsuarioId();

        $servicioId = (int)($_POST['servicio_id'] ?? 0);
        $tipoId = (int)($_POST['tipo_vehiculo_id'] ?? 0);
        $nuevoPrecio = (float)($_POST['nuevo_precio'] ?? 0);

        if ($servicioId <= 0 || $tipoId <= 0 || $nuevoPrecio <= 0) {
            Sesion::setFlash('error', 'Datos de precio invalidos. El monto debe ser mayor a cero.');
            $this->redirect('/admin/precios');
            return;
        }

        PrecioServicio::modificarPrecio($servicioId, $tipoId, $nuevoPrecio);

        RegistroOperacion::registrar(
            $adminId,
            'MODIFICAR_PRECIO',
            'PrecioServicio',
            $servicioId,
            "Nueva tarifa fijada: \${$nuevoPrecio} para servicio #{$servicioId} y tipo #{$tipoId}."
        );

        Sesion::setFlash('exito', "Nueva tarifa de \${$nuevoPrecio} registrada con vigencia a partir de hoy (se conserva el historial).");
        $this->redirect('/admin/precios');
    }

    public function modificarDuracion(): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();
        $adminId = (int)$this->getUsuarioId();

        $tipoId = (int)($_POST['tipo_vehiculo_id'] ?? 0);
        $duracion = (int)($_POST['duracion_minutos'] ?? 0);

        if ($tipoId <= 0 || $duracion <= 0) {
            Sesion::setFlash('error', 'Duracion invalida. Debe ser mayor a cero minutos.');
            $this->redirect('/admin/precios');
            return;
        }

        TipoVehiculo::cambiarDuracion($tipoId, $duracion);

        RegistroOperacion::registrar(
            $adminId,
            'CONFIGURAR_DURACION',
            'TipoVehiculo',
            $tipoId,
            "Duracion para tipo #{$tipoId} fijada en {$duracion} minutos."
        );

        Sesion::setFlash('exito', "Duracion actualizada a {$duracion} minutos.");
        $this->redirect('/admin/precios');
    }

    public function bloqueos(): void {
        $this->requerirRol('administrador');
        $bloqueos = BloqueoHorario::listarFuturos();

        $this->render('admin/bloqueos/index', [
            'titulo' => 'Bloqueos de Horario - WashFlow',
            'bloqueos' => $bloqueos
        ]);
    }

    public function guardarBloqueo(): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();
        $adminId = (int)$this->getUsuarioId();

        $validador = new Validador($_POST);
        $validador->validar([
            'fecha' => 'requerido|fecha',
            'hora_inicio' => 'requerido|hora',
            'hora_fin' => 'requerido|hora',
            'motivo' => 'requerido|min:3|max:255'
        ]);

        if ($validador->tieneErrores()) {
            Sesion::setFlash('error', 'Por favor complete todos los datos requeridos para el bloqueo de horario.');
            $this->redirect('/admin/bloqueos');
            return;
        }

        $horaInicio = (string)$validador->getDato('hora_inicio');
        $horaFin = (string)$validador->getDato('hora_fin');

        if ($horaInicio >= $horaFin) {
            Sesion::setFlash('error', 'La hora de inicio debe ser anterior a la hora de finalizacion.');
            $this->redirect('/admin/bloqueos');
            return;
        }

        $bloqueoId = BloqueoHorario::crear([
            'fecha' => (string)$validador->getDato('fecha'),
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'motivo' => (string)$validador->getDato('motivo'),
            'creado_por' => $adminId
        ]);

        RegistroOperacion::registrar(
            $adminId,
            'BLOQUEAR_HORARIO',
            'BloqueoHorario',
            $bloqueoId,
            "Bloqueo fijado para {$validador->getDato('fecha')} de {$horaInicio} a {$horaFin} ({$validador->getDato('motivo')})."
        );

        Sesion::setFlash('exito', 'Franja horaria bloqueada exitosamente.');
        $this->redirect('/admin/bloqueos');
    }

    public function eliminarBloqueo(string $id): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();
        $adminId = (int)$this->getUsuarioId();

        BloqueoHorario::eliminar((int)$id);

        RegistroOperacion::registrar(
            $adminId,
            'ELIMINAR_BLOQUEO',
            'BloqueoHorario',
            (int)$id,
            "Bloqueo de horario #{$id} eliminado."
        );

        Sesion::setFlash('exito', 'Bloqueo eliminado correctamente.');
        $this->redirect('/admin/bloqueos');
    }

    public function informes(): void {
        $this->requerirRol('administrador');

        $mesInicio = date('Y-m-01');
        $hoy = date('Y-m-d');
        $desde = $_GET['desde'] ?? $mesInicio;
        $hasta = $_GET['hasta'] ?? $hoy;

        $serviciosSolicitados = ReporteServicio::serviciosMasSolicitados($desde, $hasta);
        $ocupacionDias = ReporteServicio::ocupacionPorDia($desde, $hasta);

        $this->render('admin/informes/index', [
            'titulo' => 'Informes y Estadisticas - WashFlow',
            'desde' => $desde,
            'hasta' => $hasta,
            'serviciosSolicitados' => $serviciosSolicitados,
            'ocupacionDias' => $ocupacionDias
        ]);
    }

    public function exportarCsv(): void {
        $this->requerirRol('administrador');

        $tipo = $_GET['tipo'] ?? 'reservas';
        $desde = $_GET['desde'] ?? date('Y-m-01');
        $hasta = $_GET['hasta'] ?? date('Y-m-d');

        if ($tipo === 'reservas') {
            $reservas = Reserva::listarTodas(['fecha_desde' => $desde, 'fecha_hasta' => $hasta], 500, 0);
            $encabezados = ['ID Reserva', 'Fecha', 'Hora Inicio', 'Hora Fin', 'Duracion Min', 'Precio Acordado', 'Estado', 'Cliente', 'Patente', 'Servicio'];
            $filas = [];
            foreach ($reservas as $r) {
                $filas[] = [
                    $r['id'],
                    $r['fecha'],
                    $r['hora_inicio'],
                    $r['hora_fin'],
                    $r['duracion_minutos'],
                    $r['precio_acordado'],
                    $r['estado'],
                    $r['cliente_nombre'] . ' ' . $r['cliente_apellido'],
                    $r['patente'],
                    $r['servicio_nombre']
                ];
            }
            ReporteServicio::exportarCsv("washflow_reservas_{$desde}_{$hasta}", $encabezados, $filas);
        }
    }

    public function auditoria(): void {
        $this->requerirRol('administrador');

        $filtros = [
            'accion' => trim((string)($_GET['accion'] ?? '')),
            'entidad' => trim((string)($_GET['entidad'] ?? '')),
            'fecha_desde' => trim((string)($_GET['desde'] ?? '')),
            'fecha_hasta' => trim((string)($_GET['hasta'] ?? ''))
        ];

        $operaciones = RegistroOperacion::listarConFiltro($filtros, 100);
        $total = RegistroOperacion::contar($filtros);

        $this->render('admin/auditoria/index', [
            'titulo' => 'Historial de Operaciones (Auditoria) - WashFlow',
            'operaciones' => $operaciones,
            'filtros' => $filtros,
            'total' => $total
        ]);
    }

    // --- GESTION DE MODELOS DE VEHICULO (Requisito 3.2) ---
    public function modelos(): void {
        $this->requerirRol('administrador');
        $busqueda = trim((string)($_GET['q'] ?? ''));
        $tipos = TipoVehiculo::listarTodos();

        if ($busqueda !== '') {
            $modelos = ModeloVehiculo::buscar($busqueda, 100);
        } else {
            $modelos = ModeloVehiculo::listarTodos();
        }

        $this->render('admin/modelos/index', [
            'titulo' => 'Modelos de Vehiculos - WashFlow',
            'modelos' => $modelos,
            'tipos' => $tipos,
            'busqueda' => $busqueda
        ]);
    }

    public function guardarModelo(): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();

        $marca = trim((string)($_POST['marca'] ?? ''));
        $modelo = trim((string)($_POST['modelo'] ?? ''));
        $tipoId = (int)($_POST['tipo_vehiculo_id'] ?? 0);

        if (empty($marca) || empty($modelo) || $tipoId <= 0) {
            Sesion::setFlash('error', 'Todos los campos son obligatorios para registrar el modelo.');
            $this->redirect('/admin/modelos');
            return;
        }

        ModeloVehiculo::crear($marca, $modelo, $tipoId);
        Sesion::setFlash('exito', "Modelo {$marca} {$modelo} registrado exitosamente.");
        $this->redirect('/admin/modelos');
    }

    public function actualizarModelo(string $id): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();

        $marca = trim((string)($_POST['marca'] ?? ''));
        $modelo = trim((string)($_POST['modelo'] ?? ''));
        $tipoId = (int)($_POST['tipo_vehiculo_id'] ?? 0);
        $activo = !empty($_POST['activo']);

        ModeloVehiculo::actualizar((int)$id, $marca, $modelo, $tipoId, $activo);
        Sesion::setFlash('exito', 'Modelo actualizado correctamente.');
        $this->redirect('/admin/modelos');
    }

    // --- GESTION DE PREGUNTAS FRECUENTES (FAQ - Requisito 3.5) ---
    public function preguntas(): void {
        $this->requerirRol('administrador');
        $preguntas = PreguntaFrecuente::listarTodas();

        $this->render('admin/preguntas/index', [
            'titulo' => 'Preguntas Frecuentes - WashFlow',
            'preguntas' => $preguntas
        ]);
    }

    public function guardarPregunta(): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();

        $pregunta = trim((string)($_POST['pregunta'] ?? ''));
        $respuesta = trim((string)($_POST['respuesta'] ?? ''));
        $orden = (int)($_POST['orden'] ?? 0);

        if (empty($pregunta) || empty($respuesta)) {
            Sesion::setFlash('error', 'La pregunta y respuesta son obligatorias.');
            $this->redirect('/admin/preguntas');
            return;
        }

        PreguntaFrecuente::crear($pregunta, $respuesta, $orden);
        Sesion::setFlash('exito', 'Pregunta frecuente agregada con exito.');
        $this->redirect('/admin/preguntas');
    }

    public function actualizarPregunta(string $id): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();

        $pregunta = trim((string)($_POST['pregunta'] ?? ''));
        $respuesta = trim((string)($_POST['respuesta'] ?? ''));
        $orden = (int)($_POST['orden'] ?? 0);
        $activo = !empty($_POST['activo']);

        PreguntaFrecuente::actualizar((int)$id, $pregunta, $respuesta, $orden, $activo);
        Sesion::setFlash('exito', 'Pregunta frecuente actualizada.');
        $this->redirect('/admin/preguntas');
    }

    public function eliminarPregunta(string $id): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();

        PreguntaFrecuente::eliminar((int)$id);
        Sesion::setFlash('exito', 'Pregunta eliminada correctamente.');
        $this->redirect('/admin/preguntas');
    }

    // --- GESTION DE GALERIA DE TRABAJOS (Requisito 3.4) ---
    public function galeria(): void {
        $this->requerirRol('administrador');
        $items = Galeria::listarTodas();

        $this->render('admin/galeria/index', [
            'titulo' => 'Galeria de Trabajos - WashFlow',
            'items' => $items
        ]);
    }

    public function guardarGaleria(): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();

        $titulo = trim((string)($_POST['titulo'] ?? ''));
        $descripcion = trim((string)($_POST['descripcion'] ?? ''));
        $imagenUrl = trim((string)($_POST['imagen_url'] ?? ''));
        $categoria = trim((string)($_POST['categoria'] ?? 'trabajos'));
        $orden = (int)($_POST['orden'] ?? 0);

        if (empty($titulo) || empty($imagenUrl)) {
            Sesion::setFlash('error', 'El titulo y la URL de imagen son requeridos.');
            $this->redirect('/admin/galeria');
            return;
        }

        Galeria::crear($titulo, $descripcion, $imagenUrl, $categoria, $orden);
        Sesion::setFlash('exito', 'Elemento agregado a la galeria.');
        $this->redirect('/admin/galeria');
    }

    public function actualizarGaleria(string $id): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();

        $titulo = trim((string)($_POST['titulo'] ?? ''));
        $descripcion = trim((string)($_POST['descripcion'] ?? ''));
        $imagenUrl = trim((string)($_POST['imagen_url'] ?? ''));
        $categoria = trim((string)($_POST['categoria'] ?? 'trabajos'));
        $orden = (int)($_POST['orden'] ?? 0);
        $activo = !empty($_POST['activo']);

        Galeria::actualizar((int)$id, $titulo, $descripcion, $imagenUrl, $categoria, $orden, $activo);
        Sesion::setFlash('exito', 'Elemento de galeria actualizado.');
        $this->redirect('/admin/galeria');
    }

    public function eliminarGaleria(string $id): void {
        $this->requerirRol('administrador');
        $this->validarCsrf();

        Galeria::eliminar((int)$id);
        Sesion::setFlash('exito', 'Elemento eliminado de la galeria.');
        $this->redirect('/admin/galeria');
    }
}
