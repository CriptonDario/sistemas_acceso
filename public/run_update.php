<?php
$host        = 'localhost';
$user        = 'root';
$pass        = '';
$hash_admin  = password_hash('admin123',     PASSWORD_DEFAULT);
$hash_seg    = password_hash('seguridad123', PASSWORD_DEFAULT);
$hash_123    = password_hash('123456',       PASSWORD_DEFAULT);

try {
    $pdo = new PDO(
        "mysql:host=$host;charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 1. Crear BD si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `pestalozzi_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo->exec("USE `pestalozzi_db`");

    $tablas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas encontradas: " . implode(', ', $tablas) . PHP_EOL;

    // 2. Tabla usuarios existe?
    if (in_array('usuarios', $tablas)) {

        // Actualizar admin
        $r = $pdo->prepare("UPDATE usuarios SET contrasena = :h, estado = 'activo' WHERE usuario = 'admin'");
        $r->execute([':h' => $hash_admin]);
        echo "admin actualizado: " . $r->rowCount() . " fila(s)" . PHP_EOL;

        // Actualizar seguridad
        $r2 = $pdo->prepare("UPDATE usuarios SET contrasena = :h, estado = 'activo' WHERE usuario = 'seguridad'");
        $r2->execute([':h' => $hash_seg]);
        echo "seguridad actualizado: " . $r2->rowCount() . " fila(s)" . PHP_EOL;

        // Verificar resultado
        $users = $pdo->query("SELECT id, usuario, rol, estado FROM usuarios")->fetchAll();
        echo PHP_EOL . "=== USUARIOS ===" . PHP_EOL;
        foreach ($users as $u) {
            echo "  [{$u['id']}] {$u['usuario']} | rol:{$u['rol']} | estado:{$u['estado']}" . PHP_EOL;
        }

    } else {
        echo "ERROR: tabla 'usuarios' no existe." . PHP_EOL;
        echo "Necesitas importar bk_basededatos.sql en HeidiSQL primero." . PHP_EOL;
        exit(1);
    }

    // 3. Personal sin contrasena
    if (in_array('personal', $tablas)) {
        $sin = $pdo->query("SELECT COUNT(*) FROM personal WHERE contrasena IS NULL OR contrasena = ''")->fetchColumn();
        if ($sin > 0) {
            $pdo->prepare("UPDATE personal SET contrasena = :h WHERE contrasena IS NULL OR contrasena = ''")->execute([':h' => $hash_123]);
            echo PHP_EOL . "$sin miembro(s) del personal -> contrasena asignada: 123456" . PHP_EOL;
        }
    }

    echo PHP_EOL . "=== LISTO ===" . PHP_EOL;
    echo "admin     -> admin123" . PHP_EOL;
    echo "seguridad -> seguridad123" . PHP_EOL;

} catch (PDOException $e) {
    echo "ERROR PDO: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
