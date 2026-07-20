<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignación de Docentes — Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar{min-height:100vh;background:#212529;color:#fff}
        .sidebar a{color:#adb5bd;text-decoration:none;padding:12px 20px;display:block;border-left:3px solid transparent;transition:.3s}
        .sidebar a:hover{background:#343a40;color:#fff}
        .sidebar a.active{background:#0d6efd;color:#fff;border-left-color:#fff}
        .sidebar i{width:25px}
        .badge-aula   { background:#198754 }
        .badge-curso  { background:#0d6efd }
        .badge-primaria   { background:#1565c0 }
        .badge-secundaria { background:#e65100 }
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height:100vh;overflow-y:auto">
        <h2 class="mb-1 fw-bold text-secondary">
            <i class="bi bi-person-workspace"></i> Asignación de Docentes
        </h2>
        <p class="text-muted small mb-4">
            <i class="bi bi-info-circle me-1"></i>
            <b>Primaria:</b> un docente por sección (ingresa todas las materias) &nbsp;|&nbsp;
            <b>Secundaria:</b> un docente por materia
        </p>

        <!-- Alertas -->
        <?php
            $msgs = ['creado'=>'✅ Asignación registrada.','eliminado'=>'✅ Asignación eliminada.'];
            $errs = [
                'duplicado'   => '❌ Esta asignación ya existe.',
                'campos_vacios'=>'❌ Completa todos los campos.',
                'sin_materia' => '❌ Debes seleccionar una materia para secundaria.',
                'error'       => '❌ Error al guardar.',
            ];
            if (isset($_GET['msg'])) echo "<div class='alert alert-success shadow-sm'>{$msgs[$_GET['msg']]}</div>";
            if (isset($_GET['err'])) echo "<div class='alert alert-danger shadow-sm'>{$errs[$_GET['err']]}</div>";
        ?>

        <div class="row g-4">

            <!-- ── Formulario ── -->
            <div class="col-md-4">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-plus-lg me-1"></i> Nueva Asignación
                    </div>
                    <div class="card-body">
                        <form action="?c=Asignacion&a=store" method="POST" id="formAsig">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Docente *</label>
                                <select name="personal_id" class="form-select" required>
                                    <option value="">-- Seleccionar Docente --</option>
                                    <?php foreach ($docentes as $d): ?>
                                    <option value="<?php echo $d['id']; ?>">
                                        <?php echo htmlspecialchars($d['apellidos'].' '.$d['nombres']); ?>
                                        <small>(<?php echo $d['cargo']; ?>)</small>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Grado *</label>
                                <select name="grado_id" class="form-select" required
                                        onchange="actualizarFormulario(this)">
                                    <option value="">-- Seleccionar Grado --</option>
                                    <optgroup label="🔵 Primaria (docente de aula)">
                                    <?php foreach ($grados as $g): if ($g['nivel']==='primaria'): ?>
                                        <option value="<?php echo $g['id']; ?>"
                                                data-nivel="primaria">
                                            <?php echo $g['nombre']; ?>
                                        </option>
                                    <?php endif; endforeach; ?>
                                    </optgroup>
                                    <optgroup label="🟠 Secundaria (docente por curso)">
                                    <?php foreach ($grados as $g): if ($g['nivel']==='secundaria'): ?>
                                        <option value="<?php echo $g['id']; ?>"
                                                data-nivel="secundaria">
                                            <?php echo $g['nombre']; ?>
                                        </option>
                                    <?php endif; endforeach; ?>
                                    </optgroup>
                                </select>
                            </div>

                            <!-- Indicador del tipo que se aplicará -->
                            <div id="tipoIndicador" class="alert alert-secondary py-2 small mb-3" style="display:none"></div>

                            <!-- Materia (solo visible para secundaria) -->
                            <div id="campoMateria" class="mb-3" style="display:none">
                                <label class="form-label fw-bold">
                                    Materia * <span class="badge bg-warning text-dark">Solo Secundaria</span>
                                </label>
                                <select name="materia_id" class="form-select" id="selectMateria">
                                    <option value="">-- Seleccionar Materia --</option>
                                    <?php foreach ($materias as $m): ?>
                                    <option value="<?php echo $m['id']; ?>">
                                        <?php echo htmlspecialchars($m['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <input type="hidden" name="tipo_asignacion" id="tipoAsig" value="curso">
                            <input type="hidden" name="anio" value="<?php echo $anio; ?>">

                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-save me-1"></i> Asignar Docente
                            </button>
                        </form>
                    </div>

                    <!-- Filtro de año -->
                    <div class="card-footer bg-white">
                        <form method="GET" class="d-flex gap-2">
                            <input type="hidden" name="c" value="Asignacion">
                            <label class="col-form-label fw-bold small">Ver año:</label>
                            <input type="number" name="anio" class="form-control form-control-sm"
                                   value="<?php echo $anio; ?>" style="width:80px">
                            <button class="btn btn-sm btn-outline-primary">Ver</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ── Lista de asignaciones ── -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-muted">
                            Asignaciones <?php echo $anio; ?>
                            <span class="badge bg-secondary ms-1"><?php echo count($asignaciones); ?></span>
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Docente</th>
                                    <th>Grado</th>
                                    <th>Tipo</th>
                                    <th>Materia</th>
                                    <th class="text-end pe-3">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($asignaciones)): ?>
                                <?php
                                // Agrupar por nivel para mejor visualización
                                $porNivel = ['primaria'=>[], 'secundaria'=>[]];
                                foreach ($asignaciones as $a) {
                                    $porNivel[$a['nivel']][] = $a;
                                }
                                foreach ($porNivel as $nivel => $lista):
                                    if (empty($lista)) continue;
                                ?>
                                <tr class="table-<?php echo $nivel==='primaria' ? 'primary' : 'warning'; ?> bg-opacity-10">
                                    <td colspan="5" class="ps-3 fw-bold small text-uppercase"
                                        style="background:<?php echo $nivel==='primaria' ? '#e3f2fd' : '#fff8e1'; ?>">
                                        <?php echo $nivel==='primaria' ? '🔵 PRIMARIA' : '🟠 SECUNDARIA'; ?>
                                    </td>
                                </tr>
                                <?php foreach ($lista as $a): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold">
                                            <?php echo htmlspecialchars($a['apellidos'].' '.$a['nombres']); ?>
                                        </div>
                                        <small class="text-muted"><?php echo $a['cod_personal']; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $a['nivel']==='primaria' ? 'primaria' : 'secundaria'; ?>">
                                            <?php echo htmlspecialchars($a['grado_nombre']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($a['tipo_asignacion']==='aula'): ?>
                                            <span class="badge badge-aula">
                                                <i class="bi bi-house-fill me-1"></i> Docente de Aula
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-curso">
                                                <i class="bi bi-book-fill me-1"></i> Por Curso
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($a['tipo_asignacion']==='aula'): ?>
                                            <span class="text-muted small">
                                                <i class="bi bi-check-all text-success"></i> Todas las materias
                                            </span>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($a['materia_nombre'] ?? '—'); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="?c=Asignacion&a=delete&id=<?php echo $a['id']; ?>&anio=<?php echo $anio; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('¿Eliminar esta asignación?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center p-5 text-muted">
                                        Sin asignaciones para <?php echo $anio; ?>.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function actualizarFormulario(select) {
    const opt    = select.options[select.selectedIndex];
    const nivel  = opt ? opt.getAttribute('data-nivel') : '';
    const campo  = document.getElementById('campoMateria');
    const tipo   = document.getElementById('tipoAsig');
    const ind    = document.getElementById('tipoIndicador');
    const matSel = document.getElementById('selectMateria');

    if (nivel === 'primaria') {
        campo.style.display = 'none';
        matSel.required     = false;
        tipo.value          = 'aula';
        ind.style.display   = 'block';
        ind.className       = 'alert alert-success py-2 small mb-3';
        ind.innerHTML       = '🏠 <b>Primaria:</b> Se asignará como <b>Docente de Aula</b> — podrá ingresar notas de <b>todas las materias</b> del grado.';
    } else if (nivel === 'secundaria') {
        campo.style.display = 'block';
        matSel.required     = true;
        tipo.value          = 'curso';
        ind.style.display   = 'block';
        ind.className       = 'alert alert-warning py-2 small mb-3';
        ind.innerHTML       = '📚 <b>Secundaria:</b> Se asignará por <b>Materia específica</b> — solo podrá ingresar notas de ese curso.';
    } else {
        campo.style.display = 'none';
        ind.style.display   = 'none';
        tipo.value          = 'curso';
    }
}
</script>
</body>
</html>
