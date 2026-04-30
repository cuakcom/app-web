<?php
/**
 * Cuakcom Expert Suite - Funciones auxiliares compartidas
 */

/**
 * Limpia y normaliza un hostname/dominio de entrada.
 * Elimina protocolo, ruta, y caracteres no válidos.
 */
function limpiarHost(string $raw): string {
    $host = trim($raw);
    $host = preg_replace('#^https?://(www\.)?#i', '', $host);
    $host = explode('/', $host)[0];
    $host = explode('?', $host)[0];
    $host = preg_replace('/[^a-zA-Z0-9.\-]/', '', $host);
    return strtolower($host);
}
