<?php
/**
 * Módulo: Cabeceras HTTP de seguridad
 * Variables disponibles: $domain (string, sanitizado por api.php)
 */

$urls = ["https://{$domain}", "http://{$domain}"];
$headers_raw = false;
$usedUrl = '';

$ctx = stream_context_create([
    'http' => [
        'method'          => 'GET',
        'timeout'         => 8,
        'follow_location' => 0,
        'ignore_errors'   => true,
        'user_agent'      => 'CuakcomExpertSuite/2.0',
    ],
    'ssl'  => [
        'verify_peer'      => false,
        'verify_peer_name' => false,
    ],
]);

foreach ($urls as $url) {
    $h = @get_headers($url, true, $ctx);
    if ($h !== false) {
        $headers_raw = $h;
        $usedUrl     = $url;
        break;
    }
}

if ($headers_raw === false) {
    echo json_encode(['success' => false, 'error' => 'No se pudieron obtener cabeceras HTTP del servidor']);
    exit;
}

$headersLc = array_change_key_case($headers_raw, CASE_LOWER);

// Cabeceras de seguridad a evaluar
$secChecks = [
    'strict-transport-security' => ['label' => 'HSTS',                   'desc' => 'Fuerza HTTPS y protege contra downgrade'],
    'content-security-policy'   => ['label' => 'CSP',                    'desc' => 'Controla recursos que el navegador puede cargar'],
    'x-frame-options'           => ['label' => 'X-Frame-Options',        'desc' => 'Protección contra clickjacking (iframes)'],
    'x-content-type-options'    => ['label' => 'X-Content-Type-Options', 'desc' => 'Previene MIME-type sniffing'],
    'referrer-policy'           => ['label' => 'Referrer-Policy',        'desc' => 'Controla información de referrer enviada'],
    'permissions-policy'        => ['label' => 'Permissions-Policy',     'desc' => 'Limita acceso a APIs del navegador'],
    'x-xss-protection'          => ['label' => 'X-XSS-Protection',       'desc' => 'Filtro XSS integrado en navegadores legacy'],
    'cross-origin-opener-policy'=> ['label' => 'COOP',                   'desc' => 'Aísla el contexto de ventana entre orígenes'],
    'cross-origin-embedder-policy'=> ['label' => 'COEP',                 'desc' => 'Controla recursos cross-origin incrustados'],
];

$results = [];
$score   = 0;
foreach ($secChecks as $key => $meta) {
    $present = isset($headersLc[$key]);
    $val     = null;
    if ($present) {
        $val = is_array($headersLc[$key]) ? end($headersLc[$key]) : $headersLc[$key];
        $score++;
    }
    $results[] = [
        'header'  => $key,
        'label'   => $meta['label'],
        'desc'    => $meta['desc'],
        'present' => $present,
        'value'   => $val,
    ];
}

// Datos informativos adicionales
$statusLine = $headers_raw[0] ?? '';
preg_match('/HTTP\/[\d.]+\s+(\d+)/', $statusLine, $m);
$statusCode = isset($m[1]) ? (int)$m[1] : null;

$getVal = function(string $k) use ($headersLc) {
    if (!isset($headersLc[$k])) return null;
    $v = $headersLc[$k];
    return is_array($v) ? end($v) : $v;
};

echo json_encode([
    'success'     => true,
    'url'         => $usedUrl,
    'status_code' => $statusCode,
    'server'      => $getVal('server'),
    'powered_by'  => $getVal('x-powered-by'),
    'score'       => $score,
    'total'       => count($secChecks),
    'headers'     => $results,
]);
