<?php
require_once '../app/config/db.php';

class AuthController {

    public function login() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Redirigir según sesión activa
        if (isset($_SESSION['user_id'])) {
            header("Location: ?c=Dashboard");
            exit;
        }
        if (isset($_SESSION['portal_role']) && $_SESSION['portal_role'] === 'empleado') {
            header("Location: ?c=Portal&a=index");
            exit;
        }
        if (isset($_SESSION['student_role']) && $_SESSION['student_role'] === 'alumno') {
            header("Location: ?c=StudentPortal&a=index");
            exit;
        }
        require_once '../app/views/auth/login.php';
    }

    public function authenticate() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Auth&a=login");
            exit;
        }

        // El campo email cubre tanto usuarios del sistema (username) como correo de personal/alumnos
        $input      = isset($_POST['email'])    ? trim($_POST['email'])    : '';
        $input      = $input ?: (isset($_POST['username']) ? trim(htmlspecialchars($_POST['username'])) : '');
        $contrasena = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($input) || empty($contrasena)) {
            header("Location: ?c=Auth&a=login&error=" . urlencode("Completa todos los campos."));
            exit;
        }

        if (strlen($input) > 100 || strlen($contrasena) > 100) {
            header("Location: ?c=Auth&a=login&error=" . urlencode("Datos de acceso inválidos."));
            exit;
        }

        try {
            $database = new Database();
            $db       = $database->getConnection();

            // 1. Intentar como usuario del sistema (admin/guardia)
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE usuario = :u AND estado = 'activo' LIMIT 1");
            $stmt->bindValue(':u', $input);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user && password_verify($contrasena, $user['contrasena'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role']    = $user['rol'];
                $_SESSION['usuario'] = $user['usuario'];
                header("Location: ?c=Dashboard");
                exit;
            }

            // 2. Intentar como personal (empleado)
            $stmt2 = $db->prepare("SELECT * FROM personal WHERE correo = :e AND estado = 'activo' LIMIT 1");
            $stmt2->bindValue(':e', $input);
            $stmt2->execute();
            $miembro = $stmt2->fetch();

            if ($miembro && !empty($miembro['contrasena']) && password_verify($contrasena, $miembro['contrasena'])) {
                session_regenerate_id(true);
                $_SESSION['portal_id']   = $miembro['id'];
                $_SESSION['portal_name'] = $miembro['nombres'];
                $_SESSION['portal_code'] = $miembro['codigo'];
                $_SESSION['portal_role'] = 'empleado';
                header("Location: ?c=Portal&a=index");
                exit;
            }

            // 3. Intentar como alumno
            $stmt3 = $db->prepare("SELECT * FROM alumnos WHERE correo = :e AND estado = 'activo' LIMIT 1");
            $stmt3->bindValue(':e', $input);
            $stmt3->execute();
            $alumno = $stmt3->fetch();

            if ($alumno && !empty($alumno['contrasena']) && password_verify($contrasena, $alumno['contrasena'])) {
                session_regenerate_id(true);
                $_SESSION['student_id']   = $alumno['id'];
                $_SESSION['student_name'] = $alumno['nombres'];
                $_SESSION['student_code'] = $alumno['codigo'];
                $_SESSION['student_role'] = 'alumno';
                header("Location: ?c=StudentPortal&a=index");
                exit;
            }

            // Ningún match
            header("Location: ?c=Auth&a=login&error=" . urlencode("Credenciales incorrectas o cuenta inactiva."));
            exit;

        } catch (PDOException $e) {
            error_log("AuthController::authenticate() — " . $e->getMessage());
            header("Location: ?c=Auth&a=login&error=" . urlencode("Error interno. Inténtalo más tarde."));
            exit;
        }
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        session_destroy();
        header("Location: ?c=Auth&a=login");
        exit;
    }
}
?>
