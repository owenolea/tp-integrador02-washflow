<?php
/**
 * WashFlow - Modelo Lavado (Diagramas 04b, 06, 07, 09)
 * Estados validos: 'pendiente' y 'terminado' (prohibido 'En proceso').
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use PDO;
use RuntimeException;
use WashFlow\Core\BaseDatos;
use WashFlow\Servicios\DisponibilidadServicio;

class Lavado {
    public static function buscarPorId(int $id): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT l.*,
                   v.patente, v.marca, v.modelo, v.tipo_vehiculo_id,
                   tv.nombre AS tipo_nombre, tv.duracion_minutos,
                   s.nombre AS servicio_nombre,
                   u.id AS cliente_id, u.nombre AS cliente_nombre, u.apellido AS cliente_apellido, u.telefono AS cliente_telefono, u.correo AS cliente_correo, u.es_ocasional,
                   reg.nombre AS registrador_nombre, reg.apellido AS registrador_apellido,
                   p.id AS pago_id, p.importe AS pago_importe, p.medio AS pago_medio, p.fecha_hora AS pago_fecha_hora
            FROM lavados l
            JOIN vehiculos v ON l.vehiculo_id = v.id
            JOIN tipos_vehiculo tv ON v.tipo_vehiculo_id = tv.id
            JOIN servicios s ON l.servicio_id = s.id
            JOIN usuarios u ON v.cliente_id = u.id
            JOIN usuarios reg ON l.registrado_por = reg.id
            LEFT JOIN pagos p ON (p.lavado_id = l.id OR (l.reserva_id IS NOT NULL AND p.reserva_id = l.reserva_id))
            WHERE l.id = ?
        ');
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        if ($res) {
            $res['estado_pago'] = ($res['pago_id'] !== null) ? 'pagado' : 'pendiente';
        }
        return $res ?: null;
    }

    public static function buscarPorReservaId(int $reservaId): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('SELECT * FROM lavados WHERE reserva_id = ?');
        $stmt->execute([$reservaId]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Registra la llegada de un cliente con reserva previa (Diagrama 06)
     * Crea un lavado en estado 'pendiente' y hora_llegada = NOW()
     */
    public static function registrarLlegada(int $reservaId, int $empleadoId): int {
        $reserva = Reserva::buscarPorId($reservaId);
        if (!$reserva) {
            throw new RuntimeException('Reserva no encontrada.');
        }

        if ($reserva['estado'] !== 'reservada') {
            throw new RuntimeException('La reserva no se encuentra activa (estado: ' . $reserva['estado'] . ').');
        }

        $existente = self::buscarPorReservaId($reservaId);
        if ($existente) {
            throw new RuntimeException('La llegada para esta reserva ya fue registrada previamente (Lavado #' . $existente['id'] . ').');
        }

        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            INSERT INTO lavados (reserva_id, vehiculo_id, servicio_id, precio, hora_llegada, hora_inicio, hora_fin, fecha, estado, registrado_por, creado_en)
            VALUES (?, ?, ?, ?, CURTIME(), CURTIME(), NULL, CURDATE(), "pendiente", ?, NOW())
        ');
        $stmt->execute([
            $reservaId,
            $reserva['vehiculo_id'],
            $reserva['servicio_id'],
            $reserva['precio_acordado'],
            $empleadoId
        ]);
        $lavadoId = (int)$db->lastInsertId();

        Notificacion::crear(
            (int)$reserva['cliente_id'],
            'Vehiculo ingresado al lavadero',
            "Se registro la llegada de su vehiculo {$reserva['marca']} {$reserva['modelo']} ({$reserva['patente']}). Lavado en preparacion.",
            'interna',
            $reservaId
        );

        RegistroOperacion::registrar(
            $empleadoId,
            'REGISTRAR_LLEGADA',
            'Lavado',
            $lavadoId,
            "Llegada registrada para reserva #{$reservaId} (Lavado #{$lavadoId})."
        );

        return $lavadoId;
    }

    /**
     * Registra un lavado sin reserva previa (walk-in) con control estricto de capacidad (Diagrama 07)
     */
    public static function registrarSinReserva(int $vehiculoId, int $servicioId, int $empleadoId): int {
        $vehiculo = Vehiculo::buscarPorId($vehiculoId);
        if (!$vehiculo) {
            throw new RuntimeException('Vehiculo no encontrado.');
        }

        $tipoId = (int)$vehiculo['tipo_vehiculo_id'];
        $duracion = (int)$vehiculo['duracion_minutos'];

        $precio = PrecioServicio::obtenerPrecioVigente($servicioId, $tipoId);
        if ($precio === null || $precio <= 0) {
            throw new RuntimeException('No hay tarifa configurada para este servicio y tipo de vehiculo.');
        }

        $horaActual = date('H:i:s');
        $fechaActual = date('Y-m-d');

        // Validar disponibilidad transaccional
        BaseDatos::getInstancia()->iniciarTransaccion();

        try {
            $disponible = DisponibilidadServicio::hayDisponibilidad($fechaActual, $horaActual, $duracion, null, true);
            if (!$disponible) {
                throw new RuntimeException('No hay capacidad disponible en este momento (maximo 3 vehiculos simultaneos alcanzado o fuera de horario).');
            }

            $db = BaseDatos::getInstancia()->getConexion();
            $stmt = $db->prepare('
                INSERT INTO lavados (reserva_id, vehiculo_id, servicio_id, precio, hora_llegada, hora_inicio, hora_fin, fecha, estado, registrado_por, creado_en)
                VALUES (NULL, ?, ?, ?, CURTIME(), CURTIME(), NULL, CURDATE(), "pendiente", ?, NOW())
            ');
            $stmt->execute([
                $vehiculoId,
                $servicioId,
                $precio,
                $empleadoId
            ]);
            $lavadoId = (int)$db->lastInsertId();

            RegistroOperacion::registrar(
                $empleadoId,
                'LAVADO_SIN_RESERVA',
                'Lavado',
                $lavadoId,
                "Lavado sin reserva registrado para vehiculo {$vehiculo['patente']} (precio: \${$precio})."
            );

            BaseDatos::getInstancia()->confirmar();
            return $lavadoId;
        } catch (\Throwable $e) {
            BaseDatos::getInstancia()->revertir();
            throw $e;
        }
    }

    /**
     * Marca el lavado como Terminado (Diagramas 06, 09)
     */
    public static function marcarTerminado(int $id, int $empleadoId): bool {
        $lavado = self::buscarPorId($id);
        if (!$lavado) {
            throw new RuntimeException('Lavado no encontrado.');
        }

        if ($lavado['estado'] === 'terminado') {
            return true;
        }

        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            UPDATE lavados
            SET estado = "terminado", hora_fin = CURTIME()
            WHERE id = ?
        ');
        $res = $stmt->execute([$id]);

        if ($res) {
            Notificacion::crear(
                (int)$lavado['cliente_id'],
                'Lavado terminado',
                "Su vehiculo {$lavado['marca']} {$lavado['modelo']} ({$lavado['patente']}) ya se encuentra listo para retirar.",
                'interna',
                $lavado['reserva_id'] ? (int)$lavado['reserva_id'] : null
            );

            RegistroOperacion::registrar(
                $empleadoId,
                'MARCAR_TERMINADO',
                'Lavado',
                $id,
                "Lavado #{$id} marcado como Terminado a las " . date('H:i') . " hs."
            );
        }

        return $res;
    }

    public static function listarPorFecha(string $fecha): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT l.*,
                   v.patente, v.marca, v.modelo,
                   tv.nombre AS tipo_nombre,
                   s.nombre AS servicio_nombre,
                   u.nombre AS cliente_nombre, u.apellido AS cliente_apellido, u.telefono AS cliente_telefono, u.es_ocasional,
                   p.id AS pago_id, p.importe AS pago_importe, p.medio AS pago_medio
            FROM lavados l
            JOIN vehiculos v ON l.vehiculo_id = v.id
            JOIN tipos_vehiculo tv ON v.tipo_vehiculo_id = tv.id
            JOIN servicios s ON l.servicio_id = s.id
            JOIN usuarios u ON v.cliente_id = u.id
            LEFT JOIN pagos p ON (p.lavado_id = l.id OR (l.reserva_id IS NOT NULL AND p.reserva_id = l.reserva_id))
            WHERE l.fecha = ?
            ORDER BY l.hora_llegada DESC
        ');
        $stmt->execute([$fecha]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$r) {
            $r['estado_pago'] = ($r['pago_id'] !== null) ? 'pagado' : 'pendiente';
        }
        return $rows;
    }
}
