<?php
/**
 * WashFlow - Historial y Gestion de Reservas del Cliente
 */
?>
<div class="tarjeta">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Mis Reservas y Turnos</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Consulte sus turnos programados, historial de lavados y estado de pago.
            </p>
        </div>
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservar" class="btn btn-primario btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Nueva Reserva</span>
        </a>
    </div>

    <div class="tarjeta-cuerpo" style="padding: 0;">
        <?php if (empty($reservas)): ?>
            <div style="padding: 3rem; text-align: center; color: var(--color-texto-apagado);">
                <p>No cuenta con reservas registradas en su historial.</p>
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservar" class="btn btn-acento btn-sm" style="margin-top: 1rem;">
                    Agendar mi primer turno
                </a>
            </div>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Turno #</th>
                            <th>Fecha y Horario</th>
                            <th>Vehiculo</th>
                            <th>Servicio</th>
                            <th>Tarifa Congelada</th>
                            <th>Estado Reserva</th>
                            <th>Estado Lavado</th>
                            <th>Estado Pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas as $r): ?>
                            <tr>
                                <td><strong>#<?= (int)$r['id'] ?></strong></td>
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
                                        <span class="badge badge-neutro">Sin ingresar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['estado_pago'] === 'pagado'): ?>
                                        <span class="badge badge-exito">Pagado</span>
                                    <?php else: ?>
                                        <span class="badge badge-alerta">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['estado'] === 'reservada'): ?>
                                        <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                            <?php if (!empty($r['permite_cambio_directo'])): ?>
                                                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas/<?= (int)$r['id'] ?>/modificar" class="btn btn-secundario btn-sm">
                                                    Modificar
                                                </a>
                                                <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas/<?= (int)$r['id'] ?>/cancelar" method="POST" onsubmit="return confirm('¿Confirma que desea cancelar esta reserva?');">
                                                    <?= $csrfCampo ?>
                                                    <button type="submit" class="btn btn-peligro btn-sm">Cancelar</button>
                                                </form>
                                            <?php else: ?>
                                                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas/<?= (int)$r['id'] ?>/solicitar" class="btn btn-secundario btn-sm">
                                                    Solicitar Cambio/Baja
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="font-size: 0.8rem; color: var(--color-texto-apagado);">Finalizado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
