<?php
/**
 * WashFlow - Modelo Galeria (Fotos y trabajos administrables)
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use PDO;
use WashFlow\Core\BaseDatos;

class Galeria {
    public static function listarActivas(): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->query("SELECT * FROM galeria WHERE activo = 1 ORDER BY orden ASC, id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function listarTodas(): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->query("SELECT * FROM galeria ORDER BY orden ASC, id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function buscarPorId(int $id): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare("SELECT * FROM galeria WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public static function crear(string $titulo, ?string $descripcion, string $imagenUrl, string $categoria = 'trabajos', int $orden = 0): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare("
            INSERT INTO galeria (titulo, descripcion, imagen_url, categoria, orden, activo)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([trim($titulo), $descripcion ? trim($descripcion) : null, trim($imagenUrl), trim($categoria), $orden]);
        return (int)$db->lastInsertId();
    }

    public static function actualizar(int $id, string $titulo, ?string $descripcion, string $imagenUrl, string $categoria, int $orden, bool $activo): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare("
            UPDATE galeria
            SET titulo = ?, descripcion = ?, imagen_url = ?, categoria = ?, orden = ?, activo = ?
            WHERE id = ?
        ");
        return $stmt->execute([trim($titulo), $descripcion ? trim($descripcion) : null, trim($imagenUrl), trim($categoria), $orden, $activo ? 1 : 0, $id]);
    }

    public static function eliminar(int $id): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare("DELETE FROM galeria WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

/**
 * Entidad de galeria de trabajos segun especificacion UML (Diagrama 04b)
 */
class ImagenGaleria extends Galeria {}

