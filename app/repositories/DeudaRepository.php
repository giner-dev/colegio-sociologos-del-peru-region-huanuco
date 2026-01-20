<?php
require_once __DIR__ . '/../models/Deuda.php';

class DeudaRepository {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Obtiene conceptos de pago activos CON información de recurrencia
    public function getConceptosActivos() {
        $sql = "SELECT 
                    idConcepto, 
                    nombre_completo, 
                    monto_sugerido,
                    es_recurrente,
                    frecuencia,
                    dia_vencimiento,
                    descripcion
                FROM conceptos_pago 
                WHERE estado = 'activo'
                ORDER BY nombre_completo ASC";
        
        return $this->db->query($sql);
    }

    // Busca un concepto por ID
    public function findConceptoById($id) {
        $sql = "SELECT * FROM conceptos_pago WHERE idConcepto = :id";
        return $this->db->queryOne($sql, ['id' => $id]);
    }

    // Obtiene deudas con paginación y filtros
    public function findAllPaginated($page = 1, $perPage = 25, $filtros = []) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT d.*, 
                       c.nombres, c.apellido_paterno, c.apellido_materno, 
                       c.numero_colegiatura, c.dni,
                       cp.nombre_completo as concepto_nombre,
                       cp.descripcion as concepto_descripcion,
                       cp.monto_sugerido,
                       cp.es_recurrente,
                       cp.frecuencia,
                       cp.dia_vencimiento,
                       CASE 
                           WHEN d.es_deuda_manual = 1 THEN d.concepto_manual
                           ELSE cp.nombre_completo
                       END as concepto_mostrar
                FROM deudas d
                INNER JOIN colegiados c ON d.colegiado_id = c.idColegiados
                LEFT JOIN conceptos_pago cp ON d.concepto_id = cp.idConcepto
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
        
        if (!empty($filtros['estado'])) {
            $sql .= " AND d.estado = :estado";
            $params['estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND d.fecha_vencimiento >= :fecha_desde";
            $params['fecha_desde'] = $filtros['fecha_desde'];
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND d.fecha_vencimiento <= :fecha_hasta";
            $params['fecha_hasta'] = $filtros['fecha_hasta'];
        }
        
        if (!empty($filtros['concepto_id'])) {
            $sql .= " AND d.concepto_id = :concepto_id";
            $params['concepto_id'] = $filtros['concepto_id'];
        }
        
        if (!empty($filtros['origen'])) {
            $sql .= " AND d.origen = :origen";
            $params['origen'] = $filtros['origen'];
        }
        
        $sql .= "  ORDER BY 
                      CASE 
                          WHEN d.estado = 'pendiente' THEN 1
                          WHEN d.estado = 'parcial' THEN 2
                          WHEN d.estado = 'vencido' THEN 3
                          ELSE 4
                      END,
                      d.fecha_vencimiento ASC
                    LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $results = $this->db->query($sql, $params);
        
        $deudas = [];
        foreach ($results as $row) {
            $deudas[] = new Deuda($row);
        }
        
        return $deudas;
    }

    // Cuenta total de deudas con filtros
    public function countAll($filtros = []) {
        $sql = "SELECT COUNT(*) as total
                FROM deudas d
                INNER JOIN colegiados c ON d.colegiado_id = c.idColegiados
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
        
        if (!empty($filtros['estado'])) {
            $sql .= " AND d.estado = :estado";
            $params['estado'] = $filtros['estado'];
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND d.fecha_vencimiento >= :fecha_desde";
            $params['fecha_desde'] = $filtros['fecha_desde'];
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND d.fecha_vencimiento <= :fecha_hasta";
            $params['fecha_hasta'] = $filtros['fecha_hasta'];
        }
        
        if (!empty($filtros['concepto_id'])) {
            $sql .= " AND d.concepto_id = :concepto_id";
            $params['concepto_id'] = $filtros['concepto_id'];
        }
        
        $result = $this->db->queryOne($sql, $params);
        return $result['total'];
    }

    // Busca una deuda por ID
    public function findById($id) {
        $sql = "SELECT d.*, 
                       c.nombres, c.apellido_paterno, c.apellido_materno, 
                       c.numero_colegiatura, c.dni,
                       cp.nombre_completo as concepto_nombre,
                       cp.descripcion as concepto_descripcion,
                       cp.monto_sugerido,
                       CASE 
                           WHEN d.es_deuda_manual = 1 THEN d.concepto_manual
                           ELSE cp.nombre_completo
                       END as concepto_mostrar
                FROM deudas d
                INNER JOIN colegiados c ON d.colegiado_id = c.idColegiados
                LEFT JOIN conceptos_pago cp ON d.concepto_id = cp.idConcepto
                WHERE d.idDeuda = :id";
        
        $result = $this->db->queryOne($sql, ['id' => $id]);
        
        if ($result) {
            return new Deuda($result);
        }
        
        return null;
    }

    // Obtiene deudas por colegiado
    public function findByColegiado($colegiadoId) {
        $sql = "SELECT d.*, 
                       cp.nombre_completo as concepto_nombre,
                       cp.descripcion as concepto_descripcion,
                       CASE 
                           WHEN d.es_deuda_manual = 1 THEN d.concepto_manual
                           ELSE cp.nombre_completo
                       END as concepto_mostrar
                FROM deudas d
                LEFT JOIN conceptos_pago cp ON d.concepto_id = cp.idConcepto
                WHERE d.colegiado_id = :id 
                ORDER BY d.fecha_vencimiento DESC";
        
        $results = $this->db->query($sql, ['id' => $colegiadoId]);
        
        $deudas = [];
        foreach ($results as $row) {
            $deudas[] = new Deuda($row);
        }
        
        return $deudas;
    }

    // Obtiene deudas pendientes por colegiado
    public function findPendientesByColegiado($colegiadoId) {
        $sql = "SELECT d.*, 
                       cp.nombre_completo as concepto_nombre,
                       CASE 
                           WHEN d.es_deuda_manual = 1 THEN d.concepto_manual
                           ELSE cp.nombre_completo
                       END as concepto_mostrar
                FROM deudas d
                LEFT JOIN conceptos_pago cp ON d.concepto_id = cp.idConcepto
                WHERE d.colegiado_id = :id 
                AND d.estado IN ('pendiente', 'vencido', 'parcial')
                ORDER BY d.fecha_vencimiento ASC";
        
        $results = $this->db->query($sql, ['id' => $colegiadoId]);
        
        $deudas = [];
        foreach ($results as $row) {
            $deudas[] = new Deuda($row);
        }
        
        return $deudas;
    }

    // Calcula deuda total de un colegiado
    public function calcularDeudaTotal($colegiadoId) {
        $sql = "SELECT COALESCE(SUM(d.saldo_pendiente), 0) as total
                FROM deudas d
                WHERE d.colegiado_id = :id
                AND d.estado IN ('pendiente', 'vencido', 'parcial')";
        
        $result = $this->db->queryOne($sql, ['id' => $colegiadoId]);
        return $result['total'];
    }

    // Crea una nueva deuda
    public function create($data) {
        $sql = "INSERT INTO deudas (
                    colegiado_id, 
                    concepto_id,
                    concepto_manual,
                    es_deuda_manual, 
                    descripcion_deuda, 
                    monto_esperado, 
                    fecha_generacion,
                    fecha_vencimiento, 
                    fecha_maxima_pago,
                    estado, 
                    origen,
                    usuario_generador_id,
                    observaciones
                ) VALUES (
                    :colegiado_id, 
                    :concepto_id,
                    :concepto_manual,
                    :es_deuda_manual,
                    :descripcion_deuda, 
                    :monto_esperado, 
                    :fecha_generacion,
                    :fecha_vencimiento, 
                    :fecha_maxima_pago,
                    :estado, 
                    :origen,
                    :usuario_generador_id,
                    :observaciones
                )";
        
        return $this->db->insert($sql, $data);
    }

    // Actualiza una deuda
    public function update($id, $data) {
        $sql = "UPDATE deudas 
                SET concepto_id = :concepto_id,
                    concepto_manual = :concepto_manual,
                    es_deuda_manual = :es_deuda_manual,
                    descripcion_deuda = :descripcion_deuda,
                    monto_esperado = :monto_esperado,
                    fecha_vencimiento = :fecha_vencimiento,
                    fecha_maxima_pago = :fecha_maxima_pago,
                    estado = :estado,
                    observaciones = :observaciones
                WHERE idDeuda = :id";
        
        $data['id'] = $id;
        return $this->db->execute($sql, $data);
    }

    // Actualiza el monto pagado de una deuda
    public function actualizarMontoPagado($deudaId, $nuevoMontoPagado) {
        // 1. Obtener la deuda actual
        $deuda = $this->findById($deudaId);
        
        if (!$deuda) {
            throw new Exception("Deuda no encontrada");
        }
        
        // 2. Calcular el nuevo estado en PHP
        $nuevoEstado = 'pendiente';
        
        if ($nuevoMontoPagado >= $deuda->monto_esperado) {
            $nuevoEstado = 'pagado';
        } elseif ($nuevoMontoPagado > 0) {
            $nuevoEstado = 'parcial';
        } elseif ($deuda->estado === 'vencido') {
            $nuevoEstado = 'vencido'; // Mantener vencido si ya lo estaba
        }
        
        // 3. Actualizar con una consulta simple (sin CASE)
        $sql = "UPDATE deudas 
                SET monto_pagado = :monto_pagado,
                    estado = :estado
                WHERE idDeuda = :id";
        
        $resultado = $this->db->execute($sql, [
            'monto_pagado' => $nuevoMontoPagado,
            'estado' => $nuevoEstado,
            'id' => $deudaId
        ]);
        
        logMessage("Deuda actualizada: ID $deudaId - Monto pagado: $nuevoMontoPagado - Estado: $nuevoEstado", 'info');
        
        return $resultado;
    }

    // Cambia el estado de una deuda
    public function cambiarEstado($id, $nuevoEstado, $observaciones = null) {
        $sql = "UPDATE deudas 
                SET estado = :estado,
                    observaciones = COALESCE(:observaciones, observaciones)
                WHERE idDeuda = :id";
        
        return $this->db->execute($sql, [
            'id' => $id,
            'estado' => $nuevoEstado,
            'observaciones' => $observaciones
        ]);
    }

    // Marca una deuda como cancelada
    public function cancelar($id, $motivo = null) {
        $sql = "UPDATE deudas 
                SET estado = 'cancelado',
                    observaciones = CONCAT(
                        COALESCE(observaciones, ''), 
                        CASE 
                            WHEN observaciones IS NOT NULL AND observaciones != '' 
                            THEN ' | ' 
                            ELSE '' 
                        END,
                        'Cancelada: ', 
                        :motivo
                    )
                WHERE idDeuda = :id";
        
        return $this->db->execute($sql, [
            'id' => $id,
            'motivo' => $motivo
        ]);
    }

    // Elimina una deuda (solo si está pendiente o vencida sin pagos)
    public function delete($id) {
        $sql = "DELETE FROM deudas 
                WHERE idDeuda = :id 
                AND estado IN ('pendiente', 'vencido')
                AND (monto_pagado = 0 OR monto_pagado IS NULL)";
        
        return $this->db->execute($sql, ['id' => $id]);
    }

    // Obtiene colegiados morosos
    public function getMorosos($page = 1, $perPage = 25) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT 
                    c.idColegiados,
                    c.numero_colegiatura,
                    c.dni,
                    c.nombres,
                    c.apellido_paterno,
                    c.apellido_materno,
                    COUNT(d.idDeuda) as cantidad_deudas,
                    SUM(d.saldo_pendiente) as total_deuda,
                    MAX(d.fecha_vencimiento) as ultimo_vencimiento,
                    MAX(CASE 
                        WHEN d.es_deuda_manual = 1 THEN d.concepto_manual
                        ELSE cp.nombre_completo
                    END) as concepto_mostrar
                FROM colegiados c
                INNER JOIN deudas d ON c.idColegiados = d.colegiado_id
                LEFT JOIN conceptos_pago cp ON d.concepto_id = cp.idConcepto 
                WHERE d.estado IN ('pendiente', 'vencido', 'parcial')
                GROUP BY c.idColegiados
                HAVING total_deuda > 0
                ORDER BY total_deuda DESC
                LIMIT :limit OFFSET :offset";
        
        $params = ['limit' => $perPage, 'offset' => $offset];
        return $this->db->query($sql, $params);
    }

    // Cuenta total de morosos
    public function countMorosos() {
        $sql = "SELECT COUNT(DISTINCT c.idColegiados) as total
                FROM colegiados c
                INNER JOIN deudas d ON c.idColegiados = d.colegiado_id
                WHERE d.estado IN ('pendiente', 'vencido', 'parcial')
                AND d.saldo_pendiente > 0";
        
        $result = $this->db->queryOne($sql);
        return $result['total'];
    }

    // Actualiza estados de deudas vencidas
    public function actualizarVencidas() {
        $sql = "UPDATE deudas 
                SET estado = 'vencido' 
                WHERE estado = 'pendiente' 
                AND fecha_vencimiento < CURDATE()";
        
        return $this->db->execute($sql);
    }

    // Obtiene resumen de deudas
    public function getResumen() {
        $sql = "SELECT 
                    COUNT(*) as total_deudas,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'parcial' THEN 1 ELSE 0 END) as parciales,
                    SUM(CASE WHEN estado = 'vencido' THEN 1 ELSE 0 END) as vencidas,
                    SUM(CASE WHEN estado = 'pagado' THEN 1 ELSE 0 END) as pagadas,
                    SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as canceladas,
                    COALESCE(SUM(CASE WHEN estado IN ('pendiente', 'vencido', 'parcial') THEN saldo_pendiente ELSE 0 END), 0) as monto_pendiente,
                    COALESCE(SUM(monto_esperado), 0) as monto_total,
                    COALESCE(SUM(monto_pagado), 0) as monto_pagado_total
                FROM deudas";

        $result = $this->db->queryOne($sql);
        
        return [
            'total_deudas' => $result['total_deudas'] ?? 0,
            'pendientes' => $result['pendientes'] ?? 0,
            'parciales' => $result['parciales'] ?? 0,
            'vencidas' => $result['vencidas'] ?? 0,
            'pagadas' => $result['pagadas'] ?? 0,
            'canceladas' => $result['canceladas'] ?? 0,
            'monto_pendiente' => $result['monto_pendiente'] ?? 0,
            'monto_total' => $result['monto_total'] ?? 0,
            'monto_pagado_total' => $result['monto_pagado_total'] ?? 0
        ];
    }

    // Obtiene deudas próximas a vencer
    public function getProximasAVencer($dias = 7) {
        $sql = "SELECT d.*, 
                       c.nombres, c.apellido_paterno, c.apellido_materno,
                       c.numero_colegiatura, c.dni, c.telefono, c.correo,
                       cp.nombre_completo as concepto_nombre,
                       CASE 
                           WHEN d.es_deuda_manual = 1 THEN d.concepto_manual
                           ELSE cp.nombre_completo
                       END as concepto_mostrar
                FROM deudas d
                INNER JOIN colegiados c ON d.colegiado_id = c.idColegiados
                LEFT JOIN conceptos_pago cp ON d.concepto_id = cp.idConcepto
                WHERE d.estado = 'pendiente'
                AND d.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                ORDER BY d.fecha_vencimiento ASC";
        
        return $this->db->query($sql, ['dias' => $dias]);
    }

    // Verifica si un colegiado tiene deudas pendientes
    public function tieneDeudasPendientes($colegiadoId) {
        $sql = "SELECT COUNT(*) as total 
                FROM deudas 
                WHERE colegiado_id = :id 
                AND estado IN ('pendiente', 'vencido', 'parcial')
                AND saldo_pendiente > 0";
        
        $result = $this->db->queryOne($sql, ['id' => $colegiadoId]);
        return $result['total'] > 0;
    }

    public function getColegiadosConDeudas() {
        $sql = "SELECT DISTINCT 
                    c.idColegiados,
                    c.numero_colegiatura,
                    c.dni,
                    c.nombres,
                    c.apellido_paterno,
                    c.apellido_materno,
                    c.estado,
                    COUNT(d.idDeuda) as cantidad_deudas,
                    SUM(d.saldo_pendiente) as total_deuda
                FROM colegiados c
                INNER JOIN deudas d ON c.idColegiados = d.colegiado_id
                WHERE d.estado IN ('pendiente', 'vencido', 'parcial')
                AND d.saldo_pendiente > 0
                GROUP BY c.idColegiados
                HAVING cantidad_deudas > 0
                ORDER BY c.apellido_paterno, c.apellido_materno";
        
        return $this->db->query($sql);
    }

    // Verifica si existe programación activa para un colegiado y concepto
    public function existeProgramacionActiva($colegiadoId, $conceptoId) {
        $sql = "SELECT COUNT(*) as total 
                FROM programacion_deudas 
                WHERE colegiado_id = :colegiado_id 
                AND concepto_id = :concepto_id 
                AND estado = 'activa'";

        $result = $this->db->queryOne($sql, [
            'colegiado_id' => $colegiadoId,
            'concepto_id' => $conceptoId
        ]);

        return $result['total'] > 0;
    }

    // Obtiene programación activa
    public function getProgramacionActiva($colegiadoId, $conceptoId) {
        $sql = "SELECT * FROM programacion_deudas 
                WHERE colegiado_id = :colegiado_id 
                AND concepto_id = :concepto_id 
                AND estado = 'activa'
                LIMIT 1";

        return $this->db->queryOne($sql, [
            'colegiado_id' => $colegiadoId,
            'concepto_id' => $conceptoId
        ]);
    }

    // Crea programación de deuda recurrente
    public function crearProgramacion($data) {
        $sql = "INSERT INTO programacion_deudas (
                    colegiado_id,
                    concepto_id,
                    monto,
                    frecuencia,
                    dia_vencimiento,
                    fecha_inicio,
                    fecha_fin,
                    estado,
                    ultima_generacion,
                    proxima_generacion,
                    usuario_registro_id,
                    observaciones
                ) VALUES (
                    :colegiado_id,
                    :concepto_id,
                    :monto,
                    :frecuencia,
                    :dia_vencimiento,
                    :fecha_inicio,
                    :fecha_fin,
                    'activa',
                    :ultima_generacion,
                    :proxima_generacion,
                    :usuario_registro_id,
                    :observaciones
                )";

        return $this->db->insert($sql, $data);
    }

    // Actualiza próxima generación de programación
    public function actualizarProximaGeneracion($programacionId, $proximaFecha) {
        $sql = "UPDATE programacion_deudas 
                SET proxima_generacion = :proxima_fecha,
                    ultima_generacion = CURDATE()
                WHERE idProgramacion = :id";

        return $this->db->execute($sql, [
            'proxima_fecha' => $proximaFecha,
            'id' => $programacionId
        ]);
    }
}