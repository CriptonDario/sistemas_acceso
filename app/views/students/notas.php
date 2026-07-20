<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notas de <?php echo htmlspecialchars($student['nombres']); ?> — Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: 0.3s; }
        .sidebar a:hover { background-color: #343a40; color: white; }
        .sidebar a.active { background-color: #198754; color: white; border-left-color: white; }
        .sidebar i { width: 25px; }
        .nota-badge { min-width: 48px; display: inline-block; text-align: center; font-size: 1rem; font-weight: bold; padding: 4px 10px; border-radius: 8px; }
        .nota-aprobado { background: #d1e7dd; color: #0a3622; }
        .nota-desaprobado { background: #f8d7da; color: #58151c; }
        .nota-vacia { background: #e9ecef; color: #6c757d; }
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height:100vh;overflow-y:auto;">

        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
            <a href="?c=Student" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
            <h2 class="mb-0">
                <i class="bi bi-journal-check text-info"></i>
                Notas — <?php echo htmlspecialchars($student['nombres'] . ' ' . $student['apellidos']); ?>
            </h2>
            <span class="badge bg-primary fs-6"><?php echo htmlspecialchars($student['nombre_grado'] ?? '—'); ?>
                <?php echo $student['seccion'] ? '— ' . htmlspecialchars($student['seccion']) : ''; ?>
            </span>
        </div>

        <!-- Alertas -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'nota_guardada'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill"></i> Nota guardada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['err'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                ❌ Datos inválidos. La nota debe estar entre 0 y 20.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Tabla de notas -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-table"></i> Resumen de Notas por Materia</span>
                        <span class="badge bg-secondary">Escala: 0 – 20</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Materia</th>
                                    <th>Docente</th>
                                    <th class="text-center">Bim. 1</th>
                                    <th class="text-center">Bim. 2</th>
                                    <th class="text-center">Bim. 3</th>
                                    <th class="text-center">Bim. 4</th>
                                    <th class="text-center">Promedio</th>
                                    <th class="text-end pe-3">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($resumen)): ?>
                                    <?php foreach ($resumen as $fila): ?>
                                    <?php
                                        $prom = $fila['promedio'];
                                        $promClass = ($prom === null) ? 'nota-vacia' : (($prom >= 11) ? 'nota-aprobado' : 'nota-desaprobado');
                                    ?>
                                    <tr>
                                        <td class="ps-3 fw-bold"><?php echo htmlspecialchars($fila['materia']); ?></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars(($fila['docente_apellidos'] ?? '') . ', ' . ($fila['docente_nombres'] ?? '')); ?></small></td>
                                        <?php foreach ([1,2,3,4] as $bim): ?>
                                            <?php $n = $fila['bim' . $bim]; ?>
                                            <td class="text-center">
                                                <?php if ($n !== null): ?>
                                                    <span class="nota-badge <?php echo ($n >= 11) ? 'nota-aprobado' : 'nota-desaprobado'; ?>">
                                                        <?php echo number_format($n, 1); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="nota-badge nota-vacia">—</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="text-center">
                                            <span class="nota-badge <?php echo $promClass; ?>">
                                                <?php echo ($prom !== null) ? number_format($prom, 1) : '—'; ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="abrirNota(<?php echo $fila['materia_id']; ?>, '<?php echo htmlspecialchars($fila['materia'], ENT_QUOTES); ?>')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center p-4 text-muted">
                                        No hay materias asignadas a este grado.<br>
                                        <a href="?c=Student&a=materias" class="btn btn-sm btn-primary mt-2">Crear materias</a>
                                    </td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel lateral: estadísticas -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-success text-white fw-bold">
                        <i class="bi bi-bar-chart-fill"></i> Estadísticas
                    </div>
                    <div class="card-body">
                        <?php
                        $aprobadas = 0; $desaprobadas = 0; $total = 0;
                        foreach ($resumen as $f) {
                            if ($f['promedio'] !== null) {
                                $total++;
                                if ($f['promedio'] >= 11) $aprobadas++;
                                else $desaprobadas++;
                            }
                        }
                        $promedioGral = $total > 0 ? array_sum(array_column($resumen, 'promedio')) / count(array_filter(array_column($resumen, 'promedio'), fn($v) => $v !== null)) : 0;
                        ?>
                        <div class="text-center mb-3">
                            <div class="display-5 fw-bold <?php echo ($promedioGral >= 11) ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($total > 0) ? number_format($promedioGral, 1) : '—'; ?>
                            </div>
                            <small class="text-muted">Promedio General</small>
                        </div>
                        <div class="d-flex justify-content-around text-center">
                            <div>
                                <div class="fs-4 fw-bold text-success"><?php echo $aprobadas; ?></div>
                                <small class="text-muted">Aprobadas</small>
                            </div>
                            <div>
                                <div class="fs-4 fw-bold text-danger"><?php echo $desaprobadas; ?></div>
                                <small class="text-muted">Desaprobadas</small>
                            </div>
                            <div>
                                <div class="fs-4 fw-bold text-secondary"><?php echo count($resumen) - $total; ?></div>
                                <small class="text-muted">Sin nota</small>
                            </div>
                        </div>
                        <?php if ($total > 0): ?>
                        <div class="mt-3">
                            <div class="progress" style="height:10px;">
                                <div class="progress-bar bg-success" style="width:<?php echo round($aprobadas / $total * 100); ?>%"></div>
                                <div class="progress-bar bg-danger" style="width:<?php echo round($desaprobadas / $total * 100); ?>%"></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">
                        <i class="bi bi-person-badge"></i> Datos del Alumno
                    </div>
                    <div class="card-body small">
                        <p class="mb-1"><strong>Código:</strong> <?php echo htmlspecialchars($student['codigo']); ?></p>
                        <p class="mb-1"><strong>Correo:</strong> <?php echo htmlspecialchars($student['correo']); ?></p>
                        <p class="mb-1"><strong>Grado:</strong>
                            <?php echo htmlspecialchars($student['nombre_grado'] ?? '—'); ?>
                            <?php echo $student['seccion'] ? '— ' . htmlspecialchars($student['seccion']) : ''; ?>
                        </p>
                        <p class="mb-0"><strong>Estado:</strong>
                            <?php echo ($student['estado'] === 'activo')
                                ? '<span class="badge bg-success">ACTIVO</span>'
                                : '<span class="badge bg-danger">INACTIVO</span>'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL INGRESAR NOTA -->
<div class="modal fade" id="notaModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square"></i> Registrar / Actualizar Nota</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="?c=Student&a=save_nota" method="POST">
          <input type="hidden" name="alumno_id" value="<?php echo $student['id']; ?>">
          <input type="hidden" name="materia_id" id="modalMateriaId">
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label fw-bold">Materia</label>
                <input type="text" id="modalMateriaNombre" class="form-control" readonly>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Bimestre</label>
                    <select name="bimestre" class="form-select" required>
                        <option value="1">1er Bimestre</option>
                        <option value="2">2do Bimestre</option>
                        <option value="3">3er Bimestre</option>
                        <option value="4">4to Bimestre</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nota (0 – 20)</label>
                    <input type="number" name="nota" class="form-control" min="0" max="20" step="0.5" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Observación <small class="text-muted">(opcional)</small></label>
                    <textarea name="observacion" class="form-control" rows="2" maxlength="255"></textarea>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary fw-bold">Guardar Nota</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function abrirNota(materiaId, materiaNombre) {
    document.getElementById('modalMateriaId').value    = materiaId;
    document.getElementById('modalMateriaNombre').value = materiaNombre;
    new bootstrap.Modal(document.getElementById('notaModal')).show();
}
</script>
</body>
</html>
