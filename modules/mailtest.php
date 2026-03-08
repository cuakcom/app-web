<?php
/**
 * Módulo: Diagnóstico completo de correo electrónico
 * Variables disponibles: $domain (string, sanitizado por api.php)
 *
 * Comprueba:
 *  - Registros MX (con ARSYS detection)
 *  - Conectividad SMTP (25, 465, 587)
 *  - SPF, DMARC, DKIM
 *  - PTR inverso de los MX
 *  - Blacklist DNSBL del primer MX
 *  - Puntuación de entregabilidad /10
 */

// ── SMTP test ─────────────────────────────────────────────────────────────────
function smtpTest(string $host, int $port, int $timeout = 3): array
{
    $t0   = microtime(true);
    $sock = @fsockopen($host, $port, $errno, $errstr, $timeout);
    $ms   = (int)round((microtime(true) - $t0) * 1000);
    if (!$sock) {
        return ['open' => false, 'ms' => null, 'banner' => null];
    }
    $banner = trim(@fgets($sock, 512) ?: '');
    fclose($sock);
    return ['open' => true, 'ms' => $ms, 'banner' => $banner ?: null];
}

// ── ARSYS helper ──────────────────────────────────────────────────────────────
function isArsysMx(string $host, string $ip = ''): bool
{
    if (preg_match('/\.(servidoresdns\.net|serviciodecorreo\.es)\.?$/i', $host)) return true;
    if ($ip) {
        foreach (['217.76.', '82.223.', '82.233.'] as $r) {
            if (strpos($ip, $r) === 0) return true;
        }
    }
    return false;
}

// ── DNSBL check ───────────────────────────────────────────────────────────────
function dnsblCheck(string $ip): array
{
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return [];
    $rev   = implode('.', array_reverse(explode('.', $ip)));
    $lists = [
        'zen.spamhaus.org'       => 'Spamhaus ZEN',
        'bl.spamcop.net'         => 'SpamCop',
        'b.barracudacentral.org' => 'Barracuda',
        'dnsbl.sorbs.net'        => 'SORBS',
        'cbl.abuseat.org'        => 'CBL',
        'pbl.spamhaus.org'       => 'Spamhaus PBL',
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
// 1. MX Records
// ═════════════════════════════════════════════════════════════════════════════
$mxRaw = @dns_get_record($domain, DNS_MX);
if (empty($mxRaw)) {
    echo json_encode(['success' => false, 'error' => "No se encontraron registros MX para {$domain}. El dominio puede no tener correo configurado."]);
    exit;
}

usort($mxRaw, fn($a, $b) => ($a['pri'] ?? 99) - ($b['pri'] ?? 99));

$mxRecords = [];
$firstMxIp = null;
foreach ($mxRaw as $mx) {
    $host = rtrim($mx['target'] ?? '', '.');
    if (!$host) continue;
    $ip  = gethostbyname($host);
    $ip  = ($ip !== $host) ? $ip : null;
    if (!$firstMxIp && $ip) $firstMxIp = $ip;
    $ptr = $ip ? gethostbyaddr($ip) : null;
    $mxRecords[] = [
        'host'     => $host,
        'priority' => (int)($mx['pri'] ?? 0),
        'ip'       => $ip,
        'ptr'      => ($ptr && $ptr !== $ip) ? $ptr : null,
        'ptr_ok'   => $ptr && stripos($ptr, explode('.', $host)[0]) !== false,
        'arsys'    => isArsysMx($host, $ip ?? ''),
    ];
}

// ═════════════════════════════════════════════════════════════════════════════
// 2. SMTP Connectivity (primer MX)
// ═════════════════════════════════════════════════════════════════════════════
$smtpHost  = $mxRecords[0]['host'] ?? $domain;
$smtpPorts = [
    25  => 'SMTP',
    587 => 'SMTP-S (submission)',
    465 => 'SMTPS (SSL)',
];
$smtpTests = [];
foreach ($smtpPorts as $port => $label) {
    $r = smtpTest($smtpHost, $port, 4);
    $smtpTests[] = array_merge($r, ['port' => $port, 'label' => $label]);
}

// ═════════════════════════════════════════════════════════════════════════════
// 3. SPF
// ═════════════════════════════════════════════════════════════════════════════
$spf     = null;
$spfGood = false;
$txtRecs = @dns_get_record($domain, DNS_TXT) ?: [];
foreach ($txtRecs as $t) {
    $txt = $t['txt'] ?? '';
    if (stripos($txt, 'v=spf1') === 0) {
        $spf = $txt;
        $spfGood = preg_match('/[-~]all/i', $txt) === 1;
        break;
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// 4. DMARC
// ═════════════════════════════════════════════════════════════════════════════
$dmarc       = null;
$dmarcPolicy = null;
$dmarcRecs   = @dns_get_record('_dmarc.' . $domain, DNS_TXT) ?: [];
foreach ($dmarcRecs as $t) {
    $txt = $t['txt'] ?? '';
    if (stripos($txt, 'v=DMARC1') !== false) {
        $dmarc = $txt;
        if (preg_match('/p=(none|quarantine|reject)/i', $txt, $pm)) {
            $dmarcPolicy = strtolower($pm[1]);
        }
        break;
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// 5. DKIM (selectores comunes)
// ═════════════════════════════════════════════════════════════════════════════
$dkimFound = [];
$selectors = ['default', 'mail', 'google', 'dkim', 'selector1', 'selector2',
              'k1', 'smtp', 'mailjet', 'sendgrid', 'mandrill'];
foreach ($selectors as $sel) {
    $r = @dns_get_record("{$sel}._domainkey.{$domain}", DNS_TXT);
    foreach ($r ?: [] as $t) {
        $txt = $t['txt'] ?? '';
        if (stripos($txt, 'v=DKIM1') !== false || stripos($txt, 'p=') !== false) {
            $dkimFound[] = ['selector' => $sel, 'value' => $txt];
            break;
        }
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// 6. Blacklist del primer MX
// ═════════════════════════════════════════════════════════════════════════════
$blacklist = $firstMxIp ? dnsblCheck($firstMxIp) : null;

// ═════════════════════════════════════════════════════════════════════════════
// 7. Puntuación de entregabilidad /10
// ═════════════════════════════════════════════════════════════════════════════
$score = 0;
if (!empty($mxRecords))                        $score += 1; // MX existe
if ($mxRecords[0]['ptr_ok'] ?? false)           $score += 1; // PTR válido
if ($spf !== null)                              $score += 1; // SPF existe
if ($spfGood)                                  $score += 1; // SPF tiene -all/~all
if ($dmarc !== null)                            $score += 1; // DMARC existe
if (in_array($dmarcPolicy, ['quarantine','reject'])) $score += 1; // DMARC policy fuerte
if (!empty($dkimFound))                         $score += 2; // DKIM encontrado
if ($blacklist && $blacklist['listed'] === 0)   $score += 1; // No en blacklists
$smtpOk = array_filter($smtpTests, fn($s) => $s['open']);
if (!empty($smtpOk))                            $score += 1; // SMTP accesible

// ARSYS en MX
$arsysMx = !empty(array_filter($mxRecords, fn($m) => $m['arsys']));

echo json_encode([
    'success'    => true,
    'domain'     => $domain,
    'mx'         => $mxRecords,
    'smtp'       => $smtpTests,
    'spf'        => ['record' => $spf, 'exists' => $spf !== null, 'strict' => $spfGood],
    'dmarc'      => ['record' => $dmarc, 'exists' => $dmarc !== null, 'policy' => $dmarcPolicy],
    'dkim'       => $dkimFound,
    'blacklist'  => $blacklist,
    'arsys'      => $arsysMx,
    'score'      => $score,
    'score_max'  => 10,
]);
