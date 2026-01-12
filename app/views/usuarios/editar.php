<div class="page-header">
    <h2>
        <i class="fas fa-user-edit me-2" style="color: #B91D22;"></i>
        Editar Usuario
    </h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('usuarios/actualizar/' . $usuario->idUsuario); ?>" id="formUsuario">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Nombre de Usuario</label>
                    <input type="text" name="nombre_usuario" class="form-control" 
                           value="<?php echo e($usuario->nombre_usuario); ?>" required minlength="4">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Rol</label>
                    <select name="RolId" class="form-select" required>
                        <option value="">Seleccione un rol</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo $rol['idRol']; ?>" 
                                    <?php echo $usuario->RolId == $rol['idRol'] ? 'selected' : ''; ?>>
                                <?php echo e($rol['nombre_rol']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Nombres</label>
                    <input type="text" name="nombres" class="form-control" 
                           value="<?php echo e($usuario->nombres); ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control" 
                           value="<?php echo e($usuario->apellidos); ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control" 
                           value="<?php echo e($usuario->correo); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" 
                           value="<?php echo e($usuario->telefono); ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="activo" <?php echo $usuario->estado === 'activo' ? 'selected' : ''; ?>>
                            Activo
                        </option>
                        <option value="inactivo" <?php echo $usuario->estado === 'inactivo' ? 'selected' : ''; ?>>
                            Inactivo
                        </option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <a href="<?php echo url('usuarios'); ?>" class="btn btn-secondary">
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

<div class="alert alert-info mt-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Nota:</strong> Para cambiar la contraseña del usuario, debe realizarlo desde su perfil personal.
</div>