<?php
/**
 * WashFlow - Modelo RecuperacionContrasena (Tokens con expiracion)
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use PDO;
use WashFlow\Core\BaseDatos;

class RecuperacionContrasena {
    public static function crearToken(int $usuarioId, int $minutosExpiracion = 60): string {
        $token = bin2hex(random_bytes(32));
        $db = BaseDatos::getInstancia()->getConexion();

        // Invalidar tokens previos del usuario
        $stmtInvalidar = $db->prepare('UPDATE recuperaciones_contrasena SET usado = 1 WHERE usuario_id = ?');
        $stmtInvalidar->execute([$usuarioId]);

        $stmt = $db->prepare('
            INSERT INTO recuperaciones_contrasena (usuario_id, token, expira_en, usado, creado_en)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE), 0, NOW())
        ');
        $stmt->execute([$usuarioId, $token, $minutosExpiracion]);
        return $token;
    }

    public static function buscarTokenValido(string $token): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT rc.*, u.id AS usuario_id, u.nombre, u.apellido, u.correo, u.activo
            FROM recuperaciones_contrasena rc
            JOIN usuarios u ON rc.usuario_id = u.id
            WHERE rc.token = ? AND rc.usado = 0 AND rc.expira_en > NOW()
            LIMIT 1
        ');
        $stmt->execute([$token]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function marcarUsado(string $token): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('UPDATE recuperaciones_contrasena SET usado = 1 WHERE token = ?');
        return $stmt->execute([$token]);
    }
}
