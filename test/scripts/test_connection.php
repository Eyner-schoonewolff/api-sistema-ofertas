<?php

/**
 * Script de prueba de conexión a MongoDB
 * 
 * Ejecutar con: php test_connection.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Permitir pasar la URL como argumento: php test_connection.php "mongodb+srv://..."
$mongodbUrlArg = $argv[1] ?? null;

// Cargar variables de entorno
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $value = trim($value, '"\'');
                
                if (!empty($key)) {
                    $_ENV[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
        }
    }
}

// Si se pasó la URL como argumento, usarla
if ($mongodbUrlArg !== null) {
    $_ENV['MONGODB_URL'] = $mongodbUrlArg;
    putenv("MONGODB_URL={$mongodbUrlArg}");
}

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     PRUEBA DE CONEXIÓN A MONGODB                             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Obtener configuración
$mongodbUrl = $_ENV['MONGODB_URL'] ?? 'mongodb://localhost:27017';
$mongodbDatabase = $_ENV['MONGODB_DATABASE'] ?? 'sistema_ofertas';

echo "Configuración:\n";
echo "  URL: " . (strpos($mongodbUrl, '@') !== false ? preg_replace('/:\/\/[^:]+:[^@]+@/', '://***:***@', $mongodbUrl) : $mongodbUrl) . "\n";
echo "  Base de datos: {$mongodbDatabase}\n\n";

try {
    echo "1. Creando cliente MongoDB...\n";
    $config = require __DIR__ . '/config/database.php';
    
    $options = array_merge([
        'serverSelectionTimeoutMS' => 10000, // 10 segundos para Atlas
        'connectTimeoutMS' => 10000,
        'socketTimeoutMS' => 30000,
    ], $config['options'] ?? []);
    
    $client = new \MongoDB\Client($config['url'], [], $options);
    echo "   ✓ Cliente creado\n\n";
    
    echo "2. Verificando conexión al servidor...\n";
    // Intentar listar bases de datos (operación ligera)
    $adminDb = $client->selectDatabase('admin');
    $result = $adminDb->command(['ping' => 1]);
    echo "   ✓ Conexión exitosa al servidor\n\n";
    
    echo "3. Verificando acceso a la base de datos '{$mongodbDatabase}'...\n";
    $database = $client->selectDatabase($mongodbDatabase);
    
    // Intentar listar colecciones
    $collections = $database->listCollections();
    $collectionCount = 0;
    foreach ($collections as $collection) {
        $collectionCount++;
    }
    echo "   ✓ Base de datos accesible\n";
    echo "   ✓ Colecciones encontradas: {$collectionCount}\n\n";
    
    echo "4. Probando operación de lectura...\n";
    $testCollection = $database->selectCollection('test_connection');
    $testCollection->findOne([]);
    echo "   ✓ Operación de lectura exitosa\n\n";
    
    echo "5. Probando operación de escritura...\n";
    $testDoc = [
        'test' => true,
        'timestamp' => new \MongoDB\BSON\UTCDateTime(),
        'message' => 'Prueba de conexión'
    ];
    $result = $testCollection->insertOne($testDoc);
    echo "   ✓ Operación de escritura exitosa\n";
    echo "   ✓ Documento insertado con ID: " . $result->getInsertedId() . "\n\n";
    
    echo "6. Limpiando documento de prueba...\n";
    $testCollection->deleteOne(['_id' => $result->getInsertedId()]);
    echo "   ✓ Documento eliminado\n\n";
    
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║     ✓ CONEXIÓN EXITOSA                                        ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n\n";
    
    echo "Resumen:\n";
    echo "  ✓ Cliente MongoDB creado correctamente\n";
    echo "  ✓ Conexión al servidor establecida\n";
    echo "  ✓ Base de datos '{$mongodbDatabase}' accesible\n";
    echo "  ✓ Operaciones de lectura funcionando\n";
    echo "  ✓ Operaciones de escritura funcionando\n\n";
    
    exit(0);
    
} catch (\MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
    echo "\n✗ ERROR: Timeout de conexión\n";
    echo "  Mensaje: " . $e->getMessage() . "\n";
    echo "\nPosibles causas:\n";
    echo "  - El servidor MongoDB no está accesible\n";
    echo "  - La URL de conexión es incorrecta\n";
    echo "  - Problemas de red/firewall\n";
    echo "  - Para MongoDB Atlas: Verifica la IP whitelist\n";
    exit(1);
    
} catch (\MongoDB\Driver\Exception\AuthenticationException $e) {
    echo "\n✗ ERROR: Fallo de autenticación\n";
    echo "  Mensaje: " . $e->getMessage() . "\n";
    echo "\nPosibles causas:\n";
    echo "  - Usuario o contraseña incorrectos\n";
    echo "  - El usuario no tiene permisos en la base de datos\n";
    exit(1);
    
} catch (\Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "  Tipo: " . get_class($e) . "\n";
    echo "  Archivo: " . $e->getFile() . "\n";
    echo "  Línea: " . $e->getLine() . "\n";
    
    $message = $e->getMessage();
    if (strpos($message, 'No servers') !== false) {
        echo "\nPosibles causas:\n";
        echo "  - MongoDB no está corriendo\n";
        echo "  - La URL de conexión es incorrecta\n";
        echo "  - Para MongoDB Atlas: Verifica la IP whitelist (debe incluir 0.0.0.0/0 o tu IP)\n";
    }
    
    exit(1);
}
