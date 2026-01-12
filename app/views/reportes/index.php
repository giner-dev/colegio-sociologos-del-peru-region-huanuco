<div class="reportes-view">
    <div class="page-header">
        <h2>
            <i class="fas fa-chart-bar me-2"></i>
            Centro de Reportes
        </h2>
        <p class="text-muted">Genera y exporta reportes del sistema</p>
    </div>

    <div class="row g-4">
        <!-- Reportes Financieros -->
        <div class="col-md-6">
            <div class="content-card">
                <h5 class="mb-3">
                    <i class="fas fa-money-bill-wave text-success me-2"></i>
                    Reportes Financieros
                </h5>
                
                <div class="list-group">
                    <a href="<?php echo url('reportes/ingresos'); ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-arrow-up text-success me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h6 class="mb-0">Reporte de Ingresos</h6>
                                <small class="text-muted">Ingresos por periodo, método de pago y concepto</small>
                            </div>
                        </div>
                    </a>
                    
                    <a href="<?php echo url('reportes/egresos'); ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-arrow-down text-danger me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h6 class="mb-0">Reporte de Egresos</h6>
                                <small class="text-muted">Egresos por periodo y tipo de gasto</small>
                            </div>
                        </div>
                    </a>
                    
                    <a href="<?php echo url('reportes/balance'); ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-balance-scale text-primary me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h6 class="mb-0">Balance General</h6>
                                <small class="text-muted">Comparativo de ingresos vs egresos</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Reportes de Colegiados -->
        <div class="col-md-6">
            <div class="content-card">
                <h5 class="mb-3">
                    <i class="fas fa-users text-primary me-2"></i>
                    Reportes de Colegiados
                </h5>
                
                <div class="list-group">
                    <a href="<?php echo url('reportes/habilitados'); ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-check text-success me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h6 class="mb-0">Colegiados Habilitados</h6>
                                <small class="text-muted">Listado de colegiados activos</small>
                            </div>
                        </div>
                    </a>
                    
                    <a href="<?php echo url('reportes/inhabilitados'); ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-times text-danger me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h6 class="mb-0">Colegiados Inhabilitados</h6>
                                <small class="text-muted">Listado de colegiados inactivos</small>
                            </div>
                        </div>
                    </a>
                    
                    <a href="<?php echo url('reportes/morosos'); ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle text-warning me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h6 class="mb-0">Colegiados Morosos</h6>
                                <small class="text-muted">Colegiados con deudas pendientes</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>