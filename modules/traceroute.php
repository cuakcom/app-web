<?php
/**
 * Módulo: Traceroute
 * Variables disponibles: $domain (string, sanitizado por api.php)
 * Intenta: traceroute → tracepath → mtr → ping TTL
 */

if (!function_exists('shell_exec') || !is_callable('shell_exec')) {
    echo json_encode(['success' => false, 'error' => 'shell_exec no disponible en este servidor']);
    exit;
}

$host   = escapeshellarg($domain);
$output = '';
$tool   = '';

// 1) traceroute
$tr = trim((string)@shell_exec('which traceroute 2>/dev/null'));
if ($tr) {
    $output = (string)@shell_exec("traceroute -n -m 20 -w 2 {$host} 2>&1");
    $tool   = 'traceroute';
}

// 2) tracepath
if (empty(trim($output))) {
    $tp = trim((string)@shell_exec('which tracepath 2>/dev/null'));
    if ($tp) {
        $output = (string)@shell_exec("tracepath -n -m 20 {$host} 2>&1");
        $tool   = 'tracepath';
    }
}

// 3) mtr --report
if (empty(trim($output))) {
    $mtr = trim((string)@shell_exec('which mtr 2>/dev/null'));
    if ($mtr) {
        $output = (string)@shell_exec("mtr --report --report-cycles 3 -n --max-ttl 20 {$host} 2>&1");
        $tool   = 'mtr';
    }
}

if (empty(trim($output))) {
    echo json_encode(['success' => false, 'error' => 'Ninguna herramienta de traceroute disponible en el servidor (traceroute, tracepath, mtr).']);
    exit;
}

// Parsear salida en saltos
$lines = explode("\n", trim($output));
$hops  = [];

foreach ($lines as $line) {
    // traceroute / tracepath format: "  1  192.168.1.1  1.234 ms"
    // mtr --report format: "  1.|-- 192.168.1.1   0.0%     3    1.2   1.2   1.1   1.3   0.1"
    if ($tool === 'mtr') {
        if (!preg_match('/^\s*(\d+)\.\|[-?]+\s+(\S+)\s+[\d.]+%\s+\d+\s+([\d.]+)/', $line, $m)) continue;
        $hopNum = (int)$m[1];
        $hopIp  = $m[2] === '???' ? null : $m[2];
        $avgMs  = $hopIp ? (float)$m[3] : null;
        $hops[] = ['hop' => $hopNum, 'ip' => $hopIp, 'ms' => $avgMs, 'timeout' => $hopIp === null, 'tool' => $tool];
        continue;
    }

    if (!preg_match('/^\s*(\d+)\s+(.+)$/', $line, $m)) continue;

    $hopNum = (int)$m[1];
    $rest   = trim($m[2]);

    if (preg_match('/^\*[\s*]+$/', $rest) || $rest === '???') {
        $hops[] = ['hop' => $hopNum, 'ip' => null, 'ms' => null, 'timeout' => true, 'tool' => $tool];
        continue;
    }

    preg_match('/(\d{1,3}(?:\.\d{1,3}){3}|[0-9a-f:]{3,39})/', $rest, $ipMatch);
    preg_match_all('/([\d.]+)\s+ms/', $rest, $msMatches);

    $hopIp  = $ipMatch[1]   ?? null;
    $allMs  = $msMatches[1] ?? [];
    $avgMs  = count($allMs) > 0 ? round(array_sum($allMs) / count($allMs), 2) : null;

    $hops[] = ['hop' => $hopNum, 'ip' => $hopIp, 'ms' => $avgMs, 'timeout' => false, 'tool' => $tool];
}

echo json_encode([
    'success' => true,
    'hops'    => $hops,
    'output'  => $output,
    'count'   => count($hops),
    'tool'    => $tool,
]);
