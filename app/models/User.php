<?php
class User {
    private $conn;
    private $tabla = "usuarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. LEER TODOS LOS USUARIOS
    public function readAll() {
        $query = "SELECT * FROM " . $this->tabla . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER UN USUARIO
    public function getById($id) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. CREAR USUARIO
    public function create($usuario, $contrasena, $rol) {
        // Verificar si el usuario ya existe
        $check = "SELECT id FROM " . $this->tabla . " WHERE usuario = :u";
        $stmtCheck = $this->conn->prepare($check);
        $stmtCheck->bindParam(':u', $usuario);
        $stmtCheck->execute();
        
        if ($stmtCheck->rowCount() > 0) {
            return false; // El usuario ya existe
        }

        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO " . $this->tabla . " (usuario, contrasena, rol, estado) VALUES (:u, :p, :r, 'activo')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':u', $usuario);
        $stmt->bindParam(':p', $hash);
        $stmt->bindParam(':r', $rol);
        return $stmt->execute();
    }

    // 4. ACTUALIZAR USUARIO
    public function update($id, $usuario, $rol, $contrasena = null) {
        if (!empty($contrasena)) {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $query = "UPDATE " . $this->tabla . " SET usuario = :u, rol = :r, contrasena = :p WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':p', $hash);
        } else {
            $query = "UPDATE " . $this->tabla . " SET usuario = :u, rol = :r WHERE id = :id";
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->bindParam(':u',  $usuario);
        $stmt->bindParam(':r',  $rol);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // 5. CAMBIAR ESTADO
    public function toggleStatus($id, $nuevoEstado) {
        $query = "UPDATE " . $this->tabla . " SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $nuevoEstado);
        $stmt->bindParam(':id',     $id);
        return $stmt->execute();
    }

    // 6. CAMBIAR SOLO CONTRASEÑA
    public function updatePassword($id, $nuevoHash) {
        $query = "UPDATE " . $this->tabla . " SET contrasena = :p WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':p',  $nuevoHash);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // 7. ELIMINAR
    public function delete($id) {
        $query = "DELETE FROM " . $this->tabla . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
