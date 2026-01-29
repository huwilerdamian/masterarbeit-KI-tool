<?php

return [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'name' => $_ENV['DB_NAME'] ?? '',
    'user' => $_ENV['DB_USER'] ?? '',
    'pass' => $_ENV['DB_PASS'] ?? '',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'charset' => 'utf8mb4',
];
