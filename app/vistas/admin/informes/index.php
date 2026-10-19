<?php
/**
 * WashFlow - Informes y Estadisticas del Sistema
 */
?>
<div class="tarjeta">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Informes y Estadisticas de Ocupacion</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Analisis de demanda de servicios, niveles de asistencia y cancelaciones.
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/exportar-csv?tipo=reservas&desde=<?= urlencode($desde) ?>&hasta=<?= urlencode($hasta) ?>" class="btn btn-secundario btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span>Exportar Reservas (CSV)</span>
            </a>
        </div>
    </div>

    <div class="tarjeta-cuerpo">
        <!-- Filtro Temporal -->
        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/informes" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end; margin-bottom: 2rem;">
            <div class="grupo-formulario" style="margin-bottom: 0;">
                <label for="desde" class="label-formulario">Desde:</label>
                <input type="date" id="desde" name="desde" class="control-formulario" value="<?= htmlspecialchars($desde, ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="grupo-formulario" style="margin-bottom: 0;">
                <label for="hasta" class="label-formulario">Hasta:</label>
                <input type="date" id="hasta" name="hasta" class="control-formulario" value="<?= htmlspecialchars($hasta, ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <button type="submit" class="btn btn-primario">Actualizar Reporte</button>
        </form>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <!-- Servicios Mas Solicitados -->
            <div class="tarjeta" style="margin-bottom: 0;">
                <div class="tarjeta-header">
                    <h2 class="tarjeta-titulo" style="font-size: 1rem;">Servicios mas Solicitados</h2>
                </div>
                <div class="tarjeta-cuerpo" style="padding: 0;">
                    <div class="tabla-contenedor">
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Total Reservas</th>
                                    <th>Monto Generado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($serviciosSolicitados as $ss): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($ss['nombre'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                        <td><strong><?= (int)$ss['total_reservas'] ?></strong> turnos</td>
                                        <td>$<?= number_format((float)$ss['monto_total'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Resumen de Ocupacion por Dia -->
            <div class="tarjeta" style="margin-bottom: 0;">
                <div class="tarjeta-header">
                    <h2 class="tarjeta-titulo" style="font-size: 1rem;">Ocupacion y Asistencia Diaria</h2>
                </div>
                <div class="tarjeta-cuerpo" style="padding: 0;">
                    <div class="tabla-contenedor">
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cumplidas</th>
                                    <th>Canceladas</th>
                                    <th>Ausentes</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($ocupacionDias)): ?>
                                    <tr><td colspan="5" style="text-align: center; color: var(--color-texto-apagado);">Sin datos en el periodo.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($ocupacionDias as $od): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars(date('d/m/Y', strtotime($od['fecha'])), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                            <td><span class="badge badge-exito"><?= (int)$od['reservadas'] ?></span></td>
                                            <td><span class="badge badge-peligro"><?= (int)$od['canceladas'] ?></span></td>
                                            <td><span class="badge badge-alerta"><?= (int)$od['ausentes'] ?></span></td>
                                            <td><strong><?= (int)$od['total_turnos'] ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
