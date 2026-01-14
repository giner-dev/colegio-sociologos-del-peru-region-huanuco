<!-- Encabezado de página -->
<div class="page-header">
    <h2>
        <i class="fas fa-tachometer-alt"></i>
        Escritorio
    </h2>
    <div class="page-header-date">
        <i class="fas fa-calendar me-1"></i>
        <?php echo date('d/m/Y'); ?>
    </div>
</div>

<!-- Tarjetas de estadísticas -->
<div class="stats-grid">
    <!-- Total Colegiados -->
    <div class="stat-card card-primary">
        <div class="icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="number">
            <?php echo number_format($estadisticas['total_colegiados']); ?>
        </div>
        <div class="label">Total Colegiados</div>
    </div>
    
    <!-- Habilitados -->
    <div class="stat-card card-success">
        <div class="icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="number">
            <?php echo number_format($estadisticas['colegiados_habilitados']); ?>
        </div>
        <div class="label">Habilitados</div>
    </div>
    
    <!-- Inhabilitados -->
    <div class="stat-card card-danger">
        <div class="icon">
            <i class="fas fa-user-times"></i>
        </div>
        <div class="number">
            <?php echo number_format($estadisticas['colegiados_inhabilitados']); ?>
        </div>
        <div class="label">Inhabilitados</div>
    </div>
    
    <!-- Deudores -->
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

<!-- Estadísticas Financieras -->
<div class="financial-stats">
    <div class="content-card text-center">
        <h6>Ingresos del Mes</h6>
        <h3 class="text-success">
            <i class="fas fa-arrow-up me-2"></i>
            <?php echo formatMoney($estadisticas['ingresos_mes']); ?>
        </h3>
    </div>
    
    <div class="content-card text-center">
        <h6>Egresos del Mes</h6>
        <h3 class="text-danger">
            <i class="fas fa-arrow-down me-2"></i>
            <?php echo formatMoney($estadisticas['egresos_mes']); ?>
        </h3>
    </div>
    
    <div class="content-card text-center">
        <h6>Balance del Mes</h6>
        <h3 class="<?php echo $estadisticas['balance_mes'] >= 0 ? 'text-success' : 'text-danger'; ?>">
            <i class="fas fa-balance-scale me-2"></i>
            <?php echo formatMoney($estadisticas['balance_mes']); ?>
        </h3>
    </div>
</div>

<!-- Últimos Pagos y Nuevos Colegiados -->
<div class="tables-grid">
    <!-- Últimos Pagos -->
    <div class="content-card">
        <h5>
            <i class="fas fa-money-bill-wave"></i>
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
                                    <small>
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
            <?php if (hasPermission('pagos', 'ver')): ?>
            <div class="text-end">
                <a href="<?php echo url('pagos'); ?>" class="btn btn-outline-primary btn-sm">
                    Ver todos <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-message">
                <i class="fas fa-info-circle"></i>
                <p>No hay pagos registrados</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Nuevos Colegiados -->
    <div class="content-card">
        <h5>
            <i class="fas fa-user-plus"></i>
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
            <?php if (hasPermission('colegiados', 'ver')): ?>
            <div class="text-end">
                <a href="<?php echo url('colegiados'); ?>" class="btn btn-outline-primary btn-sm">
                    Ver todos <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-message">
                <i class="fas fa-info-circle"></i>
                <p>No hay colegiados registrados</p>
            </div>
        <?php endif; ?>
    </div>
</div>