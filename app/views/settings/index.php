<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración — Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: 0.3s; }
        .sidebar a:hover { background-color: #343a40; color: white; }
        .sidebar a.active { background-color: #0d6efd; color: white; border-left-color: white; }
        .sidebar i { width: 25px; }
    </style>
</head>
<body class="bg-light">

<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height:100vh;overflow-y:auto">
        <h2 class="mb-4 fw-bold text-secondary"><i class="bi bi-gear-fill"></i> Configuración del Sistema</h2>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                ✅ Configuración guardada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['err'])): ?>
            <?php $errores = ['formato_invalido'=>'Formato de hora inválido (usa HH:MM, Ej: 07:30).']; ?>
            <div class="alert alert-danger alert-dismissible fade show">
                ❌ <?php echo $errores[$_GET['err']] ?? 'Error al guardar.'; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">

            <!-- ── Horario de entrada ── -->
            <div class="col-md-6">
                <div class="card shadow border-0 h-100">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-clock-fill me-1"></i> Horario de Entrada
                    </div>
                    <div class="card-body p-4">
                        <form action="?c=Setting&a=update" method="POST">
                            <input type="hidden" name="seccion" value="horario">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Hora de Entrada Oficial</label>
                                <p class="text-muted small mb-2">Las marcas posteriores a esta hora se considerarán tardanzas.</p>
                                <input type="time" name="entry_time"
                                       class="form-control form-control-lg text-center fw-bold"
                                       value="<?php echo htmlspecialchars($entry_time ?? '07:30'); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-save me-1"></i> Guardar Horario
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ── WhatsApp ── -->
            <div class="col-md-6">
                <div class="card shadow border-0 h-100">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="bi bi-whatsapp me-1"></i> Notificaciones WhatsApp
                    </div>
                    <div class="card-body p-4">
                        <form action="?c=Setting&a=update" method="POST">
                            <input type="hidden" name="seccion" value="whatsapp">

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="wa_activo"
                                           id="waActivo" value="1"
                                           <?php echo ($wa_activo ?? '0') === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold" for="waActivo">
                                        Activar notificaciones WhatsApp
                                    </label>
                                </div>
                                <small class="text-muted">Se enviará mensaje al apoderado al entrar/salir el alumno.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Proveedor</label>
                                <select name="wa_proveedor" class="form-select"
                                        onchange="toggleWaFields(this.value)">
                                    <option value="callmebot" <?php echo ($wa_proveedor ?? 'callmebot') === 'callmebot' ? 'selected' : ''; ?>>
                                        CallMeBot (Gratis)
                                    </option>
                                    <option value="ultramsg" <?php echo ($wa_proveedor ?? '') === 'ultramsg' ? 'selected' : ''; ?>>
                                        UltraMsg (De pago)
                                    </option>
                                </select>
                            </div>

                            <!-- UltraMsg fields -->
                            <div id="ultramsgFields" style="display:<?php echo ($wa_proveedor ?? '') === 'ultramsg' ? 'block' : 'none'; ?>">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">UltraMsg Instance ID</label>
                                    <input type="text" name="wa_instance" class="form-control"
                                           value="<?php echo htmlspecialchars($wa_instance ?? ''); ?>"
                                           placeholder="Ej: instance12345">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">UltraMsg Token</label>
                                    <input type="text" name="wa_token" class="form-control"
                                           value="<?php echo htmlspecialchars($wa_token ?? ''); ?>"
                                           placeholder="Token de UltraMsg">
                                </div>
                            </div>

                            <!-- CallMeBot info -->
                            <div id="callmebotInfo" style="display:<?php echo ($wa_proveedor ?? 'callmebot') === 'callmebot' ? 'block' : 'none'; ?>">
                                <div class="alert alert-info small mb-3">
                                    <strong><i class="bi bi-info-circle me-1"></i> CallMeBot es gratuito.</strong><br>
                                    Cada apoderado debe enviar el mensaje
                                    <code>I allow callmebot to send me messages</code>
                                    al número <strong>+34 644 59 72 64</strong> por WhatsApp.<br>
                                    Recibirá su <strong>API key personal</strong> que se ingresa en el perfil del alumno.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100 fw-bold">
                                <i class="bi bi-save me-1"></i> Guardar WhatsApp
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div><!-- /row -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleWaFields(proveedor) {
    document.getElementById('ultramsgFields').style.display  = proveedor === 'ultramsg'  ? 'block' : 'none';
    document.getElementById('callmebotInfo').style.display   = proveedor === 'callmebot' ? 'block' : 'none';
}
</script>
</body>
</html>
