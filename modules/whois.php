<?php
/**
 * Módulo: WHOIS (usa comando del sistema para obtener el servidor WHOIS correcto por TLD)
 * Variables disponibles: $domain (string, sanitizado por api.php)
 */

if (!function_exists('shell_exec')) {
    echo json_encode(['success' => false, 'error' => 'shell_exec deshabilitado en el servidor']);
    exit;
}

$escaped = escapeshellarg($domain);
$output  = @shell_exec("whois {$escaped} 2>&1");

if (empty(trim($output ?? ''))) {
    echo json_encode(['success' => false, 'error' => 'Sin respuesta del servidor WHOIS']);
    exit;
}

echo json_encode(['success' => true, 'data' => $output]);
