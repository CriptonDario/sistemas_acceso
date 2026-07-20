<?php
class Attendance {
    private $conn;
    private $tabla = "registros_asistencia";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. OBTENER LOS ÚLTIMOS 10 REGISTROS (Para tabla de recientes)
    public function getRecentLogs() {
        $query = "SELECT a.*, p.nombres, p.apellidos, p.codigo 
                  FROM " . $this->tabla . " a
                  INNER JOIN personal p ON a.personal_id = p.id
                  ORDER BY a.id DESC LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. CONTAR CUÁNTOS HAN MARCADO HOY (Para tarjeta verde)
    public function countToday() {
        $hoy = date('Y-m-d');
        $query = "SELECT COUNT(*) as total FROM " . $this->tabla . " WHERE fecha = :hoy";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hoy', $hoy);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila['total'];
    }

    // 3. OBTENER HISTORIAL POR RANGO DE FECHAS (Para Reporte Excel)
    public function getHistoryByDate($inicio, $fin) {
        $query = "SELECT a.fecha, a.hora_entrada, a.hora_salida, a.estado,
                         p.nombres, p.apellidos, p.codigo, ar.nombre as area
                  FROM " . $this->tabla . " a
                  INNER JOIN personal p ON a.personal_id = p.id
                  LEFT JOIN areas ar ON p.area_id = ar.id
                  WHERE a.fecha BETWEEN :inicio AND :fin
                  ORDER BY a.fecha DESC, a.hora_entrada DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':inicio', $inicio);
        $stmt->bindParam(':fin', $fin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. ESTADÍSTICAS ÚLTIMOS 7 DÍAS (Para el Gráfico)
    public function getWeeklyStats() {
        $query = "SELECT fecha, COUNT(*) as total 
                  FROM " . $this->tabla . " 
                  GROUP BY fecha 
                  ORDER BY fecha DESC 
                  LIMIT 7";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 5. HISTORIAL CON FILTROS (Para Pantalla Historial)
    public function getLogsWithFilters($personal_id, $inicio, $fin) {
        $query = "SELECT a.fecha, a.hora_entrada, a.hora_salida, a.estado,
                         p.nombres, p.apellidos, p.codigo, ar.nombre as area
                  FROM " . $this->tabla . " a
                  INNER JOIN personal p ON a.personal_id = p.id
                  LEFT JOIN areas ar ON p.area_id = ar.id
                  WHERE a.fecha BETWEEN :inicio AND :fin";
        
        if (!empty($personal_id)) {
            $query .= " AND p.id = :pid";
        }
        $query .= " ORDER BY a.fecha DESC, a.hora_entrada DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':inicio', $inicio);
        $stmt->bindParam(':fin', $fin);
        if (!empty($personal_id)) {
            $stmt->bindParam(':pid', $personal_id);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 6. CONTAR TARDANZAS DE HOY
    public function countLatesToday($horaLimite) {
        $hoy = date('Y-m-d');
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->tabla . " 
                  WHERE fecha = :hoy AND hora_entrada > :limite";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hoy', $hoy);
        $stmt->bindParam(':limite', $horaLimite);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila['total'];
    }
}
?>
