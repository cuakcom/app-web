<?php
// modules/relay_test.php - Test Relay & Entrega SMTP
// Expects: $dominio (string)

$mxResult = @dns_get_record($dominio, DNS_MX) ?: [];
if (!$mxResult) {
    echo '<div class="alert alert-warning small mb-0">⚠️ No se encontraron registros MX para este dominio.</div>';
    return;
}
usort($mxResult, fn($a, $b) => $a['pri'] - $b['pri']);
$mailHost = rtrim($mxResult[0]['target'], '.');
$ip       = gethostbyname($mailHost);

// Conectar SMTP puerto 25
$banner    = '';
$ehloLines = [];
$starttls  = false;
$connected = false;

$fp = @fsockopen($mailHost, 25, $errno, $errstr, 5);
if ($fp) {
    $connected = true;
    stream_set_timeout($fp, 3);
    $banner = trim(fgets($fp, 512));
    fputs($fp, "EHLO cuakcom-probe.com\r\n");
    for ($i = 0; $i < 30; $i++) {
        $line = fgets($fp, 512);
        if (!$line) break;
        $line = trim($line);
        $ehloLines[] = $line;
        if (stripos($line, 'STARTTLS') !== false) $starttls = true;
        if (strlen($line) > 3 && $line[3] === ' ') break;
    }
    fputs($fp, "QUIT\r\n");
    fclose($fp);
}

// Puerto 587 check
$port587 = @fsockopen($mailHost, 587, $e587, $es587, 3);
$has587   = false;
if ($port587) { $has587 = true; fclose($port587); }

// Puerto 465 check
$port465 = @fsockopen($mailHost, 465, $e465, $es465, 3);
$has465  = false;
if ($port465) { $has465 = true; fclose($port465); }
?>
<div class="row g-3">
    <div class="col-md-6">
        <h6 class="text-muted small fw-bold text-uppercase mb-2">🖥️ Servidor MX Principal</h6>
        <div class="mb-1"><span class="badge bg-info text-dark">HOST</span> <code class="small"><?= htmlspecialchars($mailHost) ?></code></div>
        <div class="mb-3"><span class="badge bg-primary">IP</span> <code class="small"><?= htmlspecialchars($ip) ?></code></div>

        <?php if ($banner): ?>
        <h6 class="text-muted small fw-bold text-uppercase mb-2">📡 Banner SMTP</h6>
        <div class="p-2 rounded font-monospace small" style="background:#1e293b;color:#f8fafc;word-break:break-all"><?= htmlspecialchars($banner) ?></div>
        <?php elseif (!$connected): ?>
        <div class="alert alert-warning small">⚠️ No se pudo conectar al puerto 25</div>
        <?php endif; ?>
    </div>

    <div class="col-md-6">
        <h6 class="text-muted small fw-bold text-uppercase mb-2">⚙️ Capacidades</h6>
        <div class="d-flex gap-2 align-items-center mb-2">
            <span class="badge <?= $starttls ? 'bg-success' : 'bg-danger' ?>">STARTTLS</span>
            <span class="small"><?= $starttls ? '✅ Disponible' : '❌ No disponible' ?></span>
        </div>
        <div class="d-flex gap-2 align-items-center mb-2">
            <span class="badge <?= $has587 ? 'bg-success' : 'bg-secondary' ?>">Puerto 587</span>
            <span class="small"><?= $has587 ? '✅ Abierto (submission)' : '⚫ Cerrado' ?></span>
        </div>
        <div class="d-flex gap-2 align-items-center mb-3">
            <span class="badge <?= $has465 ? 'bg-success' : 'bg-secondary' ?>">Puerto 465</span>
            <span class="small"><?= $has465 ? '✅ Abierto (SMTPS)' : '⚫ Cerrado' ?></span>
        </div>

        <?php if ($ehloLines): ?>
        <h6 class="text-muted small fw-bold text-uppercase mb-2">📋 Respuesta EHLO</h6>
        <div class="p-2 rounded font-monospace" style="font-size:0.7rem;background:#1e293b;color:#f8fafc">
            <?php foreach ($ehloLines as $line): ?>
                <div><?= htmlspecialchars($line) ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
