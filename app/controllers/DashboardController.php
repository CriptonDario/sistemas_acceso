<?php
require_once '../app/config/db.php';
require_once '../app/models/Attendance.php';
require_once '../app/models/Employee.php';
require_once '../app/models/Setting.php';

class DashboardController {
    private $db;
    private $asistenciaModel;
    private $personalModel;
    private $configuracionModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: ?c=Auth&a=login");
            exit;
        }

        $database = new Database();
        $this->db = $database->getConnection();
        
        $this->asistenciaModel    = new Attendance($this->db);
        $this->personalModel      = new Employee($this->db);
        $this->configuracionModel = new Setting($this->db);
    }

    public function index() {
        // A. Hora de entrada oficial desde la BD
        $horaEntrada = $this->configuracionModel->get('hora_entrada');
        if (!$horaEntrada) $horaEntrada = '07:30:00';

        // B. Estadísticas generales
        $totalEmpleados   = $this->contarPersonal();
        $asistenciaHoy    = $this->asistenciaModel->countToday();
        
        // C. Tardanzas del día
        $totalTardanzas = $this->asistenciaModel->countLatesToday($horaEntrada);
        
        // D. Registros recientes
        $registrosRecientes = $this->asistenciaModel->getRecentLogs();

        // E. Datos para el gráfico semanal
        $datosSemana = $this->asistenciaModel->getWeeklyStats();
        $etiquetas   = [];
        $valores     = [];
        
        foreach ($datosSemana as $dia) {
            $etiquetas[] = date('d/m', strtotime($dia['fecha']));
            $valores[]   = $dia['total'];
        }
        if (empty($etiquetas)) { 
            $etiquetas[] = date('d/m'); 
            $valores[]   = 0; 
        }

        // Variables de vista (compatibles con dashboard/index.php)
        $totalEmployees   = $totalEmpleados;
        $attendanceToday  = $asistenciaHoy;
        $totalLates       = $totalTardanzas;
        $recentLogs       = $registrosRecientes;
        $labels           = $etiquetas;
        $dataValues       = $valores;
        $horaEntrada      = $horaEntrada; // para el badge "después de HH:MM"

        require_once '../app/views/dashboard/index.php';
    }

    private function contarPersonal() {
        $query = "SELECT COUNT(*) as total FROM personal WHERE estado = 'activo'";
        $stmt  = $this->db->prepare($query);
        $stmt->execute();
        $fila  = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila['total'];
    }
}
?>
