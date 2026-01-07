<?php

namespace App\Models;

/**
 * Modelo para la colección de actividades (UNSPSC)
 */
class Actividad extends Model
{
    protected string $collectionName = 'actividades';

    /**
     * Busca actividades por código de producto
     * 
     * @param int $codigoProducto
     * @return array|null
     */
    public function findByCodigoProducto(int $codigoProducto): ?array
    {
        $result = $this->collection->findOne(['codigo_producto' => $codigoProducto]);
        return $result ? $this->formatDocument($result) : null;
    }

    /**
     * Busca actividades con filtros de búsqueda
     * 
     * @param string $search Término de búsqueda
     * @param int $limit Límite de resultados
     * @return array
     */
    public function search(string $search, int $limit = 20): array
    {
        $filter = [
            '$or' => [
                ['segmento' => new \MongoDB\BSON\Regex($search, 'i')],
                ['familia' => new \MongoDB\BSON\Regex($search, 'i')],
                ['clase' => new \MongoDB\BSON\Regex($search, 'i')],
                ['producto' => new \MongoDB\BSON\Regex($search, 'i')]
            ]
        ];

        $options = [
            'limit' => $limit,
            'sort' => ['codigo_producto' => 1]
        ];

        return $this->find($filter, $options);
    }
}
