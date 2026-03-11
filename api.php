<?php
/**
 * Cuakcom Expert Suite - API Dispatcher
 * Recibe peticiones AJAX y delega en el módulo correspondiente.
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/functions.php';

// ── Rate limiting (60 req/min por IP) ────────────────────────────────────────
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$rateDir  = sys_get_temp_dir() . '/cuakcom_rate';
if (!is_dir($rateDir)) @mkdir($rateDir, 0700, true);

if (is_dir($rateDir)) {
    $rateFile = $rateDir . '/' . md5($clientIp) . '.json';
    $now      = time();
    $window   = 60;
    $maxReqs  = 60;

    $rateData = [];
    if (file_exists($rateFile)) {
        $rateData = json_decode(@file_get_contents($rateFile), true) ?? [];
    }
    $reqs = array_filter($rateData['reqs'] ?? [], fn($t) => $t > $now - $window);

    if (count($reqs) >= $maxReqs) {
        http_response_code(429);
        echo json_encode(['success' => false, 'error' => 'Demasiadas solicitudes. Espera un momento e inténtalo de nuevo.']);
        exit;
    }

    $reqs[] = $now;
    @file_put_contents($rateFile, json_encode(['reqs' => array_values($reqs)]));
}

// ── Validación de parámetros ──────────────────────────────────────────────────
$module     = $_GET['module'] ?? '';
$domain_raw = $_GET['domain'] ?? '';
$domain     = limpiarHost($domain_raw);

if (empty($domain)) {
    echo json_encode(['success' => false, 'error' => 'Dominio inválido o vacío. Introduce un dominio como "ejemplo.com" o una dirección IP válida.']);
    exit;
}

$allowed = ['resolution', 'dns', 'ports', 'whois', 'ssl', 'ping',
            'headers', 'blacklist', 'traceroute', 'redirect', 'mailtest',
            'dnsquery', 'webinfo', 'geoip', 'dnspropagation', 'seocheck',
            'sslscan', 'smtprelay', 'spfcheck'];

if (!in_array($module, $allowed, true)) {
    echo json_encode(['success' => false, 'error' => 'Módulo desconocido']);
    exit;
}

// ── Caché de archivo para módulos costosos ────────────────────────────────────
$cacheable = [
    'dns'           => 300,
    'whois'         => 3600,
    'blacklist'     => 1800,
    'mailtest'      => 300,
    'webinfo'       => 600,
    'geoip'         => 3600,
    'dnspropagation'=> 120,
    'seocheck'      => 600,
    'sslscan'       => 600,
]; // TTL en segundos
$useCache  = false;
$cacheFile = null;

if (isset($cacheable[$module])) {
    $cacheDir = sys_get_temp_dir() . '/cuakcom_cache';
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0700, true);

    if (is_dir($cacheDir)) {
        $cacheKey  = $module . '_' . md5($domain . ($_GET['types'] ?? ''));
        $cacheFile = $cacheDir . '/' . $cacheKey . '.json';
        $ttl       = $cacheable[$module];

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
            $cached = @file_get_contents($cacheFile);
            if ($cached !== false) {
                echo $cached;
                exit;
            }
        }
        $useCache = true;
    }
}

if ($useCache) ob_start();

require __DIR__ . '/modules/' . $module . '.php';

if ($useCache) {
    $output = ob_get_clean();
    echo $output;
    if (!empty($output)) {
        @file_put_contents($cacheFile, $output);
    }
}
