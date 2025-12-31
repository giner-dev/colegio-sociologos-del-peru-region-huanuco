<style>
    .stat-card {
        border-radius: 10px;
        padding: 20px;
        color: white;
        margin-bottom: 20px;
        transition: transform 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .stat-card .icon {
        font-size: 3rem;
        opacity: 0.8;
    }
    
    .stat-card .number {
        font-size: 2rem;
        font-weight: bold;
        margin: 10px 0;
    }
    
    .stat-card .label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .card-primary { background: linear-gradient(135deg, #B91D22, #8a1519); }
    .card-success { background: linear-gradient(135deg, #28a745, #218838); }
    .card-danger { background: linear-gradient(135deg, #dc3545, #c82333); }
    .card-warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
    .card-info { background: linear-gradient(135deg, #17a2b8, #138496); }
    
    .content-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .content-card h5 {
        color: #B91D22;
        border-bottom: 2px solid #B91D22;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>

<!-- Encabezado de página -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-tachometer-alt me-2" style="color: #B91D22;"></i>
        Dashboard
    </h2>
    <div>
        <span class="text-muted">
            <i class="fas fa-calendar me-1"></i>
            <?php echo date('d/m/Y'); ?>
        </span>
    </div>
</div>

<!-- Tarjetas de estadísticas -->
<div class="row">
    <!-- Total Colegiados -->
    <div class="col-md-3">
        <div class="stat-card card-primary">
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="number">
                <?php echo number_format($estadisticas['total_colegiados']); ?>
            </div>
            <div class="label">Total Colegiados</div>
        </div>
    </div>
    
    <!-- Habilitados -->
    <div class="col-md-3">
        <div class="stat-card card-success">
            <div class="icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="number">
                <?php echo number_format($estadisticas['colegiados_habilitados']); ?>
            </div>
            <div class="label">Habilitados</div>
        </div>
    </div>
    
    <!-- Inhabilitados -->
    <div class="col-md-3">
        <div class="stat-card card-danger">
            <div class="icon">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="number">
                <?php echo number_format($estadisticas['colegiados_inhabilitados']); ?>
            </div>
            <div class="label">Inhabilitados</div>
        </div>
    </div>
    
    <!-- Deudores -->
    <div class="col-md-3">
        <div class="stat-card card-warning">
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="number">
                <?php echo number_format($estadisticas['deudas_pendientes_cantidad']); ?>
            </div>
            <div class="label">Con Deudas Pendientes</div>
        </div>
    </div>
</div>

<!-- Estadísticas Financieras -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="content-card text-center">
            <h6 class="text-muted mb-3">Ingresos del Mes</h6>
            <h3 class="text-success mb-0">
                <i class="fas fa-arrow-up me-2"></i>
                <?php echo formatMoney($estadisticas['ingresos_mes']); ?>
            </h3>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="content-card text-center">
            <h6 class="text-muted mb-3">Egresos del Mes</h6>
            <h3 class="text-danger mb-0">
                <i class="fas fa-arrow-down me-2"></i>
                <?php echo formatMoney($estadisticas['egresos_mes']); ?>
            </h3>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="content-card text-center">
            <h6 class="text-muted mb-3">Balance del Mes</h6>
            <h3 class="<?php echo $estadisticas['balance_mes'] >= 0 ? 'text-success' : 'text-danger'; ?> mb-0">
                <i class="fas fa-balance-scale me-2"></i>
                <?php echo formatMoney($estadisticas['balance_mes']); ?>
            </h3>
        </div>
    </div>
</div>

<!-- Últimos Pagos y Nuevos Colegiados -->
<div class="row mt-4">
    <!-- Últimos Pagos -->
    <div class="col-md-6">
        <div class="content-card">
            <h5>
                <i class="fas fa-money-bill-wave me-2"></i>
                Últimos Pagos Registrados
            </h5>
            
            <?php if (!empty($estadisticas['ultimos_pagos'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Colegiado</th>
                                <th>Monto</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estadisticas['ultimos_pagos'] as $pago): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($pago['numero_colegiatura']); ?></strong><br>
                                        <small class="text-muted">
                                            <?php echo e($pago['apellido_paterno'] . ' ' . $pago['apellido_materno'] . ', ' . $pago['nombres']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            <?php echo formatMoney($pago['monto']); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <small><?php echo formatDate($pago['fecha_pago']); ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end">
                    <a href="<?php echo url('pagos'); ?>" class="btn btn-sm btn-outline-primary">
                        Ver todos <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-4">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay pagos registrados
                </p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Nuevos Colegiados -->
    <div class="col-md-6">
        <div class="content-card">
            <h5>
                <i class="fas fa-user-plus me-2"></i>
                Colegiados Registrados Recientemente
            </h5>
            
            <?php if (!empty($estadisticas['nuevos_colegiados'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>N° Colegiatura</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estadisticas['nuevos_colegiados'] as $colegiado): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($colegiado['numero_colegiatura']); ?></strong>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo e($colegiado['apellido_paterno'] . ' ' . $colegiado['apellido_materno'] . ', ' . $colegiado['nombres']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($colegiado['estado'] == 'habilitado'): ?>
                                            <span class="badge bg-success">Habilitado</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inhabilitado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end">
                    <a href="<?php echo url('colegiados'); ?>" class="btn btn-sm btn-outline-primary">
                        Ver todos <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-4">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay colegiados registrados
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>