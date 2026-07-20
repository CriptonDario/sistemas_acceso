<?php
require_once '../app/config/db.php';
require_once '../app/models/Department.php';

class DepartmentController {
    private $areaModel;

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
        $db = $database->getConnection();
        $this->areaModel = new Department($db);
    }

    public function index() {
        $departments = $this->areaModel->readAll();
        require_once '../app/views/departments/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['name'];
            $tipo   = isset($_POST['tipo']) ? $_POST['tipo'] : 'academica';
            if (!empty($nombre)) $this->areaModel->create($nombre, $tipo);
            header("Location: ?c=Department&msg=creado");
        }
    }

    public function edit() {
        if (isset($_GET['id'])) {
            $dept = $this->areaModel->getById($_GET['id']);
            if ($dept) require_once '../app/views/departments/edit.php';
            else header("Location: ?c=Department");
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id     = $_POST['id'];
            $nombre = $_POST['name'];
            $tipo   = isset($_POST['tipo']) ? $_POST['tipo'] : null;
            if (!empty($nombre) && !empty($id)) $this->areaModel->update($id, $nombre, $tipo);
            header("Location: ?c=Department&msg=actualizado");
        }
    }

    public function toggle() {
        if (isset($_GET['id']) && isset($_GET['status'])) {
            $id         = $_GET['id'];
            $nuevoEstado = ($_GET['status'] == 'activo') ? 'inactivo' : 'activo';
            $this->areaModel->toggleStatus($id, $nuevoEstado);
            header("Location: ?c=Department&msg=estado_cambiado");
        }
    }

    public function delete() {
        if (isset($_GET['id'])) {
            $this->areaModel->delete($_GET['id']);
            header("Location: ?c=Department&msg=eliminado");
        }
    }
}
?>
