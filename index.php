<?php
// index.php
require_once 'functions.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$host_clean = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['host'])) {
    $host_clean = limpiarHost($_POST['host']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analizador PRO | Inteligencia General</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>🔍 Herramienta de Diagnóstico</h1>
        <p>Introduce un dominio o IP para iniciar el escaneo.</p>
        
        <form method="post" onsubmit="document.getElementById('loader').style.display='block';">
            <div class="form-group">
                <input type="text" name="host" placeholder="ejemplo.com" 
                       value="<?php echo htmlspecialchars($host_clean); ?>" required autofocus>
                <button type="submit">Analizar Ahora</button>
            </div>
        </form>

        <div id="loader">
            <span>Consultando módulos del sistema...</span>
        </div>

        <?php if (!empty($host_clean)): ?>
            <div id="output">
                <?php 
                include 'modules/screenshot.php'; 
                include 'modules/dns.php'; 
                include 'modules/ping.php'; 
                include 'modules/nslookup.php';
                include 'modules/whois.php'; 
                ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>