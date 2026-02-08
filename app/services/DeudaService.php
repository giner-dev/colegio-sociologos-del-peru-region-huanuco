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

    public function obtenerDeudas($page = 1, $perPage = 25, $filtros = []) {
        $deudas = $this->deudaRepository->findAllPaginated($page, $perPage, $filtros);
        $total = $this->deudaRepository->countAll($filtros);
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

    public function obtenerPorId($id) {
        return $this->deudaRepository->findById($id);
    }

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

    public function registrarDeuda($datos) {
        $colegiado = $this->colegiadoRepository->findById($datos['colegiado_id']);

        if (!$colegiado->puedeGenerarDeudas()) {
            return [
                'success' => false, 
                'errors' => ['No se pueden registrar deudas para colegiados con estado: ' . $colegiado->getEstadoTexto()]
            ];
        }

        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        $estado = $this->determinarEstado($datos['fecha_vencimiento']);
        $esDeudaManual = !empty($datos['es_deuda_manual']) || empty($datos['concepto_id']);

        $conceptoRecurrente = null;
        if (!$esDeudaManual && !empty($datos['concepto_id'])) {
            $conceptoRecurrente = $this->deudaRepository->findConceptoById($datos['concepto_id']);

            if ($conceptoRecurrente && $conceptoRecurrente['es_recurrente'] == 1) {
                $existeProgramacion = $this->deudaRepository->existeProgramacionActiva(
                    $datos['colegiado_id'],
                    $datos['concepto_id']
                );

                if ($existeProgramacion) {
                    return [
                        'success' => false,
                        'errors' => [
                            'El colegiado ya tiene una programación activa para este concepto recurrente.'
                        ]
                    ];
                }
            }
        }

        try {
            $db = Database::getInstance();
            $db->beginTransaction();

            if ($conceptoRecurrente && $conceptoRecurrente['es_recurrente'] == 1) {
                $fechaInicio = !empty($datos['fecha_inicio_personalizada']) 
                    ? $datos['fecha_inicio_personalizada'] 
                    : $datos['fecha_vencimiento'];
                
                $resultado = $this->generarDeudasAtrasadas(
                    $datos['colegiado_id'],
                    $datos['concepto_id'],
                    $conceptoRecurrente,
                    $fechaInicio,
                    $datos['monto_esperado'],
                    $datos['usuario_generador_id'] ?? null,
                    $db
                );

                if (!$resultado['success']) {
                    $db->rollback();
                    return $resultado;
                }

                $db->commit();
                return [
                    'success' => true, 
                    'id' => $resultado['primera_deuda_id'],
                    'deudas_generadas' => $resultado['total_generadas']
                ];
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
                'origen' => 'manual',
                'usuario_generador_id' => $datos['usuario_generador_id'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null
            ];

            $deudaId = $this->deudaRepository->create($datosInsert);

            if (!$deudaId) {
                $db->rollback();
                return ['success' => false, 'errors' => ['Error al registrar la deuda']];
            }

            $this->actualizarEstadoColegiado($datos['colegiado_id']);
            $db->commit();

            logMessage("Deuda registrada (manual): ID $deudaId", 'info');
            return ['success' => true, 'id' => $deudaId];

        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollback();
            }
            logMessage("Error al registrar deuda: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error interno del sistema: ' . $e->getMessage()]];
        }
    }

    private function generarDeudasAtrasadas($colegiadoId, $conceptoId, $concepto, $fechaInicio, $monto, $usuarioId, $db) {
        $hoy = new DateTime();
        $fechaActual = new DateTime($fechaInicio);
        $deudasGeneradas = 0;
        $primeraDeudaId = null;
        $ultimaFechaGenerada = null;

        while ($fechaActual <= $hoy) {
            $fechaVenc = $fechaActual->format('Y-m-d');
            $estado = ($fechaVenc < $hoy->format('Y-m-d')) ? 'vencido' : 'pendiente';
            
            $periodo = $this->obtenerNombrePeriodo($fechaVenc, $concepto['frecuencia']);
            $descripcion = "{$concepto['nombre_completo']} - {$periodo}";

            $datosDeuda = [
                'colegiado_id' => $colegiadoId,
                'concepto_id' => $conceptoId,
                'concepto_manual' => null,
                'es_deuda_manual' => 0,
                'descripcion_deuda' => $descripcion,
                'monto_esperado' => $monto,
                'fecha_generacion' => date('Y-m-d'),
                'fecha_vencimiento' => $fechaVenc,
                'fecha_maxima_pago' => $fechaVenc,
                'estado' => $estado,
                'origen' => 'recurrente',
                'usuario_generador_id' => $usuarioId,
                'observaciones' => 'Generada automáticamente - deuda recurrente'
            ];

            $deudaId = $this->deudaRepository->create($datosDeuda);
            
            if (!$deudaId) {
                return ['success' => false, 'errors' => ['Error al generar deuda para periodo: ' . $periodo]];
            }

            if ($primeraDeudaId === null) {
                $primeraDeudaId = $deudaId;
            }

            $ultimaFechaGenerada = $fechaVenc;
            $deudasGeneradas++;
            logMessage("Deuda recurrente generada: ID $deudaId para periodo $periodo", 'info');

            $fechaActual = $this->incrementarFechaPorFrecuencia($fechaActual, $concepto['frecuencia']);
        }

        $proximaGeneracion = $fechaActual->format('Y-m-d');

        $datosProgramacion = [
            'colegiado_id' => $colegiadoId,
            'concepto_id' => $conceptoId,
            'monto' => $monto,
            'frecuencia' => $concepto['frecuencia'],
            'dia_vencimiento' => $concepto['dia_vencimiento'],
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => null,
            'ultima_generacion' => $ultimaFechaGenerada,
            'proxima_generacion' => $proximaGeneracion,
            'usuario_registro_id' => $usuarioId,
            'observaciones' => "Programación creada con {$deudasGeneradas} deudas iniciales desde {$fechaInicio}"
        ];

        $programacionId = $this->deudaRepository->crearProgramacion($datosProgramacion);

        if (!$programacionId) {
            return ['success' => false, 'errors' => ['Error al crear la programación']];
        }

        $this->actualizarEstadoColegiado($colegiadoId);

        return [
            'success' => true,
            'primera_deuda_id' => $primeraDeudaId,
            'total_generadas' => $deudasGeneradas
        ];
    }

    private function incrementarFechaPorFrecuencia(DateTime $fecha, $frecuencia) {
        $nuevaFecha = clone $fecha;
        
        switch($frecuencia) {
            case 'mensual':
                $nuevaFecha->modify('+1 month');
                break;
            case 'trimestral':
                $nuevaFecha->modify('+3 months');
                break;
            case 'semestral':
                $nuevaFecha->modify('+6 months');
                break;
            case 'anual':
                $nuevaFecha->modify('+1 year');
                break;
        }
        
        return $nuevaFecha;
    }

    private function obtenerNombrePeriodo($fecha, $frecuencia) {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        $dt = new DateTime($fecha);
        $mes = (int)$dt->format('m');
        $anio = $dt->format('Y');
        
        switch($frecuencia) {
            case 'mensual':
                return $meses[$mes] . ' ' . $anio;
            case 'trimestral':
                $trimestre = ceil($mes / 3);
                return "Q{$trimestre} {$anio}";
            case 'semestral':
                $semestre = ($mes <= 6) ? 1 : 2;
                return "Semestre {$semestre} {$anio}";
            case 'anual':
                return "Año {$anio}";
            default:
                return $meses[$mes] . ' ' . $anio;
        }
    }

    public function actualizarDeuda($id, $datos) {
        $deuda = $this->deudaRepository->findById($id);
        
        if (!$deuda) {
            return ['success' => false, 'errors' => ['Deuda no encontrada']];
        }
        
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
            $this->actualizarEstadoColegiado($deuda->colegiado_id);
            logMessage("Deuda cancelada: ID $id - Motivo: $motivo", 'warning');
            return ['success' => true, 'message' => 'Deuda cancelada correctamente'];
        }
        
        return ['success' => false, 'message' => 'Error al cancelar la deuda'];
    }

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
            $this->actualizarEstadoColegiado($deuda->colegiado_id);
            logMessage("Deuda eliminada: ID $id", 'warning');
            return ['success' => true, 'message' => 'Deuda eliminada correctamente'];
        }
        
        return ['success' => false, 'message' => 'Error al eliminar la deuda'];
    }

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

    public function obtenerResumen() {
        return $this->deudaRepository->getResumen();
    }

    public function actualizarVencidas() {
        return $this->deudaRepository->actualizarVencidas();
    }

    public function obtenerProximasAVencer($dias = 7) {
        return $this->deudaRepository->getProximasAVencer($dias);
    }

    private function actualizarEstadoColegiado($colegiadoId) {
        $tieneDeudas = $this->deudaRepository->tieneDeudasPendientes($colegiadoId);
        $colegiado = $this->colegiadoRepository->findById($colegiadoId);
        
        if ($colegiado) {
            $nuevoEstado = $tieneDeudas ? 'inhabilitado' : 'habilitado';
            $motivoAutomatico = $tieneDeudas 
                ? 'Inhabilitado automáticamente por deudas pendientes' 
                : 'Habilitado automáticamente por pago de deudas';
            
            if ($colegiado->estado !== $nuevoEstado) {
                $this->colegiadoRepository->cambiarEstado($colegiadoId, $nuevoEstado, $motivoAutomatico);
                $this->registrarCambioEstadoAutomatico($colegiadoId, $colegiado->estado, $nuevoEstado, $motivoAutomatico);
                logMessage("Estado de colegiado actualizado automáticamente: ID $colegiadoId -> $nuevoEstado", 'info');
            }
        }
    }

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

    private function determinarEstado($fechaVencimiento) {
        $hoy = date('Y-m-d');
        return ($fechaVencimiento < $hoy) ? 'vencido' : 'pendiente';
    }

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

        $esDeudaManual = !empty($datos['es_deuda_manual']) || empty($datos['concepto_id']);

        if ($esDeudaManual) {
            if (empty($datos['concepto_manual'])) {
                $errores[] = 'Debe ingresar una descripción para la deuda manual';
            }
        } else {
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

    public function obtenerConceptos() {
        return $this->deudaRepository->getConceptosActivos();
    }

    public function existeProgramacionActiva($colegiadoId, $conceptoId) {
        return $this->deudaRepository->existeProgramacionActiva($colegiadoId, $conceptoId);
    }

    public function puedeAgregarDeuda($colegiadoId) {
        $colegiado = $this->colegiadoRepository->findById($colegiadoId);
        return ($colegiado !== null);
    }
}