<?php
/**
 * WashFlow - Busqueda de Clientes y Vehiculos
 */
?>
<div class="tarjeta">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Busqueda de Clientes y Patentes</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Busque clientes por nombre, apellido, correo, telefono o dominio de patente.
            </p>
        </div>
    </div>

    <div class="tarjeta-cuerpo">
        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/buscar" method="GET" style="display: flex; gap: 0.75rem; max-width: 650px; margin-bottom: 2rem;">
            <input type="text" name="q" class="control-formulario" placeholder="Buscar por telefono (ej: 11-4523-8899), patente (ej: AF184CD) o apellido (ej: Fernandez)..."
                   value="<?= htmlspecialchars($criterio, ENT_QUOTES, 'UTF-8') ?>" required autofocus>
            <button type="submit" class="btn btn-primario" style="white-space: nowrap;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <span>Buscar</span>
            </button>
        </form>

        <?php if ($criterio !== ''): ?>
            <?php if (empty($resultados)): ?>
                <div style="padding: 2.5rem; text-align: center; color: var(--color-texto-apagado); background-color: var(--color-superficie-alt); border-radius: var(--radio-md);">
                    <p>No se encontraron resultados que coincidan con "<strong><?= htmlspecialchars($criterio, ENT_QUOTES, 'UTF-8') ?></strong>".</p>
                    <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/lavado-directo" class="btn btn-acento btn-sm" style="margin-top: 1rem;">
                        Registrar como cliente ocasional en lavado sin reserva
                    </a>
                </div>
            <?php else: ?>
                <div class="tabla-contenedor">
                    <table class="tabla">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Tipo de Cuenta</th>
                                <th>Telefono</th>
                                <th>Correo</th>
                                <th>Vehiculos Asociados</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $u): ?>
                                <?php
                                $vehiculosUsuario = WashFlow\Modelos\Vehiculo::listarPorCliente((int)$u['id'], false);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($u['es_ocasional'])): ?>
                                            <span class="badge badge-alerta">Cliente Ocasional</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Cliente Registrado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($u['telefono'] ?? 'Sin telefono', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($u['correo'] ?? 'Sin correo', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php if (empty($vehiculosUsuario)): ?>
                                            <span style="font-size: 0.8rem; color: var(--color-texto-apagado);">Ninguno registrado</span>
                                        <?php else: ?>
                                            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                                <?php foreach ($vehiculosUsuario as $vu): ?>
                                                    <span class="badge badge-neutro">
                                                        <?= htmlspecialchars($vu['patente'] . ' (' . $vu['marca'] . ')', ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $queryParam = http_build_query([
                                            'nombre' => $u['nombre'] . ' ' . $u['apellido'],
                                            'telefono' => $u['telefono'] ?? '',
                                            'patente' => !empty($vehiculosUsuario[0]['patente']) ? $vehiculosUsuario[0]['patente'] : '',
                                            'marca' => !empty($vehiculosUsuario[0]['marca']) ? $vehiculosUsuario[0]['marca'] : '',
                                            'modelo' => !empty($vehiculosUsuario[0]['modelo']) ? $vehiculosUsuario[0]['modelo'] : '',
                                            'tipo_id' => !empty($vehiculosUsuario[0]['tipo_vehiculo_id']) ? $vehiculosUsuario[0]['tipo_vehiculo_id'] : ''
                                        ]);
                                        ?>
                                        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/empleado/lavado-directo?<?= htmlspecialchars($queryParam, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secundario btn-sm">
                                            Nuevo Lavado
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
