<?php
require_once __DIR__ . '/../repositories/PagoRepository.php';
require_once __DIR__ . '/../repositories/ColegiadoRepository.php';
require_once __DIR__ . '/../repositories/DeudaRepository.php';

class PagoService {
    private $db;
    private $pagoRepository;
    private $colegiadoRepository;
    private $deudaRepository;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->pagoRepository = new PagoRepository();
        $this->colegiadoRepository = new ColegiadoRepository();
        $this->deudaRepository = new DeudaRepository();
    }

    // Obtiene pagos con paginación
    public function obtenerPagos($page = 1, $perPage = 25, $filtros = []) {
        $pagos = $this->pagoRepository->findAllPaginated($page, $perPage, $filtros);
        $total = $this->pagoRepository->countAll($filtros);
        
        $conceptos = $this->pagoRepository->getConceptosPago();
        $metodos = $this->pagoRepository->getMetodosPago();
        
        return [
            'pagos' => $pagos,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage),
            'conceptos' => $conceptos,
            'metodos' => $metodos
        ];
    }

    // Obtiene un pago por ID
    public function obtenerPorId($id) {
        return $this->pagoRepository->findById($id);
    }

    // 🔧 CORREGIDO: Registra un nuevo pago
    public function registrarPago($datos, $usuarioId) {
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }
        
        // Verificar que la deuda existe
        $deuda = $this->deudaRepository->findById($datos['deuda_id']);
        if (!$deuda) {
            return ['success' => false, 'errors' => ['La deuda no existe']];
        }
        
        // Verificar que el colegiado coincide
        if ($deuda->colegiado_id != $datos['colegiado_id']) {
            return ['success' => false, 'errors' => ['La deuda no pertenece al colegiado seleccionado']];
        }
        
        // Verificar que la deuda esté pendiente
        if ($deuda->isPagada() || $deuda->isCancelada()) {
            return ['success' => false, 'errors' => ['La deuda ya está pagada o cancelada']];
        }
        
        // Verificar que el monto no exceda el saldo pendiente
        $saldoPendiente = $deuda->getSaldoPendiente();
        if ($datos['monto'] > $saldoPendiente) {
            return ['success' => false, 'errors' => [
                'El monto excede el saldo pendiente de la deuda (S/ ' . 
                number_format($saldoPendiente, 2) . ')'
            ]];
        }
        
        // Verificar comprobante único si aplica
        if (!empty($datos['numero_comprobante'])) {
            $metodo = $this->pagoRepository->findMetodoById($datos['metodo_pago_id']);
            if ($metodo && $metodo['requiere_comprobante']) {
                $existeComprobante = $this->pagoRepository->existeComprobante(
                    $datos['numero_comprobante'], 
                    $datos['metodo_pago_id']
                );
                
                if ($existeComprobante) {
                    return ['success' => false, 'errors' => ['El número de comprobante ya existe para este método de pago']];
                }
            }
        }
        
        $this->db->beginTransaction();
        
        try {
            // ✅ 1. Registrar el pago (SIN fecha_registro_pago)
            $datosInsert = [
                'colegiado_id' => $datos['colegiado_id'],
                'deuda_id' => $datos['deuda_id'],
                'monto' => $datos['monto'],
                'fecha_pago' => $datos['fecha_pago'],
                'metodo_pago_id' => $datos['metodo_pago_id'],
                'numero_comprobante' => $datos['numero_comprobante'] ?? null,
                'archivo_comprobante' => $datos['archivo_comprobante'] ?? null,
                'estado' => 'registrado',
                'observaciones' => $datos['observaciones'] ?? null,
                'usuario_registro_id' => $usuarioId
            ];
            
            $pagoId = $this->pagoRepository->create($datosInsert);
            
            if (!$pagoId) {
                throw new Exception("Error al registrar el pago");
            }
            
            // 2. Crear detalle de aplicación del pago
            $detalleData = [
                'pago_id' => $pagoId,
                'deuda_id' => $datos['deuda_id'],
                'monto_aplicado' => $datos['monto'],
                'usuario_aplicacion_id' => $usuarioId
            ];
            
            $detalleId = $this->pagoRepository->crearDetalleAplicacion($detalleData);
            
            if (!$detalleId) {
                throw new Exception("Error al crear el detalle de aplicación");
            }
            
            // 3. Actualizar la deuda
            $nuevoMontoPagado = ($deuda->monto_pagado ?? 0) + $datos['monto'];
            $this->deudaRepository->actualizarMontoPagado($datos['deuda_id'], $nuevoMontoPagado);
            
            // 4. Si el pago cubre completamente la deuda, confirmar automáticamente
            if ($nuevoMontoPagado >= $deuda->monto_esperado) {
                $this->pagoRepository->confirmar($pagoId, $usuarioId);
            }
            
            // 5. Actualizar estado del colegiado
            $this->actualizarEstadoColegiado($datos['colegiado_id']);
            
            $this->db->commit();
            
            logMessage("Pago registrado: ID $pagoId - Deuda: {$datos['deuda_id']} - Monto: {$datos['monto']}", 'info');
            return ['success' => true, 'id' => $pagoId];
            
        } catch (Exception $e) {
            $this->db->rollback();
            logMessage("Error al registrar pago: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error interno: ' . $e->getMessage()]];
        }
    }

    // Confirma un pago
    public function confirmarPago($id, $usuarioId) {
        $pago = $this->pagoRepository->findById($id);
        
        if (!$pago) {
            return ['success' => false, 'message' => 'Pago no encontrado'];
        }
        
        if ($pago->isConfirmado()) {
            return ['success' => false, 'message' => 'El pago ya está confirmado'];
        }
        
        if ($pago->isAnulado()) {
            return ['success' => false, 'message' => 'No se puede confirmar un pago anulado'];
        }
        
        $resultado = $this->pagoRepository->confirmar($id, $usuarioId);
        
        if ($resultado) {
            logMessage("Pago confirmado: ID $id por usuario ID $usuarioId", 'info');
            return ['success' => true, 'message' => 'Pago confirmado correctamente'];
        }
        
        return ['success' => false, 'message' => 'Error al confirmar el pago'];
    }

    // Anula un pago (con reversión de la deuda)
    public function anularPago($id, $usuarioId) {
        $pago = $this->pagoRepository->findById($id);
        
        if (!$pago) {
            return ['success' => false, 'message' => 'Pago no encontrado'];
        }
        
        if ($pago->isAnulado()) {
            return ['success' => false, 'message' => 'El pago ya está anulado'];
        }
        
        $this->db->beginTransaction();
        
        try {
            // 1. Anular el pago
            $resultado = $this->pagoRepository->anular($id, $usuarioId);
            
            if (!$resultado) {
                throw new Exception("Error al anular el pago");
            }
            
            // 2. Revertir el monto pagado en la deuda
            $deuda = $this->deudaRepository->findById($pago->deuda_id);
            if ($deuda) {
                $nuevoMontoPagado = max(0, ($deuda->monto_pagado ?? 0) - $pago->monto);
                $this->deudaRepository->actualizarMontoPagado($pago->deuda_id, $nuevoMontoPagado);
            }
            
            // 3. Actualizar estado del colegiado
            $this->actualizarEstadoColegiado($pago->colegiado_id);
            
            $this->db->commit();
            
            logMessage("Pago anulado: ID $id por usuario ID $usuarioId", 'warning');
            return ['success' => true, 'message' => 'Pago anulado correctamente'];
            
        } catch (Exception $e) {
            $this->db->rollback();
            logMessage("Error al anular pago: " . $e->getMessage(), 'error');
            return ['success' => false, 'message' => 'Error interno del sistema al anular el pago'];
        }
    }

    // Obtiene resumen de ingresos
    public function obtenerResumen($fechaInicio, $fechaFin) {
        $resumen = $this->pagoRepository->getResumenIngresos($fechaInicio, $fechaFin);
        $porMetodo = $this->pagoRepository->getIngresosPorMetodo($fechaInicio, $fechaFin);
        $porConcepto = $this->pagoRepository->getIngresosPorConcepto($fechaInicio, $fechaFin);
        $totales = $this->pagoRepository->getTotalPorPeriodo($fechaInicio, $fechaFin);
        
        return [
            'resumen' => $resumen,
            'por_metodo' => $porMetodo,
            'por_concepto' => $porConcepto,
            'totales' => $totales
        ];
    }

    // Obtiene métodos y conceptos para formularios
    public function obtenerOpcionesPago() {
        return [
            'metodos' => $this->pagoRepository->getMetodosPago(),
            'conceptos' => $this->pagoRepository->getConceptosPago()
        ];
    }

    public function obtenerProgramacionesPorColegiado($colegiadoId) {
        return $this->pagoRepository->getProgramacionesActivas($colegiadoId);
    }

    // Valida datos del pago
    private function validarDatos($datos) {
        $errores = [];
        
        if (empty($datos['colegiado_id'])) {
            $errores[] = 'Debe seleccionar un colegiado';
        }
        
        if (empty($datos['deuda_id'])) {
            $errores[] = 'Debe seleccionar una deuda';
        }
        
        if (empty($datos['monto']) || $datos['monto'] <= 0) {
            $errores[] = 'El monto debe ser mayor a 0';
        }
        
        if (empty($datos['fecha_pago'])) {
            $errores[] = 'La fecha de pago es obligatoria';
        }
        
        if (empty($datos['metodo_pago_id'])) {
            $errores[] = 'Debe seleccionar un método de pago';
        }
        
        return $errores;
    }

    // Actualiza estado del colegiado según deudas
    private function actualizarEstadoColegiado($colegiadoId) {
        $tieneDeudas = $this->deudaRepository->tieneDeudasPendientes($colegiadoId);
        $colegiado = $this->colegiadoRepository->findById($colegiadoId);
        
        if ($colegiado) {
            $nuevoEstado = $tieneDeudas ? 'inhabilitado' : 'habilitado';
            $motivoAutomatico = $tieneDeudas 
                ? 'Inhabilitado automáticamente por deudas pendientes' 
                : 'Habilitado automáticamente al pagar todas las deudas';
            
            if ($colegiado->estado !== $nuevoEstado) {
                $this->colegiadoRepository->cambiarEstado($colegiadoId, $nuevoEstado, $motivoAutomatico);
                
                // Registrar en historial
                $this->registrarCambioEstadoAutomatico($colegiadoId, $colegiado->estado, $nuevoEstado, $motivoAutomatico);
                
                logMessage("Estado de colegiado actualizado automáticamente: ID $colegiadoId -> $nuevoEstado", 'info');
            }
        }
    }
    
    private function registrarCambioEstadoAutomatico($colegiadoId, $estadoAnterior, $estadoNuevo, $motivo) {
        $sql = "INSERT INTO historial_estados (colegiado_id, estado_anterior, estado_nuevo, motivo, tipo_cambio, usuario_id)
                VALUES (:colegiado_id, :estado_anterior, :estado_nuevo, :motivo, 'automatico', NULL)";
        
        try {
            $this->db->execute($sql, [
                'colegiado_id' => $colegiadoId,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $estadoNuevo,
                'motivo' => $motivo
            ]);
        } catch (Exception $e) {
            logMessage("Error al registrar historial de estado automático: " . $e->getMessage(), 'error');
        }
    }

    // Procesa archivo de comprobante
    public function subirComprobante($archivo) {
        return FileUploadManager::uploadComprobante($archivo, 'pago');
    }

    // Obtiene deudas pendientes de un colegiado
    public function obtenerDeudasPendientes($colegiadoId) {
        $deudas = $this->pagoRepository->getDeudasPendientes($colegiadoId);
        
        $resultado = [];
        foreach ($deudas as $deuda) {
            if (is_array($deuda)) {
                $resultado[] = [
                    'idDeuda' => $deuda['idDeuda'] ?? $deuda['idDeuda'],
                    'concepto_id' => $deuda['concepto_id'] ?? null,
                    'concepto_nombre' => $deuda['concepto_nombre'] ?? $deuda['nombre_completo'] ?? '',
                    'descripcion_deuda' => $deuda['descripcion_deuda'] ?? '',
                    'monto_esperado' => floatval($deuda['monto_esperado'] ?? 0),
                    'monto_pagado' => floatval($deuda['monto_pagado'] ?? 0),
                    'saldo_pendiente' => floatval($deuda['saldo_pendiente'] ?? 0),
                    'fecha_generacion' => $deuda['fecha_generacion'] ?? null,
                    'fecha_vencimiento' => $deuda['fecha_vencimiento'] ?? '',
                    'fecha_maxima_pago' => $deuda['fecha_maxima_pago'] ?? null,
                    'estado' => $deuda['estado'] ?? 'pendiente',
                    'origen' => $deuda['origen'] ?? 'manual'
                ];
            } elseif (is_object($deuda)) {
                $resultado[] = $deuda;
            }
        }
        
        return $resultado;
    }

    // Obtiene historial de pagos de un colegiado
    public function obtenerHistorialColegiado($colegiadoId) {
        return $this->pagoRepository->findByColegiado($colegiadoId, 100);
    }

    // GESTIÓN DE CONCEPTOS DE PAGO
    public function obtenerTodosConceptos() {
        return $this->pagoRepository->getAllConceptos();
    }
    
    public function obtenerConceptoPorId($id) {
        return $this->pagoRepository->findConceptoById($id);
    }
    
    public function crearConcepto($datos) {
        $errores = [];
        
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre del concepto es obligatorio';
        }
        
        if (!isset($datos['monto']) || $datos['monto'] < 0) {
            $errores[] = 'El monto sugerido debe ser mayor o igual a 0';
        }
        
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }
        
        $esRecurrente = isset($datos['es_recurrente']) && $datos['es_recurrente'] == '1' ? 1 : 0;
        
        $datosInsert = [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'monto' => $datos['monto'],
            'tipo' => $datos['tipo'] ?? 'otro',
            'requiere' => isset($datos['requiere_comprobante']) && $datos['requiere_comprobante'] == '1' ? 1 : 0,
            'es_recurrente' => $esRecurrente,
            'frecuencia' => $esRecurrente ? ($datos['frecuencia'] ?? null) : null,
            'dia_vencimiento' => $esRecurrente ? ($datos['dia_vencimiento'] ?? null) : null,
            'estado' => 'activo'
        ];
        
        $id = $this->pagoRepository->createConcepto($datosInsert);
        
        if ($id) {
            logMessage("Concepto de pago creado: ID $id - {$datos['nombre']}", 'info');
            return ['success' => true, 'id' => $id];
        }
        
        return ['success' => false, 'errors' => ['Error al crear el concepto']];
    }
    
    public function actualizarConcepto($id, $datos) {
        $concepto = $this->pagoRepository->findConceptoById($id);
        
        if (!$concepto) {
            return ['success' => false, 'errors' => ['Concepto no encontrado']];
        }
        
        $errores = [];
        
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre del concepto es obligatorio';
        }
        
        if (!isset($datos['monto']) || $datos['monto'] < 0) {
            $errores[] = 'El monto sugerido debe ser mayor o igual a 0';
        }
        
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }
        
        $esRecurrente = isset($datos['es_recurrente']) && $datos['es_recurrente'] == '1' ? 1 : 0;
        
        $datosUpdate = [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'monto' => $datos['monto'],
            'tipo' => $datos['tipo'] ?? 'otro',
            'requiere' => isset($datos['requiere_comprobante']) && $datos['requiere_comprobante'] == '1' ? 1 : 0,
            'es_recurrente' => $esRecurrente,
            'frecuencia' => $esRecurrente ? ($datos['frecuencia'] ?? null) : null,
            'dia_vencimiento' => $esRecurrente ? ($datos['dia_vencimiento'] ?? null) : null,
            'estado' => $datos['estado'] ?? 'activo'
        ];
        
        $resultado = $this->pagoRepository->updateConcepto($id, $datosUpdate);
        
        if ($resultado) {
            logMessage("Concepto de pago actualizado: ID $id", 'info');
            return ['success' => true];
        }
        
        return ['success' => false, 'errors' => ['Error al actualizar el concepto']];
    }
    
    public function eliminarConcepto($id) {
        $concepto = $this->pagoRepository->findConceptoById($id);
        
        if (!$concepto) {
            return ['success' => false, 'message' => 'Concepto no encontrado'];
        }
        
        $resultado = $this->pagoRepository->deleteConcepto($id);
        
        if ($resultado) {
            logMessage("Concepto de pago eliminado: ID $id", 'warning');
            return ['success' => true, 'message' => 'Concepto desactivado correctamente'];
        }
        
        return ['success' => false, 'message' => 'Error al eliminar el concepto'];
    }

    // GESTIÓN DE MÉTODOS DE PAGO
    public function obtenerTodosMetodos() {
        return $this->pagoRepository->getAllMetodos();
    }
    
    public function obtenerMetodoPorId($id) {
        return $this->pagoRepository->findMetodoById($id);
    }
    
    public function crearMetodo($datos) {
        if (empty($datos['nombre'])) {
            return ['success' => false, 'errors' => ['El nombre del método es obligatorio']];
        }
        
        $datosInsert = [
            'codigo' => $datos['codigo'] ?? strtoupper(substr($datos['nombre'], 0, 3)),
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'requiere_comprobante' => isset($datos['requiere_comprobante']) && $datos['requiere_comprobante'] == '1' ? 1 : 0,
            'datos_adicionales' => !empty($datos['datos_adicionales']) ? 
                $datos['datos_adicionales'] : null,
            'orden' => $datos['orden'] ?? 0,
            'activo' => 'activo'
        ];
        
        $id = $this->pagoRepository->createMetodo($datosInsert);
        
        if ($id) {
            logMessage("Método de pago creado: ID $id - {$datos['nombre']}", 'info');
            return ['success' => true, 'id' => $id];
        }
        
        return ['success' => false, 'errors' => ['Error al crear el método']];
    }
    
    public function actualizarMetodo($id, $datos) {
        $metodo = $this->pagoRepository->findMetodoById($id);
        
        if (!$metodo) {
            return ['success' => false, 'errors' => ['Método no encontrado']];
        }
        
        if (empty($datos['nombre'])) {
            return ['success' => false, 'errors' => ['El nombre del método es obligatorio']];
        }
        
        $datosUpdate = [
            'codigo' => $datos['codigo'] ?? $metodo['codigo'],
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'requiere_comprobante' => isset($datos['requiere_comprobante']) && $datos['requiere_comprobante'] == '1' ? 1 : 0,
            'datos_adicionales' => !empty($datos['datos_adicionales']) ? 
                $datos['datos_adicionales'] : $metodo['datos_adicionales'],
            'orden' => $datos['orden'] ?? $metodo['orden'],
            'activo' => $datos['activo'] ?? 'activo'
        ];
        
        $resultado = $this->pagoRepository->updateMetodo($id, $datosUpdate);
        
        if ($resultado) {
            logMessage("Método de pago actualizado: ID $id", 'info');
            return ['success' => true];
        }
        
        return ['success' => false, 'errors' => ['Error al actualizar el método']];
    }
    
    public function eliminarMetodo($id) {
        $metodo = $this->pagoRepository->findMetodoById($id);
        
        if (!$metodo) {
            return ['success' => false, 'message' => 'Método no encontrado'];
        }
        
        $resultado = $this->pagoRepository->deleteMetodo($id);
        
        if ($resultado) {
            logMessage("Método de pago eliminado: ID $id", 'warning');
            return ['success' => true, 'message' => 'Método desactivado correctamente'];
        }
        
        return ['success' => false, 'message' => 'Error al eliminar el método'];
    }


    private function calcularPeriodosAdelantados($programacion, $mesesAPagar) {
        $periodos = [];
        $fechaInicio = new DateTime($programacion['proxima_generacion'] ?? 'now');

        $incremento = 1;
        switch ($programacion['frecuencia']) {
            case 'mensual':
                $incremento = 1;
                break;
            case 'trimestral':
                $incremento = 3;
                break;
            case 'semestral':
                $incremento = 6;
                break;
            case 'anual':
                $incremento = 12;
                break;
        }
            
        $periodosNecesarios = ceil($mesesAPagar / $incremento);
            
        for ($i = 0; $i < $periodosNecesarios; $i++) {
            $fecha = clone $fechaInicio;
            $fecha->modify("+{$i} {$incremento} months");
            $periodos[] = $fecha->format('Y-m');
        }
            
        return $periodos;
    }

    public function registrarPagoAdelantado($datos, $usuarioId) {
        $errores = $this->validarDatosAdelantado($datos);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }
                
        $programacion = $this->pagoRepository->getProgramacionesActivas($datos['colegiado_id']);
                
        $programacionSeleccionada = null;
        foreach ($programacion as $prog) {
            if ($prog['idProgramacion'] == $datos['programacion_id']) {
                $programacionSeleccionada = $prog;
                break;
            }
        }
                
        if (!$programacionSeleccionada) {
            return ['success' => false, 'errors' => ['Programación no encontrada']];
        }
                
        $mesesAPagar = (int)$datos['meses_adelantado'];
        $montoPorPeriodo = (float)$programacionSeleccionada['monto'];
        $montoTotal = $montoPorPeriodo * $mesesAPagar;
                
        if ((float)$datos['monto'] < $montoTotal) {
            return ['success' => false, 'errors' => ['El monto debe cubrir al menos ' . $mesesAPagar . ' periodo(s). Total requerido: S/ ' . number_format($montoTotal, 2)]];
        }
                
        $periodos = $this->calcularPeriodosAdelantados($programacionSeleccionada, $mesesAPagar);
                
        $this->db->beginTransaction();
                
        try {
            $deudaIdReferencia = null;
                
            foreach ($periodos as $periodo) {
                $descripcion = $programacionSeleccionada['concepto_nombre'] . ' - Periodo ' . $periodo;
                
                $datosDeuda = [
                    'colegiado_id' => $datos['colegiado_id'],
                    'concepto_id' => $programacionSeleccionada['concepto_id'],
                    'concepto_manual' => null,
                    'es_deuda_manual' => 0,
                    'descripcion_deuda' => $descripcion,
                    'monto_esperado' => $montoPorPeriodo,
                    'fecha_generacion' => date('Y-m-d'),
                    'fecha_vencimiento' => $periodo . '-' . str_pad($programacionSeleccionada['dia_vencimiento'], 2, '0', STR_PAD_LEFT),
                    'fecha_maxima_pago' => null,
                    'estado' => 'pendiente',
                    'origen' => 'recurrente',
                    'usuario_generador_id' => $usuarioId,
                    'observaciones' => 'Generada por pago adelantado'
                ];
                
                $deudaId = $this->deudaRepository->create($datosDeuda);
                
                if (!$deudaId) {
                    throw new Exception("Error al generar deuda para periodo {$periodo}");
                }
                
                if ($deudaIdReferencia === null) {
                    $deudaIdReferencia = $deudaId;
                }
                
                $this->deudaRepository->actualizarMontoPagado($deudaId, $montoPorPeriodo);
            }
                
            $datosPago = [
                'colegiado_id' => $datos['colegiado_id'],
                'deuda_id' => $deudaIdReferencia,
                'monto' => $datos['monto'],
                'fecha_pago' => $datos['fecha_pago'],
                'metodo_pago_id' => $datos['metodo_pago_id'],
                'numero_comprobante' => $datos['numero_comprobante'] ?? null,
                'archivo_comprobante' => $datos['archivo_comprobante'] ?? null,
                'estado' => 'confirmado',
                'observaciones' => 'Pago adelantado por ' . $mesesAPagar . ' periodo(s): ' . implode(', ', $periodos),
                'usuario_registro_id' => $usuarioId,
                'es_pago_adelantado' => true,
                'periodo_adelantado' => implode(',', $periodos)
            ];
                
            $pagoId = $this->pagoRepository->create($datosPago);
                
            if (!$pagoId) {
                throw new Exception("Error al registrar el pago");
            }
                
            $sqlUpdateProgramacion = "UPDATE programacion_deudas 
                                      SET ultima_generacion = CURDATE(),
                                          proxima_generacion = DATE_ADD(CURDATE(), INTERVAL :meses MONTH)
                                      WHERE idProgramacion = :id";

            $this->db->execute($sqlUpdateProgramacion, [
                'meses' => $mesesAPagar,
                'id' => $programacionSeleccionada['idProgramacion']
            ]);
                
            $this->actualizarEstadoColegiado($datos['colegiado_id']);
                
            $this->db->commit();
                
            logMessage("Pago adelantado registrado: ID {$pagoId} - {$mesesAPagar} periodos - Monto: {$datos['monto']}", 'info');
                
            return ['success' => true, 'id' => $pagoId, 'periodos_pagados' => count($periodos)];
                
        } catch (Exception $e) {
            $this->db->rollback();
            logMessage("Error al registrar pago adelantado: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error: ' . $e->getMessage()]];
        }
    }

    private function validarDatosAdelantado($datos) {
        $errores = [];
                
        if (empty($datos['colegiado_id'])) {
            $errores[] = 'Debe seleccionar un colegiado';
        }
                
        if (empty($datos['programacion_id'])) {
            $errores[] = 'Debe seleccionar una programación recurrente';
        }
                
        if (empty($datos['meses_adelantado']) || $datos['meses_adelantado'] < 1) {
            $errores[] = 'Debe especificar cuántos meses desea pagar por adelantado';
        }
                
        if (empty($datos['monto']) || $datos['monto'] <= 0) {
            $errores[] = 'El monto debe ser mayor a 0';
        }
                
        if (empty($datos['fecha_pago'])) {
            $errores[] = 'La fecha de pago es obligatoria';
        }
                
        if (empty($datos['metodo_pago_id'])) {
            $errores[] = 'Debe seleccionar un método de pago';
        }
                
        return $errores;
    }
}