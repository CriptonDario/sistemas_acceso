<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Personal — Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: 0.3s; }
        .sidebar a:hover { background-color: #343a40; color: white; }
        .sidebar a.active { background-color: #0d6efd; color: white; border-left-color: white; }
        .sidebar i { width: 25px; }
        .table-inactive { opacity: 0.55; background-color: #f8f9fa; }
        .foto-tabla { width: 44px; height: 44px; object-fit: cover; border-radius: 50%; border: 2px solid #dee2e6; }

        /* ── CARNET IMPRESIÓN ─────────────────────────── */
        @media print {
            body * { visibility: hidden; }
            #printableArea, #printableArea * { visibility: visible; }
            #printableArea {
                position: fixed; inset: 0;
                display: flex; align-items: center; justify-content: center;
            }
            .carnet-wrap {
                width: 320px;
                border: 2.5px solid #1a1a1a;
                border-radius: 20px;
                padding: 28px 20px 20px;
                background: #fff;
                text-align: center;
                font-family: 'Segoe UI', sans-serif;
                box-shadow: none !important;
            }
            .carnet-wrap * { visibility: visible !important; }
        }
    </style>
</head>
<body class="bg-light">

<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height:100vh;overflow-y:auto;">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>👨‍🏫 Lista de Personal</h2>
            <button class="btn btn-success shadow" data-bs-toggle="modal" data-bs-target="#newEmployeeModal">
                <i class="bi bi-plus-circle"></i> Nuevo Personal
            </button>
        </div>

        <!-- Buscador -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="c" value="Employee">
                    <div class="col-auto"><label class="col-form-label fw-bold">Buscar:</label></div>
                    <div class="col-md-5">
                        <input type="text" name="q" class="form-control" placeholder="Nombre, Código o Cargo..."
                               value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
                        <?php if (!empty($_GET['q'])): ?>
                            <a href="?c=Employee" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (isset($_GET['msg'])): ?>
            <?php $msgs = ['guardado'=>'Personal registrado con éxito.','actualizado'=>'Datos actualizados correctamente.','estado_cambiado'=>'Estado actualizado.']; ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                <?php echo $msgs[$_GET['msg']] ?? 'Operación exitosa.'; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['err'])): ?>
            <?php $errs = ['codigo_duplicado'=>'El código ya existe.','campos_vacios'=>'Completa todos los campos.','error'=>'Ocurrió un error.']; ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
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
                            <th>Área</th>
                            <th>Cargo</th>
                            <th>Tipo</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($employees)): ?>
                            <?php foreach ($employees as $emp): ?>
                            <?php
                                $fotoSrc = (!empty($emp['foto']) && $emp['foto'] !== 'default.png' && file_exists(__DIR__ . '/../../../public/uploads/' . $emp['foto']))
                                    ? 'uploads/' . $emp['foto']
                                    : 'https://ui-avatars.com/api/?name=' . urlencode($emp['nombres'] . '+' . $emp['apellidos']) . '&background=0d6efd&color=fff&size=80';
                            ?>
                            <tr class="<?php echo ($emp['estado'] === 'inactivo') ? 'table-inactive' : ''; ?>">
                                <td class="ps-3">
                                    <img src="<?php echo $fotoSrc; ?>" class="foto-tabla" alt="foto">
                                </td>
                                <td>
                                    <?php echo ($emp['estado'] === 'activo')
                                        ? '<span class="badge bg-success">ACTIVO</span>'
                                        : '<span class="badge bg-danger">INACTIVO</span>'; ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo $emp['nombres'] . ' ' . $emp['apellidos']; ?></div>
                                    <small class="text-muted"><?php echo $emp['codigo']; ?></small>
                                </td>
                                <td><?php echo $emp['nombre_area']; ?></td>
                                <td><?php echo $emp['cargo']; ?></td>
                                <td><span class="badge bg-info text-dark text-capitalize"><?php echo $emp['tipo_personal']; ?></span></td>
                                <td class="text-end pe-4">
                                    <?php if ($emp['estado'] === 'activo'): ?>
                                        <button class="btn btn-sm btn-primary shadow-sm"
                                            onclick="verCarnet(
                                                '<?php echo htmlspecialchars($emp['nombres'].' '.$emp['apellidos'], ENT_QUOTES); ?>',
                                                '<?php echo $emp['codigo']; ?>',
                                                '<?php echo htmlspecialchars($emp['cargo'], ENT_QUOTES); ?>',
                                                '<?php echo htmlspecialchars($emp['nombre_area'] ?? '', ENT_QUOTES); ?>',
                                                '<?php echo $fotoSrc; ?>'
                                            )">
                                            <i class="bi bi-id-card"></i> Carnet
                                        </button>
                                    <?php endif; ?>
                                    <a href="?c=Employee&a=edit&id=<?php echo $emp['id']; ?>"
                                       class="btn btn-sm btn-warning shadow-sm"><i class="bi bi-pencil-fill"></i></a>
                                    <?php if ($emp['estado'] === 'activo'): ?>
                                        <a href="?c=Employee&a=toggle&id=<?php echo $emp['id']; ?>&status=activo"
                                           class="btn btn-sm btn-outline-danger shadow-sm"
                                           onclick="confirmarAccion(event,this.href,'¿Desactivar?','No podrá marcar asistencia.','warning')">
                                            <i class="bi bi-power"></i></a>
                                    <?php else: ?>
                                        <a href="?c=Employee&a=toggle&id=<?php echo $emp['id']; ?>&status=inactivo"
                                           class="btn btn-sm btn-outline-success shadow-sm"
                                           onclick="confirmarAccion(event,this.href,'¿Reactivar?','Podrá marcar asistencia.','success')">
                                            <i class="bi bi-power"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center p-5 text-muted">No hay personal registrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- /flex -->
</div>

<!-- ══ MODAL NUEVO PERSONAL ══════════════════════════════════════════ -->
<div class="modal fade" id="newEmployeeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill"></i> Nuevo Personal</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="?c=Employee&a=store" method="POST" enctype="multipart/form-data">
          <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombres</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Apellidos</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Código (para QR)</label>
                    <input type="text" name="employee_code" class="form-control" required placeholder="Ej: PES010">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Área</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">-- Seleccionar --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo $dept['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Cargo / Puesto</label>
                    <input type="text" name="position" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tipo de Personal</label>
                    <select name="tipo_personal" class="form-select">
                        <option value="docente">Docente</option>
                        <option value="administrativo" selected>Administrativo</option>
                        <option value="apoyo">Apoyo</option>
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

<!-- ══ MODAL CARNET ══════════════════════════════════════════════════ -->
<div class="modal fade" id="carnetModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-id-card-fill me-2"></i>Carnet Digital</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3 bg-light">

        <!-- Área imprimible -->
        <div id="printableArea">
          <div class="carnet-wrap bg-white rounded-4 border shadow-sm p-3 text-center mx-auto" style="max-width:280px;">

            <!-- Header colegio -->
            <div class="d-flex align-items-center justify-content-center gap-2 mb-3 pb-2 border-bottom">
                <img src="https://www.pestalozzi.edu.pe/wp-content/themes/wp-theme-pestalozzi/public/assets/images/logo.svg"
                     style="height:28px;" alt="Pestalozzi"
                     onerror="this.style.display='none'">
            </div>

            <!-- Foto circular -->
            <img id="carnetFoto" src="" alt="foto"
                 style="width:80px;height:80px;object-fit:cover;border-radius:50%;border:3px solid #0d6efd;margin-bottom:10px;">

            <!-- QR -->
            <img id="qrImage" src="" alt="QR"
                 style="width:160px;height:160px;display:block;margin:0 auto 10px;">

            <!-- Nombre y datos -->
            <h5 id="carnetName" class="fw-bold text-primary mb-1" style="font-size:1.05rem;"></h5>
            <p  id="carnetCargo" class="text-muted mb-1" style="font-size:0.78rem;text-transform:uppercase;"></p>
            <p  id="carnetArea"  class="text-muted mb-2" style="font-size:0.75rem;"></p>
            <span id="carnetCode" class="badge bg-dark px-3 py-2 font-monospace" style="font-size:0.9rem;"></span>

          </div>
        </div>

      </div>
      <div class="modal-footer justify-content-center bg-white border-0 gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Imprimir
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmarAccion(event, url, titulo, mensaje, icono) {
    event.preventDefault();
    Swal.fire({ title:titulo, text:mensaje, icon:icono, showCancelButton:true,
        confirmButtonColor:'#3085d6', cancelButtonColor:'#d33',
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

function verCarnet(nombre, codigo, cargo, area, fotoSrc) {
    document.getElementById('carnetName').innerText  = nombre;
    document.getElementById('carnetCargo').innerText = cargo;
    document.getElementById('carnetArea').innerText  = area;
    document.getElementById('carnetCode').innerText  = codigo;
    document.getElementById('qrImage').src   = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(codigo);
    document.getElementById('carnetFoto').src = fotoSrc;
    new bootstrap.Modal(document.getElementById('carnetModal')).show();
}
</script>
</body>
</html>
