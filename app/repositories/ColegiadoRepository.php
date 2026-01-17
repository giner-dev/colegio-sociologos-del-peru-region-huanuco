<?php
require_once __DIR__ . '/../models/Colegiado.php';

class ColegiadoRepository{
    private $db;

    public function __construct(){
        $this->db = Database::getInstance();
    }

    // obtiene colegiados con paginación
    public function findAllPaginated($pagina = 1, $porPagina = 20) {
        $offset = ($pagina - 1) * $porPagina;
        
        // Obtener total de registros
        $sqlCount = "SELECT COUNT(*) as total FROM colegiados";
        $resultCount = $this->db->queryOne($sqlCount);
        $total = $resultCount['total'];
        
        // Obtener registros de la página actual
        $sql = "SELECT * FROM colegiados 
                ORDER BY idColegiados DESC 
                LIMIT :limit OFFSET :offset";
        
        $results = $this->db->query($sql, [
            'limit' => $porPagina,
            'offset' => $offset
        ]);
        
        $colegiados = [];
        foreach ($results as $row) {
            $colegiados[] = new Colegiado($row);
        }
        
        return [
            'data' => $colegiados,
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'totalPaginas' => ceil($total / $porPagina)
        ];
    }

    // buscar colegiados con paginación
    public function buscarPaginated($filtros = [], $pagina = 1, $porPagina = 20) {
        // Calcular offset
        $offset = ($pagina - 1) * $porPagina;
        
        // Construir WHERE
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($filtros['numero_colegiatura'])) {
            $where .= " AND numero_colegiatura LIKE :numero";
            $params['numero'] = '%' . $filtros['numero_colegiatura'] . '%';
        }
        
        if (!empty($filtros['dni'])) {
            $where .= " AND dni LIKE :dni";
            $params['dni'] = '%' . $filtros['dni'] . '%';
        }
        
        if (!empty($filtros['nombres'])) {
            // usar parámetros distintos para cada campo
            $where .= " AND (nombres LIKE :nombre_busqueda OR apellido_paterno LIKE :apellido1 OR apellido_materno LIKE :apellido2)";
            $params['nombre_busqueda'] = '%' . $filtros['nombres'] . '%';
            $params['apellido1'] = '%' . $filtros['nombres'] . '%';
            $params['apellido2'] = '%' . $filtros['nombres'] . '%';
        }
        
        if (!empty($filtros['estado'])) {
            $where .= " AND estado = :estado";
            $params['estado'] = $filtros['estado'];
        }
        
        // Contar total con filtros
        $sqlCount = "SELECT COUNT(*) as total FROM colegiados $where";
        $resultCount = $this->db->queryOne($sqlCount, $params);
        $total = $resultCount['total'];
        
        // Obtener registros con filtros y paginación
        $sql = "SELECT * FROM colegiados $where 
                ORDER BY apellido_paterno ASC, apellido_materno ASC 
                LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $porPagina;
        $params['offset'] = $offset;
        
        $results = $this->db->query($sql, $params);
        
        $colegiados = [];
        foreach ($results as $row) {
            $colegiados[] = new Colegiado($row);
        }
        
        return [
            'data' => $colegiados,
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'totalPaginas' => ceil($total / $porPagina)
        ];
    }

    // Obtiene todos los colegiados
    public function findAll(){
        $sql = "SELECT * FROM colegiados ORDER BY apellido_paterno ASC, apellido_materno ASC";
        $results = $this->db->query($sql);
        
        $colegiados = [];
        foreach ($results as $row) {
            $colegiados[] = new Colegiado($row);
        }
        
        return $colegiados;
    }


    // obtener ultimo númoro de colegiatura
    public function obtenerUltimoNumeroColegiatura() {
        $sql = "SELECT numero_colegiatura 
                FROM colegiados 
                ORDER BY CAST(numero_colegiatura AS UNSIGNED) DESC 
                LIMIT 1";

        $result = $this->db->queryOne($sql);
        
        if ($result && !empty($result['numero_colegiatura'])) {
            return $result['numero_colegiatura'];
        }

        return null;
    }

    public function generarNumeroColegiatura() {
        $ultimo = $this->obtenerUltimoNumeroColegiatura();
        
        if ($ultimo === null) {
            return '1';
        }

        $numero = intval($ultimo);
        $nuevoNumero = $numero + 1;

        return (string)$nuevoNumero;
    }


    // Buscar un colegiado por id
    public function findById($id) {
        $sql = "SELECT * FROM colegiados WHERE idColegiados = :id";
        $result = $this->db->queryOne($sql, ['id' => $id]);
        
        if ($result) {
            return new Colegiado($result);
        }
        
        return null;
    }

    // Buscar un colegiado por n° de colegiado
    public function findByNumeroColegiatura($numero) {
        $sql = "SELECT * FROM colegiados WHERE numero_colegiatura = :numero";
        $result = $this->db->queryOne($sql, ['numero' => $numero]);
        
        if ($result) {
            return new Colegiado($result);
        }
        
        return null;
    }

    // Buscar colegiado por dni
    public function findByDni($dni) {
        $sql = "SELECT * FROM colegiados WHERE dni = :dni";
        $result = $this->db->queryOne($sql, ['dni' => $dni]);
        
        if ($result) {
            return new Colegiado($result);
        }
        
        return null;
    }

    // Buscar colegiados por multiples filtros
    public function buscar($filtros = []) {
        $sql = "SELECT * FROM colegiados WHERE 1=1";
        $params = [];
        
        if (!empty($filtros['numero_colegiatura'])) {
            $sql .= " AND numero_colegiatura LIKE :numero";
            $params['numero'] = '%' . $filtros['numero_colegiatura'] . '%';
        }
        
        if (!empty($filtros['dni'])) {
            $sql .= " AND dni LIKE :dni";
            $params['dni'] = '%' . $filtros['dni'] . '%';
        }
        
        if (!empty($filtros['nombres'])) {
            $sql .= " AND (nombres LIKE :nombres OR apellido_paterno LIKE :nombres OR apellido_materno LIKE :nombres)";
            $params['nombres'] = '%' . $filtros['nombres'] . '%';
        }
        
        if (!empty($filtros['estado'])) {
            $sql .= " AND estado = :estado";
            $params['estado'] = $filtros['estado'];
        }
        
        $sql .= " ORDER BY apellido_paterno ASC, apellido_materno ASC";
        
        $results = $this->db->query($sql, $params);
        
        $colegiados = [];
        foreach ($results as $row) {
            $colegiados[] = new Colegiado($row);
        }
        
        return $colegiados;
    }

    // crear un nuevo colegiado
    public function create($data) {
        // Validar si el número ya existe
        if (!empty($data['numero_colegiatura'])) {
            $existeNumero = $this->existeNumeroColegiatura($data['numero_colegiatura']);
            if ($existeNumero) {
                // Buscar al colegiado con ese número
                $colegiadoExistente = $this->findByNumeroColegiatura($data['numero_colegiatura']);
                // Si existe, NO creamos duplicado
                throw new Exception("El número de colegiatura {$data['numero_colegiatura']} ya está asignado a otro colegiado");
            }
        }

        $sql = "INSERT INTO colegiados (
                    numero_colegiatura, dni, nombres, apellido_paterno, apellido_materno,
                    fecha_colegiatura, telefono, correo, direccion, fecha_nacimiento,
                    estado, observaciones
                ) VALUES (
                    :numero_colegiatura, :dni, :nombres, :apellido_paterno, :apellido_materno,
                    :fecha_colegiatura, :telefono, :correo, :direccion, :fecha_nacimiento,
                    :estado, :observaciones
                )";

        return $this->db->insert($sql, $data);
    }
    // actualizar un colegiado existente
    public function update($id, $data) {
        $sql = "UPDATE colegiados SET
                    numero_colegiatura = :numero_colegiatura,
                    dni = :dni,
                    nombres = :nombres,
                    apellido_paterno = :apellido_paterno,
                    apellido_materno = :apellido_materno,
                    fecha_colegiatura = :fecha_colegiatura,
                    telefono = :telefono,
                    correo = :correo,
                    direccion = :direccion,
                    fecha_nacimiento = :fecha_nacimiento,
                    observaciones = :observaciones
                WHERE idColegiados = :id";
        
        $data['id'] = $id;
        return $this->db->execute($sql, $data);
    }

    // cambiar el estado de un colegiado
    public function cambiarEstado($id, $nuevoEstado, $motivo = null){
        $sql = "UPDATE colegiados SET 
                estado = :estado, 
                motivo_inhabilitacion = :motivo,
                fecha_cambio_estado = NOW() 
            WHERE idColegiados = :id";
        return $this->db->execute($sql, [
                'id' => $id,
                'estado' => $nuevoEstado,
                'motivo' => $motivo ?? null
            ]);
    }

    // actualizar la foto de un colegiado
    public function updateFoto($id, $rutaFoto) {
        $sql = "UPDATE colegiados SET foto = :foto WHERE idColegiados = :id";
        return $this->db->execute($sql, ['id' => $id, 'foto' => $rutaFoto]);
    }

    // eliminar un colegiado (soft delete)
    public function delete($id) {
        $sql = "DELETE FROM colegiados WHERE idColegiados = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    // obtener colegiados habilitados
    public function findHabilitados() {
        $sql = "SELECT * FROM colegiados WHERE estado = 'habilitado' ORDER BY apellido_paterno ASC";
        $results = $this->db->query($sql);
        
        $colegiados = [];
        foreach ($results as $row) {
            $colegiados[] = new Colegiado($row);
        }
        
        return $colegiados;
    }

    // obtener colegiados inhabilitados
    public function findInhabilitados() {
        $sql = "SELECT * FROM colegiados WHERE estado = 'inhabilitado' ORDER BY apellido_paterno ASC";
        $results = $this->db->query($sql);
        
        $colegiados = [];
        foreach ($results as $row) {
            $colegiados[] = new Colegiado($row);
        }
        
        return $colegiados;
    }

    // verifica si existe un número de colegiatura
    public function existeNumeroColegiatura($numero, $excludeId = null) {
        // Aseguramos que sea un entero para la comparación
        $numeroInt = intval($numero);
        
        $params = ['numero' => $numeroInt];
        $sql = "SELECT COUNT(*) as total FROM colegiados WHERE CAST(numero_colegiatura AS UNSIGNED) = :numero";
        
        if ($excludeId) {
            $sql .= " AND idColegiados != :id";
            $params['id'] = $excludeId;
        }
    
        $result = $this->db->queryOne($sql, $params);
        return $result['total'] > 0;
    }

    // verifica si existe dni
    public function existeDni($dni, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as total FROM colegiados WHERE dni = :dni AND idColegiados != :id";
            $result = $this->db->queryOne($sql, ['dni' => $dni, 'id' => $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM colegiados WHERE dni = :dni";
            $result = $this->db->queryOne($sql, ['dni' => $dni]);
        }
        
        return $result['total'] > 0;
    }

    // cuenta total de colegiados
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM colegiados";
        $result = $this->db->queryOne($sql);
        return $result['total'];
    }

    // obtener el historial de cambios de estado
    public function getHistorialEstados($idColegiado, $limit = 10) {
        $sql = "SELECT h.*, u.nombre_usuario 
                FROM historial_estados h
                LEFT JOIN usuarios u ON h.usuario_id = u.idUsuario
                WHERE h.colegiado_id = :id
                ORDER BY h.fecha_cambio DESC
                LIMIT :limit";
        
        return $this->db->query($sql, ['id' => $idColegiado, 'limit' => $limit]);
    }

    // obtiene el historial de pagos de un colegiado
    public function getHistorialPagos($idColegiado, $limit = 20) {
        $sql = "SELECT 
                    p.idPago,
                    p.monto,
                    p.fecha_pago,
                    p.fecha_registro_pago,
                    p.numero_comprobante,
                    p.estado,
                    p.observaciones,
                    d.descripcion_deuda,
                    d.es_deuda_manual,
                    d.concepto_manual,
                    c.nombre_completo as concepto_nombre,
                    mp.nombre as metodo_pago_nombre,
                    u.nombre_usuario
                FROM pagos p
                INNER JOIN deudas d ON p.deuda_id = d.idDeuda
                LEFT JOIN conceptos_pago c ON d.concepto_id = c.idConcepto
                LEFT JOIN metodo_pago mp ON p.metodo_pago_id = mp.idMetodo
                LEFT JOIN usuarios u ON p.usuario_registro_id = u.idUsuario
                WHERE p.colegiado_id = :id
                ORDER BY p.fecha_pago DESC
                LIMIT :limit";

        return $this->db->query($sql, ['id' => $idColegiado, 'limit' => $limit]);
    }

    // obtener las deudas de un colegiado
    public function getDeudas($idColegiado, $soloActivas = true) {
        $whereEstado = $soloActivas ? "AND d.estado IN ('pendiente', 'parcial', 'vencido')" : "";
        
        $sql = "SELECT 
                    d.idDeuda,
                    d.concepto_id,
                    d.concepto_manual,
                    d.es_deuda_manual,
                    d.descripcion_deuda,
                    d.monto_esperado,
                    d.monto_pagado,
                    d.saldo_pendiente,
                    d.fecha_generacion,
                    d.fecha_vencimiento,
                    d.fecha_maxima_pago,
                    d.estado,
                    d.origen,
                    d.observaciones,
                    c.nombre_completo as concepto_nombre,
                    c.tipo_concepto
                FROM deudas d
                LEFT JOIN conceptos_pago c ON d.concepto_id = c.idConcepto
                WHERE d.colegiado_id = :id 
                $whereEstado
                ORDER BY d.fecha_vencimiento ASC";

        return $this->db->query($sql, ['id' => $idColegiado]);
    }
}