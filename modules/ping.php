<?php
/**
 * Módulo: Ping / test de conectividad
 * Variables disponibles: $domain (string, sanitizado por api.php)
 */

if (!function_exists('shell_exec')) {
    echo json_encode(['success' => false, 'error' => 'shell_exec deshabilitado en el servidor']);
    exit;
}

$escaped = escapeshellarg($domain);
$output  = @shell_exec("ping -c 4 -W 2 {$escaped} 2>&1");

if (empty(trim($output ?? ''))) {
    echo json_encode(['success' => false, 'error' => 'Sin respuesta del ping']);
    exit;
}

$avgMs = null;
$loss  = null;

if (preg_match('/min\/avg\/max.*?=\s*[\d.]+\/([\d.]+)\//', $output, $m)) {
    $avgMs = (float)$m[1];
}
if (preg_match('/(\d+)%\s+packet loss/', $output, $m)) {
    $loss = (int)$m[1];
}

echo json_encode([
    'success'     => true,
    'output'      => $output,
    'avg_ms'      => $avgMs,
    'packet_loss' => $loss,
]);
