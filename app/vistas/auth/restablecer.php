<?php
/**
 * WashFlow - Vista de Restablecimiento de Contrasena por Token
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
        <h1 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.35rem;">Nueva Contrasena</h1>
        <p class="auth-subtitulo">Ingrese una nueva contrasena para la cuenta <?= htmlspecialchars($correo ?? '', ENT_QUOTES, 'UTF-8') ?></p>
    </div>

    <?php if (!empty($errorGeneral)): ?>
        <div class="alerta alerta-error">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            <span><?= htmlspecialchars($errorGeneral, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/restablecer" method="POST" novalidate>
        <?= $csrfCampo ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <div class="grupo-formulario">
            <label for="contrasena" class="label-formulario">Nueva contrasena</label>
            <input type="password" id="contrasena" name="contrasena" class="control-formulario"
                   required autocomplete="new-password" placeholder="Minimo 8 caracteres">
        </div>

        <div class="grupo-formulario">
            <label for="confirmar_contrasena" class="label-formulario">Confirmar nueva contrasena</label>
            <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" class="control-formulario"
                   required autocomplete="new-password" placeholder="Repetir nueva contrasena">
        </div>

        <button type="submit" class="btn btn-primario" style="width: 100%; margin-top: 0.5rem; padding: 0.75rem;">
            Guardar Nueva Contrasena
        </button>
    </form>
</div>
