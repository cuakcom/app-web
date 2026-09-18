<!-- ===================== HEADER ===================== -->
<header class="header-section">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="header-title-block">
            <div class="d-flex align-items-center gap-3">
                <img src="favicon.svg" alt="Check Berry Logo" class="header-logo" width="50" height="50">
                <div>
                    <h1 class="header-title-main">
                        Check Berry
                    </h1>
                    <span class="header-title-sub">Soporte técnico: diagnóstico, redes, correo, web y utilidades</span>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="btn-group" role="group" title="Cambiar tema">
                <button type="button" class="btn btn-sm darkmode-toggle" id="btn-darkmode" title="Modo oscuro/claro">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <button type="button" class="btn btn-sm darkmode-toggle dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" id="theme-dropdown" title="Seleccionar tema">
                    <i class="fa-solid fa-palette"></i>
                    <span class="visually-hidden">Temas</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" id="theme-menu">
                    <li><a class="dropdown-item" href="#" data-theme="default" onclick="switchTheme('default', event)">
                        <i class="fa-solid fa-check me-2" id="check-default"></i>Por defecto
                    </a></li>
                    <li><a class="dropdown-item" href="#" data-theme="luminous" onclick="switchTheme('luminous', event)">
                        <i class="fa-solid fa-check me-2" id="check-luminous"></i>Luminous (Claro)
                    </a></li>
                    <li><a class="dropdown-item" href="#" data-theme="neon-tokyo" onclick="switchTheme('neon-tokyo', event)">
                        <i class="fa-solid fa-check me-2" id="check-neon"></i>Neon Tokyo (Oscuro)
                    </a></li>
                </ul>
            </div>
            <span class="version-badge">v<?= APP_VERSION ?></span>
        </div>
    </div>
</header>

<!-- ===================== MAIN ===================== -->
<main class="container py-3 py-md-4">

    <!-- Tabs (encima del buscador) -->
    <div class="main-tabs-wrap">
        <ul class="nav main-tabs" id="mainTabs" role="tablist">
            <?php foreach (MENU as $i => $item): ?>
            <li class="nav-item" role="presentation">
                <button class="main-tab-btn<?= $i === 0 ? ' active' : '' ?>" id="<?= $item['btn_id'] ?>" data-bs-toggle="tab"
                        data-bs-target="#<?= $item['pane_id'] ?>" type="button" role="tab">
                    <i class="fa-solid <?= $item['icon'] ?> me-1"></i><?= htmlspecialchars($item['label']) ?>
                    <?php if (!empty($item['info'])): ?>
                    <span class="info-popover-btn" data-info-key="<?= $item['pane_id'] ?>" title="Información" onclick="event.stopPropagation()">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>
                    <?php endif; ?>
                </button>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Search card (se oculta en pestañas con input propio) -->
    <div id="search-card-wrap">
    <div class="card search-card mb-0" style="border-radius:10px 10px 0 0; border-bottom:none;">
        <div class="card-body p-3 p-md-4 pb-2">
            <div class="input-group position-relative">
                <input type="text" id="input-domain" class="form-control form-control-lg"
                       placeholder="dominio.com o IP" autocomplete="off" spellcheck="false"
                       aria-label="Dominio a analizar">
                <button class="btn btn-dark btn-lg fw-bold px-3 px-md-4" id="btn-analyze" type="button">
                    <span id="btn-text"><i class="fa-solid fa-magnifying-glass me-1 d-none d-sm-inline"></i>ANALIZAR</span>
                    <span id="btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                </button>
                <div id="history-dropdown" class="history-dropdown d-none"></div>
            </div>
        </div>
    </div>
    </div>

    <div class="tab-content mb-4">
        <?php foreach (MENU as $item): ?>
            <?php require $item['file']; ?>
        <?php endforeach; ?>
    </div><!-- /tab-content -->

</main>
