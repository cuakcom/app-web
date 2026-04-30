<?php
/**
 * Módulo: Información web del dominio
 * - URL de captura de pantalla (thum.io, sin API key)
 * - Wayback Machine: primer y último snapshot
 * - Tranco ranking (top sites, gratuito)
 * - Tiempo de respuesta HTTP
 */

$ctx = stream_context_create([
    'http' => [
        'timeout'         => 4,
        'ignore_errors'   => true,
        'follow_location' => 1,
        'user_agent'      => 'CuakcomExpertSuite/2.3',
    ],
    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
]);

// ── Captura de pantalla (URL generada, sin llamada server-side) ───────────────
$screenshotUrl = 'https://image.thum.io/get/width/900/crop/600/https://' . $domain;

// ── HTTP response time y código ───────────────────────────────────────────────
$responseMs   = null;
$responseCode = null;
foreach (["https://{$domain}", "http://{$domain}"] as $tryUrl) {
    $t0 = microtime(true);
    $h  = @get_headers($tryUrl, true, $ctx);
    if ($h !== false) {
        $responseMs   = (int)round((microtime(true) - $t0) * 1000);
        preg_match('/HTTP\/[\d.]+\s+(\d+)/', $h[0] ?? '', $m);
        $responseCode = isset($m[1]) ? (int)$m[1] : null;
        break;
    }
}

// ── Wayback Machine ───────────────────────────────────────────────────────────
$wayback = null;
$wbRaw = @file_get_contents(
    "https://archive.org/wayback/available?url={$domain}",
    false, $ctx
);
if ($wbRaw) {
    $wbData = json_decode($wbRaw, true);
    $closest = $wbData['archived_snapshots']['closest'] ?? null;
    if ($closest) {
        $ts = $closest['timestamp'] ?? '';
        $wayback = [
            'available' => true,
            'url'       => $closest['url'] ?? null,
            'date'      => $ts ? date('d/m/Y', mktime(
                (int)substr($ts,8,2),
                (int)substr($ts,10,2),
                0,
                (int)substr($ts,4,2),
                (int)substr($ts,6,2),
                (int)substr($ts,0,4)
            )) : null,
        ];
    }
}

// Primer snapshot via CDX (más rápido que cuenta total)
$firstSnap = null;
$cdxRaw = @file_get_contents(
    "https://web.archive.org/cdx/search/cdx?url={$domain}&output=json&fl=timestamp&limit=1&from=19900101&to=20501231",
    false, $ctx
);
if ($cdxRaw) {
    $cdx = json_decode($cdxRaw, true);
    if (!empty($cdx[1][0])) {
        $ts = $cdx[1][0];
        $firstSnap = date('d/m/Y', mktime(0, 0, 0,
            (int)substr($ts,4,2), (int)substr($ts,6,2), (int)substr($ts,0,4)
        ));
    }
}

// ── Tranco ranking (top 1M sites, gratuito) ───────────────────────────────────
$tranco = null;
$tRaw = @file_get_contents(
    "https://tranco-list.eu/api/ranks/domain/{$domain}",
    false, $ctx
);
if ($tRaw) {
    $tData = json_decode($tRaw, true);
    if (!empty($tData['ranks'])) {
        $tranco = [
            'rank' => $tData['ranks'][0]['rank'] ?? null,
            'list' => $tData['ranks'][0]['list'] ?? null,
        ];
    }
}

echo json_encode([
    'success'        => true,
    'screenshot_url' => $screenshotUrl,
    'response_ms'    => $responseMs,
    'response_code'  => $responseCode,
    'wayback'        => $wayback,
    'wayback_first'  => $firstSnap,
    'tranco'         => $tranco,
    'notes'          => [
        'dmoz'      => 'DMOZ (Open Directory) fue descontinuado en marzo de 2017.',
        'pagerank'  => 'Google PageRank público fue eliminado en 2016. Alternativa: openpagerank.com (requiere API key).',
        'visits'    => 'Estadísticas de visitas estimadas requieren SimilarWeb, Semrush u otras herramientas externas.',
    ],
]);
