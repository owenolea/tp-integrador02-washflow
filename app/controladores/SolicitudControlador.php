<?php
/**
 * WashFlow - Controlador de Solicitudes Fuera de Plazo (Diagramas 02, 04a, 09, 12)
 * Se aplica cuando faltan menos de 2 horas para el turno.
 */

declare(strict_types=1);

namespace WashFlow\Controladores;

use Throwable;
use WashFlow\Core\Controlador;
use WashFlow\Core\Sesion;
use WashFlow\Core\Validador;
use WashFlow\Modelos\Reserva;
use WashFlow\Modelos\SolicitudReserva;

class SolicitudControlador extends Controlador {
    /**
     * Vista de creacion de solicitud para el cliente (menos de 2 horas)
     */
    public function mostrarSolicitar(string $reservaId): void {
        $this->requerirRol('cliente');
        $clienteId = (int)$this->getUsuarioId();
        $reserva = Reserva::buscarPorId((int)$reservaId);

        if (!$reserva || (int)$reserva['cliente_id'] !== $clienteId) {
            $this->mostrarError404('Reserva no encontrada.');
        }

        $this->render('cliente/reservas/solicitar', [
            'titulo' => 'Solicitud de Cambio o Cancelacion - WashFlow',
            'reserva' => $reserva
        ]);
    }

    public function guardarSolicitud(string $reservaId): void {
        $this->requerirRol('cliente');
        $this->validarCsrf();
        $clienteId = (int)$this->getUsuarioId();
        $reserva = Reserva::buscarPorId((int)$reservaId);

        if (!$reserva || (int)$reserva['cliente_id'] !== $clienteId) {
            $this->mostrarError404('Reserva no encontrada.');
        }

        $validador = new Validador($_POST);
        $validador->validar([
            'tipo' => 'requerido|in:cambio,cancelacion',
            'motivo' => 'requerido|min:5|max:500'
        ]);

        $tipo = (string)$validador->getDato('tipo');
        $nuevaFecha = ($tipo === 'cambio') ? (string)$validador->getDato('nueva_fecha') : null;
        $nuevaHora = ($tipo === 'cambio') ? (string)$validador->getDato('nueva_hora') : null;

        if ($tipo === 'cambio' && (!$nuevaFecha || !$nuevaHora)) {
            $validador->validar(['nueva_fecha' => 'requerido']);
        }

        if ($validador->tieneErrores()) {
            $this->render('cliente/reservas/solicitar', [
                'titulo' => 'Solicitud de Cambio o Cancelacion - WashFlow',
                'reserva' => $reserva,
                'errores' => $validador->getErrores(),
                'valores' => $validador->getDatos()
            ]);
            return;
        }

        $solicitudId = SolicitudReserva::crear(
            (int)$reservaId,
            $tipo,
            $nuevaFecha,
            $nuevaHora,
            (string)$validador->getDato('motivo')
        );

        Sesion::setFlash('exito', "Solicitud #{$solicitudId} enviada con exito. El personal del lavadero la revisara a la brevedad.");
        $this->redirect('/cliente/reservas');
    }

    /**
     * Resolucion de solicitudes por Empleado o Administrador
     */
    public function aprobar(string $id): void {
        $this->requerirRol(['empleado', 'administrador']);
        $this->validarCsrf();
        $empleadoId = (int)$this->getUsuarioId();
        $observacion = trim((string)($_POST['observacion'] ?? ''));

        try {
            SolicitudReserva::aprobar((int)$id, $empleadoId, $observacion);
            Sesion::setFlash('exito', "Solicitud #{$id} aprobada correctamente.");
        } catch (Throwable $e) {
            Sesion::setFlash('error', 'No se pudo aprobar la solicitud: ' . $e->getMessage());
        }

        $this->redirect('/empleado/solicitudes');
    }

    public function rechazar(string $id): void {
        $this->requerirRol(['empleado', 'administrador']);
        $this->validarCsrf();
        $empleadoId = (int)$this->getUsuarioId();
        $motivo = trim((string)($_POST['motivo_rechazo'] ?? ''));

        if ($motivo === '') {
            Sesion::setFlash('error', 'Debe especificar el motivo del rechazo.');
            $this->redirect('/empleado/solicitudes');
            return;
        }

        try {
            SolicitudReserva::rechazar((int)$id, $empleadoId, $motivo);
            Sesion::setFlash('exito', "Solicitud #{$id} rechazada.");
        } catch (Throwable $e) {
            Sesion::setFlash('error', 'No se pudo rechazar la solicitud: ' . $e->getMessage());
        }

        $this->redirect('/empleado/solicitudes');
    }
}
