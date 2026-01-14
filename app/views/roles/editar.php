<div class="page-header">
    <h2>
        <i class="fas fa-user-edit me-2" style="color: #B91D22;"></i>
        Editar Rol
    </h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('roles/actualizar/' . $rol->idRol); ?>" id="formRol">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Nombre del Rol</label>
                    <input type="text" name="nombre_rol" class="form-control" required
                           value="<?php echo e($rol->nombre_rol); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="activo" <?php echo $rol->estado === 'activo' ? 'selected' : ''; ?>>
                            Activo
                        </option>
                        <option value="inactivo" <?php echo $rol->estado === 'inactivo' ? 'selected' : ''; ?>>
                            Inactivo
                        </option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?php echo e($rol->descripcion); ?></textarea>
                </div>
            </div>

            <hr>

            <h5 class="mb-3">
                <i class="fas fa-shield-alt me-2"></i>
                Asignación de Permisos
            </h5>

            <?php 
            $permisos = is_array($rol->permisos) ? $rol->permisos : [];
            ?>

            <div class="permisos-grid">
                <?php foreach ($permisosDisponibles as $modulo => $nombreModulo): ?>
                    <?php 
                    $tienePermiso = isset($permisos[$modulo]);
                    $esAll = $tienePermiso && $permisos[$modulo] === 'all';
                    $acciones = $tienePermiso && is_array($permisos[$modulo]) ? $permisos[$modulo] : [];
                    ?>
                    <div class="permiso-card">
                        <div class="permiso-header">
                            <label class="permiso-checkbox">
                                <input type="checkbox" 
                                       class="modulo-checkbox" 
                                       data-modulo="<?php echo $modulo; ?>"
                                       name="permisos[<?php echo $modulo; ?>]"
                                       value="1"
                                       onchange="toggleModulo('<?php echo $modulo; ?>')"
                                       <?php echo $tienePermiso ? 'checked' : ''; ?>>
                                <strong><?php echo e($nombreModulo); ?></strong>
                            </label>
                        </div>
                        <div class="permiso-acciones" 
                             id="acciones-<?php echo $modulo; ?>" 
                             style="display: <?php echo $tienePermiso ? 'block' : 'none'; ?>;">
                            <label class="accion-checkbox">
                                <input type="checkbox" 
                                       name="permisos[<?php echo $modulo; ?>_ver]" 
                                       value="1"
                                       class="accion-<?php echo $modulo; ?>"
                                       <?php echo ($esAll || in_array('ver', $acciones)) ? 'checked' : ''; ?>>
                                <i class="fas fa-eye"></i> Ver
                            </label>
                            <label class="accion-checkbox">
                                <input type="checkbox" 
                                       name="permisos[<?php echo $modulo; ?>_crear]" 
                                       value="1"
                                       class="accion-<?php echo $modulo; ?>"
                                       <?php echo ($esAll || in_array('crear', $acciones)) ? 'checked' : ''; ?>>
                                <i class="fas fa-plus"></i> Crear
                            </label>
                            <label class="accion-checkbox">
                                <input type="checkbox" 
                                       name="permisos[<?php echo $modulo; ?>_editar]" 
                                       value="1"
                                       class="accion-<?php echo $modulo; ?>"
                                       <?php echo ($esAll || in_array('editar', $acciones)) ? 'checked' : ''; ?>>
                                <i class="fas fa-edit"></i> Editar
                            </label>
                            <label class="accion-checkbox">
                                <input type="checkbox" 
                                       name="permisos[<?php echo $modulo; ?>_eliminar]" 
                                       value="1"
                                       class="accion-<?php echo $modulo; ?>"
                                       <?php echo ($esAll || in_array('eliminar', $acciones)) ? 'checked' : ''; ?>>
                                <i class="fas fa-trash"></i> Eliminar
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr>
            
            <div class="row">
                <div class="col-md-12">
                    <a href="<?php echo url('roles'); ?>" class="btn btn-secondary">
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

<script>
function toggleModulo(modulo) {
    const checkbox = document.querySelector(`[data-modulo="${modulo}"]`);
    const accionesDiv = document.getElementById(`acciones-${modulo}`);
    const accionCheckboxes = document.querySelectorAll(`.accion-${modulo}`);
    
    if (checkbox.checked) {
        accionesDiv.style.display = 'block';
        accionCheckboxes.forEach(cb => cb.checked = true);
    } else {
        accionesDiv.style.display = 'none';
        accionCheckboxes.forEach(cb => cb.checked = false);
    }
}
</script>