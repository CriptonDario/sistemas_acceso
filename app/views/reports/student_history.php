<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Asistencia - Alumnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: 0.3s; }
        .sidebar a:hover { background-color: #343a40; color: white; }
        .sidebar a.active { background-color: #0d6efd; color: white; border-left-color: white; }
        .sidebar i { width: 25px; }
    </style>
</head>
<body class="bg-light">

<div class="d-flex">
    <?php require_once '../app/views/layouts/sidebar.php'; ?>

    <style>
        /* Marca de agua central (usa public/images/logo_watermark.png) */
        .watermark-container { position: relative; }
        .watermark-container::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url('/public/images/logo_watermark.png');
            background-repeat: no-repeat;
            background-position: center;
            background-size: 50%;
            opacity: 0.06;
            pointer-events: none;
            z-index: 0;
        }
        .watermark-container > * { position: relative; z-index: 1; }
    </style>

    <div class="watermark-container">
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        <h2 class="mb-4 fw-bold text-secondary"><i class="bi bi-person-badge"></i> Historial de Asistencia — Alumnos</h2>

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body bg-white rounded">
                <form action="" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="c" value="Report">
                    <input type="hidden" name="a" value="studentHistory">

                    <div class="col-md-5">
                        <label class="form-label fw-bold">Seleccionar Alumno</label>
                        <select name="student_id" class="form-select">
                            <option value="">-- Seleccione un alumno --</option>
                            <?php foreach($students as $s): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo ($student_id == $s['id']) ? 'selected' : ''; ?>>
                                    <?php echo $s['nombres'] . ' ' . $s['apellidos'] . ' (' . $s['codigo'] . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Desde</label>
                        <input type="date" name="start" class="form-control" value="<?php echo $start_date; ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Hasta</label>
                        <input type="date" name="end" class="form-control" value="<?php echo $end_date; ?>">
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">
                            <i class="bi bi-filter"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mb-3 d-flex justify-content-end">
            <?php if(!empty($student_id)): ?>
            <form action="?c=Report&a=exportStudent" method="POST" class="d-flex gap-2">
                <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
                <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
                <button class="btn btn-success"><i class="bi bi-download"></i> Exportar CSV</button>
            </form>
            <?php endif; ?>
        </div>

        <div class="card shadow border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Alumno</th>
                                <th>Grado</th>
                                <th>Fecha</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($logs) && count($logs) > 0): ?>
                                <?php foreach($logs as $log): ?>
                                    <tr>
                                        <td>
                                            <?php
                                                $al = (new Student($this->db))->getById($log['alumno_id']);
                                                echo $al['nombres'] . ' ' . $al['apellidos'];
                                            ?>
                                        </td>
                                        <td><?php echo $al['nombre_grado'] ?? '--'; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($log['fecha'])); ?></td>
                                        <td><?php echo date('H:i:s', strtotime($log['hora_entrada'])); ?></td>
                                        <td><?php echo !empty($log['hora_salida']) ? date('H:i:s', strtotime($log['hora_salida'])) : '<span class="badge bg-warning text-dark">En Turno</span>'; ?></td>
                                        <td><?php echo $log['estado'] ?? '--'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center p-5 text-muted">No se encontraron registros.</td></tr>
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
</body>
</html>
