<?php
class Egreso{
    public $idEgreso;
    public $tipo_gasto_id;
    public $descripcion;
    public $monto;
    public $fecha_egreso;
    public $num_comprobante;
    public $comprobante;
    public $observaciones;
    public $usuario_registro_id;
    public $fecha_registro;
    public $fecha_actualizacion;
    
    // Propiedades de joins
    public $tipo_gasto_nombre;
    public $usuario_nombre;
    
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
            'idEgreso' => $this->idEgreso,
            'tipo_gasto_id' => $this->tipo_gasto_id,
            'descripcion' => $this->descripcion,
            'monto' => $this->monto,
            'fecha_egreso' => $this->fecha_egreso,
            'num_comprobante' => $this->num_comprobante,
            'comprobante' => $this->comprobante,
            'observaciones' => $this->observaciones,
            'usuario_registro_id' => $this->usuario_registro_id
        ];
    }
    
    public function getTipoGasto() {
        return $this->tipo_gasto_nombre ?? 'Sin categoría';
    }
}