<?php
/**
 * WashFlow - Solicitud de Cambio o Cancelacion fuera de plazo (Regla de 2 horas)
 */
?>
<div class="tarjeta" style="max-width: 640px; margin: 0 auto;">
    <div class="tarjeta-header">
        <h1 class="tarjeta-titulo">Solicitud Fuera de Plazo para Turno #<?= (int)$reserva['id'] ?></h1>
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas" class="btn btn-secundario btn-sm">
            Volver
        </a>
    </div>

    <div class="tarjeta-cuerpo">
        <div class="alerta alerta-aviso" style="margin-bottom: 1.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <div>
                <strong>Aviso de anticipacion:</strong> Faltan menos de 2 horas para su turno (o ya paso la hora estipulada).
                De acuerdo a las politicas del lavadero, debe enviar una solicitud explicando el motivo para que un empleado o el administrador la evalue.
            </div>
        </div>

        <div style="background-color: var(--color-superficie-alt); padding: 1rem; border-radius: var(--radio-md); margin-bottom: 1.5rem; font-size: 0.9rem;">
            <strong>Datos del turno actual:</strong><br>
            Fecha: <?= htmlspecialchars(date('d/m/Y', strtotime($reserva['fecha'])), ENT_QUOTES, 'UTF-8') ?> a las <?= substr($reserva['hora_inicio'], 0, 5) ?> hs.<br>
            Vehiculo: <?= htmlspecialchars($reserva['marca'] . ' ' . $reserva['modelo'] . ' (' . $reserva['patente'] . ')', ENT_QUOTES, 'UTF-8') ?>.<br>
            Servicio: <?= htmlspecialchars($reserva['servicio_nombre'], ENT_QUOTES, 'UTF-8') ?> ($<?= number_format((float)$reserva['precio_acordado'], 0, ',', '.') ?>).
        </div>

        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas/<?= (int)$reserva['id'] ?>/solicitar" method="POST" novalidate>
            <?= $csrfCampo ?>

            <div class="grupo-formulario">
                <label for="tipo" class="label-formulario">Tipo de Solicitud *</label>
                <select id="tipo" name="tipo" class="control-formulario" required onchange="toggleCamposCambio(this.value);">
                    <option value="cancelacion" <?= (($valores['tipo'] ?? '') === 'cancelacion') ? 'selected' : '' ?>>Solicitar Cancelacion de Turno</option>
                    <option value="cambio" <?= (($valores['tipo'] ?? '') === 'cambio') ? 'selected' : '' ?>>Solicitar Reprogramacion (Cambio de fecha/hora)</option>
                </select>
            </div>

            <div id="seccion-cambio" style="display: <?= (($valores['tipo'] ?? '') === 'cambio') ? 'grid' : 'none' ?>; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                <div class="grupo-formulario" style="margin-bottom: 0;">
                    <label for="nueva_fecha" class="label-formulario">Nueva Fecha solicitada</label>
                    <input type="date" id="nueva_fecha" name="nueva_fecha" class="control-formulario"
                           min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($valores['nueva_fecha'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="grupo-formulario" style="margin-bottom: 0;">
                    <label for="nueva_hora" class="label-formulario">Nueva Hora deseada</label>
                    <input type="time" id="nueva_hora" name="nueva_hora" class="control-formulario"
                           min="10:00" max="17:00" step="1800" value="<?= htmlspecialchars($valores['nueva_hora'] ?? '11:00', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <div class="grupo-formulario">
                <label for="motivo" class="label-formulario">Motivo de la solicitud *</label>
                <textarea id="motivo" name="motivo" class="control-formulario <?= !empty($errores['motivo']) ? 'con-error' : '' ?>"
                          rows="4" required placeholder="Explique la razon por la cual no podra asistir al turno fijado..."><?= htmlspecialchars($valores['motivo'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                <?php if (!empty($errores['motivo'])): ?>
                    <span class="mensaje-error-campo"><?= htmlspecialchars($errores['motivo'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas" class="btn btn-secundario">Cancelar</a>
                <button type="submit" class="btn btn-primario">Enviar Solicitud</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleCamposCambio(tipo) {
    const sec = document.getElementById('seccion-cambio');
    if (sec) {
        sec.style.display = (tipo === 'cambio') ? 'grid' : 'none';
    }
}
</script>
