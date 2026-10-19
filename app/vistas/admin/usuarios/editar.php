<?php
/**
 * WashFlow - Edicion de Usuario por Administrador
 */
?>
<div class="tarjeta" style="max-width: 640px; margin: 0 auto;">
    <div class="tarjeta-header">
        <h1 class="tarjeta-titulo">Editar Usuario #<?= (int)$usuario['id'] ?></h1>
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios" class="btn btn-secundario btn-sm">
            Volver
        </a>
    </div>

    <div class="tarjeta-cuerpo">
        <?php if (!empty($errorGeneral)): ?>
            <div class="alerta alerta-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><?= htmlspecialchars($errorGeneral, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios/<?= (int)$usuario['id'] ?>/editar" method="POST" novalidate>
            <?= $csrfCampo ?>

            <div style="background-color: var(--color-superficie-alt); padding: 1rem; border-radius: var(--radio-md); margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 0.8rem; color: var(--color-texto-apagado);">Rol del Usuario:</span>
                    <strong style="display: block; font-size: 1.1rem; color: var(--color-texto);"><?= ucfirst($usuario['rol']) ?></strong>
                </div>
                <div>
                    <span style="font-size: 0.8rem; color: var(--color-texto-apagado);">Estado Actual:</span>
                    <strong style="display: block; color: <?= (int)$usuario['activo'] === 1 ? '#10b981' : '#ef4444' ?>;">
                        <?= (int)$usuario['activo'] === 1 ? 'Activo' : 'Desactivado' ?>
                    </strong>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="grupo-formulario">
                    <label for="nombre" class="label-formulario">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" class="control-formulario"
                           value="<?= htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="grupo-formulario">
                    <label for="apellido" class="label-formulario">Apellido *</label>
                    <input type="text" id="apellido" name="apellido" class="control-formulario"
                           value="<?= htmlspecialchars($usuario['apellido'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
            </div>

            <div class="grupo-formulario">
                <label for="correo" class="label-formulario">Correo electronico *</label>
                <input type="email" id="correo" name="correo" class="control-formulario"
                       value="<?= htmlspecialchars($usuario['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="grupo-formulario">
                <label for="telefono" class="label-formulario">Telefono de contacto</label>
                <input type="tel" id="telefono" name="telefono" class="control-formulario"
                       value="<?= htmlspecialchars($usuario['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="grupo-formulario">
                <label for="nueva_contrasena" class="label-formulario">Cambiar Contrasena (opcional)</label>
                <input type="password" id="nueva_contrasena" name="nueva_contrasena" class="control-formulario"
                       placeholder="Dejar vacio para conservar la actual">
                <span class="texto-ayuda">Si ingresa una nueva clave, debe contener minimo 8 caracteres.</span>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios" class="btn btn-secundario">Cancelar</a>
                <button type="submit" class="btn btn-primario">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
