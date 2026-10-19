<?php
/**
 * WashFlow - Encabezado superior (TopBar interno de empleados y administracion)
 */
$nombreUsuario = $usuarioActual['nombre'] ?? 'Usuario';
$iniciales = '';
$partesNombre = explode(' ', trim($nombreUsuario));
foreach ($partesNombre as $p) {
    if (!empty($p)) {
        $iniciales .= mb_strtoupper(mb_substr($p, 0, 1));
        if (mb_strlen($iniciales) >= 2) break;
    }
}
if ($iniciales === '') $iniciales = 'WF';
?>
<header class="topbar">
    <div class="topbar-izq">
        <button id="btn-toggle-sidebar" class="btn-toggle-sidebar" aria-label="Abrir navegacion">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <div class="indicador-estado-lavadero">
            <span class="punto-pulso-activo"></span>
            <span>Lavadero Operativo &bull; 10:00 a 18:00 hs</span>
        </div>
    </div>

    <div class="topbar-der">
        <!-- Selector Modo Claro / Oscuro -->
        <button id="btn-cambiar-tema" class="btn-tema-redondo" aria-label="Cambiar tema de color" title="Alternar modo claro u oscuro">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line>
                <line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
        </button>

        <?php if (!empty($usuarioActual)): ?>
            <div style="display: flex; align-items: center; gap: 0.85rem; border-left: 1px solid var(--color-borde-suave); padding-left: 1.25rem;">
                <div class="usuario-avatar-circulo" title="<?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div style="display: flex; flex-direction: column; text-align: left;" class="d-none-mobile">
                    <span style="font-size: 0.9rem; font-weight: 700; color: var(--color-texto); line-height: 1.2;">
                        <?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span style="font-size: 0.72rem; font-weight: 800; color: var(--color-acento-brillante); text-transform: uppercase; letter-spacing: 0.06em;">
                        <?= htmlspecialchars($rolActual ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>
