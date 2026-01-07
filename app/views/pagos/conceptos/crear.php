<div class="page-header">
    <h2>
        <i class="fas fa-plus-circle me-2" style="color: #B91D22;"></i>
        Nuevo Concepto de Pago
    </h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('pagos/conceptos/guardar'); ?>">
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
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Opciones</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="requiere_comprobante" 
                               id="requiereComprobante" value="1" checked>
                        <label class="form-check-label" for="requiereComprobante">
                            Requiere número de comprobante
                        </label>
                    </div>
                </div>
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