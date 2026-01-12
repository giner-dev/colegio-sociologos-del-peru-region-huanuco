<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-plus-circle me-2"></i>
            Registrar Nueva Deuda
        </h2>
        <a href="<?php echo url('deudas'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-search">
        <i class="fas fa-file-invoice me-2"></i> Datos de la Deuda
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo url('deudas/guardar'); ?>" id="formRegistrarDeuda">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label required">Colegiado</label>
                    <select name="colegiado_id" id="selectColegiado" class="form-select" required>
                        <option value="">Seleccione un colegiado...</option>
                        <?php foreach ($colegiados as $colegiado): ?>
                            <option value="<?php echo $colegiado->idColegiados; ?>">
                                <?php echo formatNumeroColegiatura($colegiado->numero_colegiatura); ?> - 
                                <?php echo e($colegiado->getNombreCompleto()); ?> -
                                DNI: <?php echo e($colegiado->dni); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Busque por número de colegiatura, nombre o DNI</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Concepto de la Deuda</label>
                    <select name="concepto_id" id="selectConcepto" class="form-select" required>
                        <option value="">Seleccione un concepto...</option>
                        <?php foreach ($conceptos as $concepto): ?>
                            <option value="<?php echo $concepto['idConcepto']; ?>" 
                                    data-monto="<?php echo $concepto['monto_sugerido']; ?>"
                                    data-recurrente="<?php echo $concepto['es_recurrente'] ? '1' : '0'; ?>"
                                    data-frecuencia="<?php echo e($concepto['frecuencia'] ?? ''); ?>"
                                    data-dia-vencimiento="<?php echo $concepto['dia_vencimiento'] ?? ''; ?>">
                                <?php echo e($concepto['nombre_completo']); ?> 
                                <?php if ($concepto['es_recurrente']): ?>
                                    <span class="badge bg-info">Recurrente</span>
                                <?php endif; ?>
                                (S/ <?php echo number_format($concepto['monto_sugerido'], 2); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Los conceptos recurrentes calculan automáticamente el vencimiento</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Descripción Específica</label>
                    <input type="text" name="descripcion_deuda" class="form-control" 
                           placeholder="Ej: Cuota mensual octubre 2024">
                    <small class="text-muted">Si se deja vacío, se usará el nombre del concepto</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label required">Monto</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="number" name="monto_esperado" id="montoDeuda" 
                               class="form-control" step="0.01" min="0.01" 
                               required placeholder="0.00">
                    </div>
                    <small class="text-muted">Se auto-completa según el concepto seleccionado</small>
                </div>
                
                <div class="col-md-4 mb-3" id="grupoFechaVencimiento">
                    <label class="form-label required">Fecha de Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" class="form-control" required>
                    <small class="text-muted">Fecha en que vence la deuda</small>
                </div>
                
                <div class="col-md-4 mb-3" id="grupoFechaMaxima">
                    <label class="form-label">Fecha Máxima de Pago</label>
                    <input type="date" name="fecha_maxima_pago" class="form-control">
                    <small class="text-muted">Para aplicar recargos después de esta fecha</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3" 
                              placeholder="Observaciones adicionales sobre esta deuda..."></textarea>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Nota:</strong> El estado de la deuda se determinará automáticamente según la fecha de vencimiento.
                Si la fecha ya pasó, se registrará como "Vencida".
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <a href="<?php echo url('deudas'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Registrar Deuda
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>