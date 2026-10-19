<?php
/**
 * WashFlow - Vista de Registro de Clientes
 */
?>
<div class="tarjeta-auth" style="max-width: 480px;">
    <div class="auth-header">
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/" class="auth-brand">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--color-acento)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
            </svg>
            <span>WashFlow</span>
        </a>
        <p class="auth-subtitulo">Cree su cuenta de cliente para reservar turnos online</p>
    </div>

    <?php if (!empty($errorGeneral)): ?>
        <div class="alerta alerta-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            <span><?= htmlspecialchars($errorGeneral, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/registro" method="POST" novalidate>
        <?= $csrfCampo ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div class="grupo-formulario">
                <label for="nombre" class="label-formulario">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="control-formulario <?= !empty($errores['nombre']) ? 'con-error' : '' ?>"
                       value="<?= htmlspecialchars($valores['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autocomplete="given-name">
                <?php if (!empty($errores['nombre'])): ?>
                    <span class="mensaje-error-campo"><?= htmlspecialchars($errores['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="grupo-formulario">
                <label for="apellido" class="label-formulario">Apellido</label>
                <input type="text" id="apellido" name="apellido" class="control-formulario <?= !empty($errores['apellido']) ? 'con-error' : '' ?>"
                       value="<?= htmlspecialchars($valores['apellido'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autocomplete="family-name">
                <?php if (!empty($errores['apellido'])): ?>
                    <span class="mensaje-error-campo"><?= htmlspecialchars($errores['apellido'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="grupo-formulario">
            <label for="telefono" class="label-formulario">Telefono de contacto</label>
            <input type="tel" id="telefono" name="telefono" class="control-formulario <?= !empty($errores['telefono']) ? 'con-error' : '' ?>"
                   value="<?= htmlspecialchars($valores['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autocomplete="tel" placeholder="Ej. 11 2345 6789">
            <?php if (!empty($errores['telefono'])): ?>
                <span class="mensaje-error-campo"><?= htmlspecialchars($errores['telefono'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="grupo-formulario">
            <label for="correo" class="label-formulario">Correo electronico</label>
            <input type="email" id="correo" name="correo" class="control-formulario <?= !empty($errores['correo']) ? 'con-error' : '' ?>"
                   value="<?= htmlspecialchars($valores['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autocomplete="email" placeholder="ejemplo@correo.com">
            <?php if (!empty($errores['correo'])): ?>
                <span class="mensaje-error-campo"><?= htmlspecialchars($errores['correo'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div class="grupo-formulario">
                <label for="contrasena" class="label-formulario">Contrasena</label>
                <input type="password" id="contrasena" name="contrasena" class="control-formulario <?= !empty($errores['contrasena']) ? 'con-error' : '' ?>"
                       required autocomplete="new-password" placeholder="Min. 8 car.">
                <?php if (!empty($errores['contrasena'])): ?>
                    <span class="mensaje-error-campo"><?= htmlspecialchars($errores['contrasena'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="grupo-formulario">
                <label for="confirmar_contrasena" class="label-formulario">Confirmar</label>
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" class="control-formulario <?= !empty($errores['confirmar_contrasena']) ? 'con-error' : '' ?>"
                       required autocomplete="new-password" placeholder="Repetir clave">
                <?php if (!empty($errores['confirmar_contrasena'])): ?>
                    <span class="mensaje-error-campo"><?= htmlspecialchars($errores['confirmar_contrasena'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primario" style="width: 100%; margin-top: 0.5rem; padding: 0.75rem;">
            Crear Cuenta de Cliente
        </button>
    </form>

    <div style="margin-top: 1.5rem; text-align: center; font-size: 0.875rem; color: var(--color-texto-apagado);">
        ¿Ya tiene una cuenta registrada?
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/login" style="font-weight: 600;">Inicie sesion</a>
    </div>
</div>
