<?php
/**
 * WashFlow - Capa de conexion a Base de Datos con PDO (Singleton)
 */

declare(strict_types=1);

namespace WashFlow\Core;

use PDO;
use PDOException;
use RuntimeException;

class BaseDatos {
    private static ?BaseDatos $instancia = null;
    private PDO $conexion;

    private function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $db = $config['db'];

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $db['host'],
            $db['puerto'],
            $db['nombre'],
            $db['charset']
        );

        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$db['charset']} COLLATE {$db['charset']}_unicode_ci, time_zone = '-03:00'"
        ];

        try {
            $this->conexion = new PDO($dsn, $db['usuario'], $db['contrasena'], $opciones);
        } catch (PDOException $e) {
            throw new RuntimeException('Error de conexion a la base de datos: ' . $e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstancia(): BaseDatos {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public function getConexion(): PDO {
        return $this->conexion;
    }

    public function iniciarTransaccion(): bool {
        if (!$this->conexion->inTransaction()) {
            return $this->conexion->beginTransaction();
        }
        return true;
    }

    public function confirmar(): bool {
        if ($this->conexion->inTransaction()) {
            return $this->conexion->commit();
        }
        return false;
    }

    public function revertir(): bool {
        if ($this->conexion->inTransaction()) {
            return $this->conexion->rollBack();
        }
        return false;
    }

    // Evitar clonacion y deserializacion
    private function __clone() {}
    public function __wakeup() {
        throw new RuntimeException('No se permite deserializar el Singleton de Base de Datos.');
    }
}
