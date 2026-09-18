<!-- ══════════ SECCIÓN: DNS ══════════ -->
<div class="tab-pane fade" id="tab-dns" role="tabpanel">
    <div class="row g-3 align-items-start">
        <!-- Left Sidebar: DNS Options -->
        <div class="col-12 col-lg-3">
            <div class="card search-options-card h-100">
                <div class="card-body p-3">
                    <label class="form-label small fw-semibold mb-2"><i class="fa-solid fa-terminal me-1"></i>Consulta manual (DIG/DNS)</label>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-2"><i class="fa-solid fa-globe me-1"></i>Dominio</label>
                        <input type="text" id="dnsq-domain" class="form-control form-control-sm" placeholder="ejemplo.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-2">Tipo de registro</label>
                        <select id="dnsq-type" class="form-select form-select-sm">
                            <?php foreach (['A','AAAA','CNAME','MX','NS','TXT','SOA','SRV','CAA','PTR','ANY'] as $t): ?>
                            <option value="<?= $t ?>"><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-2">Servidor DNS</label>
                        <select id="dnsq-server" class="form-select form-select-sm" onchange="toggleCustomDns()">
                            <option value="8.8.8.8">8.8.8.8 — Google</option>
                            <option value="8.8.4.4">8.8.4.4 — Google Alt</option>
                            <option value="1.1.1.1">1.1.1.1 — Cloudflare</option>
                            <option value="1.0.0.1">1.0.0.1 — Cloudflare Alt</option>
                            <option value="9.9.9.9">9.9.9.9 — Quad9</option>
                            <option value="208.67.222.222">208.67.222.222 — OpenDNS</option>
                            <option value="94.140.14.14">94.140.14.14 — AdGuard</option>
                            <option value="custom">Personalizado…</option>
                        </select>
                    </div>

                    <div class="mb-3" id="dnsq-custom-wrap" style="display:none">
                        <label class="form-label small fw-semibold mb-2">IP personalizada</label>
                        <input type="text" id="dnsq-custom" class="form-control form-control-sm" placeholder="x.x.x.x">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold mb-2">Puerto</label>
                        <input type="number" id="dnsq-port" class="form-control form-control-sm" value="53" min="1" max="65535">
                    </div>

                    <button class="btn btn-dark btn-sm w-100" onclick="startDnsQuery()">
                        <span id="dnsq-btn-text"><i class="fa-solid fa-search me-1"></i>Consultar</span>
                        <span id="dnsq-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                    </button>

                    <div class="border-top pt-3 mt-3">
                        <label class="form-label small fw-semibold mb-2"><i class="fa-solid fa-globe me-1"></i>Propagación DNS</label>
                        <div class="mb-2">
                            <select id="prop-type" class="form-select form-select-sm">
                                <?php foreach (['A','AAAA','CNAME','MX','NS','TXT','SOA'] as $t): ?>
                                <option value="<?= $t ?>"><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-secondary btn-sm w-100" onclick="startPropagation()">
                            <span id="prop-btn-text"><i class="fa-solid fa-globe me-1"></i>Consultar</span>
                            <span id="prop-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        </button>
                        <small class="text-muted d-block mt-1">Utiliza el dominio del buscador arriba</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content: Results -->
        <div class="col-12 col-lg-9">
            <div class="card search-options-card">
                <div class="card-body p-3">
                    <span class="small text-muted fw-semibold">Resultados de consultas DNS</span>
                </div>
            </div>
    <div id="dnsq-results" class="d-none mt-3">
        <div class="card result-card">
            <div class="card-header-cuak">
                <div class="d-flex align-items-center gap-2">
                    <span class="header-badge" style="background:#7c3aed">Resultado</span>
                    <span class="small text-muted" id="dnsq-meta"></span>
                </div>
                <button class="btn btn-link p-0" style="color:#7c3aed" onclick="downloadDnsQuery()" title="Descargar">
                    <i class="fa-solid fa-download"></i>
                </button>
            </div>
            <div class="card-body p-3" id="body-dnsq"></div>
        </div>
    </div>

    <div id="prop-results" class="d-none mt-3">
        <div class="card result-card">
            <div class="card-header-cuak">
                <div class="d-flex align-items-center gap-2">
                    <span class="header-badge" style="background:#7c3aed">Propagación DNS</span>
                    <span class="small text-muted" id="prop-meta"></span>
                    <span class="info-popover-btn" data-info-key="mod-propagation"><i class="fa-solid fa-circle-info"></i></span>
                </div>
                <button class="btn btn-link p-0" style="color:#7c3aed" onclick="downloadPropagation()" title="Descargar">
                    <i class="fa-solid fa-download"></i>
                </button>
            </div>
            <div class="card-body p-3" id="body-propagation"></div>
        </div>
    </div>
            </div>
        </div>
</div><!-- /tab-dns -->
