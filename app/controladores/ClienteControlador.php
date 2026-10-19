<?php
/**
 * WashFlow - Controlador del Modulo Cliente (Diagramas 01, 04a, 12)
 */

declare(strict_types=1);

namespace WashFlow\Controladores;

use WashFlow\Core\Controlador;
use WashFlow\Core\Sesion;
use WashFlow\Core\Validador;
use WashFlow\Modelos\Notificacion;
use WashFlow\Modelos\RegistroOperacion;
use WashFlow\Modelos\Reserva;
use WashFlow\Modelos\TipoVehiculo;
use WashFlow\Modelos\Vehiculo;

class ClienteControlador extends Controlador {
    public function dashboard(): void {
        $this->requerirRol('cliente');
        $clienteId = (int)$this->getUsuarioId();

        $vehiculos = Vehiculo::listarPorCliente($clienteId, true);
        $reservas = Reserva::listarPorCliente($clienteId);

        // Separar reservas activas / proximas vs historicas
        $proximas = [];
        $historicas = [];
        $hoy = date('Y-m-d');
        $ahora = date('H:i:s');

        foreach ($reservas as $r) {
            $esFutura = ($r['fecha'] > $hoy) || ($r['fecha'] === $hoy && $r['hora_inicio'] >= $ahora);
            if ($r['estado'] === 'reservada' && $esFutura) {
                $proximas[] = $r;
            } else {
                $historicas[] = $r;
            }
        }

        $notificaciones = Notificacion::listarPorUsuario($clienteId, 5);
        $noLeidas = Notificacion::contarNoLeidas($clienteId);

        $this->render('cliente/dashboard', [
            'titulo' => 'Mi Panel - WashFlow',
            'vehiculos' => $vehiculos,
            'proximas' => $proximas,
            'historicas' => array_slice($historicas, 0, 5),
            'notificaciones' => $notificaciones,
            'noLeidas' => $noLeidas
        ]);
    }

    public function vehiculos(): void {
        $this->requerirRol('cliente');
        $clienteId = (int)$this->getUsuarioId();
        $vehiculos = Vehiculo::listarPorCliente($clienteId, false);
        $tipos = TipoVehiculo::listarTodos();

        $this->render('cliente/vehiculos/index', [
            'titulo' => 'Mis Vehiculos - WashFlow',
            'vehiculos' => $vehiculos,
            'tipos' => $tipos
        ]);
    }

    public function mostrarCrearVehiculo(): void {
        $this->requerirRol('cliente');
        $this->redirect('/cliente/vehiculos?crear=1');
    }

    public function guardarVehiculo(): void {
        $this->requerirRol('cliente');
        $this->validarCsrf();
        $clienteId = (int)$this->getUsuarioId();

        $validador = new Validador($_POST);
        $validador->validar([
            'tipo_vehiculo_id' => 'requerido|entero',
            'patente' => 'requerido|patente|max:9',
            'marca' => 'requerido|min:2|max:80',
            'modelo' => 'requerido|min:1|max:80'
        ]);

        if ($validador->tieneErrores()) {
            $primerError = current($validador->getErrores());
            Sesion::setFlash('error', $primerError ?: 'Por favor verifique los datos ingresados.');
            $this->redirect('/cliente/vehiculos?crear=1');
            return;
        }

        $patente = strtoupper(trim((string)$validador->getDato('patente')));
        if (Vehiculo::buscarPorPatente($patente)) {
            Sesion::setFlash('error', 'Ya existe un vehiculo registrado con la patente ingresada.');
            $this->redirect('/cliente/vehiculos?crear=1');
            return;
        }

        $vehiculoId = Vehiculo::crear([
            'cliente_id' => $clienteId,
            'tipo_vehiculo_id' => (int)$validador->getDato('tipo_vehiculo_id'),
            'patente' => $patente,
            'marca' => (string)$validador->getDato('marca'),
            'modelo' => (string)$validador->getDato('modelo')
        ]);

        RegistroOperacion::registrar(
            $clienteId,
            'REGISTRAR_VEHICULO',
            'Vehiculo',
            $vehiculoId,
            "Vehiculo {$patente} registrado por el cliente."
        );

        Sesion::setFlash('exito', 'Vehiculo registrado exitosamente.');
        $this->redirect('/cliente/vehiculos');
    }

    public function mostrarEditarVehiculo(string $id): void {
        $this->requerirRol('cliente');
        $clienteId = (int)$this->getUsuarioId();
        $vehiculo = Vehiculo::buscarPorId((int)$id);

        if (!$vehiculo || (int)$vehiculo['cliente_id'] !== $clienteId) {
            $this->mostrarError404('Vehiculo no encontrado.');
        }

        $this->redirect("/cliente/vehiculos?editar={$id}");
    }

    public function actualizarVehiculo(string $id): void {
        $this->requerirRol('cliente');
        $this->validarCsrf();
        $clienteId = (int)$this->getUsuarioId();
        $vehiculo = Vehiculo::buscarPorId((int)$id);

        if (!$vehiculo || (int)$vehiculo['cliente_id'] !== $clienteId) {
            $this->mostrarError404('Vehiculo no encontrado.');
        }

        $validador = new Validador($_POST);
        $validador->validar([
            'tipo_vehiculo_id' => 'requerido|entero',
            'patente' => 'requerido|patente|max:9',
            'marca' => 'requerido|min:2|max:80',
            'modelo' => 'requerido|min:1|max:80'
        ]);

        if ($validador->tieneErrores()) {
            $tipos = TipoVehiculo::listarTodos();
            $primerError = current($validador->getErrores());
            Sesion::setFlash('error', $primerError ?: 'Por favor verifique los datos ingresados.');
            $this->redirect("/cliente/vehiculos?editar={$id}");
            return;
        }

        $patente = strtoupper(trim((string)$validador->getDato('patente')));
        $existente = Vehiculo::buscarPorPatente($patente);
        if ($existente && (int)$existente['id'] !== (int)$id) {
            Sesion::setFlash('error', 'Ya existe otro vehiculo con la patente ingresada.');
            $this->redirect("/cliente/vehiculos?editar={$id}");
            return;
        }

        Vehiculo::actualizar((int)$id, [
            'tipo_vehiculo_id' => (int)$validador->getDato('tipo_vehiculo_id'),
            'patente' => $patente,
            'marca' => (string)$validador->getDato('marca'),
            'modelo' => (string)$validador->getDato('modelo')
        ]);

        RegistroOperacion::registrar(
            $clienteId,
            'EDITAR_VEHICULO',
            'Vehiculo',
            (int)$id,
            "Vehiculo #{$id} actualizado ({$patente})."
        );

        Sesion::setFlash('exito', 'Vehiculo actualizado correctamente.');
        $this->redirect('/cliente/vehiculos');
    }

    public function desactivarVehiculo(string $id): void {
        $this->requerirRol('cliente');
        $this->validarCsrf();
        $clienteId = (int)$this->getUsuarioId();
        $vehiculo = Vehiculo::buscarPorId((int)$id);

        if (!$vehiculo || (int)$vehiculo['cliente_id'] !== $clienteId) {
            $this->mostrarError404('Vehiculo no encontrado.');
        }

        Vehiculo::desactivar((int)$id);

        RegistroOperacion::registrar(
            $clienteId,
            'DESACTIVAR_VEHICULO',
            'Vehiculo',
            (int)$id,
            "Vehiculo #{$id} desactivado (baja logica)."
        );

        Sesion::setFlash('exito', 'Vehiculo dado de baja correctamente.');
        $this->redirect('/cliente/vehiculos');
    }

    public function notificaciones(): void {
        $this->requerirRol('cliente');
        $clienteId = (int)$this->getUsuarioId();
        $notificaciones = Notificacion::listarPorUsuario($clienteId, 50);

        $this->render('cliente/notificaciones/index', [
            'titulo' => 'Centro de Notificaciones - WashFlow',
            'notificaciones' => $notificaciones
        ]);
    }

    public function marcarTodasNotificacionesLeidas(): void {
        $this->requerirRol('cliente');
        $this->validarCsrf();
        $clienteId = (int)$this->getUsuarioId();
        Notificacion::marcarTodasLeidas($clienteId);

        Sesion::setFlash('exito', 'Todas las notificaciones fueron marcadas como leidas.');
        $this->redirect('/cliente/notificaciones');
    }
}
