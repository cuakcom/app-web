<?php
/**
 * Módulo: Registros DNS extendido
 * Variables disponibles: $domain (string, sanitizado por api.php)
 * Parámetro opcional: $_GET['types'] (lista separada por comas de tipos a consultar)
 *
 * Tipos soportados:
 *   Estándar PHP: A, AAAA, CNAME, MX, NS, TXT, SOA, SRV, CAA
 *   Especiales:   SPF (TXT v=spf1), DMARC (_dmarc.), DKIM (selectores comunes),
 *                 MTA-STS (_mta-sts.), BIMI (default._bimi.)
 */

$allTypes = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SOA', 'SRV', 'CAA',
             'SPF', 'DMARC', 'DKIM', 'MTA-STS', 'BIMI'];

// Leer tipos solicitados
$requested = isset($_GET['types']) ? trim($_GET['types']) : '';
if ($requested !== '') {
    $parsed = array_filter(array_map(
        fn($t) => strtoupper(trim($t)),
        explode(',', $requested)
    ));
    $activeTypes = array_values(array_intersect($parsed, $allTypes));
} else {
    $activeTypes = $allTypes;
}

// Mapa de constantes PHP para tipos estándar
$phpTypeMap = [
    'A'     => DNS_A,
    'AAAA'  => DNS_AAAA,
    'CNAME' => DNS_CNAME,
    'MX'    => DNS_MX,
    'NS'    => DNS_NS,
    'TXT'   => DNS_TXT,
    'SOA'   => DNS_SOA,
    'SRV'   => DNS_SRV,
    'CAA'   => defined('DNS_CAA') ? DNS_CAA : 8192,
];

$records = [];
$arsysNs = false;

// Helper: detectar si un valor (IP o hostname) pertenece a ARSYS
$isArsys = function(string $value, string $type): bool {
    if ($type === 'A') {
        foreach (['217.76.', '82.223.', '82.233.'] as $range) {
            if (strpos($value, $range) === 0) return true;
        }
    }
    if (in_array($type, ['NS', 'MX', 'CNAME', 'SRV'])) {
        if (preg_match('/\.(servidoresdns\.net|serviciodecorreo\.es)\.?$/i', $value)) return true;
    }
    return false;
};

foreach ($activeTypes as $type) {
    switch ($type) {

        // ── Tipos estándar via dns_get_record() ──────────────────────────
        case 'A':
        case 'AAAA':
        case 'CNAME':
        case 'MX':
        case 'NS':
        case 'TXT':
        case 'SOA':
        case 'SRV':
        case 'CAA':
            $const = $phpTypeMap[$type] ?? null;
            if ($const === null) break;
            $res = @dns_get_record($domain, $const);
            if (empty($res)) break;
            foreach ($res as $r) {
                switch ($type) {
                    case 'A':     $value = $r['ip']     ?? ''; break;
                    case 'AAAA':  $value = $r['ipv6']   ?? ''; break;
                    case 'CNAME': $value = $r['target'] ?? ''; break;
                    case 'MX':    $value = $r['target'] ?? ''; break;
                    case 'NS':    $value = $r['target'] ?? ''; break;
                    case 'TXT':   $value = $r['txt']    ?? ''; break;
                    case 'SOA':
                        $value = sprintf('%s %s — serial %s, refresh %ss',
                            $r['mname']   ?? '',
                            $r['rname']   ?? '',
                            $r['serial']  ?? '',
                            $r['refresh'] ?? ''
                        );
                        break;
                    case 'SRV':
                        $value = sprintf('%s:%d (prio %d, weight %d)',
                            $r['target'] ?? '',
                            $r['port']   ?? 0,
                            $r['pri']    ?? 0,
                            $r['weight'] ?? 0
                        );
                        break;
                    case 'CAA':
                        $value = sprintf('%d %s "%s"',
                            $r['flags'] ?? 0,
                            $r['tag']   ?? '',
                            $r['value'] ?? ''
                        );
                        break;
                    default: $value = '';
                }
                if ($value === '') continue;

                $entry = [
                    'type'  => $type,
                    'value' => $value,
                    'ttl'   => $r['ttl'] ?? null,
                ];
                if ($type === 'MX' && isset($r['pri'])) {
                    $entry['priority'] = (int)$r['pri'];
                }
                // Detectar ARSYS (NS, MX, A, CNAME…)
                if ($isArsys($value, $type)) {
                    $entry['arsys'] = true;
                    if ($type === 'NS') $arsysNs = true;
                }
                $records[] = $entry;
            }
            break;

        // ── SPF: registros TXT que comienzan por v=spf1 ──────────────────
        case 'SPF':
            $res = @dns_get_record($domain, DNS_TXT);
            if (empty($res)) break;
            foreach ($res as $r) {
                $txt = $r['txt'] ?? '';
                if (stripos($txt, 'v=spf1') === 0) {
                    $records[] = [
                        'type'  => 'SPF',
                        'value' => $txt,
                        'ttl'   => $r['ttl'] ?? null,
                    ];
                }
            }
            break;

        // ── DMARC: TXT en _dmarc.dominio ─────────────────────────────────
        case 'DMARC':
            $res = @dns_get_record('_dmarc.' . $domain, DNS_TXT);
            if (empty($res)) break;
            foreach ($res as $r) {
                $txt = $r['txt'] ?? '';
                if (stripos($txt, 'v=DMARC1') !== false) {
                    $records[] = [
                        'type'  => 'DMARC',
                        'value' => $txt,
                        'ttl'   => $r['ttl'] ?? null,
                    ];
                }
            }
            break;

        // ── DKIM: TXT en {selector}._domainkey.dominio ───────────────────
        case 'DKIM':
            $selectors = ['default', 'mail', 'google', 'dkim', 'selector1', 'selector2',
                          'k1', 'smtp', 'mailjet', 'sendgrid', 'mandrill', 'amazonses'];
            foreach ($selectors as $sel) {
                $res = @dns_get_record("{$sel}._domainkey.{$domain}", DNS_TXT);
                if (empty($res)) continue;
                foreach ($res as $r) {
                    $txt = $r['txt'] ?? '';
                    if (stripos($txt, 'v=DKIM1') !== false ||
                        stripos($txt, 'k=rsa')   !== false ||
                        stripos($txt, 'p=')       !== false) {
                        $records[] = [
                            'type'     => 'DKIM',
                            'value'    => $txt,
                            'ttl'      => $r['ttl'] ?? null,
                            'selector' => $sel,
                        ];
                    }
                }
            }
            break;

        // ── MTA-STS: TXT en _mta-sts.dominio ─────────────────────────────
        case 'MTA-STS':
            $res = @dns_get_record('_mta-sts.' . $domain, DNS_TXT);
            if (empty($res)) break;
            foreach ($res as $r) {
                $txt = $r['txt'] ?? '';
                if (stripos($txt, 'v=STSv1') !== false) {
                    $records[] = [
                        'type'  => 'MTA-STS',
                        'value' => $txt,
                        'ttl'   => $r['ttl'] ?? null,
                    ];
                }
            }
            break;

        // ── BIMI: TXT en default._bimi.dominio ───────────────────────────
        case 'BIMI':
            $res = @dns_get_record('default._bimi.' . $domain, DNS_TXT);
            if (empty($res)) break;
            foreach ($res as $r) {
                $txt = $r['txt'] ?? '';
                if (stripos($txt, 'v=BIMI1') !== false) {
                    $records[] = [
                        'type'  => 'BIMI',
                        'value' => $txt,
                        'ttl'   => $r['ttl'] ?? null,
                    ];
                }
            }
            break;
    }
}

echo json_encode(['success' => true, 'records' => $records, 'arsys_ns' => $arsysNs]);
