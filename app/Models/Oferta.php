<?php

namespace App\Models;

use MongoDB\BSON\ObjectId;

/**
 * Modelo para la colección de ofertas
 */
class Oferta extends Model
{
    protected string $collectionName = 'ofertas';
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
     * MongoDB maneja automáticamente índices duplicados, así que no necesitamos verificar
     * 
     * @return void
     */
    private function initializeIndexes(): void
    {
        try {
            // Crear índices con background: true para no bloquear
            $this->collection->createIndex(
                ['consecutivo' => 1],
                ['unique' => true, 'background' => true]
            );
            $this->collection->createIndex(
                ['fecha_inicio' => 1],
                ['background' => true]
            );
            $this->collection->createIndex(
                ['estado' => 1],
                ['background' => true]
            );
        } catch (\Exception $e) {
            // Los índices ya existen o hay un error - no bloquear la aplicación
            // MongoDB retornará error si el índice ya existe, pero no afecta la operación
        }
    }

    /**
     * Busca ofertas con filtros y paginación
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function findWithPagination(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $skip = ($page - 1) * $perPage;
        
        $options = [
            'skip' => $skip,
            'limit' => $perPage,
            'sort' => ['creado_en' => -1]
        ];

        $results = $this->find($filters, $options);
        $total = $this->count($filters);

        return [
            'data' => $results,
            'total' => $total
        ];
    }

    /**
     * Busca una oferta por consecutivo
     * 
     * @param string $consecutivo
     * @return array|null
     */
    public function findByConsecutivo(string $consecutivo): ?array
    {
        $result = $this->collection->findOne(['consecutivo' => $consecutivo]);
        return $result ? $this->formatDocument($result) : null;
    }

    /**
     * Verifica si existe una oferta con un consecutivo dado
     * 
     * @param string $consecutivo
     * @return bool
     */
    public function consecutivoExists(string $consecutivo): bool
    {
        return $this->count(['consecutivo' => $consecutivo]) > 0;
    }

    /**
     * Obtiene el último número de consecutivo del año
     * 
     * @param string $year Año en formato YY (ej: "25")
     * @return int
     */
    public function getLastConsecutiveNumber(string $year): int
    {
        // Buscar ofertas que coincidan con el patrón del año
        $pattern = "^O-\\d+-{$year}$";
        $ofertas = $this->collection->find([
            'consecutivo' => new \MongoDB\BSON\Regex($pattern)
        ]);

        $maxNumber = 0;
        $phpPattern = "/^O-(\d+)-{$year}$/";
        
        foreach ($ofertas as $oferta) {
            $consecutivo = $oferta['consecutivo'] ?? '';
            if (preg_match($phpPattern, $consecutivo, $matches)) {
                $number = (int) $matches[1];
                $maxNumber = max($maxNumber, $number);
            }
        }

        return $maxNumber;
    }

    /**
     * Obtiene ofertas con sus actividades relacionadas
     * 
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function findWithActividades(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $result = $this->findWithPagination($filters, $page, $perPage);
        
        // Solo cargar actividades si hay datos
        if (empty($result['data'])) {
            return $result;
        }
        
        $actividadModel = new Actividad();

        foreach ($result['data'] as &$oferta) {
            if (isset($oferta['actividad_id'])) {
                $actividad = $actividadModel->findById($oferta['actividad_id']);
                $oferta['actividad'] = $actividad;
            }
        }

        return $result;
    }
}
