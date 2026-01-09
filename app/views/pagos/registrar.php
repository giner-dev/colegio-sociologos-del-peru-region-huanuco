
<div class="page-header">
    <h2>
        <i class="fas fa-plus-circle me-2"></i>
        Registrar Nuevo Pago
    </h2>
</div>

<!-- Paso 1: Seleccionar Colegiado con Tabla Paginada -->
<div class="card mb-4" id="paso1">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <span class="badge bg-light text-primary me-2">1</span>
            Seleccionar Colegiado con Deudas
        </h5>
    </div>
    <div class="card-body">
        <!-- Buscador -->
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchColegiado" class="form-control" 
                           placeholder="Buscar por N° Colegiatura, DNI o Nombre...">
                </div>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-info fs-6">
                    <i class="fas fa-users me-1"></i>
                    <span id="totalColegiadosConDeudas"><?php echo count($colegiados); ?></span> 
                    colegiados con deudas
                </span>
            </div>
        </div>

        <!-- Tabla de Colegiados -->
        <div class="table-responsive">
            <table class="table table-hover" id="tablaColegiadosConDeudas">
                <thead class="table-dark">
                    <tr>
                        <th width="10%">N° Colegiatura</th>
                        <th width="15%">DNI</th>
                        <th width="35%">Nombre Completo</th>
                        <th width="15%" class="text-center">Deudas Pendientes</th>
                        <th width="15%" class="text-center">Monto Total</th>
                        <th width="10%" class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($colegiados)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle me-2"></i>
                                No hay colegiados con deudas pendientes
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($colegiados as $colegiado): ?>
                            <tr class="colegiado-row" 
                                data-colegiado-id="<?php echo $colegiado->idColegiados; ?>"
                                data-numero="<?php echo strtolower($colegiado->numero_colegiatura); ?>"
                                data-dni="<?php echo $colegiado->dni; ?>"
                                data-nombre="<?php echo strtolower($colegiado->getNombreCompleto()); ?>">
                                <td><strong><?php echo e($colegiado->numero_colegiatura); ?></strong></td>
                                <td><?php echo e($colegiado->dni); ?></td>
                                <td><?php echo e($colegiado->getNombreCompleto()); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark" id="count-deudas-<?php echo $colegiado->idColegiados; ?>">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="text-danger fw-bold" id="monto-total-<?php echo $colegiado->idColegiados; ?>">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-primary btn-seleccionar-colegiado" 
                                            data-colegiado-id="<?php echo $colegiado->idColegiados; ?>"
                                            data-nombre="<?php echo e($colegiado->getNombreCompleto()); ?>"
                                            data-numero="<?php echo e($colegiado->numero_colegiatura); ?>">
                                        <i class="fas fa-hand-pointer me-1"></i> Seleccionar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <nav id="paginationContainer" class="mt-3">
            <ul class="pagination justify-content-center" id="pagination"></ul>
        </nav>
    </div>
</div>

<!-- Paso 2: Seleccionar Deuda -->
<div class="card mb-4" id="paso2" style="display: none;">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <span class="badge bg-light text-success me-2">2</span>
            Seleccionar Deuda a Pagar
        </h5>
    </div>
    <div class="card-body">
        <!-- Info del Colegiado Seleccionado -->
        <div class="alert alert-info mb-3">
            <div class="row align-items-center">
                <div class="col-md-10">
                    <i class="fas fa-user me-2"></i>
                    <strong>Colegiado seleccionado:</strong>
                    <span id="colegiadoSeleccionadoInfo"></span>
                </div>
                <div class="col-md-2 text-end">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="volverPaso1()">
                        <i class="fas fa-arrow-left me-1"></i> Cambiar
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de Deudas Pendientes -->
        <div id="mensaje-sin-deudas" class="alert alert-warning" style="display: none;">
            <i class="fas fa-info-circle me-2"></i>
            El colegiado seleccionado no tiene deudas pendientes.
        </div>

        <div id="tabla-deudas-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="5%"></th>
                            <th>Concepto</th>
                            <th>Descripción</th>
                            <th class="text-end">Monto Total</th>
                            <th class="text-end">Pagado</th>
                            <th class="text-end">Saldo</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="deudas-body">
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                                <p class="mt-2 mb-0">Cargando deudas...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Deuda Seleccionada -->
        <div id="deuda-seleccionada-container" class="mt-4" style="display: none;">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-check-circle me-2"></i> Deuda Seleccionada
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Concepto:</strong>
                            <div id="deuda-concepto" class="fs-6"></div>
                        </div>
                        <div class="col-md-4">
                            <strong>Saldo Pendiente:</strong>
                            <div id="deuda-saldo" class="text-danger fs-4 fw-bold"></div>
                        </div>
                        <div class="col-md-4">
                            <strong>Vencimiento:</strong>
                            <div id="deuda-vencimiento" class="fs-6"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 text-end">
            <button type="button" class="btn btn-secondary" onclick="volverPaso1()">
                <i class="fas fa-arrow-left me-1"></i> Anterior
            </button>
            <button type="button" class="btn btn-success" id="btnSiguientePaso3" disabled onclick="irPaso3()">
                Siguiente <i class="fas fa-arrow-right ms-1"></i>
            </button>
        </div>
    </div>
</div>

<!-- Paso 3: Detalles del Pago -->
<div class="card mb-4" id="paso3" style="display: none;">
    <div class="card-header bg-warning">
        <h5 class="mb-0">
            <span class="badge bg-light text-warning me-2">3</span>
            Ingresar Detalles del Pago
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo url('pagos/guardar'); ?>" 
              id="formRegistrarPago" enctype="multipart/form-data">
            
            <input type="hidden" name="colegiado_id" id="inputColegiadoId">
            <input type="hidden" name="deuda_id" id="inputDeudaId">
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Monto a Pagar</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="number" name="monto" id="inputMonto" class="form-control" 
                               step="0.01" min="0.01" required placeholder="0.00">
                    </div>
                    <small class="text-muted">Máximo: <span id="max-monto">S/ 0.00</span></small>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Fecha de Pago</label>
                    <input type="date" name="fecha_pago" class="form-control" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Método de Pago</label>
                    <select name="metodo_pago_id" id="selectMetodo" class="form-select" required>
                        <option value="">Seleccione...</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Número de Comprobante</label>
                    <input type="text" name="numero_comprobante" id="inputComprobante" 
                           class="form-control" placeholder="Ej: 001-00123">
                    <small class="text-muted" id="comprobante-requerido" style="display: none;">
                        <i class="fas fa-exclamation-triangle text-warning"></i> Este método requiere comprobante
                    </small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Archivo de Comprobante</label>
                    <input type="file" name="archivo_comprobante" class="form-control"
                           accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                    <small class="text-muted">Formatos: JPG, PNG, PDF, DOC (Max: 5MB)</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3"
                              placeholder="Observaciones adicionales del pago..."></textarea>
                </div>
            </div>
            
            <!-- Resumen -->
            <div class="card border-success mb-3">
                <div class="card-body text-center">
                    <h5 class="text-success mb-2">TOTAL A REGISTRAR</h5>
                    <h2 class="text-success mb-0" id="total-registrar">S/ 0.00</h2>
                </div>
            </div>
            
            <div class="text-end">
                <button type="button" class="btn btn-secondary" onclick="volverPaso2()">
                    <i class="fas fa-arrow-left me-1"></i> Anterior
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Registrar Pago
                </button>
            </div>
        </form>
    </div>
</div>