<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Alumno — Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: 0.3s; }
        .sidebar a:hover { background-color: #343a40; color: white; }
        .sidebar a.active { background-color: #198754; color: white; border-left-color: white; }
        .sidebar i { width: 25px; }
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height:100vh;overflow-y:auto;">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="?c=Student" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
            <h2 class="mb-0"><i class="bi bi-pencil-square text-warning"></i> Editar Alumno</h2>
        </div>

        <?php
        $fotoSrc = (!empty($student['foto']) && $student['foto'] !== 'default.png' && file_exists(__DIR__ . '/../../../public/uploads/' . $student['foto']))
            ? 'uploads/' . $student['foto']
            : 'https://ui-avatars.com/api/?name=' . urlencode($student['nombres'] . '+' . $student['apellidos']) . '&background=198754&color=fff&size=120';
        ?>

        <div class="card shadow-sm border-0 mx-auto" style="max-width:700px;">
            <div class="card-header bg-warning text-dark fw-bold">
                <i class="bi bi-person-fill"></i> Datos del Alumno
            </div>
            <form action="?c=Student&a=update_data" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img id="fotoPreview" src="<?php echo $fotoSrc; ?>"
                             style="width:90px;height:90px;object-fit:cover;border-radius:50%;border:3px solid #198754;">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombres</label>
                            <input type="text" name="nombres" class="form-control"
                                   value="<?php echo htmlspecialchars($student['nombres']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control"
                                   value="<?php echo htmlspecialchars($student['apellidos']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Correo</label>
                            <input type="email" name="correo" class="form-control"
                                   value="<?php echo htmlspecialchars($student['correo']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Código</label>
                            <input type="text" name="codigo" class="form-control"
                                   value="<?php echo htmlspecialchars($student['codigo']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Grado</label>
                            <select name="grado_id" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($grados as $g): ?>
                                    <option value="<?php echo $g['id']; ?>"
                                        <?php echo ($g['id'] == $student['grado_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($g['nombre']); ?>
                                        <?php echo $g['seccion'] ? '— ' . htmlspecialchars($g['seccion']) : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Foto <small class="text-muted">(opcional)</small></label>
                            <input type="file" name="foto" class="form-control" accept="image/*"
                                   onchange="previewFoto(this,'fotoPreview')">
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex gap-2 justify-content-end">
                    <a href="?c=Student" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-warning fw-bold">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewFoto(input, previewId) {
    const prev = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { prev.src = e.target.result; };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
