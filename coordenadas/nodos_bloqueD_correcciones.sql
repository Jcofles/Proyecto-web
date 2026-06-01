-- ============================================================
-- Bloque D — Nodos de corredor y salones (Piso 1)
-- Coordenadas derivadas de los centroides de los polígonos
-- en salonesBloquedD.js para que coincidan con la vista gráfica.
-- Ejecutar sobre la BD mapeo_itfip DESPUÉS del volcado base.
-- ============================================================

USE `mapeo_itfip`;

-- ------------------------------------------------------------
-- 1. Eliminar nodos anteriores del Bloque D interior (si existen)
--    para evitar duplicados. Solo aplica IDs >= 52 con nombre Bloque D.
-- ------------------------------------------------------------
DELETE FROM `conexiones`
  WHERE `nodo_origen_id` >= 52 OR `nodo_destino_id` >= 52;

DELETE FROM `nodos` WHERE `id` >= 52;

-- Resetear AUTO_INCREMENT
ALTER TABLE `nodos` AUTO_INCREMENT = 52;
ALTER TABLE `conexiones` AUTO_INCREMENT = 51;

-- ------------------------------------------------------------
-- 2. Nodos de corredor (tipo_id = 2 = pasillo, piso = 1)
--    Pasillo principal horizontal de Bloque D.
--    Lat fija ~4.15657 (entre fila superior e inferior de salones).
--    Junctions a lo largo del pasillo de oeste a este.
-- ------------------------------------------------------------
INSERT INTO `nodos` (`id`,`user_id`,`nombre`,`latitud`,`longitud`,`tipo_id`,`piso`,`created_at`,`updated_at`) VALUES
(52, NULL, 'Pasillo D - Entrada',  4.15655000, -74.89768000, 2, 1, NOW(), NOW()),
(53, NULL, 'Pasillo D - J1',       4.15657000, -74.89762000, 2, 1, NOW(), NOW()),
(54, NULL, 'Pasillo D - J2',       4.15657000, -74.89758000, 2, 1, NOW(), NOW()),
(55, NULL, 'Pasillo D - J3',       4.15657000, -74.89752000, 2, 1, NOW(), NOW()),
(56, NULL, 'Pasillo D - J4',       4.15657000, -74.89748000, 2, 1, NOW(), NOW()),
(57, NULL, 'Pasillo D - J5',       4.15657000, -74.89744000, 2, 1, NOW(), NOW()),
(58, NULL, 'Pasillo D - J6',       4.15657000, -74.89740000, 2, 1, NOW(), NOW());

-- ------------------------------------------------------------
-- 3. Nodos de salones y servicios (centroide de cada polígono)
--    tipo_id: 1=salon, 3=baño, 4=escaleras
-- ------------------------------------------------------------
INSERT INTO `nodos` (`id`,`user_id`,`nombre`,`latitud`,`longitud`,`tipo_id`,`piso`,`created_at`,`updated_at`) VALUES
(59, NULL, 'Salón D 101',  4.15650016, -74.89754316, 1, 1, NOW(), NOW()),
(60, NULL, 'Salón D 102',  4.15650298, -74.89749188, 1, 1, NOW(), NOW()),
(61, NULL, 'Salón D 103',  4.15648137, -74.89741781, 1, 1, NOW(), NOW()),
(62, NULL, 'Salón D 104',  4.15651953, -74.89741176, 1, 1, NOW(), NOW()),
(63, NULL, 'Salón D 105',  4.15656386, -74.89740441, 1, 1, NOW(), NOW()),
(64, NULL, 'Salón D 106',  4.15661588, -74.89739692, 1, 1, NOW(), NOW()),
(65, NULL, 'Salón D 107',  4.15662502, -74.89744101, 1, 1, NOW(), NOW()),
(66, NULL, 'Salón D 108',  4.15663185, -74.89748452, 1, 1, NOW(), NOW()),
(67, NULL, 'Salón D 109',  4.15662388, -74.89753213, 1, 1, NOW(), NOW()),
(68, NULL, 'Salón D 110',  4.15661579, -74.89758357, 1, 1, NOW(), NOW()),
(69, NULL, 'Salón D 111',  4.15652584, -74.89762227, 1, 1, NOW(), NOW()),
(70, NULL, 'Baños D',      4.15651397, -74.89758775, 3, 1, NOW(), NOW()),
(71, NULL, 'Escaleras D',  4.15650412, -74.89745298, 4, 1, NOW(), NOW());

-- ------------------------------------------------------------
-- 4. Conexiones del Bloque D
--    Distancias en metros (Haversine aproximado).
--    La ruta entra por nodo 44 (Entrada Bloque 2 exterior).
-- ------------------------------------------------------------

-- Cadena principal del pasillo (oeste → este)
INSERT INTO `conexiones` (`id`,`nodo_origen_id`,`nodo_destino_id`,`distancia`,`created_at`,`updated_at`) VALUES
-- Desde nodo exterior 44 (Entrada Bloque 2) hasta entrada del pasillo
(51,  44, 52,  6.23, NOW(), NOW()),
-- Pasillo interno
(52,  52, 53,  7.01, NOW(), NOW()),
(53,  53, 54,  4.43, NOW(), NOW()),
(54,  54, 55,  6.65, NOW(), NOW()),
(55,  55, 56,  4.43, NOW(), NOW()),
(56,  56, 57,  4.43, NOW(), NOW()),
(57,  57, 58,  4.43, NOW(), NOW()),

-- Ramas desde J1 (lng -74.89762): acceso a D111
(58,  53, 69,  4.91, NOW(), NOW()),

-- Ramas desde J2 (lng -74.89758): D110 (norte), D101 (sur), Baños (sur)
(59,  54, 68,  5.09, NOW(), NOW()),
(60,  54, 59,  8.77, NOW(), NOW()),
(61,  54, 70,  6.23, NOW(), NOW()),

-- Ramas desde J3 (lng -74.89752): D109 (norte), D102 (sur)
(62,  55, 67,  5.99, NOW(), NOW()),
(63,  55, 60,  8.08, NOW(), NOW()),

-- Ramas desde J4 (lng -74.89748): D108 (norte)
(64,  56, 66,  6.87, NOW(), NOW()),

-- Ramas desde J5 (lng -74.89744): D107 (norte), Escaleras (sur), D104 (sur)
(65,  57, 65,  6.11, NOW(), NOW()),
(66,  57, 71,  7.32, NOW(), NOW()),
(67,  57, 62,  6.42, NOW(), NOW()),

-- Ramas desde J6 (lng -74.89740): D106 (norte), D105 (corredor), D103 (sur)
(68,  58, 64,  5.10, NOW(), NOW()),
(69,  58, 63,  0.61, NOW(), NOW()),
(70,  58, 61,  9.85, NOW(), NOW());

-- ------------------------------------------------------------
-- 5. Verificación rápida
-- ------------------------------------------------------------
SELECT COUNT(*) AS total_nodos_bloqueD FROM `nodos` WHERE `id` >= 52;
SELECT COUNT(*) AS total_conexiones_bloqueD FROM `conexiones` WHERE `id` >= 51;
