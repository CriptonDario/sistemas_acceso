<?php
class Visitor {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. REGISTRAR ENTRADA
    public function registerEntry($dni, $nombre, $institucion, $motivo) {
        // Verificar si el visitante ya existe
        $queryCheck = "SELECT id FROM visitantes WHERE dni = :dni LIMIT 1";
        $stmtCheck = $this->conn->prepare($queryCheck);
        $stmtCheck->bindParam(':dni', $dni);
        $stmtCheck->execute();
        
        if ($fila = $stmtCheck->fetch(PDO::FETCH_ASSOC)) {
            $visitanteId = $fila['id'];
        } else {
            // Crear nuevo visitante
            $queryNuevo = "INSERT INTO visitantes (dni, nombre_completo, institucion) VALUES (:dni, :nombre, :institucion)";
            $stmtNuevo = $this->conn->prepare($queryNuevo);
            $stmtNuevo->bindParam(':dni',         $dni);
            $stmtNuevo->bindParam(':nombre',      $nombre);
            $stmtNuevo->bindParam(':institucion', $institucion);
            $stmtNuevo->execute();
            $visitanteId = $this->conn->lastInsertId();
        }

        // Registrar log de visita
        $queryLog = "INSERT INTO registros_visitas (visitante_id, motivo, entrada) VALUES (:vid, :motivo, NOW())";
        $stmtLog = $this->conn->prepare($queryLog);
        $stmtLog->bindParam(':vid',    $visitanteId);
        $stmtLog->bindParam(':motivo', $motivo);
        
        return $stmtLog->execute();
    }

    // 2. REGISTRAR SALIDA
    public function registerExit($dni) {
        $query = "SELECT rv.id, v.nombre_completo 
                  FROM registros_visitas rv
                  JOIN visitantes v ON rv.visitante_id = v.id
                  WHERE v.dni = :dni AND rv.salida IS NULL 
                  ORDER BY rv.id DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dni', $dni);
        $stmt->execute();

        if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $update = "UPDATE registros_visitas SET salida = NOW() WHERE id = :logId";
            $stmtUp = $this->conn->prepare($update);
            $stmtUp->bindParam(':logId', $fila['id']);
            $stmtUp->execute();
            return "Salida registrada para: " . $fila['nombre_completo'];
        } else {
            return false;
        }
    }

    // 3. HISTORIAL CON FILTROS
    public function getHistoryWithFilters($inicio, $fin, $busqueda = "") {
        $query = "SELECT rv.id, rv.motivo, rv.entrada, rv.salida, 
                         v.dni, v.nombre_completo, v.institucion 
                  FROM registros_visitas rv
                  INNER JOIN visitantes v ON rv.visitante_id = v.id
                  WHERE rv.entrada BETWEEN :inicio AND :fin";
        
        if (!empty($busqueda)) {
            $query .= " AND (v.nombre_completo LIKE :busqueda OR v.dni LIKE :busqueda OR v.institucion LIKE :busqueda)";
        }
        
        $query .= " ORDER BY rv.entrada DESC";
        
        $stmt = $this->conn->prepare($query);
        
        $inicioCompleto = $inicio . " 00:00:00";
        $finCompleto    = $fin . " 23:59:59";
        
        $stmt->bindParam(':inicio', $inicioCompleto);
        $stmt->bindParam(':fin',    $finCompleto);
        
        if (!empty($busqueda)) {
            $termino = "%" . $busqueda . "%";
            $stmt->bindParam(':busqueda', $termino);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
