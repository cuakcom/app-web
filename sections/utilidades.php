<!-- ══════════ SECCIÓN: UTILIDADES ══════════ -->
<div class="tab-pane fade" id="tab-utilidades" role="tabpanel">
    <div class="row g-3">

        <!-- Conversor de bases numéricas -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card result-card h-100">
                <div class="card-header-cuak">
                    <span class="header-badge" style="background:#0369a1"><i class="fa-solid fa-hashtag me-1"></i>Bases numéricas</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-8">
                            <input type="text" id="u-base-input" class="form-control form-control-sm tool-mono" placeholder="Valor, p.ej. ff">
                        </div>
                        <div class="col-4">
                            <select id="u-base-from" class="form-select form-select-sm">
                                <option value="2">Bin (2)</option>
                                <option value="8">Oct (8)</option>
                                <option value="10" selected>Dec (10)</option>
                                <option value="16">Hex (16)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-8">
                            <input type="number" id="u-base-custom" min="2" max="36" class="form-control form-control-sm" placeholder="Base destino extra (2-36, opcional)">
                        </div>
                        <div class="col-4">
                            <button class="btn btn-dark btn-sm w-100" onclick="baseConvert()">Convertir</button>
                        </div>
                    </div>
                    <div id="u-base-output" class="mt-2 small"></div>
                </div>
            </div>
        </div>

        <!-- Cuenta atrás / evento -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card result-card h-100">
                <div class="card-header-cuak">
                    <span class="header-badge" style="background:#7c3aed"><i class="fa-solid fa-hourglass-half me-1"></i>Cuenta atrás</span>
                </div>
                <div class="card-body p-3">
                    <label class="form-label small fw-semibold mb-1">Fecha/hora del evento</label>
                    <div class="row g-2">
                        <div class="col-8">
                            <input type="datetime-local" id="u-event-date" class="form-control form-control-sm">
                        </div>
                        <div class="col-4">
                            <button class="btn btn-dark btn-sm w-100" onclick="startEventCountdown()">Calcular</button>
                        </div>
                    </div>
                    <div id="u-event-output" class="mt-2 small"><span class="text-muted">Elige una fecha.</span></div>
                </div>
            </div>
        </div>

        <!-- Lorem Ipsum -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card result-card h-100">
                <div class="card-header-cuak">
                    <span class="header-badge bg-dark"><i class="fa-solid fa-paragraph me-1"></i>Lorem Ipsum</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-5">
                            <input type="number" id="u-lorem-count" value="3" min="1" max="50" class="form-control form-control-sm">
                        </div>
                        <div class="col-4">
                            <select id="u-lorem-unit" class="form-select form-select-sm">
                                <option value="parrafos">Párrafos</option>
                                <option value="palabras">Palabras</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <button class="btn btn-dark btn-sm w-100" onclick="generateLorem()">Ir</button>
                        </div>
                    </div>
                    <textarea id="u-lorem-output" class="form-control form-control-sm mt-2 tool-mono" rows="4" readonly></textarea>
                </div>
            </div>
        </div>

        <!-- HTML -> Markdown -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card result-card h-100">
                <div class="card-header-cuak">
                    <span class="header-badge" style="background:#0f766e"><i class="fa-brands fa-markdown me-1"></i>HTML → Markdown</span>
                </div>
                <div class="card-body p-3">
                    <textarea id="u-htmd-input" class="form-control form-control-sm tool-mono" rows="4" placeholder="Pega aquí HTML..."></textarea>
                    <button class="btn btn-dark btn-sm w-100 mt-2" onclick="convertHtmlToMarkdown()">Convertir</button>
                    <textarea id="u-htmd-output" class="form-control form-control-sm mt-2 tool-mono" rows="4" readonly></textarea>
                </div>
            </div>
        </div>

        <!-- JSON <-> YAML -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card result-card h-100">
                <div class="card-header-cuak">
                    <span class="header-badge" style="background:#b45309"><i class="fa-solid fa-code-compare me-1"></i>JSON ↔ YAML</span>
                </div>
                <div class="card-body p-3">
                    <textarea id="u-jy-input" class="form-control form-control-sm tool-mono" rows="4" placeholder="Pega aquí JSON o YAML..."></textarea>
                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <button class="btn btn-dark btn-sm w-100" onclick="convertJsonYaml('json2yaml')">JSON → YAML</button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-dark btn-sm w-100" onclick="convertJsonYaml('yaml2json')">YAML → JSON</button>
                        </div>
                    </div>
                    <div id="u-jy-error" class="small text-danger mt-1"></div>
                    <textarea id="u-jy-output" class="form-control form-control-sm mt-2 tool-mono" rows="4" readonly></textarea>
                </div>
            </div>
        </div>

        <!-- Base64 / URL / Hash -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card result-card h-100">
                <div class="card-header-cuak">
                    <span class="header-badge" style="background:#be185d"><i class="fa-solid fa-key me-1"></i>Base64 / URL / Hash</span>
                </div>
                <div class="card-body p-3">
                    <textarea id="u-enc-input" class="form-control form-control-sm tool-mono" rows="2" placeholder="Texto..."></textarea>
                    <div class="row g-2 mt-2">
                        <div class="col-6"><button class="btn btn-outline-dark btn-sm w-100" onclick="b64UrlHash('b64-encode')">Base64 →</button></div>
                        <div class="col-6"><button class="btn btn-outline-dark btn-sm w-100" onclick="b64UrlHash('b64-decode')">Base64 ←</button></div>
                        <div class="col-6"><button class="btn btn-outline-dark btn-sm w-100" onclick="b64UrlHash('url-encode')">URL →</button></div>
                        <div class="col-6"><button class="btn btn-outline-dark btn-sm w-100" onclick="b64UrlHash('url-decode')">URL ←</button></div>
                    </div>
                    <textarea id="u-enc-output" class="form-control form-control-sm mt-2 tool-mono" rows="2" readonly></textarea>
                    <button class="btn btn-dark btn-sm w-100 mt-2" onclick="computeHashes()">Calcular hashes (MD5/SHA1/SHA256)</button>
                    <div class="mt-2 small tool-mono" style="word-break:break-all">
                        <div>MD5: <span id="u-hash-md5" class="text-muted">—</span></div>
                        <div>SHA1: <span id="u-hash-sha1" class="text-muted">—</span></div>
                        <div>SHA256: <span id="u-hash-sha256" class="text-muted">—</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generador de contraseñas -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card result-card h-100">
                <div class="card-header-cuak">
                    <span class="header-badge" style="background:#4d7c0f"><i class="fa-solid fa-lock me-1"></i>Contraseñas</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-8">
                            <label class="form-label small mb-0">Longitud</label>
                            <input type="number" id="u-pwd-len" value="16" min="4" max="128" class="form-control form-control-sm">
                        </div>
                        <div class="col-4 d-flex align-items-end h-100">
                            <button class="btn btn-dark btn-sm w-100" onclick="generatePassword()">Generar</button>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-3 mt-2 small">
                        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="u-pwd-lower" checked><label class="form-check-label" for="u-pwd-lower">a-z</label></div>
                        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="u-pwd-upper" checked><label class="form-check-label" for="u-pwd-upper">A-Z</label></div>
                        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="u-pwd-digits" checked><label class="form-check-label" for="u-pwd-digits">0-9</label></div>
                        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="u-pwd-symbols"><label class="form-check-label" for="u-pwd-symbols">símbolos</label></div>
                    </div>
                    <div id="u-pwd-error" class="small text-danger mt-1"></div>
                    <input type="text" id="u-pwd-output" class="form-control form-control-sm mt-2 tool-mono" readonly>
                </div>
            </div>
        </div>

        <!-- Subredes IP -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card result-card h-100">
                <div class="card-header-cuak">
                    <span class="header-badge" style="background:#1e40af"><i class="fa-solid fa-network-wired me-1"></i>Subredes IP</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-8">
                            <input type="text" id="u-cidr-input" class="form-control form-control-sm tool-mono" placeholder="192.168.1.10/24">
                        </div>
                        <div class="col-4">
                            <button class="btn btn-dark btn-sm w-100" onclick="calcSubnet()">Calcular</button>
                        </div>
                    </div>
                    <div id="u-cidr-output" class="mt-2 small"></div>
                </div>
            </div>
        </div>

        <!-- Timestamps -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card result-card h-100">
                <div class="card-header-cuak">
                    <span class="header-badge" style="background:#334155"><i class="fa-solid fa-clock me-1"></i>Timestamps</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-12">
                            <select id="u-ts-mode" class="form-select form-select-sm" onchange="toggleTimestampMode()">
                                <option value="to-date">Unix → fecha</option>
                                <option value="to-unix">Fecha → Unix</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-8">
                            <input type="text" id="u-ts-input" class="form-control form-control-sm tool-mono" placeholder="1735689600">
                            <input type="datetime-local" id="u-ts-date" class="form-control form-control-sm d-none">
                        </div>
                        <div class="col-4">
                            <button class="btn btn-dark btn-sm w-100" onclick="convertTimestamp()">Convertir</button>
                        </div>
                    </div>
                    <div id="u-ts-output" class="mt-2 small"></div>
                </div>
            </div>
        </div>

    </div>
</div><!-- /tab-utilidades -->
