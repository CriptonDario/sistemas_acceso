<?php
// ==========================================
// CONFIGURACIÓN GLOBAL
// ==========================================
date_default_timezone_set('America/Lima');

// En producción cambiar a 0
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==========================================
// FRONT CONTROLLER — ENRUTAMIENTO
// ==========================================
require_once '../app/config/db.php';

// Sanitizar parámetros de ruta — solo letras
$controller = isset($_GET['c']) ? preg_replace('/[^a-zA-Z]/', '', $_GET['c']) : 'Dashboard';
$action     = isset($_GET['a']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['a']) : 'index';

// Valores por defecto seguros
if (empty($controller)) $controller = 'Dashboard';
if (empty($action))     $action     = 'index';

$controllerName = ucfirst($controller) . 'Controller';
$controllerFile = '../app/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;

    if (class_exists($controllerName)) {
        $obj = new $controllerName();

        if (method_exists($obj, $action)) {
            $obj->$action();
        } else {
            http_response_code(404);
            echo "<div style='font-family:sans-serif;padding:40px'>";
            echo "<h2>404 — Acción no encontrada</h2>";
            echo "<p>La acción <b>" . htmlspecialchars($action) . "</b> no existe.</p>";
            echo "<a href='?c=Dashboard'>Volver al inicio</a>";
            echo "</div>";
        }
    } else {
        http_response_code(500);
        echo "<div style='font-family:sans-serif;padding:40px'><h2>Error interno</h2></div>";
    }
} else {
    header("Location: ?c=Auth&a=login");
    exit;
}
?>
