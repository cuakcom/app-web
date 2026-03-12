<?php
/**
 * Módulo: Consulta AbuseIPDB v2
 * Variables disponibles: $domain (string, sanitizado por api.php)
 *
 * Configuración: define la variable de entorno ABUSEIPDB_KEY con tu API key.
 * Puedes obtener una clave gratuita en https://www.abuseipdb.com/register
 */

// Cargar config.php si existe (define ABUSEIPDB_KEY)
$configFile = __DIR__ . '/../config.php';
if (file_exists($configFile) && !defined('ABUSEIPDB_KEY')) {
    require_once $configFile;
}

// Obtener API key: config.php → variable de entorno
$apiKey = (defined('ABUSEIPDB_KEY') && ABUSEIPDB_KEY !== '') ? ABUSEIPDB_KEY : (getenv('ABUSEIPDB_KEY') ?: '');

if (empty($apiKey)) {
    echo json_encode([
        'success' => false,
        'error'   => 'API Key de AbuseIPDB no configurada. Configura la variable de entorno ABUSEIPDB_KEY con tu clave de https://www.abuseipdb.com',
    ]);
    exit;
}

// Resolver IP del dominio
$ip = gethostbyname($domain);
if ($ip === $domain) {
    if (!filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        echo json_encode(['success' => false, 'error' => 'No se pudo resolver la IP del dominio o no es una IPv4 válida']);
        exit;
    }
    $ip = $domain;
}

if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    echo json_encode(['success' => false, 'error' => 'Solo se admiten IPs IPv4 para la consulta AbuseIPDB']);
    exit;
}

// Consultar AbuseIPDB API v2
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => 'https://api.abuseipdb.com/api/v2/check?' . http_build_query([
        'ipAddress'    => $ip,
        'maxAgeInDays' => 90,
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_HTTPHEADER     => [
        'Key: ' . $apiKey,
        'Accept: application/json',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($response === false || !empty($curlErr)) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión con AbuseIPDB: ' . $curlErr]);
    exit;
}

if ($httpCode === 401) {
    echo json_encode(['success' => false, 'error' => 'API Key inválida o sin permisos. Verifica tu clave en abuseipdb.com']);
    exit;
}
if ($httpCode === 429) {
    echo json_encode(['success' => false, 'error' => 'Límite de solicitudes AbuseIPDB alcanzado. Inténtalo más tarde.']);
    exit;
}
if ($httpCode !== 200) {
    echo json_encode(['success' => false, 'error' => 'AbuseIPDB respondió con HTTP ' . $httpCode]);
    exit;
}

$json = json_decode($response, true);
if (!$json || !isset($json['data'])) {
    echo json_encode(['success' => false, 'error' => 'Respuesta inesperada de AbuseIPDB']);
    exit;
}

$d = $json['data'];

echo json_encode([
    'success'              => true,
    'ip'                   => $d['ipAddress']            ?? $ip,
    'isPublic'             => $d['isPublic']             ?? true,
    'abuseConfidenceScore' => $d['abuseConfidenceScore'] ?? 0,
    'countryCode'          => $d['countryCode']          ?? null,
    'usageType'            => $d['usageType']            ?? null,
    'isp'                  => $d['isp']                  ?? null,
    'domain'               => $d['domain']               ?? null,
    'totalReports'         => $d['totalReports']         ?? 0,
    'numDistinctUsers'     => $d['numDistinctUsers']     ?? 0,
    'lastReportedAt'       => $d['lastReportedAt']       ?? null,
    'isWhitelisted'        => $d['isWhitelisted']        ?? false,
]);
