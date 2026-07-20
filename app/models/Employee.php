<?php
class Employee {
    private $conn;
    private $tabla = "personal";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. LEER TODO EL PERSONAL
    public function read($busqueda = "") {
        $query = "SELECT p.*, ar.nombre as nombre_area 
                  FROM " . $this->tabla . " p
                  LEFT JOIN areas ar ON p.area_id = ar.id";
        
        if (!empty($busqueda)) {
            $query .= " WHERE p.nombres  LIKE :b1
                           OR p.apellidos LIKE :b2
                           OR p.codigo    LIKE :b3
                           OR p.cargo     LIKE :b4";
        }
        $query .= " ORDER BY p.id DESC";
        $stmt = $this->conn->prepare($query);
        if (!empty($busqueda)) {
            $t = "%" . $busqueda . "%";
            $stmt->bindValue(':b1', $t);
            $stmt->bindValue(':b2', $t);
            $stmt->bindValue(':b3', $t);
            $stmt->bindValue(':b4', $t);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER UNO
    public function getById($id) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. CREAR (Con contraseña por defecto '123456')
    public function create($datos) {
        $claveDefecto = password_hash("123456", PASSWORD_DEFAULT);
        $foto = $datos['foto'] ?? 'default.png';
        $query = "INSERT INTO " . $this->tabla . " 
                 (codigo, nombres, apellidos, correo, contrasena, area_id, cargo, tipo_personal, foto, estado) 
                 VALUES (:codigo, :nombres, :apellidos, :correo, :contrasena, :area, :cargo, :tipo, :foto, 'activo')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':codigo',     $datos['codigo']);
        $stmt->bindParam(':nombres',    $datos['nombres']);
        $stmt->bindParam(':apellidos',  $datos['apellidos']);
        $stmt->bindParam(':correo',     $datos['correo']);
        $stmt->bindParam(':contrasena', $claveDefecto);
        $stmt->bindParam(':area',       $datos['area_id']);
        $stmt->bindParam(':cargo',      $datos['cargo']);
        $tipo = isset($datos['tipo_personal']) ? $datos['tipo_personal'] : 'administrativo';
        $stmt->bindParam(':tipo',       $tipo);
        $stmt->bindParam(':foto',       $foto);
        return $stmt->execute();
    }

    // 4. ACTUALIZAR DATOS
    public function update($datos) {
        // Si viene foto nueva, actualizarla también
        if (!empty($datos['foto'])) {
            $query = "UPDATE " . $this->tabla . " 
                      SET nombres = :nombres, apellidos = :apellidos, 
                          correo = :correo, area_id = :area, cargo = :cargo, 
                          codigo = :codigo, tipo_personal = :tipo, foto = :foto
                      WHERE id = :id";
        } else {
            $query = "UPDATE " . $this->tabla . " 
                      SET nombres = :nombres, apellidos = :apellidos, 
                          correo = :correo, area_id = :area, cargo = :cargo, 
                          codigo = :codigo, tipo_personal = :tipo
                      WHERE id = :id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombres',   $datos['nombres']);
        $stmt->bindParam(':apellidos', $datos['apellidos']);
        $stmt->bindParam(':correo',    $datos['correo']);
        $stmt->bindParam(':area',      $datos['area_id']);
        $stmt->bindParam(':cargo',     $datos['cargo']);
        $stmt->bindParam(':codigo',    $datos['codigo']);
        $tipo = isset($datos['tipo_personal']) ? $datos['tipo_personal'] : 'administrativo';
        $stmt->bindParam(':tipo',      $tipo);
        if (!empty($datos['foto'])) $stmt->bindParam(':foto', $datos['foto']);
        $stmt->bindParam(':id',        $datos['id']);
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

    // 7. LOGIN PERSONAL (Portal del Empleado)
    public function login($correo, $contrasena) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE correo = :correo AND estado = 'activo' LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        
        if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($fila['contrasena']) && password_verify($contrasena, $fila['contrasena'])) {
                return $fila;
            }
        }
        return false;
    }

    // 8. ACTUALIZAR SOLO CONTRASEÑA
    public function updatePassword($id, $nuevoHash) {
        $query = "UPDATE " . $this->tabla . " SET contrasena = :contrasena WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':contrasena', $nuevoHash);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
