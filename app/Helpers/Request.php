<?php

namespace App\Helpers;

/**
 * Helper para obtener datos de la petición HTTP
 */
class Request
{
    /**
     * Obtiene el método HTTP de la petición
     * 
     * @return string
     */
    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Obtiene la URI de la petición
     * 
     * @return string
     */
    public static function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);
        return rtrim($uri, '/') ?: '/';
    }

    /**
     * Obtiene los datos del cuerpo de la petición como array
     * 
     * @return array
     */
    public static function body(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $body = file_get_contents('php://input');
            $data = json_decode($body, true);
            return $data ?? [];
        }

        return $_POST ?? [];
    }

    /**
     * Obtiene un parámetro de la query string
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function query(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Obtiene todos los parámetros de la query string
     * 
     * @return array
     */
    public static function allQuery(): array
    {
        return $_GET ?? [];
    }

    /**
     * Obtiene un archivo subido
     * 
     * @param string $key
     * @return array|null
     */
    public static function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Sanitiza un string
     * 
     * @param string $value
     * @return string
     */
    public static function sanitize(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }
}
