<!-- ══════════ SECCIÓN: DIAGNÓSTICO ══════════ -->
<div class="tab-pane fade show active" id="tab-diagnostico" role="tabpanel">
    <div class="row g-3">
        <!-- Left Sidebar: Module Selectors -->
        <div class="col-12 col-lg-3">
            <div class="card search-options-card h-100">
                <div class="card-body p-3">
                    <button class="btn btn-xs-cuak btn-toggle-all w-100 mb-3" id="btn-toggle-all" onclick="toggleAllModules()">
                        <i class="fa-solid fa-check-double me-1"></i><span id="toggle-all-label">Activar todo</span>
                    </button>
                    <div class="module-selectors-vertical">
                        <div class="form-check form-switch">
                            <input class="form-check-input mod-check" type="checkbox" id="mod-dns">
                            <label class="form-check-label" for="mod-dns"><i class="fa-solid fa-server me-1"></i>DNS</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input mod-check" type="checkbox" id="mod-ports">
                            <label class="form-check-label" for="mod-ports"><i class="fa-solid fa-plug me-1"></i>Puertos</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input mod-check" type="checkbox" id="mod-whois">
                            <label class="form-check-label" for="mod-whois"><i class="fa-solid fa-id-card me-1"></i>WHOIS</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input mod-check" type="checkbox" id="mod-ssl">
                            <label class="form-check-label" for="mod-ssl"><i class="fa-solid fa-lock me-1"></i>SSL</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input mod-check" type="checkbox" id="mod-ping">
                            <label class="form-check-label" for="mod-ping"><i class="fa-solid fa-satellite-dish me-1"></i>Ping</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input mod-check" type="checkbox" id="mod-headers">
                            <label class="form-check-label" for="mod-headers"><i class="fa-solid fa-shield-halved me-1"></i>Cabeceras</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input mod-check" type="checkbox" id="mod-blacklist">
                            <label class="form-check-label" for="mod-blacklist"><i class="fa-solid fa-ban me-1"></i>Blacklist</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input mod-check" type="checkbox" id="mod-traceroute">
                            <label class="form-check-label" for="mod-traceroute"><i class="fa-solid fa-route me-1"></i>Traceroute</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input mod-check" type="checkbox" id="mod-redirect">
                            <label class="form-check-label" for="mod-redirect"><i class="fa-solid fa-arrow-right-arrow-left me-1"></i>Redirecciones</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content: Search and Options -->
        <div class="col-12 col-lg-9">
            <div class="card search-options-card">
                <div class="card-body p-3">
            <!-- DNS type chips -->
            <div id="dns-types-row" class="dns-types-row d-none">
                <span class="dns-types-label"><i class="fa-solid fa-filter me-1"></i>Registros DNS a consultar:</span>
                <div class="dns-chips" id="dns-chips">
                    <label class="dns-chip"><input type="checkbox" value="A"       checked><span>A</span></label>
                    <label class="dns-chip"><input type="checkbox" value="AAAA"    checked><span>AAAA</span></label>
                    <label class="dns-chip"><input type="checkbox" value="CNAME"   checked><span>CNAME</span></label>
                    <label class="dns-chip"><input type="checkbox" value="MX"      checked><span>MX</span></label>
                    <label class="dns-chip"><input type="checkbox" value="NS"      checked><span>NS</span></label>
                    <label class="dns-chip"><input type="checkbox" value="TXT"     checked><span>TXT</span></label>
                    <label class="dns-chip"><input type="checkbox" value="SPF"     checked><span>SPF</span></label>
                    <label class="dns-chip"><input type="checkbox" value="DMARC"   checked><span>DMARC</span></label>
                    <label class="dns-chip"><input type="checkbox" value="DKIM"    checked><span>DKIM</span></label>
                    <label class="dns-chip"><input type="checkbox" value="CAA"     checked><span>CAA</span></label>
                    <label class="dns-chip"><input type="checkbox" value="SOA"><span>SOA</span></label>
                    <label class="dns-chip"><input type="checkbox" value="SRV"><span>SRV</span></label>
                    <label class="dns-chip"><input type="checkbox" value="MTA-STS"><span>MTA-STS</span></label>
                    <label class="dns-chip"><input type="checkbox" value="BIMI"><span>BIMI</span></label>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export bar -->
    <div id="export-bar" class="d-none d-flex justify-content-between align-items-center my-3">
        <small class="text-muted fw-semibold" id="analyzed-domain-label"></small>
        <button class="btn btn-sm btn-outline-primary fw-semibold" onclick="exportAll()">
            <i class="fa-solid fa-file-export me-1"></i>Exportar todo
        </button>
    </div>

    <!-- Results grid -->
    <div id="results" class="d-none mt-3">
        <div class="row g-3">
            <div class="col-12 col-md-6 d-flex flex-column gap-3" id="col-left">
                <?php foreach ([
                    ['resolution', 'bg-primary',   'text-primary',   'Resolución',    null],
                    ['ssl',        'bg-success',    'text-success',   'SSL',           'badge-ssl'],
                    ['ports',      'bg-dark',       'text-dark',      'Puertos',       null],
                    ['ping',       null,            null,             'Ping',          null],
                    ['headers',    null,            null,             'Cabeceras',     null],
                ] as [$mod, $badgeCls, $dlCls, $label, $badgeId]):
                    $extra = match($mod) {
                        'ping'    => 'style="background:#ea580c"',
                        'headers' => 'style="background:#0891b2"',
                        default   => '',
                    };
                    $dlExtra = match($mod) {
                        'ping'    => 'style="color:#ea580c"',
                        'headers' => 'style="color:#0891b2"',
                        default   => '',
                    };
                    $hide = ($mod !== 'resolution') ? 'd-none' : '';
                ?>
                <div class="card result-card <?= $hide ?>" id="card-<?= $mod ?>">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                            <span class="header-badge <?= $badgeCls ?? '' ?>" <?= $extra ?> <?= $badgeId ? "id=\"{$badgeId}\"" : '' ?>><?= $label ?></span>
                            <span class="info-popover-btn" data-info-key="mod-<?= $mod ?>"><i class="fa-solid fa-circle-info"></i></span>
                        </div>
                        <button class="btn btn-link p-0 <?= $dlCls ?? '' ?>" <?= $dlExtra ?> <?= $mod === 'ssl' ? 'id="dl-ssl"' : '' ?> onclick="downloadCard('<?= $mod ?>')" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-<?= $mod ?>">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="col-12 col-md-6 d-flex flex-column gap-3" id="col-right">
                <!-- Web Info (siempre visible al analizar) -->
                <div class="card result-card" id="card-webinfo">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                            <span class="header-badge" style="background:#0f766e">Web Info</span>
                            <span class="info-popover-btn" data-info-key="mod-webinfo"><i class="fa-solid fa-circle-info"></i></span>
                        </div>
                        <button class="btn btn-link p-0" style="color:#0f766e" onclick="downloadCard('webinfo')" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-webinfo">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>
                <?php foreach ([
                    ['dns',       null,  null,           'DNS',          null],
                    ['whois',     'bg-danger', 'text-danger', 'WHOIS',  null],
                    ['blacklist', 'bg-success','text-success','Blacklist','badge-blacklist'],
                    ['traceroute',null,  null,           'Traceroute',   null],
                    ['redirect',  null,  null,           'Redirecciones',null],
                ] as [$mod, $badgeCls, $dlCls, $label, $badgeId]):
                    $extra = match($mod) {
                        'dns'        => 'style="background:#7c3aed"',
                        'traceroute' => 'style="background:#065f46"',
                        'redirect'   => 'style="background:#92400e"',
                        default      => '',
                    };
                    $dlExtra = match($mod) {
                        'dns'        => 'style="color:#7c3aed"',
                        'traceroute' => 'style="color:#065f46"',
                        'redirect'   => 'style="color:#92400e"',
                        default      => '',
                    };
                ?>
                <div class="card result-card d-none" id="card-<?= $mod ?>">
                    <div class="card-header-cuak">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-up-down-left-right drag-handle" title="Mover"></i>
                            <span class="header-badge <?= $badgeCls ?? '' ?>" <?= $extra ?> <?= $badgeId ? "id=\"{$badgeId}\"" : '' ?>><?= $label ?></span>
                            <span class="info-popover-btn" data-info-key="mod-<?= $mod ?>"><i class="fa-solid fa-circle-info"></i></span>
                        </div>
                        <button class="btn btn-link p-0 <?= $dlCls ?? '' ?>" <?= $dlExtra ?> <?= $mod === 'blacklist' ? 'id="dl-blacklist"' : '' ?> onclick="downloadCard('<?= $mod ?>')" title="Descargar">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                    <div class="card-body p-3" id="body-<?= $mod ?>">
                        <div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div><!-- /tab-diagnostico -->
