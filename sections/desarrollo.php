<!-- ══════════ SECCIÓN: DESARROLLO ══════════ -->
<div class="tab-pane fade" id="tab-desarrollo" role="tabpanel">
    <div class="row g-3">

        <!-- Generador de regex por IA -->
        <div class="col-12 col-lg-6">
            <div class="card result-card h-100">
                <div class="card-header-cuak">
                    <span class="header-badge" style="background:#7c3aed"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Regex por descripción (IA)</span>
                </div>
                <div class="card-body p-3">
                    <p class="small text-muted mb-2">Describe en lenguaje natural qué quieres capturar. Ej: "todo lo que acabe en .pez" o "líneas que no contengan geo".</p>
                    <textarea id="d-airx-input" class="form-control form-control-sm" rows="2" maxlength="400" placeholder="Describe el patrón..."></textarea>
                    <button class="btn btn-dark btn-sm w-100 mt-2" id="d-airx-btn" onclick="generateRegexAI()">
                        <span id="d-airx-btn-text"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Generar</span>
                        <span id="d-airx-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                    </button>
                    <div id="d-airx-output" class="mt-2 small"></div>
                </div>
            </div>
        </div>

        <!-- Tester de regex -->
        <div class="col-12 col-lg-6">
            <div class="card result-card h-100">
                <div class="card-header-cuak">
                    <span class="header-badge" style="background:#1e40af"><i class="fa-solid fa-magnifying-glass me-1"></i>Tester de regex</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-8">
                            <input type="text" id="d-rx-pattern" class="form-control form-control-sm tool-mono" placeholder="Patrón, p.ej. \\d+">
                        </div>
                        <div class="col-4">
                            <input type="text" id="d-rx-flags" class="form-control form-control-sm" placeholder="flags: gi">
                        </div>
                    </div>
                    <textarea id="d-rx-text" class="form-control form-control-sm tool-mono mt-2" rows="3" placeholder="Texto de prueba..." oninput="testRegex()"></textarea>
                    <div id="d-rx-output" class="mt-2 small tool-mono" style="white-space:pre-wrap"></div>
                </div>
            </div>
        </div>

        <!-- JSON formatter/validador -->
        <div class="col-12 col-lg-6">
            <div class="card result-card h-100">
                <div class="card-header-cuak">
                    <span class="header-badge" style="background:#b45309"><i class="fa-solid fa-code me-1"></i>JSON: formatear/validar</span>
                </div>
                <div class="card-body p-3">
                    <textarea id="d-json-input" class="form-control form-control-sm tool-mono" rows="4" placeholder='{"clave": "valor"}'></textarea>
                    <button class="btn btn-dark btn-sm w-100 mt-2" onclick="formatJson()">Formatear/validar</button>
                    <div id="d-json-error" class="small text-danger mt-1"></div>
                    <textarea id="d-json-output" class="form-control form-control-sm mt-2 tool-mono" rows="4" readonly></textarea>
                </div>
            </div>
        </div>

        <!-- Decodificador JWT -->
        <div class="col-12 col-lg-6">
            <div class="card result-card h-100">
                <div class="card-header-cuak">
                    <span class="header-badge bg-dark"><i class="fa-solid fa-key me-1"></i>Decodificador JWT</span>
                </div>
                <div class="card-body p-3">
                    <textarea id="d-jwt-input" class="form-control form-control-sm tool-mono" rows="3" placeholder="eyJhbGciOi..."></textarea>
                    <button class="btn btn-dark btn-sm w-100 mt-2" onclick="decodeJwt()">Decodificar</button>
                    <p class="small text-muted mt-1 mb-0" style="font-size:0.72rem">Solo decodifica header/payload; no verifica la firma.</p>
                    <div id="d-jwt-output" class="mt-2 small tool-mono" style="white-space:pre-wrap;word-break:break-all"></div>
                </div>
            </div>
        </div>

        <!-- Explicador de cron -->
        <div class="col-12 col-lg-6">
            <div class="card result-card h-100">
                <div class="card-header-cuak">
                    <span class="header-badge" style="background:#0f766e"><i class="fa-solid fa-clock me-1"></i>Explicador de cron</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-8">
                            <input type="text" id="d-cron-input" class="form-control form-control-sm tool-mono" placeholder="*/15 * * * *" oninput="explainCron()">
                        </div>
                        <div class="col-4">
                            <button class="btn btn-dark btn-sm w-100" onclick="explainCron()">Explicar</button>
                        </div>
                    </div>
                    <div id="d-cron-output" class="mt-2 small"></div>
                </div>
            </div>
        </div>

    </div>
</div><!-- /tab-desarrollo -->
