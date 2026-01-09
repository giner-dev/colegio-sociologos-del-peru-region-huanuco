<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-file-invoice-dollar me-2"></i>
            Gestión de Deudas
        </h2>
        <div>
            <a href="<?php echo url('deudas/morosos'); ?>" class="btn btn-warning me-2">
                <i class="fas fa-exclamation-triangle me-1"></i> Ver Morosos
            </a>
            <?php if (hasRole(['administrador', 'tesorero'])): ?>
                <a href="<?php echo url('deudas/registrar'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Registrar Deuda
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card-stat bg-warning">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3><?php echo number_format($resumen['pendientes'] ?? 0); ?></h3>
                <p>Pendientes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat bg-danger">
            <div class="stat-icon"><i class="fas fa-calendar-times"></i></div>
            <div class="stat-info">
                <h3><?php echo number_format($resumen['vencidas'] ?? 0); ?></h3>
                <p>Vencidas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat bg-success">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <h3><?php echo number_format($resumen['pagadas'] ?? 0); ?></h3>
                <p>Pagadas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stat bg-primary">
            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-info">
                <h3><?php echo formatMoney($resumen['monto_pendiente']); ?></h3>
                <p>Total Pendiente</p>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header-red">
        <i class="fas fa-filter me-2"></i> Filtros de Búsqueda
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo url('deudas'); ?>">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">N° Colegiatura</label>
                    <input type="text" name="numero_colegiatura" class="form-control" 
                           value="<?php echo e($filtros['numero_colegiatura'] ?? ''); ?>"
                           placeholder="Ej: 001234">
                </div>
                        
                <div class="col-md-2">
                    <label class="form-label">DNI</label>
                    <input type="text" name="dni" class="form-control" 
                           value="<?php echo e($filtros['dni'] ?? ''); ?>"
                           placeholder="12345678">
                </div>
                        
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendiente" <?php echo ($filtros['estado'] ?? '') === 'pendiente' ? 'selected' : ''; ?>>
                            Pendiente
                        </option>
                        <option value="parcial" <?php echo ($filtros['estado'] ?? '') === 'parcial' ? 'selected' : ''; ?>>
                            Pago Parcial
                        </option>
                        <option value="vencido" <?php echo ($filtros['estado'] ?? '') === 'vencido' ? 'selected' : ''; ?>>
                            Vencido
                        </option>
                        <option value="pagado" <?php echo ($filtros['estado'] ?? '') === 'pagado' ? 'selected' : ''; ?>>
                            Pagado
                        </option>
                        <option value="cancelado" <?php echo ($filtros['estado'] ?? '') === 'cancelado' ? 'selected' : ''; ?>>
                            Cancelado
                        </option>
                    </select>
                </div>
                        
                <div class="col-md-3">
                    <label class="form-label">Concepto</label>
                    <select name="concepto_id" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($conceptos as $concepto): ?>
                            <option value="<?php echo $concepto['idConcepto']; ?>" 
                                <?php echo ($filtros['concepto_id'] ?? '') == $concepto['idConcepto'] ? 'selected' : ''; ?>>
                                <?php echo e($concepto['nombre_completo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                    <a href="<?php echo url('deudas'); ?>" class="btn btn-secondary" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="card">
    <div class="card-header bg-light">
        <i class="fas fa-list me-2"></i> 
        Resultados: <strong><?php echo number_format($pagination['total']); ?></strong> deuda(s)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>N° Colegiatura</th>
                        <th>Colegiado</th>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Pagado</th>
                        <th>Saldo</th>
                        <th>F. Vencimiento</th>
                        <th>Estado</th>
                        <th>Origen</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($deudas)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle me-2"></i>
                                No se encontraron deudas
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($deudas as $deuda): ?>
                            <tr data-estado="<?php echo $deuda->estado; ?>" data-deuda-id="<?php echo $deuda->idDeuda; ?>">
                                <td><strong><?php echo e($deuda->numero_colegiatura); ?></strong></td>
                                <td>
                                    <a href="<?php echo url('colegiados/ver/' . $deuda->colegiado_id); ?>" 
                                       class="text-decoration-none">
                                        <?php echo e($deuda->getNombreCompleto()); ?>
                                    </a>
                                </td>
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
                                    <span class="badge <?php echo $deuda->getEstadoBadgeClass(); ?>">
                                        <?php echo $deuda->getEstadoTexto(); ?>
                                    </span>
                                    <?php if ($deuda->isParcial()): ?>
                                        <br><small class="text-muted">
                                            <?php echo round($deuda->getPorcentajePagado(), 1); ?>%
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?php echo $deuda->getOrigenTexto(); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($deuda->puedeSerPagada() && hasRole(['administrador', 'tesorero'])): ?>
                                        <a href="<?php echo url('pagos/registrar?deuda_id=' . $deuda->idDeuda); ?>" 
                                           class="btn btn-sm btn-success" title="Registrar pago">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (hasRole('administrador')): ?>
                                        <?php if ($deuda->puedeSeCancelada()): ?>
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                    onclick="cancelarDeuda(<?php echo $deuda->idDeuda; ?>)" 
                                                    title="Cancelar deuda">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($deuda->isPendiente() && $deuda->monto_pagado == 0): ?>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="eliminarDeuda(<?php echo $deuda->idDeuda; ?>)" 
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if ($pagination['totalPages'] > 1): ?>
            <nav aria-label="Paginación de deudas" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $pagination['page'] <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('deudas?page=1&' . http_build_query($filtros)); ?>" 
                           aria-label="Primera página">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                    </li>
                    
                    <li class="page-item <?php echo $pagination['page'] <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('deudas?page=' . ($pagination['page'] - 1) . '&' . http_build_query($filtros)); ?>"
                           aria-label="Página anterior">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>
                    
                    <?php
                    $startPage = max(1, $pagination['page'] - 2);
                    $endPage = min($pagination['totalPages'], $pagination['page'] + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li class="page-item <?php echo $i === $pagination['page'] ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo url('deudas?page=' . $i . '&' . http_build_query($filtros)); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('deudas?page=' . ($pagination['page'] + 1) . '&' . http_build_query($filtros)); ?>"
                           aria-label="Página siguiente">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                    
                    <li class="page-item <?php echo $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('deudas?page=' . $pagination['totalPages'] . '&' . http_build_query($filtros)); ?>"
                           aria-label="Última página">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    </li>
                </ul>
                
                <p class="text-center text-muted mb-0">
                    Página <?php echo $pagination['page']; ?> de <?php echo $pagination['totalPages']; ?>
                    (<?php echo number_format($pagination['total']); ?> registro<?php echo $pagination['total'] != 1 ? 's' : ''; ?>)
                </p>
            </nav>
        <?php endif; ?>
    </div>
</div>