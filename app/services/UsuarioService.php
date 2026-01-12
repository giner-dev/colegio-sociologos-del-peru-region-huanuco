<?php
require_once __DIR__ . '/../repositories/UsuarioRepository.php';

class UsuarioService {
    private $usuarioRepository;

    public function __construct() {
        $this->usuarioRepository = new UsuarioRepository();
    }

    public function obtenerTodos() {
        return $this->usuarioRepository->findAll();
    }

    public function obtenerActivos() {
        return $this->usuarioRepository->findActivos();
    }

    public function obtenerPorId($id) {
        return $this->usuarioRepository->findById($id);
    }

    public function obtenerPorUsername($username) {
        return $this->usuarioRepository->findByUsername($username);
    }

    public function crear($datos) {
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        if ($this->usuarioRepository->existeUsername($datos['nombre_usuario'])) {
            return ['success' => false, 'errors' => ['El nombre de usuario ya existe']];
        }

        if (!empty($datos['correo']) && $this->usuarioRepository->existeCorreo($datos['correo'])) {
            return ['success' => false, 'errors' => ['El correo electrónico ya está registrado']];
        }

        $datosInsert = [
            'RolId' => $datos['RolId'],
            'nombre_usuario' => $datos['nombre_usuario'],
            'contrasenia' => Usuario::hashPassword($datos['contrasenia']),
            'nombres' => $datos['nombres'],
            'apellidos' => $datos['apellidos'],
            'correo' => $datos['correo'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'estado' => 'activo'
        ];

        $id = $this->usuarioRepository->create($datosInsert);

        if ($id) {
            logMessage("Usuario creado: ID $id - {$datos['nombre_usuario']}", 'info');
            return ['success' => true, 'id' => $id];
        }

        return ['success' => false, 'errors' => ['Error al crear el usuario']];
    }

    public function actualizar($id, $datos) {
        $usuario = $this->usuarioRepository->findById($id);
        if (!$usuario) {
            return ['success' => false, 'errors' => ['Usuario no encontrado']];
        }

        $errores = $this->validarDatos($datos, $id);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        if ($this->usuarioRepository->existeUsername($datos['nombre_usuario'], $id)) {
            return ['success' => false, 'errors' => ['El nombre de usuario ya existe']];
        }

        if (!empty($datos['correo']) && $this->usuarioRepository->existeCorreo($datos['correo'], $id)) {
            return ['success' => false, 'errors' => ['El correo electrónico ya está registrado']];
        }

        $datosUpdate = [
            'RolId' => $datos['RolId'],
            'nombre_usuario' => $datos['nombre_usuario'],
            'nombres' => $datos['nombres'],
            'apellidos' => $datos['apellidos'],
            'correo' => $datos['correo'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'estado' => $datos['estado']
        ];

        $resultado = $this->usuarioRepository->update($id, $datosUpdate);

        if ($resultado) {
            logMessage("Usuario actualizado: ID $id", 'info');
            return ['success' => true];
        }

        return ['success' => false, 'errors' => ['Error al actualizar el usuario']];
    }

    public function cambiarEstado($id, $nuevoEstado) {
        $usuario = $this->usuarioRepository->findById($id);

        if (!$usuario) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        if ($usuario->estado === $nuevoEstado) {
            return ['success' => false, 'message' => 'El usuario ya tiene ese estado'];
        }

        $resultado = $this->usuarioRepository->cambiarEstado($id, $nuevoEstado);

        if ($resultado) {
            logMessage("Estado cambiado para usuario ID $id: {$usuario->estado} -> $nuevoEstado", 'info');
            return ['success' => true, 'message' => 'Estado actualizado correctamente'];
        }

        return ['success' => false, 'message' => 'Error al cambiar el estado'];
    }

    public function cambiarPassword($id, $passwordActual, $passwordNueva) {
        $usuario = $this->usuarioRepository->findById($id);

        if (!$usuario) {
            return ['success' => false, 'errors' => ['Usuario no encontrado']];
        }

        if (!$usuario->verificarPassword($passwordActual)) {
            return ['success' => false, 'errors' => ['La contraseña actual es incorrecta']];
        }

        if (strlen($passwordNueva) < 6) {
            return ['success' => false, 'errors' => ['La contraseña debe tener al menos 6 caracteres']];
        }

        $hashedPassword = Usuario::hashPassword($passwordNueva);
        $resultado = $this->usuarioRepository->updatePassword($id, $hashedPassword);

        if ($resultado) {
            logMessage("Contraseña cambiada para usuario ID $id", 'info');
            return ['success' => true, 'message' => 'Contraseña actualizada correctamente'];
        }

        return ['success' => false, 'errors' => ['Error al cambiar la contraseña']];
    }

    public function actualizarPerfil($id, $datos) {
        $usuario = $this->usuarioRepository->findById($id);
        if (!$usuario) {
            return ['success' => false, 'errors' => ['Usuario no encontrado']];
        }

        $errores = [];

        if (empty($datos['nombres'])) {
            $errores[] = 'Los nombres son obligatorios';
        }

        if (empty($datos['apellidos'])) {
            $errores[] = 'Los apellidos son obligatorios';
        }

        if (!empty($datos['correo']) && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo electrónico no es válido';
        }

        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        if (!empty($datos['correo']) && $this->usuarioRepository->existeCorreo($datos['correo'], $id)) {
            return ['success' => false, 'errors' => ['El correo electrónico ya está registrado']];
        }

        $datosUpdate = [
            'RolId' => $usuario->RolId,
            'nombre_usuario' => $usuario->nombre_usuario,
            'nombres' => $datos['nombres'],
            'apellidos' => $datos['apellidos'],
            'correo' => $datos['correo'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'estado' => $usuario->estado
        ];

        $resultado = $this->usuarioRepository->update($id, $datosUpdate);

        if ($resultado) {
            $_SESSION['usuario_nombre'] = $datosUpdate['nombres'] . ' ' . $datosUpdate['apellidos'];
            logMessage("Perfil actualizado: ID $id", 'info');
            return ['success' => true];
        }

        return ['success' => false, 'errors' => ['Error al actualizar el perfil']];
    }

    private function validarDatos($datos, $excludeId = null) {
        $errores = [];

        if (empty($datos['RolId'])) {
            $errores[] = 'El rol es obligatorio';
        }

        if (empty($datos['nombre_usuario'])) {
            $errores[] = 'El nombre de usuario es obligatorio';
        } elseif (strlen($datos['nombre_usuario']) < 4) {
            $errores[] = 'El nombre de usuario debe tener al menos 4 caracteres';
        }

        if ($excludeId === null && empty($datos['contrasenia'])) {
            $errores[] = 'La contraseña es obligatoria';
        }

        if (!empty($datos['contrasenia']) && strlen($datos['contrasenia']) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if (empty($datos['nombres'])) {
            $errores[] = 'Los nombres son obligatorios';
        }

        if (empty($datos['apellidos'])) {
            $errores[] = 'Los apellidos son obligatorios';
        }

        if (!empty($datos['correo']) && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo electrónico no es válido';
        }

        return $errores;
    }

    public function obtenerPorRol($rolId) {
        return $this->usuarioRepository->findByRol($rolId);
    }

    public function contarTotal() {
        return $this->usuarioRepository->count();
    }
}