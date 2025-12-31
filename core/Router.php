<?php
/**
 * ENRUTADOR DE PETICIONES
 * Decide qué controlador ejecutar según la URL
 */

class Router {
    private $routes = [];
    private $currentRoute;
    private $currentMethod;
    
    public function __construct() {
        $this->currentRoute = $this->getRoute();
        $this->currentMethod = $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Obtiene la ruta actual desde la URL
     * 
     * EJEMPLO:
     * URL: http://localhost/colegio-sociologos/public/colegiados/ver/123
     * Retorna: /colegiados/ver/123
     */
    private function getRoute() {
        $route = $_SERVER['REQUEST_URI'];
        
        $position = strpos($route, '?');
        if ($position !== false) {
            $route = substr($route, 0, $position);
        }
        
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/') {
            $route = str_replace($scriptName, '', $route);
        }
        
        $route = '/' . trim($route, '/');
        
        return $route;
    }
    
    public function get($route, $handler) {
        $this->addRoute('GET', $route, $handler);
    }
    
    public function post($route, $handler) {
        $this->addRoute('POST', $route, $handler);
    }
    
    public function put($route, $handler) {
        $this->addRoute('PUT', $route, $handler);
    }
    
    public function delete($route, $handler) {
        $this->addRoute('DELETE', $route, $handler);
    }
    
    private function addRoute($method, $route, $handler) {
        $route = preg_replace('/{([a-zA-Z0-9_]+)}/', '([0-9]+)', $route);
        
        $this->routes[$method][$route] = $handler;
    }
    
    public function resolve() {
        $method = $this->currentMethod;
        $route = $this->currentRoute;
        
        // Verificar si hay rutas registradas para este método
        if (!isset($this->routes[$method])) {
            $this->notFound();
            return;
        }
        
        // Buscar coincidencia exacta o con regex
        foreach ($this->routes[$method] as $registeredRoute => $handler) {
            $pattern = '#^' . $registeredRoute . '$#';
            
            if (preg_match($pattern, $route, $matches)) {
                array_shift($matches);
                
                $this->executeHandler($handler, $matches);
                return;
            }
        }
        
        $this->notFound();
    }
    
    private function executeHandler($handler, $params = []) {
        $parts = explode('@', $handler);
        
        if (count($parts) !== 2) {
            throw new Exception("Handler inválido: $handler");
        }
        
        $controllerName = $parts[0];
        $methodName = $parts[1];
        
        // Ruta del archivo del controlador
        $controllerFile = basePath('app/controllers/' . $controllerName . '.php');
        
        // Verificar si existe el archivo
        if (!file_exists($controllerFile)) {
            throw new Exception("Controlador no encontrado: $controllerFile");
        }
        
        // Incluir el archivo del controlador
        require_once $controllerFile;
        
        // Verificar si existe la clase
        if (!class_exists($controllerName)) {
            throw new Exception("Clase controlador no encontrada: $controllerName");
        }
        
        // Instanciar el controlador
        $controller = new $controllerName();
        
        // Verificar si existe el método
        if (!method_exists($controller, $methodName)) {
            throw new Exception("Método no encontrado: $controllerName::$methodName");
        }
        
        // Ejecutar el método con los parámetros
        call_user_func_array([$controller, $methodName], $params);
    }
    

    // Maneja error 404 - Página no encontrada
    private function notFound() {
        http_response_code(404);
        
        // Si existe una vista de 404, mostrarla
        $view404 = basePath('app/views/errors/404.php');
        
        if (file_exists($view404)) {
            require_once $view404;
        } else {
            echo "<h1>404 - Página no encontrada</h1>";
            echo "<p>La ruta <strong>{$this->currentRoute}</strong> no existe.</p>";
        }
        exit();
    }
    
    // Obtiene la ruta actual
    public function getCurrentRoute() {
        return $this->currentRoute;
    }
    
    // Obtien
    public function getCurrentMethod() {
        return $this->currentMethod;
    }
}