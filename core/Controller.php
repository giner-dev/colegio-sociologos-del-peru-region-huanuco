<?php
/**
 * CLASE CONTROLLER (BASE)
 * Todos los controladores heredan de esta clase
 */

class Controller{
    protected $view; // Instancia de View para renderizar vistas

    public function __construct(){
        // Iniciar sesion si no está iniciada
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }

        // Cargar el motor de vistas
        $this->view = new View();
    }

    // Renderizar una vista
    // Ejemplo: $this->render('colegiados/index', ['colegiados' => $listaColegiados]);
    protected function render($viewPath, $data = []){
        $this->view->render($viewPath, $data);
    }

    // Retorna una respuesta JSON
    // Ejemplo: $this->json(['success' => true, 'message' => 'Usuario creado']);
    protected function json($data, $statusCode = 200){
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Redirecciona a una URL
    // Ejemplo: $this->redirect('/colegiados');
    protected function redirect($url) {
        redirect($url);
    }

    // Redirecciona de vuelta a la página anterior
    protected function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        redirect($referer);
    }

    // Valida que el método HTTP sea el esperado
    // Ejemplo: $this->validateMethod('POST'); // Solo permite POST
    protected function validateMethod($method) {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            throw new Exception("Método no permitido. Se esperaba: $method");
        }
        return true;
    }

    // Verifica que el usuario esté autenticado (Si no lo está, redirige al login)
    protected function requireAuth() {
        requireAuth();
    }

    // Verifica que el usuario tenga un rol específico
    // Ejemplo: $this->requireRole('administrador'); || $this->requireRole(['administrador', 'tesorero']);
    protected function requireRole($roles) {
        requireRole($roles);
    }

    // Obtener datos del formulario POST de forma segura
    // Ejemplo: $nombre = $this->getPost('nombre'); || $email = $this->getPost('email', 'default@ejemplo.com');
    protected function getPost($key, $default = null) {
        if (isset($_POST[$key])) {
            return sanitize($_POST[$key]);
        }
        return $default;
    }

    // Obtiene datos del query string GET de forma segura
    // Ejemplo: $page = $this->getQuery('page', 1);
    protected function getQuery($key, $default = null) {
        if (isset($_GET[$key])) {
            return sanitize($_GET[$key]);
        }
        return $default;
    }

    // Obtiene todos los datos POST
    protected function getAllPost() {
        $data = [];
        foreach ($_POST as $key => $value) {
            $data[$key] = sanitize($value);
        }
        return $data;
    }

    // Valida datos POST con reglas
    /**Ejemplo:
     * $errors = $this->validate($_POST, [
     *     'nombre' => 'required',
     *     'email' => 'required|email',
     *     'password' => 'required|min:6'
     * ]);
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            // Separar múltiples reglas por |
            $rulesArray = explode('|', $rule);
            
            foreach ($rulesArray as $singleRule) {
                // Verificar si la regla tiene parámetros (ej: min:6)
                $params = explode(':', $singleRule);
                $ruleName = $params[0];
                $ruleValue = $params[1] ?? null;
                
                $value = $data[$field] ?? '';
                
                switch ($ruleName) {
                    case 'required':
                        if (!required($value)) {
                            $errors[$field][] = "El campo $field es obligatorio";
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !validEmail($value)) {
                            $errors[$field][] = "El campo $field debe ser un email válido";
                        }
                        break;
                        
                    case 'min':
                        if (!minLength($value, $ruleValue)) {
                            $errors[$field][] = "El campo $field debe tener mínimo $ruleValue caracteres";
                        }
                        break;
                        
                    case 'max':
                        if (!maxLength($value, $ruleValue)) {
                            $errors[$field][] = "El campo $field debe tener máximo $ruleValue caracteres";
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }

    // Establece un mensaje flash de éxito
    protected function setSuccess($message) {
        setFlash('success', $message);
    }

    // Establece un mensaje flash de error
    protected function setError($message) {
        setFlash('error', $message);
    }

    // Establece un mensaje flash de advertencia
    protected function setWarning($message) {
        setFlash('warning', $message);
    }

    // Establece un mensaje flash informativo
    protected function setInfo($message) {
        setFlash('info', $message);
    }

}