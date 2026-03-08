<?php
/**
 * Cuakcom Expert Suite - v2.2.0
 */
define('APP_VERSION', '2.2.0');

// Datos del visitante (server-side)
function getClientIp(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_REAL_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $h) {
        if (!empty($_SERVER[$h])) {
            $ip = trim(explode(',', $_SERVER[$h])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}
$visitorIp = getClientIp();
$visitorUa = $_SERVER['HTTP_USER_AGENT']       ?? '';
$visitorLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
$visitorRef  = $_SERVER['HTTP_REFERER']         ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuakcom Expert Suite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
</head>
<body>

<!-- ===================== HEADER ===================== -->
<header class="header-section">
    <div class="container d-flex align-items-center justify-content-between">
        <h1 class="h5 fw-bold m-0">
            <i class="fa-solid fa-bolt me-2"></i>Cuakcom Expert Suite
        </h1>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm darkmode-toggle" id="btn-darkmode" title="Modo oscuro">
                <i class="fa-solid fa-moon"></i>
            </button>
            <span class="version-badge">v<?= APP_VERSION ?></span>
        </div>
    </div>
</header>

<!-- ===================== MAIN ===================== -->
<main class="container py-3 py-md-4">

    <!-- Search card (compartida entre pestañas) -->
    <div class="card search-card mb-0 mb-md-0" style="border-radius:10px 10px 0 0; border-bottom:none;">
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

    <!-- Tabs -->
    <div class="main-tabs-wrap">
        <ul class="nav main-tabs" id="mainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="main-tab-btn active" id="tab-diag-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-diagnostico" type="button" role="tab">
                    <i class="fa-solid fa-magnifying-glass-chart me-1"></i>Diagnóstico
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="main-tab-btn" id="tab-mail-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-correo" type="button" role="tab">
                    <i class="fa-solid fa-envelope me-1"></i>Correo
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content mb-4">

        <!-- ══════════ TAB: DIAGNÓSTICO ══════════ -->
        <div class="tab-pane fade show active" id="tab-diagnostico" role="tabpanel">
            <div class="card search-options-card">
                <div class="card-body p-3">
                    <!-- Módulos -->
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <button class="btn btn-xs-cuak btn-toggle-all" id="btn-toggle-all" onclick="toggleAllModules()">
                            <i class="fa-solid fa-check-double me-1"></i><span id="toggle-all-label">Activar todo</span>
                        </button>
                        <div class="module-selectors">
                            <div class="form-check form-switch">
                                <input class="form-check-input mod-check" type="checkbox" id="mod-dns">
                                <label class="form-check-label" for="mod-dns"><i class="fa-solid fa-server me-1"></i>DNS</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input mod-check" type="checkbox" id="mod-ports">
                                <label class="form-check-label" for="mod-ports"><i class="fa-solid fa-plug me-1"></i>Puertos</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input mod-check" type="checkbox" id="mod-whois">
                                <label class="form-check-label" for="mod-whois"><i class="fa-solid fa-id-card me-1"></i>WHOIS</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input mod-check" type="checkbox" id="mod-ssl">
                                <label class="form-check-label" for="mod-ssl"><i class="fa-solid fa-lock me-1"></i>SSL</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input mod-check" type="checkbox" id="mod-ping">
                                <label class="form-check-label" for="mod-ping"><i class="fa-solid fa-satellite-dish me-1"></i>Ping</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input mod-check" type="checkbox" id="mod-headers">
                                <label class="form-check-label" for="mod-headers"><i class="fa-solid fa-shield-halved me-1"></i>Cabeceras</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input mod-check" type="checkbox" id="mod-blacklist">
                                <label class="form-check-label" for="mod-blacklist"><i class="fa-solid fa-ban me-1"></i>Blacklist</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input mod-check" type="checkbox" id="mod-traceroute">
                                <label class="form-check-label" for="mod-traceroute"><i class="fa-solid fa-route me-1"></i>Traceroute</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input mod-check" type="checkbox" id="mod-redirect">
                                <label class="form-check-label" for="mod-redirect"><i class="fa-solid fa-arrow-right-arrow-left me-1"></i>Redirecciones</label>
                            </div>
                        </div>
                    </div>
                    <!-- DNS type chips -->
                    <div id="dns-types-row" class="dns-types-row d-none">
                        <span class="dns-types-label"><i class="fa-solid fa-filter me-1"></i>Registros DNS a consultar:</span>
                        <div class="dns-chips" id="dns-chips">
                            <label class="dns-chip"><input type="checkbox" value="A"       checked><span>A</span></label>
                            <label class="dns-chip"><input type="checkbox" value="AAAA"    checked><span>AAAA</span></label>
                            <label class="dns-chip"><input type="checkbox" value="CNAME"   checked><span>CNAME</span></label>
                            <label class="dns-chip"><input type="checkbox" value="MX"      checked><span>MX</span></label>
                            <label class="dns-chip"><input type="checkbox" value="NS"      checked><span>NS</span></label>
                            <label class="dns-chip"><input type="checkbox" value="TXT"     checked><span>TXT</span></label>
                            <label class="dns-chip"><input type="checkbox" value="SPF"     checked><span>SPF</span></label>
                            <label class="dns-chip"><input type="checkbox" value="DMARC"   checked><span>DMARC</span></label>
                            <label class="dns-chip"><input type="checkbox" value="DKIM"    checked><span>DKIM</span></label>
                            <label class="dns-chip"><input type="checkbox" value="CAA"     checked><span>CAA</span></label>
                            <label class="dns-chip"><input type="checkbox" value="SOA"><span>SOA</span></label>
                            <label class="dns-chip"><input type="checkbox" value="SRV"><span>SRV</span></label>
                            <label class="dns-chip"><input type="checkbox" value="MTA-STS"><span>MTA-STS</span></label>
                            <label class="dns-chip"><input type="checkbox" value="BIMI"><span>BIMI</span></label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export bar -->
            <div id="export-bar" class="d-none d-flex justify-content-between align-items-center my-3">
                <small class="text-muted fw-semibold" id="analyzed-domain-label"></small>
                <button class="btn btn-sm btn-outline-primary fw-semibold" onclick="exportAll()">
                    <i class="fa-solid fa-file-export me-1"></i>Exportar todo
                </button>
            </div>

            <!-- Results grid -->
            <div id="results" class="d-none mt-3">
                <div class="row g-3">
                    <div class="col-12 col-md-6 d-flex flex-column gap-3" id="col-left">
                        <?php foreach ([
                            ['resolution', 'bg-primary',   'text-primary',   'Resolución',    null],
                            ['ssl',        'bg-success',    'text-success',   'SSL',           'badge-ssl'],
                            ['ports',      'bg-dark',       'text-dark',      'Puertos',       null],
                            ['ping',       null,            null,             'Ping',          null],
                            ['headers',    null,            null,             'Cabeceras',     null],
                        ] as [$mod, $badgeCls, $dlCls, $label, $badgeId]):
                            $extra = match($mod) {
                                'ping'    => 'style="background:#ea580c"',
                                'headers' => 'style="background:#0891b2"',
                                default   => '',
                            };
                            $dlExtra = match($mod) {
                                'ping'    => 'style="color:#ea580c"',
                                'headers' => 'style="color:#0891b2"',
                                default   => '',
                            };
                            $hide = ($mod !== 'resolution') ? 'd-none' : '';
                        ?>
                        <div class="card result-card <?= $hide ?>" id="card-<?= $mod ?>">
                            <div class="card-header-cuak">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                                    <span class="header-badge <?= $badgeCls ?? '' ?>" <?= $extra ?> <?= $badgeId ? "id=\"{$badgeId}\"" : '' ?>><?= $label ?></span>
                                </div>
                                <button class="btn btn-link p-0 <?= $dlCls ?? '' ?>" <?= $dlExtra ?> <?= $mod === 'ssl' ? 'id="dl-ssl"' : '' ?> onclick="downloadCard('<?= $mod ?>')" title="Descargar">
                                    <i class="fa-solid fa-download"></i>
                                </button>
                            </div>
                            <div class="card-body p-3" id="body-<?= $mod ?>">
                                <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="col-12 col-md-6 d-flex flex-column gap-3" id="col-right">
                        <?php foreach ([
                            ['dns',       null,  null,           'DNS',          null],
                            ['whois',     'bg-danger', 'text-danger', 'WHOIS',  null],
                            ['blacklist', 'bg-success','text-success','Blacklist','badge-blacklist'],
                            ['traceroute',null,  null,           'Traceroute',   null],
                            ['redirect',  null,  null,           'Redirecciones',null],
                        ] as [$mod, $badgeCls, $dlCls, $label, $badgeId]):
                            $extra = match($mod) {
                                'dns'        => 'style="background:#7c3aed"',
                                'traceroute' => 'style="background:#065f46"',
                                'redirect'   => 'style="background:#92400e"',
                                default      => '',
                            };
                            $dlExtra = match($mod) {
                                'dns'        => 'style="color:#7c3aed"',
                                'traceroute' => 'style="color:#065f46"',
                                'redirect'   => 'style="color:#92400e"',
                                default      => '',
                            };
                        ?>
                        <div class="card result-card d-none" id="card-<?= $mod ?>">
                            <div class="card-header-cuak">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                                    <span class="header-badge <?= $badgeCls ?? '' ?>" <?= $extra ?> <?= $badgeId ? "id=\"{$badgeId}\"" : '' ?>><?= $label ?></span>
                                </div>
                                <button class="btn btn-link p-0 <?= $dlCls ?? '' ?>" <?= $dlExtra ?> <?= $mod === 'blacklist' ? 'id="dl-blacklist"' : '' ?> onclick="downloadCard('<?= $mod ?>')" title="Descargar">
                                    <i class="fa-solid fa-download"></i>
                                </button>
                            </div>
                            <div class="card-body p-3" id="body-<?= $mod ?>">
                                <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div><!-- /tab-diagnostico -->

        <!-- ══════════ TAB: CORREO ══════════ -->
        <div class="tab-pane fade" id="tab-correo" role="tabpanel">
            <div class="card search-options-card">
                <div class="card-body p-3 d-flex align-items-center gap-3 flex-wrap">
                    <span class="small text-muted fw-semibold">
                        <i class="fa-solid fa-envelope me-1"></i>Diagnóstico de correo para el dominio introducido arriba
                    </span>
                    <button class="btn btn-mail-analyze ms-auto fw-bold" id="btn-mail-analyze" onclick="startMailAnalysis()">
                        <span id="mail-btn-text"><i class="fa-solid fa-paper-plane me-1"></i>Analizar correo</span>
                        <span id="mail-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                    </button>
                </div>
            </div>

            <div id="mail-results" class="d-none mt-3">
                <div class="row g-3">
                    <!-- Score -->
                    <div class="col-12">
                        <div class="card result-card" id="card-mail-score">
                            <div class="card-header-cuak">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-up-down-left-right drag-handle"></i>
                                    <span class="header-badge" style="background:#1d4ed8" id="badge-mail-score">Entregabilidad</span>
                                </div>
                                <button class="btn btn-link p-0" style="color:#1d4ed8" onclick="downloadMailCard('score')" title="Descargar">
                                    <i class="fa-solid fa-download"></i>
                                </button>
                            </div>
                            <div class="card-body p-3" id="body-mail-score">
                                <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 d-flex flex-column gap-3" id="col-mail-left">
                        <!-- MX -->
                        <div class="card result-card" id="card-mail-mx">
                            <div class="card-header-cuak">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-up-down-left-right drag-handle"></i>
                                    <span class="header-badge bg-warning text-dark">MX Records</span>
                                </div>
                                <button class="btn btn-link p-0 text-warning" onclick="downloadMailCard('mx')" title="Descargar">
                                    <i class="fa-solid fa-download"></i>
                                </button>
                            </div>
                            <div class="card-body p-3" id="body-mail-mx">
                                <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                            </div>
                        </div>
                        <!-- SMTP -->
                        <div class="card result-card" id="card-mail-smtp">
                            <div class="card-header-cuak">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-up-down-left-right drag-handle"></i>
                                    <span class="header-badge" style="background:#374151">SMTP</span>
                                </div>
                                <button class="btn btn-link p-0" style="color:#374151" onclick="downloadMailCard('smtp')" title="Descargar">
                                    <i class="fa-solid fa-download"></i>
                                </button>
                            </div>
                            <div class="card-body p-3" id="body-mail-smtp">
                                <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                            </div>
                        </div>
                        <!-- Blacklist -->
                        <div class="card result-card" id="card-mail-blacklist">
                            <div class="card-header-cuak">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-up-down-left-right drag-handle"></i>
                                    <span class="header-badge bg-success" id="badge-mail-blacklist">Blacklist MX</span>
                                </div>
                                <button class="btn btn-link p-0 text-success" id="dl-mail-blacklist" onclick="downloadMailCard('blacklist')" title="Descargar">
                                    <i class="fa-solid fa-download"></i>
                                </button>
                            </div>
                            <div class="card-body p-3" id="body-mail-blacklist">
                                <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 d-flex flex-column gap-3" id="col-mail-right">
                        <!-- SPF -->
                        <div class="card result-card" id="card-mail-spf">
                            <div class="card-header-cuak">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-up-down-left-right drag-handle"></i>
                                    <span class="header-badge" style="background:#0f766e">SPF</span>
                                </div>
                                <button class="btn btn-link p-0" style="color:#0f766e" onclick="downloadMailCard('spf')" title="Descargar">
                                    <i class="fa-solid fa-download"></i>
                                </button>
                            </div>
                            <div class="card-body p-3" id="body-mail-spf">
                                <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                            </div>
                        </div>
                        <!-- DMARC -->
                        <div class="card result-card" id="card-mail-dmarc">
                            <div class="card-header-cuak">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-up-down-left-right drag-handle"></i>
                                    <span class="header-badge" style="background:#7c3aed">DMARC</span>
                                </div>
                                <button class="btn btn-link p-0" style="color:#7c3aed" onclick="downloadMailCard('dmarc')" title="Descargar">
                                    <i class="fa-solid fa-download"></i>
                                </button>
                            </div>
                            <div class="card-body p-3" id="body-mail-dmarc">
                                <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                            </div>
                        </div>
                        <!-- DKIM -->
                        <div class="card result-card" id="card-mail-dkim">
                            <div class="card-header-cuak">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-up-down-left-right drag-handle"></i>
                                    <span class="header-badge" style="background:#0891b2">DKIM</span>
                                </div>
                                <button class="btn btn-link p-0" style="color:#0891b2" onclick="downloadMailCard('dkim')" title="Descargar">
                                    <i class="fa-solid fa-download"></i>
                                </button>
                            </div>
                            <div class="card-body p-3" id="body-mail-dkim">
                                <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /tab-correo -->

    </div><!-- /tab-content -->

</main>

<!-- ===================== FOOTER VISITANTE ===================== -->
<footer class="visitor-footer">
    <div class="container">
        <div class="visitor-footer-inner" id="visitor-info">
            <span class="visitor-item"><i class="fa-solid fa-circle-notch fa-spin me-1"></i>Cargando datos de acceso…</span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ============================================================
// Cuakcom Expert Suite - v<?= APP_VERSION ?> — Frontend
// ============================================================

const DNS_COLORS = {
    A:'bg-primary', AAAA:'bg-info text-dark', CNAME:'bg-secondary',
    MX:'bg-warning text-dark', NS:'bg-danger', TXT:'bg-success',
    SOA:'bg-dark', SRV:'dns-badge-srv', CAA:'dns-badge-caa',
    SPF:'dns-badge-spf', DMARC:'dns-badge-dmarc', DKIM:'dns-badge-dkim',
    'MTA-STS':'dns-badge-mtasts', BIMI:'dns-badge-bimi',
};

let currentDomain  = '';
const exportData   = {};
const mailExport   = {};

// ── Inicialización ────────────────────────────────────────────
document.getElementById('btn-analyze').addEventListener('click', startAnalysis);
document.getElementById('input-domain').addEventListener('keydown', e => {
    if (e.key === 'Enter') {
        // Analizar según la pestaña activa
        if (document.getElementById('tab-correo').classList.contains('show')) {
            startMailAnalysis();
        } else {
            startAnalysis();
        }
    }
    if (e.key === 'Escape') hideHistoryDropdown();
});
document.getElementById('input-domain').addEventListener('input', showHistoryDropdown);
document.getElementById('input-domain').addEventListener('focus', showHistoryDropdown);
document.addEventListener('click', e => {
    if (!e.target.closest('#input-domain') && !e.target.closest('#history-dropdown'))
        hideHistoryDropdown();
});

// DNS chips toggle
document.getElementById('mod-dns').addEventListener('change', function () {
    document.getElementById('dns-types-row').classList.toggle('d-none', !this.checked);
});

// ── Dark mode ─────────────────────────────────────────────────
const darkBtn = document.getElementById('btn-darkmode');
if (localStorage.getItem('darkMode') === '1') {
    document.body.classList.add('dark-mode');
    darkBtn.innerHTML = '<i class="fa-solid fa-sun"></i>';
}
darkBtn.addEventListener('click', () => {
    const on = document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', on ? '1' : '0');
    darkBtn.innerHTML = on ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
});

// ── Toggle todos los módulos ──────────────────────────────────
function toggleAllModules() {
    const checks = Array.from(document.querySelectorAll('.mod-check'));
    const allOn  = checks.every(c => c.checked);
    checks.forEach(c => { c.checked = !allOn; c.dispatchEvent(new Event('change')); });
    document.getElementById('toggle-all-label').textContent = allOn ? 'Activar todo' : 'Desactivar todo';
}
// Actualizar label del botón al cambiar individualmente
document.querySelectorAll('.mod-check').forEach(c => {
    c.addEventListener('change', () => {
        const all   = document.querySelectorAll('.mod-check');
        const allOn = Array.from(all).every(x => x.checked);
        document.getElementById('toggle-all-label').textContent = allOn ? 'Desactivar todo' : 'Activar todo';
    });
});

// ── Historial ─────────────────────────────────────────────────
const HISTORY_KEY = 'cuakcom_history';
const MAX_HISTORY = 12;
function getHistory() {
    try { return JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]'); } catch { return []; }
}
function saveToHistory(domain) {
    let h = getHistory().filter(d => d !== domain);
    h.unshift(domain);
    localStorage.setItem(HISTORY_KEY, JSON.stringify(h.slice(0, MAX_HISTORY)));
}
function showHistoryDropdown() {
    const val = document.getElementById('input-domain').value.trim().toLowerCase();
    const h   = getHistory().filter(d => !val || d.includes(val));
    const dd  = document.getElementById('history-dropdown');
    if (!h.length) { dd.classList.add('d-none'); return; }
    dd.innerHTML = h.map(d =>
        `<div class="history-item" onclick="selectHistory('${esc(d)}')">`+
        `<i class="fa-solid fa-clock-rotate-left me-2 text-muted small"></i>${esc(d)}</div>`
    ).join('');
    dd.classList.remove('d-none');
}
function hideHistoryDropdown() {
    document.getElementById('history-dropdown').classList.add('d-none');
}
function selectHistory(domain) {
    document.getElementById('input-domain').value = domain;
    hideHistoryDropdown();
    startAnalysis();
}

// ── Análisis principal (Diagnóstico) ─────────────────────────
function startAnalysis() {
    const domain = normalizeDomain();
    if (!domain) return;
    hideHistoryDropdown();
    currentDomain = domain;
    saveToHistory(domain);

    const optional = ['dns','ports','whois','ssl','ping','headers','blacklist','traceroute','redirect'];
    const modules  = optional.filter(m => document.getElementById('mod-' + m).checked);
    const active   = ['resolution', ...modules];

    setAnalyzing(true);
    document.getElementById('results').classList.remove('d-none');
    document.getElementById('export-bar').classList.remove('d-none');
    document.getElementById('analyzed-domain-label').textContent = '🔍 ' + domain;

    const allCards = ['resolution','dns','ports','whois','ssl','ping','headers','blacklist','traceroute','redirect'];
    allCards.forEach(m => {
        const card = document.getElementById('card-' + m);
        if (!card) return;
        if (active.includes(m)) { card.classList.remove('d-none'); setCardLoading(m); }
        else card.classList.add('d-none');
    });

    Promise.allSettled(active.map(m => fetchModule(m, domain)))
        .then(() => setAnalyzing(false));
}

function setAnalyzing(active) {
    document.getElementById('btn-text').classList.toggle('d-none', active);
    document.getElementById('btn-loading').classList.toggle('d-none', !active);
    document.getElementById('btn-analyze').disabled = active;
}

// ── Análisis de correo ────────────────────────────────────────
async function startMailAnalysis() {
    const domain = normalizeDomain();
    if (!domain) return;
    currentDomain = domain;
    saveToHistory(domain);

    document.getElementById('mail-btn-text').classList.add('d-none');
    document.getElementById('mail-btn-loading').classList.remove('d-none');
    document.getElementById('btn-mail-analyze').disabled = true;
    document.getElementById('mail-results').classList.remove('d-none');

    ['mail-score','mail-mx','mail-smtp','mail-spf','mail-dmarc','mail-dkim','mail-blacklist']
        .forEach(id => setMailCardLoading(id.replace('mail-','')));

    try {
        const res  = await fetch(`api.php?module=mailtest&domain=${encodeURIComponent(domain)}`);
        const data = await res.json();
        mailExport['mailtest'] = data;
        if (data.success) renderMailResults(data);
        else {
            ['score','mx','smtp','spf','dmarc','dkim','blacklist'].forEach(k =>
                setMailCardError(k, data.error ?? 'Error desconocido'));
        }
    } catch(e) {
        ['score','mx','smtp','spf','dmarc','dkim','blacklist'].forEach(k =>
            setMailCardError(k, 'Error de conexión: ' + e.message));
    } finally {
        document.getElementById('mail-btn-text').classList.remove('d-none');
        document.getElementById('mail-btn-loading').classList.add('d-none');
        document.getElementById('btn-mail-analyze').disabled = false;
    }
}

function setMailCardLoading(key) {
    const el = document.getElementById('body-mail-' + key);
    if (el) el.innerHTML = `<div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>`;
}
function setMailCardError(key, msg) {
    const el = document.getElementById('body-mail-' + key);
    if (el) el.innerHTML = `<div class="alert alert-danger py-2 mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(msg)}</div>`;
}

// ── Render correo ─────────────────────────────────────────────
function renderMailResults(d) {
    renderMailScore(d);
    renderMailMx(d);
    renderMailSmtp(d);
    renderMailSpf(d);
    renderMailDmarc(d);
    renderMailDkim(d);
    renderMailBlacklist(d);
}

function renderMailScore(d) {
    const pct = Math.round((d.score / d.score_max) * 100);
    const cls = pct >= 70 ? 'bg-success' : (pct >= 40 ? 'bg-warning' : 'bg-danger');
    const txt = pct >= 70 ? 'Buena entregabilidad' : (pct >= 40 ? 'Entregabilidad mejorable' : 'Entregabilidad deficiente');
    document.getElementById('badge-mail-score').className = 'header-badge ' + cls;
    const checks = [
        [d.mx?.length > 0,           'MX configurado'],
        [d.mx?.[0]?.ptr_ok,          'PTR inverso válido'],
        [d.spf?.exists,              'SPF presente'],
        [d.spf?.strict,              'SPF con -all/~all'],
        [d.dmarc?.exists,            'DMARC presente'],
        [['quarantine','reject'].includes(d.dmarc?.policy), 'Política DMARC fuerte'],
        [d.dkim?.length > 0,         'DKIM encontrado'],
        [d.blacklist?.listed === 0,  'IP no en blacklists'],
        [d.smtp?.some(s => s.open),  'SMTP accesible'],
    ];
    const rows = checks.map(([ok, label]) =>
        `<div class="d-flex align-items-center gap-2 mb-1">
            <i class="fa-solid ${ok ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger'} small"></i>
            <span class="small ${ok ? '' : 'text-muted'}">${label}</span>
         </div>`
    ).join('');
    document.getElementById('body-mail-score').innerHTML = `
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="mail-score-circle ${cls.replace('bg-','score-')}">
                <span class="score-num">${d.score}</span>
                <span class="score-den">/${d.score_max}</span>
            </div>
            <div>
                <div class="fw-bold">${txt}</div>
                <div class="progress mt-1" style="height:6px;width:140px">
                    <div class="progress-bar ${cls}" style="width:${pct}%"></div>
                </div>
                ${d.arsys ? '<span class="arsys-badge mt-1"><i class="fa-solid fa-server me-1"></i>ARSYS</span>' : ''}
            </div>
        </div>
        <div class="row g-0">${rows}</div>`;
}

function renderMailMx(d) {
    const rows = (d.mx ?? []).map(m => {
        const arsys = m.arsys ? `<span class="arsys-badge ms-1"><i class="fa-solid fa-server me-1"></i>ARSYS</span>` : '';
        const rowCls = m.arsys ? 'arsys-highlight' : '';
        const ptrIcon = m.ptr_ok ? '<i class="fa-solid fa-circle-check text-success small ms-1" title="PTR válido"></i>'
                                  : (m.ptr ? '<i class="fa-solid fa-triangle-exclamation text-warning small ms-1" title="PTR no coincide"></i>' : '');
        return `<div class="dns-row d-flex align-items-start gap-2 ${rowCls}">
            <span class="badge dns-badge bg-warning text-dark">${m.priority}</span>
            <div class="flex-grow-1 min-w-0">
                <div class="dns-value">${esc(m.host)}${ptrIcon}${arsys}</div>
                ${m.ip   ? `<span class="ttl-badge">IP: ${esc(m.ip)}</span>` : ''}
                ${m.ptr  ? `<span class="ttl-badge ms-1">PTR: ${esc(m.ptr)}</span>` : ''}
            </div>
        </div>`;
    }).join('');
    document.getElementById('body-mail-mx').innerHTML = rows || '<p class="text-muted small mb-0">Sin registros MX.</p>';
}

function renderMailSmtp(d) {
    const rows = (d.smtp ?? []).map(s => {
        const cls  = s.open ? 'text-success' : 'text-muted';
        const icon = s.open ? 'fa-circle-check' : 'fa-circle-xmark';
        return `<div class="d-flex align-items-start gap-2 mb-1">
            <i class="fa-solid ${icon} ${cls} mt-1 small"></i>
            <div>
                <span class="fw-semibold small">${s.port} ${esc(s.label)}</span>
                ${s.open ? `<span class="ttl-badge ms-1">${s.ms} ms</span>` : ''}
                ${s.banner ? `<div class="dns-value small text-muted">${esc(s.banner)}</div>` : ''}
            </div>
        </div>`;
    }).join('');
    document.getElementById('body-mail-smtp').innerHTML = rows;
}

function renderMailSpf(d) {
    const spf = d.spf;
    if (!spf?.exists) {
        document.getElementById('body-mail-spf').innerHTML =
            `<div class="d-flex align-items-center gap-2 text-danger">
                <i class="fa-solid fa-circle-xmark fa-lg"></i>
                <span class="fw-semibold">SPF no configurado</span>
             </div>
             <p class="small text-muted mt-2 mb-0">Sin registro SPF, el correo puede ser rechazado o marcado como spam.</p>`;
        return;
    }
    const strictIcon = spf.strict
        ? '<i class="fa-solid fa-circle-check text-success me-1"></i>'
        : '<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>';
    document.getElementById('body-mail-spf').innerHTML = `
        <div class="d-flex align-items-center gap-2 mb-2 text-success">
            <i class="fa-solid fa-circle-check fa-lg"></i>
            <span class="fw-semibold">SPF configurado</span>
            <span class="ttl-badge">${spf.strict ? 'Estricto' : 'Permisivo'}</span>
        </div>
        <div class="dns-value small p-2" style="background:var(--field-bg);border-radius:6px">${esc(spf.record)}</div>
        <p class="small text-muted mt-2 mb-0">${strictIcon}${spf.strict ? 'Política estricta (-all/~all)' : 'Considera usar -all o ~all para mayor seguridad'}</p>`;
}

function renderMailDmarc(d) {
    const dm = d.dmarc;
    if (!dm?.exists) {
        document.getElementById('body-mail-dmarc').innerHTML =
            `<div class="d-flex align-items-center gap-2 text-danger">
                <i class="fa-solid fa-circle-xmark fa-lg"></i>
                <span class="fw-semibold">DMARC no configurado</span>
             </div>
             <p class="small text-muted mt-2 mb-0">Sin DMARC el dominio es vulnerable a spoofing de correo.</p>`;
        return;
    }
    const polClass = dm.policy === 'reject' ? 'text-success' : (dm.policy === 'quarantine' ? 'text-warning' : 'text-danger');
    const polIcon  = dm.policy === 'reject' ? 'fa-shield-halved' : 'fa-triangle-exclamation';
    document.getElementById('body-mail-dmarc').innerHTML = `
        <div class="d-flex align-items-center gap-2 mb-2 text-success">
            <i class="fa-solid fa-circle-check fa-lg"></i>
            <span class="fw-semibold">DMARC configurado</span>
            <span class="ttl-badge ${polClass}">p=${esc(dm.policy ?? 'none')}</span>
        </div>
        <div class="dns-value small p-2" style="background:var(--field-bg);border-radius:6px">${esc(dm.record)}</div>
        <p class="small mt-2 mb-0 ${polClass}">
            <i class="fa-solid ${polIcon} me-1"></i>
            Política: <strong>${dm.policy ?? 'none'}</strong>
            ${dm.policy !== 'reject' ? ' — Se recomienda usar p=reject' : ' — Excelente'}
        </p>`;
}

function renderMailDkim(d) {
    const dkim = d.dkim ?? [];
    if (!dkim.length) {
        document.getElementById('body-mail-dkim').innerHTML =
            `<div class="d-flex align-items-center gap-2 text-warning">
                <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                <span class="fw-semibold">DKIM no detectado</span>
             </div>
             <p class="small text-muted mt-2 mb-0">No se encontró DKIM en los selectores más comunes. Puede existir con un selector personalizado.</p>`;
        return;
    }
    const rows = dkim.map(k =>
        `<div class="dns-row">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check text-success small"></i>
                <span class="fw-semibold small">Selector: <code>${esc(k.selector)}</code></span>
            </div>
            <div class="dns-value small mt-1" style="background:var(--field-bg);border-radius:4px;padding:4px 8px">${esc(k.value)}</div>
         </div>`
    ).join('');
    document.getElementById('body-mail-dkim').innerHTML =
        `<div class="d-flex align-items-center gap-2 mb-2 text-success">
            <i class="fa-solid fa-circle-check fa-lg"></i>
            <span class="fw-semibold">${dkim.length} selector${dkim.length > 1 ? 'es' : ''} DKIM encontrado${dkim.length > 1 ? 's' : ''}</span>
         </div>${rows}`;
}

function renderMailBlacklist(d) {
    const bl = d.blacklist;
    if (!bl) {
        document.getElementById('body-mail-blacklist').innerHTML =
            '<p class="text-muted small mb-0">No se pudo obtener la IP del MX para comprobar blacklists.</p>';
        return;
    }
    const badge = document.getElementById('badge-mail-blacklist');
    const dl    = document.getElementById('dl-mail-blacklist');
    if (bl.listed === 0) {
        badge.className = 'header-badge bg-success';
        dl.className    = 'btn btn-link p-0 text-success';
    } else {
        badge.className = 'header-badge bg-danger';
        dl.className    = 'btn btn-link p-0 text-danger';
    }
    const rows = (bl.results ?? []).map(r =>
        `<div class="bl-row d-flex align-items-center gap-2">
            <i class="fa-solid ${r.listed ? 'fa-circle-xmark text-danger' : 'fa-circle-check text-success'} small"></i>
            <span class="bl-name">${esc(r.name)}</span>
         </div>`
    ).join('');
    document.getElementById('body-mail-blacklist').innerHTML = `
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid ${bl.listed === 0 ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger'} fa-lg"></i>
            <span class="small fw-semibold ${bl.listed === 0 ? 'text-success' : 'text-danger'}">
                ${bl.listed === 0 ? `IP del MX limpia (${bl.total} listas)` : `Listada en ${bl.listed}/${bl.total}`}
            </span>
        </div>
        <details><summary class="small text-muted" style="cursor:pointer">Ver detalle</summary>
            <div class="mt-2">${rows}</div>
        </details>`;
}

// ── Fetch módulo diagnóstico ──────────────────────────────────
async function fetchModule(module, domain) {
    try {
        let url = `api.php?module=${module}&domain=${encodeURIComponent(domain)}`;
        if (module === 'dns') {
            const types = getSelectedDnsTypes();
            if (types.length) url += `&types=${encodeURIComponent(types.join(','))}`;
        }
        const res  = await fetch(url);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        exportData[module] = data;
        data.success ? renderCard(module, data) : setCardError(module, data.error ?? 'Error desconocido');
    } catch(e) { setCardError(module, 'Error de conexión: ' + e.message); }
}

function getSelectedDnsTypes() {
    return Array.from(document.querySelectorAll('#dns-chips input[type=checkbox]:checked')).map(c => c.value);
}

function setCardLoading(module) {
    document.getElementById('body-' + module).innerHTML =
        `<div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div><div class="skeleton-line"></div></div>`;
}
function setCardError(module, msg) {
    document.getElementById('body-' + module).innerHTML =
        `<div class="alert alert-danger py-2 mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(msg)}</div>`;
}

// ── Renderers diagnóstico ─────────────────────────────────────
function renderCard(module, data) {
    ({resolution:renderResolution,dns:renderDNS,ports:renderPorts,whois:renderWhois,
      ssl:renderSSL,ping:renderPing,headers:renderHeaders,blacklist:renderBlacklist,
      traceroute:renderTraceroute,redirect:renderRedirect})[module]?.(data);
}

function arsysBadgeHtml() {
    return `<span class="arsys-badge ms-1"><i class="fa-solid fa-server me-1"></i>ARSYS</span>`;
}

function renderResolution(data) {
    const ip  = data.ip      ?? 'No resolvió';
    const rev = data.reverse ?? '—';
    const hc  = data.arsys ? 'arsys-highlight' : '';
    const ab  = data.arsys ? arsysBadgeHtml() : '';
    document.getElementById('body-resolution').innerHTML = `
        <div class="dns-row d-flex align-items-center gap-2 border-0 ${hc}">
            <span class="badge dns-badge bg-primary">IP</span>
            <div class="dns-value text-primary fw-bold flex-grow-1">${esc(ip)}</div>${ab}
        </div>
        <div class="dns-row d-flex align-items-start gap-2 border-0 ${hc}">
            <span class="badge dns-badge bg-dark">HOST</span>
            <div class="dns-value text-secondary small flex-grow-1">${esc(rev)}</div>${ab}
        </div>`;
}

function renderDNS(data) {
    const records = data.records ?? [];
    if (!records.length) {
        document.getElementById('body-dns').innerHTML = '<p class="text-muted small mb-0">No se encontraron registros para los tipos seleccionados.</p>';
        return;
    }
    const groups = {};
    for (const r of records) { if (!groups[r.type]) groups[r.type] = []; groups[r.type].push(r); }
    const order = ['A','AAAA','CNAME','MX','NS','TXT','SPF','DMARC','DKIM','CAA','SOA','SRV','MTA-STS','BIMI'];
    const types = order.filter(t => groups[t]);
    const half  = Math.ceil(types.length / 2);
    let html = '<div class="row g-0">';
    for (let col = 0; col < 2; col++) {
        const ct = types.slice(col * half, (col + 1) * half);
        if (!ct.length) break;
        html += `<div class="col-12 col-md-6 ${col === 0 ? 'pe-md-2 dns-col-border' : 'ps-md-2'}">`;
        for (const type of ct) {
            html += `<div class="dns-type-label">${type} Records</div>`;
            for (const r of groups[type]) {
                const bc  = DNS_COLORS[type] ?? 'bg-dark';
                const pri = r.priority  != null ? `<span class="text-muted small ms-1">prio ${r.priority}</span>` : '';
                const ttl = r.ttl       != null ? `<span class="ttl-badge">TTL ${r.ttl}s</span>` : '';
                const sel = r.selector  != null ? `<span class="ttl-badge ms-1">sel: ${esc(r.selector)}</span>` : '';
                const ab  = r.arsys     ? arsysBadgeHtml() : '';
                const rc  = r.arsys     ? 'arsys-highlight' : '';
                html += `<div class="dns-row d-flex align-items-start gap-2 ${rc}">
                    <span class="badge dns-badge ${bc}">${type}</span>
                    <div class="flex-grow-1 min-w-0">
                        <div class="dns-value">${esc(r.value)}${pri}${ab}</div>${ttl}${sel}
                    </div></div>`;
            }
        }
        html += '</div>';
    }
    document.getElementById('body-dns').innerHTML = html + '</div>';
}

function renderPorts(data) {
    let html = '';
    for (const cat of (data.categories ?? [])) {
        html += `<div class="port-group-title">${esc(cat.category)}</div><div class="row g-1 mb-1">`;
        for (const p of cat.ports) {
            html += `<div class="col-6 col-sm-4"><div class="port-row">
                <span class="port-label ${p.open?'open':''}">${esc(p.label)}</span>
                <span class="port-number ${p.open?'open':''}">${p.port}</span>
            </div></div>`;
        }
        html += '</div>';
    }
    document.getElementById('body-ports').innerHTML = html;
}

function renderSSL(data) {
    const expired = data.expired, warning = data.warning, soon = data.soon;
    let stClass, stIcon, badgeCls;
    if (expired)      { stClass='text-danger';  stIcon='fa-circle-xmark';        badgeCls='bg-danger'; }
    else if (warning) { stClass='text-danger';  stIcon='fa-triangle-exclamation'; badgeCls='bg-danger'; }
    else if (soon)    { stClass='text-warning'; stIcon='fa-triangle-exclamation'; badgeCls='bg-warning text-dark'; }
    else              { stClass='text-success'; stIcon='fa-circle-check';         badgeCls='bg-success'; }
    const daysMsg = expired ? `Expirado hace ${Math.abs(data.days_left)} días` : `Expira en ${data.days_left} días`;
    document.getElementById('badge-ssl').className = 'header-badge ' + badgeCls;
    document.getElementById('dl-ssl').className    = 'btn btn-link p-0 ' + stClass;
    document.getElementById('body-ssl').innerHTML = `
        <div class="ssl-status ${stClass} mb-3"><i class="fa-solid ${stIcon} me-2 fa-lg"></i><strong>${daysMsg}</strong></div>
        <div class="ssl-grid">
            <div class="ssl-field"><span class="ssl-label">Dominio</span><span class="ssl-val">${esc(data.subject)}</span></div>
            <div class="ssl-field"><span class="ssl-label">Emisor</span><span class="ssl-val">${esc(data.issuer)}</span></div>
            <div class="ssl-field"><span class="ssl-label">Válido desde</span><span class="ssl-val">${esc(data.valid_from)}</span></div>
            <div class="ssl-field"><span class="ssl-label">Expira</span><span class="ssl-val ${stClass} fw-bold">${esc(data.valid_to)}</span></div>
        </div>`;
}

function renderWhois(data) {
    document.getElementById('body-whois').innerHTML = `<div class="whois-scroll">${esc(data.data)}</div>`;
}

function renderPing(data) {
    const loss=data.packet_loss??0;
    const lc=loss===0?'text-success':(loss===100?'text-danger':'text-warning');
    const li=loss===0?'fa-circle-check':(loss===100?'fa-circle-xmark':'fa-triangle-exclamation');
    let stats='';
    if (data.avg_ms!==null) stats=`
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <div class="ping-stat"><div class="ping-stat-val">${data.avg_ms} ms</div><div class="ping-stat-label">latencia avg</div></div>
            <div class="ping-stat"><div class="ping-stat-val ${lc}"><i class="fa-solid ${li} me-1 small"></i>${loss}%</div><div class="ping-stat-label">pérdida</div></div>
        </div>`;
    document.getElementById('body-ping').innerHTML=`${stats}
        <details><summary class="small text-muted" style="cursor:pointer">Ver salida completa</summary>
        <pre class="terminal mt-2">${esc(data.output)}</pre></details>`;
}

function renderHeaders(data) {
    const pct=Math.round((data.score/data.total)*100);
    const bc=pct>=70?'bg-success':(pct>=40?'bg-warning':'bg-danger');
    let info='';
    if(data.server) info+=`<span class="ttl-badge me-1">Server: ${esc(data.server)}</span>`;
    if(data.powered_by) info+=`<span class="ttl-badge me-1">X-Powered-By: ${esc(data.powered_by)}</span>`;
    if(data.status_code) info+=`<span class="ttl-badge me-1">HTTP ${data.status_code}</span>`;
    const rows=(data.headers??[]).map(h=>`
        <div class="hdr-row d-flex align-items-start gap-2">
            <div class="mt-1"><i class="fa-solid ${h.present?'fa-circle-check text-success':'fa-circle-xmark text-danger'}"></i></div>
            <div class="flex-grow-1 min-w-0">
                <div class="hdr-label">${esc(h.label)}</div>
                <div class="hdr-desc">${esc(h.desc)}</div>
                ${h.present&&h.value?`<div class="dns-value small text-muted mt-1" style="font-size:.7rem">${esc(h.value)}</div>`:''}
            </div>
        </div>`).join('');
    document.getElementById('body-headers').innerHTML=`
        <div class="mb-2 d-flex align-items-center gap-2">
            <div class="flex-grow-1"><div class="progress" style="height:6px"><div class="progress-bar ${bc}" style="width:${pct}%"></div></div></div>
            <small class="fw-bold">${data.score}/${data.total}</small>
        </div>
        ${info?`<div class="mb-2">${info}</div>`:''}
        <div class="hdr-list">${rows}</div>`;
}

function renderBlacklist(data) {
    const badge=document.getElementById('badge-blacklist');
    const dl=document.getElementById('dl-blacklist');
    badge.className='header-badge '+(data.clean?'bg-success':'bg-danger');
    dl.className='btn btn-link p-0 '+(data.clean?'text-success':'text-danger');
    const rows=(data.results??[]).map(r=>`
        <div class="bl-row d-flex align-items-center gap-2">
            <i class="fa-solid ${r.listed?'fa-circle-xmark text-danger':'fa-circle-check text-success'} small"></i>
            <span class="bl-name">${esc(r.name)}</span>
            ${r.rcode?`<span class="ttl-badge ms-auto">${esc(r.rcode)}</span>`:''}
        </div>`).join('');
    document.getElementById('body-blacklist').innerHTML=`
        <div class="d-flex align-items-center mb-2">
            <i class="fa-solid ${data.clean?'fa-circle-check text-success':'fa-circle-xmark text-danger'} fa-lg me-2"></i>
            <span class="small">${data.clean?`IP limpia (${data.total} listas)`:`Listada en ${data.listed}/${data.total}`}</span>
        </div>
        <small class="text-muted d-block mb-2">IP: <code>${esc(data.ip)}</code></small>
        <details><summary class="small text-muted" style="cursor:pointer">Ver detalle</summary><div class="mt-2">${rows}</div></details>`;
}

function renderTraceroute(data) {
    const rows=(data.hops??[]).map(h=>`
        <div class="tr-row d-flex align-items-center gap-2">
            <span class="tr-hop">${h.hop}</span>
            <span class="dns-value small flex-grow-1">${h.timeout?'<span class="text-muted">* * *</span>':esc(h.ip??'?')}</span>
            ${h.ms!=null?`<span class="ttl-badge">${h.ms} ms`:''}
        </div>`).join('');
    document.getElementById('body-traceroute').innerHTML=`
        <div class="small text-muted mb-2">${data.count} saltos</div>
        <div class="tr-list">${rows}</div>
        <details class="mt-2"><summary class="small text-muted" style="cursor:pointer">Ver salida completa</summary>
        <pre class="terminal mt-2">${esc(data.output)}</pre></details>`;
}

function renderRedirect(data) {
    const cc=c=>(c>=200&&c<300?'text-success':c>=300&&c<400?'text-warning':'text-danger');
    const steps=(data.chain??[]).map((s,i)=>`
        <div class="rd-step"><span class="rd-code ${cc(s.code)}">${s.code}</span>
            <span class="rd-url">${esc(s.url)}</span>
            <span class="ttl-badge ms-auto">${s.ms} ms</span>
        </div>${i<data.chain.length-1?'<div class="rd-arrow">↓</div>':''}`).join('');
    document.getElementById('body-redirect').innerHTML=`
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <div class="ping-stat"><div class="ping-stat-val">${data.hops}</div><div class="ping-stat-label">saltos</div></div>
            <div class="ping-stat"><div class="ping-stat-val">${data.total_ms} ms</div><div class="ping-stat-label">total</div></div>
            <div class="ping-stat"><div class="ping-stat-val">${data.has_https?'<i class="fa-solid fa-lock text-success me-1"></i>HTTPS':'HTTP'}</div><div class="ping-stat-label">destino</div></div>
        </div><div class="rd-chain">${steps}</div>`;
}

// ── Exportar ──────────────────────────────────────────────────
function downloadCard(module) {
    const data = exportData[module];
    if (!data) return;
    downloadText(formatExport(module, data), `${module}_${currentDomain}_${stamp()}.txt`);
}
function downloadMailCard(key) {
    const data = mailExport['mailtest'];
    if (!data) return;
    downloadText(formatMailExport(key, data), `mail_${key}_${currentDomain}_${stamp()}.txt`);
}
function exportAll() {
    let text = `REPORTE CUAKCOM EXPERT v<?= APP_VERSION ?>\nDominio: ${currentDomain}\nFecha: ${new Date().toLocaleString('es-ES')}\n${'='.repeat(50)}\n\n`;
    for (const [mod, data] of Object.entries(exportData)) text += formatExport(mod, data) + '\n\n';
    downloadText(text, `reporte_${currentDomain}_${stamp()}.txt`);
}

function formatExport(module, data) {
    if (!data?.success) return `[${module.toUpperCase()}]\nError: ${data?.error??'desconocido'}\n`;
    switch(module) {
        case 'resolution': return `[RESOLUCIÓN]\nIP: ${data.ip??'N/A'}\nHost: ${data.reverse??'N/A'}${data.arsys?'\n⚠ ARSYS':''}`;
        case 'dns':        return `[DNS]\n`+(data.records??[]).map(r=>`${r.type.padEnd(8)} ${r.value}${r.selector?` (${r.selector})`:''}`).join('\n');
        case 'ports':      return `[PUERTOS]\n`+(data.categories??[]).map(c=>`${c.category}:\n`+c.ports.map(p=>`  ${p.port} ${p.label} ${p.open?'OPEN':'CLOSED'}`).join('\n')).join('\n\n');
        case 'ssl':        return `[SSL]\n${data.subject}\n${data.issuer}\n${data.valid_from} → ${data.valid_to}\n${data.days_left} días`;
        case 'whois':      return `[WHOIS]\n${data.data}`;
        case 'ping':       return `[PING]\n${data.avg_ms} ms / ${data.packet_loss}% pérdida`;
        case 'headers':    return `[CABECERAS]\n${(data.headers??[]).map(h=>`${h.present?'✓':'✗'} ${h.label}`).join('\n')}`;
        case 'blacklist':  return `[BLACKLIST]\nIP: ${data.ip}\n${data.clean?'LIMPIA':'LISTADA en '+data.listed}`;
        case 'traceroute': return `[TRACEROUTE]\n${data.output}`;
        case 'redirect':   return `[REDIRECCIONES]\n`+(data.chain??[]).map((s,i)=>`${i+1}. [${s.code}] ${s.url}`).join('\n');
        default:           return `[${module.toUpperCase()}]\n${JSON.stringify(data,null,2)}`;
    }
}
function formatMailExport(key, data) {
    switch(key) {
        case 'score': return `[SCORE]\n${data.score}/${data.score_max}`;
        case 'mx':    return `[MX]\n`+(data.mx??[]).map(m=>`${m.priority} ${m.host} (${m.ip??'?'})`).join('\n');
        case 'smtp':  return `[SMTP]\n`+(data.smtp??[]).map(s=>`${s.port} ${s.open?'OPEN':'CLOSED'}`).join('\n');
        case 'spf':   return `[SPF]\n${data.spf?.record??'No configurado'}`;
        case 'dmarc': return `[DMARC]\n${data.dmarc?.record??'No configurado'}`;
        case 'dkim':  return `[DKIM]\n`+(data.dkim??[]).map(k=>`${k.selector}: ${k.value}`).join('\n\n');
        case 'blacklist': return `[BLACKLIST MX]\n${data.blacklist?.listed===0?'Limpia':'Listada en '+data.blacklist?.listed}`;
        default: return JSON.stringify(data,null,2);
    }
}

function downloadText(content, filename) {
    const blob = new Blob([content], {type:'text/plain;charset=utf-8'});
    const url  = URL.createObjectURL(blob);
    const a    = Object.assign(document.createElement('a'), {href:url, download:filename});
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function normalizeDomain() {
    const raw = document.getElementById('input-domain').value.trim();
    return raw.replace(/^https?:\/\/(www\.)?/i,'').split('/')[0].split('?')[0]
              .replace(/[^a-zA-Z0-9.\-]/g,'').toLowerCase();
}

function esc(str) {
    if (str==null) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function stamp() { return new Date().toISOString().replace(/[:.]/g,'-').slice(0,19); }

// ── SortableJS ────────────────────────────────────────────────
const sortOpts = {group:'cards', handle:'.drag-handle', animation:150, ghostClass:'sortable-ghost', dragClass:'sortable-drag'};
new Sortable(document.getElementById('col-left'),       sortOpts);
new Sortable(document.getElementById('col-right'),      sortOpts);
new Sortable(document.getElementById('col-mail-left'),  sortOpts);
new Sortable(document.getElementById('col-mail-right'), sortOpts);

// ── Footer: datos del visitante ───────────────────────────────
const VISITOR_SERVER = <?= json_encode([
    'ip'   => $visitorIp,
    'ua'   => $visitorUa,
    'lang' => $visitorLang,
    'ref'  => $visitorRef,
]) ?>;

(async function loadVisitorInfo() {
    const el = document.getElementById('visitor-info');
    try {
        // Parseo básico de navegador/OS desde UA
        const ua  = VISITOR_SERVER.ua;
        let browser = 'Desconocido', os = 'Desconocido';
        if (/Edg\//.test(ua))          browser = 'Microsoft Edge';
        else if (/OPR\//.test(ua))     browser = 'Opera';
        else if (/Chrome\//.test(ua))  browser = 'Chrome';
        else if (/Firefox\//.test(ua)) browser = 'Firefox';
        else if (/Safari\//.test(ua))  browser = 'Safari';
        if (/Windows NT/.test(ua))     os = 'Windows';
        else if (/Mac OS X/.test(ua))  os = 'macOS';
        else if (/Linux/.test(ua))     os = 'Linux';
        else if (/Android/.test(ua))   os = 'Android';
        else if (/iPhone|iPad/.test(ua)) os = 'iOS';

        // Geolocalización via ipapi.co (client-side, sin API key)
        let geo = {};
        try {
            const r = await fetch(`https://ipapi.co/${encodeURIComponent(VISITOR_SERVER.ip)}/json/`, {signal: AbortSignal.timeout(5000)});
            geo = await r.json();
        } catch(_) {}

        const items = [
            ['fa-location-dot', geo.country_name ?? '—',           geo.city ? geo.city + ', ' + (geo.region ?? '') : ''],
            ['fa-network-wired', VISITOR_SERVER.ip,                  geo.org ?? ''],
            ['fa-globe',         browser + ' / ' + os,              ''],
            ['fa-language',      VISITOR_SERVER.lang.split(',')[0] ?? '—', ''],
        ];
        if (geo.asn)  items.push(['fa-building', geo.org ?? '', '']);
        if (window.screen) items.push(['fa-display', `${screen.width}×${screen.height}`, '']);

        el.innerHTML = items.map(([icon, main, sub]) =>
            `<span class="visitor-item">
                <i class="fa-solid ${icon} me-1"></i>
                <span>${main}</span>${sub?`<span class="visitor-sub"> · ${sub}</span>`:''}
             </span>`
        ).join('');
    } catch(_) {
        el.innerHTML = `<span class="visitor-item"><i class="fa-solid fa-location-dot me-1"></i>${VISITOR_SERVER.ip}</span>`;
    }
})();
</script>
</body>
</html>
