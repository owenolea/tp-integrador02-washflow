<?php
/**
 * WashFlow - Modelo BloqueoHorario (Diagramas 04a, 04b)
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use PDO;
use WashFlow\Core\BaseDatos;

class BloqueoHorario {
    public static function listarPorFecha(string $fecha): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT b.*, u.nombre AS creador_nombre, u.apellido AS creador_apellido
            FROM bloqueos_horario b
            JOIN usuarios u ON b.creado_por = u.id
            WHERE b.fecha = ?
            ORDER BY b.hora_inicio ASC
        ');
        $stmt->execute([$fecha]);
        return $stmt->fetchAll();
    }

    public static function listarFuturos(): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->query('
            SELECT b.*, u.nombre AS creador_nombre, u.apellido AS creador_apellido
            FROM bloqueos_horario b
            JOIN usuarios u ON b.creado_por = u.id
            WHERE b.fecha >= CURDATE()
            ORDER BY b.fecha ASC, b.hora_inicio ASC
        ');
        return $stmt->fetchAll();
    }

    public static function crear(array $datos): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            INSERT INTO bloqueos_horario (fecha, hora_inicio, hora_fin, motivo, creado_por, creado_en)
            VALUES (?, ?, ?, ?, ?, NOW())
        ');
        $stmt->execute([
            $datos['fecha'],
            $datos['hora_inicio'],
            $datos['hora_fin'],
            $datos['motivo'],
            $datos['creado_por']
        ]);
        return (int)$db->lastInsertId();
    }

    public static function eliminar(int $id): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('DELETE FROM bloqueos_horario WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function afectaA(string $fecha, string $horaInicio, string $horaFin): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        // Hay solapamiento si: bloqueo.hora_inicio < horaFin AND bloqueo.hora_fin > horaInicio
        $stmt = $db->prepare('
            SELECT COUNT(*)
            FROM bloqueos_horario
            WHERE fecha = ?
              AND hora_inicio < ?
              AND hora_fin > ?
        ');
        $stmt->execute([$fecha, $horaFin, $horaInicio]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
