<?php
// modules/redirects.php - Cadena de Redirecciones HTTP
// Expects: $dominio (string)

$url     = "http://{$dominio}";
$chain   = [];
$visited = [];

for ($i = 0; $i < 10; $i++) {
    if (in_array($url, $visited)) {
        $chain[] = ['url' => $url, 'code' => 'LOOP', 'error' => 'Bucle detectado'];
        break;
    }
    $visited[] = $url;

    if (!function_exists('curl_init')) {
        $headers = @get_headers($url, 1);
        if (!$headers) { $chain[] = ['url' => $url, 'code' => 'ERR', 'error' => 'Sin respuesta']; break; }
        $code     = substr($headers[0], 9, 3);
        $location = is_array($headers['Location'] ?? null) ? end($headers['Location']) : ($headers['Location'] ?? null);
    } else {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_NOBODY         => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'CuakcomBot/1.1',
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!$code) { $chain[] = ['url' => $url, 'code' => 'ERR', 'error' => 'Sin respuesta']; break; }
        $location = null;
        if (preg_match('/^Location:\s*(.+)$/im', $response, $m)) {
            $location = trim($m[1]);
        }
    }

    if ($location && !preg_match('#^https?://#', $location)) {
        $base     = parse_url($url);
        $location = $base['scheme'] . '://' . $base['host'] . $location;
    }

    $chain[] = ['url' => $url, 'code' => $code, 'location' => $location ?? null, 'error' => null];
    if ($code < 300 || $code >= 400 || !$location) break;
    $url = $location;
}
?>
<div class="redirect-chain">
<?php foreach ($chain as $idx => $step):
    $codeStr = (string)$step['code'];
    $badgeClass = match(true) {
        $codeStr[0] === '2'          => 'bg-success',
        $codeStr[0] === '3'          => 'bg-warning text-dark',
        $codeStr >= '400'            => 'bg-danger',
        default                      => 'bg-secondary',
    };
?>
    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
        <span class="text-muted small" style="min-width:18px"><?= $idx + 1 ?></span>
        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($step['code']) ?></span>
        <span class="font-monospace small text-truncate" style="max-width:220px"><?= htmlspecialchars($step['url']) ?></span>
        <?php if ($step['error']): ?>
            <span class="text-danger small"><?= htmlspecialchars($step['error']) ?></span>
        <?php endif; ?>
    </div>
    <?php if (!empty($step['location'])): ?>
        <div class="ms-4 mb-2 text-muted small">↳ <?= htmlspecialchars($step['location']) ?></div>
    <?php endif; ?>
<?php endforeach; ?>
</div>
