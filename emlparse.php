<?php
/**
 * Cuakcom Expert Suite - Analizador de cabeceras .eml
 * Endpoint independiente (acepta POST multipart o JSON base64)
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$content = '';

if (!empty($_FILES['eml']['tmp_name'])) {
    $file = $_FILES['eml'];
    if ($file['size'] > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'error' => 'Archivo demasiado grande (máx 10 MB)']);
        exit;
    }
    $content = (string)file_get_contents($file['tmp_name']);
} elseif (!empty($_POST['content'])) {
    $content = (string)base64_decode($_POST['content']);
} else {
    echo json_encode(['success' => false, 'error' => 'No se recibió ningún archivo o contenido']);
    exit;
}

if (empty($content)) {
    echo json_encode(['success' => false, 'error' => 'El archivo está vacío']);
    exit;
}

// Validar que parece un email (debe tener cabeceras básicas)
if (!preg_match('/^(From|Received|Date|Message-ID):/im', $content)) {
    echo json_encode(['success' => false, 'error' => 'El archivo no parece un mensaje de correo válido']);
    exit;
}

// ── Separar cabeceras del cuerpo ──────────────────────────────────────────────
$parts       = preg_split('/\r?\n\r?\n/', $content, 2);
$headerBlock = $parts[0] ?? '';

// Desdoblar cabeceras multi-línea (RFC 2822: líneas de continuación empiezan con espacio/tab)
$headerBlock = preg_replace('/\r?\n([ \t]+)/', ' ', $headerBlock);

// ── Parsear cabeceras individuales ────────────────────────────────────────────
$raw = [];
foreach (explode("\n", $headerBlock) as $line) {
    $line = rtrim($line, "\r\n");
    if (preg_match('/^([a-zA-Z0-9][a-zA-Z0-9\-]*)\s*:\s*(.*)$/', $line, $m)) {
        $name = $m[1];
        $raw[$name][] = trim($m[2]);
    }
}

// ── Cabeceras importantes (con descripción) ───────────────────────────────────
$metaHeaders = [
    'From'                      => ['cat' => 'Origen',         'desc' => 'Remitente del mensaje'],
    'To'                        => ['cat' => 'Destino',        'desc' => 'Destinatario principal'],
    'Cc'                        => ['cat' => 'Destino',        'desc' => 'Copia a'],
    'Bcc'                       => ['cat' => 'Destino',        'desc' => 'Copia oculta'],
    'Reply-To'                  => ['cat' => 'Origen',         'desc' => 'Dirección de respuesta'],
    'Return-Path'               => ['cat' => 'Origen',         'desc' => 'Dirección de rebote (envelope from)'],
    'Subject'                   => ['cat' => 'Contenido',      'desc' => 'Asunto del mensaje'],
    'Date'                      => ['cat' => 'Fecha',          'desc' => 'Fecha y hora de envío'],
    'Message-ID'                => ['cat' => 'Identificación', 'desc' => 'Identificador único del mensaje'],
    'In-Reply-To'               => ['cat' => 'Hilo',           'desc' => 'ID del mensaje al que responde'],
    'References'                => ['cat' => 'Hilo',           'desc' => 'IDs de mensajes anteriores en el hilo'],
    'MIME-Version'              => ['cat' => 'Formato',        'desc' => 'Versión MIME'],
    'Content-Type'              => ['cat' => 'Formato',        'desc' => 'Tipo de contenido y codificación'],
    'Content-Transfer-Encoding' => ['cat' => 'Formato',        'desc' => 'Codificación de transferencia'],
    'X-Mailer'                  => ['cat' => 'Software',       'desc' => 'Cliente de correo usado para enviar'],
    'User-Agent'                => ['cat' => 'Software',       'desc' => 'Agente de usuario del cliente'],
    'X-Originating-IP'          => ['cat' => 'Red',            'desc' => 'IP origen del envío'],
    'X-Forwarded-To'            => ['cat' => 'Red',            'desc' => 'Reenvío de mensaje'],
    'Authentication-Results'    => ['cat' => 'Autenticación',  'desc' => 'Resultados de SPF, DKIM y DMARC'],
    'Received-SPF'              => ['cat' => 'Autenticación',  'desc' => 'Resultado de verificación SPF'],
    'DKIM-Signature'            => ['cat' => 'Autenticación',  'desc' => 'Firma DKIM del mensaje'],
    'ARC-Authentication-Results'=> ['cat' => 'Autenticación',  'desc' => 'ARC: resultados de autenticación en cadena'],
    'ARC-Message-Signature'     => ['cat' => 'Autenticación',  'desc' => 'ARC: firma del mensaje'],
    'ARC-Seal'                  => ['cat' => 'Autenticación',  'desc' => 'ARC: sello de la cadena'],
    'X-Spam-Score'              => ['cat' => 'Spam',           'desc' => 'Puntuación de spam asignada'],
    'X-Spam-Status'             => ['cat' => 'Spam',           'desc' => 'Estado spam (Yes/No)'],
    'X-Spam-Flag'               => ['cat' => 'Spam',           'desc' => 'Marca de spam'],
    'X-Spam-Level'              => ['cat' => 'Spam',           'desc' => 'Nivel de spam (asteriscos)'],
    'X-Spam-Report'             => ['cat' => 'Spam',           'desc' => 'Detalle del análisis antispam'],
    'List-Unsubscribe'          => ['cat' => 'Lista',          'desc' => 'Enlace para darse de baja'],
    'List-ID'                   => ['cat' => 'Lista',          'desc' => 'Identificador de lista de correo'],
    'Precedence'                => ['cat' => 'Lista',          'desc' => 'Prioridad (bulk, list, junk)'],
];

// ── Construir lista de cabeceras para la tabla ────────────────────────────────
$headers = [];
foreach ($raw as $name => $values) {
    $meta = $metaHeaders[$name] ?? null;
    foreach ($values as $val) {
        $headers[] = [
            'name'      => $name,
            'value'     => $val,
            'category'  => $meta['cat']  ?? 'Otras',
            'desc'      => $meta['desc'] ?? '',
            'important' => $meta !== null,
        ];
    }
}

// Ordenar: importantes primero, luego por nombre
usort($headers, fn($a, $b) => [$b['important'], $a['name']] <=> [$a['important'], $b['name']]);

// ── Received chain (ruta del mensaje) ────────────────────────────────────────
$received = array_reverse($raw['Received'] ?? []);  // Cronológico: primer hop primero

// ── Autenticación ─────────────────────────────────────────────────────────────
$spfResult   = null;
$dkimResult  = null;
$dmarcResult = null;
foreach ($raw['Authentication-Results'] ?? [] as $ar) {
    if ($spfResult   === null && preg_match('/spf=(pass|fail|softfail|neutral|none|permerror|temperror)/i',   $ar, $m)) $spfResult   = strtolower($m[1]);
    if ($dkimResult  === null && preg_match('/dkim=(pass|fail|neutral|none|permerror|temperror)/i',          $ar, $m)) $dkimResult  = strtolower($m[1]);
    if ($dmarcResult === null && preg_match('/dmarc=(pass|fail|bestguesspass|none)/i',                       $ar, $m)) $dmarcResult = strtolower($m[1]);
}
// Fallback: Received-SPF
if ($spfResult === null && !empty($raw['Received-SPF'])) {
    preg_match('/^(Pass|Fail|SoftFail|Neutral|None|PermError|TempError)/i', $raw['Received-SPF'][0], $m);
    if (isset($m[1])) $spfResult = strtolower($m[1]);
}

// ── Spam score ────────────────────────────────────────────────────────────────
$spamScore = null;
if (!empty($raw['X-Spam-Score'])) {
    preg_match('/(-?[\d.]+)/', $raw['X-Spam-Score'][0], $m);
    if (isset($m[1])) $spamScore = (float)$m[1];
}

echo json_encode([
    'success'      => true,
    'subject'      => $raw['Subject'][0]    ?? null,
    'from'         => $raw['From'][0]        ?? null,
    'to'           => $raw['To'][0]          ?? null,
    'date'         => $raw['Date'][0]        ?? null,
    'message_id'   => $raw['Message-ID'][0] ?? null,
    'headers'      => $headers,
    'received'     => $received,
    'auth'         => ['spf' => $spfResult, 'dkim' => $dkimResult, 'dmarc' => $dmarcResult],
    'spam_score'   => $spamScore,
    'total_headers'=> count($headers),
    'hops'         => count($received),
]);
