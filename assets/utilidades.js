/**
 * Check Berry - Sección Utilidades.
 * Todo aquí es cálculo 100% en el navegador (sin llamadas a api.php): son
 * conversores/generadores genéricos, no diagnósticos de dominio/IP.
 */

// ── Bases numéricas (BigInt: precisión arbitraria, no solo hasta 2^53) ──────
function digitValue(ch) {
    const c = ch.toLowerCase();
    if (c >= '0' && c <= '9') return c.charCodeAt(0) - 48;
    if (c >= 'a' && c <= 'z') return c.charCodeAt(0) - 97 + 10;
    return -1;
}
function parseBigInBase(str, base) {
    let value = 0n;
    const b = BigInt(base);
    for (const ch of str) {
        const d = digitValue(ch);
        if (d < 0 || d >= base) return null;
        value = value * b + BigInt(d);
    }
    return value;
}
function toBaseBig(value, base) {
    if (value === 0n) return '0';
    const b = BigInt(base);
    const digits = '0123456789abcdefghijklmnopqrstuvwxyz';
    let v = value, out = '';
    while (v > 0n) {
        out = digits[Number(v % b)] + out;
        v = v / b;
    }
    return out;
}
function baseConvert() {
    const raw     = document.getElementById('u-base-input').value.trim().replace(/\s+/g, '');
    const from    = parseInt(document.getElementById('u-base-from').value, 10);
    const custom  = parseInt(document.getElementById('u-base-custom').value, 10);
    const out     = document.getElementById('u-base-output');
    if (!raw) { out.innerHTML = '<span class="text-muted">Introduce un valor.</span>'; return; }
    const value = parseBigInBase(raw, from);
    if (value === null) { out.innerHTML = `<span class="text-danger">"${escapeHtml(raw)}" no es válido en base ${from}.</span>`; return; }
    const rows = [
        ['Binario (2)', toBaseBig(value, 2)],
        ['Octal (8)', toBaseBig(value, 8)],
        ['Decimal (10)', toBaseBig(value, 10)],
        ['Hexadecimal (16)', toBaseBig(value, 16).toUpperCase()],
    ];
    if (custom >= 2 && custom <= 36 && ![2, 8, 10, 16].includes(custom)) {
        rows.push([`Base ${custom}`, toBaseBig(value, custom).toUpperCase()]);
    }
    out.innerHTML = rows.map(([l, v]) =>
        `<div class="d-flex justify-content-between border-bottom py-1"><span class="text-muted small">${l}</span><code>${escapeHtml(v)}</code></div>`
    ).join('');
}

// ── Cuenta atrás hasta un evento ────────────────────────────────────────────
let eventCountdownTimer = null;
function renderEventCountdown(target) {
    const out = document.getElementById('u-event-output');
    const now = new Date();
    let diffMs = target - now;
    const past = diffMs < 0;
    diffMs = Math.abs(diffMs);
    const days  = Math.floor(diffMs / 86400000);
    const hours = Math.floor((diffMs % 86400000) / 3600000);
    const mins  = Math.floor((diffMs % 3600000) / 60000);
    const secs  = Math.floor((diffMs % 60000) / 1000);
    out.innerHTML = `<p class="mb-0">${past ? 'Han pasado' : 'Faltan'} <strong>${days}</strong>d <strong>${hours}</strong>h <strong>${mins}</strong>m <strong>${secs}</strong>s.</p>`;
}
function startEventCountdown() {
    const dtStr = document.getElementById('u-event-date').value;
    const out   = document.getElementById('u-event-output');
    if (eventCountdownTimer) { clearInterval(eventCountdownTimer); eventCountdownTimer = null; }
    if (!dtStr) { out.innerHTML = '<span class="text-muted">Elige una fecha.</span>'; return; }
    const target = new Date(dtStr);
    if (isNaN(target.getTime())) { out.innerHTML = '<span class="text-danger">Fecha no válida.</span>'; return; }
    renderEventCountdown(target);
    eventCountdownTimer = setInterval(() => renderEventCountdown(target), 1000);
}

// ── Lorem Ipsum ──────────────────────────────────────────────────────────────
const LOREM_WORDS = 'lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua enim ad minim veniam quis nostrud exercitation ullamco laboris nisi aliquip ex ea commodo consequat duis aute irure in reprehenderit voluptate velit esse cillum eu fugiat nulla pariatur excepteur sint occaecat cupidatat non proident sunt culpa qui officia deserunt mollit anim id est laborum'.split(' ');
function loremWord(i) { return LOREM_WORDS[i % LOREM_WORDS.length]; }
function capitalize(word) { return word.charAt(0).toUpperCase() + word.slice(1); }
function generateLorem() {
    const count = parseInt(document.getElementById('u-lorem-count').value, 10) || 3;
    const unit  = document.getElementById('u-lorem-unit').value;
    const out   = document.getElementById('u-lorem-output');
    if (unit === 'palabras') {
        const words = [];
        for (let i = 0; i < count; i++) words.push(loremWord(i));
        words[0] = capitalize(words[0]);
        out.value = words.join(' ') + '.';
        return;
    }
    const paras = [];
    for (let p = 0; p < count; p++) {
        const sentLen = 8 + Math.floor(Math.random() * 8);
        const words = [];
        for (let i = 0; i < sentLen; i++) words.push(loremWord(p * 37 + i));
        words[0] = capitalize(words[0]);
        paras.push(words.join(' ') + '.');
    }
    out.value = paras.join('\n\n');
}

// ── HTML → Markdown ──────────────────────────────────────────────────────────
function tableToMarkdown(table) {
    const rows = Array.from(table.querySelectorAll('tr')).map(tr =>
        Array.from(tr.children).map(td => td.textContent.trim().replace(/\|/g, '\\|'))
    );
    if (!rows.length) return '';
    const [header, ...body] = rows;
    const line = arr => '| ' + arr.join(' | ') + ' |';
    return [line(header), line(header.map(() => '---')), ...body.map(line)].join('\n');
}
function htmlNodeToMarkdown(node) {
    if (node.nodeType === Node.TEXT_NODE) return node.textContent.replace(/\s+/g, ' ');
    if (node.nodeType !== Node.ELEMENT_NODE) return '';
    const tag   = node.tagName.toLowerCase();
    const inner = Array.from(node.childNodes).map(htmlNodeToMarkdown).join('');
    switch (tag) {
        case 'h1': return `\n# ${inner.trim()}\n`;
        case 'h2': return `\n## ${inner.trim()}\n`;
        case 'h3': return `\n### ${inner.trim()}\n`;
        case 'h4': return `\n#### ${inner.trim()}\n`;
        case 'h5': return `\n##### ${inner.trim()}\n`;
        case 'h6': return `\n###### ${inner.trim()}\n`;
        case 'strong': case 'b': return `**${inner}**`;
        case 'em': case 'i': return `*${inner}*`;
        case 'code': return node.parentElement && node.parentElement.tagName.toLowerCase() === 'pre' ? inner : `\`${inner}\``;
        case 'pre': return `\n\`\`\`\n${node.textContent}\n\`\`\`\n`;
        case 'a': return `[${inner}](${node.getAttribute('href') || ''})`;
        case 'img': return `![${node.getAttribute('alt') || ''}](${node.getAttribute('src') || ''})`;
        case 'br': return '\n';
        case 'hr': return '\n---\n';
        case 'blockquote': return '\n' + inner.trim().split('\n').map(l => '> ' + l).join('\n') + '\n';
        case 'li': {
            const isOrdered = node.parentElement && node.parentElement.tagName.toLowerCase() === 'ol';
            const idx = isOrdered ? Array.from(node.parentElement.children).indexOf(node) + 1 : null;
            return `${isOrdered ? idx + '.' : '-'} ${inner.trim()}\n`;
        }
        case 'ul': case 'ol': return '\n' + inner + '\n';
        case 'p': case 'div': return `\n${inner.trim()}\n`;
        case 'table': return '\n' + tableToMarkdown(node) + '\n';
        default: return inner;
    }
}
function convertHtmlToMarkdown() {
    const html = document.getElementById('u-htmd-input').value;
    const doc  = new DOMParser().parseFromString(html, 'text/html');
    const md   = htmlNodeToMarkdown(doc.body).replace(/\n{3,}/g, '\n\n').trim();
    document.getElementById('u-htmd-output').value = md;
}

// ── JSON ↔ YAML (usa js-yaml, cargado en includes/head.php) ────────────────
function convertJsonYaml(direction) {
    const input = document.getElementById('u-jy-input').value;
    const out   = document.getElementById('u-jy-output');
    const errEl = document.getElementById('u-jy-error');
    try {
        if (direction === 'json2yaml') {
            out.value = jsyaml.dump(JSON.parse(input));
        } else {
            out.value = JSON.stringify(jsyaml.load(input), null, 2);
        }
        errEl.textContent = '';
    } catch (e) {
        out.value = '';
        errEl.textContent = 'Error: ' + e.message;
    }
}

// ── Base64 / URL / Hash ──────────────────────────────────────────────────────
function b64UrlHash(mode) {
    const input = document.getElementById('u-enc-input').value;
    const out   = document.getElementById('u-enc-output');
    try {
        switch (mode) {
            case 'b64-encode': out.value = btoa(unescape(encodeURIComponent(input))); break;
            case 'b64-decode': out.value = decodeURIComponent(escape(atob(input))); break;
            case 'url-encode': out.value = encodeURIComponent(input); break;
            case 'url-decode': out.value = decodeURIComponent(input); break;
        }
    } catch (e) {
        out.value = 'Error: entrada no válida para esta operación.';
    }
}
function bufToHex(buf) {
    return Array.from(new Uint8Array(buf)).map(b => b.toString(16).padStart(2, '0')).join('');
}
async function computeHashes() {
    const input  = document.getElementById('u-enc-input').value;
    const enc    = new TextEncoder().encode(input);
    const sha1   = await crypto.subtle.digest('SHA-1', enc);
    const sha256 = await crypto.subtle.digest('SHA-256', enc);
    document.getElementById('u-hash-md5').textContent    = (typeof md5 === 'function') ? md5(input) : 'no disponible';
    document.getElementById('u-hash-sha1').textContent   = bufToHex(sha1);
    document.getElementById('u-hash-sha256').textContent = bufToHex(sha256);
}

// ── Generador de contraseñas ──────────────────────────────────────────────────
function generatePassword() {
    const len        = Math.min(128, Math.max(4, parseInt(document.getElementById('u-pwd-len').value, 10) || 16));
    const useLower    = document.getElementById('u-pwd-lower').checked;
    const useUpper    = document.getElementById('u-pwd-upper').checked;
    const useDigits   = document.getElementById('u-pwd-digits').checked;
    const useSymbols  = document.getElementById('u-pwd-symbols').checked;
    const errEl = document.getElementById('u-pwd-error');
    const out   = document.getElementById('u-pwd-output');
    let chars = '';
    // Sin caracteres visualmente ambiguos (l/1/I/O/0) para que se puedan
    // transcribir a mano sin errores.
    if (useLower) chars += 'abcdefghijkmnopqrstuvwxyz';
    if (useUpper) chars += 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    if (useDigits) chars += '23456789';
    if (useSymbols) chars += '!@#$%^&*()-_=+[]{}';
    if (!chars) { out.value = ''; errEl.textContent = 'Elige al menos un tipo de carácter.'; return; }
    errEl.textContent = '';
    const rnd = new Uint32Array(len);
    crypto.getRandomValues(rnd);
    let pwd = '';
    for (let i = 0; i < len; i++) pwd += chars[rnd[i] % chars.length];
    out.value = pwd;
}

// ── Subredes IP (CIDR) ───────────────────────────────────────────────────────
function ipToInt(ip) {
    const parts = ip.split('.').map(Number);
    if (parts.length !== 4 || parts.some(p => isNaN(p) || p < 0 || p > 255)) return null;
    return ((parts[0] << 24) | (parts[1] << 16) | (parts[2] << 8) | parts[3]) >>> 0;
}
function intToIp(int) {
    return [(int >>> 24) & 255, (int >>> 16) & 255, (int >>> 8) & 255, int & 255].join('.');
}
function calcSubnet() {
    const input = document.getElementById('u-cidr-input').value.trim();
    const out   = document.getElementById('u-cidr-output');
    const m = input.match(/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\/(\d{1,2})$/);
    if (!m) { out.innerHTML = '<span class="text-danger">Formato esperado: 192.168.1.10/24</span>'; return; }
    const ip     = ipToInt(m[1]);
    const prefix = parseInt(m[2], 10);
    if (ip === null || prefix < 0 || prefix > 32) { out.innerHTML = '<span class="text-danger">IP o prefijo no válidos.</span>'; return; }
    const maskInt    = prefix === 0 ? 0 : (0xFFFFFFFF << (32 - prefix)) >>> 0;
    const network    = (ip & maskInt) >>> 0;
    const broadcast  = (network | (~maskInt >>> 0)) >>> 0;
    const totalHosts = Math.pow(2, 32 - prefix);
    const usableHosts = prefix >= 31 ? 0 : totalHosts - 2;
    const rows = [
        ['Dirección de red', intToIp(network)],
        ['Máscara', intToIp(maskInt)],
        ['Broadcast', intToIp(broadcast)],
        ['Primer host útil', prefix >= 31 ? '—' : intToIp(network + 1)],
        ['Último host útil', prefix >= 31 ? '—' : intToIp(broadcast - 1)],
        ['Hosts totales', totalHosts.toLocaleString('es-ES')],
        ['Hosts útiles', usableHosts.toLocaleString('es-ES')],
    ];
    out.innerHTML = rows.map(([l, v]) =>
        `<div class="d-flex justify-content-between border-bottom py-1"><span class="text-muted small">${l}</span><code>${escapeHtml(String(v))}</code></div>`
    ).join('');
}

// ── Timestamps ────────────────────────────────────────────────────────────────
function toggleTimestampMode() {
    const mode = document.getElementById('u-ts-mode').value;
    document.getElementById('u-ts-input').classList.toggle('d-none', mode !== 'to-date');
    document.getElementById('u-ts-date').classList.toggle('d-none', mode !== 'to-unix');
}
function convertTimestamp() {
    const mode = document.getElementById('u-ts-mode').value;
    const out  = document.getElementById('u-ts-output');
    if (mode === 'to-date') {
        const raw = document.getElementById('u-ts-input').value.trim();
        if (!/^\d+$/.test(raw)) { out.innerHTML = '<span class="text-danger">Introduce un timestamp Unix (segundos o ms).</span>'; return; }
        const ms = raw.length > 10 ? Number(raw) : Number(raw) * 1000;
        const d = new Date(ms);
        if (isNaN(d.getTime())) { out.innerHTML = '<span class="text-danger">Timestamp fuera de rango.</span>'; return; }
        out.innerHTML = `<div>Local: <code>${escapeHtml(d.toLocaleString('es-ES'))}</code></div><div>UTC: <code>${escapeHtml(d.toUTCString())}</code></div><div>ISO 8601: <code>${escapeHtml(d.toISOString())}</code></div>`;
    } else {
        const raw = document.getElementById('u-ts-date').value;
        const d = new Date(raw);
        if (isNaN(d.getTime())) { out.innerHTML = '<span class="text-danger">Fecha no válida.</span>'; return; }
        out.innerHTML = `<div>Unix (segundos): <code>${Math.floor(d.getTime() / 1000)}</code></div><div>Unix (ms): <code>${d.getTime()}</code></div>`;
    }
}
