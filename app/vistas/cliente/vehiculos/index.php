<?php
/**
 * WashFlow - Gestion Unificada de Vehiculos del Cliente
 * Incluye listado y modales nativos para Crear y Editar en una sola vista.
 */
$tipos = $tipos ?? [];
?>
<div class="tarjeta">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Mis Vehiculos Registrados</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Administre sus vehiculos para agilizar la solicitud de turnos de lavado.
            </p>
        </div>
        <button type="button" class="btn btn-primario btn-sm" onclick="document.getElementById('modal-nuevo-vehiculo').showModal();">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Nuevo Vehiculo</span>
        </button>
    </div>

    <div class="tarjeta-cuerpo" style="padding: 0;">
        <?php if (empty($vehiculos)): ?>
            <div style="padding: 3rem; text-align: center; color: var(--color-texto-apagado);">
                <p>No tiene vehiculos dados de alta actualmente.</p>
                <button type="button" class="btn btn-acento btn-sm" style="margin-top: 1rem;" onclick="document.getElementById('modal-nuevo-vehiculo').showModal();">
                    Registrar mi primer vehiculo
                </button>
            </div>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Patente</th>
                            <th>Vehiculo (Marca y Modelo)</th>
                            <th>Categoria / Tipo</th>
                            <th>Duracion Estimada</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehiculos as $v): ?>
                            <?php
                            $patenteTexto = !empty($v['patente']) ? (string)$v['patente'] : 'S/Patente';
                            $marcaTexto = (string)($v['marca'] ?? '');
                            $modeloTexto = (string)($v['modelo'] ?? '');
                            $tipoNombre = (string)($v['tipo_nombre'] ?? 'Vehiculo');
                            ?>
                            <tr>
                                <td>
                                    <span class="badge badge-neutro" style="font-size: 0.9rem; font-weight: 700; letter-spacing: 0.05em;">
                                        <?= htmlspecialchars($patenteTexto, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($marcaTexto, ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?= htmlspecialchars($modeloTexto, ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td><?= htmlspecialchars($tipoNombre, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int)($v['duracion_minutos'] ?? 60) ?> minutos</td>
                                <td>
                                    <?php if ((int)($v['activo'] ?? 1) === 1): ?>
                                        <span class="badge badge-exito">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-neutro">Dado de baja</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <?php if ((int)($v['activo'] ?? 1) === 1): ?>
                                            <button type="button" class="btn btn-secundario btn-sm btn-abrir-editar"
                                                    data-id="<?= (int)$v['id'] ?>"
                                                    data-patente="<?= htmlspecialchars($patenteTexto, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-marca="<?= htmlspecialchars($marcaTexto, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-modelo="<?= htmlspecialchars($modeloTexto, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-tipo-id="<?= (int)($v['tipo_vehiculo_id'] ?? 1) ?>">
                                                Editar
                                            </button>
                                            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/vehiculos/<?= (int)$v['id'] ?>/desactivar" method="POST" onsubmit="return confirm('¿Confirma que desea dar de baja este vehiculo?');">
                                                <?= $csrfCampo ?>
                                                <button type="submit" class="btn btn-peligro btn-sm">Baja</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="font-size: 0.8rem; color: var(--color-texto-apagado);">Inactivo</span>
                                        <?php endif; ?>
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

<!-- MODAL NATIVO: REGISTRAR NUEVO VEHICULO -->
<dialog id="modal-nuevo-vehiculo" class="dialog-modal" style="max-width: 520px; width: 90%; padding: 0; border: 1px solid var(--color-borde); border-radius: var(--radio-lg); background: var(--color-superficie); color: var(--color-texto); box-shadow: var(--sombra-lg);">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-borde); display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0;">Registrar Nuevo Vehiculo</h2>
        <button type="button" onclick="document.getElementById('modal-nuevo-vehiculo').close();" class="btn-cerrar" style="background: none; border: none; color: var(--color-texto-apagado); cursor: pointer; font-size: 1.25rem;">✕</button>
    </div>
    <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/vehiculos/crear" method="POST" style="padding: 1.5rem;">
        <?= $csrfCampo ?>
        <div class="grupo-formulario">
            <label for="nuevo_tipo_vehiculo_id" class="label-formulario">Tipo de Vehiculo *</label>
            <select id="nuevo_tipo_vehiculo_id" name="tipo_vehiculo_id" class="control-formulario" required>
                <option value="">Seleccione el tipo...</option>
                <?php foreach ($tipos as $t): ?>
                    <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['nombre'], ENT_QUOTES, 'UTF-8') ?> (<?= (int)$t['duracion_minutos'] ?> min)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="grupo-formulario">
            <label for="nuevo_patente" class="label-formulario">Patente / Dominio *</label>
            <input type="text" id="nuevo_patente" name="patente" class="control-formulario input-patente" required placeholder="Ej. AB123CD o ABC123" maxlength="7" pattern="[a-zA-Z0-9]{6,7}" title="Patente argentina valida (6 o 7 caracteres alfanumericos)" style="text-transform: uppercase;">
            <span class="texto-ayuda">Formato tradicional (ABC123) o Mercosur (AB123CD / A123BCD).</span>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="grupo-formulario">
                <label for="nuevo_marca" class="label-formulario">Marca *</label>
                <input type="text" id="nuevo_marca" name="marca" class="control-formulario" required placeholder="Ej. Toyota, Ford">
            </div>
            <div class="grupo-formulario">
                <label for="nuevo_modelo" class="label-formulario">Modelo *</label>
                <input type="text" id="nuevo_modelo" name="modelo" class="control-formulario" required placeholder="Ej. Corolla, Focus">
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--color-borde);">
            <button type="button" class="btn btn-secundario" onclick="document.getElementById('modal-nuevo-vehiculo').close();">Cancelar</button>
            <button type="submit" class="btn btn-primario">Registrar Vehiculo</button>
        </div>
    </form>
</dialog>

<!-- MODAL NATIVO: EDITAR VEHICULO -->
<dialog id="modal-editar-vehiculo" class="dialog-modal" style="max-width: 520px; width: 90%; padding: 0; border: 1px solid var(--color-borde); border-radius: var(--radio-lg); background: var(--color-superficie); color: var(--color-texto); box-shadow: var(--sombra-lg);">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-borde); display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0;">Editar Datos del Vehiculo</h2>
        <button type="button" onclick="document.getElementById('modal-editar-vehiculo').close();" class="btn-cerrar" style="background: none; border: none; color: var(--color-texto-apagado); cursor: pointer; font-size: 1.25rem;">✕</button>
    </div>
    <form id="form-editar-vehiculo" action="" method="POST" style="padding: 1.5rem;">
        <?= $csrfCampo ?>
        <div class="grupo-formulario">
            <label for="edit_tipo_vehiculo_id" class="label-formulario">Tipo de Vehiculo *</label>
            <select id="edit_tipo_vehiculo_id" name="tipo_vehiculo_id" class="control-formulario" required>
                <?php foreach ($tipos as $t): ?>
                    <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['nombre'], ENT_QUOTES, 'UTF-8') ?> (<?= (int)$t['duracion_minutos'] ?> min)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="grupo-formulario">
            <label for="edit_patente" class="label-formulario">Patente / Dominio *</label>
            <input type="text" id="edit_patente" name="patente" class="control-formulario input-patente" required maxlength="7" pattern="[a-zA-Z0-9]{6,7}" title="Patente argentina valida (6 o 7 caracteres alfanumericos)" style="text-transform: uppercase;">
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="grupo-formulario">
                <label for="edit_marca" class="label-formulario">Marca *</label>
                <input type="text" id="edit_marca" name="marca" class="control-formulario" required>
            </div>
            <div class="grupo-formulario">
                <label for="edit_modelo" class="label-formulario">Modelo *</label>
                <input type="text" id="edit_modelo" name="modelo" class="control-formulario" required>
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--color-borde);">
            <button type="button" class="btn btn-secundario" onclick="document.getElementById('modal-editar-vehiculo').close();">Cancelar</button>
            <button type="submit" class="btn btn-primario">Guardar Cambios</button>
        </div>
    </form>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const urlBase = '<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>';
    const formEditar = document.getElementById('form-editar-vehiculo');
    const modalEditar = document.getElementById('modal-editar-vehiculo');
    const modalNuevo = document.getElementById('modal-nuevo-vehiculo');

    // Botones de editar en la tabla
    document.querySelectorAll('.btn-abrir-editar').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            formEditar.action = `${urlBase}/cliente/vehiculos/${id}/editar`;
            document.getElementById('edit_patente').value = btn.dataset.patente || '';
            document.getElementById('edit_marca').value = btn.dataset.marca || '';
            document.getElementById('edit_modelo').value = btn.dataset.modelo || '';
            document.getElementById('edit_tipo_vehiculo_id').value = btn.dataset.tipoId || '';
            modalEditar.showModal();
        });
    });

    // Auto-apertura si viene por URL con query params (?crear=1 o ?editar=ID)
    const params = new URLSearchParams(window.location.search);
    if (params.get('crear') === '1' && modalNuevo) {
        modalNuevo.showModal();
    } else if (params.get('editar')) {
        const id = params.get('editar');
        const targetBtn = document.querySelector(`.btn-abrir-editar[data-id="${id}"]`);
        if (targetBtn) targetBtn.click();
    }
});
</script>
