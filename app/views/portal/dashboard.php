<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        /* =================================================
           ESTILOS DE IMPRESIÓN (CORREGIDOS)
           ================================================= */
        @media print {
            @page { margin: 0; size: auto; }
            body { margin: 0; padding: 0; }
            body * { visibility: hidden; }
            .navbar, .btn, .card-footer, .card-header, .col-md-8 { display: none !important; }
            #printableArea, #printableArea * { visibility: visible; }
            #printableArea {
                position: fixed; inset: 0;
                display: flex; align-items: center; justify-content: center;
            }
            .carnet-wrap {
                width: 280px !important;
                border: 2.5px solid #111 !important;
                border-radius: 20px !important;
                padding: 20px !important;
                background: #fff !important;
            }
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <span class="navbar-brand fw-bold"><i class="bi bi-mortarboard-fill"></i> Portal Personal — Colegio Pestalozzi</span>
    <div class="d-flex align-items-center gap-2">
        <span class="text-white me-2 d-none d-md-block">Hola, <?php echo $empName; ?></span>
        <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#passModal">
            <i class="bi bi-key-fill"></i>
        </button>
        <a href="?c=Portal&a=logout" class="btn btn-sm btn-light text-primary fw-bold">Salir</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <div class="row">
        
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 text-center h-100">
                <div class="card-header bg-white fw-bold text-muted">MI CREDENCIAL</div>
                
                <div class="card-body d-flex flex-column align-items-center justify-content-center p-3">
                    <div id="printableArea">
                      <div class="carnet-wrap bg-white rounded-4 border p-3 text-center" style="max-width:260px;">

                        <!-- Logo -->
                        <div class="mb-2 pb-2 border-bottom">
                            <img src="https://www.pestalozzi.edu.pe/wp-content/themes/wp-theme-pestalozzi/public/assets/images/logo.svg"
                                 style="height:26px;" alt="Pestalozzi" onerror="this.style.display='none'">
                        </div>

                        <!-- Foto del empleado -->
                        <img id="portalFoto" src="<?php echo $fotoPortal; ?>" alt="foto"
                             style="width:80px;height:80px;object-fit:cover;border-radius:50%;border:3px solid #0d6efd;margin:8px auto;display:block;">

                        <!-- QR -->
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode($empCode); ?>"
                             id="qrImage" class="img-fluid border p-1 rounded mb-2" style="width:150px;" alt="QR">

                        <h6 class="fw-bold text-primary mb-1"><?php echo $empName; ?></h6>
                        <span class="badge bg-dark px-3 py-1 font-monospace"><?php echo $empCode; ?></span>
                        <p class="text-muted small mt-2 mb-0">Presenta este código en el kiosco.</p>
                      </div>
                    </div>
                </div>
                
                <div class="card-footer bg-white border-0">
                    <button class="btn btn-outline-primary w-100 mb-2" onclick="window.print()">
                        <i class="bi bi-printer"></i> Imprimir Carnet
                    </button>
                    <!-- Acceso al Portal Docente si es docente -->
                    <?php
                        // Verificar si el empleado tiene asignaciones como docente
                        $esDocente = false;
                        try {
                            $stmtDc = (new Database())->getConnection()->prepare(
                                "SELECT COUNT(*) FROM docente_materia_grado
                                 WHERE personal_id=:pid AND anio=:anio AND activo=1"
                            );
                            $stmtDc->execute([':pid'=>$empId, ':anio'=>date('Y')]);
                            $esDocente = $stmtDc->fetchColumn() > 0;
                        } catch(Exception $e) {}
                    ?>
                    <?php if ($esDocente): ?>
                    <a href="?c=PortalDocente" class="btn btn-success w-100 fw-bold">
                        <i class="bi bi-journal-check me-1"></i> Portal Docente — Mis Notas
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold text-muted d-flex justify-content-between">
                    <span>MIS ASISTENCIAS (Este Mes)</span>
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr><th class="ps-3">Fecha</th><th>Entrada</th><th>Salida</th><th>Horas</th></tr>
                            </thead>
                            <tbody>
                                <?php if(count($myLogs) > 0): ?>
                                    <?php foreach($myLogs as $log): ?>
                                    <tr>
                                        <td class="ps-3"><?php echo date('d/m/Y', strtotime($log['fecha'])); ?></td>
                                        <td class="text-success fw-bold"><?php echo date('H:i', strtotime($log['hora_entrada'])); ?></td>
                                        <td class="text-danger"><?php echo ($log['hora_salida']) ? date('H:i', strtotime($log['hora_salida'])) : 'En curso'; ?></td>
                                        <td><?php if($log['hora_salida']) { $diff = (new DateTime($log['hora_entrada']))->diff(new DateTime($log['hora_salida'])); echo $diff->format('%Hh %Im'); } else { echo '--'; } ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center p-4 text-muted">No tienes registros este mes.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="passModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock"></i> Cambiar Contraseña</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="?c=Portal&a=change_password" method="POST">
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
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const err = urlParams.get('err');

    if(msg === 'pass_actualizada') Swal.fire('¡Éxito!', 'Contraseña actualizada.', 'success');
    if(err === 'pass_incorrecta') Swal.fire('Error', 'Contraseña actual incorrecta.', 'error');
    if(err === 'no_coinciden') Swal.fire('Error', 'Las nuevas contraseñas no coinciden.', 'error');
</script>

</body>
</html>