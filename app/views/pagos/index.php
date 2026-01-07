<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-money-bill-wave me-2"></i>
            Gestión de Pagos
        </h2>
        <div>
            <?php if (hasRole(['administrador', 'tesorero'])): ?>
                <a href="<?php echo url('pagos/registrar'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Registrar Pago
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filtros de búsqueda -->
<div class="card mb-4">
    <div class="card-header-red">
        <i class="fas fa-filter me-2"></i> Filtros de Búsqueda
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo url('pagos'); ?>" id="formFiltros">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">N° Colegiatura</label>
                    <input type="text" name="numero_colegiatura" class="form-control" 
                           value="<?php echo e($filtros['numero_colegiatura'] ?? ''); ?>"
                           placeholder="Buscar por colegiatura">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" 
                           value="<?php echo e($filtros['fecha_inicio'] ?? ''); ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control" 
                           value="<?php echo e($filtros['fecha_fin'] ?? ''); ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Método de Pago</label>
                    <select name="metodo_pago" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($metodos as $metodo): ?>
                            <option value="<?php echo $metodo['idMetodo']; ?>"
                                <?php echo ($filtros['metodo_pago'] ?? '') == $metodo['idMetodo'] ? 'selected' : ''; ?>>
                                <?php echo e($metodo['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="registrado" <?php echo ($filtros['estado'] ?? '') === 'registrado' ? 'selected' : ''; ?>>
                            Registrado
                        </option>
                        <option value="validado" <?php echo ($filtros['estado'] ?? '') === 'validado' ? 'selected' : ''; ?>>
                            Validado
                        </option>
                        <option value="anulado" <?php echo ($filtros['estado'] ?? '') === 'anulado' ? 'selected' : ''; ?>>
                            Anulado
                        </option>
                    </select>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-12 text-end">
                    <a href="<?php echo url('pagos'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Limpiar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de pagos -->
<div class="card">
    <div class="card-header bg-results">
        <i class="fas fa-list me-2"></i> 
        Resultados: <strong><?php echo number_format($pagination['total']); ?></strong> pago(s)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>N° Colegiatura</th>
                        <th>Colegiado</th>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pagos)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle me-2"></i>
                                No se encontraron pagos
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td><?php echo formatDate($pago->fecha_pago); ?></td>
                                <td><strong><?php echo e($pago->numero_colegiatura); ?></strong></td>
                                <td><?php echo e($pago->getNombreColegiado()); ?></td>
                                <td><?php echo e($pago->concepto_nombre ?: $pago->concepto_texto); ?></td>
                                <td><strong class="text-success"><?php echo formatMoney($pago->monto); ?></strong></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo e($pago->metodo_nombre); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($pago->estado === 'registrado'): ?>
                                        <span class="badge bg-primary">Registrado</span>
                                    <?php elseif ($pago->estado === 'validado'): ?>
                                        <span class="badge bg-success">Validado</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Anulado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo url('pagos/ver/' . $pago->idPagos); ?>" 
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
        
        <!-- Paginación -->
        <?php if ($pagination['totalPages'] > 1): ?>
            <nav aria-label="Paginación" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Primera página -->
                    <li class="page-item <?php echo $pagination['page'] <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('pagos?page=1&' . http_build_query($filtros)); ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                    </li>
                    
                    <!-- Anterior -->
                    <li class="page-item <?php echo $pagination['page'] <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('pagos?page=' . ($pagination['page'] - 1) . '&' . http_build_query($filtros)); ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>
                    
                    <!-- Páginas -->
                    <?php
                    $startPage = max(1, $pagination['page'] - 2);
                    $endPage = min($pagination['totalPages'], $pagination['page'] + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li class="page-item <?php echo $i === $pagination['page'] ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo url('pagos?page=' . $i . '&' . http_build_query($filtros)); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Siguiente -->
                    <li class="page-item <?php echo $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('pagos?page=' . ($pagination['page'] + 1) . '&' . http_build_query($filtros)); ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                    
                    <!-- Última página -->
                    <li class="page-item <?php echo $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('pagos?page=' . $pagination['totalPages'] . '&' . http_build_query($filtros)); ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    </li>
                </ul>
                
                <p class="text-center text-muted">
                    Página <?php echo $pagination['page']; ?> de <?php echo $pagination['totalPages']; ?>
                    (<?php echo number_format($pagination['total']); ?> registros totales)
                </p>
            </nav>
        <?php endif; ?>
    </div>
</div>