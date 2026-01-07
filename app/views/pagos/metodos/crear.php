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
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Nombre del Método</label>
                    <input type="text" name="nombre" class="form-control" 
                           placeholder="Ej: Transferencia Bancaria" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"
                              placeholder="Descripción opcional del método de pago"></textarea>
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