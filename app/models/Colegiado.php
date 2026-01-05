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
    public $estado_manual;
    public $foto;
    public $observaciones;
    public $fecha_registro;
    public $fecha_actualizacion;

    public function __construct($data = null){
        if(!empty($data)){
            $this->hydrate($data);
        }
    }

    // Llena las propiedades del objeto desde un array
    public function hydrate($data){
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    // Converte el objeto en array
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
            'estado_manual' => $this->estado_manual,
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

    // Calcula la edad del colegiado
    public function getEdad() {
        if (empty($this->fecha_nacimiento)) {
            return null;
        }
        
        $fechaNac = new DateTime($this->fecha_nacimiento);
        $hoy = new DateTime();
        $edad = $hoy->diff($fechaNac);
        
        return $edad->y;
    }

    // Calcula los años de colegiatura
    public function getAniosColegiatura() {
        if (empty($this->fecha_colegiatura)) {
            return null;
        }
        
        $fechaCol = new DateTime($this->fecha_colegiatura);
        $hoy = new DateTime();
        $diff = $hoy->diff($fechaCol);
        
        return $diff->y;
    }
}