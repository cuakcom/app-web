<?php
/**
 * Herramienta: Analizador de Red Profesional
 * Versión: 3.0 - Salida DNS en Tabla
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- FUNCIONES ---

/**
 * Ejecuta comando y devuelve texto plano para consola
 */
function ejecutarComando($titulo, $comando) {
    if (!function_exists('shell_exec')) return "<h3>$titulo</h3><p style='color:red;'>Error: shell_exec deshabilitado.</p>";
    $salida = shell_exec($comando . " 2>&1");
    $html = "<h3>$titulo</h3>";
    $html .= "<pre class='terminal'>" . (($salida) ? htmlspecialchars(trim($salida)) : "Sin respuesta.") . "</pre>";
    return $html;
}

/**
 * Ejecuta DIG y convierte la salida en una tabla HTML limpia
 */
function ejecutarDnsTabla($host) {
    $salida = shell_exec("dig " . escapeshellarg($host) . " ANY +noall +answer 2>&1");
    
    $html = "<h3>3. Registros DNS (A, MX, TXT)</h3>";
    
    if (empty(trim($salida))) {
        return $html . "<p>No se encontraron registros.</p>";
    }

    $html .= "<table class='dns-table'>";
    $html .= "<thead><tr><th>Entrada</th><th>Tipo</th><th>Valor / Destino</th></tr></thead>";
    $html .= "<tbody>";

    // Procesamos cada línea de la respuesta de dig
    $lineas = explode("\n", trim($salida));
    foreach ($lineas as $linea) {
        // Expresión regular para capturar: Dominio, TTL, Clase, TIPO, Valor
        // Ejemplo: arsys.es.  57  IN  A  217.76.128.28
        if (preg_match('/^(\S+)\s+\d+\s+IN\s+(\S+)\s+(.*)$/', $linea, $matches)) {
            $entrada = rtrim($matches[1], '.'); // Quitamos el punto final del dominio
            $tipo    = $matches[2];
            $valor   = $matches[3];

            $html .= "<tr>";
            $html .= "<td><strong>$entrada</strong></td>";
            $html .= "<td><span class='badge badge-$tipo'>$tipo</span></td>";
            $html .= "<td><code>$valor</code></td>";
            $html .= "</tr>";
        }
    }

    $html .= "</tbody></table>";
    return $html;
}

$resultado = "";
$host_clean = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['host'])) {
    $host_input = (string)$_POST['host'];
    $host_clean = preg_replace('/[^a-zA-Z0-9.-]/', '', $host_input);
    $host_escaped = escapeshellarg($host_clean);

    $resultado .= "<div class='results-container'>";
    $resultado .= "<h2>Análisis para: " . htmlspecialchars($host_clean) . "</h2>";

    $resultado .= ejecutarComando("1. Prueba de Ping", "ping -c 4 $host_escaped");
    $resultado .= ejecutarComando("2. Servidores DNS Autoritativos (NS)", "dig $host_escaped NS +short");
    
    // --- NUEVA SALIDA EN TABLA ---
    $resultado .= ejecutarDnsTabla($host_clean);

    $resultado .= ejecutarComando("4. Resolución Global (8.8.8.8)", "nslookup $host_escaped 8.8.8.8");

    $es_dominio_es = (substr(strtolower($host_clean), -3) === '.es');
    if ($es_dominio_es) {
        $resultado .= "<h3>5. Datos WHOIS</h3><div class='notice-box'>Consulta manual en NIC.es</div>";
    } else {
        $resultado .= ejecutarComando("5. Registro WHOIS", "whois $host_escaped");
    }
    $resultado .= "</div>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Analizador de Red | Inteligencia General</title>
    <style>
        :root { --primary: #007bff; --bg: #f4f7fa; --dark: #1e1e1e; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        /* Estilos de la Tabla DNS */
        .dns-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .dns-table th { background: #f8f9fa; color: #333; text-align: left; padding: 12px; border-bottom: 2px solid #dee2e6; }
        .dns-table td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        .dns-table tr:hover { background-color: #fcfcfc; }
        
        /* Badges para tipos de registro */
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; color: white; background: #6c757d; }
        .badge-A { background: #28a745; }
        .badge-MX { background: #007bff; }
        .badge-NS { background: #6f42c1; }
        .badge-TXT { background: #fd7e14; }
        .badge-SOA { background: #e83e8c; }

        .terminal { background: var(--dark); color: #33ff33; padding: 15px; border-radius: 8px; overflow-x: auto; font-family: monospace; border-left: 5px solid var(--primary); }
        .form-group { display: flex; gap: 10px; margin: 20px 0; }
        input { flex-grow: 1; padding: 12px; border: 1px solid #ccc; border-radius: 6px; }
        button { padding: 12px 25px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        
        #loader { display: none; text-align: center; padding: 20px; }
        .spinner { width: 30px; height: 30px; border: 4px solid #f3f3f3; border-top: 4px solid var(--primary); border-radius: 50%; animation: spin 1s linear infinite; display: inline-block; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="container">
    <h1>🔍 Herramienta de Diagnóstico</h1>
    <form method="post" onsubmit="document.getElementById('loader').style.display='block';">
        <div class="form-group">
            <input type="text" name="host" placeholder="Ej: arsys.es" value="<?php echo htmlspecialchars($host_clean); ?>" required>
            <button type="submit">Analizar</button>
        </div>
    </form>

    <div id="loader"><div class="spinner"></div><br>Procesando...</div>

    <div id="output">
        <?php echo $resultado; ?>
    </div>
</div>

</body>
</html>