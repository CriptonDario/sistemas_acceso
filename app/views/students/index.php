<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Alumnos — Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: 0.3s; }
        .sidebar a:hover { background-color: #343a40; color: white; }
        .sidebar a.active { background-color: #198754; color: white; border-left-color: white; }
        .sidebar i { width: 25px; }
        .table-inactive { opacity: 0.55; background-color: #f8f9fa; }
        .foto-tabla { width: 44px; height: 44px; object-fit: cover; border-radius: 50%; border: 2px solid #dee2e6; }
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height:100vh;overflow-y:auto;">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <h2><i class="bi bi-mortarboard-fill text-success"></i> Alumnos</h2>
            <div class="d-flex gap-2">
                <a href="?c=Student&a=materias" class="btn btn-outline-primary shadow-sm">
                    <i class="bi bi-journal-bookmark-fill"></i> Materias
                </a>
                <button class="btn btn-success shadow" data-bs-toggle="modal" data-bs-target="#newStudentModal">
                    <i class="bi bi-plus-circle"></i> Nuevo Alumno
                </button>
            </div>
        </div>

        <!-- Buscador -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="c" value="Student">
                    <div class="col-auto"><label class="col-form-label fw-bold">Buscar:</label></div>
                    <div class="col-md-5">
                        <input type="text" name="q" class="form-control" placeholder="Nombre, código..."
                               value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                        <?php if (!empty($_GET['q'])): ?>
                            <a href="?c=Student" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (isset($_GET['msg'])): ?>
            <?php $msgs = ['guardado'=>'Alumno registrado con éxito.','actualizado'=>'Datos actualizados.','estado_cambiado'=>'Estado actualizado.','eliminado'=>'Alumno eliminado.']; ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill"></i> <?php echo $msgs[$_GET['msg']] ?? 'Operación exitosa.'; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['err'])): ?>
            <?php $errs = ['codigo_duplicado'=>'El código ya existe.','campos_vacios'=>'Completa todos los campos.','error'=>'Ocurrió un error.']; ?>
            <div class="alert alert-danger alert-dismissible fade show">
                ❌ <?php echo $errs[$_GET['err']] ?? 'Error desconocido.'; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Tabla -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Foto</th>
                            <th>Estado</th>
                            <th>Nombre Completo</th>
                            <th>Grado / Sección</th>
                            <th>Correo</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $s): ?>
                            <?php
                                $fotoSrc = (!empty($s['foto']) && $s['foto'] !== 'default.png' && file_exists(__DIR__ . '/../../../public/uploads/' . $s['foto']))
                                    ? 'uploads/' . $s['foto']
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($s['nombres'] . '+' . $s['apellidos']) . '&background=198754&color=fff&size=80';
                            ?>
                            <tr class="<?php echo ($s['estado'] === 'inactivo') ? 'table-inactive' : ''; ?>">
                                <td class="ps-3">
                                    <img src="<?php echo $fotoSrc; ?>" class="foto-tabla" alt="foto">
                                </td>
                                <td>
                                    <?php echo ($s['estado'] === 'activo')
                                        ? '<span class="badge bg-success">ACTIVO</span>'
                                        : '<span class="badge bg-danger">INACTIVO</span>'; ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($s['nombres'] . ' ' . $s['apellidos']); ?></div>
                                    <small class="text-muted font-monospace"><?php echo htmlspecialchars($s['codigo']); ?></small>
                                </td>
                                <td>
                                    <?php if ($s['nombre_grado']): ?>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($s['nombre_grado']); ?></span>
                                        <?php if ($s['seccion']): ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($s['seccion']); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?php echo htmlspecialchars($s['correo']); ?></small></td>
                                <td class="text-end pe-4">
                                    <a href="?c=Student&a=notas&id=<?php echo $s['id']; ?>"
                                       class="btn btn-sm btn-info text-white shadow-sm">
                                        <i class="bi bi-journal-check"></i> Notas
                                    </a>
                                    <a href="?c=Student&a=edit&id=<?php echo $s['id']; ?>"
                                       class="btn btn-sm btn-warning shadow-sm"><i class="bi bi-pencil-fill"></i></a>
                                    <?php if ($s['estado'] === 'activo'): ?>
                                        <a href="?c=Student&a=toggle&id=<?php echo $s['id']; ?>&status=activo"
                                           class="btn btn-sm btn-outline-danger shadow-sm"
                                           onclick="confirmarAccion(event,this.href,'¿Desactivar alumno?','No podrá acceder al portal.','warning')">
                                            <i class="bi bi-power"></i></a>
                                    <?php else: ?>
                                        <a href="?c=Student&a=toggle&id=<?php echo $s['id']; ?>&status=inactivo"
                                           class="btn btn-sm btn-outline-success shadow-sm"
                                           onclick="confirmarAccion(event,this.href,'¿Reactivar alumno?','Podrá acceder al portal.','success')">
                                            <i class="bi bi-power"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center p-5 text-muted">
                                <i class="bi bi-mortarboard display-4 d-block mb-2"></i>
                                No hay alumnos registrados.
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL NUEVO ALUMNO -->
<div class="modal fade" id="newStudentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill"></i> Nuevo Alumno</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="?c=Student&a=store" method="POST" enctype="multipart/form-data">
          <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombres</label>
                    <input type="text" name="nombres" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Código de Alumno</label>
                    <input type="text" name="codigo" class="form-control" required placeholder="Ej: ALU001">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Grado</label>
                    <select name="grado_id" class="form-select" required>
                        <option value="">-- Seleccionar --</option>
                        <?php foreach ($grados as $g): ?>
                            <option value="<?php echo $g['id']; ?>">
                                <?php echo htmlspecialchars($g['nombre']); ?>
                                <?php echo $g['seccion'] ? '— ' . htmlspecialchars($g['seccion']) : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Foto <small class="text-muted">(opcional, máx 3MB)</small></label>
                    <input type="file" name="foto" class="form-control" accept="image/*" onchange="previewFoto(this,'prevNuevo')">
                    <img id="prevNuevo" src="" class="mt-2 rounded-circle d-none" style="width:60px;height:60px;object-fit:cover;">
                </div>
                <div class="col-12">
                    <div class="alert alert-info mb-0 small">
                        <i class="bi bi-info-circle"></i> Contraseña por defecto del portal: <strong>123456</strong>
                    </div>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success fw-bold">Guardar</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmarAccion(event, url, titulo, mensaje, icono) {
    event.preventDefault();
    Swal.fire({ title:titulo, text:mensaje, icon:icono, showCancelButton:true,
        confirmButtonText:'Sí', cancelButtonText:'No'
    }).then(r => { if(r.isConfirmed) window.location.href = url; });
}
function previewFoto(input, previewId) {
    const prev = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { prev.src = e.target.result; prev.classList.remove('d-none'); };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
