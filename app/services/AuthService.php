<?php
require_once __DIR__ . '/../repositories/UsuarioRepository.php';

// Maneja toda la lógica de negocio
class AuthService{
    private $usuarioRepository;

    public function __construct(){
        $this->usuarioRepository = new UsuarioRepository();
    }

    // Intenta autenticar un usuario
    public function login($username, $password){
        // Buscar usuario por nombre de usuario
        $usuario = $this->usuarioRepository->findByUsername($username);

        // Verificar si existe el usuario
        if(!$usuario){
            logMessage("Intento de login fallido: usuario '$username' no encontrado", 'warning');
            return [
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos'
            ];
        }

        // Verificar si el usuario está activo
        if (!$usuario->isActivo()) {
            logMessage("Intento de login con usuario inactivo: '$username'", 'warning');
            return [
                'success' => false,
                'message' => 'Usuario inactivo. Contacte al administrador'
            ];
        }

        // Verificar la contraseña
        if (!$usuario->verificarPassword($password)) {
            logMessage("Intento de login fallido: contraseña incorrecta para usuario '$username'", 'warning');
            return [
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos'
            ];
        }

        // Actualizar fecha de último acceso
        $this->usuarioRepository->updateLastAccess($usuario->idUsuario);

        // Login exitoso
        logMessage("Login exitoso: usuario '$username' (ID: {$usuario->idUsuario})", 'info');
        
        return [
            'success' => true,
            'usuario' => $usuario
        ];
    }

    public function crearSesion($usuario){
        session_regenerate_id(true);

        // Guardar datos en sesión
        $_SESSION['usuario_id'] = $usuario->idUsuario;
        $_SESSION['usuario_nombre'] = $usuario->getNombreCompleto();
        $_SESSION['usuario_username'] = $usuario->nombre_usuario;
        $_SESSION['usuario_rol'] = $usuario->nombre_rol;
        $_SESSION['usuario_rol_id'] = $usuario->RolId;
        $_SESSION['usuario_correo'] = $usuario->correo;
        $_SESSION['login_time'] = time();

        // marca de tiempo de última actividad
        $_SESSION['LAST_ACTIVITY'] = time();
        
        // Token CSRF
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public function logout() {
        $username = $_SESSION['usuario_username'] ?? 'Desconocido';
        
        // Limpiar sesión
        $_SESSION = array();
        
        // Destruir cookie de sesión
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destruir sesión
        session_destroy();
        
        logMessage("Logout: usuario '$username'", 'info');
    }

    // Verificar si hay sesión activa
    public function isAuthenticated() {
        return isset($_SESSION['usuario_id']);
    }

    // Obtiene el usuario autenticado actual
    public function getUsuarioAutenticado() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return $this->usuarioRepository->findById($_SESSION['usuario_id']);
    }

    // Verifica si el usuario tiene un rol específico
    public function tieneRol($rolesPermitidos) {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        $rolActual = $_SESSION['usuario_rol'] ?? null;
        
        if (is_array($rolesPermitidos)) {
            return in_array($rolActual, $rolesPermitidos);
        }
        
        return $rolActual === $rolesPermitidos;
    }

    // Verifica si la sesión ha expirado
    public function verificarExpiracionSesion() {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        $tiempoVida = env('SESSION_LIFETIME', 120); // minutos
        $tiempoLogin = $_SESSION['login_time'] ?? 0;
        $tiempoActual = time();
        
        $diferencia = ($tiempoActual - $tiempoLogin) / 60;
        
        if ($diferencia > $tiempoVida) {
            $this->logout();
            return true;
        }
        
        return false;
    }

    // Cambia la contraseña de un usuario
    public function cambiarPassword($usuarioId, $passwordActual, $passwordNueva) {
        $usuario = $this->usuarioRepository->findById($usuarioId);
        
        if (!$usuario) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado'
            ];
        }
        
        // Verificar contraseña actual
        if (!$usuario->verificarPassword($passwordActual)) {
            return [
                'success' => false,
                'message' => 'La contraseña actual es incorrecta'
            ];
        }
        
        // Validar nueva contraseña
        if (strlen($passwordNueva) < 6) {
            return [
                'success' => false,
                'message' => 'La nueva contraseña debe tener al menos 6 caracteres'
            ];
        }
        
        // Encriptar y actualizar
        $hashedPassword = Usuario::hashPassword($passwordNueva);
        $this->usuarioRepository->updatePassword($usuarioId, $hashedPassword);
        
        logMessage("Contraseña cambiada para usuario ID: $usuarioId", 'info');
        
        return [
            'success' => true,
            'message' => 'Contraseña actualizada correctamente'
        ];
    }

    // Valida los datos de login
    public function validarDatosLogin($username, $password) {
        $errores = [];
        
        if (empty(trim($username))) {
            $errores[] = 'El nombre de usuario es obligatorio';
        }
        
        if (empty(trim($password))) {
            $errores[] = 'La contraseña es obligatoria';
        }
        
        return $errores;
    }

}