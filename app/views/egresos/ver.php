<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-file-invoice me-2"></i>
            Detalle del Egreso
        </h2>
        <div>
            <a href="<?php echo url('egresos'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            <?php if (hasPermission('egresos', 'editar')): ?>
                <a href="<?php echo url('egresos/editar/' . $egreso->idEgreso); ?>" 
                   class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Editar
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header-red">
                <i class="fas fa-info-circle me-2"></i> Información del Egreso
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="info-label">Descripción</div>
                        <div class="info-value"><?php echo e($egreso->descripcion); ?></div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-label">Tipo de Gasto</div>
                        <div class="info-value">
                            <span class="badge bg-secondary">
                                <?php echo e($egreso->getTipoGasto()); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="info-label">Monto</div>
                        <div class="info-value">
                            <h3 class="text-danger mb-0">
                                <?php echo formatMoney($egreso->monto); ?>
                            </h3>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-label">Fecha del Egreso</div>
                        <div class="info-value"><?php echo formatDate($egreso->fecha_egreso); ?></div>
                    </div>
                </div>

                <?php if ($egreso->num_comprobante): ?>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="info-label">Número de Comprobante</div>
                        <div class="info-value">
                            <span class="badge bg-info">
                                <?php echo e($egreso->num_comprobante); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($egreso->comprobante): ?>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="info-label">Comprobante</div>
                        <div class="info-value">
                            <a href="<?php echo url($egreso->comprobante); ?>" 
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> Descargar
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($egreso->observaciones): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="info-label">Observaciones</div>
                        <div class="info-value"><?php echo nl2br(e($egreso->observaciones)); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <i class="fas fa-info-circle me-2"></i> Información de Registro
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="info-label">Registrado por</div>
                    <div class="info-value"><?php echo e($egreso->usuario_nombre); ?></div>
                </div>
                
                <div class="mb-3">
                    <div class="info-label">Fecha de Registro</div>
                    <div class="info-value"><?php echo formatDateTime($egreso->fecha_registro); ?></div>
                </div>
                
                <?php if ($egreso->fecha_actualizacion != $egreso->fecha_registro): ?>
                <div>
                    <div class="info-label">Última Actualización</div>
                    <div class="info-value"><?php echo formatDateTime($egreso->fecha_actualizacion); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>