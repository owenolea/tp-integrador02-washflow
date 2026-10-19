<?php
/**
 * WashFlow - Configuracion principal del entorno local
 */

declare(strict_types=1);

// Deteccion dinamica de URL base amigable para XAMPP y php -S
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])) : '';
$scriptDir = rtrim($scriptDir, '/');
$urlDinamica = $protocolo . $host . $scriptDir;

return [
    'entorno' => 'development',
    'depuracion' => true,
    'timezone' => 'America/Argentina/Buenos_Aires',

    // URL base detectada automaticamente o personalizada
    'url_base' => $urlDinamica,

    // Base de datos (MySQL / MariaDB con PDO)
    'db' => [
        'host' => '127.0.0.1',
        'puerto' => 3306,
        'nombre' => 'washflow',
        'usuario' => 'root',
        'contrasena' => '',
        'charset' => 'utf8mb4'
    ],

    // Reglas de negocio obligatorias (fuente de verdad: diagramas y consigna)
    'negocio' => [
        'capacidad_maxima' => 3,              // Maximo 3 vehiculos en simultaneo
        'hora_apertura' => '10:00',           // 10:00 hs
        'hora_cierre' => '18:00',             // 18:00 hs
        'intervalo_minutos' => 30,            // Franjas de 30 minutos
        'dias_atencion' => [1, 2, 3, 4, 5, 6],// Lunes (1) a Sabado (6). Domingo (0) cerrado.
        'anticipacion_horas_cambio_directo' => 2 // Regla de 2 horas
    ],

    // Seguridad
    'seguridad' => [
        'nombre_sesion' => 'washflow_sess',
        'max_intentos_login' => 5,
        'minutos_bloqueo_login' => 15,
        'tiempo_token_recuperacion_minutos' => 60
    ],

    // Servicio de correo (desacoplado y desactivado por defecto)
    'correo' => [
        'activo' => false,
        'host' => 'smtp.ejemplo.com',
        'puerto' => 587,
        'seguridad' => 'tls',
        'usuario' => '',
        'contrasena' => '',
        'remitente_correo' => 'no-responder@washflow.com',
        'remitente_nombre' => 'WashFlow'
    ]
];
