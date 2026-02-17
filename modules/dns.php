<?php
// modules/dns.php
$host_escaped_dns = escapeshellarg($host_clean);
$salida = shell_exec("dig $host_escaped_dns ANY +noall +answer 2>&1");

echo "<h3>3. Registros DNS (A, MX, TXT)</h3>";
echo "<table class='custom-table'>";
echo "<thead><tr><th>Entrada</th><th>Tipo</th><th>Valor / Destino</th></tr></thead>";
echo "<tbody>";

if ($salida) {
    $lineas = explode("\n", trim($salida));
    foreach ($lineas as $linea) {
        if (preg_match('/^(\S+)\s+\d+\s+IN\s+(\S+)\s+(.*)$/', $linea, $matches)) {
            $entrada = rtrim($matches[1], '.');
            $tipo    = $matches[2];
            $valor   = $matches[3];

            echo "<tr>";
            echo "<td><strong>$entrada</strong></td>";
            echo "<td><span class='badge badge-$tipo'>$tipo</span></td>";
            echo "<td><code>$valor</code></td>";
            echo "</tr>";
        }
    }
} else {
    echo "<tr><td colspan='3'>No se encontraron registros.</td></tr>";
}
echo "</tbody></table>";
?>