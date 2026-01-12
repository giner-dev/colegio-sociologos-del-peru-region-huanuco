<div class="page-header">
    <h2>
        <i class="fas fa-user me-2"></i>
        Mi Perfil
    </h2>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="perfil-avatar">
                    <?php 
                        $iniciales = '';
                        if (!empty($usuario->nombres)) {
                            $iniciales = strtoupper(substr($usuario->nombres, 0, 1));
                        }
                        if (!empty($usuario->apellidos)) {
                            $iniciales .= strtoupper(substr($usuario->apellidos, 0, 1));
                        }
                        echo $iniciales;
                    ?>
                </div>
                <h4 class="mt-3"><?php echo e($usuario->getNombreCompleto()); ?></h4>
                <p class="text-muted">@<?php echo e($usuario->nombre_usuario); ?></p>
                <span class="badge bg-info"><?php echo e($usuario->nombre_rol); ?></span>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-search">
                <i class="fas fa-info-circle me-2"></i> Información
            </div>
            <div class="card-body">
                <div class="info-item">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <small>Usuario desde</small>
                        <strong><?php echo formatDate($usuario->fecha_creacion); ?></strong>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <small>Último acceso</small>
                        <strong><?php echo $usuario->fecha_ultimo_acceso ? formatDateTime($usuario->fecha_ultimo_acceso) : 'Nunca'; ?></strong>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <small>Estado</small>
                        <strong class="<?php echo $usuario->estado === 'activo' ? 'text-success' : 'text-danger'; ?>">
                            <?php echo ucfirst($usuario->estado); ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-search">
                <i class="fas fa-edit me-2"></i> Datos Personales
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo url('usuarios/actualizar-perfil'); ?>" id="formPerfil">
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
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-warning">
                <i class="fas fa-key me-2"></i> Cambiar Contraseña
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo url('usuarios/cambiar-password'); ?>" id="formPassword">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label required">Contraseña Actual</label>
                            <input type="password" name="password_actual" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Nueva Contraseña</label>
                            <input type="password" name="password_nueva" class="form-control" required minlength="6">
                            <small class="text-muted">Mínimo 6 caracteres</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Confirmar Nueva Contraseña</label>
                            <input type="password" name="password_confirmar" class="form-control" required minlength="6">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key"></i> Cambiar Contraseña
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>