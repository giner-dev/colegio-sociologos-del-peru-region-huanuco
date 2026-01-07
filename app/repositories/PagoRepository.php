<?php
require_once __DIR__ . '/../models/Pago.php';

class PagoRepository{
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    // obtiene pagos con paginación y filtros
    public function findAllPaginated($page = 1, $perPage = 25, $filtros = []) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT p.*, 
                       c.nombres, c.apellido_paterno, c.apellido_materno, c.numero_colegiatura,
                       cp.nombre_completo as concepto_nombre,
                       m.nombre as metodo_nombre,
                       u.nombre_usuario as usuario_nombre
                FROM pagos p
                INNER JOIN colegiados c ON p.colegiados_id = c.idColegiados
                LEFT JOIN conceptos_pago cp ON p.concepto_id = cp.idConcepto
                LEFT JOIN metodo_pago m ON p.metodo_pago_id = m.idMetodo
                LEFT JOIN usuarios u ON p.usuario_registro_id = u.idUsuario
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['numero_colegiatura'])) {
            $sql .= " AND c.numero_colegiatura LIKE :numero";
            $params['numero'] = '%' . $filtros['numero_colegiatura'] . '%';
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
        
        $sql .= " ORDER BY p.fecha_pago DESC, p.fecha_registro DESC LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $results = $this->db->query($sql, $params);
        
        $pagos = [];
        foreach ($results as $row) {
            $pagos[] = new Pago($row);
        }
        
        return $pagos;
    }

    // cuenta total de pagos con filtros
    public function countAll($filtros = []) {
        $sql = "SELECT COUNT(*) as total
                FROM pagos p
                INNER JOIN colegiados c ON p.colegiados_id = c.idColegiados
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['numero_colegiatura'])) {
            $sql .= " AND c.numero_colegiatura LIKE :numero";
            $params['numero'] = '%' . $filtros['numero_colegiatura'] . '%';
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
        
        $result = $this->db->queryOne($sql, $params);
        return $result['total'];
    }

    // busca un pago por ID
    public function findById($id) {
        $sql = "SELECT p.*, 
                       c.nombres, c.apellido_paterno, c.apellido_materno, c.numero_colegiatura, c.dni,
                       cp.nombre_completo as concepto_nombre,
                       m.nombre as metodo_nombre,
                       u.nombre_usuario as usuario_nombre
                FROM pagos p
                INNER JOIN colegiados c ON p.colegiados_id = c.idColegiados
                LEFT JOIN conceptos_pago cp ON p.concepto_id = cp.idConcepto
                LEFT JOIN metodo_pago m ON p.metodo_pago_id = m.idMetodo
                LEFT JOIN usuarios u ON p.usuario_registro_id = u.idUsuario
                WHERE p.idPagos = :id";
        
        $result = $this->db->queryOne($sql, ['id' => $id]);
        
        if ($result) {
            return new Pago($result);
        }
        
        return null;
    }

    // crea un nuevo pago
    public function create($data) {
        $sql = "INSERT INTO pagos (
                    colegiados_id, concepto_id, concepto_texto, monto, fecha_pago,
                    estado, metodo_pago_id, numero_comprobante, observaciones,
                    usuario_registro_id, archivo_comprobante
                ) VALUES (
                    :colegiados_id, :concepto_id, :concepto_texto, :monto, :fecha_pago,
                    :estado, :metodo_pago_id, :numero_comprobante, :observaciones,
                    :usuario_registro_id, :archivo_comprobante
                )";
        
        return $this->db->insert($sql, $data);
    }

    // anula un pago
    public function anular($id) {
        $sql = "UPDATE pagos SET estado = 'anulado' WHERE idPagos = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    // valida un pago
    public function validar($id) {
        $sql = "UPDATE pagos SET estado = 'validado' WHERE idPagos = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    // obtiene resumen de ingresos por periodo
    public function getResumenIngresos($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    COUNT(*) as total_pagos,
                    SUM(monto) as total_monto,
                    AVG(monto) as promedio_monto,
                    MIN(monto) as monto_minimo,
                    MAX(monto) as monto_maximo
                FROM pagos
                WHERE fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
                AND estado != 'anulado'";
        
        return $this->db->queryOne($sql, [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);
    }

    // obtiene ingresos agrupados por método de pago
    public function getIngresosPorMetodo($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    m.nombre as metodo,
                    COUNT(p.idPagos) as cantidad,
                    SUM(p.monto) as total
                FROM pagos p
                INNER JOIN metodo_pago m ON p.metodo_pago_id = m.idMetodo
                WHERE p.fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
                AND p.estado != 'anulado'
                GROUP BY m.idMetodo, m.nombre
                ORDER BY total DESC";
        
        return $this->db->query($sql, [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);
    }

    // obtiene ingresos agrupados por concepto
    public function getIngresosPorConcepto($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    COALESCE(cp.nombre_completo, p.concepto_texto) as concepto,
                    COUNT(p.idPagos) as cantidad,
                    SUM(p.monto) as total
                FROM pagos p
                LEFT JOIN conceptos_pago cp ON p.concepto_id = cp.idConcepto
                WHERE p.fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
                AND p.estado != 'anulado'
                GROUP BY concepto
                ORDER BY total DESC";
        
        return $this->db->query($sql, [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);
    }

    // obtiene todos los métodos de pago
    public function getMetodosPago() {
        $sql = "SELECT * FROM metodo_pago WHERE activo = 'activo' ORDER BY nombre ASC";
        return $this->db->query($sql);
    }

    // obtiene todos los conceptos de pago
    public function getConceptosPago() {
        $sql = "SELECT * FROM conceptos_pago WHERE estado = 'activo' ORDER BY nombre_completo ASC";
        return $this->db->query($sql);
    }



    // GESTIÓN DE CONCEPTOS DE PAGO
    public function getAllConceptos() {
        $sql = "SELECT * FROM conceptos_pago ORDER BY nombre_completo ASC";
        return $this->db->query($sql);
    }

    public function findConceptoById($id) {
        $sql = "SELECT * FROM conceptos_pago WHERE idConcepto = :id";
        return $this->db->queryOne($sql, ['id' => $id]);
    }
    
    public function createConcepto($data) {
        $sql = "INSERT INTO conceptos_pago (nombre_completo, descripcion, monto_sugerido, tipo_concepto, requiere_comprobante, estado)
                VALUES (:nombre, :descripcion, :monto, :tipo, :requiere, :estado)";
        return $this->db->insert($sql, $data);
    }
    
    public function updateConcepto($id, $data) {
        $sql = "UPDATE conceptos_pago 
                SET nombre_completo = :nombre, 
                    descripcion = :descripcion, 
                    monto_sugerido = :monto, 
                    tipo_concepto = :tipo,
                    requiere_comprobante = :requiere,
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
        $sql = "SELECT * FROM metodo_pago ORDER BY nombre ASC";
        return $this->db->query($sql);
    }
    
    public function findMetodoById($id) {
        $sql = "SELECT * FROM metodo_pago WHERE idMetodo = :id";
        return $this->db->queryOne($sql, ['id' => $id]);
    }
    
    public function createMetodo($data) {
        $sql = "INSERT INTO metodo_pago (nombre, descripcion, activo)
                VALUES (:nombre, :descripcion, :activo)";
        return $this->db->insert($sql, $data);
    }
    
    public function updateMetodo($id, $data) {
        $sql = "UPDATE metodo_pago 
                SET nombre = :nombre, 
                    descripcion = :descripcion, 
                    activo = :activo
                WHERE idMetodo = :id";
        $data['id'] = $id;
        return $this->db->execute($sql, $data);
    }
    
    public function deleteMetodo($id) {
        $sql = "UPDATE metodo_pago SET activo = 'inactivo' WHERE idMetodo = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }
}