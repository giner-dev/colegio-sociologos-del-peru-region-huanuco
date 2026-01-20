<?php
class Pago {
    public $idPago;
    public $colegiado_id;
    public $deuda_id;
    public $monto;
    public $fecha_pago;
    public $fecha_registro_pago;
    public $metodo_pago_id;
    public $numero_comprobante;
    public $archivo_comprobante;
    public $estado;
    public $observaciones;
    public $usuario_registro_id;
    public $fecha_confirmacion;
    public $usuario_confirmacion_id;
    public $fecha_registro;
    public $fecha_actualizacion;
    public $es_pago_adelantado;
    public $periodo_adelantado;
    
    // Propiedades del colegiado (para joins)
    public $nombres;
    public $apellido_paterno;
    public $apellido_materno;
    public $numero_colegiatura;
    public $dni;
    
    // Propiedades de la deuda (para joins)
    public $deuda_descripcion;
    public $deuda_concepto;
    public $deuda_monto_esperado;
    
    // Propiedades relacionadas
    public $metodo_nombre;
    public $usuario_registro_nombre;
    public $usuario_confirmacion_nombre;
    
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
        
        // Mantener compatibilidad con nombres antiguos
        if (isset($data['idPagos']) && !isset($data['idPago'])) {
            $this->idPago = $data['idPagos'];
        }
        if (isset($data['colegiados_id']) && !isset($data['colegiado_id'])) {
            $this->colegiado_id = $data['colegiados_id'];
        }
        if (isset($data['concepto_nombre']) && !isset($data['deuda_concepto'])) {
            $this->deuda_concepto = $data['concepto_nombre'];
        }
    }
    
    public function toArray() {
        return [
            'idPago' => $this->idPago,
            'colegiado_id' => $this->colegiado_id,
            'deuda_id' => $this->deuda_id,
            'monto' => $this->monto,
            'fecha_pago' => $this->fecha_pago,
            'metodo_pago_id' => $this->metodo_pago_id,
            'numero_comprobante' => $this->numero_comprobante,
            'archivo_comprobante' => $this->archivo_comprobante,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'usuario_registro_id' => $this->usuario_registro_id
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
    
    public function isConfirmado() {
        return $this->estado === 'confirmado';
    }
    
    public function isAnulado() {
        return $this->estado === 'anulado';
    }
    
    public function getEstadoTexto() {
        $estados = [
            'registrado' => 'Registrado',
            'confirmado' => 'Confirmado',
            'anulado' => 'Anulado'
        ];
        
        return $estados[$this->estado] ?? 'Desconocido';
    }
    
    public function getEstadoClase() {
        $clases = [
            'registrado' => 'primary',
            'confirmado' => 'success',
            'anulado' => 'danger'
        ];
        
        return $clases[$this->estado] ?? 'secondary';
    }
    
    // Obtiene el concepto del pago (desde la deuda)
    public function getConcepto() {
        return $this->deuda_concepto ?? $this->deuda_descripcion ?? 'Pago';
    }
}