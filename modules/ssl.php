<?php
/**
 * Módulo: Información del certificado SSL/TLS
 * Variables disponibles: $domain (string, sanitizado por api.php)
 */

$context = stream_context_create([
    'ssl' => [
        'capture_peer_cert' => true,
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'SNI_enabled'       => true,
        'peer_name'         => $domain,
    ],
]);

$socket = @stream_socket_client(
    "ssl://{$domain}:443", $errno, $errstr, 5,
    STREAM_CLIENT_CONNECT, $context
);

if (!$socket) {
    echo json_encode(['success' => false, 'error' => "Puerto 443 no accesible: {$errstr}"]);
    exit;
}

$params = stream_context_get_params($socket);
fclose($socket);

$cert = $params['options']['ssl']['peer_certificate'] ?? null;
if (!$cert) {
    echo json_encode(['success' => false, 'error' => 'No se pudo leer el certificado SSL']);
    exit;
}

$info     = openssl_x509_parse($cert);
$daysLeft = (int)ceil(($info['validTo_time_t'] - time()) / 86400);

echo json_encode([
    'success'    => true,
    'subject'    => $info['subject']['CN'] ?? $domain,
    'issuer'     => $info['issuer']['CN'] ?? ($info['issuer']['O'] ?? 'Desconocido'),
    'valid_from' => date('d/m/Y', $info['validFrom_time_t']),
    'valid_to'   => date('d/m/Y', $info['validTo_time_t']),
    'days_left'  => $daysLeft,
    'expired'    => $daysLeft < 0,
    'warning'    => ($daysLeft >= 0 && $daysLeft < 30),
]);
