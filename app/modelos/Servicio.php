<?php
/**
 * WashFlow - Modelo Servicio (Diagramas 04a, 04b)
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use PDO;
use WashFlow\Core\BaseDatos;

class Servicio {
    public static function listarActivos(): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->query('SELECT * FROM servicios WHERE activo = 1 ORDER BY id ASC');
        return $stmt->fetchAll();
    }

    public static function listarTodos(): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->query('SELECT * FROM servicios ORDER BY id ASC');
        return $stmt->fetchAll();
    }

    public static function buscarPorId(int $id): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('SELECT * FROM servicios WHERE id = ?');
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function crear(string $nombre, ?string $descripcion): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('INSERT INTO servicios (nombre, descripcion, activo) VALUES (?, ?, 1)');
        $stmt->execute([$nombre, $descripcion]);
        return (int)$db->lastInsertId();
    }

    public static function actualizar(int $id, string $nombre, ?string $descripcion, bool $activo): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('UPDATE servicios SET nombre = ?, descripcion = ?, activo = ? WHERE id = ?');
        return $stmt->execute([$nombre, $descripcion, $activo ? 1 : 0, $id]);
    }

    public static function obtenerPrecio(int $servicioId, int $tipoVehiculoId): ?float {
        return PrecioServicio::obtenerPrecioVigente($servicioId, $tipoVehiculoId);
    }
}
