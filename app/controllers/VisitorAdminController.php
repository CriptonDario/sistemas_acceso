<?php
require_once '../app/config/db.php';
require_once '../app/models/Visitor.php';

class VisitorAdminController {
    private $visitanteModel;
    private $db;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: ?c=Auth&a=login");
            exit;
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->visitanteModel = new Visitor($this->db);
    }

    public function index() {
        $start   = isset($_GET['start'])  ? $_GET['start']  : date('Y-m-01');
        $end     = isset($_GET['end'])    ? $_GET['end']    : date('Y-m-d');
        $search  = isset($_GET['search']) ? $_GET['search'] : '';

        $visitors = $this->visitanteModel->getHistoryWithFilters($start, $end, $search);
        
        require_once '../app/views/visitors/index.php';
    }
}
?>
