<?php
class Materia {
    private $conn;
    private $tabla = "materias";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll($soloActivas = false) {
        $sql = "SELECT * FROM " . $this->tabla;
        if ($soloActivas) $sql .= " WHERE estado = 'activo'";
        $sql .= " ORDER BY nombre";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->tabla . " WHERE id=:id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($nombre, $nivel) {
        if (!in_array($nivel, ['primaria','secundaria','ambos'])) $nivel = 'ambos';
        $stmt = $this->conn->prepare(
            "INSERT INTO " . $this->tabla . " (nombre, nivel, estado) VALUES (:n, :niv, 'activo')"
        );
        $stmt->bindValue(':n',   $nombre);
        $stmt->bindValue(':niv', $nivel);
        return $stmt->execute();
    }

    public function update($id, $nombre, $nivel) {
        if (!in_array($nivel, ['primaria','secundaria','ambos'])) $nivel = 'ambos';
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->tabla . " SET nombre=:n, nivel=:niv WHERE id=:id"
        );
        $stmt->bindValue(':n',   $nombre);
        $stmt->bindValue(':niv', $nivel);
        $stmt->bindValue(':id',  $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function toggleStatus($id, $estado) {
        $stmt = $this->conn->prepare("UPDATE " . $this->tabla . " SET estado=:e WHERE id=:id");
        $stmt->bindValue(':e',  $estado);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
