<?php
/**
 * Módulo: DNS Propagation Check
 * Consulta el mismo registro desde múltiples servidores DNS simultáneamente.
 */

$qType = strtoupper(preg_replace('/[^A-Z0-9]/', '', $_GET['type'] ?? 'A'));
$validTypes = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SOA'];
if (!in_array($qType, $validTypes, true)) $qType = 'A';

$servers = [
    ['ip' => '8.8.8.8',       'name' => 'Google',        'location' => 'EE.UU.',   'flag' => '🇺🇸'],
    ['ip' => '8.8.4.4',       'name' => 'Google Alt',    'location' => 'EE.UU.',   'flag' => '🇺🇸'],
    ['ip' => '1.1.1.1',       'name' => 'Cloudflare',    'location' => 'EE.UU.',   'flag' => '🇺🇸'],
    ['ip' => '1.0.0.1',       'name' => 'Cloudflare Alt','location' => 'EE.UU.',   'flag' => '🇺🇸'],
    ['ip' => '9.9.9.9',       'name' => 'Quad9',         'location' => 'EE.UU.',   'flag' => '🇺🇸'],
    ['ip' => '208.67.222.222', 'name' => 'OpenDNS',      'location' => 'EE.UU.',   'flag' => '🇺🇸'],
    ['ip' => '94.140.14.14',   'name' => 'AdGuard',      'location' => 'Chipre',   'flag' => '🇨🇾'],
    ['ip' => '185.228.168.9',  'name' => 'CleanBrowsing', 'location' => 'EE.UU.', 'flag' => '🇺🇸'],
    ['ip' => '77.88.8.8',      'name' => 'Yandex',       'location' => 'Rusia',    'flag' => '🇷🇺'],
    ['ip' => '64.6.64.6',      'name' => 'Neustar',      'location' => 'EE.UU.',   'flag' => '🇺🇸'],
];

$digPath = trim((string)@shell_exec('which dig 2>/dev/null'));

$phpMap = [
    'A' => DNS_A, 'AAAA' => DNS_AAAA, 'MX' => DNS_MX, 'NS' => DNS_NS,
    'TXT' => DNS_TXT, 'CNAME' => DNS_CNAME, 'SOA' => DNS_SOA,
];

$results = [];
foreach ($servers as $srv) {
    $t0      = microtime(true);
    $records = [];
    $status  = 'TIMEOUT';

    if ($digPath) {
        $cmd = sprintf(
            'dig @%s %s %s +time=3 +tries=1 +short +noidnout 2>/dev/null',
            escapeshellarg($srv['ip']),
            escapeshellarg($domain),
            escapeshellarg($qType)
        );
        $out   = (string)@shell_exec($cmd);
        $ms    = (int)round((microtime(true) - $t0) * 1000);
        $lines = array_values(array_filter(
            array_map('trim', explode("\n", $out)),
            fn($l) => $l !== '' && !str_starts_with($l, ';')
        ));
        $records = $lines;
        $status  = $ms > 2900 ? 'TIMEOUT' : (empty($records) ? 'NXDOMAIN' : 'NOERROR');
    } else {
        // dns_get_record() cannot target a specific resolver; it always uses the
        // system resolver, so results are not per-server measurements.
        $res = @dns_get_record($domain, $phpMap[$qType] ?? DNS_A) ?: [];
        $ms  = (int)round((microtime(true) - $t0) * 1000);
        foreach ($res as $r) {
            $records[] = match($qType) {
                'A'              => $r['ip']     ?? '',
                'AAAA'           => $r['ipv6']   ?? '',
                'MX'             => ($r['pri'] ?? '') . ' ' . ($r['target'] ?? ''),
                'NS','CNAME'     => $r['target'] ?? '',
                'TXT'            => $r['txt']    ?? '',
                'SOA'            => sprintf('%s %s %s', $r['mname'] ?? '', $r['rname'] ?? '', $r['serial'] ?? ''),
                default          => json_encode($r),
            };
        }
        $status = empty($records) ? 'NXDOMAIN' : 'NOERROR';
    }

    $results[] = [
        'server'    => $srv['ip'],
        'name'      => $srv['name'],
        'location'  => $srv['location'],
        'flag'      => $srv['flag'],
        'records'   => $records,
        'status'    => $status,
        'ms'        => $ms,
        'dig_used'  => (bool)$digPath,
    ];
}

// Consistencia: ¿todos los servidores devuelven lo mismo?
$uniqueVals = array_unique(array_map(fn($r) => implode('|', $r['records']), $results));
$consistent = count($uniqueVals) === 1 && !in_array('NXDOMAIN', array_column($results, 'status'));

echo json_encode([
    'success'    => true,
    'domain'     => $domain,
    'type'       => $qType,
    'results'    => $results,
    'consistent' => $consistent,
    'unique'     => count($uniqueVals),
]);
