<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-plus-circle me-2"></i>
            Nuevo Tipo de Gasto
        </h2>
        <a href="<?php echo url('egresos/tipos-gasto'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-tag me-2"></i> Datos del Tipo de Gasto
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo url('egresos/tipos-gasto/guardar'); ?>" id="formTipoGasto">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Nombre</label>
                    <input type="text" name="nombre" class="form-control" 
                           required placeholder="Ej: Servicios Básicos">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Código</label>
                    <input type="text" name="codigo" class="form-control" 
                           placeholder="Ej: SRV" maxlength="20">
                    <small class="text-muted">Si se deja vacío, se generará automáticamente</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3" 
                              placeholder="Describe el tipo de gasto..."></textarea>
                </div>
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                    <a href="<?php echo url('egresos/tipos-gasto'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>