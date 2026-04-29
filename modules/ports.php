<?php
// modules/ports.php - Escáner de Puertos
// Expects: $dominio (string), esPuertoAbierto() function in scope

$categorias = [
    'Web'            => [80 => 'HTTP',   443 => 'HTTPS',   8080 => 'PROXY'],
    'Correo'         => [25 => 'SMTP',   465 => 'SMTPS',   587 => 'SMTP-S', 110 => 'POP3', 995 => 'POP3S', 143 => 'IMAP', 993 => 'IMAPS'],
    'Bases de Datos' => [3306 => 'MYSQL', 5432 => 'POSTGRE', 1433 => 'SQL'],
    'Acceso/Otros'   => [22 => 'SSH',    3389 => 'RDP',    53 => 'DNS'],
];

foreach ($categorias as $cat => $ports): ?>
    <div class="port-group-title"><?= htmlspecialchars($cat) ?></div>
    <div class="row g-2">
        <?php foreach ($ports as $p => $label):
            $isOpen = esPuertoAbierto($dominio, $p); ?>
            <div class="col-6">
                <div class="port-row">
                    <span class="port-label <?= $isOpen ? 'open' : '' ?>"><?= htmlspecialchars($label) ?></span>
                    <span class="port-number <?= $isOpen ? 'open' : 'text-muted' ?>"><?= $p ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach;
