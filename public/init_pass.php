<?php
// public/init_pass.php
// Inicializa contraseña '123456' para personal sin contraseña asignada.

require_once '../app/config/db.php';

$database = new Database();
$db = $database->getConnection();

$passHash = password_hash("123456", PASSWORD_DEFAULT);

// Tabla nueva: personal, columna: contrasena
$query = "UPDATE personal SET contrasena = :p WHERE contrasena IS NULL";
$stmt  = $db->prepare($query);
$stmt->bindParam(':p', $passHash);

if ($stmt->execute()) {
    echo "<h1>✅ ¡Éxito!</h1>";
    echo "<p>El personal sin contraseña ahora tiene la clave: <b>123456</b></p>";
    echo "<a href='index.php?c=Auth&a=login'>Ir al Login</a>";
} else {
    echo "Error al actualizar.";
}
?>
