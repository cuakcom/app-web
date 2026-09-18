<?php
/**
 * Check Berry
 */
define('APP_VERSION', '4.0.1');

require_once __DIR__ . '/menu.php';

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
require __DIR__ . '/includes/footer_visitor.php';
require __DIR__ . '/includes/scripts_bottom.php';
?>
</body>
</html>
