<?php

/**
 * Punto de entrada principal de la API
 * 
 * Este archivo se encarga de:
 * - Cargar el autoloader de Composer
 * - Cargar las variables de entorno
 * - Configurar el manejo de errores
 * - Inicializar el enrutador
 */

// Definir constantes
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Cargar autoloader de Composer
require_once ROOT_PATH . '/vendor/autoload.php';

// Cargar variables de entorno
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Saltar comentarios
        }
        
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remover comillas si existen
            $value = trim($value, '"\'');
            
            if (!empty($key)) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

// Configurar zona horaria
$timezone = $_ENV['APP_TIMEZONE'] ?? 'America/Bogota';
date_default_timezone_set($timezone);

// Configurar manejo de errores
$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Manejar errores fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Error interno del servidor',
            'errors' => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
});

// Manejar excepciones no capturadas
set_exception_handler(function($exception) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    
    $debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
    
    // Detectar errores de conexión a MongoDB
    $message = $exception->getMessage();
    $isMongoError = strpos($message, 'No servers') !== false || 
                    strpos($message, 'connection') !== false ||
                    strpos($message, 'ConnectionException') !== false ||
                    strpos($message, 'MongoDB') !== false;
    
    if ($isMongoError) {
        http_response_code(503); // Service Unavailable
        $response = [
            'success' => false,
            'message' => 'No se pudo conectar a la base de datos MongoDB. Verifica que el servidor esté corriendo en ' . ($_ENV['MONGODB_URL'] ?? 'mongodb://localhost:27017'),
            'errors' => []
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Error interno del servidor',
            'errors' => []
        ];
    }
    
    if ($debug) {
        $response['debug'] = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'type' => get_class($exception)
        ];
        
        // Solo incluir trace en modo debug extremo
        if (isset($_ENV['APP_DEBUG_TRACE']) && $_ENV['APP_DEBUG_TRACE'] === 'true') {
            $response['debug']['trace'] = $exception->getTraceAsString();
        }
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
});

// Cargar rutas
$routes = require ROOT_PATH . '/routes/api.php';

// Inicializar y ejecutar el enrutador
use App\Helpers\Router;

try {
    $router = new Router($routes);
    $router->resolve();
} catch (\Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    
    $debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
    
    $response = [
        'success' => false,
        'message' => 'Error al procesar la petición',
        'errors' => []
    ];
    
    if ($debug) {
        $response['debug'] = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
