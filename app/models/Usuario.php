<?php
class Usuario {
    
    public $idUsuario;
    public $RolId;
    public $nombre_usuario;
    public $contrasenia;
    public $nombres;
    public $apellidos;
    public $correo;
    public $telefono;
    public $estado;
    public $fecha_creacion;
    public $fecha_ultimo_acceso;
    public $fecha_actualizacion;

    public $nombre_rol;
    
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }
    
    public function hydrate($data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    public function toArray() {
        return [
            'idUsuario' => $this->idUsuario,
            'RolId' => $this->RolId,
            'nombre_usuario' => $this->nombre_usuario,
            'contrasenia' => $this->contrasenia,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'correo' => $this->correo,
            'telefono' => $this->telefono,
            'estado' => $this->estado
        ];
    }
    
    public function getNombreCompleto() {
        return trim($this->nombres . ' ' . $this->apellidos);
    }
    
    public function isActivo() {
        return $this->estado === 'activo';
    }
    public function verificarPassword($password) {
        return password_verify($password, $this->contrasenia);
    }
    
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}