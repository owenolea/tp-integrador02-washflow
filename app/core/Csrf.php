<?php
/**
 * WashFlow - Proteccion contra falsificacion de peticiones entre sitios (CSRF)
 */

declare(strict_types=1);

namespace WashFlow\Core;

class Csrf {
    private const CLAVE_TOKEN = '_csrf_token';

    public static function generarToken(): string {
        Sesion::iniciar();
        $token = Sesion::get(self::CLAVE_TOKEN);
        if (!$token || !is_string($token)) {
            $token = bin2hex(random_bytes(32));
            Sesion::set(self::CLAVE_TOKEN, $token);
        }
        return $token;
    }

    public static function obtenerToken(): string {
        return self::generarToken();
    }

    public static function validar(?string $token): bool {
        Sesion::iniciar();
        $guardado = Sesion::get(self::CLAVE_TOKEN);
        if (!$guardado || !is_string($guardado) || !$token) {
            return false;
        }
        return hash_equals($guardado, $token);
    }

    public static function campoHtml(): string {
        $token = htmlspecialchars(self::generarToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
    }
}
