<div class="page-header">
    <h2>
        <i class="fas fa-edit me-2" style="color: #B91D22;"></i>
        Editar Concepto de Pago
    </h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('pagos/conceptos/actualizar/' . $concepto['idConcepto']); ?>">
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label required">Nombre del Concepto</label>
                    <input type="text" name="nombre" class="form-control" 
                           value="<?php echo e($concepto['nombre_completo']); ?>" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Tipo</label>
                    <select name="tipo" class="form-select" required>
                        <option value="cuota" <?php echo $concepto['tipo_concepto'] === 'cuota' ? 'selected' : ''; ?>>
                            Cuota
                        </option>
                        <option value="certificado" <?php echo $concepto['tipo_concepto'] === 'certificado' ? 'selected' : ''; ?>>
                            Certificado
                        </option>
                        <option value="tramite" <?php echo $concepto['tipo_concepto'] === 'tramite' ? 'selected' : ''; ?>>
                            Trámite
                        </option>
                        <option value="otro" <?php echo $concepto['tipo_concepto'] === 'otro' ? 'selected' : ''; ?>>
                            Otro
                        </option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2"><?php echo e($concepto['descripcion']); ?></textarea>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label required">Monto Sugerido</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="number" name="monto" class="form-control" 
                               step="0.01" min="0" value="<?php echo $concepto['monto_sugerido']; ?>" required>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="activo" <?php echo $concepto['estado'] === 'activo' ? 'selected' : ''; ?>>
                            Activo
                        </option>
                        <option value="inactivo" <?php echo $concepto['estado'] === 'inactivo' ? 'selected' : ''; ?>>
                            Inactivo
                        </option>
                    </select>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Opciones</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="requiere_comprobante" 
                               id="requiereComprobante" value="1" 
                               <?php echo $concepto['requiere_comprobante'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="requiereComprobante">
                            Requiere comprobante
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <a href="<?php echo url('pagos/conceptos'); ?>" class="btn btn-secondary">
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