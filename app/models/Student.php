<?php
class Student {
    private $conn;
    private $tabla = "alumnos";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. LEER TODOS LOS ALUMNOS (con filtro opcional)
    public function read($busqueda = "") {
        $query = "SELECT a.*, g.nombre as nombre_grado, g.seccion
                  FROM " . $this->tabla . " a
                  LEFT JOIN grados g ON a.grado_id = g.id";

        if (!empty($busqueda)) {
            $query .= " WHERE a.nombres LIKE :busqueda
                        OR a.apellidos LIKE :busqueda
                        OR a.codigo LIKE :busqueda";
        }
        $query .= " ORDER BY a.id DESC";

        $stmt = $this->conn->prepare($query);
        if (!empty($busqueda)) {
            $termino = "%" . $busqueda . "%";
            $stmt->bindParam(':busqueda', $termino);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. OBTENER UNO POR ID
    public function getById($id) {
        $query = "SELECT a.*, g.nombre as nombre_grado, g.seccion
                  FROM " . $this->tabla . " a
                  LEFT JOIN grados g ON a.grado_id = g.id
                  WHERE a.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. CREAR ALUMNO (contraseña por defecto: 123456)
    public function create($datos) {
        $claveDefecto = password_hash("123456", PASSWORD_DEFAULT);
        $foto = $datos['foto'] ?? 'default.png';

        $query = "INSERT INTO " . $this->tabla . "
                 (codigo, nombres, apellidos, correo, contrasena, grado_id, foto, estado)
                 VALUES (:codigo, :nombres, :apellidos, :correo, :contrasena, :grado_id, :foto, 'activo')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':codigo',     $datos['codigo']);
        $stmt->bindParam(':nombres',    $datos['nombres']);
        $stmt->bindParam(':apellidos',  $datos['apellidos']);
        $stmt->bindParam(':correo',     $datos['correo']);
        $stmt->bindParam(':contrasena', $claveDefecto);
        $stmt->bindParam(':grado_id',   $datos['grado_id']);
        $stmt->bindParam(':foto',       $foto);
        return $stmt->execute();
    }

    // 4. ACTUALIZAR DATOS
    public function update($datos) {
        if (!empty($datos['foto'])) {
            $query = "UPDATE " . $this->tabla . "
                      SET nombres = :nombres, apellidos = :apellidos,
                          correo = :correo, grado_id = :grado_id,
                          codigo = :codigo, foto = :foto
                      WHERE id = :id";
        } else {
            $query = "UPDATE " . $this->tabla . "
                      SET nombres = :nombres, apellidos = :apellidos,
                          correo = :correo, grado_id = :grado_id,
                          codigo = :codigo
                      WHERE id = :id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombres',   $datos['nombres']);
        $stmt->bindParam(':apellidos', $datos['apellidos']);
        $stmt->bindParam(':correo',    $datos['correo']);
        $stmt->bindParam(':grado_id',  $datos['grado_id']);
        $stmt->bindParam(':codigo',    $datos['codigo']);
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

    // 7. LOGIN ALUMNO (Portal del Alumno)
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

    // 8. ACTUALIZAR CONTRASEÑA
    public function updatePassword($id, $nuevoHash) {
        $query = "UPDATE " . $this->tabla . " SET contrasena = :contrasena WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':contrasena', $nuevoHash);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
