<?php
/**
 * WashFlow - Vista de Recuperacion de Contrasena
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
        <h1 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.35rem;">Recuperar Contrasena</h1>
        <p class="auth-subtitulo">Ingrese su correo para generar un token de restablecimiento</p>
    </div>

    <?php if (!empty($enviado)): ?>
        <div class="alerta alerta-exito">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <span>Si el correo ingresado corresponde a una cuenta activa, se ha generado el enlace de recuperacion.</span>
        </div>

        <?php if (!empty($tokenDemo)): ?>
            <div class="tarjeta" style="margin-top: 1rem; border-color: var(--color-acento);">
                <div class="tarjeta-cuerpo" style="padding: 1rem; font-size: 0.85rem;">
                    <strong>Enlace directo de prueba local:</strong>
                    <p style="margin: 0.5rem 0; word-break: break-all;">
                        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/restablecer?token=<?= htmlspecialchars($tokenDemo, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/restablecer?token=<?= htmlspecialchars($tokenDemo, ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </p>
                    <span class="texto-ayuda">Este enlace directo se muestra en modo desarrollo local para permitir probar la recuperacion sin requerir servidor SMTP activo.</span>
                </div>
            </div>
        <?php endif; ?>

        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/login" class="btn btn-secundario" style="width: 100%;">
                Volver al Inicio de Sesion
            </a>
        </div>
    <?php else: ?>
        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/recuperar" method="POST" novalidate>
            <?= $csrfCampo ?>

            <div class="grupo-formulario">
                <label for="correo" class="label-formulario">Correo electronico de su cuenta</label>
                <input type="email" id="correo" name="correo" class="control-formulario <?= !empty($errores['correo']) ? 'con-error' : '' ?>"
                       required autocomplete="email" placeholder="ejemplo@correo.com">
                <?php if (!empty($errores['correo'])): ?>
                    <span class="mensaje-error-campo"><?= htmlspecialchars($errores['correo'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primario" style="width: 100%; margin-top: 0.5rem; padding: 0.75rem;">
                Generar Enlace de Recuperacion
            </button>
        </form>

        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.875rem;">
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/login">Volver a Iniciar Sesion</a>
        </div>
    <?php endif; ?>
</div>
