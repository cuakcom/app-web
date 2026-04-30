<?php
/**
 * Módulo: SPF Check
 * Evalúa si una IP está autorizada por el registro SPF de un dominio.
 * Variables disponibles: $domain (sanitizado por api.php)
 * Parámetros extra: $_GET['ip'] — IP a verificar
 *
 * Implementa RFC 7208 (mecanismos: all, ip4, ip6, a, mx, include, redirect, exists, ptr)
 */

$testIp = trim($_GET['ip'] ?? '');

// Validar IP (v4 o v6)
if (empty($testIp) || !filter_var($testIp, FILTER_VALIDATE_IP)) {
    echo json_encode(['success' => false, 'error' => 'IP inválida. Introduce una IPv4 o IPv6 válida.']);
    exit;
}

$isIpv6 = filter_var($testIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;

// ── Helpers ────────────────────────────────────────────────────────────────

/** Obtiene el registro SPF TXT de un dominio */
function spf_get_record(string $domain): ?string {
    $records = @dns_get_record($domain, DNS_TXT);
    if (!is_array($records)) return null;
    foreach ($records as $r) {
        $txt = $r['txt'] ?? $r['entries'][0] ?? '';
        if (stripos(trim($txt), 'v=spf1') === 0) return trim($txt);
    }
    return null;
}

/** Comprueba si una IP IPv4 está dentro de un rango CIDR */
function spf_ip4_match(string $ip, string $network, int $prefix): bool {
    if (!filter_var($ip,      FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return false;
    if (!filter_var($network, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return false;
    $ipLong   = ip2long($ip);
    $netLong  = ip2long($network);
    $mask     = $prefix === 0 ? 0 : (~0 << (32 - $prefix));
    return ($ipLong & $mask) === ($netLong & $mask);
}

/** Comprueba si una IP IPv6 está dentro de un rango CIDR */
function spf_ip6_match(string $ip, string $network, int $prefix): bool {
    if (!filter_var($ip,      FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) return false;
    if (!filter_var($network, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) return false;
    $ipBin  = inet_pton($ip);
    $netBin = inet_pton($network);
    if ($ipBin === false || $netBin === false) return false;
    $fullBytes  = intdiv($prefix, 8);
    $remainBits = $prefix % 8;
    if (substr($ipBin, 0, $fullBytes) !== substr($netBin, 0, $fullBytes)) return false;
    if ($remainBits === 0) return true;
    $mask = (0xFF << (8 - $remainBits)) & 0xFF;
    return (ord($ipBin[$fullBytes]) & $mask) === (ord($netBin[$fullBytes]) & $mask);
}

/** Resuelve A/AAAA de un dominio y comprueba si la IP testada está en la lista (con CIDR opcional) */
function spf_check_a(string $domain, string $testIp, bool $isIpv6, int $cidr4 = 32, int $cidr6 = 128): bool {
    $type    = $isIpv6 ? DNS_AAAA : DNS_A;
    $records = @dns_get_record($domain, $type);
    if (!is_array($records)) return false;
    foreach ($records as $r) {
        $addr = $r['ip'] ?? $r['ipv6'] ?? '';
        if (!$addr) continue;
        if ($isIpv6 ? spf_ip6_match($testIp, $addr, $cidr6) : spf_ip4_match($testIp, $addr, $cidr4))
            return true;
    }
    return false;
}

/** Resuelve MX de un dominio y comprueba si la IP testada coincide */
function spf_check_mx(string $domain, string $testIp, bool $isIpv6, int $cidr4 = 32, int $cidr6 = 128): bool {
    $mx = @dns_get_record($domain, DNS_MX);
    if (!is_array($mx)) return false;
    foreach ($mx as $m) {
        $host = $m['target'] ?? '';
        if (!$host) continue;
        if (spf_check_a($host, $testIp, $isIpv6, $cidr4, $cidr6)) return true;
    }
    return false;
}

/** Nombre legible del resultado */
function spf_result_label(string $q): string {
    return match ($q) {
        '+'  => 'pass',
        '-'  => 'fail',
        '~'  => 'softfail',
        '?'  => 'neutral',
        default => 'neutral',
    };
}

/**
 * Evaluador SPF principal.
 * Devuelve ['result' => 'pass'|'fail'|'softfail'|'neutral'|'none'|'permerror'|'temperror',
 *           'matched' => string, 'lookups' => int, 'trace' => array]
 */
function spf_evaluate(string $domain, string $testIp, bool $isIpv6, int &$lookups, array &$trace, int $depth = 0): array {
    if ($depth > 5 || $lookups > 10) {
        return ['result' => 'permerror', 'matched' => 'too many lookups / recursive includes', 'lookups' => $lookups, 'trace' => $trace];
    }

    $spfRaw = spf_get_record($domain);
    $lookups++;

    if ($spfRaw === null) {
        $trace[] = ['domain' => $domain, 'spf' => null, 'note' => 'Sin registro SPF'];
        return ['result' => 'none', 'matched' => 'No SPF record', 'lookups' => $lookups, 'trace' => $trace];
    }

    $trace[] = ['domain' => $domain, 'spf' => $spfRaw];

    // Parsear términos (sin "v=spf1")
    $terms = preg_split('/\s+/', trim($spfRaw));
    array_shift($terms); // quitar v=spf1

    $redirect = null;

    foreach ($terms as $term) {
        if ($term === '') continue;

        // Qualifier
        $q = '+';
        if (in_array($term[0], ['+','-','~','?'], true)) {
            $q    = $term[0];
            $term = substr($term, 1);
        }

        // redirect modifier
        if (preg_match('/^redirect=(.+)$/i', $term, $m)) {
            $redirect = $m[1];
            continue;
        }
        // exp modifier — ignorar
        if (stripos($term, 'exp=') === 0) continue;

        // Mecanismo "all"
        if (strtolower($term) === 'all') {
            $result = spf_result_label($q);
            $trace[] = ['mechanism' => 'all', 'qualifier' => $q, 'matched' => true, 'result' => $result];
            return ['result' => $result, 'matched' => 'all', 'lookups' => $lookups, 'trace' => $trace];
        }

        // ip4
        if (preg_match('/^ip4:(.+)$/i', $term, $m)) {
            [$net, $prefix] = array_pad(explode('/', $m[1], 2), 2, '32');
            $prefix = (int)$prefix;
            $match  = spf_ip4_match($testIp, $net, $prefix);
            $trace[] = ['mechanism' => "ip4:{$m[1]}", 'qualifier' => $q, 'matched' => $match];
            if ($match) {
                $result = spf_result_label($q);
                return ['result' => $result, 'matched' => "ip4:{$m[1]}", 'lookups' => $lookups, 'trace' => $trace];
            }
            continue;
        }

        // ip6
        if (preg_match('/^ip6:(.+)$/i', $term, $m)) {
            // Separar prefijo: "2001:db8::/32" → net=2001:db8:: prefix=32
            $slashPos = strrpos($m[1], '/');
            if ($slashPos !== false && is_numeric(substr($m[1], $slashPos + 1))) {
                $net    = substr($m[1], 0, $slashPos);
                $prefix = (int)substr($m[1], $slashPos + 1);
            } else {
                $net    = $m[1];
                $prefix = 128;
            }
            $match = spf_ip6_match($testIp, $net, $prefix);
            $trace[] = ['mechanism' => "ip6:{$m[1]}", 'qualifier' => $q, 'matched' => $match];
            if ($match) {
                $result = spf_result_label($q);
                return ['result' => $result, 'matched' => "ip6:{$m[1]}", 'lookups' => $lookups, 'trace' => $trace];
            }
            continue;
        }

        // a[:domain][/cidr4][//cidr6]
        if (preg_match('/^a(?::([^\/ ]+))?(?:\/(\d+))?(?:\/\/(\d+))?$/i', $term, $m)) {
            $lookups++;
            $aDomain = $m[1] ?: $domain;
            $cidr4   = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : 32;
            $cidr6   = isset($m[3]) && $m[3] !== '' ? (int)$m[3] : 128;
            $match   = spf_check_a($aDomain, $testIp, $isIpv6, $cidr4, $cidr6);
            $trace[] = ['mechanism' => "a:{$aDomain}", 'qualifier' => $q, 'matched' => $match];
            if ($match) {
                $result = spf_result_label($q);
                return ['result' => $result, 'matched' => "a:{$aDomain}", 'lookups' => $lookups, 'trace' => $trace];
            }
            continue;
        }

        // mx[:domain][/cidr4][//cidr6]
        if (preg_match('/^mx(?::([^\/ ]+))?(?:\/(\d+))?(?:\/\/(\d+))?$/i', $term, $m)) {
            $lookups++;
            $mxDomain = $m[1] ?: $domain;
            $cidr4    = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : 32;
            $cidr6    = isset($m[3]) && $m[3] !== '' ? (int)$m[3] : 128;
            $match    = spf_check_mx($mxDomain, $testIp, $isIpv6, $cidr4, $cidr6);
            $trace[] = ['mechanism' => "mx:{$mxDomain}", 'qualifier' => $q, 'matched' => $match];
            if ($match) {
                $result = spf_result_label($q);
                return ['result' => $result, 'matched' => "mx:{$mxDomain}", 'lookups' => $lookups, 'trace' => $trace];
            }
            continue;
        }

        // include:domain
        if (preg_match('/^include:(.+)$/i', $term, $m)) {
            $lookups++;
            $incDomain = $m[1];
            $sub = spf_evaluate($incDomain, $testIp, $isIpv6, $lookups, $trace, $depth + 1);
            $match = ($sub['result'] === 'pass');
            $trace[] = ['mechanism' => "include:{$incDomain}", 'qualifier' => $q, 'sub_result' => $sub['result'], 'matched' => $match];
            if ($match) {
                $result = spf_result_label($q);
                return ['result' => $result, 'matched' => "include:{$incDomain} → {$sub['matched']}", 'lookups' => $lookups, 'trace' => $trace];
            }
            if ($sub['result'] === 'none' || $sub['result'] === 'permerror') {
                return ['result' => 'permerror', 'matched' => "include:{$incDomain} error", 'lookups' => $lookups, 'trace' => $trace];
            }
            continue;
        }

        // exists:domain (simplified — only checks if domain resolves to any A)
        if (preg_match('/^exists:(.+)$/i', $term, $m)) {
            $lookups++;
            $exDomain = $m[1];
            $recs     = @dns_get_record($exDomain, DNS_A);
            $match    = !empty($recs);
            $trace[] = ['mechanism' => "exists:{$exDomain}", 'qualifier' => $q, 'matched' => $match];
            if ($match) {
                $result = spf_result_label($q);
                return ['result' => $result, 'matched' => "exists:{$exDomain}", 'lookups' => $lookups, 'trace' => $trace];
            }
            continue;
        }

        // ptr[:domain] — deprecated, limited support
        if (preg_match('/^ptr(?::(.+))?$/i', $term, $m)) {
            $lookups++;
            $ptrDomain = $m[1] ?? $domain;
            // Reverse lookup of testIp
            $ptr = @gethostbyaddr($testIp);
            $match = ($ptr && $ptr !== $testIp && (
                $ptr === $ptrDomain || str_ends_with($ptr, '.' . $ptrDomain)
            ));
            $trace[] = ['mechanism' => "ptr:{$ptrDomain}", 'qualifier' => $q, 'matched' => $match, 'ptr' => ($ptr ?: null)];
            if ($match) {
                $result = spf_result_label($q);
                return ['result' => $result, 'matched' => "ptr:{$ptrDomain}", 'lookups' => $lookups, 'trace' => $trace];
            }
            continue;
        }

        // Término desconocido/macro — ignorar con nota
        $trace[] = ['mechanism' => $term, 'qualifier' => $q, 'matched' => false, 'note' => 'mecanismo no soportado'];
    }

    // Si había redirect= y no hubo match
    if ($redirect !== null) {
        $lookups++;
        $trace[] = ['mechanism' => "redirect={$redirect}", 'note' => 'siguiendo redirect'];
        return spf_evaluate($redirect, $testIp, $isIpv6, $lookups, $trace, $depth + 1);
    }

    // Sin match explícito → neutral (implicit ?all)
    $trace[] = ['mechanism' => 'implicit ?all', 'matched' => true, 'result' => 'neutral'];
    return ['result' => 'neutral', 'matched' => 'implicit ?all (sin mecanismo coincidente)', 'lookups' => $lookups, 'trace' => $trace];
}

// ── Ejecutar ───────────────────────────────────────────────────────────────

$lookups = 0;
$trace   = [];
$eval    = spf_evaluate($domain, $testIp, $isIpv6, $lookups, $trace);

// Obtener el SPF raw del dominio principal (ya en trace)
$spfRaw = null;
foreach ($trace as $t) {
    if (($t['domain'] ?? '') === $domain && isset($t['spf'])) {
        $spfRaw = $t['spf'];
        break;
    }
}

echo json_encode([
    'success'  => true,
    'domain'   => $domain,
    'ip'       => $testIp,
    'ip_type'  => $isIpv6 ? 'IPv6' : 'IPv4',
    'result'   => $eval['result'],
    'matched'  => $eval['matched'],
    'spf_raw'  => $spfRaw,
    'lookups'  => $lookups,
    'trace'    => $trace,
]);
