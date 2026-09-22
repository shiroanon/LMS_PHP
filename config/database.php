<?php
return [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'database' => $_ENV['DB_DATABASE'] ?? 'lms',
    'username' => $_ENV['DB_USERNAME'] ?? 'lms',
    'password' => $_ENV['DB_PASSWORD'] ?? 'lms_dev_2026',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
