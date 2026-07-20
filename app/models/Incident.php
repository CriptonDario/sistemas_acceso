<?php
class Incident {
    private $conn;
    private $tabla = "incidencias";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. REGISTRAR INCIDENCIA
    public function create($titulo, $descripcion, $severidad, $usuarioId, $adjunto = null) {
        $query = "INSERT INTO " . $this->tabla . " (titulo, descripcion, severidad, registrado_por, adjunto) 
                  VALUES (:titulo, :descripcion, :severidad, :uid, :adjunto)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titulo',      $titulo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':severidad',   $severidad);
        $stmt->bindParam(':uid',         $usuarioId);
        $stmt->bindParam(':adjunto',     $adjunto);
        return $stmt->execute();
    }

    // 2. LISTAR CON FILTROS
    public function getWithFilters($inicio, $fin, $busqueda = "") {
        $query = "SELECT i.*, u.usuario as reportado_por 
                  FROM " . $this->tabla . " i
                  LEFT JOIN usuarios u ON i.registrado_por = u.id
                  WHERE (i.fecha_registro BETWEEN :inicio AND :fin)";

        if (!empty($busqueda)) {
            $query .= " AND (i.titulo LIKE :busqueda OR i.descripcion LIKE :busqueda OR u.usuario LIKE :busqueda)";
        }

        $query .= " ORDER BY i.fecha_registro DESC";

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
