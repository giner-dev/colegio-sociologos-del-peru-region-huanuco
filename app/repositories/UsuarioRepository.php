<?php
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioRepository{
    private $db;

    public function __construct(){
        $this->db = Database::getInstance();
    }

    // Función busca un usuario por su nombre de usuario
    public function findByUsername($username){
        $sql = "SELECT u.*, r.nombre_rol 
                FROM usuarios u 
                INNER JOIN roles r ON u.RolId = r.idRol 
                WHERE u.nombre_usuario = :username";
        $result = $this->db->queryOne($sql, ['username' => $username]);

        if ($result){
            return new Usuario($result);
        }

        return null;
    }

    // Función busca un usuario por su ID
    public function findById($id){
        $sql = "SELECT u.*, r.nombre_rol 
                FROM usuarios u 
                INNER JOIN roles r ON u.RolId = r.idRol 
                WHERE u.idUsuario = :id";
        
        $result = $this->db->queryOne($sql, ['id' => $id]);
        
        if ($result) {
            return new Usuario($result);
        }
        
        return null;
    }

    // Función obtiene todos los usuarios
    public function findAll() {
        $sql = "SELECT u.*, r.nombre_rol 
                FROM usuarios u 
                INNER JOIN roles r ON u.RolId = r.idRol 
                ORDER BY u.fecha_creacion DESC";
        
        $results = $this->db->query($sql);
        
        $usuarios = [];
        foreach ($results as $row) {
            $usuarios[] = new Usuario($row);
        }
        
        return $usuarios;
    }

    // Función obtiene usuarios activos solamente
    public function findActivos() {
        $sql = "SELECT u.*, r.nombre_rol 
                FROM usuarios u 
                INNER JOIN roles r ON u.RolId = r.idRol 
                WHERE u.estado = 'activo' 
                ORDER BY u.nombres ASC";
        
        $results = $this->db->query($sql);
        
        $usuarios = [];
        foreach ($results as $row) {
            $usuarios[] = new Usuario($row);
        }
        
        return $usuarios;
    }

    // Función crea un nuevo usuario
    public function create($data) {
        $sql = "INSERT INTO usuarios (RolId, nombre_usuario, contrasenia, nombres, apellidos, correo, telefono, estado) 
                VALUES (:RolId, :nombre_usuario, :contrasenia, :nombres, :apellidos, :correo, :telefono, :estado)";
        
        return $this->db->insert($sql, $data);
    }

    // Función actualiza un usuario existente
    public function update($id, $data) {
        $sql = "UPDATE usuarios 
                SET RolId = :RolId, 
                    nombre_usuario = :nombre_usuario, 
                    nombres = :nombres, 
                    apellidos = :apellidos, 
                    correo = :correo, 
                    telefono = :telefono, 
                    estado = :estado 
                WHERE idUsuario = :id";
        
        $data['id'] = $id;
        return $this->db->execute($sql, $data);
    }

    // Función actualiza la contraseña de un usuario
    public function updatePassword($id, $hashedPassword) {
        $sql = "UPDATE usuarios SET contrasenia = :password WHERE idUsuario = :id";
        return $this->db->execute($sql, ['id' => $id, 'password' => $hashedPassword]);
    }

    // Función actualiza la fecha del último acceso
    public function updateLastAccess($id) {
        $sql = "UPDATE usuarios SET fecha_ultimo_acceso = NOW() WHERE idUsuario = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    // Función cambia el estado de un usuario
    public function cambiarEstado($id, $nuevoEstado) {
        $sql = "UPDATE usuarios SET estado = :estado WHERE idUsuario = :id";
        return $this->db->execute($sql, ['id' => $id, 'estado' => $nuevoEstado]);
    }

    // Función verifica si existe un nombre de usuario
    public function existeUsername($username, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE nombre_usuario = :username AND idUsuario != :id";
            $result = $this->db->queryOne($sql, ['username' => $username, 'id' => $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE nombre_usuario = :username";
            $result = $this->db->queryOne($sql, ['username' => $username]);
        }
        
        return $result['total'] > 0;
    }

    // Función verifica si existe un correo electrónico
    public function existeCorreo($correo, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE correo = :correo AND idUsuario != :id";
            $result = $this->db->queryOne($sql, ['correo' => $correo, 'id' => $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE correo = :correo";
            $result = $this->db->queryOne($sql, ['correo' => $correo]);
        }
        
        return $result['total'] > 0;
    }

    // Función obtiene la cantidad total de usuarios
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM usuarios";
        $result = $this->db->queryOne($sql);
        return $result['total'];
    }

    // Función obtiene usuarios por rol
    public function findByRol($rolId) {
        $sql = "SELECT u.*, r.nombre_rol 
                FROM usuarios u 
                INNER JOIN roles r ON u.RolId = r.idRol 
                WHERE u.RolId = :rolId 
                ORDER BY u.nombres ASC";
        
        $results = $this->db->query($sql, ['rolId' => $rolId]);
        
        $usuarios = [];
        foreach ($results as $row) {
            $usuarios[] = new Usuario($row);
        }
        
        return $usuarios;
    }
}