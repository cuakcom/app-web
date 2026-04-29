<?php
// modules/abuseipdb.php - Comprobación AbuseIPDB
// Expects: $dominio (string)
// Optional: define ABUSEIPDB_KEY in config.php for automatic API lookup

$ip       = gethostbyname($dominio);
$abuseKey = defined('ABUSEIPDB_KEY') ? ABUSEIPDB_KEY : '';

if ($abuseKey && function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://api.abuseipdb.com/api/v2/check?ipAddress=' . urlencode($ip) . '&maxAgeInDays=90',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ["Key: $abuseKey", 'Accept: application/json'],
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data  = json_decode($response, true)['data'] ?? [];
        $score = $data['abuseConfidenceScore'] ?? 0;
        $scCls = $score > 50 ? 'danger' : ($score > 20 ? 'warning' : 'success');
        ?>
        <div class="row g-3 align-items-center">
            <div class="col-md-3 text-center">
                <div class="display-4 fw-bold text-<?= $scCls ?>"><?= $score ?>%</div>
                <div class="small text-muted">Abuse Score</div>
            </div>
            <div class="col-md-9">
                <table class="table table-sm mb-0">
                    <tr><th class="small text-muted border-0" style="width:40%">IP</th><td class="font-monospace small border-0"><?= htmlspecialchars($ip) ?></td></tr>
                    <tr><th class="small text-muted">País</th><td class="small"><?= htmlspecialchars($data['countryCode'] ?? '---') ?></td></tr>
                    <tr><th class="small text-muted">ISP</th><td class="small"><?= htmlspecialchars($data['isp'] ?? '---') ?></td></tr>
                    <tr><th class="small text-muted">Total reportes</th><td class="small"><?= $data['totalReports'] ?? 0 ?></td></tr>
                    <tr><th class="small text-muted">Último reporte</th><td class="small"><?= htmlspecialchars($data['lastReportedAt'] ?? 'Nunca') ?></td></tr>
                </table>
            </div>
        </div>
        <?php
        return;
    }
    echo '<div class="alert alert-danger small">Error al consultar AbuseIPDB (HTTP ' . $httpCode . ')</div>';
    return;
}
?>
<div class="text-center py-3">
    <div class="mb-3">
        <span class="badge bg-primary fs-6 mb-1"><?= htmlspecialchars($ip) ?></span>
        <div class="small text-muted">IP de <?= htmlspecialchars($dominio) ?></div>
    </div>
    <a href="https://www.abuseipdb.com/check/<?= urlencode($ip) ?>" target="_blank" rel="noopener"
       class="btn btn-warning fw-bold">
        🔍 Ver en AbuseIPDB
    </a>
    <div class="mt-3 alert alert-info small text-start">
        Para activar la comprobación automática, define la constante <code>ABUSEIPDB_KEY</code> en un archivo <code>config.php</code>.
    </div>
</div>
