<?php
require_once '../app/config/db.php';
require_once '../app/models/Incident.php';

class IncidentController {
    private $incidenciaModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: ?c=Auth&a=login");
            exit;
        }

        $database = new Database();
        $this->incidenciaModel = new Incident($database->getConnection());
    }

    public function index() {
        $start  = isset($_GET['start'])  ? $_GET['start']  : date('Y-m-d');
        $end    = isset($_GET['end'])    ? $_GET['end']    : date('Y-m-d');
        $search = isset($_GET['search']) ? $_GET['search'] : '';

        $incidents = $this->incidenciaModel->getWithFilters($start, $end, $search);
        require_once '../app/views/incidents/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titulo      = trim(htmlspecialchars($_POST['title']       ?? ''));
            $descripcion = trim(htmlspecialchars($_POST['description'] ?? ''));
            $severidad   = $_POST['severity'] ?? 'baja';
            $usuarioId   = $_SESSION['user_id'];

            // Validar severidad
            if (!in_array($severidad, ['baja', 'media', 'alta'])) {
                $severidad = 'baja';
            }

            if (empty($titulo)) {
                header("Location: ?c=Incident&err=sin_titulo");
                exit;
            }

            $archivo = null;

            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $file          = $_FILES['attachment'];
                $ext           = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
                $maxBytes      = 5 * 1024 * 1024; // 5 MB

                if (!in_array($ext, $tiposPermitidos)) {
                    header("Location: ?c=Incident&err=tipo_invalido");
                    exit;
                }

                if ($file['size'] > $maxBytes) {
                    header("Location: ?c=Incident&err=archivo_grande");
                    exit;
                }

                $archivo = 'evidencia_' . time() . '.' . $ext;
                $destino = __DIR__ . '/../../public/uploads/' . $archivo;

                if (!move_uploaded_file($file['tmp_name'], $destino)) {
                    header("Location: ?c=Incident&err=upload_fallo");
                    exit;
                }
            }

            if ($this->incidenciaModel->create($titulo, $descripcion, $severidad, $usuarioId, $archivo)) {
                header("Location: ?c=Incident&msg=guardado");
            } else {
                header("Location: ?c=Incident&err=error");
            }
        }
    }
}
?>
