<?php
/**
 * Cuakcom Expert Suite v1.1
 */
define('APP_VERSION', '1.1');

// ─── DESCARGA ─────────────────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'download') {
    $contenido = $_POST['content'] ?? '';
    $filename  = 'reporte_' . ($_POST['type'] ?? 'consulta') . '_' . date('Ymd_His') . '.txt';
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "==========================================\n";
    echo "   REPORTE TÉCNICO CUAKCOM EXPERT v" . APP_VERSION . "\n";
    echo "   Fecha: " . date('Y-m-d H:i:s') . "\n";
    echo "==========================================\n\n";
    echo $contenido;
    exit;
}

// ─── UPLOAD .EML ──────────────────────────────────────────────────────────────
$emlContent = '';
if (isset($_POST['correo_action']) && $_POST['correo_action'] === 'revisar_cabeceras') {
    if (!empty($_FILES['eml_file']['tmp_name'])) {
        $emlContent = file_get_contents($_FILES['eml_file']['tmp_name']);
    } elseif (!empty($_POST['raw_headers'])) {
        $emlContent = $_POST['raw_headers'];
    }
}

if (file_exists(__DIR__ . '/config.php')) require_once __DIR__ . '/config.php';
require_once 'functions.php';

// ─── FUNCIONES ────────────────────────────────────────────────────────────────
function obtenerWhois(string $dominio): string {
    $fp = @fsockopen('whois.iana.org', 43, $e, $es, 5);
    if (!$fp) return 'Error de conexión WHOIS.';
    fputs($fp, $dominio . "\r\n");
    $out = '';
    while (!feof($fp)) { $out .= fgets($fp, 128); }
    fclose($fp);
    return $out;
}

function obtenerColorDns(string $tipo): string {
    $colores = [
        'A' => 'bg-primary', 'MX' => 'bg-warning text-dark', 'NS' => 'bg-danger',
        'TXT' => 'bg-success', 'AAAA' => 'bg-info text-dark', 'CNAME' => 'bg-secondary',
        'IP' => 'bg-primary', 'HOST' => 'bg-dark',
    ];
    return $colores[$tipo] ?? 'bg-dark';
}

function esPuertoAbierto(string $host, int $puerto, float $timeout = 0.3): bool {
    $conn = @fsockopen($host, $puerto, $errno, $errstr, $timeout);
    if (is_resource($conn)) { fclose($conn); return true; }
    return false;
}

function limpiarDominio(string $raw): string {
    return explode('/', preg_replace('#^https?://(www\.)?#', '', trim($raw)))[0];
}

// ─── ROUTING ──────────────────────────────────────────────────────────────────
$activeTab = $_POST['active_tab'] ?? 'diagnostico';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuakcom Expert Suite v<?= APP_VERSION ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="header-section text-center mb-4">
    <div class="container">
        <h1 class="h4 fw-bold m-0">
            <i class="fa-solid fa-bolt me-2"></i>Cuakcom Expert Suite
            <span class="version-badge">v<?= APP_VERSION ?></span>
        </h1>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">

            <!-- ── NAVEGACIÓN POR PESTAÑAS ──────────────────────────── -->
            <ul class="nav nav-pills main-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link tab-btn <?= $activeTab === 'diagnostico' ? 'active' : '' ?>"
                       href="#" data-tab="diagnostico">🔍 Diagnóstico</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link tab-btn <?= $activeTab === 'correo' ? 'active' : '' ?>"
                       href="#" data-tab="correo">📧 Correo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link tab-btn <?= $activeTab === 'puertos' ? 'active' : '' ?>"
                       href="#" data-tab="puertos">🔌 Puertos</a>
                </li>
            </ul>

            <!-- ══════════════════════════════════════════════════════════
                 PESTAÑA: DIAGNÓSTICO
            ══════════════════════════════════════════════════════════ -->
            <div id="tab-diagnostico" class="tab-panel <?= $activeTab !== 'diagnostico' ? 'd-none' : '' ?>">

                <div class="card p-4 mb-4">
                    <form action="index.php" method="POST" id="form-diagnostico">
                        <input type="hidden" name="active_tab" value="diagnostico">
                        <input type="hidden" name="tool" value="diagnostico">
                        <div class="row g-2">
                            <div class="col-md-9">
                                <input type="text" name="dominio" class="form-control form-control-lg"
                                    placeholder="ejemplo.com" required
                                    value="<?= htmlspecialchars($_POST['dominio'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-analyze btn-lg w-100 fw-bold" id="btn-submit-diag">
                                    <span id="btn-text-diag">🔍 ANALIZAR</span>
                                    <span id="btn-loading-diag"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                </button>
                            </div>
                            <div class="col-12">
                                <?php
                                $isPostDiag  = isset($_POST['tool']) && $_POST['tool'] === 'diagnostico';
                                $modulosDiag = $_POST['modulos'] ?? [];
                                $defaultDiag = ['dns', 'ssl', 'redirecciones', 'whois'];
                                $checkDiag   = fn($m) => $isPostDiag ? in_array($m, $modulosDiag) : in_array($m, $defaultDiag);
                                $modulosDef  = [
                                    'dns'           => ['icon' => '🌐', 'label' => 'DNS'],
                                    'ssl'           => ['icon' => '🔒', 'label' => 'SSL'],
                                    'redirecciones' => ['icon' => '↪️',  'label' => 'Redirecciones'],
                                    'puertos'       => ['icon' => '🔌', 'label' => 'Puertos'],
                                    'whois'         => ['icon' => '📋', 'label' => 'Whois'],
                                ];
                                ?>
                                <div class="module-selectors d-flex gap-4 flex-wrap justify-content-center">
                                    <?php foreach ($modulosDef as $id => $info): ?>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="modulos[]"
                                            value="<?= $id ?>" id="chk-<?= $id ?>"
                                            <?= $checkDiag($id) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="chk-<?= $id ?>">
                                            <?= $info['icon'] ?> <?= $info['label'] ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if ($activeTab === 'diagnostico' && $isPostDiag && !empty($_POST['dominio'])):
                    $dominio        = limpiarDominio($_POST['dominio']);
                    $modulos_activos = $_POST['modulos'] ?? [];
                    $ip             = gethostbyname($dominio);
                    $host           = gethostbyaddr($ip);
                ?>
                <div class="row g-3">

                    <!-- Columna izquierda -->
                    <div class="col-md-4">

                        <!-- Resolución IP -->
                        <div class="card mb-3">
                            <div class="card-header-cuak">
                                <span class="header-badge bg-primary">🌐 Resolución</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="dns-row d-flex align-items-center gap-2 border-0">
                                    <span class="badge dns-badge bg-primary">IP</span>
                                    <div class="dns-value text-primary fw-bold"><?= htmlspecialchars($ip) ?></div>
                                </div>
                                <div class="dns-row d-flex align-items-start gap-2 border-0">
                                    <span class="badge dns-badge bg-dark">HOST</span>
                                    <div class="dns-value text-secondary small"><?= htmlspecialchars($host) ?></div>
                                </div>
                            </div>
                        </div>

                        <?php if (in_array('ssl', $modulos_activos)): ?>
                        <div class="card mb-3">
                            <div class="card-header-cuak">
                                <span class="header-badge bg-success">🔒 SSL</span>
                            </div>
                            <div class="card-body p-3">
                                <?php require 'modules/ssl.php'; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (in_array('redirecciones', $modulos_activos)): ?>
                        <div class="card mb-3">
                            <div class="card-header-cuak">
                                <span class="header-badge bg-warning text-dark">↪️ Redirecciones</span>
                            </div>
                            <div class="card-body p-3">
                                <?php require 'modules/redirects.php'; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (in_array('puertos', $modulos_activos)): ?>
                        <div class="card mb-3">
                            <div class="card-header-cuak">
                                <span class="header-badge bg-dark">🔌 Puertos</span>
                            </div>
                            <div class="card-body p-3">
                                <?php require 'modules/ports.php'; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                    <!-- Columna derecha -->
                    <div class="col-md-8">

                        <?php if (in_array('dns', $modulos_activos)): ?>
                        <div class="card mb-3">
                            <div class="card-header-cuak">
                                <span class="header-badge bg-primary">🌐 DNS</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                <?php foreach ([['A', 'MX'], ['NS', 'TXT']] as $idx => $colGroup): ?>
                                    <div class="col-md-6 <?= $idx === 0 ? 'border-end' : '' ?>">
                                        <?php foreach ($colGroup as $t):
                                            $regs = @dns_get_record($dominio, constant("DNS_$t"));
                                            echo "<div class='text-muted mt-2 mb-2 fw-bold text-uppercase' style='font-size:0.6rem;'>$t Records</div>";
                                            if ($regs): foreach ($regs as $r):
                                                $val = $r['ip'] ?? $r['target'] ?? $r['txt'] ?? '---'; ?>
                                                <div class="dns-row d-flex align-items-start gap-2">
                                                    <span class="badge dns-badge <?= obtenerColorDns($t) ?>"><?= $t ?></span>
                                                    <div class="dns-value"><?= htmlspecialchars($val) ?></div>
                                                </div>
                                            <?php endforeach; else: echo "<div class='small text-muted opacity-50'>No data</div>"; endif;
                                        endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (in_array('whois', $modulos_activos)): ?>
                        <div class="card">
                            <div class="card-header-cuak">
                                <span class="header-badge bg-danger">📋 Whois</span>
                            </div>
                            <div class="card-body p-3">
                                <?php $whoisData = obtenerWhois($dominio); ?>
                                <div class="whois-scroll"><?= htmlspecialchars($whoisData) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ══════════════════════════════════════════════════════════
                 PESTAÑA: CORREO
            ══════════════════════════════════════════════════════════ -->
            <div id="tab-correo" class="tab-panel <?= $activeTab !== 'correo' ? 'd-none' : '' ?>">

                <div class="card p-4 mb-4">
                    <!-- Input dominio compartido -->
                    <div class="row g-2 mb-3">
                        <div class="col-12">
                            <input type="text" id="correo-dominio-input" class="form-control form-control-lg"
                                placeholder="dominio.com"
                                value="<?= htmlspecialchars($_POST['dominio'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- 4 botones grandes -->
                    <div class="row g-3">
                        <div class="col-md-3">
                            <form action="index.php" method="POST" class="correo-form h-100">
                                <input type="hidden" name="active_tab" value="correo">
                                <input type="hidden" name="correo_action" value="analizar_correo">
                                <input type="hidden" name="dominio" class="correo-dominio-field"
                                    value="<?= htmlspecialchars($_POST['dominio'] ?? '') ?>">
                                <button type="submit"
                                    class="btn btn-correo btn-correo-analizar w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-2 p-4">
                                    <span class="correo-btn-icon">📨</span>
                                    <span class="correo-btn-label">Analizar Correo</span>
                                    <span class="correo-btn-desc">MX · SPF · DKIM · DMARC</span>
                                </button>
                            </form>
                        </div>

                        <div class="col-md-3">
                            <form action="index.php" method="POST" class="correo-form h-100">
                                <input type="hidden" name="active_tab" value="correo">
                                <input type="hidden" name="correo_action" value="relay_test">
                                <input type="hidden" name="dominio" class="correo-dominio-field"
                                    value="<?= htmlspecialchars($_POST['dominio'] ?? '') ?>">
                                <button type="submit"
                                    class="btn btn-correo btn-correo-relay w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-2 p-4">
                                    <span class="correo-btn-icon">📡</span>
                                    <span class="correo-btn-label">Test Relay & Entrega</span>
                                    <span class="correo-btn-desc">SMTP · STARTTLS · Banner</span>
                                </button>
                            </form>
                        </div>

                        <div class="col-md-3">
                            <form action="index.php" method="POST" class="correo-form h-100">
                                <input type="hidden" name="active_tab" value="correo">
                                <input type="hidden" name="correo_action" value="abuseipdb">
                                <input type="hidden" name="dominio" class="correo-dominio-field"
                                    value="<?= htmlspecialchars($_POST['dominio'] ?? '') ?>">
                                <button type="submit"
                                    class="btn btn-correo btn-correo-abuse w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-2 p-4">
                                    <span class="correo-btn-icon">🚨</span>
                                    <span class="correo-btn-label">Comprobar AbuseIPDB</span>
                                    <span class="correo-btn-desc">Reputación de IP</span>
                                </button>
                            </form>
                        </div>

                        <div class="col-md-3">
                            <button type="button" id="btn-toggle-headers"
                                class="btn btn-correo btn-correo-headers w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-2 p-4">
                                <span class="correo-btn-icon">📬</span>
                                <span class="correo-btn-label">Revisar Cabeceras</span>
                                <span class="correo-btn-desc">EML o texto pegado</span>
                            </button>
                        </div>
                    </div>

                    <!-- Panel Revisar Cabeceras (desplegable) -->
                    <div id="panel-cabeceras" class="mt-4
                        <?= (isset($_POST['correo_action']) && $_POST['correo_action'] === 'revisar_cabeceras') ? '' : 'd-none' ?>">
                        <div class="card border">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3">📬 Revisar Cabeceras</h6>
                                <div class="d-flex gap-2 mb-3">
                                    <button type="button" class="btn btn-sm fw-bold" id="opt-eml"
                                        onclick="selectHeadersMode('eml')">📎 Subir .eml</button>
                                    <button type="button" class="btn btn-sm fw-bold" id="opt-paste"
                                        onclick="selectHeadersMode('paste')">📝 Pegar Cabeceras</button>
                                </div>

                                <!-- Subir .eml -->
                                <form action="index.php" method="POST" enctype="multipart/form-data"
                                      id="form-eml" class="d-none">
                                    <input type="hidden" name="active_tab" value="correo">
                                    <input type="hidden" name="correo_action" value="revisar_cabeceras">
                                    <input type="hidden" name="headers_mode" value="eml">
                                    <div class="mb-2">
                                        <input type="file" name="eml_file" accept=".eml,.txt" class="form-control">
                                    </div>
                                    <button type="submit" class="btn btn-primary fw-bold rounded-pill">
                                        📂 Analizar EML
                                    </button>
                                </form>

                                <!-- Pegar cabeceras -->
                                <form action="index.php" method="POST" id="form-paste" class="d-none">
                                    <input type="hidden" name="active_tab" value="correo">
                                    <input type="hidden" name="correo_action" value="revisar_cabeceras">
                                    <input type="hidden" name="headers_mode" value="paste">
                                    <div class="mb-2">
                                        <textarea name="raw_headers" class="form-control font-monospace" rows="10"
                                            placeholder="Pega aquí las cabeceras del correo (desde From: hasta la línea en blanco)..."><?= htmlspecialchars($_POST['raw_headers'] ?? '') ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary fw-bold rounded-pill">
                                        🔍 Analizar Cabeceras
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resultados de Correo -->
                <?php
                $correoAction = $_POST['correo_action'] ?? '';
                $correoDominio = !empty($_POST['dominio']) ? limpiarDominio($_POST['dominio']) : '';
                $showCorreoResult = $activeTab === 'correo' && !empty($correoAction) &&
                    ($correoAction === 'revisar_cabeceras' ? !empty($emlContent) : !empty($correoDominio));
                if ($showCorreoResult):
                    $dominio = $correoDominio;
                ?>
                <div class="card">
                    <div class="card-body p-4">
                    <?php switch ($correoAction):
                        case 'analizar_correo': ?>
                            <h5 class="mb-3">📨 Análisis de Correo — <strong><?= htmlspecialchars($dominio) ?></strong></h5>
                            <?php require 'modules/mail_analyze.php';
                            break;
                        case 'relay_test': ?>
                            <h5 class="mb-3">📡 Test Relay & Entrega — <strong><?= htmlspecialchars($dominio) ?></strong></h5>
                            <?php require 'modules/relay_test.php';
                            break;
                        case 'abuseipdb': ?>
                            <h5 class="mb-3">🚨 AbuseIPDB — <strong><?= htmlspecialchars($dominio) ?></strong></h5>
                            <?php require 'modules/abuseipdb.php';
                            break;
                        case 'revisar_cabeceras': ?>
                            <h5 class="mb-3">📬 Análisis de Cabeceras</h5>
                            <?php $rawHeaders = $emlContent;
                            require 'modules/email_headers.php';
                            break;
                    endswitch; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ══════════════════════════════════════════════════════════
                 PESTAÑA: PUERTOS
            ══════════════════════════════════════════════════════════ -->
            <div id="tab-puertos" class="tab-panel <?= $activeTab !== 'puertos' ? 'd-none' : '' ?>">

                <div class="card p-4 mb-4">
                    <form action="index.php" method="POST" id="form-puertos">
                        <input type="hidden" name="active_tab" value="puertos">
                        <input type="hidden" name="tool" value="puertos">
                        <div class="row g-2">
                            <div class="col-md-9">
                                <input type="text" name="dominio" class="form-control form-control-lg"
                                    placeholder="ejemplo.com" required
                                    value="<?= htmlspecialchars($_POST['dominio'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-analyze btn-lg w-100 fw-bold" id="btn-submit-ports">
                                    <span id="btn-text-ports">🔌 ESCANEAR</span>
                                    <span id="btn-loading-ports"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if ($activeTab === 'puertos' && isset($_POST['tool']) && $_POST['tool'] === 'puertos' && !empty($_POST['dominio'])):
                    $dominio = limpiarDominio($_POST['dominio']);
                ?>
                <div class="card">
                    <div class="card-header-cuak">
                        <span class="header-badge bg-dark">🔌 Puertos — <?= htmlspecialchars($dominio) ?></span>
                    </div>
                    <div class="card-body p-3">
                        <?php require 'modules/ports.php'; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Cambio de pestañas ──────────────────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        var tab = this.dataset.tab;
        document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.add('d-none'); });
        this.classList.add('active');
        var panel = document.getElementById('tab-' + tab);
        if (panel) panel.classList.remove('d-none');
    });
});

// ── Sincronizar dominio a los formularios de correo ─────────────────────────
var correoDomInput = document.getElementById('correo-dominio-input');
if (correoDomInput) {
    correoDomInput.addEventListener('input', function() {
        document.querySelectorAll('.correo-dominio-field').forEach(function(f) {
            f.value = correoDomInput.value;
        });
    });
}

// ── Toggle panel cabeceras ──────────────────────────────────────────────────
var btnHeaders  = document.getElementById('btn-toggle-headers');
var panelCab    = document.getElementById('panel-cabeceras');
if (btnHeaders) {
    btnHeaders.addEventListener('click', function() {
        if (panelCab) panelCab.classList.toggle('d-none');
    });
}

function selectHeadersMode(mode) {
    var formEml   = document.getElementById('form-eml');
    var formPaste = document.getElementById('form-paste');
    var optEml    = document.getElementById('opt-eml');
    var optPaste  = document.getElementById('opt-paste');

    formEml.classList.add('d-none');
    formPaste.classList.add('d-none');
    optEml.className   = 'btn btn-sm fw-bold btn-outline-primary';
    optPaste.className = 'btn btn-sm fw-bold btn-outline-secondary';

    if (mode === 'eml') {
        formEml.classList.remove('d-none');
        optEml.className = 'btn btn-sm fw-bold btn-primary';
    } else {
        formPaste.classList.remove('d-none');
        optPaste.className = 'btn btn-sm fw-bold btn-secondary';
    }
}

// ── Estados de carga ────────────────────────────────────────────────────────
function setupLoading(formId, btnId, textId, loadId) {
    var form = document.getElementById(formId);
    if (!form) return;
    form.addEventListener('submit', function() {
        document.getElementById(textId).style.display = 'none';
        document.getElementById(loadId).style.display = 'inline-block';
        document.getElementById(btnId).classList.add('disabled');
    });
}
setupLoading('form-diagnostico', 'btn-submit-diag',   'btn-text-diag',   'btn-loading-diag');
setupLoading('form-puertos',     'btn-submit-ports',  'btn-text-ports',  'btn-loading-ports');

// ── Auto-seleccionar modo cabeceras al volver de un submit ──────────────────
<?php if (isset($_POST['correo_action']) && $_POST['correo_action'] === 'revisar_cabeceras'): ?>
document.addEventListener('DOMContentLoaded', function() {
    selectHeadersMode('<?= htmlspecialchars($_POST['headers_mode'] ?? 'paste') ?>');
});
<?php endif; ?>
</script>
</body>
</html>
