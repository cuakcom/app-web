<?php
require_once __DIR__ . '/includes/version.php';
require_once __DIR__ . '/includes/auth.php';

$user = require_login('login.php?next=roadmap_new.php');

$pageTitle = 'Proponer una idea';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/page_header_simple.php';
?>
    <h1 class="h4 fw-bold mb-3"><i class="fa-solid fa-lightbulb me-2"></i>Proponer una idea</h1>

    <div class="card search-options-card">
        <div class="card-body p-4">
            <p class="small text-muted">Tu propuesta entra como "pendiente de validar" en el Roadmap.</p>
            <form method="post" action="roadmap_propose.php">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Título</label>
                    <input type="text" name="title" class="form-control" required maxlength="150">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Descripción (opcional)</label>
                    <textarea name="description" class="form-control" rows="3" maxlength="500"></textarea>
                </div>
                <button type="submit" class="btn btn-dark w-100 fw-bold">Enviar propuesta</button>
            </form>
        </div>
    </div>
    <p class="small text-muted mt-3"><a href="roadmap.php">&larr; Volver al Roadmap</a></p>
</main>
<?php require __DIR__ . '/includes/scripts_bottom_simple.php'; ?>
