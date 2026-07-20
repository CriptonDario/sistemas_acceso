<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Personal — Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: 0.3s; }
        .sidebar a:hover { background-color: #343a40; color: white; }
        .sidebar a.active { background-color: #0d6efd; color: white; border-left-color: white; }
        .sidebar i { width: 25px; }
        #fotoPreview { width:100px;height:100px;object-fit:cover;border-radius:50%;border:3px solid #0d6efd; }
    </style>
</head>
<body class="bg-light">

<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height:100vh;overflow-y:auto;">

        <div class="d-flex align-items-center mb-4">
            <a href="?c=Employee" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Volver</a>
            <h2 class="mb-0 fw-bold text-secondary"><i class="bi bi-pencil-square"></i> Editar Personal</h2>
        </div>

        <div class="card shadow border-0">
            <div class="card-header bg-warning text-dark fw-bold">Actualizar Datos</div>
            <div class="card-body p-4">
                <form action="?c=Employee&a=update_data" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $emp['id']; ?>">

                    <div class="row g-3">

                        <!-- Foto actual + cambiar -->
                        <div class="col-12 d-flex align-items-center gap-4 mb-2">
                            <?php
                                $fotoActual = (!empty($emp['foto']) && $emp['foto'] !== 'default.png' && file_exists(__DIR__ . '/../../../public/uploads/' . $emp['foto']))
                                    ? 'uploads/' . $emp['foto']
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($emp['nombres'] . '+' . $emp['apellidos']) . '&background=0d6efd&color=fff&size=100';
                            ?>
                            <img id="fotoPreview" src="<?php echo $fotoActual; ?>" alt="foto actual">
                            <div>
                                <label class="form-label fw-bold mb-1">Cambiar Foto <small class="text-muted">(máx 3MB)</small></label>
                                <input type="file" name="foto" class="form-control form-control-sm" accept="image/*"
                                       onchange="previewFoto(this)">
                                <small class="text-muted">Deja vacío para mantener la foto actual.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Código (QR)</label>
                            <input type="text" name="employee_code" class="form-control" required
                                   value="<?php echo htmlspecialchars($emp['codigo']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?php echo htmlspecialchars($emp['correo']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombres</label>
                            <input type="text" name="first_name" class="form-control" required
                                   value="<?php echo htmlspecialchars($emp['nombres']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Apellidos</label>
                            <input type="text" name="last_name" class="form-control" required
                                   value="<?php echo htmlspecialchars($emp['apellidos']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Área</label>
                            <select name="department_id" class="form-select">
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>"
                                        <?php echo ($dept['id'] == $emp['area_id']) ? 'selected' : ''; ?>>
                                        <?php echo $dept['nombre']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Cargo</label>
                            <input type="text" name="position" class="form-control" required
                                   value="<?php echo htmlspecialchars($emp['cargo']); ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Tipo de Personal</label>
                            <select name="tipo_personal" class="form-select">
                                <option value="docente"        <?php echo ($emp['tipo_personal']==='docente')        ? 'selected' : ''; ?>>Docente</option>
                                <option value="administrativo" <?php echo ($emp['tipo_personal']==='administrativo') ? 'selected' : ''; ?>>Administrativo</option>
                                <option value="apoyo"          <?php echo ($emp['tipo_personal']==='apoyo')          ? 'selected' : ''; ?>>Apoyo</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end">
                        <a href="?c=Employee" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-warning fw-bold px-4">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('fotoPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
