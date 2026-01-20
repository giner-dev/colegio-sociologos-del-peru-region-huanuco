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
        <!-- FORMULARIO DE BÚSQUEDA -->
        <form method="GET" action="<?php echo url('pagos/registrar-adelantado'); ?>" class="mb-3">
            <div class="row">
                <div class="col-md-10">
                    <label class="form-label">Buscar Colegiado</label>
                    <input type="text" name="busqueda" id="searchColegiado" class="form-control" 
                           value="<?php echo e($busqueda ?? ''); ?>"
                           placeholder="Buscar por N° Colegiatura, DNI o Nombre...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                </div>
            </div>
        </form>
        <?php if (!empty($busqueda)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Mostrando resultados para: <strong>"<?php echo e($busqueda); ?>"</strong>
                <a href="<?php echo url('pagos/registrar-adelantado'); ?>" class="btn btn-sm btn-secondary ms-2">
                    <i class="fas fa-times me-1"></i> Limpiar
                </a>
            </div>
        <?php endif; ?>

        <!-- INFORMACIÓN DE RESULTADOS -->
        <div class="mb-2">
            <small class="text-muted">
                Mostrando <?php echo count($colegiados); ?> de <?php echo $pagination['total']; ?> colegiado(s) con programaciones activas
            </small>
        </div>
        

        <!-- TABLA -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
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
                                <?php if (!empty($busqueda)): ?>
                                    <i class="fas fa-search me-2"></i>
                                    No se encontraron colegiados que coincidan con la búsqueda
                                <?php else: ?>
                                    <i class="fas fa-info-circle me-2"></i>
                                    No hay colegiados con programaciones activas
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($colegiados as $col): ?>
                            <tr>
                                <td><?php echo formatNumeroColegiatura($col->numero_colegiatura); ?></td>
                                <td><?php echo e($col->dni); ?></td>
                                <td>
                                    <?php echo e($col->getNombreCompleto()); ?>
                                    <!-- Mostrar estado -->
                                    <span class="badge <?php echo $col->estado === 'habilitado' ? 'bg-success' : 'bg-danger'; ?> ms-2">
                                        <?php echo $col->estado === 'habilitado' ? 'Habilitado' : 'Inhabilitado'; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary btn-seleccionar-colegiado"
                                            data-colegiado-id="<?php echo $col->idColegiados; ?>"
                                            data-nombre="<?php echo e($col->getNombreCompleto()); ?>"
                                            data-numero="<?php echo formatNumeroColegiatura($col->numero_colegiatura); ?>"
                                            <?php echo ($col->estado === 'inhabilitado') ? 'title="Colegiado inhabilitado - Puede pagar adelantado para habilitarse"' : ''; ?>>
                                        <i class="fas fa-check-circle me-1"></i> Seleccionar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <?php if ($pagination['totalPages'] > 1): ?>
            <nav aria-label="Paginación de colegiados" class="mt-3">
                <ul class="pagination justify-content-center">
                    <!-- Primera página -->
                    <li class="page-item <?php echo $pagination['page'] <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo buildPaginationUrlAdelantado(1, $busqueda); ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                    </li>
                    
                    <!-- Anterior -->
                    <li class="page-item <?php echo $pagination['page'] <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo buildPaginationUrlAdelantado($pagination['page'] - 1, $busqueda); ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>
                    
                    <!-- Números de página -->
                    <?php
                    $startPage = max(1, $pagination['page'] - 2);
                    $endPage = min($pagination['totalPages'], $pagination['page'] + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li class="page-item <?php echo $i === $pagination['page'] ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo buildPaginationUrlAdelantado($i, $busqueda); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Siguiente -->
                    <li class="page-item <?php echo $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo buildPaginationUrlAdelantado($pagination['page'] + 1, $busqueda); ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                    
                    <!-- Última página -->
                    <li class="page-item <?php echo $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo buildPaginationUrlAdelantado($pagination['totalPages'], $busqueda); ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    </li>
                </ul>
                
                <p class="text-center text-muted mb-0">
                    Página <?php echo $pagination['page']; ?> de <?php echo $pagination['totalPages']; ?>
                </p>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php
// Helper para construir URLs de paginación
function buildPaginationUrlAdelantado($page, $busqueda = '') {
    $params = ['page' => $page];
    if (!empty($busqueda)) {
        $params['busqueda'] = $busqueda;
    }
    return url('pagos/registrar-adelantado') . '?' . http_build_query($params);
}
?>

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