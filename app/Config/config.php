<?php
/**
 * Application Configuration
 * Copy this file to config.local.php and customize for your environment
 */

return [
    // Database
    'db' => [
        'host'     => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_DATABASE'] ?? 'my_crm_database',
        'username' => $_ENV['DB_USERNAME'] ?? 'my_crm_user',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset'  => 'utf8mb4',
    ],

    // Application
    'app' => [
        'name'        => 'نظام إدارة العملاء',
        'env'         => $_ENV['APP_ENV'] ?? 'production',
        'debug'       => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
        'url'         => $_ENV['APP_URL'] ?? 'http://localhost',
        'timezone'    => 'Africa/Cairo',
        'locale'      => 'ar',
    ],

    // Session
    'session' => [
        'lifetime' => 7200, // 2 hours
        'secure'   => true,
        'httponly' => true,
    ],

    // Security
    'security' => [
        'csrf_token_name' => 'csrf_token',
        'encryption_key'  => $_ENV['ENCRYPTION_KEY'] ?? '',
    ],
];
