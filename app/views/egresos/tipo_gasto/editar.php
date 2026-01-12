<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-edit me-2"></i>
            Editar Tipo de Gasto
        </h2>
        <a href="<?php echo url('egresos/tipos-gasto'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-warning">
        <i class="fas fa-tag me-2"></i> Actualizar Datos del Tipo de Gasto
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo url('egresos/tipos-gasto/actualizar/' . $tipoGasto['idTipo_Gasto']); ?>" 
              id="formTipoGasto">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Nombre</label>
                    <input type="text" name="nombre" class="form-control" 
                           required value="<?php echo e($tipoGasto['nombre_tipo']); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Código</label>
                    <input type="text" name="codigo" class="form-control" 
                           value="<?php echo e($tipoGasto['codigo']); ?>" maxlength="20">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?php echo e($tipoGasto['descripcion']); ?></textarea>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="activo" <?php echo $tipoGasto['estado'] === 'activo' ? 'selected' : ''; ?>>
                            Activo
                        </option>
                        <option value="inactivo" <?php echo $tipoGasto['estado'] === 'inactivo' ? 'selected' : ''; ?>>
                            Inactivo
                        </option>
                    </select>
                </div>
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i> Actualizar
                    </button>
                    <a href="<?php echo url('egresos/tipos-gasto'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>