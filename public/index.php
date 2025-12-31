<?php
// Iniciar output buffering para mejor control de salida
ob_start();

// Definir constantes del sistema
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', __DIR__);

// Reportar todos los errores en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar funciones auxiliares
require_once ROOT_PATH . '/helpers/functions.php';

// Cargar variables de entorno (.env)
loadEnv();

// Configurar zona horaria
date_default_timezone_set('America/Lima');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar clases del CORE
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/View.php';
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Router.php';

// Establecer conexión a la base de datos
try {
    // Crear instancia de Database
    $db = Database::getInstance();
    
    // Puedes verificar la conexión
    if (env('APP_ENV') === 'development') {
        logMessage("Sistema iniciado correctamente", 'info');
    }
    
} catch (Exception $e) {
    die("<h1>Error del Sistema</h1><p>No se pudo conectar a la base de datos. Contacte al administrador.</p>");
}

// Crear instancia del Router
$router = new Router();

// Cargar definición de rutas
require_once ROOT_PATH . '/config/routes.php';

// Resolver la ruta actual
try {
    $router->resolve();
} catch (Exception $e) {
    logMessage("Error en ruta: " . $e->getMessage(), 'error');
    
    // Mostrar error en desarrollo, mensaje genérico en producción
    if (env('APP_ENV') === 'development') {
        echo "<h1>Error en el sistema</h1>";
        echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        echo "<h1>Error</h1><p>Ocurrió un error en el sistema. Por favor, intente más tarde.</p>";
    }
}

// Enviar el output buffer
ob_end_flush();