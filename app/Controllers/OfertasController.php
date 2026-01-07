<?php

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Request;
use App\Models\Oferta;
use App\Models\Actividad;
use App\Models\OfertaDocumento;
use App\Validators\OfertaValidator;
use App\Validators\DocumentoValidator;
use App\Services\ConsecutivoService;
use App\Services\ExcelExportService;
use App\Services\FileUploadService;
use MongoDB\BSON\ObjectId;

/**
 * Controlador para gestionar ofertas
 */
class OfertasController
{
    private Oferta $ofertaModel;
    private Actividad $actividadModel;
    private OfertaDocumento $documentoModel;
    private OfertaValidator $ofertaValidator;
    private DocumentoValidator $documentoValidator;
    private ConsecutivoService $consecutivoService;
    private ExcelExportService $excelService;
    private FileUploadService $fileUploadService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ofertaModel = new Oferta();
        $this->actividadModel = new Actividad();
        $this->documentoModel = new OfertaDocumento();
        $this->ofertaValidator = new OfertaValidator();
        $this->documentoValidator = new DocumentoValidator();
        $this->consecutivoService = new ConsecutivoService();
        $this->excelService = new ExcelExportService();
        $this->fileUploadService = new FileUploadService();
    }

    /**
     * Lista todas las ofertas con filtros y paginación
     * 
     * GET /ofertas
     */
    public function index(): void
    {
        try {
            $page = (int) Request::query('page', 1);
            $perPage = (int) Request::query('per_page', 10);
            $estado = Request::query('estado');
            $fechaInicio = Request::query('fecha_inicio');
            $fechaCierre = Request::query('fecha_cierre');

            // Construir filtros
            $filters = [];
            
            if ($estado !== null) {
                $filters['estado'] = $estado;
            }
            
            if ($fechaInicio !== null) {
                $filters['fecha_inicio'] = $fechaInicio;
            }
            
            if ($fechaCierre !== null) {
                $filters['fecha_cierre'] = $fechaCierre;
            }

            // Obtener ofertas con paginación y actividades
            $result = $this->ofertaModel->findWithActividades($filters, $page, $perPage);

            Response::paginated(
                $result['data'],
                $page,
                $perPage,
                $result['total']
            );
        } catch (\Exception $e) {
            $message = $e->getMessage();
            // Detectar errores de conexión a MongoDB
            if (strpos($message, 'No servers') !== false || 
                strpos($message, 'connection') !== false ||
                strpos($message, 'ConnectionException') !== false) {
                Response::error(
                    'No se pudo conectar a la base de datos MongoDB. Verifica que el servidor esté corriendo.',
                    503
                );
            } else {
                Response::error(
                    'Error al obtener las ofertas: ' . $message,
                    500
                );
            }
        }
    }

    /**
     * Obtiene el detalle de una oferta
     * 
     * GET /ofertas/{id}
     */
    public function show(string $id): void
    {
        $oferta = $this->ofertaModel->findById($id);

        if (!$oferta) {
            Response::error('Oferta no encontrada', 404);
        }

        // Obtener actividad relacionada
        if (isset($oferta['actividad_id'])) {
            $actividad = $this->actividadModel->findById($oferta['actividad_id']);
            $oferta['actividad'] = $actividad;
        }

        // Obtener documentos
        $documentos = $this->documentoModel->findByOfertaId($id);
        $oferta['documentos'] = $documentos;

        Response::success($oferta, 'Oferta obtenida exitosamente');
    }

    /**
     * Crea una nueva oferta
     * 
     * POST /ofertas
     */
    public function store(): void
    {
        $data = Request::body();

        // Validar datos
        if (!$this->ofertaValidator->validate($data)) {
            Response::error(
                'Error de validación',
                422,
                $this->ofertaValidator->getErrors()
            );
        }

        // Verificar que la actividad existe
        $actividad = $this->actividadModel->findById($data['actividad_id']);
        if (!$actividad) {
            Response::error('La actividad especificada no existe', 404);
        }

        // Generar consecutivo
        $consecutivo = $this->consecutivoService->generate();
        $data['consecutivo'] = $consecutivo;

        // Establecer estado por defecto si no se proporciona
        if (!isset($data['estado'])) {
            $data['estado'] = 'BORRADOR';
        }

        // Convertir actividad_id a ObjectId
        $data['actividad_id'] = new ObjectId($data['actividad_id']);

        // Insertar oferta
        $id = $this->ofertaModel->insert($data);

        // Obtener la oferta creada
        $oferta = $this->ofertaModel->findById($id);
        $oferta['actividad'] = $actividad;

        Response::success($oferta, 'Oferta creada exitosamente', 201);
    }

    /**
     * Actualiza una oferta existente
     * 
     * PUT /ofertas/{id}
     */
    public function update(string $id): void
    {
        $oferta = $this->ofertaModel->findById($id);

        if (!$oferta) {
            Response::error('Oferta no encontrada', 404);
        }

        $data = Request::body();

        // Validar datos
        if (!$this->ofertaValidator->validate($data)) {
            Response::error(
                'Error de validación',
                422,
                $this->ofertaValidator->getErrors()
            );
        }

        // Verificar que la actividad existe
        if (isset($data['actividad_id'])) {
            $actividad = $this->actividadModel->findById($data['actividad_id']);
            if (!$actividad) {
                Response::error('La actividad especificada no existe', 404);
            }
            $data['actividad_id'] = new ObjectId($data['actividad_id']);
        }

        // Verificar que tenga al menos un documento en edición
        $documentosCount = $this->documentoModel->countByOfertaId($id);
        if ($documentosCount === 0) {
            Response::error('La oferta debe tener al menos un documento cargado', 422);
        }

        // No permitir modificar el consecutivo
        unset($data['consecutivo']);

        // Actualizar oferta
        $this->ofertaModel->update($id, $data);

        // Obtener la oferta actualizada
        $ofertaActualizada = $this->ofertaModel->findById($id);
        
        if (isset($ofertaActualizada['actividad_id'])) {
            $actividad = $this->actividadModel->findById($ofertaActualizada['actividad_id']);
            $ofertaActualizada['actividad'] = $actividad;
        }

        Response::success($ofertaActualizada, 'Oferta actualizada exitosamente');
    }

    /**
     * Sube un documento a una oferta
     * 
     * POST /ofertas/{id}/documentos
     */
    public function uploadDocument(string $id): void
    {
        $oferta = $this->ofertaModel->findById($id);

        if (!$oferta) {
            Response::error('Oferta no encontrada', 404);
        }

        // Validar archivo
        if (!$this->documentoValidator->validateFile('archivo')) {
            Response::error(
                'Error de validación del archivo',
                422,
                $this->documentoValidator->getErrors()
            );
        }

        // Validar datos del documento
        $data = Request::body();
        if (!$this->documentoValidator->validate($data)) {
            Response::error(
                'Error de validación de datos',
                422,
                $this->documentoValidator->getErrors()
            );
        }

        // Subir archivo
        try {
            $filePath = $this->fileUploadService->upload('archivo', 'ofertas');
        } catch (\Exception $e) {
            Response::error('Error al subir el archivo: ' . $e->getMessage(), 500);
        }

        // Guardar documento en base de datos
        $documentoData = [
            'oferta_id' => new ObjectId($id),
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? '',
            'archivo' => $filePath
        ];

        $documentoId = $this->documentoModel->insert($documentoData);
        $documento = $this->documentoModel->findById($documentoId);

        Response::success($documento, 'Documento subido exitosamente', 201);
    }

    /**
     * Exporta las ofertas a Excel
     * 
     * GET /ofertas/export/excel
     */
    public function exportExcel(): void
    {
        $filters = [];
        
        $estado = Request::query('estado');
        if ($estado !== null) {
            $filters['estado'] = $estado;
        }

        $this->excelService->export($filters);
    }
}
