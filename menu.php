<?php
/**
 * Check Berry - Árbol de navegación.
 *
 * Único sitio que hay que tocar para añadir un apartado principal nuevo.
 * Cada entrada de nivel superior apunta a un archivo en /sections que
 * contiene su contenido (y, si tiene sub-apartados propios, los resuelve
 * internamente con su propia navegación izquierda).
 *
 * 'placeholder' => true = apartado aún sin utilidades (aparece en el menú,
 * pero muestra un aviso "en construcción") hasta que una fase posterior le
 * añada contenido real tocando solo el archivo de esa sección.
 *
 * 'btn_id'/'pane_id' deben coincidir con los id= usados dentro del archivo
 * de la sección (data-bs-target) y con las reglas de color en style.css.
 */

const MENU = [
    [
        'key'     => 'diagnostico',
        'label'   => 'Diagnóstico',
        'icon'    => 'fa-magnifying-glass-chart',
        'file'    => __DIR__ . '/sections/diagnostico.php',
        'btn_id'  => 'tab-diag-btn',
        'pane_id' => 'tab-diagnostico',
        'info'    => true,
    ],
    [
        'key'     => 'web',
        'label'   => 'Web',
        'icon'    => 'fa-globe',
        'file'    => __DIR__ . '/sections/web.php',
        'btn_id'  => 'tab-web-btn',
        'pane_id' => 'tab-web',
        'info'    => true,
    ],
    [
        'key'     => 'correo',
        'label'   => 'Correo',
        'icon'    => 'fa-envelope',
        'file'    => __DIR__ . '/sections/correo.php',
        'btn_id'  => 'tab-mail-btn',
        'pane_id' => 'tab-correo',
        'info'    => true,
    ],
    [
        'key'     => 'dns',
        'label'   => 'DNS',
        'icon'    => 'fa-terminal',
        'file'    => __DIR__ . '/sections/dns.php',
        'btn_id'  => 'tab-dns-btn',
        'pane_id' => 'tab-dns',
        'info'    => true,
    ],
    [
        'key'     => 'redes',
        'label'   => 'Redes',
        'icon'    => 'fa-network-wired',
        'file'    => __DIR__ . '/sections/redes.php',
        'btn_id'  => 'tab-redes-btn',
        'pane_id' => 'tab-redes',
        'info'    => true,
    ],
    [
        'key'         => 'utilidades',
        'label'       => 'Utilidades',
        'icon'        => 'fa-toolbox',
        'file'        => __DIR__ . '/sections/utilidades.php',
        'btn_id'      => 'tab-util-btn',
        'pane_id'     => 'tab-utilidades',
        'placeholder' => true,
    ],
    [
        'key'         => 'herramientas',
        'label'       => 'Herramientas',
        'icon'        => 'fa-wrench',
        'file'        => __DIR__ . '/sections/herramientas.php',
        'btn_id'      => 'tab-herr-btn',
        'pane_id'     => 'tab-herramientas',
        'placeholder' => true,
    ],
    [
        'key'         => 'desarrollo',
        'label'       => 'Desarrollo',
        'icon'        => 'fa-code',
        'file'        => __DIR__ . '/sections/desarrollo.php',
        'btn_id'      => 'tab-dev-btn',
        'pane_id'     => 'tab-desarrollo',
        'placeholder' => true,
    ],
];
