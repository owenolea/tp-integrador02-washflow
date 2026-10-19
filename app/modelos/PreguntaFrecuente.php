<?php
/**
 * WashFlow - Modelo PreguntaFrecuente (FAQ administrables)
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use PDO;
use WashFlow\Core\BaseDatos;

class PreguntaFrecuente {
    public static function listarActivas(): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->query("SELECT * FROM preguntas_frecuentes WHERE activo = 1 ORDER BY orden ASC, id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function listarTodas(): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->query("SELECT * FROM preguntas_frecuentes ORDER BY orden ASC, id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function buscarPorId(int $id): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare("SELECT * FROM preguntas_frecuentes WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public static function crear(string $pregunta, string $respuesta, int $orden = 0): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare("
            INSERT INTO preguntas_frecuentes (pregunta, respuesta, orden, activo)
            VALUES (?, ?, ?, 1)
        ");
        $stmt->execute([trim($pregunta), trim($respuesta), $orden]);
        return (int)$db->lastInsertId();
    }

    public static function actualizar(int $id, string $pregunta, string $respuesta, int $orden, bool $activo): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare("
            UPDATE preguntas_frecuentes
            SET pregunta = ?, respuesta = ?, orden = ?, activo = ?
            WHERE id = ?
        ");
        return $stmt->execute([trim($pregunta), trim($respuesta), $orden, $activo ? 1 : 0, $id]);
    }

    public static function eliminar(int $id): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare("DELETE FROM preguntas_frecuentes WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
