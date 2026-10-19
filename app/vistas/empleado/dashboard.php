<?php
/**
 * WashFlow - Panel Operativo de Empleados
 */
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-texto);">
            Panel Operativo del Lavadero
        </h1>
        <p style="color: var(--color-texto-apagado);">Gestion del dia <?= htmlspecialchars(date('d/m/Y') ?: '', ENT_QUOTES, 'UTF-8') ?> — Turnos y lavados</p>
    </div>

    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/lavado-directo" class="btn btn-acento btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Lavado Sin Reserva</span>
        </a>
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/agenda" class="btn btn-primario btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <span>Ver Agenda Completa</span>
        </a>
    </div>
</div>

<!-- KPIs Operativos -->
<div class="grilla-kpis">
    <div class="tarjeta-kpi">
        <span class="kpi-titulo">Turnos de Hoy</span>
        <span class="kpi-valor"><?= count($reservasHoy) ?></span>
        <span class="kpi-subtexto">Reservas agendadas</span>
    </div>

    <div class="tarjeta-kpi">
        <span class="kpi-titulo">Lavados en Curso</span>
        <span class="kpi-valor"><?= count($pendientesLavado) ?></span>
        <span class="kpi-subtexto">Vehiculos en estado Pendiente</span>
    </div>

    <div class="tarjeta-kpi">
        <span class="kpi-titulo">Terminados Hoy</span>
        <span class="kpi-valor"><?= count($terminadosLavado) ?></span>
        <span class="kpi-subtexto">Listos para entrega / entregados</span>
    </div>

    <div class="tarjeta-kpi">
        <span class="kpi-titulo">Lavados Directos Hoy</span>
        <span class="kpi-valor"><?= count($lavadosSinReserva) ?></span>
        <span class="kpi-subtexto">Vehiculos ingresados sin turno previo</span>
    </div>
</div>

<!-- Turnos del Dia -->
<div class="tarjeta">
    <div class="tarjeta-header">
        <h2 class="tarjeta-titulo">Turnos Programados para Hoy</h2>
        <span class="texto-ayuda">Capacidad maxima simultanea: 3 vehiculos</span>
    </div>
    <div class="tarjeta-cuerpo" style="padding: 0;">
        <?php if (empty($reservasHoy)): ?>
            <div style="padding: 2.5rem; text-align: center; color: var(--color-texto-apagado);">
                <p>No hay reservas agendadas para el dia de hoy.</p>
            </div>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Horario</th>
                            <th>Cliente</th>
                            <th>Vehiculo / Patente</th>
                            <th>Servicio</th>
                            <th>Estado Turno</th>
                            <th>Estado Lavado</th>
                            <th>Acciones Operativas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservasHoy as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= substr($r['hora_inicio'], 0, 5) ?> - <?= substr($r['hora_fin'], 0, 5) ?></strong><br>
                                    <span style="font-size: 0.75rem; color: var(--color-texto-apagado);"><?= (int)$r['duracion_minutos'] ?> min</span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars(($r['cliente_nombre'] ?? '') . ' ' . ($r['cliente_apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <span style="font-size: 0.8rem; color: var(--color-texto-apagado);"><?= htmlspecialchars($r['cliente_telefono'] ?? 'Sin tel.', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars(($r['marca'] ?? '') . ' ' . ($r['modelo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <span class="badge badge-neutro"><?= htmlspecialchars($r['patente'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($r['servicio_nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?><br>
                                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-acento);">$<?= number_format((float)($r['precio_acordado'] ?? 0), 0, ',', '.') ?></span>
                                </td>
                                <td>
                                    <?php if ($r['estado'] === 'reservada'): ?>
                                        <span class="badge badge-info">Reservada</span>
                                    <?php elseif ($r['estado'] === 'cancelada'): ?>
                                        <span class="badge badge-peligro">Cancelada</span>
                                    <?php else: ?>
                                        <span class="badge badge-alerta">Ausente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($r['lavado_estado'])): ?>
                                        <?php if ($r['lavado_estado'] === 'terminado'): ?>
                                            <span class="badge badge-exito">Terminado</span>
                                        <?php else: ?>
                                            <span class="badge badge-alerta">Pendiente</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-neutro">No ingresado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                        <?php if ($r['estado'] === 'reservada' && empty($r['lavado_id'])): ?>
                                            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/llegada" method="POST">
                                                <?= $csrfCampo ?>
                                                <input type="hidden" name="reserva_id" value="<?= (int)$r['id'] ?>">
                                                <button type="submit" class="btn btn-acento btn-sm">Registrar Llegada</button>
                                            </form>
                                            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/ausencia" method="POST" onsubmit="return confirm('Registrar inasistencia del cliente?');">
                                                <?= $csrfCampo ?>
                                                <input type="hidden" name="reserva_id" value="<?= (int)$r['id'] ?>">
                                                <button type="submit" class="btn btn-secundario btn-sm">Ausente</button>
                                            </form>
                                        <?php elseif (!empty($r['lavado_id']) && ($r['lavado_estado'] ?? '') === 'pendiente'): ?>
                                            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/terminar-lavado" method="POST">
                                                <?= $csrfCampo ?>
                                                <input type="hidden" name="lavado_id" value="<?= (int)$r['lavado_id'] ?>">
                                                <button type="submit" class="btn btn-primario btn-sm">Marcar Terminado</button>
                                            </form>
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
