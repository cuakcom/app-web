<?php
/**
 * Check Berry - Smart Check: punto de entrada de la home.
 * Ejecuta los módulos reales según el tipo elegido y le pide a Gemini que
 * redacte un resumen en lenguaje natural a partir de ESOS datos, nunca al
 * revés. Devuelve además enlaces deterministas (no generados por la IA) a
 * los apartados donde se puede ver el detalle.
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/gemini.php';
require_once __DIR__ . '/includes/smart_check_types.php';

function smart_check_fail(string $error, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $error]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    smart_check_fail('Método no permitido', 405);
}

// ── Rate limiting propio (más estricto: cada consulta dispara varios
//    módulos + 1 llamada a Gemini) ─────────────────────────────────────────
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$rateDir  = sys_get_temp_dir() . '/checkberry_smartcheck_rate';
if (!is_dir($rateDir)) @mkdir($rateDir, 0700, true);
if (is_dir($rateDir)) {
    $rateFile = $rateDir . '/' . md5($clientIp) . '.json';
    $now      = time();
    $reqs     = [];
    if (file_exists($rateFile)) {
        $reqs = json_decode((string)@file_get_contents($rateFile), true) ?: [];
    }
    $reqs = array_filter($reqs, fn($t) => $t > $now - 300);
    if (count($reqs) >= 10) {
        smart_check_fail('Demasiadas consultas. Espera unos minutos e inténtalo de nuevo.', 429);
    }
    $reqs[] = $now;
    @file_put_contents($rateFile, json_encode(array_values($reqs)));
}

// ── Entrada ────────────────────────────────────────────────────────────────
$rawQuery  = trim((string)($_POST['query'] ?? ''));
$checkType = (string)($_POST['check_type'] ?? '');
$question  = trim((string)($_POST['question'] ?? ''));
$modelRaw  = trim((string)($_POST['model'] ?? ''));

// Solo letras/números/puntos/guiones (nombre real de modelo de Gemini);
// cualquier otra cosa se ignora y se usa el modelo por defecto.
$model = ($modelRaw !== '' && preg_match('/^[a-zA-Z0-9.\-]{3,60}$/', $modelRaw)) ? $modelRaw : null;

if ($rawQuery === '' && $question === '') {
    smart_check_fail('Introduce un correo, dominio, IP, o escribe tu pregunta.');
}

$isEmail = filter_var($rawQuery, FILTER_VALIDATE_EMAIL) !== false;
$isIp    = filter_var($rawQuery, FILTER_VALIDATE_IP) !== false;
$domain  = $isEmail ? substr(strrchr($rawQuery, '@'), 1) : limpiarHost($rawQuery);

if ($domain === '' && $rawQuery !== '') {
    smart_check_fail('No se ha reconocido un dominio, IP o email válido en "' . $rawQuery . '".');
}

$config = SMART_CHECK_TYPES[$checkType] ?? null;
$moduleSpecs = $config['modules'] ?? SMART_CHECK_DEFAULT_MODULES;

// Si preguntan por una cuenta de correo concreta, se lo pasamos a mailtest.
if ($isEmail) {
    $moduleSpecs = array_map(function ($spec) use ($rawQuery) {
        [$mod, $extra] = $spec;
        if ($mod === 'mailtest') {
            $extra['email'] = $rawQuery;
        }
        return [$mod, $extra];
    }, $moduleSpecs);
}

// ── Ejecutar módulos reales (en paralelo) ───────────────────────────────────
$results = $domain !== '' ? run_modules($moduleSpecs, $domain) : [];

// ── Prompt para Gemini: solo puede razonar sobre estos datos ────────────────
$checkLabel = $config['label'] ?? 'Consulta general';
$promptLines = [
    'Eres el asistente de soporte técnico de Check Berry. Responde en español, tono técnico y directo, máximo 120 palabras.',
    'Basa tu respuesta ÚNICAMENTE en los datos JSON de abajo, que ya han sido verificados por herramientas reales (WHOIS, DNS, listas negras...). No inventes datos que no estén ahí. Si un dato no está disponible, dilo.',
    '',
    'Consulta del usuario: "' . $rawQuery . '"',
    'Tipo de comprobación solicitada: ' . $checkLabel,
];
if ($question !== '') {
    $promptLines[] = 'Pregunta adicional del usuario: ' . $question;
}
if (!empty($config['nota'])) {
    $promptLines[] = 'Limitación conocida: ' . $config['nota'];
}
$promptLines[] = '';
$promptLines[] = 'Datos verificados (JSON):';
$promptLines[] = json_encode($results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$prompt = implode("\n", $promptLines);

$gemini = empty($results) && $domain === ''
    ? ['success' => false, 'error' => 'No hay dominio/IP que analizar; solo se puede responder a la pregunta con conocimiento general (no verificado).']
    : gemini_generate($prompt, $model);

// ── Enlaces deterministas a los apartados (no los genera la IA) ────────────
$sections = [];
foreach (($config['sections'] ?? []) as $s) {
    $sections[] = [
        'label' => $s['label'],
        'url'   => 'index.php?tab=' . urlencode($s['tab']) . '&q=' . urlencode($rawQuery),
    ];
}

echo json_encode([
    'success'  => true,
    'query'    => $rawQuery,
    'domain'   => $domain,
    'summary'  => $gemini['success'] ? $gemini['text'] : null,
    'ai_error' => $gemini['success'] ? null : $gemini['error'],
    'model'    => $gemini['model'] ?? $model ?? GEMINI_DEFAULT_MODEL,
    'sections' => $sections,
    'raw'      => $results,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
