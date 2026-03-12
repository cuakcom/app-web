<?php
/**
 * Cuakcom Expert Suite - v3.0.0
 */
define('APP_VERSION', '3.0.0');

// Datos del visitante (server-side)
function getClientIp(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_REAL_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $h) {
        if (!empty($_SERVER[$h])) {
            $ip = trim(explode(',', $_SERVER[$h])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}
$visitorIp = getClientIp();
$visitorUa = $_SERVER['HTTP_USER_AGENT']       ?? '';
$visitorLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
$visitorRef  = $_SERVER['HTTP_REFERER']         ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tools Check Dominios y DNS</title>
    <meta name="description" content="Utilidades para revisar Dominios, DNS, Correo y herramientas de verificación">
    <meta name="keywords" content="dns checker, ssl scanner, análisis correo, SPF DKIM DMARC, blacklist email, propagación dns, traceroute online, geolocalización ip, whois dominio, análisis seo web">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Cuakcom">
    <link rel="canonical" href="https://tools.cuakcom.com/">

    <!-- Open Graph -->
    <meta property="og:title" content="NetScope — Analizador DNS, SSL, Correo y Web">
    <meta property="og:description" content="Diagnóstico DNS completo, escáner SSL/TLS, análisis SPF/DKIM/DMARC, comprobación de blacklists, geolocalización IP, propagación DNS y análisis SEO web.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://tools.cuakcom.com/">
    <meta property="og:image" content="https://tools.cuakcom.com/favicon.svg">
    <meta property="og:site_name" content="NetScope by Cuakcom">
    <meta property="og:locale" content="es_ES">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="NetScope — Analizador DNS, SSL, Correo y Web">
    <meta name="twitter:description" content="Diagnóstico DNS, SSL, correo (SPF/DKIM/DMARC), blacklist, traceroute, geolocalización y análisis SEO. Gratis y sin registro.">
    <meta name="twitter:site" content="@cuakcom">

    <!-- Favicon -->
    <link rel="icon" href="favicon.svg" type="image/svg+xml">

    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebApplication",
      "name": "NetScope",
      "description": "Suite de herramientas de diagnóstico de red: DNS checker, SSL scanner, análisis de correo electrónico, propagación DNS, geolocalización IP y análisis SEO web.",
      "url": "https://tools.cuakcom.com/",
      "applicationCategory": "UtilityApplication",
      "operatingSystem": "Any",
      "offers": { "@type": "Offer", "price": "0", "priceCurrency": "EUR" },
      "provider": { "@type": "Organization", "name": "Cuakcom", "url": "https://cuakcom.com" }
    }
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
</head>
<body>

<!-- ===================== HEADER ===================== -->
<header class="header-section">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="header-title-block">
            <h1 class="header-title-main">
                <i class="fa-solid fa-network-wired me-2"></i>Tools Check Dominios y DNS
            </h1>
            <span class="header-title-sub">Utilidades para revisar Dominios, DNS, Correo y herramientas de verificación</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm darkmode-toggle" id="btn-darkmode" title="Modo oscuro">
                <i class="fa-solid fa-moon"></i>
            </button>
            <span class="version-badge">v<?= APP_VERSION ?></span>
        </div>
    </div>
</header>

<!-- ===================== MAIN ===================== -->
<main class="container py-3 py-md-4">

    <!-- Tabs (encima del buscador) -->
    <div class="main-tabs-wrap">
        <ul class="nav main-tabs" id="mainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="main-tab-btn active" id="tab-diag-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-diagnostico" type="button" role="tab">
                    <i class="fa-solid fa-magnifying-glass-chart me-1"></i>Diagnóstico
                    <span class="info-popover-btn" data-info-key="tab-diagnostico" title="Información" onclick="event.stopPropagation()">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="main-tab-btn" id="tab-mail-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-correo" type="button" role="tab">
                    <i class="fa-solid fa-envelope me-1"></i>Correo
                    <span class="info-popover-btn" data-info-key="tab-correo" title="Información" onclick="event.stopPropagation()">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="main-tab-btn" id="tab-dnsq-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-dnsquery" type="button" role="tab">
                    <i class="fa-solid fa-terminal me-1"></i>Consultas DNS
                    <span class="info-popover-btn" data-info-key="tab-dnsquery" title="Información" onclick="event.stopPropagation()">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="main-tab-btn" id="tab-red-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-red" type="button" role="tab">
                    <i class="fa-solid fa-network-wired me-1"></i>Red &amp; IP
                    <span class="info-popover-btn" data-info-key="tab-red" title="Información" onclick="event.stopPropagation()">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="main-tab-btn" id="tab-web-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-web" type="button" role="tab">
                    <i class="fa-solid fa-globe me-1"></i>Web
                    <span class="info-popover-btn" data-info-key="tab-web" title="Información" onclick="event.stopPropagation()">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="main-tab-btn" id="tab-ssl-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-ssl" type="button" role="tab">
                    <i class="fa-solid fa-shield-halved me-1"></i>SSL/TLS
                    <span class="info-popover-btn" data-info-key="tab-ssl" title="Información" onclick="event.stopPropagation()">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>
                </button>
            </li>
        </ul>
    </div>

    <!-- Search card (se oculta en pestañas con input propio) -->
    <div id="search-card-wrap">
    <div class="card search-card mb-0" style="border-radius:10px 10px 0 0; border-bottom:none;">
        <div class="card-body p-3 p-md-4 pb-2">
            <div class="input-group position-relative">
                <input type="text" id="input-domain" class="form-control form-control-lg"
                       placeholder="dominio.com o IP" autocomplete="off" spellcheck="false"
                       aria-label="Dominio a analizar">
                <button class="btn btn-dark btn-lg fw-bold px-3 px-md-4" id="btn-analyze" type="button">
                    <span id="btn-text"><i class="fa-solid fa-magnifying-glass me-1 d-none d-sm-inline"></i>ANALIZAR</span>
                    <span id="btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                </button>
                <div id="history-dropdown" class="history-dropdown d-none"></div>
            </div>
        </div>
    </div>
    </div>

    <div class="tab-content mb-4">

        <!-- ══════════ TAB: DIAGNÓSTICO ══════════ -->
        <div class="tab-pane fade show active" id="tab-diagnostico" role="tabpanel">
            <div class="card search-options-card">
                <div class="card-body p-3">
                    <!-- Módulos -->
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <button class="btn btn-xs-cuak btn-toggle-all" id="btn-toggle-all" onclick="toggleAllModules()">
                            <i class="fa-solid fa-check-double me-1"></i><span id="toggle-all-label">Activar todo</span>
                        </button>
                        <div class="module-selectors">
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

        <!-- ══════════ TAB: CORREO ══════════ -->
        <div class="tab-pane fade" id="tab-correo" role="tabpanel">
            <div class="card search-options-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                        <span class="small text-muted fw-semibold">
                            <i class="fa-solid fa-envelope me-1"></i>Diagnóstico de correo para el dominio introducido arriba
                        </span>
                        <button class="btn btn-mail-analyze ms-auto fw-bold" id="btn-mail-analyze" onclick="startMailAnalysis()">
                            <span id="mail-btn-text"><i class="fa-solid fa-paper-plane me-1"></i>Analizar correo</span>
                            <span id="mail-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        </button>
                    </div>
                    <!-- Email account test -->
                    <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                        <input type="email" id="input-email-test" class="form-control form-control-sm" style="max-width:260px"
                               placeholder="cuenta@dominio.com — prueba RCPT TO (opcional)">
                        <span class="small text-muted">Verifica si el buzón existe (resultado orientativo)</span>
                    </div>
                    <!-- EML upload -->
                    <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                        <label class="btn btn-sm btn-eml-analyze mb-0">
                            <i class="fa-solid fa-file-arrow-up me-1"></i>Analizar .eml
                            <input type="file" id="input-eml" accept=".eml,.txt" class="d-none" onchange="uploadEml(this)">
                        </label>
                        <span class="small text-muted" id="eml-filename">Sube un archivo .eml para analizar sus cabeceras</span>
                    </div>
                    <p class="small text-muted mb-0 mt-1" style="font-size:0.72rem">
                        <i class="fa-solid fa-shield-halved me-1 text-success"></i>
                        <em>*El contenido del mensaje no se compartirá con fuentes externas ni se almacenarán datos confidenciales</em>
                    </p>
                    <!-- Relay test -->
                    <div class="d-flex align-items-center gap-2 flex-wrap mt-2 pt-2 border-top">
                        <span class="small text-muted fw-semibold">
                            <i class="fa-solid fa-arrows-left-right me-1"></i>Pruebas avanzadas SMTP
                        </span>
                        <button class="btn btn-sm btn-outline-danger ms-auto" id="btn-relay-test" onclick="startRelayTest()">
                            <span id="relay-btn-text"><i class="fa-solid fa-vials me-1"></i>Test relay &amp; entrega</span>
                            <span id="relay-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        </button>
                    </div>
                    <!-- AbuseIPDB check -->
                    <div class="d-flex align-items-center gap-2 flex-wrap mt-2 pt-2 border-top">
                        <span class="small fw-semibold" style="color:#d97706">
                            <i class="fa-solid fa-shield-virus me-1"></i>Correo / Abuse — AbuseIPDB
                        </span>
                        <span class="small text-muted">Comprueba si la IP está reportada en abuseipdb.com</span>
                        <button class="btn btn-sm btn-outline-warning ms-auto fw-semibold" id="btn-abuse-check" onclick="startAbuseCheck()">
                            <span id="abuse-btn-text"><i class="fa-solid fa-bug me-1"></i>Comprobar AbuseIPDB</span>
                            <span id="abuse-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        </button>
                    </div>
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
        </div><!-- /tab-correo -->

        <!-- ══════════ TAB: CONSULTAS DNS ══════════ -->
        <div class="tab-pane fade" id="tab-dnsquery" role="tabpanel">
            <div class="card search-options-card">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-sm-4">
                            <label class="form-label small fw-semibold mb-1"><i class="fa-solid fa-globe me-1"></i>Dominio</label>
                            <input type="text" id="dnsq-domain" class="form-control form-control-sm" placeholder="ejemplo.com">
                        </div>
                        <div class="col-6 col-sm-2">
                            <label class="form-label small fw-semibold mb-1">Tipo</label>
                            <select id="dnsq-type" class="form-select form-select-sm">
                                <?php foreach (['A','AAAA','CNAME','MX','NS','TXT','SOA','SRV','CAA','PTR','ANY'] as $t): ?>
                                <option value="<?= $t ?>"><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-sm-3">
                            <label class="form-label small fw-semibold mb-1">Servidor DNS</label>
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
                        <div class="col-6 col-sm-2" id="dnsq-custom-wrap" style="display:none">
                            <label class="form-label small fw-semibold mb-1">IP servidor</label>
                            <input type="text" id="dnsq-custom" class="form-control form-control-sm" placeholder="x.x.x.x">
                        </div>
                        <div class="col-3 col-sm-1">
                            <label class="form-label small fw-semibold mb-1">Puerto</label>
                            <input type="number" id="dnsq-port" class="form-control form-control-sm" value="53" min="1" max="65535">
                        </div>
                        <div class="col-3 col-sm-2">
                            <button class="btn btn-dark btn-sm w-100" onclick="startDnsQuery()">
                                <span id="dnsq-btn-text"><i class="fa-solid fa-search me-1"></i>Consultar</span>
                                <span id="dnsq-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                            </button>
                        </div>
                    </div>
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

            <!-- ── Validador SPF ── -->
            <div class="card search-options-card mt-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="header-badge" style="background:#0f766e; font-size:0.75rem">
                            <i class="fa-solid fa-shield-halved me-1"></i>Validador SPF
                        </span>
                        <span class="info-popover-btn" data-info-key="mod-spfcheck"><i class="fa-solid fa-circle-info"></i></span>
                        <span class="small text-muted ms-1">¿Está autorizada esta IP para enviar correo en nombre del dominio?</span>
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-sm-4">
                            <label class="form-label small fw-semibold mb-1"><i class="fa-solid fa-globe me-1"></i>Dominio</label>
                            <input type="text" id="spf-domain" class="form-control form-control-sm" placeholder="ejemplo.com">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label small fw-semibold mb-1"><i class="fa-solid fa-server me-1"></i>IP del servidor remitente</label>
                            <input type="text" id="spf-ip" class="form-control form-control-sm" placeholder="1.2.3.4 ó 2001:db8::1">
                        </div>
                        <div class="col-12 col-sm-4">
                            <button class="btn btn-dark btn-sm w-100" onclick="startSpfCheck()">
                                <span id="spf-btn-text"><i class="fa-solid fa-magnifying-glass me-1"></i>Verificar SPF</span>
                                <span id="spf-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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

        </div><!-- /tab-dnsquery -->

        <!-- ══════════ TAB: RED & IP ══════════ -->
        <div class="tab-pane fade" id="tab-red" role="tabpanel">
            <div class="card search-options-card">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-sm-5">
                            <label class="form-label small fw-semibold mb-1"><i class="fa-solid fa-server me-1"></i>IP o dominio</label>
                            <input type="text" id="red-input" class="form-control form-control-sm" placeholder="1.2.3.4 o ejemplo.com">
                        </div>
                        <div class="col-6 col-sm-2">
                            <button class="btn btn-dark btn-sm w-100" onclick="startGeoIp()">
                                <span id="geo-btn-text"><i class="fa-solid fa-location-dot me-1"></i>Geolocalizar</span>
                                <span id="geo-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                            </button>
                        </div>
                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-12 col-sm-5">
                            <label class="form-label small fw-semibold mb-1"><i class="fa-solid fa-satellite-dish me-1"></i>DNS Propagación — dominio del buscador arriba</label>
                        </div>
                        <div class="col-6 col-sm-2">
                            <label class="form-label small fw-semibold mb-1">Tipo</label>
                            <select id="prop-type" class="form-select form-select-sm">
                                <?php foreach (['A','AAAA','CNAME','MX','NS','TXT','SOA'] as $t): ?>
                                <option value="<?= $t ?>"><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-sm-2">
                            <button class="btn btn-secondary btn-sm w-100" onclick="startPropagation()">
                                <span id="prop-btn-text"><i class="fa-solid fa-globe me-1"></i>Propagación</span>
                                <span id="prop-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                            </button>
                        </div>
                    </div>
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
        </div><!-- /tab-red -->

        <!-- ══════════ TAB: WEB ══════════ -->
        <div class="tab-pane fade" id="tab-web" role="tabpanel">
            <div class="card search-options-card">
                <div class="card-body p-3 d-flex align-items-center gap-3 flex-wrap">
                    <span class="small text-muted fw-semibold">
                        <i class="fa-solid fa-globe me-1"></i>Análisis SEO, Open Graph y tecnologías del dominio del buscador arriba
                    </span>
                    <button class="btn btn-dark btn-sm ms-auto fw-bold" id="btn-web-analyze" onclick="startWebAnalysis()">
                        <span id="web-btn-text"><i class="fa-solid fa-magnifying-glass me-1"></i>Analizar web</span>
                        <span id="web-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                    </button>
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
        </div><!-- /tab-web -->

        <!-- ══════════ TAB: SSL/TLS ══════════ -->
        <div class="tab-pane fade" id="tab-ssl" role="tabpanel">
            <div class="card search-options-card">
                <div class="card-body p-3 d-flex align-items-center gap-3 flex-wrap">
                    <span class="small text-muted fw-semibold">
                        <i class="fa-solid fa-lock me-1"></i>Escaneo TLS/SSL extendido del dominio del buscador arriba
                    </span>
                    <button class="btn btn-dark btn-sm ms-auto fw-bold" id="btn-ssl-scan" onclick="startSslScan()">
                        <span id="ssl-btn-text"><i class="fa-solid fa-shield-halved me-1"></i>Escanear SSL/TLS</span>
                        <span id="ssl-btn-loading" class="d-none"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                    </button>
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
        </div><!-- /tab-ssl -->

    </div><!-- /tab-content -->

</main>

<!-- ===================== FOOTER VISITANTE ===================== -->
<footer class="visitor-footer">
    <div class="container">
        <div class="visitor-footer-inner">
            <span class="visitor-label-prefix"><i class="fa-solid fa-user me-1"></i>Tus datos</span>
            <div id="visitor-info" class="d-inline-flex flex-wrap gap-3 align-items-center">
                <span class="visitor-item"><i class="fa-solid fa-circle-notch fa-spin me-1"></i>Cargando datos de acceso…</span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ============================================================
// Cuakcom Expert Suite - v<?= APP_VERSION ?> — Frontend
// ============================================================

const DNS_COLORS = {
    A:'bg-primary', AAAA:'bg-info text-dark', CNAME:'bg-secondary',
    MX:'bg-warning text-dark', NS:'bg-danger', TXT:'bg-success',
    SOA:'bg-dark', SRV:'dns-badge-srv', CAA:'dns-badge-caa',
    SPF:'dns-badge-spf', DMARC:'dns-badge-dmarc', DKIM:'dns-badge-dkim',
    'MTA-STS':'dns-badge-mtasts', BIMI:'dns-badge-bimi',
};

let currentDomain  = '';
const exportData   = {};
const mailExport   = {};

// ── Inicialización ────────────────────────────────────────────
document.getElementById('btn-analyze').addEventListener('click', startAnalysis);
document.getElementById('input-domain').addEventListener('keydown', e => {
    if (e.key === 'Enter') {
        // Analizar según la pestaña activa
        if (document.getElementById('tab-correo').classList.contains('show')) {
            startMailAnalysis();
        } else if (document.getElementById('tab-dnsquery').classList.contains('show')) {
            startDnsQuery();
        } else if (document.getElementById('tab-web').classList.contains('show')) {
            startWebAnalysis();
        } else if (document.getElementById('tab-ssl').classList.contains('show')) {
            startSslScan();
        } else {
            startAnalysis();
        }
    }
    if (e.key === 'Escape') hideHistoryDropdown();
});
document.getElementById('input-domain').addEventListener('input', showHistoryDropdown);
document.getElementById('input-domain').addEventListener('focus', showHistoryDropdown);
document.addEventListener('click', e => {
    if (!e.target.closest('#input-domain') && !e.target.closest('#history-dropdown'))
        hideHistoryDropdown();
});

// ── Ocultar buscador en pestañas con input propio ──────────────
const TABS_NO_SEARCH = new Set(['tab-dnsq-btn', 'tab-red-btn']);
document.getElementById('mainTabs').addEventListener('shown.bs.tab', e => {
    const hide = TABS_NO_SEARCH.has(e.target.id);
    document.getElementById('search-card-wrap').classList.toggle('d-none', hide);
    document.body.classList.toggle('search-hidden', hide);
});

// DNS chips toggle
document.getElementById('mod-dns').addEventListener('change', function () {
    document.getElementById('dns-types-row').classList.toggle('d-none', !this.checked);
});

// ── Dark mode ─────────────────────────────────────────────────
const darkBtn = document.getElementById('btn-darkmode');
if (localStorage.getItem('darkMode') === '1') {
    document.body.classList.add('dark-mode');
    darkBtn.innerHTML = '<i class="fa-solid fa-sun"></i>';
}
darkBtn.addEventListener('click', () => {
    const on = document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', on ? '1' : '0');
    darkBtn.innerHTML = on ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
});

// ── Toggle todos los módulos ──────────────────────────────────
function toggleAllModules() {
    const checks = Array.from(document.querySelectorAll('.mod-check'));
    const allOn  = checks.every(c => c.checked);
    checks.forEach(c => { c.checked = !allOn; c.dispatchEvent(new Event('change')); });
    document.getElementById('toggle-all-label').textContent = allOn ? 'Activar todo' : 'Desactivar todo';
}
// Actualizar label del botón al cambiar individualmente
document.querySelectorAll('.mod-check').forEach(c => {
    c.addEventListener('change', () => {
        const all   = document.querySelectorAll('.mod-check');
        const allOn = Array.from(all).every(x => x.checked);
        document.getElementById('toggle-all-label').textContent = allOn ? 'Desactivar todo' : 'Activar todo';
    });
});

// ── Historial ─────────────────────────────────────────────────
const HISTORY_KEY = 'cuakcom_history';
const MAX_HISTORY = 12;
function getHistory() {
    try { return JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]'); } catch { return []; }
}
function saveToHistory(domain) {
    let h = getHistory().filter(d => d !== domain);
    h.unshift(domain);
    localStorage.setItem(HISTORY_KEY, JSON.stringify(h.slice(0, MAX_HISTORY)));
}
function showHistoryDropdown() {
    const val = document.getElementById('input-domain').value.trim().toLowerCase();
    const h   = getHistory().filter(d => !val || d.includes(val));
    const dd  = document.getElementById('history-dropdown');
    if (!h.length) { dd.classList.add('d-none'); return; }
    dd.innerHTML = h.map(d =>
        `<div class="history-item" onclick="selectHistory('${esc(d)}')">`+
        `<i class="fa-solid fa-clock-rotate-left me-2 text-muted small"></i>${esc(d)}</div>`
    ).join('');
    dd.classList.remove('d-none');
}
function hideHistoryDropdown() {
    document.getElementById('history-dropdown').classList.add('d-none');
}
function selectHistory(domain) {
    document.getElementById('input-domain').value = domain;
    hideHistoryDropdown();
    startAnalysis();
}

// ── Análisis principal (Diagnóstico) ─────────────────────────
function startAnalysis() {
    const domain = normalizeDomain();
    if (!domain) return;
    hideHistoryDropdown();
    currentDomain = domain;
    saveToHistory(domain);

    const optional = ['dns','ports','whois','ssl','ping','headers','blacklist','traceroute','redirect'];
    const modules  = optional.filter(m => document.getElementById('mod-' + m).checked);
    const active   = ['resolution', ...modules];

    setAnalyzing(true);
    document.getElementById('results').classList.remove('d-none');
    document.getElementById('export-bar').classList.remove('d-none');
    document.getElementById('analyzed-domain-label').textContent = '🔍 ' + domain;

    setCardLoading('webinfo');

    const allCards = ['resolution','dns','ports','whois','ssl','ping','headers','blacklist','traceroute','redirect'];
    allCards.forEach(m => {
        const card = document.getElementById('card-' + m);
        if (!card) return;
        if (active.includes(m)) { card.classList.remove('d-none'); setCardLoading(m); }
        else card.classList.add('d-none');
    });

    Promise.allSettled([...active.map(m => fetchModule(m, domain)), fetchModule('webinfo', domain)])
        .then(() => setAnalyzing(false));
}

function setAnalyzing(active) {
    document.getElementById('btn-text').classList.toggle('d-none', active);
    document.getElementById('btn-loading').classList.toggle('d-none', !active);
    document.getElementById('btn-analyze').disabled = active;
}

// ── Análisis de correo ────────────────────────────────────────
async function startMailAnalysis() {
    const domain = normalizeDomain();
    if (!domain) return;
    currentDomain = domain;
    saveToHistory(domain);

    document.getElementById('mail-btn-text').classList.add('d-none');
    document.getElementById('mail-btn-loading').classList.remove('d-none');
    document.getElementById('btn-mail-analyze').disabled = true;
    document.getElementById('mail-results').classList.remove('d-none');

    ['mail-score','mail-mx','mail-smtp','mail-spf','mail-dmarc','mail-dkim','mail-blacklist','mail-ipbl']
        .forEach(id => setMailCardLoading(id.replace('mail-','')));

    try {
        const emailTest = (document.getElementById('input-email-test')?.value ?? '').trim();
        let url = `api.php?module=mailtest&domain=${encodeURIComponent(domain)}`;
        if (emailTest) url += `&email=${encodeURIComponent(emailTest)}`;
        const blUrl = `api.php?module=blacklist&domain=${encodeURIComponent(domain)}`;
        const [res, blRes] = await Promise.all([fetch(url), fetch(blUrl)]);
        const [data, blData] = await Promise.all([res.json(), blRes.json()]);
        mailExport['mailtest'] = data;
        mailExport['blacklist_ip'] = blData;
        if (data.success) renderMailResults(data);
        else {
            ['score','mx','smtp','spf','dmarc','dkim','blacklist'].forEach(k =>
                setMailCardError(k, data.error ?? 'Error desconocido'));
        }
        if (blData.success) renderMailIpbl(blData);
        else setMailCardError('ipbl', blData.error ?? 'Error desconocido');
    } catch(e) {
        ['score','mx','smtp','spf','dmarc','dkim','blacklist','ipbl'].forEach(k =>
            setMailCardError(k, 'Error de conexión: ' + e.message));
    } finally {
        document.getElementById('mail-btn-text').classList.remove('d-none');
        document.getElementById('mail-btn-loading').classList.add('d-none');
        document.getElementById('btn-mail-analyze').disabled = false;
    }
}

function setMailCardLoading(key) {
    const el = document.getElementById('body-mail-' + key);
    if (el) el.innerHTML = `<div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>`;
}
function setMailCardError(key, msg) {
    const el = document.getElementById('body-mail-' + key);
    if (el) el.innerHTML = `<div class="alert alert-danger py-2 mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(msg)}</div>`;
}

// ── Render correo ─────────────────────────────────────────────
function renderMailResults(d) {
    renderMailScore(d);
    renderMailMx(d);
    renderMailSmtp(d);
    renderMailSpf(d);
    renderMailDmarc(d);
    renderMailDkim(d);
    renderMailBlacklist(d);
}

function renderMailScore(d) {
    const pct = Math.round((d.score / d.score_max) * 100);
    const cls = pct >= 70 ? 'bg-success' : (pct >= 40 ? 'bg-warning' : 'bg-danger');
    const txt = pct >= 70 ? 'Buena entregabilidad' : (pct >= 40 ? 'Entregabilidad mejorable' : 'Entregabilidad deficiente');
    document.getElementById('badge-mail-score').className = 'header-badge ' + cls;
    const items = d.score_items ?? [];
    const rows = items.map(item =>
        `<div class="d-flex align-items-center gap-2 mb-1">
            <i class="fa-solid ${item.ok ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger'} small"></i>
            <span class="small ${item.ok ? '' : 'text-muted'}">${esc(item.label)}</span>
            ${item.weight > 1 ? `<span class="ttl-badge ms-1">×${item.weight}</span>` : ''}
         </div>`
    ).join('');
    // RCPT TO result
    let rcptHtml = '';
    if (d.rcpt_to) {
        const r = d.rcpt_to;
        const ic = r.result === 'exists' ? 'fa-circle-check text-success' : (r.result === 'not_exists' ? 'fa-circle-xmark text-danger' : 'fa-circle-question text-warning');
        const lbl = r.result === 'exists' ? 'Buzón existe' : (r.result === 'not_exists' ? 'Buzón no existe' : 'No determinado');
        rcptHtml = `<div class="mt-2 pt-2 border-top">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fa-solid ${ic} small"></i>
                <span class="small fw-semibold">RCPT TO: ${lbl}</span>
                ${r.code ? `<span class="ttl-badge">${r.code}</span>` : ''}
            </div>
            ${r.note ? `<div class="small text-muted">${esc(r.note)}</div>` : ''}
        </div>`;
    }
    document.getElementById('body-mail-score').innerHTML = `
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="mail-score-circle ${cls.replace('bg-','score-')}">
                <span class="score-num">${d.score}</span>
                <span class="score-den">/${d.score_max}</span>
            </div>
            <div>
                <div class="fw-bold">${txt}</div>
                <div class="progress mt-1" style="height:6px;width:140px">
                    <div class="progress-bar ${cls}" style="width:${pct}%"></div>
                </div>
                ${d.arsys ? '<span class="arsys-badge mt-1 d-inline-block"><i class="fa-solid fa-server me-1"></i>ARSYS</span>' : ''}
            </div>
        </div>
        <div class="row g-0">${rows}</div>${rcptHtml}`;
}

function renderMailMx(d) {
    const rows = (d.mx ?? []).map(m => {
        const arsys = m.arsys ? `<span class="arsys-badge ms-1"><i class="fa-solid fa-server me-1"></i>ARSYS</span>` : '';
        const rowCls = m.arsys ? 'arsys-highlight' : '';
        const ptrIcon = m.ptr_ok ? '<i class="fa-solid fa-circle-check text-success small ms-1" title="PTR válido"></i>'
                                  : (m.ptr ? '<i class="fa-solid fa-triangle-exclamation text-warning small ms-1" title="PTR no coincide"></i>' : '');
        return `<div class="dns-row d-flex align-items-start gap-2 ${rowCls}">
            <span class="badge dns-badge bg-warning text-dark">${m.priority}</span>
            <div class="flex-grow-1 min-w-0">
                <div class="dns-value">${esc(m.host)}${ptrIcon}${arsys}</div>
                ${m.ip   ? `<span class="ttl-badge">IP: ${esc(m.ip)}</span>` : ''}
                ${m.ptr  ? `<span class="ttl-badge ms-1">PTR: ${esc(m.ptr)}</span>` : ''}
            </div>
        </div>`;
    }).join('');
    document.getElementById('body-mail-mx').innerHTML = rows || '<p class="text-muted small mb-0">Sin registros MX.</p>';
}

function renderMailSmtp(d) {
    const rows = (d.smtp ?? []).map(s => {
        const cls  = s.open ? 'text-success' : 'text-muted';
        const icon = s.open ? 'fa-circle-check' : 'fa-circle-xmark';
        const tlsIcon = s.open && s.starttls
            ? `<span class="ttl-badge ms-1 text-success"><i class="fa-solid fa-lock me-1"></i>${s.port===465?'SSL/TLS':'STARTTLS'}</span>`
            : (s.open ? `<span class="ttl-badge ms-1 text-warning">Sin TLS</span>` : '');
        const caps = (s.capabilities ?? []).filter(c => c !== 'SSL/TLS implícito').slice(0, 6);
        const capsHtml = caps.length ? `<div class="mt-1">${caps.map(c => `<span class="ttl-badge me-1">${esc(c)}</span>`).join('')}</div>` : '';
        return `<div class="d-flex align-items-start gap-2 mb-2">
            <i class="fa-solid ${icon} ${cls} mt-1 small"></i>
            <div>
                <span class="fw-semibold small">${s.port} ${esc(s.label)}</span>
                ${s.open ? `<span class="ttl-badge ms-1">${s.ms} ms</span>` : ''}${tlsIcon}
                ${s.banner ? `<div class="dns-value small text-muted">${esc(s.banner)}</div>` : ''}
                ${capsHtml}
            </div>
        </div>`;
    }).join('');
    document.getElementById('body-mail-smtp').innerHTML = rows || '<p class="text-muted small mb-0">Sin datos SMTP.</p>';
}

function renderMailSpf(d) {
    const spf = d.spf;
    if (!spf?.exists) {
        document.getElementById('body-mail-spf').innerHTML =
            `<div class="d-flex align-items-center gap-2 text-danger">
                <i class="fa-solid fa-circle-xmark fa-lg"></i>
                <span class="fw-semibold">SPF no configurado</span>
             </div>
             <p class="small text-muted mt-2 mb-0">Sin registro SPF, el correo puede ser rechazado o marcado como spam.</p>`;
        return;
    }
    const strictIcon = spf.strict
        ? '<i class="fa-solid fa-circle-check text-success me-1"></i>'
        : '<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>';
    document.getElementById('body-mail-spf').innerHTML = `
        <div class="d-flex align-items-center gap-2 mb-2 text-success">
            <i class="fa-solid fa-circle-check fa-lg"></i>
            <span class="fw-semibold">SPF configurado</span>
            <span class="ttl-badge">${spf.strict ? 'Estricto' : 'Permisivo'}</span>
        </div>
        <div class="dns-value small p-2" style="background:var(--field-bg);border-radius:6px">${esc(spf.record)}</div>
        <p class="small text-muted mt-2 mb-0">${strictIcon}${spf.strict ? 'Política estricta (-all/~all)' : 'Considera usar -all o ~all para mayor seguridad'}</p>`;
}

function renderMailDmarc(d) {
    const dm = d.dmarc;
    if (!dm?.exists) {
        document.getElementById('body-mail-dmarc').innerHTML =
            `<div class="d-flex align-items-center gap-2 text-danger">
                <i class="fa-solid fa-circle-xmark fa-lg"></i>
                <span class="fw-semibold">DMARC no configurado</span>
             </div>
             <p class="small text-muted mt-2 mb-0">Sin DMARC el dominio es vulnerable a spoofing de correo.</p>`;
        return;
    }
    const polClass = dm.policy === 'reject' ? 'text-success' : (dm.policy === 'quarantine' ? 'text-warning' : 'text-danger');
    const polIcon  = dm.policy === 'reject' ? 'fa-shield-halved' : 'fa-triangle-exclamation';
    document.getElementById('body-mail-dmarc').innerHTML = `
        <div class="d-flex align-items-center gap-2 mb-2 text-success">
            <i class="fa-solid fa-circle-check fa-lg"></i>
            <span class="fw-semibold">DMARC configurado</span>
            <span class="ttl-badge ${polClass}">p=${esc(dm.policy ?? 'none')}</span>
        </div>
        <div class="dns-value small p-2" style="background:var(--field-bg);border-radius:6px">${esc(dm.record)}</div>
        <p class="small mt-2 mb-0 ${polClass}">
            <i class="fa-solid ${polIcon} me-1"></i>
            Política: <strong>${dm.policy ?? 'none'}</strong>
            ${dm.policy !== 'reject' ? ' — Se recomienda usar p=reject' : ' — Excelente'}
        </p>`;
}

function renderMailDkim(d) {
    const dkim = d.dkim ?? [];
    if (!dkim.length) {
        document.getElementById('body-mail-dkim').innerHTML =
            `<div class="d-flex align-items-center gap-2 text-warning">
                <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                <span class="fw-semibold">DKIM no detectado</span>
             </div>
             <p class="small text-muted mt-2 mb-0">No se encontró DKIM en los selectores más comunes. Puede existir con un selector personalizado.</p>`;
        return;
    }
    const rows = dkim.map(k =>
        `<div class="dns-row">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check text-success small"></i>
                <span class="fw-semibold small">Selector: <code>${esc(k.selector)}</code></span>
            </div>
            <div class="dns-value small mt-1" style="background:var(--field-bg);border-radius:4px;padding:4px 8px">${esc(k.value)}</div>
         </div>`
    ).join('');
    document.getElementById('body-mail-dkim').innerHTML =
        `<div class="d-flex align-items-center gap-2 mb-2 text-success">
            <i class="fa-solid fa-circle-check fa-lg"></i>
            <span class="fw-semibold">${dkim.length} selector${dkim.length > 1 ? 'es' : ''} DKIM encontrado${dkim.length > 1 ? 's' : ''}</span>
         </div>${rows}`;
}

function renderMailBlacklist(d) {
    const bl = d.blacklist;
    if (!bl) {
        document.getElementById('body-mail-blacklist').innerHTML =
            '<p class="text-muted small mb-0">No se pudo obtener la IP del MX para comprobar blacklists.</p>';
        return;
    }
    const badge = document.getElementById('badge-mail-blacklist');
    const dl    = document.getElementById('dl-mail-blacklist');
    if (bl.listed === 0) {
        badge.className = 'header-badge bg-success';
        dl.className    = 'btn btn-link p-0 text-success';
    } else {
        badge.className = 'header-badge bg-danger';
        dl.className    = 'btn btn-link p-0 text-danger';
    }
    const rows = (bl.results ?? []).map(r =>
        `<div class="bl-row d-flex align-items-center gap-2">
            <i class="fa-solid ${r.listed ? 'fa-circle-xmark text-danger' : 'fa-circle-check text-success'} small"></i>
            <span class="bl-name">${esc(r.name)}</span>
         </div>`
    ).join('');
    document.getElementById('body-mail-blacklist').innerHTML = `
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid ${bl.listed === 0 ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger'} fa-lg"></i>
            <span class="small fw-semibold ${bl.listed === 0 ? 'text-success' : 'text-danger'}">
                ${bl.listed === 0 ? `IP del MX limpia (${bl.total} listas)` : `Listada en ${bl.listed}/${bl.total}`}
            </span>
        </div>
        <details><summary class="small text-muted" style="cursor:pointer">Ver detalle</summary>
            <div class="mt-2">${rows}</div>
        </details>`;
}

// ── Blacklist IP directa (módulo diagnóstico reutilizado en Correo) ──────────
function renderMailIpbl(data) {
    const badge = document.getElementById('badge-mail-ipbl');
    const dl    = document.getElementById('dl-mail-ipbl');
    if (badge) badge.className = 'header-badge ' + (data.clean ? 'bg-success' : 'bg-danger');
    if (dl)    dl.className    = 'btn btn-link p-0 ' + (data.clean ? 'text-success' : 'text-danger');
    const rows = (data.results ?? []).map(r =>
        `<div class="bl-row d-flex align-items-center gap-2">
            <i class="fa-solid ${r.listed ? 'fa-circle-xmark text-danger' : 'fa-circle-check text-success'} small"></i>
            <span class="bl-name">${esc(r.name)}</span>
            ${r.rcode ? `<span class="ttl-badge ms-auto">${esc(r.rcode)}</span>` : ''}
         </div>`
    ).join('');
    const el = document.getElementById('body-mail-ipbl');
    if (!el) return;
    el.innerHTML = `
        <div class="d-flex align-items-center mb-2">
            <i class="fa-solid ${data.clean ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger'} fa-lg me-2"></i>
            <span class="small fw-semibold ${data.clean ? 'text-success' : 'text-danger'}">
                ${data.clean ? `IP limpia (${data.total} listas)` : `Listada en ${data.listed}/${data.total}`}
            </span>
        </div>
        <small class="text-muted d-block mb-2">IP: <code>${esc(data.ip)}</code></small>
        <details><summary class="small text-muted" style="cursor:pointer">Ver detalle</summary>
            <div class="mt-2">${rows}</div>
        </details>`;
}

// ── AbuseIPDB ─────────────────────────────────────────────────
let abuseExportData = null;

async function startAbuseCheck() {
    const domain = normalizeDomain();
    if (!domain) { alert('Introduce un dominio o IP en el buscador'); return; }
    document.getElementById('abuse-btn-text').classList.add('d-none');
    document.getElementById('abuse-btn-loading').classList.remove('d-none');
    document.getElementById('btn-abuse-check').disabled = true;
    document.getElementById('abuse-results').classList.remove('d-none');
    document.getElementById('body-mail-abuse').innerHTML =
        `<div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>`;
    try {
        const res  = await fetch(`api.php?module=abuseipdb&domain=${encodeURIComponent(domain)}`);
        const data = await res.json();
        abuseExportData = data;
        if (data.success) renderMailAbuse(data);
        else document.getElementById('body-mail-abuse').innerHTML =
            `<div class="alert alert-danger py-2 mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(data.error ?? 'Error')}</div>`;
    } catch(e) {
        document.getElementById('body-mail-abuse').innerHTML =
            `<div class="alert alert-danger py-2 mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>Error: ${esc(e.message)}</div>`;
    } finally {
        document.getElementById('abuse-btn-text').classList.remove('d-none');
        document.getElementById('abuse-btn-loading').classList.add('d-none');
        document.getElementById('btn-abuse-check').disabled = false;
    }
}

function renderMailAbuse(data) {
    const score  = data.abuseConfidenceScore ?? 0;
    const cls    = score === 0 ? 'text-success' : (score < 50 ? 'text-warning' : 'text-danger');
    const icon   = score === 0 ? 'fa-circle-check' : (score < 50 ? 'fa-triangle-exclamation' : 'fa-circle-xmark');
    const bCls   = score === 0 ? 'bg-success' : (score < 50 ? 'bg-warning text-dark' : 'bg-danger');
    const badge  = document.getElementById('badge-mail-abuse');
    if (badge) badge.className = 'header-badge ' + bCls;
    document.getElementById('body-mail-abuse').innerHTML = `
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="mail-score-circle ${score === 0 ? 'score-success' : score < 50 ? 'score-warning' : 'score-danger'}">
                <span class="score-num">${score}</span>
                <span class="score-den">/100</span>
            </div>
            <div>
                <div class="fw-bold ${cls}">
                    <i class="fa-solid ${icon} me-1"></i>
                    ${score === 0 ? 'Sin reportes de abuso' : score < 50 ? 'Actividad sospechosa' : 'IP con alto riesgo'}
                </div>
                <div class="small text-muted mt-1">AbuseIPDB Confidence Score</div>
                ${data.isWhitelisted ? '<span class="ttl-badge text-success">En whitelist</span>' : ''}
            </div>
        </div>
        <div class="row g-2">
            <div class="col-6"><div class="ssl-field">
                <span class="ssl-label">IP</span>
                <span class="ssl-val">${esc(data.ip)}</span>
            </div></div>
            <div class="col-6"><div class="ssl-field">
                <span class="ssl-label">Total reportes</span>
                <span class="ssl-val">${esc(data.totalReports)}</span>
            </div></div>
            <div class="col-6"><div class="ssl-field">
                <span class="ssl-label">Usuarios distintos</span>
                <span class="ssl-val">${esc(data.numDistinctUsers ?? '—')}</span>
            </div></div>
            <div class="col-6"><div class="ssl-field">
                <span class="ssl-label">País</span>
                <span class="ssl-val">${esc(data.countryCode ?? '—')}</span>
            </div></div>
            <div class="col-6"><div class="ssl-field">
                <span class="ssl-label">ISP</span>
                <span class="ssl-val" style="font-size:0.72rem">${esc(data.isp ?? '—')}</span>
            </div></div>
            <div class="col-6"><div class="ssl-field">
                <span class="ssl-label">Tipo de uso</span>
                <span class="ssl-val" style="font-size:0.72rem">${esc(data.usageType ?? '—')}</span>
            </div></div>
            <div class="col-12"><div class="ssl-field">
                <span class="ssl-label">Último reporte</span>
                <span class="ssl-val">${data.lastReportedAt ? esc(data.lastReportedAt.split('T')[0]) : 'Sin reportes'}</span>
            </div></div>
        </div>`;
}

function downloadAbuseReport() {
    if (!abuseExportData) return;
    const d = abuseExportData;
    const text = `[ABUSEIPDB — Correo/Abuse]\nIP: ${d.ip}\nScore: ${d.abuseConfidenceScore}/100\nReportes: ${d.totalReports}\nPaís: ${d.countryCode??'—'}\nISP: ${d.isp??'—'}\nÚltimo reporte: ${d.lastReportedAt??'—'}`;
    downloadText(text, `abuseipdb_${currentDomain}_${stamp()}.txt`);
}

// ── Fetch módulo diagnóstico ──────────────────────────────────
async function fetchModule(module, domain) {
    try {
        let url = `api.php?module=${module}&domain=${encodeURIComponent(domain)}`;
        if (module === 'dns') {
            const types = getSelectedDnsTypes();
            if (types.length) url += `&types=${encodeURIComponent(types.join(','))}`;
        }
        const res  = await fetch(url);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        exportData[module] = data;
        data.success ? renderCard(module, data) : setCardError(module, data.error ?? 'Error desconocido');
    } catch(e) { setCardError(module, 'Error de conexión: ' + e.message); }
}

function getSelectedDnsTypes() {
    return Array.from(document.querySelectorAll('#dns-chips input[type=checkbox]:checked')).map(c => c.value);
}

function setCardLoading(module) {
    document.getElementById('body-' + module).innerHTML =
        `<div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div><div class="skeleton-line"></div></div>`;
}
function setCardError(module, msg) {
    document.getElementById('body-' + module).innerHTML =
        `<div class="alert alert-danger py-2 mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(msg)}</div>`;
}

// ── Renderers diagnóstico ─────────────────────────────────────
function renderCard(module, data) {
    ({resolution:renderResolution,dns:renderDNS,ports:renderPorts,whois:renderWhois,
      ssl:renderSSL,ping:renderPing,headers:renderHeaders,blacklist:renderBlacklist,
      traceroute:renderTraceroute,redirect:renderRedirect,webinfo:renderWebInfo})[module]?.(data);
}

function arsysBadgeHtml() {
    return `<span class="arsys-badge ms-1"><i class="fa-solid fa-server me-1"></i>ARSYS</span>`;
}

function renderResolution(data) {
    const ip  = data.ip      ?? 'No resolvió';
    const rev = data.reverse ?? '—';
    const hc  = data.arsys ? 'arsys-highlight' : '';
    const ab  = data.arsys ? arsysBadgeHtml() : '';
    document.getElementById('body-resolution').innerHTML = `
        <div class="dns-row d-flex align-items-center gap-2 border-0 ${hc}">
            <span class="badge dns-badge bg-primary">IP</span>
            <div class="dns-value text-primary fw-bold flex-grow-1">${esc(ip)}</div>${ab}
        </div>
        <div class="dns-row d-flex align-items-start gap-2 border-0 ${hc}">
            <span class="badge dns-badge bg-dark">HOST</span>
            <div class="dns-value text-secondary small flex-grow-1">${esc(rev)}</div>${ab}
        </div>`;
}

function renderDNS(data) {
    const records = data.records ?? [];
    if (!records.length) {
        document.getElementById('body-dns').innerHTML = '<p class="text-muted small mb-0">No se encontraron registros para los tipos seleccionados.</p>';
        return;
    }
    const groups = {};
    for (const r of records) { if (!groups[r.type]) groups[r.type] = []; groups[r.type].push(r); }
    const order = ['A','AAAA','CNAME','MX','NS','TXT','SPF','DMARC','DKIM','CAA','SOA','SRV','MTA-STS','BIMI'];
    const types = order.filter(t => groups[t]);
    const half  = Math.ceil(types.length / 2);
    let html = '<div class="row g-0">';
    for (let col = 0; col < 2; col++) {
        const ct = types.slice(col * half, (col + 1) * half);
        if (!ct.length) break;
        html += `<div class="col-12 col-md-6 ${col === 0 ? 'pe-md-2 dns-col-border' : 'ps-md-2'}">`;
        for (const type of ct) {
            html += `<div class="dns-type-label">${type} Records</div>`;
            for (const r of groups[type]) {
                const bc  = DNS_COLORS[type] ?? 'bg-dark';
                const pri = r.priority  != null ? `<span class="text-muted small ms-1">prio ${r.priority}</span>` : '';
                const ttl = r.ttl       != null ? `<span class="ttl-badge">TTL ${r.ttl}s</span>` : '';
                const sel = r.selector  != null ? `<span class="ttl-badge ms-1">sel: ${esc(r.selector)}</span>` : '';
                const ab  = r.arsys     ? arsysBadgeHtml() : '';
                const rc  = r.arsys     ? 'arsys-highlight' : '';
                html += `<div class="dns-row d-flex align-items-start gap-2 ${rc}">
                    <span class="badge dns-badge ${bc}">${type}</span>
                    <div class="flex-grow-1 min-w-0">
                        <div class="dns-value">${esc(r.value)}${pri}${ab}</div>${ttl}${sel}
                    </div></div>`;
            }
        }
        html += '</div>';
    }
    document.getElementById('body-dns').innerHTML = html + '</div>';
}

function renderPorts(data) {
    let html = '';
    for (const cat of (data.categories ?? [])) {
        html += `<div class="port-group-title">${esc(cat.category)}</div><div class="row g-1 mb-1">`;
        for (const p of cat.ports) {
            html += `<div class="col-6 col-sm-4"><div class="port-row">
                <span class="port-label ${p.open?'open':''}">${esc(p.label)}</span>
                <span class="port-number ${p.open?'open':''}">${p.port}</span>
            </div></div>`;
        }
        html += '</div>';
    }
    document.getElementById('body-ports').innerHTML = html;
}

function renderSSL(data) {
    const expired = data.expired, warning = data.warning, soon = data.soon;
    let stClass, stIcon, badgeCls;
    if (expired)      { stClass='text-danger';  stIcon='fa-circle-xmark';        badgeCls='bg-danger'; }
    else if (warning) { stClass='text-danger';  stIcon='fa-triangle-exclamation'; badgeCls='bg-danger'; }
    else if (soon)    { stClass='text-warning'; stIcon='fa-triangle-exclamation'; badgeCls='bg-warning text-dark'; }
    else              { stClass='text-success'; stIcon='fa-circle-check';         badgeCls='bg-success'; }
    const daysMsg = expired ? `Expirado hace ${Math.abs(data.days_left)} días` : `Expira en ${data.days_left} días`;
    document.getElementById('badge-ssl').className = 'header-badge ' + badgeCls;
    document.getElementById('dl-ssl').className    = 'btn btn-link p-0 ' + stClass;
    document.getElementById('body-ssl').innerHTML = `
        <div class="ssl-status ${stClass} mb-3"><i class="fa-solid ${stIcon} me-2 fa-lg"></i><strong>${daysMsg}</strong></div>
        <div class="ssl-grid">
            <div class="ssl-field"><span class="ssl-label">Dominio</span><span class="ssl-val">${esc(data.subject)}</span></div>
            <div class="ssl-field"><span class="ssl-label">Emisor</span><span class="ssl-val">${esc(data.issuer)}</span></div>
            <div class="ssl-field"><span class="ssl-label">Válido desde</span><span class="ssl-val">${esc(data.valid_from)}</span></div>
            <div class="ssl-field"><span class="ssl-label">Expira</span><span class="ssl-val ${stClass} fw-bold">${esc(data.valid_to)}</span></div>
        </div>`;
}

function renderWhois(data) {
    document.getElementById('body-whois').innerHTML = `<div class="whois-scroll">${esc(data.data)}</div>`;
}

function renderPing(data) {
    const loss=data.packet_loss??0;
    const lc=loss===0?'text-success':(loss===100?'text-danger':'text-warning');
    const li=loss===0?'fa-circle-check':(loss===100?'fa-circle-xmark':'fa-triangle-exclamation');
    let stats='';
    if (data.avg_ms!==null) stats=`
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <div class="ping-stat"><div class="ping-stat-val">${data.avg_ms} ms</div><div class="ping-stat-label">latencia avg</div></div>
            <div class="ping-stat"><div class="ping-stat-val ${lc}"><i class="fa-solid ${li} me-1 small"></i>${loss}%</div><div class="ping-stat-label">pérdida</div></div>
        </div>`;
    document.getElementById('body-ping').innerHTML=`${stats}
        <details><summary class="small text-muted" style="cursor:pointer">Ver salida completa</summary>
        <pre class="terminal mt-2">${esc(data.output)}</pre></details>`;
}

function renderHeaders(data) {
    const pct=Math.round((data.score/data.total)*100);
    const bc=pct>=70?'bg-success':(pct>=40?'bg-warning':'bg-danger');
    let info='';
    if(data.server) info+=`<span class="ttl-badge me-1">Server: ${esc(data.server)}</span>`;
    if(data.powered_by) info+=`<span class="ttl-badge me-1">X-Powered-By: ${esc(data.powered_by)}</span>`;
    if(data.status_code) info+=`<span class="ttl-badge me-1">HTTP ${data.status_code}</span>`;
    const rows=(data.headers??[]).map(h=>`
        <div class="hdr-row d-flex align-items-start gap-2">
            <div class="mt-1"><i class="fa-solid ${h.present?'fa-circle-check text-success':'fa-circle-xmark text-danger'}"></i></div>
            <div class="flex-grow-1 min-w-0">
                <div class="hdr-label">${esc(h.label)}</div>
                <div class="hdr-desc">${esc(h.desc)}</div>
                ${h.present&&h.value?`<div class="dns-value small text-muted mt-1" style="font-size:.7rem">${esc(h.value)}</div>`:''}
            </div>
        </div>`).join('');
    document.getElementById('body-headers').innerHTML=`
        <div class="mb-2 d-flex align-items-center gap-2">
            <div class="flex-grow-1"><div class="progress" style="height:6px"><div class="progress-bar ${bc}" style="width:${pct}%"></div></div></div>
            <small class="fw-bold">${data.score}/${data.total}</small>
        </div>
        ${info?`<div class="mb-2">${info}</div>`:''}
        <div class="hdr-list">${rows}</div>`;
}

function renderBlacklist(data) {
    const badge=document.getElementById('badge-blacklist');
    const dl=document.getElementById('dl-blacklist');
    badge.className='header-badge '+(data.clean?'bg-success':'bg-danger');
    dl.className='btn btn-link p-0 '+(data.clean?'text-success':'text-danger');
    const rows=(data.results??[]).map(r=>`
        <div class="bl-row d-flex align-items-center gap-2">
            <i class="fa-solid ${r.listed?'fa-circle-xmark text-danger':'fa-circle-check text-success'} small"></i>
            <span class="bl-name">${esc(r.name)}</span>
            ${r.rcode?`<span class="ttl-badge ms-auto">${esc(r.rcode)}</span>`:''}
        </div>`).join('');
    document.getElementById('body-blacklist').innerHTML=`
        <div class="d-flex align-items-center mb-2">
            <i class="fa-solid ${data.clean?'fa-circle-check text-success':'fa-circle-xmark text-danger'} fa-lg me-2"></i>
            <span class="small">${data.clean?`IP limpia (${data.total} listas)`:`Listada en ${data.listed}/${data.total}`}</span>
        </div>
        <small class="text-muted d-block mb-2">IP: <code>${esc(data.ip)}</code></small>
        <details><summary class="small text-muted" style="cursor:pointer">Ver detalle</summary><div class="mt-2">${rows}</div></details>`;
}

function renderTraceroute(data) {
    const rows=(data.hops??[]).map(h=>`
        <div class="tr-row d-flex align-items-center gap-2">
            <span class="tr-hop">${h.hop}</span>
            <span class="dns-value small flex-grow-1">${h.timeout?'<span class="text-muted">* * *</span>':esc(h.ip??'?')}</span>
            ${h.ms!=null?`<span class="ttl-badge">${h.ms} ms`:''}
        </div>`).join('');
    document.getElementById('body-traceroute').innerHTML=`
        <div class="small text-muted mb-2">${data.count} saltos</div>
        <div class="tr-list">${rows}</div>
        <details class="mt-2"><summary class="small text-muted" style="cursor:pointer">Ver salida completa</summary>
        <pre class="terminal mt-2">${esc(data.output)}</pre></details>`;
}

function renderRedirect(data) {
    const cc=c=>(c>=200&&c<300?'text-success':c>=300&&c<400?'text-warning':'text-danger');
    const steps=(data.chain??[]).map((s,i)=>`
        <div class="rd-step"><span class="rd-code ${cc(s.code)}">${s.code}</span>
            <span class="rd-url">${esc(s.url)}</span>
            <span class="ttl-badge ms-auto">${s.ms} ms</span>
        </div>${i<data.chain.length-1?'<div class="rd-arrow">↓</div>':''}`).join('');
    document.getElementById('body-redirect').innerHTML=`
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <div class="ping-stat"><div class="ping-stat-val">${data.hops}</div><div class="ping-stat-label">saltos</div></div>
            <div class="ping-stat"><div class="ping-stat-val">${data.total_ms} ms</div><div class="ping-stat-label">total</div></div>
            <div class="ping-stat"><div class="ping-stat-val">${data.has_https?'<i class="fa-solid fa-lock text-success me-1"></i>HTTPS':'HTTP'}</div><div class="ping-stat-label">destino</div></div>
        </div><div class="rd-chain">${steps}</div>`;
}

// ── Exportar ──────────────────────────────────────────────────
function downloadCard(module) {
    const data = exportData[module];
    if (!data) return;
    downloadText(formatExport(module, data), `${module}_${currentDomain}_${stamp()}.txt`);
}
function downloadMailCard(key) {
    const data = mailExport['mailtest'];
    if (!data) return;
    downloadText(formatMailExport(key, data), `mail_${key}_${currentDomain}_${stamp()}.txt`);
}
function exportAll() {
    let text = `REPORTE CUAKCOM EXPERT v<?= APP_VERSION ?>\nDominio: ${currentDomain}\nFecha: ${new Date().toLocaleString('es-ES')}\n${'='.repeat(50)}\n\n`;
    for (const [mod, data] of Object.entries(exportData)) text += formatExport(mod, data) + '\n\n';
    downloadText(text, `reporte_${currentDomain}_${stamp()}.txt`);
}

function formatExport(module, data) {
    if (!data?.success) return `[${module.toUpperCase()}]\nError: ${data?.error??'desconocido'}\n`;
    switch(module) {
        case 'resolution': return `[RESOLUCIÓN]\nIP: ${data.ip??'N/A'}\nHost: ${data.reverse??'N/A'}${data.arsys?'\n⚠ ARSYS':''}`;
        case 'dns':        return `[DNS]\n`+(data.records??[]).map(r=>`${r.type.padEnd(8)} ${r.value}${r.selector?` (${r.selector})`:''}`).join('\n');
        case 'ports':      return `[PUERTOS]\n`+(data.categories??[]).map(c=>`${c.category}:\n`+c.ports.map(p=>`  ${p.port} ${p.label} ${p.open?'OPEN':'CLOSED'}`).join('\n')).join('\n\n');
        case 'ssl':        return `[SSL]\n${data.subject}\n${data.issuer}\n${data.valid_from} → ${data.valid_to}\n${data.days_left} días`;
        case 'whois':      return `[WHOIS]\n${data.data}`;
        case 'ping':       return `[PING]\n${data.avg_ms} ms / ${data.packet_loss}% pérdida`;
        case 'headers':    return `[CABECERAS]\n${(data.headers??[]).map(h=>`${h.present?'✓':'✗'} ${h.label}`).join('\n')}`;
        case 'blacklist':  return `[BLACKLIST]\nIP: ${data.ip}\n${data.clean?'LIMPIA':'LISTADA en '+data.listed}`;
        case 'traceroute': return `[TRACEROUTE]\n${data.output}`;
        case 'redirect':   return `[REDIRECCIONES]\n`+(data.chain??[]).map((s,i)=>`${i+1}. [${s.code}] ${s.url}`).join('\n');
        case 'webinfo':    return `[WEB INFO]\nScreenshot: ${data.screenshot_url??''}\nWayback: ${data.wayback?.first_date??'?'} → ${data.wayback?.last_date??'?'}\nTranco: #${data.tranco?.rank??'N/A'}`;
        default:           return `[${module.toUpperCase()}]\n${JSON.stringify(data,null,2)}`;
    }
}
function formatMailExport(key, data) {
    switch(key) {
        case 'score': return `[SCORE]\n${data.score}/${data.score_max}`;
        case 'mx':    return `[MX]\n`+(data.mx??[]).map(m=>`${m.priority} ${m.host} (${m.ip??'?'})`).join('\n');
        case 'smtp':  return `[SMTP]\n`+(data.smtp??[]).map(s=>`${s.port} ${s.open?'OPEN':'CLOSED'}`).join('\n');
        case 'spf':   return `[SPF]\n${data.spf?.record??'No configurado'}`;
        case 'dmarc': return `[DMARC]\n${data.dmarc?.record??'No configurado'}`;
        case 'dkim':  return `[DKIM]\n`+(data.dkim??[]).map(k=>`${k.selector}: ${k.value}`).join('\n\n');
        case 'blacklist': return `[BLACKLIST MX]\n${data.blacklist?.listed===0?'Limpia':'Listada en '+data.blacklist?.listed}`;
        default: return JSON.stringify(data,null,2);
    }
}

function downloadText(content, filename) {
    const blob = new Blob([content], {type:'text/plain;charset=utf-8'});
    const url  = URL.createObjectURL(blob);
    const a    = Object.assign(document.createElement('a'), {href:url, download:filename});
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function normalizeDomain() {
    const raw = document.getElementById('input-domain').value.trim();
    return raw.replace(/^https?:\/\/(www\.)?/i,'').split('/')[0].split('?')[0]
              .replace(/[^a-zA-Z0-9.\-]/g,'').toLowerCase();
}

function esc(str) {
    if (str==null) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function stamp() { return new Date().toISOString().replace(/[:.]/g,'-').slice(0,19); }

// ── Web Info renderer ─────────────────────────────────────────
function renderWebInfo(data) {
    const ss = data.screenshot_url;
    const wb = data.wayback ?? {};
    const tr = data.tranco ?? {};
    const ms = data.response_ms ?? null;

    let ssHtml = '';
    if (ss) ssHtml = `<a href="${esc(ss)}" target="_blank" rel="noopener">
        <img src="${esc(ss)}" alt="Captura web" class="webinfo-screenshot img-fluid rounded mb-3"
             onerror="this.closest('.webinfo-screenshot-wrap').classList.add('d-none')">
        </a>`;

    const stats = [];
    if (ms !== null) stats.push(['<i class="fa-solid fa-bolt me-1"></i>Respuesta', ms + ' ms']);
    if (wb.first_date) stats.push(['<i class="fa-solid fa-calendar me-1"></i>Primera vez en Wayback', esc(wb.first_date)]);
    if (wb.last_date)  stats.push(['<i class="fa-solid fa-history me-1"></i>Última captura Wayback', esc(wb.last_date)]);
    if (wb.snapshots)  stats.push(['<i class="fa-solid fa-camera me-1"></i>Capturas archivadas', esc(wb.snapshots)]);
    if (tr.rank)       stats.push(['<i class="fa-solid fa-trophy me-1"></i>Tranco rank', '#' + esc(tr.rank)]);

    const statsHtml = stats.length
        ? `<div class="webinfo-stats">${stats.map(([k,v]) =>
            `<div class="webinfo-stat"><div class="webinfo-stat-label">${k}</div><div class="webinfo-stat-val">${v}</div></div>`
          ).join('')}</div>`
        : '';

    const notes = data.notes ?? [];
    const notesHtml = notes.length
        ? `<div class="mt-2">${notes.map(n => `<div class="small text-muted"><i class="fa-solid fa-circle-info me-1"></i>${esc(n)}</div>`).join('')}</div>`
        : '';

    document.getElementById('body-webinfo').innerHTML =
        `<div class="webinfo-screenshot-wrap">${ssHtml}</div>${statsHtml}${notesHtml}`;
}

// ── DNS Query tab ─────────────────────────────────────────────
let lastDnsQueryData = null;

function toggleCustomDns() {
    const sel = document.getElementById('dnsq-server').value;
    document.getElementById('dnsq-custom-wrap').style.display = sel === 'custom' ? '' : 'none';
}

async function startDnsQuery() {
    const rawDomain = (document.getElementById('dnsq-domain').value.trim() || normalizeDomain());
    const domain = rawDomain.replace(/^https?:\/\/(www\.)?/i,'').split('/')[0].split('?')[0].toLowerCase();
    if (!domain) { alert('Introduce un dominio'); return; }
    const type   = document.getElementById('dnsq-type').value;
    const selVal = document.getElementById('dnsq-server').value;
    const server = selVal === 'custom' ? document.getElementById('dnsq-custom').value.trim() : selVal;
    const port   = parseInt(document.getElementById('dnsq-port').value) || 53;

    document.getElementById('dnsq-btn-text').classList.add('d-none');
    document.getElementById('dnsq-btn-loading').classList.remove('d-none');
    document.getElementById('dnsq-results').classList.remove('d-none');
    document.getElementById('body-dnsq').innerHTML =
        `<div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>`;

    try {
        const url = `api.php?module=dnsquery&domain=${encodeURIComponent(domain)}&type=${encodeURIComponent(type)}&server=${encodeURIComponent(server)}&port=${port}`;
        const res  = await fetch(url);
        const data = await res.json();
        lastDnsQueryData = data;
        renderDnsQuery(data, domain, type, server, port);
    } catch(e) {
        document.getElementById('body-dnsq').innerHTML =
            `<div class="alert alert-danger py-2 mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>Error: ${esc(e.message)}</div>`;
    } finally {
        document.getElementById('dnsq-btn-text').classList.remove('d-none');
        document.getElementById('dnsq-btn-loading').classList.add('d-none');
    }
}

function renderDnsQuery(data, domain, type, server, port) {
    if (!data.success) {
        document.getElementById('body-dnsq').innerHTML =
            `<div class="alert alert-danger py-2 mb-0 small">${esc(data.error ?? 'Error desconocido')}</div>`;
        return;
    }
    const meta = `${esc(domain)} ${esc(type)} @ ${esc(server)}${port !== 53 ? ':'+port : ''}`;
    document.getElementById('dnsq-meta').textContent = meta;

    const records = data.records ?? [];
    let recHtml = '';
    if (records.length) {
        recHtml = records.map(r => {
            const bc = DNS_COLORS[r.type] ?? 'bg-dark';
            return `<div class="dns-row d-flex align-items-start gap-2">
                <span class="badge dns-badge ${bc}">${esc(r.type)}</span>
                <div class="flex-grow-1 min-w-0">
                    <div class="dns-value">${esc(r.value)}</div>
                    ${r.ttl != null ? `<span class="ttl-badge">TTL ${r.ttl}s</span>` : ''}
                </div>
            </div>`;
        }).join('');
    } else {
        recHtml = '<p class="text-muted small mb-0">Sin respuesta (NXDOMAIN o tipo no encontrado).</p>';
    }

    const metaInfo = [];
    if (data.query_time_ms != null) metaInfo.push(`<span class="ttl-badge">Tiempo: ${data.query_time_ms} ms</span>`);
    if (data.status)               metaInfo.push(`<span class="ttl-badge">Status: ${esc(data.status)}</span>`);
    if (data.server_used)          metaInfo.push(`<span class="ttl-badge">Servidor: ${esc(data.server_used)}</span>`);
    if (data.source)               metaInfo.push(`<span class="ttl-badge">Fuente: ${esc(data.source)}</span>`);

    const rawHtml = data.raw_output
        ? `<details class="mt-3"><summary class="small text-muted" style="cursor:pointer"><i class="fa-solid fa-terminal me-1"></i>Salida raw dig</summary>
            <pre class="terminal mt-2">${esc(data.raw_output)}</pre></details>`
        : '';

    document.getElementById('body-dnsq').innerHTML =
        `<div class="mb-2">${metaInfo.join(' ')}</div>${recHtml}${rawHtml}`;
}

function downloadDnsQuery() {
    if (!lastDnsQueryData) return;
    const lines = [`[DNS QUERY]\n`];
    (lastDnsQueryData.records ?? []).forEach(r => lines.push(`${r.type.padEnd(8)} ${r.value}`));
    if (lastDnsQueryData.raw_output) lines.push('\n--- RAW ---\n' + lastDnsQueryData.raw_output);
    downloadText(lines.join('\n'), `dnsquery_${stamp()}.txt`);
}

// ── EML upload & render ───────────────────────────────────────
let emlData = null;

async function uploadEml(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('eml-filename').textContent = file.name;
    document.getElementById('eml-results').classList.remove('d-none');
    document.getElementById('body-eml').innerHTML =
        `<div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>`;

    try {
        const fd = new FormData();
        fd.append('eml', file);
        const res  = await fetch('emlparse.php', {method:'POST', body: fd});
        const data = await res.json();
        emlData = data;
        if (data.success) renderEmlHeaders(data);
        else document.getElementById('body-eml').innerHTML =
            `<div class="alert alert-danger py-2 mb-0 small">${esc(data.error ?? 'Error')}</div>`;
    } catch(e) {
        document.getElementById('body-eml').innerHTML =
            `<div class="alert alert-danger py-2 mb-0 small">Error: ${esc(e.message)}</div>`;
    }
    input.value = '';
}

function renderEmlHeaders(data) {
    // Summary row
    const authBadge = (result, label) => {
        if (!result) return '';
        const cls = result === 'pass' ? 'bg-success' : (result === 'fail' ? 'bg-danger' : 'bg-warning text-dark');
        return `<span class="badge ${cls} me-1">${label}: ${esc(result)}</span>`;
    };
    let summaryHtml = `
        <div class="eml-summary mb-3">
            ${data.subject  ? `<div class="mb-1"><span class="eml-field-label">Asunto:</span> <span class="fw-semibold">${esc(data.subject)}</span></div>` : ''}
            ${data.from     ? `<div class="mb-1"><span class="eml-field-label">De:</span> ${esc(data.from)}</div>` : ''}
            ${data.to       ? `<div class="mb-1"><span class="eml-field-label">Para:</span> ${esc(data.to)}</div>` : ''}
            ${data.date     ? `<div class="mb-1"><span class="eml-field-label">Fecha:</span> ${esc(data.date)}</div>` : ''}
            <div class="mt-2">
                ${authBadge(data.auth?.spf,  'SPF')}
                ${authBadge(data.auth?.dkim, 'DKIM')}
                ${authBadge(data.auth?.dmarc,'DMARC')}
                ${data.spam_score != null ? `<span class="ttl-badge">Spam score: ${data.spam_score}</span>` : ''}
                <span class="ttl-badge ms-1">${data.total_headers} cabeceras</span>
                <span class="ttl-badge ms-1">${data.hops} saltos</span>
            </div>
        </div>`;

    // Received chain
    let chainHtml = '';
    if ((data.received ?? []).length) {
        chainHtml = `<div class="mb-3">
            <div class="dns-type-label">Ruta del mensaje (${data.hops} saltos)</div>
            ${data.received.map((r, i) =>
                `<div class="rd-step mb-1">
                    <span class="rd-code text-muted">${i+1}</span>
                    <span class="rd-url">${esc(r)}</span>
                </div>`
            ).join('<div class="rd-arrow">↓</div>')}
        </div>`;
    }

    // Group headers by category
    const cats = {};
    for (const h of (data.headers ?? [])) {
        if (!cats[h.category]) cats[h.category] = [];
        cats[h.category].push(h);
    }
    const catOrder = ['Autenticación','Origen','Destino','Spam','Fecha','Identificación','Hilo','Formato','Software','Red','Lista','Otras'];
    const catColors = {
        'Autenticación':'#1d4ed8','Origen':'#7c3aed','Destino':'#0891b2','Spam':'#dc2626',
        'Fecha':'#0f766e','Identificación':'#374151','Hilo':'#92400e','Formato':'#065f46',
        'Software':'#be185d','Red':'#0369a1','Lista':'#6d28d9','Otras':'#64748b',
    };
    let headersHtml = '<div class="eml-headers-grid">';
    for (const cat of catOrder) {
        if (!cats[cat]) continue;
        const color = catColors[cat] ?? '#64748b';
        headersHtml += `<div class="eml-cat-block">
            <div class="eml-cat-title" style="color:${color}">${esc(cat)}</div>`;
        for (const h of cats[cat]) {
            headersHtml += `<div class="eml-header-row">
                <div class="eml-header-name">${esc(h.name)}</div>
                <div class="eml-header-value dns-value">${esc(h.value)}</div>
                ${h.desc ? `<div class="eml-header-desc">${esc(h.desc)}</div>` : ''}
            </div>`;
        }
        headersHtml += '</div>';
    }
    headersHtml += '</div>';

    document.getElementById('body-eml').innerHTML = summaryHtml + chainHtml + headersHtml;
}

function downloadEmlReport() {
    if (!emlData) return;
    let text = `ANÁLISIS EML\nAsunto: ${emlData.subject ?? ''}\nDe: ${emlData.from ?? ''}\nPara: ${emlData.to ?? ''}\nFecha: ${emlData.date ?? ''}\n\n`;
    text += `SPF: ${emlData.auth?.spf ?? '—'}  DKIM: ${emlData.auth?.dkim ?? '—'}  DMARC: ${emlData.auth?.dmarc ?? '—'}\n\n`;
    text += `CABECERAS (${emlData.total_headers}):\n`;
    (emlData.headers ?? []).forEach(h => { text += `${h.name}: ${h.value}\n`; });
    downloadText(text, `eml_report_${stamp()}.txt`);
}

// ── SPF Validator ─────────────────────────────────────────────
let lastSpfData = null;

async function startSpfCheck() {
    const domain = (document.getElementById('spf-domain').value.trim() || normalizeDomain());
    const ip     = document.getElementById('spf-ip').value.trim();
    if (!domain) { alert('Introduce un dominio'); return; }
    if (!ip)     { alert('Introduce una IP'); return; }

    document.getElementById('spf-btn-text').classList.add('d-none');
    document.getElementById('spf-btn-loading').classList.remove('d-none');
    document.getElementById('spf-results').classList.remove('d-none');
    document.getElementById('spf-results').scrollIntoView({behavior:'smooth', block:'start'});
    document.getElementById('body-spf').innerHTML = skeletonHtml();
    document.getElementById('spf-meta').textContent = '';

    try {
        const res  = await fetch(`api.php?module=spfcheck&domain=${encodeURIComponent(domain)}&ip=${encodeURIComponent(ip)}`);
        const data = await res.json();
        lastSpfData = data;
        data.success ? renderSpfCheck(data) : setBodyErr('spf', data.error);
    } catch(e) {
        setBodyErr('spf', e.message);
    } finally {
        document.getElementById('spf-btn-text').classList.remove('d-none');
        document.getElementById('spf-btn-loading').classList.add('d-none');
    }
}

function renderSpfCheck(d) {
    document.getElementById('spf-meta').textContent = `${d.domain} · ${d.ip}`;

    const RESULT_CFG = {
        pass:      { icon: 'fa-circle-check',       cls: 'text-success', label: 'PASS',      desc: 'La IP está autorizada para enviar correo en nombre de este dominio.' },
        fail:      { icon: 'fa-circle-xmark',        cls: 'text-danger',  label: 'FAIL',      desc: 'La IP NO está autorizada. El correo debe ser rechazado.' },
        softfail:  { icon: 'fa-triangle-exclamation',cls: 'text-warning', label: 'SOFTFAIL',  desc: 'La IP no está autorizada pero el dominio prefiere no rechazar (solo marcar como sospechoso).' },
        neutral:   { icon: 'fa-circle-question',     cls: 'text-muted',   label: 'NEUTRAL',   desc: 'El dominio no hace ninguna afirmación sobre esta IP.' },
        none:      { icon: 'fa-circle-minus',         cls: 'text-muted',   label: 'NONE',      desc: 'No existe ningún registro SPF para este dominio.' },
        permerror: { icon: 'fa-triangle-exclamation', cls: 'text-danger',  label: 'PERMERROR', desc: 'Error permanente evaluando el SPF (demasiados lookups, recursión o registro mal formado).' },
        temperror: { icon: 'fa-circle-exclamation',   cls: 'text-warning', label: 'TEMPERROR', desc: 'Error temporal durante la evaluación (problema DNS transitorio).' },
    };

    const cfg = RESULT_CFG[d.result] ?? RESULT_CFG.neutral;

    // SPF raw record
    const spfHtml = d.spf_raw
        ? `<div class="spf-raw-block mt-3"><div class="seo-label">Registro SPF</div><code class="small">${esc(d.spf_raw)}</code></div>`
        : `<div class="small text-muted mt-2"><i class="fa-solid fa-circle-info me-1"></i>No se encontró registro SPF para <strong>${esc(d.domain)}</strong>.</div>`;

    // Trace table
    let traceHtml = '';
    if ((d.trace ?? []).length) {
        const rows = d.trace.map(t => {
            if (t.mechanism) {
                const matchIcon = t.matched
                    ? '<i class="fa-solid fa-circle-check text-success small me-1"></i>'
                    : '<i class="fa-solid fa-circle-xmark text-muted small me-1"></i>';
                const qual = t.qualifier ? `<span class="ttl-badge ms-1">${{'+':'pass','-':'fail','~':'softfail','?':'neutral'}[t.qualifier] ?? t.qualifier}</span>` : '';
                const note = t.note ? `<span class="small text-muted ms-2">(${esc(t.note)})</span>` : '';
                const sub  = t.sub_result ? `<span class="ttl-badge ms-1">${esc(t.sub_result)}</span>` : '';
                const ptr  = t.ptr ? `<span class="small text-muted ms-2">PTR: ${esc(t.ptr)}</span>` : '';
                return `<tr class="${t.matched ? 'spf-trace-match' : ''}">
                    <td>${matchIcon}<code class="small">${esc(t.mechanism)}</code>${note}${ptr}</td>
                    <td>${qual}${sub}</td>
                </tr>`;
            }
            if (t.domain) {
                return `<tr class="spf-trace-domain">
                    <td colspan="2"><i class="fa-solid fa-arrow-right me-1 text-muted small"></i><strong>${esc(t.domain)}</strong>
                    ${t.spf ? `<code class="small text-muted ms-2">${esc(t.spf.slice(0, 80))}${t.spf.length > 80 ? '…' : ''}</code>` : '<span class="small text-muted ms-2">sin SPF</span>'}
                    </td>
                </tr>`;
            }
            return '';
        }).join('');
        traceHtml = `
            <div class="mt-3 pt-2 border-top">
                <div class="seo-label mb-2">Evaluación de mecanismos <span class="ttl-badge ms-1">${d.lookups} lookup${d.lookups !== 1 ? 's' : ''} DNS</span></div>
                <div class="table-responsive">
                    <table class="spf-trace-table w-100"><tbody>${rows}</tbody></table>
                </div>
            </div>`;
    }

    document.getElementById('body-spf').innerHTML = `
        <div class="d-flex align-items-start gap-3 mb-2">
            <i class="fa-solid ${cfg.icon} ${cfg.cls} fa-2x mt-1"></i>
            <div>
                <div class="fw-bold fs-5 ${cfg.cls}">${cfg.label}</div>
                <div class="small mt-1">${cfg.desc}</div>
                <div class="small text-muted mt-1">
                    <span class="ttl-badge me-1">${esc(d.ip_type)}</span>
                    Mecanismo coincidente: <code>${esc(d.matched ?? '—')}</code>
                </div>
            </div>
        </div>
        ${spfHtml}
        ${traceHtml}`;
}

function downloadSpfCheck() {
    if (!lastSpfData) return;
    const d = lastSpfData;
    let t = `[SPF CHECK] ${d.domain} · IP: ${d.ip}\nResultado: ${d.result.toUpperCase()}\nCoincidencia: ${d.matched}\nLookups DNS: ${d.lookups}\n\nRegistro SPF:\n${d.spf_raw ?? '(ninguno)'}\n\nEvaluación:\n`;
    (d.trace ?? []).forEach(e => {
        if (e.mechanism) t += `  ${e.matched ? '✓' : '✗'} ${e.mechanism}\n`;
        if (e.domain)    t += `→ ${e.domain}: ${e.spf ?? 'sin SPF'}\n`;
    });
    downloadText(t, `spf_check_${d.domain}_${stamp()}.txt`);
}

// ── Red & IP tab ─────────────────────────────────────────────
let lastPropData = null;

async function startGeoIp() {
    const input = (document.getElementById('red-input').value.trim() || normalizeDomain());
    if (!input) { alert('Introduce una IP o dominio'); return; }
    document.getElementById('geo-btn-text').classList.add('d-none');
    document.getElementById('geo-btn-loading').classList.remove('d-none');
    document.getElementById('red-results').classList.remove('d-none');
    document.getElementById('body-geoip').innerHTML   = skeletonHtml();
    document.getElementById('body-whoisip').innerHTML = skeletonHtml();
    document.getElementById('red-results').scrollIntoView({behavior:'smooth', block:'start'});
    try {
        const enc = encodeURIComponent(input);
        const [geoRes, whoisRes] = await Promise.allSettled([
            fetch(`api.php?module=geoip&domain=${enc}`).then(r => r.json()),
            fetch(`api.php?module=whois&domain=${enc}`).then(r => r.json()),
        ]);
        const geo = geoRes.status === 'fulfilled' ? geoRes.value : null;
        const wh  = whoisRes.status === 'fulfilled' ? whoisRes.value : null;
        geo ? (geo.success ? renderGeoIp(geo) : setBodyErr('geoip', geo.error))
            : setBodyErr('geoip', 'Error');
        wh  ? (wh.success  ? document.getElementById('body-whoisip').innerHTML = `<div class="whois-scroll">${esc(wh.data)}</div>`
                           : setBodyErr('whoisip', wh.error))
            : setBodyErr('whoisip', 'Error');
    } catch(e) {
        setBodyErr('geoip', e.message); setBodyErr('whoisip', e.message);
    } finally {
        document.getElementById('geo-btn-text').classList.remove('d-none');
        document.getElementById('geo-btn-loading').classList.add('d-none');
    }
}

function setBodyErr(id, msg) {
    const el = document.getElementById('body-' + id);
    if (el) el.innerHTML = `<div class="alert alert-danger py-2 mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(msg ?? 'Error')}</div>`;
}
function skeletonHtml() {
    return `<div class="skeleton-wrap"><div class="skeleton-line"></div><div class="skeleton-line short"></div></div>`;
}

function renderGeoIp(d) {
    const flag  = d.country_code ? `<img src="https://flagcdn.com/24x18/${d.country_code.toLowerCase()}.png" alt="${esc(d.country_code)}" class="me-1" style="vertical-align:middle">` : '';
    const badge = (icon, val, sub) => val ? `<div class="geoip-row"><i class="fa-solid ${icon} me-2 text-muted"></i><div><div class="geoip-val">${esc(val)}</div>${sub ? `<div class="geoip-sub">${esc(sub)}</div>` : ''}</div></div>` : '';
    const tags  = [];
    if (d.is_proxy)   tags.push(`<span class="badge bg-warning text-dark me-1">Proxy/VPN</span>`);
    if (d.is_hosting) tags.push(`<span class="badge bg-secondary me-1">Hosting/DC</span>`);
    if (d.is_mobile)  tags.push(`<span class="badge bg-info text-dark me-1">Móvil</span>`);
    document.getElementById('body-geoip').innerHTML = `
        <div class="geoip-header mb-2">
            ${flag}<span class="fw-bold">${esc(d.ip)}</span>
            ${d.ptr ? `<span class="ttl-badge ms-2">${esc(d.ptr)}</span>` : ''}
        </div>
        ${tags.join('')}
        ${badge('fa-location-dot',   d.country, (d.city ? d.city + ', ' + (d.region ?? '') : null))}
        ${badge('fa-clock',          d.timezone, null)}
        ${badge('fa-building',       d.org, d.asn)}
        ${badge('fa-wifi',           d.isp, null)}
        ${d.lat != null ? `<div class="mt-2"><a href="https://www.openstreetmap.org/?mlat=${d.lat}&mlon=${d.lon}&zoom=10" target="_blank" rel="noopener" class="small text-muted"><i class="fa-solid fa-map-location-dot me-1"></i>${d.lat}, ${d.lon}</a></div>` : ''}
        ${d.geo_error ? `<div class="small text-muted mt-2"><i class="fa-solid fa-circle-info me-1"></i>${esc(d.geo_error)}</div>` : ''}`;
}

async function startPropagation() {
    const domain = normalizeDomain();
    if (!domain) { alert('Introduce un dominio en el buscador'); return; }
    const type = document.getElementById('prop-type').value;
    document.getElementById('prop-btn-text').classList.add('d-none');
    document.getElementById('prop-btn-loading').classList.remove('d-none');
    document.getElementById('prop-results').classList.remove('d-none');
    document.getElementById('prop-results').scrollIntoView({behavior:'smooth', block:'start'});
    document.getElementById('body-propagation').innerHTML = skeletonHtml();
    try {
        const res  = await fetch(`api.php?module=dnspropagation&domain=${encodeURIComponent(domain)}&type=${type}`);
        const data = await res.json();
        lastPropData = data;
        data.success ? renderPropagation(data) : (document.getElementById('body-propagation').innerHTML =
            `<div class="alert alert-danger py-2 mb-0 small">${esc(data.error)}</div>`);
    } catch(e) {
        document.getElementById('body-propagation').innerHTML = `<div class="alert alert-danger py-2 mb-0 small">${esc(e.message)}</div>`;
    } finally {
        document.getElementById('prop-btn-text').classList.remove('d-none');
        document.getElementById('prop-btn-loading').classList.add('d-none');
    }
}

function renderPropagation(d) {
    document.getElementById('prop-meta').textContent = `${d.domain} ${d.type} — ${d.unique} valor${d.unique !== 1 ? 'es distintos' : ' único'}`;
    const consIcon = d.consistent
        ? '<i class="fa-solid fa-circle-check text-success me-1"></i><span class="text-success fw-semibold">Propagado correctamente</span>'
        : '<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i><span class="text-warning fw-semibold">Resultados inconsistentes</span>';
    const rows = (d.results ?? []).map(r => {
        const ok = r.status === 'NOERROR';
        const to = r.status === 'TIMEOUT';
        const rowClass = to ? 'text-muted' : (ok ? '' : 'text-danger');
        const vals = r.records.length ? r.records.map(v => `<span class="prop-val">${esc(v)}</span>`).join(' ') : `<span class="text-muted small">${r.status}</span>`;
        return `<tr class="${rowClass}">
            <td class="prop-server-name">${r.flag} ${esc(r.name)}</td>
            <td class="prop-server-ip text-muted">${esc(r.server)}</td>
            <td>${vals}</td>
            <td class="text-end"><span class="ttl-badge">${r.ms} ms</span></td>
        </tr>`;
    }).join('');
    document.getElementById('body-propagation').innerHTML = `
        <div class="mb-2">${consIcon}</div>
        <div class="table-responsive"><table class="prop-table w-100"><thead>
            <tr><th>Servidor</th><th>IP</th><th>Respuesta</th><th></th></tr>
        </thead><tbody>${rows}</tbody></table></div>`;
}

function downloadPropagation() {
    if (!lastPropData) return;
    let t = `[DNS PROPAGACIÓN] ${lastPropData.domain} ${lastPropData.type}\n`;
    (lastPropData.results ?? []).forEach(r => {
        t += `${r.name} (${r.server}): ${r.records.join(', ') || r.status} — ${r.ms}ms\n`;
    });
    downloadText(t, `propagation_${lastPropData.domain}_${stamp()}.txt`);
}

// ── Web tab ───────────────────────────────────────────────────
async function startWebAnalysis() {
    const domain = normalizeDomain();
    if (!domain) { alert('Introduce un dominio en el buscador'); return; }
    document.getElementById('web-btn-text').classList.add('d-none');
    document.getElementById('web-btn-loading').classList.remove('d-none');
    document.getElementById('btn-web-analyze').disabled = true;
    document.getElementById('web-results').classList.remove('d-none');
    document.getElementById('body-seo').innerHTML  = skeletonHtml();
    document.getElementById('body-tech').innerHTML = skeletonHtml();
    try {
        const res  = await fetch(`api.php?module=seocheck&domain=${encodeURIComponent(domain)}`);
        const data = await res.json();
        if (data.success) { renderSeo(data); renderTech(data); }
        else { setBodyErr('seo', data.error); setBodyErr('tech', data.error); }
    } catch(e) { setBodyErr('seo', e.message); setBodyErr('tech', e.message); }
    finally {
        document.getElementById('web-btn-text').classList.remove('d-none');
        document.getElementById('web-btn-loading').classList.add('d-none');
        document.getElementById('btn-web-analyze').disabled = false;
    }
}

function renderSeo(d) {
    const s = d.seo ?? {};
    const chk = (ok, label, hint) => {
        const ic = ok === true ? 'fa-circle-check text-success' : (ok === false ? 'fa-circle-xmark text-danger' : 'fa-circle-question text-muted');
        return `<div class="hdr-row d-flex align-items-start gap-2">
            <i class="fa-solid ${ic} mt-1 small"></i>
            <div><div class="hdr-label">${label}</div>${hint ? `<div class="hdr-desc">${esc(hint)}</div>` : ''}</div>
        </div>`;
    };
    const field = (label, val, cls) => val
        ? `<div class="seo-field mb-2"><div class="seo-label">${label}</div><div class="seo-val ${cls ?? ''}">${esc(val)}</div></div>` : '';

    const statusRow = d.status_code ? `<span class="ttl-badge me-1">HTTP ${d.status_code}</span>` : '';
    const timeRow   = d.response_ms ? `<span class="ttl-badge me-1">${d.response_ms} ms</span>` : '';

    const ogHtml = (s.og_title || s.og_image || s.og_description) ? `
        <div class="mt-3 pt-2 border-top">
            <div class="dns-type-label">Open Graph</div>
            ${s.og_image ? `<img src="${esc(s.og_image)}" alt="OG image" class="img-fluid rounded mb-2" style="max-height:100px;object-fit:cover">` : ''}
            ${field('og:title', s.og_title)}
            ${field('og:description', s.og_description)}
            ${field('og:type', s.og_type)}
            ${field('og:site_name', s.og_site_name)}
        </div>` : '';

    const twHtml = s.tw_card ? `
        <div class="mt-3 pt-2 border-top">
            <div class="dns-type-label">Twitter Card</div>
            ${field('twitter:card', s.tw_card)}
            ${field('twitter:title', s.tw_title)}
            ${field('twitter:site', s.tw_site)}
        </div>` : '';

    document.getElementById('body-seo').innerHTML = `
        <div class="mb-2">${statusRow}${timeRow}</div>
        ${field('Título', s.title, s.title_ok ? 'text-success' : (s.title ? 'text-warning' : 'text-danger'))}
        ${s.title_len != null ? `<div class="seo-hint">${s.title_len} caracteres ${s.title_ok ? '✓ ideal 30-60' : '(ideal 30-60)'}</div>` : ''}
        ${field('Descripción', s.description, s.desc_ok ? 'text-success' : (s.description ? 'text-warning' : 'text-danger'))}
        ${s.desc_len  != null ? `<div class="seo-hint">${s.desc_len} caracteres ${s.desc_ok ? '✓ ideal 70-160' : '(ideal 70-160)'}</div>` : ''}
        ${field('Canonical', s.canonical)}
        ${field('Robots', s.robots)}
        ${s.h1_count != null ? `<div class="seo-field mb-2"><div class="seo-label">H1 <span class="ttl-badge ms-1">${s.h1_count}</span></div>${s.h1?.slice(0,2).map(h => `<div class="seo-val">${esc(h)}</div>`).join('') ?? ''}</div>` : ''}
        <div class="mt-2">
            ${chk(!!s.title,       'Título presente',        !s.title ? 'El título es crítico para SEO' : null)}
            ${chk(!!s.description,'Meta descripción',        !s.description ? 'Aumenta el CTR en buscadores' : null)}
            ${chk(!!s.canonical,  'URL canonical definida',  !s.canonical ? 'Evita contenido duplicado' : null)}
            ${chk(!!s.og_title,   'Open Graph configurado',  !s.og_title ? 'Mejora la presentación en redes sociales' : null)}
            ${chk(!!s.tw_card,    'Twitter Card configurado', !s.tw_card ? 'Mejora el preview en Twitter/X' : null)}
            ${chk(s.h1_count === 1,'H1 único',               s.h1_count !== 1 ? `Se encontraron ${s.h1_count ?? 0} H1 (se recomienda uno solo)` : null)}
        </div>
        ${ogHtml}${twHtml}`;
}

function renderTech(d) {
    if (!(d.tech ?? []).length) {
        document.getElementById('body-tech').innerHTML = '<p class="text-muted small mb-0">No se detectaron tecnologías conocidas.</p>';
        return;
    }
    const cats = {};
    for (const t of d.tech) {
        if (!cats[t.cat]) cats[t.cat] = [];
        cats[t.cat].push(t);
    }
    const catIcons = {
        'Servidor web':'fa-server','CMS':'fa-pencil','eCommerce':'fa-cart-shopping',
        'Constructor':'fa-wand-magic-sparkles','CSS Framework':'fa-palette',
        'JS':'fa-code','JS Framework':'fa-code','Runtime':'fa-microchip',
        'Framework':'fa-layer-group','Analytics':'fa-chart-bar',
        'CDN/Seguridad':'fa-shield-halved','Seguridad':'fa-shield-halved','UI':'fa-star',
    };
    let html = '';
    for (const [cat, items] of Object.entries(cats)) {
        const icon = catIcons[cat] ?? 'fa-puzzle-piece';
        html += `<div class="mb-3">
            <div class="tech-cat-label"><i class="fa-solid ${icon} me-1"></i>${esc(cat)}</div>
            <div class="tech-badges">${items.map(t =>
                `<span class="tech-badge">${esc(t.name)}${t.version ? ` <small>${esc(t.version)}</small>` : ''}</span>`
            ).join('')}</div>
        </div>`;
    }
    document.getElementById('body-tech').innerHTML = `
        <div class="mb-2 small text-muted">${d.tech_count} tecnolog${d.tech_count === 1 ? 'ía detectada' : 'ías detectadas'}</div>${html}`;
}

// ── SSL/TLS tab ───────────────────────────────────────────────
async function startSslScan() {
    const domain = normalizeDomain();
    if (!domain) { alert('Introduce un dominio en el buscador'); return; }
    document.getElementById('ssl-btn-text').classList.add('d-none');
    document.getElementById('ssl-btn-loading').classList.remove('d-none');
    document.getElementById('btn-ssl-scan').disabled = true;
    document.getElementById('ssl-results').classList.remove('d-none');
    ['ssl-protocols','ssl-cipher','ssl-chain','ssl-san'].forEach(id =>
        document.getElementById('body-' + id).innerHTML = skeletonHtml());
    try {
        const res  = await fetch(`api.php?module=sslscan&domain=${encodeURIComponent(domain)}`);
        const data = await res.json();
        if (data.success) renderSslScan(data);
        else ['ssl-protocols','ssl-cipher','ssl-chain','ssl-san'].forEach(id => setBodyErr(id, data.error));
    } catch(e) {
        ['ssl-protocols','ssl-cipher','ssl-chain','ssl-san'].forEach(id => setBodyErr(id, e.message));
    } finally {
        document.getElementById('ssl-btn-text').classList.remove('d-none');
        document.getElementById('ssl-btn-loading').classList.add('d-none');
        document.getElementById('btn-ssl-scan').disabled = false;
    }
}

function renderSslScan(d) {
    // Protocols
    const protos = (d.protocols ?? []).map(p => {
        const ok = p.supported === true;
        const warn = p.supported === true && !p.secure;
        const ic = ok ? (warn ? 'fa-triangle-exclamation text-warning' : 'fa-circle-check text-success') : 'fa-circle-xmark text-muted';
        return `<div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid ${ic}"></i>
            <span class="fw-semibold small">${esc(p.version)}</span>
            ${warn ? '<span class="ttl-badge text-warning">Obsoleto</span>' : ''}
            ${p.supported === null ? '<span class="ttl-badge text-muted">No determinado</span>' : ''}
        </div>`;
    }).join('') || '<p class="text-muted small mb-0">No se pudo detectar.</p>';
    document.getElementById('body-ssl-protocols').innerHTML = protos;

    // Cipher & Security
    const fsIcon = d.forward_secrecy ? '<i class="fa-solid fa-circle-check text-success me-1"></i>' : '<i class="fa-solid fa-circle-xmark text-danger me-1"></i>';
    const hstsIcon = d.hsts ? '<i class="fa-solid fa-circle-check text-success me-1"></i>' : '<i class="fa-solid fa-circle-xmark text-danger me-1"></i>';
    document.getElementById('body-ssl-cipher').innerHTML = `
        ${d.cipher ? `<div class="mb-2"><div class="seo-label">Cipher Suite</div><code class="small">${esc(d.cipher)}</code></div>` : ''}
        ${d.negotiated ? `<div class="mb-2"><div class="seo-label">Protocolo negociado</div><span class="fw-semibold">${esc(d.negotiated)}</span></div>` : ''}
        ${d.key_bits != null ? `<div class="mb-2"><div class="seo-label">Clave pública</div><span class="fw-semibold">${d.key_bits} bits</span></div>` : ''}
        <div class="mt-2">
            <div class="d-flex align-items-center gap-2 mb-1">${fsIcon}<span class="small">Forward Secrecy ${d.forward_secrecy ? '(ECDHE/DHE)' : '— no configurado'}</span></div>
            <div class="d-flex align-items-center gap-2 mb-1">${hstsIcon}<span class="small">HSTS ${d.hsts ? 'activado' : '— no configurado'}</span></div>
            ${d.hsts_header ? `<div class="dns-value small text-muted mt-1">${esc(d.hsts_header)}</div>` : ''}
        </div>`;

    // Chain
    const chain = (d.chain ?? []).map((c, i) => {
        const expire = c.days_left != null ? (c.days_left < 30 ? 'text-danger' : (c.days_left < 60 ? 'text-warning' : 'text-muted')) : 'text-muted';
        return `<div class="ssl-chain-entry ${c.is_ca ? 'ssl-chain-ca' : 'ssl-chain-leaf'}">
            <div class="ssl-chain-idx">${i + 1}</div>
            <div class="flex-grow-1 min-w-0">
                <div class="fw-semibold small">${esc(c.subject)}</div>
                <div class="small text-muted">Emisor: ${esc(c.issuer)}</div>
                <div class="small ${expire}">${esc(c.not_before)} → ${esc(c.not_after)}${c.days_left != null ? ` (${c.days_left}d)` : ''}</div>
                ${c.fingerprint ? `<div class="seo-hint">${c.fingerprint.slice(0, 29)}…</div>` : ''}
            </div>
        </div>`;
    }).join('<div class="ssl-chain-arrow">↓</div>');
    document.getElementById('body-ssl-chain').innerHTML = chain || '<p class="text-muted small mb-0">No se pudo obtener la cadena.</p>';

    // SAN
    const sans = d.san ?? [];
    document.getElementById('body-ssl-san').innerHTML = sans.length
        ? `<div class="san-grid">${sans.map(s => `<span class="san-entry">${esc(s)}</span>`).join('')}</div>`
        : '<p class="text-muted small mb-0">Sin dominios alternativos (SAN).</p>';
}

// ── SMTP Relay tab ─────────────────────────────────────────────
async function startRelayTest() {
    const domain = normalizeDomain();
    if (!domain) { alert('Introduce un dominio en el buscador'); return; }
    const email = (document.getElementById('input-email-test')?.value ?? '').trim();
    document.getElementById('relay-btn-text').classList.add('d-none');
    document.getElementById('relay-btn-loading').classList.remove('d-none');
    document.getElementById('btn-relay-test').disabled = true;
    document.getElementById('relay-results').classList.remove('d-none');
    document.getElementById('relay-results').scrollIntoView({behavior:'smooth', block:'start'});
    ['relay-openrelay','relay-delivery'].forEach(id =>
        document.getElementById('body-' + id).innerHTML = skeletonHtml());
    try {
        let url = `api.php?module=smtprelay&domain=${encodeURIComponent(domain)}`;
        if (email) url += `&email=${encodeURIComponent(email)}`;
        const res  = await fetch(url);
        const data = await res.json();
        data.success ? renderSmtpRelay(data)
            : ['relay-openrelay','relay-delivery'].forEach(id => setBodyErr(id, data.error));
    } catch(e) {
        ['relay-openrelay','relay-delivery'].forEach(id => setBodyErr(id, e.message));
    } finally {
        document.getElementById('relay-btn-text').classList.remove('d-none');
        document.getElementById('relay-btn-loading').classList.add('d-none');
        document.getElementById('btn-relay-test').disabled = false;
    }
}

function renderSmtpDialog(log) {
    return (log ?? []).map(e => {
        if (e.dir === 'ERROR') return `<div class="smtp-line smtp-error"><i class="fa-solid fa-triangle-exclamation me-1"></i>${esc(e.msg)}</div>`;
        const dir  = e.dir === '>>>' ? 'smtp-send' : 'smtp-recv';
        const code = e.code ? `<span class="ttl-badge ms-1">${e.code}</span>` : '';
        const ok   = e.code >= 200 && e.code < 400;
        const cls  = e.code >= 500 ? 'smtp-fail' : (e.code >= 400 ? 'smtp-defer' : '');
        return `<div class="smtp-line ${dir} ${cls}"><span class="smtp-dir">${e.dir}</span> ${esc(e.msg)}${code}</div>`;
    }).join('');
}

function renderSmtpRelay(d) {
    const relayOk   = !d.open_relay;
    const relayIcon = relayOk ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger';
    const relayText = d.open_relay ? '¡RELAY ABIERTO! El servidor puede ser utilizado para enviar spam.' : 'El servidor no acepta relay abierto. Configuración correcta.';
    document.getElementById('body-relay-openrelay').innerHTML = `
        <div class="d-flex align-items-start gap-2 mb-3">
            <i class="fa-solid ${relayIcon} fa-lg mt-1"></i>
            <div>
                <div class="fw-bold ${d.open_relay ? 'text-danger' : 'text-success'}">${relayText}</div>
                <div class="small text-muted mt-1">MX: ${esc(d.mx_host)}${d.mx_ip ? ` (${esc(d.mx_ip)})` : ''}</div>
            </div>
        </div>
        <details><summary class="small text-muted" style="cursor:pointer">Ver diálogo SMTP</summary>
            <div class="smtp-dialog mt-2">${renderSmtpDialog(d.relay_log)}</div>
        </details>`;

    const resMap = {accepted:'Aceptado ✓', rejected:'Rechazado', deferred:'Diferido', unknown:'No determinado'};
    const resClass = {accepted:'text-success', rejected:'text-danger', deferred:'text-warning', unknown:'text-muted'};
    const r = d.delivery_result ?? 'unknown';
    document.getElementById('body-relay-delivery').innerHTML = `
        <div class="mb-2">
            <div class="seo-label">Cuenta probada</div>
            <code class="small">${esc(d.test_email)}</code>
        </div>
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid ${r === 'accepted' ? 'fa-circle-check' : (r === 'rejected' ? 'fa-circle-xmark' : 'fa-circle-question')} ${resClass[r]}"></i>
            <span class="fw-semibold ${resClass[r]}">${resMap[r]}</span>
            <span class="ttl-badge">${d.delivery_code}</span>
        </div>
        <p class="small text-muted">Resultado orientativo — los servidores con catch-all siempre responden 250.</p>
        <details><summary class="small text-muted" style="cursor:pointer">Ver diálogo SMTP</summary>
            <div class="smtp-dialog mt-2">${renderSmtpDialog(d.delivery_log)}</div>
        </details>`;
}

// ── Info Popovers ─────────────────────────────────────────────
const INFO_CONTENT = {
    // Tabs
    'tab-diagnostico': '<strong>Diagnóstico completo</strong><br>Analiza resolución DNS, puertos TCP, certificado SSL, ping, cabeceras HTTP de seguridad, listas negras (blacklist), traceroute y cadena de redirecciones de un dominio o IP.',
    'tab-correo':      '<strong>Diagnóstico de correo</strong><br>Comprueba registros MX, conectividad SMTP, autenticación SPF, DKIM y DMARC, presencia en blacklists, puntuación de entregabilidad y permite analizar archivos .eml.',
    'tab-dnsquery':    '<strong>Consulta DNS personalizada</strong><br>Realiza consultas DNS de cualquier tipo (A, AAAA, MX, TXT, SOA…) contra el servidor DNS de tu elección. Útil para depurar registros o verificar la configuración de zona.',
    'tab-red':         '<strong>Red & IP</strong><br>Geolocaliza cualquier IP o dominio, muestra su ASN, proveedor, organización y coordenadas. Incluye herramienta de propagación DNS mundial para comprobar si un cambio de registro se ha propagado.',
    'tab-web':         '<strong>Análisis web & SEO</strong><br>Extrae metadatos SEO (título, descripción, canonical, robots), Open Graph, Twitter Card, etiquetas H1 y detecta más de 30 tecnologías web: CMS, frameworks JS, analytics, CDN y más.',
    'tab-ssl':         '<strong>Escáner SSL/TLS extendido</strong><br>Analiza los protocolos TLS soportados (1.0 a 1.3), cipher suite negociado, forward secrecy, HSTS, cadena de certificados con fechas y fingerprints SHA-256, y dominios SAN.',

    // Diagnóstico modules
    'mod-resolution':  '<strong>Resolución DNS</strong><br>Muestra la IP principal del dominio (registro A/AAAA) y su PTR (DNS inverso). Es el punto de partida de cualquier diagnóstico de conectividad.',
    'mod-ssl':         '<strong>SSL / TLS</strong><br>Verifica la validez del certificado HTTPS: emisor, fechas de validez, días restantes, nombre común y Subject Alternative Names (SAN).',
    'mod-ports':       '<strong>Puertos TCP</strong><br>Comprueba la accesibilidad de los puertos más comunes: HTTP (80), HTTPS (443), SMTP (25/587/465), FTP (21), SSH (22), RDP (3389) y otros.',
    'mod-ping':        '<strong>Ping</strong><br>Mide la latencia y disponibilidad del servidor mediante ICMP. Indica si el host responde y cuánto tiempo tarda en responder en milisegundos.',
    'mod-headers':     '<strong>Cabeceras HTTP</strong><br>Analiza las cabeceras de seguridad HTTP: Content-Security-Policy, X-Frame-Options, HSTS, Referrer-Policy, Permissions-Policy y otras cabeceras relevantes.',
    'mod-blacklist':   '<strong>Blacklist</strong><br>Comprueba si la IP del servidor está listada en más de 20 listas negras (DNSBL) utilizadas por los servidores de correo para filtrar spam.',
    'mod-traceroute':  '<strong>Traceroute</strong><br>Muestra la ruta que siguen los paquetes de red desde el servidor hasta el destino, con cada salto (hop), su IP y latencia. Útil para detectar cuellos de botella.',
    'mod-redirect':    '<strong>Redirecciones HTTP</strong><br>Sigue la cadena de redirecciones (301, 302…) hasta la URL final. Detecta bucles y muestra el código de estado de cada paso.',
    'mod-webinfo':     '<strong>Web Info</strong><br>Captura visual de la web, historial en Wayback Machine, tiempo de respuesta HTTP, popularidad Tranco y tecnologías detectadas en las cabeceras del servidor.',
    'mod-dns':         '<strong>Registros DNS</strong><br>Lista todos los registros DNS configurados: A, AAAA, CNAME, MX, NS, TXT, SPF, DMARC, DKIM, CAA, SOA, BIMI y más. Esencial para verificar la configuración DNS.',
    'mod-whois':       '<strong>WHOIS</strong><br>Información de registro del dominio: titular, registrador, fechas de creación y expiración, servidores de nombres y datos de contacto según la base de datos WHOIS.',

    // Mail modules
    'mod-mail-score':    '<strong>Puntuación de entregabilidad</strong><br>Evalúa la configuración de correo y genera una puntuación del 0 al 10. Tiene en cuenta SPF, DKIM, DMARC, blacklist, PTR del MX y conectividad SMTP.',
    'mod-mail-mx':       '<strong>Registros MX</strong><br>Lista los servidores de correo configurados para el dominio, con su prioridad, IP y verificación PTR. Un MX bien configurado es esencial para recibir correo.',
    'mod-mail-smtp':     '<strong>Conectividad SMTP</strong><br>Prueba la conexión a los puertos SMTP habituales (25, 587, 465). Verifica si soportan STARTTLS/SSL y muestra el banner del servidor.',
    'mod-mail-blacklist':'<strong>Blacklist del servidor MX</strong><br>Comprueba si la IP del servidor de correo aparece en listas negras anti-spam. Una IP en blacklist puede hacer que el correo sea rechazado o marcado como spam.',
    'mod-mail-spf':      '<strong>SPF (Sender Policy Framework)</strong><br>Verifica el registro SPF del dominio que autoriza qué servidores pueden enviar correo en su nombre. Sin SPF, el correo puede llegar a spam.',
    'mod-mail-dmarc':    '<strong>DMARC</strong><br>Comprueba la política DMARC (Domain-based Message Authentication). Define qué hacer con correos que fallan SPF/DKIM y permite recibir reportes de autenticación.',
    'mod-mail-dkim':     '<strong>DKIM (DomainKeys Identified Mail)</strong><br>Busca registros DKIM en los selectores más comunes. DKIM firma digitalmente el correo para garantizar que no ha sido modificado en tránsito.',
    'mod-eml':           '<strong>Análisis de archivo .eml</strong><br>Analiza las cabeceras de un correo electrónico: autenticación SPF/DKIM/DMARC, ruta de servidores (Received), puntuación anti-spam, codificación y todos los metadatos. Sin envío a servicios externos.',

    // Relay
    'mod-relay-open':     '<strong>Test Open Relay</strong><br>Verifica si el servidor SMTP acepta relay abierto, es decir, si permite enviar correo desde un dominio externo hacia otro externo sin autenticación. Un relay abierto es una vulnerabilidad grave usada para enviar spam.',
    'mod-relay-delivery': '<strong>Simulación de entrega SMTP</strong><br>Simula el envío de un correo a la cuenta indicada realizando una conversación SMTP completa hasta el comando RCPT TO. Permite detectar si el buzón existe o si el servidor rechaza el correo.',

    // Red & IP
    'mod-geoip':      '<strong>Geolocalización IP & ASN</strong><br>Determina el país, ciudad, región, zona horaria, proveedor (ISP), número de sistema autónomo (ASN) y organización de una IP o dominio. Identifica también proxies, VPNs y datacenters.',
    'mod-whoisip':    '<strong>WHOIS de IP</strong><br>Consulta los datos de registro del bloque IP en los registros regionales de Internet (RIR): RIPE, ARIN, LACNIC, APNIC. Muestra el propietario y rango asignado.',
    'mod-propagation':'<strong>Propagación DNS mundial</strong><br>Consulta el mismo registro DNS desde 10 servidores resolver distribuidos globalmente (Google, Cloudflare, Quad9, Yandex…). Permite detectar si un cambio DNS se ha propagado correctamente en todo el mundo.',

    // Web tab
    'mod-seo':  '<strong>SEO & Metadatos</strong><br>Extrae y evalúa el título, meta descripción, URL canonical, robots, etiquetas H1, Open Graph y Twitter Card. Analiza longitudes óptimas y detecta elementos SEO críticos ausentes.',
    'mod-tech': '<strong>Detección de tecnologías</strong><br>Identifica el CMS (WordPress, Drupal…), frameworks JS (React, Vue, Next.js…), plataformas eCommerce (Shopify, WooCommerce…), herramientas analytics, librerías CSS y más, a partir de las cabeceras y el HTML.',

    // SSL tab
    'mod-ssl-protocols': '<strong>Protocolos TLS soportados</strong><br>Prueba qué versiones de TLS acepta el servidor: TLS 1.0 (obsoleto), 1.1 (obsoleto), 1.2 (recomendado) y 1.3 (óptimo). TLS 1.0 y 1.1 deben estar deshabilitados.',
    'mod-ssl-cipher':    '<strong>Cipher Suite & Seguridad</strong><br>Muestra el algoritmo de cifrado negociado, el protocolo activo, el tamaño de la clave pública, si se usa Forward Secrecy (ECDHE/DHE) y si HSTS está activado.',
    'mod-ssl-chain':     '<strong>Cadena de certificados</strong><br>Lista todos los certificados de la cadena de confianza: certificado del servidor, intermedios y raíz. Muestra fechas de validez, emisor, días restantes y fingerprint SHA-256.',
    'mod-ssl-san':       '<strong>SAN — Subject Alternative Names</strong><br>Relación de dominios y subdominios que cubre el certificado SSL/TLS. Permite ver si el certificado es wildcard o multi-dominio.',
    'mod-spfcheck':      '<strong>Validador SPF</strong><br>Evalúa si una IP concreta está autorizada por el registro SPF del dominio para enviar correo en su nombre. Implementa RFC 7208: mecanismos ip4, ip6, a, mx, include, redirect, exists y ptr. Resultado: <em>pass</em> (autorizada), <em>fail</em> (rechazada), <em>softfail</em> (sospechosa) o <em>neutral</em>.',
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.info-popover-btn').forEach(el => {
        const key     = el.dataset.infoKey;
        const content = INFO_CONTENT[key] ?? 'Información no disponible.';
        const pop = new bootstrap.Popover(el, {
            content,
            html:      true,
            trigger:   'click',
            placement: 'auto',
            container: 'body',
        });
        // Click outside to close
        el.addEventListener('click', e => {
            e.stopPropagation();
            document.querySelectorAll('.info-popover-btn').forEach(o => {
                if (o !== el) bootstrap.Popover.getInstance(o)?.hide();
            });
        });
    });
    document.addEventListener('click', () => {
        document.querySelectorAll('.info-popover-btn').forEach(el => {
            bootstrap.Popover.getInstance(el)?.hide();
        });
    });
});

// ── SortableJS ────────────────────────────────────────────────
const sortOpts = {group:'cards', handle:'.drag-handle', animation:150, ghostClass:'sortable-ghost', dragClass:'sortable-drag'};
new Sortable(document.getElementById('col-left'),       sortOpts);
new Sortable(document.getElementById('col-right'),      sortOpts);
new Sortable(document.getElementById('col-mail-left'),  sortOpts);
new Sortable(document.getElementById('col-mail-right'), sortOpts);

// ── Footer: datos del visitante ───────────────────────────────
const VISITOR_SERVER = <?= json_encode([
    'ip'   => $visitorIp,
    'ua'   => $visitorUa,
    'lang' => $visitorLang,
    'ref'  => $visitorRef,
]) ?>;

(async function loadVisitorInfo() {
    const el = document.getElementById('visitor-info');
    try {
        // Parseo básico de navegador/OS desde UA
        const ua  = VISITOR_SERVER.ua;
        let browser = 'Desconocido', os = 'Desconocido';
        if (/Edg\//.test(ua))          browser = 'Microsoft Edge';
        else if (/OPR\//.test(ua))     browser = 'Opera';
        else if (/Chrome\//.test(ua))  browser = 'Chrome';
        else if (/Firefox\//.test(ua)) browser = 'Firefox';
        else if (/Safari\//.test(ua))  browser = 'Safari';
        if (/Windows NT/.test(ua))     os = 'Windows';
        else if (/Mac OS X/.test(ua))  os = 'macOS';
        else if (/Linux/.test(ua))     os = 'Linux';
        else if (/Android/.test(ua))   os = 'Android';
        else if (/iPhone|iPad/.test(ua)) os = 'iOS';

        // Geolocalización via ipapi.co (client-side, sin API key)
        let geo = {};
        try {
            const r = await fetch(`https://ipapi.co/${encodeURIComponent(VISITOR_SERVER.ip)}/json/`, {signal: AbortSignal.timeout(5000)});
            geo = await r.json();
        } catch(_) {}

        const items = [
            ['fa-location-dot', geo.country_name ?? '—',           geo.city ? geo.city + ', ' + (geo.region ?? '') : ''],
            ['fa-network-wired', VISITOR_SERVER.ip,                  geo.org ?? ''],
            ['fa-globe',         browser + ' / ' + os,              ''],
            ['fa-language',      VISITOR_SERVER.lang.split(',')[0] ?? '—', ''],
        ];
        if (geo.asn)  items.push(['fa-building', geo.org ?? '', '']);
        if (window.screen) items.push(['fa-display', `${screen.width}×${screen.height}`, '']);

        el.innerHTML = items.map(([icon, main, sub]) =>
            `<span class="visitor-item">
                <i class="fa-solid ${icon} me-1"></i>
                <span>${main}</span>${sub?`<span class="visitor-sub"> · ${sub}</span>`:''}
             </span>`
        ).join('');
    } catch(_) {
        el.innerHTML = `<span class="visitor-item"><i class="fa-solid fa-location-dot me-1"></i>${VISITOR_SERVER.ip}</span>`;
    }
})();
</script>
</body>
</html>
