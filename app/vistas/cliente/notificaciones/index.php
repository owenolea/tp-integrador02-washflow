<?php
/**
 * WashFlow - Centro de Notificaciones del Cliente
 */
?>
<div class="tarjeta">
    <div class="tarjeta-header">
        <div>
            <h1 class="tarjeta-titulo">Centro de Notificaciones</h1>
            <p style="color: var(--color-texto-apagado); font-size: 0.85rem; margin-top: 0.2rem;">
                Historial de avisos sobre reservas, cambios de horario y estado de sus lavados.
            </p>
        </div>
        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/cliente/notificaciones/marcar-todas" method="POST">
            <?= $csrfCampo ?>
            <button type="submit" class="btn btn-secundario btn-sm">
                Marcar todas como leidas
            </button>
        </form>
    </div>

    <div class="tarjeta-cuerpo" style="padding: 0;">
        <?php if (empty($notificaciones)): ?>
            <div style="padding: 3rem; text-align: center; color: var(--color-texto-apagado);">
                <p>No tiene notificaciones recibidas.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column;">
                <?php foreach ($notificaciones as $n): ?>
                    <div style="padding: 1.25rem; border-bottom: 1px solid var(--color-borde-suave); display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; background-color: <?= empty($n['leida']) ? 'var(--color-primario-claro)' : 'transparent' ?>;">
                        <div style="display: flex; gap: 0.75rem;">
                            <div style="margin-top: 0.2rem; color: var(--color-acento);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                </svg>
                            </div>
                            <div>
                                <strong style="color: var(--color-texto); font-size: 0.95rem;"><?= htmlspecialchars($n['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <p style="color: var(--color-texto-secundario); font-size: 0.9rem; margin-top: 0.25rem; line-height: 1.5;">
                                    <?= htmlspecialchars($n['mensaje'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                                <span style="font-size: 0.75rem; color: var(--color-texto-apagado); margin-top: 0.35rem; display: inline-block;">
                                    <?= date('d/m/Y H:i', strtotime($n['fecha_hora'])) ?> hs
                                </span>
                            </div>
                        </div>

                        <?php if (empty($n['leida'])): ?>
                            <span class="badge badge-info">No leida</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
