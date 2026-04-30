<?php
/**
 * Módulo: Diagnóstico completo de correo electrónico
 * Variables disponibles: $domain (string, sanitizado por api.php)
 * Parámetro opcional: $_GET['email'] – cuenta de correo para RCPT TO test
 */

// ── SMTP test con detección de STARTTLS ───────────────────────────────────────
function smtpTest(string $host, int $port, int $timeout = 4): array
{
    $t0 = microtime(true);
    if ($port === 465) {
        $ctx  = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $sock = @stream_socket_client("ssl://{$host}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $ctx);
    } else {
        $sock = @fsockopen($host, $port, $errno, $errstr, $timeout);
    }
    $ms = (int)round((microtime(true) - $t0) * 1000);
    if (!$sock) {
        return ['open' => false, 'ms' => null, 'banner' => null, 'starttls' => null, 'capabilities' => []];
    }

    stream_set_timeout($sock, 3);
    $banner   = trim((string)@fgets($sock, 512));
    $starttls = false;
    $caps     = [];

    if ($port !== 465) {
        // En puertos no-SSL intentamos EHLO para ver las capacidades
        fputs($sock, "EHLO check.cuakcom.com\r\n");
        $ehlo = '';
        for ($i = 0; $i < 30; $i++) {
            $line = @fgets($sock, 512);
            if ($line === false) break;
            $ehlo .= $line;
            $clean = trim(substr($line, 4));
            if ($clean) $caps[] = $clean;
            // Última línea SMTP: "250 " (código seguido de espacio)
            if (preg_match('/^\d{3} /', $line)) break;
        }
        $starttls = stripos($ehlo, 'STARTTLS') !== false;
    } else {
        $starttls = true; // Puerto SSL: TLS implícito
        $caps[]   = 'SSL/TLS implícito';
    }

    fputs($sock, "QUIT\r\n");
    fclose($sock);

    return [
        'open'         => true,
        'ms'           => $ms,
        'banner'       => $banner ?: null,
        'starttls'     => $starttls,
        'capabilities' => array_filter($caps, fn($c) => !str_starts_with($c, 'EHLO')),
    ];
}

// ── RCPT TO test (verificar existencia de buzón) ──────────────────────────────
function testRcptTo(string $mxHost, string $email, int $timeout = 5): array
{
    $sock = @fsockopen($mxHost, 25, $errno, $errstr, $timeout);
    if (!$sock) return ['result' => 'unknown', 'reason' => 'No se pudo conectar al puerto 25'];

    stream_set_timeout($sock, 4);
    @fgets($sock, 512);                            // 220 banner
    fputs($sock, "EHLO check.cuakcom.com\r\n");
    for ($i = 0; $i < 20; $i++) {
        $l = @fgets($sock, 512);
        if ($l === false || preg_match('/^\d{3} /', $l)) break;
    }
    fputs($sock, "MAIL FROM:<postmaster@cuakcom.com>\r\n");
    @fgets($sock, 512);
    fputs($sock, "RCPT TO:<{$email}>\r\n");
    $response = trim((string)@fgets($sock, 512));
    fputs($sock, "RSET\r\n");
    @fgets($sock, 512);
    fputs($sock, "QUIT\r\n");
    fclose($sock);

    $code = (int)substr($response, 0, 3);
    return [
        'result'   => match(true) {
            $code === 250 => 'exists',
            $code === 550, $code === 551, $code === 553 => 'not_exists',
            default       => 'unknown',
        },
        'code'     => $code,
        'response' => $response,
        'note'     => 'Muchos servidores responden 250 siempre (catch-all) o bloquean VRFY. Resultado orientativo.',
    ];
}

// ── ARSYS helper ──────────────────────────────────────────────────────────────
function isArsysMx(string $host, string $ip = ''): bool
{
    if (preg_match('/\.(servidoresdns\.net|serviciodecorreo\.es)\.?$/i', $host)) return true;
    foreach (['217.76.', '82.223.', '82.233.'] as $r) {
        if ($ip && strpos($ip, $r) === 0) return true;
    }
    return false;
}

// ── DNSBL check ───────────────────────────────────────────────────────────────
function dnsblCheck(string $ip): array
{
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return [];
    $rev  = implode('.', array_reverse(explode('.', $ip)));
    $lists = [
        'zen.spamhaus.org'       => 'Spamhaus ZEN',
        'sbl.spamhaus.org'       => 'Spamhaus SBL',
        'cbl.abuseat.org'        => 'Spamhaus CBL',
        'pbl.spamhaus.org'       => 'Spamhaus PBL',
        'bl.spamcop.net'         => 'SpamCop',
        'b.barracudacentral.org' => 'Barracuda',
        'dnsbl.sorbs.net'        => 'SORBS',
        'spam.dnsbl.sorbs.net'   => 'SORBS Spam',
        'dnsbl-1.uceprotect.net' => 'UCEPROTECT L1',
        'dnsbl.spfbl.net'        => 'SPFBL',
        'truncate.gbudb.net'     => 'GBUdb',
        'psbl.surriel.com'       => 'PSBL',
    ];
    $results = [];
    $listed  = 0;
    foreach ($lists as $bl => $name) {
        $hit = !empty(@dns_get_record("{$rev}.{$bl}", DNS_A));
        if ($hit) $listed++;
        $results[] = ['name' => $name, 'listed' => $hit];
    }
    return ['listed' => $listed, 'total' => count($lists), 'results' => $results];
}

// ═════════════════════════════════════════════════════════════════════════════
// 1. Validar email opcional
// ═════════════════════════════════════════════════════════════════════════════
$emailToTest = '';
if (!empty($_GET['email'])) {
    $emailRaw = trim($_GET['email']);
    if (filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
        $emailToTest = $emailRaw;
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// 2. MX Records
// ═════════════════════════════════════════════════════════════════════════════
$mxRaw = @dns_get_record($domain, DNS_MX) ?: [];
if (empty($mxRaw)) {
    echo json_encode(['success' => false, 'error' => "No se encontraron registros MX para {$domain}"]);
    exit;
}
usort($mxRaw, fn($a, $b) => ($a['pri'] ?? 99) - ($b['pri'] ?? 99));

$mxRecords = [];
$firstMxIp = null;
$firstMxHost = '';
foreach ($mxRaw as $mx) {
    $host = rtrim($mx['target'] ?? '', '.');
    if (!$host) continue;
    $ip  = gethostbyname($host);
    $ip  = ($ip !== $host) ? $ip : null;
    if (!$firstMxIp && $ip) { $firstMxIp = $ip; $firstMxHost = $host; }
    $ptr = $ip ? gethostbyaddr($ip) : null;
    $mxRecords[] = [
        'host'     => $host,
        'priority' => (int)($mx['pri'] ?? 0),
        'ip'       => $ip,
        'ptr'      => ($ptr && $ptr !== $ip) ? $ptr : null,
        'ptr_ok'   => $ptr && $ptr !== $ip && stripos($ptr, explode('.', $host)[0]) !== false,
        'arsys'    => isArsysMx($host, $ip ?? ''),
    ];
}

// ═════════════════════════════════════════════════════════════════════════════
// 3. SMTP Connectivity con STARTTLS
// ═════════════════════════════════════════════════════════════════════════════
$smtpHost  = $mxRecords[0]['host'] ?? $domain;
$smtpPorts = [
    25  => 'SMTP (relay)',
    587 => 'Submission (SMTP-S)',
    465 => 'SMTPS (SSL implícito)',
];
$smtpTests = [];
foreach ($smtpPorts as $port => $label) {
    $r = smtpTest($smtpHost, $port, 4);
    $smtpTests[] = array_merge($r, ['port' => $port, 'label' => $label]);
}

// ═════════════════════════════════════════════════════════════════════════════
// 4. SPF
// ═════════════════════════════════════════════════════════════════════════════
$spf     = null;
$spfGood = false;
foreach (@dns_get_record($domain, DNS_TXT) ?: [] as $t) {
    $txt = $t['txt'] ?? '';
    if (stripos($txt, 'v=spf1') === 0) {
        $spf = $txt;
        $spfGood = (bool)preg_match('/[-~]all/i', $txt);
        break;
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// 5. DMARC
// ═════════════════════════════════════════════════════════════════════════════
$dmarc       = null;
$dmarcPolicy = null;
$dmarcRua    = null;
foreach (@dns_get_record('_dmarc.' . $domain, DNS_TXT) ?: [] as $t) {
    $txt = $t['txt'] ?? '';
    if (stripos($txt, 'v=DMARC1') !== false) {
        $dmarc = $txt;
        if (preg_match('/p=(none|quarantine|reject)/i', $txt, $pm)) $dmarcPolicy = strtolower($pm[1]);
        if (preg_match('/rua=([^;]+)/i',                $txt, $rm)) $dmarcRua    = trim($rm[1]);
        break;
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// 6. DKIM
// ═════════════════════════════════════════════════════════════════════════════
$dkimFound = [];
foreach (['default','mail','google','dkim','selector1','selector2','k1','smtp','mailjet','sendgrid','mandrill','amazonses'] as $sel) {
    foreach (@dns_get_record("{$sel}._domainkey.{$domain}", DNS_TXT) ?: [] as $t) {
        $txt = $t['txt'] ?? '';
        if (stripos($txt, 'v=DKIM1') !== false || stripos($txt, 'p=') !== false) {
            $dkimFound[] = ['selector' => $sel, 'value' => $txt];
            break;
        }
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// 7. MTA-STS
// ═════════════════════════════════════════════════════════════════════════════
$mtaSts = null;
foreach (@dns_get_record('_mta-sts.' . $domain, DNS_TXT) ?: [] as $t) {
    if (stripos($t['txt'] ?? '', 'v=STSv1') !== false) { $mtaSts = $t['txt']; break; }
}

// ═════════════════════════════════════════════════════════════════════════════
// 8. BIMI
// ═════════════════════════════════════════════════════════════════════════════
$bimi = null;
foreach (@dns_get_record('default._bimi.' . $domain, DNS_TXT) ?: [] as $t) {
    if (stripos($t['txt'] ?? '', 'v=BIMI1') !== false) { $bimi = $t['txt']; break; }
}

// ═════════════════════════════════════════════════════════════════════════════
// 9. Blacklist del primer MX
// ═════════════════════════════════════════════════════════════════════════════
$blacklist = $firstMxIp ? dnsblCheck($firstMxIp) : null;

// ═════════════════════════════════════════════════════════════════════════════
// 10. RCPT TO test (opcional)
// ═════════════════════════════════════════════════════════════════════════════
$rcptTo = null;
if ($emailToTest && $firstMxHost) {
    $rcptTo = testRcptTo($firstMxHost, $emailToTest, 5);
}

// ═════════════════════════════════════════════════════════════════════════════
// 11. Puntuación de entregabilidad /14
// ═════════════════════════════════════════════════════════════════════════════
$scoreItems = [
    ['label' => 'MX configurado',                      'ok' => !empty($mxRecords),                              'weight' => 1],
    ['label' => 'PTR inverso del MX válido',            'ok' => (bool)($mxRecords[0]['ptr_ok'] ?? false),        'weight' => 1],
    ['label' => 'Puerto 25 accesible',                  'ok' => (bool)array_filter($smtpTests, fn($s) => $s['open'] && $s['port'] === 25), 'weight' => 1],
    ['label' => 'STARTTLS disponible (25 o 587)',       'ok' => (bool)array_filter($smtpTests, fn($s) => $s['open'] && $s['starttls']), 'weight' => 1],
    ['label' => 'Puerto 587 accesible (submission)',    'ok' => (bool)array_filter($smtpTests, fn($s) => $s['open'] && $s['port'] === 587), 'weight' => 1],
    ['label' => 'SPF presente',                         'ok' => $spf !== null,                                   'weight' => 1],
    ['label' => 'SPF con política estricta (-all/~all)','ok' => $spfGood,                                        'weight' => 1],
    ['label' => 'DMARC presente',                       'ok' => $dmarc !== null,                                 'weight' => 1],
    ['label' => 'DMARC con política enforce',           'ok' => in_array($dmarcPolicy, ['quarantine','reject']), 'weight' => 1],
    ['label' => 'DMARC con reporting (rua)',             'ok' => $dmarcRua !== null,                              'weight' => 1],
    ['label' => 'DKIM encontrado',                      'ok' => !empty($dkimFound),                              'weight' => 2],
    ['label' => 'MTA-STS configurado',                  'ok' => $mtaSts !== null,                                'weight' => 1],
    ['label' => 'No en blacklists DNSBL',               'ok' => ($blacklist['listed'] ?? 1) === 0,               'weight' => 1],
];

$score    = array_sum(array_map(fn($i) => $i['ok'] ? $i['weight'] : 0, $scoreItems));
$scoreMax = array_sum(array_column($scoreItems, 'weight'));

$arsysMx = !empty(array_filter($mxRecords, fn($m) => $m['arsys']));

echo json_encode([
    'success'      => true,
    'domain'       => $domain,
    'email_tested' => $emailToTest ?: null,
    'mx'           => $mxRecords,
    'smtp'         => $smtpTests,
    'spf'          => ['record' => $spf,   'exists' => $spf !== null,   'strict' => $spfGood],
    'dmarc'        => ['record' => $dmarc, 'exists' => $dmarc !== null, 'policy' => $dmarcPolicy, 'rua' => $dmarcRua],
    'dkim'         => $dkimFound,
    'mta_sts'      => $mtaSts,
    'bimi'         => $bimi,
    'blacklist'    => $blacklist,
    'rcpt_to'      => $rcptTo,
    'arsys'        => $arsysMx,
    'score'        => $score,
    'score_max'    => $scoreMax,
    'score_items'  => $scoreItems,
]);
