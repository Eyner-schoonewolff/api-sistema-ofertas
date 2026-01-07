<?php

/**
 * Configuración general de la aplicación
 */

return [
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Bogota',
    'base_url' => $_ENV['BASE_URL'] ?? 'http://localhost:8000',
    'upload' => [
        'max_size' => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760), // 10MB por defecto
        'allowed_types' => explode(',', $_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'pdf,zip'),
        'path' => __DIR__ . '/../public/uploads'
    ]
];
