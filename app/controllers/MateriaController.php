<?php
require_once '../app/config/db.php';
require_once '../app/models/Materia.php';

class MateriaController {
    private $materiaModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: ?c=Dashboard"); exit;
        }
        $database = new Database();
        $this->materiaModel = new Materia($database->getConnection());
    }

    public function index() {
        $materias = $this->materiaModel->readAll();
        require_once '../app/views/materias/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ?c=Materia"); exit; }
        $nombre = trim(htmlspecialchars($_POST['nombre'] ?? ''));
        $nivel  = $_POST['nivel'] ?? 'ambos';
        if (empty($nombre)) { header("Location: ?c=Materia&err=vacio"); exit; }
        $this->materiaModel->create($nombre, $nivel);
        header("Location: ?c=Materia&msg=creado");
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ?c=Materia"); exit; }
        $id     = intval($_POST['id']     ?? 0);
        $nombre = trim(htmlspecialchars($_POST['nombre'] ?? ''));
        $nivel  = $_POST['nivel'] ?? 'ambos';
        if (empty($nombre)) { header("Location: ?c=Materia&err=vacio"); exit; }
        $this->materiaModel->update($id, $nombre, $nivel);
        header("Location: ?c=Materia&msg=actualizado");
    }

    public function toggle() {
        if (!isset($_GET['id'], $_GET['status'])) { header("Location: ?c=Materia"); exit; }
        $nuevo = ($_GET['status'] === 'activo') ? 'inactivo' : 'activo';
        $this->materiaModel->toggleStatus(intval($_GET['id']), $nuevo);
        header("Location: ?c=Materia&msg=estado_cambiado");
    }
}
?>
