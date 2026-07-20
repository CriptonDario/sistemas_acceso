<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar Notas — Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body{
            background:linear-gradient(135deg,#f3f7fb 0%,#eef5ff 100%);
            font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
        .container{max-width:1420px}
        .page-shell{
            background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);
            border:1px solid #dfe7f2;
            border-radius:22px;
            box-shadow:0 16px 40px rgba(15,23,42,.08);
            padding:1.2rem;
        }
        .hero-card{
            background:linear-gradient(120deg,#0f4c81 0%,#2563eb 55%,#3b82f6 100%);
            color:#fff;
            border:none;
            border-radius:18px;
            overflow:hidden;
        }
        .hero-card .badge{
            background:rgba(255,255,255,.16);
            border:1px solid rgba(255,255,255,.22);
            color:#fff;
        }
        .card-modern{
            border:1px solid #e5ecf7;
            border-radius:16px;
            box-shadow:0 8px 22px rgba(15,23,42,.05);
            overflow:hidden;
        }
        .card-modern .card-header{
            background:linear-gradient(90deg,#1e4d8f 0%,#2563eb 100%);
            color:#fff;
            border:none;
            padding:0.9rem 1rem;
            font-weight:700;
        }
        .card-modern .card-header.bg-warning{
            background:linear-gradient(90deg,#ffdd57 0%,#fbbf24 100%) !important;
            color:#3f2e00 !important;
        }
        .card-modern .card-header.bg-success{
            background:linear-gradient(90deg,#1f9d6b 0%,#2ab673 100%) !important;
        }
        .table-modern thead{
            background:linear-gradient(90deg,#f8fbff 0%,#eef5ff 100%);
            color:#12355b;
        }
        .table-modern th{font-size:.85rem; font-weight:700; letter-spacing:.02em}
        .table-modern tbody tr:hover{background:#f8fbff}
        .nota-input{width:92px;text-align:center;font-weight:700;border-radius:10px;border:1px solid #cbd5e1}
        .nota-input:focus{box-shadow:0 0 0 3px rgba(37,99,235,.15);border-color:#2563eb}
        .nota-alta {color:#198754}
        .nota-media{color:#fd7e14}
        .nota-baja {color:#dc3545}
        .btn-soft{border-radius:12px; box-shadow:0 6px 14px rgba(37,99,235,.16)}
        .panel-title{font-size:1rem; font-weight:700; color:#12355b}
        .panel-sub{font-size:.9rem; color:#64748b}
    </style>
</head>
<body>
<?php
$periodo = isset($periodo) ? $periodo : 'T1';
$periodoTexto = is_string($periodo) ? ($periodo === 'T1' ? '1er Trimestre' : ($periodo === 'T2' ? '2do Trimestre' : ($periodo === 'T3' ? '3er Trimestre' : $periodo))) : $periodo;
?>
<nav class="navbar navbar-dark bg-primary shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="?c=PortalDocente">
            <i class="bi bi-arrow-left me-1"></i> Mis Materias
        </a>
        <span class="text-white fw-bold">
            <?php echo htmlspecialchars($materiaInfo['nombre']); ?> —
            <?php echo htmlspecialchars($gradoInfo['nombre']); ?> —
            <?php echo htmlspecialchars($periodoTexto); ?>
            (<?php echo $anio; ?>)
        </span>
        <a href="?c=Portal&a=logout" class="btn btn-sm btn-light text-primary fw-bold">Salir</a>
    </div>
</nav>

<div class="container mt-4 pb-5">
    <div class="page-shell">

    <!-- Alertas -->
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg']==='guardado'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                ✅ Notas guardadas correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['msg']==='importado'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                ✅ Importación completada: <b><?php echo intval($_GET['imp']); ?></b> notas guardadas.
                <?php if (intval($_GET['err_csv']) > 0): ?>
                    <br>⚠️ <?php echo intval($_GET['err_csv']); ?> filas con errores (código no encontrado o nota inválida).
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php elseif (isset($_GET['err'])): ?>
        <?php $errMap=['sin_archivo'=>'No se recibió archivo.','formato_invalido'=>'Solo se aceptan archivos .csv']; ?>
        <div class="alert alert-danger">❌ <?php echo $errMap[$_GET['err']] ?? 'Error.'; ?></div>
    <?php endif; ?>

    <div class="card hero-card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-journal-bookmark me-2"></i>Registro de notas del periodo</h4>
                    <p class="mb-0 opacity-75">Gestiona el ingreso, importación y consolidación de notas con una vista más clara y formal.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge px-3 py-2"><i class="bi bi-mortarboard me-1"></i> <?php echo htmlspecialchars($gradoInfo['nombre']); ?></span>
                    <span class="badge px-3 py-2"><i class="bi bi-book me-1"></i> <?php echo htmlspecialchars($materiaInfo['nombre']); ?></span>
                    <span class="badge px-3 py-2"><i class="bi bi-calendar3 me-1"></i> <?php echo htmlspecialchars($periodoTexto); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- INGRESO MANUAL -->
        <div class="col-lg-8">
            <div class="card card-modern shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-pencil-square me-1"></i> Ingreso Manual de Notas</span>
                    <span class="badge bg-light text-primary"><?php echo count($listaNotas); ?> alumnos</span>
                </div>
                <div class="card-body p-0">
                    <form action="?c=PortalDocente&a=guardar" method="POST">
                        <input type="hidden" name="grado_id"   value="<?php echo $grado_id; ?>">
                        <input type="hidden" name="materia_id" value="<?php echo $materia_id; ?>">
                        <input type="hidden" name="periodo"    value="<?php echo $periodo; ?>">
                        <input type="hidden" name="anio"       value="<?php echo $anio; ?>">

                        <table class="table table-hover align-middle mb-0 table-modern">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" width="40">Foto</th>
                                    <th>Alumno</th>
                                    <th class="text-center" width="90">Nota (0-20)</th>
                                    <th>Observación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listaNotas as $al): ?>
                                <?php
                                    $foto = (!empty($al['foto']) && $al['foto']!=='default.png'
                                             && file_exists(__DIR__.'/../../../public/uploads/'.$al['foto']))
                                        ? 'uploads/'.$al['foto']
                                        : 'https://ui-avatars.com/api/?name='.urlencode($al['nombres'].'+'.$al['apellidos']).'&background=0d6efd&color=fff&size=60';
                                    $nc = $al['nota']===null ? '' : ($al['nota']>=14 ? 'nota-alta' : ($al['nota']>=11 ? 'nota-media' : 'nota-baja'));
                                ?>
                                <tr>
                                    <td class="ps-3">
                                        <img src="<?php echo $foto; ?>" style="width:34px;height:34px;object-fit:cover;border-radius:50%">
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo $al['apellidos'].', '.$al['nombres']; ?></div>
                                        <small class="text-muted"><?php echo $al['codigo']; ?></small>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" name="notas[<?php echo $al['id']; ?>]"
                                               class="form-control nota-input mx-auto <?php echo $nc; ?>"
                                               min="0" max="20" step="0.5"
                                               value="<?php echo $al['nota'] ?? ''; ?>"
                                               placeholder="—"
                                               onchange="colorNota(this)">
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-2">
                                            <input type="text" name="obs[<?php echo $al['id']; ?>]"
                                                   class="form-control form-control-sm"
                                                   placeholder="Observación..."
                                                   value="<?php echo htmlspecialchars($al['observacion'] ?? ''); ?>">
                                            <a href="?c=PortalDocente&a=cartilla&alumno_id=<?php echo $al['id']; ?>&anio=<?php echo $anio; ?>"
                                               class="btn btn-outline-secondary btn-sm"
                                               target="_blank">
                                                <i class="bi bi-file-earmark-text me-1"></i> Ver cartilla
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="p-3 border-top text-end">
                            <button type="submit" class="btn btn-success btn-lg fw-bold px-5 btn-soft">
                                <i class="bi bi-save me-1"></i> Guardar Notas
                            </button>
                        </div>
                    </form>

                    <form action="?c=PortalDocente&a=consolidarAnual" method="POST" class="px-3 pb-3">
                        <input type="hidden" name="grado_id" value="<?php echo $grado_id; ?>">
                        <input type="hidden" name="materia_id" value="<?php echo $materia_id; ?>">
                        <input type="hidden" name="anio" value="<?php echo $anio; ?>">
                        <button type="submit" class="btn btn-outline-primary w-100 fw-bold">
                            <i class="bi bi-journal-check me-1"></i> Consolidar notas finales del año
                        </button>
                </div>
            </div>
        </div>

        <!-- PANEL DERECHO: Importar + Plantilla -->
        <div class="col-lg-4">

            <!-- Descargar plantilla -->
            <div class="card card-modern shadow-sm border-0 mb-3">
                <div class="card-header bg-success fw-bold">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i> Plantilla Excel
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Descarga una planilla automática con los alumnos de esta materia y grado.
                        Se ajusta al número de estudiantes y permite llenar la columna <b>Nota (0-20)</b> para el periodo seleccionado.
                    </p>
                    <a href="?c=PortalDocente&a=plantilla&grado_id=<?php echo $grado_id; ?>&materia_id=<?php echo $materia_id; ?>&periodo=<?php echo $periodo; ?>&anio=<?php echo $anio; ?>"
                       class="btn btn-success w-100 fw-bold btn-soft">
                        <i class="bi bi-download me-1"></i> Descargar plantilla CSV
                    </a>
                    <small class="text-muted d-block mt-2">
                        Abre el archivo en Excel o Google Sheets, llena la columna <b>NOTA</b> y luego súbelo nuevamente.
                    </small>
                </div>
            </div>

            <!-- Importar CSV -->
            <div class="card card-modern shadow-sm border-0 mb-3">
                <div class="card-header bg-warning fw-bold">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i> Importar desde Excel (CSV)
                </div>
                <div class="card-body">
                    <form action="?c=PortalDocente&a=importar" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="grado_id"   value="<?php echo $grado_id; ?>">
                        <input type="hidden" name="materia_id" value="<?php echo $materia_id; ?>">
                        <input type="hidden" name="periodo"    value="<?php echo $periodo; ?>">
                        <input type="hidden" name="anio"       value="<?php echo $anio; ?>">
                        <div class="mb-3">
                            <input type="file" name="archivo" class="form-control" accept=".csv,.txt" required>
                            <small class="text-muted">Solo archivos .CSV</small>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-bold text-dark btn-soft">
                            <i class="bi bi-upload me-1"></i> Importar Notas
                        </button>
                    </form>
                </div>
            </div>

            <!-- Ver Acta -->
            <div class="card card-modern shadow-sm border-0">
                <div class="card-body text-center">
                    <a href="?c=PortalDocente&a=acta&grado_id=<?php echo $grado_id; ?>&anio=<?php echo $anio; ?>"
                       class="btn btn-outline-secondary w-100 btn-soft">
                        <i class="bi bi-file-earmark-text me-1"></i> Ver Acta Consolidada
                    </a>
                    <small class="text-muted d-block mt-1">Todas las materias del grado</small>
                </div>
            </div>

        </div>
    </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function colorNota(input) {
    const v = parseFloat(input.value);
    input.classList.remove('nota-alta','nota-media','nota-baja');
    if (!isNaN(v)) {
        if (v >= 14)      input.classList.add('nota-alta');
        else if (v >= 11) input.classList.add('nota-media');
        else              input.classList.add('nota-baja');
    }
}
// Colorear notas al cargar
document.querySelectorAll('.nota-input').forEach(colorNota);
</script>
</body>
</html>
