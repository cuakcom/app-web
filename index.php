<?php
/**
 * Cuakcom Expert Suite - v2.0.0
 * Interfaz principal. Toda la lógica de diagnóstico se carga vía AJAX desde /modules.
 */
define('APP_VERSION', '2.0.0');
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
        <span class="version-badge">v<?= APP_VERSION ?></span>
    </div>
</header>

<!-- ===================== MAIN ===================== -->
<main class="container py-3 py-md-4">

    <!-- Search card -->
    <div class="card search-card mb-3 mb-md-4">
        <div class="card-body p-3 p-md-4">
            <div class="input-group mb-3">
                <input type="text" id="input-domain" class="form-control form-control-lg"
                       placeholder="dominio.com o IP" autocomplete="off" spellcheck="false"
                       aria-label="Dominio a analizar">
                <button class="btn btn-dark btn-lg fw-bold px-3 px-md-4" id="btn-analyze" type="button">
                    <span id="btn-text"><i class="fa-solid fa-magnifying-glass me-1 d-none d-sm-inline"></i>ANALIZAR</span>
                    <span id="btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                </button>
            </div>
            <div class="module-selectors">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="mod-dns" checked>
                    <label class="form-check-label" for="mod-dns"><i class="fa-solid fa-server me-1"></i>DNS</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="mod-ports" checked>
                    <label class="form-check-label" for="mod-ports"><i class="fa-solid fa-plug me-1"></i>Puertos</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="mod-whois" checked>
                    <label class="form-check-label" for="mod-whois"><i class="fa-solid fa-id-card me-1"></i>WHOIS</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="mod-ssl" checked>
                    <label class="form-check-label" for="mod-ssl"><i class="fa-solid fa-lock me-1"></i>SSL</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="mod-ping">
                    <label class="form-check-label" for="mod-ping"><i class="fa-solid fa-satellite-dish me-1"></i>Ping</label>
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

            <!-- Columna izquierda (md-6) -->
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
                        <div class="skeleton-wrap">
                            <div class="skeleton-line"></div>
                            <div class="skeleton-line short"></div>
                        </div>
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
                        <div class="skeleton-wrap">
                            <div class="skeleton-line"></div>
                            <div class="skeleton-line short"></div>
                            <div class="skeleton-line"></div>
                        </div>
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
                        <div class="skeleton-wrap">
                            <div class="skeleton-line"></div>
                            <div class="skeleton-line"></div>
                            <div class="skeleton-line short"></div>
                        </div>
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
                        <div class="skeleton-wrap">
                            <div class="skeleton-line"></div>
                            <div class="skeleton-line short"></div>
                        </div>
                    </div>
                </div>

            </div><!-- /col-left -->

            <!-- Columna derecha (md-6) -->
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
                        <div class="skeleton-wrap">
                            <div class="skeleton-line"></div>
                            <div class="skeleton-line short"></div>
                            <div class="skeleton-line"></div>
                            <div class="skeleton-line short"></div>
                        </div>
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
                        <div class="skeleton-wrap">
                            <div class="skeleton-line"></div>
                            <div class="skeleton-line short"></div>
                            <div class="skeleton-line"></div>
                        </div>
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
    A:     'bg-primary',
    AAAA:  'bg-info text-dark',
    CNAME: 'bg-secondary',
    MX:    'bg-warning text-dark',
    NS:    'bg-danger',
    TXT:   'bg-success',
};

let currentDomain = '';
const exportData  = {};

// ---- Inicialización ----
document.getElementById('btn-analyze').addEventListener('click', startAnalysis);
document.getElementById('input-domain').addEventListener('keydown', e => {
    if (e.key === 'Enter') startAnalysis();
});

// ---- Análisis principal ----
function startAnalysis() {
    let raw = document.getElementById('input-domain').value.trim();
    if (!raw) return;

    // Normalizar igual que limpiarHost() en PHP
    let domain = raw
        .replace(/^https?:\/\/(www\.)?/i, '')
        .split('/')[0]
        .split('?')[0]
        .replace(/[^a-zA-Z0-9.\-]/g, '')
        .toLowerCase();

    if (!domain) return;
    currentDomain = domain;

    const optional = ['dns', 'ports', 'whois', 'ssl', 'ping'];
    const modules  = optional.filter(m => document.getElementById('mod-' + m).checked);
    const active   = ['resolution', ...modules];

    // UI: mostrar resultados, resetear estado
    setAnalyzing(true);
    document.getElementById('results').classList.remove('d-none');
    document.getElementById('export-bar').classList.remove('d-none');
    document.getElementById('analyzed-domain-label').textContent = '🔍 ' + domain;

    // Mostrar tarjetas activas, ocultar las demás
    ['resolution', 'dns', 'ports', 'whois', 'ssl', 'ping'].forEach(m => {
        const card = document.getElementById('card-' + m);
        if (active.includes(m)) {
            card.classList.remove('d-none');
            setCardLoading(m);
        } else {
            card.classList.add('d-none');
        }
    });

    // Lanzar todas las peticiones en paralelo
    const promises = active.map(m => fetchModule(m, domain));
    Promise.allSettled(promises).then(() => setAnalyzing(false));
}

function setAnalyzing(active) {
    document.getElementById('btn-text').classList.toggle('d-none', active);
    document.getElementById('btn-loading').classList.toggle('d-none', !active);
    document.getElementById('btn-analyze').disabled = active;
}

function setCardLoading(module) {
    document.getElementById('body-' + module).innerHTML = `
        <div class="skeleton-wrap">
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

// ---- Fetch módulo ----
async function fetchModule(module, domain) {
    try {
        const res  = await fetch(`api.php?module=${module}&domain=${encodeURIComponent(domain)}`);
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

// ---- Renderers ----
function renderCard(module, data) {
    switch (module) {
        case 'resolution': renderResolution(data); break;
        case 'dns':        renderDNS(data);        break;
        case 'ports':      renderPorts(data);      break;
        case 'whois':      renderWhois(data);      break;
        case 'ssl':        renderSSL(data);        break;
        case 'ping':       renderPing(data);       break;
    }
}

function renderResolution(data) {
    const ip  = data.ip      ?? 'No resolvió';
    const rev = data.reverse ?? '—';
    document.getElementById('body-resolution').innerHTML = `
        <div class="dns-row d-flex align-items-center gap-2 border-0">
            <span class="badge dns-badge bg-primary">IP</span>
            <div class="dns-value text-primary fw-bold">${esc(ip)}</div>
        </div>
        <div class="dns-row d-flex align-items-start gap-2 border-0">
            <span class="badge dns-badge bg-dark">HOST</span>
            <div class="dns-value text-secondary small">${esc(rev)}</div>
        </div>`;
}

function renderDNS(data) {
    const records = data.records ?? [];
    if (!records.length) {
        document.getElementById('body-dns').innerHTML =
            '<p class="text-muted small mb-0">No se encontraron registros DNS.</p>';
        return;
    }

    // Agrupar por tipo
    const groups = {};
    for (const r of records) {
        if (!groups[r.type]) groups[r.type] = [];
        groups[r.type].push(r);
    }

    const order = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT'];
    const types = order.filter(t => groups[t]);
    const half  = Math.ceil(types.length / 2);

    let html = '<div class="row g-0">';
    for (let col = 0; col < 2; col++) {
        const colTypes = types.slice(col * half, (col + 1) * half);
        if (!colTypes.length) break;
        const borderClass = col === 0 ? 'pe-md-2 dns-col-border' : 'ps-md-2';
        html += `<div class="col-12 col-md-6 ${borderClass}">`;
        for (const type of colTypes) {
            html += `<div class="dns-type-label">${type} Records</div>`;
            for (const r of groups[type]) {
                const badge    = DNS_COLORS[type] ?? 'bg-dark';
                const priority = r.priority != null ? `<span class="text-muted small ms-1">prio ${r.priority}</span>` : '';
                const ttl      = r.ttl      != null ? `<span class="ttl-badge">TTL ${r.ttl}s</span>` : '';
                html += `
                    <div class="dns-row d-flex align-items-start gap-2">
                        <span class="badge dns-badge ${badge}">${type}</span>
                        <div class="flex-grow-1 min-w-0">
                            <div class="dns-value">${esc(r.value)}${priority}</div>
                            ${ttl}
                        </div>
                    </div>`;
            }
        }
        html += '</div>';
    }
    html += '</div>';
    document.getElementById('body-dns').innerHTML = html;
}

function renderPorts(data) {
    let html = '';
    for (const cat of (data.categories ?? [])) {
        html += `<div class="port-group-title">${esc(cat.category)}</div>`;
        html += '<div class="row g-1 mb-1">';
        for (const p of cat.ports) {
            const openClass = p.open ? 'open' : '';
            html += `
                <div class="col-6 col-sm-4">
                    <div class="port-row">
                        <span class="port-label ${openClass}">${esc(p.label)}</span>
                        <span class="port-number ${openClass}">${p.port}</span>
                    </div>
                </div>`;
        }
        html += '</div>';
    }
    document.getElementById('body-ports').innerHTML = html;
}

function renderSSL(data) {
    const expired = data.expired;
    const warning = data.warning;
    const stClass = expired ? 'text-danger' : (warning ? 'text-warning' : 'text-success');
    const stIcon  = expired ? 'fa-circle-xmark' : (warning ? 'fa-triangle-exclamation' : 'fa-circle-check');
    const daysMsg = expired
        ? `Expirado hace ${Math.abs(data.days_left)} días`
        : `Expira en ${data.days_left} días`;

    const badge = document.getElementById('badge-ssl');
    const dl    = document.getElementById('dl-ssl');
    badge.className = 'header-badge ' + (expired ? 'bg-danger' : (warning ? 'bg-warning text-dark' : 'bg-success'));
    dl.className    = 'btn btn-link p-0 ' + stClass;

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
                <span class="ssl-val">${esc(data.valid_to)}</span>
            </div>
        </div>`;
}

function renderWhois(data) {
    document.getElementById('body-whois').innerHTML =
        `<div class="whois-scroll">${esc(data.data)}</div>`;
}

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

// ---- Exportar ----
function downloadCard(module) {
    const data = exportData[module];
    if (!data) return;
    const text = formatExport(module, data);
    downloadText(text, `${module}_${currentDomain}_${stamp()}.txt`);
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
            return `[RESOLUCIÓN]\nIP: ${data.ip ?? 'N/A'}\nHost inverso: ${data.reverse ?? 'N/A'}`;
        case 'dns':
            return `[DNS]\n` + (data.records ?? []).map(r => {
                const pri = r.priority != null ? ` (prio ${r.priority})` : '';
                const ttl = r.ttl      != null ? ` [TTL ${r.ttl}s]`      : '';
                return `${r.type.padEnd(6)} ${r.value}${pri}${ttl}`;
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

// ---- Drag & drop entre columnas (SortableJS) ----
const sortableOptions = {
    group:     'cards',
    handle:    '.drag-handle',
    animation: 150,
    ghostClass: 'sortable-ghost',
    dragClass:  'sortable-drag',
};
new Sortable(document.getElementById('col-left'),  sortableOptions);
new Sortable(document.getElementById('col-right'), sortableOptions);
</script>
</body>
</html>
