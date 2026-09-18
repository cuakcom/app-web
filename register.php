<?php
require_once __DIR__ . '/includes/version.php';
require_once __DIR__ . '/includes/auth.php';

if (current_user()) {
    header('Location: index.php');
    exit;
}

$error = '';
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailValue = trim((string)($_POST['email'] ?? ''));
    $password   = (string)($_POST['password'] ?? '');
    $password2  = (string)($_POST['password2'] ?? '');

    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $error = 'Formulario caducado, recarga la página e inténtalo de nuevo.';
    } elseif (!filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
        $error = 'Introduce un email válido.';
    } elseif (!email_allowed($emailValue)) {
        $error = 'Este email no está autorizado a registrarse en Check Berry.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $email = strtolower($emailValue);
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Ya existe una cuenta con ese email. Inicia sesión.';
        } else {
            $isAdmin = ($email === 'jfernandez@arsys.es') ? 1 : 0;
            $stmt = db()->prepare('INSERT INTO users (email, password_hash, is_admin) VALUES (?, ?, ?)');
            $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), $isAdmin]);
            login_user((int)db()->lastInsertId());
            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = 'Crear cuenta';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/page_header_simple.php';
?>
<div class="card search-options-card">
    <div class="card-body p-4">
        <h2 class="h5 fw-bold mb-3"><i class="fa-solid fa-user-plus me-2"></i>Crear cuenta</h2>
        <p class="small text-muted">Solo para emails autorizados (equipo interno). Se usa para proponer y votar ideas en el Roadmap.</p>

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
                <input type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Repite la contraseña</label>
                <input type="password" name="password2" class="form-control" required minlength="8">
            </div>
            <button type="submit" class="btn btn-dark w-100 fw-bold">Crear cuenta</button>
        </form>
        <p class="small text-muted mt-3 mb-0">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    </div>
</div>
</main>
<?php require __DIR__ . '/includes/scripts_bottom_simple.php'; ?>
