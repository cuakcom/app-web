<?php
/**
 * LÓGICA DE DESCARGA UNIFICADA
 */
if (isset($_POST['action']) && $_POST['action'] == 'download') {
    $contenido = $_POST['content'] ?? '';
    $filename = "reporte_" . ($_POST['type'] ?? 'consulta') . "_" . date('Ymd_His') . ".txt";
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "==========================================\n";
    echo "   REPORTE TÉCNICO CUAKCOM EXPERT\n";
    echo "   Fecha: " . date('Y-m-d H:i:s') . "\n";
    echo "==========================================\n\n";
    echo $contenido;
    exit;
}

/**
 * FUNCIONES AUXILIARES
 */
function obtenerWhois($dominio) {
    $servidorWhois = "whois.iana.org";
    $fp = @fsockopen($servidorWhois, 43);
    if (!$fp) return "Error de conexión WHOIS.";
    fputs($fp, $dominio . "\r\n");
    $out = "";
    while (!feof($fp)) { $out .= fgets($fp, 128); }
    fclose($fp);
    return $out;
}

function obtenerColorDns($tipo) {
    $colores = [
        'A' => 'bg-primary', 'MX' => 'bg-warning text-dark', 'NS' => 'bg-danger', 
        'TXT' => 'bg-success', 'AAAA' => 'bg-info text-dark', 'CNAME' => 'bg-secondary',
        'IP' => 'bg-primary', 'HOST' => 'bg-dark'
    ];
    return $colores[$tipo] ?? 'bg-dark';
}

function esPuertoAbierto($host, $puerto) {
    $connection = @fsockopen($host, $puerto, $errno, $errstr, 0.3);
    if (is_resource($connection)) {
        fclose($connection);
        return true;
    }
    return false;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuakcom Suite Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="header-section text-center mb-4">
    <div class="container"><h1 class="h4 fw-bold m-0"><i class="fa-solid fa-bolt me-2"></i>Cuakcom Expert Suite</h1></div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            
            <div class="card p-4 mb-4">
                <div class="row g-2 align-items-center justify-content-center">
                    <div class="col-md-9">
                        <form action="index.php" method="POST" id="form-analyzer">
                            <input type="hidden" name="tool" value="analyzer">
                            <div class="row g-2">
                                <div class="col-md-9">
                                    <input type="text" name="dominio" class="form-control form-control-lg" placeholder="ejemplo.com" required value="<?php echo isset($_POST['dominio']) ? htmlspecialchars($_POST['dominio']) : ''; ?>">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-dark btn-lg w-100 fw-bold" id="btn-submit">
                                        <span id="btn-text">ANALIZAR</span>
                                        <span id="btn-loading"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                    </button>
                                </div>
                                <div class="col-12">
                                    <div class="module-selectors d-flex gap-4 justify-content-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulos[]" value="dns" id="chk-dns" <?php echo (!isset($_POST['tool']) || in_array('dns', $_POST['modulos'] ?? [])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="chk-dns">DNS</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulos[]" value="puertos" id="chk-puertos" <?php echo (!isset($_POST['tool']) || in_array('puertos', $_POST['modulos'] ?? [])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="chk-puertos">Puertos</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulos[]" value="whois" id="chk-whois" <?php echo (!isset($_POST['tool']) || in_array('whois', $_POST['modulos'] ?? [])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="chk-whois">WHOIS</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <?php if (isset($_POST['tool'])): ?>
                    <div class="col-md-2">
                        <form action="index.php" method="POST">
                            <input type="hidden" name="action" value="download">
                            <input type="hidden" name="type" value="completo">
                            <input type="hidden" name="content" id="export-all-content" value="">
                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold"><i class="fa-solid fa-file-export me-1"></i> TODO</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($_POST['tool']) && !empty($_POST['dominio'])): 
                $dominio = explode('/', preg_replace('#^https?://(www\.)?#', '', trim($_POST['dominio'])))[0];
                $modulos_activos = $_POST['modulos'] ?? [];
                $totalExport = "DOMINIO: $dominio\n";
            ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <?php $ip = gethostbyname($dominio); $host = gethostbyaddr($ip); $redTxt = "RED:\nIP: $ip\nHOST: $host\n"; $totalExport .= "\n$redTxt"; ?>
                        <div class="card mb-3">
                            <div class="card-header-cuak">
                                <span class="header-badge bg-primary">Resolución</span>
                                <form action="index.php" method="POST" class="m-0">
                                    <input type="hidden" name="action" value="download"><input type="hidden" name="type" value="red">
                                    <input type="hidden" name="content" value="<?php echo $redTxt; ?>">
                                    <button type="submit" class="btn btn-link text-primary p-0"><i class="fa-solid fa-download"></i></button>
                                </form>
                            </div>
                            <div class="card-body p-3">
                                <div class="dns-row d-flex align-items-center gap-2 border-0"><span class="badge dns-badge bg-primary">IP</span><div class="dns-value text-primary fw-bold"><?php echo $ip; ?></div></div>
                                <div class="dns-row d-flex align-items-start gap-2 border-0"><span class="badge dns-badge bg-dark">HOST</span><div class="dns-value text-secondary small"><?php echo $host; ?></div></div>
                            </div>
                        </div>

                        <?php if (in_array('puertos', $modulos_activos)): ?>
                        <div class="card">
                            <div class="card-header-cuak">
                                <span class="header-badge bg-dark">Puertos</span>
                                <form action="index.php" method="POST" class="m-0">
                                    <input type="hidden" name="action" value="download"><input type="hidden" name="type" value="puertos">
                                    <input type="hidden" name="content" id="ports-export-content" value="">
                                    <button type="submit" class="btn btn-link text-dark p-0"><i class="fa-solid fa-download"></i></button>
                                </form>
                            </div>
                            <div class="card-body p-3">
                                <?php 
                                $puertoTxt = "PUERTOS:\n";
                                $categorias = [
                                    'Web' => [80 => 'HTTP', 443 => 'HTTPS', 8080 => 'PROXY'],
                                    'Correo' => [25 => 'SMTP', 465 => 'SMTPS', 587 => 'SMTP-S', 110 => 'POP3', 995 => 'POP3S', 143 => 'IMAP', 993 => 'IMAPS'],
                                    'Bases de Datos' => [3306 => 'MYSQL', 5432 => 'POSTGRE', 1433 => 'SQL'],
                                    'Acceso/Otros' => [22 => 'SSH', 3389 => 'RDP', 53 => 'DNS']
                                ];
                                foreach ($categorias as $cat => $ports): ?>
                                    <div class="port-group-title"><?php echo $cat; ?></div>
                                    <div class="row g-2">
                                        <?php foreach ($ports as $p => $label): 
                                            $isOpen = esPuertoAbierto($dominio, $p); 
                                            $puertoTxt .= "[$p] $label: ".($isOpen?'OPEN':'CLOSED')."\n"; 
                                        ?>
                                            <div class="col-6">
                                                <div class="port-row">
                                                    <span class="port-label <?php echo $isOpen ? 'open' : ''; ?>"><?php echo $label; ?></span>
                                                    <span class="port-number <?php echo $isOpen ? 'open' : 'text-muted'; ?>"><?php echo $p; ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; $totalExport .= "\n$puertoTxt"; ?>
                                <script>document.getElementById('ports-export-content').value = `<?php echo addslashes($puertoTxt); ?>`;</script>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-8">
                        <?php if (in_array('dns', $modulos_activos)): ?>
                        <div class="card h-100">
                            <div class="card-header-cuak">
                                <span class="header-badge bg-primary">DNS</span>
                                <form action="index.php" method="POST" class="m-0">
                                    <input type="hidden" name="action" value="download"><input type="hidden" name="type" value="dns">
                                    <input type="hidden" name="content" id="dns-export-content" value="">
                                    <button type="submit" class="btn btn-link text-primary p-0"><i class="fa-solid fa-download"></i></button>
                                </form>
                            </div>
                            <div class="card-body p-3"><div class="row">
                                <?php $dnsExport = "DNS:\n"; foreach ([['A', 'MX'], ['NS', 'TXT']] as $idx => $colGroup): ?>
                                    <div class="col-md-6 <?php echo $idx == 0 ? 'border-end' : ''; ?>">
                                        <?php foreach ($colGroup as $t): $regs = @dns_get_record($dominio, constant("DNS_$t"));
                                            echo "<div class='text-muted mt-2 mb-2 fw-bold text-uppercase' style='font-size:0.6rem;'>$t Records</div>";
                                            if ($regs): foreach ($regs as $r): $val = $r['ip'] ?? $r['target'] ?? $r['txt'] ?? '---'; $dnsExport .= "[$t] $val\n"; ?>
                                                <div class="dns-row d-flex align-items-start gap-2"><span class="badge dns-badge <?php echo obtenerColorDns($t); ?>"><?php echo $t; ?></span><div class="dns-value"><?php echo $val; ?></div></div>
                                        <?php endforeach; else: echo "<div class='small text-muted opacity-50'>No data</div>"; endif; endforeach; ?>
                                    </div>
                                <?php endforeach; $totalExport .= "\n$dnsExport"; ?>
                                <script>document.getElementById('dns-export-content').value = `<?php echo addslashes($dnsExport); ?>`;</script>
                            </div></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (in_array('whois', $modulos_activos)): ?>
                    <div class="col-12"><div class="card mt-3">
                        <div class="card-header-cuak">
                            <span class="header-badge bg-danger">Whois</span>
                            <form action="index.php" method="POST" class="m-0">
                                <?php $whoisData = obtenerWhois($dominio); $totalExport .= "\nWHOIS:\n$whoisData"; ?>
                                <input type="hidden" name="action" value="download"><input type="hidden" name="type" value="whois">
                                <input type="hidden" name="content" value="<?php echo htmlspecialchars($whoisData); ?>">
                                <button type="submit" class="btn btn-link text-danger p-0"><i class="fa-solid fa-download"></i></button>
                            </form>
                        </div>
                        <div class="card-body p-3"><div class="whois-scroll"><?php echo htmlspecialchars($whoisData); ?></div></div>
                    </div></div>
                    <?php endif; ?>
                </div>
                <script>document.getElementById('export-all-content').value = `<?php echo addslashes($totalExport); ?>`;</script>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('form-analyzer').onsubmit = function() {
        document.getElementById('btn-text').style.display = 'none';
        document.getElementById('btn-loading').style.display = 'inline-block';
        document.getElementById('btn-submit').classList.add('disabled');
    };
</script>
</body>
</html>