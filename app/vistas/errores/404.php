<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagina no encontrada (404) - WashFlow</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($urlBase ?? '', ENT_QUOTES, 'UTF-8') ?>/assets/css/app.css">
</head>
<body class="bg-pantalla-error">
    <div class="contenedor-error">
        <div class="tarjeta-error">
            <div class="icono-error">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h1 class="codigo-error">404</h1>
            <h2 class="titulo-error">Pagina no encontrada</h2>
            <p class="descripcion-error"><?= htmlspecialchars($mensaje ?? 'La direccion solicitada no existe o fue trasladada.', ENT_QUOTES, 'UTF-8') ?></p>
            <div class="acciones-error">
                <a href="<?= htmlspecialchars($urlBase ?? '/', ENT_QUOTES, 'UTF-8') ?>/" class="btn btn-primario">Ir al inicio</a>
                <a href="javascript:history.back()" class="btn btn-secundario">Regresar</a>
            </div>
        </div>
    </div>
</body>
</html>
