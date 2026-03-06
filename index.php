<?php
/**
 * Cuakcom Expert Suite - v2.1.0
 * Interfaz principal. Toda la lógica de diagnóstico se carga vía AJAX desde /modules.
 */
define('APP_VERSION', '2.1.0');
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

    <!-- Search card -->
    <div class="card search-card mb-3 mb-md-4">
        <div class="card-body p-3 p-md-4">
            <!-- Input con historial -->
            <div class="input-group mb-3 position-relative">
                <input type="text" id="input-domain" class="form-control form-control-lg"
                       placeholder="dominio.com o IP" autocomplete="off" spellcheck="false"
                       aria-label="Dominio a analizar">
                <button class="btn btn-dark btn-lg fw-bold px-3 px-md-4" id="btn-analyze" type="button">
                    <span id="btn-text"><i class="fa-solid fa-magnifying-glass me-1 d-none d-sm-inline"></i>ANALIZAR</span>
                    <span id="btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                </button>
                <!-- Dropdown historial -->
                <div id="history-dropdown" class="history-dropdown d-none"></div>
            </div>

            <!-- Módulos (todos desactivados por defecto) -->
            <div class="module-selectors">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="mod-dns">
                    <label class="form-check-label" for="mod-dns"><i class="fa-solid fa-server me-1"></i>DNS</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="mod-ports">
                    <label class="form-check-label" for="mod-ports"><i class="fa-solid fa-plug me-1"></i>Puertos</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="mod-whois">
                    <label class="form-check-label" for="mod-whois"><i class="fa-solid fa-id-card me-1"></i>WHOIS</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="mod-ssl">
                    <label class="form-check-label" for="mod-ssl"><i class="fa-solid fa-lock me-1"></i>SSL</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="mod-ping">
                    <label class="form-check-label" for="mod-ping"><i class="fa-solid fa-satellite-dish me-1"></i>Ping</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="mod-headers">
                    <label class="form-check-label" for="mod-headers"><i class="fa-solid fa-shield-halved me-1"></i>Cabeceras</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="mod-blacklist">
                    <label class="form-check-label" for="mod-blacklist"><i class="fa-solid fa-ban me-1"></i>Blacklist</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="mod-traceroute">
                    <label class="form-check-label" for="mod-traceroute"><i class="fa-solid fa-route me-1"></i>Traceroute</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="mod-redirect">
                    <label class="form-check-label" for="mod-redirect"><i class="fa-solid fa-arrow-right-arrow-left me-1"></i>Redirecciones</label>
                </div>
            </div>

            <!-- Sub-selector de tipos DNS (visible sólo cuando DNS está activo) -->
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

    <!-- Export bar (visible sólo tras analizar) -->
    <div id="export-bar" class="d-none d-flex justify-content-between align-items-center mb-3">
        <small class="text-muted fw-semibold" id="analyzed-domain-label"></small>
        <button class="btn btn-sm btn-outline-primary fw-semibold" onclick="exportAll()">
            <i class="fa-solid fa-file-export me-1"></i>Exportar todo
        </button>
    </div>

    <!-- ===================== RESULTS ===================== -->
    <div id="results" class="d-none">
        <div class="row g-3">

            <!-- Columna izquierda -->
            <div class="col-12 col-md-6 d-flex flex-column gap-3" id="col-left">

                <!-- Resolución (siempre activo) -->
                <div class="card result-card" id="card-resolution">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                            <span class="header-badge bg-primary">Resolución</span>
                        </div>
                        <button class="btn btn-link p-0 text-primary" onclick="downloadCard('resolution')" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-resolution">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>

                <!-- SSL -->
                <div class="card result-card d-none" id="card-ssl">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                            <span class="header-badge bg-success" id="badge-ssl">SSL</span>
                        </div>
                        <button class="btn btn-link p-0 text-success" id="dl-ssl" onclick="downloadCard('ssl')" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-ssl">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div><div class="skeleton-line"></div></div>
                    </div>
                </div>

                <!-- Puertos -->
                <div class="card result-card d-none" id="card-ports">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                            <span class="header-badge bg-dark">Puertos</span>
                        </div>
                        <button class="btn btn-link p-0 text-dark" onclick="downloadCard('ports')" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-ports">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>

                <!-- Ping -->
                <div class="card result-card d-none" id="card-ping">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                            <span class="header-badge" style="background:#ea580c">Ping</span>
                        </div>
                        <button class="btn btn-link p-0" style="color:#ea580c" onclick="downloadCard('ping')" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-ping">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>

                <!-- Cabeceras HTTP -->
                <div class="card result-card d-none" id="card-headers">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                            <span class="header-badge" style="background:#0891b2">Cabeceras</span>
                        </div>
                        <button class="btn btn-link p-0" style="color:#0891b2" onclick="downloadCard('headers')" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-headers">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>

            </div><!-- /col-left -->

            <!-- Columna derecha -->
            <div class="col-12 col-md-6 d-flex flex-column gap-3" id="col-right">

                <!-- DNS -->
                <div class="card result-card d-none" id="card-dns">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                            <span class="header-badge" style="background:#7c3aed">DNS</span>
                        </div>
                        <button class="btn btn-link p-0" style="color:#7c3aed" onclick="downloadCard('dns')" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-dns">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>

                <!-- WHOIS -->
                <div class="card result-card d-none" id="card-whois">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                            <span class="header-badge bg-danger">WHOIS</span>
                        </div>
                        <button class="btn btn-link p-0 text-danger" onclick="downloadCard('whois')" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-whois">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div><div class="skeleton-line"></div></div>
                    </div>
                </div>

                <!-- Blacklist -->
                <div class="card result-card d-none" id="card-blacklist">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                            <span class="header-badge bg-success" id="badge-blacklist">Blacklist</span>
                        </div>
                        <button class="btn btn-link p-0 text-success" id="dl-blacklist" onclick="downloadCard('blacklist')" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-blacklist">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>

                <!-- Traceroute -->
                <div class="card result-card d-none" id="card-traceroute">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                            <span class="header-badge" style="background:#065f46">Traceroute</span>
                        </div>
                        <button class="btn btn-link p-0" style="color:#065f46" onclick="downloadCard('traceroute')" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-traceroute">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>

                <!-- Redirecciones -->
                <div class="card result-card d-none" id="card-redirect">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                            <span class="header-badge" style="background:#92400e">Redirecciones</span>
                        </div>
                        <button class="btn btn-link p-0" style="color:#92400e" onclick="downloadCard('redirect')" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-redirect">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>

            </div><!-- /col-right -->
        </div><!-- /row -->
    </div><!-- /results -->

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ============================================================
// Cuakcom Expert Suite - v<?= APP_VERSION ?> — Frontend
// ============================================================

const DNS_COLORS = {
    A:         'bg-primary',
    AAAA:      'bg-info text-dark',
    CNAME:     'bg-secondary',
    MX:        'bg-warning text-dark',
    NS:        'bg-danger',
    TXT:       'bg-success',
    SOA:       'bg-dark',
    SRV:       'dns-badge-srv',
    CAA:       'dns-badge-caa',
    SPF:       'dns-badge-spf',
    DMARC:     'dns-badge-dmarc',
    DKIM:      'dns-badge-dkim',
    'MTA-STS': 'dns-badge-mtasts',
    BIMI:      'dns-badge-bimi',
};

let currentDomain = '';
const exportData  = {};

// ── Inicialización ────────────────────────────────────────────
document.getElementById('btn-analyze').addEventListener('click', startAnalysis);
document.getElementById('input-domain').addEventListener('keydown', e => {
    if (e.key === 'Enter') startAnalysis();
    if (e.key === 'Escape') hideHistoryDropdown();
});
document.getElementById('input-domain').addEventListener('input', showHistoryDropdown);
document.getElementById('input-domain').addEventListener('focus', showHistoryDropdown);
document.addEventListener('click', e => {
    if (!e.target.closest('#input-domain') && !e.target.closest('#history-dropdown')) {
        hideHistoryDropdown();
    }
});

// ── DNS types toggle ──────────────────────────────────────────
document.getElementById('mod-dns').addEventListener('change', function () {
    document.getElementById('dns-types-row').classList.toggle('d-none', !this.checked);
});

// ── Dark mode ─────────────────────────────────────────────────
const darkBtn = document.getElementById('btn-darkmode');
const body    = document.body;
if (localStorage.getItem('darkMode') === '1') {
    body.classList.add('dark-mode');
    darkBtn.innerHTML = '<i class="fa-solid fa-sun"></i>';
}
darkBtn.addEventListener('click', () => {
    const on = body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', on ? '1' : '0');
    darkBtn.innerHTML = on ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
});

// ── Historial (localStorage) ──────────────────────────────────
const HISTORY_KEY = 'cuakcom_history';
const MAX_HISTORY = 12;

function getHistory() {
    try { return JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]'); } catch { return []; }
}
function saveToHistory(domain) {
    let h = getHistory().filter(d => d !== domain);
    h.unshift(domain);
    if (h.length > MAX_HISTORY) h = h.slice(0, MAX_HISTORY);
    localStorage.setItem(HISTORY_KEY, JSON.stringify(h));
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

// ── Análisis principal ────────────────────────────────────────
function startAnalysis() {
    let raw = document.getElementById('input-domain').value.trim();
    if (!raw) return;
    hideHistoryDropdown();

    let domain = raw
        .replace(/^https?:\/\/(www\.)?/i, '')
        .split('/')[0]
        .split('?')[0]
        .replace(/[^a-zA-Z0-9.\-]/g, '')
        .toLowerCase();

    if (!domain) return;
    currentDomain = domain;
    saveToHistory(domain);

    const optional = ['dns', 'ports', 'whois', 'ssl', 'ping', 'headers', 'blacklist', 'traceroute', 'redirect'];
    const modules  = optional.filter(m => document.getElementById('mod-' + m).checked);
    const active   = ['resolution', ...modules];

    setAnalyzing(true);
    document.getElementById('results').classList.remove('d-none');
    document.getElementById('export-bar').classList.remove('d-none');
    document.getElementById('analyzed-domain-label').textContent = '🔍 ' + domain;

    const allCards = ['resolution', 'dns', 'ports', 'whois', 'ssl', 'ping',
                      'headers', 'blacklist', 'traceroute', 'redirect'];
    allCards.forEach(m => {
        const card = document.getElementById('card-' + m);
        if (active.includes(m)) {
            card.classList.remove('d-none');
            setCardLoading(m);
        } else {
            card.classList.add('d-none');
        }
    });

    const promises = active.map(m => fetchModule(m, domain));
    Promise.allSettled(promises).then(() => setAnalyzing(false));
}

function setAnalyzing(active) {
    document.getElementById('btn-text').classList.toggle('d-none', active);
    document.getElementById('btn-loading').classList.toggle('d-none', !active);
    document.getElementById('btn-analyze').disabled = active;
}

function setCardLoading(module) {
    document.getElementById('body-' + module).innerHTML =
        `<div class="skeleton-wrap">
            <div class="skeleton-line"></div>
            <div class="skeleton-line short"></div>
            <div class="skeleton-line"></div>
        </div>`;
}

function setCardError(module, msg) {
    document.getElementById('body-' + module).innerHTML =
        `<div class="alert alert-danger py-2 mb-0 small">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(msg)}
         </div>`;
}

// ── Fetch módulo ──────────────────────────────────────────────
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
        if (data.success) {
            renderCard(module, data);
        } else {
            setCardError(module, data.error ?? 'Error desconocido');
        }
    } catch (e) {
        setCardError(module, 'Error de conexión: ' + e.message);
    }
}

function getSelectedDnsTypes() {
    return Array.from(
        document.querySelectorAll('#dns-chips input[type=checkbox]:checked')
    ).map(cb => cb.value);
}

// ── Renderers ─────────────────────────────────────────────────
function renderCard(module, data) {
    switch (module) {
        case 'resolution':  renderResolution(data);  break;
        case 'dns':         renderDNS(data);         break;
        case 'ports':       renderPorts(data);       break;
        case 'whois':       renderWhois(data);       break;
        case 'ssl':         renderSSL(data);         break;
        case 'ping':        renderPing(data);        break;
        case 'headers':     renderHeaders(data);     break;
        case 'blacklist':   renderBlacklist(data);   break;
        case 'traceroute':  renderTraceroute(data);  break;
        case 'redirect':    renderRedirect(data);    break;
    }
}

// Resolución — con destacado ARSYS
function renderResolution(data) {
    const ip  = data.ip      ?? 'No resolvió';
    const rev = data.reverse ?? '—';
    const arsysTag = data.arsys
        ? `<span class="arsys-badge ms-2"><i class="fa-solid fa-server me-1"></i>ARSYS</span>`
        : '';

    const ipRowClass  = data.arsys ? 'arsys-highlight' : '';
    const revRowClass = data.arsys ? 'arsys-highlight' : '';

    document.getElementById('body-resolution').innerHTML = `
        <div class="dns-row d-flex align-items-center gap-2 border-0 ${ipRowClass}">
            <span class="badge dns-badge bg-primary">IP</span>
            <div class="dns-value text-primary fw-bold flex-grow-1">${esc(ip)}</div>
            ${data.arsys ? arsysTag : ''}
        </div>
        <div class="dns-row d-flex align-items-start gap-2 border-0 ${revRowClass}">
            <span class="badge dns-badge bg-dark">HOST</span>
            <div class="dns-value text-secondary small flex-grow-1">${esc(rev)}</div>
            ${data.arsys ? arsysTag : ''}
        </div>`;
}

// DNS
function renderDNS(data) {
    const records = data.records ?? [];
    if (!records.length) {
        document.getElementById('body-dns').innerHTML =
            '<p class="text-muted small mb-0">No se encontraron registros DNS para los tipos seleccionados.</p>';
        return;
    }

    const groups = {};
    for (const r of records) {
        if (!groups[r.type]) groups[r.type] = [];
        groups[r.type].push(r);
    }

    const order  = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SPF', 'DMARC', 'DKIM', 'CAA', 'SOA', 'SRV', 'MTA-STS', 'BIMI'];
    const types  = order.filter(t => groups[t]);
    const half   = Math.ceil(types.length / 2);

    let html = '<div class="row g-0">';
    for (let col = 0; col < 2; col++) {
        const colTypes    = types.slice(col * half, (col + 1) * half);
        if (!colTypes.length) break;
        const borderClass = col === 0 ? 'pe-md-2 dns-col-border' : 'ps-md-2';
        html += `<div class="col-12 col-md-6 ${borderClass}">`;
        for (const type of colTypes) {
            html += `<div class="dns-type-label">${type} Records</div>`;
            for (const r of groups[type]) {
                const badgeCls  = DNS_COLORS[type] ?? 'bg-dark';
                const priority  = r.priority  != null ? `<span class="text-muted small ms-1">prio ${r.priority}</span>` : '';
                const ttl       = r.ttl       != null ? `<span class="ttl-badge">TTL ${r.ttl}s</span>` : '';
                const selector  = r.selector  != null ? `<span class="ttl-badge ms-1">sel: ${esc(r.selector)}</span>` : '';
                const arsysBadge= r.arsys     ? `<span class="arsys-badge ms-1"><i class="fa-solid fa-server me-1"></i>ARSYS</span>` : '';
                const rowClass  = r.arsys     ? 'arsys-highlight' : '';

                html += `
                    <div class="dns-row d-flex align-items-start gap-2 ${rowClass}">
                        <span class="badge dns-badge ${badgeCls}">${type}</span>
                        <div class="flex-grow-1 min-w-0">
                            <div class="dns-value">${esc(r.value)}${priority}${arsysBadge}</div>
                            ${ttl}${selector}
                        </div>
                    </div>`;
            }
        }
        html += '</div>';
    }
    html += '</div>';
    document.getElementById('body-dns').innerHTML = html;
}

// Puertos
function renderPorts(data) {
    let html = '';
    for (const cat of (data.categories ?? [])) {
        html += `<div class="port-group-title">${esc(cat.category)}</div>`;
        html += '<div class="row g-1 mb-1">';
        for (const p of cat.ports) {
            html += `
                <div class="col-6 col-sm-4">
                    <div class="port-row">
                        <span class="port-label ${p.open ? 'open' : ''}">${esc(p.label)}</span>
                        <span class="port-number ${p.open ? 'open' : ''}">${p.port}</span>
                    </div>
                </div>`;
        }
        html += '</div>';
    }
    document.getElementById('body-ports').innerHTML = html;
}

// SSL — colores por expiración
function renderSSL(data) {
    const expired = data.expired;
    const warning = data.warning; // < 30 días → rojo
    const soon    = data.soon;    // 30-60 días → naranja

    let stClass, stIcon, badgeCls;
    if (expired) {
        stClass  = 'text-danger';
        stIcon   = 'fa-circle-xmark';
        badgeCls = 'bg-danger';
    } else if (warning) {
        stClass  = 'text-danger';      // < 1 mes → ROJO
        stIcon   = 'fa-triangle-exclamation';
        badgeCls = 'bg-danger';
    } else if (soon) {
        stClass  = 'text-warning';     // 1-2 meses → NARANJA
        stIcon   = 'fa-triangle-exclamation';
        badgeCls = 'bg-warning text-dark';
    } else {
        stClass  = 'text-success';
        stIcon   = 'fa-circle-check';
        badgeCls = 'bg-success';
    }

    const daysMsg = expired
        ? `Expirado hace ${Math.abs(data.days_left)} días`
        : `Expira en ${data.days_left} días`;

    document.getElementById('badge-ssl').className = 'header-badge ' + badgeCls;
    const dl = document.getElementById('dl-ssl');
    dl.className = 'btn btn-link p-0 ' + stClass;

    document.getElementById('body-ssl').innerHTML = `
        <div class="ssl-status ${stClass} mb-3">
            <i class="fa-solid ${stIcon} me-2 fa-lg"></i>
            <strong>${daysMsg}</strong>
        </div>
        <div class="ssl-grid">
            <div class="ssl-field">
                <span class="ssl-label">Dominio</span>
                <span class="ssl-val">${esc(data.subject)}</span>
            </div>
            <div class="ssl-field">
                <span class="ssl-label">Emisor</span>
                <span class="ssl-val">${esc(data.issuer)}</span>
            </div>
            <div class="ssl-field">
                <span class="ssl-label">Válido desde</span>
                <span class="ssl-val">${esc(data.valid_from)}</span>
            </div>
            <div class="ssl-field">
                <span class="ssl-label">Expira</span>
                <span class="ssl-val ${stClass} fw-bold">${esc(data.valid_to)}</span>
            </div>
        </div>`;
}

// WHOIS
function renderWhois(data) {
    document.getElementById('body-whois').innerHTML =
        `<div class="whois-scroll">${esc(data.data)}</div>`;
}

// Ping
function renderPing(data) {
    const loss      = data.packet_loss ?? 0;
    const lossClass = loss === 0 ? 'text-success' : (loss === 100 ? 'text-danger' : 'text-warning');
    const lossIcon  = loss === 0 ? 'fa-circle-check' : (loss === 100 ? 'fa-circle-xmark' : 'fa-triangle-exclamation');

    let statsHtml = '';
    if (data.avg_ms !== null) {
        statsHtml = `
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <div class="ping-stat">
                    <div class="ping-stat-val">${data.avg_ms} ms</div>
                    <div class="ping-stat-label">latencia avg</div>
                </div>
                <div class="ping-stat">
                    <div class="ping-stat-val ${lossClass}">
                        <i class="fa-solid ${lossIcon} me-1 small"></i>${loss}%
                    </div>
                    <div class="ping-stat-label">pérdida</div>
                </div>
            </div>`;
    }
    document.getElementById('body-ping').innerHTML = `
        ${statsHtml}
        <details>
            <summary class="small text-muted" style="cursor:pointer">Ver salida completa</summary>
            <pre class="terminal mt-2">${esc(data.output)}</pre>
        </details>`;
}

// Cabeceras HTTP
function renderHeaders(data) {
    const score = data.score ?? 0;
    const total = data.total ?? 9;
    const pct   = Math.round((score / total) * 100);
    const barCls = pct >= 70 ? 'bg-success' : (pct >= 40 ? 'bg-warning' : 'bg-danger');

    let infoHtml = '';
    if (data.server)     infoHtml += `<span class="ttl-badge me-1">Server: ${esc(data.server)}</span>`;
    if (data.powered_by) infoHtml += `<span class="ttl-badge me-1">X-Powered-By: ${esc(data.powered_by)}</span>`;
    if (data.status_code)infoHtml += `<span class="ttl-badge me-1">HTTP ${data.status_code}</span>`;

    let rows = '';
    for (const h of (data.headers ?? [])) {
        const icon = h.present
            ? '<i class="fa-solid fa-circle-check text-success"></i>'
            : '<i class="fa-solid fa-circle-xmark text-danger"></i>';
        const val  = h.present && h.value
            ? `<div class="dns-value small text-muted mt-1" style="font-size:0.7rem">${esc(h.value)}</div>`
            : '';
        rows += `
            <div class="hdr-row d-flex align-items-start gap-2">
                <div class="mt-1">${icon}</div>
                <div class="flex-grow-1 min-w-0">
                    <div class="hdr-label">${esc(h.label)}</div>
                    <div class="hdr-desc">${esc(h.desc)}</div>
                    ${val}
                </div>
            </div>`;
    }

    document.getElementById('body-headers').innerHTML = `
        <div class="mb-2 d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <div class="progress" style="height:6px">
                    <div class="progress-bar ${barCls}" style="width:${pct}%"></div>
                </div>
            </div>
            <small class="fw-bold ${barCls === 'bg-danger' ? 'text-danger' : (barCls === 'bg-warning' ? 'text-warning' : 'text-success')}">${score}/${total}</small>
        </div>
        ${infoHtml ? `<div class="mb-2">${infoHtml}</div>` : ''}
        <div class="hdr-list">${rows}</div>`;
}

// Blacklist
function renderBlacklist(data) {
    const badge = document.getElementById('badge-blacklist');
    const dl    = document.getElementById('dl-blacklist');
    if (data.clean) {
        badge.className = 'header-badge bg-success';
        dl.className    = 'btn btn-link p-0 text-success';
    } else {
        badge.className = 'header-badge bg-danger';
        dl.className    = 'btn btn-link p-0 text-danger';
    }

    const statusIcon = data.clean
        ? '<i class="fa-solid fa-circle-check text-success fa-lg me-2"></i>'
        : '<i class="fa-solid fa-circle-xmark text-danger fa-lg me-2"></i>';
    const statusTxt  = data.clean
        ? `<strong class="text-success">IP limpia</strong> en las ${data.total} listas consultadas`
        : `<strong class="text-danger">Listada en ${data.listed}/${data.total}</strong> listas negras`;

    let rows = '';
    for (const r of (data.results ?? [])) {
        const cls  = r.listed ? 'text-danger' : 'text-success';
        const icon = r.listed ? 'fa-circle-xmark' : 'fa-circle-check';
        rows += `
            <div class="bl-row d-flex align-items-center gap-2">
                <i class="fa-solid ${icon} ${cls} small"></i>
                <span class="bl-name">${esc(r.name)}</span>
                ${r.rcode ? `<span class="ttl-badge ms-auto">${esc(r.rcode)}</span>` : ''}
            </div>`;
    }

    document.getElementById('body-blacklist').innerHTML = `
        <div class="d-flex align-items-center mb-2">${statusIcon}<span class="small">${statusTxt}</span></div>
        <small class="text-muted d-block mb-2">IP analizada: <code>${esc(data.ip)}</code></small>
        <details>
            <summary class="small text-muted" style="cursor:pointer">Ver detalle por lista</summary>
            <div class="mt-2">${rows}</div>
        </details>`;
}

// Traceroute
function renderTraceroute(data) {
    const hops = data.hops ?? [];
    if (!hops.length) {
        document.getElementById('body-traceroute').innerHTML =
            '<p class="text-muted small mb-0">No se obtuvieron saltos.</p>';
        return;
    }

    let rows = '';
    for (const h of hops) {
        const ip   = h.timeout ? '<span class="text-muted">* * *</span>' : esc(h.ip ?? '?');
        const ms   = h.ms != null ? `<span class="ttl-badge">${h.ms} ms</span>` : '';
        rows += `
            <div class="tr-row d-flex align-items-center gap-2">
                <span class="tr-hop">${h.hop}</span>
                <span class="dns-value small flex-grow-1">${ip}</span>
                ${ms}
            </div>`;
    }

    document.getElementById('body-traceroute').innerHTML = `
        <div class="small text-muted mb-2">${data.count} saltos detectados</div>
        <div class="tr-list">${rows}</div>
        <details class="mt-2">
            <summary class="small text-muted" style="cursor:pointer">Ver salida completa</summary>
            <pre class="terminal mt-2">${esc(data.output)}</pre>
        </details>`;
}

// Redirecciones
function renderRedirect(data) {
    const chain = data.chain ?? [];
    const httpsIcon = data.has_https
        ? '<i class="fa-solid fa-lock text-success me-1"></i>'
        : '<i class="fa-solid fa-lock-open text-danger me-1"></i>';

    const codeColor = code => {
        if (code >= 200 && code < 300) return 'text-success';
        if (code >= 300 && code < 400) return 'text-warning';
        return 'text-danger';
    };

    let steps = '';
    for (let i = 0; i < chain.length; i++) {
        const s    = chain[i];
        const cls  = codeColor(s.code);
        const arrow = i < chain.length - 1 ? '<div class="rd-arrow">↓</div>' : '';
        steps += `
            <div class="rd-step">
                <span class="rd-code ${cls}">${s.code}</span>
                <span class="rd-url">${esc(s.url)}</span>
                <span class="ttl-badge ms-auto">${s.ms} ms</span>
            </div>${arrow}`;
    }

    document.getElementById('body-redirect').innerHTML = `
        <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
            <div class="ping-stat">
                <div class="ping-stat-val">${data.hops}</div>
                <div class="ping-stat-label">saltos</div>
            </div>
            <div class="ping-stat">
                <div class="ping-stat-val">${data.total_ms} ms</div>
                <div class="ping-stat-label">tiempo total</div>
            </div>
            <div class="ping-stat">
                <div class="ping-stat-val">${httpsIcon}${data.has_https ? 'HTTPS' : 'HTTP'}</div>
                <div class="ping-stat-label">destino final</div>
            </div>
        </div>
        <div class="rd-chain">${steps}</div>`;
}

// ── Exportar ──────────────────────────────────────────────────
function downloadCard(module) {
    const data = exportData[module];
    if (!data) return;
    downloadText(formatExport(module, data), `${module}_${currentDomain}_${stamp()}.txt`);
}

function exportAll() {
    let text = `REPORTE CUAKCOM EXPERT v<?= APP_VERSION ?>\n`;
    text += `Dominio : ${currentDomain}\n`;
    text += `Fecha   : ${new Date().toLocaleString('es-ES')}\n`;
    text += '='.repeat(50) + '\n\n';
    for (const [mod, data] of Object.entries(exportData)) {
        text += formatExport(mod, data) + '\n\n';
    }
    downloadText(text, `reporte_${currentDomain}_${stamp()}.txt`);
}

function formatExport(module, data) {
    if (!data || !data.success) return `[${module.toUpperCase()}]\nError: ${data?.error ?? 'desconocido'}\n`;
    switch (module) {
        case 'resolution':
            return `[RESOLUCIÓN]\nIP: ${data.ip ?? 'N/A'}\nHost inverso: ${data.reverse ?? 'N/A'}${data.arsys ? '\n⚠ Servidor ARSYS detectado' : ''}`;
        case 'dns':
            return `[DNS]\n` + (data.records ?? []).map(r => {
                const sel = r.selector ? ` (selector: ${r.selector})` : '';
                const pri = r.priority != null ? ` (prio ${r.priority})` : '';
                const ttl = r.ttl      != null ? ` [TTL ${r.ttl}s]`      : '';
                return `${r.type.padEnd(8)} ${r.value}${sel}${pri}${ttl}`;
            }).join('\n');
        case 'ports':
            return `[PUERTOS]\n` + (data.categories ?? []).map(cat =>
                `${cat.category}:\n` + cat.ports.map(p =>
                    `  ${String(p.port).padEnd(6)} ${p.label.padEnd(14)} ${p.open ? 'OPEN' : 'CLOSED'}`
                ).join('\n')
            ).join('\n\n');
        case 'ssl':
            return `[SSL]\nDominio: ${data.subject}\nEmisor: ${data.issuer}\nVálido desde: ${data.valid_from}\nExpira: ${data.valid_to}\nDías restantes: ${data.days_left}`;
        case 'whois':
            return `[WHOIS]\n${data.data}`;
        case 'ping':
            return `[PING]\nLatencia avg: ${data.avg_ms} ms\nPérdida: ${data.packet_loss}%\n\n${data.output}`;
        case 'headers':
            return `[CABECERAS HTTP]\nURL: ${data.url}\nHTTP ${data.status_code}\nPuntuación: ${data.score}/${data.total}\n\n` +
                (data.headers ?? []).map(h => `${h.present ? '✓' : '✗'} ${h.label}${h.value ? ': ' + h.value : ''}`).join('\n');
        case 'blacklist':
            return `[BLACKLIST]\nIP: ${data.ip}\nResultado: ${data.clean ? 'LIMPIA' : 'LISTADA en ' + data.listed + '/' + data.total}\n\n` +
                (data.results ?? []).map(r => `${r.listed ? '✗' : '✓'} ${r.name}`).join('\n');
        case 'traceroute':
            return `[TRACEROUTE]\n${data.output}`;
        case 'redirect':
            return `[REDIRECCIONES]\nSaltos: ${data.hops}\nTiempo total: ${data.total_ms} ms\nDestino: ${data.final_url}\n\n` +
                (data.chain ?? []).map((s, i) => `${i + 1}. [${s.code}] ${s.url} (${s.ms} ms)`).join('\n');
        default:
            return `[${module.toUpperCase()}]\n${JSON.stringify(data, null, 2)}`;
    }
}

function downloadText(content, filename) {
    const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
    const url  = URL.createObjectURL(blob);
    const a    = Object.assign(document.createElement('a'), { href: url, download: filename });
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function esc(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function stamp() {
    return new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
}

// ── Drag & drop entre columnas (SortableJS) ───────────────────
const sortableOptions = {
    group:      'cards',
    handle:     '.drag-handle',
    animation:  150,
    ghostClass: 'sortable-ghost',
    dragClass:  'sortable-drag',
};
new Sortable(document.getElementById('col-left'),  sortableOptions);
new Sortable(document.getElementById('col-right'), sortableOptions);
</script>
</body>
</html>
