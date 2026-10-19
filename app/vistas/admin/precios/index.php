<?php
/**
 * WashFlow - Configuracion de Precios y Duraciones (Diagrama 04a)
 */
?>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Modificar Tarifa Vigente -->
    <div class="tarjeta">
        <div class="tarjeta-header">
            <h1 class="tarjeta-titulo">Modificar Tarifa de Servicio</h1>
        </div>
        <div class="tarjeta-cuerpo">
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-bottom: 1.25rem;">
                Las modificaciones de tarifa rigen a partir de hoy. El historial se conserva intacto y no afecta reservas ya acordadas.
            </p>

            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/precios/modificar" method="POST">
                <?= $csrfCampo ?>

                <div class="grupo-formulario">
                    <label for="servicio_id" class="label-formulario">Servicio *</label>
                    <select id="servicio_id" name="servicio_id" class="control-formulario" required>
                        <?php foreach ($servicios as $s): ?>
                            <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grupo-formulario">
                    <label for="tipo_vehiculo_id" class="label-formulario">Tipo de Vehiculo *</label>
                    <select id="tipo_vehiculo_id" name="tipo_vehiculo_id" class="control-formulario" required>
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grupo-formulario">
                    <label for="nuevo_precio" class="label-formulario">Nuevo Precio ($ ARS) *</label>
                    <input type="number" step="100" min="1" id="nuevo_precio" name="nuevo_precio" class="control-formulario" required placeholder="Ej. 18000">
                </div>

                <button type="submit" class="btn btn-primario" style="width: 100%; margin-top: 0.5rem;">
                    Guardar Nueva Tarifa
                </button>
            </form>
        </div>
    </div>

    <!-- Configurar Duraciones por Tipo de Vehiculo -->
    <div class="tarjeta">
        <div class="tarjeta-header">
            <h2 class="tarjeta-titulo">Configurar Duracion por Vehiculo</h2>
        </div>
        <div class="tarjeta-cuerpo">
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-bottom: 1.25rem;">
                Determina el tiempo estimado del turno para el calculo de ocupacion (duracion identica para normal y premium).
            </p>

            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/duraciones/modificar" method="POST">
                <?= $csrfCampo ?>

                <div class="grupo-formulario">
                    <label for="tipo_duracion_id" class="label-formulario">Tipo de Vehiculo *</label>
                    <select id="tipo_duracion_id" name="tipo_vehiculo_id" class="control-formulario" required onchange="actualizarInputDuracion(this);">
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?= (int)$t['id'] ?>" data-duracion="<?= (int)$t['duracion_minutos'] ?>">
                                <?= htmlspecialchars($t['nombre'], ENT_QUOTES, 'UTF-8') ?> (Actual: <?= (int)$t['duracion_minutos'] ?> min)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grupo-formulario">
                    <label for="duracion_minutos" class="label-formulario">Duracion en Minutos *</label>
                    <input type="number" step="15" min="15" max="240" id="duracion_minutos" name="duracion_minutos" class="control-formulario"
                           value="<?= (int)($tipos[0]['duracion_minutos'] ?? 60) ?>" required>
                    <span class="texto-ayuda">Recomendado en multiplos de 30 minutos (ej. 30, 60, 90).</span>
                </div>

                <button type="submit" class="btn btn-secundario" style="width: 100%; margin-top: 0.5rem;">
                    Actualizar Duracion
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Tabla de Tarifas Vigentes Actuales -->
<div class="tarjeta">
    <div class="tarjeta-header">
        <h2 class="tarjeta-titulo">Tarifario Vigente</h2>
    </div>
    <div class="tarjeta-cuerpo" style="padding: 0;">
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Servicio</th>
                        <th>Tipo de Vehiculo</th>
                        <th>Duracion Configurada</th>
                        <th>Precio Vigente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tarifas as $tf): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($tf['servicio_nombre'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($tf['tipo_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int)$tf['duracion_minutos'] ?> minutos</td>
                            <td style="font-size: 1.05rem; font-weight: 700; color: var(--color-acento);">
                                $<?= number_format((float)$tf['precio_vigente'], 2, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Historial de Precios -->
<div class="tarjeta">
    <div class="tarjeta-header">
        <h2 class="tarjeta-titulo">Historial Inmutable de Tarifas</h2>
    </div>
    <div class="tarjeta-cuerpo" style="padding: 0;">
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Servicio</th>
                        <th>Tipo de Vehiculo</th>
                        <th>Precio Registrado</th>
                        <th>Vigente Desde</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $h): ?>
                        <tr>
                            <td><?= htmlspecialchars($h['servicio_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($h['tipo_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>$<?= number_format((float)$h['precio'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($h['vigente_desde'])), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function actualizarInputDuracion(select) {
    const opt = select.options[select.selectedIndex];
    const dur = opt ? opt.dataset.duracion : '';
    if (dur) {
        document.getElementById('duracion_minutos').value = dur;
    }
}
</script>
