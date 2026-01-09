<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-tags me-2" style="color: #B91D22;"></i>
            Gestión de Conceptos de Pago
        </h2>
        <a href="<?php echo url('pagos/conceptos/crear'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nuevo Concepto
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-results">
        <i class="fas fa-list me-2"></i> 
        Conceptos Registrados: <strong><?php echo count($conceptos); ?></strong>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Concepto</th>
                        <th>Tipo</th>
                        <th>Monto Sugerido</th>
                        <th>Recurrente</th>
                        <th>Requiere Comprobante</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($conceptos)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle me-2"></i>
                                No hay conceptos registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($conceptos as $concepto): ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($concepto['nombre_completo']); ?></strong>
                                    <?php if ($concepto['descripcion']): ?>
                                        <br><small class="text-muted"><?php echo e($concepto['descripcion']); ?></small>
                                    <?php endif; ?>
                                    <?php if ($concepto['es_recurrente']): ?>
                                        <br><small class="text-info">
                                            <i class="fas fa-redo me-1"></i>
                                            <?php echo ucfirst(e($concepto['frecuencia'])); ?> 
                                            <?php if ($concepto['dia_vencimiento']): ?>
                                                - Día <?php echo $concepto['dia_vencimiento']; ?>
                                            <?php endif; ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo ucfirst(e($concepto['tipo_concepto'])); ?>
                                    </span>
                                </td>
                                <td><strong class="text-success"><?php echo formatMoney($concepto['monto_sugerido']); ?></strong></td>
                                <td class="text-center">
                                    <?php if ($concepto['es_recurrente']): ?>
                                        <i class="fas fa-check-circle text-success" title="Recurrente"></i>
                                    <?php else: ?>
                                        <i class="fas fa-times-circle text-muted" title="No recurrente"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($concepto['requiere_comprobante']): ?>
                                        <i class="fas fa-check-circle text-success" title="Requiere comprobante"></i>
                                    <?php else: ?>
                                        <i class="fas fa-times-circle text-muted" title="No requiere comprobante"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($concepto['estado'] === 'activo'): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo url('pagos/conceptos/editar/' . $concepto['idConcepto']); ?>" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($concepto['estado'] === 'activo'): ?>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="eliminarConcepto(<?php echo $concepto['idConcepto']; ?>)"
                                                title="Desactivar">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>