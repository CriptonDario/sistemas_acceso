<?php
require_once '../app/config/db.php';
require_once '../app/models/Alumno.php';

class AlumnoController {
    private $alumnoModel;
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: ?c=Auth&a=login"); exit;
        }
        if ($_SESSION['role'] !== 'admin') {
            header("Location: ?c=Dashboard"); exit;
        }
        $database = new Database();
        $this->db = $database->getConnection();
        $this->alumnoModel = new Alumno($this->db);
    }

    // LISTAR
    public function index() {
        $busqueda = $_GET['q'] ?? '';
        $alumnos  = $this->alumnoModel->read($busqueda);
        $grados   = $this->db->query("SELECT * FROM grados WHERE estado='activo' ORDER BY nivel,id")->fetchAll(PDO::FETCH_ASSOC);
        require_once '../app/views/alumnos/index.php';
    }

    // GUARDAR NUEVO
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ?c=Alumno"); exit; }

        $datos = [
            'codigo'             => trim(htmlspecialchars($_POST['codigo']             ?? '')),
            'nombres'            => trim(htmlspecialchars($_POST['nombres']            ?? '')),
            'apellidos'          => trim(htmlspecialchars($_POST['apellidos']          ?? '')),
            'dni'                => trim($_POST['dni']                ?? ''),
            'fecha_nacimiento'   => $_POST['fecha_nacimiento']        ?? null,
            'grado_id'           => intval($_POST['grado_id']         ?? 0),
            'correo'             => trim($_POST['correo']             ?? ''),
            'nombre_apoderado'   => trim(htmlspecialchars($_POST['nombre_apoderado']   ?? '')),
            'telefono_apoderado' => trim($_POST['telefono_apoderado'] ?? ''),
            'wa_apikey_apoderado'=> trim($_POST['wa_apikey_apoderado'] ?? ''),
            'foto'               => 'default.png',
        ];

        if (empty($datos['codigo']) || empty($datos['nombres']) || empty($datos['apellidos'])) {
            header("Location: ?c=Alumno&err=campos_vacios"); exit;
        }

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $f = $this->subirFoto($_FILES['foto']);
            if ($f) $datos['foto'] = $f;
        }

        try {
            $this->alumnoModel->create($datos);
            header("Location: ?c=Alumno&msg=guardado");
        } catch (PDOException $e) {
            header("Location: ?c=Alumno&err=" . ($e->getCode() == 23000 ? 'codigo_duplicado' : 'error'));
        }
    }

    // FORMULARIO EDICIÓN
    public function edit() {
        if (!isset($_GET['id'])) { header("Location: ?c=Alumno"); exit; }
        $alumno = $this->alumnoModel->getById(intval($_GET['id']));
        $grados = $this->db->query("SELECT * FROM grados WHERE estado='activo' ORDER BY nivel,id")->fetchAll(PDO::FETCH_ASSOC);
        if ($alumno) require_once '../app/views/alumnos/edit.php';
        else header("Location: ?c=Alumno");
    }

    // GUARDAR EDICIÓN
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ?c=Alumno"); exit; }

        $datos = [
            'id'                 => intval($_POST['id']               ?? 0),
            'codigo'             => trim(htmlspecialchars($_POST['codigo']             ?? '')),
            'nombres'            => trim(htmlspecialchars($_POST['nombres']            ?? '')),
            'apellidos'          => trim(htmlspecialchars($_POST['apellidos']          ?? '')),
            'dni'                => trim($_POST['dni']                ?? ''),
            'fecha_nacimiento'   => $_POST['fecha_nacimiento']        ?? null,
            'grado_id'           => intval($_POST['grado_id']         ?? 0),
            'correo'             => trim($_POST['correo']             ?? ''),
            'nombre_apoderado'   => trim(htmlspecialchars($_POST['nombre_apoderado']   ?? '')),
            'telefono_apoderado' => trim($_POST['telefono_apoderado'] ?? ''),
            'wa_apikey_apoderado'=> trim($_POST['wa_apikey_apoderado'] ?? ''),
        ];

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $f = $this->subirFoto($_FILES['foto']);
            if ($f) $datos['foto'] = $f;
        }

        try {
            $this->alumnoModel->update($datos);
            header("Location: ?c=Alumno&msg=actualizado");
        } catch (PDOException $e) {
            header("Location: ?c=Alumno&err=" . ($e->getCode() == 23000 ? 'codigo_duplicado' : 'error'));
        }
    }

    // ACTIVAR/DESACTIVAR
    public function toggle() {
        if (!isset($_GET['id'], $_GET['status'])) { header("Location: ?c=Alumno"); exit; }
        $nuevo = ($_GET['status'] === 'activo') ? 'inactivo' : 'activo';
        $this->alumnoModel->toggleStatus(intval($_GET['id']), $nuevo);
        header("Location: ?c=Alumno&msg=estado_cambiado");
    }

    // HELPER: subir foto
    private function subirFoto($file) {
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $perm = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $perm) || $file['size'] > 3 * 1024 * 1024) return null;
        $nombre  = 'alu_' . time() . '_' . mt_rand(100,999) . '.' . $ext;
        $destino = __DIR__ . '/../../public/uploads/' . $nombre;
        return move_uploaded_file($file['tmp_name'], $destino) ? $nombre : null;
    }
}
?>
