<?php
/**
 * WashFlow - Historial de Operaciones y Auditoria (Diagramas 04b, 12)
 */
?>
<div class="tarjeta">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Historial de Operaciones (Auditoria del Sistema)</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Trazabilidad inmutable de todas las acciones de alta, modificacion, cancelacion y cobros.
            </p>
        </div>
        <span class="badge badge-neutro">Total registros: <?= (int)$total ?></span>
    </div>

    <div class="tarjeta-cuerpo">
        <!-- Filtros de Auditoria -->
        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/auditoria" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 2rem; align-items: flex-end;">
            <div class="grupo-formulario" style="margin-bottom: 0;">
                <label for="accion" class="label-formulario">Accion:</label>
                <input type="text" id="accion" name="accion" class="control-formulario" placeholder="Ej. CREAR_RESERVA" value="<?= htmlspecialchars($filtros['accion'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="grupo-formulario" style="margin-bottom: 0;">
                <label for="entidad" class="label-formulario">Entidad:</label>
                <input type="text" id="entidad" name="entidad" class="control-formulario" placeholder="Ej. Reserva, Pago" value="<?= htmlspecialchars($filtros['entidad'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="grupo-formulario" style="margin-bottom: 0;">
                <label for="desde" class="label-formulario">Desde:</label>
                <input type="date" id="desde" name="desde" class="control-formulario" value="<?= htmlspecialchars($filtros['fecha_desde'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="grupo-formulario" style="margin-bottom: 0;">
                <label for="hasta" class="label-formulario">Hasta:</label>
                <input type="date" id="hasta" name="hasta" class="control-formulario" value="<?= htmlspecialchars($filtros['fecha_hasta'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <button type="submit" class="btn btn-primario">Filtrar</button>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/auditoria" class="btn btn-secundario">Limpiar</a>
        </form>

        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Usuario Responsable</th>
                        <th>Accion</th>
                        <th>Entidad</th>
                        <th>ID Entidad</th>
                        <th>Detalle de la Operacion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($operaciones)): ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--color-texto-apagado);">No se encontraron registros de auditoria con los filtros aplicados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($operaciones as $op): ?>
                            <tr>
                                <td style="white-space: nowrap; font-size: 0.825rem;">
                                    <?= date('d/m/Y H:i:s', strtotime($op['fecha_hora'])) ?>
                                </td>
                                <td>
                                    <?php if (!empty($op['usuario_nombre'])): ?>
                                        <strong><?= htmlspecialchars($op['usuario_nombre'] . ' ' . $op['usuario_apellido'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                        <span style="font-size: 0.75rem; color: var(--color-texto-apagado);"><?= htmlspecialchars($op['usuario_rol'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--color-texto-apagado);">Sistema / Anonimo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-neutro" style="font-weight: 700;"><?= htmlspecialchars($op['accion'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td><?= htmlspecialchars($op['entidad'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= $op['id_entidad'] !== null ? "#{$op['id_entidad']}" : '-' ?></td>
                                <td style="max-width: 320px; font-size: 0.85rem; line-height: 1.4;">
                                    <?= htmlspecialchars($op['detalle'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
