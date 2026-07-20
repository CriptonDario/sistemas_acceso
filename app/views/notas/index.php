<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notas — Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar{min-height:100vh;background:#212529;color:#fff}
        .sidebar a{color:#adb5bd;text-decoration:none;padding:12px 20px;display:block;border-left:3px solid transparent;transition:.3s}
        .sidebar a:hover{background:#343a40;color:#fff}
        .sidebar a.active{background:#0d6efd;color:#fff;border-left-color:#fff}
        .sidebar i{width:25px}
        .nota-input{width:70px;text-align:center}
        .nota-alta  { color:#198754;font-weight:bold }
        .nota-media { color:#fd7e14;font-weight:bold }
        .nota-baja  { color:#dc3545;font-weight:bold }
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height:100vh;overflow-y:auto">
        <h2 class="mb-4 fw-bold text-secondary"><i class="bi bi-journal-check"></i> Registro de Notas</h2>

        <?php if (isset($_GET['msg']) && $_GET['msg']==='guardado'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                ✅ Notas guardadas correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="c" value="Nota">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Grado</label>
                        <select name="grado_id" class="form-select" required onchange="this.form.submit()">
                            <option value="">-- Seleccionar Grado --</option>
                            <optgroup label="Primaria">
                            <?php foreach ($grados as $g): if ($g['nivel']==='primaria'): ?>
                                <option value="<?php echo $g['id']; ?>"
                                    <?php echo $grado_id==$g['id'] ? 'selected' : ''; ?>>
                                    <?php echo $g['nombre']; ?>
                                </option>
                            <?php endif; endforeach; ?>
                            </optgroup>
                            <optgroup label="Secundaria">
                            <?php foreach ($grados as $g): if ($g['nivel']==='secundaria'): ?>
                                <option value="<?php echo $g['id']; ?>"
                                    <?php echo $grado_id==$g['id'] ? 'selected' : ''; ?>>
                                    <?php echo $g['nombre']; ?>
                                </option>
                            <?php endif; endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Materia</label>
                        <select name="materia_id" class="form-select" required>
                            <option value="">-- Seleccionar Materia --</option>
                            <?php foreach ($materias as $mat): ?>
                                <option value="<?php echo $mat['id']; ?>"
                                    <?php echo $materia_id==$mat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($mat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Trimestre</label>
                        <select name="periodo" class="form-select">
                            <option value="T1" <?php echo $periodo==='T1' ? 'selected' : ''; ?>>1er Trimestre</option>
                            <option value="T2" <?php echo $periodo==='T2' ? 'selected' : ''; ?>>2do Trimestre</option>
                            <option value="T3" <?php echo $periodo==='T3' ? 'selected' : ''; ?>>3er Trimestre</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Año</label>
                        <input type="number" name="anio" class="form-control" value="<?php echo $anio; ?>" min="2024" max="2030">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">
                            <i class="bi bi-search"></i> Cargar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($listaNotas)): ?>
        <!-- Formulario de notas -->
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-pencil-square me-1"></i>
                    <?php echo htmlspecialchars($gradoInfo['nombre']); ?> —
                    <?php echo htmlspecialchars($materiaInfo['nombre']); ?> —
                    <?php echo ['T1'=>'1er Trimestre','T2'=>'2do Trimestre','T3'=>'3er Trimestre'][$periodo]; ?>
                    (<?php echo $anio; ?>)
                </span>
                <small><?php echo count($listaNotas); ?> alumnos</small>
            </div>
            <div class="card-body p-0">
                <form action="?c=Nota&a=guardar" method="POST">
                    <input type="hidden" name="grado_id"   value="<?php echo $grado_id; ?>">
                    <input type="hidden" name="materia_id" value="<?php echo $materia_id; ?>">
                    <input type="hidden" name="periodo"    value="<?php echo $periodo; ?>">
                    <input type="hidden" name="anio"       value="<?php echo $anio; ?>">

                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" width="40">Foto</th>
                                <th>Alumno</th>
                                <th class="text-center" width="100">Nota (0–20)</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listaNotas as $al): ?>
                            <?php
                                $foto = (!empty($al['foto']) && $al['foto'] !== 'default.png'
                                         && file_exists(__DIR__.'/../../../public/uploads/'.$al['foto']))
                                    ? 'uploads/'.$al['foto']
                                    : 'https://ui-avatars.com/api/?name='.urlencode($al['nombres'].'+'.$al['apellidos']).'&background=198754&color=fff&size=60';
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <img src="<?php echo $foto; ?>" style="width:36px;height:36px;object-fit:cover;border-radius:50%">
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo $al['apellidos'].', '.$al['nombres']; ?></div>
                                    <small class="text-muted"><?php echo $al['codigo']; ?></small>
                                </td>
                                <td class="text-center">
                                    <input type="number" name="notas[<?php echo $al['id']; ?>]"
                                           class="form-control nota-input mx-auto"
                                           min="0" max="20" step="0.5"
                                           value="<?php echo $al['nota'] ?? ''; ?>"
                                           placeholder="—">
                                </td>
                                <td>
                                    <input type="text" name="obs[<?php echo $al['id']; ?>]"
                                           class="form-control form-control-sm"
                                           placeholder="Observación opcional..."
                                           value="">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="p-3 border-top text-end">
                        <button type="submit" class="btn btn-success btn-lg fw-bold px-5">
                            <i class="bi bi-save me-1"></i> Guardar Notas
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php elseif ($grado_id && $materia_id): ?>
            <div class="alert alert-warning">No hay alumnos en este grado o la selección no tiene datos.</div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-1"></i>
                Selecciona un grado y una materia para cargar los alumnos y registrar notas.
            </div>
        <?php endif; ?>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
