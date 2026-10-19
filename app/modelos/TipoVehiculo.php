<?php
/**
 * WashFlow - Modelo TipoVehiculo (Diagramas 04a, 04b)
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use PDO;
use WashFlow\Core\BaseDatos;

class TipoVehiculo {
    public static function listarTodos(): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->query('SELECT * FROM tipos_vehiculo ORDER BY id ASC');
        return $stmt->fetchAll();
    }

    public static function buscarPorId(int $id): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('SELECT * FROM tipos_vehiculo WHERE id = ?');
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function cambiarDuracion(int $id, int $duracionMinutos): bool {
        if ($duracionMinutos <= 0) {
            return false;
        }
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('UPDATE tipos_vehiculo SET duracion_minutos = ? WHERE id = ?');
        return $stmt->execute([$duracionMinutos, $id]);
    }
}
