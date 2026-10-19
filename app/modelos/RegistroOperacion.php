<?php
/**
 * WashFlow - Modelo RegistroOperacion (Auditoria y Trazabilidad, Diagramas 04b, 12)
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use PDO;
use WashFlow\Core\BaseDatos;

class RegistroOperacion {
    public static function registrar(?int $usuarioId, string $accion, string $entidad, ?int $idEntidad, string $detalle): int {
        try {
            $db = BaseDatos::getInstancia()->getConexion();
            $stmt = $db->prepare('
                INSERT INTO registro_operaciones (usuario_id, accion, entidad, id_entidad, detalle, fecha_hora)
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $usuarioId,
                $accion,
                $entidad,
                $idEntidad,
                $detalle
            ]);
            return (int)$db->lastInsertId();
        } catch (\Throwable $e) {
            // La auditoria no debe interrumpir el flujo si falla
            error_log('Error al guardar auditoria: ' . $e->getMessage());
            return 0;
        }
    }

    public static function listarConFiltro(array $filtros = [], int $limite = 50, int $offset = 0): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $sql = '
            SELECT ro.*, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.rol AS usuario_rol, u.correo AS usuario_correo
            FROM registro_operaciones ro
            LEFT JOIN usuarios u ON ro.usuario_id = u.id
            WHERE 1=1
        ';
        $params = [];

        if (!empty($filtros['accion'])) {
            $sql .= ' AND ro.accion = ?';
            $params[] = $filtros['accion'];
        }
        if (!empty($filtros['entidad'])) {
            $sql .= ' AND ro.entidad = ?';
            $params[] = $filtros['entidad'];
        }
        if (!empty($filtros['usuario_id'])) {
            $sql .= ' AND ro.usuario_id = ?';
            $params[] = (int)$filtros['usuario_id'];
        }
        if (!empty($filtros['fecha_desde'])) {
            $sql .= ' AND DATE(ro.fecha_hora) >= ?';
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= ' AND DATE(ro.fecha_hora) <= ?';
            $params[] = $filtros['fecha_hasta'];
        }

        $sql .= ' ORDER BY ro.fecha_hora DESC, ro.id DESC LIMIT ? OFFSET ?';
        $params[] = $limite;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        foreach ($params as $k => $val) {
            $stmt->bindValue($k + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function contar(array $filtros = []): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $sql = 'SELECT COUNT(*) FROM registro_operaciones ro WHERE 1=1';
        $params = [];

        if (!empty($filtros['accion'])) {
            $sql .= ' AND ro.accion = ?';
            $params[] = $filtros['accion'];
        }
        if (!empty($filtros['entidad'])) {
            $sql .= ' AND ro.entidad = ?';
            $params[] = $filtros['entidad'];
        }
        if (!empty($filtros['usuario_id'])) {
            $sql .= ' AND ro.usuario_id = ?';
            $params[] = (int)$filtros['usuario_id'];
        }
        if (!empty($filtros['fecha_desde'])) {
            $sql .= ' AND DATE(ro.fecha_hora) >= ?';
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= ' AND DATE(ro.fecha_hora) <= ?';
            $params[] = $filtros['fecha_hasta'];
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
