<?php
/**
 * Check Berry - Conexión SQLite (usuarios, whitelist, roadmap).
 * Crea el archivo y las tablas la primera vez que se usa.
 */

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dir = __DIR__ . '/../data';
    if (!is_dir($dir)) {
        @mkdir($dir, 0770, true);
    }

    $pdo = new PDO('sqlite:' . $dir . '/app.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');

    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        email         TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        is_admin      INTEGER NOT NULL DEFAULT 0,
        created_at    TEXT NOT NULL DEFAULT (datetime(\'now\'))
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS allowed_emails (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        pattern    TEXT UNIQUE NOT NULL,
        created_at TEXT NOT NULL DEFAULT (datetime(\'now\'))
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS roadmap_items (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        title       TEXT NOT NULL,
        description TEXT NOT NULL DEFAULT \'\',
        status      TEXT NOT NULL DEFAULT \'pendiente\',
        source      TEXT NOT NULL DEFAULT \'manual\',
        created_by  TEXT NOT NULL DEFAULT \'\',
        created_at  TEXT NOT NULL DEFAULT (datetime(\'now\')),
        updated_at  TEXT NOT NULL DEFAULT (datetime(\'now\'))
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS roadmap_imports (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        source_file TEXT UNIQUE NOT NULL,
        imported_at TEXT NOT NULL DEFAULT (datetime(\'now\'))
    )');

    seed_allowed_emails($pdo);

    return $pdo;
}

/**
 * Siembra la whitelist inicial acordada (solo si la tabla está vacía,
 * para no pisar cambios hechos luego desde el panel admin).
 */
function seed_allowed_emails(PDO $pdo): void {
    $count = (int)$pdo->query('SELECT COUNT(*) FROM allowed_emails')->fetchColumn();
    if ($count > 0) {
        return;
    }
    $stmt = $pdo->prepare('INSERT INTO allowed_emails (pattern) VALUES (?)');
    foreach (['jfernandez@arsys.es', '*@arsys.es', '*@cuak.com'] as $pattern) {
        $stmt->execute([$pattern]);
    }
}
