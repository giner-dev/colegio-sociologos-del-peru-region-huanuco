<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-exclamation-triangle me-2"></i>
            Colegiados Morosos
        </h2>
        <a href="<?php echo url('deudas'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver a Deudas
        </a>
    </div>
</div>

<div class="alert alert-warning">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Nota:</strong> Se consideran morosos a los colegiados con deudas pendientes o vencidas.
</div>

<div class="card">
    <div class="card-header bg-light">
        <i class="fas fa-list me-2"></i> 
        Total de Morosos: <strong><?php echo number_format($pagination['total']); ?></strong>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>N° Colegiatura</th>
                        <th>DNI</th>
                        <th>Colegiado</th>
                        <th>Cantidad Deudas</th>
                        <th>Total Adeudado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($morosos)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-check-circle me-2"></i>
                                No hay colegiados morosos
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($morosos as $moroso): ?>
                            <tr>
                                <td><strong><?php echo e($moroso['numero_colegiatura']); ?></strong></td>
                                <td><?php echo e($moroso['dni']); ?></td>
                                <td>
                                    <?php echo e($moroso['apellido_paterno'] . ' ' . $moroso['apellido_materno'] . ', ' . $moroso['nombres']); ?>
                                </td>
                                <td>
                                    <span class="badge bg-danger">
                                        <?php echo $moroso['cantidad_deudas']; ?> deuda(s)
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-danger fs-5">
                                        <?php echo formatMoney($moroso['total_deuda']); ?>
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo url('deudas/colegiado/' . $moroso['idColegiados']); ?>" 
                                       class="btn btn-sm btn-info" title="Ver deudas">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo url('colegiados/ver/' . $moroso['idColegiados']); ?>" 
                                       class="btn btn-sm btn-primary" title="Ver colegiado">
                                        <i class="fas fa-user"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if ($pagination['totalPages'] > 1): ?>
            <nav aria-label="Paginación" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $pagination['page'] <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('deudas/morosos?page=1'); ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                    </li>
                    
                    <li class="page-item <?php echo $pagination['page'] <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('deudas/morosos?page=' . ($pagination['page'] - 1)); ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>
                    
                    <?php
                    $startPage = max(1, $pagination['page'] - 2);
                    $endPage = min($pagination['totalPages'], $pagination['page'] + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li class="page-item <?php echo $i === $pagination['page'] ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo url('deudas/morosos?page=' . $i); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('deudas/morosos?page=' . ($pagination['page'] + 1)); ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                    
                    <li class="page-item <?php echo $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('deudas/morosos?page=' . $pagination['totalPages']); ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    </li>
                </ul>
                
                <p class="text-center text-muted">
                    Página <?php echo $pagination['page']; ?> de <?php echo $pagination['totalPages']; ?>
                </p>
            </nav>
        <?php endif; ?>
    </div>
</div>