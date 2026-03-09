<?php
/**
 * Módulo: SMTP Relay Test + Simulación de entrega
 * Comprueba si el servidor acepta relay abierto y simula el flujo SMTP.
 */

// Obtener MX
$mxHosts  = [];
$mxWeights = [];
if (!@getmxrr($domain, $mxHosts, $mxWeights)) {
    echo json_encode(['success' => false, 'error' => 'No se encontraron registros MX para el dominio']);
    exit;
}
array_multisort($mxWeights, SORT_ASC, $mxHosts);
$mxHost = $mxHosts[0];
$mxIp   = @gethostbyname($mxHost);

/**
 * Ejecuta una conversación SMTP y devuelve el log de comandos/respuestas.
 */
function smtpDialog(string $host, int $port, array $commands, int $timeout = 10): array {
    $log  = [];
    $sock = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$sock) {
        return [['dir' => 'ERROR', 'msg' => "No se pudo conectar al puerto {$port}: {$errstr} ({$errno})", 'code' => 0]];
    }
    stream_set_timeout($sock, $timeout);

    // Banner
    $banner = rtrim((string)@fgets($sock, 1024));
    $log[]  = ['dir' => '<<<', 'msg' => $banner, 'code' => (int)substr($banner, 0, 3)];

    foreach ($commands as $cmd) {
        fputs($sock, $cmd . "\r\n");
        $log[]  = ['dir' => '>>>', 'msg' => $cmd, 'code' => null];
        $resp   = '';
        $tries  = 0;
        do {
            $line  = (string)@fgets($sock, 1024);
            $resp .= $line;
            $tries++;
        } while ($line && preg_match('/^\d{3}-/', $line) && $tries < 20);

        $code   = (int)substr(trim($resp), 0, 3);
        $log[]  = ['dir' => '<<<', 'msg' => rtrim($resp), 'code' => $code];

        // Abort on hard fail
        if ($code >= 500) break;
    }

    fputs($sock, "QUIT\r\n");
    $log[] = ['dir' => '>>>', 'msg' => 'QUIT', 'code' => null];
    $bye   = rtrim((string)@fgets($sock, 256));
    $log[] = ['dir' => '<<<', 'msg' => $bye, 'code' => (int)substr($bye, 0, 3)];
    fclose($sock);
    return $log;
}

function lastCode(array $log): int {
    foreach (array_reverse($log) as $e) {
        if (isset($e['code']) && $e['code'] > 0) return $e['code'];
    }
    return 0;
}

// ── Test 1: Open Relay ────────────────────────────────────────────────────────
// Intentamos hacer relay desde un dominio externo a otro externo
$relayLog = smtpDialog($mxHost, 25, [
    'EHLO relay-test.cuakcom.com',
    'MAIL FROM:<test@external-domain.org>',
    'RCPT TO:<test@gmail.com>',
]);
$relayRcptCode = 0;
// El código del RCPT TO es el determinante
foreach ($relayLog as $i => $entry) {
    if ($entry['dir'] === '>>>' && str_starts_with($entry['msg'], 'RCPT TO')) {
        $relayRcptCode = $relayLog[$i + 1]['code'] ?? 0;
    }
}
$isOpenRelay = ($relayRcptCode === 250 || $relayRcptCode === 251);

// ── Test 2: Delivery simulation ───────────────────────────────────────────────
$testEmail = $_GET['email'] ?? '';
if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
    $testEmail = 'postmaster@' . $domain;
}

$delivLog = smtpDialog($mxHost, 25, [
    'EHLO check.cuakcom.com',
    "MAIL FROM:<check@cuakcom.com>",
    "RCPT TO:<{$testEmail}>",
]);

$delivRcptCode = 0;
foreach ($delivLog as $i => $entry) {
    if ($entry['dir'] === '>>>' && str_starts_with($entry['msg'], 'RCPT TO')) {
        $delivRcptCode = $delivLog[$i + 1]['code'] ?? 0;
    }
}

$delivResult = match(true) {
    $delivRcptCode === 250 || $delivRcptCode === 251 => 'accepted',
    $delivRcptCode === 550 || $delivRcptCode === 551 => 'rejected',
    $delivRcptCode === 452                           => 'deferred',
    $delivRcptCode >= 500                            => 'rejected',
    $delivRcptCode >= 400                            => 'deferred',
    default                                          => 'unknown',
};

echo json_encode([
    'success'       => true,
    'mx_host'       => $mxHost,
    'mx_ip'         => $mxIp !== $mxHost ? $mxIp : null,
    'open_relay'    => $isOpenRelay,
    'relay_code'    => $relayRcptCode,
    'relay_log'     => $relayLog,
    'test_email'    => $testEmail,
    'delivery_code' => $delivRcptCode,
    'delivery_result'=> $delivResult,
    'delivery_log'  => $delivLog,
]);
