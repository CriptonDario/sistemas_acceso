<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Docente — Pestalozzi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body{background:#f0f4f8}
        .card-asig{border-left:4px solid #0d6efd;transition:.2s}
        .card-asig:hover{box-shadow:0 4px 16px rgba(0,0,0,.12);transform:translateY(-2px)}
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-primary shadow-sm">
    <div class="container">
        <span class="navbar-brand fw-bold">
            <i class="bi bi-mortarboard-fill me-1"></i> Portal Docente — Colegio Pestalozzi
        </span>
        <div class="d-flex gap-2 align-items-center">
            <span class="text-white d-none d-md-block">
                <?php echo htmlspecialchars($_SESSION['portal_name']); ?>
            </span>
            <a href="?c=Portal&a=index" class="btn btn-sm btn-outline-light">
                <i class="bi bi-person-circle me-1"></i> Mi Portal
            </a>
            <a href="?c=Portal&a=logout" class="btn btn-sm btn-light text-primary fw-bold">Salir</a>
        </div>
    </div>
</nav>

<?php if (isset($_GET['err']) && $_GET['err']==='sin_permiso'): ?>
    <div class="alert alert-danger m-3">❌ No tienes permiso para esa materia/grado.</div>
<?php endif; ?>

<div class="container mt-4 pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-secondary mb-0">
            <i class="bi bi-journal-check me-1"></i> Mis Materias Asignadas — <?php echo $anio; ?>
        </h4>
        <form method="GET" class="d-flex gap-2">
            <input type="hidden" name="c" value="PortalDocente">
            <input type="number" name="anio" class="form-control form-control-sm"
                   value="<?php echo $anio; ?>" style="width:80px">
            <button class="btn btn-sm btn-outline-primary">Ver</button>
        </form>
    </div>

    <?php if (!empty($asignaciones)): ?>
        <div class="row g-3">
            <?php foreach ($asignaciones as $a): ?>
            <div class="col-md-4 col-sm-6">
                <div class="card card-asig shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-<?php echo $a['nivel']==='primaria' ? 'primary' : 'warning text-dark'; ?> mb-1">
                                <?php echo $a['grado_nombre']; ?>
                            </span>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($a['materia_nombre']); ?></h5>
                        <p class="text-muted small mb-3"><?php echo ucfirst($a['nivel']); ?></p>

                        <div class="d-flex gap-1 flex-wrap">
                            <?php foreach (['T1','T2','T3'] as $per): ?>
                            <a href="?c=PortalDocente&a=notas&grado_id=<?php echo $a['grado_id']; ?>&materia_id=<?php echo $a['materia_id']; ?>&periodo=<?php echo $per; ?>&anio=<?php echo $anio; ?>"
                               class="btn btn-sm btn-outline-primary">
                                <?php echo $per; ?>
                            </a>
                            <?php endforeach; ?>
                            <a href="?c=PortalDocente&a=acta&grado_id=<?php echo $a['grado_id']; ?>&anio=<?php echo $anio; ?>"
                               class="btn btn-sm btn-outline-secondary ms-1">
                                <i class="bi bi-file-earmark-text"></i> Acta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-1"></i>
            No tienes materias asignadas para <?php echo $anio; ?>.
            Contacta al administrador para que te asigne materias.
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
