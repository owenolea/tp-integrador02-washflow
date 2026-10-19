<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error interno del servidor (500) - WashFlow</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($urlBase ?? '', ENT_QUOTES, 'UTF-8') ?>/assets/css/app.css">
</head>
<body class="bg-pantalla-error">
    <div class="contenedor-error">
        <div class="tarjeta-error">
            <div class="icono-error">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h1 class="codigo-error">500</h1>
            <h2 class="titulo-error">Error interno del servidor</h2>
            <p class="descripcion-error"><?= htmlspecialchars($mensaje ?? 'Ocurrio un inconveniente imprevisto. Por favor, intente nuevamente mas tarde.', ENT_QUOTES, 'UTF-8') ?></p>
            <div class="acciones-error">
                <a href="<?= htmlspecialchars($urlBase ?? '/', ENT_QUOTES, 'UTF-8') ?>/" class="btn btn-primario">Ir al inicio</a>
                <a href="javascript:location.reload()" class="btn btn-secundario">Reintentar</a>
            </div>
        </div>
    </div>
</body>
</html>
