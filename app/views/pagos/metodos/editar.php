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
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Código</label>
                    <input type="text" name="codigo" class="form-control" 
                           value="<?php echo e($metodo['codigo']); ?>" required>
                    <small class="text-muted">Código corto identificador</small>
                </div>
                
                <div class="col-md-8 mb-3">
                    <label class="form-label required">Nombre del Método</label>
                    <input type="text" name="nombre" class="form-control" 
                           value="<?php echo e($metodo['nombre']); ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"><?php echo e($metodo['descripcion']); ?></textarea>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Orden</label>
                    <input type="number" name="orden" class="form-control" 
                           value="<?php echo $metodo['orden']; ?>" min="0">
                    <small class="text-muted">Menor = primero</small>
                </div>
                
                <div class="col-md-4 mb-3">
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
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Opciones</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="requiere_comprobante" 
                               id="requiereComprobante" value="1" 
                               <?php echo $metodo['requiere_comprobante'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="requiereComprobante">
                            Requiere comprobante
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Datos Adicionales (JSON)</label>
                    <textarea name="datos_adicionales" class="form-control" rows="3"
                              placeholder='Información adicional en formato JSON'><?php 
                              echo !empty($metodo['datos_adicionales']) ? 
                                  json_encode(json_decode($metodo['datos_adicionales']), JSON_PRETTY_PRINT) : 
                                  ''; 
                              ?></textarea>
                    <small class="text-muted">Ej: {"banco": "BCP", "cuenta": "123456789"}</small>
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