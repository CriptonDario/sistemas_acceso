<?php
/**
 * DIAGNÓSTICO DEL SISTEMA — Colegio Pestalozzi
 * Abre: http://localhost/tu-proyecto/public/diagnostico.php
 * ELIMINA este archivo después de resolver el problema.
 */

echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'>
<title>Diagnóstico</title>
<style>
  body{font-family:monospace;padding:30px;background:#1a1a2e;color:#eee}
  h2{color:#f0c040} .ok{color:#4ade80} .err{color:#f87171} .warn{color:#fb923c}
  table{border-collapse:collapse;width:100%;margin:10px 0}
  td,th{border:1px solid #444;padding:8px 12px;text-align:left}
  th{background:#333} tr:nth-child(even){background:#222}
  pre{background:#111;padding:15px;border-radius:8px;overflow:auto}
</style></head><body>";

echo "<h2>🔍 Diagnóstico — Colegio Pestalozzi</h2>";

// ─── 1. Versión de PHP ───────────────────────────────────────────────────────
echo "<h3>1. Entorno PHP</h3><table>";
echo "<tr><th>Parámetro</th><th>Valor</th></tr>";
echo "<tr><td>Versión PHP</td><td class='ok'>" . phpversion() . "</td></tr>";
echo "<tr><td>Extensión PDO</td><td class='" . (extension_loaded('pdo') ? 'ok' : 'err') . "'>" . (extension_loaded('pdo') ? '✅ Activa' : '❌ FALTA') . "</td></tr>";
echo "<tr><td>PDO MySQL</td><td class='" . (extension_loaded('pdo_mysql') ? 'ok' : 'err') . "'>" . (extension_loaded('pdo_mysql') ? '✅ Activa' : '❌ FALTA') . "</td></tr>";
echo "<tr><td>Zona horaria PHP</td><td>" . date_default_timezone_get() . "</td></tr>";
echo "<tr><td>Fecha/hora actual</td><td>" . date('Y-m-d H:i:s') . "</td></tr>";
echo "</table>";

// ─── 2. Conexión a BD ────────────────────────────────────────────────────────
echo "<h3>2. Conexión a Base de Datos</h3>";

$host    = 'localhost';
$db_name = 'pestalozzi_db';
$user    = 'root';
$pass    = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p class='ok'>✅ Conexión exitosa a <b>$db_name</b></p>";

    // ─── 3. Tablas existentes ────────────────────────────────────────────────
    echo "<h3>3. Tablas en la base de datos</h3>";
    $tablas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    $requeridas = ['usuarios','personal','areas','registros_asistencia','incidencias','configuracion',
                   'visitantes','registros_visitas','grados','materias','alumnos',
                   'asistencia_alumnos','notas'];
    
    echo "<table><tr><th>Tabla</th><th>Estado</th></tr>";
    foreach ($requeridas as $t) {
        $existe = in_array($t, $tablas);
        echo "<tr><td>$t</td><td class='" . ($existe ? 'ok' : 'err') . "'>" . ($existe ? '✅ Existe' : '❌ NO EXISTE — Importa el SQL') . "</td></tr>";
    }
    echo "</table>";

    // ─── 4. Usuarios en la BD ────────────────────────────────────────────────
    if (in_array('usuarios', $tablas)) {
        echo "<h3>4. Usuarios registrados</h3>";
        $usuarios = $pdo->query("SELECT id, usuario, rol, estado FROM usuarios")->fetchAll();
        
        if (count($usuarios) > 0) {
            echo "<table><tr><th>ID</th><th>Usuario</th><th>Rol</th><th>Estado</th><th>Hash válido</th></tr>";
            foreach ($usuarios as $u) {
                // Verificar que el hash de contrasena sea bcrypt
                $hashStmt = $pdo->prepare("SELECT contrasena FROM usuarios WHERE id = :id");
                $hashStmt->execute([':id' => $u['id']]);
                $row  = $hashStmt->fetch();
                $hash = $row['contrasena'] ?? '';
                $esHash = (strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0);
                $hashInfo = $esHash ? "<span class='ok'>✅ Bcrypt</span>" : "<span class='err'>❌ NO es hash bcrypt: " . substr($hash,0,20) . "</span>";
                
                echo "<tr><td>{$u['id']}</td><td><b>{$u['usuario']}</b></td><td>{$u['rol']}</td><td>{$u['estado']}</td><td>$hashInfo</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='err'>❌ No hay usuarios en la tabla. Importa el SQL.</p>";
        }
    }

    // ─── 5. Configuración ───────────────────────────────────────────────────
    if (in_array('configuracion', $tablas)) {
        echo "<h3>5. Configuración</h3>";
        $config = $pdo->query("SELECT * FROM configuracion")->fetchAll();
        echo "<table><tr><th>Clave</th><th>Valor</th></tr>";
        foreach ($config as $c) {
            echo "<tr><td>{$c['clave']}</td><td>{$c['valor']}</td></tr>";
        }
        echo "</table>";
    }

    // ─── 6. Test de password_verify ─────────────────────────────────────────
    echo "<h3>6. Test de contraseña 'admin123'</h3>";
    if (in_array('usuarios', $tablas)) {
        $stmt = $pdo->prepare("SELECT contrasena FROM usuarios WHERE usuario = 'admin' LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row) {
            $ok = password_verify('admin123', $row['contrasena']);
            if ($ok) {
                echo "<p class='ok'>✅ password_verify('admin123') = TRUE — El login DEBERÍA funcionar.</p>";
            } else {
                echo "<p class='err'>❌ password_verify('admin123') = FALSE — El hash no corresponde a 'admin123'.</p>";
                echo "<p class='warn'>⚠️ Abre: <a href='fix_password.php?token=pestalozzi2024' style='color:#fb923c'>fix_password.php?token=pestalozzi2024</a> para resetear la contraseña.</p>";
            }
        } else {
            echo "<p class='err'>❌ No existe el usuario 'admin' en la BD.</p>";
        }
    }

} catch (PDOException $e) {
    echo "<p class='err'>❌ ERROR DE CONEXIÓN: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p class='warn'>Posibles causas:</p><ul>";
    echo "<li>MySQL no está corriendo (verifica Laragon/XAMPP)</li>";
    echo "<li>La BD <b>$db_name</b> no existe — importa <b>bk_basededatos.sql</b></li>";
    echo "<li>Usuario/contraseña de MySQL incorrectos en <b>app/config/db.php</b></li>";
    echo "</ul>";
    
    // Listar BDs disponibles
    try {
        $pdoRoot = new PDO("mysql:host=$host", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $dbs = $pdoRoot->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Bases de datos disponibles en MySQL:</p><pre>" . implode("\n", $dbs) . "</pre>";
    } catch (PDOException $e2) {
        echo "<p class='err'>No se pudo conectar a MySQL en absoluto: " . htmlspecialchars($e2->getMessage()) . "</p>";
    }
}

echo "<hr><p style='color:#666;font-size:12px'>⚠️ Elimina este archivo (diagnostico.php) después de resolver el problema.</p>";
echo "</body></html>";
?>
