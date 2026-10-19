<?php
/**
 * WashFlow - Servicio de Envio de Correos Desacoplado (Diagramas 11, 12)
 * Desactivado por defecto. Si falla, nunca interrumpe el flujo principal.
 */

declare(strict_types=1);

namespace WashFlow\Servicios;

use Throwable;
use WashFlow\Modelos\Usuario;

class CorreoServicio {
    /**
     * Envia una notificacion por correo a un usuario de manera desacoplada y segura.
     */
    public static function enviarNotificacionSegura(int $usuarioId, string $asunto, string $cuerpoTexto): bool {
        try {
            $config = require __DIR__ . '/../config/config.php';
            $cfgCorreo = $config['correo'] ?? [];

            if (empty($cfgCorreo['activo'])) {
                return false; // Desactivado por configuracion
            }

            $usuario = Usuario::buscarPorId($usuarioId);
            if (!$usuario || empty($usuario['correo'])) {
                return false;
            }

            return self::enviar(
                $usuario['correo'],
                "{$usuario['nombre']} {$usuario['apellido']}",
                $asunto,
                $cuerpoTexto,
                $cfgCorreo
            );
        } catch (Throwable $e) {
            // Registrar advertencia en logs sin romper la ejecucion
            error_log('WashFlow CorreoServicio Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envio de correo usando conexion socket SMTP nativa de PHP (sin librerias externas pesadas)
     */
    public static function enviar(string $destinatarioCorreo, string $destinatarioNombre, string $asunto, string $mensaje, array $cfg): bool {
        $host = $cfg['host'] ?? '';
        $puerto = (int)($cfg['puerto'] ?? 587);
        $usuario = $cfg['usuario'] ?? '';
        $contrasena = $cfg['contrasena'] ?? '';
        $remitente = $cfg['remitente_correo'] ?? 'no-responder@washflow.com';
        $remitenteNombre = $cfg['remitente_nombre'] ?? 'WashFlow';

        if (empty($host)) {
            return false;
        }

        $timeout = 5;
        $socket = @fsockopen($host, $puerto, $errno, $errstr, $timeout);
        if (!$socket) {
            error_log("WashFlow SMTP no pudo conectar con {$host}:{$puerto} ($errno - $errstr)");
            return false;
        }

        stream_set_timeout($socket, $timeout);

        $leer = function() use ($socket) {
            $res = '';
            while ($linea = fgets($socket, 515)) {
                $res .= $linea;
                if (isset($linea[3]) && $linea[3] === ' ') {
                    break;
                }
            }
            return $res;
        };

        $escribir = function(string $cmd) use ($socket) {
            fputs($socket, $cmd . "\r\n");
        };

        $res = $leer(); // Banner de bienvenida

        $escribir("EHLO " . gethostname());
        $leer();

        if (!empty($usuario) && !empty($contrasena)) {
            $escribir("AUTH LOGIN");
            $leer();
            $escribir(base64_encode($usuario));
            $leer();
            $escribir(base64_encode($contrasena));
            $leer();
        }

        $escribir("MAIL FROM: <{$remitente}>");
        $leer();

        $escribir("RCPT TO: <{$destinatarioCorreo}>");
        $leer();

        $escribir("DATA");
        $leer();

        $cabeceras = [
            "From: {$remitenteNombre} <{$remitente}>",
            "To: {$destinatarioNombre} <{$destinatarioCorreo}>",
            "Subject: =?UTF-8?B?" . base64_encode($asunto) . "?=",
            "MIME-Version: 1.0",
            "Content-Type: text/plain; charset=UTF-8",
            "Content-Transfer-Encoding: 8bit"
        ];

        $cuerpoCompleto = implode("\r\n", $cabeceras) . "\r\n\r\n" . $mensaje . "\r\n.";
        $escribir($cuerpoCompleto);
        $leer();

        $escribir("QUIT");
        $leer();

        fclose($socket);
        return true;
    }
}
