<?php
/**
 * WashFlow - Modificar Fecha u Horario de Reserva (Regla de 2 horas)
 */
?>
<div class="tarjeta" style="max-width: 640px; margin: 0 auto;">
    <div class="tarjeta-header">
        <h1 class="tarjeta-titulo">Modificar Horario de Turno #<?= (int)$reserva['id'] ?></h1>
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas" class="btn btn-secundario btn-sm">
            Volver
        </a>
    </div>

    <div class="tarjeta-cuerpo">
        <div class="alerta alerta-info" style="margin-bottom: 1.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <div>
                <strong>Turno actual:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($reserva['fecha'])), ENT_QUOTES, 'UTF-8') ?> a las <?= substr($reserva['hora_inicio'], 0, 5) ?> hs.<br>
                Vehiculo: <?= htmlspecialchars($reserva['marca'] . ' ' . $reserva['modelo'] . ' (' . $reserva['patente'] . ')', ENT_QUOTES, 'UTF-8') ?>.<br>
                Usted cuenta con mas de 2 horas de anticipacion, por lo que el cambio se aplica directamente al seleccionar un nuevo horario disponible.
            </div>
        </div>

        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas/<?= (int)$reserva['id'] ?>/reprogramar" method="POST">
            <?= $csrfCampo ?>
            <input type="hidden" id="input-vehiculo-duracion" value="<?= (int)$reserva['duracion_minutos'] ?>">
            <input type="hidden" id="input-reserva-id" value="<?= (int)$reserva['id'] ?>">

            <div class="grupo-formulario">
                <label for="input-fecha-modificar" class="label-formulario">Nueva Fecha *</label>
                <input type="date" id="input-fecha-modificar" name="fecha" class="control-formulario"
                       min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($reserva['fecha'], ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="grupo-formulario">
                <label for="select-hora-modificar" class="label-formulario">Nuevo Horario de Inicio *</label>
                <select id="select-hora-modificar" name="hora_inicio" class="control-formulario" required>
                    <option value="">Seleccione una hora disponible...</option>
                </select>
                <span id="mensaje-disponibilidad" class="texto-ayuda">Cargando disponibilidad...</span>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas" class="btn btn-secundario">Cancelar</a>
                <button type="submit" id="btn-guardar-cambio" class="btn btn-primario" disabled>Confirmar Reprogramacion</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputFecha = document.getElementById('input-fecha-modificar');
    const selectHora = document.getElementById('select-hora-modificar');
    const duracion = document.getElementById('input-vehiculo-duracion').value;
    const reservaId = document.getElementById('input-reserva-id').value;
    const btnGuardar = document.getElementById('btn-guardar-cambio');
    const msg = document.getElementById('mensaje-disponibilidad');

    function actualizarHoras() {
        const fecha = inputFecha.value;
        if (!fecha) return;

        msg.textContent = 'Consultando disponibilidad...';
        selectHora.innerHTML = '<option value="">Consultando...</option>';
        btnGuardar.disabled = true;

        const url = `${window.WASHFLOW_URL_BASE || ''}/api/disponibilidad?fecha=${encodeURIComponent(fecha)}&duracion=${encodeURIComponent(duracion)}&excluir_id=${reservaId}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                selectHora.innerHTML = '<option value="">Seleccione un horario disponible...</option>';

                if (!data.es_dia_atencion) {
                    msg.textContent = data.mensaje || 'Dia no disponible.';
                    return;
                }

                let hayDisponibles = false;
                data.franjas.forEach(f => {
                    if (f.disponible) {
                        hayDisponibles = true;
                        const opt = document.createElement('option');
                        opt.value = f.hora_inicio;
                        opt.textContent = `${f.hora_inicio} a ${f.hora_fin} hs (${f.ocupacion}/3 ocupados)`;
                        selectHora.appendChild(opt);
                    }
                });

                if (!hayDisponibles) {
                    msg.textContent = 'No hay cupos disponibles en esta fecha (capacidad completa de 3 vehiculos o bloqueado).';
                } else {
                    msg.textContent = 'Seleccione una franja horaria para confirmar.';
                }
            })
            .catch(() => {
                msg.textContent = 'Error al consultar disponibilidad.';
            });
    }

    selectHora.addEventListener('change', () => {
        btnGuardar.disabled = !selectHora.value;
    });

    inputFecha.addEventListener('change', actualizarHoras);
    actualizarHoras();
});
</script>
