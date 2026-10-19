<?php
/**
 * WashFlow - Modelo ModeloVehiculo (Buscador y clasificador inteligente de modelos)
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use PDO;
use WashFlow\Core\BaseDatos;

class ModeloVehiculo {
    /**
     * Busca modelos por texto ignorando mayusculas, minusculas y acentos.
     */
    public static function buscar(string $termino, int $limite = 15): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $termino = trim($termino);
        if ($termino === '') {
            return [];
        }

        $palabras = preg_split('/\s+/', $termino);
        $condiciones = [];
        $parametros = [];

        foreach ($palabras as $idx => $palabra) {
            $pMarca = "pm{$idx}";
            $pModelo = "pmo{$idx}";
            $condiciones[] = "(mv.marca LIKE :{$pMarca} OR mv.modelo LIKE :{$pModelo})";
            $parametros[$pMarca] = '%' . $palabra . '%';
            $parametros[$pModelo] = '%' . $palabra . '%';
        }

        $whereClause = implode(' AND ', $condiciones);

        $sql = "
            SELECT mv.id, mv.marca, mv.modelo, mv.tipo_vehiculo_id,
                   tv.nombre AS tipo_nombre, tv.duracion_minutos
            FROM modelos_vehiculo mv
            INNER JOIN tipos_vehiculo tv ON tv.id = mv.tipo_vehiculo_id
            WHERE mv.activo = 1 AND ({$whereClause})
            ORDER BY 
                CASE 
                    WHEN mv.marca LIKE :exactoMarca THEN 1
                    WHEN mv.modelo LIKE :exactoModelo THEN 2
                    ELSE 3
                END,
                mv.marca ASC, mv.modelo ASC
            LIMIT {$limite}
        ";

        $parametros['exactoMarca'] = $termino . '%';
        $parametros['exactoModelo'] = $termino . '%';

        $stmt = $db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function listarTodos(): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->query("
            SELECT mv.*, tv.nombre AS tipo_nombre
            FROM modelos_vehiculo mv
            INNER JOIN tipos_vehiculo tv ON tv.id = mv.tipo_vehiculo_id
            ORDER BY mv.marca ASC, mv.modelo ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function buscarPorId(int $id): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare("
            SELECT mv.*, tv.nombre AS tipo_nombre
            FROM modelos_vehiculo mv
            INNER JOIN tipos_vehiculo tv ON tv.id = mv.tipo_vehiculo_id
            WHERE mv.id = ?
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public static function crear(string $marca, string $modelo, int $tipoVehiculoId): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare("
            INSERT INTO modelos_vehiculo (marca, modelo, tipo_vehiculo_id, activo)
            VALUES (?, ?, ?, 1)
        ");
        $stmt->execute([trim($marca), trim($modelo), $tipoVehiculoId]);
        return (int)$db->lastInsertId();
    }

    public static function actualizar(int $id, string $marca, string $modelo, int $tipoVehiculoId, bool $activo): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare("
            UPDATE modelos_vehiculo
            SET marca = ?, modelo = ?, tipo_vehiculo_id = ?, activo = ?
            WHERE id = ?
        ");
        return $stmt->execute([trim($marca), trim($modelo), $tipoVehiculoId, $activo ? 1 : 0, $id]);
    }

    public static function eliminar(int $id): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare("DELETE FROM modelos_vehiculo WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function desactivar(int $id): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare("UPDATE modelos_vehiculo SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
