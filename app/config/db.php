<?php
class Database {
    private $host     = "localhost";
    private $db_name  = "pestalozzi_db";
    private $username = "root";
    private $password = "";
    public  $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );

            // Zona horaria de Perú — valor fijo, sin interpolación de variables
            $this->conn->exec("SET time_zone = '-05:00'");

        } catch (PDOException $e) {
            error_log("Error de conexión BD: " . $e->getMessage());
            http_response_code(500);
            die("
            <div style='font-family:sans-serif;text-align:center;padding:60px;background:#fff3cd;border:2px solid #ffc107;border-radius:10px;max-width:500px;margin:100px auto'>
                <h2 style='color:#856404'>⚠️ Sin conexión a la base de datos</h2>
                <p>Verifica que MySQL esté activo y que la BD <b>pestalozzi_db</b> exista.</p>
                <p><small>Abre <b>public/diagnostico.php</b> para más detalles.</small></p>
            </div>");
        }
        return $this->conn;
    }
}
?>
