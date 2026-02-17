<?php
// modules/whois.php
$host_escaped_whois = escapeshellarg($host_clean);
$es_dominio_es = (substr(strtolower($host_clean), -3) === '.es');

if ($es_dominio_es) {
    echo "<h3>5. Registro WHOIS</h3>";
    echo "<div class='notice-box'>Dominios .es requieren consulta manual en <a href='https://www.nic.es/' target='_blank'>NIC.es</a>.</div>";
} else {
    echo ejecutarComando("5. Registro WHOIS", "whois $host_escaped_whois");
}
?>