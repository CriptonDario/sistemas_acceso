<?php
require_once '../app/config/db.php';
require_once '../app/models/Visitor.php';

class VisitorController {

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Attendance");
            exit;
        }

        $database       = new Database();
        $db             = $database->getConnection();
        $visitanteModel = new Visitor($db);

        $message = "";
        $error   = "";

        $accion = $_POST['action_type'] ?? '';
        $dni    = trim($_POST['dni']    ?? '');

        if (empty($dni)) {
            $error = "❌ Ingresa un DNI válido.";
            header("Location: ?c=Attendance&err=" . urlencode($error));
            exit;
        }

        try {
            if ($accion === 'entrada') {
                $nombre      = trim(htmlspecialchars($_POST['full_name'] ?? ''));
                $institucion = trim(htmlspecialchars($_POST['company']   ?? ''));
                $motivo      = trim(htmlspecialchars($_POST['reason']    ?? ''));

                if (empty($nombre)) {
                    $error = "❌ El nombre del visitante es obligatorio.";
                } elseif ($visitanteModel->registerEntry($dni, $nombre, $institucion, $motivo)) {
                    $message = "✅ Entrada registrada para $nombre. Puede pasar.";
                } else {
                    $error = "❌ Error al registrar la entrada.";
                }

            } elseif ($accion === 'salida') {
                $resultado = $visitanteModel->registerExit($dni);
                if ($resultado) {
                    $message = "👋 " . $resultado;
                } else {
                    $error = "❌ No hay entrada activa para el DNI: " . htmlspecialchars($dni);
                }
            } else {
                $error = "❌ Acción no reconocida.";
            }

        } catch (PDOException $e) {
            error_log("VisitorController::register() — " . $e->getMessage());
            $error = "❌ Error interno. Intenta de nuevo.";
        }

        // Redirigir al kiosco con los mensajes en la URL
        header("Location: ?c=Attendance&msg=" . urlencode($message) . "&err=" . urlencode($error));
        exit;
    }
}
?>
