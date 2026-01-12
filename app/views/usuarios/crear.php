<div class="page-header">
    <h2>
        <i class="fas fa-user-plus me-2" style="color: #B91D22;"></i>
        Registrar Nuevo Usuario
    </h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('usuarios/guardar'); ?>" id="formUsuario">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Nombre de Usuario</label>
                    <input type="text" name="nombre_usuario" class="form-control" required minlength="4">
                    <small class="text-muted">Mínimo 4 caracteres</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Rol</label>
                    <select name="RolId" class="form-select" required>
                        <option value="">Seleccione un rol</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?php echo $rol['idRol']; ?>">
                                <?php echo e($rol['nombre_rol']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Contraseña</label>
                    <input type="password" name="contrasenia" class="form-control" required minlength="6">
                    <small class="text-muted">Mínimo 6 caracteres</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Confirmar Contraseña</label>
                    <input type="password" name="confirmar_contrasenia" class="form-control" required minlength="6">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Nombres</label>
                    <input type="text" name="nombres" class="form-control" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <a href="<?php echo url('usuarios'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Usuario
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>