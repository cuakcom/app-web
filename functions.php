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

function api_base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
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
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        curl_multi_add_handle($multi, $ch);
        $handles[$module] = $ch;
    }

    $running = null;
    do {
        curl_multi_exec($multi, $running);
        curl_multi_select($multi);
    } while ($running > 0);

    $results = [];
    foreach ($handles as $module => $ch) {
        $body = curl_multi_getcontent($ch);
        $err  = curl_error($ch);
        curl_multi_remove_handle($multi, $ch);
        curl_close($ch);

        if ($body === '' || $body === null) {
            $results[$module] = ['success' => false, 'error' => "Error interno llamando a {$module}: {$err}"];
            continue;
        }
        $data = json_decode($body, true);
        $results[$module] = is_array($data) ? $data : ['success' => false, 'error' => "Respuesta inválida de {$module}"];
    }
    curl_multi_close($multi);

    return $results;
}

/** Variante para un único módulo. */
function run_module(string $module, string $domain, array $extraGet = []): array {
    return run_modules([[$module, $extraGet]], $domain)[$module];
}
