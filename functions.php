<?php
/**
 * Cuakcom Expert Suite - Funciones auxiliares compartidas
 */

/**
 * Limpia y normaliza un hostname/dominio de entrada.
 * Elimina protocolo, ruta, y caracteres no válidos.
 */
function limpiarHost(string $raw): string {
    $host = trim($raw);
    $host = preg_replace('#^https?://(www\.)?#i', '', $host);
    $host = explode('/', $host)[0];
    $host = explode('?', $host)[0];
    $host = preg_replace('/[^a-zA-Z0-9.\-]/', '', $host);
    return strtolower($host);
}

/**
 * Base para llamadas internas (self HTTP). No basta con esquema+dominio:
 * si la app vive en una subcarpeta (p.ej. https://dominio.com/app/), hay
 * que incluirla o la llamada a /api.php da 404. Se calcula a partir de la
 * carpeta del propio script que la invoque (smart_check.php vive junto a
 * api.php, así que su misma carpeta es la correcta).
 */
function api_base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir    = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir    = rtrim($dir, '/'); // '/' o '/app' -> '' o '/app'
    return $scheme . '://' . $host . $dir;
}

/**
 * Ejecuta uno o varios módulos de /modules a través del propio api.php
 * (petición HTTP interna), en vez de hacer include() directo: varios
 * módulos terminan con exit tras un error, lo que mataría el proceso que
 * los esté combinando. Se lanzan todos en paralelo con curl_multi para que
 * pedir 2-3 módulos no sume sus tiempos de espera uno tras otro.
 *
 * @param array<int, array{0:string,1:array}> $specs Lista de [módulo, extraGet]
 * @return array<string, array> Resultado de cada módulo, indexado por nombre
 */
function run_modules(array $specs, string $domain): array {
    if (!$specs) {
        return [];
    }

    $multi    = curl_multi_init();
    $handles  = [];
    foreach ($specs as [$module, $extraGet]) {
        $params = array_merge(['module' => $module, 'domain' => $domain], $extraGet);
        $url = api_base_url() . '/api.php?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            // WHOIS puede tardar más que un módulo HTTP normal (sigue
            // referencias entre servidores registry/registrar).
            CURLOPT_TIMEOUT        => 45,
            CURLOPT_SSL_VERIFYPEER => true,
            // Algunos paneles (Imunify360, mod_security...) bloquean o
            // devuelven una página de aviso a peticiones sin User-Agent
            // "de navegador" — esto es una llamada interna legítima.
            CURLOPT_USERAGENT      => 'CheckBerry-Internal/1.0 (+' . api_base_url() . ')',
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        curl_multi_add_handle($multi, $ch);
        $handles[$module] = ['ch' => $ch, 'url' => $url];
    }

    $running = null;
    do {
        curl_multi_exec($multi, $running);
        curl_multi_select($multi);
    } while ($running > 0);

    $results = [];
    foreach ($handles as $module => ['ch' => $ch, 'url' => $url]) {
        $body     = curl_multi_getcontent($ch);
        $err      = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_multi_remove_handle($multi, $ch);
        curl_close($ch);

        if ($body === '' || $body === null) {
            $results[$module] = ['success' => false, 'error' => "Error interno llamando a {$module} ({$url}): {$err}"];
            continue;
        }
        $data = json_decode($body, true);
        if (is_array($data)) {
            $results[$module] = $data;
        } else {
            // Verboso a propósito: sin esto no hay forma de saber si api.php
            // devolvió un aviso/error PHP, un 429 del rate-limit, HTML, etc.
            $snippet = mb_substr(trim($body), 0, 300);
            $results[$module] = ['success' => false, 'error' => "Respuesta inválida de {$module} (HTTP {$httpCode}): {$snippet}"];
        }
    }
    curl_multi_close($multi);

    return $results;
}

/** Variante para un único módulo. */
function run_module(string $module, string $domain, array $extraGet = []): array {
    return run_modules([[$module, $extraGet]], $domain)[$module];
}
