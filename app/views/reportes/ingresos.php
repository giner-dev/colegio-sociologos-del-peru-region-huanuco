<div class="reportes-view">
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-arrow-up me-2"></i>
            Reporte de Ingresos
        </h2>
        <a href="<?php echo url('reportes'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header bg-search">
        <i class="fas fa-filter me-2"></i> Filtros de Periodo
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo url('reportes/ingresos'); ?>" id="formFiltros">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" 
                           value="<?php echo $fecha_inicio; ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control" 
                           value="<?php echo $fecha_fin; ?>" required>
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i> Generar
                    </button>
                    <button type="button" class="btn btn-success" onclick="exportarExcel()">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumen -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card success-card">
            <h6>Total de Ingresos</h6>
            <h3>
                <?php echo formatMoney($resumen['total_monto'] ?? 0); ?>
            </h3>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card info-card">
            <h6>Cantidad de Pagos</h6>
            <h3>
                <?php echo number_format($resumen['total_pagos'] ?? 0); ?>
            </h3>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card warning-card">
            <h6>Promedio por Pago</h6>
            <h3>
                <?php echo formatMoney($resumen['promedio_monto'] ?? 0); ?>
            </h3>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="content-card">
            <h6 class="mb-3">
                <i class="fas fa-credit-card me-2"></i>
                Ingresos por Método de Pago
            </h6>
            <canvas id="chartMetodoPago"></canvas>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="content-card">
            <h6 class="mb-3">
                <i class="fas fa-list-ul me-2"></i>
                Ingresos por Concepto
            </h6>
            <canvas id="chartConcepto"></canvas>
        </div>
    </div>
</div>

<!-- Tabla de Ingresos -->
<div class="card">
    <div class="card-header bg-results">
        <i class="fas fa-list me-2"></i> 
        Detalle de Ingresos: <strong class="result-counter"><?php echo count($ingresos); ?></strong> registro(s)
    </div>
    <div class="card-body">
        <div id="printableArea">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="tablaIngresos">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>N° Colegiatura</th>
                            <th>Colegiado</th>
                            <th>Concepto</th>
                            <th>Método</th>
                            <th>Comprobante</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ingresos)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No se encontraron ingresos en este periodo
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ingresos as $ingreso): ?>
                                <tr>
                                    <td data-label="Fecha"><?php echo formatDate($ingreso['fecha_pago']); ?></td>
                                    <td data-label="N° Colegiatura">
                                        <strong><?php echo formatNumeroColegiatura($ingreso['numero_colegiatura']); ?></strong>
                                    </td>
                                    <td data-label="Colegiado"><?php echo e($ingreso['colegiado']); ?></td>
                                    <td data-label="Concepto"><?php echo e($ingreso['concepto']); ?></td>
                                    <td data-label="Método">
                                        <span class="badge bg-info">
                                            <?php echo e($ingreso['metodo_pago']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Comprobante"><?php echo e($ingreso['numero_comprobante'] ?? '-'); ?></td>
                                    <td data-label="Monto" class="text-end">
                                        <strong class="text-success">
                                            <?php echo formatMoney($ingreso['monto']); ?>
                                        </strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
window.datosMetodoPago = <?php echo json_encode($por_metodo ?? []); ?>;

window.datosConcepto = <?php echo json_encode($por_concepto ?? []); ?>;
</script>

<!-- Cargar Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</div> <!-- Cierre reportes-view -->