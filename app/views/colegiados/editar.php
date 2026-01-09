<div class="page-header">
    <h2>
        <i class="fas fa-user-edit me-2" style="color: #B91D22;"></i>
        Editar Colegiado
    </h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('colegiados/actualizar/' . $colegiado->idColegiados); ?>" id="formColegiado">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Número de Colegiatura</label>
                    <input type="text" name="numero_colegiatura" class="form-control" 
                           value="<?php echo formatNumeroColegiatura($colegiado->numero_colegiatura); ?>" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label required">DNI</label>
                    <input type="text" name="dni" class="form-control" maxlength="8" 
                           value="<?php echo e($colegiado->dni); ?>" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Fecha de Colegiatura</label>
                    <input type="date" name="fecha_colegiatura" class="form-control" 
                           value="<?php echo e($colegiado->fecha_colegiatura); ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Apellido Paterno</label>
                    <input type="text" name="apellido_paterno" class="form-control" 
                           value="<?php echo e($colegiado->apellido_paterno); ?>" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Apellido Materno</label>
                    <input type="text" name="apellido_materno" class="form-control" 
                           value="<?php echo e($colegiado->apellido_materno); ?>" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Nombres</label>
                    <input type="text" name="nombres" class="form-control" 
                           value="<?php echo e($colegiado->nombres); ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" 
                           value="<?php echo e($colegiado->fecha_nacimiento); ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" 
                           value="<?php echo e($colegiado->telefono); ?>">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control" 
                           value="<?php echo e($colegiado->correo); ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Dirección</label>
                    <input type="text" name="direccion" class="form-control" 
                           value="<?php echo e($colegiado->direccion); ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3"><?php echo e($colegiado->observaciones); ?></textarea>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <a href="<?php echo url('colegiados/ver/' . $colegiado->idColegiados); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>