<?php
/**
 * ACTUALIZADOR PESTALOZZI — Ejecuta una sola vez
 * URL: http://localhost/tu-proyecto/public/actualizar.php?token=pestalozzi2024
 * ELIMINA este archivo después de usarlo.
 */

define('TOKEN', 'pestalozzi2024');

if (!isset($_GET['token']) || $_GET['token'] !== TOKEN) {
    http_response_code(403);
    die("<h2 style='color:red;font-family:sans-serif;padding:40px'>403 — Acceso denegado</h2>");
}

require_once '../app/config/db.php';

$ok  = [];
$err = [];

try {
    $db = (new Database())->getConnection();

    // ── 1. Resetear contraseñas de usuarios del sistema ──────────────────────
    $usuarios = [
        ['usuario' => 'admin',     'pass' => 'admin123',     'rol' => 'admin'],
        ['usuario' => 'seguridad', 'pass' => 'seguridad123', 'rol' => 'guardia'],
    ];

    foreach ($usuarios as $u) {
        $hash = password_hash($u['pass'], PASSWORD_DEFAULT);
        // Insertar si no existe, actualizar si existe
        $check = $db->prepare("SELECT id FROM usuarios WHERE usuario = :u");
        $check->execute([':u' => $u['usuario']]);
        if ($check->fetch()) {
            $stmt = $db->prepare("UPDATE usuarios SET contrasena = :h, estado = 'activo' WHERE usuario = :u");
        } else {
            $stmt = $db->prepare("INSERT INTO usuarios (usuario, contrasena, rol, estado) VALUES (:u, :h, '{$u['rol']}', 'activo')");
        }
        $stmt->execute([':h' => $hash, ':u' => $u['usuario']]);
        $ok[] = "✅ Usuario <b>{$u['usuario']}</b> → contraseña: <b>{$u['pass']}</b>";
    }

    // ── 2. Verificar tablas requeridas ───────────────────────────────────────
    $requeridas = ['usuarios','personal','areas','registros_asistencia','incidencias','configuracion',
                   'visitantes','registros_visitas','grados','materias','alumnos',
                   'asistencia_alumnos','notas','docente_materia_grado'];
    $tablas     = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($requeridas as $t) {
        if (in_array($t, $tablas)) {
            $ok[] = "✅ Tabla <b>$t</b> existe";
        } else {
            $err[] = "❌ Tabla <b>$t</b> NO EXISTE — re-importa bk_basededatos.sql";
        }
    }

    // ── 3. Verificar/crear configuración base ────────────────────────────────
    if (in_array('configuracion', $tablas)) {
        $claves = [
            'hora_entrada'   => '07:30',
            'nombre_colegio' => 'Colegio Pestalozzi',
            'wa_activo'      => '0',
            'wa_proveedor'   => 'callmebot',
            'wa_token'       => '',
            'wa_instance'    => '',
        ];
        foreach ($claves as $clave => $valor) {
            $chk = $db->prepare("SELECT id FROM configuracion WHERE clave = :c");
            $chk->execute([':c' => $clave]);
            if (!$chk->fetch()) {
                $db->prepare("INSERT INTO configuracion (clave, valor) VALUES (:c, :v)")
                   ->execute([':c' => $clave, ':v' => $valor]);
                $ok[] = "✅ Config <b>$clave</b> creada";
            } else {
                $ok[] = "✅ Config <b>$clave</b> ya existe";
            }
        }
    }

    // ── 4. Verificar personal con contraseña NULL ────────────────────────────
    if (in_array('personal', $tablas)) {
        $sinPass = $db->query("SELECT COUNT(*) as total FROM personal WHERE contrasena IS NULL OR contrasena = ''")->fetch();
        if ($sinPass['total'] > 0) {
            $hash = password_hash('123456', PASSWORD_DEFAULT);
            $db->prepare("UPDATE personal SET contrasena = :h WHERE contrasena IS NULL OR contrasena = ''")->execute([':h' => $hash]);
            $ok[] = "✅ {$sinPass['total']} miembro(s) del personal sin contraseña → asignada: <b>123456</b>";
        } else {
            $ok[] = "✅ Todo el personal tiene contraseña asignada";
        }
    }

} catch (Exception $e) {
    $err[] = "❌ Error de conexión: " . htmlspecialchars($e->getMessage());
}

// ── Mostrar resultado ─────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualización — Pestalozzi</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #1e1e2e; color: #cdd6f4; padding: 40px; }
        .box { max-width: 700px; margin: 0 auto; background: #313244; border-radius: 12px; padding: 30px; }
        h1 { color: #cba6f7; margin-bottom: 5px; }
        h2 { color: #89b4fa; font-size: 1rem; font-weight: normal; margin-top: 0; }
        .item { padding: 8px 12px; margin: 4px 0; border-radius: 6px; font-size: 0.95rem; }
        .ok  { background: #1e3a2f; border-left: 4px solid #a6e3a1; }
        .err { background: #3a1e1e; border-left: 4px solid #f38ba8; }
        .credentials { background: #45475a; border-radius: 8px; padding: 20px; margin-top: 20px; }
        .credentials table { width: 100%; border-collapse: collapse; }
        .credentials td { padding: 8px 12px; border-bottom: 1px solid #585b70; }
        .credentials td:first-child { color: #89dceb; width: 40%; }
        .btn { display: inline-block; margin-top: 20px; padding: 12px 28px; background: #89b4fa; color: #1e1e2e; border-radius: 8px; text-decoration: none; font-weight: bold; }
        .warn { color: #f38ba8; margin-top: 20px; font-size: 0.85rem; }
    </style>
</head>
<body>
<div class="box">
    <h1>🎓 Colegio Pestalozzi</h1>
    <h2>Actualización del sistema completada</h2>

    <?php foreach ($ok  as $msg): ?>
        <div class="item ok"><?= $msg ?></div>
    <?php endforeach; ?>
    <?php foreach ($err as $msg): ?>
        <div class="item err"><?= $msg ?></div>
    <?php endforeach; ?>

    <?php if (empty($err)): ?>
    <div class="credentials">
        <b style="color:#cba6f7">Credenciales de acceso:</b>
        <table>
            <tr><td>Administrador</td><td>usuario: <b>admin</b> — contraseña: <b>admin123</b></td></tr>
            <tr><td>Guardia</td><td>usuario: <b>seguridad</b> — contraseña: <b>seguridad123</b></td></tr>
            <tr><td>Personal (portal)</td><td>correo corporativo — contraseña: <b>123456</b></td></tr>
        </table>
    </div>
    <a class="btn" href="index.php?c=Auth&a=login">Ir al Login →</a>
    <?php endif; ?>

    <p class="warn">⚠️ Elimina este archivo (actualizar.php) después de ingresar al sistema.</p>
</div>
</body>
</html>
