<?php
/**
 * Check Berry - Autenticación sencilla (registro solo si el email está
 * en la whitelist de allowed_emails, sesión nativa de PHP, CSRF básico).
 */

require_once __DIR__ . '/db.php';

function auth_start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/** ¿El email coincide con la whitelist (exacto o *@dominio)? */
function email_allowed(string $email): bool {
    $email = strtolower(trim($email));
    $domain = substr(strrchr($email, '@'), 1) ?: '';

    $stmt = db()->query('SELECT pattern FROM allowed_emails');
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $pattern) {
        $pattern = strtolower(trim($pattern));
        if ($pattern === $email) {
            return true;
        }
        if (str_starts_with($pattern, '*@') && substr($pattern, 2) === $domain) {
            return true;
        }
    }
    return false;
}

function current_user(): ?array {
    auth_start_session();
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $stmt = db()->prepare('SELECT id, email, is_admin, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $cache = ($user ?: null);
}

/** Redirige a login.php si no hay sesión. Devuelve el usuario si la hay. */
function require_login(string $redirectTo = 'login.php'): array {
    $user = current_user();
    if (!$user) {
        header('Location: ' . $redirectTo);
        exit;
    }
    return $user;
}

function require_admin(): array {
    $user = require_login();
    if (empty($user['is_admin'])) {
        http_response_code(403);
        exit('Acceso solo para administradores.');
    }
    return $user;
}

function login_user(int $userId): void {
    auth_start_session();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void {
    auth_start_session();
    $_SESSION = [];
    session_destroy();
}

function csrf_token(): string {
    auth_start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(?string $token): bool {
    auth_start_session();
    return !empty($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}
