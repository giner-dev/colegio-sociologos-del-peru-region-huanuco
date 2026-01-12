<div class="reportes-view">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2>
                <i class="fas fa-exclamation-triangle me-2"></i>
                Colegiados Morosos
            </h2>
            <div>
                <button type="button" class="btn btn-success" onclick="exportarExcel()">
                    <i class="fas fa-file-excel me-1"></i> Exportar Excel
                </button>
                <a href="<?php echo url('reportes'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Resumen -->
    <div class="content-card mb-4">
        <div class="row text-center">
            <div class="col-md-6">
                <h4 class="text-warning">
                    <i class="fas fa-users me-2"></i>
                    Total de Morosos: <strong><?php echo number_format($total); ?></strong>
                </h4>
            </div>
            <div class="col-md-6">
                <h4 class="text-danger">
                    <i class="fas fa-dollar-sign me-2"></i>
                    Deuda Total: 
                    <strong>
                        <?php 
                        $deudaTotal = 0;
                        foreach ($morosos as $moroso) {
                            $deudaTotal += $moroso['total_deuda'];
                        }
                        echo formatMoney($deudaTotal); 
                        ?>
                    </strong>
                </h4>
            </div>
        </div>
        <p class="text-center text-muted mb-0 mt-2">
            Fecha de reporte: <?php echo date('d/m/Y H:i'); ?>
        </p>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-header bg-results">
            <i class="fas fa-list me-2"></i> 
            Listado de Colegiados con Deudas Pendientes
        </div>
        <div class="card-body">
            <div id="printableArea">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="tablaMorosos">
                        <thead class="table-dark">
                            <tr>
                                <th>N° Colegiatura</th>
                                <th>DNI</th>
                                <th>Nombre Completo</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th class="text-center">Cant. Deudas</th>
                                <th class="text-end">Total Deuda</th>
                                <th>Deuda Más Antigua</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($morosos)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No hay colegiados con deudas pendientes
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($morosos as $moroso): ?>
                                    <tr>
                                        <td data-label="N° Colegiatura">
                                            <strong class="text-warning">
                                                <?php echo formatNumeroColegiatura($moroso['numero_colegiatura']); ?>
                                            </strong>
                                        </td>
                                        <td data-label="DNI"><?php echo e($moroso['dni']); ?></td>
                                        <td data-label="Nombre Completo"><?php echo e($moroso['nombre_completo']); ?></td>
                                        <td data-label="Correo"><?php echo e($moroso['correo'] ?? '-'); ?></td>
                                        <td data-label="Teléfono"><?php echo e($moroso['telefono'] ?? '-'); ?></td>
                                        <td data-label="Cant. Deudas" class="text-center">
                                            <span class="badge bg-warning">
                                                <?php echo $moroso['cantidad_deudas']; ?>
                                            </span>
                                        </td>
                                        <td data-label="Total Deuda" class="text-end">
                                            <strong class="text-danger">
                                                <?php echo formatMoney($moroso['total_deuda']); ?>
                                            </strong>
                                        </td>
                                        <td data-label="Deuda Más Antigua">
                                            <?php 
                                            $diasVencidos = (strtotime('now') - strtotime($moroso['deuda_mas_antigua'])) / (60 * 60 * 24);
                                            ?>
                                            <small>
                                                <?php echo formatDate($moroso['deuda_mas_antigua']); ?>
                                                <br>
                                                <span class="text-danger">
                                                    (<?php echo floor($diasVencidos); ?> días)
                                                </span>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Paginación -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo url('reportes/morosos?page=1'); ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo url('reportes/morosos?page=' . ($page - 1)); ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                        
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo url('reportes/morosos?page=' . $i); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo url('reportes/morosos?page=' . ($page + 1)); ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo url('reportes/morosos?page=' . $totalPages); ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    </ul>
                    
                    <p class="text-center text-muted">
                        Página <?php echo $page; ?> de <?php echo $totalPages; ?>
                    </p>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>