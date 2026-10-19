<?php
/**
 * WashFlow - Administracion de Usuarios (ABM con Desactivacion Logica)
 */
?>
<div class="tarjeta">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Administracion de Usuarios y Empleados</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Gestion de cuentas del sistema. Recuerde: los usuarios nunca se borran fisicamente, solo se desactivan.
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/vinculaciones" class="btn btn-secundario btn-sm">
                <span>Vincular Ocasional</span>
            </a>
            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios/crear" class="btn btn-primario btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Nuevo Usuario</span>
            </a>
        </div>
    </div>

    <div class="tarjeta-cuerpo">
        <!-- Filtros de Busqueda -->
        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem; align-items: flex-end;">
            <div class="grupo-formulario" style="margin-bottom: 0;">
                <label for="rol" class="label-formulario">Rol:</label>
                <select id="rol" name="rol" class="control-formulario" style="width: auto;">
                    <option value="">Todos los roles</option>
                    <option value="cliente" <?= $rolFiltro === 'cliente' ? 'selected' : '' ?>>Clientes</option>
                    <option value="empleado" <?= $rolFiltro === 'empleado' ? 'selected' : '' ?>>Empleados</option>
                    <option value="administrador" <?= $rolFiltro === 'administrador' ? 'selected' : '' ?>>Administradores</option>
                </select>
            </div>

            <div class="grupo-formulario" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                <label for="q" class="label-formulario">Buscar:</label>
                <input type="text" id="q" name="q" class="control-formulario" placeholder="Nombre, apellido, correo o telefono..." value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <button type="submit" class="btn btn-secundario">Filtrar</button>
            <?php if ($rolFiltro !== '' || $busqueda !== ''): ?>
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios" class="btn btn-secundario">Limpiar</a>
            <?php endif; ?>
        </form>

        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre y Apellido</th>
                        <th>Rol</th>
                        <th>Contacto</th>
                        <th>Tipo / Verificacion</th>
                        <th>Estado</th>
                        <th>Fecha Alta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><strong>#<?= (int)$u['id'] ?></strong></td>
                            <td>
                                <strong><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </td>
                            <td>
                                <?php
                                $badgeRol = match ($u['rol']) {
                                    'administrador' => 'badge-peligro',
                                    'empleado' => 'badge-info',
                                    default => 'badge-neutro'
                                };
                                ?>
                                <span class="badge <?= $badgeRol ?>"><?= ucfirst($u['rol']) ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($u['correo'] ?? 'Sin correo', ENT_QUOTES, 'UTF-8') ?><br>
                                <span style="font-size: 0.75rem; color: var(--color-texto-apagado);"><?= htmlspecialchars($u['telefono'] ?? 'Sin telefono', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <?php if (!empty($u['es_ocasional'])): ?>
                                    <span class="badge badge-alerta">Ocasional</span>
                                <?php else: ?>
                                    <span class="badge badge-neutro">Cuenta Registrada</span>
                                <?php endif; ?>

                                <?php if (!empty($u['identidad_verificada'])): ?>
                                    <span class="badge badge-exito" title="Identidad Verificada">Verificada</span>
                                <?php endif; ?>

                                <?php if (!empty($u['usuario_vinculado_id'])): ?>
                                    <span class="badge badge-neutro" title="Vinculado a cuenta principal">Vinculado #<?= (int)$u['usuario_vinculado_id'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int)$u['activo'] === 1): ?>
                                    <span class="badge badge-exito">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-peligro">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($u['creado_en'])) ?></td>
                            <td>
                                <div style="display: flex; gap: 0.35rem;">
                                    <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios/<?= (int)$u['id'] ?>/editar" class="btn btn-secundario btn-sm">
                                        Editar
                                    </a>

                                    <?php if ((int)$u['id'] !== (int)$usuarioActual['id']): ?>
                                        <?php if ((int)$u['activo'] === 1): ?>
                                            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios/<?= (int)$u['id'] ?>/desactivar" method="POST" onsubmit="return confirm('¿Confirma que desea desactivar este usuario?');">
                                                <?= $csrfCampo ?>
                                                <button type="submit" class="btn btn-peligro btn-sm">Desactivar</button>
                                            </form>
                                        <?php else: ?>
                                            <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios/<?= (int)$u['id'] ?>/activar" method="POST">
                                                <?= $csrfCampo ?>
                                                <button type="submit" class="btn btn-primario btn-sm">Activar</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
