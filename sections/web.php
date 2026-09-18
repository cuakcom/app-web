<!-- ══════════ SECCIÓN: WEB ══════════ -->
<div class="tab-pane fade" id="tab-web" role="tabpanel">
    <div class="row g-3">
        <!-- Left Sidebar: Web + SSL Options -->
        <div class="col-12 col-lg-3">
            <div class="card search-options-card h-100">
                <div class="card-body p-3">
                    <span class="small text-muted fw-semibold d-block mb-2">SEO &amp; Tecnología</span>
                    <button class="btn btn-dark btn-sm w-100 fw-bold" id="btn-web-analyze" onclick="startWebAnalysis()">
                        <span id="web-btn-text"><i class="fa-solid fa-magnifying-glass me-1"></i>Analizar web</span>
                        <span id="web-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                    </button>
                    <small class="text-muted d-block mt-2">SEO, Open Graph y tecnologías</small>
                    <small class="text-muted d-block">Utiliza el dominio del buscador arriba</small>

                    <div class="border-top pt-3 mt-3">
                        <span class="small text-muted fw-semibold d-block mb-2">SSL/TLS</span>
                        <button class="btn btn-dark btn-sm w-100 fw-bold" id="btn-ssl-scan" onclick="startSslScan()">
                            <span id="ssl-btn-text"><i class="fa-solid fa-shield-halved me-1"></i>Escanear SSL/TLS</span>
                            <span id="ssl-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        </button>
                        <small class="text-muted d-block mt-2">Protocolos, cifrados y certificados</small>
                        <small class="text-muted d-block">Utiliza el dominio del buscador arriba</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content: Results -->
        <div class="col-12 col-lg-9">
            <div class="card search-options-card">
                <div class="card-body p-3">
                    <span class="small text-muted fw-semibold">Resultados del análisis web</span>
                </div>
            </div>
    <div id="web-results" class="d-none mt-3">
        <div class="row g-3">
            <div class="col-12 col-md-7">
                <div class="card result-card">
                    <div class="card-header-cuak">
                        <span class="header-badge" style="background:#0f766e">SEO &amp; Meta</span>
                        <span class="info-popover-btn" data-info-key="mod-seo"><i class="fa-solid fa-circle-info"></i></span>
                    </div>
                    <div class="card-body p-3" id="body-seo"></div>
                </div>
            </div>
            <div class="col-12 col-md-5">
                <div class="card result-card">
                    <div class="card-header-cuak">
                        <span class="header-badge" style="background:#7c3aed">Tecnologías</span>
                        <span class="info-popover-btn" data-info-key="mod-tech"><i class="fa-solid fa-circle-info"></i></span>
                    </div>
                    <div class="card-body p-3" id="body-tech"></div>
                </div>
            </div>
        </div>
    </div>

            <div class="card search-options-card mt-3">
                <div class="card-body p-3">
                    <span class="small text-muted fw-semibold">Resultados del escaneo SSL/TLS</span>
                </div>
            </div>
    <div id="ssl-results" class="d-none mt-3">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="card result-card">
                    <div class="card-header-cuak">
                        <span class="header-badge bg-success">Protocolos</span>
                        <span class="info-popover-btn" data-info-key="mod-ssl-protocols"><i class="fa-solid fa-circle-info"></i></span>
                    </div>
                    <div class="card-body p-3" id="body-ssl-protocols"></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card result-card">
                    <div class="card-header-cuak">
                        <span class="header-badge" style="background:#0369a1">Cipher &amp; Seguridad</span>
                        <span class="info-popover-btn" data-info-key="mod-ssl-cipher"><i class="fa-solid fa-circle-info"></i></span>
                    </div>
                    <div class="card-body p-3" id="body-ssl-cipher"></div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card result-card">
                    <div class="card-header-cuak">
                        <span class="header-badge" style="background:#7c3aed">Cadena de certificados</span>
                        <span class="info-popover-btn" data-info-key="mod-ssl-chain"><i class="fa-solid fa-circle-info"></i></span>
                    </div>
                    <div class="card-body p-3" id="body-ssl-chain"></div>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <div class="card result-card">
                <div class="card-header-cuak">
                    <span class="header-badge" style="background:#374151">SAN — Dominios alternativos</span>
                    <span class="info-popover-btn" data-info-key="mod-ssl-san"><i class="fa-solid fa-circle-info"></i></span>
                </div>
                <div class="card-body p-3" id="body-ssl-san"></div>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>
</div><!-- /tab-web -->
