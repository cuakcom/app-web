<?php
/**
 * GitHub Webhook - Auto Deploy
 * Configurar en GitHub: Settings > Webhooks > Add webhook
 * URL: https://inteligenciageneral.com/app/deploy.php
 * Content type: application/json
 * Secret: (el mismo valor que DEPLOY_SECRET abajo)
 */

define('DEPLOY_SECRET', 'CAMBIA_ESTE_TOKEN_POR_UNO_SEGURO');
define('REPO_DIR', '/var/www/vhosts/inteligenciageneral.com/httpdocs/app');
define('BRANCH', 'master');

// Verificar firma de GitHub
$payload   = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$expected  = 'sha256=' . hash_hmac('sha256', $payload, DEPLOY_SECRET);

if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    exit('Forbidden');
}

// Solo actuar en push a master
$data  = json_decode($payload, true);
$ref   = $data['ref'] ?? '';
if ($ref !== 'refs/heads/' . BRANCH) {
    http_response_code(200);
    exit('Ignored: ' . $ref);
}

// Ejecutar git pull
$output = shell_exec('cd ' . escapeshellarg(REPO_DIR) . ' && git pull origin ' . BRANCH . ' 2>&1');

// Log
$log = date('Y-m-d H:i:s') . " | ref=$ref\n$output\n---\n";
file_put_contents(REPO_DIR . '/deploy.log', $log, FILE_APPEND);

http_response_code(200);
echo "OK\n" . htmlspecialchars($output);
