<?php
/**
 * WashFlow - Archivo de configuracion de ejemplo
 * Copiar como config.php y adaptar a su entorno.
 */

declare(strict_types=1);

return [
    // Entorno de la aplicacion (development | production)
    'entorno' => 'development',
    'depuracion' => true,

    // URL base de la aplicacion (sin barra final)
    // Ejemplo para XAMPP htdocs: 'http://localhost/washflow/public'
    // Ejemplo para servidor interno PHP: 'http://localhost:8000'
    'url_base' => 'http://localhost/washflow/public',

    // Base de datos (MySQL / MariaDB con PDO)
    'db' => [
        'host' => '127.0.0.1',
        'puerto' => 3306,
        'nombre' => 'washflow',
        'usuario' => 'root',
        'contrasena' => '',
        'charset' => 'utf8mb4'
    ],

    // Reglas de negocio obligatorias (definidas en TP Integrador)
    'negocio' => [
        'capacidad_maxima' => 3,              // Maximo 3 vehiculos en simultaneo
        'hora_apertura' => '10:00',           // 10:00 hs
        'hora_cierre' => '18:00',             // 18:00 hs
        'intervalo_minutos' => 30,            // Franjas de 30 minutos
        'dias_atencion' => [1, 2, 3, 4, 5, 6],// Lunes (1) a Sabado (6). Domingo (0) cerrado.
        'anticipacion_horas_cambio_directo' => 2 // Regla de 2 horas para cambio o cancelacion directa
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
