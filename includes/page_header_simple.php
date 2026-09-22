<!-- Cabecera reducida para páginas fuera del panel de herramientas (login/registro/admin) -->
<header class="header-section">
    <div class="container d-flex align-items-center justify-content-between">
        <a href="index.php" class="d-flex align-items-center gap-3 text-decoration-none">
            <img src="favicon.svg" alt="Check Berry Logo" class="header-logo" width="50" height="50">
            <div>
                <h1 class="header-title-main">Check Berry</h1>
                <span class="header-title-sub"><?= htmlspecialchars($pageTitle ?? '') ?></span>
            </div>
        </a>
    </div>
</header>
<main class="container py-4" style="max-width:<?= (int)($pageMaxWidth ?? 480) ?>px">
