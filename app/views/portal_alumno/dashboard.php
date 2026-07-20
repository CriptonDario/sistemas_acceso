<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Alumno — Colegio Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background:#f0f4f8 }
        .navbar-brand img { height:32px; filter:brightness(0) invert(1) }
        .nota-alta  { color:#198754;font-weight:700 }
        .nota-media { color:#fd7e14;font-weight:700 }
        .nota-baja  { color:#dc3545;font-weight:700 }
        .nota-nd    { color:#adb5bd }
        .card-nota  { border-left:4px solid #0d6efd }
        @media print {
            body * { visibility:hidden }
            #printArea, #printArea * { visibility:visible }
            #printArea { position:fixed;inset:0;display:flex;align-items:center;justify-content:center }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-success shadow-sm">
    <div class="container">
        <span class="navbar-brand fw-bold">
            <img src="https://www.pestalozzi.edu.pe/wp-content/themes/wp-theme-pestalozzi/public/assets/images/logo.svg"
                 alt="Pestalozzi" onerror="this.style.display='none'">
            Portal del Alumno
        </span>
        <div class="d-flex align-items-center gap-2">
            <span class="text-white d-none d-md-block">Hola, <strong><?php echo htmlspecialchars($alumnoNombre); ?></strong></span>
            <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#passModal">
                <i class="bi bi-key-fill"></i>
            </button>
            <a href="?c=PortalAlumno&a=logout" class="btn btn-sm btn-light text-success fw-bold">Salir</a>
        </div>
    </div>
</nav>

<?php
    // Alertas
    $err = $_GET['err'] ?? '';
    $msg = $_GET['msg'] ?? '';
?>
<?php if ($msg === 'pass_ok'): ?>
    <div class="alert alert-success alert-dismissible fade show m-3 mb-0">✅ Contraseña actualizada.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif ($err): ?>
    <?php $errMsg = ['pass_incorrecta'=>'Contraseña actual incorrecta.','no_coinciden'=>'Las contraseñas no coinciden.','pass_corta'=>'Mínimo 6 caracteres.']; ?>
    <div class="alert alert-danger alert-dismissible fade show m-3 mb-0">❌ <?php echo $errMsg[$err] ?? 'Error.'; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="container mt-4 pb-5">
    <div class="row g-4">

        <!-- Carnet -->
        <div class="col-md-4 col-lg-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-header bg-white fw-bold text-muted small">MI CARNET</div>
                <div class="card-body">
                    <div id="printArea">
                      <div class="bg-white rounded-4 border p-3 text-center">
                        <div class="mb-2 pb-1 border-bottom">
                            <img src="https://www.pestalozzi.edu.pe/wp-content/themes/wp-theme-pestalozzi/public/assets/images/logo.svg"
                                 style="height:22px" alt="Pestalozzi" onerror="this.style.display='none'">
                        </div>
                        <?php
                            $foto = (!empty($alumnoFoto) && $alumnoFoto !== 'default.png'
                                     && file_exists(__DIR__.'/../../../public/uploads/'.$alumnoFoto))
                                ? 'uploads/'.$alumnoFoto
                                : 'https://ui-avatars.com/api/?name='.urlencode($alumnoNombre).'&background=198754&color=fff&size=120';
                        ?>
                        <img src="<?php echo $foto; ?>" alt="foto"
                             style="width:72px;height:72px;object-fit:cover;border-radius:50%;border:3px solid #198754;margin:8px auto;display:block">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode($alumnoCodigo); ?>"
                             style="width:140px;height:140px;display:block;margin:6px auto" alt="QR">
                        <h6 class="fw-bold text-success mb-1" style="font-size:.9rem"><?php echo htmlspecialchars($alumnoNombre); ?></h6>
                        <p class="text-muted mb-1" style="font-size:.75rem"><?php echo htmlspecialchars($alumno['nombre_grado'] ?? ''); ?></p>
                        <span class="badge bg-dark px-3 py-1 font-monospace"><?php echo $alumnoCodigo; ?></span>
                      </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <button class="btn btn-outline-success btn-sm w-100 mb-2" onclick="window.print()">
                        <i class="bi bi-printer"></i> Imprimir Carnet
                    </button>
                    <a href="?c=PortalAlumno&a=cartilla&anio=<?php echo date('Y'); ?>"
                       target="_blank"
                       class="btn btn-success btn-sm w-100 fw-bold">
                        <i class="bi bi-file-earmark-person me-1"></i> Mi Cartilla de Notas
                    </a>
                    <!-- Selector de año -->
                    <div class="mt-2 d-flex gap-1 align-items-center">
                        <small class="text-muted">Otro año:</small>
                        <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                            <?php if ($y != date('Y')): ?>
                            <a href="?c=PortalAlumno&a=cartilla&anio=<?php echo $y; ?>"
                               target="_blank"
                               class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:.75rem">
                                <?php echo $y; ?>
                            </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Asistencia del mes -->
            <div class="card shadow-sm border-0 mt-3">
                <div class="card-header bg-white fw-bold text-muted small d-flex justify-content-between">
                    <span>ASISTENCIA (Este Mes)</span>
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Fecha</th><th>Entrada</th><th>Estado</th></tr></thead>
                        <tbody>
                            <?php if (!empty($asistencias)): ?>
                                <?php foreach (array_slice($asistencias, 0, 10) as $as): ?>
                                <tr>
                                    <td><?php echo date('d/m', strtotime($as['fecha'])); ?></td>
                                    <td><?php echo $as['hora_entrada'] ? date('H:i', strtotime($as['hora_entrada'])) : '—'; ?></td>
                                    <td>
                                        <?php if ($as['estado']==='puntual'): ?>
                                            <span class="badge bg-success">Puntual</span>
                                        <?php elseif ($as['estado']==='tarde'): ?>
                                            <span class="badge bg-warning text-dark">Tarde</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Falta</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted p-3">Sin registros este mes.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Notas -->
        <div class="col-md-8 col-lg-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-muted">MIS NOTAS — <?php echo $anio; ?></span>
                    <!-- Selector de año -->
                    <form method="GET" class="d-flex gap-2 align-items-center">
                        <input type="hidden" name="c" value="PortalAlumno">
                        <select name="anio" class="form-select form-select-sm" style="width:90px" onchange="this.form.submit()">
                            <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo $anio==$y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($resumenNotas)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th class="text-start ps-3">Materia</th>
                                    <th>1er Trimestre</th>
                                    <th>2do Trimestre</th>
                                    <th>3er Trimestre</th>
                                    <th>Promedio</th>
                                </tr>
                            </thead>
                            <?php
                                // Función definida UNA SOLA VEZ, fuera del bucle
                                $fmtNota = function($v) {
                                    if ($v === null) return '<span class="nota-nd">—</span>';
                                    $c = $v >= 14 ? 'nota-alta' : ($v >= 11 ? 'nota-media' : 'nota-baja');
                                    return "<span class='$c'>$v</span>";
                                };
                            ?>
                            <tbody>
                                <?php foreach ($resumenNotas as $materia => $notas): ?>
                                <?php
                                    $prom = $notas['promedio'];
                                    $cls  = $prom === null ? 'nota-nd' : ($prom >= 14 ? 'nota-alta' : ($prom >= 11 ? 'nota-media' : 'nota-baja'));
                                ?>
                                <tr>
                                    <td class="ps-3 fw-bold"><?php echo htmlspecialchars($materia); ?></td>
                                    <td class="text-center"><?php echo $fmtNota($notas['T1']); ?></td>
                                    <td class="text-center"><?php echo $fmtNota($notas['T2']); ?></td>
                                    <td class="text-center"><?php echo $fmtNota($notas['T3']); ?></td>
                                    <td class="text-center fs-5">
                                        <?php echo $prom !== null ? "<span class='$cls'>$prom</span>" : '<span class="nota-nd">—</span>'; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 border-top">
                        <div class="d-flex gap-3 small">
                            <span class="nota-alta"><i class="bi bi-circle-fill me-1"></i>14–20 Aprobado</span>
                            <span class="nota-media"><i class="bi bi-circle-fill me-1"></i>11–13 Regular</span>
                            <span class="nota-baja"><i class="bi bi-circle-fill me-1"></i>0–10 Desaprobado</span>
                        </div>
                    </div>

                    <?php else: ?>
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                            No hay notas registradas para <?php echo $anio; ?>.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal cambiar contraseña -->
<div class="modal fade" id="passModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark fw-bold">
        <h5 class="modal-title"><i class="bi bi-shield-lock"></i> Cambiar Contraseña</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="?c=PortalAlumno&a=change_password" method="POST">
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label fw-bold">Contraseña Actual</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Nueva Contraseña</label>
                <input type="password" name="new_password" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Confirmar Contraseña</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="6">
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
</body>
</html>
