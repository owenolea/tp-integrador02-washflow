<?php
/**
 * WashFlow - Modelo PrecioServicio con historial y vigencia (Diagramas 04a, 04b)
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use PDO;
use WashFlow\Core\BaseDatos;

class PrecioServicio {
    public static function obtenerPrecioVigente(int $servicioId, int $tipoVehiculoId): ?float {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT precio
            FROM precios_servicio
            WHERE servicio_id = ? AND tipo_vehiculo_id = ? AND vigente_desde <= CURDATE()
            ORDER BY vigente_desde DESC, id DESC
            LIMIT 1
        ');
        $stmt->execute([$servicioId, $tipoVehiculoId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (float)$val : null;
    }

    public static function listarPreciosVigentes(): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->query('
            SELECT s.id AS servicio_id, s.nombre AS servicio_nombre, s.descripcion AS servicio_descripcion,
                   t.id AS tipo_vehiculo_id, t.nombre AS tipo_nombre, t.duracion_minutos,
                   (
                       SELECT ps.precio
                       FROM precios_servicio ps
                       WHERE ps.servicio_id = s.id AND ps.tipo_vehiculo_id = t.id AND ps.vigente_desde <= CURDATE()
                       ORDER BY ps.vigente_desde DESC, ps.id DESC
                       LIMIT 1
                   ) AS precio_vigente
            FROM servicios s
            CROSS JOIN tipos_vehiculo t
            WHERE s.activo = 1
            ORDER BY s.id ASC, t.id ASC
        ');
        return $stmt->fetchAll();
    }

    public static function modificarPrecio(int $servicioId, int $tipoVehiculoId, float $nuevoPrecio): bool {
        if ($nuevoPrecio <= 0) {
            return false;
        }

        $db = BaseDatos::getInstancia()->getConexion();
        // Insertar nuevo registro para conservar historial inmutable
        $stmt = $db->prepare('
            INSERT INTO precios_servicio (servicio_id, tipo_vehiculo_id, precio, vigente_desde)
            VALUES (?, ?, ?, CURDATE())
        ');
        return $stmt->execute([$servicioId, $tipoVehiculoId, $nuevoPrecio]);
    }

    public static function historialPrecios(int $servicioId = 0, int $tipoVehiculoId = 0): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $sql = '
            SELECT ps.*, s.nombre AS servicio_nombre, t.nombre AS tipo_nombre
            FROM precios_servicio ps
            JOIN servicios s ON ps.servicio_id = s.id
            JOIN tipos_vehiculo t ON ps.tipo_vehiculo_id = t.id
            WHERE 1=1
        ';
        $params = [];

        if ($servicioId > 0) {
            $sql .= ' AND ps.servicio_id = ?';
            $params[] = $servicioId;
        }
        if ($tipoVehiculoId > 0) {
            $sql .= ' AND ps.tipo_vehiculo_id = ?';
            $params[] = $tipoVehiculoId;
        }

        $sql .= ' ORDER BY ps.vigente_desde DESC, ps.id DESC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
