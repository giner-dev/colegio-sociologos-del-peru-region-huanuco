<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-plus-circle me-2"></i>
            Registrar Nueva Deuda
        </h2>
        <a href="<?php echo url('deudas'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-search">
        <i class="fas fa-file-invoice me-2"></i> Datos de la Deuda
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo url('deudas/guardar'); ?>" id="formRegistrarDeuda">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label required">Colegiado</label>
                    <div class="input-group">
                        <input type="hidden" name="colegiado_id" id="colegiadoIdHidden" required>
                        <input type="text" id="colegiadoSeleccionado" class="form-control" 
                               placeholder="Ningún colegiado seleccionado" readonly>
                        <button type="button" class="btn btn-primary" id="btnSeleccionarColegiado">
                            <i class="fas fa-search me-1"></i> Seleccionar Colegiado
                        </button>
                    </div>
                    <small class="text-muted">Haga clic en el botón para buscar y seleccionar un colegiado</small>
                </div>
            </div>

            <!-- NUEVA SECCIÓN: TIPO DE DEUDA -->
            <div class="card mb-3" style="border: 2px solid #B91D22;">
                <div class="card-header" style="background: linear-gradient(135deg, #B91D22, #8a1519); color: white;">
                    <i class="fas fa-list-alt me-2"></i> Tipo de Deuda
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label required">Seleccione el tipo de deuda</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="tipo_deuda" id="tipoConcepto" value="concepto" checked>
                                <label class="form-check-label" for="tipoConcepto">
                                    <strong>Deuda con Concepto Predefinido</strong>
                                    <br><small class="text-muted">Usar concepto de pago existente (cuota mensual, certificados, etc.)</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_deuda" id="tipoManual" value="manual">
                                <label class="form-check-label" for="tipoManual">
                                    <strong>Deuda Manual Personalizada</strong>
                                    <br><small class="text-muted">Para deudas antiguas, ajustes o casos especiales sin concepto predefinido</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--SECCIÓN: CONCEPTO PREDEFINIDO -->            
            <div id="seccionConcepto">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Concepto de la Deuda</label>
                        <select name="concepto_id" id="selectConcepto" class="form-select">
                            <option value="">Seleccione un concepto...</option>
                            <?php foreach ($conceptos as $concepto): ?>
                                <option value="<?php echo $concepto['idConcepto']; ?>" 
                                        data-monto="<?php echo $concepto['monto_sugerido']; ?>"
                                        data-recurrente="<?php echo $concepto['es_recurrente'] ? '1' : '0'; ?>"
                                        data-frecuencia="<?php echo e($concepto['frecuencia'] ?? ''); ?>"
                                        data-dia-vencimiento="<?php echo $concepto['dia_vencimiento'] ?? ''; ?>">
                                    <?php echo e($concepto['nombre_completo']); ?>
                                    <?php if ($concepto['es_recurrente']): ?>
                                        <span class="badge bg-info">Recurrente</span>
                                    <?php endif; ?>
                                    (S/ <?php echo number_format($concepto['monto_sugerido'], 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Descripción Específica</label>
                        <input type="text" name="descripcion_deuda_concepto" id="descripcionConcepto" class="form-control" 
                               placeholder="Ej: Cuota mensual octubre 2024">
                        <small class="text-muted">Si se deja vacío, se usará el nombre del concepto</small>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN: DEUDA MANUAL -->
            <div id="seccionManual" style="display: none;">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Deuda Manual:</strong> Use esta opción para deudas que no corresponden a un concepto predefinido (ej: deudas antiguas, ajustes especiales, etc.)
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label required">Descripción de la Deuda Manual</label>
                        <input type="text" name="concepto_manual" id="conceptoManual" class="form-control" 
                               placeholder="Ej: Deuda pendiente años 2018-2020">
                        <small class="text-muted">Describa brevemente el motivo de esta deuda</small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Descripción Detallada (Opcional)</label>
                        <input type="text" name="descripcion_deuda_manual" id="descripcionManual" class="form-control" 
                               placeholder="Detalles adicionales sobre esta deuda...">
                    </div>
                </div>

                <input type="hidden" name="es_deuda_manual" id="esDeudaManual" value="0">
            </div>

            
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label required">Monto</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="number" name="monto_esperado" id="montoDeuda" 
                               class="form-control" step="0.01" min="0.01" 
                               required placeholder="0.00">
                    </div>
                    <small class="text-muted">Se auto-completa según el concepto seleccionado</small>
                </div>
                
                <div class="col-md-4 mb-3" id="grupoFechaVencimiento">
                    <label class="form-label required">Fecha de Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" class="form-control" required>
                    <small class="text-muted">Fecha en que vence la deuda</small>
                </div>
                
                <div class="col-md-4 mb-3" id="grupoFechaMaxima">
                    <label class="form-label">Fecha Máxima de Pago</label>
                    <input type="date" name="fecha_maxima_pago" class="form-control">
                    <small class="text-muted">Para aplicar recargos después de esta fecha</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3" 
                              placeholder="Observaciones adicionales sobre esta deuda..."></textarea>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Nota:</strong> El estado de la deuda se determinará automáticamente según la fecha de vencimiento.
                Si la fecha ya pasó, se registrará como "Vencida".
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <a href="<?php echo url('deudas'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Registrar Deuda
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- Modal de Selección de Colegiado - VERSIÓN PURA SIN BOOTSTRAP -->
<div class="custom-modal" id="modalSeleccionarColegiado">
    <div class="custom-modal-overlay"></div>
    <div class="custom-modal-dialog">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h5 class="custom-modal-title">
                    <i class="fas fa-users me-2"></i>
                    Seleccionar Colegiado
                </h5>
                <button type="button" class="custom-modal-close" id="btnCerrarModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="custom-modal-body">
                <!-- Buscador -->
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="buscarColegiadoModal" 
                               placeholder="Buscar por N° Colegiatura, DNI o Nombre...">
                        <button class="btn btn-primary" type="button" id="btnBuscarModal">
                            Buscar
                        </button>
                    </div>
                </div>
                
                <!-- Tabla de resultados -->
                <div class="custom-modal-table-wrapper">
                    <table class="table table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>N° Col.</th>
                                <th>DNI</th>
                                <th>Nombre Completo</th>
                                <th>Estado</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tablaColegiadosModal">
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="custom-spinner">
                                        <div class="spinner"></div>
                                        <p>Cargando...</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <nav aria-label="Paginación modal" class="mt-3">
                    <ul class="custom-pagination" id="paginacionModal">
                    </ul>
                </nav>
            </div>
            
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelarModal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
            </div>
        </div>
    </div>
</div>