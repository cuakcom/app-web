<?php
// modules/screenshot.php

// Obtenemos la IP de forma simple
$ip_dest = gethostbyname($host_clean);

// Usamos Thum.io, que es más estable y rápido. 
// auth=123 es un parámetro genérico, puedes usarlo sin registro para pruebas.
$url_captura = "https://image.thum.io/get/width/400/crop/800/noanimate/https://" . $host_clean;
?>

<div class="premium-header">
    <div class="header-left">
        <h2>Resumen del Host</h2>
        <table class="presentation-table">
            <tr><th>Dominio:</th><td><?php echo htmlspecialchars($host_clean); ?></td></tr>
            <tr><th>IP Detectada:</th><td><mark><?php echo $ip_dest; ?></mark></td></tr>
            <tr><th>Estado:</th><td><span class="status-dot"></span> Online</td></tr>
        </table>
    </div>
    <div class="header-right">
        <div class="img-frame">
            <img src="<?php echo $url_captura; ?>" 
                 alt="Captura de pantalla" 
                 class="web-thumb" 
                 onload="this.style.opacity='1'"
                 onerror="this.src='https://placehold.jp/24/cccccc/ffffff/400x300.png?text=Vista+Previa+No+Disponible';">
        </div>
    </div>
</div>