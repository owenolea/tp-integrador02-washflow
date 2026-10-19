<?php
/**
 * WashFlow - Consulta de Reserva Publica por Codigo y Telefono
 * Pagina premium con hero, formulario flotante y tips informativos.
 * Sin emojis.
 */

$flashError = \WashFlow\Core\Sesion::getFlash('error');
?>
<!DOCTYPE html>
<html lang="es" data-tema="oscuro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Mi Reserva — WashFlow</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/assets/css/app.css">
</head>
<body>
    <?php $paginaActual = 'mi-reserva'; include dirname(__DIR__) . '/layouts/public_navbar.php'; ?>

    <!-- Hero Section -->
    <section class="buscar-hero-seccion">
        <span class="seccion-rotulo">Acceso a tu Turno</span>
        <h1 class="buscar-hero-titulo">Consultar Mi Reserva</h1>
        <p class="buscar-hero-desc">
            Ingresa tu codigo de reserva y el telefono que registraste para ver el estado de tu turno, reprogramar o cancelar.
        </p>
    </section>

    <!-- Formulario Flotante -->
    <div class="buscar-form-panel">
        <div class="buscar-form-card">
            <?php if (!empty($flashError)): ?>
                <div class="alerta alerta-error" style="margin-bottom: 1.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/mi-reserva" method="POST">
                <?= $csrfCampo ?>

                <div style="margin-bottom: 1.25rem;">
                    <label for="input-codigo" style="font-size: 0.85rem; font-weight: 700; color: var(--color-texto); display: block; margin-bottom: 0.35rem;">
                        Codigo de Reserva:
                    </label>
                    <input type="text" name="codigo" id="input-codigo" class="campo-control" placeholder="WF-XXXXX" required style="text-transform: uppercase; font-family: var(--fuente-titulo); font-size: 1.15rem; letter-spacing: 0.05em;">
                    <div style="font-size: 0.78rem; color: var(--color-texto-apagado); margin-top: 0.3rem;">
                        Formato: WF- seguido de 5 digitos (ej: WF-10492)
                    </div>
                </div>

                <div style="margin-bottom: 1.75rem;">
                    <label for="input-telefono" style="font-size: 0.85rem; font-weight: 700; color: var(--color-texto); display: block; margin-bottom: 0.35rem;">
                        Telefono Celular Registrado:
                    </label>
                    <input type="tel" name="telefono" id="input-telefono" class="campo-control" placeholder="Ej: 11 4567 8900" required>
                </div>

                <button type="submit" class="btn btn-primario btn-lg" style="width: 100%; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <span>Consultar Mi Turno</span>
                </button>
            </form>

            <div class="buscar-separador">
                <span style="font-size: 0.85rem; color: var(--color-texto-apagado);">Todavia no tenes una reserva agendada?</span>
                <div style="margin-top: 0.5rem;">
                    <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/reservar" class="link-subrayado" style="font-size: 0.9rem; font-weight: 600;">Reservar turno sin cuenta aqui</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tips informativos -->
    <div class="buscar-tip-seccion">
        <div class="buscar-tips-grilla">
            <div class="buscar-tip-card">
                <div class="buscar-tip-icono">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                </div>
                <div class="buscar-tip-titulo">Codigo Legible</div>
                <div class="buscar-tip-desc">Recibiste tu codigo al confirmar la reserva. Formato WF-XXXXX.</div>
            </div>

            <div class="buscar-tip-card">
                <div class="buscar-tip-icono">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <div class="buscar-tip-titulo">Regla de 2 Horas</div>
                <div class="buscar-tip-desc">Podes reprogramar o cancelar hasta 2 horas antes del turno.</div>
            </div>

            <div class="buscar-tip-card">
                <div class="buscar-tip-icono">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <div class="buscar-tip-titulo">Enlace Seguro</div>
                <div class="buscar-tip-desc">Tambien podes acceder con el enlace privado que generamos al reservar.</div>
            </div>
        </div>
    </div>

    <?php include dirname(__DIR__) . '/layouts/public_footer.php'; ?>

    <script src="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/assets/js/app.js"></script>
</body>
</html>
