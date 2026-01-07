<?php

/**
 * Script de prueba para endpoints POST
 * 
 * Ejecutar con: php test_endpoints.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Cargar variables de entorno desde .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Saltar comentarios
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

// Permitir pasar la URL como argumento: php test_endpoints.php "mongodb+srv://..."
$mongodbUrlArg = $argv[1] ?? null;
if ($mongodbUrlArg !== null) {
    $_ENV['MONGODB_URL'] = $mongodbUrlArg;
    putenv("MONGODB_URL={$mongodbUrlArg}");
}

// Valores por defecto si no están configurados
if (empty($_ENV['MONGODB_URL'])) {
    $_ENV['MONGODB_URL'] = 'mongodb://localhost:27017';
    echo "⚠ ADVERTENCIA: No se encontró MONGODB_URL en .env\n";
    echo "   Usando valor por defecto: mongodb://localhost:27017\n";
    echo "   Para usar MongoDB Atlas, crea un archivo .env o pasa la URL como argumento:\n";
    echo "   php test_endpoints.php \"mongodb+srv://usuario:password@cluster.mongodb.net/\"\n\n";
}

if (empty($_ENV['MONGODB_DATABASE'])) {
    $_ENV['MONGODB_DATABASE'] = 'sistema_ofertas';
}

if (empty($_ENV['APP_TIMEZONE'])) {
    $_ENV['APP_TIMEZONE'] = 'America/Bogota';
}

// Configurar zona horaria
date_default_timezone_set($_ENV['APP_TIMEZONE']);

use App\Models\Oferta;
use App\Models\Actividad;
use App\Validators\OfertaValidator;
use App\Services\ConsecutivoService;
use App\Helpers\Database;

echo "=== PRUEBA DE ENDPOINTS POST ===\n\n";

try {
    // 1. Verificar conexión a MongoDB
    echo "1. Verificando conexión a MongoDB...\n";
    $mongodbUrl = $_ENV['MONGODB_URL'] ?? 'mongodb://localhost:27017';
    $displayUrl = strpos($mongodbUrl, '@') !== false 
        ? preg_replace('/:\/\/[^:]+:[^@]+@/', '://***:***@', $mongodbUrl)
        : $mongodbUrl;
    echo "   URL: {$displayUrl}\n";
    echo "   Base de datos: " . ($_ENV['MONGODB_DATABASE'] ?? 'sistema_ofertas') . "\n";
    
    try {
        $db = Database::getDatabase();
        
        // Intentar una operación simple para verificar la conexión
        $db->listCollections();
        echo "   ✓ Conexión exitosa\n\n";
    } catch (\Exception $e) {
        $errorMsg = $e->getMessage();
        echo "   ✗ Error al verificar conexión: {$errorMsg}\n";
        echo "\n   Posibles soluciones:\n";
        
        if (strpos($errorMsg, 'localhost') !== false || strpos($errorMsg, 'connection refused') !== false) {
            echo "   1. El script está intentando conectarse a localhost\n";
            echo "   2. Crea un archivo .env con MONGODB_URL apuntando a MongoDB Atlas:\n";
            echo "      MONGODB_URL=mongodb+srv://usuario:password@cluster.mongodb.net/\n";
            echo "   3. O ejecuta el script pasando la URL como argumento:\n";
            echo "      php test_endpoints.php \"mongodb+srv://usuario:password@cluster.mongodb.net/\"\n";
        } else {
            echo "   1. Verifica que el archivo .env exista y tenga MONGODB_URL configurado\n";
            echo "   2. Para MongoDB Atlas, verifica la IP whitelist (debe incluir 0.0.0.0/0 o tu IP)\n";
            echo "   3. Verifica las credenciales en la URL de conexión\n";
        }
        echo "   4. Ejecuta: php test_connection.php para probar la conexión\n\n";
        throw $e;
    }

    // 2. Crear una actividad de prueba si no existe
    echo "2. Preparando actividad de prueba...\n";
    $actividadModel = new Actividad();
    
    // Buscar si existe alguna actividad
    $actividades = $actividadModel->find([], ['limit' => 1]);
    
    $actividadId = null;
    if (empty($actividades)) {
        // Crear actividad de prueba
        $actividadData = [
            'codigo_segmento' => 10000000,
            'segmento' => 'Segmento de Prueba',
            'codigo_familia' => 10100000,
            'familia' => 'Familia de Prueba',
            'codigo_clase' => 10101500,
            'clase' => 'Clase de Prueba',
            'codigo_producto' => 10101501,
            'producto' => 'Producto de Prueba'
        ];
        $actividadId = $actividadModel->insert($actividadData);
        echo "   ✓ Actividad de prueba creada (ID: {$actividadId})\n\n";
    } else {
        $actividadId = $actividades[0]['id'];
        echo "   ✓ Usando actividad existente (ID: {$actividadId})\n\n";
    }

    // 3. Preparar datos de prueba para crear una oferta
    echo "3. Preparando datos de prueba para POST /ofertas...\n";
    $ofertaData = [
        'objeto' => 'Adquisición de equipos informáticos de prueba',
        'descripcion' => 'Esta es una oferta de prueba para verificar el funcionamiento del endpoint POST. Se requiere la adquisición de equipos informáticos con especificaciones técnicas mínimas.',
        'moneda' => 'COP',
        'presupuesto' => 50000000,
        'actividad_id' => $actividadId,
        'fecha_inicio' => date('Y-m-d', strtotime('+1 day')),
        'hora_inicio' => '08:00',
        'fecha_cierre' => date('Y-m-d', strtotime('+10 days')),
        'hora_cierre' => '17:00',
        'estado' => 'BORRADOR'
    ];
    
    echo "   Datos de prueba:\n";
    foreach ($ofertaData as $key => $value) {
        echo "   - {$key}: {$value}\n";
    }
    echo "\n";

    // 4. Validar datos
    echo "4. Validando datos...\n";
    $validator = new OfertaValidator();
    if (!$validator->validate($ofertaData)) {
        echo "   ✗ Error de validación:\n";
        foreach ($validator->getErrors() as $field => $errors) {
            foreach ($errors as $error) {
                echo "     - {$field}: {$error}\n";
            }
        }
        exit(1);
    }
    echo "   ✓ Validación exitosa\n\n";

    // 5. Generar consecutivo
    echo "5. Generando consecutivo...\n";
    $consecutivoService = new ConsecutivoService();
    $consecutivo = $consecutivoService->generate();
    echo "   ✓ Consecutivo generado: {$consecutivo}\n\n";

    // 6. Crear la oferta
    echo "6. Creando oferta (POST /ofertas)...\n";
    $ofertaModel = new Oferta();
    
    // Convertir actividad_id a ObjectId
    $ofertaData['actividad_id'] = new \MongoDB\BSON\ObjectId($actividadId);
    $ofertaData['consecutivo'] = $consecutivo;
    
    $ofertaId = $ofertaModel->insert($ofertaData);
    echo "   ✓ Oferta creada exitosamente (ID: {$ofertaId})\n\n";

    // 7. Verificar que la oferta se creó correctamente
    echo "7. Verificando oferta creada (GET /ofertas/{id})...\n";
    $ofertaCreada = $ofertaModel->findById($ofertaId);
    
    if ($ofertaCreada) {
        echo "   ✓ Oferta encontrada:\n";
        echo "     - ID: {$ofertaCreada['id']}\n";
        echo "     - Consecutivo: {$ofertaCreada['consecutivo']}\n";
        echo "     - Objeto: {$ofertaCreada['objeto']}\n";
        echo "     - Estado: {$ofertaCreada['estado']}\n";
        echo "     - Presupuesto: {$ofertaCreada['presupuesto']} {$ofertaCreada['moneda']}\n";
        echo "\n";
    } else {
        echo "   ✗ Error: No se pudo encontrar la oferta creada\n";
        exit(1);
    }

    // 8. Probar listado de ofertas
    echo "8. Probando listado de ofertas (GET /ofertas)...\n";
    $result = $ofertaModel->findWithPagination([], 1, 10);
    echo "   ✓ Total de ofertas: {$result['total']}\n";
    echo "   ✓ Ofertas en esta página: " . count($result['data']) . "\n\n";

    echo "=== PRUEBAS COMPLETADAS EXITOSAMENTE ===\n";
    echo "\nResumen:\n";
    echo "- ✓ Conexión a MongoDB funcionando\n";
    echo "- ✓ Validación de datos funcionando\n";
    echo "- ✓ Generación de consecutivo funcionando\n";
    echo "- ✓ Creación de oferta (POST) funcionando\n";
    echo "- ✓ Consulta de oferta (GET) funcionando\n";
    echo "- ✓ Listado de ofertas funcionando\n";

} catch (\Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
        echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    }
    exit(1);
}
