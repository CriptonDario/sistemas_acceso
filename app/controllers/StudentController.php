<?php
require_once '../app/config/db.php';
require_once '../app/models/Student.php';
require_once '../app/models/Grade.php';

class StudentController {
    private $studentModel;
    private $gradeModel;
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
        $this->studentModel = new Student($this->db);
        $this->gradeModel   = new Grade($this->db);
    }

    // HELPER: obtener todos los grados
    private function getGrados() {
        $stmt = $this->db->query("SELECT * FROM grados WHERE estado = 'activo' ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // HELPER: obtener docentes (tipo docente)
    private function getDocentes() {
        $stmt = $this->db->query("SELECT id, nombres, apellidos FROM personal WHERE tipo_personal = 'docente' AND estado = 'activo' ORDER BY apellidos ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 1. LISTAR ALUMNOS
    public function index() {
        $busqueda = isset($_GET['q']) ? $_GET['q'] : "";
        $students = $this->studentModel->read($busqueda);
        $grados   = $this->getGrados();
        require_once '../app/views/students/index.php';
    }

    // 2. GUARDAR NUEVO ALUMNO
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'codigo'    => trim(htmlspecialchars($_POST['codigo']    ?? '')),
                'nombres'   => trim(htmlspecialchars($_POST['nombres']   ?? '')),
                'apellidos' => trim(htmlspecialchars($_POST['apellidos'] ?? '')),
                'correo'    => trim($_POST['correo']    ?? ''),
                'grado_id'  => intval($_POST['grado_id']  ?? 0),
                'foto'      => 'default.png'
            ];

            if (empty($datos['codigo']) || empty($datos['nombres']) || empty($datos['apellidos'])) {
                header("Location: ?c=Student&err=campos_vacios");
                exit;
            }

            // Subir foto si se envió
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $fotoNombre = $this->subirFoto($_FILES['foto']);
                if ($fotoNombre) $datos['foto'] = $fotoNombre;
            }

            try {
                if ($this->studentModel->create($datos)) {
                    header("Location: ?c=Student&msg=guardado");
                } else {
                    header("Location: ?c=Student&err=error");
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    header("Location: ?c=Student&err=codigo_duplicado");
                } else {
                    error_log("StudentController::store() — " . $e->getMessage());
                    header("Location: ?c=Student&err=error");
                }
            }
        }
    }

    // 3. FORMULARIO DE EDICIÓN
    public function edit() {
        if (isset($_GET['id'])) {
            $student = $this->studentModel->getById($_GET['id']);
            $grados  = $this->getGrados();
            if ($student) {
                require_once '../app/views/students/edit.php';
            } else {
                header("Location: ?c=Student");
            }
        }
    }

    // 4. GUARDAR EDICIÓN
    public function update_data() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'id'        => intval($_POST['id']        ?? 0),
                'codigo'    => trim(htmlspecialchars($_POST['codigo']    ?? '')),
                'nombres'   => trim(htmlspecialchars($_POST['nombres']   ?? '')),
                'apellidos' => trim(htmlspecialchars($_POST['apellidos'] ?? '')),
                'correo'    => trim($_POST['correo']    ?? ''),
                'grado_id'  => intval($_POST['grado_id']  ?? 0),
            ];

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $fotoNombre = $this->subirFoto($_FILES['foto']);
                if ($fotoNombre) $datos['foto'] = $fotoNombre;
            }

            try {
                $this->studentModel->update($datos);
                header("Location: ?c=Student&msg=actualizado");
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    header("Location: ?c=Student&err=codigo_duplicado");
                } else {
                    error_log("StudentController::update_data() — " . $e->getMessage());
                    header("Location: ?c=Student&err=error");
                }
            }
        }
    }

    // 5. VER NOTAS DE UN ALUMNO (admin)
    public function notas() {
        if (!isset($_GET['id'])) {
            header("Location: ?c=Student");
            exit;
        }

        $student  = $this->studentModel->getById($_GET['id']);
        if (!$student) {
            header("Location: ?c=Student");
            exit;
        }

        $resumen  = $this->gradeModel->getSummaryByStudent($_GET['id']);
        $materias = $this->gradeModel->getMateriasByGrado($student['grado_id']);
        $docentes = $this->getDocentes();
        require_once '../app/views/students/notas.php';
    }

    // 6. GUARDAR / ACTUALIZAR UNA NOTA
    public function save_nota() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Student");
            exit;
        }

        $alumno_id  = intval($_POST['alumno_id']  ?? 0);
        $materia_id = intval($_POST['materia_id'] ?? 0);
        $bimestre   = intval($_POST['bimestre']   ?? 0);
        $nota       = floatval($_POST['nota']     ?? 0);
        $obs        = trim(htmlspecialchars($_POST['observacion'] ?? ''));

        if ($alumno_id < 1 || $materia_id < 1 || $bimestre < 1 || $bimestre > 4 || $nota < 0 || $nota > 20) {
            header("Location: ?c=Student&a=notas&id=$alumno_id&err=datos_invalidos");
            exit;
        }

        $this->gradeModel->upsert($alumno_id, $materia_id, $bimestre, $nota, $obs);
        header("Location: ?c=Student&a=notas&id=$alumno_id&msg=nota_guardada");
        exit;
    }

    // 7. ACTIVAR / DESACTIVAR
    public function toggle() {
        if (isset($_GET['id']) && isset($_GET['status'])) {
            $nuevoEstado = ($_GET['status'] == 'activo') ? 'inactivo' : 'activo';
            $this->studentModel->toggleStatus($_GET['id'], $nuevoEstado);
            header("Location: ?c=Student&msg=estado_cambiado");
        }
    }

    // 8. ELIMINAR ALUMNO
    public function delete() {
        if (isset($_GET['id'])) {
            $this->studentModel->delete($_GET['id']);
            header("Location: ?c=Student&msg=eliminado");
        }
    }

    // 9. GESTIÓN DE MATERIAS
    public function materias() {
        $materias = $this->gradeModel->getAllMaterias();
        $grados   = $this->getGrados();
        $docentes = $this->getDocentes();
        require_once '../app/views/students/materias.php';
    }

    // 10. CREAR MATERIA
    public function store_materia() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre     = trim(htmlspecialchars($_POST['nombre']     ?? ''));
            $grado_id   = intval($_POST['grado_id']   ?? 0);
            $docente_id = intval($_POST['docente_id'] ?? 0) ?: null;

            if (empty($nombre) || $grado_id < 1) {
                header("Location: ?c=Student&a=materias&err=campos_vacios");
                exit;
            }

            $this->gradeModel->createMateria($nombre, $grado_id, $docente_id);
            header("Location: ?c=Student&a=materias&msg=materia_creada");
        }
    }

    // 11. ELIMINAR MATERIA
    public function delete_materia() {
        if (isset($_GET['id'])) {
            $this->gradeModel->deleteMateria($_GET['id']);
            header("Location: ?c=Student&a=materias&msg=materia_eliminada");
        }
    }

    // HELPER: subir foto
    private function subirFoto($file) {
        $ext             = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxBytes        = 3 * 1024 * 1024;

        if (!in_array($ext, $tiposPermitidos) || $file['size'] > $maxBytes) return null;

        $nombre  = 'foto_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
        $destino = __DIR__ . '/../../public/uploads/' . $nombre;

        return move_uploaded_file($file['tmp_name'], $destino) ? $nombre : null;
    }
}
?>
