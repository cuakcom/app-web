<?php
/**
 * Check Berry - Desarrollo: genera una expresión regular a partir de una
 * descripción en lenguaje natural (usa Gemini). Solo se usa para redactar
 * el patrón; el propio endpoint valida que compile antes de devolverlo.
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
set_time_limit(30);

function regex_generate_fail(string $error, int $code = 400): void {
    if (!headers_sent()) {
        http_response_code($code);
    }
    echo json_encode(['success' => false, 'error' => $error]);
    exit;
}

set_exception_handler(function (Throwable $e) {
    regex_generate_fail('Error interno: ' . $e->getMessage(), 500);
});
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        echo json_encode(['success' => false, 'error' => 'Error fatal en el servidor: ' . $err['message'] . ' (' . $err['file'] . ':' . $err['line'] . ')']);
    }
});

require_once __DIR__ . '/includes/gemini.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    regex_generate_fail('Método no permitido', 405);
}

// ── Rate limiting propio ────────────────────────────────────────────────────
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$rateDir  = sys_get_temp_dir() . '/checkberry_regex_rate';
if (!is_dir($rateDir)) @mkdir($rateDir, 0700, true);
if (is_dir($rateDir)) {
    $rateFile = $rateDir . '/' . md5($clientIp) . '.json';
    $now      = time();
    $reqs     = [];
    if (file_exists($rateFile)) {
        $reqs = json_decode((string)@file_get_contents($rateFile), true) ?: [];
    }
    $reqs = array_filter($reqs, fn($t) => $t > $now - 300);
    if (count($reqs) >= 15) {
        regex_generate_fail('Demasiadas consultas. Espera unos minutos e inténtalo de nuevo.', 429);
    }
    $reqs[] = $now;
    @file_put_contents($rateFile, json_encode(array_values($reqs)));
}

$description = trim((string)($_POST['description'] ?? ''));
if ($description === '') {
    regex_generate_fail('Describe qué quieres que capture la expresión regular.');
}
if (mb_strlen($description) > 400) {
    regex_generate_fail('Descripción demasiado larga (máx. 400 caracteres).');
}

$prompt = 'Eres un generador de expresiones regulares (sintaxis compatible con JavaScript). '
    . 'A partir de la descripción en lenguaje natural de abajo, devuelve ÚNICAMENTE un objeto JSON '
    . 'con este formato exacto, sin texto adicional ni bloques de código: '
    . '{"regex": "<patrón sin delimitadores>", "flags": "<flags, p.ej. i, g, o vacío>", "explicacion": "<explicación breve en español, máx 40 palabras>"}'
    . "\n\nDescripción: \"{$description}\"";

$gemini = gemini_generate($prompt);

if (!$gemini['success']) {
    regex_generate_fail($gemini['error'], 502);
}

// Gemini a veces envuelve el JSON en ```json ... ``` a pesar de la instrucción.
$text = preg_replace('/^```(?:json)?\s*|\s*```$/', '', trim($gemini['text']));

$parsed = json_decode($text, true);
if (!is_array($parsed) || !isset($parsed['regex'])) {
    regex_generate_fail('Gemini no devolvió un patrón válido. Prueba a describirlo de otra forma.');
}

// Delimitador "~" para no chocar con "/" dentro del propio patrón. Se valida
// que compile antes de devolverlo: un patrón que no compila no sirve de nada.
$flags = preg_replace('/[^a-zA-Z]/', '', (string)($parsed['flags'] ?? ''));
$valid = @preg_match('~' . $parsed['regex'] . '~' . $flags, '');
if ($valid === false) {
    regex_generate_fail('El patrón generado no es válido en PHP/PCRE. Prueba a describirlo de otra forma.');
}

echo json_encode([
    'success'     => true,
    'regex'       => $parsed['regex'],
    'flags'       => $flags,
    'explicacion' => $parsed['explicacion'] ?? '',
    'model'       => $gemini['model'],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
