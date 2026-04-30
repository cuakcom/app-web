<?php
/**
 * Módulo: Verificación en listas negras DNSBL
 * Variables disponibles: $domain (string, sanitizado por api.php)
 */

// Resolver IP
$ip = gethostbyname($domain);
if ($ip === $domain) {
    // Puede ser ya una IP directa
    if (!filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        echo json_encode(['success' => false, 'error' => 'No se pudo resolver la IP del dominio o no es IPv4']);
        exit;
    }
    $ip = $domain;
}

if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    echo json_encode(['success' => false, 'error' => 'Solo se admiten IPs IPv4 para la comprobación de blacklists']);
    exit;
}

// Invertir octetos para queries DNSBL: 1.2.3.4 → 4.3.2.1
$reversed = implode('.', array_reverse(explode('.', $ip)));

// Principales listas negras DNSBL
$dnsbls = [
    'zen.spamhaus.org'       => 'Spamhaus ZEN',
    'sbl.spamhaus.org'       => 'Spamhaus SBL',
    'xbl.spamhaus.org'       => 'Spamhaus XBL',
    'pbl.spamhaus.org'       => 'Spamhaus PBL',
    'cbl.abuseat.org'        => 'Spamhaus CBL',
    'bl.spamcop.net'         => 'SpamCop',
    'dnsbl.sorbs.net'        => 'SORBS',
    'spam.dnsbl.sorbs.net'   => 'SORBS Spam',
    'b.barracudacentral.org' => 'Barracuda',
    'dnsbl-1.uceprotect.net' => 'UCEPROTECT L1',
    'dnsbl.spfbl.net'        => 'SPFBL',
    'truncate.gbudb.net'     => 'GBUdb',
];

$results = [];
$listed  = 0;

foreach ($dnsbls as $dnsbl => $name) {
    $query    = "{$reversed}.{$dnsbl}";
    $lookup   = @dns_get_record($query, DNS_A);
    $isListed = !empty($lookup);
    if ($isListed) $listed++;
    $results[] = [
        'name'   => $name,
        'listed' => $isListed,
        'rcode'  => $isListed ? ($lookup[0]['ip'] ?? null) : null,
    ];
}

echo json_encode([
    'success' => true,
    'ip'      => $ip,
    'listed'  => $listed,
    'total'   => count($dnsbls),
    'clean'   => $listed === 0,
    'results' => $results,
]);
