<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Materias — Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar{min-height:100vh;background:#212529;color:#fff}
        .sidebar a{color:#adb5bd;text-decoration:none;padding:12px 20px;display:block;border-left:3px solid transparent;transition:.3s}
        .sidebar a:hover{background:#343a40;color:#fff}
        .sidebar a.active{background:#0d6efd;color:#fff;border-left-color:#fff}
        .sidebar i{width:25px}
        .table-inactive{opacity:.55}
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height:100vh;overflow-y:auto">
        <h2 class="mb-4 fw-bold text-secondary"><i class="bi bi-book-fill"></i> Gestión de Materias</h2>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Operación exitosa. <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['err']) && $_GET['err']==='vacio'): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                ❌ El nombre no puede estar vacío. <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <!-- Agregar nueva -->
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-plus-lg"></i> Nueva Materia
                    </div>
                    <div class="card-body">
                        <form action="?c=Materia&a=store" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre *</label>
                                <input type="text" name="nombre" class="form-control" required placeholder="Ej: Física">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nivel</label>
                                <select name="nivel" class="form-select">
                                    <option value="ambos">Primaria y Secundaria</option>
                                    <option value="primaria">Solo Primaria</option>
                                    <option value="secundaria">Solo Secundaria</option>
                                </select>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary fw-bold">Agregar Materia</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Estado</th>
                                    <th>Materia</th>
                                    <th>Nivel</th>
                                    <th class="text-end pe-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($materias)): ?>
                                    <?php foreach ($materias as $m): ?>
                                    <tr class="<?php echo $m['estado']==='inactivo' ? 'table-inactive' : ''; ?>">
                                        <td class="ps-3">
                                            <?php echo $m['estado']==='activo'
                                                ? '<span class="badge bg-success">ACTIVO</span>'
                                                : '<span class="badge bg-danger">INACTIVO</span>'; ?>
                                        </td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($m['nombre']); ?></td>
                                        <td>
                                            <?php
                                                $nivLabels = ['ambos'=>'Primaria y Secundaria','primaria'=>'Primaria','secundaria'=>'Secundaria'];
                                                $nivColors = ['ambos'=>'bg-secondary','primaria'=>'bg-primary','secundaria'=>'bg-warning text-dark'];
                                                echo '<span class="badge '.$nivColors[$m['nivel']].'">'.$nivLabels[$m['nivel']].'</span>';
                                            ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <button class="btn btn-sm btn-outline-warning me-1"
                                                onclick="editarMateria(<?php echo $m['id']; ?>,'<?php echo htmlspecialchars($m['nombre'],ENT_QUOTES); ?>','<?php echo $m['nivel']; ?>')">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <?php if ($m['estado']==='activo'): ?>
                                                <a href="?c=Materia&a=toggle&id=<?php echo $m['id']; ?>&status=activo"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('¿Desactivar materia?')"><i class="bi bi-power"></i></a>
                                            <?php else: ?>
                                                <a href="?c=Materia&a=toggle&id=<?php echo $m['id']; ?>&status=inactivo"
                                                   class="btn btn-sm btn-outline-success"
                                                   onclick="return confirm('¿Reactivar materia?')"><i class="bi bi-power"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center p-4 text-muted">Sin materias registradas.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal editar -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark fw-bold">
        <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Materia</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="?c=Materia&a=update" method="POST">
        <div class="modal-body">
            <input type="hidden" name="id" id="editId">
            <div class="mb-3">
                <label class="form-label fw-bold">Nombre *</label>
                <input type="text" name="nombre" id="editNombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Nivel</label>
                <select name="nivel" id="editNivel" class="form-select">
                    <option value="ambos">Primaria y Secundaria</option>
                    <option value="primaria">Solo Primaria</option>
                    <option value="secundaria">Solo Secundaria</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-warning fw-bold">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editarMateria(id, nombre, nivel) {
    document.getElementById('editId').value     = id;
    document.getElementById('editNombre').value = nombre;
    document.getElementById('editNivel').value  = nivel;
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
</script>
</body>
</html>
