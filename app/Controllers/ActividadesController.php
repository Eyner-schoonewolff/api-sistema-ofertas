<?php

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Request;
use App\Models\Actividad;
use App\Validators\ActividadValidator;

/**
 * Controlador para gestionar actividades (UNSPSC)
 */
class ActividadesController
{
    private Actividad $actividadModel;
    private ActividadValidator $actividadValidator;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->actividadModel = new Actividad();
        $this->actividadValidator = new ActividadValidator();
    }

    /**
     * Lista todas las actividades con paginación y búsqueda
     * 
     * GET /actividades
     */
    public function index(): void
    {
        try {
            $page = (int) Request::query('page', 1);
            $perPage = (int) Request::query('per_page', 20);
            $search = Request::query('search');

            // Si hay búsqueda, usar el método search
            if ($search !== null && $search !== '') {
                $results = $this->actividadModel->search($search, $perPage);
                $total = count($results);
                
                // Aplicar paginación manual
                $skip = ($page - 1) * $perPage;
                $paginatedResults = array_slice($results, $skip, $perPage);
                
                Response::paginated(
                    $paginatedResults,
                    $page,
                    $perPage,
                    $total
                );
            } else {
                // Listado normal con paginación
                $skip = ($page - 1) * $perPage;
                $options = [
                    'skip' => $skip,
                    'limit' => $perPage,
                    'sort' => ['codigo_producto' => 1]
                ];

                $results = $this->actividadModel->find([], $options);
                $total = $this->actividadModel->count([]);

                Response::paginated(
                    $results,
                    $page,
                    $perPage,
                    $total
                );
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (strpos($message, 'No servers') !== false || 
                strpos($message, 'connection') !== false ||
                strpos($message, 'ConnectionException') !== false) {
                Response::error(
                    'No se pudo conectar a la base de datos MongoDB. Verifica que el servidor esté corriendo.',
                    503
                );
            } else {
                Response::error(
                    'Error al obtener las actividades: ' . $message,
                    500
                );
            }
        }
    }

    /**
     * Obtiene el detalle de una actividad
     * 
     * GET /actividades/{id}
     */
    public function show(string $id): void
    {
        try {
            $actividad = $this->actividadModel->findById($id);

            if (!$actividad) {
                Response::error('Actividad no encontrada', 404);
            }

            Response::success($actividad, 'Actividad obtenida exitosamente');
        } catch (\Exception $e) {
            Response::error(
                'Error al obtener la actividad: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Crea una nueva actividad
     * 
     * POST /actividades
     */
    public function store(): void
    {
        try {
            $data = Request::body();

            // Validar datos
            if (!$this->actividadValidator->validate($data)) {
                Response::error(
                    'Error de validación',
                    422,
                    $this->actividadValidator->getErrors()
                );
            }

            // Verificar que el código_producto no exista
            $codigoProducto = (int) $data['codigo_producto'];
            $existente = $this->actividadModel->findByCodigoProducto($codigoProducto);
            if ($existente) {
                Response::error(
                    'Ya existe una actividad con el código de producto ' . $codigoProducto,
                    409
                );
            }

            // Convertir códigos a enteros
            $data['codigo_segmento'] = (int) $data['codigo_segmento'];
            $data['codigo_familia'] = (int) $data['codigo_familia'];
            $data['codigo_clase'] = (int) $data['codigo_clase'];
            $data['codigo_producto'] = (int) $data['codigo_producto'];

            // Insertar actividad
            $id = $this->actividadModel->insert($data);

            // Obtener la actividad creada
            $actividad = $this->actividadModel->findById($id);

            Response::success($actividad, 'Actividad creada exitosamente', 201);
        } catch (\Exception $e) {
            Response::error(
                'Error al crear la actividad: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Actualiza una actividad existente
     * 
     * PUT /actividades/{id}
     */
    public function update(string $id): void
    {
        try {
            $actividad = $this->actividadModel->findById($id);

            if (!$actividad) {
                Response::error('Actividad no encontrada', 404);
            }

            $data = Request::body();

            // Validar datos
            if (!$this->actividadValidator->validate($data)) {
                Response::error(
                    'Error de validación',
                    422,
                    $this->actividadValidator->getErrors()
                );
            }

            // Verificar que el código_producto no exista en otra actividad
            if (isset($data['codigo_producto'])) {
                $codigoProducto = (int) $data['codigo_producto'];
                $existente = $this->actividadModel->findByCodigoProducto($codigoProducto);
                if ($existente && $existente['id'] !== $id) {
                    Response::error(
                        'Ya existe otra actividad con el código de producto ' . $codigoProducto,
                        409
                    );
                }
            }

            // Convertir códigos a enteros si están presentes
            if (isset($data['codigo_segmento'])) {
                $data['codigo_segmento'] = (int) $data['codigo_segmento'];
            }
            if (isset($data['codigo_familia'])) {
                $data['codigo_familia'] = (int) $data['codigo_familia'];
            }
            if (isset($data['codigo_clase'])) {
                $data['codigo_clase'] = (int) $data['codigo_clase'];
            }
            if (isset($data['codigo_producto'])) {
                $data['codigo_producto'] = (int) $data['codigo_producto'];
            }

            // Actualizar actividad
            $this->actividadModel->update($id, $data);

            // Obtener la actividad actualizada
            $actividadActualizada = $this->actividadModel->findById($id);

            Response::success($actividadActualizada, 'Actividad actualizada exitosamente');
        } catch (\Exception $e) {
            Response::error(
                'Error al actualizar la actividad: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Elimina una actividad
     * 
     * DELETE /actividades/{id}
     */
    public function destroy(string $id): void
    {
        try {
            $actividad = $this->actividadModel->findById($id);

            if (!$actividad) {
                Response::error('Actividad no encontrada', 404);
            }

            // Verificar si la actividad está siendo usada en alguna oferta
            // Esto requeriría una consulta a la colección de ofertas
            // Por ahora, permitimos la eliminación
            // TODO: Agregar verificación de uso en ofertas

            // Eliminar actividad
            $deleted = $this->actividadModel->delete($id);

            if (!$deleted) {
                Response::error('No se pudo eliminar la actividad', 500);
            }

            Response::success(null, 'Actividad eliminada exitosamente');
        } catch (\Exception $e) {
            Response::error(
                'Error al eliminar la actividad: ' . $e->getMessage(),
                500
            );
        }
    }
}
