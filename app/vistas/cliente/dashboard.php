<?php
/**
 * WashFlow - Dashboard del Cliente
 */
?>
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-texto);">
        Hola, <?= htmlspecialchars($usuarioActual['nombre'] ?? 'Cliente', ENT_QUOTES, 'UTF-8') ?>
    </h1>
    <p style="color: var(--color-texto-apagado);">Bienvenido a su panel personal de gestion de turnos y vehiculos.</p>
</div>

<!-- KPIs del Cliente -->
<div class="grilla-kpis">
    <div class="tarjeta-kpi">
        <span class="kpi-titulo">Proximos Turnos</span>
        <span class="kpi-valor"><?= count($proximas) ?></span>
        <span class="kpi-subtexto">Reservas confirmadas activas</span>
    </div>

    <div class="tarjeta-kpi">
        <span class="kpi-titulo">Vehiculos Registrados</span>
        <span class="kpi-valor"><?= count($vehiculos) ?></span>
        <span class="kpi-subtexto">Listos para reservar turno</span>
    </div>

    <div class="tarjeta-kpi">
        <span class="kpi-titulo">Notificaciones</span>
        <span class="kpi-valor"><?= (int)$noLeidas ?></span>
        <span class="kpi-subtexto">Mensajes sin leer</span>
    </div>
</div>

<!-- Proximas Reservas -->
<div class="tarjeta">
    <div class="tarjeta-header">
        <h2 class="tarjeta-titulo">Mis Proximos Turnos</h2>
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservar" class="btn btn-primario btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Reservar Nuevo Turno</span>
        </a>
    </div>
    <div class="tarjeta-cuerpo" style="padding: 0;">
        <?php if (empty($proximas)): ?>
            <div style="padding: 2.5rem; text-align: center; color: var(--color-texto-apagado);">
                <p>No tiene turnos proximos programados.</p>
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservar" class="btn btn-acento btn-sm" style="margin-top: 1rem;">
                    Agendar un turno ahora
                </a>
            </div>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Fecha y Horario</th>
                            <th>Vehiculo</th>
                            <th>Servicio</th>
                            <th>Precio Acordado</th>
                            <th>Estado Pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proximas as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars(date('d/m/Y', strtotime($r['fecha'])), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <span style="font-size: 0.8rem; color: var(--color-texto-apagado);">
                                        <?= substr($r['hora_inicio'], 0, 5) ?> a <?= substr($r['hora_fin'], 0, 5) ?> hs (<?= (int)$r['duracion_minutos'] ?> min)
                                    </span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($r['marca'] . ' ' . $r['modelo'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <span class="badge badge-neutro"><?= htmlspecialchars($r['patente'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td><?= htmlspecialchars($r['servicio_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><strong>$<?= number_format((float)$r['precio_acordado'], 0, ',', '.') ?></strong></td>
                                <td>
                                    <?php if ($r['estado_pago'] === 'pagado'): ?>
                                        <span class="badge badge-exito">Pagado</span>
                                    <?php else: ?>
                                        <span class="badge badge-alerta">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <?php if (!empty($r['permite_cambio_directo'])): ?>
                                            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas/<?= (int)$r['id'] ?>/modificar" class="btn btn-secundario btn-sm">
                                                Modificar
                                            </a>
                                            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas/<?= (int)$r['id'] ?>/cancelar" method="POST" onsubmit="return confirm('¿Confirma que desea cancelar esta reserva?');">
                                                <?= $csrfCampo ?>
                                                <button type="submit" class="btn btn-peligro btn-sm">Cancelar</button>
                                            </form>
                                        <?php else: ?>
                                            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas/<?= (int)$r['id'] ?>/solicitar" class="btn btn-secundario btn-sm" title="Menos de 2 horas: requiere solicitud">
                                                Solicitar Cambio / Cancelacion
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Grilla Secundaria: Mis Vehiculos y Ultimas Notificaciones -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
    <!-- Mis Vehiculos -->
    <div class="tarjeta">
        <div class="tarjeta-header">
            <h2 class="tarjeta-titulo">Mis Vehiculos</h2>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/vehiculos/crear" class="btn btn-secundario btn-sm">
                Agregar
            </a>
        </div>
        <div class="tarjeta-cuerpo" style="padding: 0;">
            <?php if (empty($vehiculos)): ?>
                <div style="padding: 2rem; text-align: center; color: var(--color-texto-apagado);">
                    <p>Aun no ha registrado vehiculos.</p>
                    <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/vehiculos/crear" class="btn btn-secundario btn-sm" style="margin-top: 0.75rem;">
                        Registrar vehiculo
                    </a>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column;">
                    <?php foreach ($vehiculos as $v): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-borde-suave);">
                            <div>
                                <strong style="color: var(--color-texto);"><?= htmlspecialchars($v['marca'] . ' ' . $v['modelo'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <div style="display: flex; gap: 0.5rem; align-items: center; margin-top: 0.25rem;">
                                    <span class="badge badge-neutro"><?= htmlspecialchars($v['patente'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span style="font-size: 0.8rem; color: var(--color-texto-apagado);"><?= htmlspecialchars($v['tipo_nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </div>
                            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/vehiculos/<?= (int)$v['id'] ?>/editar" class="btn btn-secundario btn-sm">
                                Editar
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ultimas Notificaciones -->
    <div class="tarjeta">
        <div class="tarjeta-header">
            <h2 class="tarjeta-titulo">Notificaciones Recientes</h2>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/notificaciones" class="btn btn-secundario btn-sm">
                Ver Todas
            </a>
        </div>
        <div class="tarjeta-cuerpo" style="padding: 0;">
            <?php if (empty($notificaciones)): ?>
                <div style="padding: 2rem; text-align: center; color: var(--color-texto-apagado);">
                    <p>No posee notificaciones recientes.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column;">
                    <?php foreach ($notificaciones as $n): ?>
                        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-borde-suave); background-color: <?= empty($n['leida']) ? 'var(--color-primario-claro)' : 'transparent' ?>;">
                            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.25rem;">
                                <strong style="font-size: 0.9rem; color: var(--color-texto);"><?= htmlspecialchars($n['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span style="font-size: 0.75rem; color: var(--color-texto-apagado);"><?= date('d/m H:i', strtotime($n['fecha_hora'])) ?></span>
                            </div>
                            <p style="font-size: 0.85rem; color: var(--color-texto-secundario); margin: 0; line-height: 1.4;">
                                <?= htmlspecialchars($n['mensaje'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
