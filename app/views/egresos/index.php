<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-money-bill-wave me-2"></i>
            Gestión de Egresos
        </h2>
        <div>
            <?php if (hasRole('administrador')): ?>
                <a href="<?php echo url('egresos/tipos-gasto'); ?>" class="btn btn-success">
                    <i class="fas fa-tags me-1"></i> Tipos de Gasto
                </a>
            <?php endif; ?>
            <?php if (hasPermission('egresos', 'crear')): ?>
                <a href="<?php echo url('egresos/registrar'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Registrar Egreso
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-search">
        <i class="fas fa-filter me-2"></i> Filtros de Búsqueda
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo url('egresos'); ?>">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" 
                           value="<?php echo e($filtros['fecha_inicio'] ?? ''); ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control" 
                           value="<?php echo e($filtros['fecha_fin'] ?? ''); ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Tipo de Gasto</label>
                    <select name="tipo_gasto_id" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($tiposGasto as $tipo): ?>
                            <option value="<?php echo $tipo['idTipo_Gasto']; ?>"
                                <?php echo ($filtros['tipo_gasto_id'] ?? '') == $tipo['idTipo_Gasto'] ? 'selected' : ''; ?>>
                                <?php echo e($tipo['nombre_tipo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                    <a href="<?php echo url('egresos'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-results">
        <i class="fas fa-list me-2"></i> 
        Resultados: <strong><?php echo number_format($pagination['total']); ?></strong> egreso(s)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>N° Comprobante</th>
                        <th>Descripción</th>
                        <th>Tipo de Gasto</th>
                        <th>Monto</th>
                        <th>Comprobante</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($egresos)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle me-2"></i>
                                No se encontraron egresos
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($egresos as $egreso): ?>
                            <tr>
                                <td><?php echo formatDate($egreso->fecha_egreso); ?></td>
                                <td>
                                    <?php if ($egreso->num_comprobante): ?>
                                        <span class="badge bg-info">
                                            <?php echo e($egreso->num_comprobante); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($egreso->descripcion); ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo e($egreso->getTipoGasto()); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-danger">
                                        <?php echo formatMoney($egreso->monto); ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php if ($egreso->comprobante): ?>
                                        <a href="<?php echo url($egreso->comprobante); ?>" 
                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Sin comprobante</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo url('egresos/ver/' . $egreso->idEgreso); ?>" 
                                       class="btn btn-sm btn-info" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if (hasPermission('egresos', 'editar')): ?>
                                        <a href="<?php echo url('egresos/editar/' . $egreso->idEgreso); ?>" 
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($pagination['totalPages'] > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $pagination['page'] <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('egresos?page=1&' . http_build_query($filtros)); ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                    </li>
                    
                    <li class="page-item <?php echo $pagination['page'] <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('egresos?page=' . ($pagination['page'] - 1) . '&' . http_build_query($filtros)); ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>
                    
                    <?php
                    $startPage = max(1, $pagination['page'] - 2);
                    $endPage = min($pagination['totalPages'], $pagination['page'] + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li class="page-item <?php echo $i === $pagination['page'] ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo url('egresos?page=' . $i . '&' . http_build_query($filtros)); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('egresos?page=' . ($pagination['page'] + 1) . '&' . http_build_query($filtros)); ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                    
                    <li class="page-item <?php echo $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('egresos?page=' . $pagination['totalPages'] . '&' . http_build_query($filtros)); ?>">
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