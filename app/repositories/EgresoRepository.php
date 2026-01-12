<?php
require_once __DIR__ . '/../models/Egreso.php';

class EgresoRepository {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAllPaginated($page = 1, $perPage = 25, $filtros = []) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT e.*,
                       tg.nombre_tipo as tipo_gasto_nombre,
                       u.nombre_usuario as usuario_nombre
                FROM egresos e
                LEFT JOIN tipo_gasto tg ON e.tipo_gasto_id = tg.idTipo_Gasto
                INNER JOIN usuarios u ON e.usuario_registro_id = u.idUsuario
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND e.fecha_egreso >= :fecha_inicio";
            $params['fecha_inicio'] = $filtros['fecha_inicio'];
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND e.fecha_egreso <= :fecha_fin";
            $params['fecha_fin'] = $filtros['fecha_fin'];
        }
        
        if (!empty($filtros['tipo_gasto_id'])) {
            $sql .= " AND e.tipo_gasto_id = :tipo_gasto_id";
            $params['tipo_gasto_id'] = $filtros['tipo_gasto_id'];
        }
        
        $sql .= " ORDER BY e.fecha_egreso DESC, e.fecha_registro DESC 
                  LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $results = $this->db->query($sql, $params);
        
        $egresos = [];
        foreach ($results as $row) {
            $egresos[] = new Egreso($row);
        }
        
        return $egresos;
    }

    public function countAll($filtros = []) {
        $sql = "SELECT COUNT(*) as total
                FROM egresos e
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND e.fecha_egreso >= :fecha_inicio";
            $params['fecha_inicio'] = $filtros['fecha_inicio'];
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND e.fecha_egreso <= :fecha_fin";
            $params['fecha_fin'] = $filtros['fecha_fin'];
        }
        
        if (!empty($filtros['tipo_gasto_id'])) {
            $sql .= " AND e.tipo_gasto_id = :tipo_gasto_id";
            $params['tipo_gasto_id'] = $filtros['tipo_gasto_id'];
        }
        
        $result = $this->db->queryOne($sql, $params);
        return $result['total'];
    }

    public function findById($id) {
        $sql = "SELECT e.*,
                       tg.nombre_tipo as tipo_gasto_nombre,
                       u.nombre_usuario as usuario_nombre
                FROM egresos e
                LEFT JOIN tipo_gasto tg ON e.tipo_gasto_id = tg.idTipo_Gasto
                INNER JOIN usuarios u ON e.usuario_registro_id = u.idUsuario
                WHERE e.idEgreso = :id";
        
        $result = $this->db->queryOne($sql, ['id' => $id]);
        
        if ($result) {
            return new Egreso($result);
        }
        
        return null;
    }

    public function create($data) {
        $sql = "INSERT INTO egresos (
                    tipo_gasto_id,
                    descripcion,
                    monto,
                    fecha_egreso,
                    num_comprobante,
                    comprobante,
                    observaciones,
                    usuario_registro_id
                ) VALUES (
                    :tipo_gasto_id,
                    :descripcion,
                    :monto,
                    :fecha_egreso,
                    :num_comprobante,
                    :comprobante,
                    :observaciones,
                    :usuario_registro_id
                )";
        
        return $this->db->insert($sql, $data);
    }

    public function update($id, $data) {
        $archivoAnterior = null;
        
        if (isset($data['comprobante']) && !empty($data['comprobante'])) {
            $archivoAnterior = $this->getArchivoComprobante($id);
        }

        $sql = "UPDATE egresos 
                SET tipo_gasto_id = :tipo_gasto_id,
                    descripcion = :descripcion,
                    monto = :monto,
                    fecha_egreso = :fecha_egreso,
                    num_comprobante = :num_comprobante,
                    comprobante = :comprobante,
                    observaciones = :observaciones
                WHERE idEgreso = :id";

        $data['id'] = $id;
        $resultado = $this->db->execute($sql, $data);

        if ($resultado && $archivoAnterior) {
            FileUploadManager::deleteComprobante($archivoAnterior);
        }

        return $resultado;
    }

    public function delete($id) {
        $archivoPath = $this->getArchivoComprobante($id);
        
        $sql = "DELETE FROM egresos WHERE idEgreso = :id";
        $resultado = $this->db->execute($sql, ['id' => $id]);
        
        if ($resultado && $archivoPath) {
            FileUploadManager::deleteComprobante($archivoPath);
        }
        
        return $resultado;
    }

    public function getTiposGasto() {
        $sql = "SELECT * FROM tipo_gasto 
                WHERE estado = 'activo' 
                ORDER BY nombre_tipo ASC";
        return $this->db->query($sql);
    }

    public function getResumenPorPeriodo($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    COUNT(*) as total_egresos,
                    SUM(monto) as total_monto,
                    AVG(monto) as promedio_monto
                FROM egresos
                WHERE fecha_egreso BETWEEN :fecha_inicio AND :fecha_fin";
        
        return $this->db->queryOne($sql, [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);
    }

    public function getEgresosPorTipo($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    tg.nombre_tipo as tipo,
                    COUNT(e.idEgreso) as cantidad,
                    SUM(e.monto) as total
                FROM egresos e
                LEFT JOIN tipo_gasto tg ON e.tipo_gasto_id = tg.idTipo_Gasto
                WHERE e.fecha_egreso BETWEEN :fecha_inicio AND :fecha_fin
                GROUP BY tg.idTipo_Gasto, tg.nombre_tipo
                ORDER BY total DESC";
        
        return $this->db->query($sql, [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);
    }




    // GESTIÓN DE TIPOS DE GASTO
    public function getAllTiposGasto() {
        $sql = "SELECT * FROM tipo_gasto ORDER BY nombre_tipo ASC";
        return $this->db->query($sql);
    }

    public function findTipoGastoById($id) {
        $sql = "SELECT * FROM tipo_gasto WHERE idTipo_Gasto = :id";
        return $this->db->queryOne($sql, ['id' => $id]);
    }

    public function createTipoGasto($data) {
        $sql = "INSERT INTO tipo_gasto (
                    nombre_tipo,
                    descripcion,
                    codigo,
                    estado
                ) VALUES (
                    :nombre,
                    :descripcion,
                    :codigo,
                    :estado
                )";
        return $this->db->insert($sql, $data);
    }

    public function updateTipoGasto($id, $data) {
        $sql = "UPDATE tipo_gasto 
                SET nombre_tipo = :nombre,
                    descripcion = :descripcion,
                    codigo = :codigo,
                    estado = :estado
                WHERE idTipo_Gasto = :id";
        $data['id'] = $id;
        return $this->db->execute($sql, $data);
    }

    public function deleteTipoGasto($id) {
        $sql = "UPDATE tipo_gasto SET estado = 'inactivo' WHERE idTipo_Gasto = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    public function tipoGastoTieneEgresos($id) {
        $sql = "SELECT COUNT(*) as total FROM egresos WHERE tipo_gasto_id = :id";
        $result = $this->db->queryOne($sql, ['id' => $id]);
        return $result['total'] > 0;
    }

    //  Obtiene la ruta del comprobante de un egreso
    public function getArchivoComprobante($id) {
        $sql = "SELECT comprobante FROM egresos WHERE idEgreso = :id";
        $result = $this->db->queryOne($sql, ['id' => $id]);
        return $result['comprobante'] ?? null;
    }
}