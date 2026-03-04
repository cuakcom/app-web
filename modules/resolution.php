<?php
/**
 * Módulo: Resolución IP / DNS inverso
 * Variables disponibles: $domain (string, sanitizado por api.php)
 */

$ip      = gethostbyname($domain);
$hasIP   = ($ip !== $domain);
$reverse = $hasIP ? gethostbyaddr($ip) : null;

echo json_encode([
    'success' => true,
    'ip'      => $hasIP ? $ip : null,
    'reverse' => $reverse,
]);
