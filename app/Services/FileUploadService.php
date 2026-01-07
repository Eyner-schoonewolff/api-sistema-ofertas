<?php

namespace App\Services;

use App\Helpers\Request;

/**
 * Servicio para gestionar la subida de archivos
 */
class FileUploadService
{
    /**
     * Sube un archivo y retorna la ruta relativa
     * 
     * @param string $fieldName Nombre del campo del formulario
     * @param string $subfolder Subcarpeta donde guardar (ej: 'ofertas')
     * @return string Ruta relativa del archivo guardado
     * @throws \Exception
     */
    public function upload(string $fieldName, string $subfolder = 'ofertas'): string
    {
        $file = Request::file($fieldName);

        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Error al subir el archivo');
        }

        $config = require __DIR__ . '/../../config/app.php';
        $uploadPath = $config['upload']['path'] . '/' . $subfolder;

        // Crear directorio si no existe
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                throw new \Exception('No se pudo crear el directorio de uploads');
            }
        }

        // Generar nombre único para el archivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('doc_', true) . '.' . $extension;
        $destination = $uploadPath . '/' . $filename;

        // Mover el archivo
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \Exception('No se pudo guardar el archivo');
        }

        // Retornar ruta relativa desde public
        return '/uploads/' . $subfolder . '/' . $filename;
    }

    /**
     * Elimina un archivo
     * 
     * @param string $relativePath Ruta relativa del archivo (ej: '/uploads/ofertas/file.pdf')
     * @return bool
     */
    public function delete(string $relativePath): bool
    {
        $config = require __DIR__ . '/../../config/app.php';
        $publicPath = __DIR__ . '/../../public';
        $fullPath = $publicPath . $relativePath;

        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }
}
