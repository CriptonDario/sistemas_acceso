<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kiosco — Colegio Pestalozzi</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0d6efd">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        body { background-color: #1a1a2e; overflow-x: hidden; }
        #reader, #reader2 { width: 100%; border-radius: 10px; overflow: hidden; background: #000; }
        #reader video, #reader2 video { object-fit: cover; width: 100% !important; border-radius: 10px; }
        .nav-tabs .nav-link { font-size: 0.85rem; padding: 10px 6px; }
    </style>
</head>
<body>

<div class="container min-vh-100 d-flex align-items-center justify-content-center py-3">
    <div class="col-12 col-md-8 col-lg-5 col-xl-4">

        <div class="text-center text-white mb-3">
            <img src="https://www.pestalozzi.edu.pe/wp-content/themes/wp-theme-pestalozzi/public/assets/images/logo.svg"
                 style="height:40px;filter:brightness(0) invert(1)" alt="Pestalozzi"
                 onerror="this.style.display='none'">
            <p class="text-muted small mt-1 mb-0">Control de Asistencia y Acceso</p>
        </div>

        <?php
            $msg = isset($_GET['msg']) ? $_GET['msg'] : (isset($message) ? $message : '');
            $err = isset($_GET['err']) ? $_GET['err'] : (isset($error)   ? $error   : '');
        ?>
        <?php if (!empty($msg)): ?>
            <div class="alert alert-success fw-bold text-center shadow mb-3 py-2">
                <i class="bi bi-check-circle-fill me-1"></i><?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($err)): ?>
            <div class="alert alert-danger fw-bold text-center shadow mb-3 py-2">
                <i class="bi bi-exclamation-triangle-fill me-1"></i><?php echo htmlspecialchars($err); ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">

            <!-- Pestañas -->
            <div class="card-header bg-white p-0">
                <ul class="nav nav-tabs nav-fill border-0" id="kioscoTab">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold border-0 border-bottom border-3 border-primary rounded-0"
                                data-bs-toggle="tab" data-bs-target="#pane-personal" type="button">
                            <i class="bi bi-people-fill"></i> Personal
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold border-0 text-secondary"
                                data-bs-toggle="tab" data-bs-target="#pane-alumno" type="button"
                                onclick="iniciarScannerAlumno()">
                            <i class="bi bi-mortarboard-fill"></i> Alumnos
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold border-0 text-secondary"
                                data-bs-toggle="tab" data-bs-target="#pane-visitante" type="button">
                            <i class="bi bi-person-vcard"></i> Visitas
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-3">
                <div class="tab-content">

                    <!-- ── PERSONAL ── -->
                    <div class="tab-pane fade show active" id="pane-personal">
                        <div id="reader" class="mb-3 shadow-sm"></div>
                        <form action="?c=Attendance&a=register" method="POST" id="formPersonal">
                            <div class="form-floating mb-3">
                                <input type="text" name="employee_code" id="employee_code"
                                       class="form-control text-center fw-bold fs-5"
                                       placeholder="Código" autofocus>
                                <label>Código QR del Personal (Ej: PES001)</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-lg py-3 fw-bold shadow">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Registrar Asistencia
                            </button>
                        </form>
                    </div>

                    <!-- ── ALUMNOS ── -->
                    <div class="tab-pane fade" id="pane-alumno">
                        <div id="reader2" class="mb-3 shadow-sm"></div>
                        <form action="?c=Attendance&a=registerAlumno" method="POST" id="formAlumno">
                            <div class="form-floating mb-3">
                                <input type="text" name="alumno_code" id="alumno_code"
                                       class="form-control text-center fw-bold fs-5"
                                       placeholder="Código">
                                <label>Código QR del Alumno (Ej: ALU001)</label>
                            </div>
                            <button type="submit" class="btn btn-success w-100 btn-lg py-3 fw-bold shadow">
                                <i class="bi bi-mortarboard-fill me-1"></i> Registrar Asistencia Alumno
                            </button>
                        </form>
                    </div>

                    <!-- ── VISITANTES ── -->
                    <div class="tab-pane fade" id="pane-visitante">
                        <form action="?c=Visitor&a=register" method="POST">
                            <div class="btn-group w-100 mb-3">
                                <input type="radio" class="btn-check" name="action_type" id="v_in" value="entrada" checked onchange="toggleVisitorFields()">
                                <label class="btn btn-outline-success" for="v_in">🟢 Entrada</label>
                                <input type="radio" class="btn-check" name="action_type" id="v_out" value="salida"  onchange="toggleVisitorFields()">
                                <label class="btn btn-outline-danger"  for="v_out">🔴 Salida</label>
                            </div>
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input type="number" name="dni" class="form-control form-control-lg" required placeholder="DNI / Cédula">
                                </div>
                            </div>
                            <div id="visitorDetails">
                                <input type="text" name="full_name" class="form-control mb-2" placeholder="Nombre completo del visitante">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="text" name="company" class="form-control form-control-sm" placeholder="Institución / Empresa">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="reason" class="form-control form-control-sm" placeholder="Motivo">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold shadow mt-3">
                                Procesar Registro
                            </button>
                        </form>
                    </div>

                </div>
            </div>

            <div class="card-footer bg-white text-center py-2 border-0">
                <small class="text-muted">
                    <a href="?c=Auth&a=login" class="text-decoration-none">Administrador</a>
                    &nbsp;|&nbsp;
                    <a href="?c=PortalAlumno&a=index" class="text-decoration-none">Portal Alumnos</a>
                </small>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('sw.js'));
}

// ── Scanner Personal ────────────────────────────────────
let scanPersonal = false;
const scannerPersonal = new Html5QrcodeScanner("reader", { fps:10, qrbox:{width:220,height:220} }, false);
scannerPersonal.render(function(text) {
    if (scanPersonal) return;
    scanPersonal = true;
    scannerPersonal.pause();
    document.getElementById('employee_code').value = text;
    document.getElementById('formPersonal').submit();
});

// ── Scanner Alumno (lazy — solo cuando se abre la pestaña) ──
let scannerAlumno = null;
let scanAlumno    = false;

function iniciarScannerAlumno() {
    if (scannerAlumno) return; // ya iniciado
    scannerAlumno = new Html5QrcodeScanner("reader2", { fps:10, qrbox:{width:220,height:220} }, false);
    scannerAlumno.render(function(text) {
        if (scanAlumno) return;
        scanAlumno = true;
        scannerAlumno.pause();
        document.getElementById('alumno_code').value = text;
        document.getElementById('formAlumno').submit();
    });
}

// ── Visitantes ──────────────────────────────────────────
function toggleVisitorFields() {
    const isOut = document.getElementById('v_out').checked;
    const div   = document.getElementById('visitorDetails');
    div.style.display = isOut ? 'none' : 'block';
    div.querySelectorAll('input').forEach(i => i.required = !isOut);
}
</script>
</body>
</html>
