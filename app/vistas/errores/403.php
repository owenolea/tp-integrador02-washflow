<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso no autorizado (403) - WashFlow</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($urlBase ?? '', ENT_QUOTES, 'UTF-8') ?>/assets/css/app.css">
</head>
<body class="bg-pantalla-error">
    <div class="contenedor-error">
        <div class="tarjeta-error">
            <div class="icono-error">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <h1 class="codigo-error">403</h1>
            <h2 class="titulo-error">Acceso no autorizado</h2>
            <p class="descripcion-error"><?= htmlspecialchars($mensaje ?? 'No cuenta con los permisos necesarios para acceder a este recurso.', ENT_QUOTES, 'UTF-8') ?></p>
            <div class="acciones-error">
                <a href="<?= htmlspecialchars($urlBase ?? '/', ENT_QUOTES, 'UTF-8') ?>/" class="btn btn-primario">Ir al inicio</a>
                <a href="javascript:history.back()" class="btn btn-secundario">Regresar</a>
            </div>
        </div>
    </div>
</body>
</html>
