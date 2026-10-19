<?php
/**
 * WashFlow - Modelo Reserva (Diagramas 04a, 04b, 05, 09, 10)
 */

declare(strict_types=1);

namespace WashFlow\Modelos;

use DateTime;
use PDO;
use RuntimeException;
use WashFlow\Core\BaseDatos;
use WashFlow\Servicios\DisponibilidadServicio;

class Reserva {
    public static function generarCodigo(): string {
        $db = BaseDatos::getInstancia()->getConexion();
        do {
            $codigo = 'WF-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $stmt = $db->prepare('SELECT COUNT(*) FROM reservas WHERE codigo = ?');
            $stmt->execute([$codigo]);
            $existe = (int)$stmt->fetchColumn() > 0;
        } while ($existe);
        return $codigo;
    }

    public static function buscarPorId(int $id): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT r.*,
                   v.patente, v.marca, v.modelo, v.tipo_vehiculo_id, v.tipo_a_confirmar,
                   tv.nombre AS tipo_nombre, tv.duracion_minutos AS tipo_duracion,
                   s.nombre AS servicio_nombre, s.descripcion AS servicio_descripcion,
                   u.id AS cliente_id, u.nombre AS cliente_nombre, u.apellido AS cliente_apellido,
                   u.telefono AS cliente_telefono, u.correo AS cliente_correo, u.es_ocasional,
                   p.id AS pago_id, p.importe AS pago_importe, p.medio AS pago_medio, p.fecha_hora AS pago_fecha_hora,
                   l.id AS lavado_id, l.estado AS lavado_estado
            FROM reservas r
            JOIN vehiculos v ON r.vehiculo_id = v.id
            JOIN tipos_vehiculo tv ON v.tipo_vehiculo_id = tv.id
            JOIN servicios s ON r.servicio_id = s.id
            JOIN usuarios u ON v.cliente_id = u.id
            LEFT JOIN pagos p ON p.reserva_id = r.id
            LEFT JOIN lavados l ON l.reserva_id = r.id
            WHERE r.id = ?
        ');
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        if ($res) {
            $res['estado_pago'] = ($res['pago_id'] !== null) ? 'pagado' : 'pendiente';
            $res['permite_cambio_directo'] = self::calcularPermiteCambioDirecto($res['fecha'], $res['hora_inicio']);
        }
        return $res ?: null;
    }

    public static function buscarPorCodigoYToken(string $codigo, string $token): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT r.*,
                   v.patente, v.marca, v.modelo, v.tipo_vehiculo_id, v.tipo_a_confirmar,
                   tv.nombre AS tipo_nombre, tv.duracion_minutos AS tipo_duracion,
                   s.nombre AS servicio_nombre, s.descripcion AS servicio_descripcion,
                   u.id AS cliente_id, u.nombre AS cliente_nombre, u.apellido AS cliente_apellido,
                   u.telefono AS cliente_telefono, u.correo AS cliente_correo, u.es_ocasional,
                   p.id AS pago_id, p.importe AS pago_importe, p.medio AS pago_medio, p.fecha_hora AS pago_fecha_hora,
                   l.id AS lavado_id, l.estado AS lavado_estado
            FROM reservas r
            JOIN vehiculos v ON r.vehiculo_id = v.id
            JOIN tipos_vehiculo tv ON v.tipo_vehiculo_id = tv.id
            JOIN servicios s ON r.servicio_id = s.id
            JOIN usuarios u ON v.cliente_id = u.id
            LEFT JOIN pagos p ON p.reserva_id = r.id
            LEFT JOIN lavados l ON l.reserva_id = r.id
            WHERE UPPER(r.codigo) = UPPER(?) AND r.token_publico = ?
        ');
        $stmt->execute([trim($codigo), trim($token)]);
        $res = $stmt->fetch();
        if ($res) {
            // Validar expiracion de token publico (Diagrama 04a y 13)
            if (!empty($res['token_vence']) && !self::tokenVigente($res['token_vence'])) {
                return null;
            }
            $res['estado_pago'] = ($res['pago_id'] !== null) ? 'pagado' : 'pendiente';
            $res['permite_cambio_directo'] = self::calcularPermiteCambioDirecto($res['fecha'], $res['hora_inicio']);
        }
        return $res ?: null;
    }

    public static function tokenVigente(?string $tokenVence): bool {
        if ($tokenVence === null || $tokenVence === '') {
            return false;
        }
        return strtotime($tokenVence) > time();
    }

    public static function permiteCambioDirecto(string $fecha, string $horaInicio): bool {
        return self::calcularPermiteCambioDirecto($fecha, $horaInicio);
    }

    public static function calcularHoraFin(string $horaInicio, int $duracionMinutos): string {
        return DisponibilidadServicio::calcularHoraFin($horaInicio, $duracionMinutos);
    }

    public static function buscarPorCodigoYTelefono(string $codigo, string $telefono): ?array {
        $db = BaseDatos::getInstancia()->getConexion();
        $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);
        $stmt = $db->prepare('
            SELECT r.*,
                   v.patente, v.marca, v.modelo, v.tipo_vehiculo_id, v.tipo_a_confirmar,
                   tv.nombre AS tipo_nombre, tv.duracion_minutos AS tipo_duracion,
                   s.nombre AS servicio_nombre, s.descripcion AS servicio_descripcion,
                   u.id AS cliente_id, u.nombre AS cliente_nombre, u.apellido AS cliente_apellido,
                   u.telefono AS cliente_telefono, u.correo AS cliente_correo, u.es_ocasional,
                   p.id AS pago_id, p.importe AS pago_importe, p.medio AS pago_medio, p.fecha_hora AS pago_fecha_hora,
                   l.id AS lavado_id, l.estado AS lavado_estado
            FROM reservas r
            JOIN vehiculos v ON r.vehiculo_id = v.id
            JOIN tipos_vehiculo tv ON v.tipo_vehiculo_id = tv.id
            JOIN servicios s ON r.servicio_id = s.id
            JOIN usuarios u ON v.cliente_id = u.id
            LEFT JOIN pagos p ON p.reserva_id = r.id
            LEFT JOIN lavados l ON l.reserva_id = r.id
            WHERE UPPER(r.codigo) = UPPER(?)
              AND (
                  REPLACE(REPLACE(REPLACE(REPLACE(r.contacto_telefono, " ", ""), "-", ""), "(", ""), ")", "") LIKE ?
                  OR REPLACE(REPLACE(REPLACE(REPLACE(u.telefono, " ", ""), "-", ""), "(", ""), ")", "") LIKE ?
              )
            ORDER BY r.id DESC
            LIMIT 1
        ');
        $stmt->execute([trim($codigo), '%' . $telefonoLimpio . '%', '%' . $telefonoLimpio . '%']);
        $res = $stmt->fetch();
        if ($res) {
            $res['estado_pago'] = ($res['pago_id'] !== null) ? 'pagado' : 'pendiente';
            $res['permite_cambio_directo'] = self::calcularPermiteCambioDirecto($res['fecha'], $res['hora_inicio']);
        }
        return $res ?: null;
    }

    public static function contarActivasPorTelefono(string $telefono): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);
        if (strlen($telefonoLimpio) < 6) {
            return 0;
        }
        $stmt = $db->prepare('
            SELECT COUNT(*)
            FROM reservas r
            JOIN vehiculos v ON r.vehiculo_id = v.id
            JOIN usuarios u ON v.cliente_id = u.id
            WHERE r.estado = "reservada"
              AND r.fecha >= CURDATE()
              AND (
                  REPLACE(REPLACE(REPLACE(REPLACE(r.contacto_telefono, " ", ""), "-", ""), "(", ""), ")", "") LIKE ?
                  OR REPLACE(REPLACE(REPLACE(REPLACE(u.telefono, " ", ""), "-", ""), "(", ""), ")", "") LIKE ?
              )
        ');
        $stmt->execute(['%' . $telefonoLimpio . '%', '%' . $telefonoLimpio . '%']);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Regla de negocio de 2 horas (Diagramas 02, 04a, 09):
     * Retorna true si faltan 2 horas o mas para el turno; false si faltan menos de 2 horas o ya paso.
     */
    public static function calcularPermiteCambioDirecto(string $fecha, string $horaInicio): bool {
        $fechaTurno = DateTime::createFromFormat('Y-m-d H:i:s', $fecha . ' ' . $horaInicio);
        if (!$fechaTurno) {
            $fechaTurno = DateTime::createFromFormat('Y-m-d H:i', $fecha . ' ' . $horaInicio);
        }
        $ahora = new DateTime();
        $diferenciaSegundos = $fechaTurno->getTimestamp() - $ahora->getTimestamp();
        return $diferenciaSegundos >= 7200; // 2 horas = 7200 segundos
    }

    /**
     * Crea una reserva congelando precio y duracion con proteccion contra concurrencia.
     */
    public static function crear(int $vehiculoId, int $servicioId, string $fecha, string $horaInicio, int $creadaPor): int {
        $db = BaseDatos::getInstancia()->getConexion();
        BaseDatos::getInstancia()->iniciarTransaccion();

        try {
            // 1. Obtener datos del vehiculo y tipo
            $vehiculo = Vehiculo::buscarPorId($vehiculoId);
            if (!$vehiculo) {
                throw new RuntimeException('Vehiculo no encontrado.');
            }
            $duracion = (int)$vehiculo['duracion_minutos'];

            // 2. Obtener precio vigente al momento de la reserva (congelado)
            $precio = PrecioServicio::obtenerPrecioVigente($servicioId, (int)$vehiculo['tipo_vehiculo_id']);
            if ($precio === null || $precio <= 0) {
                throw new RuntimeException('No existe tarifa vigente configurada para este servicio y tipo de vehiculo.');
            }

            // 3. Validar disponibilidad con bloqueo pesimista en transaccion
            $disponible = DisponibilidadServicio::hayDisponibilidad($fecha, $horaInicio, $duracion, null, true);
            if (!$disponible) {
                throw new RuntimeException('El turno seleccionado no se encuentra disponible (capacidad maxima alcanzada o fuera de horario).');
            }

            $horaFin = DisponibilidadServicio::calcularHoraFin($horaInicio, $duracion);

            $codigo = self::generarCodigo();
            $tokenPublico = bin2hex(random_bytes(24));
            $tokenVence = date('Y-m-d H:i:s', strtotime('+30 days'));

            // 4. Insertar reserva
            $stmt = $db->prepare('
                INSERT INTO reservas (
                    codigo, token_publico, token_vence, vehiculo_id, servicio_id,
                    fecha, hora_inicio, hora_fin, duracion_minutos, precio_acordado,
                    estado, contacto_nombre, contacto_telefono, contacto_correo,
                    creada_por, creado_en
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "reservada", ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $codigo,
                $tokenPublico,
                $tokenVence,
                $vehiculoId,
                $servicioId,
                $fecha,
                $horaInicio,
                $horaFin,
                $duracion,
                $precio,
                $vehiculo['cliente_nombre'] . ' ' . $vehiculo['cliente_apellido'],
                $vehiculo['cliente_telefono'] ?? null,
                $vehiculo['cliente_correo'] ?? null,
                $creadaPor
            ]);
            $reservaId = (int)$db->lastInsertId();

            // 5. Notificacion interna
            Notificacion::crear(
                (int)$vehiculo['cliente_id'],
                'Reserva confirmada',
                "Su turno para {$vehiculo['marca']} {$vehiculo['modelo']} (" . ($vehiculo['patente'] ?: 'Sin patente') . ") el dia {$fecha} a las " . substr($horaInicio, 0, 5) . " ha sido confirmado.",
                'interna',
                $reservaId
            );

            // 6. Auditoria
            RegistroOperacion::registrar(
                $creadaPor,
                'CREAR_RESERVA',
                'Reserva',
                $reservaId,
                "Reserva #{$reservaId} ({$codigo}) creada para {$fecha} {$horaInicio} - {$horaFin}. Precio acordado: \${$precio}."
            );

            BaseDatos::getInstancia()->confirmar();
            return $reservaId;
        } catch (\Throwable $e) {
            BaseDatos::getInstancia()->revertir();
            throw $e;
        }
    }

    /**
     * Reserva rapida publica sin cuenta (Requisito 3.1)
     */
    public static function crearPublica(array $datos): array {
        // 1. Honeypot check (campo trampa antispam)
        if (!empty($datos['sitio_web'])) {
            return [
                'id' => 0,
                'codigo' => 'WF-00000',
                'token' => 'dummy',
                'mensaje' => 'Solicitud recibida'
            ];
        }

        // 2. Validacion de datos de contacto
        $nombre = trim((string)($datos['nombre'] ?? ''));
        $telefono = trim((string)($datos['telefono'] ?? ''));
        $correo = !empty($datos['correo']) ? trim((string)$datos['correo']) : null;

        if (empty($nombre) || strlen($nombre) < 2) {
            throw new RuntimeException('Por favor, ingrese su nombre y apellido completo.');
        }

        $telDigitos = preg_replace('/[^0-9]/', '', $telefono);
        if (strlen($telDigitos) < 8 || strlen($telDigitos) > 15) {
            throw new RuntimeException('Ingrese un numero de telefono celular valido (minimo 8 digitos).');
        }

        // 3. Limite de maximo 3 reservas activas por telefono
        if (self::contarActivasPorTelefono($telefono) >= 3) {
            throw new RuntimeException('Alcanzo el limite maximo de 3 reservas activas para este numero de telefono. Modifique o cancele una anterior para poder reservar.');
        }

        // 4. Tipo de vehiculo y modelo
        $tipoVehiculoId = (int)($datos['tipo_vehiculo_id'] ?? 0);
        $tipoVehiculo = TipoVehiculo::buscarPorId($tipoVehiculoId);
        if (!$tipoVehiculo) {
            throw new RuntimeException('Debe seleccionar una categoria valida de vehiculo.');
        }

        $marca = trim((string)($datos['marca'] ?? ''));
        $modelo = trim((string)($datos['modelo'] ?? ''));
        if (empty($marca)) $marca = 'Vehiculo';
        if (empty($modelo)) $modelo = 'Cliente';

        $patente = null;
        if (!empty($datos['patente'])) {
            $patenteCandidata = Vehiculo::normalizarPatente((string)$datos['patente']);
            if ($patenteCandidata !== null && !Vehiculo::esPatenteValida($patenteCandidata)) {
                throw new RuntimeException('Patente invalida (' . htmlspecialchars((string)$datos['patente']) . '). Formato esperado: AB123CD (Mercosur) o ABC123 (anterior).');
            }
            $patente = $patenteCandidata;
        }
        $tipoAConfirmar = !empty($datos['tipo_a_confirmar']) ? 1 : 0;

        // 5. Servicio
        $servicioId = (int)($datos['servicio_id'] ?? 0);
        $servicio = Servicio::buscarPorId($servicioId);
        if (!$servicio || empty($servicio['activo'])) {
            throw new RuntimeException('Debe seleccionar un servicio valido.');
        }

        // 6. Fecha y Horario
        $fecha = trim((string)($datos['fecha'] ?? ''));
        $horaInicio = trim((string)($datos['hora_inicio'] ?? ''));
        if (empty($fecha) || empty($horaInicio)) {
            throw new RuntimeException('Debe seleccionar la fecha y el horario del turno.');
        }

        $duracion = (int)$tipoVehiculo['duracion_minutos'];

        // 7. Base de datos y transaccion
        $db = BaseDatos::getInstancia()->getConexion();
        BaseDatos::getInstancia()->iniciarTransaccion();

        try {
            // Congelar precio vigente
            $precio = PrecioServicio::obtenerPrecioVigente($servicioId, $tipoVehiculoId);
            if ($precio === null || $precio <= 0) {
                throw new RuntimeException('No existe tarifa vigente configurada para este servicio y vehiculo.');
            }

            // Validar disponibilidad con bloqueo pesimista
            $disponible = DisponibilidadServicio::hayDisponibilidad($fecha, $horaInicio, $duracion, null, true);
            if (!$disponible) {
                throw new RuntimeException('El turno seleccionado ya no se encuentra disponible. Por favor elija otro horario.');
            }

            $horaFin = DisponibilidadServicio::calcularHoraFin($horaInicio, $duracion);

            // Obtener o crear cliente ocasional
            $clienteId = Usuario::obtenerOCrearClienteOcasional($nombre, $telefono, $correo);

            // Crear vehiculo con patente opcional y tipo_a_confirmar
            $vehiculoId = Vehiculo::crear([
                'cliente_id' => $clienteId,
                'tipo_vehiculo_id' => $tipoVehiculoId,
                'patente' => $patente,
                'marca' => $marca,
                'modelo' => $modelo,
                'tipo_a_confirmar' => $tipoAConfirmar
            ]);

            // Generar codigo legible y token seguro
            $codigo = self::generarCodigo();
            $tokenPublico = bin2hex(random_bytes(24));
            $tokenVence = date('Y-m-d H:i:s', strtotime('+30 days'));

            $stmt = $db->prepare('
                INSERT INTO reservas (
                    codigo, token_publico, token_vence, vehiculo_id, servicio_id,
                    fecha, hora_inicio, hora_fin, duracion_minutos, precio_acordado,
                    estado, contacto_nombre, contacto_telefono, contacto_correo,
                    creada_por, creado_en
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "reservada", ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $codigo,
                $tokenPublico,
                $tokenVence,
                $vehiculoId,
                $servicioId,
                $fecha,
                $horaInicio,
                $horaFin,
                $duracion,
                $precio,
                $nombre,
                $telefono,
                $correo,
                $clienteId
            ]);
            $reservaId = (int)$db->lastInsertId();

            // Notificacion
            Notificacion::crear(
                $clienteId,
                'Turno reservado',
                "Turno {$codigo} confirmado para {$marca} {$modelo} el {$fecha} a las " . substr($horaInicio, 0, 5) . " hs.",
                'interna',
                $reservaId
            );

            // Auditoria
            RegistroOperacion::registrar(
                $clienteId,
                'RESERVA_PUBLICA',
                'Reserva',
                $reservaId,
                "Reserva publica {$codigo} creada para {$fecha} {$horaInicio} por {$nombre} ({$telefono}). Precio acordado: \${$precio}."
            );

            BaseDatos::getInstancia()->confirmar();

            return [
                'id' => $reservaId,
                'codigo' => $codigo,
                'token' => $tokenPublico,
                'fecha' => $fecha,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'precio' => $precio,
                'duracion' => $duracion,
                'tipo_nombre' => $tipoVehiculo['nombre'],
                'servicio_nombre' => $servicio['nombre'],
                'tipo_a_confirmar' => $tipoAConfirmar
            ];
        } catch (\Throwable $e) {
            BaseDatos::getInstancia()->revertir();
            throw $e;
        }
    }

    public static function reprogramarPublica(string $codigo, string $token, string $nuevaFecha, string $nuevaHora, string $motivo = ''): array {
        $reserva = self::buscarPorCodigoYToken($codigo, $token);
        if (!$reserva) {
            throw new RuntimeException('Reserva no encontrada o enlace invalido.');
        }

        if ($reserva['estado'] !== 'reservada') {
            throw new RuntimeException('Solo se pueden reprogramar reservas activas.');
        }

        $permiteDirecto = self::calcularPermiteCambioDirecto($reserva['fecha'], $reserva['hora_inicio']);
        if ($permiteDirecto) {
            // Reprogramacion directa
            self::reprogramar((int)$reserva['id'], $nuevaFecha, $nuevaHora, (int)$reserva['cliente_id']);
            return [
                'tipo' => 'directo',
                'mensaje' => "Su turno fue reprogramado exitosamente para el {$nuevaFecha} a las " . substr($nuevaHora, 0, 5) . " hs."
            ];
        }

        // Fuera de plazo (< 2 horas): se genera solicitud para aprobacion
        $solicitudId = SolicitudReserva::crear(
            (int)$reserva['id'],
            'cambio',
            $nuevaFecha,
            $nuevaHora,
            $motivo ?: 'Reprogramacion solicitada con menos de 2 horas de anticipacion.'
        );

        return [
            'tipo' => 'solicitud',
            'solicitud_id' => $solicitudId,
            'mensaje' => 'Como faltan menos de 2 horas para su turno, su solicitud de cambio fue enviada al lavadero. El equipo la revisara a la brevedad y le avisara por telefono.'
        ];
    }

    public static function cancelarPublica(string $codigo, string $token, string $motivo = ''): array {
        $reserva = self::buscarPorCodigoYToken($codigo, $token);
        if (!$reserva) {
            throw new RuntimeException('Reserva no encontrada o enlace invalido.');
        }

        if ($reserva['estado'] !== 'reservada') {
            throw new RuntimeException('Solo se pueden cancelar reservas activas.');
        }

        $permiteDirecto = self::calcularPermiteCambioDirecto($reserva['fecha'], $reserva['hora_inicio']);
        if ($permiteDirecto) {
            self::cancelar((int)$reserva['id'], (int)$reserva['cliente_id'], $motivo ?: 'Cancelada por el cliente desde el enlace publico.');
            return [
                'tipo' => 'directo',
                'mensaje' => 'Su reserva ha sido cancelada correctamente.'
            ];
        }

        // Fuera de plazo (< 2 horas)
        $solicitudId = SolicitudReserva::crear(
            (int)$reserva['id'],
            'cancelacion',
            null,
            null,
            $motivo ?: 'Cancelacion solicitada con menos de 2 horas de anticipacion.'
        );

        return [
            'tipo' => 'solicitud',
            'solicitud_id' => $solicitudId,
            'mensaje' => 'Como faltan menos de 2 horas para el turno, se envio una solicitud de cancelacion al lavadero para su resolucion.'
        ];
    }

    public static function reprogramar(int $id, string $nuevaFecha, string $nuevaHora, int $modificadaPor): bool {
        $reserva = self::buscarPorId($id);
        if (!$reserva) {
            throw new RuntimeException('Reserva no encontrada.');
        }

        if ($reserva['estado'] !== 'reservada') {
            throw new RuntimeException('Solo se pueden reprogramar reservas en estado reservada.');
        }

        $duracion = (int)$reserva['duracion_minutos'];
        $db = BaseDatos::getInstancia()->getConexion();
        BaseDatos::getInstancia()->iniciarTransaccion();

        try {
            $disponible = DisponibilidadServicio::hayDisponibilidad($nuevaFecha, $nuevaHora, $duracion, $id, true);
            if (!$disponible) {
                throw new RuntimeException('No hay disponibilidad en la nueva fecha y hora solicitada.');
            }

            $nuevaHoraFin = DisponibilidadServicio::calcularHoraFin($nuevaHora, $duracion);

            $stmt = $db->prepare('
                UPDATE reservas
                SET fecha = ?, hora_inicio = ?, hora_fin = ?
                WHERE id = ?
            ');
            $stmt->execute([$nuevaFecha, $nuevaHora, $nuevaHoraFin, $id]);

            // Notificacion
            Notificacion::crear(
                (int)$reserva['cliente_id'],
                'Turno reprogramado',
                "Su reserva #{$id} fue reprogramada para el {$nuevaFecha} a las " . substr($nuevaHora, 0, 5) . " hs.",
                'interna',
                $id
            );

            // Auditoria
            RegistroOperacion::registrar(
                $modificadaPor,
                'REPROGRAMAR_RESERVA',
                'Reserva',
                $id,
                "Reserva #{$id} reprogramada a {$nuevaFecha} de {$nuevaHora} a {$nuevaHoraFin}."
            );

            BaseDatos::getInstancia()->confirmar();
            return true;
        } catch (\Throwable $e) {
            BaseDatos::getInstancia()->revertir();
            throw $e;
        }
    }

    public static function cancelar(int $id, int $canceladaPor, string $motivo = ''): bool {
        $reserva = self::buscarPorId($id);
        if (!$reserva) {
            throw new RuntimeException('Reserva no encontrada.');
        }

        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('UPDATE reservas SET estado = "cancelada" WHERE id = ?');
        $res = $stmt->execute([$id]);

        if ($res) {
            Notificacion::crear(
                (int)$reserva['cliente_id'],
                'Reserva cancelada',
                "La reserva #{$id} programada para el {$reserva['fecha']} ha sido cancelada." . ($motivo ? " Motivo: {$motivo}" : ""),
                'interna',
                $id
            );

            RegistroOperacion::registrar(
                $canceladaPor,
                'CANCELAR_RESERVA',
                'Reserva',
                $id,
                "Reserva #{$id} marcada como cancelada. Motivo: {$motivo}"
            );
        }
        return $res;
    }

    public static function marcarAusente(int $id, int $empleadoId): bool {
        $reserva = self::buscarPorId($id);
        if (!$reserva) {
            throw new RuntimeException('Reserva no encontrada.');
        }

        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('UPDATE reservas SET estado = "ausente" WHERE id = ?');
        $res = $stmt->execute([$id]);

        if ($res) {
            Notificacion::crear(
                (int)$reserva['cliente_id'],
                'Aviso de ausencia',
                "Se registro su inasistencia al turno programado el {$reserva['fecha']} a las {$reserva['hora_inicio']}.",
                'interna',
                $id
            );

            RegistroOperacion::registrar(
                $empleadoId,
                'REGISTRAR_AUSENCIA',
                'Reserva',
                $id,
                "Se registro ausencia para la reserva #{$id}."
            );
        }
        return $res;
    }

    public static function listarPorCliente(int $clienteId): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT r.*,
                   v.patente, v.marca, v.modelo, v.tipo_vehiculo_id, v.tipo_a_confirmar,
                   tv.nombre AS tipo_nombre,
                   s.nombre AS servicio_nombre,
                   p.id AS pago_id, p.importe AS pago_importe, p.medio AS pago_medio,
                   l.id AS lavado_id, l.estado AS lavado_estado
            FROM reservas r
            JOIN vehiculos v ON r.vehiculo_id = v.id
            JOIN tipos_vehiculo tv ON v.tipo_vehiculo_id = tv.id
            JOIN servicios s ON r.servicio_id = s.id
            LEFT JOIN pagos p ON p.reserva_id = r.id
            LEFT JOIN lavados l ON l.reserva_id = r.id
            WHERE v.cliente_id = ?
            ORDER BY r.fecha DESC, r.hora_inicio DESC
        ');
        $stmt->execute([$clienteId]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$r) {
            $r['estado_pago'] = ($r['pago_id'] !== null) ? 'pagado' : 'pendiente';
            $r['permite_cambio_directo'] = self::calcularPermiteCambioDirecto($r['fecha'], $r['hora_inicio']);
        }
        return $rows;
    }

    public static function listarPorFecha(string $fecha): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT r.*,
                   v.patente, v.marca, v.modelo, v.tipo_vehiculo_id, v.tipo_a_confirmar,
                   tv.nombre AS tipo_nombre,
                   s.nombre AS servicio_nombre,
                   u.nombre AS cliente_nombre, u.apellido AS cliente_apellido, u.telefono AS cliente_telefono,
                   p.id AS pago_id, p.medio AS pago_medio,
                   l.id AS lavado_id, l.estado AS lavado_estado
            FROM reservas r
            JOIN vehiculos v ON r.vehiculo_id = v.id
            JOIN tipos_vehiculo tv ON v.tipo_vehiculo_id = tv.id
            JOIN servicios s ON r.servicio_id = s.id
            JOIN usuarios u ON v.cliente_id = u.id
            LEFT JOIN pagos p ON p.reserva_id = r.id
            LEFT JOIN lavados l ON l.reserva_id = r.id
            WHERE r.fecha = ?
            ORDER BY r.hora_inicio ASC
        ');
        $stmt->execute([$fecha]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$r) {
            $r['estado_pago'] = ($r['pago_id'] !== null) ? 'pagado' : 'pendiente';
            $r['permite_cambio_directo'] = self::calcularPermiteCambioDirecto($r['fecha'], $r['hora_inicio']);
        }
        return $rows;
    }

    public static function listarTodas(array $filtros = [], int $limite = 50, int $offset = 0): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $sql = '
            SELECT r.*,
                   v.patente, v.marca, v.modelo, v.tipo_vehiculo_id, v.tipo_a_confirmar,
                   tv.nombre AS tipo_nombre,
                   s.nombre AS servicio_nombre,
                   u.nombre AS cliente_nombre, u.apellido AS cliente_apellido, u.telefono AS cliente_telefono, u.correo AS cliente_correo,
                   p.id AS pago_id, p.importe AS pago_importe, p.medio AS pago_medio,
                   l.id AS lavado_id, l.estado AS lavado_estado
            FROM reservas r
            JOIN vehiculos v ON r.vehiculo_id = v.id
            JOIN tipos_vehiculo tv ON v.tipo_vehiculo_id = tv.id
            JOIN servicios s ON r.servicio_id = s.id
            JOIN usuarios u ON v.cliente_id = u.id
            LEFT JOIN pagos p ON p.reserva_id = r.id
            LEFT JOIN lavados l ON l.reserva_id = r.id
            WHERE 1=1
        ';
        $params = [];

        if (!empty($filtros['fecha_desde'])) {
            $sql .= ' AND r.fecha >= ?';
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= ' AND r.fecha <= ?';
            $params[] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['estado'])) {
            $sql .= ' AND r.estado = ?';
            $params[] = $filtros['estado'];
        }
        if (!empty($filtros['busqueda'])) {
            $sql .= ' AND (v.patente LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ? OR u.telefono LIKE ?)';
            $like = '%' . $filtros['busqueda'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' ORDER BY r.fecha DESC, r.hora_inicio DESC LIMIT ? OFFSET ?';
        $params[] = $limite;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        foreach ($params as $k => $val) {
            $stmt->bindValue($k + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as &$r) {
            $r['estado_pago'] = ($r['pago_id'] !== null) ? 'pagado' : 'pendiente';
            $r['permite_cambio_directo'] = self::calcularPermiteCambioDirecto($r['fecha'], $r['hora_inicio']);
        }
        return $rows;
    }

    public static function contarTodas(array $filtros = []): int {
        $db = BaseDatos::getInstancia()->getConexion();
        $sql = '
            SELECT COUNT(*)
            FROM reservas r
            JOIN vehiculos v ON r.vehiculo_id = v.id
            JOIN usuarios u ON v.cliente_id = u.id
            WHERE 1=1
        ';
        $params = [];

        if (!empty($filtros['fecha_desde'])) {
            $sql .= ' AND r.fecha >= ?';
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= ' AND r.fecha <= ?';
            $params[] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['estado'])) {
            $sql .= ' AND r.estado = ?';
            $params[] = $filtros['estado'];
        }
        if (!empty($filtros['busqueda'])) {
            $sql .= ' AND (v.patente LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ? OR u.telefono LIKE ?)';
            $like = '%' . $filtros['busqueda'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Confirma o corrige la categoria de un vehiculo para una reserva (Secciones 13.2 y 14)
     * Si cambia el precio, valida aviso al cliente, actualiza reserva y lavado, notifica a administradores y audita.
     */
    public static function corregirCategoriaVehiculo(
        int $reservaId, 
        int $nuevoTipoId, 
        ?string $patente, 
        bool $avisoClienteConfirmado, 
        string $motivo, 
        int $usuarioId
    ): array {
        $reserva = self::buscarPorId($reservaId);
        if (!$reserva) {
            throw new RuntimeException("Reserva #{$reservaId} no encontrada.");
        }

        $vehiculoId = (int)$reserva['vehiculo_id'];
        $tipoAnteriorId = (int)$reserva['tipo_vehiculo_id'];
        $precioAnterior = (float)$reserva['precio_acordado'];
        $duracionAnterior = (int)$reserva['duracion_minutos'];

        $nuevoTipo = TipoVehiculo::buscarPorId($nuevoTipoId);
        if (!$nuevoTipo) {
            throw new RuntimeException("Tipo de vehiculo #{$nuevoTipoId} invalido.");
        }

        $cambioCategoria = ($tipoAnteriorId !== $nuevoTipoId);
        $nuevoPrecio = $precioAnterior;
        $nuevaDuracion = $duracionAnterior;

        if ($cambioCategoria) {
            $nuevoPrecio = PrecioServicio::obtenerPrecioVigente((int)$reserva['servicio_id'], $nuevoTipoId);
            $nuevaDuracion = (int)$nuevoTipo['duracion_minutos'];

            if ($nuevoPrecio !== $precioAnterior && !$avisoClienteConfirmado) {
                throw new RuntimeException("El cambio de categoria modifica el precio (de \${$precioAnterior} a \${$nuevoPrecio}). Debe confirmar que le informo la nueva tarifa al cliente.");
            }
        }

        $db = BaseDatos::getInstancia()->getConexion();
        BaseDatos::getInstancia()->iniciarTransaccion();

        try {
            // Actualizar vehiculo
            $patenteFinal = $reserva['patente'];
            if (!empty($patente)) {
                $patenteCandidata = Vehiculo::normalizarPatente($patente);
                if ($patenteCandidata !== null && !Vehiculo::esPatenteValida($patenteCandidata)) {
                    throw new RuntimeException('Patente invalida (' . htmlspecialchars($patente) . '). Formato esperado: AB123CD o ABC123.');
                }
                $patenteFinal = $patenteCandidata;
            }
            $stmtV = $db->prepare('
                UPDATE vehiculos 
                SET tipo_vehiculo_id = ?, patente = ?, tipo_a_confirmar = 0 
                WHERE id = ?
            ');
            $stmtV->execute([$nuevoTipoId, $patenteFinal, $vehiculoId]);

            // Actualizar reserva si cambio categoria/precio/duracion
            if ($cambioCategoria) {
                $stmtR = $db->prepare('
                    UPDATE reservas 
                    SET precio_acordado = ?, duracion_minutos = ? 
                    WHERE id = ?
                ');
                $stmtR->execute([$nuevoPrecio, $nuevaDuracion, $reservaId]);

                // Si ya habia lavado pendiente creado, actualizar su precio
                $stmtL = $db->prepare('
                    UPDATE lavados 
                    SET precio = ? 
                    WHERE reserva_id = ? AND estado = "pendiente"
                ');
                $stmtL->execute([$nuevoPrecio, $reservaId]);

                // Notificar internamente a los Administradores si cambio el precio
                if ($nuevoPrecio !== $precioAnterior) {
                    $tituloNotif = "Correccion de categoria en turno #{$reservaId}";
                    $msjNotif = "El usuario #{$usuarioId} corrigio la categoria del vehiculo en la reserva #{$reservaId} ({$nuevoTipo['nombre']}). Tarifa actualizada de \${$precioAnterior} a \${$nuevoPrecio}. Aviso al cliente confirmado.";
                    Notificacion::notificarAdmins($tituloNotif, $msjNotif, $reservaId);
                }

                $detalleAudit = sprintf(
                    "Categoria de vehiculo corregida para Reserva #%d. Anterior: tipo #%d ($%.2f, %d min). Nuevo: tipo #%d ($%.2f, %d min). Patente: %s. Motivo: %s. Aviso al cliente: %s",
                    $reservaId,
                    $tipoAnteriorId,
                    $precioAnterior,
                    $duracionAnterior,
                    $nuevoTipoId,
                    $nuevoPrecio,
                    $nuevaDuracion,
                    $patenteFinal,
                    $motivo ?: 'Sin motivo adicional',
                    $avisoClienteConfirmado ? 'Confirmado' : 'No requirio'
                );

                RegistroOperacion::registrar(
                    $usuarioId,
                    'CORREGIR_CATEGORIA_VEHICULO',
                    'Reserva',
                    $reservaId,
                    $detalleAudit
                );
            } else {
                // Solo confirmacion sin cambio de categoria
                $stmtR = $db->prepare('UPDATE vehiculos SET tipo_a_confirmar = 0 WHERE id = ?');
                $stmtR->execute([$vehiculoId]);

                RegistroOperacion::registrar(
                    $usuarioId,
                    'CONFIRMAR_CATEGORIA_VEHICULO',
                    'Vehiculo',
                    $vehiculoId,
                    "Categoria {$nuevoTipo['nombre']} confirmada sin cambios tarifarios para Reserva #{$reservaId}."
                );
            }

            BaseDatos::getInstancia()->confirmar();
            return [
                'exito' => true,
                'nuevo_precio' => $nuevoPrecio,
                'nueva_duracion' => $nuevaDuracion,
                'cambio_precio' => ($nuevoPrecio !== $precioAnterior)
            ];
        } catch (\Throwable $e) {
            BaseDatos::getInstancia()->revertir();
            throw $e;
        }
    }
}

