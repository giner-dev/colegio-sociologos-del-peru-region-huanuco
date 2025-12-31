<?php
// FUNCIONES AUXILIARES GLOBALES

// Carga las variables de entorno .env
function loadEnv(){
    $envFile = __DIR__. '/../.env';

    if(!file_exists($envFile)){
        die("ERROR: El archivo .env no existe o no se encuentra"); // Detiene todo
    }

    // Leer .env linea por linea
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach($lines as $line){
        // Ignorar comentarios y lineas vacías
        if(strpos(trim($line), '#') === 0 || empty(trim($line))){
            continue;
        }

        // Separar clave = valor
        list($key, $value) = explode('=', $line, 2);

        // Limpiar espacios y comillas
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, '"\'');

        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Obtiene el valor de una variable de entorno
function env($key, $default=null){
    //Buscar en $_ENV
    if(isset($_ENV[$key])){
        return $_ENV[$key];
    }

    // Buscar en getenv()
    $value = getenv($key);
    if($value !== false){
        return $value;
    }

    return $default;
}

// Redirecciona a una URL
function redirect($url){
    header("Location: " . $url);
    exit();
}

// Obtiene la URL base de la aplicación
function url($path = '') {
    $baseUrl = env('APP_URL', 'http://localhost');
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

// Obtiene la ruta absoluta a un archivo
function basePath($path = '') {
    return __DIR__ . '/../' . ltrim($path, '/');
}

// Escapa caracteres HTML para prevenir XSS
function e($string){
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Verifica si el usuario está autenticado
function isAuthenticated() {
    return isset($_SESSION['usuario_id']);
}

// Obtiene el ID del usuario autenticado
function authUserId() {
    return $_SESSION['usuario_id'] ?? null;
}

// Obtiene el nombre del usuario autenticado
function authUserName() {
    return $_SESSION['usuario_nombre'] ?? null;
}

// Obtiene el rol del usuario autenticado
function authUserRole() {
    return $_SESSION['usuario_rol'] ?? null;
}

// Verifica si el usuario tiene un rol específico
function hasRole($roles) {
    if (!isAuthenticated()) {
        return false;
    }
    
    $userRole = authUserRole();
    
    if (is_array($roles)) {
        return in_array($userRole, $roles);
    }
    
    return $userRole === $roles;
}

// Requiere autenticación - Redirige al login si no está logueado
function requireAuth() {
    if (!isAuthenticated()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect(url('login'));
    }
}

// Requiere un rol específico - Redirige si no tiene permisos
function requireRole($roles) {
    requireAuth();
    
    if (!hasRole($roles)) {
        redirect(url('sin-permisos'));
    }
}

// Formatea una fecha
function formatDate($date) {
    if (empty($date)) {
        return '-';
    }
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date('d/m/Y', $timestamp);
}

// ormatea una fecha con hora
function formatDateTime($datetime) {
    if (empty($datetime)) {
        return '-';
    }
    
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    return date('d/m/Y H:i', $timestamp);
}

// Formatea un monto en soles peruanos
function formatMoney($amount, $showSymbol = true) {
    $formatted = number_format($amount, 2, '.', ',');
    return $showSymbol ? 'S/ ' . $formatted : $formatted;
}

// Genera un token CSRF
function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verifica un token CSRF
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Devuelve un mensaje de sesión flash y lo elimina
function flash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

// Establece un mensaje flash para la siguiente petición
function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

// Valida que una cadena no esté vacía
function required($value) {
    return !empty(trim($value));
}

// Valida formato de email
function validEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Valida longitud mínima
function minLength($value, $min) {
    return strlen($value) >= $min;
}

// Valida longitud máxima
function maxLength($value, $max) {
    return strlen($value) <= $max;
}

// Sanitiza una cadena para prevenir inyecciones
function sanitize($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

//Debug: Imprime una variable de forma legible y detiene la ejecución
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

// Debug: Imprime una variable de forma legible sin detener

function dump($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}

// Registra un mensaje en el log del sistema

function logMessage($message, $level = 'info') {
    $logFile = basePath('storage/logs/app.log');
    $logDir = dirname($logFile);
    
    // Crear directorio si no existe
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}