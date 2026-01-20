<div class="reportes-view">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h2>
                <i class="fas fa-user-times me-2"></i>
                Colegiados Inhabilitados / Inactivos por Cese
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

    <!-- Filtro de Estado -->
    <div class="card mb-4">
        <div class="card-header-red">
            <i class="fas fa-filter me-2"></i> Filtrar por Estado
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo url('reportes/inhabilitados'); ?>" id="formFiltroEstado">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Estado del Colegiado</label>
                        <select name="estado" class="form-select">
                            <option value="">Todos</option>
                            <option value="inhabilitado" <?php echo ($filtroEstado ?? '') === 'inhabilitado' ? 'selected' : ''; ?>>
                                Solo Inhabilitados
                            </option>
                            <option value="inactivo_cese" <?php echo ($filtroEstado ?? '') === 'inactivo_cese' ? 'selected' : ''; ?>>
                                Solo Inactivos por Cese
                            </option>
                        </select>
                    </div>
                    <div class="col-md-8 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                        <a href="<?php echo url('reportes/inhabilitados'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen -->
    <div class="content-card mb-4">
        <div class="row text-center">
            <div class="col-md-12">
                <h4 class="text-danger">
                    <i class="fas fa-users-slash me-2"></i>
                    Total: <strong><?php echo number_format($total); ?></strong>
                    <?php if (!empty($filtroEstado)): ?>
                        <span class="badge bg-info ms-2">
                            <?php 
                            echo $filtroEstado === 'inhabilitado' ? 'Solo Inhabilitados' : 'Solo Inactivos por Cese';
                            ?>
                        </span>
                    <?php endif; ?>
                </h4>
                <p class="text-muted mb-0">
                    Fecha de reporte: <?php echo date('d/m/Y H:i'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-header bg-results">
            <i class="fas fa-list me-2"></i> 
            Listado de Colegiados Inhabilitados / Inactivos por Cese
        </div>
        <div class="card-body">
            <div id="printableArea">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="tablaInhabilitados">
                        <thead class="table-dark">
                            <tr>
                                <th>N° Colegiatura</th>
                                <th>DNI</th>
                                <th>Nombre Completo</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Fecha Colegiatura</th>
                                <th>Estado</th>
                                <th>Motivo/Observación</th>
                                <th>Fecha Cambio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($inhabilitados)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No hay colegiados inhabilitados o inactivos por cese
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($inhabilitados as $colegiado): ?>
                                    <tr>
                                        <td data-label="N° Colegiatura">
                                            <strong class="text-danger">
                                                <?php echo formatNumeroColegiatura($colegiado['numero_colegiatura']); ?>
                                            </strong>
                                        </td>
                                        <td data-label="DNI"><?php echo e($colegiado['dni']); ?></td>
                                        <td data-label="Nombre Completo"><?php echo e($colegiado['nombre_completo']); ?></td>
                                        <td data-label="Correo"><?php echo e($colegiado['correo'] ?? '-'); ?></td>
                                        <td data-label="Teléfono"><?php echo e($colegiado['telefono'] ?? '-'); ?></td>
                                        <td data-label="Fecha Colegiatura"><?php echo formatDate($colegiado['fecha_colegiatura']); ?></td>
                                        <td data-label="Estado">
                                            <?php if ($colegiado['estado'] === 'inactivo_cese'): ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-user-slash"></i> Inactivo por Cese
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-user-times"></i> Inhabilitado
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Motivo/Observación">
                                            <?php if ($colegiado['estado'] === 'inactivo_cese'): ?>
                                                <small class="text-muted">
                                                    <?php echo e($colegiado['motivo_cese'] ?? '-'); ?>
                                                    <?php if ($colegiado['fecha_cese']): ?>
                                                        <br><strong>Fecha cese:</strong> <?php echo formatDate($colegiado['fecha_cese']); ?>
                                                    <?php endif; ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">
                                                    <?php echo e($colegiado['motivo_inhabilitacion'] ?? '-'); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Fecha Cambio">
                                            <?php if ($colegiado['fecha_cambio_estado']): ?>
                                                <?php echo formatDate($colegiado['fecha_cambio_estado']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
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
                        <?php
                        $estadoParam = !empty($filtroEstado) ? '&estado=' . $filtroEstado : '';
                        ?>
                        
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo url('reportes/inhabilitados?page=1' . $estadoParam); ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo url('reportes/inhabilitados?page=' . ($page - 1) . $estadoParam); ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                        
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo url('reportes/inhabilitados?page=' . $i . $estadoParam); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo url('reportes/inhabilitados?page=' . ($page + 1) . $estadoParam); ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo url('reportes/inhabilitados?page=' . $totalPages . $estadoParam); ?>">
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