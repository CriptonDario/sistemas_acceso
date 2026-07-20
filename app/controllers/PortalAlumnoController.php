<?php
require_once '../app/config/db.php';
require_once '../app/models/Alumno.php';
require_once '../app/models/Nota.php';
require_once '../app/models/AsistenciaAlumno.php';

class PortalAlumnoController {
    private $db;
    private $alumnoModel;
    private $notaModel;
    private $asistModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $database = new Database();
        $this->db          = $database->getConnection();
        $this->alumnoModel = new Alumno($this->db);
        $this->notaModel   = new Nota($this->db);
        $this->asistModel  = new AsistenciaAlumno($this->db);
    }

    // LOGIN
    public function login() {
        if (isset($_SESSION['alumno_id'])) {
            header("Location: ?c=PortalAlumno&a=index"); exit;
        }
        header("Location: ?c=Auth&a=login");
    }

    // AUTENTICAR
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Auth&a=login"); exit;
        }
        $correo     = trim($_POST['email_alumno']   ?? '');
        $contrasena = $_POST['password_alumno']     ?? '';

        if (empty($correo) || empty($contrasena)) {
            header("Location: ?c=Auth&a=login&error=" . urlencode("Completa todos los campos.")); exit;
        }

        $alumno = $this->alumnoModel->login($correo, $contrasena);
        if ($alumno) {
            session_regenerate_id(true);
            $_SESSION['alumno_id']     = $alumno['id'];
            $_SESSION['alumno_nombre'] = $alumno['nombres'];
            $_SESSION['alumno_codigo'] = $alumno['codigo'];
            $_SESSION['alumno_foto']   = $alumno['foto'] ?? 'default.png';
            header("Location: ?c=PortalAlumno&a=index");
        } else {
            header("Location: ?c=Auth&a=login&error=" . urlencode("Credenciales incorrectas o alumno inactivo."));
        }
        exit;
    }

    // DASHBOARD DEL ALUMNO
    public function index() {
        if (!isset($_SESSION['alumno_id'])) {
            header("Location: ?c=Auth&a=login"); exit;
        }

        $alumnoId   = $_SESSION['alumno_id'];
        $alumnoNombre = $_SESSION['alumno_nombre'];
        $alumnoCodigo = $_SESSION['alumno_codigo'];
        $alumnoFoto   = $_SESSION['alumno_foto'];

        // Datos completos del alumno
        $alumno = $this->alumnoModel->getById($alumnoId);

        // Notas del año actual
        $anio        = intval($_GET['anio'] ?? date('Y'));
        $resumenNotas = $this->notaModel->getResumenAlumno($alumnoId, $anio);

        // Asistencia del mes
        $inicio = date('Y-m-01');
        $fin    = date('Y-m-d');
        $asistencias = $this->asistModel->getByAlumno($alumnoId, $inicio, $fin);

        require_once '../app/views/portal_alumno/dashboard.php';
    }

    // CAMBIAR CONTRASEÑA
    public function change_password() {
        if (!isset($_SESSION['alumno_id'])) { header("Location: ?c=Auth&a=login"); exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ?c=PortalAlumno&a=index"); exit; }

        $actual   = $_POST['current_password'] ?? '';
        $nueva    = $_POST['new_password']     ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $id       = $_SESSION['alumno_id'];

        if (strlen($nueva) < 6) {
            header("Location: ?c=PortalAlumno&a=index&err=pass_corta"); exit;
        }
        if ($nueva !== $confirm) {
            header("Location: ?c=PortalAlumno&a=index&err=no_coinciden"); exit;
        }

        $alumno = $this->alumnoModel->getById($id);
        if (!$alumno || !password_verify($actual, $alumno['contrasena'])) {
            header("Location: ?c=PortalAlumno&a=index&err=pass_incorrecta"); exit;
        }

        $this->alumnoModel->updatePassword($id, password_hash($nueva, PASSWORD_DEFAULT));
        header("Location: ?c=PortalAlumno&a=index&msg=pass_ok");
        exit;
    }

    // LOGOUT
    public function logout() {
        unset($_SESSION['alumno_id'], $_SESSION['alumno_nombre'],
              $_SESSION['alumno_codigo'], $_SESSION['alumno_foto']);
        header("Location: ?c=Auth&a=login"); exit;
    }

    // CARTILLA DE NOTAS (misma vista que usa el docente)
    public function cartilla() {
        if (!isset($_SESSION['alumno_id'])) {
            header("Location: ?c=Auth&a=login"); exit;
        }

        $alumno_id = $_SESSION['alumno_id'];
        $anio      = intval($_GET['anio'] ?? date('Y'));

        $alumno = $this->db->prepare(
            "SELECT a.*, g.nombre as grado_nombre, g.nivel, g.seccion
             FROM alumnos a LEFT JOIN grados g ON a.grado_id = g.id
             WHERE a.id = :id LIMIT 1"
        );
        $alumno->execute([':id' => $alumno_id]);
        $alumno = $alumno->fetch(PDO::FETCH_ASSOC);

        if (!$alumno) { header("Location: ?c=PortalAlumno&a=index"); exit; }

        $resumenNotas = $this->notaModel->getResumenAlumno($alumno_id, $anio);

        $asistencias = $this->db->prepare(
            "SELECT QUARTER(fecha) as periodo,
                    SUM(CASE WHEN estado='puntual' THEN 1 ELSE 0 END) as puntuales,
                    SUM(CASE WHEN estado='tarde'   THEN 1 ELSE 0 END) as tardanzas,
                    COUNT(*) as total
             FROM asistencia_alumnos
             WHERE alumno_id = :aid AND YEAR(fecha) = :anio
             GROUP BY QUARTER(fecha)
             ORDER BY QUARTER(fecha)"
        );
        $asistencias->execute([':aid' => $alumno_id, ':anio' => $anio]);
        $asistencias = $asistencias->fetchAll(PDO::FETCH_ASSOC);

        // Reutiliza exactamente la misma vista que el docente
        require_once '../app/views/portal_docente/cartilla.php';
    }
}
?>
