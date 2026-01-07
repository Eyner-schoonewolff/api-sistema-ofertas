<?php

namespace App\Helpers;

/**
 * Helper para generar respuestas JSON estandarizadas
 */
class Response
{
    /**
     * Envía una respuesta JSON exitosa
     * 
     * @param mixed $data Datos a enviar
     * @param string $message Mensaje opcional
     * @param int $statusCode Código de estado HTTP
     * @return void
     */
    public static function success($data = null, string $message = '', int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Envía una respuesta JSON de error
     * 
     * @param string $message Mensaje de error
     * @param int $statusCode Código de estado HTTP
     * @param array $errors Errores de validación opcionales
     * @return void
     */
    public static function error(string $message, int $statusCode = 400, array $errors = []): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Envía una respuesta JSON con paginación
     * 
     * @param array $data Datos paginados
     * @param int $page Página actual
     * @param int $perPage Elementos por página
     * @param int $total Total de elementos
     * @return void
     */
    public static function paginated(array $data, int $page, int $perPage, int $total): void
    {
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'success' => true,
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
