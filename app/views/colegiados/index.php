<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-users me-2"></i>
            Gestión de Colegiados
        </h2>
        <div>
            <?php if (hasPermission('colegiados', 'crear')): ?>
                <a href="<?php echo url('colegiados/importar'); ?>" class="btn btn-secondary me-2">
                    <i class="fas fa-file-excel"></i> Importar Excel
                </a>
                <a href="<?php echo url('colegiados/crear'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Colegiado
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Formulario de búsqueda -->
<div class="card mb-4">
    <div class="card-header bg-search">
        <i class="fas fa-search me-2"></i> Búsqueda de Colegiados
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo url('colegiados'); ?>" id="formBusqueda">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">N° Colegiatura</label>
                    <input type="text" name="numero_colegiatura" class="form-control" 
                           value="<?php echo e($filtros['numero_colegiatura'] ?? ''); ?>"
                           placeholder="Ej: 12345">
                </div>
                <div class="col-md-3">
                    <label class="form-label">DNI</label>
                    <input type="text" name="dni" class="form-control" 
                           value="<?php echo e($filtros['dni'] ?? ''); ?>"
                           placeholder="Ej: 12345678">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Apellidos y Nombres</label>
                    <input type="text" name="nombres" class="form-control" 
                           value="<?php echo e($filtros['nombres'] ?? ''); ?>"
                           placeholder="Buscar por nombre">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="habilitado" <?php echo ($filtros['estado'] ?? '') === 'habilitado' ? 'selected' : ''; ?>>
                            Habilitado
                        </option>
                        <option value="inhabilitado" <?php echo ($filtros['estado'] ?? '') === 'inhabilitado' ? 'selected' : ''; ?>>
                            Inhabilitado
                        </option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12 text-end">
                    <a href="<?php echo url('colegiados'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de resultados -->
<div class="card">
    <div class="card-header bg-results">
        <i class="fas fa-list me-2"></i> 
        Resultados: <strong><?php echo $paginacion['total']; ?></strong> colegiado(s) encontrado(s)
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>N° Colegiatura</th>
                        <th>DNI</th>
                        <th>Apellidos y Nombres</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($colegiados)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                No se encontraron colegiados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($colegiados as $colegiado): ?>
                            <tr>
                                <td data-label="N° Colegiatura">
                                    <strong><?php echo formatNumeroColegiatura($colegiado->numero_colegiatura); ?></strong>
                                </td>
                                <td data-label="DNI"><?php echo e($colegiado->dni); ?></td>
                                <td data-label="Apellidos y Nombres"><?php echo e($colegiado->getNombreCompleto()); ?></td>
                                <td data-label="Teléfono"><?php echo e($colegiado->telefono ?: '-'); ?></td>
                                <td data-label="Correo"><?php echo e($colegiado->correo ?: '-'); ?></td>
                                <td data-label="Estado">
                                    <?php if ($colegiado->estado === 'habilitado'): ?>
                                        <span class="badge badge-habilitado">
                                            <i class="fas fa-check-circle"></i> Habilitado
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-inhabilitado">
                                            <i class="fas fa-times-circle"></i> Inhabilitado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center" data-label="Acciones">
                                    <a href="<?php echo url('colegiados/ver/' . $colegiado->idColegiados); ?>" 
                                       class="btn btn-sm btn-info" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (hasRole(['administrador', 'tesorero'])): ?>
                                        <a href="<?php echo url('colegiados/editar/' . $colegiado->idColegiados); ?>" 
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

        <!-- COMPONENTE DE PAGINACIÓN -->
        <?php if ($paginacion['total_paginas'] > 1): ?>
            <div class="pagination-wrapper">
                <nav aria-label="Navegación de páginas">
                    <ul class="pagination justify-content-center">
                        
                        <!-- Botón Primera Página -->
                        <?php if ($paginacion['pagina_actual'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo buildPaginationUrl(1, $filtros); ?>" aria-label="Primera">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Botón Anterior -->
                        <?php if ($paginacion['pagina_actual'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo buildPaginationUrl($paginacion['pagina_actual'] - 1, $filtros); ?>" aria-label="Anterior">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-angle-left"></i></span>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Números de página -->
                        <?php
                        $rango = 2; // Páginas a mostrar a cada lado
                        $inicio = max(1, $paginacion['pagina_actual'] - $rango);
                        $fin = min($paginacion['total_paginas'], $paginacion['pagina_actual'] + $rango);
                        
                        // Mostrar primera página si no está en el rango
                        if ($inicio > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo buildPaginationUrl(1, $filtros); ?>">1</a>
                            </li>
                            <?php if ($inicio > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Páginas del rango -->
                        <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                            <li class="page-item <?php echo $i == $paginacion['pagina_actual'] ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo buildPaginationUrl($i, $filtros); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Mostrar última página si no está en el rango -->
                        <?php if ($fin < $paginacion['total_paginas']): ?>
                            <?php if ($fin < $paginacion['total_paginas'] - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo buildPaginationUrl($paginacion['total_paginas'], $filtros); ?>">
                                    <?php echo $paginacion['total_paginas']; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Botón Siguiente -->
                        <?php if ($paginacion['pagina_actual'] < $paginacion['total_paginas']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo buildPaginationUrl($paginacion['pagina_actual'] + 1, $filtros); ?>" aria-label="Siguiente">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-angle-right"></i></span>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Botón Última Página -->
                        <?php if ($paginacion['pagina_actual'] < $paginacion['total_paginas']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo buildPaginationUrl($paginacion['total_paginas'], $filtros); ?>" aria-label="Última">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                    </ul>
                </nav>
                
                <!-- Info de paginación -->
                <div class="pagination-info text-center mt-2">
                    <small class="text-muted">
                        Página <?php echo $paginacion['pagina_actual']; ?> de <?php echo $paginacion['total_paginas']; ?>
                    </small>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php
/**
 * Helper para construir URLs de paginación conservando filtros
 */
function buildPaginationUrl($pagina, $filtros) {
    $params = ['pagina' => $pagina];
    
    // Agregar filtros activos
    foreach ($filtros as $key => $value) {
        if (!empty($value)) {
            $params[$key] = $value;
        }
    }
    
    return url('colegiados') . '?' . http_build_query($params);
}
?>