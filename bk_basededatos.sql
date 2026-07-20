-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- Base de datos:                Colegio Pestalozzi
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para pestalozzi_db
CREATE DATABASE IF NOT EXISTS `pestalozzi_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `pestalozzi_db`;

-- ============================================================
-- TABLA: areas
-- Áreas académicas y administrativas del colegio Pestalozzi
-- ============================================================
CREATE TABLE IF NOT EXISTS `areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tipo` enum('academica','administrativa','apoyo') COLLATE utf8mb4_general_ci DEFAULT 'academica',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla pestalozzi_db.areas
DELETE FROM `areas`;
INSERT INTO `areas` (`id`, `nombre`, `tipo`, `estado`) VALUES
  (1, 'DIRECCIÓN GENERAL',       'administrativa', 'activo'),
  (2, 'SECRETARÍA ACADÉMICA',    'administrativa', 'activo'),
  (3, 'RECURSOS HUMANOS',        'administrativa', 'activo'),
  (4, 'DOCENTES PRIMARIA',       'academica',      'activo'),
  (5, 'DOCENTES SECUNDARIA',     'academica',      'activo'),
  (6, 'PSICOLOGÍA Y BIENESTAR',  'apoyo',          'activo'),
  (7, 'BIBLIOTECA Y RECURSOS',   'apoyo',          'activo'),
  (8, 'MANTENIMIENTO',           'administrativa', 'activo');

-- ============================================================
-- TABLA: personal
-- Personal del colegio: docentes, administrativos y de apoyo
-- ============================================================
CREATE TABLE IF NOT EXISTS `personal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Código único del personal (usado en QR)',
  `nombres` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `correo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `area_id` int DEFAULT NULL,
  `cargo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo_personal` enum('docente','administrativo','apoyo') COLLATE utf8mb4_general_ci DEFAULT 'administrativo',
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'default.png',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `area_id` (`area_id`),
  CONSTRAINT `personal_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla pestalozzi_db.personal
DELETE FROM `personal`;
INSERT INTO `personal` (`id`, `codigo`, `nombres`, `apellidos`, `correo`, `contrasena`, `area_id`, `cargo`, `tipo_personal`, `foto`, `estado`, `fecha_registro`) VALUES
  (1, 'PES001', 'VICTOR',  'RAMOS',   'victor.ramos@pestalozzi.edu.pe',  '$2y$10$hjoa6/izjbON6303KeT9GuxstoJqZlEdq.E7N.Y7KBX7qWQzxbq0y', 1, 'DIRECTOR GENERAL',         'administrativo', 'default.png', 'activo', '2025-03-01 08:00:00'),
  (2, 'PES002', 'CARLOS',  'RAMIREZ', 'carlos.ramirez@pestalozzi.edu.pe','$2y$10$cOhxXeHlMBYzpdFVTcx/2.rwrHetLpnXBRDIodanipx6G.FBNEESi', 5, 'DOCENTE DE MATEMÁTICAS',   'docente',        'default.png', 'activo', '2025-03-01 08:05:00'),
  (3, 'PES003', 'MARIA',   'ARIAS',   'maria.arias@pestalozzi.edu.pe',   '$2y$10$NebEGaM19vO7si.8ctlfvuJQPaHPgh0j6znFxLs8PQJVeM2vU4khm', 3, 'JEFA DE RECURSOS HUMANOS', 'administrativo', 'default.png', 'activo', '2025-03-01 08:10:00'),
  (4, 'PES004', 'LUCIA',   'TORRES',  'lucia.torres@pestalozzi.edu.pe',  '$2y$10$NebEGaM19vO7si.8ctlfvuJQPaHPgh0j6znFxLs8PQJVeM2vU4khm', 6, 'PSICÓLOGA ESCOLAR',        'apoyo',          'default.png', 'activo', '2025-03-01 08:15:00');

-- ============================================================
-- TABLA: registros_asistencia
-- Registro de entrada y salida del personal
-- ============================================================
CREATE TABLE IF NOT EXISTS `registros_asistencia` (
  `id` int NOT NULL AUTO_INCREMENT,
  `personal_id` int NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `total_horas` decimal(5,2) DEFAULT NULL,
  `estado` enum('puntual','tarde') COLLATE utf8mb4_general_ci DEFAULT 'puntual',
  PRIMARY KEY (`id`),
  KEY `personal_id` (`personal_id`),
  CONSTRAINT `registros_asistencia_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla pestalozzi_db.registros_asistencia
DELETE FROM `registros_asistencia`;
INSERT INTO `registros_asistencia` (`id`, `personal_id`, `fecha`, `hora_entrada`, `hora_salida`, `total_horas`, `estado`) VALUES
  (1, 2, '2025-11-21', '07:28:00', '15:30:00', NULL, 'puntual'),
  (2, 3, '2025-11-21', '07:45:00', '16:00:00', NULL, 'puntual'),
  (3, 1, '2025-11-23', '07:55:00', NULL,        NULL, 'puntual');

-- ============================================================
-- TABLA: incidencias
-- Bitácora de seguridad e incidencias del colegio
-- ============================================================
CREATE TABLE IF NOT EXISTS `incidencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  `severidad` enum('baja','media','alta') COLLATE utf8mb4_general_ci DEFAULT 'baja',
  `registrado_por` int DEFAULT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `adjunto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `registrado_por` (`registrado_por`),
  CONSTRAINT `incidencias_ibfk_1` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla pestalozzi_db.incidencias
DELETE FROM `incidencias`;
INSERT INTO `incidencias` (`id`, `titulo`, `descripcion`, `severidad`, `registrado_por`, `fecha_registro`, `adjunto`) VALUES
  (1, 'VISITA DE PROVEEDOR DE LIBROS', 'EL PROVEEDOR INGRESÓ CON MATERIALES EDUCATIVOS', 'baja', 1, '2025-11-21 10:46:02', 'evidencia_1763739962.jpg');

-- ============================================================
-- TABLA: configuracion
-- Configuración general del sistema (horarios, etc.)
-- ============================================================
CREATE TABLE IF NOT EXISTS `configuracion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `clave` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `valor` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla pestalozzi_db.configuracion
DELETE FROM `configuracion`;
INSERT INTO `configuracion` (`id`, `clave`, `valor`) VALUES
  (1, 'hora_entrada',   '07:30'),
  (2, 'nombre_colegio', 'Colegio Pestalozzi'),
  (3, 'wa_activo',      '0'),
  (4, 'wa_proveedor',   'callmebot'),
  (5, 'wa_token',       ''),
  (6, 'wa_instance',    '');

-- ============================================================
-- TABLA: usuarios
-- Usuarios del sistema (admin y personal de seguridad)
-- ============================================================
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `rol` enum('admin','guardia') COLLATE utf8mb4_general_ci DEFAULT 'guardia',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla pestalozzi_db.usuarios
DELETE FROM `usuarios`;
INSERT INTO `usuarios` (`id`, `usuario`, `contrasena`, `rol`, `fecha_registro`, `estado`) VALUES
-- Contraseña admin    : admin123
-- Contraseña seguridad: seguridad123
  (1, 'admin',     '$2y$10$QWp8.dtXmIlEOaIyp6.jxOR4JHYL/7raBirHbKnRBwHPfGW1eN5aq', 'admin',   '2025-11-18 01:01:37', 'activo'),
  (3, 'seguridad', '$2y$10$VUL2zgmpI3V/vO89MIlK/e2KEeeUG1OPbYFcQTVamduDkdKu3esjm', 'guardia', '2025-11-21 10:28:36', 'activo');

-- ============================================================
-- TABLA: visitantes
-- Registro de personas externas que visitan el colegio
-- ============================================================
CREATE TABLE IF NOT EXISTS `visitantes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dni` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nombre_completo` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `institucion` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Empresa, institución o parentesco',
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bloqueado` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla pestalozzi_db.visitantes
DELETE FROM `visitantes`;
INSERT INTO `visitantes` (`id`, `dni`, `nombre_completo`, `institucion`, `telefono`, `bloqueado`) VALUES
  (1, '44444444', 'MARIO ARIAS', 'EDITORIAL SANTILLANA',   NULL, 0),
  (2, '33333333', 'JUAN ARIAS',  'PADRE DE FAMILIA',       NULL, 0);

-- ============================================================
-- TABLA: registros_visitas
-- Log de entradas y salidas de visitantes
-- ============================================================
CREATE TABLE IF NOT EXISTS `registros_visitas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `visitante_id` int NOT NULL,
  `personal_a_visitar_id` int DEFAULT NULL COMMENT 'Miembro del personal que recibe la visita',
  `motivo` text COLLATE utf8mb4_general_ci,
  `entrada` datetime DEFAULT CURRENT_TIMESTAMP,
  `salida` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visitante_id` (`visitante_id`),
  KEY `personal_a_visitar_id` (`personal_a_visitar_id`),
  CONSTRAINT `registros_visitas_ibfk_1` FOREIGN KEY (`visitante_id`) REFERENCES `visitantes` (`id`),
  CONSTRAINT `registros_visitas_ibfk_2` FOREIGN KEY (`personal_a_visitar_id`) REFERENCES `personal` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla pestalozzi_db.registros_visitas
DELETE FROM `registros_visitas`;
INSERT INTO `registros_visitas` (`id`, `visitante_id`, `personal_a_visitar_id`, `motivo`, `entrada`, `salida`) VALUES
  (1, 1, NULL, 'ENTREGA DE MATERIAL BIBLIOGRÁFICO',    '2025-11-21 10:43:22', '2025-11-21 10:44:23'),
  (2, 2, NULL, 'REUNIÓN CON DOCENTE TUTOR DE AULA',    '2025-11-23 16:06:17', NULL);

-- ============================================================
-- TABLA: grados
-- Grados escolares del colegio Pestalozzi
-- ============================================================
CREATE TABLE IF NOT EXISTS `grados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Ej: 1ro Primaria',
  `nivel` enum('primaria','secundaria') COLLATE utf8mb4_general_ci NOT NULL,
  `seccion` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'A',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `grados`;
INSERT INTO `grados` (`id`, `nombre`, `nivel`, `seccion`, `estado`) VALUES
  (1,  '1ro Primaria',   'primaria',   'A', 'activo'),
  (2,  '2do Primaria',   'primaria',   'A', 'activo'),
  (3,  '3ro Primaria',   'primaria',   'A', 'activo'),
  (4,  '4to Primaria',   'primaria',   'A', 'activo'),
  (5,  '5to Primaria',   'primaria',   'A', 'activo'),
  (6,  '6to Primaria',   'primaria',   'A', 'activo'),
  (7,  '1ro Secundaria', 'secundaria', 'A', 'activo'),
  (8,  '2do Secundaria', 'secundaria', 'A', 'activo'),
  (9,  '3ro Secundaria', 'secundaria', 'A', 'activo'),
  (10, '4to Secundaria', 'secundaria', 'A', 'activo'),
  (11, '5to Secundaria', 'secundaria', 'A', 'activo');

-- ============================================================
-- TABLA: materias
-- Materias/asignaturas del colegio
-- ============================================================
CREATE TABLE IF NOT EXISTS `materias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nivel` enum('primaria','secundaria','ambos') COLLATE utf8mb4_general_ci DEFAULT 'ambos',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `materias`;
INSERT INTO `materias` (`id`, `nombre`, `nivel`, `estado`) VALUES
  (1,  'Matemática',              'ambos',      'activo'),
  (2,  'Comunicación',            'ambos',      'activo'),
  (3,  'Ciencia y Tecnología',    'ambos',      'activo'),
  (4,  'Personal Social',         'primaria',   'activo'),
  (5,  'Arte y Cultura',          'ambos',      'activo'),
  (6,  'Educación Física',        'ambos',      'activo'),
  (7,  'Inglés',                  'ambos',      'activo'),
  (8,  'Historia y Geografía',    'secundaria', 'activo'),
  (9,  'Formación Ciudadana',     'secundaria', 'activo'),
  (10, 'Educación Religiosa',     'ambos',      'activo'),
  (11, 'Tutoría',                 'ambos',      'activo');

-- ============================================================
-- TABLA: alumnos
-- Registro de alumnos del colegio Pestalozzi
-- ============================================================
CREATE TABLE IF NOT EXISTS `alumnos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Código único para QR (Ej: ALU001)',
  `nombres` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `dni` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `grado_id` int NOT NULL,
  `correo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'default.png',
  `nombre_apoderado` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono_apoderado` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wa_apikey_apoderado` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'API key CallMeBot del apoderado',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `grado_id` (`grado_id`),
  CONSTRAINT `alumnos_ibfk_1` FOREIGN KEY (`grado_id`) REFERENCES `grados` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `alumnos`;
INSERT INTO `alumnos` (`id`, `codigo`, `nombres`, `apellidos`, `dni`, `grado_id`, `correo`, `contrasena`, `foto`, `nombre_apoderado`, `estado`) VALUES
  (1, 'ALU001', 'ANDREA',  'LOPEZ',   '75000001', 7,  'andrea.lopez@pestalozzi.edu.pe',  '$2y$10$d9LwByYTx4orApcv.NA/keh1cNBq7yif3QfBZFSsdhJxcvinHc4vu', 'default.png', 'CARLOS LOPEZ',   'activo'),
  (2, 'ALU002', 'MIGUEL',  'TORRES',  '75000002', 1,  'miguel.torres@pestalozzi.edu.pe', '$2y$10$d9LwByYTx4orApcv.NA/keh1cNBq7yif3QfBZFSsdhJxcvinHc4vu', 'default.png', 'ANA TORRES',     'activo'),
  (3, 'ALU003', 'VALERIA', 'QUISPE',  '75000003', 10, 'valeria.quispe@pestalozzi.edu.pe','$2y$10$d9LwByYTx4orApcv.NA/keh1cNBq7yif3QfBZFSsdhJxcvinHc4vu', 'default.png', 'JORGE QUISPE',   'activo');

-- ============================================================
-- TABLA: asistencia_alumnos
-- Registro de asistencia de alumnos por QR
-- ============================================================
CREATE TABLE IF NOT EXISTS `asistencia_alumnos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alumno_id` int NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `estado` enum('puntual','tarde','falta') COLLATE utf8mb4_general_ci DEFAULT 'puntual',
  PRIMARY KEY (`id`),
  KEY `alumno_id` (`alumno_id`),
  CONSTRAINT `asistencia_alumnos_ibfk_1` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `asistencia_alumnos`;

-- ============================================================
-- TABLA: notas
-- Notas de alumnos por materia y bimestre
-- ============================================================
CREATE TABLE IF NOT EXISTS `notas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alumno_id` int NOT NULL,
  `materia_id` int NOT NULL,
  `periodo` enum('T1','T2','T3') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Trimestre',
  `anio` year NOT NULL DEFAULT (YEAR(CURDATE())),
  `nota` decimal(4,1) DEFAULT NULL COMMENT 'Nota de 0 a 20',
  `observacion` text COLLATE utf8mb4_general_ci DEFAULT NULL,
  `registrado_por` int DEFAULT NULL COMMENT 'ID del personal que registró',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nota` (`alumno_id`,`materia_id`,`periodo`,`anio`),
  KEY `alumno_id` (`alumno_id`),
  KEY `materia_id` (`materia_id`),
  CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`alumno_id`)  REFERENCES `alumnos` (`id`),
  CONSTRAINT `notas_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `notas`;
INSERT INTO `notas` (`alumno_id`, `materia_id`, `periodo`, `anio`, `nota`, `registrado_por`) VALUES
  (1, 1, 'T1', 2025, 16.5, 1),
  (1, 2, 'T1', 2025, 18.0, 1),
  (1, 3, 'T1', 2025, 15.0, 1),
  (1, 7, 'T1', 2025, 17.5, 1),
  (1, 1, 'T2', 2025, 17.0, 1),
  (1, 2, 'T2', 2025, 19.0, 1),
  (2, 1, 'T1', 2025, 14.0, 1),
  (2, 2, 'T1', 2025, 13.5, 1),
  (3, 1, 'T1', 2025, 18.0, 1),
  (3, 2, 'T1', 2025, 17.0, 1);

-- ============================================================
-- TABLA: docente_materia_grado
-- Asignación de docentes a materia+grado (configurable por admin)
-- tipo_asignacion:
--   'aula'  = Primaria/Inicial: UN docente por sección, ingresa TODAS las materias
--   'curso' = Secundaria: UN docente por materia específica
-- ============================================================
CREATE TABLE IF NOT EXISTS `docente_materia_grado` (
  `id`              int NOT NULL AUTO_INCREMENT,
  `personal_id`     int NOT NULL COMMENT 'Docente de tabla personal',
  `materia_id`      int DEFAULT NULL COMMENT 'NULL si tipo_asignacion=aula (accede a todas)',
  `grado_id`        int NOT NULL,
  `anio`            year NOT NULL DEFAULT (YEAR(CURDATE())),
  `activo`          tinyint(1) DEFAULT 1,
  `tipo_asignacion` enum('aula','curso') NOT NULL DEFAULT 'curso'
                    COMMENT 'aula=un docente para todo el grado, curso=un docente por materia',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_aula`  (`personal_id`, `grado_id`, `anio`, `tipo_asignacion`),
  KEY `personal_id` (`personal_id`),
  KEY `materia_id`  (`materia_id`),
  KEY `grado_id`    (`grado_id`),
  CONSTRAINT `dmg_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`),
  CONSTRAINT `dmg_ibfk_2` FOREIGN KEY (`materia_id`)  REFERENCES `materias` (`id`),
  CONSTRAINT `dmg_ibfk_3` FOREIGN KEY (`grado_id`)    REFERENCES `grados`   (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `docente_materia_grado`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
pestalozzi_db