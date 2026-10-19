<?php
/**
 * WashFlow - Script de consola para creacion de Administrador inicial
 * Ejecutar con: php scripts/crear_admin.php
 */

declare(strict_types=1);

// Cargar configuracion y conexion
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/BaseDatos.php';

echo "========================================================\n";
echo " WashFlow - Alta de Administrador por Consola\n";
echo "========================================================\n\n";

// Verificar si se ejecuta por CLI
if (php_sapi_name() !== 'cli') {
    echo "ERROR: Este script solo puede ejecutarse desde la linea de comandos.\n";
    exit(1);
}

function leerEntrada(string $mensaje): string {
    echo $mensaje . ': ';
    $linea = fgets(STDIN);
    return trim($linea !== false ? $linea : '');
}

function leerContrasena(string $mensaje): string {
    echo $mensaje . ': ';
    // En Windows CLI basico fgets lee la entrada
    $linea = fgets(STDIN);
    return trim($linea !== false ? $linea : '');
}

$nombre = '';
while ($nombre === '') {
    $nombre = leerEntrada('Nombre');
    if ($nombre === '') {
        echo "[!] El nombre es obligatorio.\n";
    }
}

$apellido = '';
while ($apellido === '') {
    $apellido = leerEntrada('Apellido');
    if ($apellido === '') {
        echo "[!] El apellido es obligatorio.\n";
    }
}

$telefono = leerEntrada('Telefono (opcional)');

$correo = '';
while ($correo === '') {
    $correo = leerEntrada('Correo electronico');
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo "[!] Ingrese un correo electronico valido.\n";
        $correo = '';
    }
}

$contrasena = '';
while ($contrasena === '') {
    $c1 = leerContrasena('Contrasena (minimo 8 caracteres)');
    if (strlen($c1) < 8) {
        echo "[!] La contrasena debe contener al menos 8 caracteres.\n";
        continue;
    }
    $c2 = leerContrasena('Confirmar contrasena');
    if ($c1 !== $c2) {
        echo "[!] Las contrasenas no coinciden. Intente nuevamente.\n";
        continue;
    }
    $contrasena = $c1;
}

try {
    $db = WashFlow\Core\BaseDatos::getInstancia()->getConexion();

    // Verificar si el correo ya existe
    $stmt = $db->prepare('SELECT id, rol FROM usuarios WHERE correo = ?');
    $stmt->execute([$correo]);
    $existente = $stmt->fetch(\PDO::FETCH_ASSOC);

    if ($existente) {
        echo "\n[ERROR] Ya existe un usuario registrado con el correo: $correo\n";
        exit(1);
    }

    $hash = password_hash($contrasena, PASSWORD_BCRYPT);

    $stmtInsert = $db->prepare('
        INSERT INTO usuarios (rol, nombre, apellido, telefono, correo, contrasena_hash, activo, es_ocasional, identidad_verificada, creado_en)
        VALUES ("administrador", ?, ?, ?, ?, ?, 1, 0, 1, NOW())
    ');
    $stmtInsert->execute([$nombre, $apellido, $telefono ?: null, $correo, $hash]);
    $nuevoId = (int)$db->lastInsertId();

    // Registrar en auditoria
    $stmtAudit = $db->prepare('
        INSERT INTO registro_operaciones (usuario_id, accion, entidad, id_entidad, detalle, fecha_hora)
        VALUES (?, "CREAR_ADMIN", "Usuario", ?, ?, NOW())
    ');
    $stmtAudit->execute([$nuevoId, $nuevoId, "Administrador creado por consola: $nombre $apellido ($correo)"]);

    echo "\n--------------------------------------------------------\n";
    echo "[OK] Administrador creado exitosamente.\n";
    echo "ID: $nuevoId\n";
    echo "Nombre: $nombre $apellido\n";
    echo "Correo: $correo\n";
    echo "Rol: administrador\n";
    echo "--------------------------------------------------------\n\n";
    exit(0);
} catch (\Throwable $e) {
    echo "\n[ERROR] Ocurrio un problema al guardar el administrador: " . $e->getMessage() . "\n";
    exit(1);
}
