<?php
// modules/ssl.php - Comprobación de Certificado SSL
// Expects: $dominio (string)

$context = stream_context_create(['ssl' => [
    'capture_peer_cert' => true,
    'verify_peer'       => false,
    'verify_peer_name'  => false,
]]);
$socket = @stream_socket_client("ssl://{$dominio}:443", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);
if (!$socket) {
    echo '<div class="alert alert-danger small mb-0">❌ Sin SSL: ' . htmlspecialchars($errstr ?: 'No se pudo conectar') . '</div>';
    return;
}
$params = stream_context_get_params($socket);
fclose($socket);
$cert = $params['options']['ssl']['peer_certificate'] ?? null;
if (!$cert) {
    echo '<div class="alert alert-warning small mb-0">⚠️ No se pudo obtener el certificado.</div>';
    return;
}
$info      = openssl_x509_parse($cert);
$validFrom = date('d/m/Y', $info['validFrom_time_t']);
$validTo   = date('d/m/Y', $info['validTo_time_t']);
$daysLeft  = (int)(($info['validTo_time_t'] - time()) / 86400);
$cn        = $info['subject']['CN'] ?? '---';
$issuer    = $info['issuer']['O'] ?? ($info['issuer']['CN'] ?? '---');
$san       = $info['extensions']['subjectAltName'] ?? '---';

$bgClass  = $daysLeft > 30 ? 'bg-success' : ($daysLeft > 7 ? 'bg-warning' : 'bg-danger');
$txtClass = $daysLeft > 30 ? 'text-success' : ($daysLeft > 7 ? 'text-warning' : 'text-danger');
$icon     = $daysLeft > 30 ? '✅' : ($daysLeft > 7 ? '⚠️' : '❌');
?>
<div class="d-flex align-items-center gap-2 mb-2 p-2 rounded" style="background:#f8fafc">
    <span class="fs-5"><?= $icon ?></span>
    <span class="fw-bold <?= $txtClass ?>"><?= $daysLeft ?> días restantes</span>
</div>
<table class="table table-sm mb-0">
    <tr><th class="text-muted small border-0" style="width:40%">Dominio</th><td class="font-monospace small border-0"><?= htmlspecialchars($cn) ?></td></tr>
    <tr><th class="text-muted small">Emisor</th><td class="small"><?= htmlspecialchars($issuer) ?></td></tr>
    <tr><th class="text-muted small">Desde</th><td class="small"><?= $validFrom ?></td></tr>
    <tr><th class="text-muted small">Hasta</th><td class="small"><?= $validTo ?></td></tr>
    <tr><th class="text-muted small">SAN</th><td class="font-monospace" style="font-size:0.7rem;word-break:break-all"><?= htmlspecialchars($san) ?></td></tr>
</table>
