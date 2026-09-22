<?php
require_once __DIR__ . '/includes/auth.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf_token'] ?? null)) {
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    if ($title !== '') {
        $stmt = db()->prepare(
            "INSERT INTO roadmap_items (title, description, status, source, created_by) VALUES (?, ?, 'pendiente', 'manual', ?)"
        );
        $stmt->execute([substr($title, 0, 150), substr($description, 0, 500), $user['email']]);
    }
}

header('Location: roadmap.php');
exit;
