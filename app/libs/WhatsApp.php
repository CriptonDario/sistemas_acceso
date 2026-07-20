<?php
/**
 * Servicio de notificaciones WhatsApp — Colegio Pestalozzi
 *
 * Proveedores soportados:
 *   - callmebot : Gratis. El apoderado debe activar primero enviando
 *                 "I allow callmebot to send me messages" al +34 644 59 72 64
 *                 y recibirá su API key personal.
 *   - ultramsg  : De pago ($15/mes). Necesita instance_id + token.
 *
 * Configuración en tabla `configuracion`:
 *   wa_activo      => 1 | 0
 *   wa_proveedor   => callmebot | ultramsg
 *   wa_token       => (ultramsg token o no aplica para callmebot)
 *   wa_instance    => (ultramsg instance_id)
 */
class WhatsApp {

    private string $proveedor;
    private string $token;
    private string $instance;
    private bool   $activo;

    public function __construct(array $config) {
        $this->activo    = ($config['wa_activo']    ?? '0') === '1';
        $this->proveedor = $config['wa_proveedor']  ?? 'callmebot';
        $this->token     = $config['wa_token']      ?? '';
        $this->instance  = $config['wa_instance']   ?? '';
    }

    /**
     * Enviar mensaje a un número de WhatsApp.
     *
     * @param string $telefono  Número con código de país, sin + ni espacios (Ej: 51987654321)
     * @param string $mensaje   Texto del mensaje
     * @param string $apikey    API key personal del destinatario (solo CallMeBot)
     * @return bool
     */
    public function enviar(string $telefono, string $mensaje, string $apikey = ''): bool {
        if (!$this->activo) return false;

        // Limpiar número — solo dígitos
        $telefono = preg_replace('/\D/', '', $telefono);
        if (empty($telefono)) return false;

        try {
            switch ($this->proveedor) {
                case 'callmebot':
                    return $this->enviarCallmebot($telefono, $mensaje, $apikey);

                case 'ultramsg':
                    return $this->enviarUltramsg($telefono, $mensaje);

                default:
                    error_log("WhatsApp: proveedor desconocido '{$this->proveedor}'");
                    return false;
            }
        } catch (\Exception $e) {
            error_log("WhatsApp::enviar() — " . $e->getMessage());
            return false;
        }
    }

    // ── CallMeBot ────────────────────────────────────────────────────────────
    private function enviarCallmebot(string $tel, string $msg, string $apikey): bool {
        if (empty($apikey)) {
            error_log("WhatsApp CallMeBot: falta la apikey del destinatario ($tel)");
            return false;
        }

        $url = "https://api.callmebot.com/whatsapp.php?"
             . http_build_query([
                 'phone'   => $tel,
                 'text'    => $msg,
                 'apikey'  => $apikey,
               ]);

        $result = $this->httpGet($url);
        $ok = ($result !== false && stripos($result, 'Message Queued') !== false);
        if (!$ok) error_log("WhatsApp CallMeBot respuesta: $result");
        return $ok;
    }

    // ── UltraMsg ─────────────────────────────────────────────────────────────
    private function enviarUltramsg(string $tel, string $msg): bool {
        if (empty($this->instance) || empty($this->token)) {
            error_log("WhatsApp UltraMsg: faltan instance_id o token");
            return false;
        }

        $url  = "https://api.ultramsg.com/{$this->instance}/messages/chat";
        $body = http_build_query([
            'token'  => $this->token,
            'to'     => $tel . '@c.us',  // formato WhatsApp
            'body'   => $msg,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $result = curl_exec($ch);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($err) { error_log("WhatsApp UltraMsg cURL: $err"); return false; }

        $json = json_decode($result, true);
        $ok   = isset($json['sent']) && $json['sent'] === 'true';
        if (!$ok) error_log("WhatsApp UltraMsg respuesta: $result");
        return $ok;
    }

    // ── HTTP GET simple ───────────────────────────────────────────────────────
    private function httpGet(string $url): string|false {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $r = curl_exec($ch);
            curl_close($ch);
            return $r;
        }
        // Fallback: file_get_contents
        return @file_get_contents($url);
    }
}
?>
