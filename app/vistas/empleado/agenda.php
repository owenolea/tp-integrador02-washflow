<?php
/**
 * WashFlow - Agenda y Calendario Diario de Reservas y Ocupacion
 */
?>
<div class="tarjeta">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Agenda Diaria y Control de Capacidad</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Visualice la ocupacion franja por franja (maximo 3 vehiculos en simultaneo).
            </p>
        </div>

        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/agenda" method="GET" style="display: flex; gap: 0.5rem; align-items: center; margin: 0;">
            <label for="fecha" class="label-formulario" style="margin: 0; white-space: nowrap;">Fecha:</label>
            <input type="date" id="fecha" name="fecha" class="control-formulario" value="<?= htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8') ?>" onchange="this.form.submit();" style="width: auto;">
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/agenda?fecha=<?= date('Y-m-d') ?>" class="btn btn-secundario btn-sm">Hoy</a>
        </form>
    </div>

    <div class="tarjeta-cuerpo">
        <!-- Mapa de Franjas y Ocupacion -->
        <h2 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--color-texto);">
            Ocupacion por Franja Horaria — <?= htmlspecialchars(date('d/m/Y', strtotime($fecha)), ENT_QUOTES, 'UTF-8') ?>
        </h2>

        <?php if (!empty($mapa['es_dia_atencion'])): ?>
            <div class="grilla-franjas" style="margin-bottom: 2rem;">
                <?php foreach ($mapa['franjas'] as $f): ?>
                    <?php
                    $ocupacion = (int)$f['ocupacion'];
                    $claseCupo = "cupo-{$ocupacion}";
                    $pct = ($ocupacion / 3) * 100;
                    ?>
                    <div class="franja-card" style="cursor: default;">
                        <div class="franja-hora"><?= $f['hora_inicio'] ?> a <?= $f['hora_fin'] ?></div>
                        <div class="franja-ocupacion">
                            <span><?= $ocupacion ?> de 3 lugares</span>
                            <span class="badge <?= $ocupacion < 3 ? 'badge-exito' : 'badge-peligro' ?>">
                                <?= $ocupacion < 3 ? 'Cupo disponible' : 'Capacidad maxima' ?>
                            </span>
                        </div>
                        <div class="barra-cupo">
                            <div class="barra-cupo-relleno <?= $claseCupo ?>" style="width: <?= $pct ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alerta alerta-aviso" style="margin-bottom: 2rem;">
                <?= htmlspecialchars($mapa['mensaje'] ?? 'Dia cerrado.', ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- Listado Detallado de Turnos y Lavados del Dia -->
        <h2 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--color-texto);">
            Detalle de Turnos y Lavados del Dia
        </h2>

        <?php if (empty($reservas) && empty($lavados)): ?>
            <div style="padding: 2.5rem; text-align: center; color: var(--color-texto-apagado); background-color: var(--color-superficie-alt); border-radius: var(--radio-md);">
                <p>No hay actividad registrada para la fecha seleccionada.</p>
            </div>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Horario</th>
                            <th>Tipo / Origen</th>
                            <th>Cliente</th>
                            <th>Vehiculo</th>
                            <th>Servicio y Precio</th>
                            <th>Estado Turno</th>
                            <th>Estado Lavado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas as $r): ?>
                            <tr>
                                <td><strong><?= substr((string)($r['hora_inicio'] ?? ''), 0, 5) ?> - <?= substr((string)($r['hora_fin'] ?? ''), 0, 5) ?></strong></td>
                                <td><span class="badge badge-info">Con Reserva #<?= (int)($r['id'] ?? 0) ?></span></td>
                                <td>
                                    <?= htmlspecialchars(trim(($r['cliente_nombre'] ?? '') . ' ' . ($r['cliente_apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?><br>
                                    <span style="font-size: 0.75rem; color: var(--color-texto-apagado);"><?= htmlspecialchars((string)($r['cliente_telefono'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars(trim(($r['marca'] ?? '') . ' ' . ($r['modelo'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <span class="badge badge-neutro"><?= htmlspecialchars((string)(!empty($r['patente']) ? $r['patente'] : 'S/Patente'), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if (!empty($r['tipo_a_confirmar'])): ?>
                                        <span class="badge badge-alerta" style="font-size: 0.68rem; margin-top: 0.2rem;" title="El cliente indico no estar seguro de su categoria">A confirmar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars((string)($r['servicio_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?><br>
                                    <strong>$<?= number_format((float)($r['precio_acordado'] ?? 0), 0, ',', '.') ?></strong>
                                    <span style="font-size: 0.75rem; color: var(--color-texto-apagado);"> (<?= htmlspecialchars((string)($r['tipo_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)</span>
                                </td>
                                <td>
                                    <?php if (($r['estado'] ?? '') === 'reservada'): ?>
                                        <span class="badge badge-info">Reservada</span>
                                    <?php elseif (($r['estado'] ?? '') === 'cancelada'): ?>
                                        <span class="badge badge-peligro">Cancelada</span>
                                    <?php else: ?>
                                        <span class="badge badge-alerta">Ausente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($r['lavado_estado'])): ?>
                                        <span class="badge <?= $r['lavado_estado'] === 'terminado' ? 'badge-exito' : 'badge-alerta' ?>">
                                            <?= ucfirst((string)$r['lavado_estado']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-neutro">No ingresado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                        <?php if (($r['estado'] ?? '') === 'reservada' && empty($r['lavado_id'])): ?>
                                            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/llegada" method="POST">
                                                <?= $csrfCampo ?>
                                                <input type="hidden" name="reserva_id" value="<?= (int)($r['id'] ?? 0) ?>">
                                                <button type="submit" class="btn btn-acento btn-sm">Llegada</button>
                                            </form>
                                            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/ausencia" method="POST" onsubmit="return confirm('Confirma registrar la ausencia del cliente para el turno #<?= (int)($r['id'] ?? 0) ?>?');">
                                                <?= $csrfCampo ?>
                                                <input type="hidden" name="reserva_id" value="<?= (int)($r['id'] ?? 0) ?>">
                                                <button type="submit" class="btn btn-peligro btn-sm">Ausencia</button>
                                            </form>
                                        <?php elseif (!empty($r['lavado_id']) && ($r['lavado_estado'] ?? '') === 'pendiente'): ?>
                                            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/terminar-lavado" method="POST">
                                                <?= $csrfCampo ?>
                                                <input type="hidden" name="lavado_id" value="<?= (int)$r['lavado_id'] ?>">
                                                <button type="submit" class="btn btn-primario btn-sm">Terminar</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (($r['estado'] ?? '') === 'reservada'): ?>
                                            <button type="button" class="btn btn-secundario btn-sm"
                                                    onclick="abrirModalCategoria(<?= (int)($r['id'] ?? 0) ?>, <?= (int)($r['tipo_vehiculo_id'] ?? 0) ?>, '<?= htmlspecialchars((string)($r['tipo_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars((string)($r['patente'] ?? ''), ENT_QUOTES, 'UTF-8') ?>', <?= (float)($r['precio_acordado'] ?? 0) ?>, <?= !empty($r['tipo_a_confirmar']) ? 'true' : 'false' ?>);">
                                                Categoria
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Lavados sin reserva -->
                        <?php
                        $lavadosSinReserva = array_filter($lavados, fn($l) => empty($l['reserva_id']));
                        ?>
                        <?php foreach ($lavadosSinReserva as $ls): ?>
                            <tr style="background-color: var(--color-superficie-alt);">
                                <td><strong><?= substr((string)($ls['hora_llegada'] ?? ''), 0, 5) ?> hs</strong></td>
                                <td><span class="badge badge-alerta">Sin Reserva #<?= (int)($ls['id'] ?? 0) ?></span></td>
                                <td>
                                    <?= htmlspecialchars(trim(($ls['cliente_nombre'] ?? '') . ' ' . ($ls['cliente_apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?><br>
                                    <span class="badge badge-neutro">Ocasional</span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars(trim(($ls['marca'] ?? '') . ' ' . ($ls['modelo'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <span class="badge badge-neutro"><?= htmlspecialchars((string)($ls['patente'] ?? 'S/Patente'), ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <?= htmlspecialchars((string)($ls['servicio_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?><br>
                                    <strong>$<?= number_format((float)($ls['precio'] ?? 0), 0, ',', '.') ?></strong>
                                </td>
                                <td><span class="badge badge-neutro">Directo</span></td>
                                <td>
                                    <span class="badge <?= ($ls['estado'] ?? '') === 'terminado' ? 'badge-exito' : 'badge-alerta' ?>">
                                        <?= ucfirst((string)($ls['estado'] ?? '')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($ls['estado'] ?? '') === 'pendiente'): ?>
                                        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/terminar-lavado" method="POST">
                                            <?= $csrfCampo ?>
                                            <input type="hidden" name="lavado_id" value="<?= (int)($ls['id'] ?? 0) ?>">
                                            <button type="submit" class="btn btn-primario btn-sm">Terminar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Confirmar / Corregir Categoria de Vehiculo (Seccion 13.2 y 14) -->
<dialog id="modal-corregir-categoria" style="border: 1px solid var(--color-borde-suave); border-radius: var(--radio-lg); background-color: var(--color-superficie); color: var(--color-texto); padding: 2rem; max-width: 520px; width: 90%;">
    <form id="form-corregir-categoria" method="POST" action="">
        <?= $csrfCampo ?>
        <h2 id="modal-cat-titulo" style="font-size: 1.25rem; font-weight: 700; margin-top: 0; margin-bottom: 0.5rem; color: var(--color-texto);">
            Confirmar o Corregir Categoria
        </h2>
        <p style="font-size: 0.85rem; color: var(--color-texto-apagado); margin-bottom: 1.25rem;">
            Verifique la categoria real del vehiculo al ingresar al box. Si la categoria cambia, el sistema recalculara la tarifa y notificara a administracion.
        </p>

        <div class="grupo-formulario">
            <label for="cat-tipo" class="label-formulario">Categoria del Vehiculo *</label>
            <select id="cat-tipo" name="tipo_vehiculo_id" class="control-formulario" required onchange="verificarCambioTarifa();">
                <?php if (!empty($tipos)): ?>
                    <?php foreach ($tipos as $t): ?>
                        <option value="<?= (int)$t['id'] ?>" data-duracion="<?= (int)$t['duracion_minutos'] ?>">
                            <?= htmlspecialchars($t['nombre'], ENT_QUOTES, 'UTF-8') ?> (<?= (int)$t['duracion_minutos'] ?> min)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="grupo-formulario">
            <label for="cat-patente" class="label-formulario">Patente del Vehiculo</label>
            <input type="text" id="cat-patente" name="patente" class="control-formulario input-patente" placeholder="Ej. AB123CD o ABC123" maxlength="7" pattern="[a-zA-Z0-9]{6,7}" title="Patente argentina valida (6 o 7 caracteres alfanumericos)" style="text-transform: uppercase;">
        </div>

        <div class="grupo-formulario" id="grupo-aviso-cliente" style="background-color: var(--color-superficie-alt); padding: 1rem; border-radius: var(--radio-md); border: 1px solid var(--color-borde-suave);">
            <label style="display: flex; gap: 0.5rem; align-items: flex-start; cursor: pointer; font-size: 0.85rem; color: var(--color-texto);">
                <input type="checkbox" id="cat-aviso" name="aviso_cliente_confirmado" value="1" style="margin-top: 0.2rem;">
                <span><strong>Confirmo que le informe la diferencia de tarifa al cliente *</strong><br><span style="color: var(--color-texto-apagado);">Requerido obligatoriamente si el cambio de categoria modifica el precio pactado.</span></span>
            </label>
        </div>

        <div class="grupo-formulario">
            <label for="cat-motivo" class="label-formulario">Observaciones / Motivo (opcional)</label>
            <textarea id="cat-motivo" name="motivo" class="control-formulario" rows="2" placeholder="Ej. Cliente trajo camioneta 4x4 en lugar de sedan mediano."></textarea>
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
            <button type="button" class="btn btn-secundario" onclick="document.getElementById('modal-corregir-categoria').close();">Cancelar</button>
            <button type="submit" class="btn btn-primario">Confirmar Categoria</button>
        </div>
    </form>
</dialog>

<script>
var tipoOriginalId = 0;

function abrirModalCategoria(reservaId, tipoId, tipoNombre, patente, precio, tipoAConfirmar) {
    tipoOriginalId = tipoId;
    var form = document.getElementById('form-corregir-categoria');
    form.action = '<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/reservas/' + reservaId + '/corregir-categoria';
    document.getElementById('modal-cat-titulo').textContent = 'Categoria Turno #' + reservaId + (tipoAConfirmar ? ' (Pendiente de confirmacion)' : '');
    document.getElementById('cat-tipo').value = tipoId;
    document.getElementById('cat-patente').value = patente;
    document.getElementById('cat-aviso').checked = false;
    document.getElementById('cat-motivo').value = '';
    verificarCambioTarifa();
    document.getElementById('modal-corregir-categoria').showModal();
}

function verificarCambioTarifa() {
    var sel = document.getElementById('cat-tipo');
    var grupoAviso = document.getElementById('grupo-aviso-cliente');
    var nuevoId = parseInt(sel.value, 10);
    if (nuevoId !== tipoOriginalId) {
        grupoAviso.style.display = 'block';
    } else {
        grupoAviso.style.display = 'none';
    }
}
</script>

