<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-tags me-2"></i>
            Tipos de Gasto
        </h2>
        <div>
            <a href="<?php echo url('egresos'); ?>" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Volver a Egresos
            </a>
            <a href="<?php echo url('egresos/tipos-gasto/crear'); ?>" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nuevo Tipo de Gasto
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-results">
        <i class="fas fa-list me-2"></i> 
        Tipos de Gasto: <strong><?php echo count($tiposGasto); ?></strong>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th width="10%">Código</th>
                        <th width="30%">Nombre</th>
                        <th width="40%">Descripción</th>
                        <th width="10%">Estado</th>
                        <th width="10%" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tiposGasto)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle me-2"></i>
                                No hay tipos de gasto registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tiposGasto as $tipo): ?>
                            <tr>
                                <td><code><?php echo e($tipo['codigo']); ?></code></td>
                                <td><strong><?php echo e($tipo['nombre_tipo']); ?></strong></td>
                                <td><?php echo e($tipo['descripcion'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $tipo['estado'] === 'activo' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($tipo['estado']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo url('egresos/tipos-gasto/editar/' . $tipo['idTipo_Gasto']); ?>" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="eliminarTipoGasto(<?php echo $tipo['idTipo_Gasto']; ?>)" 
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>