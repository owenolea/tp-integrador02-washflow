<?php
/**
 * WashFlow - Registrar Lavado Sin Reserva Previa (Diagrama 07)
 */
?>
<div class="tarjeta" style="max-width: 680px; margin: 0 auto;">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Registrar Lavado Sin Reserva (Ingreso Directo)</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Atencion de vehiculos que llegan directamente al lavadero.
            </p>
        </div>
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/agenda" class="btn btn-secundario btn-sm">
            Volver a la Agenda
        </a>
    </div>

    <div class="tarjeta-cuerpo">
        <?php if (!empty($errorGeneral)): ?>
            <div class="alerta alerta-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><?= htmlspecialchars($errorGeneral, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/lavado-directo" method="POST" novalidate>
            <?= $csrfCampo ?>

            <div style="background-color: var(--color-superficie-alt); padding: 1.25rem; border-radius: var(--radio-md); margin-bottom: 1.5rem; border: 1px solid var(--color-borde-suave);">
                <h2 style="font-size: 1rem; font-weight: 700; color: var(--color-texto); margin-bottom: 1rem;">
                    1. Datos del Cliente
                </h2>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="grupo-formulario" style="margin-bottom: 0;">
                        <label for="nombre_cliente" class="label-formulario">Nombre y Apellido *</label>
                        <input type="text" id="nombre_cliente" name="nombre_cliente" class="control-formulario <?= !empty($errores['nombre_cliente']) ? 'con-error' : '' ?>"
                               value="<?= htmlspecialchars($valores['nombre_cliente'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required placeholder="Ej. Ramiro Gomez">
                        <?php if (!empty($errores['nombre_cliente'])): ?>
                            <span class="mensaje-error-campo"><?= htmlspecialchars($errores['nombre_cliente'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="grupo-formulario" style="margin-bottom: 0;">
                        <label for="telefono" class="label-formulario">Telefono de contacto</label>
                        <input type="tel" id="telefono" name="telefono" class="control-formulario"
                               value="<?= htmlspecialchars($valores['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ej. 11 5544 3322">
                    </div>
                </div>
            </div>

            <div style="background-color: var(--color-superficie-alt); padding: 1.25rem; border-radius: var(--radio-md); margin-bottom: 1.5rem; border: 1px solid var(--color-borde-suave);">
                <h2 style="font-size: 1rem; font-weight: 700; color: var(--color-texto); margin-bottom: 1rem;">
                    2. Datos del Vehiculo
                </h2>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="grupo-formulario" style="margin-bottom: 0;">
                        <label for="patente" class="label-formulario">Patente *</label>
                        <input type="text" id="patente" name="patente" class="control-formulario input-patente <?= !empty($errores['patente']) ? 'con-error' : '' ?>"
                               value="<?= htmlspecialchars($valores['patente'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required placeholder="Ej. AE321XY o ABC123" maxlength="7" pattern="[a-zA-Z0-9]{6,7}" title="Patente argentina valida (6 o 7 caracteres alfanumericos)" style="text-transform: uppercase;">
                        <?php if (!empty($errores['patente'])): ?>
                            <span class="mensaje-error-campo"><?= htmlspecialchars($errores['patente'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="grupo-formulario" style="margin-bottom: 0;">
                        <label for="tipo_vehiculo_id" class="label-formulario">Tipo de Vehiculo *</label>
                        <select id="tipo_vehiculo_id" name="tipo_vehiculo_id" class="control-formulario" required>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= (int)$t['id'] ?>" <?= ((string)($valores['tipo_vehiculo_id'] ?? '') === (string)$t['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['nombre'], ENT_QUOTES, 'UTF-8') ?> (<?= (int)$t['duracion_minutos'] ?> min)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="grupo-formulario" style="margin-bottom: 0;">
                        <label for="marca" class="label-formulario">Marca *</label>
                        <input type="text" id="marca" name="marca" class="control-formulario <?= !empty($errores['marca']) ? 'con-error' : '' ?>"
                               value="<?= htmlspecialchars($valores['marca'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required placeholder="Ej. Ford">
                    </div>

                    <div class="grupo-formulario" style="margin-bottom: 0;">
                        <label for="modelo" class="label-formulario">Modelo *</label>
                        <input type="text" id="modelo" name="modelo" class="control-formulario <?= !empty($errores['modelo']) ? 'con-error' : '' ?>"
                               value="<?= htmlspecialchars($valores['modelo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required placeholder="Ej. Focus">
                    </div>
                </div>
            </div>

            <div style="background-color: var(--color-superficie-alt); padding: 1.25rem; border-radius: var(--radio-md); margin-bottom: 1.5rem; border: 1px solid var(--color-borde-suave);">
                <h2 style="font-size: 1rem; font-weight: 700; color: var(--color-texto); margin-bottom: 1rem;">
                    3. Servicio Solicitado
                </h2>

                <div class="grupo-formulario" style="margin-bottom: 0;">
                    <label for="servicio_id" class="label-formulario">Servicio *</label>
                    <select id="servicio_id" name="servicio_id" class="control-formulario" required>
                        <?php foreach ($servicios as $s): ?>
                            <option value="<?= (int)$s['id'] ?>">
                                <?= htmlspecialchars($s['nombre'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($s['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="alerta alerta-aviso" style="font-size: 0.85rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>El sistema verificara que en este momento no se supere la capacidad maxima de 3 vehiculos simultaneos antes de registrar el ingreso.</span>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/agenda" class="btn btn-secundario">Cancelar</a>
                <button type="submit" class="btn btn-acento">Registrar Ingreso de Lavado</button>
            </div>
        </form>
    </div>
</div>
