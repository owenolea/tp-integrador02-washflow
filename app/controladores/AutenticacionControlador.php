<?php
/**
 * WashFlow - Controlador de Autenticacion y Seguridad (Diagramas 01, 11, 12)
 * Reglas de negocio y seguridad:
 * - password_verify y password_hash (BCRYPT)
 * - session_regenerate_id al iniciar sesion
 * - Limite de intentos fallidos con bloqueo temporal
 * - Mensajes genericos de error para prevenir enumeracion
 * - Sin registro publico de administradores (solo clientes)
 */

declare(strict_types=1);

namespace WashFlow\Controladores;

use WashFlow\Core\Controlador;
use WashFlow\Core\Csrf;
use WashFlow\Core\Sesion;
use WashFlow\Core\Validador;
use WashFlow\Modelos\RecuperacionContrasena;
use WashFlow\Modelos\RegistroOperacion;
use WashFlow\Modelos\Usuario;

class AutenticacionControlador extends Controlador {
    public function mostrarLogin(): void {
        $this->requerirInvitado();
        $this->render('auth/login', [
            'titulo' => 'Iniciar Sesion - WashFlow'
        ], 'layouts/auth');
    }

    public function login(): void {
        $this->requerirInvitado();
        $this->validarCsrf();

        $validador = new Validador($_POST);
        $validador->validar([
            'correo' => 'requerido|correo',
            'contrasena' => 'requerido'
        ]);

        if ($validador->tieneErrores()) {
            $this->render('auth/login', [
                'titulo' => 'Iniciar Sesion - WashFlow',
                'errores' => $validador->getErrores(),
                'valores' => $validador->getDatos()
            ], 'layouts/auth');
            return;
        }

        $correo = (string)$validador->getDato('correo');
        $contrasena = (string)$validador->getDato('contrasena');

        $usuario = Usuario::buscarPorCorreo($correo);

        // Si el usuario existe y esta temporalmente bloqueado por exceso de intentos
        if ($usuario && Usuario::estaBloqueado($usuario)) {
            $this->render('auth/login', [
                'titulo' => 'Iniciar Sesion - WashFlow',
                'errorGeneral' => 'Cuenta bloqueada temporalmente por multiples intentos fallidos. Intente mas tarde.',
                'valores' => ['correo' => $correo]
            ], 'layouts/auth');
            return;
        }

        // Validacion de credenciales y usuario activo
        if (!$usuario || (int)$usuario['activo'] !== 1 || empty($usuario['contrasena_hash']) || !password_verify($contrasena, $usuario['contrasena_hash'])) {
            if ($usuario) {
                Usuario::incrementarIntentosFallidos((int)$usuario['id']);
                $intentos = (int)$usuario['intentos_fallidos'] + 1;
                if ($intentos >= 5) {
                    Usuario::bloquearTemporalmente((int)$usuario['id'], 15);
                }
            }

            // Mensaje generico para evitar enumeracion de cuentas
            $this->render('auth/login', [
                'titulo' => 'Iniciar Sesion - WashFlow',
                'errorGeneral' => 'Correo electronico o contrasena incorrectos.',
                'valores' => ['correo' => $correo]
            ], 'layouts/auth');
            return;
        }

        // Login exitoso: reiniciar contador de fallos y regenerar ID de sesion
        Usuario::reiniciarIntentosFallidos((int)$usuario['id']);
        Sesion::regenerar(true);

        Sesion::set('usuario_id', (int)$usuario['id']);
        Sesion::set('usuario_rol', $usuario['rol']);
        Sesion::set('usuario', [
            'id' => (int)$usuario['id'],
            'rol' => $usuario['rol'],
            'nombre' => $usuario['nombre'],
            'apellido' => $usuario['apellido'],
            'correo' => $usuario['correo'],
            'telefono' => $usuario['telefono']
        ]);

        RegistroOperacion::registrar(
            (int)$usuario['id'],
            'LOGIN',
            'Usuario',
            (int)$usuario['id'],
            "Inicio de sesion exitoso desde {$_SERVER['REMOTE_ADDR']}."
        );

        Sesion::setFlash('exito', "Bienvenido/a, {$usuario['nombre']}.");

        // Redireccion inteligente segun rol
        if ($usuario['rol'] === 'administrador') {
            $this->redirect('/admin/dashboard');
        } elseif ($usuario['rol'] === 'empleado') {
            $this->redirect('/empleado/dashboard');
        } else {
            $this->redirect('/cliente/dashboard');
        }
    }

    public function mostrarRegistro(): void {
        $this->requerirInvitado();
        $this->render('auth/registro', [
            'titulo' => 'Registro de Clientes - WashFlow'
        ], 'layouts/auth');
    }

    public function registro(): void {
        $this->requerirInvitado();
        $this->validarCsrf();

        $validador = new Validador($_POST);
        $validador->validar([
            'nombre' => 'requerido|min:2|max:100',
            'apellido' => 'requerido|min:2|max:100',
            'telefono' => 'requerido|min:6|max:30',
            'correo' => 'requerido|correo|max:150',
            'contrasena' => 'requerido|min:8',
            'confirmar_contrasena' => 'requerido'
        ]);

        $c1 = (string)$validador->getDato('contrasena');
        $c2 = (string)$validador->getDato('confirmar_contrasena');
        if ($c1 !== $c2) {
            $validador->validar(['confirmar_contrasena' => 'in:coincide']); // fuerza error
        }

        $correo = (string)$validador->getDato('correo');
        if (Usuario::buscarPorCorreo($correo)) {
            $this->render('auth/registro', [
                'titulo' => 'Registro de Clientes - WashFlow',
                'errorGeneral' => 'Ya existe una cuenta registrada con este correo electronico.',
                'valores' => $validador->getDatos()
            ], 'layouts/auth');
            return;
        }

        if ($validador->tieneErrores()) {
            $this->render('auth/registro', [
                'titulo' => 'Registro de Clientes - WashFlow',
                'errores' => $validador->getErrores(),
                'valores' => $validador->getDatos()
            ], 'layouts/auth');
            return;
        }

        $hash = password_hash($c1, PASSWORD_BCRYPT);

        // Registro publico SOLO crea usuarios con rol cliente
        $nuevoId = Usuario::crear([
            'rol' => 'cliente',
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
            $nuevoId,
            'REGISTRO_CLIENTE',
            'Usuario',
            $nuevoId,
            "Nuevo cliente registrado en la plataforma: {$correo}."
        );

        Sesion::setFlash('exito', 'Su cuenta ha sido creada exitosamente. Inicie sesion para comenzar.');
        $this->redirect('/login');
    }

    public function logout(): void {
        $this->validarCsrf();
        $usuarioId = Sesion::getUsuarioId();
        if ($usuarioId) {
            RegistroOperacion::registrar($usuarioId, 'LOGOUT', 'Usuario', $usuarioId, 'Cierre de sesion.');
        }
        Sesion::destruir();
        $this->redirect('/login');
    }

    public function mostrarRecuperar(): void {
        $this->requerirInvitado();
        $this->render('auth/recuperar', [
            'titulo' => 'Recuperar Contrasena - WashFlow'
        ], 'layouts/auth');
    }

    public function enviarRecuperacion(): void {
        $this->requerirInvitado();
        $this->validarCsrf();

        $validador = new Validador($_POST);
        $validador->validar(['correo' => 'requerido|correo']);

        if ($validador->tieneErrores()) {
            $this->render('auth/recuperar', [
                'titulo' => 'Recuperar Contrasena - WashFlow',
                'errores' => $validador->getErrores()
            ], 'layouts/auth');
            return;
        }

        $correo = (string)$validador->getDato('correo');
        $usuario = Usuario::buscarPorCorreo($correo);

        $tokenGenerado = null;
        if ($usuario && (int)$usuario['activo'] === 1 && (int)$usuario['es_ocasional'] === 0) {
            $tokenGenerado = RecuperacionContrasena::crearToken((int)$usuario['id'], 60);

            RegistroOperacion::registrar(
                (int)$usuario['id'],
                'SOLICITUD_RECUPERACION',
                'Usuario',
                (int)$usuario['id'],
                "Solicitud de token de recuperacion de clave para {$correo}."
            );
        }

        // Siempre mostrar confirmacion generica para evitar enumeracion
        $this->render('auth/recuperar', [
            'titulo' => 'Recuperar Contrasena - WashFlow',
            'enviado' => true,
            'tokenDemo' => $tokenGenerado // Visible en entorno local para facilitar pruebas sin SMTP
        ], 'layouts/auth');
    }

    public function mostrarRestablecer(): void {
        $this->requerirInvitado();
        $token = $_GET['token'] ?? '';
        $registro = RecuperacionContrasena::buscarTokenValido($token);

        if (!$registro) {
            Sesion::setFlash('error', 'El enlace de recuperacion es invalido o ha expirado.');
            $this->redirect('/recuperar');
        }

        $this->render('auth/restablecer', [
            'titulo' => 'Restablecer Contrasena - WashFlow',
            'token' => $token,
            'correo' => $registro['correo']
        ], 'layouts/auth');
    }

    public function restablecer(): void {
        $this->requerirInvitado();
        $this->validarCsrf();

        $token = (string)($_POST['token'] ?? '');
        $registro = RecuperacionContrasena::buscarTokenValido($token);

        if (!$registro) {
            Sesion::setFlash('error', 'El enlace de recuperacion es invalido o ha expirado.');
            $this->redirect('/recuperar');
        }

        $validador = new Validador($_POST);
        $validador->validar([
            'contrasena' => 'requerido|min:8',
            'confirmar_contrasena' => 'requerido'
        ]);

        $c1 = (string)$validador->getDato('contrasena');
        $c2 = (string)$validador->getDato('confirmar_contrasena');
        if ($c1 !== $c2) {
            $this->render('auth/restablecer', [
                'titulo' => 'Restablecer Contrasena - WashFlow',
                'token' => $token,
                'errorGeneral' => 'Las contrasenas no coinciden.'
            ], 'layouts/auth');
            return;
        }

        $hash = password_hash($c1, PASSWORD_BCRYPT);
        Usuario::actualizar((int)$registro['usuario_id'], ['contrasena_hash' => $hash]);
        RecuperacionContrasena::marcarUsado($token);

        RegistroOperacion::registrar(
            (int)$registro['usuario_id'],
            'RESTABLECER_CONTRASENA',
            'Usuario',
            (int)$registro['usuario_id'],
            "Contrasena restablecida exitosamente mediante token."
        );

        Sesion::setFlash('exito', 'Su contrasena ha sido actualizada exitosamente. Inicie sesion con su nueva clave.');
        $this->redirect('/login');
    }
}
