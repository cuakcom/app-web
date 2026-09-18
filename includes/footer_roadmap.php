<?php
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
?>
<!-- ===================== ROADMAP ===================== -->
<section class="roadmap-section border-top" id="roadmap">
    <div class="container py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 fw-bold mb-0"><i class="fa-solid fa-map me-2"></i>Roadmap</h2>
            <?php if (!empty($roadmapUser['is_admin'])): ?>
            <a href="admin.php" class="small">Panel admin</a>
            <?php endif; ?>
        </div>

        <div class="row g-3">
            <div class="col-12 col-lg-7">
                <?php if (!$roadmapPublicItems): ?>
                    <p class="small text-muted">Todavía no hay nada en el roadmap.</p>
                <?php endif; ?>
                <?php foreach ($roadmapPublicItems as $item): [$label, $badge] = $roadmapStatusLabels[$item['status']] ?? ['?', 'bg-secondary']; ?>
                    <div class="d-flex align-items-start gap-2 mb-2">
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

            <div class="col-12 col-lg-5">
                <div class="card search-options-card">
                    <div class="card-body p-3">
                        <?php if ($roadmapUser): ?>
                        <h3 class="h6 fw-bold mb-2">Proponer una idea</h3>
                        <form method="post" action="roadmap_propose.php">
                            <?= csrf_field() ?>
                            <input type="text" name="title" class="form-control form-control-sm mb-2" placeholder="Título" required maxlength="150">
                            <textarea name="description" class="form-control form-control-sm mb-2" rows="2" placeholder="Descripción (opcional)" maxlength="500"></textarea>
                            <button type="submit" class="btn btn-dark btn-sm w-100">Enviar propuesta</button>
                        </form>
                        <?php else: ?>
                        <p class="small text-muted mb-2">Inicia sesión para proponer ideas nuevas.</p>
                        <a href="login.php" class="btn btn-dark btn-sm w-100">Iniciar sesión</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
