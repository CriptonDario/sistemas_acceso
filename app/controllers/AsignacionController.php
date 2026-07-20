<?php
require_once '../app/config/db.php';
require_once '../app/models/AsignacionDocente.php';

class AsignacionController {
    private $asignModel;
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: ?c=Dashboard"); exit;
        }
        $database = new Database();
        $this->db = $database->getConnection();
        $this->asignModel = new AsignacionDocente($this->db);
    }

    public function index() {
        $anio         = intval($_GET['anio'] ?? date('Y'));
        $asignaciones = $this->asignModel->getAll($anio);

        // Solo docentes (tipo_personal = docente)
        $docentes = $this->db->query(
            "SELECT id, nombres, apellidos, codigo, cargo FROM personal
             WHERE tipo_personal = 'docente' AND estado = 'activo'
             ORDER BY apellidos, nombres"
        )->fetchAll(PDO::FETCH_ASSOC);

        $materias = $this->db->query(
            "SELECT * FROM materias WHERE estado = 'activo' ORDER BY nombre"
        )->fetchAll(PDO::FETCH_ASSOC);

        $grados = $this->db->query(
            "SELECT * FROM grados WHERE estado = 'activo' ORDER BY nivel, id"
        )->fetchAll(PDO::FETCH_ASSOC);

        require_once '../app/views/asignaciones/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Asignacion"); exit;
        }

        $personal_id = intval($_POST['personal_id'] ?? 0);
        $grado_id    = intval($_POST['grado_id']    ?? 0);
        $anio        = intval($_POST['anio']        ?? date('Y'));
        $tipo        = $_POST['tipo_asignacion']    ?? 'curso';

        if (!$personal_id || !$grado_id) {
            header("Location: ?c=Asignacion&err=campos_vacios&anio=$anio"); exit;
        }

        // Para curso necesita materia_id
        $materia_id = ($tipo === 'curso') ? intval($_POST['materia_id'] ?? 0) : null;
        if ($tipo === 'curso' && !$materia_id) {
            header("Location: ?c=Asignacion&err=sin_materia&anio=$anio"); exit;
        }

        // Verificar que el nivel del grado corresponde al tipo
        $gradoInfo = $this->db->prepare("SELECT nivel FROM grados WHERE id=:id");
        $gradoInfo->execute([':id'=>$grado_id]);
        $nivel = $gradoInfo->fetchColumn();

        // Primaria/inicial → forzar tipo aula
        if ($nivel === 'primaria') $tipo = 'aula';
        // Secundaria → forzar tipo curso
        if ($nivel === 'secundaria') $tipo = 'curso';

        try {
            $this->asignModel->create($personal_id, $materia_id, $grado_id, $anio, $tipo);
            header("Location: ?c=Asignacion&msg=creado&anio=$anio");
        } catch (PDOException $e) {
            $code = $e->getCode() == 23000 ? 'duplicado' : 'error';
            header("Location: ?c=Asignacion&err=$code&anio=$anio");
        }
    }

    public function delete() {
        if (!isset($_GET['id'])) { header("Location: ?c=Asignacion"); exit; }
        $this->asignModel->delete(intval($_GET['id']));
        $anio = $_GET['anio'] ?? date('Y');
        header("Location: ?c=Asignacion&msg=eliminado&anio=$anio");
    }
}
?>
