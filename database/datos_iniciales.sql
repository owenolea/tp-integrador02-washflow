-- ============================================================================
-- PROYECTO: WashFlow
-- ARCHIVO: database/datos_iniciales.sql
-- DESCRIPCION: Datos iniciales del sistema (tipos de vehiculo, servicios,
--              precios vigentes y productos base de inventario).
-- NOTA: El administrador no se incluye aqui; se genera con scripts/crear_admin.php
-- ============================================================================

USE `washflow`;

-- 1. TIPOS DE VEHICULO
INSERT INTO `tipos_vehiculo` (`id`, `nombre`, `duracion_minutos`) VALUES
(1, 'Moto', 30),
(2, 'Auto', 60),
(3, 'Camioneta', 60),
(4, '4x4', 90)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `duracion_minutos` = VALUES(`duracion_minutos`);

-- 2. SERVICIOS
INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `activo`) VALUES
(1, 'Lavado normal', 'Lavado exterior con espuma activa, aspirado interior completo y silicona en cubiertas.', 1),
(2, 'Lavado premium', 'Lavado normal mas aplicacion de cera permanente y acondicionador de plasticos.', 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `descripcion` = VALUES(`descripcion`), `activo` = VALUES(`activo`);

-- 3. PRECIOS DE SERVICIO (con fecha de vigencia)
-- Normal (ID 1)
-- Moto: 12.000 / Auto: 15.000 / Camioneta: 20.000 / 4x4: 25.000
-- Premium (ID 2)
-- Moto: 17.000 / Auto: 20.000 / Camioneta: 25.000 / 4x4: 30.000
INSERT INTO `precios_servicio` (`servicio_id`, `tipo_vehiculo_id`, `precio`, `vigente_desde`) VALUES
(1, 1, 12000.00, '2026-01-01'),
(1, 2, 15000.00, '2026-01-01'),
(1, 3, 20000.00, '2026-01-01'),
(1, 4, 25000.00, '2026-01-01'),
(2, 1, 17000.00, '2026-01-01'),
(2, 2, 20000.00, '2026-01-01'),
(2, 3, 25000.00, '2026-01-01'),
(2, 4, 30000.00, '2026-01-01');
