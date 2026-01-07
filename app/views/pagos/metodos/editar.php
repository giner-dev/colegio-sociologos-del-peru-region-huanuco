<link rel="stylesheet" href="<?php echo url('assets/css/pagos.css'); ?>">

<div class="page-header">
    <h2>
        <i class="fas fa-edit me-2" style="color: #B91D22;"></i>
        Editar Método de Pago
    </h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('pagos/metodos/actualizar/' . $metodo['idMetodo']); ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Nombre del Método</label>
                    <input type="text" name="nombre" class="form-control" 
                           value="<?php echo e($metodo['nombre']); ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estado</label>
                    <select name="activo" class="form-select">
                        <option value="activo" <?php echo $metodo['activo'] === 'activo' ? 'selected' : ''; ?>>
                            Activo
                        </option>
                        <option value="inactivo" <?php echo $metodo['activo'] === 'inactivo' ? 'selected' : ''; ?>>
                            Inactivo
                        </option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?php echo e($metodo['descripcion']); ?></textarea>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <a href="<?php echo url('pagos/metodos'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>