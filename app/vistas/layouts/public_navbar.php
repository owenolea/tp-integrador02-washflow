<?php
/**
 * WashFlow - Barra de Navegacion Publica Unificada y de Alta Gama
 * Cero emojis. Diseno oscuro/petroleo con cyan electrico y contraste accesible.
 */
$paginaActual = $paginaActual ?? '';
$usuarioActual = \WashFlow\Core\Sesion::get('usuario');
$rolActual = $usuarioActual['rol'] ?? '';
?>
<header class="navbar-publica">
    <div class="navbar-publica-contenedor">
        <div class="navbar-publica-izq">
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/" class="brand-link">
                <div class="brand-emblema">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                    </svg>
                </div>
                <span>WashFlow</span>
            </a>
            <nav class="navbar-publica-links d-none-mobile">
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/" class="nav-link-publico <?= $paginaActual === 'inicio' ? 'activo' : '' ?>">Inicio</a>
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/guia-lavados" class="nav-link-publico <?= $paginaActual === 'guia' ? 'activo' : '' ?>">Guia de Servicios</a>
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/#tarifas" class="nav-link-publico">Tarifas</a>
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/mi-reserva" class="nav-link-publico <?= $paginaActual === 'mi-reserva' ? 'activo' : '' ?>">Mi Reserva</a>
            </nav>
        </div>

        <div class="navbar-publica-der">
            <!-- Boton de Modo Claro / Oscuro -->
            <button id="btn-cambiar-tema" class="btn-tema-redondo" aria-label="Cambiar tema de color" title="Alternar tema claro u oscuro">
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
                <?php
                $urlDashboard = match ($rolActual) {
                    'administrador' => '/admin/dashboard',
                    'empleado' => '/empleado/dashboard',
                    default => '/cliente/dashboard'
                };
                ?>
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') . $urlDashboard ?>" class="btn btn-primario btn-sm">Mi Panel</a>
            <?php else: ?>
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/login" class="btn-fantasma">Ingreso</a>
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/reservar" class="btn btn-primario btn-sm">Reservar Turno</a>
            <?php endif; ?>
        </div>
    </div>
</header>
