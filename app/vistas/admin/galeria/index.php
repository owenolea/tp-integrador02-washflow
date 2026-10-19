<?php
/**
 * WashFlow - Administracion de Galeria de Trabajos (Requisito 3.4)
 * Sin emojis.
 */
?>
<div class="tarjeta">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Galeria de Trabajos y Fotografias</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Administracion de fotos de demostracion exhibidas en la portada de WashFlow.
            </p>
        </div>
        <div>
            <button type="button" class="btn btn-primario btn-sm" id="btn-nueva-foto">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Nueva Imagen</span>
            </button>
        </div>
    </div>

    <div class="tarjeta-cuerpo">
        <!-- Formulario Colapsable de Alta -->
        <div id="seccion-alta-foto" style="display: none; background: var(--color-superficie-alt); border: 1px solid var(--color-borde); border-radius: var(--radio-lg); padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1rem; color: var(--color-texto);">Agregar Fotografia a la Galeria</h3>
            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/galeria/crear" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; align-items: end;">
                <?= $csrfCampo ?>
                <div class="grupo-formulario" style="margin-bottom: 0;">
                    <label for="titulo" class="label-formulario">Titulo del Trabajo *:</label>
                    <input type="text" id="titulo" name="titulo" class="control-formulario" placeholder="Ej: Tratamiento acrilico Ford Ranger" required>
                </div>
                <div class="grupo-formulario" style="margin-bottom: 0;">
                    <label for="descripcion" class="label-formulario">Descripcion:</label>
                    <input type="text" id="descripcion" name="descripcion" class="control-formulario" placeholder="Ej: Brillo profundo y proteccion hidrofobica">
                </div>
                <div class="grupo-formulario" style="margin-bottom: 0;">
                    <label for="imagen_url" class="label-formulario">URL de la Imagen *:</label>
                    <input type="text" id="imagen_url" name="imagen_url" class="control-formulario" placeholder="https://ejemplo.com/foto.jpg" required>
                </div>
                <div class="grupo-formulario" style="margin-bottom: 0; width: 140px;">
                    <label for="orden" class="label-formulario">Orden:</label>
                    <input type="number" id="orden" name="orden" class="control-formulario" value="0">
                </div>
                <div>
                    <button type="submit" class="btn btn-primario" style="width: 100%;">Guardar Imagen</button>
                </div>
            </form>
        </div>

        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th style="width: 80px;">Miniatura</th>
                        <th>Titulo</th>
                        <th>Descripcion</th>
                        <th style="width: 60px;">Orden</th>
                        <th style="width: 100px;">Estado</th>
                        <th style="width: 160px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--color-texto-apagado); padding: 2rem;">
                                No hay fotografias cargadas en la galeria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($item['imagen_url'], ENT_QUOTES, 'UTF-8') ?>" alt="" style="width: 60px; height: 42px; object-fit: cover; border-radius: var(--radio-sm); border: 1px solid var(--color-borde);">
                                </td>
                                <td><strong><?= htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td style="color: var(--color-texto-secundario); font-size: 0.875rem;">
                                    <?= htmlspecialchars($item['descripcion'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td><?= (int)$item['orden'] ?></td>
                                <td>
                                    <?php if (!empty($item['activo'])): ?>
                                        <span class="badge badge-exito">Visible</span>
                                    <?php else: ?>
                                        <span class="badge badge-peligro">Oculta</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button type="button" class="btn btn-secundario btn-sm btn-editar-galeria"
                                                data-id="<?= $item['id'] ?>"
                                                data-titulo="<?= htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-descripcion="<?= htmlspecialchars($item['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                data-url="<?= htmlspecialchars($item['imagen_url'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-categoria="<?= htmlspecialchars($item['categoria'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-orden="<?= $item['orden'] ?>"
                                                data-activo="<?= $item['activo'] ?>">
                                            Editar
                                        </button>
                                        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/galeria/<?= $item['id'] ?>/eliminar" method="POST" onsubmit="return confirm('Deseas eliminar esta foto de la galeria?');" style="margin: 0;">
                                            <?= $csrfCampo ?>
                                            <button type="submit" class="btn btn-peligro btn-sm">Borrar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edicion Galeria -->
<div id="modal-editar-galeria" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--color-superficie); border: 1px solid var(--color-borde); border-radius: var(--radio-xl); padding: 2rem; width: 90%; max-width: 520px; box-shadow: var(--sombra-xl);">
        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 1.25rem; color: var(--color-texto);">Editar Fotografia</h3>
        <form id="form-editar-galeria" method="POST">
            <?= $csrfCampo ?>
            <div class="grupo-formulario">
                <label for="edit-titulo" class="label-formulario">Titulo:</label>
                <input type="text" id="edit-titulo" name="titulo" class="control-formulario" required>
            </div>
            <div class="grupo-formulario">
                <label for="edit-descripcion" class="label-formulario">Descripcion:</label>
                <input type="text" id="edit-descripcion" name="descripcion" class="control-formulario">
            </div>
            <div class="grupo-formulario">
                <label for="edit-imagen-url" class="label-formulario">URL de la Imagen:</label>
                <input type="text" id="edit-imagen-url" name="imagen_url" class="control-formulario" required>
            </div>
            <div class="grupo-formulario" style="display: flex; gap: 1.5rem; align-items: center;">
                <div>
                    <label for="edit-orden-foto" class="label-formulario">Orden:</label>
                    <input type="number" id="edit-orden-foto" name="orden" class="control-formulario" style="width: 100px;">
                </div>
                <div style="margin-top: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="edit-activo-foto" name="activo" value="1" style="width: 18px; height: 18px;">
                    <label for="edit-activo-foto" class="label-formulario" style="margin-bottom: 0;">Visible en la portada</label>
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secundario" id="btn-cerrar-modal-galeria">Cancelar</button>
                <button type="submit" class="btn btn-primario">Actualizar Imagen</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnNueva = document.getElementById('btn-nueva-foto');
        const seccionAlta = document.getElementById('seccion-alta-foto');
        if (btnNueva && seccionAlta) {
            btnNueva.addEventListener('click', function() {
                seccionAlta.style.display = (seccionAlta.style.display === 'block') ? 'none' : 'block';
            });
        }

        const modal = document.getElementById('modal-editar-galeria');
        const btnCerrar = document.getElementById('btn-cerrar-modal-galeria');
        const formEditar = document.getElementById('form-editar-galeria');
        const editTitulo = document.getElementById('edit-titulo');
        const editDescripcion = document.getElementById('edit-descripcion');
        const editUrl = document.getElementById('edit-imagen-url');
        const editOrden = document.getElementById('edit-orden-foto');
        const editActivo = document.getElementById('edit-activo-foto');

        document.querySelectorAll('.btn-editar-galeria').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                editTitulo.value = this.getAttribute('data-titulo');
                editDescripcion.value = this.getAttribute('data-descripcion');
                editUrl.value = this.getAttribute('data-url');
                editOrden.value = this.getAttribute('data-orden');
                editActivo.checked = (this.getAttribute('data-activo') === '1');
                formEditar.action = '<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/galeria/' + id + '/actualizar';
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
