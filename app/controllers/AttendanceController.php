<?php
require_once '../app/config/db.php';
require_once '../app/models/Setting.php';
require_once '../app/models/AsistenciaAlumno.php';
require_once '../app/libs/WhatsApp.php';

class AttendanceController {

    // ── Helper: cargar config WhatsApp desde BD ───────────────────────────────
    private function getWhatsApp($db): WhatsApp {
        $settingModel = new Setting($db);
        return new WhatsApp([
            'wa_activo'    => $settingModel->get('wa_activo')    ?? '0',
            'wa_proveedor' => $settingModel->get('wa_proveedor') ?? 'callmebot',
            'wa_token'     => $settingModel->get('wa_token')     ?? '',
            'wa_instance'  => $settingModel->get('wa_instance')  ?? '',
        ]);
    }

    // 1. Mostrar kiosco
    public function index() {
        $message = $_GET['msg'] ?? '';
        $error   = $_GET['err'] ?? '';
        require_once '../app/views/attendance/kiosk.php';
    }

    // 2. Registrar asistencia PERSONAL (sin notificación WA por ahora)
    public function register() {
        $message = "";
        $error   = "";

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Attendance"); exit;
        }

        $codigo = trim(htmlspecialchars($_POST['employee_code'] ?? ''));

        if (empty($codigo)) {
            $error = "Código vacío. Intenta de nuevo.";
            require_once '../app/views/attendance/kiosk.php';
            return;
        }

        try {
            $db         = (new Database())->getConnection();
            $horaLimite = (new Setting($db))->get('hora_entrada') ?? '07:30:00';

            $stmt = $db->prepare(
                "SELECT id, nombres FROM personal WHERE codigo = :codigo AND estado = 'activo' LIMIT 1"
            );
            $stmt->bindValue(':codigo', $codigo);
            $stmt->execute();
            $miembro = $stmt->fetch();

            if (!$miembro) {
                $error = "Código QR no válido o personal inactivo.";
                require_once '../app/views/attendance/kiosk.php';
                return;
            }

            $personalId = $miembro['id'];
            $fechaHoy   = date('Y-m-d');
            $horaActual = date('H:i:s');

            $stmtCheck = $db->prepare(
                "SELECT id, hora_salida FROM registros_asistencia
                 WHERE personal_id = :pid AND fecha = :fecha LIMIT 1"
            );
            $stmtCheck->bindValue(':pid',   $personalId, PDO::PARAM_INT);
            $stmtCheck->bindValue(':fecha', $fechaHoy);
            $stmtCheck->execute();
            $registroHoy = $stmtCheck->fetch();

            if (!$registroHoy) {
                $estado = ($horaActual > $horaLimite) ? 'tarde' : 'puntual';
                $db->prepare(
                    "INSERT INTO registros_asistencia (personal_id, fecha, hora_entrada, estado)
                     VALUES (:pid, :fecha, :hora, :estado)"
                )->execute([':pid'=>$personalId,':fecha'=>$fechaHoy,
                             ':hora'=>$horaActual,':estado'=>$estado]);
                $saludo  = ($estado === 'tarde') ? '⚠️ Llegada tarde,' : '✅ Buenos días,';
                $message = "$saludo {$miembro['nombres']}. Entrada: " . date('H:i', strtotime($horaActual));

            } elseif ($registroHoy['hora_salida'] === null) {
                $db->prepare(
                    "UPDATE registros_asistencia SET hora_salida = :hora WHERE id = :id"
                )->execute([':hora'=>$horaActual, ':id'=>$registroHoy['id']]);
                $message = "👋 Hasta mañana, {$miembro['nombres']}. Salida: " . date('H:i', strtotime($horaActual));

            } else {
                $error = "⚠️ Ya registraste tu salida hoy.";
            }

        } catch (PDOException $e) {
            error_log("AttendanceController::register() — " . $e->getMessage());
            $error = "Error interno. Inténtalo de nuevo.";
        }

        require_once '../app/views/attendance/kiosk.php';
    }

    // 3. Registrar asistencia ALUMNO + notificación WhatsApp al apoderado
    public function registerAlumno() {
        $message = "";
        $error   = "";

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?c=Attendance"); exit;
        }

        $codigo = trim(htmlspecialchars($_POST['alumno_code'] ?? ''));

        if (empty($codigo)) {
            $error = "Código vacío.";
            require_once '../app/views/attendance/kiosk.php';
            return;
        }

        try {
            $db         = (new Database())->getConnection();
            $horaLimite = (new Setting($db))->get('hora_entrada') ?? '07:30:00';
            $colegio    = (new Setting($db))->get('nombre_colegio') ?? 'Colegio Pestalozzi';
            $wa         = $this->getWhatsApp($db);

            // Obtener datos completos del alumno (incluyendo apoderado y apikey WA)
            $stmt = $db->prepare(
                "SELECT a.id, a.nombres, a.apellidos, a.codigo,
                        a.nombre_apoderado, a.telefono_apoderado, a.wa_apikey_apoderado,
                        g.nombre as grado
                 FROM alumnos a
                 LEFT JOIN grados g ON a.grado_id = g.id
                 WHERE a.codigo = :codigo AND a.estado = 'activo' LIMIT 1"
            );
            $stmt->bindValue(':codigo', $codigo);
            $stmt->execute();
            $alumno = $stmt->fetch();

            if (!$alumno) {
                $error = "Código QR no válido o alumno inactivo.";
                require_once '../app/views/attendance/kiosk.php';
                return;
            }

            $asistModel = new AsistenciaAlumno($db);
            $horaActual = date('H:i:s');
            $horaFmt    = date('H:i', strtotime($horaActual));
            $fechaFmt   = date('d/m/Y');
            $registro   = $asistModel->getRegistroHoy($alumno['id']);
            $nombreCompleto = $alumno['nombres'] . ' ' . $alumno['apellidos'];

            if (!$registro) {
                // ── ENTRADA ──────────────────────────────────────────────────
                $estado = ($horaActual > $horaLimite) ? 'tarde' : 'puntual';
                $asistModel->registrarEntrada($alumno['id'], $horaActual, $estado);

                if ($estado === 'tarde') {
                    $saludo  = '⚠️ Llegada tarde,';
                    $message = "⚠️ $nombreCompleto llegó tarde. Entrada: $horaFmt";

                    // WhatsApp — tardanza
                    $msgWA = "⚠️ *TARDANZA — $colegio*\n\n"
                           . "Estimado/a *{$alumno['nombre_apoderado']}*,\n\n"
                           . "Su hijo/a *$nombreCompleto* ({$alumno['grado']}) "
                           . "llegó *tarde* al colegio.\n\n"
                           . "🕐 Hora de ingreso: *$horaFmt*\n"
                           . "📅 Fecha: $fechaFmt\n\n"
                           . "_Colegio Pestalozzi_";
                } else {
                    $saludo  = '✅ Buenos días,';
                    $message = "✅ $nombreCompleto. Entrada: $horaFmt";

                    // WhatsApp — entrada normal
                    $msgWA = "✅ *INGRESO AL COLEGIO — $colegio*\n\n"
                           . "Estimado/a *{$alumno['nombre_apoderado']}*,\n\n"
                           . "Su hijo/a *$nombreCompleto* ({$alumno['grado']}) "
                           . "ha ingresado al colegio correctamente.\n\n"
                           . "🕐 Hora de ingreso: *$horaFmt*\n"
                           . "📅 Fecha: $fechaFmt\n\n"
                           . "_Colegio Pestalozzi_";
                }

                // Enviar WhatsApp si tiene teléfono y apikey configurados
                $this->enviarNotificacion(
                    $wa,
                    $alumno['telefono_apoderado']  ?? '',
                    $alumno['wa_apikey_apoderado'] ?? '',
                    $msgWA
                );

            } elseif ($registro['hora_salida'] === null) {
                // ── SALIDA ───────────────────────────────────────────────────
                $asistModel->registrarSalida($registro['id'], $horaActual);
                $message = "👋 Hasta mañana, $nombreCompleto. Salida: $horaFmt";

                // WhatsApp — salida
                $msgWA = "🏠 *SALIDA DEL COLEGIO — $colegio*\n\n"
                       . "Estimado/a *{$alumno['nombre_apoderado']}*,\n\n"
                       . "Su hijo/a *$nombreCompleto* ({$alumno['grado']}) "
                       . "ha salido del colegio.\n\n"
                       . "🕐 Hora de salida: *$horaFmt*\n"
                       . "📅 Fecha: $fechaFmt\n\n"
                       . "_Por favor recójalo(a) a la brevedad._\n"
                       . "_Colegio Pestalozzi_";

                $this->enviarNotificacion(
                    $wa,
                    $alumno['telefono_apoderado']  ?? '',
                    $alumno['wa_apikey_apoderado'] ?? '',
                    $msgWA
                );

            } else {
                $error = "⚠️ $nombreCompleto ya registró salida hoy.";
            }

        } catch (PDOException $e) {
            error_log("AttendanceController::registerAlumno() — " . $e->getMessage());
            $error = "Error interno. Inténtalo de nuevo.";
        }

        require_once '../app/views/attendance/kiosk.php';
    }

    // ── Helper privado: enviar notificación (no bloquea la ejecución) ─────────
    private function enviarNotificacion(WhatsApp $wa, string $tel, string $apikey, string $msg): void {
        if (empty($tel)) return;
        // Ejecutar en background para no hacer esperar al kiosco
        // Si no hay soporte para background, se envía sincrónicamente
        try {
            $wa->enviar($tel, $msg, $apikey);
        } catch (\Exception $e) {
            error_log("WhatsApp notificación falló: " . $e->getMessage());
            // No interrumpir el flujo principal
        }
    }
}
?>
