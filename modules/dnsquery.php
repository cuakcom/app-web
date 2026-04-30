<?php
/**
 * Módulo: Consulta DNS personalizada
 * Permite elegir servidor DNS, tipo de query y puerto
 * Variables disponibles: $domain (string, sanitizado por api.php)
 *
 * Parámetros extra vía GET:
 *   qtype   – tipo DNS (A, AAAA, MX, …)
 *   qserver – servidor DNS (IP o hostname)
 *   qport   – puerto (por defecto 53)
 */

// ── Validar parámetros extra ──────────────────────────────────────────────────
$validTypes = ['A','AAAA','CNAME','MX','NS','TXT','SOA','SRV','CAA',
               'PTR','ANY','NAPTR','DNSKEY','DS','TLSA','CERT','HINFO'];
$qType   = strtoupper(preg_replace('/[^A-Z0-9]/', '', $_GET['type']   ?? 'A'));
$qServer = preg_replace('/[^a-zA-Z0-9.\-]/', '', $_GET['server'] ?? '8.8.8.8');
$qPort   = max(1, min(65535, (int)($_GET['port'] ?? 53)));

if (!in_array($qType, $validTypes, true)) $qType = 'A';
if (!$qServer || (!filter_var($qServer, FILTER_VALIDATE_IP) &&
    !preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.\-]{0,252}$/', $qServer))) {
    $qServer = '8.8.8.8';
}

$records   = [];
$raw       = '';
$queryMs   = null;
$rcode     = null;
$usedDig   = false;
$usedServer= "{$qServer}:{$qPort}";

// ── Intento con dig ───────────────────────────────────────────────────────────
$digPath = trim((string)@shell_exec('which dig 2>/dev/null'));
if ($digPath) {
    $usedDig = true;
    $cmd = sprintf('dig %s -p %d %s %s +time=5 +tries=1 +noidnout 2>&1',
        escapeshellarg('@' . $qServer),
        $qPort,
        escapeshellarg($domain),
        escapeshellarg($qType)
    );
    $raw = (string)@shell_exec($cmd);

    // Metadata
    if (preg_match('/Query time:\s*(\d+)\s*msec/i',  $raw, $m)) $queryMs   = (int)$m[1];
    if (preg_match('/status:\s*([A-Z]+)/i',           $raw, $m)) $rcode     = $m[1];
    if (preg_match('/SERVER:\s*([\d.]+)#(\d+)/i',     $raw, $m)) $usedServer = $m[1] . ':' . $m[2];

    // Parsear sección ANSWER
    $inAnswer = false;
    foreach (explode("\n", $raw) as $line) {
        $line = rtrim($line);
        if (str_contains($line, 'ANSWER SECTION'))  { $inAnswer = true;  continue; }
        if ($inAnswer && str_starts_with($line, ';;')) { $inAnswer = false; }
        if ($inAnswer && trim($line) !== '' && !str_starts_with($line, ';')) {
            $parts = preg_split('/\s+/', trim($line), 5);
            if (count($parts) >= 5) {
                $records[] = [
                    'name'  => $parts[0],
                    'ttl'   => (int)$parts[1],
                    'class' => $parts[2],
                    'type'  => $parts[3],
                    'value' => $parts[4],
                ];
            }
        }
    }
} else {
    // ── Fallback: dns_get_record() (sin servidor personalizado) ────────────────
    $phpMap = [
        'A'    => DNS_A,    'AAAA' => DNS_AAAA, 'CNAME' => DNS_CNAME,
        'MX'   => DNS_MX,   'NS'   => DNS_NS,    'TXT'   => DNS_TXT,
        'SOA'  => DNS_SOA,  'SRV'  => DNS_SRV,   'PTR'   => DNS_PTR,
        'ANY'  => DNS_ANY,  'CAA'  => defined('DNS_CAA') ? DNS_CAA : 8192,
    ];
    $const = $phpMap[$qType] ?? DNS_A;
    $res = @dns_get_record($domain, $const) ?: [];
    foreach ($res as $r) {
        $data = match($qType) {
            'A'     => $r['ip']     ?? '',
            'AAAA'  => $r['ipv6']   ?? '',
            'CNAME', 'NS', 'PTR', 'MX' => $r['target'] ?? '',
            'TXT'   => $r['txt']    ?? '',
            'SOA'   => sprintf('%s %s %s', $r['mname'] ?? '', $r['rname'] ?? '', $r['serial'] ?? ''),
            'SRV'   => sprintf('%s:%d', $r['target'] ?? '', $r['port'] ?? 0),
            default => json_encode($r),
        };
        $records[] = [
            'name'  => $domain . '.',
            'ttl'   => $r['ttl'] ?? 0,
            'class' => 'IN',
            'type'  => $qType,
            'value' => $data,
        ];
    }
    $rcode      = empty($records) ? 'NXDOMAIN' : 'NOERROR';
    $usedServer = 'Resolver del sistema (sin servidor personalizado)';
}

echo json_encode([
    'success'      => true,
    'domain'       => $domain,
    'type'         => $qType,
    'server_used'  => $usedServer,
    'port'         => $qPort,
    'records'      => $records,
    'count'        => count($records),
    'status'       => $rcode,
    'query_time_ms'=> $queryMs,
    'raw_output'   => $raw,
    'source'       => $usedDig ? 'dig' : 'dns_get_record',
]);
