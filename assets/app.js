// ============================================================
// Check Berry — Frontend (usa el global APP_VERSION inyectado
// por includes/scripts_bottom.php antes de cargar este archivo)
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
        } else if (document.getElementById('tab-dns').classList.contains('show')) {
            startDnsQuery();
        } else if (document.getElementById('tab-web').classList.contains('show')) {
            startWebAnalysis();
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

// ── Ocultar buscador en pestañas con input propio o sin analizador ──
const TABS_NO_SEARCH = new Set(['tab-dns-btn', 'tab-redes-btn', 'tab-util-btn', 'tab-herr-btn', 'tab-dev-btn']);
document.getElementById('mainTabs').addEventListener('shown.bs.tab', e => {
    const hide = TABS_NO_SEARCH.has(e.target.id);
    document.getElementById('search-card-wrap').classList.toggle('d-none', hide);
    document.body.classList.toggle('search-hidden', hide);
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

    setCardLoading('webinfo');

    const allCards = ['resolution','dns','ports','whois','ssl','ping','headers','blacklist','traceroute','redirect'];
    allCards.forEach(m => {
        const card = document.getElementById('card-' + m);
        if (!card) return;
        if (active.includes(m)) { card.classList.remove('d-none'); setCardLoading(m); }
        else card.classList.add('d-none');
    });

    Promise.allSettled([...active.map(m => fetchModule(m, domain)), fetchModule('webinfo', domain)])
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

    ['mail-score','mail-mx','mail-smtp','mail-spf','mail-dmarc','mail-dkim','mail-blacklist','mail-ipbl']
        .forEach(id => setMailCardLoading(id.replace('mail-','')));

    try {
        const emailTest = (document.getElementById('input-email-test')?.value ?? '').trim();
        let url = `api.php?module=mailtest&domain=${encodeURIComponent(domain)}`;
        if (emailTest) url += `&email=${encodeURIComponent(emailTest)}`;
        const blUrl = `api.php?module=blacklist&domain=${encodeURIComponent(domain)}`;
        const [res, blRes] = await Promise.all([fetch(url), fetch(blUrl)]);
        const [data, blData] = await Promise.all([res.json(), blRes.json()]);
        mailExport['mailtest'] = data;
        mailExport['blacklist_ip'] = blData;
        if (data.success) renderMailResults(data);
        else {
            ['score','mx','smtp','spf','dmarc','dkim','blacklist'].forEach(k =>
                setMailCardError(k, data.error ?? 'Error desconocido'));
        }
        if (blData.success) renderMailIpbl(blData);
        else setMailCardError('ipbl', blData.error ?? 'Error desconocido');
    } catch(e) {
        ['score','mx','smtp','spf','dmarc','dkim','blacklist','ipbl'].forEach(k =>
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
    const items = d.score_items ?? [];
    const rows = items.map(item =>
        `<div class="d-flex align-items-center gap-2 mb-1">
            <i class="fa-solid ${item.ok ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger'} small"></i>
            <span class="small ${item.ok ? '' : 'text-muted'}">${esc(item.label)}</span>
            ${item.weight > 1 ? `<span class="ttl-badge ms-1">×${item.weight}</span>` : ''}
         </div>`
    ).join('');
    // RCPT TO result
    let rcptHtml = '';
    if (d.rcpt_to) {
        const r = d.rcpt_to;
        const ic = r.result === 'exists' ? 'fa-circle-check text-success' : (r.result === 'not_exists' ? 'fa-circle-xmark text-danger' : 'fa-circle-question text-warning');
        const lbl = r.result === 'exists' ? 'Buzón existe' : (r.result === 'not_exists' ? 'Buzón no existe' : 'No determinado');
        rcptHtml = `<div class="mt-2 pt-2 border-top">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fa-solid ${ic} small"></i>
                <span class="small fw-semibold">RCPT TO: ${lbl}</span>
                ${r.code ? `<span class="ttl-badge">${r.code}</span>` : ''}
            </div>
            ${r.note ? `<div class="small text-muted">${esc(r.note)}</div>` : ''}
        </div>`;
    }
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
                ${d.arsys ? '<span class="arsys-badge mt-1 d-inline-block"><i class="fa-solid fa-server me-1"></i>ARSYS</span>' : ''}
            </div>
        </div>
        <div class="row g-0">${rows}</div>${rcptHtml}`;
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
        const tlsIcon = s.open && s.starttls
            ? `<span class="ttl-badge ms-1 text-success"><i class="fa-solid fa-lock me-1"></i>${s.port===465?'SSL/TLS':'STARTTLS'}</span>`
            : (s.open ? `<span class="ttl-badge ms-1 text-warning">Sin TLS</span>` : '');
        const caps = (s.capabilities ?? []).filter(c => c !== 'SSL/TLS implícito').slice(0, 6);
        const capsHtml = caps.length ? `<div class="mt-1">${caps.map(c => `<span class="ttl-badge me-1">${esc(c)}</span>`).join('')}</div>` : '';
        return `<div class="d-flex align-items-start gap-2 mb-2">
            <i class="fa-solid ${icon} ${cls} mt-1 small"></i>
            <div>
                <span class="fw-semibold small">${s.port} ${esc(s.label)}</span>
                ${s.open ? `<span class="ttl-badge ms-1">${s.ms} ms</span>` : ''}${tlsIcon}
                ${s.banner ? `<div class="dns-value small text-muted">${esc(s.banner)}</div>` : ''}
                ${capsHtml}
            </div>
        </div>`;
    }).join('');
    document.getElementById('body-mail-smtp').innerHTML = rows || '<p class="text-muted small mb-0">Sin datos SMTP.</p>';
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

// ── Blacklist IP directa (módulo diagnóstico reutilizado en Correo) ──────────
function renderMailIpbl(data) {
    const badge = document.getElementById('badge-mail-ipbl');
    const dl    = document.getElementById('dl-mail-ipbl');
    if (badge) badge.className = 'header-badge ' + (data.clean ? 'bg-success' : 'bg-danger');
    if (dl)    dl.className    = 'btn btn-link p-0 ' + (data.clean ? 'text-success' : 'text-danger');
    const rows = (data.results ?? []).map(r =>
        `<div class="bl-row d-flex align-items-center gap-2">
            <i class="fa-solid ${r.listed ? 'fa-circle-xmark text-danger' : 'fa-circle-check text-success'} small"></i>
            <span class="bl-name">${esc(r.name)}</span>
            ${r.rcode ? `<span class="ttl-badge ms-auto">${esc(r.rcode)}</span>` : ''}
         </div>`
    ).join('');
    const el = document.getElementById('body-mail-ipbl');
    if (!el) return;
    el.innerHTML = `
        <div class="d-flex align-items-center mb-2">
            <i class="fa-solid ${data.clean ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger'} fa-lg me-2"></i>
            <span class="small fw-semibold ${data.clean ? 'text-success' : 'text-danger'}">
                ${data.clean ? `IP limpia (${data.total} listas)` : `Listada en ${data.listed}/${data.total}`}
            </span>
        </div>
        <small class="text-muted d-block mb-2">IP: <code>${esc(data.ip)}</code></small>
        <details><summary class="small text-muted" style="cursor:pointer">Ver detalle</summary>
            <div class="mt-2">${rows}</div>
        </details>`;
}

// ── AbuseIPDB ─────────────────────────────────────────────────
let abuseExportData = null;

async function startAbuseCheck() {
    const domain = normalizeDomain();
    if (!domain) { alert('Introduce un dominio o IP en el buscador'); return; }
    document.getElementById('abuse-btn-text').classList.add('d-none');
    document.getElementById('abuse-btn-loading').classList.remove('d-none');
    document.getElementById('btn-abuse-check').disabled = true;
    document.getElementById('abuse-results').classList.remove('d-none');
    document.getElementById('body-mail-abuse').innerHTML =
        `<div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>`;
    try {
        const res  = await fetch(`api.php?module=abuseipdb&domain=${encodeURIComponent(domain)}`);
        const data = await res.json();
        abuseExportData = data;
        if (data.success) renderMailAbuse(data);
        else document.getElementById('body-mail-abuse').innerHTML =
            `<div class="alert alert-danger py-2 mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(data.error ?? 'Error')}</div>`;
    } catch(e) {
        document.getElementById('body-mail-abuse').innerHTML =
            `<div class="alert alert-danger py-2 mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>Error: ${esc(e.message)}</div>`;
    } finally {
        document.getElementById('abuse-btn-text').classList.remove('d-none');
        document.getElementById('abuse-btn-loading').classList.add('d-none');
        document.getElementById('btn-abuse-check').disabled = false;
    }
}

function renderMailAbuse(data) {
    const score  = data.abuseConfidenceScore ?? 0;
    const cls    = score === 0 ? 'text-success' : (score < 50 ? 'text-warning' : 'text-danger');
    const icon   = score === 0 ? 'fa-circle-check' : (score < 50 ? 'fa-triangle-exclamation' : 'fa-circle-xmark');
    const bCls   = score === 0 ? 'bg-success' : (score < 50 ? 'bg-warning text-dark' : 'bg-danger');
    const badge  = document.getElementById('badge-mail-abuse');
    if (badge) badge.className = 'header-badge ' + bCls;
    document.getElementById('body-mail-abuse').innerHTML = `
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="mail-score-circle ${score === 0 ? 'score-success' : score < 50 ? 'score-warning' : 'score-danger'}">
                <span class="score-num">${score}</span>
                <span class="score-den">/100</span>
            </div>
            <div>
                <div class="fw-bold ${cls}">
                    <i class="fa-solid ${icon} me-1"></i>
                    ${score === 0 ? 'Sin reportes de abuso' : score < 50 ? 'Actividad sospechosa' : 'IP con alto riesgo'}
                </div>
                <div class="small text-muted mt-1">AbuseIPDB Confidence Score</div>
                ${data.isWhitelisted ? '<span class="ttl-badge text-success">En whitelist</span>' : ''}
            </div>
        </div>
        <div class="row g-2">
            <div class="col-6"><div class="ssl-field">
                <span class="ssl-label">IP</span>
                <span class="ssl-val">${esc(data.ip)}</span>
            </div></div>
            <div class="col-6"><div class="ssl-field">
                <span class="ssl-label">Total reportes</span>
                <span class="ssl-val">${esc(data.totalReports)}</span>
            </div></div>
            <div class="col-6"><div class="ssl-field">
                <span class="ssl-label">Usuarios distintos</span>
                <span class="ssl-val">${esc(data.numDistinctUsers ?? '—')}</span>
            </div></div>
            <div class="col-6"><div class="ssl-field">
                <span class="ssl-label">País</span>
                <span class="ssl-val">${esc(data.countryCode ?? '—')}</span>
            </div></div>
            <div class="col-6"><div class="ssl-field">
                <span class="ssl-label">ISP</span>
                <span class="ssl-val" style="font-size:0.72rem">${esc(data.isp ?? '—')}</span>
            </div></div>
            <div class="col-6"><div class="ssl-field">
                <span class="ssl-label">Tipo de uso</span>
                <span class="ssl-val" style="font-size:0.72rem">${esc(data.usageType ?? '—')}</span>
            </div></div>
            <div class="col-12"><div class="ssl-field">
                <span class="ssl-label">Último reporte</span>
                <span class="ssl-val">${data.lastReportedAt ? esc(data.lastReportedAt.split('T')[0]) : 'Sin reportes'}</span>
            </div></div>
        </div>`;
}

function downloadAbuseReport() {
    if (!abuseExportData) return;
    const d = abuseExportData;
    const text = `[ABUSEIPDB — Correo/Abuse]\nIP: ${d.ip}\nScore: ${d.abuseConfidenceScore}/100\nReportes: ${d.totalReports}\nPaís: ${d.countryCode??'—'}\nISP: ${d.isp??'—'}\nÚltimo reporte: ${d.lastReportedAt??'—'}`;
    downloadText(text, `abuseipdb_${currentDomain}_${stamp()}.txt`);
}

// ── Fetch módulo diagnóstico ──────────────────────────────────
async function fetchModule(module, domain) {
    try {
        let url = `api.php?module=${module}&domain=${encodeURIComponent(domain)}`;
        if (module === 'dns') {
            const types = getSelectedDnsTypes();
            if (types.length) url += `&types=${encodeURIComponent(types.join(','))}`;
        }
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000); // 30s timeout
        const res = await fetch(url, { signal: controller.signal });
        clearTimeout(timeoutId);

        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        exportData[module] = data;
        data.success ? renderCard(module, data) : setCardError(module, data.error ?? 'Error desconocido');
    } catch(e) {
        const msg = e.name === 'AbortError' ? 'Timeout (>15s)' : e.message;
        setCardError(module, `Error: ${msg}`);
    }
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
      traceroute:renderTraceroute,redirect:renderRedirect,webinfo:renderWebInfo})[module]?.(data);
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
    let text = `REPORTE CHECK BERRY v${APP_VERSION}\nDominio: ${currentDomain}\nFecha: ${new Date().toLocaleString('es-ES')}\n${'='.repeat(50)}\n\n`;
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
        case 'webinfo':    return `[WEB INFO]\nScreenshot: ${data.screenshot_url??''}\nWayback: ${data.wayback?.first_date??'?'} → ${data.wayback?.last_date??'?'}\nTranco: #${data.tranco?.rank??'N/A'}`;
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

// ── Web Info renderer ─────────────────────────────────────────
function renderWebInfo(data) {
    const ss = data.screenshot_url;
    const wb = data.wayback ?? {};
    const tr = data.tranco ?? {};
    const ms = data.response_ms ?? null;

    let ssHtml = '';
    if (ss) ssHtml = `<a href="${esc(ss)}" target="_blank" rel="noopener">
        <img src="${esc(ss)}" alt="Captura web" class="webinfo-screenshot img-fluid rounded mb-3"
             onerror="this.closest('.webinfo-screenshot-wrap').classList.add('d-none')">
        </a>`;

    const stats = [];
    if (ms !== null) stats.push(['<i class="fa-solid fa-bolt me-1"></i>Respuesta', ms + ' ms']);
    if (wb.first_date) stats.push(['<i class="fa-solid fa-calendar me-1"></i>Primera vez en Wayback', esc(wb.first_date)]);
    if (wb.last_date)  stats.push(['<i class="fa-solid fa-history me-1"></i>Última captura Wayback', esc(wb.last_date)]);
    if (wb.snapshots)  stats.push(['<i class="fa-solid fa-camera me-1"></i>Capturas archivadas', esc(wb.snapshots)]);
    if (tr.rank)       stats.push(['<i class="fa-solid fa-trophy me-1"></i>Tranco rank', '#' + esc(tr.rank)]);

    const statsHtml = stats.length
        ? `<div class="webinfo-stats">${stats.map(([k,v]) =>
            `<div class="webinfo-stat"><div class="webinfo-stat-label">${k}</div><div class="webinfo-stat-val">${v}</div></div>`
          ).join('')}</div>`
        : '';

    const notes = data.notes ?? [];
    const notesHtml = notes.length
        ? `<div class="mt-2">${notes.map(n => `<div class="small text-muted"><i class="fa-solid fa-circle-info me-1"></i>${esc(n)}</div>`).join('')}</div>`
        : '';

    document.getElementById('body-webinfo').innerHTML =
        `<div class="webinfo-screenshot-wrap">${ssHtml}</div>${statsHtml}${notesHtml}`;
}

// ── DNS Query tab ─────────────────────────────────────────────
let lastDnsQueryData = null;

function toggleCustomDns() {
    const sel = document.getElementById('dnsq-server').value;
    document.getElementById('dnsq-custom-wrap').style.display = sel === 'custom' ? '' : 'none';
}

async function startDnsQuery() {
    const rawDomain = (document.getElementById('dnsq-domain').value.trim() || normalizeDomain());
    const domain = rawDomain.replace(/^https?:\/\/(www\.)?/i,'').split('/')[0].split('?')[0].toLowerCase();
    if (!domain) { alert('Introduce un dominio'); return; }
    const type   = document.getElementById('dnsq-type').value;
    const selVal = document.getElementById('dnsq-server').value;
    const server = selVal === 'custom' ? document.getElementById('dnsq-custom').value.trim() : selVal;
    const port   = parseInt(document.getElementById('dnsq-port').value) || 53;

    document.getElementById('dnsq-btn-text').classList.add('d-none');
    document.getElementById('dnsq-btn-loading').classList.remove('d-none');
    document.getElementById('dnsq-results').classList.remove('d-none');
    document.getElementById('body-dnsq').innerHTML =
        `<div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>`;

    try {
        const url = `api.php?module=dnsquery&domain=${encodeURIComponent(domain)}&type=${encodeURIComponent(type)}&server=${encodeURIComponent(server)}&port=${port}`;
        const res  = await fetch(url);
        const data = await res.json();
        lastDnsQueryData = data;
        renderDnsQuery(data, domain, type, server, port);
    } catch(e) {
        document.getElementById('body-dnsq').innerHTML =
            `<div class="alert alert-danger py-2 mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>Error: ${esc(e.message)}</div>`;
    } finally {
        document.getElementById('dnsq-btn-text').classList.remove('d-none');
        document.getElementById('dnsq-btn-loading').classList.add('d-none');
    }
}

function renderDnsQuery(data, domain, type, server, port) {
    if (!data.success) {
        document.getElementById('body-dnsq').innerHTML =
            `<div class="alert alert-danger py-2 mb-0 small">${esc(data.error ?? 'Error desconocido')}</div>`;
        return;
    }
    const meta = `${esc(domain)} ${esc(type)} @ ${esc(server)}${port !== 53 ? ':'+port : ''}`;
    document.getElementById('dnsq-meta').textContent = meta;

    const records = data.records ?? [];
    let recHtml = '';
    if (records.length) {
        recHtml = records.map(r => {
            const bc = DNS_COLORS[r.type] ?? 'bg-dark';
            return `<div class="dns-row d-flex align-items-start gap-2">
                <span class="badge dns-badge ${bc}">${esc(r.type)}</span>
                <div class="flex-grow-1 min-w-0">
                    <div class="dns-value">${esc(r.value)}</div>
                    ${r.ttl != null ? `<span class="ttl-badge">TTL ${r.ttl}s</span>` : ''}
                </div>
            </div>`;
        }).join('');
    } else {
        recHtml = '<p class="text-muted small mb-0">Sin respuesta (NXDOMAIN o tipo no encontrado).</p>';
    }

    const metaInfo = [];
    if (data.query_time_ms != null) metaInfo.push(`<span class="ttl-badge">Tiempo: ${data.query_time_ms} ms</span>`);
    if (data.status)               metaInfo.push(`<span class="ttl-badge">Status: ${esc(data.status)}</span>`);
    if (data.server_used)          metaInfo.push(`<span class="ttl-badge">Servidor: ${esc(data.server_used)}</span>`);
    if (data.source)               metaInfo.push(`<span class="ttl-badge">Fuente: ${esc(data.source)}</span>`);

    const rawHtml = data.raw_output
        ? `<details class="mt-3"><summary class="small text-muted" style="cursor:pointer"><i class="fa-solid fa-terminal me-1"></i>Salida raw dig</summary>
            <pre class="terminal mt-2">${esc(data.raw_output)}</pre></details>`
        : '';

    document.getElementById('body-dnsq').innerHTML =
        `<div class="mb-2">${metaInfo.join(' ')}</div>${recHtml}${rawHtml}`;
}

function downloadDnsQuery() {
    if (!lastDnsQueryData) return;
    const lines = [`[DNS QUERY]\n`];
    (lastDnsQueryData.records ?? []).forEach(r => lines.push(`${r.type.padEnd(8)} ${r.value}`));
    if (lastDnsQueryData.raw_output) lines.push('\n--- RAW ---\n' + lastDnsQueryData.raw_output);
    downloadText(lines.join('\n'), `dnsquery_${stamp()}.txt`);
}

// ── EML upload & render ───────────────────────────────────────
let emlData = null;

async function uploadEml(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('eml-filename').textContent = file.name;
    document.getElementById('eml-results').classList.remove('d-none');
    document.getElementById('body-eml').innerHTML =
        `<div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>`;

    try {
        const fd = new FormData();
        fd.append('eml', file);
        const res  = await fetch('emlparse.php', {method:'POST', body: fd});
        const data = await res.json();
        emlData = data;
        if (data.success) renderEmlHeaders(data);
        else document.getElementById('body-eml').innerHTML =
            `<div class="alert alert-danger py-2 mb-0 small">${esc(data.error ?? 'Error')}</div>`;
    } catch(e) {
        document.getElementById('body-eml').innerHTML =
            `<div class="alert alert-danger py-2 mb-0 small">Error: ${esc(e.message)}</div>`;
    }
    input.value = '';
}

function renderEmlHeaders(data) {
    // Summary row
    const authBadge = (result, label) => {
        if (!result) return '';
        const cls = result === 'pass' ? 'bg-success' : (result === 'fail' ? 'bg-danger' : 'bg-warning text-dark');
        return `<span class="badge ${cls} me-1">${label}: ${esc(result)}</span>`;
    };
    let summaryHtml = `
        <div class="eml-summary mb-3">
            ${data.subject  ? `<div class="mb-1"><span class="eml-field-label">Asunto:</span> <span class="fw-semibold">${esc(data.subject)}</span></div>` : ''}
            ${data.from     ? `<div class="mb-1"><span class="eml-field-label">De:</span> ${esc(data.from)}</div>` : ''}
            ${data.to       ? `<div class="mb-1"><span class="eml-field-label">Para:</span> ${esc(data.to)}</div>` : ''}
            ${data.date     ? `<div class="mb-1"><span class="eml-field-label">Fecha:</span> ${esc(data.date)}</div>` : ''}
            <div class="mt-2">
                ${authBadge(data.auth?.spf,  'SPF')}
                ${authBadge(data.auth?.dkim, 'DKIM')}
                ${authBadge(data.auth?.dmarc,'DMARC')}
                ${data.spam_score != null ? `<span class="ttl-badge">Spam score: ${data.spam_score}</span>` : ''}
                <span class="ttl-badge ms-1">${data.total_headers} cabeceras</span>
                <span class="ttl-badge ms-1">${data.hops} saltos</span>
            </div>
        </div>`;

    // Received chain
    let chainHtml = '';
    if ((data.received ?? []).length) {
        chainHtml = `<div class="mb-3">
            <div class="dns-type-label">Ruta del mensaje (${data.hops} saltos)</div>
            ${data.received.map((r, i) =>
                `<div class="rd-step mb-1">
                    <span class="rd-code text-muted">${i+1}</span>
                    <span class="rd-url">${esc(r)}</span>
                </div>`
            ).join('<div class="rd-arrow">↓</div>')}
        </div>`;
    }

    // Group headers by category
    const cats = {};
    for (const h of (data.headers ?? [])) {
        if (!cats[h.category]) cats[h.category] = [];
        cats[h.category].push(h);
    }
    const catOrder = ['Autenticación','Origen','Destino','Spam','Fecha','Identificación','Hilo','Formato','Software','Red','Lista','Otras'];
    const catColors = {
        'Autenticación':'#1d4ed8','Origen':'#7c3aed','Destino':'#0891b2','Spam':'#dc2626',
        'Fecha':'#0f766e','Identificación':'#374151','Hilo':'#92400e','Formato':'#065f46',
        'Software':'#be185d','Red':'#0369a1','Lista':'#6d28d9','Otras':'#64748b',
    };
    let headersHtml = '<div class="eml-headers-grid">';
    for (const cat of catOrder) {
        if (!cats[cat]) continue;
        const color = catColors[cat] ?? '#64748b';
        headersHtml += `<div class="eml-cat-block">
            <div class="eml-cat-title" style="color:${color}">${esc(cat)}</div>`;
        for (const h of cats[cat]) {
            headersHtml += `<div class="eml-header-row">
                <div class="eml-header-name">${esc(h.name)}</div>
                <div class="eml-header-value dns-value">${esc(h.value)}</div>
                ${h.desc ? `<div class="eml-header-desc">${esc(h.desc)}</div>` : ''}
            </div>`;
        }
        headersHtml += '</div>';
    }
    headersHtml += '</div>';

    document.getElementById('body-eml').innerHTML = summaryHtml + chainHtml + headersHtml;
}

function downloadEmlReport() {
    if (!emlData) return;
    let text = `ANÁLISIS EML\nAsunto: ${emlData.subject ?? ''}\nDe: ${emlData.from ?? ''}\nPara: ${emlData.to ?? ''}\nFecha: ${emlData.date ?? ''}\n\n`;
    text += `SPF: ${emlData.auth?.spf ?? '—'}  DKIM: ${emlData.auth?.dkim ?? '—'}  DMARC: ${emlData.auth?.dmarc ?? '—'}\n\n`;
    text += `CABECERAS (${emlData.total_headers}):\n`;
    (emlData.headers ?? []).forEach(h => { text += `${h.name}: ${h.value}\n`; });
    downloadText(text, `eml_report_${stamp()}.txt`);
}

// ── SPF Validator ─────────────────────────────────────────────
let lastSpfData = null;

async function startSpfCheck() {
    const domain = (document.getElementById('spf-domain').value.trim() || normalizeDomain());
    const ip     = document.getElementById('spf-ip').value.trim();
    if (!domain) { alert('Introduce un dominio'); return; }
    if (!ip)     { alert('Introduce una IP'); return; }

    document.getElementById('spf-btn-text').classList.add('d-none');
    document.getElementById('spf-btn-loading').classList.remove('d-none');
    document.getElementById('spf-results').classList.remove('d-none');
    document.getElementById('spf-results').scrollIntoView({behavior:'smooth', block:'start'});
    document.getElementById('body-spf').innerHTML = skeletonHtml();
    document.getElementById('spf-meta').textContent = '';

    try {
        const res  = await fetch(`api.php?module=spfcheck&domain=${encodeURIComponent(domain)}&ip=${encodeURIComponent(ip)}`);
        const data = await res.json();
        lastSpfData = data;
        data.success ? renderSpfCheck(data) : setBodyErr('spf', data.error);
    } catch(e) {
        setBodyErr('spf', e.message);
    } finally {
        document.getElementById('spf-btn-text').classList.remove('d-none');
        document.getElementById('spf-btn-loading').classList.add('d-none');
    }
}

function renderSpfCheck(d) {
    document.getElementById('spf-meta').textContent = `${d.domain} · ${d.ip}`;

    const RESULT_CFG = {
        pass:      { icon: 'fa-circle-check',       cls: 'text-success', label: 'PASS',      desc: 'La IP está autorizada para enviar correo en nombre de este dominio.' },
        fail:      { icon: 'fa-circle-xmark',        cls: 'text-danger',  label: 'FAIL',      desc: 'La IP NO está autorizada. El correo debe ser rechazado.' },
        softfail:  { icon: 'fa-triangle-exclamation',cls: 'text-warning', label: 'SOFTFAIL',  desc: 'La IP no está autorizada pero el dominio prefiere no rechazar (solo marcar como sospechoso).' },
        neutral:   { icon: 'fa-circle-question',     cls: 'text-muted',   label: 'NEUTRAL',   desc: 'El dominio no hace ninguna afirmación sobre esta IP.' },
        none:      { icon: 'fa-circle-minus',         cls: 'text-muted',   label: 'NONE',      desc: 'No existe ningún registro SPF para este dominio.' },
        permerror: { icon: 'fa-triangle-exclamation', cls: 'text-danger',  label: 'PERMERROR', desc: 'Error permanente evaluando el SPF (demasiados lookups, recursión o registro mal formado).' },
        temperror: { icon: 'fa-circle-exclamation',   cls: 'text-warning', label: 'TEMPERROR', desc: 'Error temporal durante la evaluación (problema DNS transitorio).' },
    };

    const cfg = RESULT_CFG[d.result] ?? RESULT_CFG.neutral;

    // SPF raw record
    const spfHtml = d.spf_raw
        ? `<div class="spf-raw-block mt-3"><div class="seo-label">Registro SPF</div><code class="small">${esc(d.spf_raw)}</code></div>`
        : `<div class="small text-muted mt-2"><i class="fa-solid fa-circle-info me-1"></i>No se encontró registro SPF para <strong>${esc(d.domain)}</strong>.</div>`;

    // Trace table
    let traceHtml = '';
    if ((d.trace ?? []).length) {
        const rows = d.trace.map(t => {
            if (t.mechanism) {
                const matchIcon = t.matched
                    ? '<i class="fa-solid fa-circle-check text-success small me-1"></i>'
                    : '<i class="fa-solid fa-circle-xmark text-muted small me-1"></i>';
                const qual = t.qualifier ? `<span class="ttl-badge ms-1">${{'+':'pass','-':'fail','~':'softfail','?':'neutral'}[t.qualifier] ?? t.qualifier}</span>` : '';
                const note = t.note ? `<span class="small text-muted ms-2">(${esc(t.note)})</span>` : '';
                const sub  = t.sub_result ? `<span class="ttl-badge ms-1">${esc(t.sub_result)}</span>` : '';
                const ptr  = t.ptr ? `<span class="small text-muted ms-2">PTR: ${esc(t.ptr)}</span>` : '';
                return `<tr class="${t.matched ? 'spf-trace-match' : ''}">
                    <td>${matchIcon}<code class="small">${esc(t.mechanism)}</code>${note}${ptr}</td>
                    <td>${qual}${sub}</td>
                </tr>`;
            }
            if (t.domain) {
                return `<tr class="spf-trace-domain">
                    <td colspan="2"><i class="fa-solid fa-arrow-right me-1 text-muted small"></i><strong>${esc(t.domain)}</strong>
                    ${t.spf ? `<code class="small text-muted ms-2">${esc(t.spf.slice(0, 80))}${t.spf.length > 80 ? '…' : ''}</code>` : '<span class="small text-muted ms-2">sin SPF</span>'}
                    </td>
                </tr>`;
            }
            return '';
        }).join('');
        traceHtml = `
            <div class="mt-3 pt-2 border-top">
                <div class="seo-label mb-2">Evaluación de mecanismos <span class="ttl-badge ms-1">${d.lookups} lookup${d.lookups !== 1 ? 's' : ''} DNS</span></div>
                <div class="table-responsive">
                    <table class="spf-trace-table w-100"><tbody>${rows}</tbody></table>
                </div>
            </div>`;
    }

    document.getElementById('body-spf').innerHTML = `
        <div class="d-flex align-items-start gap-3 mb-2">
            <i class="fa-solid ${cfg.icon} ${cfg.cls} fa-2x mt-1"></i>
            <div>
                <div class="fw-bold fs-5 ${cfg.cls}">${cfg.label}</div>
                <div class="small mt-1">${cfg.desc}</div>
                <div class="small text-muted mt-1">
                    <span class="ttl-badge me-1">${esc(d.ip_type)}</span>
                    Mecanismo coincidente: <code>${esc(d.matched ?? '—')}</code>
                </div>
            </div>
        </div>
        ${spfHtml}
        ${traceHtml}`;
}

function downloadSpfCheck() {
    if (!lastSpfData) return;
    const d = lastSpfData;
    let t = `[SPF CHECK] ${d.domain} · IP: ${d.ip}\nResultado: ${d.result.toUpperCase()}\nCoincidencia: ${d.matched}\nLookups DNS: ${d.lookups}\n\nRegistro SPF:\n${d.spf_raw ?? '(ninguno)'}\n\nEvaluación:\n`;
    (d.trace ?? []).forEach(e => {
        if (e.mechanism) t += `  ${e.matched ? '✓' : '✗'} ${e.mechanism}\n`;
        if (e.domain)    t += `→ ${e.domain}: ${e.spf ?? 'sin SPF'}\n`;
    });
    downloadText(t, `spf_check_${d.domain}_${stamp()}.txt`);
}

// ── Red & IP tab ─────────────────────────────────────────────
let lastPropData = null;

async function startGeoIp() {
    const input = (document.getElementById('red-input').value.trim() || normalizeDomain());
    if (!input) { alert('Introduce una IP o dominio'); return; }
    document.getElementById('geo-btn-text').classList.add('d-none');
    document.getElementById('geo-btn-loading').classList.remove('d-none');
    document.getElementById('red-results').classList.remove('d-none');
    document.getElementById('body-geoip').innerHTML   = skeletonHtml();
    document.getElementById('body-whoisip').innerHTML = skeletonHtml();
    document.getElementById('red-results').scrollIntoView({behavior:'smooth', block:'start'});
    try {
        const enc = encodeURIComponent(input);
        const [geoRes, whoisRes] = await Promise.allSettled([
            fetch(`api.php?module=geoip&domain=${enc}`).then(r => r.json()),
            fetch(`api.php?module=whois&domain=${enc}`).then(r => r.json()),
        ]);
        const geo = geoRes.status === 'fulfilled' ? geoRes.value : null;
        const wh  = whoisRes.status === 'fulfilled' ? whoisRes.value : null;
        geo ? (geo.success ? renderGeoIp(geo) : setBodyErr('geoip', geo.error))
            : setBodyErr('geoip', 'Error');
        wh  ? (wh.success  ? document.getElementById('body-whoisip').innerHTML = `<div class="whois-scroll">${esc(wh.data)}</div>`
                           : setBodyErr('whoisip', wh.error))
            : setBodyErr('whoisip', 'Error');
    } catch(e) {
        setBodyErr('geoip', e.message); setBodyErr('whoisip', e.message);
    } finally {
        document.getElementById('geo-btn-text').classList.remove('d-none');
        document.getElementById('geo-btn-loading').classList.add('d-none');
    }
}

function setBodyErr(id, msg) {
    const el = document.getElementById('body-' + id);
    if (el) el.innerHTML = `<div class="alert alert-danger py-2 mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(msg ?? 'Error')}</div>`;
}
function skeletonHtml() {
    return `<div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>`;
}

function renderGeoIp(d) {
    const flag  = d.country_code ? `<img src="https://flagcdn.com/24x18/${d.country_code.toLowerCase()}.png" alt="${esc(d.country_code)}" class="me-1" style="vertical-align:middle">` : '';
    const badge = (icon, val, sub) => val ? `<div class="geoip-row"><i class="fa-solid ${icon} me-2 text-muted"></i><div><div class="geoip-val">${esc(val)}</div>${sub ? `<div class="geoip-sub">${esc(sub)}</div>` : ''}</div></div>` : '';
    const tags  = [];
    if (d.is_proxy)   tags.push(`<span class="badge bg-warning text-dark me-1">Proxy/VPN</span>`);
    if (d.is_hosting) tags.push(`<span class="badge bg-secondary me-1">Hosting/DC</span>`);
    if (d.is_mobile)  tags.push(`<span class="badge bg-info text-dark me-1">Móvil</span>`);
    document.getElementById('body-geoip').innerHTML = `
        <div class="geoip-header mb-2">
            ${flag}<span class="fw-bold">${esc(d.ip)}</span>
            ${d.ptr ? `<span class="ttl-badge ms-2">${esc(d.ptr)}</span>` : ''}
        </div>
        ${tags.join('')}
        ${badge('fa-location-dot',   d.country, (d.city ? d.city + ', ' + (d.region ?? '') : null))}
        ${badge('fa-clock',          d.timezone, null)}
        ${badge('fa-building',       d.org, d.asn)}
        ${badge('fa-wifi',           d.isp, null)}
        ${d.lat != null ? `<div class="mt-2"><a href="https://www.openstreetmap.org/?mlat=${d.lat}&mlon=${d.lon}&zoom=10" target="_blank" rel="noopener" class="small text-muted"><i class="fa-solid fa-map-location-dot me-1"></i>${d.lat}, ${d.lon}</a></div>` : ''}
        ${d.geo_error ? `<div class="small text-muted mt-2"><i class="fa-solid fa-circle-info me-1"></i>${esc(d.geo_error)}</div>` : ''}`;
}

async function startPropagation() {
    const domain = normalizeDomain();
    if (!domain) { alert('Introduce un dominio en el buscador'); return; }
    const type = document.getElementById('prop-type').value;
    document.getElementById('prop-btn-text').classList.add('d-none');
    document.getElementById('prop-btn-loading').classList.remove('d-none');
    document.getElementById('prop-results').classList.remove('d-none');
    document.getElementById('prop-results').scrollIntoView({behavior:'smooth', block:'start'});
    document.getElementById('body-propagation').innerHTML = skeletonHtml();
    try {
        const res  = await fetch(`api.php?module=dnspropagation&domain=${encodeURIComponent(domain)}&type=${type}`);
        const data = await res.json();
        lastPropData = data;
        data.success ? renderPropagation(data) : (document.getElementById('body-propagation').innerHTML =
            `<div class="alert alert-danger py-2 mb-0 small">${esc(data.error)}</div>`);
    } catch(e) {
        document.getElementById('body-propagation').innerHTML = `<div class="alert alert-danger py-2 mb-0 small">${esc(e.message)}</div>`;
    } finally {
        document.getElementById('prop-btn-text').classList.remove('d-none');
        document.getElementById('prop-btn-loading').classList.add('d-none');
    }
}

function renderPropagation(d) {
    document.getElementById('prop-meta').textContent = `${d.domain} ${d.type} — ${d.unique} valor${d.unique !== 1 ? 'es distintos' : ' único'}`;
    const consIcon = d.consistent
        ? '<i class="fa-solid fa-circle-check text-success me-1"></i><span class="text-success fw-semibold">Propagado correctamente</span>'
        : '<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i><span class="text-warning fw-semibold">Resultados inconsistentes</span>';
    const rows = (d.results ?? []).map(r => {
        const ok = r.status === 'NOERROR';
        const to = r.status === 'TIMEOUT';
        const rowClass = to ? 'text-muted' : (ok ? '' : 'text-danger');
        const vals = r.records.length ? r.records.map(v => `<span class="prop-val">${esc(v)}</span>`).join(' ') : `<span class="text-muted small">${r.status}</span>`;
        return `<tr class="${rowClass}">
            <td class="prop-server-name">${r.flag} ${esc(r.name)}</td>
            <td class="prop-server-ip text-muted">${esc(r.server)}</td>
            <td>${vals}</td>
            <td class="text-end"><span class="ttl-badge">${r.ms} ms</span></td>
        </tr>`;
    }).join('');
    document.getElementById('body-propagation').innerHTML = `
        <div class="mb-2">${consIcon}</div>
        <div class="table-responsive"><table class="prop-table w-100"><thead>
            <tr><th>Servidor</th><th>IP</th><th>Respuesta</th><th></th></tr>
        </thead><tbody>${rows}</tbody></table></div>`;
}

function downloadPropagation() {
    if (!lastPropData) return;
    let t = `[DNS PROPAGACIÓN] ${lastPropData.domain} ${lastPropData.type}\n`;
    (lastPropData.results ?? []).forEach(r => {
        t += `${r.name} (${r.server}): ${r.records.join(', ') || r.status} — ${r.ms}ms\n`;
    });
    downloadText(t, `propagation_${lastPropData.domain}_${stamp()}.txt`);
}

// ── Web tab ───────────────────────────────────────────────────
async function startWebAnalysis() {
    const domain = normalizeDomain();
    if (!domain) { alert('Introduce un dominio en el buscador'); return; }
    document.getElementById('web-btn-text').classList.add('d-none');
    document.getElementById('web-btn-loading').classList.remove('d-none');
    document.getElementById('btn-web-analyze').disabled = true;
    document.getElementById('web-results').classList.remove('d-none');
    document.getElementById('body-seo').innerHTML  = skeletonHtml();
    document.getElementById('body-tech').innerHTML = skeletonHtml();
    try {
        const res  = await fetch(`api.php?module=seocheck&domain=${encodeURIComponent(domain)}`);
        const data = await res.json();
        if (data.success) { renderSeo(data); renderTech(data); }
        else { setBodyErr('seo', data.error); setBodyErr('tech', data.error); }
    } catch(e) { setBodyErr('seo', e.message); setBodyErr('tech', e.message); }
    finally {
        document.getElementById('web-btn-text').classList.remove('d-none');
        document.getElementById('web-btn-loading').classList.add('d-none');
        document.getElementById('btn-web-analyze').disabled = false;
    }
}

function renderSeo(d) {
    const s = d.seo ?? {};
    const chk = (ok, label, hint) => {
        const ic = ok === true ? 'fa-circle-check text-success' : (ok === false ? 'fa-circle-xmark text-danger' : 'fa-circle-question text-muted');
        return `<div class="hdr-row d-flex align-items-start gap-2">
            <i class="fa-solid ${ic} mt-1 small"></i>
            <div><div class="hdr-label">${label}</div>${hint ? `<div class="hdr-desc">${esc(hint)}</div>` : ''}</div>
        </div>`;
    };
    const field = (label, val, cls) => val
        ? `<div class="seo-field mb-2"><div class="seo-label">${label}</div><div class="seo-val ${cls ?? ''}">${esc(val)}</div></div>` : '';

    const statusRow = d.status_code ? `<span class="ttl-badge me-1">HTTP ${d.status_code}</span>` : '';
    const timeRow   = d.response_ms ? `<span class="ttl-badge me-1">${d.response_ms} ms</span>` : '';

    const ogHtml = (s.og_title || s.og_image || s.og_description) ? `
        <div class="mt-3 pt-2 border-top">
            <div class="dns-type-label">Open Graph</div>
            ${s.og_image ? `<img src="${esc(s.og_image)}" alt="OG image" class="img-fluid rounded mb-2" style="max-height:100px;object-fit:cover">` : ''}
            ${field('og:title', s.og_title)}
            ${field('og:description', s.og_description)}
            ${field('og:type', s.og_type)}
            ${field('og:site_name', s.og_site_name)}
        </div>` : '';

    const twHtml = s.tw_card ? `
        <div class="mt-3 pt-2 border-top">
            <div class="dns-type-label">Twitter Card</div>
            ${field('twitter:card', s.tw_card)}
            ${field('twitter:title', s.tw_title)}
            ${field('twitter:site', s.tw_site)}
        </div>` : '';

    document.getElementById('body-seo').innerHTML = `
        <div class="mb-2">${statusRow}${timeRow}</div>
        ${field('Título', s.title, s.title_ok ? 'text-success' : (s.title ? 'text-warning' : 'text-danger'))}
        ${s.title_len != null ? `<div class="seo-hint">${s.title_len} caracteres ${s.title_ok ? '✓ ideal 30-60' : '(ideal 30-60)'}</div>` : ''}
        ${field('Descripción', s.description, s.desc_ok ? 'text-success' : (s.description ? 'text-warning' : 'text-danger'))}
        ${s.desc_len  != null ? `<div class="seo-hint">${s.desc_len} caracteres ${s.desc_ok ? '✓ ideal 70-160' : '(ideal 70-160)'}</div>` : ''}
        ${field('Canonical', s.canonical)}
        ${field('Robots', s.robots)}
        ${s.h1_count != null ? `<div class="seo-field mb-2"><div class="seo-label">H1 <span class="ttl-badge ms-1">${s.h1_count}</span></div>${s.h1?.slice(0,2).map(h => `<div class="seo-val">${esc(h)}</div>`).join('') ?? ''}</div>` : ''}
        <div class="mt-2">
            ${chk(!!s.title,       'Título presente',        !s.title ? 'El título es crítico para SEO' : null)}
            ${chk(!!s.description,'Meta descripción',        !s.description ? 'Aumenta el CTR en buscadores' : null)}
            ${chk(!!s.canonical,  'URL canonical definida',  !s.canonical ? 'Evita contenido duplicado' : null)}
            ${chk(!!s.og_title,   'Open Graph configurado',  !s.og_title ? 'Mejora la presentación en redes sociales' : null)}
            ${chk(!!s.tw_card,    'Twitter Card configurado', !s.tw_card ? 'Mejora el preview en Twitter/X' : null)}
            ${chk(s.h1_count === 1,'H1 único',               s.h1_count !== 1 ? `Se encontraron ${s.h1_count ?? 0} H1 (se recomienda uno solo)` : null)}
        </div>
        ${ogHtml}${twHtml}`;
}

function renderTech(d) {
    if (!(d.tech ?? []).length) {
        document.getElementById('body-tech').innerHTML = '<p class="text-muted small mb-0">No se detectaron tecnologías conocidas.</p>';
        return;
    }
    const cats = {};
    for (const t of d.tech) {
        if (!cats[t.cat]) cats[t.cat] = [];
        cats[t.cat].push(t);
    }
    const catIcons = {
        'Servidor web':'fa-server','CMS':'fa-pencil','eCommerce':'fa-cart-shopping',
        'Constructor':'fa-wand-magic-sparkles','CSS Framework':'fa-palette',
        'JS':'fa-code','JS Framework':'fa-code','Runtime':'fa-microchip',
        'Framework':'fa-layer-group','Analytics':'fa-chart-bar',
        'CDN/Seguridad':'fa-shield-halved','Seguridad':'fa-shield-halved','UI':'fa-star',
    };
    let html = '';
    for (const [cat, items] of Object.entries(cats)) {
        const icon = catIcons[cat] ?? 'fa-puzzle-piece';
        html += `<div class="mb-3">
            <div class="tech-cat-label"><i class="fa-solid ${icon} me-1"></i>${esc(cat)}</div>
            <div class="tech-badges">${items.map(t =>
                `<span class="tech-badge">${esc(t.name)}${t.version ? ` <small>${esc(t.version)}</small>` : ''}</span>`
            ).join('')}</div>
        </div>`;
    }
    document.getElementById('body-tech').innerHTML = `
        <div class="mb-2 small text-muted">${d.tech_count} tecnolog${d.tech_count === 1 ? 'ía detectada' : 'ías detectadas'}</div>${html}`;
}

// ── SSL/TLS tab ───────────────────────────────────────────────
async function startSslScan() {
    const domain = normalizeDomain();
    if (!domain) { alert('Introduce un dominio en el buscador'); return; }
    document.getElementById('ssl-btn-text').classList.add('d-none');
    document.getElementById('ssl-btn-loading').classList.remove('d-none');
    document.getElementById('btn-ssl-scan').disabled = true;
    document.getElementById('ssl-results').classList.remove('d-none');
    ['ssl-protocols','ssl-cipher','ssl-chain','ssl-san'].forEach(id =>
        document.getElementById('body-' + id).innerHTML = skeletonHtml());
    try {
        const res  = await fetch(`api.php?module=sslscan&domain=${encodeURIComponent(domain)}`);
        const data = await res.json();
        if (data.success) renderSslScan(data);
        else ['ssl-protocols','ssl-cipher','ssl-chain','ssl-san'].forEach(id => setBodyErr(id, data.error));
    } catch(e) {
        ['ssl-protocols','ssl-cipher','ssl-chain','ssl-san'].forEach(id => setBodyErr(id, e.message));
    } finally {
        document.getElementById('ssl-btn-text').classList.remove('d-none');
        document.getElementById('ssl-btn-loading').classList.add('d-none');
        document.getElementById('btn-ssl-scan').disabled = false;
    }
}

function renderSslScan(d) {
    // Protocols
    const protos = (d.protocols ?? []).map(p => {
        const ok = p.supported === true;
        const warn = p.supported === true && !p.secure;
        const ic = ok ? (warn ? 'fa-triangle-exclamation text-warning' : 'fa-circle-check text-success') : 'fa-circle-xmark text-muted';
        return `<div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid ${ic}"></i>
            <span class="fw-semibold small">${esc(p.version)}</span>
            ${warn ? '<span class="ttl-badge text-warning">Obsoleto</span>' : ''}
            ${p.supported === null ? '<span class="ttl-badge text-muted">No determinado</span>' : ''}
        </div>`;
    }).join('') || '<p class="text-muted small mb-0">No se pudo detectar.</p>';
    document.getElementById('body-ssl-protocols').innerHTML = protos;

    // Cipher & Security
    const fsIcon = d.forward_secrecy ? '<i class="fa-solid fa-circle-check text-success me-1"></i>' : '<i class="fa-solid fa-circle-xmark text-danger me-1"></i>';
    const hstsIcon = d.hsts ? '<i class="fa-solid fa-circle-check text-success me-1"></i>' : '<i class="fa-solid fa-circle-xmark text-danger me-1"></i>';
    document.getElementById('body-ssl-cipher').innerHTML = `
        ${d.cipher ? `<div class="mb-2"><div class="seo-label">Cipher Suite</div><code class="small">${esc(d.cipher)}</code></div>` : ''}
        ${d.negotiated ? `<div class="mb-2"><div class="seo-label">Protocolo negociado</div><span class="fw-semibold">${esc(d.negotiated)}</span></div>` : ''}
        ${d.key_bits != null ? `<div class="mb-2"><div class="seo-label">Clave pública</div><span class="fw-semibold">${d.key_bits} bits</span></div>` : ''}
        <div class="mt-2">
            <div class="d-flex align-items-center gap-2 mb-1">${fsIcon}<span class="small">Forward Secrecy ${d.forward_secrecy ? '(ECDHE/DHE)' : '— no configurado'}</span></div>
            <div class="d-flex align-items-center gap-2 mb-1">${hstsIcon}<span class="small">HSTS ${d.hsts ? 'activado' : '— no configurado'}</span></div>
            ${d.hsts_header ? `<div class="dns-value small text-muted mt-1">${esc(d.hsts_header)}</div>` : ''}
        </div>`;

    // Chain
    const chain = (d.chain ?? []).map((c, i) => {
        const expire = c.days_left != null ? (c.days_left < 30 ? 'text-danger' : (c.days_left < 60 ? 'text-warning' : 'text-muted')) : 'text-muted';
        return `<div class="ssl-chain-entry ${c.is_ca ? 'ssl-chain-ca' : 'ssl-chain-leaf'}">
            <div class="ssl-chain-idx">${i + 1}</div>
            <div class="flex-grow-1 min-w-0">
                <div class="fw-semibold small">${esc(c.subject)}</div>
                <div class="small text-muted">Emisor: ${esc(c.issuer)}</div>
                <div class="small ${expire}">${esc(c.not_before)} → ${esc(c.not_after)}${c.days_left != null ? ` (${c.days_left}d)` : ''}</div>
                ${c.fingerprint ? `<div class="seo-hint">${c.fingerprint.slice(0, 29)}…</div>` : ''}
            </div>
        </div>`;
    }).join('<div class="ssl-chain-arrow">↓</div>');
    document.getElementById('body-ssl-chain').innerHTML = chain || '<p class="text-muted small mb-0">No se pudo obtener la cadena.</p>';

    // SAN
    const sans = d.san ?? [];
    document.getElementById('body-ssl-san').innerHTML = sans.length
        ? `<div class="san-grid">${sans.map(s => `<span class="san-entry">${esc(s)}</span>`).join('')}</div>`
        : '<p class="text-muted small mb-0">Sin dominios alternativos (SAN).</p>';
}

// ── SMTP Relay tab ─────────────────────────────────────────────
async function startRelayTest() {
    const domain = normalizeDomain();
    if (!domain) { alert('Introduce un dominio en el buscador'); return; }
    const email = (document.getElementById('input-email-test')?.value ?? '').trim();
    document.getElementById('relay-btn-text').classList.add('d-none');
    document.getElementById('relay-btn-loading').classList.remove('d-none');
    document.getElementById('btn-relay-test').disabled = true;
    document.getElementById('relay-results').classList.remove('d-none');
    document.getElementById('relay-results').scrollIntoView({behavior:'smooth', block:'start'});
    ['relay-openrelay','relay-delivery'].forEach(id =>
        document.getElementById('body-' + id).innerHTML = skeletonHtml());
    try {
        let url = `api.php?module=smtprelay&domain=${encodeURIComponent(domain)}`;
        if (email) url += `&email=${encodeURIComponent(email)}`;
        const res  = await fetch(url);
        const data = await res.json();
        data.success ? renderSmtpRelay(data)
            : ['relay-openrelay','relay-delivery'].forEach(id => setBodyErr(id, data.error));
    } catch(e) {
        ['relay-openrelay','relay-delivery'].forEach(id => setBodyErr(id, e.message));
    } finally {
        document.getElementById('relay-btn-text').classList.remove('d-none');
        document.getElementById('relay-btn-loading').classList.add('d-none');
        document.getElementById('btn-relay-test').disabled = false;
    }
}

function renderSmtpDialog(log) {
    return (log ?? []).map(e => {
        if (e.dir === 'ERROR') return `<div class="smtp-line smtp-error"><i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(e.msg)}</div>`;
        const dir  = e.dir === '>>>' ? 'smtp-send' : 'smtp-recv';
        const code = e.code ? `<span class="ttl-badge ms-1">${e.code}</span>` : '';
        const ok   = e.code >= 200 && e.code < 400;
        const cls  = e.code >= 500 ? 'smtp-fail' : (e.code >= 400 ? 'smtp-defer' : '');
        return `<div class="smtp-line ${dir} ${cls}"><span class="smtp-dir">${e.dir}</span> ${esc(e.msg)}${code}</div>`;
    }).join('');
}

function renderSmtpRelay(d) {
    const relayOk   = !d.open_relay;
    const relayIcon = relayOk ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger';
    const relayText = d.open_relay ? '¡RELAY ABIERTO! El servidor puede ser utilizado para enviar spam.' : 'El servidor no acepta relay abierto. Configuración correcta.';
    document.getElementById('body-relay-openrelay').innerHTML = `
        <div class="d-flex align-items-start gap-2 mb-3">
            <i class="fa-solid ${relayIcon} fa-lg mt-1"></i>
            <div>
                <div class="fw-bold ${d.open_relay ? 'text-danger' : 'text-success'}">${relayText}</div>
                <div class="small text-muted mt-1">MX: ${esc(d.mx_host)}${d.mx_ip ? ` (${esc(d.mx_ip)})` : ''}</div>
            </div>
        </div>
        <details><summary class="small text-muted" style="cursor:pointer">Ver diálogo SMTP</summary>
            <div class="smtp-dialog mt-2">${renderSmtpDialog(d.relay_log)}</div>
        </details>`;

    const resMap = {accepted:'Aceptado ✓', rejected:'Rechazado', deferred:'Diferido', unknown:'No determinado'};
    const resClass = {accepted:'text-success', rejected:'text-danger', deferred:'text-warning', unknown:'text-muted'};
    const r = d.delivery_result ?? 'unknown';
    document.getElementById('body-relay-delivery').innerHTML = `
        <div class="mb-2">
            <div class="seo-label">Cuenta probada</div>
            <code class="small">${esc(d.test_email)}</code>
        </div>
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid ${r === 'accepted' ? 'fa-circle-check' : (r === 'rejected' ? 'fa-circle-xmark' : 'fa-circle-question')} ${resClass[r]}"></i>
            <span class="fw-semibold ${resClass[r]}">${resMap[r]}</span>
            <span class="ttl-badge">${d.delivery_code}</span>
        </div>
        <p class="small text-muted">Resultado orientativo — los servidores con catch-all siempre responden 250.</p>
        <details><summary class="small text-muted" style="cursor:pointer">Ver diálogo SMTP</summary>
            <div class="smtp-dialog mt-2">${renderSmtpDialog(d.delivery_log)}</div>
        </details>`;
}

// ── Info Popovers ─────────────────────────────────────────────
const INFO_CONTENT = {
    // Tabs
    'tab-diagnostico': '<strong>Diagnóstico completo</strong><br>Analiza resolución DNS, puertos TCP, certificado SSL, ping, cabeceras HTTP de seguridad, listas negras (blacklist), traceroute y cadena de redirecciones de un dominio o IP.',
    'tab-correo':      '<strong>Diagnóstico de correo</strong><br>Comprueba registros MX, conectividad SMTP, autenticación SPF, DKIM y DMARC, presencia en blacklists, puntuación de entregabilidad y permite analizar archivos .eml.',
    'tab-dns':         '<strong>DNS</strong><br>Consultas DNS personalizadas de cualquier tipo (A, AAAA, MX, TXT, SOA…) contra el servidor DNS de tu elección, y comprobación de propagación mundial del mismo registro desde 10 resolvers distintos.',
    'tab-redes':       '<strong>Redes</strong><br>Geolocaliza cualquier IP o dominio: país, ciudad, ASN, proveedor, organización y coordenadas. Identifica también proxies, VPNs y datacenters.',
    'tab-web':         '<strong>Web</strong><br>Extrae metadatos SEO (título, descripción, canonical, robots), Open Graph, Twitter Card y tecnologías detectadas. Incluye además un escáner SSL/TLS extendido: protocolos, cipher suite, cadena de certificados y dominios SAN.',
    'tab-herramientas':'<strong>Smart Check</strong><br>Introduce un correo, dominio o IP y elige qué comprobar (spam, reputación, SSL, caducidad...). Ejecuta los módulos reales correspondientes y usa Gemini solo para resumir esos datos ya verificados, nunca para inventarlos.',

    // Diagnóstico modules
    'mod-resolution':  '<strong>Resolución DNS</strong><br>Muestra la IP principal del dominio (registro A/AAAA) y su PTR (DNS inverso). Es el punto de partida de cualquier diagnóstico de conectividad.',
    'mod-ssl':         '<strong>SSL / TLS</strong><br>Verifica la validez del certificado HTTPS: emisor, fechas de validez, días restantes, nombre común y Subject Alternative Names (SAN).',
    'mod-ports':       '<strong>Puertos TCP</strong><br>Comprueba la accesibilidad de los puertos más comunes: HTTP (80), HTTPS (443), SMTP (25/587/465), FTP (21), SSH (22), RDP (3389) y otros.',
    'mod-ping':        '<strong>Ping</strong><br>Mide la latencia y disponibilidad del servidor mediante ICMP. Indica si el host responde y cuánto tiempo tarda en responder en milisegundos.',
    'mod-headers':     '<strong>Cabeceras HTTP</strong><br>Analiza las cabeceras de seguridad HTTP: Content-Security-Policy, X-Frame-Options, HSTS, Referrer-Policy, Permissions-Policy y otras cabeceras relevantes.',
    'mod-blacklist':   '<strong>Blacklist</strong><br>Comprueba si la IP del servidor está listada en más de 20 listas negras (DNSBL) utilizadas por los servidores de correo para filtrar spam.',
    'mod-traceroute':  '<strong>Traceroute</strong><br>Muestra la ruta que siguen los paquetes de red desde el servidor hasta el destino, con cada salto (hop), su IP y latencia. Útil para detectar cuellos de botella.',
    'mod-redirect':    '<strong>Redirecciones HTTP</strong><br>Sigue la cadena de redirecciones (301, 302…) hasta la URL final. Detecta bucles y muestra el código de estado de cada paso.',
    'mod-webinfo':     '<strong>Web Info</strong><br>Captura visual de la web, historial en Wayback Machine, tiempo de respuesta HTTP, popularidad Tranco y tecnologías detectadas en las cabeceras del servidor.',
    'mod-dns':         '<strong>Registros DNS</strong><br>Lista todos los registros DNS configurados: A, AAAA, CNAME, MX, NS, TXT, SPF, DMARC, DKIM, CAA, SOA, BIMI y más. Esencial para verificar la configuración DNS.',
    'mod-whois':       '<strong>WHOIS</strong><br>Información de registro del dominio: titular, registrador, fechas de creación y expiración, servidores de nombres y datos de contacto según la base de datos WHOIS.',

    // Mail modules
    'mod-mail-score':    '<strong>Puntuación de entregabilidad</strong><br>Evalúa la configuración de correo y genera una puntuación del 0 al 10. Tiene en cuenta SPF, DKIM, DMARC, blacklist, PTR del MX y conectividad SMTP.',
    'mod-mail-mx':       '<strong>Registros MX</strong><br>Lista los servidores de correo configurados para el dominio, con su prioridad, IP y verificación PTR. Un MX bien configurado es esencial para recibir correo.',
    'mod-mail-smtp':     '<strong>Conectividad SMTP</strong><br>Prueba la conexión a los puertos SMTP habituales (25, 587, 465). Verifica si soportan STARTTLS/SSL y muestra el banner del servidor.',
    'mod-mail-blacklist':'<strong>Blacklist del servidor MX</strong><br>Comprueba si la IP del servidor de correo aparece en listas negras anti-spam. Una IP en blacklist puede hacer que el correo sea rechazado o marcado como spam.',
    'mod-mail-spf':      '<strong>SPF (Sender Policy Framework)</strong><br>Verifica el registro SPF del dominio que autoriza qué servidores pueden enviar correo en su nombre. Sin SPF, el correo puede llegar a spam.',
    'mod-mail-dmarc':    '<strong>DMARC</strong><br>Comprueba la política DMARC (Domain-based Message Authentication). Define qué hacer con correos que fallan SPF/DKIM y permite recibir reportes de autenticación.',
    'mod-mail-dkim':     '<strong>DKIM (DomainKeys Identified Mail)</strong><br>Busca registros DKIM en los selectores más comunes. DKIM firma digitalmente el correo para garantizar que no ha sido modificado en tránsito.',
    'mod-eml':           '<strong>Análisis de archivo .eml</strong><br>Analiza las cabeceras de un correo electrónico: autenticación SPF/DKIM/DMARC, ruta de servidores (Received), puntuación anti-spam, codificación y todos los metadatos. Sin envío a servicios externos.',

    // Relay
    'mod-relay-open':     '<strong>Test Open Relay</strong><br>Verifica si el servidor SMTP acepta relay abierto, es decir, si permite enviar correo desde un dominio externo hacia otro externo sin autenticación. Un relay abierto es una vulnerabilidad grave usada para enviar spam.',
    'mod-relay-delivery': '<strong>Simulación de entrega SMTP</strong><br>Simula el envío de un correo a la cuenta indicada realizando una conversación SMTP completa hasta el comando RCPT TO. Permite detectar si el buzón existe o si el servidor rechaza el correo.',

    // Red & IP
    'mod-geoip':      '<strong>Geolocalización IP & ASN</strong><br>Determina el país, ciudad, región, zona horaria, proveedor (ISP), número de sistema autónomo (ASN) y organización de una IP o dominio. Identifica también proxies, VPNs y datacenters.',
    'mod-whoisip':    '<strong>WHOIS de IP</strong><br>Consulta los datos de registro del bloque IP en los registros regionales de Internet (RIR): RIPE, ARIN, LACNIC, APNIC. Muestra el propietario y rango asignado.',
    'mod-propagation':'<strong>Propagación DNS mundial</strong><br>Consulta el mismo registro DNS desde 10 servidores resolver distribuidos globalmente (Google, Cloudflare, Quad9, Yandex…). Permite detectar si un cambio DNS se ha propagado correctamente en todo el mundo.',

    // Web tab
    'mod-seo':  '<strong>SEO & Metadatos</strong><br>Extrae y evalúa el título, meta descripción, URL canonical, robots, etiquetas H1, Open Graph y Twitter Card. Analiza longitudes óptimas y detecta elementos SEO críticos ausentes.',
    'mod-tech': '<strong>Detección de tecnologías</strong><br>Identifica el CMS (WordPress, Drupal…), frameworks JS (React, Vue, Next.js…), plataformas eCommerce (Shopify, WooCommerce…), herramientas analytics, librerías CSS y más, a partir de las cabeceras y el HTML.',

    // SSL tab
    'mod-ssl-protocols': '<strong>Protocolos TLS soportados</strong><br>Prueba qué versiones de TLS acepta el servidor: TLS 1.0 (obsoleto), 1.1 (obsoleto), 1.2 (recomendado) y 1.3 (óptimo). TLS 1.0 y 1.1 deben estar deshabilitados.',
    'mod-ssl-cipher':    '<strong>Cipher Suite & Seguridad</strong><br>Muestra el algoritmo de cifrado negociado, el protocolo activo, el tamaño de la clave pública, si se usa Forward Secrecy (ECDHE/DHE) y si HSTS está activado.',
    'mod-ssl-chain':     '<strong>Cadena de certificados</strong><br>Lista todos los certificados de la cadena de confianza: certificado del servidor, intermedios y raíz. Muestra fechas de validez, emisor, días restantes y fingerprint SHA-256.',
    'mod-ssl-san':       '<strong>SAN — Subject Alternative Names</strong><br>Relación de dominios y subdominios que cubre el certificado SSL/TLS. Permite ver si el certificado es wildcard o multi-dominio.',
    'mod-spfcheck':      '<strong>Validador SPF</strong><br>Evalúa si una IP concreta está autorizada por el registro SPF del dominio para enviar correo en su nombre. Implementa RFC 7208: mecanismos ip4, ip6, a, mx, include, redirect, exists y ptr. Resultado: <em>pass</em> (autorizada), <em>fail</em> (rechazada), <em>softfail</em> (sospechosa) o <em>neutral</em>.',
};

// ── Theme Switching ───────────────────────────────────────────
function switchTheme(themeName, event) {
    if (event) event.preventDefault();

    const root = document.documentElement;
    const themes = ['default', 'luminous', 'neon-tokyo'];

    // Remove all theme classes
    themes.forEach(t => root.classList.remove(`theme-${t}`));

    // Add selected theme class
    if (themeName !== 'default') {
        root.classList.add(`theme-${themeName}`);
    }

    // Save to localStorage
    localStorage.setItem('cuakcom-theme', themeName);

    // Update checkmarks (solo dentro del menú de temas: #theme-menu también
    // conviven otros .dropdown-item de Bootstrap sin icono de check, p.ej.
    // el menú "Mi cuenta", y tocar su .style rompía el resto del script)
    document.querySelectorAll('#theme-menu .dropdown-item').forEach(item => {
        const theme = item.dataset.theme;
        const checkIcon = item.querySelector('.fa-check');
        if (!checkIcon) return;
        checkIcon.style.visibility = (theme === themeName) ? 'visible' : 'hidden';
    });
}

function initializeTheme() {
    const savedTheme = localStorage.getItem('cuakcom-theme') || 'default';
    switchTheme(savedTheme);
}

// Initialize theme on page load. Envuelto en try/catch: un fallo aquí no
// debe impedir que se ejecute el resto del script (event listeners,
// Smart Check...) más abajo en este mismo archivo.
function safeInitializeTheme() {
    try { initializeTheme(); } catch (e) { console.error('initializeTheme:', e); }
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', safeInitializeTheme);
} else {
    safeInitializeTheme();
}

document.addEventListener('DOMContentLoaded', () => {
    // Initialize checkmarks visibility
    const savedTheme = localStorage.getItem('cuakcom-theme') || 'default';
    document.querySelectorAll('#theme-menu .dropdown-item').forEach(item => {
        const theme = item.dataset.theme;
        const checkIcon = item.querySelector('.fa-check');
        if (!checkIcon) return;
        checkIcon.style.visibility = (theme === savedTheme) ? 'visible' : 'hidden';
    });

    document.querySelectorAll('.info-popover-btn').forEach(el => {
        const key     = el.dataset.infoKey;
        const content = INFO_CONTENT[key] ?? 'Información no disponible.';
        const pop = new bootstrap.Popover(el, {
            content,
            html:      true,
            trigger:   'click',
            placement: 'auto',
            container: 'body',
        });
        // Click outside to close
        el.addEventListener('click', e => {
            e.stopPropagation();
            document.querySelectorAll('.info-popover-btn').forEach(o => {
                if (o !== el) bootstrap.Popover.getInstance(o)?.hide();
            });
        });
    });
    document.addEventListener('click', () => {
        document.querySelectorAll('.info-popover-btn').forEach(el => {
            bootstrap.Popover.getInstance(el)?.hide();
        });
    });
});

// ── SortableJS ────────────────────────────────────────────────
const sortOpts = {group:'cards', handle:'.drag-handle', animation:150, ghostClass:'sortable-ghost', dragClass:'sortable-drag'};
new Sortable(document.getElementById('col-left'),       sortOpts);
new Sortable(document.getElementById('col-right'),      sortOpts);
new Sortable(document.getElementById('col-mail-left'),  sortOpts);
new Sortable(document.getElementById('col-mail-right'), sortOpts);

// ── Smart Check (Herramientas) ─────────────────────────────────
function toggleCustomModel() {
    const sel = document.getElementById('sc-model').value;
    document.getElementById('sc-model-custom-wrap').classList.toggle('d-none', sel !== 'custom');
}

async function startSmartCheck() {
    const summary = document.getElementById('sc-summary');
    const secEl   = document.getElementById('sc-sections');
    const btnText = document.getElementById('sc-btn-text');
    const btnLoad = document.getElementById('sc-btn-loading');
    const btn     = document.getElementById('sc-btn');

    const modelUsedEl = document.getElementById('sc-model-used');

    try {
        const query      = document.getElementById('sc-query').value.trim();
        const checkType  = document.getElementById('sc-type').value;
        const question   = document.getElementById('sc-question').value.trim();
        const modelSel   = document.getElementById('sc-model').value;
        const model      = modelSel === 'custom'
            ? document.getElementById('sc-model-custom').value.trim()
            : modelSel;

        document.getElementById('sc-results').classList.remove('d-none');

        if (!query && !question) {
            summary.innerHTML = '<span class="text-danger">Escribe un correo, dominio o IP, o al menos una pregunta.</span>';
            secEl.innerHTML = '';
            modelUsedEl.textContent = '';
            return;
        }

        btnText.classList.add('d-none');
        btnLoad.classList.remove('d-none');
        btn.disabled = true;
        summary.innerHTML = '<div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>';
        secEl.innerHTML = '';
        modelUsedEl.textContent = '';

        const body = new URLSearchParams({query, check_type: checkType, question, model});
        const res  = await fetch('smart_check.php', {method: 'POST', body});
        const data = await res.json();

        if (!data.success) {
            summary.innerHTML = `<span class="text-danger">${escapeHtml(data.error ?? 'Error desconocido')}</span>`;
            return;
        }

        if (data.model) modelUsedEl.textContent = data.model;

        let html = '';
        if (data.summary) {
            html += `<p class="mb-0">${escapeHtml(data.summary).replace(/\n/g, '<br>')}</p>`;
        } else if (data.ai_error) {
            html += `<p class="mb-0 text-muted small"><i class="fa-solid fa-triangle-exclamation me-1"></i>${escapeHtml(data.ai_error)}</p>`;
        } else {
            html += '<span class="text-muted">Sin resumen.</span>';
        }
        summary.innerHTML = html;

        (data.sections || []).forEach(s => {
            const a = document.createElement('a');
            a.href = s.url;
            a.className = 'btn btn-sm btn-outline-primary';
            a.innerHTML = `<i class="fa-solid fa-arrow-right me-1"></i>${escapeHtml(s.label)}`;
            secEl.appendChild(a);
        });
    } catch (e) {
        summary.innerHTML = `<span class="text-danger">Error inesperado: ${escapeHtml(e.message)}</span>`;
    } finally {
        btnText.classList.remove('d-none');
        btnLoad.classList.add('d-none');
        btn.disabled = false;
    }
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}

// Enlace de vuelta desde el Smart Check: ?tab=<seccion>&q=<consulta>
(function handleSmartCheckDeepLink() {
    const params = new URLSearchParams(window.location.search);
    const tabKey = params.get('tab');
    const q      = params.get('q');
    if (!tabKey) return;

    const btnId = (typeof MENU_BTN_IDS !== 'undefined') ? MENU_BTN_IDS[tabKey] : null;
    const btn   = btnId ? document.getElementById(btnId) : null;
    if (btn && window.bootstrap?.Tab) {
        new bootstrap.Tab(btn).show();
    }
    if (q) {
        const input = document.getElementById('input-domain');
        if (input) input.value = q;
    }
})();

// ── Footer: datos del visitante ───────────────────────────────
// VISITOR_SERVER lo inyecta includes/scripts_bottom.php antes de este script.
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
