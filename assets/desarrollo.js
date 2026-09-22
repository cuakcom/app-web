/**
 * Check Berry - Sección Desarrollo.
 * Todo cálculo local salvo el generador de regex por IA, que llama a
 * regex_generate.php (mismo patrón que Smart Check: nunca genera nada sin
 * pasar por un paso que lo valida antes de mostrarlo).
 */

// ── Generador de regex por descripción (IA) ─────────────────────────────────
async function generateRegexAI() {
    const input = document.getElementById('d-airx-input').value.trim();
    const out   = document.getElementById('d-airx-output');
    const btn   = document.getElementById('d-airx-btn');
    if (!input) { out.innerHTML = '<span class="text-muted">Describe qué quieres capturar.</span>'; return; }
    btn.disabled = true;
    document.getElementById('d-airx-btn-text').classList.add('d-none');
    document.getElementById('d-airx-btn-loading').classList.remove('d-none');
    try {
        const resp = await fetch('regex_generate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'description=' + encodeURIComponent(input),
        });
        const data = await resp.json();
        if (!data.success) {
            out.innerHTML = `<p class="mb-0 text-muted small"><i class="fa-solid fa-triangle-exclamation me-1"></i>${escapeHtml(data.error)}</p>`;
            return;
        }
        out.innerHTML = `
            <div class="tool-mono border-bottom py-1"><code>/${escapeHtml(data.regex)}/${escapeHtml(data.flags)}</code></div>
            <p class="small text-muted mt-1 mb-0">${escapeHtml(data.explicacion)}</p>
        `;
        // Lo pasamos directo al tester para que se pueda probar sin retipear.
        const rxPattern = document.getElementById('d-rx-pattern');
        const rxFlags   = document.getElementById('d-rx-flags');
        if (rxPattern && rxFlags) {
            rxPattern.value = data.regex;
            rxFlags.value   = data.flags;
            testRegex();
        }
    } catch (e) {
        out.innerHTML = '<span class="text-danger">Error de red al pedir la sugerencia a Gemini.</span>';
    } finally {
        btn.disabled = false;
        document.getElementById('d-airx-btn-text').classList.remove('d-none');
        document.getElementById('d-airx-btn-loading').classList.add('d-none');
    }
}

// ── Tester de regex ─────────────────────────────────────────────────────────
function testRegex() {
    const pattern = document.getElementById('d-rx-pattern').value;
    const flags   = document.getElementById('d-rx-flags').value.replace(/[^a-z]/gi, '');
    const text    = document.getElementById('d-rx-text').value;
    const out     = document.getElementById('d-rx-output');
    if (!pattern) { out.innerHTML = '<span class="text-muted">Escribe un patrón.</span>'; return; }
    let re;
    try {
        re = new RegExp(pattern, flags.includes('g') ? flags : flags + 'g');
    } catch (e) {
        out.innerHTML = `<span class="text-danger">Patrón no válido: ${escapeHtml(e.message)}</span>`;
        return;
    }
    if (!text) { out.innerHTML = '<span class="text-muted">Escribe un texto de prueba.</span>'; return; }
    let match, count = 0, lastIndex = 0, highlighted = '';
    while ((match = re.exec(text)) !== null) {
        highlighted += escapeHtml(text.slice(lastIndex, match.index));
        highlighted += `<mark>${escapeHtml(match[0])}</mark>`;
        lastIndex = match.index + match[0].length;
        count++;
        if (match[0] === '') { re.lastIndex++; }
        if (count > 1000) break; // corta patrones degenerados (ej. //g sobre texto largo)
    }
    highlighted += escapeHtml(text.slice(lastIndex));
    out.innerHTML = `<div class="mb-1 text-muted">${count} coincidencia${count === 1 ? '' : 's'}</div><div>${highlighted || '<span class="text-muted">(sin coincidencias)</span>'}</div>`;
}

// ── JSON: formatear/validar ─────────────────────────────────────────────────
function formatJson() {
    const input = document.getElementById('d-json-input').value;
    const out   = document.getElementById('d-json-output');
    const errEl = document.getElementById('d-json-error');
    try {
        out.value = JSON.stringify(JSON.parse(input), null, 2);
        errEl.textContent = '';
    } catch (e) {
        out.value = '';
        errEl.textContent = 'JSON no válido: ' + e.message;
    }
}

// ── Decodificador JWT (solo header/payload, no verifica firma) ─────────────
function base64UrlDecode(str) {
    const padded = str.replace(/-/g, '+').replace(/_/g, '/').padEnd(str.length + (4 - str.length % 4) % 4, '=');
    return decodeURIComponent(escape(atob(padded)));
}
function decodeJwt() {
    const token = document.getElementById('d-jwt-input').value.trim();
    const out   = document.getElementById('d-jwt-output');
    const parts = token.split('.');
    if (parts.length < 2) { out.innerHTML = '<span class="text-danger">No parece un JWT (se esperan al menos 2 segmentos separados por ".").</span>'; return; }
    try {
        const header  = JSON.stringify(JSON.parse(base64UrlDecode(parts[0])), null, 2);
        const payload = JSON.stringify(JSON.parse(base64UrlDecode(parts[1])), null, 2);
        out.innerHTML = `<div class="text-muted small">Header</div><div>${escapeHtml(header)}</div><div class="text-muted small mt-2">Payload</div><div>${escapeHtml(payload)}</div>`;
    } catch (e) {
        out.innerHTML = '<span class="text-danger">No se ha podido decodificar: ' + escapeHtml(e.message) + '</span>';
    }
}

// ── Explicador de cron (usa cronstrue, cargado en includes/head.php) ───────
function explainCron() {
    const expr = document.getElementById('d-cron-input').value.trim();
    const out  = document.getElementById('d-cron-output');
    if (!expr) { out.innerHTML = '<span class="text-muted">Escribe una expresión cron, p.ej. */15 * * * *</span>'; return; }
    try {
        const text = cronstrue.toString(expr, { locale: 'es', throwExceptionOnParseError: true });
        out.innerHTML = `<p class="mb-0">${escapeHtml(text)}</p>`;
    } catch (e) {
        out.innerHTML = `<span class="text-danger">Expresión no válida: ${escapeHtml(String(e))}</span>`;
    }
}
