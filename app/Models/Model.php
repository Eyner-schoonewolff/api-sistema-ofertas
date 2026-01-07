<?php

namespace App\Models;

use App\Helpers\Database;
use MongoDB\Collection;
use MongoDB\BSON\ObjectId;

/**
 * Clase base para todos los modelos
 * 
 * Proporciona funcionalidad común para interactuar
 * con las colecciones de MongoDB.
 */
abstract class Model
{
    protected string $collectionName;
    protected Collection $collection;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->collection = Database::getCollection($this->collectionName);
    }

    /**
     * Encuentra un documento por su ID
     * 
     * @param string $id
     * @return array|null
     */
    public function findById(string $id): ?array
    {
        try {
            $result = $this->collection->findOne(['_id' => new ObjectId($id)]);
            return $result ? $this->formatDocument($result) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Encuentra documentos según criterios
     * 
     * @param array $filter
     * @param array $options
     * @return array
     */
    public function find(array $filter = [], array $options = []): array
    {
        $cursor = $this->collection->find($filter, $options);
        $results = [];

        foreach ($cursor as $document) {
            $results[] = $this->formatDocument($document);
        }

        return $results;
    }

    /**
     * Inserta un nuevo documento
     * 
     * @param array $data
     * @return string ID del documento insertado
     */
    public function insert(array $data): string
    {
        $data['creado_en'] = new \MongoDB\BSON\UTCDateTime();
        $data['actualizado_en'] = new \MongoDB\BSON\UTCDateTime();

        $result = $this->collection->insertOne($data);
        return (string) $result->getInsertedId();
    }

    /**
     * Actualiza un documento por ID
     * 
     * @param string $id
     * @param array $data
     * @return bool
     */
    public function update(string $id, array $data): bool
    {
        $data['actualizado_en'] = new \MongoDB\BSON\UTCDateTime();

        $result = $this->collection->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => $data]
        );

        return $result->getModifiedCount() > 0;
    }

    /**
     * Elimina un documento por ID
     * 
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        $result = $this->collection->deleteOne(['_id' => new ObjectId($id)]);
        return $result->getDeletedCount() > 0;
    }

    /**
     * Cuenta documentos según criterios
     * 
     * @param array $filter
     * @return int
     */
    public function count(array $filter = []): int
    {
        return $this->collection->countDocuments($filter);
    }

    /**
     * Formatea un documento de MongoDB para la respuesta
     * 
     * @param array $document
     * @return array
     */
    protected function formatDocument(array $document): array
    {
        if (isset($document['_id'])) {
            $document['id'] = (string) $document['_id'];
            unset($document['_id']);
        }

        // Convertir fechas MongoDB a formato ISO
        foreach ($document as $key => $value) {
            if ($value instanceof \MongoDB\BSON\UTCDateTime) {
                $document[$key] = $value->toDateTime()->format('c');
            }
        }

        return $document;
    }

    /**
     * Crea índices en la colección
     * 
     * @param array $indexes
     * @return void
     */
    public function createIndexes(array $indexes): void
    {
        foreach ($indexes as $index) {
            $this->collection->createIndex($index['keys'], $index['options'] ?? []);
        }
    }
}
