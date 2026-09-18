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
                </div>
            </div>

            <div id="sc-results" class="d-none mt-3">
                <div class="card result-card">
                    <div class="card-header-cuak">
                        <span class="header-badge" style="background:#7c3aed"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Resultado</span>
                    </div>
                    <div class="card-body p-3" id="sc-summary"></div>
                </div>
                <div id="sc-sections" class="d-flex flex-wrap gap-2 mt-2"></div>
            </div>
        </div>
    </div>
</div><!-- /tab-herramientas -->
