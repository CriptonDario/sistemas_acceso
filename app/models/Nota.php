<?php
class Nota {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function ensureFinalPeriodSupported() {
        try {
            $stmt = $this->conn->query("SHOW COLUMNS FROM notas LIKE 'periodo'");
            $col = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$col) return true;

            $type = $col['Type'] ?? '';
            if (stripos($type, 'final') !== false) return true;

            $sql = "ALTER TABLE notas MODIFY COLUMN periodo ENUM('T1','T2','T3','FINAL') NOT NULL COMMENT 'Trimestre o consolidado anual'";
            $this->conn->exec($sql);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Obtener notas de un alumno (todos los trimestres del año)
    public function getByAlumno($alumno_id, $anio = null) {
        $anio = $anio ?? date('Y');
        $stmt = $this->conn->prepare(
            "SELECT n.*, m.nombre as materia_nombre
             FROM notas n
             INNER JOIN materias m ON n.materia_id = m.id
             WHERE n.alumno_id = :aid AND n.anio = :anio
             ORDER BY m.nombre, n.periodo"
        );
        $stmt->bindValue(':aid',  $alumno_id, PDO::PARAM_INT);
        $stmt->bindValue(':anio', $anio,      PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener notas organizadas por materia → trimestre (para mostrar tabla bonita)
    public function getResumenAlumno($alumno_id, $anio = null) {
        $anio  = $anio ?? date('Y');
        $filas = $this->getByAlumno($alumno_id, $anio);
        $res   = [];
        foreach ($filas as $f) {
            $mat = $f['materia_nombre'];
            if (!isset($res[$mat])) {
                $res[$mat] = ['T1' => null, 'T2' => null, 'T3' => null, 'FINAL' => null, 'promedio' => null];
            }
            $res[$mat][$f['periodo']] = $f['nota'];
        }
        // Calcular promedio
        foreach ($res as $mat => &$datos) {
            if ($datos['FINAL'] !== null && $datos['FINAL'] !== '') {
                $datos['promedio'] = round(floatval($datos['FINAL']), 1);
            } else {
                $vals = array_filter([$datos['T1'], $datos['T2'], $datos['T3']], fn($v) => $v !== null && $v !== '');
                $datos['promedio'] = count($vals) ? round(array_sum($vals) / count($vals), 1) : null;
            }
        }
        return $res;
    }

    // Guardar o actualizar una nota
    public function guardar($alumno_id, $materia_id, $periodo, $anio, $nota, $obs, $user_id) {
        $this->ensureFinalPeriodSupported();
        $stmt = $this->conn->prepare(
            "INSERT INTO notas (alumno_id, materia_id, periodo, anio, nota, observacion, registrado_por)
             VALUES (:aid, :mid, :per, :anio, :nota, :obs, :uid)
             ON DUPLICATE KEY UPDATE nota=:nota2, observacion=:obs2, registrado_por=:uid2"
        );
        $stmt->bindValue(':aid',   $alumno_id,  PDO::PARAM_INT);
        $stmt->bindValue(':mid',   $materia_id, PDO::PARAM_INT);
        $stmt->bindValue(':per',   $periodo);
        $stmt->bindValue(':anio',  $anio,       PDO::PARAM_INT);
        $stmt->bindValue(':nota',  $nota);
        $stmt->bindValue(':obs',   $obs);
        $stmt->bindValue(':uid',   $user_id,    PDO::PARAM_INT);
        $stmt->bindValue(':nota2', $nota);
        $stmt->bindValue(':obs2',  $obs);
        $stmt->bindValue(':uid2',  $user_id,    PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function consolidarAnual($grado_id, $materia_id, $anio, $user_id) {
        $stmt = $this->conn->prepare(
            "SELECT a.id AS alumno_id,
                    n1.nota AS t1,
                    n2.nota AS t2,
                    n3.nota AS t3
             FROM alumnos a
             LEFT JOIN notas n1 ON n1.alumno_id = a.id AND n1.materia_id = :mid1 AND n1.periodo = 'T1' AND n1.anio = :anio1
             LEFT JOIN notas n2 ON n2.alumno_id = a.id AND n2.materia_id = :mid2 AND n2.periodo = 'T2' AND n2.anio = :anio2
             LEFT JOIN notas n3 ON n3.alumno_id = a.id AND n3.materia_id = :mid3 AND n3.periodo = 'T3' AND n3.anio = :anio3
             WHERE a.grado_id = :gid AND a.estado = 'activo'
             ORDER BY a.apellidos, a.nombres"
        );
        $stmt->bindValue(':gid', $grado_id, PDO::PARAM_INT);
        $stmt->bindValue(':mid1', $materia_id, PDO::PARAM_INT);
        $stmt->bindValue(':mid2', $materia_id, PDO::PARAM_INT);
        $stmt->bindValue(':mid3', $materia_id, PDO::PARAM_INT);
        $stmt->bindValue(':anio1', $anio, PDO::PARAM_INT);
        $stmt->bindValue(':anio2', $anio, PDO::PARAM_INT);
        $stmt->bindValue(':anio3', $anio, PDO::PARAM_INT);
        $stmt->execute();
        $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $consolidados = 0;
        foreach ($alumnos as $al) {
            $vals = array_filter([
                $al['t1'] ?? null,
                $al['t2'] ?? null,
                $al['t3'] ?? null,
            ], fn($v) => $v !== null && $v !== '');

            if (empty($vals)) continue;

            $promedio = round(array_sum($vals) / count($vals), 1);
            $this->guardar(
                intval($al['alumno_id']),
                $materia_id,
                'FINAL',
                $anio,
                $promedio,
                'Promedio anual consolidado automáticamente',
                $user_id
            );
            $consolidados++;
        }

        return $consolidados;
    }

    // Guardar o actualizar solo la observación de una nota
    public function guardarObservacion($alumno_id, $materia_id, $periodo, $anio, $obs, $user_id) {
        $this->ensureFinalPeriodSupported();
        $stmt = $this->conn->prepare(
            "INSERT INTO notas (alumno_id, materia_id, periodo, anio, nota, observacion, registrado_por)
             VALUES (:aid, :mid, :per, :anio, NULL, :obs, :uid)
             ON DUPLICATE KEY UPDATE observacion=:obs2, registrado_por=:uid2"
        );
        $stmt->bindValue(':aid',   $alumno_id,  PDO::PARAM_INT);
        $stmt->bindValue(':mid',   $materia_id, PDO::PARAM_INT);
        $stmt->bindValue(':per',   $periodo);
        $stmt->bindValue(':anio',  $anio,       PDO::PARAM_INT);
        $stmt->bindValue(':obs',   $obs);
        $stmt->bindValue(':uid',   $user_id,    PDO::PARAM_INT);
        $stmt->bindValue(':obs2',  $obs);
        $stmt->bindValue(':uid2',  $user_id,    PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Obtener notas de todo un grado en una materia (para el docente)
    public function getByGradoMateria($grado_id, $materia_id, $periodo, $anio) {
        $stmt = $this->conn->prepare(
            "SELECT a.id, a.codigo, a.nombres, a.apellidos, a.foto,
                    n.nota, n.observacion
             FROM alumnos a
             LEFT JOIN notas n ON n.alumno_id = a.id
                   AND n.materia_id = :mid
                   AND n.periodo    = :per
                   AND n.anio       = :anio
             WHERE a.grado_id = :gid AND a.estado = 'activo'
             ORDER BY a.apellidos"
        );
        $stmt->bindValue(':gid',  $grado_id,  PDO::PARAM_INT);
        $stmt->bindValue(':mid',  $materia_id,PDO::PARAM_INT);
        $stmt->bindValue(':per',  $periodo);
        $stmt->bindValue(':anio', $anio,      PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
