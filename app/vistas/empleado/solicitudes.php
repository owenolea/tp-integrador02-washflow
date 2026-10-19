<?php
/**
 * WashFlow - Solicitudes Fuera de Plazo (Empleado / Administrador)
 */
?>
<div class="tarjeta">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Solicitudes de Cambio y Cancelacion Fuera de Plazo</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Turnos solicitados con menos de 2 horas de anticipacion que requieren resolucion del personal.
            </p>
        </div>
    </div>

    <div class="tarjeta-cuerpo" style="padding: 0;">
        <?php if (empty($solicitudes)): ?>
            <div style="padding: 3rem; text-align: center; color: var(--color-texto-apagado);">
                <p>No hay solicitudes pendientes de resolucion en este momento.</p>
            </div>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Solicitud #</th>
                            <th>Tipo</th>
                            <th>Fecha Envio</th>
                            <th>Cliente</th>
                            <th>Turno Original</th>
                            <th>Detalle / Nueva Fecha</th>
                            <th>Motivo del Cliente</th>
                            <th>Resolucion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $s): ?>
                            <tr>
                                <td><strong>#<?= (int)$s['id'] ?></strong></td>
                                <td>
                                    <?php if ($s['tipo'] === 'cambio'): ?>
                                        <span class="badge badge-info">Cambio</span>
                                    <?php else: ?>
                                        <span class="badge badge-peligro">Cancelacion</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m H:i', strtotime($s['fecha_solicitud'])) ?> hs</td>
                                <td>
                                    <strong><?= htmlspecialchars($s['cliente_nombre'] . ' ' . $s['cliente_apellido'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <span style="font-size: 0.75rem; color: var(--color-texto-apagado);"><?= htmlspecialchars($s['cliente_telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <?= htmlspecialchars(date('d/m/Y', strtotime($s['reserva_fecha'])), ENT_QUOTES, 'UTF-8') ?><br>
                                    <?= substr($s['reserva_hora_inicio'], 0, 5) ?> a <?= substr($s['reserva_hora_fin'], 0, 5) ?> hs<br>
                                    <span class="badge badge-neutro"><?= htmlspecialchars($s['patente'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <?php if ($s['tipo'] === 'cambio'): ?>
                                        <strong>Solicita:</strong><br>
                                        <?= htmlspecialchars(date('d/m/Y', strtotime($s['nueva_fecha'])), ENT_QUOTES, 'UTF-8') ?> a las <?= substr($s['nueva_hora'], 0, 5) ?> hs
                                    <?php else: ?>
                                        <span>Cancelacion de turno</span>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width: 250px;">
                                    <p style="font-size: 0.85rem; color: var(--color-texto-secundario); margin: 0; line-height: 1.4;">
                                        <?= htmlspecialchars($s['motivo'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                        <!-- Formulario Aprobar -->
                                        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/solicitudes/<?= (int)$s['id'] ?>/aprobar" method="POST" style="margin: 0;">
                                            <?= $csrfCampo ?>
                                            <button type="submit" class="btn btn-primario btn-sm" style="width: 100%;">Aprobar</button>
                                        </form>

                                        <!-- Boton para abrir el modal de rechazo -->
                                        <button type="button" class="btn btn-peligro btn-sm" style="width: 100%;"
                                                onclick="abrirModalRechazar(<?= (int)$s['id'] ?>, '<?= htmlspecialchars($s['cliente_nombre'] . ' ' . $s['cliente_apellido'], ENT_QUOTES, 'UTF-8') ?>');">
                                            Rechazar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Rechazar Solicitud -->
<dialog id="modal-rechazar-solicitud" style="border: 1px solid var(--color-borde-suave); border-radius: var(--radio-lg); background-color: var(--color-superficie); color: var(--color-texto); padding: 2rem; max-width: 520px; width: 90%;">
    <form id="form-rechazar-solicitud" method="POST" action="">
        <?= $csrfCampo ?>
        <h2 id="modal-rechazar-titulo" style="font-size: 1.25rem; font-weight: 700; margin-top: 0; margin-bottom: 0.5rem; color: var(--color-texto);">
            Rechazar Solicitud
        </h2>
        <p style="font-size: 0.85rem; color: var(--color-texto-apagado); margin-bottom: 1.25rem;">
            Indique el motivo del rechazo. Este mensaje sera enviado como notificacion al cliente correspondiente.
        </p>

        <div class="grupo-formulario">
            <label for="rechazar-motivo" class="label-formulario" style="color: var(--color-peligro);">Motivo del Rechazo (obligatorio) *</label>
            <textarea id="rechazar-motivo" name="motivo_rechazo" class="control-formulario" rows="3" required
                      placeholder="Ej. No hay disponibilidad en la fecha solicitada. Le sugerimos intentar otro horario." minlength="5"></textarea>
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
            <button type="button" class="btn btn-secundario" onclick="document.getElementById('modal-rechazar-solicitud').close();">Cancelar</button>
            <button type="submit" class="btn btn-peligro">Confirmar Rechazo</button>
        </div>
    </form>
</dialog>

<script>
function abrirModalRechazar(solicitudId, clienteNombre) {
    var form = document.getElementById('form-rechazar-solicitud');
    form.action = '<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/solicitudes/' + solicitudId + '/rechazar';
    document.getElementById('modal-rechazar-titulo').textContent = 'Rechazar Solicitud #' + solicitudId + ' - ' + clienteNombre;
    document.getElementById('rechazar-motivo').value = '';
    document.getElementById('modal-rechazar-solicitud').showModal();
}
</script>
