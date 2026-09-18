<?php
/**
 * Check Berry - Cliente mínimo de la API de Gemini (Google AI Studio).
 * Solo se usa para redactar el resumen del Smart Check a partir de datos
 * que YA hemos comprobado con nuestros propios módulos: nunca se le pide
 * que "adivine" nada por su cuenta.
 */

function gemini_generate(string $prompt): array {
    $configFile = __DIR__ . '/../config.php';
    if (file_exists($configFile) && !defined('GEMINI_API_KEY')) {
        require_once $configFile;
    }

    $apiKey = (defined('GEMINI_API_KEY') && GEMINI_API_KEY !== '') ? GEMINI_API_KEY : (getenv('GEMINI_API_KEY') ?: '');
    if (empty($apiKey)) {
        return ['success' => false, 'error' => 'GEMINI_API_KEY no configurada. Consigue una clave gratuita en https://aistudio.google.com/apikey y ponla en config.php.'];
    }

    $model = (defined('GEMINI_MODEL') && GEMINI_MODEL !== '') ? GEMINI_MODEL : 'gemini-2.0-flash';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

    $payload = json_encode([
        'contents' => [[
            'parts' => [['text' => $prompt]],
        ]],
        'generationConfig' => [
            'temperature'     => 0.3,
            'maxOutputTokens' => 700,
        ],
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['success' => false, 'error' => 'Error de conexión con Gemini: ' . $curlErr];
    }

    $data = json_decode($response, true);

    if ($httpCode !== 200) {
        $msg = $data['error']['message'] ?? "HTTP {$httpCode}";
        return ['success' => false, 'error' => 'Gemini: ' . $msg];
    }

    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    if ($text === null) {
        $blockReason = $data['promptFeedback']['blockReason'] ?? null;
        return ['success' => false, 'error' => $blockReason
            ? "Gemini bloqueó la respuesta ({$blockReason})."
            : 'Gemini no devolvió texto.'];
    }

    return ['success' => true, 'text' => trim($text)];
}
