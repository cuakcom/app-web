<!-- ══════════ SECCIÓN: CORREO ══════════ -->
<div class="tab-pane fade" id="tab-correo" role="tabpanel">
    <div class="row g-3">
        <!-- Left Sidebar: Email Options -->
        <div class="col-12 col-lg-3">
            <div class="card search-options-card h-100">
                <div class="card-body p-3">
                    <button class="btn btn-mail-analyze w-100 mb-3 fw-bold" id="btn-mail-analyze" onclick="startMailAnalysis()">
                        <span id="mail-btn-text"><i class="fa-solid fa-paper-plane me-1"></i>Analizar correo</span>
                        <span id="mail-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                    </button>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-2"><i class="fa-solid fa-at me-1"></i>Probar cuenta</label>
                        <input type="email" id="input-email-test" class="form-control form-control-sm"
                               placeholder="cuenta@dominio.com">
                        <small class="text-muted d-block mt-1">Verifica si el buzón existe</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-2"><i class="fa-solid fa-file me-1"></i>Analizar .eml</label>
                        <label class="btn btn-sm btn-eml-analyze w-100 mb-0">
                            <i class="fa-solid fa-file-arrow-up me-1"></i>Cargar archivo
                            <input type="file" id="input-eml" accept=".eml,.txt" class="d-none" onchange="uploadEml(this)">
                        </label>
                        <small class="text-muted d-block mt-1" id="eml-filename">Sube un .eml</small>
                    </div>

                    <div class="border-top pt-3">
                        <button class="btn btn-sm btn-outline-danger w-100 mb-2" id="btn-relay-test" onclick="startRelayTest()">
                            <span id="relay-btn-text"><i class="fa-solid fa-vials me-1"></i>Test SMTP</span>
                            <span id="relay-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        </button>
                        <small class="text-muted d-block">Relay &amp; entrega</small>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <button class="btn btn-sm btn-outline-warning w-100 mb-2 fw-semibold" id="btn-abuse-check" onclick="startAbuseCheck()">
                            <span id="abuse-btn-text"><i class="fa-solid fa-bug me-1"></i>AbuseIPDB</span>
                            <span id="abuse-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        </button>
                        <small class="text-muted d-block">Verificar IP reportada</small>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <label class="form-label small fw-semibold mb-2">
                            <i class="fa-solid fa-shield-halved me-1"></i>Validador SPF
                            <span class="info-popover-btn" data-info-key="mod-spfcheck"><i class="fa-solid fa-circle-info"></i></span>
                        </label>
                        <input type="text" id="spf-domain" class="form-control form-control-sm mb-2" placeholder="Dominio: ejemplo.com">
                        <input type="text" id="spf-ip" class="form-control form-control-sm mb-2" placeholder="IP remitente: 1.2.3.4">
                        <button class="btn btn-dark btn-sm w-100" onclick="startSpfCheck()">
                            <span id="spf-btn-text"><i class="fa-solid fa-magnifying-glass me-1"></i>Verificar SPF</span>
                            <span id="spf-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        </button>
                        <small class="text-muted d-block mt-1">¿IP autorizada para enviar en nombre del dominio?</small>
                    </div>

                    <p class="small text-muted mb-0 mt-3" style="font-size:0.72rem">
                        <i class="fa-solid fa-shield-halved me-1 text-success"></i>
                        <em>Datos confidenciales protegidos</em>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Content: Results -->
        <div class="col-12 col-lg-9">
            <div class="card search-options-card">
                <div class="card-body p-3">
                    <span class="small text-muted fw-semibold">
                        <i class="fa-solid fa-envelope me-1"></i>Diagnóstico de correo para el dominio introducido arriba
                    </span>
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
                            <span class="info-popover-btn" data-info-key="mod-mail-score"><i class="fa-solid fa-circle-info"></i></span>
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
                            <span class="info-popover-btn" data-info-key="mod-mail-mx"><i class="fa-solid fa-circle-info"></i></span>
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
                            <span class="info-popover-btn" data-info-key="mod-mail-smtp"><i class="fa-solid fa-circle-info"></i></span>
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
                            <span class="info-popover-btn" data-info-key="mod-mail-blacklist"><i class="fa-solid fa-circle-info"></i></span>
                        </div>
                        <button class="btn btn-link p-0 text-success" id="dl-mail-blacklist" onclick="downloadMailCard('blacklist')" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-mail-blacklist">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>
                <!-- Blacklist IP (mismo módulo que en Diagnóstico, para la IP del dominio) -->
                <div class="card result-card" id="card-mail-ipbl">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-up-down-left-right drag-handle"></i>
                            <span class="header-badge bg-success" id="badge-mail-ipbl">Blacklist IP</span>
                            <span class="info-popover-btn" data-info-key="mod-blacklist"><i class="fa-solid fa-circle-info"></i></span>
                        </div>
                        <button class="btn btn-link p-0 text-success" id="dl-mail-ipbl" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-mail-ipbl">
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
                            <span class="info-popover-btn" data-info-key="mod-mail-spf"><i class="fa-solid fa-circle-info"></i></span>
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
                            <span class="info-popover-btn" data-info-key="mod-mail-dmarc"><i class="fa-solid fa-circle-info"></i></span>
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
                            <span class="info-popover-btn" data-info-key="mod-mail-dkim"><i class="fa-solid fa-circle-info"></i></span>
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
    <!-- EML results -->
    <div id="eml-results" class="d-none mt-3">
        <div class="card result-card">
            <div class="card-header-cuak">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-up-down-left-right drag-handle"></i>
                    <span class="header-badge" style="background:#0369a1">Análisis .eml</span>
                    <span class="info-popover-btn" data-info-key="mod-eml"><i class="fa-solid fa-circle-info"></i></span>
                </div>
                <button class="btn btn-link p-0" style="color:#0369a1" onclick="downloadEmlReport()" title="Descargar">
                    <i class="fa-solid fa-download"></i>
                </button>
            </div>
            <div class="card-body p-3" id="body-eml">
                <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
            </div>
        </div>
    </div>
    <!-- Relay results -->
    <div id="relay-results" class="d-none mt-3">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="card result-card">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <span class="header-badge bg-danger">Open Relay</span>
                            <span class="info-popover-btn" data-info-key="mod-relay-open"><i class="fa-solid fa-circle-info"></i></span>
                        </div>
                    </div>
                    <div class="card-body p-3" id="body-relay-openrelay">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card result-card">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <span class="header-badge" style="background:#374151">Simulación entrega</span>
                            <span class="info-popover-btn" data-info-key="mod-relay-delivery"><i class="fa-solid fa-circle-info"></i></span>
                        </div>
                    </div>
                    <div class="card-body p-3" id="body-relay-delivery">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- AbuseIPDB results -->
    <div id="abuse-results" class="d-none mt-3">
        <div class="card result-card">
            <div class="card-header-cuak">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-up-down-left-right drag-handle"></i>
                    <span class="header-badge" style="background:#d97706" id="badge-mail-abuse">Correo / Abuse</span>
                    <span class="info-popover-btn" data-info-key="mod-abuseipdb"><i class="fa-solid fa-circle-info"></i></span>
                </div>
                <button class="btn btn-link p-0" style="color:#d97706" onclick="downloadAbuseReport()" title="Descargar">
                    <i class="fa-solid fa-download"></i>
                </button>
            </div>
            <div class="card-body p-3" id="body-mail-abuse">
                <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
            </div>
        </div>
    </div>
    <!-- SPF Check results -->
    <div id="spf-results" class="d-none mt-3">
        <div class="card result-card">
            <div class="card-header-cuak">
                <div class="d-flex align-items-center gap-2">
                    <span class="header-badge" style="background:#0f766e">SPF Check</span>
                    <span class="small text-muted" id="spf-meta"></span>
                </div>
                <button class="btn btn-link p-0" style="color:#0f766e" onclick="downloadSpfCheck()" title="Descargar">
                    <i class="fa-solid fa-download"></i>
                </button>
            </div>
            <div class="card-body p-3" id="body-spf"></div>
        </div>
    </div>
            </div>
        </div>
    </div>
</div><!-- /tab-correo -->
