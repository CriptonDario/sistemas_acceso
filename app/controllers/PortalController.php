<?php
require_once '../app/config/db.php';
require_once '../app/models/Employee.php';
require_once '../app/models/Attendance.php';

class PortalController {
    private $db;
    private $personalModel;
    private $asistenciaModel;

    public function __construct() {
        // Sesión SIEMPRE primero
        if (session_status() === PHP_SESSION_NONE) session_start();

        $database = new Database();
        $this->db = $database->getConnection();
        $this->personalModel   = new Employee($this->db);
        $this->asistenciaModel = new Attendance($this->db);
    }

    // 1. LOGIN — redirige al login unificado
    public function login() {
        if (isset($_SESSION['portal_role']) && $_SESSION['portal_role'] === 'empleado') {
            header("Location: ?c=Portal&a=index");
            exit;
        }
        header("Location: ?c=Auth&a=login");
        exit;
    }

    // 2. AUTENTICAR EMPLEADO (desde login unificado)
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

        $miembro = $this->personalModel->login($correo, $contrasena);

        if ($miembro) {
            session_regenerate_id(true);
            $_SESSION['portal_id']   = $miembro['id'];
            $_SESSION['portal_name'] = $miembro['nombres'];
            $_SESSION['portal_code'] = $miembro['codigo'];
            $_SESSION['portal_role'] = 'empleado';
            header("Location: ?c=Portal&a=index");
        } else {
            header("Location: ?c=Auth&a=login&error=" . urlencode("Credenciales incorrectas o cuenta inactiva."));
        }
        exit;
    }

    // 3. DASHBOARD DEL PERSONAL
    public function index() {
        if (!isset($_SESSION['portal_role']) || $_SESSION['portal_role'] !== 'empleado') {
            header("Location: ?c=Auth&a=login");
            exit;
        }

        $empId   = $_SESSION['portal_id'];
        $empName = $_SESSION['portal_name'];
        $empCode = $_SESSION['portal_code'];

        // Obtener foto del personal
        $miembro  = $this->personalModel->getById($empId);
        $fotoPortal = 'https://ui-avatars.com/api/?name=' . urlencode($empName) . '&background=0d6efd&color=fff&size=120';
        if ($miembro && !empty($miembro['foto']) && $miembro['foto'] !== 'default.png') {
            $fotoPortal = 'uploads/' . $miembro['foto'];
        }

        $inicio = date('Y-m-01');
        $fin    = date('Y-m-d');
        $myLogs = $this->asistenciaModel->getLogsWithFilters($empId, $inicio, $fin);

        require_once '../app/views/portal/dashboard.php';
    }

    // 4. CAMBIAR CONTRASEÑA (Portal del empleado)
    public function change_password() {
        if (!isset($_SESSION['portal_role']) || $_SESSION['portal_role'] !== 'empleado') {
            header("Location: ?c=Auth&a=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Portal&a=index");
            exit;
        }

        $claveActual  = $_POST['current_password'] ?? '';
        $claveNueva   = $_POST['new_password']     ?? '';
        $claveConfirm = $_POST['confirm_password'] ?? '';
        $empId        = $_SESSION['portal_id'];

        if (strlen($claveNueva) < 6) {
            header("Location: ?c=Portal&a=index&err=pass_corta");
            exit;
        }

        if ($claveNueva !== $claveConfirm) {
            header("Location: ?c=Portal&a=index&err=no_coinciden");
            exit;
        }

        $miembro = $this->personalModel->getById($empId);

        if (!$miembro || !password_verify($claveActual, $miembro['contrasena'])) {
            header("Location: ?c=Portal&a=index&err=pass_incorrecta");
            exit;
        }

        $this->personalModel->updatePassword($empId, password_hash($claveNueva, PASSWORD_DEFAULT));
        header("Location: ?c=Portal&a=index&msg=pass_actualizada");
        exit;
    }

    // 5. CERRAR SESIÓN DEL PORTAL
    public function logout() {
        unset(
            $_SESSION['portal_id'],
            $_SESSION['portal_name'],
            $_SESSION['portal_code'],
            $_SESSION['portal_role']
        );
        header("Location: ?c=Auth&a=login");
        exit;
    }
}
?>
