<?php
// functions.php

function limpiarHost($host) {
    return preg_replace('/[^a-zA-Z0-9.-]/', '', trim((string)$host));
}

function ejecutarComando($titulo, $comando) {
    if (!function_exists('shell_exec')) {
        return "<h3>$titulo</h3><p style='color:red;'>Error: shell_exec deshabilitado.</p>";
    }
    
    $salida = shell_exec($comando . " 2>&1");
    
    $html = "<h3>$titulo</h3>";
    $html .= "<pre class='terminal'>";
    $html .= ($salida) ? htmlspecialchars(trim($salida)) : "Sin respuesta del comando.";
    $html .= "</pre>";
    return $html;
}
?>