<?php
require_once '../app/config/db.php';
require_once '../app/models/Nota.php';
require_once '../app/models/Alumno.php';
require_once '../app/models/Materia.php';

class NotaController {
    private $notaModel;
    private $alumnoModel;
    private $materiaModel;
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: ?c=Auth&a=login"); exit;
        }
        $database = new Database();
        $this->db           = $database->getConnection();
        $this->notaModel    = new Nota($this->db);
        $this->alumnoModel  = new Alumno($this->db);
        $this->materiaModel = new Materia($this->db);
    }

    // PANEL PRINCIPAL — elegir grado/materia/trimestre
    public function index() {
        $grados   = $this->db->query("SELECT * FROM grados WHERE estado='activo' ORDER BY nivel,id")->fetchAll(PDO::FETCH_ASSOC);
        $materias = $this->materiaModel->readAll(true);

        $grado_id   = intval($_GET['grado_id']   ?? 0);
        $materia_id = intval($_GET['materia_id']  ?? 0);
        $periodo    = $_GET['periodo']            ?? 'T1';
        $anio       = intval($_GET['anio']        ?? date('Y'));

        if (!in_array($periodo, ['T1','T2','T3'])) $periodo = 'T1';

        $listaNotas = [];
        $gradoInfo  = null;
        $materiaInfo = null;

        if ($grado_id && $materia_id) {
            $listaNotas  = $this->notaModel->getByGradoMateria($grado_id, $materia_id, $periodo, $anio);
            $gradoInfo   = $this->db->prepare("SELECT * FROM grados WHERE id=:id");
            $gradoInfo->execute([':id' => $grado_id]);
            $gradoInfo   = $gradoInfo->fetch(PDO::FETCH_ASSOC);
            $materiaInfo = $this->materiaModel->getById($materia_id);
        }

        require_once '../app/views/notas/index.php';
    }

    // GUARDAR NOTAS (desde el formulario del panel)
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ?c=Nota"); exit; }

        $materia_id = intval($_POST['materia_id'] ?? 0);
        $periodo    = $_POST['periodo']           ?? 'T1';
        $anio       = intval($_POST['anio']       ?? date('Y'));
        $grado_id   = intval($_POST['grado_id']   ?? 0);
        $userId     = $_SESSION['user_id'];

        if (!in_array($periodo, ['T1','T2','T3'])) $periodo = 'T1';

        $notas = $_POST['notas'] ?? [];  // array [alumno_id => nota]
        $obs   = $_POST['obs']   ?? [];  // array [alumno_id => observacion]

        foreach ($notas as $alumno_id => $nota) {
            $nota = trim($nota);
            if ($nota === '' || !is_numeric($nota)) continue;
            $nota = max(0, min(20, floatval($nota)));
            $this->notaModel->guardar(
                intval($alumno_id), $materia_id, $periodo, $anio,
                $nota, trim($obs[$alumno_id] ?? ''), $userId
            );
        }

        header("Location: ?c=Nota&grado_id=$grado_id&materia_id=$materia_id&periodo=$periodo&anio=$anio&msg=guardado");
    }
}
?>
