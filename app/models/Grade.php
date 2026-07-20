<?php
class Grade {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. OBTENER NOTAS DE UN ALUMNO (todas las materias con sus bimestres)
    public function getByStudent($alumno_id) {
        $query = "SELECT n.*, m.nombre as materia_nombre,
                         p.nombres as docente_nombres, p.apellidos as docente_apellidos
                  FROM notas n
                  INNER JOIN materias m ON n.materia_id = m.id
                  LEFT JOIN personal p ON m.docente_id = p.id
                  WHERE n.alumno_id = :alumno_id
                  ORDER BY m.nombre ASC, n.bimestre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':alumno_id', $alumno_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. RESUMEN AGRUPADO POR MATERIA (promedio por materia para el alumno)
    public function getSummaryByStudent($alumno_id) {
        $query = "SELECT m.id as materia_id, m.nombre as materia,
                         p.nombres as docente_nombres, p.apellidos as docente_apellidos,
                         MAX(CASE WHEN n.bimestre = 1 THEN n.nota ELSE NULL END) AS bim1,
                         MAX(CASE WHEN n.bimestre = 2 THEN n.nota ELSE NULL END) AS bim2,
                         MAX(CASE WHEN n.bimestre = 3 THEN n.nota ELSE NULL END) AS bim3,
                         MAX(CASE WHEN n.bimestre = 4 THEN n.nota ELSE NULL END) AS bim4,
                         ROUND(AVG(n.nota), 1) AS promedio
                  FROM materias m
                  LEFT JOIN notas n ON n.materia_id = m.id AND n.alumno_id = :alumno_id
                  LEFT JOIN personal p ON m.docente_id = p.id
                  LEFT JOIN alumnos a ON a.id = :alumno_id2
                  WHERE m.grado_id = a.grado_id
                  GROUP BY m.id, m.nombre, p.nombres, p.apellidos
                  ORDER BY m.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':alumno_id',  $alumno_id);
        $stmt->bindParam(':alumno_id2', $alumno_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. GUARDAR O ACTUALIZAR UNA NOTA (INSERT ... ON DUPLICATE KEY UPDATE)
    public function upsert($alumno_id, $materia_id, $bimestre, $nota, $observacion = '') {
        $query = "INSERT INTO notas (alumno_id, materia_id, bimestre, nota, observacion)
                  VALUES (:alumno_id, :materia_id, :bimestre, :nota, :obs)
                  ON DUPLICATE KEY UPDATE nota = :nota2, observacion = :obs2, fecha_registro = NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':alumno_id',  $alumno_id);
        $stmt->bindParam(':materia_id', $materia_id);
        $stmt->bindParam(':bimestre',   $bimestre);
        $stmt->bindParam(':nota',       $nota);
        $stmt->bindParam(':obs',        $observacion);
        $stmt->bindParam(':nota2',      $nota);
        $stmt->bindParam(':obs2',       $observacion);
        return $stmt->execute();
    }

    // 4. ELIMINAR UNA NOTA
    public function delete($id) {
        $query = "DELETE FROM notas WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // 5. LISTAR TODAS LAS MATERIAS DE UN GRADO
    public function getMateriasByGrado($grado_id) {
        $query = "SELECT m.*, p.nombres as docente_nombres, p.apellidos as docente_apellidos
                  FROM materias m
                  LEFT JOIN personal p ON m.docente_id = p.id
                  WHERE m.grado_id = :grado_id AND m.estado = 'activo'
                  ORDER BY m.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':grado_id', $grado_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 6. LISTAR TODAS LAS MATERIAS (para admin)
    public function getAllMaterias() {
        $query = "SELECT m.*, g.nombre as grado_nombre, g.seccion,
                         p.nombres as docente_nombres, p.apellidos as docente_apellidos
                  FROM materias m
                  LEFT JOIN grados g ON m.grado_id = g.id
                  LEFT JOIN personal p ON m.docente_id = p.id
                  WHERE m.estado = 'activo'
                  ORDER BY g.nombre ASC, m.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 7. CREAR MATERIA
    public function createMateria($nombre, $grado_id, $docente_id) {
        $query = "INSERT INTO materias (nombre, grado_id, docente_id, estado)
                  VALUES (:nombre, :grado_id, :docente_id, 'activo')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre',     $nombre);
        $stmt->bindParam(':grado_id',   $grado_id);
        $stmt->bindParam(':docente_id', $docente_id);
        return $stmt->execute();
    }

    // 8. ELIMINAR MATERIA
    public function deleteMateria($id) {
        $query = "DELETE FROM materias WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // 9. OBTENER NOTAS POR MATERIA (para el docente/admin ver todos los alumnos)
    public function getByMateria($materia_id) {
        $query = "SELECT n.*, a.nombres, a.apellidos, a.codigo,
                         g.nombre as grado_nombre, g.seccion
                  FROM notas n
                  INNER JOIN alumnos a ON n.alumno_id = a.id
                  INNER JOIN grados g ON a.grado_id = g.id
                  WHERE n.materia_id = :materia_id
                  ORDER BY n.bimestre ASC, a.apellidos ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':materia_id', $materia_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
