<?php
// modules/ping.php
$host_escaped_ping = escapeshellarg($host_clean);
echo ejecutarComando("1. Prueba de Conectividad (Ping)", "ping -c 4 $host_escaped_ping");
?>