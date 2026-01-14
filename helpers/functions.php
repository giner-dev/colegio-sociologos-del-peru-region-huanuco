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
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Verifica si el usuario está autenticado
function isAuthenticated() {
    // Verificar que exista usuario_id y que la sesión no haya expirado
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['LAST_ACTIVITY'])) {
        return false;
    }
    
    // Verificar expiración
    $sessionLifetime = env('SESSION_LIFETIME', 120);
    $inactiveTime = time() - $_SESSION['LAST_ACTIVITY'];
    
    if ($inactiveTime > ($sessionLifetime * 60)) {
        // Sesión expirada
        session_unset();
        session_destroy();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return false;
    }
    
    // Actualizar última actividad si la sesión sigue activa
    $_SESSION['LAST_ACTIVITY'] = time();
    
    return true;
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
        // Guardar URL actual para redirección después de login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        // Verificar si expiró por tiempo
        if (isset($_SESSION['LAST_ACTIVITY'])) {
            $sessionLifetime = env('SESSION_LIFETIME', 120);
            $inactiveTime = time() - $_SESSION['LAST_ACTIVITY'];
            
            if ($inactiveTime > ($sessionLifetime * 60)) {
                // Limpiar sesión expirada
                session_unset();
                session_destroy();
                redirect(url('login') . '?expired=1');
            }
        }
        
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
    if ($amount === null) {
        $amount = 0;
    }
    
    $formatted = number_format((float)$amount, 2, '.', ',');
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



// Obtiene el tiempo restante de sesión en minutos
function getSessionTimeLeft() {
    if (!isset($_SESSION['LAST_ACTIVITY'])) {
        return 0;
    }
    
    $sessionLifetime = env('SESSION_LIFETIME', 120);
    $inactiveTime = time() - $_SESSION['LAST_ACTIVITY'];
    $timeLeft = ($sessionLifetime * 60) - $inactiveTime;
    
    return max(0, floor($timeLeft / 60));
}

// Renueva la sesión (extiende el tiempo)
function renewSession() {
    if (isset($_SESSION['LAST_ACTIVITY'])) {
        $_SESSION['LAST_ACTIVITY'] = time();
    }
}

// Forzar cierre de sesión
function forceLogout() {
    // Asegurarse de que la sesión esté iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    session_unset();
    session_destroy();
    
    // Eliminar cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Iniciar nueva sesión limpia
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// formatea el número de colegiatura con ceros a la izquierda
function formatNumeroColegiatura($numero, $digitos = 5) {
    if (empty($numero)) {
        return str_repeat('0', $digitos);
    }
    return str_pad($numero, $digitos, '0', STR_PAD_LEFT);
}


// Verifica si el usuario tiene un permiso específico para un módulo
function hasPermission($modulo, $accion = 'ver') {
    if (!isAuthenticated()) {
        return false;
    }
    
    if (hasRole('administrador')) {
        return true;
    }
    
    if (!isset($_SESSION['usuario_permisos'])) {
        return false;
    }
    
    $permisos = $_SESSION['usuario_permisos'];
    
    if (!isset($permisos[$modulo])) {
        return false;
    }
    
    if ($permisos[$modulo] === 'all') {
        return true;
    }
    
    if (is_array($permisos[$modulo])) {
        return in_array($accion, $permisos[$modulo]);
    }
    
    return false;
}

// Requiere un permiso específico
function requirePermission($modulo, $accion = 'ver') {
    requireAuth();
    
    if (!hasPermission($modulo, $accion)) {
        redirect(url('sin-permisos'));
    }
}