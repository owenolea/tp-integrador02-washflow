<?php
/**
 * WashFlow - Asistente de Reserva de Turnos en 4 Pasos (Diagrama 10)
 */
?>
<div class="tarjeta" style="max-width: 900px; margin: 0 auto;">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Asistente para Reserva de Turno</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Complete los 4 pasos para agendar su lavado con tarifa y duracion garantizadas.
            </p>
        </div>
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas" class="btn btn-secundario btn-sm">
            Cancelar
        </a>
    </div>

    <div class="tarjeta-cuerpo">
        <!-- Indicador de Pasos (Stepper) -->
        <div class="stepper">
            <div class="step-item activo" data-paso="1">
                <div class="step-numero">1</div>
                <span class="step-etiqueta">Vehiculo</span>
            </div>
            <div class="step-item" data-paso="2">
                <div class="step-numero">2</div>
                <span class="step-etiqueta">Servicio</span>
            </div>
            <div class="step-item" data-paso="3">
                <div class="step-numero">3</div>
                <span class="step-etiqueta">Fecha y Horario</span>
            </div>
            <div class="step-item" data-paso="4">
                <div class="step-numero">4</div>
                <span class="step-etiqueta">Confirmacion</span>
            </div>
        </div>

        <form id="asistente-reserva-form" action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/reservas/guardar" method="POST">
            <?= $csrfCampo ?>
            <input type="hidden" id="input-vehiculo-id" name="vehiculo_id" value="">
            <input type="hidden" id="input-servicio-id" name="servicio_id" value="">
            <input type="hidden" id="input-reserva-hora" name="hora_inicio" value="">

            <!-- PASO 1: SELECCIONAR VEHICULO -->
            <div class="panel-paso" id="panel-paso-1">
                <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto);">
                    Paso 1: Seleccione el vehiculo para el turno
                </h2>

                <?php if (empty($vehiculos)): ?>
                    <div style="padding: 2.5rem; text-align: center; background-color: var(--color-superficie-alt); border-radius: var(--radio-md); border: 1px dashed var(--color-borde);">
                        <p style="color: var(--color-texto-apagado); margin-bottom: 1rem;">No tiene vehiculos activos registrados para seleccionar.</p>
                        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/vehiculos/crear" class="btn btn-primario btn-sm">
                            Registrar un vehiculo ahora
                        </a>
                    </div>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                        <?php foreach ($vehiculos as $v): ?>
                            <?php
                            $patenteTexto = !empty($v['patente']) ? (string)$v['patente'] : 'S/Patente';
                            $vehiculoNombre = trim(($v['marca'] ?? '') . ' ' . ($v['modelo'] ?? ''));
                            $tipoNombre = (string)($v['tipo_nombre'] ?? 'Vehiculo');
                            $duracionMin = (int)($v['duracion_minutos'] ?? 60);
                            $infoCompleta = $vehiculoNombre . ' (' . $patenteTexto . ')';
                            ?>
                            <div class="card-seleccion-vehiculo franja-card"
                                 data-id="<?= (int)$v['id'] ?>"
                                 data-duracion="<?= $duracionMin ?>"
                                 data-tipo-nombre="<?= htmlspecialchars($tipoNombre, ENT_QUOTES, 'UTF-8') ?>"
                                 data-info="<?= htmlspecialchars($infoCompleta, ENT_QUOTES, 'UTF-8') ?>">
                                <div style="font-size: 1.1rem; font-weight: 700; color: var(--color-texto);">
                                    <?= htmlspecialchars($vehiculoNombre, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                                    <span class="badge badge-neutro" style="font-weight: 700;"><?= htmlspecialchars($patenteTexto, ENT_QUOTES, 'UTF-8') ?></span>
                                    <span style="font-size: 0.8rem; color: var(--color-texto-apagado);"><?= htmlspecialchars($tipoNombre, ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--color-texto-secundario); margin-top: 0.5rem;">
                                    Duracion estimada: <strong><?= $duracionMin ?> min</strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- PASO 2: SELECCIONAR SERVICIO -->
            <div class="panel-paso" id="panel-paso-2" style="display: none;">
                <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto);">
                    Paso 2: Seleccione el tipo de lavado
                </h2>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                    <?php foreach ($servicios as $s): ?>
                        <div class="card-seleccion-servicio franja-card"
                             data-id="<?= (int)$s['id'] ?>"
                             data-nombre="<?= htmlspecialchars($s['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                            <div style="font-size: 1.25rem; font-weight: 700; color: var(--color-texto); margin-bottom: 0.5rem;">
                                <?= htmlspecialchars($s['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <p style="font-size: 0.875rem; color: var(--color-texto-secundario); line-height: 1.5;">
                                <?= htmlspecialchars($s['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- PASO 3: SELECCIONAR FECHA Y FRANJA HORARIA -->
            <div class="panel-paso" id="panel-paso-3" style="display: none;">
                <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto);">
                    Paso 3: Seleccione fecha y horario con disponibilidad
                </h2>

                <div class="grupo-formulario" style="max-width: 320px;">
                    <label for="input-reserva-fecha" class="label-formulario">Fecha del turno *</label>
                    <input type="date" id="input-reserva-fecha" name="fecha" class="control-formulario"
                           min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
                    <span class="texto-ayuda">Horario de lunes a sabado, 10:00 a 18:00 hs. Domingos cerrado.</span>
                </div>

                <div style="margin-top: 1.5rem;">
                    <label class="label-formulario">Franjas horarias disponibles (Capacidad maxima: 3 vehiculos):</label>
                    <div id="contenedor-franjas-horarias" style="margin-top: 0.5rem;">
                        <p class="texto-ayuda">Seleccione una fecha para consultar la ocupacion en tiempo real.</p>
                    </div>
                </div>
            </div>

            <!-- PASO 4: CONFIRMACION DE TURNO -->
            <div class="panel-paso" id="panel-paso-4" style="display: none;">
                <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto);">
                    Paso 4: Verifique y confirme los datos de su turno
                </h2>

                <div class="tarjeta" style="background-color: var(--color-superficie-alt); border-style: dashed;">
                    <div class="tarjeta-cuerpo" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                        <div>
                            <span class="kpi-titulo">Vehiculo</span>
                            <div id="resumen-vehiculo" style="font-size: 1.05rem; font-weight: 700; margin-top: 0.25rem;">-</div>
                        </div>
                        <div>
                            <span class="kpi-titulo">Servicio Contratado</span>
                            <div id="resumen-servicio" style="font-size: 1.05rem; font-weight: 700; margin-top: 0.25rem;">-</div>
                        </div>
                        <div>
                            <span class="kpi-titulo">Fecha</span>
                            <div id="resumen-fecha" style="font-size: 1.05rem; font-weight: 700; margin-top: 0.25rem;">-</div>
                        </div>
                        <div>
                            <span class="kpi-titulo">Horario de Inicio</span>
                            <div id="resumen-hora" style="font-size: 1.05rem; font-weight: 700; margin-top: 0.25rem;">-</div>
                        </div>
                        <div>
                            <span class="kpi-titulo">Duracion Estimada</span>
                            <div id="resumen-duracion" style="font-size: 1.05rem; font-weight: 700; margin-top: 0.25rem;">-</div>
                        </div>
                        <div>
                            <span class="kpi-titulo">Precio Congelado</span>
                            <div id="resumen-precio" style="font-size: 1.25rem; font-weight: 800; color: var(--color-acento); margin-top: 0.25rem;">-</div>
                        </div>
                    </div>
                </div>

                <div class="alerta alerta-aviso" style="margin-top: 1rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span>El precio acordado y la duracion quedan congelados al momento de confirmar. Podra modificar o cancelar directamente con hasta 2 horas de anticipacion. El pago se realiza de manera presencial al retirar su vehiculo.</span>
                </div>
            </div>

            <!-- Botones de Navegacion del Wizard -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-borde);">
                <button type="button" id="btn-paso-anterior" class="btn btn-secundario" style="display: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    <span>Anterior</span>
                </button>

                <div style="margin-left: auto; display: flex; gap: 0.75rem;">
                    <button type="button" id="btn-paso-siguiente" class="btn btn-primario" disabled>
                        <span>Siguiente</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                    <button type="submit" id="btn-confirmar-reserva" class="btn btn-acento" style="display: none;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <span>Confirmar Reserva</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
