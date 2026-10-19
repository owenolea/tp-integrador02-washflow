<?php
/**
 * WashFlow - Vinculacion de Clientes Ocasionales a Cuentas Verificadas
 */
?>
<div class="tarjeta" style="max-width: 800px; margin: 0 auto;">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Vinculacion de Clientes Ocasionales</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Transfiera el historial y vehiculos de un cliente ocasional a su cuenta verificada.
            </p>
        </div>
        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios" class="btn btn-secundario btn-sm">
            Volver
        </a>
    </div>

    <div class="tarjeta-cuerpo">
        <div class="alerta alerta-info" style="margin-bottom: 1.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <div>
                <strong>Regla de Vinculacion:</strong> Al confirmar la identidad del cliente, todos los vehiculos registrados y reservas historicas del perfil ocasional se transferiran a su cuenta registrada definitiva. El registro ocasional se desactiva y se enlaza como vinculado.
            </div>
        </div>

        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/vinculaciones" method="POST" novalidate>
            <?= $csrfCampo ?>

            <div class="grupo-formulario">
                <label for="ocasional_id" class="label-formulario">1. Seleccione el Cliente Ocasional (Perfil Temporal) *</label>
                <select id="ocasional_id" name="ocasional_id" class="control-formulario" required>
                    <option value="">Seleccione el cliente ocasional...</option>
                    <?php foreach ($ocasionales as $oc): ?>
                        <option value="<?= (int)$oc['id'] ?>">
                            ID #<?= (int)$oc['id'] ?> — <?= htmlspecialchars($oc['nombre'] . ' ' . $oc['apellido'] . ' (' . ($oc['telefono'] ?? 'Sin tel.') . ')', ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grupo-formulario">
                <label for="cuenta_destino_id" class="label-formulario">2. Seleccione la Cuenta Registrada Verificada (Destino) *</label>
                <select id="cuenta_destino_id" name="cuenta_destino_id" class="control-formulario" required>
                    <option value="">Seleccione la cuenta de destino...</option>
                    <?php foreach ($clientesRegistrados as $cr): ?>
                        <option value="<?= (int)$cr['id'] ?>">
                            ID #<?= (int)$cr['id'] ?> — <?= htmlspecialchars($cr['nombre'] . ' ' . $cr['apellido'] . ' [' . $cr['correo'] . ']', ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/admin/usuarios" class="btn btn-secundario">Cancelar</a>
                <button type="submit" class="btn btn-primario" onclick="return confirm('¿Confirma la verificacion de identidad y la transferencia definitiva del historial?');">
                    Vincular y Verificar Identidad
                </button>
            </div>
        </form>
    </div>
</div>
