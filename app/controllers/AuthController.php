<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../repositories/RolRepository.php';

// Maneja las peticiones
class AuthController extends Controller{
    private $authService;

    public function __construct(){
        parent::__construct();
        $this->authService = new AuthService();
    }

    // Muestra el formulario de login
    public function showLogin(){
        if ($this->authService->isAuthenticated()) {
            $this->redirect(url('dashboard'));
            return;
        }

        $this->view->setLayout(null);
        $this->render('auth/login');
    }

    // Procesa el intento de login
    public function login() {
        $this->validateMethod('POST');
        
        $username = $this->getPost('username');
        $password = $this->getPost('password');
        
        $errores = $this->authService->validarDatosLogin($username, $password);
        
        if (!empty($errores)) {
            $this->setError(implode(', ', $errores));
            $this->redirect(url('login'));
            return;
        }
        
        // Intentar autenticar
        $resultado = $this->authService->login($username, $password);
        
        if (!$resultado['success']) {
            $this->setError($resultado['message']);
            $this->redirect(url('login'));
            return;
        }
        
        // Login exitoso - Crear sesión
        $this->authService->crearSesion($resultado['usuario']);

        // Cargar permisos del rol
        $usuario = $resultado['usuario'];
        $rolRepository = new RolRepository();
        $rol = $rolRepository->findById($usuario->RolId);
            
        if ($rol) {
        // ASEGURAR que siempre sea array
        $permisos = $rol->permisos;
            
        // Si viene como string JSON, decodificar
        if (is_string($permisos)) {
            $permisos = json_decode($permisos, true) ?? [];
        }

        // Si no es array, inicializar vacío
        if (!is_array($permisos)) {
            $permisos = [];
        }

        $_SESSION['usuario_permisos'] = $permisos;
    }

        // establecer marca de tiempo de última actividad
        $_SESSION['LAST_ACTIVITY'] = time();
        
        $nombreUsuario = $resultado['usuario']->getNombreCompleto();
        $this->setSuccess("Bienvenido, $nombreUsuario");
        
        // Redirigir al dashboard
        $redirectUrl = $_SESSION['redirect_after_login'] ?? url('dashboard');
        unset($_SESSION['redirect_after_login']);
        
        $this->redirect($redirectUrl);
    }

    // Cierra la sesión del usuario
    public function logout() {
        $this->authService->logout();
        $this->setSuccess('Sesión cerrada correctamente');
        $this->redirect(url('login'));
    }
}