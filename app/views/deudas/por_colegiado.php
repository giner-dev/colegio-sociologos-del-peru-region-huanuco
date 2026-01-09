<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-file-invoice-dollar me-2"></i>
            Deudas del Colegiado
        </h2>
        <a href="<?php echo url('colegiados/ver/' . $colegiado->idColegiados); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver al Colegiado
        </a>
    </div>
</div>

<!-- Info del Colegiado -->
<div class="card mb-4">
    <div class="card-header-red">
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

<!-- Resumen de Deuda -->
<div class="card mb-4 border-danger">
    <div class="card-body text-center">
        <h5 class="text-danger">DEUDA TOTAL PENDIENTE</h5>
        <h2 class="text-danger mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo formatMoney($total); ?>
        </h2>
    </div>
</div>

<!-- Tabla de Deudas -->
<div class="card">
    <div class="card-header bg-light">
        <i class="fas fa-list me-2"></i> 
        Detalle de Deudas: <strong><?php echo count($deudas); ?></strong> registro(s)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Concepto</th>
                        <th>Monto Esperado</th>
                        <th>Pagado</th>
                        <th>Saldo</th>
                        <th>Fecha Vencimiento</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($deudas)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-check-circle me-2"></i>
                                No tiene deudas registradas
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($deudas as $deuda): ?>
                            <tr data-estado="<?php echo $deuda->estado; ?>">
                                <td><?php echo e($deuda->concepto_nombre ?? $deuda->descripcion_deuda); ?></td>
                                <td><strong class="text-danger"><?php echo formatMoney($deuda->monto_esperado); ?></strong></td>
                                <td class="text-success"><?php echo formatMoney($deuda->monto_pagado); ?></td>
                                <td>
                                    <strong class="<?php echo $deuda->getSaldoPendiente() > 0 ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo formatMoney($deuda->getSaldoPendiente()); ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php echo formatDate($deuda->fecha_vencimiento); ?>
                                    <?php 
                                    $dias = $deuda->getDiasVencimiento();
                                    if ($dias !== null && ($deuda->isPendiente() || $deuda->isVencida())):
                                        if ($dias < 0):
                                    ?>
                                        <br><small class="text-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Vencido hace <?php echo abs($dias); ?> día(s)
                                        </small>
                                    <?php elseif ($dias <= 7): ?>
                                        <br><small class="text-warning">
                                            <i class="fas fa-clock me-1"></i>
                                            Vence en <?php echo $dias; ?> día(s)
                                        </small>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($deuda->isPendiente()): ?>
                                        <span class="badge bg-warning">Pendiente</span>
                                    <?php elseif ($deuda->isParcial()): ?>
                                        <span class="badge bg-info">Pago Parcial</span>
                                        <br><small class="text-muted"><?php echo round($deuda->getPorcentajePagado(), 1); ?>%</small>
                                    <?php elseif ($deuda->isVencida()): ?>
                                        <span class="badge bg-danger">Vencido</span>
                                    <?php elseif ($deuda->isPagada()): ?>
                                        <span class="badge bg-success">Pagado</span>
                                    <?php elseif ($deuda->isCancelada()): ?>
                                        <span class="badge bg-secondary">Cancelado</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatDate($deuda->fecha_registro); ?></td>
                                <td class="text-center">
                                    <?php if (!$deuda->isPagada() && !$deuda->isCancelada() && hasRole(['administrador', 'tesorero'])): ?>
                                        <a href="<?php echo url('pagos/registrar?deuda_id=' . $deuda->idDeuda . '&colegiado_id=' . $colegiado->idColegiados); ?>" 
                                           class="btn btn-sm btn-success" title="Registrar pago">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($deudas)): ?>
                <tfoot class="table-secondary">
                    <tr>
                        <th colspan="7" class="text-end">TOTAL ADEUDADO:</th>
                        <th class="text-center">
                            <strong class="text-danger"><?php echo formatMoney($total); ?></strong>
                        </th>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>