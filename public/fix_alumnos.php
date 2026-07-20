<?php
/**
 * Reset de contraseñas de alumnos — usar UNA VEZ
 * URL: http://localhost/sistema_acceso - copia/public/fix_alumnos.php?token=pestalozzi2024
 */
if (!isset($_GET['token']) || $_GET['token'] !== 'pestalozzi2024') {
    http_response_code(403); die("<h2 style='color:red'>403 — Acceso denegado</h2>");
}

require_once '../app/config/db.php';

try {
    $db   = (new Database())->getConnection();
    $hash = password_hash('123456', PASSWORD_DEFAULT);

    // Actualizar TODOS los alumnos con hash correcto de "123456"
    $stmt = $db->prepare("UPDATE alumnos SET contrasena = :h");
    $stmt->execute([':h' => $hash]);
    $afectados = $stmt->rowCount();

    echo "<h2 style='color:green;font-family:sans-serif'>✅ $afectados alumno(s) actualizados</h2>";
    echo "<p style='font-family:sans-serif'>Contraseña: <b>123456</b></p>";

    // Mostrar estado actual
    $rows = $db->query("SELECT id, codigo, correo, estado FROM alumnos ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='8' style='font-family:sans-serif;border-collapse:collapse'>";
    echo "<tr><th>ID</th><th>Código</th><th>Correo</th><th>Estado</th><th>Verify '123456'</th></tr>";
    foreach ($rows as $r) {
        // Re-fetch hash para verificar
        $hrow = $db->prepare("SELECT contrasena FROM alumnos WHERE id=:id");
        $hrow->execute([':id'=>$r['id']]);
        $h = $hrow->fetchColumn();
        $ok = password_verify('123456', $h) ? '✅ OK' : '❌ ERROR';
        echo "<tr><td>{$r['id']}</td><td>{$r['codigo']}</td><td>{$r['correo']}</td><td>{$r['estado']}</td><td>$ok</td></tr>";
    }
    echo "</table>";
    echo "<br><a href='index.php?c=Auth&a=login' style='font-family:sans-serif'>Ir al Login</a>";
    echo "<p style='color:red;font-family:sans-serif'>⚠️ Elimina este archivo después de usarlo.</p>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
?>
