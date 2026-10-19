<?php
/**
 * WashFlow - Servicio de Calculo de Disponibilidad y Capacidad (Diagramas 05, 07, 10)
 * Reglas de negocio:
 * - Lun a Sab, 10:00 a 18:00. Domingos cerrado.
 * - Ninguna reserva puede terminar despues de las 18:00. Franjas cada 30 minutos.
 * - Capacidad maxima: 3 vehiculos simultaneos en cualquier franja del intervalo pedido.
 */

declare(strict_types=1);

namespace WashFlow\Servicios;

use DateTime;
use PDO;
use WashFlow\Core\BaseDatos;

class DisponibilidadServicio {
    public const HORA_APERTURA = '10:00:00';
    public const HORA_CIERRE = '18:00:00';
    public const CAPACIDAD_MAXIMA = 3;
    public const INTERVALO_FRANJA_MINUTOS = 30;

    /**
     * Valida si un dia de la semana esta abierto (1=Lunes, 6=Sabado. 0=Domingo cerrado).
     */
    public static function esDiaAtencion(string $fecha): bool {
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$dt) {
            return false;
        }
        $diaSemana = (int)$dt->format('w'); // 0=Domingo, 6=Sabado
        return $diaSemana >= 1 && $diaSemana <= 6;
    }

    /**
     * Calcula la hora de finalizacion dada una hora de inicio y duracion en minutos.
     */
    public static function calcularHoraFin(string $horaInicio, int $duracionMinutos): string {
        $dt = DateTime::createFromFormat('H:i:s', strlen($horaInicio) === 5 ? $horaInicio . ':00' : $horaInicio);
        if (!$dt) {
            $dt = DateTime::createFromFormat('H:i', $horaInicio);
        }
        $dt->modify("+{$duracionMinutos} minutes");
        return $dt->format('H:i:s');
    }

    /**
     * Verifica disponibilidad exacta considerando reservas, lavados sin reserva y bloqueos.
     * Soporta bloqueo pesimista (FOR UPDATE) para proteger concurrencia dentro de transacciones.
     */
    public static function hayDisponibilidad(
        string $fecha,
        string $horaInicio,
        int $duracionMinutos,
        ?int $excluirReservaId = null,
        bool $conLock = false
    ): bool {
        // Normalizar formato de hora
        if (strlen($horaInicio) === 5) {
            $horaInicio .= ':00';
        }

        // 1. Validar atencion en lunes a sabado
        if (!self::esDiaAtencion($fecha)) {
            return false;
        }

        // 2. Validar que no sea fecha pasada
        $hoy = date('Y-m-d');
        if ($fecha < $hoy) {
            return false;
        }

        // Si es hoy, verificar que la hora inicio sea posterior a la hora actual
        if ($fecha === $hoy) {
            $ahora = date('H:i:s');
            if ($horaInicio <= $ahora) {
                return false;
            }
        }

        // 3. Validar limites de horario
        $horaFin = self::calcularHoraFin($horaInicio, $duracionMinutos);
        if ($horaInicio < self::HORA_APERTURA || $horaFin > self::HORA_CIERRE) {
            return false;
        }

        // 4. Evaluar la capacidad para cada franja de 30 minutos dentro del rango solicitado
        $cursor = DateTime::createFromFormat('H:i:s', $horaInicio);
        $finObj = DateTime::createFromFormat('H:i:s', $horaFin);

        while ($cursor < $finObj) {
            $slotInicio = $cursor->format('H:i:s');
            $slotFinObj = clone $cursor;
            $slotFinObj->modify('+' . self::INTERVALO_FRANJA_MINUTOS . ' minutes');
            $slotFin = $slotFinObj->format('H:i:s');

            $ocupacion = self::contarOcupacionEnTramo($fecha, $slotInicio, $slotFin, $excluirReservaId, $conLock);

            if ($ocupacion >= self::CAPACIDAD_MAXIMA) {
                return false; // Al menos un tramo alcanzo o supero la capacidad maxima
            }

            $cursor->modify('+' . self::INTERVALO_FRANJA_MINUTOS . ' minutes');
        }

        return true;
    }

    /**
     * Cuenta la cantidad de vehiculos simultaneos en un tramo [tramoInicio, tramoFin]:
     * - Reservas con estado 'reservada'
     * - Lavados sin reserva en curso (estimados segun duracion del tipo de vehiculo)
     * - Bloqueos de horario (cada bloqueo cuenta como indisponibilidad o reduce cupo)
     */
    public static function contarOcupacionEnTramo(
        string $fecha,
        string $tramoInicio,
        string $tramoFin,
        ?int $excluirReservaId = null,
        bool $conLock = false
    ): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $lockSql = $conLock ? ' FOR UPDATE' : '';

        // A. Reservas activas que se solapan con el tramo
        $sqlRes = '
            SELECT COUNT(*)
            FROM reservas
            WHERE fecha = ?
              AND estado = "reservada"
              AND hora_inicio < ?
              AND hora_fin > ?
        ';
        $paramsRes = [$fecha, $tramoFin, $tramoInicio];
        if ($excluirReservaId !== null) {
            $sqlRes .= ' AND id != ?';
            $paramsRes[] = $excluirReservaId;
        }
        $sqlRes .= $lockSql;

        $stmtRes = $db->prepare($sqlRes);
        $stmtRes->execute($paramsRes);
        $conteoReservas = (int)$stmtRes->fetchColumn();

        // B. Lavados sin reserva (reserva_id IS NULL) que se solapan en esa fecha
        // Se calcula el fin estimado con la duracion del tipo de vehiculo si hora_fin es null
        $sqlLav = '
            SELECT COUNT(*)
            FROM lavados l
            JOIN vehiculos v ON l.vehiculo_id = v.id
            JOIN tipos_vehiculo tv ON v.tipo_vehiculo_id = tv.id
            WHERE l.fecha = ?
              AND l.reserva_id IS NULL
              AND l.hora_llegada < ?
              AND COALESCE(l.hora_fin, ADDTIME(l.hora_llegada, SEC_TO_TIME(tv.duracion_minutos * 60))) > ?
        ' . $lockSql;

        $stmtLav = $db->prepare($sqlLav);
        $stmtLav->execute([$fecha, $tramoFin, $tramoInicio]);
        $conteoLavados = (int)$stmtLav->fetchColumn();

        // C. Bloqueos de horario que afecten el tramo
        $sqlBloq = '
            SELECT COUNT(*)
            FROM bloqueos_horario
            WHERE fecha = ?
              AND hora_inicio < ?
              AND hora_fin > ?
        ' . $lockSql;

        $stmtBloq = $db->prepare($sqlBloq);
        $stmtBloq->execute([$fecha, $tramoFin, $tramoInicio]);
        $conteoBloqueos = (int)$stmtBloq->fetchColumn();

        // Si hay bloqueo en este tramo, computa como capacidad ocupada completa para inhabilitarlo
        if ($conteoBloqueos > 0) {
            return self::CAPACIDAD_MAXIMA;
        }

        return $conteoReservas + $conteoLavados;
    }

    /**
     * Genera la lista de todas las franjas horarias con su ocupacion y disponibilidad
     * para una fecha y duracion dadas (para calendario y asistente de turnos).
     */
    public static function obtenerMapaDisponibilidad(string $fecha, int $duracionMinutos = 60, ?int $excluirReservaId = null): array {
        $resultado = [];

        if (!self::esDiaAtencion($fecha)) {
            return [
                'es_dia_atencion' => false,
                'mensaje' => 'El lavadero permanece cerrado los domingos.',
                'franjas' => []
            ];
        }

        $apertura = DateTime::createFromFormat('H:i:s', self::HORA_APERTURA);
        $cierre = DateTime::createFromFormat('H:i:s', self::HORA_CIERRE);
        $hoy = date('Y-m-d');
        $ahora = date('H:i:s');

        while ($apertura < $cierre) {
            $horaInicio = $apertura->format('H:i:s');
            $horaFin = self::calcularHoraFin($horaInicio, $duracionMinutos);

            // Si la reserva completa excede las 18:00, no es una franja valida de inicio
            if ($horaFin > self::HORA_CIERRE) {
                break;
            }

            $pasada = ($fecha < $hoy) || ($fecha === $hoy && $horaInicio <= $ahora);

            // Calcular ocupacion maxima en cualquiera de sus sub-tramos
            $ocupacionMax = 0;
            $subCursor = clone $apertura;
            $subFin = DateTime::createFromFormat('H:i:s', $horaFin);

            while ($subCursor < $subFin) {
                $subTramoInicio = $subCursor->format('H:i:s');
                $subTramoFinObj = clone $subCursor;
                $subTramoFinObj->modify('+' . self::INTERVALO_FRANJA_MINUTOS . ' minutes');
                $subTramoFin = $subTramoFinObj->format('H:i:s');

                $oc = self::contarOcupacionEnTramo($fecha, $subTramoInicio, $subTramoFin, $excluirReservaId, false);
                if ($oc > $ocupacionMax) {
                    $ocupacionMax = $oc;
                }

                $subCursor->modify('+' . self::INTERVALO_FRANJA_MINUTOS . ' minutes');
            }

            $disponible = !$pasada && ($ocupacionMax < self::CAPACIDAD_MAXIMA);

            $motivo = '';
            if ($pasada) {
                $motivo = 'Horario anterior a la hora actual';
            } elseif ($ocupacionMax >= self::CAPACIDAD_MAXIMA) {
                $motivo = 'Capacidad completa (3 de 3 lugares ocupados o bloqueado)';
            }

            $resultado[] = [
                'hora' => substr($horaInicio, 0, 5),
                'hora_inicio' => substr($horaInicio, 0, 5),
                'hora_fin' => substr($horaFin, 0, 5),
                'ocupacion' => min($ocupacionMax, self::CAPACIDAD_MAXIMA),
                'capacidad_maxima' => self::CAPACIDAD_MAXIMA,
                'lugares_libres' => max(0, self::CAPACIDAD_MAXIMA - $ocupacionMax),
                'disponible' => $disponible,
                'motivo' => $motivo
            ];

            $apertura->modify('+' . self::INTERVALO_FRANJA_MINUTOS . ' minutes');
        }

        return [
            'es_dia_atencion' => true,
            'fecha' => $fecha,
            'duracion_minutos' => $duracionMinutos,
            'franjas' => $resultado
        ];
    }
}
