<div class="page-header">
    <h2>
        <i class="fas fa-plus-circle me-2" style="color: #B91D22;"></i>
        Nuevo Método de Pago
    </h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('pagos/metodos/guardar'); ?>">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Código</label>
                    <input type="text" name="codigo" class="form-control" 
                           placeholder="Ej: EFE, TRA, DEP" required>
                    <small class="text-muted">Código corto identificador (máx 3 letras)</small>
                </div>
                
                <div class="col-md-8 mb-3">
                    <label class="form-label required">Nombre del Método</label>
                    <input type="text" name="nombre" class="form-control" 
                           placeholder="Ej: Transferencia Bancaria" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"
                              placeholder="Descripción del método de pago"></textarea>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Orden</label>
                    <input type="number" name="orden" class="form-control" 
                           value="0" min="0">
                    <small class="text-muted">Define el orden de aparición (menor = primero)</small>
                </div>
                
                <div class="col-md-8 mb-3">
                    <label class="form-label">Opciones</label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="requiere_comprobante" 
                               id="requiereComprobante" value="1" checked>
                        <label class="form-check-label" for="requiereComprobante">
                            Requiere número de comprobante
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" 
                               id="activo" value="1" checked>
                        <label class="form-check-label" for="activo">
                            Activo
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Datos Adicionales (JSON)</label>
                    <textarea name="datos_adicionales" class="form-control" rows="3"
                              placeholder='Ej: {"banco": "BCP", "cuenta": "123456789", "tipo": "ahorros"}'></textarea>
                    <small class="text-muted">Información adicional en formato JSON (opcional)</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <a href="<?php echo url('pagos/metodos'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar Método
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>