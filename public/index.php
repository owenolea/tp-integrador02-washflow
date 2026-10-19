<?php
/**
 * WashFlow - Punto Unico de Entrada de la Aplicacion Web (Front Controller)
 * Arquitectura MVC propia sin frameworks externos.
 */

declare(strict_types=1);

// Soporte para servidor embebido de PHP (php -S)
if (php_sapi_name() === 'cli-server') {
    $ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $archivo = __DIR__ . $ruta;
    if ($ruta !== '/' && file_exists($archivo) && !is_dir($archivo)) {
        return false;
    }
}

// Definir constante base
define('WASHFLOW_ROOT', dirname(__DIR__));

// Configurar reporte de errores segun configuracion
$config = require WASHFLOW_ROOT . '/app/config/config.php';
if (!empty($config['depuracion'])) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Configurar zona horaria oficial
date_default_timezone_set($config['timezone'] ?? 'America/Argentina/Buenos_Aires');

// 1. Autocargador PSR-4 propio
spl_autoload_register(function (string $clase) {
    $prefijo = 'WashFlow\\';
    if (!str_starts_with($clase, $prefijo)) {
        return;
    }

    $claseRelativa = substr($clase, strlen($prefijo));
    $partes = explode('\\', $claseRelativa);

    $modulo = strtolower(array_shift($partes));
    $subruta = implode('/', $partes);

    // Mapeo a carpetas del proyecto
    $directorio = match ($modulo) {
        'core' => WASHFLOW_ROOT . '/app/core',
        'controladores' => WASHFLOW_ROOT . '/app/controladores',
        'modelos' => WASHFLOW_ROOT . '/app/modelos',
        'servicios' => WASHFLOW_ROOT . '/app/servicios',
        default => null
    };

    // Mapeo de subclases agrupadas
    $aliasAgrupados = [
        'modelos/Cliente' => 'modelos/Usuario',
        'modelos/Empleado' => 'modelos/Usuario',
        'modelos/Administrador' => 'modelos/Usuario',
        'modelos/ImagenGaleria' => 'modelos/Galeria',
    ];
    $claveSubruta = $modulo . '/' . $subruta;
    if (isset($aliasAgrupados[$claveSubruta])) {
        $subruta = explode('/', $aliasAgrupados[$claveSubruta])[1];
    }

    if ($directorio !== null) {
        $archivo = $directorio . '/' . $subruta . '.php';
        if (file_exists($archivo)) {
            require_once $archivo;
        }
    }
});

// Capturador de excepciones no controladas para pagina 500 propia
set_exception_handler(function (\Throwable $e) use ($config) {
    error_log("WashFlow Error Fatal: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);

    if (!empty($config['depuracion'])) {
        $mensaje = $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine();
    } else {
        $mensaje = 'Ocurrio un problema imprevisto en el servidor. Intente nuevamente en unos minutos.';
    }

    WashFlow\Core\Vista::render('errores/500', ['mensaje' => $mensaje], null);
    exit;
});

// 2. Iniciar Sesion Segura
WashFlow\Core\Sesion::iniciar();

// 3. Configurar Enrutador
use WashFlow\Core\Router;
use WashFlow\Controladores\InicioControlador;
use WashFlow\Controladores\AutenticacionControlador;
use WashFlow\Controladores\ClienteControlador;
use WashFlow\Controladores\ReservaControlador;
use WashFlow\Controladores\SolicitudControlador;
use WashFlow\Controladores\EmpleadoControlador;
use WashFlow\Controladores\AdminControlador;

$router = new Router();

// --- RUTAS PUBLICAS Y ASISTENTE SIN CUENTA (Requisitos 3.1, 3.3, 3.4) ---
$router->get('/', [InicioControlador::class, 'index']);
$router->get('/guia-lavados', [InicioControlador::class, 'guiaLavados']);
$router->get('/reservar', [ReservaControlador::class, 'mostrarReservarPublica']);
$router->post('/reservar', [ReservaControlador::class, 'guardarReservaPublica']);
$router->get('/reserva/{codigo}/{token}', [ReservaControlador::class, 'mostrarDetallePublico']);
$router->post('/reserva/{codigo}/{token}/reprogramar', [ReservaControlador::class, 'reprogramarPublica']);
$router->post('/reserva/{codigo}/{token}/cancelar', [ReservaControlador::class, 'cancelarPublica']);
$router->post('/reserva/{codigo}/{token}/crear-cuenta', [ReservaControlador::class, 'crearCuentaDesdePublica']);
$router->get('/mi-reserva', [ReservaControlador::class, 'mostrarBuscarPublica']);
$router->post('/mi-reserva', [ReservaControlador::class, 'buscarPublica']);
$router->get('/login', [AutenticacionControlador::class, 'mostrarLogin']);
$router->post('/login', [AutenticacionControlador::class, 'login']);
$router->get('/registro', [AutenticacionControlador::class, 'mostrarRegistro']);
$router->post('/registro', [AutenticacionControlador::class, 'registro']);
$router->post('/logout', [AutenticacionControlador::class, 'logout']);
$router->get('/recuperar', [AutenticacionControlador::class, 'mostrarRecuperar']);
$router->post('/recuperar', [AutenticacionControlador::class, 'enviarRecuperacion']);
$router->get('/restablecer', [AutenticacionControlador::class, 'mostrarRestablecer']);
$router->post('/restablecer', [AutenticacionControlador::class, 'restablecer']);

// --- APIS INTERNAS PARA ASISTENTE Y DISPONIBILIDAD ---
$router->get('/api/modelos', [ReservaControlador::class, 'apiModelos']);
$router->get('/api/disponibilidad', [ReservaControlador::class, 'apiDisponibilidad']);
$router->get('/api/precio', [ReservaControlador::class, 'apiPrecio']);

// --- RUTAS DEL CLIENTE ---
$router->get('/cliente/dashboard', [ClienteControlador::class, 'dashboard']);
$router->get('/cliente/vehiculos', [ClienteControlador::class, 'vehiculos']);
$router->get('/cliente/vehiculos/crear', [ClienteControlador::class, 'mostrarCrearVehiculo']);
$router->post('/cliente/vehiculos/crear', [ClienteControlador::class, 'guardarVehiculo']);
$router->get('/cliente/vehiculos/{id}/editar', [ClienteControlador::class, 'mostrarEditarVehiculo']);
$router->post('/cliente/vehiculos/{id}/editar', [ClienteControlador::class, 'actualizarVehiculo']);
$router->post('/cliente/vehiculos/{id}/desactivar', [ClienteControlador::class, 'desactivarVehiculo']);
$router->get('/cliente/reservar', [ReservaControlador::class, 'mostrarAsistente']);
$router->post('/cliente/reservas/guardar', [ReservaControlador::class, 'guardarReserva']);
$router->get('/cliente/reservas', [ReservaControlador::class, 'misReservas']);
$router->get('/cliente/reservas/{id}/modificar', [ReservaControlador::class, 'mostrarModificar']);
$router->post('/cliente/reservas/{id}/reprogramar', [ReservaControlador::class, 'reprogramarDirecta']);
$router->post('/cliente/reservas/{id}/cancelar', [ReservaControlador::class, 'cancelarDirecta']);
$router->get('/cliente/reservas/{id}/solicitar', [SolicitudControlador::class, 'mostrarSolicitar']);
$router->post('/cliente/reservas/{id}/solicitar', [SolicitudControlador::class, 'guardarSolicitud']);
$router->get('/cliente/notificaciones', [ClienteControlador::class, 'notificaciones']);
$router->post('/cliente/notificaciones/marcar-todas', [ClienteControlador::class, 'marcarTodasNotificacionesLeidas']);

// --- RUTAS DEL EMPLEADO / OPERACIONES ---
$router->get('/empleado/dashboard', [EmpleadoControlador::class, 'dashboard']);
$router->get('/empleado/agenda', [EmpleadoControlador::class, 'agenda']);
$router->get('/empleado/buscar', [EmpleadoControlador::class, 'buscar']);
$router->post('/empleado/llegada', [EmpleadoControlador::class, 'registrarLlegada']);
$router->post('/empleado/terminar-lavado', [EmpleadoControlador::class, 'marcarLavadoTerminado']);
$router->post('/empleado/ausencia', [EmpleadoControlador::class, 'registrarAusencia']);
$router->get('/empleado/lavado-directo', [EmpleadoControlador::class, 'mostrarLavadoDirecto']);
$router->post('/empleado/lavado-directo', [EmpleadoControlador::class, 'guardarLavadoDirecto']);
$router->get('/empleado/solicitudes', [EmpleadoControlador::class, 'solicitudes']);
$router->post('/empleado/solicitudes/{id}/aprobar', [SolicitudControlador::class, 'aprobar']);
$router->post('/empleado/solicitudes/{id}/rechazar', [SolicitudControlador::class, 'rechazar']);
$router->post('/empleado/reservas/{id}/corregir-categoria', [EmpleadoControlador::class, 'corregirCategoriaVehiculo']);

// --- RUTAS EXCLUSIVAS DEL ADMINISTRADOR ---
$router->get('/admin/dashboard', [AdminControlador::class, 'dashboard']);
$router->get('/admin/usuarios', [AdminControlador::class, 'usuarios']);
$router->get('/admin/usuarios/crear', [AdminControlador::class, 'mostrarCrearUsuario']);
$router->post('/admin/usuarios/crear', [AdminControlador::class, 'guardarUsuario']);
$router->get('/admin/usuarios/{id}/editar', [AdminControlador::class, 'mostrarEditarUsuario']);
$router->post('/admin/usuarios/{id}/editar', [AdminControlador::class, 'actualizarUsuario']);
$router->post('/admin/usuarios/{id}/desactivar', [AdminControlador::class, 'desactivarUsuario']);
$router->post('/admin/usuarios/{id}/activar', [AdminControlador::class, 'activarUsuario']);
$router->get('/admin/vinculaciones', [AdminControlador::class, 'vinculaciones']);
$router->post('/admin/vinculaciones', [AdminControlador::class, 'ejecutarVinculacion']);
$router->get('/admin/precios', [AdminControlador::class, 'precios']);
$router->post('/admin/precios/modificar', [AdminControlador::class, 'modificarPrecio']);
$router->post('/admin/duraciones/modificar', [AdminControlador::class, 'modificarDuracion']);
$router->get('/admin/bloqueos', [AdminControlador::class, 'bloqueos']);
$router->post('/admin/bloqueos', [AdminControlador::class, 'guardarBloqueo']);
$router->post('/admin/bloqueos/{id}/eliminar', [AdminControlador::class, 'eliminarBloqueo']);
$router->get('/admin/informes', [AdminControlador::class, 'informes']);
$router->get('/admin/exportar-csv', [AdminControlador::class, 'exportarCsv']);
$router->get('/admin/auditoria', [AdminControlador::class, 'auditoria']);

// --- GESTION DE CONTENIDOS Y MODELOS (Requisitos 3.2, 3.4, 3.5) ---
$router->get('/admin/modelos', [AdminControlador::class, 'modelos']);
$router->post('/admin/modelos/crear', [AdminControlador::class, 'guardarModelo']);
$router->post('/admin/modelos/{id}/actualizar', [AdminControlador::class, 'actualizarModelo']);
$router->get('/admin/preguntas', [AdminControlador::class, 'preguntas']);
$router->post('/admin/preguntas/crear', [AdminControlador::class, 'guardarPregunta']);
$router->post('/admin/preguntas/{id}/actualizar', [AdminControlador::class, 'actualizarPregunta']);
$router->post('/admin/preguntas/{id}/eliminar', [AdminControlador::class, 'eliminarPregunta']);
$router->get('/admin/galeria', [AdminControlador::class, 'galeria']);
$router->post('/admin/galeria/crear', [AdminControlador::class, 'guardarGaleria']);
$router->post('/admin/galeria/{id}/actualizar', [AdminControlador::class, 'actualizarGaleria']);
$router->post('/admin/galeria/{id}/eliminar', [AdminControlador::class, 'eliminarGaleria']);

// 4. Despachar la peticion entrante
$router->despachar();
