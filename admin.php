<?php
require_once __DIR__ . '/includes/version.php';
require_once __DIR__ . '/includes/auth.php';

$admin = require_admin();
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $notice = 'Formulario caducado, recarga la página e inténtalo de nuevo.';
    } else {
        $action = (string)($_POST['action'] ?? '');

        if ($action === 'add_email') {
            $pattern = strtolower(trim((string)($_POST['pattern'] ?? '')));
            $valid = $pattern !== '' && (
                filter_var($pattern, FILTER_VALIDATE_EMAIL) ||
                preg_match('/^\*@[a-z0-9.\-]+\.[a-z]{2,}$/', $pattern)
            );
            if ($valid) {
                $stmt = db()->prepare('INSERT OR IGNORE INTO allowed_emails (pattern) VALUES (?)');
                $stmt->execute([$pattern]);
                $notice = 'Añadido: ' . htmlspecialchars($pattern);
            } else {
                $notice = 'Patrón inválido. Usa "usuario@dominio.com" o "*@dominio.com".';
            }
        } elseif ($action === 'remove_email') {
            $stmt = db()->prepare('DELETE FROM allowed_emails WHERE id = ?');
            $stmt->execute([(int)($_POST['id'] ?? 0)]);
            $notice = 'Eliminado de la whitelist.';
        } elseif ($action === 'update_roadmap_status') {
            $status = (string)($_POST['status'] ?? '');
            if (in_array($status, ['pendiente', 'aceptada', 'rechazada', 'en_progreso', 'hecha'], true)) {
                $stmt = db()->prepare("UPDATE roadmap_items SET status = ?, updated_at = datetime('now') WHERE id = ?");
                $stmt->execute([$status, (int)($_POST['id'] ?? 0)]);
                $notice = 'Estado del roadmap actualizado.';
            }
        } elseif ($action === 'delete_roadmap') {
            $stmt = db()->prepare('DELETE FROM roadmap_items WHERE id = ?');
            $stmt->execute([(int)($_POST['id'] ?? 0)]);
            $notice = 'Propuesta eliminada.';
        }
    }
}

$allowedEmails = db()->query('SELECT * FROM allowed_emails ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
$roadmapItems  = db()->query('SELECT * FROM roadmap_items ORDER BY (status = \'pendiente\') DESC, created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

$statusLabels = [
    'pendiente'   => ['Pendiente', 'bg-secondary'],
    'aceptada'    => ['Aceptada', 'bg-primary'],
    'en_progreso' => ['En progreso', 'bg-warning text-dark'],
    'hecha'       => ['Hecha', 'bg-success'],
    'rechazada'   => ['Rechazada', 'bg-danger'],
];

$pageTitle = 'Panel de administración';
$pageMaxWidth = 900;
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/page_header_simple.php';
?>
    <h1 class="h4 fw-bold mb-3">Panel de administración</h1>

    <?php if ($notice): ?>
    <div class="alert alert-info py-2 small"><?= $notice ?></div>
    <?php endif; ?>

    <div class="card search-options-card mb-4">
        <div class="card-body p-3">
            <h2 class="h6 fw-bold mb-3"><i class="fa-solid fa-users me-2"></i>Whitelist de registro</h2>
            <form method="post" class="row g-2 align-items-end mb-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_email">
                <div class="col-auto flex-grow-1">
                    <input type="text" name="pattern" class="form-control form-control-sm" placeholder="usuario@dominio.com o *@dominio.com" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-dark btn-sm">Añadir</button>
                </div>
            </form>
            <table class="table table-sm mb-0">
                <tbody>
                <?php foreach ($allowedEmails as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['pattern']) ?></td>
                        <td class="text-end">
                            <form method="post" class="d-inline" onsubmit="return confirm('¿Quitar de la whitelist?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="remove_email">
                                <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
                                <button type="submit" class="btn btn-link btn-sm text-danger p-0">Quitar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card search-options-card">
        <div class="card-body p-3">
            <h2 class="h6 fw-bold mb-3"><i class="fa-solid fa-map me-2"></i>Roadmap — propuestas</h2>
            <?php if (!$roadmapItems): ?>
                <p class="small text-muted mb-0">Todavía no hay propuestas.</p>
            <?php endif; ?>
            <?php foreach ($roadmapItems as $item): [$label, $badge] = $statusLabels[$item['status']] ?? ['?', 'bg-secondary']; ?>
                <div class="border-top pt-2 mt-2">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <span class="header-badge <?= $badge ?>" style="font-size:0.65rem"><?= $label ?></span>
                            <strong class="ms-1"><?= htmlspecialchars($item['title']) ?></strong>
                            <p class="small text-muted mb-0"><?= htmlspecialchars($item['description']) ?></p>
                            <span class="small text-muted" style="font-size:0.7rem">
                                <?= htmlspecialchars($item['created_by'] ?: 'anónimo') ?> · <?= htmlspecialchars($item['created_at']) ?>
                                <?= $item['source'] === 'routine' ? ' · propuesta automática' : '' ?>
                            </span>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="update_roadmap_status">
                                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <?php foreach ($statusLabels as $key => [$lbl, ]): ?>
                                    <option value="<?= $key ?>" <?= $key === $item['status'] ? 'selected' : '' ?>><?= $lbl ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <form method="post" onsubmit="return confirm('¿Eliminar esta propuesta?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_roadmap">
                                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                <button type="submit" class="btn btn-link btn-sm text-danger p-0">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<?php require __DIR__ . '/includes/scripts_bottom_simple.php'; ?>
