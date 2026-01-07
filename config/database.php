<?php

/**
 * Configuración de conexión a MongoDB
 * 
 * Lee las variables de entorno para establecer la conexión
 * con la base de datos MongoDB.
 */

return [
    'url' => $_ENV['MONGODB_URL'] ?? 'mongodb://localhost:27017',
    'database' => $_ENV['MONGODB_DATABASE'] ?? 'sistema_ofertas',
    'options' => [
        'typeMap' => [
            'root' => 'array',
            'document' => 'array',
            'array' => 'array'
        ],
        'serverSelectionTimeoutMS' => 5000, // 5 segundos (reducido para fallar rápido)
        'connectTimeoutMS' => 5000,
        'socketTimeoutMS' => 10000, // 10 segundos para operaciones
        'serverSelectionTryOnce' => false, // Intentar múltiples veces
        'maxPoolSize' => 10
    ]
];
