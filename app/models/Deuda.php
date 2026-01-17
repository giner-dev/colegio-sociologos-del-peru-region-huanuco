<?php
class Deuda{
    public $idDeuda;
    public $colegiado_id;
    public $concepto_id;
    public $concepto_manual;
    public $es_deuda_manual;
    public $descripcion_deuda;
    public $monto_esperado;
    public $monto_pagado;
    public $saldo_pendiente;
    public $fecha_generacion;
    public $fecha_vencimiento;
    public $fecha_maxima_pago;
    public $estado;
    public $origen;
    public $deuda_padre_id;
    public $usuario_generador_id;
    public $observaciones;
    public $fecha_registro;
    public $fecha_actualizacion;
    
    // Propiedades adicionales
    public $nombre_colegiado;
    public $numero_colegiatura;
    public $dni;
    public $nombres;
    public $apellido_paterno;
    public $apellido_materno;
    public $concepto_nombre;
    public $concepto_descripcion;
    public $monto_sugerido;
    public $es_recurrente;
    public $frecuencia;
    public $dia_vencimiento;

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
        
        // Si viene 'concepto' sin 'descripcion_deuda'
        if (isset($data['concepto']) && !isset($data['descripcion_deuda'])) {
            $this->descripcion_deuda = $data['concepto'];
        }
    }

    public function toArray() {
        return [
            'idDeuda' => $this->idDeuda,
            'colegiado_id' => $this->colegiado_id,
            'concepto_id' => $this->concepto_id,
            'descripcion_deuda' => $this->descripcion_deuda,
            'monto_esperado' => $this->monto_esperado,
            'monto_pagado' => $this->monto_pagado,
            'saldo_pendiente' => $this->getSaldoPendiente(),
            'fecha_generacion' => $this->fecha_generacion,
            'fecha_vencimiento' => $this->fecha_vencimiento,
            'fecha_maxima_pago' => $this->fecha_maxima_pago,
            'estado' => $this->estado,
            'origen' => $this->origen,
            'deuda_padre_id' => $this->deuda_padre_id,
            'observaciones' => $this->observaciones
        ];
    }

    // MÉTODOS PARA VERIFICAR ESTADO
    public function isPendiente() {
        return $this->estado === 'pendiente';
    }
    
    public function isVencida() {
        return $this->estado === 'vencido';
    }
    
    public function isPagada() {
        return $this->estado === 'pagado';
    }
    
    public function isParcial() {
        return $this->estado === 'parcial';
    }
    
    public function isCancelada() {
        return $this->estado === 'cancelado';
    }
    
    // MÉTODOS PARA ACCIONES
    public function puedeSerPagada() {
        return in_array($this->estado, ['pendiente', 'vencido', 'parcial']) 
               && $this->getSaldoPendiente() > 0;
    }
    
    public function puedeSeCancelada() {
        return !$this->isPagada() && !$this->isCancelada();
    }
    
    public function puedeSerEliminada() {
        return $this->isPendiente() && $this->monto_pagado == 0;
    }
    
    
    public function getNombreCompleto() {
        if (!empty($this->apellido_paterno) && !empty($this->nombres)) {
            return trim($this->apellido_paterno . ' ' . ($this->apellido_materno ?? '') . ', ' . $this->nombres);
        }
        return $this->nombre_colegiado ?? 'Sin nombre';
    }
    
    public function getDiasVencimiento() {
        if ($this->estado === 'pagado' || $this->estado === 'cancelado') {
            return null;
        }
        
        $hoy = new DateTime();
        $hoy->setTime(0, 0, 0);
        
        $vencimiento = new DateTime($this->fecha_vencimiento);
        $vencimiento->setTime(0, 0, 0);
        
        if ($hoy > $vencimiento) {
            $interval = $hoy->diff($vencimiento);
            return -$interval->days;
        } else {
            $interval = $vencimiento->diff($hoy);
            return $interval->days;
        }
    }
    
    public function getSaldoPendiente() {
        if ($this->saldo_pendiente !== null) {
            return $this->saldo_pendiente;
        }
        // Sino calcularlo
        return $this->monto_esperado - ($this->monto_pagado ?? 0);
    }
    
    public function getPorcentajePagado() {
        if ($this->monto_esperado == 0) {
            return 0;
        }
        return (($this->monto_pagado ?? 0) / $this->monto_esperado) * 100;
    }
    
    public function getEstadoTexto() {
        $estados = [
            'pendiente' => 'Pendiente',
            'parcial' => 'Pago Parcial',
            'pagado' => 'Pagado',
            'vencido' => 'Vencido',
            'cancelado' => 'Cancelado'
        ];
        
        return $estados[$this->estado] ?? 'Desconocido';
    }

    public function getNombreConcepto() {
        if ($this->es_deuda_manual) {
            return $this->concepto_manual ?? 'Deuda manual';
        }
        return $this->concepto_nombre ?? 'Sin concepto';
    }

    public function getDescripcionCompleta() {
        if ($this->es_deuda_manual) {
            return $this->descripcion_deuda ?? $this->concepto_manual ?? 'Deuda manual';
        }
        return $this->descripcion_deuda ?? $this->concepto_nombre ?? 'Sin descripción';
    }
    
    public function getOrigenTexto() {
        $origenes = [
            'manual' => 'Manual',
            'automatico' => 'Automático',
            'recurrente' => 'Recurrente'
        ];
        
        return $origenes[$this->origen] ?? 'Manual';
    }
    
    public function getEstadoBadgeClass() {
        $clases = [
            'pendiente' => 'bg-warning text-dark',
            'parcial' => 'bg-info',
            'pagado' => 'bg-success',
            'vencido' => 'bg-danger',
            'cancelado' => 'bg-secondary'
        ];
        
        return $clases[$this->estado] ?? 'bg-light text-dark';
    }
    
    public function getColorAlerta() {
        $dias = $this->getDiasVencimiento();
        
        if ($this->isVencida()) {
            return 'danger';
        } elseif ($dias !== null && $dias <= 7) {
            return 'warning';
        } else {
            return 'success';
        }
    }
}