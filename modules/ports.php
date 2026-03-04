<?php
/**
 * Módulo: Escáner de puertos (parallel, ~0.6s máx)
 * Variables disponibles: $domain (string, sanitizado por api.php)
 */

/**
 * Escanea múltiples puertos en paralelo usando sockets no bloqueantes + stream_select.
 * Tiempo máximo ≈ $timeout, independientemente del número de puertos.
 */
function parallelPortScan(string $host, array $ports, float $timeout = 0.6): array {
    $sockets = [];
    $results = array_fill_keys($ports, false);

    foreach ($ports as $port) {
        $sock = @stream_socket_client(
            "tcp://{$host}:{$port}", $errno, $errstr, 0,
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT
        );
        if ($sock !== false) {
            stream_set_blocking($sock, false);
            $sockets[$port] = $sock;
        }
    }

    if (empty($sockets)) return $results;

    $deadline  = microtime(true) + $timeout;
    $remaining = $sockets;

    while (!empty($remaining)) {
        $left = $deadline - microtime(true);
        if ($left <= 0) break;

        $write = array_values($remaining);
        $null  = null;
        $us    = min((int)($left * 1_000_000), 500_000);

        $n = @stream_select($null, $write, $null, 0, $us);
        if ($n === false || $n === 0) break;

        foreach ($write as $sock) {
            $port = array_search($sock, $remaining, true);
            if ($port !== false) {
                $peer            = @stream_socket_get_name($sock, true);
                $results[$port]  = ($peer !== false);
                unset($remaining[$port]);
                @fclose($sock);
            }
        }
    }

    foreach ($remaining as $sock) {
        @fclose($sock);
    }

    return $results;
}

$categories = [
    'Web'            => [80 => 'HTTP', 443 => 'HTTPS', 8080 => 'Proxy'],
    'Correo'         => [25 => 'SMTP', 465 => 'SMTPS', 587 => 'SMTP-S', 110 => 'POP3', 995 => 'POP3S', 143 => 'IMAP', 993 => 'IMAPS'],
    'Bases de datos' => [3306 => 'MySQL', 5432 => 'PostgreSQL', 1433 => 'SQL Server'],
    'Acceso'         => [22 => 'SSH', 3389 => 'RDP', 53 => 'DNS'],
];

$allPorts = [];
foreach ($categories as $ports) {
    $allPorts = array_merge($allPorts, array_keys($ports));
}

$scanResults = parallelPortScan($domain, $allPorts);

$result = [];
foreach ($categories as $cat => $ports) {
    $items = [];
    foreach ($ports as $port => $label) {
        $items[] = [
            'port'  => $port,
            'label' => $label,
            'open'  => $scanResults[$port] ?? false,
        ];
    }
    $result[] = ['category' => $cat, 'ports' => $items];
}

echo json_encode(['success' => true, 'categories' => $result]);
