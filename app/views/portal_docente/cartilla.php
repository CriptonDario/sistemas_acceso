<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Informe de Progreso — <?php echo htmlspecialchars($alumno['apellidos'].' '.$alumno['nombres']); ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box }
body { background:#f2f4f8; font-family:"Segoe UI", Arial, sans-serif; font-size:8.6pt; color:#17202a }

/* ── Toolbar ── */
.toolbar { background:#14213d; color:#fff; padding:7px 14px; display:flex; gap:8px; align-items:center }
.toolbar a, .toolbar button { padding:5px 14px; border-radius:4px; font-size:11px; cursor:pointer; text-decoration:none; border:none; font-weight:bold }
.btn-w { background:#fff; color:#14213d }
.btn-b { background:#2563eb; color:#fff }

/* ── Hoja A4 ── */
.hoja {
    width: 210mm;
    min-height: 297mm;
    margin: 10px auto;
    background: linear-gradient(180deg,#ffffff 0%,#fcfdff 100%);
    padding: 6mm 7mm;
    border: 1px solid #cfd8dc;
    box-shadow: 0 2px 16px rgba(0,0,0,.14);
}

/* ── Cabecera ── */
.cab { display:flex; align-items:stretch; border:1.5px solid #14213d; margin-bottom:0; background:linear-gradient(90deg,#f8fbff 0%,#eef5ff 100%) }
.cab-escudo { width:58px; border-right:1px solid #14213d; display:flex; align-items:center; justify-content:center; padding:4px; flex-shrink:0; background:#fff }
.cab-escudo img { width:46px }
.cab-centro { flex:1; padding:5px 10px; border-right:1px solid #14213d }
.cab-titulo { font-size:9.3pt; font-weight:bold; text-align:center; text-transform:uppercase; line-height:1.4; color:#14213d }
.cab-logo { width:70px; display:flex; align-items:center; justify-content:center; padding:4px; flex-shrink:0; background:#fff }
.cab-logo img { max-width:58px; max-height:55px; object-fit:contain }

/* ── Ficha institucional ── */
.ficha { border:1.5px solid #14213d; border-top:none; background:#fcfdff }
.ficha table { width:100%; border-collapse:collapse }
.ficha td { border:1px solid #cfd8dc; padding:2px 5px; font-size:8pt; vertical-align:middle }
.ficha .td-lbl { background:#e8eefc; font-weight:bold; width:130px; color:#1d4ed8 }
.ficha .td-lbl2{ background:#e8eefc; font-weight:bold; width:90px; color:#1d4ed8 }
.ficha .nivel  { font-size:13.5pt; font-weight:bold; color:#14213d; padding:6px 8px }

/* ── Foto del alumno ── */
.foto-box { border:1px solid #bfc9d1; text-align:center; padding:2px; background:#fff }
.foto-box img { width:58px; height:68px; object-fit:cover }

/* ── Resumen destacado ── */
.resumen-box { display:grid; grid-template-columns:repeat(3,1fr); gap:6px; margin:4mm 0 2mm }
.resumen-item { border:1px solid #d0d7de; border-left:4px solid #2563eb; padding:6px 8px; background:#f8fbff }
.resumen-item span { display:block; font-size:7.2pt; text-transform:uppercase; color:#64748b; letter-spacing:.04em }
.resumen-item strong { display:block; font-size:8.8pt; color:#0f172a; margin-top:1px }

/* ── Separador de sección ── */
.sep { height:4px; background:linear-gradient(90deg,#1a237e,#1565c0,#1a237e); margin:4px 0 }

/* ── Layout de dos columnas ── */
.dos-col { display:grid; grid-template-columns:1fr 1fr; gap:3mm; margin-top:3mm; align-items:start }
.dos-col > div { min-width:0 }

/* ── Tabla de notas por área ── */
.area-table { width:100%; border-collapse:collapse; font-size:7.4pt; margin-bottom:3mm }
.area-table th, .area-table td { border:1px solid #b8c2cc; padding:2px 3px; vertical-align:middle }
.th-area { background:#14213d; color:#fff; font-weight:bold; font-size:7pt; text-align:center; width:72px }
.th-comp { background:#1d4ed8; color:#fff; font-weight:bold; font-size:7pt }
.th-per  { background:#2563eb; color:#fff; font-weight:bold; text-align:center; font-size:7pt; width:18px }
.th-cal-a{ background:#0f766e; color:#fff; font-weight:bold; text-align:center; font-size:6.7pt; width:32px; line-height:1.2 }

.td-area { background:#eef4ff; font-weight:bold; color:#14213d; text-align:center; vertical-align:middle; font-size:7.2pt }
.td-comp { font-size:7pt; text-align:left; line-height:1.25; color:#334155 }
.td-per  { text-align:center; background:#fffdf1; width:18px; font-size:8pt }
.td-cal-a{ text-align:center; background:#ecfdf5; font-weight:bold; width:32px }

.ad { color:#1b5e20; font-weight:bold }
.a  { color:#2e7d32 }
.b  { color:#e65100; font-weight:bold }
.c  { color:#b71c1c; font-weight:bold }
.nd { color:#aaa }

/* ── Conclusiones descriptivas por periodo ── */
.concl-table { width:100%; border-collapse:collapse; font-size:8pt; margin-bottom:3mm; table-layout:fixed }
.concl-table th { background:#37474f; color:#fff; padding:3px 5px; font-size:7.5pt }
.concl-table td { border:1px solid #888; padding:4px 5px; font-size:8pt }
.concl-table .td-per { background:#eceff1; font-weight:bold; text-align:center; width:55px }
.concl-table .td-comp{ background:#f3e5f5; width:130px; font-size:7.5pt }
.concl-table .td-desc{ background:#fff; height:20px }

/* ── Asistencia ── */
.asist-table { width:100%; border-collapse:collapse; font-size:8pt; margin-bottom:3mm; table-layout:fixed }
.asist-table th { background:#4a148c; color:#fff; padding:3px 5px; text-align:center; font-size:7.5pt }
.asist-table td { border:1px solid #888; padding:3px 5px; text-align:center; font-size:8pt }
.asist-table .td-per { background:#ede7f6; font-weight:bold; text-align:left; width:80px }

/* ── Escala CNEB ── */
.escala-box { border:1.5px solid #1a237e; padding:4px; background:#f8f9ff }
.escala-title{ background:#1a237e; color:#fff; font-weight:bold; text-align:center; font-size:8pt; padding:3px }
.escala-table { width:100%; border-collapse:collapse; font-size:7pt }
.escala-table td { border:1px solid #bbb; padding:3px 4px; vertical-align:top }
.e-ad { background:#1b5e20; color:#fff; font-weight:bold; text-align:center; width:22px; font-size:10pt }
.e-a  { background:#2e7d32; color:#fff; font-weight:bold; text-align:center; width:22px; font-size:10pt }
.e-b  { background:#e65100; color:#fff; font-weight:bold; text-align:center; width:22px; font-size:10pt }
.e-c  { background:#b71c1c; color:#fff; font-weight:bold; text-align:center; width:22px; font-size:10pt }

/* ── Firmas ── */
.firmas { display:flex; justify-content:space-around; margin-top:20mm; padding-top:0 }
.firma  { text-align:center; width:40% }
.f-lin  { border-top:1px solid #000; padding-top:3px; font-size:8pt }

/* ── Impresión ── */
@media print {
    .toolbar { display:none !important }
    .obs-panel { display:none !important }
    body { background:#fff }
    .hoja { margin:0; border:none; box-shadow:none; padding:5mm 6mm; width:100% }
    .dos-col { break-inside:avoid }
    @page { size:A4; margin:0 }
}
</style>
</head>
<body>

<!-- Toolbar -->
<div class="toolbar">
    <a class="btn-w" href="javascript:history.back()">← Volver</a>
    <button class="btn-b" onclick="window.print()">🖨 Imprimir / Descargar PDF</button>
    <span style="margin-left:auto;font-size:11px;color:#90caf9">
        <?php echo htmlspecialchars($alumno['apellidos'].', '.$alumno['nombres']); ?> —
        <?php echo htmlspecialchars($alumno['grado_nombre']); ?> — <?php echo $anio; ?>
    </span>
</div>

<div class="obs-panel" style="max-width:210mm;margin:10px auto 0;padding:14px;background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);border:1px solid #d0d7de;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.08)">
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'observaciones_guardadas'): ?>
        <div style="margin-bottom:10px;padding:8px 10px;border-radius:6px;background:#dcfce7;color:#166534;font-size:11px;font-weight:bold">
            ✅ Observaciones guardadas correctamente.
        </div>
    <?php endif; ?>
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:10px">
        <div>
            <div style="font-size:12px;font-weight:bold;color:#1a237e">Observaciones del docente</div>
            <div style="font-size:11px;color:#5f6b7a">Registra las observaciones por materia y período para este estudiante.</div>
        </div>
        <span style="font-size:11px;color:#6c757d"><?php echo htmlspecialchars($alumno['apellidos'].', '.$alumno['nombres']); ?></span>
    </div>
    <form action="?c=PortalDocente&a=guardarObservacionesCartilla" method="POST">
        <input type="hidden" name="alumno_id" value="<?php echo $alumno_id; ?>">
        <input type="hidden" name="anio" value="<?php echo $anio; ?>">
        <?php $observacionesPorMateria = isset($observacionesPorMateria) && is_array($observacionesPorMateria) ? $observacionesPorMateria : []; ?>
        <?php if (!empty($observacionesPorMateria)): ?>
            <?php foreach ($observacionesPorMateria as $materiaId => $datos): ?>
                <div style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-bottom:10px;background:#f8fafc">
                    <div style="font-weight:bold;color:#0f172a;margin-bottom:8px">
                        <?php echo htmlspecialchars($datos['materia'] ?? 'Materia'); ?>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px">
                        <?php foreach (['T1','T2','T3'] as $periodo): ?>
                            <label style="font-size:11px;color:#475569">
                                <div style="margin-bottom:4px;font-weight:bold"><?php echo $periodo; ?></div>
                                <textarea name="obs[<?php echo $materiaId; ?>][<?php echo $periodo; ?>]"
                                          rows="3"
                                          style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:6px;font-size:11px;min-height:70px"><?php echo htmlspecialchars($datos[$periodo] ?? ''); ?></textarea>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="padding:12px;border:1px dashed #cbd5e1;border-radius:8px;background:#f8fafc;color:#64748b;font-size:11px">
                No hay observaciones registradas aún para este estudiante.
            </div>
        <?php endif; ?>
        <div style="text-align:right;margin-top:10px">
            <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:6px;padding:8px 14px;font-weight:bold;cursor:pointer">
                Guardar observaciones
            </button>
        </div>
    </form>
</div>

<div class="hoja">

<?php
    // ── Foto ───────────────────────────────────────────────────────────────
    $fotoSrc = (!empty($alumno['foto']) && $alumno['foto'] !== 'default.png'
                && file_exists(__DIR__.'/../../../public/uploads/'.$alumno['foto']))
        ? 'uploads/'.$alumno['foto']
        : 'https://ui-avatars.com/api/?name='.urlencode($alumno['nombres'].'+'.$alumno['apellidos'])
          .'&background=1a237e&color=fff&size=120';

    // ── Función calificativo ───────────────────────────────────────────────
    $cal = function($v) {
        if ($v === null) return ['—','nd'];
        if ($v >= 18) return ['AD','ad'];
        if ($v >= 14) return ['A','a'];
        if ($v >= 11) return ['B','b'];
        return ['C','c'];
    };
    $tdNota = function($v) use ($cal) {
        [$label,$cls] = $cal($v);
        return "<td class='td-per'><span class='$cls'>$label</span></td>";
    };
?>

<!-- ══ CABECERA ══════════════════════════════════════════════════════════ -->
<div class="cab">
    <div class="cab-escudo">
        <!-- Escudo del Perú (SVG inline simple) -->
        <svg viewBox="0 0 80 100" width="46" height="58" xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="40" cy="55" rx="36" ry="42" fill="#d32f2f" stroke="#000" stroke-width="2"/>
            <ellipse cx="40" cy="55" rx="32" ry="38" fill="#fff"/>
            <rect x="8" y="35" width="32" height="40" fill="#d32f2f"/>
            <rect x="40" y="35" width="32" height="40" fill="#d32f2f"/>
            <rect x="22" y="35" width="36" height="40" fill="#fff"/>
            <text x="40" y="58" text-anchor="middle" fill="#1a237e" font-size="7" font-weight="bold">PERÚ</text>
        </svg>
    </div>
    <div class="cab-centro">
        <div class="cab-titulo">
            INFORME DE PROGRESO DEL APRENDIZAJE DEL ESTUDIANTE — <?php echo $anio; ?>
        </div>
        <div style="margin-top:4px; display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:2px 10px; font-size:8.2pt; color:#334155;">
            <div><span style="font-weight:bold;color:#1a237e">DRE:</span> Lima</div>
            <div><span style="font-weight:bold;color:#1a237e">UGEL:</span> Colegio Pestalozzi</div>
            <div><span style="font-weight:bold;color:#1a237e">Nivel:</span> <?php echo ucfirst($alumno['nivel'] ?? 'Secundaria'); ?></div>
            <div><span style="font-weight:bold;color:#1a237e">Código Modular:</span> <?php echo $alumno['codigo']; ?></div>
        </div>
    </div>
    <div class="cab-logo">
        <img src="https://www.pestalozzi.edu.pe/wp-content/themes/wp-theme-pestalozzi/public/assets/images/logo.svg"
             onerror="this.style.display='none'" alt="Logo">
    </div>
</div>

<!-- Ficha del alumno -->
<div class="ficha">
<table>
    <tr>
        <td class="td-lbl">Institución Educativa:</td>
        <td colspan="3">Colegio Pestalozzi</td>
        <td rowspan="4" style="width:65px;text-align:center;vertical-align:middle">
            <div class="foto-box">
                <img src="<?php echo $fotoSrc; ?>" alt="foto">
            </div>
        </td>
    </tr>
    <tr>
        <td class="td-lbl">Grado:</td>
        <td><?php echo htmlspecialchars($alumno['grado_nombre']); ?></td>
        <td class="td-lbl2">Sección:</td>
        <td><?php echo htmlspecialchars($alumno['seccion'] ?? 'Única'); ?></td>
    </tr>
    <tr>
        <td class="td-lbl">Apellidos y Nombres:</td>
        <td colspan="3"><b><?php echo htmlspecialchars($alumno['apellidos'].', '.$alumno['nombres']); ?></b></td>
    </tr>
    <tr>
        <td class="td-lbl">Código del Estudiante:</td>
        <td><?php echo $alumno['codigo']; ?></td>
        <td class="td-lbl2">DNI:</td>
        <td><?php echo $alumno['dni'] ?? ''; ?></td>
    </tr>
</table>
</div>

<div class="resumen-box">
    <div class="resumen-item">
        <span>Periodo lectivo</span>
        <strong><?php echo $anio; ?></strong>
    </div>
    <div class="resumen-item">
        <span>Nivel</span>
        <strong><?php echo htmlspecialchars(ucfirst($alumno['nivel'] ?? 'Secundaria')); ?></strong>
    </div>
    <div class="resumen-item">
        <span>Estado del estudiante</span>
        <strong><?php echo htmlspecialchars(ucfirst($alumno['estado'] ?? 'Activo')); ?></strong>
    </div>
</div>

<div class="sep"></div>

<?php
// ══ PREPARAR NOTAS ════════════════════════════════════════════════════════
// $resumenNotas viene del controlador: [materia => [T1, T2, T3, promedio]]
// Las "competencias" en nuestro sistema son las materias completas.
// Si en el futuro hay competencias por área, se adapta.

$notasPorArea = $resumenNotas ?? [];
$mitad = (int)ceil(count($notasPorArea) / 2);
$colIzq = array_slice($notasPorArea, 0, $mitad, true);
$colDer = array_slice($notasPorArea, $mitad, null, true);

// Función para renderizar el bloque de una materia/área
$renderArea = function($materia, $notas) use ($cal, $tdNota) {
    $finalValue = null;
    if (isset($notas['FINAL']) && $notas['FINAL'] !== null && $notas['FINAL'] !== '') {
        $finalValue = $notas['FINAL'];
    } elseif (isset($notas['promedio']) && $notas['promedio'] !== null && $notas['promedio'] !== '') {
        $finalValue = $notas['promedio'];
    } elseif (isset($notas['T1'], $notas['T2'], $notas['T3'])) {
        $vals = array_filter([$notas['T1'], $notas['T2'], $notas['T3']], function($v) { return $v !== null && $v !== ''; });
        if (!empty($vals)) {
            $finalValue = round(array_sum($vals) / count($vals), 1);
        }
    }
    [$calAnual, $clsAnual] = $cal($finalValue);
    ob_start();
?>
<table class="area-table">
    <thead>
        <tr>
            <th class="th-area">ÁREA / MATERIA</th>
            <th class="th-comp">DESCRIPCIÓN</th>
            <th class="th-per">T1</th>
            <th class="th-per">T2</th>
            <th class="th-per">T3</th>
            <th class="th-cal-a">FINAL</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="td-area"><?php echo htmlspecialchars($materia); ?></td>
            <td class="td-comp">Desarrollo de aprendizajes esperados según el plan curricular.</td>
            <?php echo $tdNota($notas['T1']); ?>
            <?php echo $tdNota($notas['T2']); ?>
            <?php echo $tdNota($notas['T3']); ?>
            <td class="td-cal-a <?php echo $clsAnual; ?>">
                <span class="<?php echo $clsAnual; ?>"><?php echo $calAnual; ?></span>
            </td>
        </tr>
    </tbody>
</table>
<?php
    return ob_get_clean();
};
?>

<!-- ══ DOS COLUMNAS DE ÁREAS ══════════════════════════════════════════════ -->
<div class="dos-col">
    <div>
        <?php foreach ($colIzq as $materia => $notas): ?>
            <?php echo $renderArea($materia, $notas); ?>
        <?php endforeach; ?>
    </div>
    <div>
        <?php foreach ($colDer as $materia => $notas): ?>
            <?php echo $renderArea($materia, $notas); ?>
        <?php endforeach; ?>

        <?php if (empty($colDer)): ?>
        <div style="padding:10px;text-align:center;color:#aaa;border:1px dashed #ccc">
            (Sin más áreas)
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="sep"></div>

<!-- ══ CONCLUSIONES + ASISTENCIA + ESCALA ════════════════════════════════ -->
<div class="dos-col" style="margin-top:3mm; gap:4mm">

    <!-- Columna izquierda: conclusiones por periodo -->
    <div>
        <table class="concl-table">
            <thead>
                <tr>
                    <th style="width:40px">Período</th>
                    <th>Competencia</th>
                    <th>Conclusión descriptiva</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($p = 1; $p <= 3; $p++): ?>
                <tr>
                    <td class="td-per" style="text-align:center"><?php echo $p; ?></td>
                    <td class="td-comp"></td>
                    <td class="td-desc"></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- Conclusión descriptiva por período (tabla simple) -->
        <table class="concl-table" style="margin-top:3mm">
            <thead>
                <tr>
                    <th style="width:40px">Período</th>
                    <th colspan="2">Conclusión descriptiva por período</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($p = 1; $p <= 3; $p++): ?>
                <tr>
                    <td class="td-per" style="text-align:center"><?php echo $p; ?></td>
                    <td colspan="2" class="td-desc" style="height:22px"></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <!-- Columna derecha: asistencia + escala -->
    <div>
        <!-- Resumen de asistencia -->
        <p style="font-weight:bold;font-size:8.5pt;margin-bottom:2px;color:#1a237e">Resumen de asistencia del estudiante</p>
        <table class="asist-table">
            <thead>
                <tr>
                    <th rowspan="2">Período</th>
                    <th colspan="2">Inasistencias</th>
                    <th colspan="2">Tardanzas</th>
                </tr>
                <tr>
                    <th>Justificadas</th>
                    <th>Injustificadas</th>
                    <th>Justificadas</th>
                    <th>Injustificadas</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $perNom = [1=>'1er Trim.',2=>'2do Trim.',3=>'3er Trim.'];
                    for ($p = 1; $p <= 3; $p++):
                        $dato = null;
                        foreach ($asistencias as $as) {
                            if (intval($as['periodo']) === $p) { $dato = $as; break; }
                        }
                ?>
                <tr>
                    <td class="td-per" style="text-align:left"><?php echo $perNom[$p]; ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><?php echo $dato ? $dato['tardanzas'] : ''; ?></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- Escala CNEB -->
        <div class="escala-box" style="margin-top:4mm">
            <div class="escala-title">ESCALA DE CALIFICACIONES DEL CNEB</div>
            <table class="escala-table">
                <tr>
                    <td class="e-ad">AD</td>
                    <td>
                        <b>Logro destacado</b><br>
                        El estudiante evidencia un nivel superior a lo esperado respecto a la competencia.
                        Esto quiere decir que demuestra aprendizajes que van más allá del nivel esperado.
                    </td>
                </tr>
                <tr>
                    <td class="e-a">A</td>
                    <td>
                        <b>Logro esperado</b><br>
                        El estudiante evidencia el nivel esperado respecto a la competencia, demostrando
                        manejo satisfactorio en todas las tareas propuestas y en el tiempo programado.
                    </td>
                </tr>
                <tr>
                    <td class="e-b">B</td>
                    <td>
                        <b>En proceso</b><br>
                        El estudiante está próximo o cerca del nivel esperado respecto a la competencia,
                        para lo cual requiere acompañamiento durante un tiempo razonable para lograrlo.
                    </td>
                </tr>
                <tr>
                    <td class="e-c">C</td>
                    <td>
                        <b>En inicio</b><br>
                        El estudiante muestra un progreso mínimo en una competencia de acuerdo al
                        nivel esperado. Evidencia con frecuencia dificultades en el desarrollo de las
                        tareas, por lo que necesita mayor tiempo de acompañamiento e intervención.
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="sep" style="margin-top:4mm"></div>

<!-- ══ FIRMAS ══════════════════════════════════════════════════════════== -->
<div class="firmas">
    <div class="firma">
        <div class="f-lin">
            Firma y sello del Docente o Tutor(a)<br>
            <small style="color:#555"><?php echo date('d/m/Y'); ?></small>
        </div>
    </div>
    <div class="firma">
        <div class="f-lin">
            Firma y sello del Director(a)<br>
            <small style="color:#555">Colegio Pestalozzi</small>
        </div>
    </div>
</div>

</div><!-- /hoja -->
</body>
</html>
