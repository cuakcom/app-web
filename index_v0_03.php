<?php
/**
 * Herramienta: Analizador PRO - Versión 6.0 (Captura Optimizada)
 * Servidor: Ubuntu 20 + Plesk
 */

// 1. Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Funciones de Sistema
function ejecutarComando($titulo, $comando) {
    if (!function_exists('shell_exec')) return "<h3>$titulo</h3><p style='color:red;'>Error: shell_exec bloqueado.</p>";
    $salida = shell_exec($comando . " 2>&1");
    $html = "<h3>$titulo</h3><pre class='terminal'>";
    $html .= ($salida) ? htmlspecialchars(trim($salida)) : "Sin respuesta del sistema.";
    $html .= "</pre>";
    return $html;
}

function generarDnsTabla($host) {
    $salida = shell_exec("dig " . escapeshellarg($host) . " ANY +noall +answer 2>&1");
    $html = "<h3>Registros DNS (Estructurados)</h3>";
    $html .= "<table class='custom-table'><thead><tr><th>Entrada</th><th>Tipo</th><th>Valor</th></tr></thead><tbody>";
    
    if ($salida) {
        $lineas = explode("\n", trim($salida));
        foreach ($lineas as $linea) {
            if (preg_match('/^(\S+)\s+\d+\s+IN\s+(\S+)\s+(.*)$/', $linea, $matches)) {
                $entrada = rtrim($matches[1], '.');
                $tipo = $matches[2];
                $valor = $matches[3];
                $html .= "<tr><td>$entrada</td><td><span class='badge badge-$tipo'>$tipo</span></td><td><code>$valor</code></td></tr>";
            }
        }
    } else {
        $html .= "<tr><td colspan='3'>No se detectaron registros DNS públicos.</td></tr>";
    }
    $html .= "</tbody></table>";
    return $html;
}

// 3. Variables de aplicación
$resultado = "";
$cabecera = "";
$host_clean = "";

// 4. Lógica Principal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['host'])) {
    $host_input = (string)$_POST['host'];
    $host_clean = preg_replace('/[^a-zA-Z0-9.-]/', '', $host_input);
    $host_escaped = escapeshellarg($host_clean);

    // IP y URL de captura
    $ip_dest = gethostbyname($host_clean);
    // Usamos una API alternativa (Thum.io) que suele ser muy fiable para pruebas
    $url_captura = "https://image.thum.io/get/width/400/crop/800/noanimate/https://" . $host_clean;

    $cabecera = "
    <div class='premium-header'>
        <div class='header-left'>
            <h2>Resumen del Host</h2>
            <table class='presentation-table'>
                <tr><th>Dominio:</th><td>$host_clean</td></tr>
                <tr><th>IP Detectada:</th><td><mark>$ip_dest</mark></td></tr>
                <tr><th>Estado:</th><td><span class='status-dot'></span> Online</td></tr>
            </table>
        </div>
        <div class='header-right'>
            <div class='img-frame' id='frame'>
                <p id='loading-img' style='position:absolute; padding:20px; color:#666;'>Cargando captura...</p>
                <img src='$url_captura' alt='Captura Web' class='web-thumb' onload='document.getElementById(\"loading-img\").style.display=\"none\"' onerror='this.src=\"https://placehold.jp/24/cccccc/ffffff/400x300.png?text=Captura+No+Disponible\"'>
            </div>
        </div>
    </div>";

    $resultado .= "<div class='results-container'>";
    $resultado .= generarDnsTabla($host_clean);
    $resultado .= ejecutarComando("Prueba de Conectividad (Ping)", "ping -c 4 $host_escaped");
    $resultado .= ejecutarComando("Resolución Global (Google DNS)", "nslookup $host_escaped 8.8.8.8");

    if (substr(strtolower($host_clean), -3) === '.es') {
        $resultado .= "<h3>Registro WHOIS</h3><div class='notice-box'>Consulta manual en <strong>nic.es</strong> (Restricción de Red.es para consultas automáticas).</div>";
    } else {
        $resultado .= ejecutarComando("Registro WHOIS", "whois $host_escaped");
    }
    $resultado .= "</div>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analizador de Red | Inteligencia General</title>
    <style>
        :root { --primary: #007bff; --accent: #28a745; --dark: #1e1e1e; --gray: #f4f6f9; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--gray); margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); }
        
        /* Cabecera */
        .premium-header { display: flex; flex-wrap: wrap; gap: 20px; background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 12px; margin-bottom: 30px; align-items: center; }
        .header-left, .header-right { flex: 1; min-width: 280px; }
        .presentation-table { width: 100%; border-collapse: collapse; }
        .presentation-table th, .presentation-table td { text-align: left; padding: 10px; border-bottom: 1px solid #eee; }
        
        .img-frame { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fafafa; position: relative; min-height: 200px; display: flex; justify-content: center; align-items: center; }
        .web-thumb { width: 100%; height: auto; display: block; z-index: 10; transition: 0.3s; }
        .web-thumb:hover { transform: scale(1.02); }

        /* Estética */
        .terminal { background: var(--dark); color: #00ff00; padding: 15px; border-radius: 8px; overflow-x: auto; font-family: 'Consolas', monospace; border-left: 5px solid var(--primary); margin-bottom: 20px; font-size: 13px; }
        .custom-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .custom-table th { background: #f8f9fa; padding: 10px; text-align: left; border-bottom: 2px solid #ddd; font-size: 14px; }
        .custom-table td { padding: 10px; border-bottom: 1px solid #eee; font-size: 13px; }
        
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 10px; color: #fff; font-weight: bold; text-transform: uppercase; }
        .badge-A { background: #28a745; } .badge-MX { background: #007bff; } .badge-NS { background: #6f42c1; } .badge-TXT { background: #fd7e14; }
        
        .status-dot { height: 10px; width: 10px; background: var(--accent); border-radius: 50%; display: inline-block; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }

        .form-group { display: flex; gap: 10px; margin-bottom: 30px; }
        input { flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 16px; }
        button { padding: 12px 20px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; }
        
        #loader { display: none; text-align: center; margin-bottom: 20px; color: var(--primary); font-weight: bold; }
        .notice-box { background: #fff3cd; padding: 15px; border: 1px solid #ffeeba; border-radius: 8px; color: #856404; }
        mark { background: #e7f3ff; color: #007bff; padding: 2px 4px; border-radius: 3px; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔍 Herramienta de Diagnóstico</h1>
    <p>Análisis en tiempo real de infraestructura DNS y conectividad.</p>
    
    <form method="post" onsubmit="document.getElementById('loader').style.display='block';">
        <div class="form-group">
            <input type="text" name="host" placeholder="Ej: arsys.es o google.com" value="<?php echo htmlspecialchars($host_clean); ?>" required>
            <button type="submit">Analizar Ahora</button>
        </div>
    </form>

    <div id="loader">⚙️ Consultando infraestructura... por favor espera.</div>

    <?php echo $cabecera; ?>
    <?php echo $resultado; ?>
</div>

</body>
</html>