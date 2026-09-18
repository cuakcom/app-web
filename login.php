<?php
require_once __DIR__ . '/includes/version.php';
require_once __DIR__ . '/includes/auth.php';

if (current_user()) {
    header('Location: index.php');
    exit;
}

$error = '';
$emailValue = '';

// Bloqueo simple de fuerza bruta: máx 8 intentos fallidos cada 5 min por IP.
$rateDir = sys_get_temp_dir() . '/checkberry_login_rate';
if (!is_dir($rateDir)) @mkdir($rateDir, 0700, true);
$clientIp  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$rateFile  = $rateDir . '/' . md5($clientIp) . '.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $now = time();
    $attempts = [];
    if (is_dir($rateDir) && file_exists($rateFile)) {
        $attempts = json_decode((string)@file_get_contents($rateFile), true) ?: [];
    }
    $attempts = array_filter($attempts, fn($t) => $t > $now - 300);

    if (count($attempts) >= 8) {
        $error = 'Demasiados intentos fallidos. Espera unos minutos e inténtalo de nuevo.';
    } elseif (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Formulario caducado, recarga la página e inténtalo de nuevo.';
    } else {
        $emailValue = trim((string)($_POST['email'] ?? ''));
        $password   = (string)($_POST['password'] ?? '');

        $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
        $stmt->execute([strtolower($emailValue)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            login_user((int)$user['id']);
            header('Location: index.php');
            exit;
        }

        $attempts[] = $now;
        @file_put_contents($rateFile, json_encode(array_values($attempts)));
        $error = 'Email o contraseña incorrectos.';
    }
}

$pageTitle = 'Iniciar sesión';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/page_header_simple.php';
?>
<div class="card search-options-card">
    <div class="card-body p-4">
        <h2 class="h5 fw-bold mb-3"><i class="fa-solid fa-right-to-bracket me-2"></i>Iniciar sesión</h2>

        <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($emailValue) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-dark w-100 fw-bold">Entrar</button>
        </form>
        <p class="small text-muted mt-3 mb-0">¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
    </div>
</div>
</main>
<?php require __DIR__ . '/includes/scripts_bottom_simple.php'; ?>
