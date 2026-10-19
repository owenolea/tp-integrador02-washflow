<?php
/**
 * WashFlow - Modelo SolicitudReserva (Diagramas 02, 04a, 04b, 09)
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use PDO;
use RuntimeException;
use WashFlow\Core\BaseDatos;

class SolicitudReserva {
    public static function buscarPorId(int $id): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT sr.*,
                   r.fecha AS reserva_fecha, r.hora_inicio AS reserva_hora_inicio, r.hora_fin AS reserva_hora_fin,
                   r.precio_acordado, r.estado AS reserva_estado,
                   v.patente, v.marca, v.modelo,
                   tv.nombre AS tipo_nombre,
                   s.nombre AS servicio_nombre,
                   u.id AS cliente_id, u.nombre AS cliente_nombre, u.apellido AS cliente_apellido, u.telefono AS cliente_telefono, u.correo AS cliente_correo,
                   res.nombre AS resolutor_nombre, res.apellido AS resolutor_apellido
            FROM solicitudes_reserva sr
            JOIN reservas r ON sr.reserva_id = r.id
            JOIN vehiculos v ON r.vehiculo_id = v.id
            JOIN tipos_vehiculo tv ON v.tipo_vehiculo_id = tv.id
            JOIN servicios s ON r.servicio_id = s.id
            JOIN usuarios u ON v.cliente_id = u.id
            LEFT JOIN usuarios res ON sr.resuelta_por = res.id
            WHERE sr.id = ?
        ');
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function crear(int $reservaId, string $tipo, ?string $nuevaFecha, ?string $nuevaHora, string $motivo): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            INSERT INTO solicitudes_reserva (reserva_id, tipo, nueva_fecha, nueva_hora, motivo, estado, fecha_solicitud)
            VALUES (?, ?, ?, ?, ?, "pendiente", NOW())
        ');
        $stmt->execute([
            $reservaId,
            $tipo,
            $nuevaFecha ?: null,
            $nuevaHora ?: null,
            $motivo
        ]);
        $nuevoId = (int)$db->lastInsertId();

        $reserva = Reserva::buscarPorId($reservaId);
        if ($reserva) {
            RegistroOperacion::registrar(
                (int)$reserva['cliente_id'],
                'SOLICITAR_' . strtoupper($tipo),
                'SolicitudReserva',
                $nuevoId,
                "Solicitud de {$tipo} creada fuera de plazo para reserva #{$reservaId}."
            );
        }

        return $nuevoId;
    }

    public static function listarPendientes(): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->query('
            SELECT sr.*,
                   r.fecha AS reserva_fecha, r.hora_inicio AS reserva_hora_inicio, r.hora_fin AS reserva_hora_fin,
                   v.patente, v.marca, v.modelo,
                   s.nombre AS servicio_nombre,
                   u.nombre AS cliente_nombre, u.apellido AS cliente_apellido, u.telefono AS cliente_telefono
            FROM solicitudes_reserva sr
            JOIN reservas r ON sr.reserva_id = r.id
            JOIN vehiculos v ON r.vehiculo_id = v.id
            JOIN servicios s ON r.servicio_id = s.id
            JOIN usuarios u ON v.cliente_id = u.id
            WHERE sr.estado = "pendiente"
            ORDER BY sr.fecha_solicitud ASC
        ');
        return $stmt->fetchAll();
    }

    public static function listarPorCliente(int $clienteId): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT sr.*,
                   r.fecha AS reserva_fecha, r.hora_inicio AS reserva_hora_inicio,
                   v.patente, v.marca, v.modelo,
                   s.nombre AS servicio_nombre
            FROM solicitudes_reserva sr
            JOIN reservas r ON sr.reserva_id = r.id
            JOIN vehiculos v ON r.vehiculo_id = v.id
            JOIN servicios s ON r.servicio_id = s.id
            WHERE v.cliente_id = ?
            ORDER BY sr.fecha_solicitud DESC
        ');
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll();
    }

    public static function aprobar(int $id, int $resueltaPor, string $observacion = ''): bool {
        $solicitud = self::buscarPorId($id);
        if (!$solicitud) {
            throw new RuntimeException('Solicitud no encontrada.');
        }

        if ($solicitud['estado'] !== 'pendiente') {
            throw new RuntimeException('La solicitud ya fue resuelta previamente.');
        }

        $db = BaseDatos::getInstancia()->getConexion();
        BaseDatos::getInstancia()->iniciarTransaccion();

        try {
            if ($solicitud['tipo'] === 'cancelacion') {
                Reserva::cancelar((int)$solicitud['reserva_id'], $resueltaPor, 'Aprobada solicitud de cancelacion: ' . $observacion);
            } elseif ($solicitud['tipo'] === 'cambio') {
                Reserva::reprogramar(
                    (int)$solicitud['reserva_id'],
                    $solicitud['nueva_fecha'],
                    $solicitud['nueva_hora'],
                    $resueltaPor
                );
            }

            $stmt = $db->prepare('
                UPDATE solicitudes_reserva
                SET estado = "aprobada", resuelta_por = ?, fecha_resolucion = NOW(), observacion_resolucion = ?
                WHERE id = ?
            ');
            $stmt->execute([$resueltaPor, $observacion, $id]);

            Notificacion::crear(
                (int)$solicitud['cliente_id'],
                'Solicitud aprobada',
                "Su solicitud de {$solicitud['tipo']} para la reserva #{$solicitud['reserva_id']} fue aprobada." . ($observacion ? " Detalle: {$observacion}" : ""),
                'interna',
                (int)$solicitud['reserva_id']
            );

            RegistroOperacion::registrar(
                $resueltaPor,
                'APROBAR_SOLICITUD',
                'SolicitudReserva',
                $id,
                "Solicitud #{$id} de tipo {$solicitud['tipo']} aprobada."
            );

            BaseDatos::getInstancia()->confirmar();
            return true;
        } catch (\Throwable $e) {
            BaseDatos::getInstancia()->revertir();
            throw $e;
        }
    }

    public static function rechazar(int $id, int $resueltaPor, string $motivoRechazo): bool {
        $solicitud = self::buscarPorId($id);
        if (!$solicitud) {
            throw new RuntimeException('Solicitud no encontrada.');
        }

        if ($solicitud['estado'] !== 'pendiente') {
            throw new RuntimeException('La solicitud ya fue resuelta previamente.');
        }

        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            UPDATE solicitudes_reserva
            SET estado = "rechazada", resuelta_por = ?, fecha_resolucion = NOW(), observacion_resolucion = ?
            WHERE id = ?
        ');
        $res = $stmt->execute([$resueltaPor, $motivoRechazo, $id]);

        if ($res) {
            Notificacion::crear(
                (int)$solicitud['cliente_id'],
                'Solicitud rechazada',
                "Su solicitud de {$solicitud['tipo']} para la reserva #{$solicitud['reserva_id']} fue rechazada. Motivo: {$motivoRechazo}",
                'interna',
                (int)$solicitud['reserva_id']
            );

            RegistroOperacion::registrar(
                $resueltaPor,
                'RECHAZAR_SOLICITUD',
                'SolicitudReserva',
                $id,
                "Solicitud #{$id} rechazada. Motivo: {$motivoRechazo}"
            );
        }

        return $res;
    }
}
