<?php
require_once '../app/config/db.php';
require_once '../app/models/Student.php';
require_once '../app/models/Grade.php';

class StudentPortalController {
    private $db;
    private $studentModel;
    private $gradeModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $database = new Database();
        $this->db = $database->getConnection();
        $this->studentModel = new Student($this->db);
        $this->gradeModel   = new Grade($this->db);
    }

    // 1. AUTENTICAR ALUMNO (desde login unificado)
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Auth&a=login");
            exit;
        }

        $correo     = trim($_POST['email']    ?? '');
        $contrasena = $_POST['password']      ?? '';

        if (empty($correo) || empty($contrasena)) {
            header("Location: ?c=Auth&a=login&error=" . urlencode("Completa todos los campos."));
            exit;
        }

        $alumno = $this->studentModel->login($correo, $contrasena);

        if ($alumno) {
            session_regenerate_id(true);
            $_SESSION['student_id']   = $alumno['id'];
            $_SESSION['student_name'] = $alumno['nombres'];
            $_SESSION['student_code'] = $alumno['codigo'];
            $_SESSION['student_role'] = 'alumno';
            header("Location: ?c=StudentPortal&a=index");
        } else {
            header("Location: ?c=Auth&a=login&error=" . urlencode("Credenciales incorrectas o cuenta inactiva."));
        }
        exit;
    }

    // 2. DASHBOARD DEL ALUMNO — notas en tiempo real
    public function index() {
        if (!isset($_SESSION['student_role']) || $_SESSION['student_role'] !== 'alumno') {
            header("Location: ?c=Auth&a=login");
            exit;
        }

        $studentId   = $_SESSION['student_id'];
        $studentName = $_SESSION['student_name'];

        $alumno  = $this->studentModel->getById($studentId);

        // Foto por defecto si no tiene
        $foto = 'https://ui-avatars.com/api/?name=' . urlencode($studentName) . '&background=198754&color=fff&size=120';
        if ($alumno && !empty($alumno['foto']) && $alumno['foto'] !== 'default.png') {
            $foto = 'uploads/' . $alumno['foto'];
        }

        // Notas agrupadas por materia con promedios
        $notasResumen = $this->gradeModel->getSummaryByStudent($studentId);

        require_once '../app/views/student_portal/dashboard.php';
    }

    // 3. API — devuelve notas en JSON (para actualización en tiempo real via JS)
    public function api_notas() {
        if (!isset($_SESSION['student_role']) || $_SESSION['student_role'] !== 'alumno') {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }

        header('Content-Type: application/json');
        $notas = $this->gradeModel->getSummaryByStudent($_SESSION['student_id']);
        echo json_encode($notas);
        exit;
    }

    // 4. CAMBIAR CONTRASEÑA
    public function change_password() {
        if (!isset($_SESSION['student_role']) || $_SESSION['student_role'] !== 'alumno') {
            header("Location: ?c=Auth&a=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=StudentPortal&a=index");
            exit;
        }

        $claveActual  = $_POST['current_password'] ?? '';
        $claveNueva   = $_POST['new_password']     ?? '';
        $claveConfirm = $_POST['confirm_password'] ?? '';
        $studentId    = $_SESSION['student_id'];

        if (strlen($claveNueva) < 6) {
            header("Location: ?c=StudentPortal&a=index&err=pass_corta");
            exit;
        }

        if ($claveNueva !== $claveConfirm) {
            header("Location: ?c=StudentPortal&a=index&err=no_coinciden");
            exit;
        }

        $alumno = $this->studentModel->getById($studentId);

        if (!$alumno || !password_verify($claveActual, $alumno['contrasena'])) {
            header("Location: ?c=StudentPortal&a=index&err=pass_incorrecta");
            exit;
        }

        $this->studentModel->updatePassword($studentId, password_hash($claveNueva, PASSWORD_DEFAULT));
        header("Location: ?c=StudentPortal&a=index&msg=pass_actualizada");
        exit;
    }

    // 5. CERRAR SESIÓN DEL PORTAL ALUMNO
    public function logout() {
        unset(
            $_SESSION['student_id'],
            $_SESSION['student_name'],
            $_SESSION['student_code'],
            $_SESSION['student_role']
        );
        header("Location: ?c=Auth&a=login");
        exit;
    }
}
?>
