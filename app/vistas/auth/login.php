<?php
/**
 * WashFlow - Vista de Inicio de Sesion
 */
?>
<div class="tarjeta-auth">
    <div class="auth-header">
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/" class="auth-brand">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--color-acento)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
            </svg>
            <span>WashFlow</span>
        </a>
        <p class="auth-subtitulo">Ingrese sus credenciales para acceder a la plataforma</p>
    </div>

    <?php if (!empty($errorGeneral)): ?>
        <div class="alerta alerta-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><?= htmlspecialchars($errorGeneral, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/login" method="POST" novalidate>
        <?= $csrfCampo ?>

        <div class="grupo-formulario">
            <label for="correo" class="label-formulario">Correo electronico</label>
            <input type="email" id="correo" name="correo" class="control-formulario <?= !empty($errores['correo']) ? 'con-error' : '' ?>"
                   value="<?= htmlspecialchars($valores['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   required autocomplete="email" placeholder="ejemplo@correo.com">
            <?php if (!empty($errores['correo'])): ?>
                <span class="mensaje-error-campo"><?= htmlspecialchars($errores['correo'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div class="grupo-formulario">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <label for="contrasena" class="label-formulario">Contrasena</label>
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/recuperar" style="font-size: 0.8rem;">Olvide mi clave</a>
            </div>
            <input type="password" id="contrasena" name="contrasena" class="control-formulario <?= !empty($errores['contrasena']) ? 'con-error' : '' ?>"
                   required autocomplete="current-password" placeholder="••••••••">
            <?php if (!empty($errores['contrasena'])): ?>
                <span class="mensaje-error-campo"><?= htmlspecialchars($errores['contrasena'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primario" style="width: 100%; margin-top: 0.5rem; padding: 0.75rem;">
            Iniciar Sesion
        </button>
    </form>

    <div style="margin-top: 1.5rem; text-align: center; font-size: 0.875rem; color: var(--color-texto-apagado);">
        ¿No posee una cuenta de cliente?
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/registro" style="font-weight: 600; color: var(--color-acento);">Registrese aqui</a>
    </div>
</div>
