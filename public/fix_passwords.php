<?php
// public/fix_passwords.php
// Resetea la contraseña de todo el personal a '123456'
// URL: http://localhost/tu-proyecto/public/fix_passwords.php

require_once '../app/config/db.php';

try {
    echo "<h2>🔧 Reparando contraseñas del personal...</h2>";

    $database = new Database();
    $db = $database->getConnection();

    $passHash = password_hash("123456", PASSWORD_DEFAULT);

    // Tabla nueva: personal, columna: contrasena
    $query = "UPDATE personal SET contrasena = :p WHERE contrasena IS NULL OR contrasena = ''";
    $stmt  = $db->prepare($query);
    $stmt->bindParam(':p', $passHash);

    if ($stmt->execute()) {
        $count = $stmt->rowCount();
        echo "<div style='color:green;font-weight:bold'>";
        echo "✅ Se actualizaron $count registros de personal.<br>";
        echo "Contraseña del portal: <b>123456</b>";
        echo "</div>";
        echo "<br><a href='index.php?c=Auth&a=login'>Ir al Login</a>";
    } else {
        echo "<div style='color:red'>❌ Error al actualizar.</div>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
