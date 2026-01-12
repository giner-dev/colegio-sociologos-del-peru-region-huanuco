<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-plus-circle me-2"></i>
            Registrar Egreso
        </h2>

        <a href="<?php echo url('egresos'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-file-invoice me-2"></i> Datos del Egreso
    </div>

    <div class="card-body">
        <form 
            method="POST" 
            action="<?php echo url('egresos/guardar'); ?>" 
            id="formEgreso" 
            enctype="multipart/form-data"
        >
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Descripción</label>
                    <textarea 
                        name="descripcion" 
                        class="form-control" 
                        rows="3" 
                        required 
                        placeholder="Describe el egreso..."
                    ></textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipo de Gasto</label>
                    <select name="tipo_gasto_id" class="form-select">
                        <option value="">Sin categoría</option>

                        <?php foreach ($tiposGasto as $tipo): ?>
                            <option value="<?php echo $tipo['idTipo_Gasto']; ?>">
                                <?php echo e($tipo['nombre_tipo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label required">Monto</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input 
                            type="number" 
                            name="monto" 
                            class="form-control" 
                            step="0.01" 
                            min="0.01" 
                            required 
                            placeholder="0.00"
                        >
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label required">Fecha del Egreso</label>
                    <input 
                        type="date" 
                        name="fecha_egreso" 
                        class="form-control" 
                        value="<?php echo date('Y-m-d'); ?>" 
                        required
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Número de Comprobante</label>
                    <input 
                        type="text" 
                        name="num_comprobante" 
                        class="form-control" 
                        placeholder="Ej: F001-00123456"
                        maxlength="50"
                    >
                    <small class="text-muted">
                        Número de factura, boleta o recibo
                    </small>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Comprobante</label>
                    <input 
                        type="file" 
                        name="comprobante" 
                        class="form-control" 
                        accept=".jpg,.jpeg,.png,.pdf"
                    >
                    <small class="text-muted">
                        JPG, PNG o PDF (Max: 5MB)
                    </small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea 
                        name="observaciones" 
                        class="form-control" 
                        rows="2" 
                        placeholder="Observaciones adicionales..."
                    ></textarea>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Registrar Egreso
                    </button>

                    <a href="<?php echo url('egresos'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>