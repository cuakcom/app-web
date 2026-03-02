<?php
// functions.php

/**
 * Sanitize domain input (removes any character that is not alphanumeric, dot or hyphen)
 */
function limpiarHost($host) {
    return preg_replace('/[^a-zA-Z0-9.-]/', '', trim((string)$host));
}

/**
 * Validate that a string looks like a valid domain name
 */
function validarDominio($dominio) {
    return strlen($dominio) >= 3
        && strlen($dominio) <= 253
        && preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z]{2,})+$/', $dominio);
}

/**
 * Execute a shell command and return formatted HTML output
 */
function ejecutarComando($titulo, $comando) {
    if (!function_exists('shell_exec')) {
        return "<h3>$titulo</h3><p style='color:red;'>Error: shell_exec deshabilitado.</p>";
    }
    $salida = shell_exec($comando . " 2>&1");
    $html  = "<h3>$titulo</h3>";
    $html .= "<pre class='terminal'>";
    $html .= ($salida) ? htmlspecialchars(trim($salida)) : "Sin respuesta del comando.";
    $html .= "</pre>";
    return $html;
}

/**
 * Query a single WHOIS server and return the raw response
 */
function _whoisQuery($servidor, $query) {
    $fp = @fsockopen($servidor, 43, $errno, $errstr, 10);
    if (!$fp) return null;
    stream_set_timeout($fp, 10);
    fputs($fp, $query . "\r\n");
    $out = "";
    while (!feof($fp)) {
        $chunk = fgets($fp, 4096);
        if ($chunk === false) break;
        $out .= $chunk;
    }
    fclose($fp);
    return $out ?: null;
}

/**
 * WHOIS lookup — queries IANA first, then follows the referral to the
 * authoritative registrar server to return full domain information.
 */
function obtenerWhois($dominio) {
    $respuesta = _whoisQuery("whois.iana.org", $dominio);
    if (!$respuesta) return "Error: no se pudo conectar al servidor WHOIS.";

    // Follow the referral line to get the full registrar WHOIS data
    if (preg_match('/refer:\s*(\S+)/i', $respuesta, $m)) {
        $referido = _whoisQuery(trim($m[1]), $dominio);
        if ($referido) return $referido;
    }

    return $respuesta;
}

/**
 * Return the Bootstrap badge colour class for a given DNS record type
 */
function obtenerColorDns($tipo) {
    $colores = [
        'A'     => 'bg-primary',
        'MX'    => 'bg-warning text-dark',
        'NS'    => 'bg-danger',
        'TXT'   => 'bg-success',
        'AAAA'  => 'bg-info text-dark',
        'CNAME' => 'bg-secondary',
        'IP'    => 'bg-primary',
        'HOST'  => 'bg-dark',
    ];
    return $colores[$tipo] ?? 'bg-dark';
}

/**
 * Check whether a TCP port is open on the given host.
 * Uses a 1-second timeout to reduce false negatives.
 */
function esPuertoAbierto($host, $puerto) {
    $conn = @fsockopen($host, $puerto, $errno, $errstr, 1);
    if (is_resource($conn)) {
        fclose($conn);
        return true;
    }
    return false;
}
