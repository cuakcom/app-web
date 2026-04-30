<?php
/**
 * Módulo: Resolución IP / DNS inverso
 * Variables disponibles: $domain (string, sanitizado por api.php)
 *
 * ARSYS se detecta si:
 *  - IP empieza por 217.76. / 82.223. / 82.233.
 *  - PTR (reverso) coincide con *.servidoresdns.net o *.serviciodecorreo.es
 */

$ip      = gethostbyname($domain);
$hasIP   = ($ip !== $domain);
$reverse = $hasIP ? gethostbyaddr($ip) : null;

$arsys = false;
if ($hasIP) {
    foreach (['217.76.', '82.223.', '82.233.'] as $range) {
        if (strpos($ip, $range) === 0) { $arsys = true; break; }
    }
}
if (!$arsys && $reverse) {
    if (preg_match('/\.(servidoresdns\.net|serviciodecorreo\.es)\.?$/i', $reverse)) {
        $arsys = true;
    }
}

echo json_encode([
    'success' => true,
    'ip'      => $hasIP ? $ip : null,
    'reverse' => $reverse,
    'arsys'   => $arsys,
]);
