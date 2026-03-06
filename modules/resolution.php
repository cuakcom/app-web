<?php
/**
 * Módulo: Resolución IP / DNS inverso
 * Variables disponibles: $domain (string, sanitizado por api.php)
 */

$ip      = gethostbyname($domain);
$hasIP   = ($ip !== $domain);
$reverse = $hasIP ? gethostbyaddr($ip) : null;

// Detección ARSYS: rangos IP 217.76.x.x / 82.233.x.x o hostname *.servidoresdns.net
$arsys = false;
if ($hasIP) {
    if (strpos($ip, '217.76.') === 0 || strpos($ip, '82.233.') === 0) {
        $arsys = true;
    }
}
if (!$arsys && $reverse && preg_match('/\.servidoresdns\.net\.?$/i', $reverse)) {
    $arsys = true;
}

echo json_encode([
    'success' => true,
    'ip'      => $hasIP ? $ip : null,
    'reverse' => $reverse,
    'arsys'   => $arsys,
]);
