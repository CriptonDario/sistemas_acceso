<?php
/**
 * Crea la tabla docente_materia_grado si no existe.
 * URL: http://localhost/sistema_acceso - copia/public/crear_tabla_docentes.php?token=pestalozzi2024
 * ELIMINA este archivo después de usarlo.
 */
if (!isset($_GET['token']) || $_GET['token'] !== 'pestalozzi2024') {
    http_response_code(403); die("<h2 style='color:red'>403 — Acceso denegado</h2>");
}

require_once '../app/config/db.php';

try {
    $db = (new Database())->getConnection();

    $db->exec("
        CREATE TABLE IF NOT EXISTS `docente_materia_grado` (
          `id`          int NOT NULL AUTO_INCREMENT,
          `personal_id` int NOT NULL COMMENT 'Docente de tabla personal',
          `materia_id`  int NOT NULL,
          `grado_id`    int NOT NULL,
          `anio`        year NOT NULL DEFAULT (YEAR(CURDATE())),
          `activo`      tinyint(1) DEFAULT 1,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uq_asignacion` (`personal_id`,`materia_id`,`grado_id`,`anio`),
          KEY `personal_id` (`personal_id`),
          KEY `materia_id`  (`materia_id`),
          KEY `grado_id`    (`grado_id`),
          CONSTRAINT `dmg_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`),
          CONSTRAINT `dmg_ibfk_2` FOREIGN KEY (`materia_id`)  REFERENCES `materias` (`id`),
          CONSTRAINT `dmg_ibfk_3` FOREIGN KEY (`grado_id`)    REFERENCES `grados`   (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    // También agregar columna wa_apikey_apoderado si no existe en alumnos
    try {
        $db->exec("ALTER TABLE `alumnos` ADD COLUMN `wa_apikey_apoderado` varchar(50) DEFAULT NULL COMMENT 'API key CallMeBot del apoderado'");
        echo "<p style='color:green;font-family:sans-serif'>✅ Columna wa_apikey_apoderado agregada a alumnos.</p>";
    } catch(PDOException $e) {
        echo "<p style='color:orange;font-family:sans-serif'>ℹ️ Columna wa_apikey_apoderado ya existía.</p>";
    }

    // Verificar que las tablas grados y materias existen (necesarias para FK)
    $tablas = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $reqs   = ['grados','materias','personal'];
    $falta  = false;
    foreach ($reqs as $t) {
        if (!in_array($t, $tablas)) {
            echo "<p style='color:red;font-family:sans-serif'>❌ Falta tabla <b>$t</b> — importa bk_basededatos.sql primero.</p>";
            $falta = true;
        }
    }

    if (!$falta) {
        echo "<h2 style='color:green;font-family:sans-serif'>✅ Tabla docente_materia_grado creada correctamente.</h2>";
        echo "<p style='font-family:sans-serif'>Ya puedes usar <strong>Asig. Docentes</strong> en el menú.</p>";
        echo "<a href='index.php?c=Asignacion' style='font-family:sans-serif'>→ Ir a Asignaciones</a>";
    }

    echo "<br><br><p style='color:red;font-family:sans-serif'>⚠️ Elimina este archivo después de usarlo.</p>";

} catch (PDOException $e) {
    echo "<h2 style='color:red;font-family:sans-serif'>Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
    echo "<p style='font-family:sans-serif'>Verifica que las tablas <b>personal</b>, <b>materias</b> y <b>grados</b> existan.</p>";
}
?>
