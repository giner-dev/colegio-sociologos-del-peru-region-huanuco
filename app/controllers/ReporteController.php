<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/ReporteService.php';

class ReporteController extends Controller {
    private $reporteService;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->reporteService = new ReporteService();
    }

    public function index() {
        $this->requirePermission('reportes', 'ver');
        
        $this->render('reportes/index', [
            'active_menu' => 'reportes',
            'titulo' => 'Centro de Reportes'
        ]);
    }

    // REPORTE DE INGRESOS
    public function ingresos() {
        $this->requirePermission('reportes', 'ver');
        
        $fechaInicio = $this->getQuery('fecha_inicio', date('Y-m-01'));
        $fechaFin = $this->getQuery('fecha_fin', date('Y-m-d'));
        
        $datos = $this->reporteService->obtenerReporteIngresos($fechaInicio, $fechaFin);
        
        $this->render('reportes/ingresos', array_merge($datos, [
            'active_menu' => 'reportes',
            'titulo' => 'Reporte de Ingresos'
        ]));
    }

    // REPORTE DE EGRESOS
    public function egresos() {
        $this->requirePermission('reportes', 'ver');
        
        $fechaInicio = $this->getQuery('fecha_inicio', date('Y-m-01'));
        $fechaFin = $this->getQuery('fecha_fin', date('Y-m-d'));
        
        $datos = $this->reporteService->obtenerReporteEgresos($fechaInicio, $fechaFin);
        
        $this->render('reportes/egresos', array_merge($datos, [
            'active_menu' => 'reportes',
            'titulo' => 'Reporte de Egresos'
        ]));
    }

    // REPORTE DE BALANCE
    public function balance() {
        $this->requirePermission('reportes', 'ver');
        
        $fechaInicio = $this->getQuery('fecha_inicio', date('Y-m-01'));
        $fechaFin = $this->getQuery('fecha_fin', date('Y-m-d'));
        
        $datos = $this->reporteService->obtenerReporteBalance($fechaInicio, $fechaFin);
        
        $this->render('reportes/balance', array_merge($datos, [
            'active_menu' => 'reportes',
            'titulo' => 'Balance General'
        ]));
    }

    // REPORTE DE HABILITADOS
    public function habilitados() {
        $this->requirePermission('reportes', 'ver');
        
        $page = (int)($this->getQuery('page') ?? 1);
        $perPage = 50;
        
        $datos = $this->reporteService->obtenerReporteHabilitados($page, $perPage);
        
        $this->render('reportes/habilitados', array_merge($datos, [
            'active_menu' => 'reportes',
            'titulo' => 'Colegiados Habilitados'
        ]));
    }

    // REPORTE DE INHABILITADOS
    public function inhabilitados() {
        $this->requirePermission('reportes', 'ver');
        
        $page = (int)($this->getQuery('page') ?? 1);
        $perPage = 50;
        $filtroEstado = $this->getQuery('estado');
        
        $datos = $this->reporteService->obtenerReporteInhabilitados($page, $perPage, $filtroEstado);
        
        $this->render('reportes/inhabilitados', array_merge($datos, [
            'active_menu' => 'reportes',
            'titulo' => 'Colegiados Inhabilitados / Inactivos por Cese'
        ]));
    }

    // REPORTE DE MOROSOS
    public function morosos() {
        $this->requirePermission('reportes', 'ver');
        
        $page = (int)($this->getQuery('page') ?? 1);
        $perPage = 50;
        
        $datos = $this->reporteService->obtenerReporteMorosos($page, $perPage);
        
        $this->render('reportes/morosos', array_merge($datos, [
            'active_menu' => 'reportes',
            'titulo' => 'Colegiados Morosos'
        ]));
    }

    // EXPORTAR A EXCEL
    public function exportarExcel() {
        $this->requirePermission('reportes', 'ver');

        $tipo = $this->getQuery('tipo');
        $fechaInicio = $this->getQuery('fecha_inicio');
        $fechaFin = $this->getQuery('fecha_fin');
        $filtroEstado = $this->getQuery('estado');

        try {
            $filename = null;

            switch ($tipo) {
                case 'ingresos':
                    $filename = $this->reporteService->exportarIngresosExcel($fechaInicio, $fechaFin);
                    break;
                case 'egresos':
                    $filename = $this->reporteService->exportarEgresosExcel($fechaInicio, $fechaFin);
                    break;
                case 'balance':
                    $filename = $this->reporteService->exportarBalanceExcel($fechaInicio, $fechaFin);
                    break;
                case 'habilitados':
                    $filename = $this->reporteService->exportarHabilitadosExcel();
                    break;
                case 'inhabilitados':
                    // Pasar filtro de estado al exportar
                    $filename = $this->reporteService->exportarInhabilitadosExcel($filtroEstado);
                    break;
                case 'morosos':
                    $filename = $this->reporteService->exportarMorososExcel();
                    break;
                default:
                    throw new Exception('Tipo de reporte no válido');
            }
                
            if ($filename) {
                $filepath = basePath('public/temp/' . $filename);
                
                if (file_exists($filepath)) {
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('Content-Length: ' . filesize($filepath));
                    header('Cache-Control: max-age=0');

                    readfile($filepath);

                    unlink($filepath);
                    exit();
                }
            }
                
            throw new Exception('Error al generar el archivo Excel');
                
        } catch (Exception $e) {
            logMessage("Error al exportar Excel: " . $e->getMessage(), 'error');
            $this->setError('Error al generar el reporte: ' . $e->getMessage());
            $this->back();
        }
    }
}