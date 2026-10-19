<?php
/**
 * WashFlow - Modelo Notificacion (Diagramas 04b, 12)
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use PDO;
use WashFlow\Core\BaseDatos;
use WashFlow\Servicios\CorreoServicio;

class Notificacion {
    public static function crear(int $usuarioId, string $titulo, string $mensaje, string $canal = 'interna', ?int $reservaId = null): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            INSERT INTO notificaciones (usuario_id, titulo, mensaje, leida, canal, fecha_hora, reserva_id)
            VALUES (?, ?, ?, 0, ?, NOW(), ?)
        ');
        $stmt->execute([
            $usuarioId,
            $titulo,
            $mensaje,
            $canal,
            $reservaId
        ]);
        $notifId = (int)$db->lastInsertId();

        // Si el canal es correo o esta activado el envio por correo, intentar envio desacoplado
        CorreoServicio::enviarNotificacionSegura($usuarioId, $titulo, $mensaje);

        return $notifId;
    }

    public static function listarPorUsuario(int $usuarioId, int $limite = 30): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT *
            FROM notificaciones
            WHERE usuario_id = ?
            ORDER BY fecha_hora DESC
            LIMIT ?
        ');
        $stmt->bindValue(1, $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function contarNoLeidas(int $usuarioId): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT COUNT(*)
            FROM notificaciones
            WHERE usuario_id = ? AND leida = 0
        ');
        $stmt->execute([$usuarioId]);
        return (int)$stmt->fetchColumn();
    }

    public static function marcarLeida(int $id, int $usuarioId): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('UPDATE notificaciones SET leida = 1 WHERE id = ? AND usuario_id = ?');
        return $stmt->execute([$id, $usuarioId]);
    }

    public static function marcarTodasLeidas(int $usuarioId): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('UPDATE notificaciones SET leida = 1 WHERE usuario_id = ? AND leida = 0');
        return $stmt->execute([$usuarioId]);
    }

    /**
     * Envia una notificacion interna a todos los administradores activos (Seccion 14)
     */
    public static function notificarAdmins(string $titulo, string $mensaje, ?int $reservaId = null): void {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE rol = 'administrador' AND activo = 1");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($admins as $adminId) {
            self::crear((int)$adminId, $titulo, $mensaje, 'interna', $reservaId);
        }
    }

    public static function enviarPorCorreo(int $usuarioId, string $asunto, string $cuerpoHtml): bool {
        return CorreoServicio::enviarNotificacionSegura($usuarioId, $asunto, $cuerpoHtml);
    }
}

