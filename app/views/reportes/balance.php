<div class="reportes-view">
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-balance-scale me-2"></i>
            Balance General
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
        <form method="GET" action="<?php echo url('reportes/balance'); ?>">
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

<div id="printableArea">
    <!-- Resumen del Balance -->
    <div class="content-card mb-4">
        <h5 class="mb-4 text-center">
            <i class="fas fa-calendar-alt me-2"></i>
            Balance del Periodo: 
            <?php echo formatDate($fecha_inicio); ?> - <?php echo formatDate($fecha_fin); ?>
        </h5>
        
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card success-card">
                    <h6>Total Ingresos</h6>
                    <h3>
                        <i class="fas fa-arrow-up me-2"></i>
                        <?php echo formatMoney($total_ingresos); ?>
                    </h3>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card danger-card">
                    <h6>Total Egresos</h6>
                    <h3>
                        <i class="fas fa-arrow-down me-2"></i>
                        <?php echo formatMoney($total_egresos); ?>
                    </h3>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card <?php echo $balance >= 0 ? 'info-card' : 'warning-card'; ?>">
                    <h6>Balance Neto</h6>
                    <h3>
                        <i class="fas fa-balance-scale me-2"></i>
                        <?php echo formatMoney($balance); ?>
                    </h3>
                    <small>
                        <?php if ($balance >= 0): ?>
                            <i class="fas fa-check-circle me-1"></i> Superávit
                        <?php else: ?>
                            <i class="fas fa-exclamation-triangle me-1"></i> Déficit
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico Comparativo -->
    <div class="content-card mb-4">
        <h6 class="mb-3">
            <i class="fas fa-chart-line me-2"></i>
            Comparativo Ingresos vs Egresos
        </h6>
        <div style="height: 300px;">
            <canvas id="chartBalance"></canvas>
        </div>
    </div>

    <!-- Detalles por Método y Tipo -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="content-card">
                <h6 class="mb-3">
                    <i class="fas fa-money-bill-wave text-success me-2"></i>
                    Ingresos por Método
                </h6>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Método</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ingresos['por_metodo'])): ?>
                            <?php foreach ($ingresos['por_metodo'] as $metodo): ?>
                                <tr>
                                    <td><?php echo e($metodo['metodo']); ?></td>
                                    <td class="text-end">
                                        <strong class="text-success">
                                            <?php echo formatMoney($metodo['total']); ?>
                                        </strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted">Sin datos</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="content-card">
                <h6 class="mb-3">
                    <i class="fas fa-arrow-down text-danger me-2"></i>
                    Egresos por Tipo
                </h6>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Tipo de Gasto</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($egresos['por_tipo'])): ?>
                            <?php foreach ($egresos['por_tipo'] as $tipo): ?>
                                <tr>
                                    <td><?php echo e($tipo['tipo']); ?></td>
                                    <td class="text-end">
                                        <strong class="text-danger">
                                            <?php echo formatMoney($tipo['total']); ?>
                                        </strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="text-center text-muted">Sin datos</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
window.totalIngresos = <?php echo $total_ingresos ?? 0; ?>;
window.totalEgresos = <?php echo $total_egresos ?? 0; ?>;

window.datosMetodoPago = <?php echo json_encode($ingresos['por_metodo'] ?? []); ?>;
window.datosTipoGasto = <?php echo json_encode($egresos['por_tipo'] ?? []); ?>;
</script>

<!-- Cargar Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</div> 