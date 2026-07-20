<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Materias — Pestalozzi</title>
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

        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="?c=Student" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
                <h2 class="mb-0"><i class="bi bi-journal-bookmark-fill text-primary"></i> Materias</h2>
            </div>
            <button class="btn btn-primary shadow" data-bs-toggle="modal" data-bs-target="#newMateriaModal">
                <i class="bi bi-plus-circle"></i> Nueva Materia
            </button>
        </div>

        <!-- Alertas -->
        <?php if (isset($_GET['msg'])): ?>
            <?php $msgs = ['materia_creada'=>'Materia creada correctamente.','materia_eliminada'=>'Materia eliminada.']; ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill"></i> <?php echo $msgs[$_GET['msg']] ?? 'Operación exitosa.'; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['err'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                ❌ Completa todos los campos obligatorios.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Materia</th>
                            <th>Grado / Sección</th>
                            <th>Docente Asignado</th>
                            <th class="text-end pe-4">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($materias)): ?>
                            <?php foreach ($materias as $m): ?>
                            <tr>
                                <td class="ps-3 fw-bold"><?php echo htmlspecialchars($m['nombre']); ?></td>
                                <td>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($m['grado_nombre'] ?? '—'); ?></span>
                                    <?php if (!empty($m['seccion'])): ?>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($m['seccion']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($m['docente_nombres']): ?>
                                        <?php echo htmlspecialchars($m['docente_apellidos'] . ', ' . $m['docente_nombres']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Sin asignar</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="?c=Student&a=delete_materia&id=<?php echo $m['id']; ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="confirmarAccion(event,this.href,'¿Eliminar materia?','Se borrarán todas las notas asociadas.','warning')">
                                        <i class="bi bi-trash3"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center p-5 text-muted">No hay materias registradas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL NUEVA MATERIA -->
<div class="modal fade" id="newMateriaModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle"></i> Nueva Materia</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="?c=Student&a=store_materia" method="POST">
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label fw-bold">Nombre de la Materia</label>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej: Matemáticas">
            </div>
            <div class="mb-3">
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
            <div class="mb-3">
                <label class="form-label fw-bold">Docente <small class="text-muted">(opcional)</small></label>
                <select name="docente_id" class="form-select">
                    <option value="">-- Sin asignar --</option>
                    <?php foreach ($docentes as $d): ?>
                        <option value="<?php echo $d['id']; ?>">
                            <?php echo htmlspecialchars($d['apellidos'] . ', ' . $d['nombres']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary fw-bold">Crear Materia</button>
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
        confirmButtonText:'Sí, eliminar', cancelButtonText:'No'
    }).then(r => { if(r.isConfirmed) window.location.href = url; });
}
</script>
</body>
</html>
