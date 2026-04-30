<?php
/**
 * Módulo: SSL/TLS Scan extendido
 * Analiza protocolos, cipher suite y cadena de certificados.
 */

$host = $domain;
$port = 443;

$result = [
    'host'             => $host,
    'port'             => $port,
    'protocols'        => [],
    'cipher'           => null,
    'negotiated'       => null,
    'key_bits'         => null,
    'forward_secrecy'  => false,
    'hsts'             => false,
    'hsts_header'      => null,
    'chain'            => [],
    'san'              => [],
];

// ── Cadena y SAN via PHP stream_socket_client ─────────────────────────────────
$sslCtx = stream_context_create(['ssl' => [
    'capture_peer_cert_chain' => true,
    'capture_peer_cert'       => true,
    'verify_peer'             => false,
    'verify_peer_name'        => false,
    'allow_self_signed'       => true,
]]);
$sock = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, 8, STREAM_CLIENT_CONNECT, $sslCtx);
if ($sock) {
    $params = stream_context_get_params($sock);
    $chain  = $params['options']['ssl']['peer_certificate_chain'] ?? [];
    foreach ($chain as $cert) {
        $parsed = @openssl_x509_parse($cert);
        if (!$parsed) continue;
        $entry = [
            'subject' => $parsed['subject']['CN'] ?? ($parsed['subject']['O'] ?? '?'),
            'issuer'  => $parsed['issuer']['CN']  ?? ($parsed['issuer']['O']  ?? '?'),
            'not_before' => date('d/m/Y', $parsed['validFrom_time_t'] ?? 0),
            'not_after'  => date('d/m/Y', $parsed['validTo_time_t']   ?? 0),
            'days_left'  => (int)round(($parsed['validTo_time_t'] - time()) / 86400),
            'is_ca'      => !empty($parsed['extensions']['basicConstraints']) &&
                            str_contains($parsed['extensions']['basicConstraints'], 'CA:TRUE'),
        ];
        // Fingerprint SHA-256
        openssl_x509_export($cert, $certPem);
        $entry['fingerprint'] = strtoupper(implode(':', str_split(hash('sha256', base64_decode(
            str_replace(['-----BEGIN CERTIFICATE-----','-----END CERTIFICATE-----',"\n",' '], '', $certPem)
        )), 2)));
        $result['chain'][] = $entry;
    }

    // SAN from leaf cert
    $leafCert = $params['options']['ssl']['peer_certificate'] ?? null;
    if ($leafCert) {
        $leafParsed = @openssl_x509_parse($leafCert);
        $altNames   = $leafParsed['extensions']['subjectAltName'] ?? '';
        foreach (explode(',', $altNames) as $san) {
            $san = trim($san);
            if (str_starts_with($san, 'DNS:')) $result['san'][] = substr($san, 4);
        }
    }
    fclose($sock);
}

// ── HSTS ─────────────────────────────────────────────────────────────────────
$hstCtx = stream_context_create([
    'http' => ['timeout' => 5, 'ignore_errors' => true, 'max_redirects' => 0, 'follow_location' => false],
    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
]);
$hstHeaders = @get_headers('https://' . $host, false, $hstCtx) ?: [];
foreach ($hstHeaders as $h) {
    if (stripos($h, 'Strict-Transport-Security') !== false) {
        $result['hsts']        = true;
        $result['hsts_header'] = $h;
        break;
    }
}

// ── Protocolo, cipher y versiones via openssl s_client ───────────────────────
$opensslBin = trim((string)@shell_exec('which openssl 2>/dev/null'));

if ($opensslBin) {
    // Negociación completa (mejor protocolo disponible)
    $cmd = sprintf(
        'echo Q | timeout 8 %s s_client -connect %s -servername %s 2>&1',
        escapeshellarg($opensslBin),
        escapeshellarg("{$host}:{$port}"),
        escapeshellarg($host)
    );
    $out = (string)@shell_exec($cmd);

    if (preg_match('/Cipher\s*:\s*(.+)/i', $out, $m))   $result['cipher']      = trim($m[1]);
    if (preg_match('/Protocol\s*:\s*(.+)/i', $out, $m)) $result['negotiated']   = trim($m[1]);
    if (preg_match('/Server public key is (\d+)/i', $out, $m)) $result['key_bits'] = (int)$m[1];
    if ($result['cipher']) {
        $result['forward_secrecy'] = (bool)preg_match('/ECDHE|DHE/i', $result['cipher']);
    }

    // Test each TLS version
    $tlsFlags = [
        'TLS 1.0' => '-tls1',
        'TLS 1.1' => '-tls1_1',
        'TLS 1.2' => '-tls1_2',
        'TLS 1.3' => '-tls1_3',
    ];
    foreach ($tlsFlags as $label => $flag) {
        $cmd2 = sprintf(
            'echo Q | timeout 5 %s s_client -connect %s -servername %s %s 2>&1',
            escapeshellarg($opensslBin),
            escapeshellarg("{$host}:{$port}"),
            escapeshellarg($host),
            $flag
        );
        $out2 = (string)@shell_exec($cmd2);
        // Cipher : 0000 means failed handshake; require a real cipher name
        $supported = (bool)preg_match('/Cipher\s*:\s*(?!0{3,})[A-Z0-9_\-]{3,}/i', $out2);
        $result['protocols'][] = [
            'version'   => $label,
            'supported' => $supported,
            'secure'    => in_array($label, ['TLS 1.2', 'TLS 1.3'], true),
        ];
    }
} else {
    // Fallback: try via PHP crypto methods
    $cryptoMethods = [
        'TLS 1.0' => STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT,
        'TLS 1.1' => STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT,
        'TLS 1.2' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
    ];
    foreach ($cryptoMethods as $label => $method) {
        $ctx2 = stream_context_create(['ssl' => [
            'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true,
        ]]);
        $s2 = @stream_socket_client("tcp://{$host}:{$port}", $e, $es, 4);
        $supported = false;
        if ($s2) {
            $supported = @stream_socket_enable_crypto($s2, true, $method) === true;
            fclose($s2);
        }
        $result['protocols'][] = [
            'version'   => $label,
            'supported' => $supported,
            'secure'    => $label === 'TLS 1.2',
        ];
    }
    // TLS 1.3 via default
    $s3 = @stream_socket_client("ssl://{$host}:{$port}", $e, $es, 5, STREAM_CLIENT_CONNECT, $sslCtx);
    $result['protocols'][] = ['version' => 'TLS 1.3', 'supported' => (bool)$s3, 'secure' => true];
    if ($s3) fclose($s3);
}

$result['success'] = true;
echo json_encode($result);
