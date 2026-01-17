<?php
require_once __DIR__ . '/../models/Pago.php';

class PagoRepository {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Obtiene pagos con paginación y filtros
    public function findAllPaginated($page = 1, $perPage = 25, $filtros = []) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT p.*,
                       c.nombres, c.apellido_paterno, c.apellido_materno, 
                       c.numero_colegiatura, c.dni,
                       d.descripcion_deuda as deuda_descripcion,
                       CASE 
                           WHEN d.es_deuda_manual = 1 THEN d.concepto_manual
                           ELSE cp.nombre_completo
                       END as deuda_concepto,
                       d.es_deuda_manual,
                       d.monto_esperado as deuda_monto_esperado,
                       mp.nombre as metodo_nombre,
                       ur.nombre_usuario as usuario_registro_nombre,
                       uc.nombre_usuario as usuario_confirmacion_nombre
                FROM pagos p
                INNER JOIN colegiados c ON p.colegiado_id = c.idColegiados
                INNER JOIN deudas d ON p.deuda_id = d.idDeuda
                INNER JOIN conceptos_pago cp ON d.concepto_id = cp.idConcepto
                INNER JOIN metodo_pago mp ON p.metodo_pago_id = mp.idMetodo
                INNER JOIN usuarios ur ON p.usuario_registro_id = ur.idUsuario
                LEFT JOIN usuarios uc ON p.usuario_confirmacion_id = uc.idUsuario
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['numero_colegiatura'])) {
            $sql .= " AND c.numero_colegiatura LIKE :numero";
            $params['numero'] = '%' . $filtros['numero_colegiatura'] . '%';
        }
        
        if (!empty($filtros['dni'])) {
            $sql .= " AND c.dni LIKE :dni";
            $params['dni'] = '%' . $filtros['dni'] . '%';
        }
        
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND p.fecha_pago >= :fecha_inicio";
            $params['fecha_inicio'] = $filtros['fecha_inicio'];
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND p.fecha_pago <= :fecha_fin";
            $params['fecha_fin'] = $filtros['fecha_fin'];
        }
        
        if (!empty($filtros['metodo_pago'])) {
            $sql .= " AND p.metodo_pago_id = :metodo";
            $params['metodo'] = $filtros['metodo_pago'];
        }
        
        if (!empty($filtros['estado'])) {
            $sql .= " AND p.estado = :estado";
            $params['estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['concepto_id'])) {
            $sql .= " AND d.concepto_id = :concepto_id";
            $params['concepto_id'] = $filtros['concepto_id'];
        }
        
        $sql .= " ORDER BY p.fecha_pago DESC, p.fecha_registro DESC 
                  LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $results = $this->db->query($sql, $params);
        
        $pagos = [];
        foreach ($results as $row) {
            $pagos[] = new Pago($row);
        }
        
        return $pagos;
    }

    // Cuenta total de pagos con filtros
    public function countAll($filtros = []) {
        $sql = "SELECT COUNT(*) as total
                FROM pagos p
                INNER JOIN colegiados c ON p.colegiado_id = c.idColegiados
                INNER JOIN deudas d ON p.deuda_id = d.idDeuda
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['numero_colegiatura'])) {
            $sql .= " AND c.numero_colegiatura LIKE :numero";
            $params['numero'] = '%' . $filtros['numero_colegiatura'] . '%';
        }
        
        if (!empty($filtros['dni'])) {
            $sql .= " AND c.dni LIKE :dni";
            $params['dni'] = '%' . $filtros['dni'] . '%';
        }
        
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND p.fecha_pago >= :fecha_inicio";
            $params['fecha_inicio'] = $filtros['fecha_inicio'];
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND p.fecha_pago <= :fecha_fin";
            $params['fecha_fin'] = $filtros['fecha_fin'];
        }
        
        if (!empty($filtros['metodo_pago'])) {
            $sql .= " AND p.metodo_pago_id = :metodo";
            $params['metodo'] = $filtros['metodo_pago'];
        }
        
        if (!empty($filtros['estado'])) {
            $sql .= " AND p.estado = :estado";
            $params['estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['concepto_id'])) {
            $sql .= " AND d.concepto_id = :concepto_id";
            $params['concepto_id'] = $filtros['concepto_id'];
        }
        
        $result = $this->db->queryOne($sql, $params);
        return $result['total'];
    }

    // Busca un pago por ID
    public function findById($id) {
        $sql = "SELECT p.*,
                       c.nombres, c.apellido_paterno, c.apellido_materno, 
                       c.numero_colegiatura, c.dni,
                       d.descripcion_deuda as deuda_descripcion,
                       CASE 
                           WHEN d.es_deuda_manual = 1 THEN d.concepto_manual
                           ELSE cp.nombre_completo
                       END as deuda_concepto,
                       d.es_deuda_manual,
                       d.monto_esperado as deuda_monto_esperado,
                       mp.nombre as metodo_nombre,
                       ur.nombre_usuario as usuario_registro_nombre,
                       uc.nombre_usuario as usuario_confirmacion_nombre
                FROM pagos p
                INNER JOIN colegiados c ON p.colegiado_id = c.idColegiados
                INNER JOIN deudas d ON p.deuda_id = d.idDeuda
                INNER JOIN conceptos_pago cp ON d.concepto_id = cp.idConcepto
                INNER JOIN metodo_pago mp ON p.metodo_pago_id = mp.idMetodo
                INNER JOIN usuarios ur ON p.usuario_registro_id = ur.idUsuario
                LEFT JOIN usuarios uc ON p.usuario_confirmacion_id = uc.idUsuario
                WHERE p.idPago = :id";
        
        $result = $this->db->queryOne($sql, ['id' => $id]);
        
        if ($result) {
            return new Pago($result);
        }
        
        return null;
    }

    // 🔧 CORREGIDO: Crea un nuevo pago (ERROR SQL SOLUCIONADO)
    public function create($data) {
        // ✅ CORRECCIÓN: Eliminar fecha_registro_pago duplicado
        // fecha_registro ya se maneja con DEFAULT CURRENT_TIMESTAMP
        $sql = "INSERT INTO pagos (
                    colegiado_id, 
                    deuda_id, 
                    monto, 
                    fecha_pago,
                    metodo_pago_id, 
                    numero_comprobante, 
                    archivo_comprobante,
                    estado, 
                    observaciones,
                    usuario_registro_id
                ) VALUES (
                    :colegiado_id, 
                    :deuda_id, 
                    :monto, 
                    :fecha_pago,
                    :metodo_pago_id, 
                    :numero_comprobante, 
                    :archivo_comprobante,
                    :estado, 
                    :observaciones,
                    :usuario_registro_id
                )";
        
        // ✅ Preparar parámetros sin fecha_registro_pago
        $params = [
            'colegiado_id' => $data['colegiado_id'],
            'deuda_id' => $data['deuda_id'],
            'monto' => $data['monto'],
            'fecha_pago' => $data['fecha_pago'],
            'metodo_pago_id' => $data['metodo_pago_id'],
            'numero_comprobante' => $data['numero_comprobante'] ?? null,
            'archivo_comprobante' => $data['archivo_comprobante'] ?? null,
            'estado' => $data['estado'] ?? 'registrado',
            'observaciones' => $data['observaciones'] ?? null,
            'usuario_registro_id' => $data['usuario_registro_id']
        ];
        
        return $this->db->insert($sql, $params);
    }

    // Crea el detalle de aplicación del pago
    public function crearDetalleAplicacion($data) {
        $sql = "INSERT INTO detalle_aplicacion_pagos (
                    pago_id, 
                    deuda_id, 
                    monto_aplicado,
                    usuario_aplicacion_id
                ) VALUES (
                    :pago_id, 
                    :deuda_id, 
                    :monto_aplicado,
                    :usuario_aplicacion_id
                )";
        
        return $this->db->insert($sql, $data);
    }

    // Confirma un pago
    public function confirmar($id, $usuarioConfirmacionId) {
        $sql = "UPDATE pagos 
                SET estado = 'confirmado',
                    fecha_confirmacion = NOW(),
                    usuario_confirmacion_id = :usuario_confirmacion_id
                WHERE idPago = :id
                AND estado = 'registrado'";
        
        return $this->db->execute($sql, [
            'id' => $id,
            'usuario_confirmacion_id' => $usuarioConfirmacionId
        ]);
    }

    // Anula un pago
    public function anular($id, $usuarioId = null) {
        $archivoPath = $this->getArchivoComprobante($id);
        
        $sql = "UPDATE pagos 
                SET estado = 'anulado',
                    usuario_confirmacion_id = COALESCE(:usuario_confirmacion_id, usuario_confirmacion_id)
                WHERE idPago = :id
                AND estado IN ('registrado', 'confirmado')";
        
        $resultado = $this->db->execute($sql, [
            'id' => $id,
            'usuario_confirmacion_id' => $usuarioId
        ]);
        
        if ($resultado && $archivoPath) {
            FileUploadManager::deleteComprobante($archivoPath);
        }
        
        return $resultado;
    }

    // Obtiene resumen de ingresos por periodo
    public function getResumenIngresos($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    COUNT(*) as total_pagos,
                    SUM(p.monto) as total_monto,
                    AVG(p.monto) as promedio_monto,
                    MIN(p.monto) as monto_minimo,
                    MAX(p.monto) as monto_maximo
                FROM pagos p
                WHERE p.fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
                AND p.estado = 'confirmado'";
        
        return $this->db->queryOne($sql, [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);
    }

    // Obtiene ingresos agrupados por método de pago
    public function getIngresosPorMetodo($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    mp.nombre as metodo,
                    COUNT(p.idPago) as cantidad,
                    SUM(p.monto) as total
                FROM pagos p
                INNER JOIN metodo_pago mp ON p.metodo_pago_id = mp.idMetodo
                WHERE p.fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
                AND p.estado = 'confirmado'
                GROUP BY mp.idMetodo, mp.nombre
                ORDER BY total DESC";
        
        return $this->db->query($sql, [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);
    }

    // Obtiene ingresos agrupados por concepto
    public function getIngresosPorConcepto($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    CASE 
                        WHEN d.es_deuda_manual = 1 THEN d.concepto_manual
                        ELSE cp.nombre_completo
                    END as concepto,
                    COUNT(p.idPago) as cantidad,
                    SUM(p.monto) as total
                FROM pagos p
                INNER JOIN deudas d ON p.deuda_id = d.idDeuda
                INNER JOIN conceptos_pago cp ON d.concepto_id = cp.idConcepto
                WHERE p.fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
                AND p.estado = 'confirmado'
                GROUP BY concepto
                ORDER BY total DESC";
        
        return $this->db->query($sql, [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);
    }

    // Obtiene todos los métodos de pago activos
    public function getMetodosPago() {
        $sql = "SELECT * FROM metodo_pago 
                WHERE activo = 'activo' 
                ORDER BY orden ASC, nombre ASC";
        return $this->db->query($sql);
    }

    // Obtiene todos los conceptos de pago activos
    public function getConceptosPago() {
        $sql = "SELECT * FROM conceptos_pago 
                WHERE estado = 'activo' 
                ORDER BY nombre_completo ASC";
        return $this->db->query($sql);
    }

    // Obtiene pagos de un colegiado
    public function findByColegiado($colegiadoId, $limit = 50) {
        $sql = "SELECT p.*,
                       c.nombres, c.apellido_paterno, c.apellido_materno,
                       c.numero_colegiatura,
                       d.descripcion_deuda as deuda_descripcion,
                       CASE 
                           WHEN d.es_deuda_manual = 1 THEN d.concepto_manual
                           ELSE cp.nombre_completo
                       END as deuda_concepto,
                       d.es_deuda_manual,
                       mp.nombre as metodo_nombre
                FROM pagos p
                INNER JOIN colegiados c ON p.colegiado_id = c.idColegiados
                INNER JOIN deudas d ON p.deuda_id = d.idDeuda
                INNER JOIN conceptos_pago cp ON d.concepto_id = cp.idConcepto
                INNER JOIN metodo_pago mp ON p.metodo_pago_id = mp.idMetodo
                WHERE p.colegiado_id = :colegiado_id
                ORDER BY p.fecha_pago DESC
                LIMIT :limit";
        
        $results = $this->db->query($sql, [
            'colegiado_id' => $colegiadoId,
            'limit' => $limit
        ]);
        
        $pagos = [];
        foreach ($results as $row) {
            $pagos[] = new Pago($row);
        }
        
        return $pagos;
    }

    // Verifica si existe un comprobante con el mismo número
    public function existeComprobante($numeroComprobante, $metodoPagoId, $excluirId = null) {
        if ($excluirId) {
            $sql = "SELECT COUNT(*) as total 
                    FROM pagos 
                    WHERE numero_comprobante = :comprobante 
                    AND metodo_pago_id = :metodo_id
                    AND idPago != :excluir_id";
            
            $params = [
                'comprobante' => $numeroComprobante,
                'metodo_id' => $metodoPagoId,
                'excluir_id' => $excluirId
            ];
        } else {
            $sql = "SELECT COUNT(*) as total 
                    FROM pagos 
                    WHERE numero_comprobante = :comprobante 
                    AND metodo_pago_id = :metodo_id";
            
            $params = [
                'comprobante' => $numeroComprobante,
                'metodo_id' => $metodoPagoId
            ];
        }
        
        $result = $this->db->queryOne($sql, $params);
        return $result['total'] > 0;
    }

    // Obtiene total de pagos por período
    public function getTotalPorPeriodo($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    SUM(CASE WHEN estado = 'confirmado' THEN monto ELSE 0 END) as total_confirmado,
                    SUM(CASE WHEN estado = 'registrado' THEN monto ELSE 0 END) as total_registrado,
                    SUM(CASE WHEN estado = 'anulado' THEN monto ELSE 0 END) as total_anulado,
                    COUNT(CASE WHEN estado = 'confirmado' THEN 1 END) as cantidad_confirmados,
                    COUNT(CASE WHEN estado = 'registrado' THEN 1 END) as cantidad_registrados,
                    COUNT(CASE WHEN estado = 'anulado' THEN 1 END) as cantidad_anulados
                FROM pagos
                WHERE fecha_pago BETWEEN :fecha_inicio AND :fecha_fin";
        
        return $this->db->queryOne($sql, [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);
    }

    // GESTIÓN DE CONCEPTOS DE PAGO (Administración)
    public function getAllConceptos() {
        $sql = "SELECT * FROM conceptos_pago ORDER BY nombre_completo ASC";
        return $this->db->query($sql);
    }

    public function findConceptoById($id) {
        $sql = "SELECT * FROM conceptos_pago WHERE idConcepto = :id";
        return $this->db->queryOne($sql, ['id' => $id]);
    }
    
    public function createConcepto($data) {
        $sql = "INSERT INTO conceptos_pago (
                    nombre_completo, descripcion, monto_sugerido, 
                    tipo_concepto, requiere_comprobante, es_recurrente,
                    frecuencia, dia_vencimiento, estado
                ) VALUES (
                    :nombre, :descripcion, :monto, :tipo, :requiere,
                    :es_recurrente, :frecuencia, :dia_vencimiento, :estado
                )";
        return $this->db->insert($sql, $data);
    }
    
    public function updateConcepto($id, $data) {
        $sql = "UPDATE conceptos_pago 
                SET nombre_completo = :nombre, 
                    descripcion = :descripcion, 
                    monto_sugerido = :monto, 
                    tipo_concepto = :tipo,
                    requiere_comprobante = :requiere,
                    es_recurrente = :es_recurrente,
                    frecuencia = :frecuencia,
                    dia_vencimiento = :dia_vencimiento,
                    estado = :estado
                WHERE idConcepto = :id";
        $data['id'] = $id;
        return $this->db->execute($sql, $data);
    }
    
    public function deleteConcepto($id) {
        $sql = "UPDATE conceptos_pago SET estado = 'inactivo' WHERE idConcepto = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    // GESTIÓN DE MÉTODOS DE PAGO
    public function getAllMetodos() {
        $sql = "SELECT * FROM metodo_pago ORDER BY orden ASC, nombre ASC";
        return $this->db->query($sql);
    }
    
    public function findMetodoById($id) {
        $sql = "SELECT * FROM metodo_pago WHERE idMetodo = :id";
        return $this->db->queryOne($sql, ['id' => $id]);
    }
    
    public function createMetodo($data) {
        $sql = "INSERT INTO metodo_pago (
                    codigo, nombre, descripcion, requiere_comprobante,
                    datos_adicionales, orden, activo
                ) VALUES (
                    :codigo, :nombre, :descripcion, :requiere_comprobante,
                    :datos_adicionales, :orden, :activo
                )";
        return $this->db->insert($sql, $data);
    }
    
    public function updateMetodo($id, $data) {
        $sql = "UPDATE metodo_pago 
                SET codigo = :codigo,
                    nombre = :nombre, 
                    descripcion = :descripcion, 
                    requiere_comprobante = :requiere_comprobante,
                    datos_adicionales = :datos_adicionales,
                    orden = :orden,
                    activo = :activo
                WHERE idMetodo = :id";
        $data['id'] = $id;
        return $this->db->execute($sql, $data);
    }
    
    public function deleteMetodo($id) {
        $sql = "UPDATE metodo_pago SET activo = 'inactivo' WHERE idMetodo = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    // Obtiene deudas pendientes de un colegiado
    public function getDeudasPendientes($colegiado_id) {
        $sql = "SELECT d.*, 
                       CASE 
                            WHEN d.es_deuda_manual = 1 THEN d.concepto_manual
                            ELSE COALESCE(cp.nombre_completo, 'Concepto no definido')
                       END as concepto_nombre,
                       COALESCE(cp.descripcion, d.concepto_manual) as concepto_descripcion,
                       d.monto_esperado,
                       d.monto_pagado,
                       d.saldo_pendiente
                FROM deudas d
                LEFT JOIN conceptos_pago cp ON d.concepto_id = cp.idConcepto
                WHERE d.colegiado_id = :colegiado_id
                AND d.estado IN ('pendiente', 'vencido', 'parcial')
                AND d.saldo_pendiente > 0
                ORDER BY d.fecha_vencimiento ASC";

        return $this->db->query($sql, ['colegiado_id' => $colegiado_id]);
    }

    // obtiene la ruta del comprobante de un pago
    public function getArchivoComprobante($id) {
        $sql = "SELECT archivo_comprobante FROM pagos WHERE idPago = :id";
        $result = $this->db->queryOne($sql, ['id' => $id]);
        return $result['archivo_comprobante'] ?? null;
    }

    // Elimina un pago y su archivo asociado
    public function deleteConArchivo($id) {
        try {
            $archivoPath = $this->getArchivoComprobante($id);

            $sql = "DELETE FROM pagos WHERE idPago = :id";
            $resultado = $this->db->execute($sql, ['id' => $id]);

            if ($resultado && $archivoPath) {
                FileUploadManager::deleteComprobante($archivoPath);
            }

            return $resultado;

        } catch (Exception $e) {
            logMessage("Error al eliminar pago con archivo: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
}