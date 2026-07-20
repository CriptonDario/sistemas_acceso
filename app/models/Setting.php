<?php
class Setting {
    private $conn;
    private $tabla = "configuracion";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener un valor por su clave (Ej: 'hora_entrada', 'nombre_colegio')
    public function get($clave) {
        $query = "SELECT valor FROM " . $this->tabla . " WHERE clave = :clave LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':clave', $clave);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ? $fila['valor'] : null;
    }

    // Guardar un valor (UPDATE si existe)
    public function set($clave, $valor) {
        $query = "UPDATE " . $this->tabla . " SET valor = :valor WHERE clave = :clave";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':clave', $clave);
        return $stmt->execute();
    }

    // Insertar o actualizar (INSERT ... ON DUPLICATE KEY UPDATE)
    public function upsert($clave, $valor) {
        $stmt = $this->conn->prepare(
            "INSERT INTO " . $this->tabla . " (clave, valor)
             VALUES (:clave, :valor)
             ON DUPLICATE KEY UPDATE valor = :valor2"
        );
        $stmt->bindValue(':clave',  $clave);
        $stmt->bindValue(':valor',  $valor);
        $stmt->bindValue(':valor2', $valor);
        return $stmt->execute();
    }
}
?>
