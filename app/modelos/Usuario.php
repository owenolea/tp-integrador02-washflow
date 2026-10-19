<?php
/**
 * WashFlow - Modelo Usuario (Diagramas 04a, 04b)
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use PDO;
use WashFlow\Core\BaseDatos;

class Usuario {
    public static function buscarPorId(int $id): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT u.*, v.nombre AS vinculado_nombre, v.apellido AS vinculado_apellido
            FROM usuarios u
            LEFT JOIN usuarios v ON u.usuario_vinculado_id = v.id
            WHERE u.id = ?
        ');
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function buscarPorCorreo(string $correo): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE correo = ?');
        $stmt->execute([$correo]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function buscarPorTelefono(string $telefono): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE telefono = ? AND activo = 1 ORDER BY id DESC LIMIT 1');
        $stmt->execute([$telefono]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function buscarGeneral(string $criterio): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $like = '%' . $criterio . '%';
        $stmt = $db->prepare('
            SELECT DISTINCT u.*
            FROM usuarios u
            LEFT JOIN vehiculos v ON v.cliente_id = u.id
            WHERE u.nombre LIKE ? OR u.apellido LIKE ? OR u.correo LIKE ? OR u.telefono LIKE ? OR v.patente LIKE ?
            ORDER BY u.apellido, u.nombre
            LIMIT 20
        ');
        $stmt->execute([$like, $like, $like, $like, $like]);
        return $stmt->fetchAll();
    }

    public static function crear(array $datos): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            INSERT INTO usuarios (rol, nombre, apellido, telefono, correo, contrasena_hash, activo, es_ocasional, identidad_verificada, creado_en)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ');
        $stmt->execute([
            $datos['rol'] ?? 'cliente',
            $datos['nombre'],
            $datos['apellido'],
            $datos['telefono'] ?? null,
            $datos['correo'] ?? null,
            $datos['contrasena_hash'] ?? null,
            $datos['activo'] ?? 1,
            $datos['es_ocasional'] ?? 0,
            $datos['identidad_verificada'] ?? 0
        ]);
        return (int)$db->lastInsertId();
    }

    public static function crearClienteOcasional(string $nombre, ?string $telefono): int {
        return self::obtenerOCrearClienteOcasional($nombre, $telefono ?? '');
    }

    public static function obtenerOCrearClienteOcasional(string $nombre, string $telefono, ?string $correo = null): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $telefonoLimpio = trim($telefono);

        // 1. Buscar por correo si se proporciono
        if ($correo !== null && trim($correo) !== '') {
            $u = self::buscarPorCorreo(trim($correo));
            if ($u) {
                return (int)$u['id'];
            }
        }

        // 2. Buscar por telefono
        if ($telefonoLimpio !== '') {
            $u = self::buscarPorTelefono($telefonoLimpio);
            if ($u) {
                return (int)$u['id'];
            }
        }

        // 3. Crear cliente ocasional
        $partes = explode(' ', trim($nombre), 2);
        $nom = $partes[0];
        $ape = $partes[1] ?? 'Ocasional';

        $stmt = $db->prepare('
            INSERT INTO usuarios (rol, nombre, apellido, telefono, correo, contrasena_hash, activo, es_ocasional, identidad_verificada, creado_en)
            VALUES ("cliente", ?, ?, ?, ?, NULL, 1, 1, 0, NOW())
        ');
        $stmt->execute([
            $nom,
            $ape,
            $telefonoLimpio !== '' ? $telefonoLimpio : null,
            ($correo && trim($correo) !== '') ? trim($correo) : null
        ]);
        return (int)$db->lastInsertId();
    }

    public static function actualizar(int $id, array $datos): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $campos = [];
        $params = [];

        foreach ($datos as $columna => $valor) {
            $campos[] = "{$columna} = ?";
            $params[] = $valor;
        }

        if (empty($campos)) {
            return false;
        }

        $params[] = $id;
        $sql = 'UPDATE usuarios SET ' . implode(', ', $campos) . ' WHERE id = ?';
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function desactivar(int $id): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('UPDATE usuarios SET activo = 0 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function activar(int $id): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('UPDATE usuarios SET activo = 1 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function vincularOcasionalACuenta(int $ocasionalId, int $cuentaDestinoId): bool {
        $db = BaseDatos::getInstancia()->getConexion();
        BaseDatos::getInstancia()->iniciarTransaccion();

        try {
            // Transferir vehiculos del cliente ocasional al cliente con cuenta
            $stmtVeh = $db->prepare('UPDATE vehiculos SET cliente_id = ? WHERE cliente_id = ?');
            $stmtVeh->execute([$cuentaDestinoId, $ocasionalId]);

            // Transferir reservas creadas
            $stmtRes = $db->prepare('UPDATE reservas SET creada_por = ? WHERE creada_por = ?');
            $stmtRes->execute([$cuentaDestinoId, $ocasionalId]);

            // Marcar usuario ocasional como vinculado y desactivarlo
            $stmtU = $db->prepare('
                UPDATE usuarios
                SET usuario_vinculado_id = ?, activo = 0, identidad_verificada = 1
                WHERE id = ?
            ');
            $stmtU->execute([$cuentaDestinoId, $ocasionalId]);

            BaseDatos::getInstancia()->confirmar();
            return true;
        } catch (\Throwable $e) {
            BaseDatos::getInstancia()->revertir();
            throw $e;
        }
    }

    public static function incrementarIntentosFallidos(int $id): void {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('UPDATE usuarios SET intentos_fallidos = intentos_fallidos + 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function reiniciarIntentosFallidos(int $id): void {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function bloquearTemporalmente(int $id, int $minutos = 15): void {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            UPDATE usuarios
            SET bloqueado_hasta = DATE_ADD(NOW(), INTERVAL ? MINUTE)
            WHERE id = ?
        ');
        $stmt->execute([$minutos, $id]);
    }

    public static function estaBloqueado(array $usuario): bool {
        if (!empty($usuario['bloqueado_hasta'])) {
            $ahora = new \DateTime();
            $bloqueo = new \DateTime($usuario['bloqueado_hasta']);
            return $bloqueo > $ahora;
        }
        return false;
    }

    public static function listar(string $rol = '', string $busqueda = '', int $limite = 50, int $offset = 0): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $sql = 'SELECT * FROM usuarios WHERE 1=1';
        $params = [];

        if ($rol !== '') {
            $sql .= ' AND rol = ?';
            $params[] = $rol;
        }

        if ($busqueda !== '') {
            $sql .= ' AND (nombre LIKE ? OR apellido LIKE ? OR correo LIKE ? OR telefono LIKE ?)';
            $like = '%' . $busqueda . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' ORDER BY id DESC LIMIT ? OFFSET ?';
        $params[] = $limite;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        // PDO bindValue para enteros
        foreach ($params as $k => $val) {
            $stmt->bindValue($k + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function contar(string $rol = '', string $busqueda = ''): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $sql = 'SELECT COUNT(*) FROM usuarios WHERE 1=1';
        $params = [];

        if ($rol !== '') {
            $sql .= ' AND rol = ?';
            $params[] = $rol;
        }

        if ($busqueda !== '') {
            $sql .= ' AND (nombre LIKE ? OR apellido LIKE ? OR correo LIKE ? OR telefono LIKE ?)';
            $like = '%' . $busqueda . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}

/**
 * Especializaciones de Usuario segun Diagramas UML (01, 04a)
 */
class Cliente extends Usuario {}
class Empleado extends Usuario {}
class Administrador extends Empleado {}

