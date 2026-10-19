<?php
/**
 * WashFlow - Alta de Usuarios por Administrador
 */
?>
<div class="tarjeta" style="max-width: 640px; margin: 0 auto;">
    <div class="tarjeta-header">
        <h1 class="tarjeta-titulo">Alta de Usuario</h1>
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

        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios/crear" method="POST" novalidate>
            <?= $csrfCampo ?>

            <div class="grupo-formulario">
                <label for="rol" class="label-formulario">Rol del Usuario *</label>
                <select id="rol" name="rol" class="control-formulario" required>
                    <option value="empleado" <?= (($valores['rol'] ?? '') === 'empleado') ? 'selected' : '' ?>>Empleado</option>
                    <option value="cliente" <?= (($valores['rol'] ?? '') === 'cliente') ? 'selected' : '' ?>>Cliente</option>
                </select>
                <span class="texto-ayuda">Los administradores del sistema solo se crean mediante la consola local (scripts/crear_admin.php).</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="grupo-formulario">
                    <label for="nombre" class="label-formulario">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" class="control-formulario <?= !empty($errores['nombre']) ? 'con-error' : '' ?>"
                           value="<?= htmlspecialchars($valores['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    <?php if (!empty($errores['nombre'])): ?>
                        <span class="mensaje-error-campo"><?= htmlspecialchars($errores['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>

                <div class="grupo-formulario">
                    <label for="apellido" class="label-formulario">Apellido *</label>
                    <input type="text" id="apellido" name="apellido" class="control-formulario <?= !empty($errores['apellido']) ? 'con-error' : '' ?>"
                           value="<?= htmlspecialchars($valores['apellido'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    <?php if (!empty($errores['apellido'])): ?>
                        <span class="mensaje-error-campo"><?= htmlspecialchars($errores['apellido'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grupo-formulario">
                <label for="correo" class="label-formulario">Correo electronico *</label>
                <input type="email" id="correo" name="correo" class="control-formulario <?= !empty($errores['correo']) ? 'con-error' : '' ?>"
                       value="<?= htmlspecialchars($valores['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required placeholder="ejemplo@washflow.com">
                <?php if (!empty($errores['correo'])): ?>
                    <span class="mensaje-error-campo"><?= htmlspecialchars($errores['correo'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="grupo-formulario">
                <label for="telefono" class="label-formulario">Telefono de contacto</label>
                <input type="tel" id="telefono" name="telefono" class="control-formulario"
                       value="<?= htmlspecialchars($valores['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ej. 11 4455 6677">
            </div>

            <div class="grupo-formulario">
                <label for="contrasena" class="label-formulario">Contrasena inicial *</label>
                <input type="password" id="contrasena" name="contrasena" class="control-formulario <?= !empty($errores['contrasena']) ? 'con-error' : '' ?>"
                       required placeholder="Minimo 8 caracteres">
                <?php if (!empty($errores['contrasena'])): ?>
                    <span class="mensaje-error-campo"><?= htmlspecialchars($errores['contrasena'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios" class="btn btn-secundario">Cancelar</a>
                <button type="submit" class="btn btn-primario">Crear Usuario</button>
            </div>
        </form>
    </div>
</div>
