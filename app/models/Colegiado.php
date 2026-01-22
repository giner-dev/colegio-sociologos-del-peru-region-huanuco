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
    
    // Campos para cese
    public $fecha_cese;
    public $motivo_cese;
    
    // NUEVOS: Campos para traslado
    public $fecha_traslado;
    public $motivo_traslado;
    public $colegio_destino;

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

    // Verificar si está inactivo por traslado
    public function isInactivoTraslado(){
        return $this->estado === 'inactivo_traslado';
    }

    // Incluir traslado en la lógica
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

    // Incluir el nuevo estado
    public function getEstadoTexto() {
        $estados = [
            'habilitado' => 'Habilitado',
            'inhabilitado' => 'Inhabilitado',
            'inactivo_cese' => 'Inactivo por Cese',
            'inactivo_traslado' => 'Inactivo por Traslado'
        ];
        return $estados[$this->estado] ?? 'Desconocido';
    }

    public function getEstadoBadgeClass() {
        $clases = [
            'habilitado' => 'badge-habilitado',
            'inhabilitado' => 'badge-inhabilitado',
            'inactivo_cese' => 'badge-inactivo-cese',
            'inactivo_traslado' => 'badge-inactivo-traslado'
        ];
        return $clases[$this->estado] ?? 'badge-secondary';
    }
}