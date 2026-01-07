<?php

namespace App\Validators;

use App\Helpers\Request;

/**
 * Validador para documentos de ofertas
 */
class DocumentoValidator extends Validator
{
    /**
     * Valida un archivo subido
     * 
     * @param string $fieldName
     * @return bool
     */
    public function validateFile(string $fieldName = 'archivo'): bool
    {
        $this->errors = [];
        $file = Request::file($fieldName);

        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
            $this->addError($fieldName, 'No se pudo subir el archivo');
            return false;
        }

        // Validar tamaño máximo
        $config = require __DIR__ . '/../../config/app.php';
        $maxSize = $config['upload']['max_size'];

        if ($file['size'] > $maxSize) {
            $maxSizeMB = round($maxSize / 1048576, 2);
            $this->addError($fieldName, "El archivo excede el tamaño máximo permitido de {$maxSizeMB}MB");
            return false;
        }

        // Validar tipo de archivo
        $allowedTypes = $config['upload']['allowed_types'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedTypes)) {
            $allowedTypesStr = implode(', ', $allowedTypes);
            $this->addError($fieldName, "El tipo de archivo no es permitido. Solo se permiten: {$allowedTypesStr}");
            return false;
        }

        // Validar que realmente sea el tipo de archivo correcto
        $mimeType = mime_content_type($file['tmp_name']);
        $allowedMimeTypes = [
            'pdf' => ['application/pdf'],
            'zip' => ['application/zip', 'application/x-zip-compressed']
        ];

        if (!isset($allowedMimeTypes[$extension]) || !in_array($mimeType, $allowedMimeTypes[$extension])) {
            $this->addError($fieldName, 'El archivo no coincide con su extensión');
            return false;
        }

        return true;
    }

    /**
     * Valida los datos del documento
     * 
     * @param array $data
     * @return bool
     */
    public function validate(array $data): bool
    {
        $this->errors = [];
        $this->data = $data;
        $this->rules();
        
        return empty($this->errors);
    }

    /**
     * Define las reglas de validación para documentos
     * 
     * @return void
     */
    protected function rules(): void
    {
        // Validar título (obligatorio)
        $this->required('titulo', 'El título del documento es obligatorio');
        if (isset($this->data['titulo'])) {
            $this->maxLength('titulo', 200, 'El título no puede exceder 200 caracteres');
        }

        // Validar descripción (opcional)
        if (isset($this->data['descripcion'])) {
            $this->maxLength('descripcion', 500, 'La descripción no puede exceder 500 caracteres');
        }
    }
}
