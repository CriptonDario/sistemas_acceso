<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal del Alumno — Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background: #f0f4f8; }
        .nota-badge { min-width: 48px; display: inline-block; text-align: center;
                      font-size: 1rem; font-weight: bold; padding: 5px 12px; border-radius: 8px; }
        .nota-aprobado     { background: #d1e7dd; color: #0a3622; }
        .nota-desaprobado  { background: #f8d7da; color: #58151c; }
        .nota-vacia        { background: #e9ecef; color: #6c757d; }
        .card-materia { border-left: 4px solid #198754; transition: box-shadow 0.2s; }
        .card-materia:hover { box-shadow: 0 4px 16px rgba(0,0,0,.12) !important; }
        .promedio-badge { font-size: 1.3rem; min-width: 58px; text-align: center; border-radius: 10px; padding: 6px 12px; font-weight: 800; }
        .spinner-live { width: 1rem; height: 1rem; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm no-print">
  <div class="container">
    <span class="navbar-brand fw-bold">
        <i class="bi bi-mortarboard-fill"></i> Portal del Alumno — Pestalozzi
    </span>
    <div class="d-flex align-items-center gap-2">
        <span class="text-white me-2 d-none d-md-block">
            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($studentName); ?>
        </span>
        <!-- Indicador de actualización en tiempo real -->
        <span class="badge bg-light text-success me-1 d-flex align-items-center gap-1" id="liveIndicator">
            <span class="spinner-border spinner-live text-success" role="status"></span>
            <span id="liveText">En vivo</span>
        </span>
        <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#passModal">
            <i class="bi bi-key-fill"></i>
        </button>
        <a href="?c=StudentPortal&a=logout" class="btn btn-sm btn-light text-success fw-bold">Salir</a>
    </div>
  </div>
</nav>

<div class="container mt-4">

    <!-- Encabezado del alumno -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 text-center h-100">
                <div class="card-header bg-success text-white fw-bold">MI CREDENCIAL</div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center p-3">
                    <img src="<?php echo $foto; ?>" alt="foto"
                         style="width:90px;height:90px;object-fit:cover;border-radius:50%;border:3px solid #198754;margin-bottom:12px;">
                    <h5 class="fw-bold text-success mb-1"><?php echo htmlspecialchars($studentName); ?></h5>
                    <?php if ($alumno): ?>
                        <p class="text-muted small mb-1">
                            <i class="bi bi-book-half"></i>
                            <?php echo htmlspecialchars($alumno['nombre_grado'] ?? 'Sin grado asignado'); ?>
                            <?php echo !empty($alumno['seccion']) ? ' — ' . htmlspecialchars($alumno['seccion']) : ''; ?>
                        </p>
                    <?php endif; ?>
                    <span class="badge bg-dark px-3 py-1 font-monospace"><?php echo htmlspecialchars($_SESSION['student_code']); ?></span>
                </div>
            </div>
        </div>

        <!-- Resumen estadístico -->
        <div class="col-md-8 mb-3">
            <div class="card shadow-sm border-0 h-100" id="statsCard">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-bar-chart-fill text-success"></i> Resumen General</span>
                    <small class="text-muted" id="lastUpdate">—</small>
                </div>
                <div class="card-body" id="statsBody">
                    <?php
                    $totalMat = count($notasResumen);
                    $aprobadas = 0; $desaprobadas = 0; $sinNota = 0; $sumaPromedios = 0; $conNota = 0;
                    foreach ($notasResumen as $n) {
                        if ($n['promedio'] !== null) {
                            $conNota++;
                            $sumaPromedios += $n['promedio'];
                            if ($n['promedio'] >= 11) $aprobadas++;
                            else $desaprobadas++;
                        } else { $sinNota++; }
                    }
                    $promedioGral = $conNota > 0 ? $sumaPromedios / $conNota : null;
                    ?>
                    <div class="row text-center g-3">
                        <div class="col-3">
                            <div class="p-3 rounded-3 bg-light">
                                <div class="display-6 fw-bold <?php echo ($promedioGral !== null && $promedioGral >= 11) ? 'text-success' : 'text-danger'; ?>" id="statPromedio">
                                    <?php echo ($promedioGral !== null) ? number_format($promedioGral, 1) : '—'; ?>
                                </div>
                                <small class="text-muted">Promedio</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-3 rounded-3" style="background:#d1e7dd;">
                                <div class="display-6 fw-bold text-success" id="statAprobadas"><?php echo $aprobadas; ?></div>
                                <small class="text-muted">Aprobadas</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-3 rounded-3" style="background:#f8d7da;">
                                <div class="display-6 fw-bold text-danger" id="statDesaprobadas"><?php echo $desaprobadas; ?></div>
                                <small class="text-muted">Desaprobadas</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-3 rounded-3 bg-light">
                                <div class="display-6 fw-bold text-secondary" id="statSinNota"><?php echo $sinNota; ?></div>
                                <small class="text-muted">Sin nota</small>
                            </div>
                        </div>
                    </div>
                    <?php if ($conNota > 0): ?>
                    <div class="mt-3">
                        <div class="progress" style="height:12px;" id="statProgress">
                            <div class="progress-bar bg-success" style="width:<?php echo round($aprobadas / $conNota * 100); ?>%"
                                 title="Aprobadas"></div>
                            <div class="progress-bar bg-danger" style="width:<?php echo round($desaprobadas / $conNota * 100); ?>%"
                                 title="Desaprobadas"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de notas por materia -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-table"></i> Mis Notas por Materia</span>
            <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="notasTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Materia</th>
                            <th>Docente</th>
                            <th class="text-center">Bim. 1</th>
                            <th class="text-center">Bim. 2</th>
                            <th class="text-center">Bim. 3</th>
                            <th class="text-center">Bim. 4</th>
                            <th class="text-center">Promedio</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="notasBody">
                        <?php if (!empty($notasResumen)): ?>
                            <?php foreach ($notasResumen as $fila): ?>
                            <?php
                                $prom = $fila['promedio'];
                                $promClass = ($prom === null) ? 'nota-vacia' : (($prom >= 11) ? 'nota-aprobado' : 'nota-desaprobado');
                            ?>
                            <tr>
                                <td class="ps-3 fw-bold"><?php echo htmlspecialchars($fila['materia']); ?></td>
                                <td><small class="text-muted">
                                    <?php echo htmlspecialchars(trim(($fila['docente_apellidos'] ?? '') . ', ' . ($fila['docente_nombres'] ?? ''), ', ')); ?>
                                </small></td>
                                <?php foreach ([1,2,3,4] as $bim): ?>
                                    <?php $n = $fila['bim' . $bim]; ?>
                                    <td class="text-center">
                                        <span class="nota-badge <?php echo ($n !== null) ? (($n >= 11) ? 'nota-aprobado' : 'nota-desaprobado') : 'nota-vacia'; ?>">
                                            <?php echo ($n !== null) ? number_format($n, 1) : '—'; ?>
                                        </span>
                                    </td>
                                <?php endforeach; ?>
                                <td class="text-center">
                                    <span class="promedio-badge <?php echo $promClass; ?>">
                                        <?php echo ($prom !== null) ? number_format($prom, 1) : '—'; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($prom === null): ?>
                                        <span class="badge bg-secondary">Pendiente</span>
                                    <?php elseif ($prom >= 11): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Aprobado</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Desaprobado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="emptyRow"><td colspan="8" class="text-center p-5 text-muted">
                                <i class="bi bi-journal-x display-4 d-block mb-2"></i>
                                Aún no tienes notas registradas.
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal cambio de contraseña -->
<div class="modal fade" id="passModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock"></i> Cambiar Contraseña</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="?c=StudentPortal&a=change_password" method="POST">
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Contraseña Actual</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <hr>
            <div class="mb-3">
                <label class="form-label">Nueva Contraseña</label>
                <input type="password" name="new_password" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmar Nueva</label>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ============================================================
// ACTUALIZACIÓN EN TIEMPO REAL (polling cada 30 segundos)
// ============================================================
const REFRESH_INTERVAL = 30000; // 30 segundos
let refreshTimer;

function renderNota(val) {
    if (val === null || val === undefined) {
        return '<span class="nota-badge nota-vacia">—</span>';
    }
    const cls = parseFloat(val) >= 11 ? 'nota-aprobado' : 'nota-desaprobado';
    return `<span class="nota-badge ${cls}">${parseFloat(val).toFixed(1)}</span>`;
}

function renderPromedio(val) {
    if (val === null || val === undefined) {
        return '<span class="promedio-badge nota-vacia">—</span>';
    }
    const cls = parseFloat(val) >= 11 ? 'nota-aprobado' : 'nota-desaprobado';
    return `<span class="promedio-badge ${cls}">${parseFloat(val).toFixed(1)}</span>`;
}

function renderEstado(prom) {
    if (prom === null || prom === undefined) return '<span class="badge bg-secondary">Pendiente</span>';
    return parseFloat(prom) >= 11
        ? '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Aprobado</span>'
        : '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Desaprobado</span>';
}

function setLiveStatus(ok) {
    const badge = document.getElementById('liveIndicator');
    const text  = document.getElementById('liveText');
    if (ok) {
        badge.className = 'badge bg-light text-success me-1 d-flex align-items-center gap-1';
        text.textContent = 'En vivo';
    } else {
        badge.className = 'badge bg-danger text-white me-1 d-flex align-items-center gap-1';
        text.textContent = 'Sin conexión';
    }
}

async function refreshNotas() {
    try {
        const res  = await fetch('?c=StudentPortal&a=api_notas', { cache: 'no-store' });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();

        if (data.error) { setLiveStatus(false); return; }
        setLiveStatus(true);

        // Reconstruir tabla
        const tbody = document.getElementById('notasBody');
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center p-5 text-muted"><i class="bi bi-journal-x display-4 d-block mb-2"></i>Aún no tienes notas registradas.</td></tr>';
        } else {
            tbody.innerHTML = data.map(fila => `
                <tr>
                    <td class="ps-3 fw-bold">${fila.materia}</td>
                    <td><small class="text-muted">${((fila.docente_apellidos||'') + ', ' + (fila.docente_nombres||'')).replace(/^,\s*|,\s*$/g,'')}</small></td>
                    <td class="text-center">${renderNota(fila.bim1)}</td>
                    <td class="text-center">${renderNota(fila.bim2)}</td>
                    <td class="text-center">${renderNota(fila.bim3)}</td>
                    <td class="text-center">${renderNota(fila.bim4)}</td>
                    <td class="text-center">${renderPromedio(fila.promedio)}</td>
                    <td class="text-center">${renderEstado(fila.promedio)}</td>
                </tr>
            `).join('');
        }

        // Actualizar estadísticas
        let aprobadas = 0, desaprobadas = 0, sinNota = 0, sumaP = 0, conNota = 0;
        data.forEach(f => {
            if (f.promedio !== null && f.promedio !== undefined) {
                conNota++; sumaP += parseFloat(f.promedio);
                if (parseFloat(f.promedio) >= 11) aprobadas++;
                else desaprobadas++;
            } else sinNota++;
        });
        const promGral = conNota > 0 ? sumaP / conNota : null;
        const promEl   = document.getElementById('statPromedio');
        promEl.textContent = promGral !== null ? promGral.toFixed(1) : '—';
        promEl.className   = 'display-6 fw-bold ' + (promGral !== null && promGral >= 11 ? 'text-success' : 'text-danger');
        document.getElementById('statAprobadas').textContent    = aprobadas;
        document.getElementById('statDesaprobadas').textContent = desaprobadas;
        document.getElementById('statSinNota').textContent      = sinNota;

        // Actualizar barra de progreso
        const prog = document.getElementById('statProgress');
        if (prog && conNota > 0) {
            prog.innerHTML = `
                <div class="progress-bar bg-success" style="width:${Math.round(aprobadas/conNota*100)}%"></div>
                <div class="progress-bar bg-danger"  style="width:${Math.round(desaprobadas/conNota*100)}%"></div>`;
        }

        // Marca de tiempo
        const now = new Date();
        document.getElementById('lastUpdate').textContent =
            'Actualizado: ' + now.toLocaleTimeString('es-PE', {hour:'2-digit', minute:'2-digit', second:'2-digit'});

    } catch (err) {
        setLiveStatus(false);
    } finally {
        refreshTimer = setTimeout(refreshNotas, REFRESH_INTERVAL);
    }
}

// Arrancar ciclo de actualización
refreshTimer = setTimeout(refreshNotas, REFRESH_INTERVAL);

// Mensajes de alerta SweetAlert
const urlParams = new URLSearchParams(window.location.search);
const msg = urlParams.get('msg');
const err = urlParams.get('err');
if (msg === 'pass_actualizada') Swal.fire('¡Éxito!', 'Contraseña actualizada.', 'success');
if (err === 'pass_incorrecta')  Swal.fire('Error', 'Contraseña actual incorrecta.', 'error');
if (err === 'no_coinciden')     Swal.fire('Error', 'Las nuevas contraseñas no coinciden.', 'error');
if (err === 'pass_corta')       Swal.fire('Error', 'La contraseña debe tener al menos 6 caracteres.', 'error');
</script>
</body>
</html>
