<?php
/**
 * Módulo: Geo IP + ASN + PTR
 * Acepta dominio o IP. Resuelve la IP si es dominio.
 * Usa ip-api.com (gratuito, sin API key, ~45 req/min)
 */

// Resolver IP si es dominio
$inputIp = $_GET['ip'] ?? '';
$targetIp = $inputIp ? preg_replace('/[^0-9a-fA-F:.]/', '', $inputIp) : $domain;

if (!filter_var($targetIp, FILTER_VALIDATE_IP)) {
    $resolved = @gethostbyname($targetIp);
    if (!filter_var($resolved, FILTER_VALIDATE_IP)) {
        echo json_encode(['success' => false, 'error' => 'No se pudo resolver la IP del dominio']);
        exit;
    }
    $targetIp = $resolved;
}

// PTR (Reverse DNS)
$ptr = @gethostbyaddr($targetIp);
$ptrValid = ($ptr && $ptr !== $targetIp) ? $ptr : null;

// Geo + ASN via ip-api.com
$fields = 'status,message,country,countryCode,region,regionName,city,zip,lat,lon,timezone,isp,org,as,asname,mobile,proxy,hosting,query';
$ctx = stream_context_create(['http' => [
    'timeout'       => 6,
    'user_agent'    => 'CuakcomExpertSuite/3.0',
    'ignore_errors' => true,
]]);
$rawGeo = @file_get_contents("http://ip-api.com/json/{$targetIp}?fields={$fields}", false, $ctx);
$geo    = ($rawGeo !== false) ? (json_decode($rawGeo, true) ?? []) : [];
$geoOk  = ($geo['status'] ?? 'fail') === 'success';

echo json_encode([
    'success'      => true,
    'input'        => $domain,
    'ip'           => $targetIp,
    'ptr'          => $ptrValid,
    'country'      => $geoOk ? ($geo['country']     ?? null) : null,
    'country_code' => $geoOk ? ($geo['countryCode'] ?? null) : null,
    'region'       => $geoOk ? ($geo['regionName']  ?? null) : null,
    'city'         => $geoOk ? ($geo['city']         ?? null) : null,
    'zip'          => $geoOk ? ($geo['zip']          ?? null) : null,
    'lat'          => $geoOk ? ($geo['lat']          ?? null) : null,
    'lon'          => $geoOk ? ($geo['lon']          ?? null) : null,
    'timezone'     => $geoOk ? ($geo['timezone']     ?? null) : null,
    'isp'          => $geoOk ? ($geo['isp']          ?? null) : null,
    'org'          => $geoOk ? ($geo['org']          ?? null) : null,
    'asn'          => $geoOk ? ($geo['as']           ?? null) : null,
    'asname'       => $geoOk ? ($geo['asname']       ?? null) : null,
    'is_mobile'    => $geoOk ? ($geo['mobile']       ?? false) : null,
    'is_proxy'     => $geoOk ? ($geo['proxy']        ?? false) : null,
    'is_hosting'   => $geoOk ? ($geo['hosting']      ?? false) : null,
    'geo_error'    => !$geoOk ? ($geo['message'] ?? 'Geo no disponible') : null,
]);
