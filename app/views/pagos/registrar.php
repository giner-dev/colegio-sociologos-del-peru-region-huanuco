
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
            <span class="badge">1</span>
            Seleccionar Colegiado con Deudas
        </h5>
    </div>
    <div class="card-body">
        <!-- Buscador -->
        <form method="GET" action="<?php echo url('pagos/registrar'); ?>" class="mb-3">
            <div class="row">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="busqueda" class="form-control" 
                               value="<?php echo e($busqueda ?? ''); ?>"
                               placeholder="Buscar por N° Colegiatura, DNI o Nombre...">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                        <?php if (!empty($busqueda)): ?>
                            <a href="<?php echo url('pagos/registrar'); ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-2 text-end">
                    <span class="badge bg-info fs-6">
                        <i class="fas fa-users me-1"></i>
                        <?php echo $pagination['total']; ?> colegiados
                    </span>
                </div>
            </div>
        </form>

        <!-- Tabla de Colegiados -->
        <div class="table-responsive">
            <table class="table table-hover">
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
                                No se encontraron colegiados con deudas pendientes
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($colegiados as $col): ?>
                            <tr>
                                <td><strong><?php echo formatNumeroColegiatura($col['numero_colegiatura']); ?></strong></td>
                                <td><?php echo e($col['dni']); ?></td>
                                <td><?php echo e($col['apellido_paterno'] . ' ' . $col['apellido_materno'] . ', ' . $col['nombres']); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">
                                        <?php echo $col['cantidad_deudas']; ?> deuda(s)
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="text-danger fw-bold">
                                        <?php echo formatMoney($col['total_deuda']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-primary btn-seleccionar-colegiado" 
                                            data-colegiado-id="<?php echo $col['idColegiados']; ?>"
                                            data-nombre="<?php echo e($col['apellido_paterno'] . ' ' . $col['apellido_materno'] . ', ' . $col['nombres']); ?>"
                                            data-numero="<?php echo formatNumeroColegiatura($col['numero_colegiatura']); ?>">
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
        <?php if ($pagination['totalPages'] > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <!-- Primera -->
                    <li class="page-item <?php echo $pagination['page'] <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('pagos/registrar?page=1' . (!empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '')); ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                    </li>
                    
                    <!-- Anterior -->
                    <li class="page-item <?php echo $pagination['page'] <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('pagos/registrar?page=' . ($pagination['page'] - 1) . (!empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '')); ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>
                    
                    <!-- Páginas -->
                    <?php
                    $start = max(1, $pagination['page'] - 2);
                    $end = min($pagination['totalPages'], $pagination['page'] + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li class="page-item <?php echo $i == $pagination['page'] ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo url('pagos/registrar?page=' . $i . (!empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '')); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Siguiente -->
                    <li class="page-item <?php echo $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('pagos/registrar?page=' . ($pagination['page'] + 1) . (!empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '')); ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                    
                    <!-- Última -->
                    <li class="page-item <?php echo $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo url('pagos/registrar?page=' . $pagination['totalPages'] . (!empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '')); ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    </li>
                </ul>
                <p class="text-center text-muted mb-0">
                    Página <?php echo $pagination['page']; ?> de <?php echo $pagination['totalPages']; ?>
                    (<?php echo $pagination['total']; ?> colegiado(s) con deudas)
                </p>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Paso 2: Seleccionar Deuda -->
<div class="card mb-4" id="paso2" style="display: none;">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <span class="badge">2</span>
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

        <!-- BUSCADOR DE DEUDAS - AQUÍ -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchDeuda" class="form-control" 
                           placeholder="Buscar deuda por concepto o descripción...">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearchDeuda">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mensaje cuando no hay deudas -->
        <div id="mensaje-sin-deudas" class="alert alert-warning" style="display: none;">
            <i class="fas fa-info-circle me-2"></i>
            El colegiado seleccionado no tiene deudas pendientes.
        </div>

        <!-- Contenedor de tabla con búsqueda y paginación -->
        <div id="tabla-deudas-container">
            <!-- Estos elementos deben existir desde el inicio -->
            <div id="deudasPaginationContainer" class="mb-3" style="display: none;">
                <div class="d-flex justify-content-between align-items-center">
                    <div id="deudasPageInfo" class="text-muted small"></div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="deudasPagination"></ul>
                    </nav>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">
                                <input type="checkbox" id="selectAllDeudas" class="form-check-input">
                            </th>
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
                        <!-- El contenido se llenará dinámicamente -->
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
                               step="0.01" min="0.01" required placeholder="0.00"
                               oninput="validarMonto(this)">
                    </div>
                    <small class="text-muted">
                        <span id="tipo-monto-info"></span>
                        <span id="max-monto">S/ 0.00</span>
                    </small>
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

<script>
// Seleccionar todas las deudas
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAllDeudas');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="deuda_checkbox"]:not(:disabled)');
            
            if (this.checked) {
                // Seleccionar todas las visibles
                checkboxes.forEach((checkbox, index) => {
                    if (!checkbox.checked) {
                        checkbox.checked = true;
                        const dataIndex = checkbox.dataset.index;
                        if (dataIndex !== undefined) {
                            // Usar la función del módulo
                            if (typeof window.PagosModule?.seleccionarDeuda === 'function') {
                                window.PagosModule.seleccionarDeuda(parseInt(dataIndex));
                            }
                        }
                    }
                });
            } else {
                // Deseleccionar todas
                window.PagosModule.deudasSeleccionadas = [];
                window.PagosModule.tipoSeleccionActual = null;
                
                // Actualizar UI
                if (typeof window.PagosModule?.actualizarSeleccionDeudasUI === 'function') {
                    window.PagosModule.actualizarSeleccionDeudasUI();
                }
                if (typeof window.PagosModule?.actualizarResumenSeleccion === 'function') {
                    window.PagosModule.actualizarResumenSeleccion();
                }
            }
        });
    }
});
</script>