<?php
/**
 * Módulo: Cadena de redirecciones y tiempo de respuesta
 * Variables disponibles: $domain (string, sanitizado por api.php)
 */

function cuak_fetch_head(string $url, int $timeout = 8): ?array
{
    $ctx = stream_context_create([
        'http' => [
            'method'          => 'GET',
            'timeout'         => $timeout,
            'follow_location' => 0,
            'ignore_errors'   => true,
            'user_agent'      => 'CuakcomExpertSuite/2.0',
        ],
        'ssl'  => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ],
    ]);

    $t0      = microtime(true);
    $headers = @get_headers($url, true, $ctx);
    $ms      = (int)round((microtime(true) - $t0) * 1000);

    if ($headers === false) return null;

    $statusLine = $headers[0] ?? '';
    preg_match('/HTTP\/[\d.]+\s+(\d+)/', $statusLine, $sm);
    $code = isset($sm[1]) ? (int)$sm[1] : null;

    $loc = $headers['Location'] ?? null;
    if (is_array($loc)) $loc = end($loc);

    return ['url' => $url, 'code' => $code, 'location' => $loc, 'ms' => $ms];
}

$chain   = [];
$current = 'http://' . $domain;
$maxHops = 10;

for ($i = 0; $i < $maxHops; $i++) {
    $step = cuak_fetch_head($current);
    if ($step === null) {
        if ($i === 0) {
            echo json_encode(['success' => false, 'error' => 'No se pudo conectar con el servidor']);
            exit;
        }
        break;
    }
    $chain[] = $step;

    // Parar si no es redirección
    if (!in_array($step['code'], [301, 302, 303, 307, 308], true)) break;

    $loc = $step['location'];
    if (empty($loc)) break;

    // Normalizar URL relativa
    if (str_starts_with($loc, '//')) {
        $scheme  = parse_url($current, PHP_URL_SCHEME) ?? 'http';
        $loc     = $scheme . ':' . $loc;
    } elseif (str_starts_with($loc, '/')) {
        $parsed  = parse_url($current);
        $loc     = ($parsed['scheme'] ?? 'http') . '://' . ($parsed['host'] ?? '') . $loc;
    }

    $current = $loc;
}

$final    = !empty($chain) ? end($chain) : null;
$totalMs  = array_sum(array_column($chain, 'ms'));
$hasHttps = $final && str_contains($final['url'], 'https://');

echo json_encode([
    'success'    => true,
    'chain'      => $chain,
    'hops'       => count($chain),
    'total_ms'   => $totalMs,
    'final_url'  => $final['url']  ?? null,
    'final_code' => $final['code'] ?? null,
    'has_https'  => $hasHttps,
]);
