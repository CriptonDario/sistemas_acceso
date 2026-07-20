<?php
/**
 * HERRAMIENTA DE RECUPERACIÓN — Solo para uso del administrador del servidor.
 * Accede: http://localhost/tu-proyecto/public/fix_password.php?token=pestalozzi2024
 * ELIMINA este archivo después de usarlo.
 */

// Token de seguridad — cámbialo antes de usar
define('TOKEN_SEGURO', 'pestalozzi2024');

if (!isset($_GET['token']) || $_GET['token'] !== TOKEN_SEGURO) {
    http_response_code(403);
    die("<h2 style='color:red'>403 — Acceso denegado.</h2><p>Se requiere token válido.</p>");
}

require_once '../app/config/db.php';

$nuevaContrasena = 'admin123';

try {
    $database = new Database();
    $db       = $database->getConnection();

    $hash  = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
    $stmt  = $db->prepare("UPDATE usuarios SET contrasena = :pass WHERE usuario = 'admin'");
    $stmt->bindValue(':pass', $hash);

    if ($stmt->execute() && $stmt->rowCount() > 0) {
        echo "<h1 style='color:green'>✅ Contraseña actualizada correctamente</h1>";
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><td>Usuario</td><td><b>admin</b></td></tr>";
        echo "<tr><td>Contraseña</td><td><b>{$nuevaContrasena}</b></td></tr>";
        echo "</table>";
        echo "<br><a href='index.php?c=Auth&a=login'>Ir al Login</a>";
        echo "<br><br><b style='color:red'>⚠️ Elimina este archivo del servidor después de usarlo.</b>";
    } else {
        echo "<h2 style='color:orange'>⚠️ No se encontró el usuario 'admin'.</h2>";
        echo "<p>Verifica que hayas importado <b>bk_basededatos.sql</b> en MySQL.</p>";
    }

} catch (Exception $e) {
    echo "<h2 style='color:red'>Error de conexión</h2><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Revisa los datos en <b>app/config/db.php</b> (host, usuario, contraseña, nombre de BD).</p>";
}
?>
