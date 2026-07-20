<?php
require_once '../app/config/db.php';
require_once '../app/models/Nota.php';
require_once '../app/models/AsignacionDocente.php';

class PortalDocenteController {
    private $db;
    private $notaModel;
    private $asignModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['portal_role']) || $_SESSION['portal_role'] !== 'empleado') {
            header("Location: ?c=Auth&a=login"); exit;
        }
        $database = new Database();
        $this->db        = $database->getConnection();
        $this->notaModel = new Nota($this->db);
        $this->asignModel = new AsignacionDocente($this->db);
    }

    // 1. Dashboard — ver asignaciones del docente
    public function index() {
        $docenteId    = $_SESSION['portal_id'];
        $anio         = intval($_GET['anio'] ?? date('Y'));
        $asignaciones = $this->asignModel->getByDocente($docenteId, $anio);
        require_once '../app/views/portal_docente/index.php';
    }

    // 2. Ver/ingresar notas de una materia+grado
    public function notas() {
        $docenteId  = $_SESSION['portal_id'];
        $grado_id   = intval($_GET['grado_id']  ?? 0);
        $materia_id = intval($_GET['materia_id'] ?? 0);
        $periodo    = $_GET['periodo']           ?? 'T1';
        $anio       = intval($_GET['anio']       ?? date('Y'));
        if (!in_array($periodo, ['T1','T2','T3'])) $periodo = 'T1';

        if (!$this->asignModel->tienePermiso($docenteId, $materia_id, $grado_id, $anio)) {
            header("Location: ?c=PortalDocente&err=sin_permiso"); exit;
        }

        $listaNotas  = $this->notaModel->getByGradoMateria($grado_id, $materia_id, $periodo, $anio);
        $gradoInfo   = $this->db->prepare("SELECT * FROM grados   WHERE id=:id");
        $gradoInfo->execute([':id' => $grado_id]);
        $gradoInfo   = $gradoInfo->fetch(PDO::FETCH_ASSOC);
        $materiaInfo = $this->db->prepare("SELECT * FROM materias WHERE id=:id");
        $materiaInfo->execute([':id' => $materia_id]);
        $materiaInfo = $materiaInfo->fetch(PDO::FETCH_ASSOC);

        require_once '../app/views/portal_docente/notas.php';
    }

    // 3. Guardar notas manualmente
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ?c=PortalDocente"); exit; }

        $docenteId  = $_SESSION['portal_id'];
        $materia_id = intval($_POST['materia_id'] ?? 0);
        $grado_id   = intval($_POST['grado_id']   ?? 0);
        $periodo    = $_POST['periodo']            ?? 'T1';
        $anio       = intval($_POST['anio']        ?? date('Y'));
        if (!in_array($periodo, ['T1','T2','T3'])) $periodo = 'T1';

        if (!$this->asignModel->tienePermiso($docenteId, $materia_id, $grado_id, $anio)) {
            header("Location: ?c=PortalDocente&err=sin_permiso"); exit;
        }

        $notas = $_POST['notas'] ?? [];
        $obs   = $_POST['obs']   ?? [];
        foreach ($notas as $alumno_id => $nota) {
            $nota = trim($nota);
            if ($nota === '' || !is_numeric($nota)) continue;
            $this->notaModel->guardar(
                intval($alumno_id), $materia_id, $periodo, $anio,
                max(0, min(20, floatval($nota))),
                trim($obs[$alumno_id] ?? ''),
                $docenteId
            );
        }

        if ($periodo === 'T3') {
            $this->notaModel->consolidarAnual($grado_id, $materia_id, $anio, $docenteId);
        }

        header("Location: ?c=PortalDocente&a=notas&grado_id=$grado_id&materia_id=$materia_id&periodo=$periodo&anio=$anio&msg=guardado");
    }

    public function consolidarAnual() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ?c=PortalDocente"); exit; }

        $docenteId  = $_SESSION['portal_id'];
        $materia_id = intval($_POST['materia_id'] ?? 0);
        $grado_id   = intval($_POST['grado_id']   ?? 0);
        $anio       = intval($_POST['anio']       ?? date('Y'));

        if (!$this->asignModel->tienePermiso($docenteId, $materia_id, $grado_id, $anio)) {
            header("Location: ?c=PortalDocente&err=sin_permiso"); exit;
        }

        $consolidados = $this->notaModel->consolidarAnual($grado_id, $materia_id, $anio, $docenteId);
        header("Location: ?c=PortalDocente&a=notas&grado_id=$grado_id&materia_id=$materia_id&periodo=T3&anio=$anio&msg=consolidado&consol=$consolidados");
    }

    // 4. Guardar observaciones desde la cartilla del alumno
    public function guardarObservacionesCartilla() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ?c=PortalDocente"); exit; }

        $docenteId  = $_SESSION['portal_id'];
        $alumno_id  = intval($_POST['alumno_id'] ?? 0);
        $anio       = intval($_POST['anio'] ?? date('Y'));
        $obsPorMateria = $_POST['obs'] ?? [];

        if ($alumno_id < 1) {
            header("Location: ?c=PortalDocente"); exit;
        }

        foreach ($obsPorMateria as $materia_id => $periodos) {
            $materia_id = intval($materia_id);
            if ($materia_id < 1) continue;
            foreach ($periodos as $periodo => $obs) {
                $periodo = strtoupper(trim($periodo));
                if (!in_array($periodo, ['T1','T2','T3'])) continue;
                $this->notaModel->guardarObservacion(
                    $alumno_id,
                    $materia_id,
                    $periodo,
                    $anio,
                    trim($obs ?? ''),
                    $docenteId
                );
            }
        }

        header("Location: ?c=PortalDocente&a=cartilla&alumno_id=$alumno_id&anio=$anio&msg=observaciones_guardadas");
    }

    // 4. Importar notas desde CSV
    public function importar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ?c=PortalDocente"); exit; }

        $docenteId  = $_SESSION['portal_id'];
        $materia_id = intval($_POST['materia_id'] ?? 0);
        $grado_id   = intval($_POST['grado_id']   ?? 0);
        $periodo    = $_POST['periodo']            ?? 'T1';
        $anio       = intval($_POST['anio']        ?? date('Y'));
        $redir      = "?c=PortalDocente&a=notas&grado_id=$grado_id&materia_id=$materia_id&periodo=$periodo&anio=$anio";

        if (!$this->asignModel->tienePermiso($docenteId, $materia_id, $grado_id, $anio)) {
            header("Location: ?c=PortalDocente&err=sin_permiso"); exit;
        }

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            header("Location: $redir&err=sin_archivo"); exit;
        }
        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv','txt'])) {
            header("Location: $redir&err=formato_invalido"); exit;
        }

        $importados = 0;
        $errores    = 0;
        $handle = fopen($_FILES['archivo']['tmp_name'], 'r');
        $header = fgetcsv($handle);
        $headerMap = [];

        if (is_array($header)) {
            foreach ($header as $idx => $col) {
                $key = strtolower(trim($col));
                $key = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $key);
                $key = preg_replace('/[^a-z0-9]+/', '_', $key);
                $headerMap[$key] = $idx;
            }
        }

        while (($fila = fgetcsv($handle)) !== false) {
            if (!is_array($fila) || count($fila) < 2) continue;

            $codigo = '';
            $nota   = '';
            $obs    = '';

            if (isset($headerMap['codigo'])) {
                $codigo = trim($fila[$headerMap['codigo']] ?? '');
            } elseif (isset($headerMap['codigo_alumno'])) {
                $codigo = trim($fila[$headerMap['codigo_alumno']] ?? '');
            } else {
                $codigo = trim($fila[0] ?? '');
            }

            if (isset($headerMap['nota']) || isset($headerMap['nota_0_20']) || isset($headerMap['nota_020'])) {
                $notaKey = isset($headerMap['nota']) ? 'nota' : (isset($headerMap['nota_0_20']) ? 'nota_0_20' : 'nota_020');
                $nota = trim($fila[$headerMap[$notaKey]] ?? '');
            } else {
                $nota = trim($fila[1] ?? '');
            }

            if (isset($headerMap['observacion']) || isset($headerMap['observaciones'])) {
                $obsKey = isset($headerMap['observacion']) ? 'observacion' : 'observaciones';
                $obs = trim($fila[$headerMap[$obsKey]] ?? '');
            } elseif (isset($fila[2])) {
                $obs = trim($fila[2]);
            }

            if ($codigo === '' || $nota === '') continue;
            if (!is_numeric($nota)) { $errores++; continue; }

            $s = $this->db->prepare(
                "SELECT id FROM alumnos WHERE codigo=:c AND grado_id=:g AND estado='activo' LIMIT 1"
            );
            $s->execute([':c'=>$codigo,':g'=>$grado_id]);
            $al = $s->fetch(PDO::FETCH_ASSOC);
            if (!$al) { $errores++; continue; }

            $this->notaModel->guardar(
                $al['id'], $materia_id, $periodo, $anio,
                max(0, min(20, floatval($nota))),
                $obs,
                $docenteId
            );
            $importados++;
        }
        fclose($handle);

        if ($periodo === 'T3') {
            $this->notaModel->consolidarAnual($grado_id, $materia_id, $anio, $docenteId);
        }

        header("Location: $redir&msg=importado&imp=$importados&err_csv=$errores");
    }

    // 5. Descargar plantilla Excel (HTML que Excel abre)
    public function plantilla() {
        $docenteId  = $_SESSION['portal_id'];
        $grado_id   = intval($_GET['grado_id']  ?? 0);
        $materia_id = intval($_GET['materia_id'] ?? 0);
        $periodo    = $_GET['periodo']           ?? 'T1';
        $anio       = intval($_GET['anio']       ?? date('Y'));

        if (!$this->asignModel->tienePermiso($docenteId, $materia_id, $grado_id, $anio)) {
            header("Location: ?c=PortalDocente&err=sin_permiso"); exit;
        }

        $rows = $this->db->prepare(
            "SELECT a.id, a.codigo, a.apellidos, a.nombres, a.dni, n.nota, n.observacion
             FROM alumnos a
             LEFT JOIN notas n ON n.alumno_id=a.id AND n.materia_id=:mid
                               AND n.periodo=:per AND n.anio=:anio
             WHERE a.grado_id=:gid AND a.estado='activo'
             ORDER BY a.apellidos, a.nombres"
        );
        $rows->execute([':mid'=>$materia_id,':per'=>$periodo,':anio'=>$anio,':gid'=>$grado_id]);
        $alumnos = $rows->fetchAll(PDO::FETCH_ASSOC);

        $gNom = $this->db->prepare("SELECT nombre FROM grados   WHERE id=:id");
        $gNom->execute([':id'=>$grado_id]);   $gNom = $gNom->fetchColumn() ?: 'Grado';
        $mNom = $this->db->prepare("SELECT nombre FROM materias WHERE id=:id");
        $mNom->execute([':id'=>$materia_id]); $mNom = $mNom->fetchColumn() ?: 'Materia';

        $perNom = ['T1'=>'1er Trimestre','T2'=>'2do Trimestre','T3'=>'3er Trimestre'];
        $perLabel = $perNom[$periodo] ?? $periodo;

        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', "Notas_{$gNom}_{$mNom}_{$periodo}_{$anio}").".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: no-cache, must-revalidate');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        $cab = ['N°', 'Código', 'DNI', 'Apellidos y Nombres', 'Nota (0-20)', 'Observación'];
        fputcsv($out, $cab, ';');

        foreach ($alumnos as $i => $al) {
            $fila = [
                $i + 1,
                $al['codigo'],
                $al['dni'] ?? '',
                $al['apellidos'] . ', ' . $al['nombres'],
                $al['nota'] !== null ? $al['nota'] : '',
                $al['observacion'] ?? ''
            ];
            fputcsv($out, $fila, ';');
        }

        fclose($out);
        exit;
    }

    // 6. Acta consolidada del grado (imprimible)
    public function acta() {
        $docenteId = $_SESSION['portal_id'];
        $grado_id  = intval($_GET['grado_id'] ?? 0);
        $anio      = intval($_GET['anio']     ?? date('Y'));

        $gradoInfo = $this->db->prepare("SELECT * FROM grados WHERE id=:id");
        $gradoInfo->execute([':id' => $grado_id]);
        $gradoInfo = $gradoInfo->fetch(PDO::FETCH_ASSOC);

        $materias = $this->db->prepare(
            "SELECT DISTINCT m.id, m.nombre
             FROM notas n
             INNER JOIN materias m ON n.materia_id = m.id
             INNER JOIN alumnos  a ON n.alumno_id  = a.id
             WHERE a.grado_id=:gid AND n.anio=:anio
             ORDER BY m.nombre"
        );
        $materias->execute([':gid'=>$grado_id,':anio'=>$anio]);
        $materias = $materias->fetchAll(PDO::FETCH_ASSOC);

        $alumnos = $this->db->prepare(
            "SELECT * FROM alumnos WHERE grado_id=:gid AND estado='activo' ORDER BY apellidos"
        );
        $alumnos->execute([':gid' => $grado_id]);
        $alumnos = $alumnos->fetchAll(PDO::FETCH_ASSOC);

        $stmtN = $this->db->prepare(
            "SELECT n.alumno_id, n.materia_id, n.periodo, n.nota
             FROM notas n INNER JOIN alumnos a ON n.alumno_id=a.id
             WHERE a.grado_id=:gid AND n.anio=:anio"
        );
        $stmtN->execute([':gid'=>$grado_id,':anio'=>$anio]);
        $notasIdx = [];
        foreach ($stmtN->fetchAll(PDO::FETCH_ASSOC) as $n) {
            $notasIdx[$n['alumno_id']][$n['materia_id']][$n['periodo']] = $n['nota'];
        }

        require_once '../app/views/portal_docente/acta.php';
    }

    // 7. Exportar acta a CSV
    public function actaExcel() {
        $grado_id = intval($_GET['grado_id'] ?? 0);
        $anio     = intval($_GET['anio']     ?? date('Y'));

        $gInfo = $this->db->prepare("SELECT nombre FROM grados WHERE id=:id");
        $gInfo->execute([':id'=>$grado_id]); $gNom = $gInfo->fetchColumn();

        $materias = $this->db->prepare(
            "SELECT DISTINCT m.id, m.nombre
             FROM notas n INNER JOIN materias m ON n.materia_id=m.id
             INNER JOIN alumnos a ON n.alumno_id=a.id
             WHERE a.grado_id=:gid AND n.anio=:anio ORDER BY m.nombre"
        );
        $materias->execute([':gid'=>$grado_id,':anio'=>$anio]);
        $materias = $materias->fetchAll(PDO::FETCH_ASSOC);

        $alumnos = $this->db->prepare(
            "SELECT * FROM alumnos WHERE grado_id=:gid AND estado='activo' ORDER BY apellidos"
        );
        $alumnos->execute([':gid'=>$grado_id]);
        $alumnos = $alumnos->fetchAll(PDO::FETCH_ASSOC);

        $stmtN = $this->db->prepare(
            "SELECT n.alumno_id, n.materia_id, n.periodo, n.nota
             FROM notas n INNER JOIN alumnos a ON n.alumno_id=a.id
             WHERE a.grado_id=:gid AND n.anio=:anio"
        );
        $stmtN->execute([':gid'=>$grado_id,':anio'=>$anio]);
        $notasIdx = [];
        foreach ($stmtN->fetchAll(PDO::FETCH_ASSOC) as $n) {
            $notasIdx[$n['alumno_id']][$n['materia_id']][$n['periodo']] = $n['nota'];
        }

        $filename = preg_replace('/[^A-Za-z0-9_\-.]/', '_', "Acta_{$gNom}_{$anio}.csv");
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        $cab = ['N°','Código','Apellidos y Nombres'];
        foreach ($materias as $m) {
            $cab[] = $m['nombre'].' T1';
            $cab[] = $m['nombre'].' T2';
            $cab[] = $m['nombre'].' T3';
            $cab[] = $m['nombre'].' Prom';
        }
        $cab[] = 'Promedio Anual';
        fputcsv($out, $cab);

        foreach ($alumnos as $i => $al) {
            $fila = [$i+1, $al['codigo'], $al['apellidos'].', '.$al['nombres']];
            $sumaP = 0; $cntP = 0;
            foreach ($materias as $m) {
                $n  = $notasIdx[$al['id']][$m['id']] ?? [];
                $t1 = $n['T1'] ?? '';
                $t2 = $n['T2'] ?? '';
                $t3 = $n['T3'] ?? '';
                $vals = array_filter([$n['T1']??null,$n['T2']??null,$n['T3']??null], fn($v)=>$v!==null);
                $prom = count($vals) ? round(array_sum($vals)/count($vals),1) : '';
                if ($prom !== '') { $sumaP += $prom; $cntP++; }
                $fila[] = $t1; $fila[] = $t2; $fila[] = $t3; $fila[] = $prom;
            }
            $fila[] = $cntP ? round($sumaP/$cntP,1) : '';
            fputcsv($out, $fila);
        }
        fclose($out); exit;
    }

    // 8. Cartilla individual del alumno
    public function cartilla() {
        $alumno_id = intval($_GET['alumno_id'] ?? 0);
        $anio      = intval($_GET['anio']      ?? date('Y'));

        $alumno = $this->db->prepare(
            "SELECT a.*, g.nombre as grado_nombre, g.nivel
             FROM alumnos a LEFT JOIN grados g ON a.grado_id=g.id
             WHERE a.id=:id LIMIT 1"
        );
        $alumno->execute([':id' => $alumno_id]);
        $alumno = $alumno->fetch(PDO::FETCH_ASSOC);

        if (!$alumno) { header("Location: ?c=PortalDocente"); exit; }

        $resumenNotas = $this->notaModel->getResumenAlumno($alumno_id, $anio);
        $detalleNotas = $this->notaModel->getByAlumno($alumno_id, $anio);
        $observacionesPorMateria = [];
        foreach ($detalleNotas as $fila) {
            $materiaId = intval($fila['materia_id'] ?? 0);
            if ($materiaId < 1) continue;
            if (!isset($observacionesPorMateria[$materiaId])) {
                $observacionesPorMateria[$materiaId] = [
                    'materia' => $fila['materia_nombre'] ?? 'Materia',
                    'T1' => '',
                    'T2' => '',
                    'T3' => '',
                ];
            }
            $periodo = $fila['periodo'] ?? '';
            if (in_array($periodo, ['T1','T2','T3'])) {
                $observacionesPorMateria[$materiaId][$periodo] = $fila['observacion'] ?? '';
            }
        }

        $asistencias = $this->db->prepare(
            "SELECT QUARTER(fecha) as periodo,
                    SUM(CASE WHEN estado='puntual' THEN 1 ELSE 0 END) as puntuales,
                    SUM(CASE WHEN estado='tarde'   THEN 1 ELSE 0 END) as tardanzas,
                    COUNT(*) as total
             FROM asistencia_alumnos
             WHERE alumno_id=:aid AND YEAR(fecha)=:anio
             GROUP BY QUARTER(fecha)
             ORDER BY QUARTER(fecha)"
        );
        $asistencias->execute([':aid'=>$alumno_id,':anio'=>$anio]);
        $asistencias = $asistencias->fetchAll(PDO::FETCH_ASSOC);

        require_once '../app/views/portal_docente/cartilla.php';
    }
}
?>
