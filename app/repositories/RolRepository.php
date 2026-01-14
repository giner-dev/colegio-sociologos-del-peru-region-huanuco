<?php
require_once __DIR__ . '/../models/Rol.php';

class RolRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAll() {
        $sql = "SELECT * FROM roles ORDER BY nombre_rol ASC";
        $results = $this->db->query($sql);
        
        $roles = [];
        foreach ($results as $row) {
            $roles[] = new Rol($row);
        }
        
        return $roles;
    }

    public function findActivos() {
        $sql = "SELECT * FROM roles WHERE estado = 'activo' ORDER BY nombre_rol ASC";
        $results = $this->db->query($sql);
        
        $roles = [];
        foreach ($results as $row) {
            $roles[] = new Rol($row);
        }
        
        return $roles;
    }

    public function findById($id) {
        $sql = "SELECT * FROM roles WHERE idRol = :id";
        $result = $this->db->queryOne($sql, ['id' => $id]);
        
        if ($result) {
            return new Rol($result);
        }
        
        return null;
    }

    public function findByNombre($nombre) {
        $sql = "SELECT * FROM roles WHERE nombre_rol = :nombre";
        $result = $this->db->queryOne($sql, ['nombre' => $nombre]);
        
        if ($result) {
            return new Rol($result);
        }
        
        return null;
    }

    public function create($data) {
        $sql = "INSERT INTO roles (nombre_rol, descripcion, permisos, estado)
                VALUES (:nombre_rol, :descripcion, :permisos, :estado)";
        
        return $this->db->insert($sql, [
            'nombre_rol' => $data['nombre_rol'],
            'descripcion' => $data['descripcion'] ?? null,
            'permisos' => $data['permisos'],
            'estado' => $data['estado'] ?? 'activo'
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE roles SET
                    nombre_rol = :nombre_rol,
                    descripcion = :descripcion,
                    permisos = :permisos,
                    estado = :estado
                WHERE idRol = :id";
        
        return $this->db->execute($sql, [
            'id' => $id,
            'nombre_rol' => $data['nombre_rol'],
            'descripcion' => $data['descripcion'] ?? null,
            'permisos' => $data['permisos'],
            'estado' => $data['estado'] ?? 'activo'
        ]);
    }

    public function cambiarEstado($id, $nuevoEstado) {
        $sql = "UPDATE roles SET estado = :estado WHERE idRol = :id";
        return $this->db->execute($sql, [
            'id' => $id,
            'estado' => $nuevoEstado
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM roles WHERE idRol = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    public function existeNombre($nombre, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as total FROM roles WHERE nombre_rol = :nombre AND idRol != :id";
            $result = $this->db->queryOne($sql, ['nombre' => $nombre, 'id' => $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM roles WHERE nombre_rol = :nombre";
            $result = $this->db->queryOne($sql, ['nombre' => $nombre]);
        }
        
        return $result['total'] > 0;
    }

    public function tieneUsuariosAsociados($id) {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE RolId = :id";
        $result = $this->db->queryOne($sql, ['id' => $id]);
        
        return $result['total'] > 0;
    }

    public function count() {
        $sql = "SELECT COUNT(*) as total FROM roles";
        $result = $this->db->queryOne($sql);
        return $result['total'];
    }
}