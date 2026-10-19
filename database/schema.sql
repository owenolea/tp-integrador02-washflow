-- ============================================================================
-- PROYECTO: WashFlow - Sistema de Administracion de Lavadero de Vehiculos
-- ARCHIVO: database/schema.sql
-- MOTOR: MySQL / MariaDB (InnoDB, utf8mb4)
-- NOTA: Respeta estrictamente los diagramas UML (04a y 04b) y requerimientos.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `washflow` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `washflow`;

-- Desactivar chequeo de claves foraneas durante creacion
SET FOREIGN_KEY_CHECKS = 0;

-- 1. USUARIOS
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rol` ENUM('cliente', 'empleado', 'administrador') NOT NULL DEFAULT 'cliente',
    `nombre` VARCHAR(100) NOT NULL,
    `apellido` VARCHAR(100) NOT NULL,
    `telefono` VARCHAR(30) NULL,
    `correo` VARCHAR(150) NULL UNIQUE,
    `contrasena_hash` VARCHAR(255) NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `es_ocasional` TINYINT(1) NOT NULL DEFAULT 0,
    `identidad_verificada` TINYINT(1) NOT NULL DEFAULT 0,
    `usuario_vinculado_id` INT NULL,
    `intentos_fallidos` INT NOT NULL DEFAULT 0,
    `bloqueado_hasta` DATETIME NULL,
    `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_usuarios_vinculado` FOREIGN KEY (`usuario_vinculado_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
    INDEX `idx_usuarios_rol` (`rol`),
    INDEX `idx_usuarios_telefono` (`telefono`),
    INDEX `idx_usuarios_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TIPOS DE VEHICULO
DROP TABLE IF EXISTS `tipos_vehiculo`;
CREATE TABLE `tipos_vehiculo` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(50) NOT NULL,
    `duracion_minutos` INT NOT NULL DEFAULT 60,
    CONSTRAINT `chk_tipo_duracion` CHECK (`duracion_minutos` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. VEHICULOS
DROP TABLE IF EXISTS `vehiculos`;
CREATE TABLE `vehiculos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cliente_id` INT NOT NULL,
    `tipo_vehiculo_id` INT NOT NULL,
    `patente` VARCHAR(20) NULL,
    `marca` VARCHAR(80) NOT NULL,
    `modelo` VARCHAR(80) NOT NULL,
    `tipo_a_confirmar` TINYINT(1) NOT NULL DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_vehiculos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`),
    CONSTRAINT `fk_vehiculos_tipo` FOREIGN KEY (`tipo_vehiculo_id`) REFERENCES `tipos_vehiculo` (`id`),
    INDEX `idx_vehiculos_cliente` (`cliente_id`),
    INDEX `idx_vehiculos_patente` (`patente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. SERVICIOS
DROP TABLE IF EXISTS `servicios`;
CREATE TABLE `servicios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` TEXT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. PRECIOS DE SERVICIO (con historial)
DROP TABLE IF EXISTS `precios_servicio`;
CREATE TABLE `precios_servicio` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `servicio_id` INT NOT NULL,
    `tipo_vehiculo_id` INT NOT NULL,
    `precio` DECIMAL(10,2) NOT NULL,
    `vigente_desde` DATE NOT NULL,
    CONSTRAINT `fk_precios_servicio` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`),
    CONSTRAINT `fk_precios_tipo` FOREIGN KEY (`tipo_vehiculo_id`) REFERENCES `tipos_vehiculo` (`id`),
    CONSTRAINT `chk_precio_positivo` CHECK (`precio` > 0),
    INDEX `idx_precios_vigencia` (`servicio_id`, `tipo_vehiculo_id`, `vigente_desde`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. RESERVAS
DROP TABLE IF EXISTS `reservas`;
CREATE TABLE `reservas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(20) NOT NULL UNIQUE,
    `token_publico` VARCHAR(64) NULL UNIQUE,
    `token_vence` DATETIME NULL,
    `vehiculo_id` INT NOT NULL,
    `servicio_id` INT NOT NULL,
    `fecha` DATE NOT NULL,
    `hora_inicio` TIME NOT NULL,
    `hora_fin` TIME NOT NULL,
    `duracion_minutos` INT NOT NULL,
    `precio_acordado` DECIMAL(10,2) NOT NULL,
    `estado` ENUM('reservada', 'cancelada', 'ausente') NOT NULL DEFAULT 'reservada',
    `contacto_nombre` VARCHAR(100) NULL,
    `contacto_telefono` VARCHAR(30) NULL,
    `contacto_correo` VARCHAR(150) NULL,
    `creada_por` INT NULL,
    `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_reservas_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`),
    CONSTRAINT `fk_reservas_servicio` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`),
    CONSTRAINT `fk_reservas_creador` FOREIGN KEY (`creada_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
    CONSTRAINT `chk_reserva_precio` CHECK (`precio_acordado` > 0),
    CONSTRAINT `chk_reserva_duracion` CHECK (`duracion_minutos` > 0),
    INDEX `idx_reservas_codigo` (`codigo`),
    INDEX `idx_reservas_token` (`token_publico`),
    INDEX `idx_reservas_telefono` (`contacto_telefono`),
    INDEX `idx_reservas_fecha_horario` (`fecha`, `hora_inicio`, `hora_fin`, `estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. SOLICITUDES DE RESERVA
DROP TABLE IF EXISTS `solicitudes_reserva`;
CREATE TABLE `solicitudes_reserva` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reserva_id` INT NOT NULL,
    `tipo` ENUM('cambio', 'cancelacion') NOT NULL,
    `nueva_fecha` DATE NULL,
    `nueva_hora` TIME NULL,
    `motivo` TEXT NOT NULL,
    `estado` ENUM('pendiente', 'aprobada', 'rechazada') NOT NULL DEFAULT 'pendiente',
    `resuelta_por` INT NULL,
    `fecha_solicitud` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fecha_resolucion` DATETIME NULL,
    `observacion_resolucion` TEXT NULL,
    CONSTRAINT `fk_solicitudes_reserva` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`),
    CONSTRAINT `fk_solicitudes_resolutor` FOREIGN KEY (`resuelta_por`) REFERENCES `usuarios` (`id`),
    INDEX `idx_solicitudes_estado` (`estado`),
    INDEX `idx_solicitudes_reserva` (`reserva_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. BLOQUEOS DE HORARIO
DROP TABLE IF EXISTS `bloqueos_horario`;
CREATE TABLE `bloqueos_horario` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fecha` DATE NOT NULL,
    `hora_inicio` TIME NOT NULL,
    `hora_fin` TIME NOT NULL,
    `motivo` VARCHAR(255) NOT NULL,
    `creado_por` INT NOT NULL,
    `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_bloqueos_creador` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`),
    INDEX `idx_bloqueos_fecha` (`fecha`, `hora_inicio`, `hora_fin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. LAVADOS (Sin estado "En proceso": solo pendiente y terminado)
DROP TABLE IF EXISTS `lavados`;
CREATE TABLE `lavados` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reserva_id` INT NULL,
    `vehiculo_id` INT NOT NULL,
    `servicio_id` INT NOT NULL,
    `precio` DECIMAL(10,2) NOT NULL,
    `hora_llegada` TIME NOT NULL,
    `hora_inicio` TIME NULL,
    `hora_fin` TIME NULL,
    `fecha` DATE NOT NULL,
    `estado` ENUM('pendiente', 'terminado') NOT NULL DEFAULT 'pendiente',
    `registrado_por` INT NOT NULL,
    `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_lavados_reserva` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`),
    CONSTRAINT `fk_lavados_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`),
    CONSTRAINT `fk_lavados_servicio` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`),
    CONSTRAINT `fk_lavados_registrador` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`),
    CONSTRAINT `chk_lavado_precio` CHECK (`precio` > 0),
    INDEX `idx_lavados_reserva` (`reserva_id`),
    INDEX `idx_lavados_fecha` (`fecha`, `estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. NOTIFICACIONES
DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE `notificaciones` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `titulo` VARCHAR(150) NOT NULL,
    `mensaje` TEXT NOT NULL,
    `leida` TINYINT(1) NOT NULL DEFAULT 0,
    `canal` ENUM('interna', 'correo') NOT NULL DEFAULT 'interna',
    `fecha_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `reserva_id` INT NULL,
    CONSTRAINT `fk_notificaciones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
    CONSTRAINT `fk_notificaciones_reserva` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE SET NULL,
    INDEX `idx_notificaciones_usuario` (`usuario_id`, `leida`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. REGISTRO DE OPERACIONES (Auditoria y trazabilidad)
DROP TABLE IF EXISTS `registro_operaciones`;
CREATE TABLE `registro_operaciones` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NULL,
    `accion` VARCHAR(100) NOT NULL,
    `entidad` VARCHAR(100) NOT NULL,
    `id_entidad` INT NULL,
    `detalle` TEXT NOT NULL,
    `fecha_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_registro_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
    INDEX `idx_registro_entidad` (`entidad`, `id_entidad`),
    INDEX `idx_registro_fecha` (`fecha_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. RECUPERACIONES DE CONTRASENA (Tokens con expiracion)
DROP TABLE IF EXISTS `recuperaciones_contrasena`;
CREATE TABLE `recuperaciones_contrasena` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `token` VARCHAR(64) NOT NULL UNIQUE,
    `expira_en` DATETIME NOT NULL,
    `usado` TINYINT(1) NOT NULL DEFAULT 0,
    `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_recuperaciones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
    INDEX `idx_recuperaciones_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. MODELOS DE VEHICULO (Para buscador inteligente de categorias)
DROP TABLE IF EXISTS `modelos_vehiculo`;
CREATE TABLE `modelos_vehiculo` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `marca` VARCHAR(80) NOT NULL,
    `modelo` VARCHAR(100) NOT NULL,
    `tipo_vehiculo_id` INT NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_modelos_tipo` FOREIGN KEY (`tipo_vehiculo_id`) REFERENCES `tipos_vehiculo` (`id`),
    INDEX `idx_modelos_busqueda` (`marca`, `modelo`),
    INDEX `idx_modelos_tipo` (`tipo_vehiculo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. PREGUNTAS FRECUENTES (FAQ Administrables)
DROP TABLE IF EXISTS `preguntas_frecuentes`;
CREATE TABLE `preguntas_frecuentes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `pregunta` VARCHAR(255) NOT NULL,
    `respuesta` TEXT NOT NULL,
    `orden` INT NOT NULL DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_preguntas_orden` (`orden`, `activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. GALERIA DE TRABAJOS (Fotos y antes/despues administrables)
DROP TABLE IF EXISTS `galeria`;
CREATE TABLE `galeria` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(150) NOT NULL,
    `descripcion` VARCHAR(255) NULL,
    `imagen_url` VARCHAR(255) NOT NULL,
    `categoria` VARCHAR(50) NOT NULL DEFAULT 'trabajos',
    `orden` INT NOT NULL DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_galeria_orden` (`orden`, `activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reactivar chequeo de claves foraneas
SET FOREIGN_KEY_CHECKS = 1;
