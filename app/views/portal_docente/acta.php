<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta Consolidada — Pestalozzi</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box }
        body { background:#fff; font-family:Arial,sans-serif; font-size:10px; color:#000 }

        /* ── Barra de herramientas (solo pantalla) ── */
        .toolbar { background:#2c3e50; color:#fff; padding:8px 16px; display:flex; gap:8px; align-items:center; flex-wrap:wrap }
        .toolbar a, .toolbar button { padding:5px 12px; border-radius:4px; font-size:12px; cursor:pointer; text-decoration:none; border:none }
        .btn-wh  { background:#fff;     color:#2c3e50 }
        .btn-gr  { background:#27ae60;  color:#fff }
        .btn-bl  { background:#2980b9;  color:#fff }
        .btn-or  { background:#e67e22;  color:#fff }
        .toolbar span { margin-left:auto; color:#aaa; font-size:11px }

        /* ── Página del acta ── */
        .acta-page { max-width:1100px; margin:12px auto; padding:10px; border:1px solid #ccc }

        /* ── Cabecera superior ── */
        .cab-top { display:flex; border:1px solid #000; margin-bottom:0 }
        .cab-logo { width:80px; border-right:1px solid #000; display:flex; align-items:center; justify-content:center; padding:6px; flex-shrink:0 }
        .cab-logo img { width:65px }
        .cab-titulo { flex:1; text-align:center; padding:6px; border-right:1px solid #000 }
        .cab-titulo h2 { font-size:12px; font-weight:bold; text-transform:uppercase; line-height:1.5 }
        .cab-titulo h3 { font-size:11px; font-weight:bold }
        .cab-anio { width:100px; text-align:center; padding:6px; font-size:11px }
        .cab-anio .anio { font-size:22px; font-weight:bold; color:#1a237e }

        /* ── Info de la institución ── */
        .cab-info { border:1px solid #000; border-top:none; display:grid; grid-template-columns:1fr 1fr; font-size:10px }
        .cab-info-col { padding:4px 8px; border-right:1px solid #000 }
        .cab-info-col:last-child { border-right:none }
        .cab-info table td { padding:1px 4px; vertical-align:top }
        .cab-info td.lbl { font-weight:bold; white-space:nowrap; color:#1a237e }

        /* ── Tabla de notas ── */
        .wrap-table { overflow-x:auto; margin-top:0 }
        table.acta { border-collapse:collapse; width:100%; font-size:9px }
        table.acta th, table.acta td { border:1px solid #555; padding:2px 4px; text-align:center; vertical-align:middle }
        table.acta thead tr:first-child th { background:#1a237e; color:#fff; font-size:9px }
        table.acta thead tr:last-child  th { background:#bbdefb; color:#000; font-size:8px }
        table.acta td.nombre { text-align:left; white-space:nowrap; font-size:9px }
        table.acta td.num    { background:#f5f5f5; color:#555 }
        .prom-mat { background:#fff9c4; font-weight:bold }
        .prom-fin { background:#c8e6c9; font-weight:bold }
        .ad { color:#1b5e20; font-weight:bold }
        .a  { color:#2e7d32; font-weight:bold }
        .b  { color:#f57f17; font-weight:bold }
        .c  { color:#b71c1c; font-weight:bold }
        .nd { color:#aaa }

        /* ── Pie de página ── */
        .pie { margin-top:16px; border:1px solid #000; padding:8px }
        .pie-leyenda { display:flex; gap:20px; font-size:9px; margin-bottom:12px }
        .pie-leyenda span { padding:2px 8px; border:1px solid #999; border-radius:3px }
        .firmas { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; margin-top:30px }
        .firma-box { text-align:center; font-size:10px }
        .firma-linea { border-top:1px solid #000; padding-top:4px; margin-top:40px }

        @media print {
            .toolbar { display:none !important }
            .acta-page { max-width:100%; margin:0; border:none; padding:5mm }
            body { font-size:9px }
        }
    </style>
</head>
<body>

<!-- ── Barra de herramientas ── -->
<div class="toolbar">
    <a class="btn-wh" href="?c=PortalDocente">← Volver</a>
    <button class="btn-bl" onclick="window.print()">🖨️ Imprimir Acta</button>
    <a class="btn-gr"
       href="?c=PortalDocente&a=actaExcel&grado_id=<?php echo $grado_id ?? 0; ?>&anio=<?php echo $anio; ?>">
        📊 Exportar CSV
    </a>
    <!-- Dropdown cartillas -->
    <div style="position:relative;display:inline-block" id="ddWrap">
        <button class="btn-or" onclick="document.getElementById('ddMenu').style.display=document.getElementById('ddMenu').style.display==='block'?'none':'block'">
            📋 Cartillas ▾
        </button>
        <div id="ddMenu" style="display:none;position:absolute;top:30px;left:0;background:#fff;border:1px solid #ccc;border-radius:4px;min-width:220px;z-index:99;box-shadow:0 4px 12px rgba(0,0,0,.2)">
            <?php foreach ($alumnos as $al): ?>
            <a target="_blank"
               href="?c=PortalDocente&a=cartilla&alumno_id=<?php echo $al['id']; ?>&anio=<?php echo $anio; ?>"
               style="display:block;padding:7px 12px;color:#333;text-decoration:none;font-size:12px;border-bottom:1px solid #eee">
                <?php echo htmlspecialchars($al['apellidos'].', '.$al['nombres']); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <span><?php echo htmlspecialchars($gradoInfo['nombre'] ?? ''); ?> &mdash; <?php echo $anio; ?></span>
</div>

<?php if (empty($alumnos) || empty($materias)): ?>
<div style="padding:40px;text-align:center;color:#666">
    <p style="font-size:16px">⚠️ No hay notas registradas para este grado en <?php echo $anio; ?>.</p>
    <a href="?c=PortalDocente" style="color:#0d6efd">← Volver al portal</a>
</div>
<?php else: ?>

<?php
    // ── Función de celda de nota ──────────────────────────────
    $celdaNota = function($v, $extra='') {
        if ($v === null) return "<td class='nd$extra'>—</td>";
        if ($v >= 18) $cls = 'ad';
        elseif ($v >= 14) $cls = 'a';
        elseif ($v >= 11) $cls = 'b';
        else              $cls = 'c';
        return "<td class='$cls$extra'>$v</td>";
    };

    // ── Índice de notas ───────────────────────────────────────
    // Ya construido en el controlador como $notasIdx[alumno_id][materia_id][periodo]
?>

<div class="acta-page">

    <!-- ── Cabecera ── -->
    <div class="cab-top">
        <div class="cab-logo">
            <img src="https://www.pestalozzi.edu.pe/wp-content/themes/wp-theme-pestalozzi/public/assets/images/logo.svg"
                 onerror="this.style.display='none'" alt="Logo">
        </div>
        <div class="cab-titulo">
            <h2>ACTA CONSOLIDADA DE EVALUACIÓN INTEGRAL</h2>
            <h3>COLEGIO PESTALOZZI &nbsp;|&nbsp; <?php echo ucfirst($gradoInfo['nivel'] ?? ''); ?> EBR</h3>
        </div>
        <div class="cab-anio">
            <div style="font-size:9px;color:#555">AÑO LECTIVO</div>
            <div class="anio"><?php echo $anio; ?></div>
        </div>
    </div>

    <!-- ── Info institución ── -->
    <div class="cab-info">
        <div class="cab-info-col">
            <table>
                <tr><td class="lbl">Institución:</td><td>Colegio Pestalozzi</td></tr>
                <tr><td class="lbl">Grado:</td><td><b><?php echo htmlspecialchars($gradoInfo['nombre']); ?></b></td></tr>
                <tr><td class="lbl">Nivel:</td><td><?php echo ucfirst($gradoInfo['nivel']); ?></td></tr>
            </table>
        </div>
        <div class="cab-info-col">
            <table>
                <tr><td class="lbl">Sección:</td><td><?php echo htmlspecialchars($gradoInfo['seccion'] ?? 'Única'); ?></td></tr>
                <tr><td class="lbl">Turno:</td><td>Mañana</td></tr>
                <tr><td class="lbl">Fecha emisión:</td><td><?php echo date('d/m/Y'); ?></td></tr>
            </table>
        </div>
    </div>

    <!-- ── Tabla principal ── -->
    <div class="wrap-table">
    <table class="acta">
        <thead>
            <tr>
                <th rowspan="3" style="width:22px;background:#37474f">N°</th>
                <th rowspan="3" style="min-width:180px;background:#37474f;text-align:left;padding-left:6px">
                    APELLIDOS Y NOMBRES
                </th>
                <?php foreach ($materias as $mat): ?>
                <th colspan="4"><?php echo strtoupper($mat['nombre']); ?></th>
                <?php endforeach; ?>
                <th rowspan="3" style="background:#2e7d32;color:#fff;min-width:42px">PROM<br>ANUAL</th>
                <th rowspan="3" style="background:#1565c0;color:#fff;min-width:32px">CAL</th>
            </tr>
            <tr>
                <?php foreach ($materias as $mat): ?>
                <th colspan="3" style="background:#1565c0;color:#fff;font-size:8px">TRIMESTRES</th>
                <th style="background:#e65100;color:#fff;font-size:8px">PROM</th>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($materias as $mat): ?>
                <th>T1</th><th>T2</th><th>T3</th>
                <th class="prom-mat" style="font-size:8px">P</th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alumnos as $i => $al):
                $sumaProms = 0; $cntProms  = 0;
                $filaNotas = '';
                foreach ($materias as $mat) {
                    $n    = $notasIdx[$al['id']][$mat['id']] ?? [];
                    $t1   = $n['T1'] ?? null;
                    $t2   = $n['T2'] ?? null;
                    $t3   = $n['T3'] ?? null;
                    $vals = array_filter([$t1,$t2,$t3], fn($v) => $v !== null);
                    $prom = count($vals) ? round(array_sum($vals)/count($vals),1) : null;
                    if ($prom !== null) { $sumaProms += $prom; $cntProms++; }
                    $filaNotas .= $celdaNota($t1) . $celdaNota($t2) . $celdaNota($t3);
                    $filaNotas .= $celdaNota($prom,' prom-mat');
                }
                $promAnual = $cntProms ? round($sumaProms/$cntProms,1) : null;
                $cal = $promAnual===null ? '—' : ($promAnual>=18 ? 'AD' : ($promAnual>=14 ? 'A' : ($promAnual>=11 ? 'B' : 'C')));
                $calCls = ['AD'=>'ad','A'=>'a','B'=>'b','C'=>'c','—'=>'nd'][$cal];
            ?>
            <tr>
                <td class="num"><?php echo $i+1; ?></td>
                <td class="nombre"><?php echo htmlspecialchars($al['apellidos'].', '.$al['nombres']); ?></td>
                <?php echo $filaNotas; ?>
                <?php echo $celdaNota($promAnual,' prom-fin'); ?>
                <td class="<?php echo $calCls; ?>"><?php echo $cal; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <!-- ── Pie ── -->
    <div class="pie">
        <div class="pie-leyenda">
            <strong>Calificativos:</strong>
            <span class="ad">AD = 18–20 &nbsp; Logro Destacado</span>
            <span class="a">A &nbsp;= 14–17 &nbsp; Logro Esperado</span>
            <span class="b">B &nbsp;= 11–13 &nbsp; En Proceso</span>
            <span class="c">C &nbsp;= 0–10 &nbsp; En Inicio</span>
        </div>
        <div class="firmas">
            <div class="firma-box">
                <div class="firma-linea">
                    Director(a) del Plantel<br>
                    <small>Colegio Pestalozzi</small>
                </div>
            </div>
            <div class="firma-box">
                <div class="firma-linea">
                    Docente a Cargo<br>
                    <small><?php echo htmlspecialchars($gradoInfo['nombre']); ?></small>
                </div>
            </div>
            <div class="firma-box">
                <div class="firma-linea">
                    Secretaría Académica<br>
                    <small>Fecha: <?php echo date('d/m/Y'); ?></small>
                </div>
            </div>
        </div>
    </div>

</div><!-- /acta-page -->

<?php endif; ?>

<script>
// Cerrar dropdown al hacer clic fuera
document.addEventListener('click', function(e) {
    if (!document.getElementById('ddWrap').contains(e.target)) {
        document.getElementById('ddMenu').style.display = 'none';
    }
});
</script>
</body>
</html>
