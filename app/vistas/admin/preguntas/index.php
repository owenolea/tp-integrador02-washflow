<?php
/**
 * WashFlow - Administracion de Preguntas Frecuentes (FAQ - Requisito 3.5)
 * Sin emojis.
 */
?>
<div class="tarjeta">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Preguntas Frecuentes (FAQ)</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Administracion de dudas y respuestas mostradas en el acordeon de la pagina de inicio sin tocar codigo.
            </p>
        </div>
        <div>
            <button type="button" class="btn btn-primario btn-sm" id="btn-nueva-pregunta">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Nueva Pregunta</span>
            </button>
        </div>
    </div>

    <div class="tarjeta-cuerpo">
        <!-- Formulario Colapsable de Alta -->
        <div id="seccion-alta-pregunta" style="display: none; background: var(--color-superficie-alt); border: 1px solid var(--color-borde); border-radius: var(--radio-lg); padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1rem; color: var(--color-texto);">Agregar Pregunta Frecuente</h3>
            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/preguntas/crear" method="POST">
                <?= $csrfCampo ?>
                <div class="grupo-formulario">
                    <label for="pregunta" class="label-formulario">Pregunta *:</label>
                    <input type="text" id="pregunta" name="pregunta" class="control-formulario" placeholder="Ej: Cuanto tiempo dura el lavado?" required>
                </div>
                <div class="grupo-formulario">
                    <label for="respuesta" class="label-formulario">Respuesta *:</label>
                    <textarea id="respuesta" name="respuesta" class="control-formulario" rows="3" placeholder="Redacta una respuesta clara y concisa..." required></textarea>
                </div>
                <div style="display: flex; gap: 1rem; align-items: flex-end;">
                    <div class="grupo-formulario" style="margin-bottom: 0; width: 140px;">
                        <label for="orden" class="label-formulario">Orden (posicion):</label>
                        <input type="number" id="orden" name="orden" class="control-formulario" value="0">
                    </div>
                    <button type="submit" class="btn btn-primario">Guardar Pregunta</button>
                </div>
            </form>
        </div>

        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th style="width: 60px;">Orden</th>
                        <th>Pregunta</th>
                        <th>Respuesta</th>
                        <th style="width: 100px;">Estado</th>
                        <th style="width: 160px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($preguntas)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--color-texto-apagado); padding: 2rem;">
                                No hay preguntas frecuentes registradas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($preguntas as $p): ?>
                            <tr>
                                <td><strong><?= (int)$p['orden'] ?></strong></td>
                                <td><strong><?= htmlspecialchars($p['pregunta'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td style="color: var(--color-texto-secundario); font-size: 0.9rem;">
                                    <?= htmlspecialchars(mb_strimwidth($p['respuesta'], 0, 90, '...'), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td>
                                    <?php if (!empty($p['activo'])): ?>
                                        <span class="badge badge-exito">Visible</span>
                                    <?php else: ?>
                                        <span class="badge badge-peligro">Oculta</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button type="button" class="btn btn-secundario btn-sm btn-editar-faq"
                                                data-id="<?= $p['id'] ?>"
                                                data-pregunta="<?= htmlspecialchars($p['pregunta'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-respuesta="<?= htmlspecialchars($p['respuesta'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-orden="<?= $p['orden'] ?>"
                                                data-activo="<?= $p['activo'] ?>">
                                            Editar
                                        </button>
                                        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/preguntas/<?= $p['id'] ?>/eliminar" method="POST" onsubmit="return confirm('Seguro que deseas eliminar esta pregunta?');" style="margin: 0;">
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

<!-- Modal Edicion FAQ -->
<div id="modal-editar-faq" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--color-superficie); border: 1px solid var(--color-borde); border-radius: var(--radio-xl); padding: 2rem; width: 90%; max-width: 520px; box-shadow: var(--sombra-xl);">
        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 1.25rem; color: var(--color-texto);">Editar Pregunta Frecuente</h3>
        <form id="form-editar-faq" method="POST">
            <?= $csrfCampo ?>
            <div class="grupo-formulario">
                <label for="edit-pregunta" class="label-formulario">Pregunta:</label>
                <input type="text" id="edit-pregunta" name="pregunta" class="control-formulario" required>
            </div>
            <div class="grupo-formulario">
                <label for="edit-respuesta" class="label-formulario">Respuesta:</label>
                <textarea id="edit-respuesta" name="respuesta" class="control-formulario" rows="4" required></textarea>
            </div>
            <div class="grupo-formulario" style="display: flex; gap: 1.5rem; align-items: center;">
                <div>
                    <label for="edit-orden" class="label-formulario">Orden de aparicion:</label>
                    <input type="number" id="edit-orden" name="orden" class="control-formulario" style="width: 100px;">
                </div>
                <div style="margin-top: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="edit-activo" name="activo" value="1" style="width: 18px; height: 18px;">
                    <label for="edit-activo" class="label-formulario" style="margin-bottom: 0;">Visible en la web</label>
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secundario" id="btn-cerrar-modal-faq">Cancelar</button>
                <button type="submit" class="btn btn-primario">Actualizar Pregunta</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnNueva = document.getElementById('btn-nueva-pregunta');
        const seccionAlta = document.getElementById('seccion-alta-pregunta');
        if (btnNueva && seccionAlta) {
            btnNueva.addEventListener('click', function() {
                seccionAlta.style.display = (seccionAlta.style.display === 'block') ? 'none' : 'block';
            });
        }

        const modal = document.getElementById('modal-editar-faq');
        const btnCerrar = document.getElementById('btn-cerrar-modal-faq');
        const formEditar = document.getElementById('form-editar-faq');
        const editPregunta = document.getElementById('edit-pregunta');
        const editRespuesta = document.getElementById('edit-respuesta');
        const editOrden = document.getElementById('edit-orden');
        const editActivo = document.getElementById('edit-activo');

        document.querySelectorAll('.btn-editar-faq').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                editPregunta.value = this.getAttribute('data-pregunta');
                editRespuesta.value = this.getAttribute('data-respuesta');
                editOrden.value = this.getAttribute('data-orden');
                editActivo.checked = (this.getAttribute('data-activo') === '1');
                formEditar.action = '<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/preguntas/' + id + '/actualizar';
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
