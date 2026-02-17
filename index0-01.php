<?php
/**
 * Herramienta: Analizador de Red Profesional con Spinner
 * Servidor: Ubuntu 20 + Plesk
 * VER 0.01
 */

// 1. Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Definición de Funciones (Estructura robusta)
function ejecutarComando($titulo, $comando) {
    if (!function_exists('shell_exec')) {
        return "<h3>$titulo</h3><p style='color:red;'>Error: 'shell_exec' desactivado en PHP.</p>";
    }
    
    // Ejecutamos capturando salida y errores
    $salida = shell_exec($comando . " 2>&1");
    
    $html = "<h3>$titulo</h3>";
    $html .= "<pre class='terminal'>";
    $html .= ($salida) ? htmlspecialchars(trim($salida)) : "Sin respuesta del comando.";
    $html .= "</pre>";
    return $html;
}

$resultado = "";
$host_clean = "";

// 3. Lógica de Procesamiento al recibir el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['host'])) {
    
    $host_input = (string)$_POST['host'];
    $host_clean = preg_replace('/[^a-zA-Z0-9.-]/', '', $host_input);
    $host_escaped = escapeshellarg($host_clean);

    $resultado .= "<div class='results-container' id='resultsBlock'>";
    $resultado .= "<h2>Análisis para: " . htmlspecialchars($host_clean) . "</h2>";

    // --- BLOQUE DE COMANDOS ---
    $resultado .= ejecutarComando("1. Prueba de Ping", "ping -c 4 $host_escaped");
    $resultado .= ejecutarComando("2. Servidores DNS Autoritativos (NS)", "dig $host_escaped NS +short");
    $resultado .= ejecutarComando("3. Registros DNS (A, MX, TXT)", "dig $host_escaped ANY +noall +answer");
    $resultado .= ejecutarComando("4. Resolución via Google DNS (8.8.8.8)", "nslookup $host_escaped 8.8.8.8");

    // --- LÓGICA WHOIS ---
    $es_dominio_es = (substr(strtolower($host_clean), -3) === '.es');
    if ($es_dominio_es) {
        $resultado .= "<h3>5. Datos WHOIS</h3>";
        $resultado .= "<div class='notice-box'>Nota: Los dominios .es requieren consulta manual en <a href='https://www.nic.es/' target='_blank'>NIC.es</a>.</div>";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analizador de Red | Inteligencia General</title>
    <style>
        :root { --primary: #007bff; --bg: #f4f7fa; --dark: #1e1e1e; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: var(--bg); padding: 20px; color: #333; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        h1 { color: #2c3e50; border-bottom: 3px solid var(--primary); padding-bottom: 10px; margin-top: 0; }
        
        .form-group { display: flex; gap: 10px; margin: 20px 0; }
        input[type="text"] { flex-grow: 1; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px; outline: none; }
        input[type="text"]:focus { border-color: var(--primary); }
        
        button { padding: 12px 25px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        button:hover { background: #0056b3; }
        button:disabled { background: #ccc; cursor: not-allowed; }

        .terminal { background: var(--dark); color: #33ff33; padding: 15px; border-radius: 8px; overflow-x: auto; font-family: 'Consolas', monospace; font-size: 14px; border-left: 5px solid var(--primary); margin-bottom: 20px; }
        
        .notice-box { background-color: #fff3cd; color: #856404; padding: 15px; border: 1px solid #ffeeba; border-radius: 6px; margin-bottom: 20px; }

        /* --- ESTILOS DEL CARGADOR (SPINNER) --- */
        #loader { display: none; text-align: center; padding: 30px; background: #f9f9f9; border-radius: 8px; border: 1px dashed #ccc; margin: 20px 0; }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
            vertical-align: middle;
        }

        .loader-text { display: block; margin-top: 10px; font-weight: bold; color: var(--primary); }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .results-container { animation: fadeIn 0.6s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>

<div class="container">
    <h1>🔍 Diagnóstico de Red</h1>
    <p>Consulta datos autoritativos, conectividad y registros de dominio.</p>
    
    <form method="post" id="analizadorForm" onsubmit="activarCarga()">
        <div class="form-group">
            <input type="text" name="host" id="hostInput" placeholder="Ej: google.com o 8.8.8.8" 
                   value="<?php echo htmlspecialchars($host_clean); ?>" required>
            <button type="submit" id="btnSubmit">Analizar Ahora</button>
        </div>
    </form>

    <div id="loader">
        <div class="spinner"></div>
        <span class="loader-text">Consultando servidores internacionales...</span>
        <small>Esto puede tardar hasta 10 segundos debido a los tiempos de respuesta de red.</small>
    </div>

    <div id="output">
        <?php echo $resultado; ?>
    </div>
</div>

<script>
    function activarCarga() {
        // 1. Deshabilitar el botón para evitar múltiples envíos
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerText = 'Analizando...';

        // 2. Mostrar el div del spinner
        document.getElementById('loader').style.display = 'block';

        // 3. Atenuar los resultados anteriores si los hay
        const output = document.getElementById('output');
        if(output) {
            output.style.opacity = '0.3';
        }
    }
</script>

</body>
</html>