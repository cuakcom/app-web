<?php
/**
 * Check Berry
 */
require_once __DIR__ . '/includes/version.php';
require_once __DIR__ . '/menu.php';
require_once __DIR__ . '/includes/auth.php';

// session_start() debe correr antes de imprimir cualquier byte. header.php
// llama a current_user() para pintar "Mi cuenta", pero eso pasa DESPUÉS de
// que head.php ya haya emitido <!DOCTYPE html> y todo el <head> — arrancar
// la sesión aquí evita el warning "headers already sent".
auth_start_session();

// Datos del visitante (server-side)
function getClientIp(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_REAL_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $h) {
        if (!empty($_SERVER[$h])) {
            $ip = trim(explode(',', $_SERVER[$h])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}
$visitorIp   = getClientIp();
$visitorUa   = $_SERVER['HTTP_USER_AGENT']       ?? '';
$visitorLang = $_SERVER['HTTP_ACCEPT_LANGUAGE']   ?? '';
$visitorRef  = $_SERVER['HTTP_REFERER']           ?? '';

require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/footer_roadmap.php';
require __DIR__ . '/includes/footer_visitor.php';
require __DIR__ . '/includes/scripts_bottom.php';
?>
</body>
</html>
