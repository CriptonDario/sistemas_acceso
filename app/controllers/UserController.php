<?php
require_once '../app/config/db.php';
require_once '../app/models/User.php';

class UserController {
    private $usuarioModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: ?c=Dashboard");
            exit;
        }
        $database = new Database();
        $this->usuarioModel = new User($database->getConnection());
    }

    public function index() {
        $users = $this->usuarioModel->readAll();
        require_once '../app/views/users/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=User");
            exit;
        }

        $usuario    = trim(htmlspecialchars($_POST['username'] ?? ''));
        $contrasena = $_POST['password'] ?? '';
        $rol        = $_POST['role']     ?? 'guardia';

        // Validar rol
        if (!in_array($rol, ['admin', 'guardia'])) $rol = 'guardia';

        if (empty($usuario) || empty($contrasena)) {
            header("Location: ?c=User&err=campos_vacios");
            exit;
        }

        if ($this->usuarioModel->create($usuario, $contrasena, $rol)) {
            header("Location: ?c=User&msg=creado");
        } else {
            header("Location: ?c=User&err=existe");
        }
    }

    public function edit() {
        if (isset($_GET['id'])) {
            $id   = intval($_GET['id']);
            $user = $this->usuarioModel->getById($id);
            if ($user) {
                require_once '../app/views/users/edit.php';
            } else {
                header("Location: ?c=User");
            }
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=User");
            exit;
        }

        $id         = intval($_POST['id'] ?? 0);
        $usuario    = trim(htmlspecialchars($_POST['username'] ?? ''));
        $rol        = $_POST['role'] ?? 'guardia';
        $contrasena = !empty($_POST['password']) ? $_POST['password'] : null;

        if (!in_array($rol, ['admin', 'guardia'])) $rol = 'guardia';

        if (empty($usuario)) {
            header("Location: ?c=User&err=campos_vacios");
            exit;
        }

        $this->usuarioModel->update($id, $usuario, $rol, $contrasena);
        header("Location: ?c=User&msg=actualizado");
    }

    public function toggle() {
        if (!isset($_GET['id'], $_GET['status'])) {
            header("Location: ?c=User");
            exit;
        }

        $id = intval($_GET['id']);

        if ($id === intval($_SESSION['user_id'])) {
            header("Location: ?c=User&err=self_disable");
            exit;
        }

        $estadoActual = $_GET['status'];
        $nuevoEstado  = ($estadoActual === 'activo') ? 'inactivo' : 'activo';

        $this->usuarioModel->toggleStatus($id, $nuevoEstado);
        header("Location: ?c=User&msg=estado_cambiado");
    }

    public function change_own_password() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=User");
            exit;
        }

        $idActual     = intval($_SESSION['user_id']);
        $claveNueva   = $_POST['new_password']     ?? '';
        $claveConfirm = $_POST['confirm_password'] ?? '';

        if (empty($claveNueva) || strlen($claveNueva) < 6) {
            header("Location: ?c=User&err=pass_corta");
            exit;
        }

        if ($claveNueva !== $claveConfirm) {
            header("Location: ?c=User&err=pass_mismatch");
            exit;
        }

        $this->usuarioModel->updatePassword($idActual, password_hash($claveNueva, PASSWORD_DEFAULT));
        header("Location: ?c=User&msg=pass_ok");
    }
}
?>
