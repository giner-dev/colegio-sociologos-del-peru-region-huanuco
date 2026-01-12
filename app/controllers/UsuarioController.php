<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/UsuarioService.php';

class UsuarioController extends Controller {
    private $usuarioService;

    public function __construct() {
        parent::__construct();
        $this->usuarioService = new UsuarioService();
    }

    public function index() {
        $this->requireAuth();
        $this->requireRole('administrador');

        $usuarios = $this->usuarioService->obtenerTodos();

        $this->render('usuarios/index', [
            'usuarios' => $usuarios,
            'active_menu' => 'usuarios',
            'titulo' => 'Gestión de Usuarios'
        ]);
    }

    public function crear() {
        $this->requireAuth();
        $this->requireRole('administrador');

        $roles = $this->obtenerRoles();

        $this->render('usuarios/crear', [
            'roles' => $roles,
            'titulo' => 'Nuevo Usuario',
            'active_menu' => 'usuarios'
        ]);
    }

    public function guardar() {
        $this->requireAuth();
        $this->requireRole('administrador');
        $this->validateMethod('POST');

        $datos = [
            'RolId' => $this->getPost('RolId'),
            'nombre_usuario' => $this->getPost('nombre_usuario'),
            'contrasenia' => $this->getPost('contrasenia'),
            'nombres' => $this->getPost('nombres'),
            'apellidos' => $this->getPost('apellidos'),
            'correo' => $this->getPost('correo'),
            'telefono' => $this->getPost('telefono')
        ];

        $resultado = $this->usuarioService->crear($datos);

        if ($resultado['success']) {
            $this->setSuccess('Usuario registrado correctamente');
            $this->redirect(url('usuarios'));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('usuarios/crear'));
        }
    }

    public function editar($id) {
        $this->requireAuth();
        $this->requireRole('administrador');

        $usuario = $this->usuarioService->obtenerPorId($id);

        if (!$usuario) {
            $this->setError('Usuario no encontrado');
            $this->redirect(url('usuarios'));
            return;
        }

        $roles = $this->obtenerRoles();

        $this->render('usuarios/editar', [
            'usuario' => $usuario,
            'roles' => $roles,
            'active_menu' => 'usuarios',
            'titulo' => 'Editar Usuario'
        ]);
    }

    public function actualizar($id) {
        $this->requireAuth();
        $this->requireRole('administrador');
        $this->validateMethod('POST');

        $datos = [
            'RolId' => $this->getPost('RolId'),
            'nombre_usuario' => $this->getPost('nombre_usuario'),
            'nombres' => $this->getPost('nombres'),
            'apellidos' => $this->getPost('apellidos'),
            'correo' => $this->getPost('correo'),
            'telefono' => $this->getPost('telefono'),
            'estado' => $this->getPost('estado')
        ];

        $resultado = $this->usuarioService->actualizar($id, $datos);

        if ($resultado['success']) {
            $this->setSuccess('Usuario actualizado correctamente');
            $this->redirect(url('usuarios'));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('usuarios/editar/' . $id));
        }
    }

    public function cambiarEstado($id) {
        $this->requireAuth();
        $this->requireRole('administrador');
        $this->validateMethod('POST');

        $nuevoEstado = $this->getPost('estado');

        $resultado = $this->usuarioService->cambiarEstado($id, $nuevoEstado);

        if ($resultado['success']) {
            $this->setSuccess($resultado['message']);
        } else {
            $this->setError($resultado['message']);
        }

        $this->redirect(url('usuarios'));
    }

    public function perfil() {
        $this->requireAuth();

        $usuarioId = authUserId();
        $usuario = $this->usuarioService->obtenerPorId($usuarioId);

        if (!$usuario) {
            $this->setError('Usuario no encontrado');
            $this->redirect(url('dashboard'));
            return;
        }

        $this->render('usuarios/perfil', [
            'usuario' => $usuario,
            'active_menu' => 'perfil',
            'titulo' => 'Mi Perfil'
        ]);
    }

    public function actualizarPerfil() {
        $this->requireAuth();
        $this->validateMethod('POST');

        $usuarioId = authUserId();

        $datos = [
            'nombres' => $this->getPost('nombres'),
            'apellidos' => $this->getPost('apellidos'),
            'correo' => $this->getPost('correo'),
            'telefono' => $this->getPost('telefono')
        ];

        $resultado = $this->usuarioService->actualizarPerfil($usuarioId, $datos);

        if ($resultado['success']) {
            $this->setSuccess('Perfil actualizado correctamente');
        } else {
            $this->setError(implode(', ', $resultado['errors']));
        }

        $this->redirect(url('usuarios/perfil'));
    }

    public function cambiarPassword() {
        $this->requireAuth();
        $this->validateMethod('POST');

        $usuarioId = authUserId();
        $passwordActual = $this->getPost('password_actual');
        $passwordNueva = $this->getPost('password_nueva');
        $passwordConfirmar = $this->getPost('password_confirmar');

        if ($passwordNueva !== $passwordConfirmar) {
            $this->setError('Las contraseñas nuevas no coinciden');
            $this->redirect(url('usuarios/perfil'));
            return;
        }

        $resultado = $this->usuarioService->cambiarPassword($usuarioId, $passwordActual, $passwordNueva);

        if ($resultado['success']) {
            $this->setSuccess($resultado['message']);
        } else {
            $this->setError(implode(', ', $resultado['errors']));
        }

        $this->redirect(url('usuarios/perfil'));
    }

    private function obtenerRoles() {
        $db = Database::getInstance();
        $sql = "SELECT idRol, nombre_rol FROM roles WHERE estado = 'activo' ORDER BY nombre_rol ASC";
        return $db->query($sql);
    }
}