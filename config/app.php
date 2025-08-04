<?php

return [
    'app' => [
        'name' => 'Engenha Rio',
        'version' => '1.0.0',
        'environment' => 'development',
        'debug' => true,
        'timezone' => 'America/Sao_Paulo',
        'charset' => 'UTF-8'
    ],
    'database' => [
        'type' => 'json',
        'path' => __DIR__ . '/../data/'
    ],
    'upload' => [
        'path' => __DIR__ . '/../public/uploads/',
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xls', 'xlsx']
    ],
    'session' => [
        'name' => 'engenha_rio_session',
        'lifetime' => 86400, // 24 horas
        'secure' => false,
        'httponly' => true,
        'save_path' => '' // Will be auto-detected or use default
    ],
    'hostinger' => [
        'environment' => true,
        'base_path' => '' // Leave empty for auto-detection
    ]
];
