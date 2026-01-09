<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-history me-2"></i>
            Historial de Pagos
        </h2>
        <div>
            <a href="<?php echo url('colegiados/ver/' . $colegiado->idColegiados); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver al Colegiado
            </a>
            <a href="<?php echo url('deudas/colegiado/' . $colegiado->idColegiados); ?>" class="btn btn-warning">
                <i class="fas fa-file-invoice-dollar me-1"></i> Ver Deudas
            </a>
        </div>
    </div>
</div>

<!-- Información del Colegiado -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-user me-2"></i> Información del Colegiado
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <strong>N° Colegiatura:</strong><br>
                <?php echo e($colegiado->numero_colegiatura); ?>
            </div>
            <div class="col-md-3">
                <strong>DNI:</strong><br>
                <?php echo e($colegiado->dni); ?>
            </div>
            <div class="col-md-6">
                <strong>Nombre Completo:</strong><br>
                <?php echo e($colegiado->getNombreCompleto()); ?>
            </div>
        </div>
    </div>
</div>

<!-- Resumen de Pagos -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card-stat bg-success">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <h3><?php echo number_format(array_reduce($pagos, function($carry, $pago) { 
                    return $carry + ($pago->isConfirmado() ? 1 : 0); 
                }, 0)); ?></h3>
                <p>Pagos Confirmados</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat bg-primary">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3><?php echo number_format(array_reduce($pagos, function($carry, $pago) { 
                    return $carry + ($pago->isRegistrado() ? 1 : 0); 
                }, 0)); ?></h3>
                <p>Pagos Registrados</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat bg-danger">
            <div class="stat-icon"><i class="fas fa-ban"></i></div>
            <div class="stat-info">
                <h3><?php echo number_format(array_reduce($pagos, function($carry, $pago) { 
                    return $carry + ($pago->isAnulado() ? 1 : 0); 
                }, 0)); ?></h3>
                <p>Pagos Anulados</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat bg-info">
            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-info">
                <h3><?php echo formatMoney(array_reduce($pagos, function($carry, $pago) { 
                    return $carry + ($pago->isConfirmado() ? $pago->monto : 0); 
                }, 0)); ?></h3>
                <p>Total Pagado</p>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Pagos -->
<div class="card">
    <div class="card-header bg-results">
        <i class="fas fa-list me-2"></i> 
        Historial de Pagos: <strong><?php echo count($pagos); ?></strong> registro(s)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Comprobante</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pagos)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle me-2"></i>
                                No hay pagos registrados para este colegiado
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td><?php echo formatDate($pago->fecha_pago); ?></td>
                                <td><?php echo e($pago->getConcepto()); ?></td>
                                <td><strong class="text-success"><?php echo formatMoney($pago->monto); ?></strong></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo e($pago->metodo_nombre); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($pago->numero_comprobante): ?>
                                        <code><?php echo e($pago->numero_comprobante); ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $pago->getEstadoClase(); ?>">
                                        <?php echo $pago->getEstadoTexto(); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo url('pagos/ver/' . $pago->idPago); ?>" 
                                       class="btn btn-sm btn-info" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>