<?php
/**
 * WashFlow - Administracion de Modelos de Vehiculo (Requisito 3.2)
 * Sin emojis.
 */
?>
<div class="tarjeta">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Catalogo de Modelos de Vehiculos</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Administracion de marcas y modelos para el clasificador inteligente de categorias (Auto, Camioneta, 4x4, Moto).
            </p>
        </div>
        <div>
            <button type="button" class="btn btn-primario btn-sm" id="btn-nuevo-modelo">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Nuevo Modelo</span>
            </button>
        </div>
    </div>

    <div class="tarjeta-cuerpo">
        <!-- Formulario Colapsable de Alta -->
        <div id="seccion-alta-modelo" style="display: none; background: var(--color-superficie-alt); border: 1px solid var(--color-borde); border-radius: var(--radio-lg); padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1rem; color: var(--color-texto);">Registrar Nuevo Modelo</h3>
            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/modelos/crear" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <?= $csrfCampo ?>
                <div class="grupo-formulario" style="margin-bottom: 0;">
                    <label for="marca" class="label-formulario">Marca *:</label>
                    <input type="text" id="marca" name="marca" class="control-formulario" placeholder="Ej: Ford, Toyota, Peugeot" required>
                </div>
                <div class="grupo-formulario" style="margin-bottom: 0;">
                    <label for="modelo" class="label-formulario">Modelo *:</label>
                    <input type="text" id="modelo" name="modelo" class="control-formulario" placeholder="Ej: Ka, Hilux, 208" required>
                </div>
                <div class="grupo-formulario" style="margin-bottom: 0;">
                    <label for="tipo_vehiculo_id" class="label-formulario">Categoria *:</label>
                    <select id="tipo_vehiculo_id" name="tipo_vehiculo_id" class="control-formulario" required>
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre'], ENT_QUOTES, 'UTF-8') ?> (<?= $t['duracion_minutos'] ?> min)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primario" style="width: 100%;">Guardar Modelo</button>
                </div>
            </form>
        </div>

        <!-- Buscador -->
        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/modelos" method="GET" style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem; max-width: 500px;">
            <input type="text" name="q" class="control-formulario" placeholder="Buscar por marca o modelo..." value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn-secundario">Buscar</button>
            <?php if ($busqueda !== ''): ?>
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/modelos" class="btn btn-secundario">Limpiar</a>
            <?php endif; ?>
        </form>

        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Categoria Asignada</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($modelos)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--color-texto-apagado); padding: 2rem;">
                                No se encontraron modelos registrados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($modelos as $m): ?>
                            <tr>
                                <td><?= (int)$m['id'] ?></td>
                                <td><strong><?= htmlspecialchars($m['marca'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars($m['modelo'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge badge-activo"><?= htmlspecialchars($m['tipo_nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($m['activo'])): ?>
                                        <span class="badge badge-exito">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-peligro">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-secundario btn-sm btn-editar-modelo" 
                                            data-id="<?= $m['id'] ?>" 
                                            data-marca="<?= htmlspecialchars($m['marca'], ENT_QUOTES, 'UTF-8') ?>" 
                                            data-modelo="<?= htmlspecialchars($m['modelo'], ENT_QUOTES, 'UTF-8') ?>" 
                                            data-tipo="<?= $m['tipo_vehiculo_id'] ?>" 
                                            data-activo="<?= $m['activo'] ?>">
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edicion de Modelo -->
<div id="modal-editar-modelo" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--color-superficie); border: 1px solid var(--color-borde); border-radius: var(--radio-xl); padding: 2rem; width: 90%; max-width: 480px; box-shadow: var(--sombra-xl);">
        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 1.25rem; color: var(--color-texto);">Editar Modelo de Vehiculo</h3>
        <form id="form-editar-modelo" method="POST">
            <?= $csrfCampo ?>
            <div class="grupo-formulario">
                <label for="edit-marca" class="label-formulario">Marca:</label>
                <input type="text" id="edit-marca" name="marca" class="control-formulario" required>
            </div>
            <div class="grupo-formulario">
                <label for="edit-modelo" class="label-formulario">Modelo:</label>
                <input type="text" id="edit-modelo" name="modelo" class="control-formulario" required>
            </div>
            <div class="grupo-formulario">
                <label for="edit-tipo" class="label-formulario">Categoria:</label>
                <select id="edit-tipo" name="tipo_vehiculo_id" class="control-formulario" required>
                    <?php foreach ($tipos as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grupo-formulario" style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" id="edit-activo" name="activo" value="1" style="width: 18px; height: 18px;">
                <label for="edit-activo" class="label-formulario" style="margin-bottom: 0;">Activo en el buscador</label>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secundario" id="btn-cerrar-modal">Cancelar</button>
                <button type="submit" class="btn btn-primario">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnNuevo = document.getElementById('btn-nuevo-modelo');
        const seccionAlta = document.getElementById('seccion-alta-modelo');
        if (btnNuevo && seccionAlta) {
            btnNuevo.addEventListener('click', function() {
                seccionAlta.style.display = (seccionAlta.style.display === 'block') ? 'none' : 'block';
            });
        }

        const modal = document.getElementById('modal-editar-modelo');
        const btnCerrar = document.getElementById('btn-cerrar-modal');
        const formEditar = document.getElementById('form-editar-modelo');
        const editMarca = document.getElementById('edit-marca');
        const editModelo = document.getElementById('edit-modelo');
        const editTipo = document.getElementById('edit-tipo');
        const editActivo = document.getElementById('edit-activo');

        document.querySelectorAll('.btn-editar-modelo').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                editMarca.value = this.getAttribute('data-marca');
                editModelo.value = this.getAttribute('data-modelo');
                editTipo.value = this.getAttribute('data-tipo');
                editActivo.checked = (this.getAttribute('data-activo') === '1');
                formEditar.action = '<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/modelos/' + id + '/actualizar';
                modal.style.display = 'flex';
            });
        });

        if (btnCerrar && modal) {
            btnCerrar.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }
    });
</script>
