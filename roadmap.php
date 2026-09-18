<?php
require_once __DIR__ . '/includes/version.php';
require_once __DIR__ . '/includes/auth.php';

$roadmapUser = current_user();
$roadmapPublicItems = db()->query(
    "SELECT * FROM roadmap_items WHERE status != 'rechazada' ORDER BY
     CASE status WHEN 'en_progreso' THEN 0 WHEN 'aceptada' THEN 1 WHEN 'pendiente' THEN 2 WHEN 'hecha' THEN 3 END,
     created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);

$roadmapStatusLabels = [
    'pendiente'   => ['Pendiente de validar', 'bg-secondary'],
    'aceptada'    => ['Aceptada', 'bg-primary'],
    'en_progreso' => ['En progreso', 'bg-warning text-dark'],
    'hecha'       => ['Hecha', 'bg-success'],
];

$pageTitle = 'Roadmap';
$pageMaxWidth = 800;
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/page_header_simple.php';
?>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="fa-solid fa-map me-2"></i>Roadmap</h1>
        <a href="roadmap_new.php" class="btn btn-dark btn-sm"><i class="fa-solid fa-plus me-1"></i>Proponer una idea</a>
    </div>

    <div class="card search-options-card">
        <div class="card-body p-3">
            <?php if (!$roadmapPublicItems): ?>
                <p class="small text-muted mb-0">Todavía no hay nada en el roadmap.</p>
            <?php endif; ?>
            <?php foreach ($roadmapPublicItems as $item): [$label, $badge] = $roadmapStatusLabels[$item['status']] ?? ['?', 'bg-secondary']; ?>
                <div class="d-flex align-items-start gap-2 mb-3">
                    <span class="header-badge <?= $badge ?>" style="font-size:0.62rem; white-space:nowrap"><?= $label ?></span>
                    <div>
                        <strong class="small"><?= htmlspecialchars($item['title']) ?></strong>
                        <?php if ($item['description']): ?>
                        <p class="small text-muted mb-0"><?= htmlspecialchars($item['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <p class="small text-muted mt-3">
        <?php if (!empty($roadmapUser['is_admin'])): ?>
        <a href="admin.php">Panel admin</a> ·
        <?php endif; ?>
        <a href="index.php">Volver a Check Berry</a>
    </p>
</main>
<?php require __DIR__ . '/includes/scripts_bottom_simple.php'; ?>
