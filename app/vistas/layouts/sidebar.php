<?php
/**
 * WashFlow - Barra lateral de navegacion segmentada por rol (Diagramas 01, 03, 12)
 * Sin emojis. Iconos en formato SVG inline accesibles.
 */

use WashFlow\Modelos\Notificacion;
use WashFlow\Modelos\SolicitudReserva;

$rol = $rolActual ?? '';
$usuarioId = $usuarioActual['id'] ?? 0;

// Contadores de alertas para badges
$noLeidas = ($usuarioId > 0 && $rol === 'cliente') ? Notificacion::contarNoLeidas($usuarioId) : 0;
$solicitudesPendientes = ($rol === 'empleado' || $rol === 'administrador') ? count(SolicitudReserva::listarPendientes()) : 0;

$uriActual = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/" class="brand-link">
            <div class="brand-emblema">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                </svg>
            </div>
            <span>WashFlow</span>
        </a>
        <span class="brand-badge"><?= htmlspecialchars(ucfirst($rol), ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <nav class="sidebar-nav">
        <?php if ($rol === 'cliente'): ?>
            <div class="nav-seccion-titulo">Menu Cliente</div>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/dashboard" class="nav-link <?= str_contains($uriActual, '/cliente/dashboard') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                <span>Panel Principal</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservar" class="nav-link <?= str_contains($uriActual, '/cliente/reservar') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span>Reservar Turno</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas" class="nav-link <?= (str_contains($uriActual, '/cliente/reservas') && !str_contains($uriActual, '/cliente/reservar')) ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span>Mis Reservas</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/vehiculos" class="nav-link <?= str_contains($uriActual, '/cliente/vehiculos') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                <span>Mis Vehiculos</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/notificaciones" class="nav-link <?= str_contains($uriActual, '/cliente/notificaciones') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                <span>Notificaciones</span>
                <?php if ($noLeidas > 0): ?>
                    <span class="badge badge-alerta" style="margin-left: auto;"><?= $noLeidas ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

        <?php if ($rol === 'empleado' || $rol === 'administrador'): ?>
            <div class="nav-seccion-titulo">Operaciones</div>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/dashboard" class="nav-link <?= str_contains($uriActual, '/empleado/dashboard') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                <span>Panel Operativo</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/agenda" class="nav-link <?= str_contains($uriActual, '/empleado/agenda') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>Agenda y Turnos</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/buscar" class="nav-link <?= str_contains($uriActual, '/empleado/buscar') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <span>Buscar Cliente / Patente</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/lavado-directo" class="nav-link <?= str_contains($uriActual, '/empleado/lavado-directo') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Lavado Sin Reserva</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/solicitudes" class="nav-link <?= str_contains($uriActual, '/empleado/solicitudes') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                <span>Solicitudes</span>
                <?php if ($solicitudesPendientes > 0): ?>
                    <span class="badge badge-alerta" style="margin-left: auto;"><?= $solicitudesPendientes ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

        <?php if ($rol === 'administrador'): ?>
            <div class="nav-seccion-titulo">Administracion</div>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/dashboard" class="nav-link <?= str_contains($uriActual, '/admin/dashboard') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <span>Panel Administrador</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios" class="nav-link <?= str_contains($uriActual, '/admin/usuarios') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Gestion de Usuarios</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/precios" class="nav-link <?= str_contains($uriActual, '/admin/precios') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                <span>Precios y Duraciones</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/bloqueos" class="nav-link <?= str_contains($uriActual, '/admin/bloqueos') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                <span>Bloqueos de Horario</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/informes" class="nav-link <?= str_contains($uriActual, '/admin/informes') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                <span>Informes y Estadisticas</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/auditoria" class="nav-link <?= str_contains($uriActual, '/admin/auditoria') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span>Historial de Operaciones</span>
            </a>

            <div class="nav-seccion-titulo">Contenido y Web</div>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/modelos" class="nav-link <?= str_contains($uriActual, '/admin/modelos') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                <span>Modelos de Vehiculos</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/preguntas" class="nav-link <?= str_contains($uriActual, '/admin/preguntas') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <span>Preguntas Frecuentes</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/galeria" class="nav-link <?= str_contains($uriActual, '/admin/galeria') ? 'activo' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                <span>Galeria de Trabajos</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/logout" method="POST" style="margin: 0;">
            <?= $csrfCampo ?>
            <button type="submit" class="btn btn-secundario btn-sm" style="width: 100%; justify-content: flex-start;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Cerrar Sesion</span>
            </button>
        </form>
    </div>
</aside>
<div id="sidebar-overlay" class="overlay-sidebar"></div>
