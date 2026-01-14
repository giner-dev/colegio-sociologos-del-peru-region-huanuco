<?php
require_once __DIR__ . '/../repositories/RolRepository.php';

class RolService {
    private $rolRepository;

    public function __construct() {
        $this->rolRepository = new RolRepository();
    }

    public function obtenerTodos() {
        return $this->rolRepository->findAll();
    }

    public function obtenerActivos() {
        return $this->rolRepository->findActivos();
    }

    public function obtenerPorId($id) {
        return $this->rolRepository->findById($id);
    }

    public function crear($datos) {
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        if ($this->rolRepository->existeNombre($datos['nombre_rol'])) {
            return ['success' => false, 'errors' => ['El nombre del rol ya existe']];
        }

        $permisos = $this->construirPermisos($datos);

        $datosInsert = [
            'nombre_rol' => $datos['nombre_rol'],
            'descripcion' => $datos['descripcion'] ?? null,
            'permisos' => json_encode($permisos),
            'estado' => $datos['estado'] ?? 'activo'
        ];

        $id = $this->rolRepository->create($datosInsert);

        if ($id) {
            logMessage("Rol creado: ID $id - {$datos['nombre_rol']}", 'info');
            return ['success' => true, 'id' => $id];
        }

        return ['success' => false, 'errors' => ['Error al crear el rol']];
    }

    public function actualizar($id, $datos) {
        $rol = $this->rolRepository->findById($id);
        if (!$rol) {
            return ['success' => false, 'errors' => ['Rol no encontrado']];
        }

        $errores = $this->validarDatos($datos, $id);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        if ($this->rolRepository->existeNombre($datos['nombre_rol'], $id)) {
            return ['success' => false, 'errors' => ['El nombre del rol ya existe']];
        }

        $permisos = $this->construirPermisos($datos);

        $datosUpdate = [
            'nombre_rol' => $datos['nombre_rol'],
            'descripcion' => $datos['descripcion'] ?? null,
            'permisos' => json_encode($permisos),
            'estado' => $datos['estado'] ?? 'activo'
        ];

        $resultado = $this->rolRepository->update($id, $datosUpdate);

        if ($resultado) {
            logMessage("Rol actualizado: ID $id", 'info');
            return ['success' => true];
        }

        return ['success' => false, 'errors' => ['Error al actualizar el rol']];
    }

    public function cambiarEstado($id, $nuevoEstado) {
        $rol = $this->rolRepository->findById($id);
        
        if (!$rol) {
            return ['success' => false, 'message' => 'Rol no encontrado'];
        }

        if ($rol->estado === $nuevoEstado) {
            return ['success' => false, 'message' => 'El rol ya tiene ese estado'];
        }

        $this->rolRepository->cambiarEstado($id, $nuevoEstado);
        
        logMessage("Estado cambiado para rol ID $id: {$rol->estado} -> $nuevoEstado", 'info');
        
        return ['success' => true, 'message' => 'Estado actualizado correctamente'];
    }

    public function eliminar($id) {
        $rol = $this->rolRepository->findById($id);
        
        if (!$rol) {
            return ['success' => false, 'message' => 'Rol no encontrado'];
        }

        if ($this->rolRepository->tieneUsuariosAsociados($id)) {
            return ['success' => false, 'message' => 'No se puede eliminar el rol porque tiene usuarios asociados'];
        }

        $resultado = $this->rolRepository->delete($id);

        if ($resultado) {
            logMessage("Rol eliminado: ID $id - {$rol->nombre_rol}", 'info');
            return ['success' => true, 'message' => 'Rol eliminado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al eliminar el rol'];
    }

    private function validarDatos($datos, $excludeId = null) {
        $errores = [];

        if (empty($datos['nombre_rol'])) {
            $errores[] = 'El nombre del rol es obligatorio';
        }

        return $errores;
    }

    private function construirPermisos($datos) {
        $permisos = [];
        
        $modulos = [
            'dashboard', 'colegiados', 'deudas', 'pagos', 
            'egresos', 'reportes', 'usuarios', 'roles', 
            'conceptos', 'metodos'
        ];
    
        foreach ($modulos as $modulo) {
            // Verificar si el módulo principal está marcado
            $moduloCheckbox = $datos['permisos'][$modulo] ?? null;
            
            if ($moduloCheckbox) {
                $acciones = [];
                
                // Buscar las acciones individuales
                foreach (['ver', 'crear', 'editar', 'eliminar'] as $accion) {
                    $key = $modulo . '_' . $accion;
                    if (isset($datos['permisos'][$key]) && $datos['permisos'][$key]) {
                        $acciones[] = $accion;
                    }
                }
                
                if (!empty($acciones)) {
                    $permisos[$modulo] = $acciones;
                }
            }
        }
    
        // Si no hay permisos, retornar objeto vacío en lugar de array vacío
        return empty($permisos) ? new stdClass() : $permisos;
    }

    public function obtenerPermisosDisponibles() {
        return [
            'dashboard' => 'Escritorio',
            'colegiados' => 'Colegiados',
            'deudas' => 'Deudas',
            'pagos' => 'Pagos',
            'egresos' => 'Egresos',
            'reportes' => 'Reportes',
            'usuarios' => 'Usuarios',
            'roles' => 'Roles',
            'conceptos' => 'Conceptos de Pago',
            'metodos' => 'Métodos de Pago'
        ];
    }
}