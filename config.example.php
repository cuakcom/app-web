<?php
/**
 * Check Berry - Plantilla de configuración de claves y secretos.
 *
 * Copia este archivo como "config.php" (mismo directorio) y rellena los
 * valores reales. "config.php" está en .gitignore: nunca se sube al repo.
 */

// AbuseIPDB (https://www.abuseipdb.com/register) - módulo modules/abuseipdb.php
define('ABUSEIPDB_KEY', '');

// Google Gemini API (https://aistudio.google.com/apikey) - Smart Check de la home
define('GEMINI_API_KEY', '');

// Secreto del webhook de GitHub que dispara el auto-deploy (deploy.php).
// Genera un valor aleatorio largo y usa el mismo en GitHub > Settings > Webhooks > Secret.
define('DEPLOY_SECRET', '');
