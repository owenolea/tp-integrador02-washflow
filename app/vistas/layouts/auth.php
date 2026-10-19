<!DOCTYPE html>
<html lang="es" data-tema="oscuro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'WashFlow - Acceso al Sistema', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/assets/css/app.css">
    <script>
        window.WASHFLOW_URL_BASE = <?= json_encode($urlBase, JSON_UNESCAPED_SLASHES) ?>;
    </script>
</head>
<body class="bg-pantalla-auth">
    <div class="contenedor-auth">
        <?php if (!empty($flashes)): ?>
            <div style="margin-bottom: 1rem;">
                <?php foreach ($flashes as $tipo => $mensajes): ?>
                    <?php
                    $claseAlerta = match ($tipo) {
                        'exito', 'success' => 'alerta-exito',
                        'error', 'danger' => 'alerta-error',
                        default => 'alerta-aviso'
                    };
                    ?>
                    <?php foreach ($mensajes as $msg): ?>
                        <div class="alerta <?= $claseAlerta ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <?php if ($tipo === 'exito' || $tipo === 'success'): ?>
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>
                                <?php elseif ($tipo === 'error' || $tipo === 'danger'): ?>
                                    <circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>
                                <?php else: ?>
                                    <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>
                                <?php endif; ?>
                            </svg>
                            <span><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= $contenido ?>
    </div>
    <script src="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/assets/js/app.js"></script>
</body>
</html>
