<?php
class Colegiado{
    public $idColegiados;
    public $numero_colegiatura;
    public $dni;
    public $nombres;
    public $apellido_paterno;
    public $apellido_materno;
    public $fecha_colegiatura;
    public $telefono;
    public $correo;
    public $direccion;
    public $fecha_nacimiento;
    public $estado;
    public $foto;
    public $observaciones;
    public $fecha_registro;
    public $fecha_actualizacion;
    public $fecha_cese;
    public $motivo_cese;

    public function __construct($data = null){
        if(!empty($data)){
            $this->hydrate($data);
        }
    }

    public function hydrate($data){
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function toArray(){
        return[
            'idColegiados' => $this->idColegiados,
            'numero_colegiatura' => $this->numero_colegiatura,
            'dni' => $this->dni,
            'nombres' => $this->nombres,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'fecha_colegiatura' => $this->fecha_colegiatura,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'direccion' => $this->direccion,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'estado' => $this->estado,
            'foto' => $this->foto,
            'observaciones' => $this->observaciones
        ];
    }

    public function getNombreCompleto(){
        $nombre = ($this->apellido_paterno ?? '') . ' ' . 
                  ($this->apellido_materno ?? '') . ', ' . 
                  ($this->nombres ?? '');
        return trim($nombre, ' ,');
    }

    public function isHabilitado(){
        return $this->estado === 'habilitado';
    }

    public function isInhabilitado(){
        return $this->estado === 'inhabilitado';
    }

    public function isInactivoCese(){
        return $this->estado === 'inactivo_cese';
    }

    // Verifica si el colegiado está activo (puede generar deudas)
    public function puedeGenerarDeudas(){
        return $this->estado === 'habilitado' || $this->estado === 'inhabilitado';
    }

    public function getEdad() {
        if (empty($this->fecha_nacimiento)) {
            return null;
        }
        
        $fechaNac = new DateTime($this->fecha_nacimiento);
        $hoy = new DateTime();
        $edad = $hoy->diff($fechaNac);
        
        return $edad->y;
    }

    public function getAniosColegiatura() {
        if (empty($this->fecha_colegiatura)) {
            return null;
        }
        
        $fechaCol = new DateTime($this->fecha_colegiatura);
        $hoy = new DateTime();
        $diff = $hoy->diff($fechaCol);
        
        return $diff->y;
    }

    // Retorna el nombre amigable del estado
    public function getEstadoTexto() {
        $estados = [
            'habilitado' => 'Habilitado',
            'inhabilitado' => 'Inhabilitado',
            'inactivo_cese' => 'Inactivo por Cese'
        ];
        return $estados[$this->estado] ?? 'Desconocido';
    }

    public function getEstadoBadgeClass() {
        $clases = [
            'habilitado' => 'badge-habilitado',
            'inhabilitado' => 'badge-inhabilitado',
            'inactivo_cese' => 'badge-inactivo-cese'
        ];
        return $clases[$this->estado] ?? 'badge-secondary';
    }
}