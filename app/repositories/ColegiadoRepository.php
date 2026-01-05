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
                ORDER BY apellido_paterno ASC, apellido_materno ASC 
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
            $where .= " AND (nombres LIKE :nombres OR apellido_paterno LIKE :nombres OR apellido_materno LIKE :nombres)";
            $params['nombres'] = '%' . $filtros['nombres'] . '%';
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
        $sql = "INSERT INTO colegiados (
                    numero_colegiatura, dni, nombres, apellido_paterno, apellido_materno,
                    fecha_colegiatura, telefono, correo, direccion, fecha_nacimiento,
                    estado, estado_manual, observaciones
                ) VALUES (
                    :numero_colegiatura, :dni, :nombres, :apellido_paterno, :apellido_materno,
                    :fecha_colegiatura, :telefono, :correo, :direccion, :fecha_nacimiento,
                    :estado, :estado_manual, :observaciones
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
    public function cambiarEstado($id, $nuevoEstado, $esManual = true) {
        $sql = "UPDATE colegiados SET estado = :estado, estado_manual = :manual WHERE idColegiados = :id";
        return $this->db->execute($sql, [
            'id' => $id,
            'estado' => $nuevoEstado,
            'manual' => $esManual ? 1 : 0
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
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as total FROM colegiados WHERE numero_colegiatura = :numero AND idColegiados != :id";
            $result = $this->db->queryOne($sql, ['numero' => $numero, 'id' => $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM colegiados WHERE numero_colegiatura = :numero";
            $result = $this->db->queryOne($sql, ['numero' => $numero]);
        }
        
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
    public function getHistorialEstados($idColegiado) {
        $sql = "SELECT h.*, u.nombre_usuario 
                FROM historial_estados h
                LEFT JOIN usuarios u ON h.usuario_id = u.idUsuario
                WHERE h.colegiado_id = :id
                ORDER BY h.fecha_cambio DESC";
        
        return $this->db->query($sql, ['id' => $idColegiado]);
    }

    // obtiene el historial de pagos de un colegiado
    public function getHistorialPagos($idColegiado) {
        $sql = "SELECT p.*, cp.nombre_completo as concepto_nombre, u.nombre_usuario
                FROM pagos p
                LEFT JOIN conceptos_pago cp ON p.concepto_id = cp.idConcepto
                LEFT JOIN usuarios u ON p.usuario_registro_id = u.idUsuario
                WHERE p.colegiados_id = :id
                ORDER BY p.fecha_pago DESC";
        
        return $this->db->query($sql, ['id' => $idColegiado]);
    }

    // obtener las deudas de un colegiado
    public function getDeudas($idColegiado) {
        $sql = "SELECT * FROM deudas 
                WHERE colegiado_id = :id 
                ORDER BY fecha_vencimiento ASC";
        
        return $this->db->query($sql, ['id' => $idColegiado]);
    }
}