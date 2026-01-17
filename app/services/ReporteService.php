<?php
require_once __DIR__ . '/../repositories/PagoRepository.php';
require_once __DIR__ . '/../repositories/DeudaRepository.php';
require_once __DIR__ . '/../repositories/ColegiadoRepository.php';
require_once __DIR__ . '/../repositories/EgresoRepository.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReporteService {
    private $db;
    private $pagoRepository;
    private $deudaRepository;
    private $colegiadoRepository;
    private $egresoRepository;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->pagoRepository = new PagoRepository();
        $this->deudaRepository = new DeudaRepository();
        $this->colegiadoRepository = new ColegiadoRepository();
        $this->egresoRepository = new EgresoRepository();
    }

    // REPORTE DE INGRESOS
    public function obtenerReporteIngresos($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    p.fecha_pago,
                    p.monto,
                    c.numero_colegiatura,
                    CONCAT(c.apellido_paterno, ' ', c.apellido_materno, ', ', c.nombres) as colegiado,
                    CASE 
                        WHEN d.es_deuda_manual = 1 THEN d.concepto_manual
                        ELSE COALESCE(cp.nombre_completo, 'Sin concepto')
                    END as concepto,
                    mp.nombre as metodo_pago,
                    p.numero_comprobante,
                    p.estado
                FROM pagos p
                INNER JOIN colegiados c ON p.colegiado_id = c.idColegiados
                INNER JOIN deudas d ON p.deuda_id = d.idDeuda
                LEFT JOIN conceptos_pago cp ON d.concepto_id = cp.idConcepto
                INNER JOIN metodo_pago mp ON p.metodo_pago_id = mp.idMetodo
                WHERE p.fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
                AND p.estado = 'confirmado'
                ORDER BY p.fecha_pago DESC";
        
        $ingresos = $this->db->query($sql, [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);
        
        $resumen = $this->pagoRepository->getResumenIngresos($fechaInicio, $fechaFin);
        $porMetodo = $this->pagoRepository->getIngresosPorMetodo($fechaInicio, $fechaFin);
        $porConcepto = $this->pagoRepository->getIngresosPorConcepto($fechaInicio, $fechaFin);
        
        return [
            'ingresos' => $ingresos,
            'resumen' => $resumen,
            'por_metodo' => $porMetodo,
            'por_concepto' => $porConcepto,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];
    }

    // REPORTE DE EGRESOS
    public function obtenerReporteEgresos($fechaInicio, $fechaFin) {
        $sql = "SELECT 
                    e.fecha_egreso,
                    e.descripcion,
                    e.monto,
                    tg.nombre_tipo as tipo_gasto,
                    u.nombre_usuario as registrado_por
                FROM egresos e
                LEFT JOIN tipo_gasto tg ON e.tipo_gasto_id = tg.idTipo_Gasto
                INNER JOIN usuarios u ON e.usuario_registro_id = u.idUsuario
                WHERE e.fecha_egreso BETWEEN :fecha_inicio AND :fecha_fin
                ORDER BY e.fecha_egreso DESC";
        
        $egresos = $this->db->query($sql, [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);
        
        $resumen = $this->egresoRepository->getResumenPorPeriodo($fechaInicio, $fechaFin);
        $porTipo = $this->egresoRepository->getEgresosPorTipo($fechaInicio, $fechaFin);
        
        return [
            'egresos' => $egresos,
            'resumen' => $resumen,
            'por_tipo' => $porTipo,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];
    }

    // REPORTE DE BALANCE
    public function obtenerReporteBalance($fechaInicio, $fechaFin) {
        $ingresos = $this->obtenerReporteIngresos($fechaInicio, $fechaFin);
        $egresos = $this->obtenerReporteEgresos($fechaInicio, $fechaFin);
        
        $totalIngresos = $ingresos['resumen']['total_monto'] ?? 0;
        $totalEgresos = $egresos['resumen']['total_monto'] ?? 0;
        $balance = $totalIngresos - $totalEgresos;
        
        return [
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
            'balance' => $balance,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];
    }

    // REPORTE DE HABILITADOS
    public function obtenerReporteHabilitados($page = 1, $perPage = 50) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT 
                    numero_colegiatura,
                    dni,
                    CONCAT(apellido_paterno, ' ', apellido_materno, ', ', nombres) as nombre_completo,
                    correo,
                    telefono,
                    fecha_colegiatura
                FROM colegiados
                WHERE estado = 'habilitado'
                ORDER BY apellido_paterno, apellido_materno
                LIMIT :limit OFFSET :offset";
        
        $habilitados = $this->db->query($sql, [
            'limit' => $perPage,
            'offset' => $offset
        ]);
        
        $sqlCount = "SELECT COUNT(*) as total FROM colegiados WHERE estado = 'habilitado'";
        $total = $this->db->queryOne($sqlCount);
        
        return [
            'habilitados' => $habilitados,
            'total' => $total['total'],
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total['total'] / $perPage)
        ];
    }

    // REPORTE DE INHABILITADOS
    public function obtenerReporteInhabilitados($page = 1, $perPage = 50) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT 
                    numero_colegiatura,
                    dni,
                    CONCAT(apellido_paterno, ' ', apellido_materno, ', ', nombres) as nombre_completo,
                    correo,
                    telefono,
                    fecha_colegiatura,
                    motivo_inhabilitacion,
                    fecha_cambio_estado
                FROM colegiados
                WHERE estado = 'inhabilitado'
                ORDER BY fecha_cambio_estado DESC
                LIMIT :limit OFFSET :offset";
        
        $inhabilitados = $this->db->query($sql, [
            'limit' => $perPage,
            'offset' => $offset
        ]);
        
        $sqlCount = "SELECT COUNT(*) as total FROM colegiados WHERE estado = 'inhabilitado'";
        $total = $this->db->queryOne($sqlCount);
        
        return [
            'inhabilitados' => $inhabilitados,
            'total' => $total['total'],
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total['total'] / $perPage)
        ];
    }

    // REPORTE DE MOROSOS
    public function obtenerReporteMorosos($page = 1, $perPage = 50) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT 
                    c.numero_colegiatura,
                    c.dni,
                    CONCAT(c.apellido_paterno, ' ', c.apellido_materno, ', ', c.nombres) as nombre_completo,
                    c.correo,
                    c.telefono,
                    COUNT(d.idDeuda) as cantidad_deudas,
                    SUM(d.saldo_pendiente) as total_deuda,
                    MIN(d.fecha_vencimiento) as deuda_mas_antigua
                FROM colegiados c
                INNER JOIN deudas d ON c.idColegiados = d.colegiado_id
                WHERE d.estado IN ('pendiente', 'vencido', 'parcial')
                AND d.saldo_pendiente > 0
                GROUP BY c.idColegiados
                ORDER BY total_deuda DESC
                LIMIT :limit OFFSET :offset";
        
        $morosos = $this->db->query($sql, [
            'limit' => $perPage,
            'offset' => $offset
        ]);
        
        $total = $this->deudaRepository->countMorosos();
        
        return [
            'morosos' => $morosos,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    // EXPORTAR A EXCEL - INGRESOS
    public function exportarIngresosExcel($fechaInicio, $fechaFin) {
        $datos = $this->obtenerReporteIngresos($fechaInicio, $fechaFin);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Ingresos');
        
        // Encabezado
        $sheet->setCellValue('A1', 'REPORTE DE INGRESOS');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'Periodo: ' . date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)));
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Resumen
        $sheet->setCellValue('A4', 'RESUMEN');
        $sheet->setCellValue('A5', 'Total de Ingresos:');
        $sheet->setCellValue('B5', 'S/ ' . number_format($datos['resumen']['total_monto'] ?? 0, 2));
        $sheet->setCellValue('A6', 'Cantidad de Pagos:');
        $sheet->setCellValue('B6', $datos['resumen']['total_pagos'] ?? 0);
        $sheet->setCellValue('A7', 'Promedio por Pago:');
        $sheet->setCellValue('B7', 'S/ ' . number_format($datos['resumen']['promedio_monto'] ?? 0, 2));
        
        $sheet->getStyle('A5:B7')->getFont()->setBold(true);
        
        // Columnas de datos
        $row = 9;
        $headers = ['Fecha', 'N° Colegiatura', 'Colegiado', 'Concepto', 'Método', 'Comprobante', 'Monto', 'Estado'];
        $sheet->fromArray($headers, null, 'A' . $row);
        
        $headerStyle = $sheet->getStyle('A' . $row . ':H' . $row);
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B91D22');
        $headerStyle->getFont()->getColor()->setRGB('FFFFFF');
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Datos
        $row++;
        foreach ($datos['ingresos'] as $ingreso) {
            $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($ingreso['fecha_pago'])));
            $sheet->setCellValue('B' . $row, $ingreso['numero_colegiatura']);
            $sheet->setCellValue('C' . $row, $ingreso['colegiado']);
            $sheet->setCellValue('D' . $row, $ingreso['concepto']);
            $sheet->setCellValue('E' . $row, $ingreso['metodo_pago']);
            $sheet->setCellValue('F' . $row, $ingreso['numero_comprobante'] ?? '-');
            $sheet->setCellValue('G' . $row, 'S/ ' . number_format($ingreso['monto'], 2));
            $sheet->setCellValue('H' . $row, ucfirst($ingreso['estado']));
            $row++;
        }
        
        // Autoajustar columnas
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Bordes
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A9:H' . ($row - 1))->applyFromArray($styleArray);
        
        $filename = 'reporte_ingresos_' . date('Ymd_His') . '.xlsx';
        $filepath = basePath('public/temp/' . $filename);
        
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filename;
    }

    // EXPORTAR A EXCEL - EGRESOS
    public function exportarEgresosExcel($fechaInicio, $fechaFin) {
        $datos = $this->obtenerReporteEgresos($fechaInicio, $fechaFin);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Egresos');
        
        // Encabezado
        $sheet->setCellValue('A1', 'REPORTE DE EGRESOS');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'Periodo: ' . date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)));
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Resumen
        $sheet->setCellValue('A4', 'RESUMEN');
        $sheet->setCellValue('A5', 'Total de Egresos:');
        $sheet->setCellValue('B5', 'S/ ' . number_format($datos['resumen']['total_monto'] ?? 0, 2));
        $sheet->setCellValue('A6', 'Cantidad de Egresos:');
        $sheet->setCellValue('B6', $datos['resumen']['total_egresos'] ?? 0);
        
        $sheet->getStyle('A5:B6')->getFont()->setBold(true);
        
        // Columnas de datos
        $row = 8;
        $headers = ['Fecha', 'Descripción', 'Tipo de Gasto', 'Monto', 'Registrado por'];
        $sheet->fromArray($headers, null, 'A' . $row);
        
        $headerStyle = $sheet->getStyle('A' . $row . ':E' . $row);
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B91D22');
        $headerStyle->getFont()->getColor()->setRGB('FFFFFF');
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Datos
        $row++;
        foreach ($datos['egresos'] as $egreso) {
            $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($egreso['fecha_egreso'])));
            $sheet->setCellValue('B' . $row, $egreso['descripcion']);
            $sheet->setCellValue('C' . $row, $egreso['tipo_gasto'] ?? 'Sin categoría');
            $sheet->setCellValue('D' . $row, 'S/ ' . number_format($egreso['monto'], 2));
            $sheet->setCellValue('E' . $row, $egreso['registrado_por']);
            $row++;
        }
        
        // Autoajustar columnas
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Bordes
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A8:E' . ($row - 1))->applyFromArray($styleArray);
        
        $filename = 'reporte_egresos_' . date('Ymd_His') . '.xlsx';
        $filepath = basePath('public/temp/' . $filename);
        
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filename;
    }

    // EXPORTAR A EXCEL - BALANCE
    public function exportarBalanceExcel($fechaInicio, $fechaFin) {
        $datos = $this->obtenerReporteBalance($fechaInicio, $fechaFin);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Balance');
        
        // Encabezado
        $sheet->setCellValue('A1', 'REPORTE DE BALANCE GENERAL');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'Periodo: ' . date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)));
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Resumen
        $row = 4;
        $sheet->setCellValue('A' . $row, 'CONCEPTO');
        $sheet->setCellValue('B' . $row, 'MONTO');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Ingresos');
        $sheet->setCellValue('B' . $row, 'S/ ' . number_format($datos['total_ingresos'], 2));
        $sheet->getStyle('B' . $row)->getFont()->getColor()->setRGB('28a745');
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'Total Egresos');
        $sheet->setCellValue('B' . $row, 'S/ ' . number_format($datos['total_egresos'], 2));
        $sheet->getStyle('B' . $row)->getFont()->getColor()->setRGB('dc3545');
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        
        $row++;
        $sheet->setCellValue('A' . $row, 'BALANCE');
        $sheet->setCellValue('B' . $row, 'S/ ' . number_format($datos['balance'], 2));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true)->setSize(14);
        
        if ($datos['balance'] >= 0) {
            $sheet->getStyle('B' . $row)->getFont()->getColor()->setRGB('28a745');
        } else {
            $sheet->getStyle('B' . $row)->getFont()->getColor()->setRGB('dc3545');
        }
        
        // Autoajustar columnas
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        
        $filename = 'reporte_balance_' . date('Ymd_His') . '.xlsx';
        $filepath = basePath('public/temp/' . $filename);
        
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filename;
    }

    // EXPORTAR A EXCEL - HABILITADOS
    public function exportarHabilitadosExcel() {
        $sql = "SELECT 
                    numero_colegiatura,
                    dni,
                    CONCAT(apellido_paterno, ' ', apellido_materno, ', ', nombres) as nombre_completo,
                    correo,
                    telefono,
                    fecha_colegiatura
                FROM colegiados
                WHERE estado = 'habilitado'
                ORDER BY apellido_paterno, apellido_materno";
        
        $habilitados = $this->db->query($sql);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Habilitados');
        
        $sheet->setCellValue('A1', 'COLEGIADOS HABILITADOS');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'Fecha: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row = 4;
        $headers = ['N° Colegiatura', 'DNI', 'Nombre Completo', 'Correo', 'Teléfono', 'Fecha Colegiatura'];
        $sheet->fromArray($headers, null, 'A' . $row);
        
        $headerStyle = $sheet->getStyle('A' . $row . ':F' . $row);
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B91D22');
        $headerStyle->getFont()->getColor()->setRGB('FFFFFF');
        
        $row++;
        foreach ($habilitados as $colegiado) {
            $sheet->setCellValue('A' . $row, $colegiado['numero_colegiatura']);
            $sheet->setCellValue('B' . $row, $colegiado['dni']);
            $sheet->setCellValue('C' . $row, $colegiado['nombre_completo']);
            $sheet->setCellValue('D' . $row, $colegiado['correo'] ?? '-');
            $sheet->setCellValue('E' . $row, $colegiado['telefono'] ?? '-');
            $sheet->setCellValue('F' . $row, date('d/m/Y', strtotime($colegiado['fecha_colegiatura'])));
            $row++;
        }
        
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'colegiados_habilitados_' . date('Ymd_His') . '.xlsx';
        $filepath = basePath('public/temp/' . $filename);
        
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filename;
    }

    // EXPORTAR A EXCEL - INHABILITADOS
    public function exportarInhabilitadosExcel() {
        $sql = "SELECT 
                    numero_colegiatura,
                    dni,
                    CONCAT(apellido_paterno, ' ', apellido_materno, ', ', nombres) as nombre_completo,
                    correo,
                    telefono,
                    fecha_colegiatura,
                    motivo_inhabilitacion,
                    fecha_cambio_estado
                FROM colegiados
                WHERE estado = 'inhabilitado'
                ORDER BY fecha_cambio_estado DESC";
        
        $inhabilitados = $this->db->query($sql);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Inhabilitados');
        
        $sheet->setCellValue('A1', 'COLEGIADOS INHABILITADOS');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row = 3;
        $headers = ['N° Colegiatura', 'DNI', 'Nombre Completo', 'Correo', 'Teléfono', 'Fecha Colegiatura', 'Motivo', 'Fecha Cambio'];
        $sheet->fromArray($headers, null, 'A' . $row);
        
        $headerStyle = $sheet->getStyle('A' . $row . ':H' . $row);
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B91D22');
        $headerStyle->getFont()->getColor()->setRGB('FFFFFF');
        
        $row++;
        foreach ($inhabilitados as $colegiado) {
            $sheet->setCellValue('A' . $row, $colegiado['numero_colegiatura']);
            $sheet->setCellValue('B' . $row, $colegiado['dni']);
            $sheet->setCellValue('C' . $row, $colegiado['nombre_completo']);
            $sheet->setCellValue('D' . $row, $colegiado['correo'] ?? '-');
            $sheet->setCellValue('E' . $row, $colegiado['telefono'] ?? '-');
            $sheet->setCellValue('F' . $row, date('d/m/Y', strtotime($colegiado['fecha_colegiatura'])));
            $sheet->setCellValue('G' . $row, $colegiado['motivo_inhabilitacion'] ?? '-');
            $sheet->setCellValue('H' . $row, $colegiado['fecha_cambio_estado'] ? date('d/m/Y', strtotime($colegiado['fecha_cambio_estado'])) : '-');
            $row++;
        }
        
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'colegiados_inhabilitados_' . date('Ymd_His') . '.xlsx';
        $filepath = basePath('public/temp/' . $filename);
        
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filename;
    }

    // EXPORTAR A EXCEL - MOROSOS
    public function exportarMorososExcel() {
        $sql = "SELECT 
                    c.numero_colegiatura,
                    c.dni,
                    CONCAT(c.apellido_paterno, ' ', c.apellido_materno, ', ', c.nombres) as nombre_completo,
                    c.correo,
                    c.telefono,
                    COUNT(d.idDeuda) as cantidad_deudas,
                    SUM(d.saldo_pendiente) as total_deuda,
                    MIN(d.fecha_vencimiento) as deuda_mas_antigua
                FROM colegiados c
                INNER JOIN deudas d ON c.idColegiados = d.colegiado_id
                WHERE d.estado IN ('pendiente', 'vencido', 'parcial')
                AND d.saldo_pendiente > 0
                GROUP BY c.idColegiados
                ORDER BY total_deuda DESC";
        
        $morosos = $this->db->query($sql);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Morosos');
        
        // Encabezado
        $sheet->setCellValue('A1', 'COLEGIADOS MOROSOS');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Cabeceras
        $row = 3;
        $headers = [
            'N° Colegiatura',
            'DNI',
            'Nombre Completo',
            'Correo',
            'Teléfono',
            'Cant. Deudas',
            'Total Deuda',
            'Deuda Más Antigua'
        ];
        $sheet->fromArray($headers, null, 'A' . $row);
        
        $headerStyle = $sheet->getStyle('A' . $row . ':H' . $row);
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B91D22');
        $headerStyle->getFont()->getColor()->setRGB('FFFFFF');
        
        // Datos
        $row++;
        foreach ($morosos as $moroso) {
            $sheet->setCellValue('A' . $row, $moroso['numero_colegiatura']);
            $sheet->setCellValue('B' . $row, $moroso['dni']);
            $sheet->setCellValue('C' . $row, $moroso['nombre_completo']);
            $sheet->setCellValue('D' . $row, $moroso['correo'] ?? '-');
            $sheet->setCellValue('E' . $row, $moroso['telefono'] ?? '-');
            $sheet->setCellValue('F' . $row, $moroso['cantidad_deudas']);
            $sheet->setCellValue('G' . $row, 'S/ ' . number_format($moroso['total_deuda'], 2));
            $sheet->setCellValue('H' . $row, date('d/m/Y', strtotime($moroso['deuda_mas_antigua'])));
            $row++;
        }
        
        // Autoajustar columnas
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'colegiados_morosos_' . date('Ymd_His') . '.xlsx';
        $filepath = basePath('public/temp/' . $filename);
        
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filename;
    }
}