<?php
require_once '../app/config/db.php';
require_once '../app/models/Employee.php';

class EmployeeController {
    private $personalModel;
    private $db;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: ?c=Auth&a=login");
            exit;
        }
        
        if ($_SESSION['role'] != 'admin') {
            header("Location: ?c=Dashboard");
            exit;
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->personalModel = new Employee($this->db);
    }

    // 1. LISTAR PERSONAL
    public function index() {
        $busqueda  = isset($_GET['q']) ? $_GET['q'] : "";
        $employees = $this->personalModel->read($busqueda);
        
        // Áreas activas para el modal de crear
        $stmt        = $this->db->query("SELECT * FROM areas WHERE estado = 'activo' ORDER BY nombre");
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once '../app/views/employees/index.php';
    }

    // 2. GUARDAR NUEVO MIEMBRO DEL PERSONAL
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipo = $_POST['tipo_personal'] ?? 'administrativo';
            if (!in_array($tipo, ['docente', 'administrativo', 'apoyo'])) {
                $tipo = 'administrativo';
            }

            $datos = [
                'codigo'        => trim(htmlspecialchars($_POST['employee_code'] ?? '')),
                'nombres'       => trim(htmlspecialchars($_POST['first_name']    ?? '')),
                'apellidos'     => trim(htmlspecialchars($_POST['last_name']     ?? '')),
                'correo'        => trim($_POST['email']         ?? ''),
                'area_id'       => intval($_POST['department_id'] ?? 0),
                'cargo'         => trim(htmlspecialchars($_POST['position']      ?? '')),
                'tipo_personal' => $tipo,
                'foto'          => 'default.png'
            ];

            if (empty($datos['codigo']) || empty($datos['nombres']) || empty($datos['apellidos'])) {
                header("Location: ?c=Employee&err=campos_vacios");
                exit;
            }

            // Subir foto si se envió
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $fotoNombre = $this->subirFoto($_FILES['foto']);
                if ($fotoNombre) $datos['foto'] = $fotoNombre;
            }

            try {
                if ($this->personalModel->create($datos)) {
                    header("Location: ?c=Employee&msg=guardado");
                } else {
                    header("Location: ?c=Employee&err=error");
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    header("Location: ?c=Employee&err=codigo_duplicado");
                } else {
                    error_log("EmployeeController::store() — " . $e->getMessage());
                    header("Location: ?c=Employee&err=error");
                }
            }
        }
    }

    // 3. MOSTRAR FORMULARIO DE EDICIÓN
    public function edit() {
        if (isset($_GET['id'])) {
            $emp  = $this->personalModel->getById($_GET['id']);
            $stmt = $this->db->query("SELECT * FROM areas WHERE estado = 'activo' ORDER BY nombre");
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($emp) {
                require_once '../app/views/employees/edit.php';
            } else {
                header("Location: ?c=Employee");
            }
        }
    }

    // 4. GUARDAR CAMBIOS DE EDICIÓN
    public function update_data() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipo = $_POST['tipo_personal'] ?? 'administrativo';
            if (!in_array($tipo, ['docente', 'administrativo', 'apoyo'])) {
                $tipo = 'administrativo';
            }

            $datos = [
                'id'            => intval($_POST['id'] ?? 0),
                'codigo'        => trim(htmlspecialchars($_POST['employee_code'] ?? '')),
                'nombres'       => trim(htmlspecialchars($_POST['first_name']    ?? '')),
                'apellidos'     => trim(htmlspecialchars($_POST['last_name']     ?? '')),
                'correo'        => trim($_POST['email']           ?? ''),
                'area_id'       => intval($_POST['department_id'] ?? 0),
                'cargo'         => trim(htmlspecialchars($_POST['position']      ?? '')),
                'tipo_personal' => $tipo
            ];

            // Subir nueva foto si se envió
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $fotoNombre = $this->subirFoto($_FILES['foto']);
                if ($fotoNombre) $datos['foto'] = $fotoNombre;
            }

            try {
                $this->personalModel->update($datos);
                header("Location: ?c=Employee&msg=actualizado");
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    header("Location: ?c=Employee&err=codigo_duplicado");
                } else {
                    error_log("EmployeeController::update_data() — " . $e->getMessage());
                    header("Location: ?c=Employee&err=error");
                }
            }
        }
    }

    // HELPER: subir foto de perfil
    private function subirFoto($file) {
        $ext             = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxBytes        = 3 * 1024 * 1024; // 3 MB

        if (!in_array($ext, $tiposPermitidos) || $file['size'] > $maxBytes) return null;

        $nombre  = 'foto_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
        $destino = __DIR__ . '/../../public/uploads/' . $nombre;

        return move_uploaded_file($file['tmp_name'], $destino) ? $nombre : null;
    }

    // 5. ACTIVAR / DESACTIVAR
    public function toggle() {
        if (isset($_GET['id']) && isset($_GET['status'])) {
            $id          = $_GET['id'];
            $estadoActual = $_GET['status'];
            $nuevoEstado = ($estadoActual == 'activo') ? 'inactivo' : 'activo';
            $this->personalModel->toggleStatus($id, $nuevoEstado);
            header("Location: ?c=Employee&msg=estado_cambiado");
        }
    }

    // 6. ELIMINAR
    public function delete() {
        if (isset($_GET['id'])) {
            $this->personalModel->delete($_GET['id']);
            header("Location: ?c=Employee&msg=eliminado");
        }
    }
}
?>
