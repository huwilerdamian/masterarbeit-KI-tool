<?php
// Zentrale Initialisierung der Anwendung.

// .env laden (einfacher Loader, nur wenn die Datei existiert)
$envPath = __DIR__ . '/.env';
if (is_file($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $value = trim($value, "\"'");

        if ($key !== '' && getenv($key) === false) {
            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}

$config = require __DIR__ . '/config/app.php';

require __DIR__ . '/src/database.php';
