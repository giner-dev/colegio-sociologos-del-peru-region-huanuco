<?php
require_once __DIR__ . '/../repositories/PagoRepository.php';
require_once __DIR__ . '/../repositories/ColegiadoRepository.php';

class PagoService{
    private $pagoRepository;
    private $colegiadoRepository;
    
    public function __construct() {
        $this->pagoRepository = new PagoRepository();
        $this->colegiadoRepository = new ColegiadoRepository();
    }

    // obtiene pagos con paginación
    public function obtenerPagos($page = 1, $perPage = 25, $filtros = []) {
        $pagos = $this->pagoRepository->findAllPaginated($page, $perPage, $filtros);
        $total = $this->pagoRepository->countAll($filtros);
        
        return [
            'pagos' => $pagos,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    // obtiene un pago por ID
    public function obtenerPorId($id) {
        return $this->pagoRepository->findById($id);
    }

    // registra un nuevo pago
    public function registrarPago($datos, $usuarioId) {
        $errores = $this->validarDatos($datos);
        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }
        
        $colegiado = $this->colegiadoRepository->findById($datos['colegiados_id']);
        if (!$colegiado) {
            return ['success' => false, 'errors' => ['El colegiado no existe']];
        }
        
        $datosInsert = [
            'colegiados_id' => $datos['colegiados_id'],
            'concepto_id' => !empty($datos['concepto_id']) ? $datos['concepto_id'] : null,
            'concepto_texto' => !empty($datos['concepto_texto']) ? $datos['concepto_texto'] : null,
            'monto' => $datos['monto'],
            'fecha_pago' => $datos['fecha_pago'],
            'estado' => 'registrado',
            'metodo_pago_id' => $datos['metodo_pago_id'],
            'numero_comprobante' => $datos['numero_comprobante'] ?? null,
            'observaciones' => $datos['observaciones'] ?? null,
            'usuario_registro_id' => $usuarioId,
            'archivo_comprobante' => $datos['archivo_comprobante'] ?? null
        ];
        
        $id = $this->pagoRepository->create($datosInsert);
        
        if ($id) {
            logMessage("Pago registrado: ID $id - Colegiado {$colegiado->numero_colegiatura} - Monto {$datos['monto']}", 'info');
            return ['success' => true, 'id' => $id];
        }
        
        return ['success' => false, 'errors' => ['Error al registrar el pago']];
    }

    // anula un pago
    public function anularPago($id, $usuarioId) {
        $pago = $this->pagoRepository->findById($id);
        
        if (!$pago) {
            return ['success' => false, 'message' => 'Pago no encontrado'];
        }
        
        if ($pago->estado === 'anulado') {
            return ['success' => false, 'message' => 'El pago ya está anulado'];
        }
        
        $resultado = $this->pagoRepository->anular($id);
        
        if ($resultado) {
            logMessage("Pago anulado: ID $id por usuario ID $usuarioId", 'warning');
            return ['success' => true, 'message' => 'Pago anulado correctamente'];
        }
        
        return ['success' => false, 'message' => 'Error al anular el pago'];
    }

    // valida un pago
    public function validarPago($id, $usuarioId) {
        $pago = $this->pagoRepository->findById($id);
        
        if (!$pago) {
            return ['success' => false, 'message' => 'Pago no encontrado'];
        }
        
        if ($pago->estado === 'validado') {
            return ['success' => false, 'message' => 'El pago ya está validado'];
        }
        
        if ($pago->estado === 'anulado') {
            return ['success' => false, 'message' => 'No se puede validar un pago anulado'];
        }
        
        $resultado = $this->pagoRepository->validar($id);
        
        if ($resultado) {
            logMessage("Pago validado: ID $id por usuario ID $usuarioId", 'info');
            return ['success' => true, 'message' => 'Pago validado correctamente'];
        }
        
        return ['success' => false, 'message' => 'Error al validar el pago'];
    }

    // obtiene resumen de ingresos
    public function obtenerResumen($fechaInicio, $fechaFin) {
        $resumen = $this->pagoRepository->getResumenIngresos($fechaInicio, $fechaFin);
        $porMetodo = $this->pagoRepository->getIngresosPorMetodo($fechaInicio, $fechaFin);
        $porConcepto = $this->pagoRepository->getIngresosPorConcepto($fechaInicio, $fechaFin);
        
        return [
            'resumen' => $resumen,
            'por_metodo' => $porMetodo,
            'por_concepto' => $porConcepto
        ];
    }

    // obtiene métodos y conceptos para formularios
    public function obtenerOpcionesPago() {
        return [
            'metodos' => $this->pagoRepository->getMetodosPago(),
            'conceptos' => $this->pagoRepository->getConceptosPago()
        ];
    }

    // valida datos del pago
    private function validarDatos($datos) {
        $errores = [];
        
        if (empty($datos['colegiados_id'])) {
            $errores[] = 'Debe seleccionar un colegiado';
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
        
        if (empty($datos['concepto_id']) && empty($datos['concepto_texto'])) {
            $errores[] = 'Debe especificar un concepto de pago';
        }
        
        return $errores;
    }

    // procesa archivo de comprobante
    public function subirComprobante($id, $archivo) {
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error al subir el archivo'];
        }
        
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $extensionesPermitidas)) {
            return ['success' => false, 'message' => 'Solo se permiten archivos JPG, PNG o PDF'];
        }
        
        $nombreArchivo = 'comprobante_' . $id . '_' . time() . '.' . $extension;
        $rutaDestino = basePath('public/uploads/comprobantes/' . $nombreArchivo);
        
        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            return ['success' => true, 'ruta' => 'uploads/comprobantes/' . $nombreArchivo];
        }
        
        return ['success' => false, 'message' => 'Error al guardar el archivo'];
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
        
        $datosInsert = [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'monto' => $datos['monto'],
            'tipo' => $datos['tipo'] ?? 'otro',
            'requiere' => isset($datos['requiere_comprobante']) ? 1 : 0,
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
        
        $datosUpdate = [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'monto' => $datos['monto'],
            'tipo' => $datos['tipo'] ?? 'otro',
            'requiere' => isset($datos['requiere_comprobante']) ? 1 : 0,
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
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
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
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
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
}