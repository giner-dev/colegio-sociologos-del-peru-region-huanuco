<?php
require_once __DIR__ . '/../../core/Controller.php';

class DashboardController extends Controller {
    public function __construct() {
        parent::__construct();
    }

    // Pagina principal del panel
    public function index() {
        $this->requireAuth();
        $estadisticas = $this->obtenerEstadisticas();
        
        // Renderizar vista
        $this->render('dashboard/index', [
            'estadisticas' => $estadisticas,
            'active_menu' => 'dashboard',
            'titulo' => 'Dashboard'
        ]);
    }

    private function obtenerEstadisticas() {
        $db = Database::getInstance();
        
        // Total de colegiados
        $totalColegiados = $db->queryOne("SELECT COUNT(*) as total FROM colegiados");
        
        // Colegiados habilitados
        $colegiadosHabilitados = $db->queryOne(
            "SELECT COUNT(*) as total FROM colegiados WHERE estado = 'habilitado'"
        );
        
        // Colegiados inhabilitados
        $colegiadosInhabilitados = $db->queryOne(
            "SELECT COUNT(*) as total FROM colegiados WHERE estado = 'inhabilitado'"
        );
        
        // Total de deudas pendientes - CORREGIDO para nueva estructura
        $deudasPendientes = $db->queryOne(
            "SELECT COUNT(*) as total, COALESCE(SUM(saldo_pendiente), 0) as monto_total 
             FROM deudas 
             WHERE estado IN ('pendiente', 'parcial', 'vencido')"
        );
        
        // Ingresos del mes actual - CORREGIDO nombre de tabla
        $ingresosMes = $db->queryOne(
            "SELECT COALESCE(SUM(monto), 0) as total 
             FROM pagos 
             WHERE MONTH(fecha_pago) = MONTH(CURRENT_DATE()) 
             AND YEAR(fecha_pago) = YEAR(CURRENT_DATE())
             AND estado != 'anulado'"
        );
        
        // Egresos del mes actual
        $egresosMes = $db->queryOne(
            "SELECT COALESCE(SUM(monto), 0) as total 
             FROM egresos 
             WHERE MONTH(fecha_egreso) = MONTH(CURRENT_DATE()) 
             AND YEAR(fecha_egreso) = YEAR(CURRENT_DATE())"
        );
        
        // Últimos pagos registrados - CORREGIDO nombre de columna y tabla
        $ultimosPagos = $db->query(
            "SELECT p.*, c.nombres, c.apellido_paterno, c.apellido_materno, c.numero_colegiatura
             FROM pagos p
             INNER JOIN colegiados c ON p.colegiado_id = c.idColegiados
             WHERE p.estado != 'anulado'
             ORDER BY p.fecha_registro DESC
             LIMIT 5"
        );
        
        // Colegiados registrados recientemente
        $nuevosColegiados = $db->query(
            "SELECT * FROM colegiados 
             ORDER BY fecha_registro DESC 
             LIMIT 5"
        );
        
        return [
            'total_colegiados' => $totalColegiados['total'] ?? 0,
            'colegiados_habilitados' => $colegiadosHabilitados['total'] ?? 0,
            'colegiados_inhabilitados' => $colegiadosInhabilitados['total'] ?? 0,
            'deudas_pendientes_cantidad' => $deudasPendientes['total'] ?? 0,
            'deudas_pendientes_monto' => $deudasPendientes['monto_total'] ?? 0,
            'ingresos_mes' => $ingresosMes['total'] ?? 0,
            'egresos_mes' => $egresosMes['total'] ?? 0,
            'balance_mes' => ($ingresosMes['total'] ?? 0) - ($egresosMes['total'] ?? 0),
            'ultimos_pagos' => $ultimosPagos,
            'nuevos_colegiados' => $nuevosColegiados
        ];
    }
}