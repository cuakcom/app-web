<?php
/**
 * Módulo: SEO / Open Graph + Detección de tecnologías
 */

$url = 'https://' . $domain;
$ctx = stream_context_create([
    'http' => [
        'timeout'          => 10,
        'follow_location'  => true,
        'max_redirects'    => 5,
        'user_agent'       => 'Mozilla/5.0 (compatible; CuakcomBot/3.0; +https://cuakcom.com)',
        'ignore_errors'    => true,
    ],
    'ssl' => [
        'verify_peer'      => false,
        'verify_peer_name' => false,
    ],
]);

$t0  = microtime(true);
$html = @file_get_contents($url, false, $ctx);
$responseHeaders = $http_response_header ?? [];

if ($html === false || empty($html)) {
    $url  = 'http://' . $domain;
    $t0   = microtime(true);
    $html = @file_get_contents($url, false, $ctx);
    $responseHeaders = $http_response_header ?? [];
}

$ms = (int)round((microtime(true) - $t0) * 1000);

if ($html === false || empty($html)) {
    echo json_encode(['success' => false, 'error' => 'No se pudo cargar la web. El servidor puede estar inaccesible.']);
    exit;
}

// Normalizar: eliminar scripts/styles para buscar meta más rápido
$htmlMeta = preg_replace('#<(script|style)[^>]*>.*?</(script|style)>#si', '', $html);

$headersStr = implode("\n", $responseHeaders);
$statusCode = null;
if (preg_match('#HTTP/\S+ (\d{3})#', $headersStr, $m)) $statusCode = (int)$m[1];

// ── Helpers ───────────────────────────────────────────────────────────────────
function getMeta(string $html, string $name): ?string {
    $n = preg_quote($name, '/');
    if (preg_match('/<meta[^>]+name=["\']' . $n . '["\'][^>]+content=["\']([^"\']*)["\'][^>]*>/i', $html, $m) ||
        preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']' . $n . '["\'][^>]*>/i', $html, $m)) {
        return trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
    }
    return null;
}

function getOg(string $html, string $prop): ?string {
    $p = preg_quote($prop, '/');
    if (preg_match('/<meta[^>]+property=["\']og:' . $p . '["\'][^>]+content=["\']([^"\']*)["\'][^>]*>/i', $html, $m) ||
        preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+property=["\']og:' . $p . '["\'][^>]*>/i', $html, $m)) {
        return trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
    }
    return null;
}

function getTwitter(string $html, string $prop): ?string {
    $p = preg_quote($prop, '/');
    if (preg_match('/<meta[^>]+name=["\']twitter:' . $p . '["\'][^>]+content=["\']([^"\']*)["\'][^>]*>/i', $html, $m) ||
        preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']twitter:' . $p . '["\'][^>]*>/i', $html, $m)) {
        return trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
    }
    return null;
}

// ── SEO / Meta ────────────────────────────────────────────────────────────────
preg_match('/<title[^>]*>(.*?)<\/title>/si', $htmlMeta, $tm);
$title = isset($tm[1]) ? trim(strip_tags($tm[1])) : null;

preg_match('/<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']+)/i', $htmlMeta, $cm);
$canonical = $cm[1] ?? null;

$h1s = [];
preg_match_all('/<h1[^>]*>(.*?)<\/h1>/si', $htmlMeta, $h1m);
foreach ($h1m[1] as $h) $h1s[] = trim(strip_tags($h));

$description = getMeta($htmlMeta, 'description');
$keywords    = getMeta($htmlMeta, 'keywords');
$robots      = getMeta($htmlMeta, 'robots');
$viewport    = getMeta($htmlMeta, 'viewport');
$generator   = getMeta($htmlMeta, 'generator');

// Title length advice
$titleLen    = $title ? mb_strlen($title) : null;
$titleOk     = $titleLen && $titleLen >= 30 && $titleLen <= 60;
$descLen     = $description ? mb_strlen($description) : null;
$descOk      = $descLen && $descLen >= 70 && $descLen <= 160;

$seo = [
    'title'          => $title,
    'title_len'      => $titleLen,
    'title_ok'       => $titleOk,
    'description'    => $description,
    'desc_len'       => $descLen,
    'desc_ok'        => $descOk,
    'robots'         => $robots,
    'keywords'       => $keywords,
    'canonical'      => $canonical,
    'viewport'       => $viewport,
    'generator'      => $generator,
    'h1'             => $h1s,
    'h1_count'       => count($h1s),
    'og_title'       => getOg($htmlMeta, 'title'),
    'og_description' => getOg($htmlMeta, 'description'),
    'og_image'       => getOg($htmlMeta, 'image'),
    'og_type'        => getOg($htmlMeta, 'type'),
    'og_url'         => getOg($htmlMeta, 'url'),
    'og_site_name'   => getOg($htmlMeta, 'site_name'),
    'tw_card'        => getTwitter($htmlMeta, 'card'),
    'tw_title'       => getTwitter($htmlMeta, 'title'),
    'tw_image'       => getTwitter($htmlMeta, 'image'),
    'tw_site'        => getTwitter($htmlMeta, 'site'),
];

// ── Tech detection ────────────────────────────────────────────────────────────
$tech = [];
$seen = [];

function addTech(array &$tech, array &$seen, string $name, string $cat, string $version = ''): void {
    if (!in_array($name, $seen, true)) {
        $seen[]  = $name;
        $tech[]  = ['name' => $name, 'cat' => $cat, 'version' => $version];
    }
}

// From response headers
$serverHeader  = '';
$poweredBy     = '';
foreach ($responseHeaders as $h) {
    if (stripos($h, 'server:') === 0)        $serverHeader = trim(substr($h, 7));
    if (stripos($h, 'x-powered-by:') === 0)  $poweredBy    = trim(substr($h, 13));
    if (stripos($h, 'x-generator:') === 0)   addTech($tech, $seen, trim(substr($h, 12)), 'CMS');
    if (stripos($h, 'x-drupal-cache:') === 0) addTech($tech, $seen, 'Drupal', 'CMS');
    if (stripos($h, 'x-wp-') !== false)       addTech($tech, $seen, 'WordPress', 'CMS');
    // Cookie-based
    if (stripos($h, 'set-cookie:') === 0) {
        if (preg_match('/PHPSESSID/i', $h))          addTech($tech, $seen, 'PHP', 'Runtime');
        if (preg_match('/JSESSIONID/i', $h))          addTech($tech, $seen, 'Java/JEE', 'Runtime');
        if (preg_match('/ASP\.NET_SessionId/i', $h))  addTech($tech, $seen, 'ASP.NET', 'Runtime');
        if (preg_match('/laravel_session/i', $h))     addTech($tech, $seen, 'Laravel', 'Framework');
        if (preg_match('/wp_/i', $h))                 addTech($tech, $seen, 'WordPress', 'CMS');
        if (preg_match('/PrestaShop/i', $h))          addTech($tech, $seen, 'PrestaShop', 'eCommerce');
    }
}
if ($serverHeader) addTech($tech, $seen, $serverHeader, 'Servidor web');
if ($poweredBy)    addTech($tech, $seen, $poweredBy, 'Runtime');

// From HTML patterns
$patterns = [
    ['WordPress',    'CMS',           '/wp-content\/(themes|plugins)|wp-includes/i'],
    ['Drupal',       'CMS',           '/sites\/default\/files|Drupal\.settings/i'],
    ['Joomla',       'CMS',           '/\/components\/com_|Joomla!/i'],
    ['TYPO3',        'CMS',           '/typo3\/(sysext|contrib)/i'],
    ['Prestashop',   'eCommerce',     '/prestashop|presta_shop/i'],
    ['Shopify',      'eCommerce',     '/cdn\.shopify\.com|Shopify\.shop/i'],
    ['WooCommerce',  'eCommerce',     '/woocommerce|wc-cart/i'],
    ['Magento',      'eCommerce',     '/mage\/|Magento_|skin\/frontend/i'],
    ['OpenCart',     'eCommerce',     '/catalog\/view\/theme/i'],
    ['Wix',          'Constructor',   '/wix\.com|wixstatic\.com/i'],
    ['Squarespace',  'Constructor',   '/squarespace\.com|sqspcdn\.com/i'],
    ['Webflow',      'Constructor',   '/webflow\.io|webflow\.com/i'],
    ['Bootstrap',    'CSS Framework', '/bootstrap\.min\.css|bootstrap\.css/i'],
    ['Tailwind CSS', 'CSS Framework', '/tailwind(css)?\.min|tailwindcss/i'],
    ['Foundation',   'CSS Framework', '/foundation\.min\.css/i'],
    ['jQuery',       'JS',            '/jquery(\.min)?\.js/i'],
    ['React',        'JS Framework',  '/react\.production\.min|react-dom/i'],
    ['Vue\.js',      'JS Framework',  '/vue(\.global|\.min)?\.js/i'],
    ['Angular',      'JS Framework',  '/angular(\.min)?\.js|ng-version/i'],
    ['Next\.js',     'JS Framework',  '/__next\/static/i'],
    ['Nuxt\.js',     'JS Framework',  '/_nuxt\//i'],
    ['Google Analytics', 'Analytics', '/google-analytics\.com\/analytics\.js|gtag\/js\?id=G-/i'],
    ['Google Tag Manager','Analytics','/googletagmanager\.com\/gtm\.js/i'],
    ['Meta Pixel',   'Analytics',     '/connect\.facebook\.net.*fbevents/i'],
    ['Hotjar',       'Analytics',     '/static\.hotjar\.com/i'],
    ['Cloudflare',   'CDN/Seguridad', '/cloudflare\.com|cf-ray/i'],
    ['reCAPTCHA',    'Seguridad',     '/google\.com\/recaptcha/i'],
    ['hCaptcha',     'Seguridad',     '/hcaptcha\.com/i'],
    ['Font Awesome', 'UI',            '/fontawesome|font-awesome/i'],
    ['Google Fonts', 'UI',            '/fonts\.googleapis\.com/i'],
];

foreach ($patterns as [$name, $cat, $rx]) {
    if (preg_match($rx, $html)) addTech($tech, $seen, $name, $cat);
}

// Sort tech by category
usort($tech, fn($a, $b) => strcmp($a['cat'], $b['cat']));

echo json_encode([
    'success'     => true,
    'url'         => $url,
    'status_code' => $statusCode,
    'response_ms' => $ms,
    'seo'         => $seo,
    'tech'        => $tech,
    'tech_count'  => count($tech),
]);
