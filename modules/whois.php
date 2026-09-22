<?php
/**
 * Módulo: WHOIS (usa comando del sistema para obtener el servidor WHOIS correcto por TLD)
 * Variables disponibles: $domain (string, sanitizado por api.php)
 */

if (!function_exists('shell_exec')) {
    echo json_encode(['success' => false, 'error' => 'shell_exec deshabilitado en el servidor']);
    exit;
}

// A diferencia del resto de módulos (que usan curl con timeout), el binario
// "whois" no tiene límite propio: si un servidor WHOIS de registro/registrador
// no responde, el proceso puede quedarse colgado mucho más allá del
// max_execution_time de PHP, matando el script sin devolver JSON válido.
// "timeout" (coreutils) garantiza que este shell_exec siempre vuelve.
$escaped = escapeshellarg($domain);
$output  = @shell_exec("timeout 20 whois {$escaped} 2>&1");

if (empty(trim($output ?? ''))) {
    echo json_encode(['success' => false, 'error' => 'Sin respuesta del servidor WHOIS']);
    exit;
}

echo json_encode(['success' => true, 'data' => $output]);
