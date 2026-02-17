<?php
// modules/nslookup.php
$host_escaped_ns = escapeshellarg($host_clean);
echo ejecutarComando("4. Resolución Global (Google DNS 8.8.8.8)", "nslookup $host_escaped_ns 8.8.8.8");
?>