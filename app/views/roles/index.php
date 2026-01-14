<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-user-tag me-2"></i>
            Gestión de Roles
        </h2>
        <div>
            <a href="<?php echo url('roles/crear'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Rol
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-search">
        <i class="fas fa-list me-2"></i> 
        Listado de Roles: <strong><?php echo count($roles); ?></strong>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Rol</th>
                        <th>Descripción</th>
                        <th>Permisos</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($roles)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                No hay roles registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($roles as $rol): ?>
                            <tr>
                                <td data-label="ID"><?php echo e($rol->idRol); ?></td>
                                <td data-label="Nombre">
                                    <strong><?php echo e($rol->nombre_rol); ?></strong>
                                </td>
                                <td data-label="Descripción">
                                    <?php echo e($rol->descripcion ?: '-'); ?>
                                </td>
                                <td data-label="Permisos">
                                    <?php 
                                    $permisos = is_array($rol->permisos) ? $rol->permisos : [];
                                    $totalPermisos = count($permisos);
                                    ?>
                                    <span class="badge bg-info">
                                        <?php echo $totalPermisos; ?> módulo(s)
                                    </span>
                                </td>
                                <td data-label="Estado">
                                    <?php if ($rol->estado === 'activo'): ?>
                                        <span class="badge badge-activo">
                                            <i class="fas fa-check-circle"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-inactivo">
                                            <i class="fas fa-times-circle"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center" data-label="Acciones">
                                    <a href="<?php echo url('roles/editar/' . $rol->idRol); ?>" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="confirmarEliminar(<?php echo $rol->idRol; ?>, '<?php echo e($rol->nombre_rol); ?>')"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
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

<script>
function confirmarEliminar(id, nombre) {
    confirmAction(
        `¿Está seguro que desea eliminar el rol "${nombre}"?`,
        () => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = getAppUrl('roles/eliminar/' + id);
            document.body.appendChild(form);
            form.submit();
        }
    );
}
</script>