<?php
// modules/email_headers.php - Parser de Cabeceras de Correo
// Expects: $rawHeaders (string)

function parseEmailHeaders(string $raw): array {
    // Desdoblar líneas de continuación
    $raw = preg_replace("/\r?\n([ \t])/", ' ', $raw);
    $headers  = [];
    $received = [];

    $currentKey = '';
    $currentVal = '';

    $lines = preg_split("/\r?\n/", $raw);
    foreach ($lines as $line) {
        if (trim($line) === '') {
            // Fin de cabeceras — guardar y limpiar para que el bloque post-bucle no duplique
            if ($currentKey) {
                $k = strtolower($currentKey);
                if ($k === 'received') $received[] = trim($currentVal);
                else $headers[$k][] = trim($currentVal);
                $currentKey = '';
            }
            break;
        }
        if (preg_match('/^([A-Za-z0-9\-]+):\s*(.*)$/', $line, $m)) {
            if ($currentKey) {
                $k = strtolower($currentKey);
                if ($k === 'received') $received[] = trim($currentVal);
                else $headers[$k][] = trim($currentVal);
            }
            $currentKey = $m[1];
            $currentVal = $m[2];
        } elseif ($currentKey && preg_match('/^[ \t]/', $line)) {
            $currentVal .= ' ' . trim($line);
        }
    }
    if ($currentKey) {
        $k = strtolower($currentKey);
        if ($k === 'received') $received[] = trim($currentVal);
        else $headers[$k][] = trim($currentVal);
    }

    return ['headers' => $headers, 'received' => $received];
}

$parsed   = parseEmailHeaders($rawHeaders);
$headers  = $parsed['headers'];
$received = array_reverse($parsed['received']); // Orden cronológico (más antiguo primero)

// Cabeceras clave en el orden deseado
$keyHeaders = [
    'from'           => '📤 Remitente',
    'to'             => '📥 Destinatario',
    'cc'             => '📋 CC',
    'subject'        => '📌 Asunto',
    'date'           => '📅 Fecha',
    'reply-to'       => '↩️ Responder a',
    'message-id'     => '🆔 Message-ID',
    'content-type'   => '📄 Tipo de contenido',
    'x-mailer'       => '📮 Mailer',
    'user-agent'     => '🌐 User-Agent',
    'x-originating-ip' => '🖥️ IP Origen',
    'x-spam-status'  => '🚫 Spam Status',
    'x-spam-score'   => '🎯 Spam Score',
    'x-spam-flag'    => '🚩 Spam Flag',
    'authentication-results' => '🔐 Auth Results',
    'dkim-signature' => '🔏 DKIM',
    'arc-seal'       => '🔗 ARC Seal',
    'arc-authentication-results' => '🛡️ ARC Auth',
];

$displayed = [];
?>

<?php if (empty($headers) && empty($received)): ?>
<div class="alert alert-warning">No se pudieron parsear cabeceras del contenido proporcionado.</div>
<?php return; endif; ?>

<div class="row g-3">
    <!-- Columna izquierda: Datos del mensaje -->
    <div class="col-md-6">
        <h6 class="text-muted small fw-bold text-uppercase mb-3">📋 Datos del mensaje</h6>
        <table class="table table-sm table-bordered">
            <tbody>
            <?php foreach ($keyHeaders as $key => $label):
                if (!isset($headers[$key])) continue;
                $displayed[] = $key; ?>
                <tr>
                    <th class="small text-muted" style="width:38%;background:#f8fafc;white-space:nowrap;vertical-align:top"><?= $label ?></th>
                    <td class="small font-monospace" style="word-break:break-all;vertical-align:top"><?= htmlspecialchars(implode('<br>', $headers[$key])) ?></td>
                </tr>
            <?php endforeach; ?>

            <?php // Resto de cabeceras no mostradas aún
            foreach ($headers as $key => $vals):
                if (in_array($key, $displayed)) continue;
                $displayed[] = $key; ?>
                <tr>
                    <th class="small text-muted" style="background:#f8fafc;white-space:nowrap;vertical-align:top"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $key))) ?></th>
                    <td class="small font-monospace" style="word-break:break-all;vertical-align:top"><?= htmlspecialchars(implode(', ', $vals)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Columna derecha: Servidores de tránsito -->
    <div class="col-md-6">
        <h6 class="text-muted small fw-bold text-uppercase mb-3">🖥️ Servidores de tránsito</h6>
        <?php if ($received): ?>
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th class="small text-center" style="width:36px;background:#f8fafc">#</th>
                    <th class="small" style="background:#f8fafc">Salto (Received)</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($received as $idx => $rcv): ?>
                <tr>
                    <td class="text-center small text-muted fw-bold"><?= $idx + 1 ?></td>
                    <td class="font-monospace text-muted" style="font-size:0.7rem;word-break:break-all"><?= htmlspecialchars($rcv) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="text-muted small">No se encontraron saltos de servidores (Received headers).</div>
        <?php endif; ?>
    </div>
</div>
