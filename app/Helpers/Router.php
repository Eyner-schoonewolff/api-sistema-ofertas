<?php

namespace App\Helpers;

use App\Helpers\Request;

/**
 * Enrutador simple para la API
 */
class Router
{
    private array $routes = [];

    /**
     * Constructor
     * 
     * @param array $routes Rutas definidas
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Resuelve la ruta y ejecuta el controlador correspondiente
     * 
     * @return void
     */
    public function resolve(): void
    {
        $method = Request::method();
        $uri = Request::uri();

        // Buscar ruta exacta primero
        $routeKey = $method . '|' . $uri;
        
        if (isset($this->routes[$routeKey])) {
            $this->executeRoute($this->routes[$routeKey], []);
            return;
        }

        // Buscar rutas con parámetros
        foreach ($this->routes as $routePattern => $handler) {
            [$routeMethod, $routePath] = explode('|', $routePattern, 2);
            
            if ($routeMethod !== $method) {
                continue;
            }

            $params = $this->matchRoute($routePath, $uri);
            
            if ($params !== null) {
                $this->executeRoute($handler, $params);
                return;
            }
        }

        // Ruta no encontrada
        Response::error('Ruta no encontrada', 404);
    }

    /**
     * Verifica si una ruta coincide con el patrón
     * 
     * @param string $routePath Patrón de la ruta (ej: /ofertas/{id})
     * @param string $uri URI de la petición
     * @return array|null Parámetros extraídos o null si no coincide
     */
    private function matchRoute(string $routePath, string $uri): ?array
    {
        // Extraer nombres de parámetros del patrón
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $routePath, $paramNames);
        $paramNames = $paramNames[1] ?? [];

        // Convertir patrón a expresión regular
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            $params = [];
            for ($i = 1; $i < count($matches); $i++) {
                $paramName = $paramNames[$i - 1] ?? "param{$i}";
                $params[$paramName] = $matches[$i];
            }
            
            return $params;
        }

        return null;
    }

    /**
     * Ejecuta la ruta llamando al controlador
     * 
     * @param string $handler Formato: Controller@method
     * @param array $params Parámetros de la ruta
     * @return void
     */
    private function executeRoute(string $handler, array $params): void
    {
        [$controllerName, $methodName] = explode('@', $handler);
        
        $controllerClass = "App\\Controllers\\{$controllerName}";
        
        if (!class_exists($controllerClass)) {
            Response::error("Controlador {$controllerClass} no encontrado", 500);
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $methodName)) {
            Response::error("Método {$methodName} no encontrado en {$controllerClass}", 500);
        }

        // Llamar al método con los parámetros
        if (empty($params)) {
            $controller->$methodName();
        } else {
            $controller->$methodName(...array_values($params));
        }
    }
}
