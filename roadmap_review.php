#!/usr/bin/env php
<?php
/**
 * Check Berry - Revisión diaria del roadmap (Fase 5).
 *
 * Pensado para un cron del propio servidor, no para HTTP (esta sandbox de
 * desarrollo no tiene salida de red hacia el servidor de producción ni
 * hacia Gemini, así que esto NO se puede disparar como una Routine de
 * Claude Code: tiene que ser un cron real en el servidor). Ejemplo de
 * línea de crontab (ajusta la ruta si no coincide con tu despliegue):
 *
 *   0 8 * * * /usr/bin/php /var/www/vhosts/inteligenciageneral.com/httpdocs/app/roadmap_review.php >> /var/www/vhosts/inteligenciageneral.com/httpdocs/app/roadmap_review.log 2>&1
 *
 * Le pasa a Gemini la estructura actual de la app (secciones + módulos) y,
 * si detecta un hueco funcional que valga la pena, propone UNA idea nueva
 * al Roadmap (roadmap_items.source = 'routine') para que el admin la
 * revise en admin.php — igual que una propuesta manual, pero marcada como
 * "propuesta automática". Si no encuentra nada útil, no inserta nada:
 * mejor 0 propuestas que relleno solo por cumplir la ejecución diaria.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Solo se puede ejecutar por línea de comandos (cron), no por HTTP.\n");
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/gemini.php';
require_once __DIR__ . '/menu.php';

$configFile = __DIR__ . '/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
}

echo date('Y-m-d H:i:s') . " — revisión de roadmap\n";

// Una ejecución por día: si ya se procesó hoy, no repetir (por si el cron
// se dispara más de una vez o se relanza a mano).
$today     = date('Y-m-d');
$importKey = 'roadmap_review_' . $today;
$pdo       = db();

$exists = $pdo->prepare('SELECT 1 FROM roadmap_imports WHERE source_file = ?');
$exists->execute([$importKey]);
if ($exists->fetchColumn()) {
    echo "Ya se ejecutó hoy ({$today}). Nada que hacer.\n";
    exit;
}

// ── Estructura actual de la app ─────────────────────────────────────────────
$sections   = array_column(MENU, 'label');
$modulesDir = __DIR__ . '/modules';
$modules    = [];
if (is_dir($modulesDir)) {
    foreach (scandir($modulesDir) as $f) {
        if (str_ends_with($f, '.php')) {
            $modules[] = basename($f, '.php');
        }
    }
}

// ── Ideas ya propuestas por esta rutina (para no repetirse día tras día) ───
$prevTitles = $pdo->query("SELECT title FROM roadmap_items WHERE source = 'routine' ORDER BY created_at DESC LIMIT 20")
    ->fetchAll(PDO::FETCH_COLUMN);

$prompt = 'Eres un asistente que revisa a diario el roadmap de "Check Berry", una suite de soporte técnico '
    . '(diagnóstico DNS/SSL/correo/web, redes, utilidades y desarrollo) para sysadmins.'
    . "\n\nSecciones actuales: " . implode(', ', $sections)
    . "\nMódulos de backend actuales: " . implode(', ', $modules)
    . ($prevTitles ? "\n\nIdeas que YA se propusieron en días anteriores (no las repitas): " . implode(' | ', $prevTitles) : '')
    . "\n\nSi ves un hueco funcional concreto y útil que no esté ya cubierto ni ya propuesto, sugiere UNA sola idea nueva. "
    . 'Si no ves nada que merezca la pena proponer hoy, dilo explícitamente en vez de forzar algo. '
    . 'Devuelve ÚNICAMENTE un objeto JSON, sin texto adicional ni bloques de código, con uno de estos dos formatos exactos: '
    . '{"propuesta": true, "titulo": "<máx 80 caracteres>", "descripcion": "<máx 300 caracteres, en español>"} '
    . 'o {"propuesta": false}';

$gemini = gemini_generate($prompt);

// Se marca el día como procesado tanto si hubo propuesta como si no, para
// no reintentar varias veces el mismo día.
$pdo->prepare('INSERT INTO roadmap_imports (source_file) VALUES (?)')->execute([$importKey]);

if (!$gemini['success']) {
    echo "Error de Gemini: {$gemini['error']}\n";
    exit(1);
}

$text   = preg_replace('/^```(?:json)?\s*|\s*```$/', '', trim($gemini['text']));
$parsed = json_decode($text, true);

if (!is_array($parsed) || empty($parsed['propuesta'])) {
    echo "Gemini no ha encontrado nada que proponer hoy.\n";
    exit;
}

$titulo      = trim((string)($parsed['titulo'] ?? ''));
$descripcion = trim((string)($parsed['descripcion'] ?? ''));
if ($titulo === '') {
    echo "Respuesta de Gemini sin título válido, se descarta.\n";
    exit;
}

$pdo->prepare('INSERT INTO roadmap_items (title, description, status, source, created_by) VALUES (?, ?, ?, ?, ?)')
    ->execute([$titulo, $descripcion, 'pendiente', 'routine', 'Gemini']);

echo "Propuesta añadida: {$titulo}\n";
