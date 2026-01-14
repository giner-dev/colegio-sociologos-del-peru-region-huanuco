<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/RolService.php';

class RolController extends Controller {
    private $rolService;

    public function __construct() {
        parent::__construct();
        $this->rolService = new RolService();
    }

    public function index() {
        $this->requireAuth();
        $this->requireRole('administrador');

        $roles = $this->rolService->obtenerTodos();

        $this->render('roles/index', [
            'roles' => $roles,
            'active_menu' => 'roles',
            'titulo' => 'Gestión de Roles'
        ]);
    }

    public function crear() {
        $this->requireAuth();
        $this->requireRole('administrador');

        $permisosDisponibles = $this->rolService->obtenerPermisosDisponibles();

        $this->render('roles/crear', [
            'permisosDisponibles' => $permisosDisponibles,
            'active_menu' => 'roles',
            'titulo' => 'Crear Rol'
        ]);
    }

    public function guardar() {
        $this->requireAuth();
        $this->requireRole('administrador');
        $this->validateMethod('POST');

        $permisos = [];
        if (isset($_POST['permisos']) && is_array($_POST['permisos'])) {
            $permisos = $_POST['permisos'];
        }

        $datos = [
            'nombre_rol' => $this->getPost('nombre_rol'),
            'descripcion' => $this->getPost('descripcion'),
            'estado' => $this->getPost('estado', 'activo'),
            'permisos' => $permisos
        ];

        $resultado = $this->rolService->crear($datos);

        if ($resultado['success']) {
            $this->setSuccess('Rol creado correctamente');
            $this->redirect(url('roles'));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('roles/crear'));
        }
    }

    public function editar($id) {
        $this->requireAuth();
        $this->requireRole('administrador');

        $rol = $this->rolService->obtenerPorId($id);

        if (!$rol) {
            $this->setError('Rol no encontrado');
            $this->redirect(url('roles'));
            return;
        }

        $permisosDisponibles = $this->rolService->obtenerPermisosDisponibles();

        $this->render('roles/editar', [
            'rol' => $rol,
            'permisosDisponibles' => $permisosDisponibles,
            'active_menu' => 'roles',
            'titulo' => 'Editar Rol'
        ]);
    }

    public function actualizar($id) {
        $this->requireAuth();
        $this->requireRole('administrador');
        $this->validateMethod('POST');

        $permisos = [];
        if (isset($_POST['permisos']) && is_array($_POST['permisos'])) {
            $permisos = $_POST['permisos'];
        }

        $datos = [
            'nombre_rol' => $this->getPost('nombre_rol'),
            'descripcion' => $this->getPost('descripcion'),
            'estado' => $this->getPost('estado', 'activo'),
            'permisos' => $permisos
        ];

        $resultado = $this->rolService->actualizar($id, $datos);

        if ($resultado['success']) {
            $this->setSuccess('Rol actualizado correctamente');
            $this->redirect(url('roles'));
        } else {
            $this->setError(implode(', ', $resultado['errors']));
            $this->redirect(url('roles/editar/' . $id));
        }
    }

    public function cambiarEstado($id) {
        $this->requireAuth();
        $this->requireRole('administrador');
        $this->validateMethod('POST');

        $nuevoEstado = $this->getPost('estado');

        $resultado = $this->rolService->cambiarEstado($id, $nuevoEstado);

        if ($resultado['success']) {
            $this->setSuccess($resultado['message']);
        } else {
            $this->setError($resultado['message']);
        }

        $this->redirect(url('roles'));
    }

    public function eliminar($id) {
        $this->requireAuth();
        $this->requireRole('administrador');
        $this->validateMethod('POST');

        $resultado = $this->rolService->eliminar($id);

        if ($resultado['success']) {
            $this->setSuccess($resultado['message']);
        } else {
            $this->setError($resultado['message']);
        }

        $this->redirect(url('roles'));
    }
}