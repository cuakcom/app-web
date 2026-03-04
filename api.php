<?php
/**
 * Cuakcom Expert Suite - API Dispatcher
 * Recibe peticiones AJAX y delega en el módulo correspondiente.
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/functions.php';

$module     = $_GET['module'] ?? '';
$domain_raw = $_GET['domain'] ?? '';
$domain     = limpiarHost($domain_raw);

if (empty($domain)) {
    echo json_encode(['success' => false, 'error' => 'Dominio inválido o vacío']);
    exit;
}

$allowed = ['resolution', 'dns', 'ports', 'whois', 'ssl', 'ping'];
if (!in_array($module, $allowed, true)) {
    echo json_encode(['success' => false, 'error' => 'Módulo no encontrado: ' . htmlspecialchars($module)]);
    exit;
}

require __DIR__ . '/modules/' . $module . '.php';
