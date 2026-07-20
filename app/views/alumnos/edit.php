<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Alumno — Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar{min-height:100vh;background:#212529;color:#fff}
        .sidebar a{color:#adb5bd;text-decoration:none;padding:12px 20px;display:block;border-left:3px solid transparent;transition:.3s}
        .sidebar a:hover{background:#343a40;color:#fff}
        .sidebar a.active{background:#0d6efd;color:#fff;border-left-color:#fff}
        .sidebar i{width:25px}
        #fotoPrev{width:90px;height:90px;object-fit:cover;border-radius:50%;border:3px solid #198754}
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height:100vh;overflow-y:auto">
        <div class="d-flex align-items-center mb-4">
            <a href="?c=Alumno" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Volver</a>
            <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-pencil-square"></i> Editar Alumno</h2>
        </div>

        <div class="card shadow border-0">
            <div class="card-header bg-warning text-dark fw-bold">Actualizar Datos del Alumno</div>
            <div class="card-body p-4">
                <form action="?c=Alumno&a=update" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $alumno['id']; ?>">

                    <div class="row g-3">
                        <!-- Foto -->
                        <div class="col-12 d-flex align-items-center gap-3 mb-1">
                            <?php
                                $fotoSrc = (!empty($alumno['foto']) && $alumno['foto'] !== 'default.png'
                                            && file_exists(__DIR__.'/../../../public/uploads/'.$alumno['foto']))
                                    ? 'uploads/'.$alumno['foto']
                                    : 'https://ui-avatars.com/api/?name='.urlencode($alumno['nombres'].'+'.$alumno['apellidos']).'&background=198754&color=fff&size=100';
                            ?>
                            <img id="fotoPrev" src="<?php echo $fotoSrc; ?>" alt="foto">
                            <div>
                                <label class="form-label fw-bold mb-1">Cambiar Foto <small class="text-muted">(máx 3MB)</small></label>
                                <input type="file" name="foto" class="form-control form-control-sm" accept="image/*"
                                       onchange="prevFoto(this)">
                                <small class="text-muted">Deja vacío para mantener la actual.</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Código QR *</label>
                            <input type="text" name="codigo" class="form-control" required
                                   value="<?php echo htmlspecialchars($alumno['codigo']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">DNI</label>
                            <input type="text" name="dni" class="form-control"
                                   value="<?php echo htmlspecialchars($alumno['dni'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control"
                                   value="<?php echo $alumno['fecha_nacimiento'] ?? ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombres *</label>
                            <input type="text" name="nombres" class="form-control" required
                                   value="<?php echo htmlspecialchars($alumno['nombres']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Apellidos *</label>
                            <input type="text" name="apellidos" class="form-control" required
                                   value="<?php echo htmlspecialchars($alumno['apellidos']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Grado *</label>
                            <select name="grado_id" class="form-select" required>
                                <optgroup label="Primaria">
                                <?php foreach ($grados as $g): if ($g['nivel']==='primaria'): ?>
                                    <option value="<?php echo $g['id']; ?>"
                                        <?php echo $g['id']==$alumno['grado_id'] ? 'selected' : ''; ?>>
                                        <?php echo $g['nombre']; ?>
                                    </option>
                                <?php endif; endforeach; ?>
                                </optgroup>
                                <optgroup label="Secundaria">
                                <?php foreach ($grados as $g): if ($g['nivel']==='secundaria'): ?>
                                    <option value="<?php echo $g['id']; ?>"
                                        <?php echo $g['id']==$alumno['grado_id'] ? 'selected' : ''; ?>>
                                        <?php echo $g['nombre']; ?>
                                    </option>
                                <?php endif; endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Correo (portal alumno)</label>
                            <input type="email" name="correo" class="form-control"
                                   value="<?php echo htmlspecialchars($alumno['correo'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre del Apoderado</label>
                            <input type="text" name="nombre_apoderado" class="form-control"
                                   value="<?php echo htmlspecialchars($alumno['nombre_apoderado'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Teléfono Apoderado <small class="text-muted">(con código país)</small></label>
                            <input type="text" name="telefono_apoderado" class="form-control"
                                   value="<?php echo htmlspecialchars($alumno['telefono_apoderado'] ?? ''); ?>"
                                   placeholder="51987654321">
                        </div>
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white py-1 small fw-bold">
                                    <i class="bi bi-whatsapp me-1"></i> WhatsApp — Notificaciones al Apoderado
                                </div>
                                <div class="card-body py-2">
                                    <div class="row g-2 align-items-start">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold mb-1">API Key CallMeBot del Apoderado</label>
                                            <input type="text" name="wa_apikey_apoderado" class="form-control"
                                                   value="<?php echo htmlspecialchars($alumno['wa_apikey_apoderado'] ?? ''); ?>"
                                                   placeholder="Ej: 123456">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold mb-1">¿Cómo obtener la API Key?</label>
                                            <small class="text-muted d-block">
                                                El apoderado debe enviar por WhatsApp al número
                                                <strong>+34 644 59 72 64</strong> el mensaje:<br>
                                                <code>I allow callmebot to send me messages</code><br>
                                                Recibirá su API key automáticamente.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="?c=Alumno" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-warning fw-bold px-4">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function prevFoto(input) {
    if (input.files && input.files[0]) {
        const r = new FileReader();
        r.onload = e => document.getElementById('fotoPrev').src = e.target.result;
        r.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
