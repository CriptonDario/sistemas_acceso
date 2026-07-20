-- ============================================================
-- NUEVAS TABLAS: Alumnos, Grados, Materias y Notas
-- Ejecutar en la base de datos: pestalozzi_db
-- ============================================================

USE `pestalozzi_db`;

-- ============================================================
-- TABLA: grados
-- Grados y secciones del colegio
-- ============================================================
CREATE TABLE IF NOT EXISTS `grados` (
  `id`      int NOT NULL AUTO_INCREMENT,
  `nombre`  varchar(80)  COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Ej: 1° Primaria, 5° Secundaria',
  `seccion` varchar(10)  COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Ej: A, B, C',
  `nivel`   enum('primaria','secundaria') COLLATE utf8mb4_general_ci DEFAULT 'primaria',
  `estado`  enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `grados` (`nombre`, `seccion`, `nivel`, `estado`) VALUES
  ('1° Primaria',   'A', 'primaria',   'activo'),
  ('1° Primaria',   'B', 'primaria',   'activo'),
  ('2° Primaria',   'A', 'primaria',   'activo'),
  ('3° Primaria',   'A', 'primaria',   'activo'),
  ('4° Primaria',   'A', 'primaria',   'activo'),
  ('5° Primaria',   'A', 'primaria',   'activo'),
  ('6° Primaria',   'A', 'primaria',   'activo'),
  ('1° Secundaria', 'A', 'secundaria', 'activo'),
  ('2° Secundaria', 'A', 'secundaria', 'activo'),
  ('3° Secundaria', 'A', 'secundaria', 'activo'),
  ('4° Secundaria', 'A', 'secundaria', 'activo'),
  ('5° Secundaria', 'A', 'secundaria', 'activo');

-- ============================================================
-- TABLA: alumnos
-- Alumnos matriculados en el colegio
-- ============================================================
CREATE TABLE IF NOT EXISTS `alumnos` (
  `id`              int NOT NULL AUTO_INCREMENT,
  `codigo`          varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Código único del alumno',
  `nombres`         varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `apellidos`       varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `correo`          varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contrasena`      varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `grado_id`        int DEFAULT NULL,
  `foto`            varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'default.png',
  `estado`          enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  `fecha_registro`  datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  UNIQUE KEY `correo` (`correo`),
  KEY `grado_id` (`grado_id`),
  CONSTRAINT `alumnos_ibfk_1` FOREIGN KEY (`grado_id`) REFERENCES `grados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Alumno de ejemplo (contraseña: 123456)
INSERT INTO `alumnos` (`codigo`, `nombres`, `apellidos`, `correo`, `contrasena`, `grado_id`) VALUES
  ('ALU001', 'JUAN DIEGO', 'PEREZ RIOS',    'juan.perez@alumnos.pestalozzi.edu.pe',  '$2y$10$hjoa6/izjbON6303KeT9GuxstoJqZlEdq.E7N.Y7KBX7qWQzxbq0y', 1),
  ('ALU002', 'SOFIA',      'RAMIREZ LUNA',  'sofia.ramirez@alumnos.pestalozzi.edu.pe','$2y$10$hjoa6/izjbON6303KeT9GuxstoJqZlEdq.E7N.Y7KBX7qWQzxbq0y', 1),
  ('ALU003', 'CARLOS',     'GUTIERREZ PAZ', 'carlos.gutierrez@alumnos.pestalozzi.edu.pe','$2y$10$hjoa6/izjbON6303KeT9GuxstoJqZlEdq.E7N.Y7KBX7qWQzxbq0y', 8);

-- ============================================================
-- TABLA: materias
-- Materias/cursos por grado
-- ============================================================
CREATE TABLE IF NOT EXISTS `materias` (
  `id`         int NOT NULL AUTO_INCREMENT,
  `nombre`     varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `grado_id`   int DEFAULT NULL,
  `docente_id` int DEFAULT NULL COMMENT 'FK a personal (docente)',
  `estado`     enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  PRIMARY KEY (`id`),
  KEY `grado_id`   (`grado_id`),
  KEY `docente_id` (`docente_id`),
  CONSTRAINT `materias_ibfk_1` FOREIGN KEY (`grado_id`)   REFERENCES `grados`  (`id`) ON DELETE SET NULL,
  CONSTRAINT `materias_ibfk_2` FOREIGN KEY (`docente_id`) REFERENCES `personal` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Materias de ejemplo para 1° Primaria A (grado_id = 1)
INSERT INTO `materias` (`nombre`, `grado_id`, `docente_id`) VALUES
  ('Matemáticas',         1, 2),
  ('Comunicación',        1, 2),
  ('Ciencias Naturales',  1, NULL),
  ('Personal Social',     1, NULL),
  ('Educación Religiosa', 1, NULL),
  ('Arte y Cultura',      1, NULL);

-- Materias para 1° Secundaria A (grado_id = 8)
INSERT INTO `materias` (`nombre`, `grado_id`, `docente_id`) VALUES
  ('Matemáticas',   8, 2),
  ('Comunicación',  8, NULL),
  ('Historia',      8, NULL),
  ('Ciencias',      8, NULL),
  ('Inglés',        8, NULL);

-- ============================================================
-- TABLA: notas
-- Notas por alumno, materia y bimestre
-- ============================================================
CREATE TABLE IF NOT EXISTS `notas` (
  `id`              int NOT NULL AUTO_INCREMENT,
  `alumno_id`       int NOT NULL,
  `materia_id`      int NOT NULL,
  `bimestre`        tinyint NOT NULL COMMENT '1, 2, 3 o 4',
  `nota`            decimal(5,2) NOT NULL COMMENT 'Escala 0-20',
  `observacion`     varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fecha_registro`  datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alumno_materia_bimestre` (`alumno_id`, `materia_id`, `bimestre`),
  KEY `alumno_id`  (`alumno_id`),
  KEY `materia_id` (`materia_id`),
  CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`alumno_id`)  REFERENCES `alumnos`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `notas_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Notas de ejemplo para ALU001
INSERT INTO `notas` (`alumno_id`, `materia_id`, `bimestre`, `nota`) VALUES
  (1, 1, 1, 16.0),
  (1, 1, 2, 14.5),
  (1, 2, 1, 18.0),
  (1, 2, 2, 17.0),
  (1, 3, 1,  9.5),
  (1, 4, 1, 13.0);
