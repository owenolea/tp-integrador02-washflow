<?php
/**
 * WashFlow - Bloqueos de Horario (Diagrama 04a)
 */
?>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Formulario para Nuevo Bloqueo -->
    <div class="tarjeta">
        <div class="tarjeta-header">
            <h1 class="tarjeta-titulo">Nuevo Bloqueo de Horario</h1>
        </div>
        <div class="tarjeta-cuerpo">
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-bottom: 1.25rem;">
                Inhabilita turnos para mantenimiento de equipos, desinfeccion o feriados. Los horarios bloqueados no podran ser reservados.
            </p>

            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/bloqueos" method="POST" novalidate>
                <?= $csrfCampo ?>

                <div class="grupo-formulario">
                    <label for="fecha" class="label-formulario">Fecha a Bloquear *</label>
                    <input type="date" id="fecha" name="fecha" class="control-formulario" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="grupo-formulario">
                        <label for="hora_inicio" class="label-formulario">Hora Inicio *</label>
                        <input type="time" id="hora_inicio" name="hora_inicio" class="control-formulario" min="10:00" max="18:00" step="1800" value="10:00" required>
                    </div>

                    <div class="grupo-formulario">
                        <label for="hora_fin" class="label-formulario">Hora Fin *</label>
                        <input type="time" id="hora_fin" name="hora_fin" class="control-formulario" min="10:00" max="18:00" step="1800" value="13:00" required>
                    </div>
                </div>

                <div class="grupo-formulario">
                    <label for="motivo" class="label-formulario">Motivo del Bloqueo *</label>
                    <input type="text" id="motivo" name="motivo" class="control-formulario" required placeholder="Ej. Mantenimiento compresores / Feriado nacional">
                </div>

                <button type="submit" class="btn btn-primario" style="width: 100%; margin-top: 0.5rem;">
                    Inhabilitar Horario
                </button>
            </form>
        </div>
    </div>

    <!-- Lista de Bloqueos Futuros -->
    <div class="tarjeta">
        <div class="tarjeta-header">
            <h2 class="tarjeta-titulo">Bloqueos Activos y Futuros</h2>
        </div>
        <div class="tarjeta-cuerpo" style="padding: 0;">
            <?php if (empty($bloqueos)): ?>
                <div style="padding: 2.5rem; text-align: center; color: var(--color-texto-apagado);">
                    <p>No hay bloqueos programados actualmente.</p>
                </div>
            <?php else: ?>
                <div class="tabla-contenedor">
                    <table class="tabla">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Franja Horaria</th>
                                <th>Motivo</th>
                                <th>Registrado Por</th>
                                <th>Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bloqueos as $b): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars(date('d/m/Y', strtotime($b['fecha'])), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    <td><?= substr($b['hora_inicio'], 0, 5) ?> a <?= substr($b['hora_fin'], 0, 5) ?> hs</td>
                                    <td><?= htmlspecialchars($b['motivo'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($b['creador_nombre'] . ' ' . $b['creador_apellido'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/bloqueos/<?= (int)$b['id'] ?>/eliminar" method="POST" onsubmit="return confirm('¿Confirma que desea rehabilitar esta franja horaria?');">
                                            <?= $csrfCampo ?>
                                            <button type="submit" class="btn btn-peligro btn-sm">Liberar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
