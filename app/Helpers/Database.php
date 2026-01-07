<?php

namespace App\Helpers;

use MongoDB\Client;
use MongoDB\Database as MongoDatabase;

/**
 * Helper para gestionar la conexión a MongoDB
 * 
 * Implementa el patrón Singleton para mantener
 * una única instancia de conexión.
 */
class Database
{
    private static ?Client $client = null;
    private static ?MongoDatabase $database = null;

    /**
     * Obtiene la instancia del cliente MongoDB
     * 
     * @return Client
     */
    public static function getClient(): Client
    {
        if (self::$client === null) {
            $config = require __DIR__ . '/../../config/database.php';
            self::$client = new Client($config['url'], [], $config['options']);
        }

        return self::$client;
    }

    /**
     * Verifica la conexión a MongoDB
     * 
     * @return bool
     */
    public static function checkConnection(): bool
    {
        try {
            $client = self::getClient();
            $database = self::getDatabase();
            // Intentar listar colecciones (operación ligera)
            $database->listCollections();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtiene la instancia de la base de datos
     * 
     * @return MongoDatabase
     */
    public static function getDatabase(): MongoDatabase
    {
        if (self::$database === null) {
            $config = require __DIR__ . '/../../config/database.php';
            $client = self::getClient();
            self::$database = $client->selectDatabase($config['database']);
        }

        return self::$database;
    }

    /**
     * Obtiene una colección específica
     * 
     * @param string $collectionName
     * @return \MongoDB\Collection
     */
    public static function getCollection(string $collectionName)
    {
        return self::getDatabase()->selectCollection($collectionName);
    }
}
