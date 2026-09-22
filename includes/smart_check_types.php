<?php
/**
 * Check Berry - Smart Check: catálogo de tipos de comprobación.
 *
 * Cada entrada define qué módulos reales se ejecutan (con sus parámetros
 * extra) y a qué apartados de la app conviene enlazar después. Único
 * archivo a tocar para añadir una opción nueva al desplegable del Smart
 * Check (smart_check.php y sections/herramientas.php lo comparten).
 */

const SMART_CHECK_TYPES = [
    'spam' => [
        'label'    => '¿Está en listas de spam?',
        'modules'  => [['blacklist', []], ['abuseipdb', []]],
        'sections' => [
            ['label' => 'Correo · Reputación', 'tab' => 'correo'],
            ['label' => 'Redes', 'tab' => 'redes'],
        ],
    ],
    'hackeo' => [
        'label'    => '¿Hackeo o filtrado?',
        'modules'  => [['abuseipdb', []], ['blacklist', []]],
        'sections' => [
            ['label' => 'Redes', 'tab' => 'redes'],
            ['label' => 'Correo · Reputación', 'tab' => 'correo'],
        ],
        'nota' => 'No comprobamos filtraciones de contraseñas/datos personales (necesitaría una API de pago tipo HaveIBeenPwned, todavía no configurada). Esto solo mira reputación de IP y listas negras.',
    ],
    'existe' => [
        'label'    => '¿Existe el dominio/cuenta?',
        'modules'  => [['resolution', []], ['whois', []], ['dns', []]],
        'sections' => [
            ['label' => 'Diagnóstico', 'tab' => 'diagnostico'],
            ['label' => 'DNS', 'tab' => 'dns'],
        ],
    ],
    'reputacion_ip' => [
        'label'    => 'Reputación de la IP',
        'modules'  => [['abuseipdb', []], ['geoip', []]],
        'sections' => [
            ['label' => 'Redes', 'tab' => 'redes'],
        ],
    ],
    'config_correo' => [
        'label'    => 'Configuración de correo',
        'modules'  => [['dns', ['types' => 'MX,SPF,DMARC,DKIM']], ['mailtest', []]],
        'sections' => [
            ['label' => 'Correo', 'tab' => 'correo'],
            ['label' => 'DNS', 'tab' => 'dns'],
        ],
    ],
    'ssl' => [
        'label'    => 'Certificado SSL',
        'modules'  => [['ssl', []], ['sslscan', []]],
        'sections' => [
            ['label' => 'Web · SSL/TLS', 'tab' => 'web'],
        ],
    ],
    'caducidad_dominio' => [
        'label'    => 'Caducidad del dominio',
        'modules'  => [['whois', []]],
        'sections' => [
            ['label' => 'Diagnóstico', 'tab' => 'diagnostico'],
            ['label' => 'DNS', 'tab' => 'dns'],
        ],
    ],
];

const SMART_CHECK_DEFAULT_MODULES = [['resolution', []], ['whois', []], ['dns', []]];
