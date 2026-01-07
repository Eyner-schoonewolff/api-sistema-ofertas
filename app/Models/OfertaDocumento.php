<?php

namespace App\Models;

use MongoDB\BSON\ObjectId;

/**
 * Modelo para la colección de documentos de ofertas
 */
class OfertaDocumento extends Model
{
    protected string $collectionName = 'ofertas_documentos';
    private static bool $indexesInitialized = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        // Inicializar índices solo una vez (lazy initialization)
        if (!self::$indexesInitialized) {
            $this->initializeIndexes();
            self::$indexesInitialized = true;
        }
    }

    /**
     * Inicializa los índices recomendados
     * Se ejecuta solo una vez por proceso
     * MongoDB maneja automáticamente índices duplicados
     * 
     * @return void
     */
    private function initializeIndexes(): void
    {
        try {
            // Crear índice con background: true para no bloquear
            $this->collection->createIndex(
                ['oferta_id' => 1],
                ['background' => true]
            );
        } catch (\Exception $e) {
            // El índice ya existe o hay un error - no bloquear la aplicación
        }
    }

    /**
     * Busca todos los documentos de una oferta
     * 
     * @param string $ofertaId
     * @return array
     */
    public function findByOfertaId(string $ofertaId): array
    {
        $filter = ['oferta_id' => new ObjectId($ofertaId)];
        $options = ['sort' => ['creado_en' => -1]];
        
        return $this->find($filter, $options);
    }

    /**
     * Cuenta los documentos de una oferta
     * 
     * @param string $ofertaId
     * @return int
     */
    public function countByOfertaId(string $ofertaId): int
    {
        return $this->count(['oferta_id' => new ObjectId($ofertaId)]);
    }

    /**
     * Elimina todos los documentos de una oferta
     * 
     * @param string $ofertaId
     * @return int Número de documentos eliminados
     */
    public function deleteByOfertaId(string $ofertaId): int
    {
        $result = $this->collection->deleteMany(['oferta_id' => new ObjectId($ofertaId)]);
        return $result->getDeletedCount();
    }
}
