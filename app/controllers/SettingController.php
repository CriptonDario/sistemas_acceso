<?php
require_once '../app/config/db.php';
require_once '../app/models/Setting.php';

class SettingController {
    private $cfg;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: ?c=Dashboard"); exit;
        }
        $database  = new Database();
        $this->cfg = new Setting($database->getConnection());
    }

    public function index() {
        // Horario
        $entry_time = $this->cfg->get('hora_entrada') ?? '07:30';

        // WhatsApp
        $wa_activo    = $this->cfg->get('wa_activo')    ?? '0';
        $wa_proveedor = $this->cfg->get('wa_proveedor') ?? 'callmebot';
        $wa_token     = $this->cfg->get('wa_token')     ?? '';
        $wa_instance  = $this->cfg->get('wa_instance')  ?? '';

        require_once '../app/views/settings/index.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Setting"); exit;
        }

        $seccion = $_POST['seccion'] ?? 'horario';

        if ($seccion === 'horario') {
            $hora = trim($_POST['entry_time'] ?? '');
            if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
                header("Location: ?c=Setting&err=formato_invalido"); exit;
            }
            $this->cfg->set('hora_entrada', $hora);

        } elseif ($seccion === 'whatsapp') {
            // Activado (checkbox — si no viene en POST, es 0)
            $activo    = isset($_POST['wa_activo']) ? '1' : '0';
            $proveedor = $_POST['wa_proveedor'] ?? 'callmebot';
            $token     = trim($_POST['wa_token']    ?? '');
            $instance  = trim($_POST['wa_instance'] ?? '');

            if (!in_array($proveedor, ['callmebot', 'ultramsg'])) {
                $proveedor = 'callmebot';
            }

            $this->cfg->upsert('wa_activo',    $activo);
            $this->cfg->upsert('wa_proveedor', $proveedor);
            $this->cfg->upsert('wa_token',     $token);
            $this->cfg->upsert('wa_instance',  $instance);
        }

        header("Location: ?c=Setting&msg=guardado");
    }
}
?>
