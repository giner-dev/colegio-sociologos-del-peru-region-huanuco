<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-receipt me-2" style="color: #B91D22;"></i>
            Detalle del Pago
        </h2>
        <div>
            <a href="<?php echo url('pagos'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            
            <?php if (hasRole(['administrador', 'tesorero']) && $pago->isRegistrado()): ?>
                <button type="button" class="btn btn-success" onclick="confirmarPago(<?php echo $pago->idPago; ?>)">
                    <i class="fas fa-check me-1"></i> Confirmar Pago
                </button>
            <?php endif; ?>
            
            <?php if (hasRole('administrador') && !$pago->isAnulado()): ?>
                <button type="button" class="btn btn-danger" onclick="anularPago(<?php echo $pago->idPago; ?>)">
                    <i class="fas fa-ban me-1"></i> Anular Pago
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <!-- Información del Pago -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header-red">
                <i class="fas fa-file-invoice-dollar me-2"></i> Información del Pago
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="info-label">N° de Pago</div>
                        <div class="info-value">
                            <strong>#<?php echo str_pad($pago->idPago, 6, '0', STR_PAD_LEFT); ?></strong>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Estado</div>
                        <div>
                            <span class="badge bg-<?php echo $pago->getEstadoClase(); ?> badge-lg">
                                <?php echo $pago->getEstadoTexto(); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Concepto Pagado</div>
                        <div class="info-value">
                            <?php echo e($pago->getConcepto()); ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Monto</div>
                        <div class="info-value">
                            <h3 class="text-success mb-0">
                                <?php echo formatMoney($pago->monto); ?>
                            </h3>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Deuda Pagada</div>
                        <div class="info-value">
                            <?php echo e($pago->deuda_descripcion); ?>
                            <br>
                            <small class="text-muted">
                                Total deuda: <?php echo formatMoney($pago->deuda_monto_esperado); ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Método de Pago</div>
                        <div class="info-value">
                            <span class="badge bg-info">
                                <?php echo e($pago->metodo_nombre); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Fecha de Pago</div>
                        <div class="info-value"><?php echo formatDate($pago->fecha_pago); ?></div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Fecha de Registro</div>
                        <div class="info-value"><?php echo formatDateTime($pago->fecha_registro); ?></div>
                    </div>
                </div>
                
                <?php if ($pago->numero_comprobante): ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="info-label">N° de Comprobante</div>
                        <div class="info-value"><?php echo e($pago->numero_comprobante); ?></div>
                    </div>
                    
                    <?php if ($pago->archivo_comprobante): ?>
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Comprobante Digital</div>
                        <div class="info-value">
                            <a href="<?php echo url($pago->archivo_comprobante); ?>" 
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download me-1"></i> Descargar
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($pago->fecha_confirmacion): ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Fecha de Confirmación</div>
                        <div class="info-value"><?php echo formatDateTime($pago->fecha_confirmacion); ?></div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Confirmado por</div>
                        <div class="info-value"><?php echo e($pago->usuario_confirmacion_nombre); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($pago->observaciones): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="info-label">Observaciones</div>
                        <div class="info-value"><?php echo nl2br(e($pago->observaciones)); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Información del Colegiado -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-user me-2"></i> Colegiado
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="info-label">N° Colegiatura</div>
                    <div class="info-value">
                        <strong><?php echo e($pago->numero_colegiatura); ?></strong>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="info-label">DNI</div>
                    <div class="info-value"><?php echo e($pago->dni); ?></div>
                </div>
                
                <div class="mb-3">
                    <div class="info-label">Nombre Completo</div>
                    <div class="info-value">
                        <?php echo e($pago->getNombreColegiado()); ?>
                    </div>
                </div>
                
                <a href="<?php echo url('colegiados/ver/' . $pago->colegiado_id); ?>" 
                   class="btn btn-sm btn-outline-primary w-100 mb-2">
                    <i class="fas fa-eye me-1"></i> Ver Colegiado
                </a>
                
                <a href="<?php echo url('pagos/historial/' . $pago->colegiado_id); ?>" 
                   class="btn btn-sm btn-outline-info w-100">
                    <i class="fas fa-history me-1"></i> Ver Historial de Pagos
                </a>
            </div>
        </div>
        
        <!-- Información de Registro -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <i class="fas fa-info-circle me-2"></i> Información de Registro
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="info-label">Registrado por</div>
                    <div class="info-value"><?php echo e($pago->usuario_registro_nombre); ?></div>
                </div>
                
                <div class="mb-3">
                    <div class="info-label">Fecha de Registro</div>
                    <div class="info-value"><?php echo formatDateTime($pago->fecha_registro); ?></div>
                </div>
                
                <?php if ($pago->fecha_actualizacion != $pago->fecha_registro): ?>
                <div>
                    <div class="info-label">Última Actualización</div>
                    <div class="info-value"><?php echo formatDateTime($pago->fecha_actualizacion); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>