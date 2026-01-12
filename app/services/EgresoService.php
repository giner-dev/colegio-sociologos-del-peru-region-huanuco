<?php
require_once __DIR__ . '/../repositories/EgresoRepository.php';

class EgresoService {
    private $db;
    private $egresoRepository;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->egresoRepository = new EgresoRepository();
    }

    public function obtenerEgresos($page = 1, $perPage = 25, $filtros = []) {
        $egresos = $this->egresoRepository->findAllPaginated($page, $perPage, $filtros);
        $total = $this->egresoRepository->countAll($filtros);
        $tiposGasto = $this->egresoRepository->getTiposGasto();
        
        return [
            'egresos' => $egresos,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage),
            'tiposGasto' => $tiposGasto
        ];
    }

    public function obtenerPorId($id) {
        return $this->egresoRepository->findById($id);
    }

    public function registrarEgreso($datos, $usuarioId) {
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }
        
        $datosInsert = [
            'tipo_gasto_id' => $datos['tipo_gasto_id'] ?? null,
            'descripcion' => $datos['descripcion'],
            'monto' => $datos['monto'],
            'fecha_egreso' => $datos['fecha_egreso'],
            'num_comprobante' => $datos['num_comprobante'] ?? null,
            'comprobante' => $datos['comprobante'] ?? null,
            'observaciones' => $datos['observaciones'] ?? null,
            'usuario_registro_id' => $usuarioId
        ];
        
        try {
            $id = $this->egresoRepository->create($datosInsert);
            
            if ($id) {
                logMessage("Egreso registrado: ID $id - Monto: {$datos['monto']}", 'info');
                return ['success' => true, 'id' => $id];
            }
            
            return ['success' => false, 'errors' => ['Error al registrar el egreso']];
            
        } catch (Exception $e) {
            logMessage("Error al registrar egreso: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error interno: ' . $e->getMessage()]];
        }
    }

    public function actualizarEgreso($id, $datos) {
        $egreso = $this->egresoRepository->findById($id);
        
        if (!$egreso) {
            return ['success' => false, 'errors' => ['Egreso no encontrado']];
        }
        
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }
        
        $datosUpdate = [
            'tipo_gasto_id' => $datos['tipo_gasto_id'] ?? null,
            'descripcion' => $datos['descripcion'],
            'monto' => $datos['monto'],
            'fecha_egreso' => $datos['fecha_egreso'],
            'num_comprobante' => $datos['num_comprobante'] ?? $egreso->num_comprobante,
            'comprobante' => $datos['comprobante'] ?? $egreso->comprobante,
            'observaciones' => $datos['observaciones'] ?? null
        ];
        
        try {
            $resultado = $this->egresoRepository->update($id, $datosUpdate);
            
            if ($resultado) {
                logMessage("Egreso actualizado: ID $id", 'info');
                return ['success' => true];
            }
            
            return ['success' => false, 'errors' => ['Error al actualizar el egreso']];
            
        } catch (Exception $e) {
            logMessage("Error al actualizar egreso: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error interno: ' . $e->getMessage()]];
        }
    }

    public function eliminarEgreso($id) {
        $egreso = $this->egresoRepository->findById($id);
        
        if (!$egreso) {
            return ['success' => false, 'message' => 'Egreso no encontrado'];
        }
        
        try {
            $resultado = $this->egresoRepository->delete($id);
            
            if ($resultado) {
                logMessage("Egreso eliminado: ID $id", 'warning');
                return ['success' => true, 'message' => 'Egreso eliminado correctamente'];
            }
            
            return ['success' => false, 'message' => 'Error al eliminar el egreso'];
            
        } catch (Exception $e) {
            logMessage("Error al eliminar egreso: " . $e->getMessage(), 'error');
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

    public function subirComprobante($archivo) {
        return FileUploadManager::uploadComprobante($archivo, 'egreso');
    }

    public function obtenerResumen($fechaInicio, $fechaFin) {
        $resumen = $this->egresoRepository->getResumenPorPeriodo($fechaInicio, $fechaFin);
        $porTipo = $this->egresoRepository->getEgresosPorTipo($fechaInicio, $fechaFin);
        
        return [
            'resumen' => $resumen,
            'por_tipo' => $porTipo
        ];
    }

    public function obtenerTiposGasto() {
        return $this->egresoRepository->getTiposGasto();
    }

    private function validarDatos($datos) {
        $errores = [];
        
        if (empty($datos['descripcion'])) {
            $errores[] = 'La descripción es obligatoria';
        }
        
        if (empty($datos['monto']) || $datos['monto'] <= 0) {
            $errores[] = 'El monto debe ser mayor a 0';
        }
        
        if (empty($datos['fecha_egreso'])) {
            $errores[] = 'La fecha del egreso es obligatoria';
        }
        
        return $errores;
    }




    // GESTIÓN DE TIPOS DE GASTO
    public function obtenerTodosTiposGasto() {
        return $this->egresoRepository->getAllTiposGasto();
    }

    public function obtenerTipoGastoPorId($id) {
        return $this->egresoRepository->findTipoGastoById($id);
    }

    public function crearTipoGasto($datos) {
        $errores = [];

        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre del tipo de gasto es obligatorio';
        }

        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        $datosInsert = [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'codigo' => $datos['codigo'] ?? strtoupper(substr($datos['nombre'], 0, 3)),
            'estado' => 'activo'
        ];

        try {
            $id = $this->egresoRepository->createTipoGasto($datosInsert);

            if ($id) {
                logMessage("Tipo de gasto creado: ID $id - {$datos['nombre']}", 'info');
                return ['success' => true, 'id' => $id];
            }

            return ['success' => false, 'errors' => ['Error al crear el tipo de gasto']];

        } catch (Exception $e) {
            logMessage("Error al crear tipo de gasto: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error interno: ' . $e->getMessage()]];
        }
    }

    public function actualizarTipoGasto($id, $datos) {
        $tipoGasto = $this->egresoRepository->findTipoGastoById($id);

        if (!$tipoGasto) {
            return ['success' => false, 'errors' => ['Tipo de gasto no encontrado']];
        }

        $errores = [];

        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre del tipo de gasto es obligatorio';
        }

        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        $datosUpdate = [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'codigo' => $datos['codigo'] ?? $tipoGasto['codigo'],
            'estado' => $datos['estado'] ?? 'activo'
        ];

        try {
            $resultado = $this->egresoRepository->updateTipoGasto($id, $datosUpdate);

            if ($resultado) {
                logMessage("Tipo de gasto actualizado: ID $id", 'info');
                return ['success' => true];
            }

            return ['success' => false, 'errors' => ['Error al actualizar el tipo de gasto']];

        } catch (Exception $e) {
            logMessage("Error al actualizar tipo de gasto: " . $e->getMessage(), 'error');
            return ['success' => false, 'errors' => ['Error interno: ' . $e->getMessage()]];
        }
    }

    public function eliminarTipoGasto($id) {
        $tipoGasto = $this->egresoRepository->findTipoGastoById($id);

        if (!$tipoGasto) {
            return ['success' => false, 'message' => 'Tipo de gasto no encontrado'];
        }

        if ($this->egresoRepository->tipoGastoTieneEgresos($id)) {
            return ['success' => false, 'message' => 'No se puede eliminar. Hay egresos asociados a este tipo de gasto'];
        }

        try {
            $resultado = $this->egresoRepository->deleteTipoGasto($id);

            if ($resultado) {
                logMessage("Tipo de gasto eliminado: ID $id", 'warning');
                return ['success' => true, 'message' => 'Tipo de gasto desactivado correctamente'];
            }

            return ['success' => false, 'message' => 'Error al eliminar el tipo de gasto'];

        } catch (Exception $e) {
            logMessage("Error al eliminar tipo de gasto: " . $e->getMessage(), 'error');
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }
}