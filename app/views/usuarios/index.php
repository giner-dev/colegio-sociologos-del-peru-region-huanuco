<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-user-cog me-2"></i>
            Gestión de Usuarios
        </h2>
        <a href="<?php echo url('usuarios/crear'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Usuario
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-search">
        <i class="fas fa-list me-2"></i> 
        Usuarios del Sistema: <strong><?php echo count($usuarios); ?></strong>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                No hay usuarios registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td data-label="Usuario">
                                    <strong><?php echo e($usuario->nombre_usuario); ?></strong>
                                </td>
                                <td data-label="Nombre Completo">
                                    <?php echo e($usuario->getNombreCompleto()); ?>
                                </td>
                                <td data-label="Correo">
                                    <?php echo $usuario->correo ? e($usuario->correo) : '<span class="text-muted">-</span>'; ?>
                                </td>
                                <td data-label="Rol">
                                    <span class="badge bg-info">
                                        <?php echo e($usuario->nombre_rol); ?>
                                    </span>
                                </td>
                                <td data-label="Estado">
                                    <?php if ($usuario->estado === 'activo'): ?>
                                        <span class="badge badge-activo">
                                            <i class="fas fa-check-circle"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-inactivo">
                                            <i class="fas fa-times-circle"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Último Acceso">
                                    <?php echo $usuario->fecha_ultimo_acceso ? formatDateTime($usuario->fecha_ultimo_acceso) : '<span class="text-muted">Nunca</span>'; ?>
                                </td>
                                <td class="text-center" data-label="Acciones">
                                    <a href="<?php echo url('usuarios/editar/' . $usuario->idUsuario); ?>" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="cambiarEstadoUsuario(<?php echo $usuario->idUsuario; ?>, '<?php echo $usuario->estado === 'activo' ? 'inactivo' : 'activo'; ?>')"
                                            class="btn btn-sm <?php echo $usuario->estado === 'activo' ? 'btn-danger' : 'btn-success'; ?>" 
                                            title="<?php echo $usuario->estado === 'activo' ? 'Desactivar' : 'Activar'; ?>">
                                        <i class="fas fa-<?php echo $usuario->estado === 'activo' ? 'ban' : 'check'; ?>"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<form id="formCambiarEstado" method="POST" style="display: none;">
    <input type="hidden" name="estado" id="nuevoEstado">
</form>