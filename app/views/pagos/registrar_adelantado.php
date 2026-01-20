<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-calendar-check me-2"></i>
            Registrar Pago Adelantado
        </h2>
        <a href="<?php echo url('pagos'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Pago Adelantado:</strong> Esta opción permite pagar múltiples periodos futuros de deudas recurrentes (cuotas mensuales, trimestrales, etc.).
</div>

<div class="card mb-4">
    <div class="card-header bg-search">
        <i class="fas fa-user-check me-2"></i> Paso 1: Seleccionar Colegiado
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-12">
                <label class="form-label">Buscar Colegiado</label>
                <input type="text" id="searchColegiado" class="form-control" 
                       placeholder="Buscar por N° Colegiatura, DNI o Nombre...">
            </div>
        </div>
        
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-hover">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>N° Colegiatura</th>
                        <th>DNI</th>
                        <th>Nombre Completo</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody id="tablaColegiados">
                    <?php if (empty($colegiados)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No hay colegiados con programaciones activas
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($colegiados as $col): ?>
                            <tr class="colegiado-row" 
                                data-colegiado-id="<?php echo $col->idColegiados; ?>"
                                data-numero="<?php echo e($col->numero_colegiatura); ?>"
                                data-dni="<?php echo e($col->dni); ?>"
                                data-nombre="<?php echo e($col->getNombreCompleto()); ?>">
                                <td><?php echo formatNumeroColegiatura($col->numero_colegiatura); ?></td>
                                <td><?php echo e($col->dni); ?></td>
                                <td><?php echo e($col->getNombreCompleto()); ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary btn-seleccionar-colegiado"
                                            data-colegiado-id="<?php echo $col->idColegiados; ?>"
                                            data-nombre="<?php echo e($col->getNombreCompleto()); ?>"
                                            data-numero="<?php echo formatNumeroColegiatura($col->numero_colegiatura); ?>">
                                        <i class="fas fa-check-circle me-1"></i> Seleccionar
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

<div id="paso2" style="display: none;">
    <form method="POST" action="<?php echo url('pagos/guardar-adelantado'); ?>" enctype="multipart/form-data" id="formPagoAdelantado">
        <input type="hidden" name="colegiado_id" id="inputColegiadoId">
        
        <div class="card mb-4">
            <div class="card-header bg-search">
                <i class="fas fa-calendar-alt me-2"></i> Paso 2: Datos del Pago Adelantado
            </div>
            <div class="card-body">
                <div class="alert alert-secondary">
                    <strong>Colegiado seleccionado:</strong> <span id="colegiadoSeleccionadoInfo"></span>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Concepto Recurrente</label>
                        <select name="programacion_id" id="selectProgramacion" class="form-select" required>
                            <option value="">Cargando programaciones...</option>
                        </select>
                        <small class="text-muted">Solo se muestran conceptos con programación activa</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">¿Cuántos periodos desea pagar?</label>
                        <input type="number" name="meses_adelantado" id="inputMesesAdelantado" 
                               class="form-control" min="1" max="36" required
                               placeholder="Ej: 12 para un año completo">
                        <small class="text-muted">Máximo 36 periodos (3 años)</small>
                    </div>
                </div>
                
                <div class="row" id="infoCalculoContainer" style="display: none;">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-calculator me-2"></i> Cálculo del Pago</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Monto por periodo:</strong> S/ <span id="montoPorPeriodo">0.00</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Periodos a pagar:</strong> <span id="cantidadPeriodos">0</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Total a pagar:</strong> <span class="text-danger fs-5">S/ <span id="montoTotalCalculado">0.00</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label required">Monto</label>
                        <div class="input-group">
                            <span class="input-group-text">S/</span>
                            <input type="number" name="monto" id="inputMonto" 
                                   class="form-control" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label required">Fecha de Pago</label>
                        <input type="date" name="fecha_pago" class="form-control" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Método de Pago</label>
                        <select name="metodo_pago_id" id="selectMetodo" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($metodos as $metodo): ?>
                                <option value="<?php echo $metodo['idMetodo']; ?>"
                                        data-requiere="<?php echo $metodo['requiere_comprobante'] ? '1' : '0'; ?>">
                                    <?php echo e($metodo['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row" id="comprobante-requerido" style="display: none;">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">N° Comprobante</label>
                        <input type="text" name="numero_comprobante" id="inputComprobante" class="form-control">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Archivo Comprobante</label>
                        <input type="file" name="archivo_comprobante" class="form-control" 
                               accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <hr>
                        <button type="button" class="btn btn-secondary" onclick="volverPaso1Adelantado()">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Registrar Pago Adelantado
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function volverPaso1Adelantado() {
    document.getElementById('paso2').style.display = 'none';
    document.getElementById('tablaColegiados').style.display = '';
}
</script>