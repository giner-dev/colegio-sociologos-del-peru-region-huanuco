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
            <?php if (hasRole('administrador') && $pago->estado !== 'anulado'): ?>
                <button type="button" class="btn btn-danger" onclick="anularPago(<?php echo $pago->idPagos; ?>)">
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
                            <strong>#<?php echo str_pad($pago->idPagos, 6, '0', STR_PAD_LEFT); ?></strong>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Estado</div>
                        <div>
                            <?php if ($pago->estado === 'registrado'): ?>
                                <span class="badge bg-primary badge-lg">REGISTRADO</span>
                            <?php elseif ($pago->estado === 'validado'): ?>
                                <span class="badge bg-success badge-lg">VALIDADO</span>
                            <?php else: ?>
                                <span class="badge bg-danger badge-lg">ANULADO</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="info-label">Concepto</div>
                        <div class="info-value">
                            <?php echo e($pago->concepto_nombre ?: $pago->concepto_texto); ?>
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
                        <div class="info-label">Fecha de Pago</div>
                        <div class="info-value"><?php echo formatDate($pago->fecha_pago); ?></div>
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
                
                <?php if ($pago->numero_comprobante): ?>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="info-label">N° de Comprobante</div>
                        <div class="info-value"><?php echo e($pago->numero_comprobante); ?></div>
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
                    <div class="info-value"><?php echo e($pago->dni ?? ''); ?></div>
                </div>
                
                <div class="mb-3">
                    <div class="info-label">Nombre Completo</div>
                    <div class="info-value">
                        <?php echo e($pago->getNombreColegiado()); ?>
                    </div>
                </div>
                
                <a href="<?php echo url('colegiados/ver/' . $pago->colegiados_id); ?>" 
                   class="btn btn-sm btn-outline-primary w-100">
                    <i class="fas fa-eye me-1"></i> Ver Colegiado
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
                    <div class="info-value"><?php echo e($pago->usuario_nombre); ?></div>
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

<script>
function anularPago(id) {
    Swal.fire({
        title: '¿Anular este pago?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#B91D22',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo url('pagos/anular/'); ?>' + id;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>