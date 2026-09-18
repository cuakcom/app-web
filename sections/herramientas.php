<?php require_once __DIR__ . '/../includes/smart_check_types.php'; ?>
<!-- ══════════ SECCIÓN: HERRAMIENTAS ══════════ -->
<div class="tab-pane fade" id="tab-herramientas" role="tabpanel">
    <div class="row g-3 align-items-start">
        <div class="col-12 col-lg-9 mx-auto">
            <div class="card search-options-card">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-1"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>Smart Check</h2>
                    <p class="small text-muted">Introduce un correo, dominio o IP, elige qué quieres comprobar, y opcionalmente añade tu pregunta. Gemini resume el resultado a partir de datos reales — nunca inventa.</p>

                    <div class="row g-2">
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-semibold mb-1">Correo, dominio o IP</label>
                            <input type="text" id="sc-query" class="form-control form-control-sm" placeholder="ejemplo.com, 1.2.3.4, usuario@dominio.com">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold mb-1">¿Qué comprobamos?</label>
                            <select id="sc-type" class="form-select form-select-sm">
                                <option value="">Solo la pregunta de abajo</option>
                                <?php foreach (SMART_CHECK_TYPES as $key => $cfg): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($cfg['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 d-flex align-items-end">
                            <button class="btn btn-dark btn-sm w-100 fw-bold" id="sc-btn" onclick="startSmartCheck()">
                                <span id="sc-btn-text"><i class="fa-solid fa-magnifying-glass me-1"></i>Analizar</span>
                                <span id="sc-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                            </button>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="form-label small fw-semibold mb-1">Pregunta (opcional)</label>
                        <textarea id="sc-question" class="form-control form-control-sm" rows="2" maxlength="300" placeholder="Ej: ¿por qué mi correo llega a spam?"></textarea>
                    </div>

                    <div class="border-top pt-2 mt-2 row g-2 align-items-end">
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-semibold mb-1">Modelo de Gemini</label>
                            <select id="sc-model" class="form-select form-select-sm" onchange="toggleCustomModel()">
                                <option value="gemini-2.0-flash" selected>gemini-2.0-flash (recomendado)</option>
                                <option value="gemini-2.0-flash-lite">gemini-2.0-flash-lite</option>
                                <option value="gemini-1.5-flash">gemini-1.5-flash</option>
                                <option value="gemini-1.5-pro">gemini-1.5-pro</option>
                                <option value="custom">Personalizado…</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-5 d-none" id="sc-model-custom-wrap">
                            <label class="form-label small fw-semibold mb-1">Nombre exacto del modelo</label>
                            <input type="text" id="sc-model-custom" class="form-control form-control-sm" placeholder="gemini-2.5-flash">
                        </div>
                    </div>
                    <p class="small text-muted mt-1 mb-0" style="font-size:0.72rem">Por defecto usamos <code>gemini-2.0-flash</code> (gratis en Google AI Studio). Cámbialo aquí si tu clave tiene acceso a otro modelo.</p>
                </div>
            </div>

            <div id="sc-results" class="d-none mt-3">
                <div class="card result-card">
                    <div class="card-header-cuak">
                        <span class="header-badge" style="background:#7c3aed"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Resultado</span>
                        <span class="small text-muted" id="sc-model-used"></span>
                    </div>
                    <div class="card-body p-3" id="sc-summary"></div>
                </div>
                <div id="sc-sections" class="d-flex flex-wrap gap-2 mt-2"></div>
            </div>
        </div>
    </div>
</div><!-- /tab-herramientas -->
