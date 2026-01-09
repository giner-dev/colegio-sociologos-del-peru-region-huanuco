<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-plus-circle me-2" style="color: #B91D22;"></i>
            Nuevo Concepto de Pago
        </h2>
        <a href="<?php echo url('pagos/conceptos'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('pagos/conceptos/guardar'); ?>" id="formConcepto">
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label required">Nombre del Concepto</label>
                    <input type="text" name="nombre" class="form-control" 
                           placeholder="Ej: Cuota mensual de colegiación" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Tipo</label>
                    <select name="tipo" class="form-select" required>
                        <option value="cuota">Cuota</option>
                        <option value="certificado">Certificado</option>
                        <option value="tramite">Trámite</option>
                        <option value="otro" selected>Otro</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2"
                              placeholder="Descripción opcional del concepto"></textarea>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label required">Monto Sugerido</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="number" name="monto" class="form-control" 
                               step="0.01" min="0" value="0.00" required>
                    </div>
                    <small class="text-muted">Puede ser 0 si el monto varía</small>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">¿Es Recurrente?</label>
                    <select name="es_recurrente" id="esRecurrente" class="form-select">
                        <option value="0" selected>No</option>
                        <option value="1">Sí</option>
                    </select>
                    <small class="text-muted">Si se genera automáticamente</small>
                </div>
                
                <div class="col-md-3 mb-3" id="frecuenciaContainer" style="display: none;">
                    <label class="form-label">Frecuencia</label>
                    <select name="frecuencia" id="selectFrecuencia" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="mensual">Mensual</option>
                        <option value="trimestral">Trimestral</option>
                        <option value="semestral">Semestral</option>
                        <option value="anual">Anual</option>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3" id="diaVencimientoContainer" style="display: none;">
                    <label class="form-label">Día de Vencimiento</label>
                    <input type="number" name="dia_vencimiento" id="inputDiaVencimiento" 
                           class="form-control" min="1" max="31" placeholder="Ej: 15">
                    <small class="text-muted">Día del mes (1-31)</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Opciones</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="requiere_comprobante" 
                               id="requiereComprobante" value="1" checked>
                        <label class="form-check-label" for="requiereComprobante">
                            Requiere número de comprobante al registrar pago
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Nota sobre conceptos recurrentes:</strong> Si marca este concepto como recurrente, 
                deberá configurar la programación de deudas automáticas para cada colegiado desde el módulo de deudas.
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <a href="<?php echo url('pagos/conceptos'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar Concepto
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>