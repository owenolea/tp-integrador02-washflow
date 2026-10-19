<?php
/**
 * WashFlow - Gestor de Sesiones Seguras
 */

declare(strict_types=1);

namespace WashFlow\Core;

class Sesion {
    public static function iniciar(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $config = require __DIR__ . '/../config/config.php';
            $nombre = $config['seguridad']['nombre_sesion'] ?? 'washflow_sess';

            session_name($nombre);

            $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $esHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_start();
        }
    }

    public static function regenerar(bool $borrarSesionAntigua = true): void {
        self::iniciar();
        session_regenerate_id($borrarSesionAntigua);
    }

    public static function set(string $clave, mixed $valor): void {
        self::iniciar();
        $_SESSION[$clave] = $valor;
    }

    public static function get(string $clave, mixed $defecto = null): mixed {
        self::iniciar();
        return $_SESSION[$clave] ?? $defecto;
    }

    public static function obtener(string $clave, mixed $defecto = null): mixed {
        return self::get($clave, $defecto);
    }

    public static function has(string $clave): bool {
        self::iniciar();
        return isset($_SESSION[$clave]);
    }

    public static function remove(string $clave): void {
        self::iniciar();
        unset($_SESSION[$clave]);
    }

    public static function destruir(): void {
        self::iniciar();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    // Mensajes Flash (notificaciones temporales de interfaz)
    public static function setFlash(string $tipo, string $mensaje): void {
        self::iniciar();
        $_SESSION['flash'][$tipo][] = $mensaje;
    }

    public static function getFlash(string $tipo): ?string {
        self::iniciar();
        if (!empty($_SESSION['flash'][$tipo])) {
            $msg = array_shift($_SESSION['flash'][$tipo]);
            if (empty($_SESSION['flash'][$tipo])) {
                unset($_SESSION['flash'][$tipo]);
            }
            return is_string($msg) ? $msg : null;
        }
        return null;
    }

    public static function getFlashes(): array {
        self::iniciar();
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }

    public static function estaAutenticado(): bool {
        return self::get('usuario_id') !== null;
    }

    public static function getUsuarioId(): ?int {
        $id = self::get('usuario_id');
        return $id !== null ? (int)$id : null;
    }

    public static function getRol(): ?string {
        return self::get('usuario_rol');
    }

    public static function getUsuario(): ?array {
        return self::get('usuario');
    }
}
