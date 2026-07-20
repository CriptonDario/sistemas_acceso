<?php
class Department {
    private $conn;
    private $tabla = "areas";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. LEER TODAS LAS ÁREAS
    public function readAll() {
        $query = "SELECT * FROM " . $this->tabla . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. CREAR
    public function create($nombre, $tipo = 'academica') {
        $query = "INSERT INTO " . $this->tabla . " (nombre, tipo, estado) VALUES (:nombre, :tipo, 'activo')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':tipo', $tipo);
        return $stmt->execute();
    }

    // 3. OBTENER UNA
    public function getById($id) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. ACTUALIZAR NOMBRE
    public function update($id, $nombre, $tipo = null) {
        if ($tipo) {
            $query = "UPDATE " . $this->tabla . " SET nombre = :nombre, tipo = :tipo WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':id', $id);
        } else {
            $query = "UPDATE " . $this->tabla . " SET nombre = :nombre WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':id', $id);
        }
        return $stmt->execute();
    }

    // 5. CAMBIAR ESTADO
    public function toggleStatus($id, $nuevoEstado) {
        $query = "UPDATE " . $this->tabla . " SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $nuevoEstado);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // 6. ELIMINAR
    public function delete($id) {
        $query = "DELETE FROM " . $this->tabla . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
