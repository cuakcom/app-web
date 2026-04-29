<?php
// modules/mail_analyze.php - Análisis de Correo (MX, SPF, DKIM, DMARC, Puertos)
// Expects: $dominio (string), esPuertoAbierto() in scope

// MX Records
$mxRecords = @dns_get_record($dominio, DNS_MX) ?: [];
usort($mxRecords, fn($a, $b) => $a['pri'] - $b['pri']);

// SPF
$spf = '';
$txtRecords = @dns_get_record($dominio, DNS_TXT) ?: [];
foreach ($txtRecords as $txt) {
    if (isset($txt['txt']) && str_starts_with($txt['txt'], 'v=spf1')) {
        $spf = $txt['txt'];
        break;
    }
}

// DKIM (selectores habituales)
$dkimSelectors = ['default', 'google', 'mail', 'dkim', 'selector1', 'selector2', 'k1', 'mimecast', 's1', 's2'];
$dkimFound = [];
foreach ($dkimSelectors as $sel) {
    $dkimTxt = @dns_get_record("$sel._domainkey.$dominio", DNS_TXT) ?: [];
    foreach ($dkimTxt as $r) {
        if (isset($r['txt']) && str_contains($r['txt'], 'v=DKIM1')) {
            $dkimFound[] = ['selector' => $sel, 'value' => $r['txt']];
        }
    }
}

// DMARC
$dmarc = '';
$dmarcResult = @dns_get_record("_dmarc.$dominio", DNS_TXT) ?: [];
foreach ($dmarcResult as $r) {
    if (isset($r['txt']) && str_starts_with($r['txt'], 'v=DMARC1')) {
        $dmarc = $r['txt'];
        break;
    }
}

$mailHost  = $mxRecords[0]['target'] ?? $dominio;
$mailPorts = [25 => 'SMTP', 465 => 'SMTPS', 587 => 'SUBMISSION', 110 => 'POP3', 995 => 'POP3S', 143 => 'IMAP', 993 => 'IMAPS'];
?>
<div class="row g-4">
    <div class="col-md-6">
        <h6 class="text-muted small fw-bold text-uppercase mb-2">📨 Registros MX</h6>
        <?php if ($mxRecords): foreach ($mxRecords as $mx): ?>
            <div class="d-flex gap-2 align-items-center mb-1">
                <span class="badge bg-warning text-dark"><?= $mx['pri'] ?></span>
                <span class="font-monospace small"><?= htmlspecialchars(rtrim($mx['target'], '.')) ?></span>
            </div>
        <?php endforeach; else: echo '<div class="text-danger small">❌ Sin registros MX</div>'; endif; ?>

        <h6 class="text-muted small fw-bold text-uppercase mt-3 mb-2">🔒 SPF</h6>
        <?php if ($spf): ?>
            <div class="p-2 rounded" style="background:#f0fdf4">
                <code class="small text-success" style="word-break:break-all"><?= htmlspecialchars($spf) ?></code>
            </div>
        <?php else: echo '<div class="text-danger small">❌ Sin registro SPF</div>'; endif; ?>

        <h6 class="text-muted small fw-bold text-uppercase mt-3 mb-2">🔏 DKIM</h6>
        <?php if ($dkimFound): foreach ($dkimFound as $dk): ?>
            <div class="mb-1">
                <span class="badge bg-success me-1"><?= htmlspecialchars($dk['selector']) ?></span>
                <span class="text-success small">✅ Encontrado</span>
            </div>
        <?php endforeach; else: echo '<div class="text-warning small">⚠️ No encontrado en selectores comunes</div>'; endif; ?>

        <h6 class="text-muted small fw-bold text-uppercase mt-3 mb-2">🛡️ DMARC</h6>
        <?php if ($dmarc): ?>
            <div class="p-2 rounded" style="background:#f0fdf4">
                <code class="small text-success" style="word-break:break-all"><?= htmlspecialchars($dmarc) ?></code>
            </div>
        <?php else: echo '<div class="text-danger small">❌ Sin registro DMARC</div>'; endif; ?>
    </div>

    <div class="col-md-6">
        <h6 class="text-muted small fw-bold text-uppercase mb-2">🔌 Puertos en <?= htmlspecialchars($mailHost) ?></h6>
        <?php foreach ($mailPorts as $port => $label):
            $isOpen = esPuertoAbierto($mailHost, $port, 2); ?>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge <?= $isOpen ? 'bg-success' : 'bg-secondary' ?>" style="min-width:90px"><?= $label ?></span>
                <span class="text-muted small"><?= $port ?></span>
                <span class="small <?= $isOpen ? 'text-success' : 'text-muted' ?>"><?= $isOpen ? '✅ ABIERTO' : '⚫ CERRADO' ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
