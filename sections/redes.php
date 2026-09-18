<!-- ══════════ SECCIÓN: REDES ══════════ -->
<div class="tab-pane fade" id="tab-redes" role="tabpanel">
    <div class="row g-3 align-items-start">
        <!-- Left Sidebar: Network Options -->
        <div class="col-12 col-lg-3">
            <div class="card search-options-card h-100">
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-2"><i class="fa-solid fa-location-dot me-1"></i>Geolocalización</label>
                        <input type="text" id="red-input" class="form-control form-control-sm mb-2" placeholder="1.2.3.4 o dominio.com">
                        <button class="btn btn-dark btn-sm w-100" onclick="startGeoIp()">
                            <span id="geo-btn-text"><i class="fa-solid fa-location-dot me-1"></i>Analizar IP</span>
                            <span id="geo-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content: Results -->
        <div class="col-12 col-lg-9">
            <div class="card search-options-card">
                <div class="card-body p-3">
                    <span class="small text-muted fw-semibold">Resultados de análisis de red</span>
                </div>
            </div>
    <div id="red-results" class="d-none mt-3">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="card result-card">
                    <div class="card-header-cuak">
                        <span class="header-badge" style="background:#0369a1">Geo IP &amp; ASN</span>
                        <span class="info-popover-btn" data-info-key="mod-geoip"><i class="fa-solid fa-circle-info"></i></span>
                    </div>
                    <div class="card-body p-3" id="body-geoip">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card result-card">
                    <div class="card-header-cuak">
                        <span class="header-badge bg-dark">WHOIS IP</span>
                        <span class="info-popover-btn" data-info-key="mod-whoisip"><i class="fa-solid fa-circle-info"></i></span>
                    </div>
                    <div class="card-body p-3" id="body-whoisip">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>
</div><!-- /tab-redes -->
