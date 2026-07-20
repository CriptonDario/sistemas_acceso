<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alumnos — Colegio Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar{min-height:100vh;background:#212529;color:#fff}
        .sidebar a{color:#adb5bd;text-decoration:none;padding:12px 20px;display:block;border-left:3px solid transparent;transition:.3s}
        .sidebar a:hover{background:#343a40;color:#fff}
        .sidebar a.active{background:#0d6efd;color:#fff;border-left-color:#fff}
        .sidebar i{width:25px}
        .table-inactive{opacity:.55;background:#f8f9fa}
        .foto-mini{width:42px;height:42px;object-fit:cover;border-radius:50%;border:2px solid #dee2e6}
        @media print{
            body *{visibility:hidden}
            #printArea,#printArea *{visibility:visible}
            #printArea{position:fixed;inset:0;display:flex;align-items:center;justify-content:center}
        }
    </style>
</head>
<body class="bg-light">
<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <div class="flex-grow-1 p-4" style="height:100vh;overflow-y:auto">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🎓 Gestión de Alumnos</h2>
            <button class="btn btn-success shadow" data-bs-toggle="modal" data-bs-target="#modalNuevo">
                <i class="bi bi-plus-circle"></i> Nuevo Alumno
            </button>
        </div>

        <!-- Buscador -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="c" value="Alumno">
                    <div class="col-md-5">
                        <input type="text" name="q" class="form-control" placeholder="Buscar por nombre, código o DNI..."
                               value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                        <?php if (!empty($_GET['q'])): ?>
                            <a href="?c=Alumno" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alertas -->
        <?php
            $alertas = ['guardado'=>['success','Alumno registrado correctamente.'],
                        'actualizado'=>['success','Datos actualizados.'],
                        'estado_cambiado'=>['info','Estado actualizado.'],
                        'codigo_duplicado'=>['danger','❌ El código ya existe.'],
                        'campos_vacios'=>['danger','❌ Completa los campos obligatorios.'],
                        'error'=>['danger','❌ Ocurrió un error.']];
            $key = $_GET['msg'] ?? ($_GET['err'] ?? null);
            if ($key && isset($alertas[$key])):
                [$tipo, $texto] = $alertas[$key];
        ?>
        <div class="alert alert-<?php echo $tipo; ?> alert-dismissible fade show shadow-sm">
            <?php echo $texto; ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                            <th>Alumno</th>
                            <th>Grado</th>
                            <th>Apoderado</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($alumnos)): ?>
                            <?php foreach ($alumnos as $al): ?>
                            <?php
                                $foto = (!empty($al['foto']) && $al['foto'] !== 'default.png'
                                         && file_exists(__DIR__.'/../../../public/uploads/'.$al['foto']))
                                    ? 'uploads/'.$al['foto']
                                    : 'https://ui-avatars.com/api/?name='.urlencode($al['nombres'].'+'.$al['apellidos']).'&background=198754&color=fff&size=80';
                            ?>
                            <tr class="<?php echo $al['estado']==='inactivo' ? 'table-inactive' : ''; ?>">
                                <td class="ps-3"><img src="<?php echo $foto; ?>" class="foto-mini" alt="foto"></td>
                                <td><?php echo $al['estado']==='activo'
                                    ? '<span class="badge bg-success">ACTIVO</span>'
                                    : '<span class="badge bg-danger">INACTIVO</span>'; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo $al['nombres'].' '.$al['apellidos']; ?></div>
                                    <small class="text-muted"><?php echo $al['codigo']; ?>
                                        <?php if ($al['dni']): ?> · DNI: <?php echo $al['dni']; ?><?php endif; ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $al['nivel']==='primaria' ? 'primary' : 'warning text-dark'; ?>">
                                        <?php echo ucfirst($al['nivel']); ?>
                                    </span><br>
                                    <small><?php echo $al['nombre_grado']; ?></small>
                                </td>
                                <td>
                                    <small><?php echo $al['nombre_apoderado'] ?? '—'; ?></small><br>
                                    <small class="text-muted"><?php echo $al['telefono_apoderado'] ?? ''; ?></small>
                                </td>
                                <td class="text-end pe-3">
                                    <?php if ($al['estado']==='activo'): ?>
                                        <button class="btn btn-sm btn-primary"
                                            onclick="verCarnet('<?php echo htmlspecialchars($al['nombres'].' '.$al['apellidos'],ENT_QUOTES); ?>',
                                                               '<?php echo $al['codigo']; ?>',
                                                               '<?php echo htmlspecialchars($al['nombre_grado'],ENT_QUOTES); ?>',
                                                               '<?php echo $foto; ?>')">
                                            <i class="bi bi-id-card"></i>
                                        </button>
                                    <?php endif; ?>
                                    <a href="?c=Alumno&a=edit&id=<?php echo $al['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-fill"></i></a>
                                    <?php if ($al['estado']==='activo'): ?>
                                        <a href="?c=Alumno&a=toggle&id=<?php echo $al['id']; ?>&status=activo"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="conf(event,this.href,'¿Desactivar alumno?','warning')"><i class="bi bi-power"></i></a>
                                    <?php else: ?>
                                        <a href="?c=Alumno&a=toggle&id=<?php echo $al['id']; ?>&status=inactivo"
                                           class="btn btn-sm btn-outline-success"
                                           onclick="conf(event,this.href,'¿Reactivar alumno?','success')"><i class="bi bi-power"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center p-5 text-muted">No hay alumnos registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL NUEVO ALUMNO -->
<div class="modal fade" id="modalNuevo" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill"></i> Nuevo Alumno</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="?c=Alumno&a=store" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Nombres *</label>
                <input type="text" name="nombres" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Apellidos *</label>
                <input type="text" name="apellidos" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Código QR *</label>
                <input type="text" name="codigo" class="form-control" required placeholder="Ej: ALU010">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">DNI</label>
                <input type="text" name="dni" class="form-control" placeholder="Opcional">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Fecha de Nacimiento</label>
                <input type="date" name="fecha_nacimiento" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Grado *</label>
                <select name="grado_id" class="form-select" required>
                    <option value="">-- Seleccionar --</option>
                    <optgroup label="Primaria">
                    <?php foreach ($grados as $g): if ($g['nivel']==='primaria'): ?>
                        <option value="<?php echo $g['id']; ?>"><?php echo $g['nombre']; ?></option>
                    <?php endif; endforeach; ?>
                    </optgroup>
                    <optgroup label="Secundaria">
                    <?php foreach ($grados as $g): if ($g['nivel']==='secundaria'): ?>
                        <option value="<?php echo $g['id']; ?>"><?php echo $g['nombre']; ?></option>
                    <?php endif; endforeach; ?>
                    </optgroup>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Correo (para portal)</label>
                <input type="email" name="correo" class="form-control" placeholder="alumno@pestalozzi.edu.pe">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Nombre del Apoderado</label>
                <input type="text" name="nombre_apoderado" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Teléfono Apoderado <small class="text-muted">(con código país)</small></label>
                <input type="text" name="telefono_apoderado" class="form-control" placeholder="51987654321">
            </div>
            <div class="col-md-9">
                <label class="form-label fw-bold">
                    <i class="bi bi-whatsapp text-success"></i> API Key CallMeBot del Apoderado
                </label>
                <input type="text" name="wa_apikey_apoderado" class="form-control" placeholder="Ej: 123456 (obtener instrucciones abajo)">
                <small class="text-muted">El apoderado debe enviar <b>"I allow callmebot to send me messages"</b> al <b>+34 644 59 72 64</b> por WhatsApp y recibirá su API key.</small>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Foto <small class="text-muted">(máx 3MB)</small></label>
                <input type="file" name="foto" class="form-control" accept="image/*" onchange="prevFoto(this,'pv1')">
                <img id="pv1" class="mt-1 rounded-circle d-none" style="width:50px;height:50px;object-fit:cover">
            </div>
            <div class="col-12">
                <div class="alert alert-info small mb-0">
                    <i class="bi bi-info-circle"></i> Contraseña portal por defecto: <strong>123456</strong>
                </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success fw-bold">Guardar Alumno</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL CARNET -->
<div class="modal fade" id="modalCarnet" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-success text-white border-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-id-card-fill me-1"></i> Carnet Alumno</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3 bg-light">
        <div id="printArea">
          <div class="bg-white rounded-4 border shadow-sm p-3 text-center mx-auto" style="max-width:260px">
            <div class="mb-2 pb-2 border-bottom">
                <img src="https://www.pestalozzi.edu.pe/wp-content/themes/wp-theme-pestalozzi/public/assets/images/logo.svg"
                     style="height:24px" alt="Pestalozzi" onerror="this.style.display='none'">
                <small class="d-block text-muted" style="font-size:.65rem">COLEGIO PESTALOZZI</small>
            </div>
            <img id="cFoto" src="" style="width:72px;height:72px;object-fit:cover;border-radius:50%;border:3px solid #198754;margin:6px auto;display:block">
            <img id="cQr"   src="" style="width:150px;height:150px;display:block;margin:6px auto">
            <h6 id="cNombre" class="fw-bold text-success mb-1" style="font-size:.95rem"></h6>
            <p  id="cGrado"  class="text-muted mb-2" style="font-size:.75rem;text-transform:uppercase"></p>
            <span id="cCodigo" class="badge bg-dark px-3 py-1 font-monospace"></span>
            <p class="text-muted mt-2 mb-0" style="font-size:.65rem">Presenta este código en el kiosco</p>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-white border-0 justify-content-center gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Imprimir
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function conf(e, url, titulo, icono) {
    e.preventDefault();
    Swal.fire({title:titulo,icon:icono,showCancelButton:true,confirmButtonText:'Sí',cancelButtonText:'No'})
       .then(r => { if(r.isConfirmed) window.location.href=url; });
}
function prevFoto(input, id) {
    if (input.files && input.files[0]) {
        const r = new FileReader();
        r.onload = e => { const img=document.getElementById(id); img.src=e.target.result; img.classList.remove('d-none'); };
        r.readAsDataURL(input.files[0]);
    }
}
function verCarnet(nombre, codigo, grado, foto) {
    document.getElementById('cNombre').innerText = nombre;
    document.getElementById('cGrado').innerText  = grado;
    document.getElementById('cCodigo').innerText = codigo;
    document.getElementById('cQr').src   = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data='+encodeURIComponent(codigo);
    document.getElementById('cFoto').src = foto;
    new bootstrap.Modal(document.getElementById('modalCarnet')).show();
}
</script>
</body>
</html>
