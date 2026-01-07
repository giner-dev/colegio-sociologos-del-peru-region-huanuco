<?php
class Pago {
    public $idPagos;
    public $colegiados_id;
    public $concepto_id;
    public $concepto_texto;
    public $monto;
    public $fecha_pago;
    public $estado;
    public $metodo_pago_id;
    public $numero_comprobante;
    public $observaciones;
    public $usuario_registro_id;
    public $archivo_comprobante;
    public $fecha_registro;
    public $fecha_actualizacion;
    
    // Propiedades del colegiado
    public $nombres;
    public $apellido_paterno;
    public $apellido_materno;
    public $numero_colegiatura;
    public $dni;
    
    // Propiedades relacionadas
    public $concepto_nombre;
    public $metodo_nombre;
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
            'idPagos' => $this->idPagos,
            'colegiados_id' => $this->colegiados_id,
            'concepto_id' => $this->concepto_id,
            'concepto_texto' => $this->concepto_texto,
            'monto' => $this->monto,
            'fecha_pago' => $this->fecha_pago,
            'estado' => $this->estado,
            'metodo_pago_id' => $this->metodo_pago_id,
            'numero_comprobante' => $this->numero_comprobante,
            'observaciones' => $this->observaciones,
            'usuario_registro_id' => $this->usuario_registro_id,
            'archivo_comprobante' => $this->archivo_comprobante
        ];
    }
    
    public function getNombreColegiado() {
        return trim(($this->apellido_paterno ?? '') . ' ' . 
                   ($this->apellido_materno ?? '') . ', ' . 
                   ($this->nombres ?? ''));
    }

    public function isRegistrado() {
        return $this->estado === 'registrado';
    }
    
    public function isValidado() {
        return $this->estado === 'validado';
    }
    
    public function isAnulado() {
        return $this->estado === 'anulado';
    }
}