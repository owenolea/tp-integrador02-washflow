<?php
/**
 * WashFlow - Panel de Control del Administrador
 */
?>
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--color-texto); letter-spacing: -0.02em;">
            Panel de Administracion
        </h1>
        <p style="color: var(--color-texto-apagado); margin-top: 0.25rem;">
            Control operativo, rendimiento, personal y configuracion del lavadero.
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/agenda" class="btn btn-secundario btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <span>Ver Agenda Diaria</span>
        </a>
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/informes" class="btn btn-primario btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            <span>Informes y Estadisticas</span>
        </a>
    </div>
</div>

<!-- KPIs Operativos -->
<div class="grilla-kpis">
    <div class="tarjeta-kpi kpi-exito">
        <div class="kpi-encabezado">
            <span class="kpi-titulo">Clientes Registrados</span>
            <div class="kpi-icono" style="background-color: var(--estado-exito-fondo); color: var(--estado-exito-texto);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
        </div>
        <div class="kpi-valor" style="color: var(--estado-exito-solido);">
            <?= (int)$totalClientes ?>
        </div>
        <div class="kpi-detalle">
            <span class="badge badge-exito">Activos</span>
            <span style="color: var(--color-texto-apagado);">Total en el sistema</span>
        </div>
    </div>

    <div class="tarjeta-kpi">
        <div class="kpi-encabezado">
            <span class="kpi-titulo">Empleados</span>
            <div class="kpi-icono" style="background-color: var(--color-acento-claro); color: var(--color-acento);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>
        </div>
        <div class="kpi-valor" style="color: var(--color-acento);">
            <?= (int)$totalEmpleados ?>
        </div>
        <div class="kpi-detalle">
            <span class="badge badge-info">Personal</span>
            <span style="color: var(--color-texto-apagado);">Equipo operativo</span>
        </div>
    </div>

    <div class="tarjeta-kpi kpi-alerta">
        <div class="kpi-encabezado">
            <span class="kpi-titulo">Reservas del Mes</span>
            <div class="kpi-icono" style="background-color: var(--estado-alerta-fondo); color: var(--estado-alerta-texto);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </div>
        </div>
        <div class="kpi-valor" style="color: var(--color-texto);">
            <?= (int)$totalReservasMes ?>
        </div>
        <div class="kpi-detalle">
            <span class="badge badge-alerta">Turnos</span>
            <span style="color: var(--color-texto-apagado);">Capacidad max 3 simultaneos</span>
        </div>
    </div>

    <div class="tarjeta-kpi">
        <div class="kpi-encabezado">
            <span class="kpi-titulo">Lavados en Curso</span>
            <div class="kpi-icono" style="background-color: var(--estado-info-fondo); color: var(--estado-info-texto);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>
            </div>
        </div>
        <div class="kpi-valor" style="color: var(--color-texto);">
            <?= (int)$lavadosEnCurso ?>
        </div>
        <div class="kpi-detalle">
            <span class="badge badge-info">Ahora</span>
            <span style="color: var(--color-texto-apagado);">Vehiculos en proceso</span>
        </div>
    </div>
</div>

<!-- Accesos Directos a Gestion Administrativa -->
<div class="tarjeta">
    <div class="tarjeta-header">
        <div>
            <h2 class="tarjeta-titulo">Modulos de Administracion General</h2>
            <p class="tarjeta-subtitulo">Herramientas exclusivas del perfil Administrador con trazabilidad en auditoria</p>
        </div>
    </div>
    <div class="tarjeta-cuerpo">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios" class="tarjeta-kpi" style="text-decoration: none !important;">
                <div class="kpi-encabezado">
                    <span class="kpi-titulo">Gestion de Usuarios</span>
                    <div class="kpi-icono">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                </div>
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--color-texto); margin-bottom: 0.35rem;">
                    <?= (int)$totalClientes ?> Clientes / <?= (int)$totalEmpleados ?> Empleados
                </div>
                <div style="font-size: 0.85rem; color: var(--color-texto-apagado); line-height: 1.5;">
                    Administracion con desactivacion logica sin perdida de historial.
                </div>
            </a>

            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/precios" class="tarjeta-kpi" style="text-decoration: none !important;">
                <div class="kpi-encabezado">
                    <span class="kpi-titulo">Precios y Duraciones</span>
                    <div class="kpi-icono">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                </div>
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--color-texto); margin-bottom: 0.35rem;">
                    Tarifario Inmutable
                </div>
                <div style="font-size: 0.85rem; color: var(--color-texto-apagado); line-height: 1.5;">
                    Vigencia historica de precios y tiempos de duracion por rodado.
                </div>
            </a>

            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/bloqueos" class="tarjeta-kpi" style="text-decoration: none !important;">
                <div class="kpi-encabezado">
                    <span class="kpi-titulo">Bloqueo de Horarios</span>
                    <div class="kpi-icono">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                    </div>
                </div>
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--color-texto); margin-bottom: 0.35rem;">
                    Gestion de Disponibilidad
                </div>
                <div style="font-size: 0.85rem; color: var(--color-texto-apagado); line-height: 1.5;">
                    Inhabilitacion de franjas por mantenimiento tecnico o feriados.
                </div>
            </a>

            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/informes" class="tarjeta-kpi" style="text-decoration: none !important;">
                <div class="kpi-encabezado">
                    <span class="kpi-titulo">Informes y Estadisticas</span>
                    <div class="kpi-icono">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    </div>
                </div>
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--color-texto); margin-bottom: 0.35rem;">
                    Exportacion a CSV
                </div>
                <div style="font-size: 0.85rem; color: var(--color-texto-apagado); line-height: 1.5;">
                    Ocupacion por dia, servicios mas requeridos y estadisticas operativas.
                </div>
            </a>

            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/auditoria" class="tarjeta-kpi" style="text-decoration: none !important;">
                <div class="kpi-encabezado">
                    <span class="kpi-titulo">Historial de Auditoria</span>
                    <div class="kpi-icono">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                </div>
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--color-texto); margin-bottom: 0.35rem;">
                    Trazabilidad de Acciones
                </div>
                <div style="font-size: 0.85rem; color: var(--color-texto-apagado); line-height: 1.5;">
                    Bitacora inmutable de cada operacion sensible registrada en el sistema.
                </div>
            </a>
        </div>
    </div>
</div>
