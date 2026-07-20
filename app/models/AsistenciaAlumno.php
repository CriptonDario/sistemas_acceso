<?php
class AsistenciaAlumno {
    private $conn;
    private $tabla = "asistencia_alumnos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getRegistroHoy($alumno_id) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM " . $this->tabla . "
             WHERE alumno_id=:aid AND fecha=:hoy LIMIT 1"
        );
        $stmt->bindValue(':aid', $alumno_id, PDO::PARAM_INT);
        $stmt->bindValue(':hoy', date('Y-m-d'));
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarEntrada($alumno_id, $hora, $estado) {
        $stmt = $this->conn->prepare(
            "INSERT INTO " . $this->tabla . " (alumno_id, fecha, hora_entrada, estado)
             VALUES (:aid, :fecha, :hora, :estado)"
        );
        $stmt->bindValue(':aid',    $alumno_id, PDO::PARAM_INT);
        $stmt->bindValue(':fecha',  date('Y-m-d'));
        $stmt->bindValue(':hora',   $hora);
        $stmt->bindValue(':estado', $estado);
        return $stmt->execute();
    }

    public function registrarSalida($id, $hora) {
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->tabla . " SET hora_salida=:hora WHERE id=:id"
        );
        $stmt->bindValue(':hora', $hora);
        $stmt->bindValue(':id',   $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getByAlumno($alumno_id, $inicio, $fin) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM " . $this->tabla . "
             WHERE alumno_id=:aid AND fecha BETWEEN :ini AND :fin
             ORDER BY fecha DESC"
        );
        $stmt->bindValue(':aid', $alumno_id, PDO::PARAM_INT);
        $stmt->bindValue(':ini', $inicio);
        $stmt->bindValue(':fin', $fin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countToday() {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as total FROM " . $this->tabla . " WHERE fecha=:hoy"
        );
        $stmt->bindValue(':hoy', date('Y-m-d'));
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
?>
