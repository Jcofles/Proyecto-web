-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 23-04-2026 a las 18:01:51
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `mapeo_itfip`
--
CREATE DATABASE IF NOT EXISTS `mapeo_itfip` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `mapeo_itfip`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conexiones`
--

DROP TABLE IF EXISTS `conexiones`;
CREATE TABLE IF NOT EXISTS `conexiones` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nodo_origen_id` bigint UNSIGNED NOT NULL,
  `nodo_destino_id` bigint UNSIGNED NOT NULL,
  `distancia` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conexiones_nodo_origen_id_nodo_destino_id_unique` (`nodo_origen_id`,`nodo_destino_id`),
  KEY `fk_conn_dst` (`nodo_destino_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `conexiones`
--

INSERT INTO `conexiones` (`id`, `nodo_origen_id`, `nodo_destino_id`, `distancia`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 11.81, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(2, 2, 3, 11.97, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(3, 3, 4, 11.98, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(4, 4, 5, 7.61, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(5, 5, 6, 15.99, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(6, 6, 7, 6.06, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(7, 7, 8, 17.76, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(8, 8, 9, 5.4, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(9, 9, 10, 7.32, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(10, 10, 11, 2.46, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(11, 11, 12, 15.47, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(12, 12, 13, 10.06, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(13, 13, 14, 17.67, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(14, 14, 15, 15.55, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(15, 15, 16, 14.75, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(16, 16, 17, 11.51, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(17, 17, 18, 1.01, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(18, 18, 19, 7.2, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(19, 19, 20, 13.8, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(20, 20, 21, 10.93, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(21, 21, 22, 6.64, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(22, 22, 23, 7.5, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(23, 23, 24, 9.37, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(24, 24, 25, 15.41, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(25, 25, 26, 13.24, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(26, 26, 27, 8.38, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(27, 27, 28, 3.77, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(28, 28, 29, 8.35, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(29, 29, 30, 10.55, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(30, 30, 31, 11.35, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(31, 31, 32, 16.13, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(32, 32, 33, 10.81, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(33, 33, 34, 7.86, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(34, 34, 35, 3.07, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(35, 35, 36, 3.28, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(36, 36, 37, 14.16, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(37, 37, 38, 5.55, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(38, 38, 39, 3.44, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(39, 39, 40, 5.06, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(40, 40, 41, 2.53, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(41, 41, 42, 4.78, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(42, 42, 43, 9.53, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(43, 43, 44, 3.18, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(44, 44, 45, 4.09, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(45, 45, 46, 11.07, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(46, 46, 47, 14.9, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(47, 47, 48, 14.36, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(48, 48, 49, 8.51, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(49, 49, 50, 6.48, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(50, 50, 51, 6.61, '2026-03-06 20:57:51', '2026-03-06 20:57:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deleted_users_log`
--

DROP TABLE IF EXISTS `deleted_users_log`;
CREATE TABLE IF NOT EXISTS `deleted_users_log` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modified_email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_by` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_deleted_log_u_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `email_change_codes`
--

DROP TABLE IF EXISTS `email_change_codes`;
CREATE TABLE IF NOT EXISTS `email_change_codes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `new_email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_change_codes_user_id_unique` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` int NOT NULL DEFAULT '0',
  `blocked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login_attempts_email_index` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `email`, `attempts`, `blocked_until`, `created_at`, `updated_at`) VALUES
(8, 'rubenmendoza427@gmail.com', 5, '2026-03-07 04:27:18', '2026-03-07 04:12:08', '2026-03-07 04:12:18'),
(11, 'lbarreto42@itfip.edu.co', 3, NULL, '2026-03-14 01:49:39', '2026-03-14 01:50:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_02_15_000000_create_nodos_table', 1),
(5, '2026_02_15_000001_create_conexiones_table', 1),
(6, '2026_02_15_211116_create_personal_access_tokens_table', 1),
(7, '2026_03_01_000000_add_email_verification_to_users', 1),
(8, '2026_03_02_000000_create_pending_users_table', 1),
(9, '2026_03_03_000000_create_password_reset_codes_table', 1),
(10, '2026_03_04_000000_add_status_to_users_table', 1),
(11, '2026_03_05_000000_add_soft_deletes_to_users_table', 1),
(12, '2026_03_06_000000_modify_status_enum_in_users_table', 1),
(13, '2026_03_07_000000_create_deleted_users_log_table', 1),
(14, '2026_03_08_000000_create_email_change_codes_table', 1),
(15, '2026_03_09_000000_create_login_attempts_table', 1),
(16, '2026_03_10_000000_split_name_into_nombres_apellidos', 1),
(17, '2026_03_11_000000_create_nodo_tipos_table', 1),
(18, '2026_03_11_000001_create_user_status_table', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nodos`
--

DROP TABLE IF EXISTS `nodos`;
CREATE TABLE IF NOT EXISTS `nodos` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitud` decimal(10,8) NOT NULL,
  `longitud` decimal(11,8) NOT NULL,
  `tipo_id` bigint UNSIGNED NOT NULL,
  `piso` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_nodos_tipo_id_new` (`tipo_id`),
  KEY `fk_nodos_usuario_creador` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `nodos`
--

INSERT INTO `nodos` (`id`, `user_id`, `nombre`, `latitud`, `longitud`, `tipo_id`, `piso`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Entrada Principal', 4.15402640, -74.89564350, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(2, NULL, 'Paso 2', 4.15409380, -74.89572580, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(3, NULL, 'Paso 3', 4.15419060, -74.89577310, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(4, NULL, 'Paso 4', 4.15426590, -74.89585030, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(5, NULL, 'Paso 5', 4.15431860, -74.89589410, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(6, NULL, 'Paso 6', 4.15444080, -74.89597010, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(7, NULL, 'Paso 7', 4.15444130, -74.89602470, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(8, NULL, 'Paso 8', 4.15458340, -74.89609780, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(9, NULL, 'Paso 9', 4.15460250, -74.89614260, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(10, NULL, 'Paso 10', 4.15466250, -74.89611540, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(11, NULL, 'Paso 11', 4.15467930, -74.89610100, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(12, NULL, 'Paso 12', 4.15476320, -74.89621230, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(13, NULL, 'Paso 13', 4.15478590, -74.89630010, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(14, NULL, 'Paso 14', 4.15493860, -74.89634430, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(15, NULL, 'Paso 15', 4.15505250, -74.89642560, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(16, NULL, 'Paso 16', 4.15517390, -74.89647910, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(17, NULL, 'Punto Central', 4.15523300, -74.89656430, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(18, NULL, 'Paso 18', 4.15522400, -74.89656520, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(19, NULL, 'Paso 19', 4.15528440, -74.89658860, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(20, NULL, 'Paso 20', 4.15535900, -74.89668800, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(21, NULL, 'Paso 21', 4.15541980, -74.89676540, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(22, NULL, 'Paso 22', 4.15547600, -74.89678570, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(23, NULL, 'Paso 23', 4.15552670, -74.89683030, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(24, NULL, 'Paso 24', 4.15557910, -74.89689650, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(25, NULL, 'Paso 25', 4.15565200, -74.89701470, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(26, NULL, 'Paso 26', 4.15575840, -74.89706830, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(27, NULL, 'Paso 27', 4.15582650, -74.89703600, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(28, NULL, 'Paso 28', 4.15584980, -74.89706070, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(29, NULL, 'Paso 29', 4.15584140, -74.89713550, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(30, NULL, 'Paso 30', 4.15592520, -74.89718020, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(31, NULL, 'Paso 31', 4.15600030, -74.89724950, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(32, NULL, 'Paso 32', 4.15610030, -74.89735480, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(33, NULL, 'Paso 33', 4.15619660, -74.89736810, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(34, NULL, 'Paso 34', 4.15623500, -74.89742760, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(35, NULL, 'Paso 35', 4.15622820, -74.89745440, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(36, NULL, 'Paso 36', 4.15624620, -74.89747780, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(37, NULL, 'Paso 37', 4.15635950, -74.89753610, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(38, NULL, 'Paso 38', 4.15638030, -74.89758160, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(39, NULL, 'Paso 39', 4.15639010, -74.89761100, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(40, NULL, 'Paso 40', 4.15643510, -74.89760450, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(41, NULL, 'Paso 41', 4.15645290, -74.89761870, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(42, NULL, 'Paso 42', 4.15644660, -74.89766130, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(43, NULL, 'Paso 43', 4.15650780, -74.89772140, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(44, NULL, 'Entrada Bloque 2', 4.15653360, -74.89773380, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(45, NULL, 'Paso 45', 4.15656930, -74.89774250, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(46, NULL, 'Paso 46', 4.15666460, -74.89777130, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(47, NULL, 'Paso 47', 4.15679740, -74.89775370, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(48, NULL, 'Paso 48', 4.15692010, -74.89771320, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(49, NULL, 'Entrada Cafetería', 4.15692990, -74.89763710, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(50, NULL, 'Paso Cafetería', 4.15690810, -74.89758290, 2, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51'),
(51, NULL, 'Escalera Cafetería', 4.15691110, -74.89764240, 4, 1, '2026-03-06 20:57:51', '2026-03-06 20:57:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nodo_tipos`
--

DROP TABLE IF EXISTS `nodo_tipos`;
CREATE TABLE IF NOT EXISTS `nodo_tipos` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nodo_tipos_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `nodo_tipos`
--

INSERT INTO `nodo_tipos` (`id`, `nombre`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'salon', 'Salón de clases', 1, '2026-03-06 20:57:50', '2026-03-06 20:57:50'),
(2, 'pasillo', 'Pasillo o corredor', 1, '2026-03-06 20:57:50', '2026-03-06 20:57:50'),
(3, 'baño', 'Baño o sanitario', 1, '2026-03-06 20:57:50', '2026-03-06 20:57:50'),
(4, 'escaleras', 'Escaleras', 1, '2026-03-06 20:57:50', '2026-03-06 20:57:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_codes`
--

DROP TABLE IF EXISTS `password_reset_codes`;
CREATE TABLE IF NOT EXISTS `password_reset_codes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `password_reset_codes_email_index` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `password_reset_codes`
--

INSERT INTO `password_reset_codes` (`id`, `email`, `code`, `expires_at`, `created_at`, `updated_at`) VALUES
(6, 'lbarreto42@itfip.edu.co', '014209', '2026-03-14 02:02:24', '2026-03-14 01:47:24', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pending_users`
--

DROP TABLE IF EXISTS `pending_users`;
CREATE TABLE IF NOT EXISTS `pending_users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombres` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verification_token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verification_expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_id` bigint UNSIGNED NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pending_users_email_unique` (`email`),
  UNIQUE KEY `pending_users_email_verification_token_unique` (`email_verification_token`),
  KEY `fk_pending_status_rel` (`status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`),
  KEY `fk_tokens_tokenable_user` (`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(16, 'App\\Models\\User', 8, 'auth_token', '354c9a318f5027251076f0f50f08b7ab0ae77ce7062c776910a977fd081b9df7', '[\"*\"]', '2026-03-07 00:45:16', NULL, '2026-03-07 00:44:40', '2026-03-07 00:45:16'),
(18, 'App\\Models\\User', 9, 'auth_token', '2524ec838fc2ffaff9e65be650b078484f6b3b8e20bab7e1a4605877b34b3e50', '[\"*\"]', '2026-03-07 00:45:42', NULL, '2026-03-07 00:45:41', '2026-03-07 00:45:42'),
(22, 'App\\Models\\User', 10, 'auth_token', '557953b3c8984c3cfd4f7d0354bb7b647d9f569b1e95634b4ca56a10856e9e16', '[\"*\"]', '2026-03-07 01:42:16', NULL, '2026-03-07 01:42:15', '2026-03-07 01:42:16'),
(26, 'App\\Models\\User', 13, 'auth_token', '21df2d6be15b113410e2bb52253a6c145a1de37918f51ee5a2316e036d3b5bde', '[\"*\"]', '2026-03-07 04:08:51', NULL, '2026-03-07 04:00:06', '2026-03-07 04:08:51'),
(30, 'App\\Models\\User', 15, 'auth_token', '30e2e87c2052761ddee8cc484def1ef23b7987138c9eb48643226a4eae7bc133', '[\"*\"]', '2026-03-07 04:32:05', NULL, '2026-03-07 04:28:48', '2026-03-07 04:32:05'),
(31, 'App\\Models\\User', 12, 'auth_token', '21664a9f0b813b377eb560b20545ae4d0eeb254e88c6080b74ca3639bdc1ce8b', '[\"*\"]', '2026-03-07 17:26:00', NULL, '2026-03-07 16:45:40', '2026-03-07 17:26:00'),
(32, 'App\\Models\\User', 16, 'auth_token', '1b6a0abcf76a4b3ad08b6a466631e0f0315004bb2ac85ea788baa33c70318694', '[\"*\"]', '2026-03-07 16:50:24', NULL, '2026-03-07 16:48:10', '2026-03-07 16:50:24'),
(34, 'App\\Models\\User', 18, 'auth_token', 'cb482a93359f08e6e82dd22ea1b56f5ee5068d5f40bbda43f5906d4e27ab49ec', '[\"*\"]', '2026-03-07 16:51:22', NULL, '2026-03-07 16:49:44', '2026-03-07 16:51:22'),
(35, 'App\\Models\\User', 19, 'auth_token', '52525220f68015ea2ad2f1db294f48b206f66d36ad6c0c922fe41278a088be93', '[\"*\"]', '2026-03-07 16:57:38', NULL, '2026-03-07 16:54:58', '2026-03-07 16:57:38'),
(36, 'App\\Models\\User', 20, 'auth_token', '996e80786baccd61110a9fb1f8e278ee02d44165142e1ff78d6974d39d7dfa33', '[\"*\"]', '2026-03-07 17:16:37', NULL, '2026-03-07 17:10:48', '2026-03-07 17:16:37'),
(37, 'App\\Models\\User', 17, 'auth_token', '328125c739c0b746733cd2189be1683db23c135bff70c37a4d9c83eedcae415a', '[\"*\"]', '2026-03-07 17:28:20', NULL, '2026-03-07 17:28:19', '2026-03-07 17:28:20'),
(38, 'App\\Models\\User', 12, 'auth_token', '8a2e1343a96db082c0bbcfd0f545b2d8891904dfd7943512902a97904c629c18', '[\"*\"]', '2026-03-13 03:30:44', NULL, '2026-03-08 03:18:53', '2026-03-13 03:30:44'),
(40, 'App\\Models\\User', 12, 'auth_token', '6ece3db87a3900b11f79ef590639d64e3222a7b08d8119fa0c97589e5574094d', '[\"*\"]', '2026-03-08 06:19:32', NULL, '2026-03-08 06:19:31', '2026-03-08 06:19:32'),
(43, 'App\\Models\\User', 12, 'auth_token', '5bb27e517ae59da8a97082764d20c0c125fcc8f5b905ec280631f1a77d617e37', '[\"*\"]', '2026-03-13 03:30:45', NULL, '2026-03-12 21:57:37', '2026-03-13 03:30:45'),
(46, 'App\\Models\\User', 21, 'auth_token', '2ea3fcb48d87559ce5929017efe0b5a8a1003903b9db70cb90a0a0eae630e820', '[\"*\"]', '2026-03-12 23:46:36', NULL, '2026-03-12 23:43:28', '2026-03-12 23:46:36'),
(48, 'App\\Models\\User', 12, 'auth_token', 'ed567e78b36620014c177a51c7eac692e3116534413b1c4918dbeedf9e0d38ae', '[\"*\"]', '2026-03-13 01:02:46', NULL, '2026-03-13 00:32:51', '2026-03-13 01:02:46'),
(49, 'App\\Models\\User', 8, 'auth_token', '4503c6f563a1b76e826c7dd5949f4db5671d02d933a0a1862711cde10b451012', '[\"*\"]', '2026-03-13 02:58:03', NULL, '2026-03-13 01:21:11', '2026-03-13 02:58:03'),
(50, 'App\\Models\\User', 8, 'auth_token', '8f93d281f69f712a8a691ef5757fa40dcc594991ed2d98cfdae12bba3b44cbd0', '[\"*\"]', '2026-03-13 02:57:39', NULL, '2026-03-13 01:44:23', '2026-03-13 02:57:39'),
(51, 'App\\Models\\User', 8, 'auth_token', '13eb5b0398c0361521b13b8e6fe2c8e0fe64ecd6f1050dc1dc36108f1d9c0790', '[\"*\"]', '2026-03-14 00:37:17', NULL, '2026-03-14 00:37:12', '2026-03-14 00:37:17'),
(54, 'App\\Models\\User', 8, 'auth_token', 'c9a79f45bd5eab8dea74e7c05a6b32ba511f00315ed5da042d17ce0d423241e0', '[\"*\"]', '2026-03-14 02:04:03', NULL, '2026-03-14 02:02:48', '2026-03-14 02:04:03'),
(55, 'App\\Models\\User', 23, 'auth_token', '4b521c7a0262bb54e8e8e22f0fa63506e144bc4a875240b1eb31edd748731ddf', '[\"*\"]', '2026-03-14 02:04:14', NULL, '2026-03-14 02:03:17', '2026-03-14 02:04:14'),
(60, 'App\\Models\\User', 25, 'auth_token', '919164ed36192a70947d15320a7b47eed06ed76cbc6791e5338604a13834cac3', '[\"*\"]', '2026-03-14 19:55:04', NULL, '2026-03-14 19:51:58', '2026-03-14 19:55:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('pT3CNTZ3RWFAS7MkucdxfNZ62FLVjCro8rP0Y3uh', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZk5WdFZhSnoyY2hsSWNEVzNia05sMGl2bmxvU3NDYXYzMmFwV0w4RiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1773537644),
('RVPxVNH5pF906eRJuqkDDAR7BpKmfSNZqofg1ln6', NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiV0NFbFQ5cTRzOWl4bGJaMGRldFhHd1ZBRFZTZlVpYlA0VHU2YmUyYyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjI6Imh0dHA6Ly9zb2x2ZWQtdHJhbnNwYXJlbmN5LWtub3dpbmctbWlncmF0aW9uLnRyeWNsb3VkZmxhcmUuY29tIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1773522500),
('XHcWBKAnHhzZzrmv6hI9IuCuuBY3Wg9W3oPU5k9j', NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMWgxNEhvNWNHSTI4cTJHY3BDT2dlTllsRFZSZm9XeTZSQVlqeHVLRyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjI6Imh0dHA6Ly9zb2x2ZWQtdHJhbnNwYXJlbmN5LWtub3dpbmctbWlncmF0aW9uLnRyeWNsb3VkZmxhcmUuY29tIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1773522495),
('YojanDVWw2wYp1SnkLnGWlFRyJKrFhpyd6wkYmv3', NULL, '127.0.0.1', 'WhatsApp/2.2607.106 W', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoia2dnZ2p1c09xZGZTR3ZSam4yM3Z3aXJaT1NYNUhHMEdwc0NIbnQ3ZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjI6Imh0dHA6Ly9zb2x2ZWQtdHJhbnNwYXJlbmN5LWtub3dpbmctbWlncmF0aW9uLnRyeWNsb3VkZmxhcmUuY29tIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1773522491);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombres` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `status_id` bigint UNSIGNED NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `email_verification_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verification_expires_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_email_verification_token_unique` (`email_verification_token`),
  KEY `fk_users_status_master` (`status_id`),
  KEY `idx_user_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `nombres`, `apellidos`, `email`, `email_verified_at`, `status_id`, `password`, `remember_token`, `created_at`, `updated_at`, `email_verification_token`, `email_verification_expires_at`, `deleted_at`) VALUES
(1, 'Juan', 'Pérez', 'test@test.com', '2026-03-06 20:58:22', 1, '$2y$12$SZgICFe8vUtI1oZGGj575ez./EXy3kaNNoJpqSXPNGRAodL4F7U9a', NULL, '2026-03-06 20:58:22', '2026-03-06 20:58:22', NULL, NULL, NULL),
(2, 'wilfer', 'mendoza', 'deleted_1772847511_wilfermendozasanchez1811@gmail.com', '2026-03-06 21:26:36', 4, '$2y$12$eijgN4aWyev2wqiDMkaD1.mPIqH35nF289hmxOGyys9jwLqMEOKS2', NULL, '2026-03-06 21:26:36', '2026-03-07 01:38:31', NULL, NULL, '2026-03-07 01:38:31'),
(3, 'julian', 'cofles', 'deleted_1772832921_wmendoza65@itfip.edu.co', '2026-03-06 21:31:20', 4, '$2y$12$AB9w0zFPiT/tnomyZOV8oeqOqaYIJHQoMSn1mBjwbSFHz2nUKDn.q', NULL, '2026-03-06 21:31:20', '2026-03-06 21:35:21', NULL, NULL, '2026-03-06 21:35:21'),
(4, 'ana', 'sanchez', 'deleted_1772833142_wmendoza65@itfip.edu.co', '2026-03-06 21:36:18', 4, '$2y$12$uNveKZl.6mrLTRg1QNskTuIb1LylyX5JpjDolSBh10Kjn86rTip46', NULL, '2026-03-06 21:36:18', '2026-03-06 21:39:02', NULL, NULL, '2026-03-06 21:39:02'),
(5, 'ruben', 'mendoza', 'deleted_1772833308_wmendoza65@itfip.edu.co', '2026-03-06 21:39:42', 4, '$2y$12$4ulf6PGOMwR5D6Irizd6rOL81qS0CR9KMw1TJETdSXLV6sZhcNk.O', NULL, '2026-03-06 21:39:42', '2026-03-06 21:41:48', NULL, NULL, '2026-03-06 21:41:48'),
(6, 'jose', 'perez', 'deleted_1772838832_wmendoza65@itfip.edu.co', '2026-03-06 21:42:21', 4, '$2y$12$eT4Ndn/heQI7E7m5p76u5eRON659pa/OjMNSQqm/2mcn7aD9WJ3Qy', NULL, '2026-03-06 21:42:21', '2026-03-06 23:13:52', NULL, NULL, '2026-03-06 23:13:52'),
(7, 'julian', 'cofles', 'deleted_1772847791_wmendoza65@itfip.edu.co', '2026-03-06 23:15:42', 4, '$2y$12$FhUSWXKTZIuDwhf//IrPze.1awlRVJO8..W35PnwymJRU7j2PNB9G', NULL, '2026-03-06 23:15:42', '2026-03-07 01:43:11', NULL, NULL, '2026-03-07 01:43:11'),
(8, 'Julian', 'Cofles Olivar', 'juliancofles.2@gmail.com', '2026-03-07 00:44:24', 1, '$2y$12$DNZ7MzMBRb7rjf4mwOmoNetgiQ0Fvur2h//lY6FxjYPTPVFDciAaa', NULL, '2026-03-07 00:44:24', '2026-03-14 02:02:48', NULL, NULL, NULL),
(9, 'Carlos', 'Valencia', 'Jvalencia34@itfip.edu.co', '2026-03-07 00:44:59', 2, '$2y$12$U8mp0QMt7v.5vRgBu9puJO3ewVw2E2lkmeL60hbanYS5FJ7ezv4YC', NULL, '2026-03-07 00:44:59', '2026-03-07 01:37:29', NULL, NULL, NULL),
(10, 'Lao', 'Nel', 'jcofles36@itfip.edu.co', '2026-03-07 01:41:59', 1, '$2y$12$PFYVEgXvf7WZ7e26wM0gzu1mq/7rVKAdwiTa4p7KwWqib.nmXpjry', NULL, '2026-03-07 01:41:59', '2026-03-07 01:41:59', NULL, NULL, NULL),
(11, 'Luis carlos', 'Barreto', 'lbarreto42@itfip.edu.co', '2026-03-07 01:48:01', 2, '$2y$12$EVp51jxOsz.GtN0RgCKil.b5nsnKRhJ/eX91J4BdUXkolFVA6AP/W', NULL, '2026-03-07 01:48:01', '2026-03-07 01:57:35', NULL, NULL, NULL),
(12, 'Wilfer', 'Mendoza', 'wmendoza65@itfip.edu.co', '2026-03-07 03:52:42', 1, '$2y$12$eFDjSQOHLOgu0e6Ogd3y3uVLwtk.Um3e7.DZEuKhu4SUE04iRzm2.', NULL, '2026-03-07 03:52:42', '2026-03-13 00:32:51', NULL, NULL, NULL),
(13, 'Óscar Javier', 'Mendoza sanchez', 'javiermendozaa157@gmail.com', '2026-03-07 03:53:14', 1, '$2y$12$7ElNVp4LEiGZVKMt.u2Vl.8FJKKk7rM4IV6tZUsTY21Ow1zIsABde', NULL, '2026-03-07 03:53:14', '2026-03-07 04:00:06', NULL, NULL, NULL),
(14, 'Ruben', 'Mendoza sanchez', 'rubenmendoza427@gmail.com', '2026-03-07 04:02:44', 2, '$2y$12$eQ5aR89z/t7XyuBjCUi8qu8omJey6D5cW9wiFIdq15tb5o/AaJn.a', NULL, '2026-03-07 04:02:44', '2026-03-07 04:11:53', NULL, NULL, NULL),
(15, 'ana', 'rodriguez', 'anaporras034@gmail.com', '2026-03-07 04:28:17', 1, '$2y$12$7nlDYAVzF4es5iDavziI4eR1K6xqrT2ABXntBXLbBHY76DY0b3eCS', NULL, '2026-03-07 04:28:17', '2026-03-07 04:28:17', NULL, NULL, NULL),
(16, 'Camilo', 'Gomez', 'jgomez53@itfip.edu.co', '2026-03-07 16:47:50', 1, '$2y$12$.1r1ipeiemW/SvQHXO.GsOyblACd49335CVMhuSGcYPtueYxt9OI2', NULL, '2026-03-07 16:47:50', '2026-03-07 16:47:50', NULL, NULL, NULL),
(17, 'Johan', 'Díaz', 'diazgarciajohanstiven@gmail.com', '2026-03-07 16:49:11', 1, '$2y$12$zFBKLSYycOnUZG09liS71.Lvby4PNdR3kqzhDMqJdxq2R/IIUc/Gi', NULL, '2026-03-07 16:49:11', '2026-03-07 17:28:19', NULL, NULL, NULL),
(18, 'Jesus Antonio', 'Prada Perdomo', 'jesantpraper819@gmail.com', '2026-03-07 16:49:16', 1, '$2y$12$IEdjCF4.KF4/XgCOwc/E8.eW6b0BEIkEFBDTFrxgSb2r30qsn/uFe', NULL, '2026-03-07 16:49:16', '2026-03-07 16:49:16', NULL, NULL, NULL),
(19, 'Anlly Marcela', 'Mendoza Sánchez', 'marcela921103@gmail.com', '2026-03-07 16:54:33', 1, '$2y$12$LYKA8g8WnjLdN8BJM0dQXeL86WCAZdZmGWY3Ip4aD/2KoOBipoR5e', NULL, '2026-03-07 16:54:33', '2026-03-07 16:54:33', NULL, NULL, NULL),
(20, 'Kevin', 'Preciado', 'kevin.preciado0805@gmail.com', '2026-03-07 17:10:33', 1, '$2y$12$MBIwLygkd6SQrS9QmXie8.iyOlG4N19IulTfMy77Pdv87IONMAece', NULL, '2026-03-07 17:10:33', '2026-03-07 17:10:33', NULL, NULL, NULL),
(21, 'Yorleni', 'Mendoza', 'yorlis2001.ms@gmail.com', '2026-03-12 23:43:19', 1, '$2y$12$o4LO3.o6DTAIvtICE8LyduluoX44jA/tSElJdN3FSIwUS3zrPYOaS', NULL, '2026-03-12 23:43:19', '2026-03-12 23:43:19', NULL, NULL, NULL),
(22, 'Wilfer', 'Mendoza', 'deleted_1773517323_wilfermendozasanchez1811@gmail.com', '2026-03-14 01:57:34', 4, '$2y$12$fPUxy4nOJNI/M6TKewnNJu/2SVzbVDaqe7eZbw3MALIavrAvmr9Hu', NULL, '2026-03-14 01:57:34', '2026-03-14 19:42:03', NULL, NULL, '2026-03-14 19:42:03'),
(23, 'Iván Camilo', 'Ruiz Bocanegra', 'iruiz07@itfip.edu.co', '2026-03-14 02:02:56', 1, '$2y$12$mAPE7kampbN0HEZ5LcK8QelIufKkJHGzQY693/0h.fJemeN9TVCRu', NULL, '2026-03-14 02:02:56', '2026-03-14 02:02:56', NULL, NULL, NULL),
(25, 'Fabian', 'Ruiz', 'ruizf1166@gmail.com', '2026-03-14 19:50:59', 1, '$2y$12$KfscMKktVw3ZWY9B/4PXEO/yxDl5Bh/b3ERgbEbYlGSOAoVAsxoDG', NULL, '2026-03-14 19:50:59', '2026-03-14 19:50:59', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_status`
--

DROP TABLE IF EXISTS `user_status`;
CREATE TABLE IF NOT EXISTS `user_status` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `user_status`
--

INSERT INTO `user_status` (`id`, `nombre`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Activo', NULL, NULL, NULL),
(2, 'Pendiente', NULL, NULL, NULL),
(3, 'Inactivo', NULL, NULL, NULL),
(4, 'Eliminado', NULL, NULL, NULL);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `conexiones`
--
ALTER TABLE `conexiones`
  ADD CONSTRAINT `fk_conn_destino` FOREIGN KEY (`nodo_destino_id`) REFERENCES `nodos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conn_destino_def` FOREIGN KEY (`nodo_destino_id`) REFERENCES `nodos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conn_dst` FOREIGN KEY (`nodo_destino_id`) REFERENCES `nodos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conn_origen` FOREIGN KEY (`nodo_origen_id`) REFERENCES `nodos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conn_origen_def` FOREIGN KEY (`nodo_origen_id`) REFERENCES `nodos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conn_src` FOREIGN KEY (`nodo_origen_id`) REFERENCES `nodos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `deleted_users_log`
--
ALTER TABLE `deleted_users_log`
  ADD CONSTRAINT `fk_deleted_log_u_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_log_user_relation` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `email_change_codes`
--
ALTER TABLE `email_change_codes`
  ADD CONSTRAINT `fk_email_change_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_email_codes_u_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_email_codes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `nodos`
--
ALTER TABLE `nodos`
  ADD CONSTRAINT `fk_nodos_author` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_nodos_creator` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_nodos_tipo` FOREIGN KEY (`tipo_id`) REFERENCES `nodo_tipos` (`id`),
  ADD CONSTRAINT `fk_nodos_tipo_def` FOREIGN KEY (`tipo_id`) REFERENCES `nodo_tipos` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_nodos_tipo_id_new` FOREIGN KEY (`tipo_id`) REFERENCES `nodo_tipos` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_nodos_usuario_creador` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `password_reset_codes`
--
ALTER TABLE `password_reset_codes`
  ADD CONSTRAINT `fk_pw_reset_email_final` FOREIGN KEY (`email`) REFERENCES `users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `fk_tokens_email_main` FOREIGN KEY (`email`) REFERENCES `users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pending_users`
--
ALTER TABLE `pending_users`
  ADD CONSTRAINT `fk_pending_status_rel` FOREIGN KEY (`status_id`) REFERENCES `user_status` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD CONSTRAINT `fk_tokens_tokenable_user` FOREIGN KEY (`tokenable_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tokens_user` FOREIGN KEY (`tokenable_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tokens_user_id` FOREIGN KEY (`tokenable_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tokens_user_relation` FOREIGN KEY (`tokenable_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sessions_user_relation` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_status_final` FOREIGN KEY (`status_id`) REFERENCES `user_status` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_status_master` FOREIGN KEY (`status_id`) REFERENCES `user_status` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
