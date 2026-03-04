<?php
/**
 * Módulo: Registros DNS (A, AAAA, CNAME, MX, NS, TXT)
 * Variables disponibles: $domain (string, sanitizado por api.php)
 */

$typeMap = [
    'A'     => DNS_A,
    'AAAA'  => DNS_AAAA,
    'CNAME' => DNS_CNAME,
    'MX'    => DNS_MX,
    'NS'    => DNS_NS,
    'TXT'   => DNS_TXT,
];

$records = [];
foreach ($typeMap as $type => $const) {
    $res = @dns_get_record($domain, $const);
    if (empty($res)) continue;
    foreach ($res as $r) {
        $value = $r['ip'] ?? $r['ipv6'] ?? $r['target'] ?? $r['txt'] ?? $r['host'] ?? '';
        if ($value === '') continue;
        $entry = [
            'type'  => $type,
            'value' => $value,
            'ttl'   => $r['ttl'] ?? null,
        ];
        if (isset($r['pri'])) {
            $entry['priority'] = (int)$r['pri'];
        }
        $records[] = $entry;
    }
}

echo json_encode(['success' => true, 'records' => $records]);
