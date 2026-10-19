<?php
/**
 * WashFlow - Modelo Vehiculo (Diagramas 04a, 04b)
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use PDO;
use WashFlow\Core\BaseDatos;

class Vehiculo {
    public static function buscarPorId(int $id): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT v.*, t.nombre AS tipo_nombre, t.duracion_minutos,
                   u.nombre AS cliente_nombre, u.apellido AS cliente_apellido, u.telefono AS cliente_telefono, u.correo AS cliente_correo
            FROM vehiculos v
            JOIN tipos_vehiculo t ON v.tipo_vehiculo_id = t.id
            JOIN usuarios u ON v.cliente_id = u.id
            WHERE v.id = ?
        ');
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function esPatenteValida(?string $patente): bool {
        if ($patente === null || trim($patente) === '') {
            return false;
        }
        $limpia = strtoupper(str_replace([' ', '-'], '', trim($patente)));
        // Formatos oficiales en Argentina:
        // 1. Mercosur autos: 2 letras, 3 numeros, 2 letras (ej. AB123CD) - 7 caracteres
        // 2. Tradicional autos: 3 letras, 3 numeros (ej. ABC123) - 6 caracteres
        // 3. Mercosur motos: 1 letra, 3 numeros, 3 letras (ej. A123BCD) - 7 caracteres
        // 4. Tradicional motos: 3 numeros, 3 letras (ej. 123ABC) - 6 caracteres
        return (bool)preg_match('/^([A-Z]{2}[0-9]{3}[A-Z]{2}|[A-Z]{3}[0-9]{3}|[A-Z]{1}[0-9]{3}[A-Z]{3}|[0-9]{3}[A-Z]{3})$/', $limpia);
    }

    public static function normalizarPatente(?string $patente): ?string {
        if ($patente === null || trim($patente) === '') {
            return null;
        }
        return strtoupper(str_replace([' ', '-'], '', trim($patente)));
    }

    public static function buscarPorPatente(string $patente): ?array {
        $patenteNormalizada = self::normalizarPatente($patente);
        if (!$patenteNormalizada) {
            return null;
        }
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT v.*, t.nombre AS tipo_nombre, t.duracion_minutos,
                   u.nombre AS cliente_nombre, u.apellido AS cliente_apellido, u.telefono AS cliente_telefono, u.correo AS cliente_correo, u.es_ocasional
            FROM vehiculos v
            JOIN tipos_vehiculo t ON v.tipo_vehiculo_id = t.id
            JOIN usuarios u ON v.cliente_id = u.id
            WHERE REPLACE(REPLACE(v.patente, " ", ""), "-", "") = ?
        ');
        $stmt->execute([$patenteNormalizada]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function listarPorCliente(int $clienteId, bool $soloActivos = true): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $sql = '
            SELECT v.*, t.nombre AS tipo_nombre, t.duracion_minutos
            FROM vehiculos v
            JOIN tipos_vehiculo t ON v.tipo_vehiculo_id = t.id
            WHERE v.cliente_id = ?
        ';
        if ($soloActivos) {
            $sql .= ' AND v.activo = 1';
        }
        $sql .= ' ORDER BY v.id DESC';

        $stmt = $db->prepare($sql);
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll();
    }

    public static function crear(array $datos): int {
        $patente = !empty($datos['patente']) ? strtoupper(trim((string)$datos['patente'])) : null;
        $tipoAConfirmar = !empty($datos['tipo_a_confirmar']) ? 1 : 0;
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            INSERT INTO vehiculos (cliente_id, tipo_vehiculo_id, patente, marca, modelo, tipo_a_confirmar, activo, creado_en)
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
        ');
        $stmt->execute([
            $datos['cliente_id'],
            $datos['tipo_vehiculo_id'],
            $patente,
            $datos['marca'],
            $datos['modelo'],
            $tipoAConfirmar
        ]);
        return (int)$db->lastInsertId();
    }

    public static function actualizar(int $id, array $datos): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $patente = !empty($datos['patente']) ? strtoupper(trim((string)$datos['patente'])) : null;
        $tipoAConfirmar = isset($datos['tipo_a_confirmar']) ? (int)$datos['tipo_a_confirmar'] : 0;
        $stmt = $db->prepare('
            UPDATE vehiculos
            SET tipo_vehiculo_id = ?, patente = ?, marca = ?, modelo = ?, tipo_a_confirmar = ?
            WHERE id = ?
        ');
        return $stmt->execute([
            $datos['tipo_vehiculo_id'],
            $patente,
            $datos['marca'],
            $datos['modelo'],
            $tipoAConfirmar,
            $id
        ]);
    }

    public static function desactivar(int $id): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('UPDATE vehiculos SET activo = 0 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function activar(int $id): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('UPDATE vehiculos SET activo = 1 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function obtenerDuracion(int $vehiculoId): int {
        $veh = self::buscarPorId($vehiculoId);
        return $veh ? (int)$veh['duracion_minutos'] : 60;
    }

    public static function confirmarTipo(int $vehiculoId, int $tipoVehiculoId): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('UPDATE vehiculos SET tipo_vehiculo_id = ?, tipo_a_confirmar = 0 WHERE id = ?');
        return $stmt->execute([$tipoVehiculoId, $vehiculoId]);
    }
}
