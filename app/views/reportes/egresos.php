<div class="reportes-view">
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-arrow-down me-2"></i>
            Reporte de Egresos
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
        <form method="GET" action="<?php echo url('reportes/egresos'); ?>" id="formFiltros">
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
        <div class="stat-card danger-card">
            <h6>Total de Egresos</h6>
            <h3>
                <?php echo formatMoney($resumen['total_monto'] ?? 0); ?>
            </h3>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card info-card">
            <h6>Cantidad de Egresos</h6>
            <h3>
                <?php echo number_format($resumen['total_egresos'] ?? 0); ?>
            </h3>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card warning-card">
            <h6>Promedio por Egreso</h6>
            <h3>
                <?php echo formatMoney($resumen['promedio_monto'] ?? 0); ?>
            </h3>
        </div>
    </div>
</div>

<!-- Gráfico por Tipo de Gasto -->
<div class="row g-3 mb-4">
    <div class="col-md-12">
        <div class="content-card">
            <h6 class="mb-3">
                <i class="fas fa-chart-bar me-2"></i>
                Egresos por Tipo de Gasto
            </h6>
            <div style="height: 400px;">
                <canvas id="chartTipoGasto"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Egresos -->
<div class="card">
    <div class="card-header bg-results">
        <i class="fas fa-list me-2"></i> 
        Detalle de Egresos: <strong class="result-counter"><?php echo count($egresos); ?></strong> registro(s)
    </div>
    <div class="card-body">
        <div id="printableArea">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="tablaEgresos">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Tipo de Gasto</th>
                            <th>Registrado Por</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($egresos)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No se encontraron egresos en este periodo
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($egresos as $egreso): ?>
                                <tr>
                                    <td data-label="Fecha"><?php echo formatDate($egreso['fecha_egreso']); ?></td>
                                    <td data-label="Descripción"><?php echo e($egreso['descripcion']); ?></td>
                                    <td data-label="Tipo de Gasto">
                                        <span class="badge bg-secondary">
                                            <?php echo e($egreso['tipo_gasto'] ?? 'Sin categoría'); ?>
                                        </span>
                                    </td>
                                    <td data-label="Registrado Por"><?php echo e($egreso['registrado_por']); ?></td>
                                    <td data-label="Monto" class="text-end">
                                        <strong class="text-danger">
                                            <?php echo formatMoney($egreso['monto']); ?>
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
window.datosTipoGasto = <?php echo json_encode($por_tipo ?? []); ?>;
</script>

<!-- Cargar Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</div> 