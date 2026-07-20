<?php
class Alumno {
    private $conn;
    private $tabla = "alumnos";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Leer todos con filtro opcional
    public function read($busqueda = "") {
        $query = "SELECT a.*, g.nombre as nombre_grado, g.nivel
                  FROM " . $this->tabla . " a
                  LEFT JOIN grados g ON a.grado_id = g.id";
        if (!empty($busqueda)) {
            $query .= " WHERE a.nombres LIKE :b1 OR a.apellidos LIKE :b2
                          OR a.codigo LIKE :b3 OR a.dni LIKE :b4";
        }
        $query .= " ORDER BY g.nivel, g.id, a.apellidos";
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

    // Obtener uno por ID
    public function getById($id) {
        $stmt = $this->conn->prepare(
            "SELECT a.*, g.nombre as nombre_grado, g.nivel
             FROM " . $this->tabla . " a
             LEFT JOIN grados g ON a.grado_id = g.id
             WHERE a.id = :id LIMIT 1"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Buscar por código QR
    public function getByCodigo($codigo) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM " . $this->tabla . " WHERE codigo = :codigo AND estado = 'activo' LIMIT 1"
        );
        $stmt->bindValue(':codigo', $codigo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Login alumno (correo + contraseña)
    public function login($correo, $contrasena) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM " . $this->tabla . " WHERE correo = :correo AND estado = 'activo' LIMIT 1"
        );
        $stmt->bindValue(':correo', $correo);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fila && !empty($fila['contrasena']) && password_verify($contrasena, $fila['contrasena'])) {
            return $fila;
        }
        return false;
    }

    // Crear alumno
    public function create($datos) {
        $clave = password_hash("123456", PASSWORD_DEFAULT);
        $foto  = $datos['foto'] ?? 'default.png';
        $stmt  = $this->conn->prepare(
            "INSERT INTO " . $this->tabla . "
             (codigo, nombres, apellidos, dni, fecha_nacimiento, grado_id, correo, contrasena,
              foto, nombre_apoderado, telefono_apoderado, wa_apikey_apoderado, estado)
             VALUES (:codigo,:nombres,:apellidos,:dni,:fnac,:grado,:correo,:clave,
                     :foto,:apoderado,:telefono,:wa_apikey,'activo')"
        );
        $stmt->bindValue(':codigo',    $datos['codigo']);
        $stmt->bindValue(':nombres',   $datos['nombres']);
        $stmt->bindValue(':apellidos', $datos['apellidos']);
        $stmt->bindValue(':dni',       $datos['dni']       ?? null);
        $stmt->bindValue(':fnac',      $datos['fecha_nacimiento'] ?? null);
        $stmt->bindValue(':grado',     $datos['grado_id'],  PDO::PARAM_INT);
        $stmt->bindValue(':correo',    $datos['correo']    ?? null);
        $stmt->bindValue(':clave',     $clave);
        $stmt->bindValue(':foto',      $foto);
        $stmt->bindValue(':apoderado', $datos['nombre_apoderado']    ?? null);
        $stmt->bindValue(':telefono',  $datos['telefono_apoderado']  ?? null);
        $stmt->bindValue(':wa_apikey', $datos['wa_apikey_apoderado'] ?? null);
        return $stmt->execute();
    }

    // Actualizar alumno
    public function update($datos) {
        $conFoto = !empty($datos['foto']);
        $sql = "UPDATE " . $this->tabla . "
                SET codigo=:codigo, nombres=:nombres, apellidos=:apellidos, dni=:dni,
                    fecha_nacimiento=:fnac, grado_id=:grado, correo=:correo,
                    nombre_apoderado=:apoderado, telefono_apoderado=:telefono,
                    wa_apikey_apoderado=:wa_apikey"
              . ($conFoto ? ", foto=:foto" : "") .
                " WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':codigo',    $datos['codigo']);
        $stmt->bindValue(':nombres',   $datos['nombres']);
        $stmt->bindValue(':apellidos', $datos['apellidos']);
        $stmt->bindValue(':dni',       $datos['dni']       ?? null);
        $stmt->bindValue(':fnac',      $datos['fecha_nacimiento'] ?? null);
        $stmt->bindValue(':grado',     $datos['grado_id'],  PDO::PARAM_INT);
        $stmt->bindValue(':correo',    $datos['correo']    ?? null);
        $stmt->bindValue(':apoderado', $datos['nombre_apoderado']   ?? null);
        $stmt->bindValue(':telefono',  $datos['telefono_apoderado'] ?? null);
        $stmt->bindValue(':wa_apikey', $datos['wa_apikey_apoderado'] ?? null);
        if ($conFoto) $stmt->bindValue(':foto', $datos['foto']);
        $stmt->bindValue(':id', $datos['id'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Cambiar estado
    public function toggleStatus($id, $estado) {
        $stmt = $this->conn->prepare("UPDATE " . $this->tabla . " SET estado=:e WHERE id=:id");
        $stmt->bindValue(':e',  $estado);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Cambiar contraseña
    public function updatePassword($id, $hash) {
        $stmt = $this->conn->prepare("UPDATE " . $this->tabla . " SET contrasena=:h WHERE id=:id");
        $stmt->bindValue(':h',  $hash);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
