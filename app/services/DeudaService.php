<?php
require_once __DIR__ . '/../repositories/DeudaRepository.php';
require_once __DIR__ . '/../repositories/ColegiadoRepository.php';
require_once __DIR__ . '/../repositories/UsuarioRepository.php';

class DeudaService {
    private $deudaRepository;
    private $colegiadoRepository;
    
    public function __construct() {
        $this->deudaRepository = new DeudaRepository();
        $this->colegiadoRepository = new ColegiadoRepository();
    }

    // Obtiene deudas con paginación
    public function obtenerDeudas($page = 1, $perPage = 25, $filtros = []) {
        $deudas = $this->deudaRepository->findAllPaginated($page, $perPage, $filtros);
        $total = $this->deudaRepository->countAll($filtros);
        
        // Obtener conceptos para filtros
        $conceptos = $this->deudaRepository->getConceptosActivos();
        
        return [
            'deudas' => $deudas,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage),
            'conceptos' => $conceptos
        ];
    }

    // Obtiene una deuda por ID
    public function obtenerPorId($id) {
        return $this->deudaRepository->findById($id);
    }

    // Obtiene deudas de un colegiado
    public function obtenerPorColegiado($colegiadoId) {
        $deudas = $this->deudaRepository->findByColegiado($colegiadoId);
        $total = $this->deudaRepository->calcularDeudaTotal($colegiadoId);
        
        $colegiado = $this->colegiadoRepository->findById($colegiadoId);
        
        return [
            'deudas' => $deudas,
            'total' => $total,
            'colegiado' => $colegiado
        ];
    }

    // Registra una nueva deuda
    public function registrarDeuda($datos) {
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        $estado = $this->determinarEstado($datos['fecha_vencimiento']);
        $esDeudaManual = !empty($datos['es_deuda_manual']) || empty($datos['concepto_id']);

        // NUEVA LÓGICA: Verificar si es concepto recurrente
        $conceptoRecurrente = null;
        if (!$esDeudaManual && !empty($datos['concepto_id'])) {
            $conceptoRecurrente = $this->deudaRepository->findConceptoById($datos['concepto_id']);

            // Verificar si ya existe programación activa
            if ($conceptoRecurrente && $conceptoRecurrente['es_recurrente'] == 1) {
                $existeProgramacion = $this->deudaRepository->existeProgramacionActiva(
                    $datos['colegiado_id'],
                    $datos['concepto_id']
                );

                if ($existeProgramacion) {
                    return [
                        'success' => false,
                        'errors' => [
                            'El colegiado ya tiene una programación activa para este concepto recurrente. ' .
                            'No se puede crear una deuda duplicada.'
                        ]
                    ];
                }
            }
        }

        $datosInsert = [
            'colegiado_id' => $datos['colegiado_id'],
            'concepto_id' => $esDeudaManual ? null : $datos['concepto_id'],
            'concepto_manual' => $esDeudaManual ? $datos['concepto_manual'] : null,
            'es_deuda_manual' => $esDeudaManual ? 1 : 0,
            'descripcion_deuda' => $datos['descripcion_deuda'] ?? ($esDeudaManual ? $datos['concepto_manual'] : null),
            'monto_esperado' => $datos['monto_esperado'],
            'fecha_generacion' => date('Y-m-d'),
            'fecha_vencimiento' => $datos['fecha_vencimiento'],
            'fecha_maxima_pago' => !empty($datos['fecha_maxima_pago']) 
                ? $datos['fecha_maxima_pago'] 
                : $datos['fecha_vencimiento'],
            'estado' => $estado,
            'origen' => $conceptoRecurrente && $conceptoRecurrente['es_recurrente'] == 1 ? 'recurrente' : 'manual',
            'usuario_generador_id' => $datos['usuario_generador_id'] ?? null,
            'observaciones' => $datos['observaciones'] ?? null
        ];

        try {
            $db = Database::getInstance();
            $db->beginTransaction();

            $deudaId = $this->deudaRepository->create($datosInsert);

            if (!$deudaId) {
                $db->rollback();
                return ['success' => false, 'errors' => ['Error al registrar la deuda']];
            }

            // NUEVA LÓGICA: Si es concepto recurrente, crear programación
            if ($conceptoRecurrente && $conceptoRecurrente['es_recurrente'] == 1) {
                $proximaGeneracion = $this->calcularProximaFechaRecurrente(
                    $datos['fecha_vencimiento'],
                    $conceptoRecurrente['frecuencia']
                );

                $datosProgramacion = [
                    'colegiado_id' => $datos['colegiado_id'],
                    'concepto_id' => $datos['concepto_id'],
                    'monto' => $datos['monto_esperado'],
                    'frecuencia' => $conceptoRecurrente['frecuencia'],
                    'dia_vencimiento' => $conceptoRecurrente['dia_vencimiento'],
                    'fecha_inicio' => $datos['fecha_vencimiento'],
                    'fecha_fin' => null,
                    'ultima_generacion' => date('Y-m-d'),
                    'proxima_generacion' => $proximaGeneracion,
                    'usuario_registro_id' => $datos['usuario_generador_id'] ?? null,
                    'observaciones' => 'Programación creada automáticamente al registrar deuda recurrente'
                ];

                $programacionId = $this->deudaRepository->crearProgramacion($datosProgramacion);

                if (!$programacionId) {
                    $db->rollback();
                    return ['success' => false, 'errors' => ['Error al crear la programación de deuda recurrente']];
                }

                logMessage("Programación recurrente creada: ID $programacionId para deuda $deudaId", 'info');
            }

            $this->actualizarEstadoColegiado($datos['colegiado_id']);
        
            $db->commit();

            $tipoDeuda = $esDeudaManual ? 'manual libre' : 
                ($conceptoRecurrente && $conceptoRecurrente['es_recurrente'] == 1 ? 'recurrente' : 'con concepto');

            logMessage("Deuda registrada ($tipoDeuda): ID $deudaId - Colegiado {$datos['colegiado_id']} - Monto {$datos['monto_esperado']}", 'info');

            return ['success' => true, 'id' => $deudaId];

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollback();
            }
            logMessage("Error al registrar deuda: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error interno del sistema: ' . $e->getMessage()]];
        }
    }

    // Actualiza una deuda
    public function actualizarDeuda($id, $datos) {
        $deuda = $this->deudaRepository->findById($id);
        
        if (!$deuda) {
            return ['success' => false, 'errors' => ['Deuda no encontrada']];
        }
        
        // No permitir modificar deudas pagadas o canceladas
        if ($deuda->isPagada() || $deuda->isCancelada()) {
            return ['success' => false, 'errors' => ['No se puede modificar una deuda pagada o cancelada']];
        }
        
        $errores = $this->validarDatos($datos, true);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }
        
        $datosUpdate = [
            'concepto_id' => $datos['concepto_id'],
            'descripcion_deuda' => $datos['descripcion_deuda'],
            'monto_esperado' => $datos['monto_esperado'],
            'fecha_vencimiento' => $datos['fecha_vencimiento'],
            'fecha_maxima_pago' => $datos['fecha_maxima_pago'] ?? $datos['fecha_vencimiento'],
            'estado' => $datos['estado'] ?? $this->determinarEstado($datos['fecha_vencimiento']),
            'observaciones' => $datos['observaciones'] ?? $deuda->observaciones
        ];
        
        try {
            $resultado = $this->deudaRepository->update($id, $datosUpdate);
            
            if ($resultado) {
                // Actualizar estado del colegiado
                $this->actualizarEstadoColegiado($deuda->colegiado_id);
                
                logMessage("Deuda actualizada: ID $id", 'info');
                return ['success' => true];
            }
            
            return ['success' => false, 'errors' => ['Error al actualizar la deuda']];
            
        } catch (Exception $e) {
            logMessage("Error al actualizar deuda: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error interno del sistema']];
        }
    }

    // Aplica un pago a una deuda
    public function aplicarPago($deudaId, $montoPago, $pagoId = null) {
        $deuda = $this->deudaRepository->findById($deudaId);
        
        if (!$deuda) {
            return ['success' => false, 'message' => 'Deuda no encontrada'];
        }
        
        if ($deuda->isPagada()) {
            return ['success' => false, 'message' => 'La deuda ya está completamente pagada'];
        }
        
        if ($deuda->isCancelada()) {
            return ['success' => false, 'message' => 'La deuda está cancelada'];
        }
        
        $nuevoMontoPagado = ($deuda->monto_pagado ?? 0) + $montoPago;
        
        if ($nuevoMontoPagado > $deuda->monto_esperado) {
            return ['success' => false, 'message' => 'El monto excede el total de la deuda'];
        }
        
        try {
            $resultado = $this->deudaRepository->actualizarMontoPagado($deudaId, $nuevoMontoPagado);
            
            if ($resultado) {
                // Actualizar estado del colegiado
                $this->actualizarEstadoColegiado($deuda->colegiado_id);
                
                $nuevoEstado = $nuevoMontoPagado >= $deuda->monto_esperado ? 'pagado' : 'parcial';
                
                logMessage("Pago aplicado a deuda: ID $deudaId - Monto: $montoPago - Nuevo estado: $nuevoEstado", 'info');
                return [
                    'success' => true, 
                    'message' => 'Pago aplicado correctamente',
                    'nuevo_saldo' => $deuda->monto_esperado - $nuevoMontoPagado,
                    'estado' => $nuevoEstado
                ];
            }
            
            return ['success' => false, 'message' => 'Error al aplicar el pago'];
            
        } catch (Exception $e) {
            logMessage("Error al aplicar pago: " . $e->getMessage(), 'error');
            return ['success' => false, 'message' => 'Error interno del sistema'];
        }
    }

    // Cancela una deuda
    public function cancelarDeuda($id, $motivo) {
        $deuda = $this->deudaRepository->findById($id);
        
        if (!$deuda) {
            return ['success' => false, 'message' => 'Deuda no encontrada'];
        }
        
        if ($deuda->isPagada()) {
            return ['success' => false, 'message' => 'No se puede cancelar una deuda pagada'];
        }
        
        if ($deuda->isCancelada()) {
            return ['success' => false, 'message' => 'La deuda ya está cancelada'];
        }
        
        $resultado = $this->deudaRepository->cancelar($id, $motivo);
        
        if ($resultado) {
            // Actualizar estado del colegiado
            $this->actualizarEstadoColegiado($deuda->colegiado_id);
            
            logMessage("Deuda cancelada: ID $id - Motivo: $motivo", 'warning');
            return ['success' => true, 'message' => 'Deuda cancelada correctamente'];
        }
        
        return ['success' => false, 'message' => 'Error al cancelar la deuda'];
    }

    // Elimina una deuda
    public function eliminarDeuda($id) {
        $deuda = $this->deudaRepository->findById($id);
        
        if (!$deuda) {
            return ['success' => false, 'message' => 'Deuda no encontrada'];
        }
        
        if ($deuda->isPagada() || $deuda->isCancelada()) {
            return ['success' => false, 'message' => 'No se puede eliminar una deuda pagada o cancelada'];
        }
        
        if (($deuda->monto_pagado ?? 0) > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar una deuda con pagos parciales'];
        }
        
        $resultado = $this->deudaRepository->delete($id);
        
        if ($resultado) {
            // Actualizar estado del colegiado
            $this->actualizarEstadoColegiado($deuda->colegiado_id);
            
            logMessage("Deuda eliminada: ID $id", 'warning');
            return ['success' => true, 'message' => 'Deuda eliminada correctamente'];
        }
        
        return ['success' => false, 'message' => 'Error al eliminar la deuda'];
    }

    // Obtiene listado de morosos con paginación
    public function obtenerMorosos($page = 1, $perPage = 25) {
        $morosos = $this->deudaRepository->getMorosos($page, $perPage);
        $total = $this->deudaRepository->countMorosos();
        
        return [
            'morosos' => $morosos,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    // Obtiene resumen de deudas
    public function obtenerResumen() {
        return $this->deudaRepository->getResumen();
    }

    // Actualiza deudas vencidas
    public function actualizarVencidas() {
        return $this->deudaRepository->actualizarVencidas();
    }

    // Obtiene deudas próximas a vencer
    public function obtenerProximasAVencer($dias = 7) {
        return $this->deudaRepository->getProximasAVencer($dias);
    }

    // Actualiza estado del colegiado según deudas (IMPORTANTE PARA FLUJO AUTOMÁTICO)
    private function actualizarEstadoColegiado($colegiadoId) {
        $tieneDeudas = $this->deudaRepository->tieneDeudasPendientes($colegiadoId);
        $colegiado = $this->colegiadoRepository->findById($colegiadoId);
        
        if ($colegiado) {
            $nuevoEstado = $tieneDeudas ? 'inhabilitado' : 'habilitado';
            $motivoAutomatico = $tieneDeudas 
                ? 'Inhabilitado automáticamente por deudas pendientes' 
                : 'Habilitado automáticamente por pago de deudas';
            
            // Solo cambiar si es diferente al estado actual
            if ($colegiado->estado !== $nuevoEstado) {
                $this->colegiadoRepository->cambiarEstado($colegiadoId, $nuevoEstado, $motivoAutomatico);
                
                // Registrar en historial de estados
                $this->registrarCambioEstadoAutomatico($colegiadoId, $colegiado->estado, $nuevoEstado, $motivoAutomatico);
                
                logMessage("Estado de colegiado actualizado automáticamente: ID $colegiadoId -> $nuevoEstado", 'info');
            }
        }
    }

    private function calcularProximaFechaRecurrente($fechaBase, $frecuencia) {
        $fecha = new DateTime($fechaBase);

        switch($frecuencia) {
            case 'mensual':
                $fecha->modify('+1 month');
                break;
            case 'trimestral':
                $fecha->modify('+3 months');
                break;
            case 'semestral':
                $fecha->modify('+6 months');
                break;
            case 'anual':
                $fecha->modify('+1 year');
                break;
        }
            
        return $fecha->format('Y-m-d');
    }
    
    // Registrar cambio de estado automático en historial
    private function registrarCambioEstadoAutomatico($colegiadoId, $estadoAnterior, $estadoNuevo, $motivo) {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO historial_estados (colegiado_id, estado_anterior, estado_nuevo, motivo, tipo_cambio, usuario_id)
                VALUES (:colegiado_id, :estado_anterior, :estado_nuevo, :motivo, 'automatico', NULL)";
        
        try {
            $db->execute($sql, [
                'colegiado_id' => $colegiadoId,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoNuevo,
                'motivo' => $motivo
            ]);
        } catch (Exception $e) {
            logMessage("Error al registrar historial de estado automático: " . $e->getMessage(), 'error');
        }
    }

    // Determina el estado de una deuda según la fecha
    private function determinarEstado($fechaVencimiento) {
        $hoy = date('Y-m-d');
        
        if ($fechaVencimiento < $hoy) {
            return 'vencido';
        }
        
        return 'pendiente';
    }

    // Valida datos de deuda
    private function validarDatos($datos, $esActualizacion = false) {
        $errores = [];
        
        if (!$esActualizacion && empty($datos['colegiado_id'])) {
            $errores[] = 'Debe seleccionar un colegiado';
        } elseif (!$esActualizacion) {
            $colegiado = $this->colegiadoRepository->findById($datos['colegiado_id']);
            if (!$colegiado) {
                $errores[] = 'El colegiado seleccionado no existe';
            }
        }

        // VALIDACIÓN PARA DEUDAS MANUALES
        $esDeudaManual = !empty($datos['es_deuda_manual']) || empty($datos['concepto_id']);

        if ($esDeudaManual) {
            // Si es deuda manual, validar concepto_manual
            if (empty($datos['concepto_manual'])) {
                $errores[] = 'Debe ingresar una descripción para la deuda manual';
            }
        } else {
            // Si no es manual, validar concepto_id
            if (empty($datos['concepto_id'])) {
                $errores[] = 'Debe seleccionar un concepto o crear una deuda manual';
            } else {
                $concepto = $this->deudaRepository->findConceptoById($datos['concepto_id']);
                if (!$concepto) {
                    $errores[] = 'El concepto seleccionado no existe';
                }
            }
        }

        if (empty($datos['monto_esperado']) || $datos['monto_esperado'] <= 0) {
            $errores[] = 'El monto debe ser mayor a 0';
        }

        if (empty($datos['fecha_vencimiento'])) {
            $errores[] = 'La fecha de vencimiento es obligatoria';
        }

        return $errores;
    }

    // Obtiene conceptos para formulario
    public function obtenerConceptos() {
        return $this->deudaRepository->getConceptosActivos();
    }

    public function existeProgramacionActiva($colegiadoId, $conceptoId) {
        return $this->deudaRepository->existeProgramacionActiva($colegiadoId, $conceptoId);
    }

    // Verifica si un colegiado puede recibir nuevas deudas
    public function puedeAgregarDeuda($colegiadoId) {
        $colegiado = $this->colegiadoRepository->findById($colegiadoId);
        
        if (!$colegiado) {
            return false;
        }
        
        // Puede agregar deuda si el colegiado existe
        return true;
    }
}