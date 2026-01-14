<?php
class Rol {
    public $idRol;
    public $nombre_rol;
    public $descripcion;
    public $permisos;
    public $estado;
    public $fecha_creacion;
    public $fecha_actualizacion;

    public function __construct($data = null) {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    public function hydrate(array $data): void {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }
        
            if ($key === 'permisos') {
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    $this->permisos = is_array($decoded) ? $decoded : [];
                } elseif (is_array($value)) {
                    $this->permisos = $value;
                } else {
                    $this->permisos = [];
                }
                continue;
            }
        
            $this->$key = $value;
        }
    }

    public function toArray() {
        return [
            'idRol' => $this->idRol,
            'nombre_rol' => $this->nombre_rol,
            'descripcion' => $this->descripcion,
            'permisos' => $this->permisos,
            'estado' => $this->estado,
            'fecha_creacion' => $this->fecha_creacion,
            'fecha_actualizacion' => $this->fecha_actualizacion
        ];
    }

    public function getPermisosJson() {
        return is_array($this->permisos) ? json_encode($this->permisos) : $this->permisos;
    }

    public function tienePermiso($modulo, $accion = 'ver') {
        if (!is_array($this->permisos)) {
            return false;
        }

        if (!isset($this->permisos[$modulo])) {
            return false;
        }

        if ($this->permisos[$modulo] === 'all') {
            return true;
        }

        if (is_array($this->permisos[$modulo])) {
            return in_array($accion, $this->permisos[$modulo]);
        }

        return false;
    }

    public function isActivo() {
        return $this->estado === 'activo';
    }
}