<?php
require_once '../app/config/db.php';
require_once '../app/models/Attendance.php';
require_once '../app/models/Employee.php';
require_once '../app/models/Setting.php';
require_once '../app/models/AsistenciaAlumno.php';
require_once '../app/models/Student.php';

class ReportController {
    private $asistenciaModel;
    private $personalModel;
    private $configuracionModel;
    private $asistenciaAlumnoModel;
    private $db;

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
        $this->db = $database->getConnection();
        $this->asistenciaModel    = new Attendance($this->db);
        $this->personalModel      = new Employee($this->db);
        $this->configuracionModel = new Setting($this->db);
        $this->asistenciaAlumnoModel = new AsistenciaAlumno($this->db);
    }

    public function index() { require_once '../app/views/reports/index.php'; }

    public function history() {
        $employees  = $this->personalModel->read();
        $start_date = isset($_GET['start'])       ? $_GET['start']       : date('Y-m-01');
        $end_date   = isset($_GET['end'])         ? $_GET['end']         : date('Y-m-d');
        $employee_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : '';
        
        $logs = $this->asistenciaModel->getLogsWithFilters($employee_id, $start_date, $end_date);
        
        $horaEntradaOficial = $this->configuracionModel->get('hora_entrada');
        if (!$horaEntradaOficial) $horaEntradaOficial = '07:30:00';

        require_once '../app/views/reports/history.php';
    }

    public function export() {
        if (isset($_POST['start_date'])) {
            $inicio = $_POST['start_date'];
            $fin    = $_POST['end_date'];

            // Validar que sean fechas reales
            $dInicio = DateTime::createFromFormat('Y-m-d', $inicio);
            $dFin    = DateTime::createFromFormat('Y-m-d', $fin);

            if (!$dInicio || !$dFin || $dInicio > $dFin) {
                header("Location: ?c=Report&err=fechas_invalidas");
                exit;
            }

            $horaLimite = $this->configuracionModel->get('hora_entrada') ?? '07:30:00';
            $datos      = $this->asistenciaModel->getHistoryByDate($inicio, $fin);

            $nombreArchivo = "Reporte_Pestalozzi_" . date('Ymd') . ".csv";
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $nombreArchivo);
            header('Cache-Control: no-cache, must-revalidate');

            $output = fopen('php://output', 'w');
            // BOM para Excel con tildes
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, ['Código', 'Personal', 'Área', 'Fecha', 'Entrada', 'Salida', 'Horas', 'Estado', 'Puntualidad']);

            foreach ($datos as $fila) {
                $horas   = "--";
                $puntual = "Puntual";

                if (!empty($fila['hora_salida'])) {
                    $diff  = (new DateTime($fila['hora_entrada']))->diff(new DateTime($fila['hora_salida']));
                    $horas = $diff->format('%H:%I:%S');
                }

                if ($fila['hora_entrada'] > $horaLimite) $puntual = "TARDE";

                $est = (!empty($fila['hora_salida'])) ? 'Completado' : 'En Turno';
                fputcsv($output, [
                    $fila['codigo'],
                    $fila['nombres'] . ' ' . $fila['apellidos'],
                    $fila['area'],
                    $fila['fecha'],
                    $fila['hora_entrada'],
                    $fila['hora_salida'] ?? '--',
                    $horas,
                    $est,
                    $puntual
                ]);
            }
            fclose($output);
            exit;
        }
    }

    // Reporte de asistencia de alumnos (pantalla + filtrado)
    public function studentHistory() {
        $studentModel = new Student($this->db);
        $students     = $studentModel->read();
        $student_id   = isset($_GET['student_id']) ? $_GET['student_id'] : '';
        $start_date   = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
        $end_date     = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');

        $logs = [];
        if (!empty($student_id)) {
            $logs = $this->asistenciaAlumnoModel->getByAlumno($student_id, $start_date, $end_date);
        }

        require_once '../app/views/reports/student_history.php';
    }

    // Exportar CSV de asistencia de un alumno
    public function exportStudent() {
        if (isset($_POST['student_id'])) {
            $studentId = $_POST['student_id'];
            $inicio = $_POST['start_date'];
            $fin    = $_POST['end_date'];

            $dInicio = DateTime::createFromFormat('Y-m-d', $inicio);
            $dFin    = DateTime::createFromFormat('Y-m-d', $fin);

            if (!$dInicio || !$dFin || $dInicio > $dFin) {
                header("Location: ?c=Report&err=fechas_invalidas");
                exit;
            }

            $datos = $this->asistenciaAlumnoModel->getByAlumno($studentId, $inicio, $fin);
            $nombreArchivo = "Reporte_Asistencia_Alumno_" . date('Ymd') . ".csv";
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $nombreArchivo);
            header('Cache-Control: no-cache, must-revalidate');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['Alumno', 'Grado', 'Fecha', 'Entrada', 'Salida', 'Estado']);

            foreach ($datos as $fila) {
                fputcsv($output, [
                    $fila['alumno_id'],
                    $fila['grado'] ?? '--',
                    $fila['fecha'],
                    $fila['hora_entrada'],
                    $fila['hora_salida'] ?? '--',
                    $fila['estado'] ?? '--'
                ]);
            }
            fclose($output);
            exit;
        }
    }
}
?>
