<?php
/**
 * Módulo: Traceroute
 * Variables disponibles: $domain (string, sanitizado por api.php)
 */

if (!function_exists('shell_exec') || !is_callable('shell_exec')) {
    echo json_encode(['success' => false, 'error' => 'shell_exec no disponible en este servidor']);
    exit;
}

$host   = escapeshellarg($domain);
$output = @shell_exec("traceroute -n -m 20 -w 2 {$host} 2>&1");

if (empty(trim((string)$output))) {
    echo json_encode(['success' => false, 'error' => 'traceroute no disponible o no devolvió respuesta']);
    exit;
}

// Parsear salida en saltos
$lines = explode("\n", trim($output));
$hops  = [];

foreach ($lines as $line) {
    if (!preg_match('/^\s*(\d+)\s+(.+)$/', $line, $m)) continue;

    $hopNum = (int)$m[1];
    $rest   = trim($m[2]);

    if (preg_match('/^\*[\s*]+$/', $rest)) {
        $hops[] = ['hop' => $hopNum, 'ip' => null, 'ms' => null, 'timeout' => true];
        continue;
    }

    preg_match('/(\d{1,3}(?:\.\d{1,3}){3})/', $rest, $ipMatch);
    preg_match_all('/([\d.]+)\s+ms/', $rest, $msMatches);

    $hopIp  = $ipMatch[1]      ?? null;
    $allMs  = $msMatches[1]    ?? [];
    $avgMs  = count($allMs) > 0 ? round(array_sum($allMs) / count($allMs), 2) : null;

    $hops[] = ['hop' => $hopNum, 'ip' => $hopIp, 'ms' => $avgMs, 'timeout' => false];
}

echo json_encode([
    'success' => true,
    'hops'    => $hops,
    'output'  => $output,
    'count'   => count($hops),
]);
