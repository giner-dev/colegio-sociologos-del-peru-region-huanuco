<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-user me-2"></i>
            Información del Colegiado
        </h2>
        <div>
            <a href="<?php echo url('colegiados'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <?php if (hasPermission('colegiados', 'editar')): ?>
                <a href="<?php echo url('colegiados/editar/' . $colegiado->idColegiados); ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Información Personal -->
<div class="card mb-4">
    <div class="card-header bg-search">
        <i class="fas fa-id-card me-2"></i> Datos Personales
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="info-label">N° Colegiatura</div>
                <div class="info-value">
                    <strong><?php echo formatNumeroColegiatura($colegiado->numero_colegiatura); ?></strong>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="info-label">DNI</div>
                <div class="info-value"><?php echo e($colegiado->dni); ?></div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="info-label">Fecha de Colegiatura</div>
                <div class="info-value"><?php echo formatDate($colegiado->fecha_colegiatura); ?></div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="info-label">Estado</div>
                <div>
                    <?php if ($colegiado->estado === 'habilitado'): ?>
                        <span class="badge bg-success status-badge-large">
                            <i class="fas fa-check-circle"></i> HABILITADO
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger status-badge-large">
                            <i class="fas fa-times-circle"></i> INHABILITADO
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <hr>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="info-label">Apellido Paterno</div>
                <div class="info-value"><?php echo e($colegiado->apellido_paterno); ?></div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="info-label">Apellido Materno</div>
                <div class="info-value"><?php echo e($colegiado->apellido_materno); ?></div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="info-label">Nombres</div>
                <div class="info-value"><?php echo e($colegiado->nombres); ?></div>
            </div>
        </div>
        
        <hr>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="info-label">Fecha de Nacimiento</div>
                <div class="info-value">
                    <?php if ($colegiado->fecha_nacimiento): ?>
                        <?php echo formatDate($colegiado->fecha_nacimiento); ?>
                        <small class="text-muted">(<?php echo $colegiado->getEdad(); ?> años)</small>
                    <?php else: ?>
                        <span class="text-muted">No registrado</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="info-label">Teléfono</div>
                <div class="info-value">
                    <?php echo $colegiado->telefono ? e($colegiado->telefono) : '<span class="text-muted">No registrado</span>'; ?>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="info-label">Correo Electrónico</div>
                <div class="info-value">
                    <?php echo $colegiado->correo ? e($colegiado->correo) : '<span class="text-muted">No registrado</span>'; ?>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="info-label">Dirección</div>
                <div class="info-value">
                    <?php echo $colegiado->direccion ? e($colegiado->direccion) : '<span class="text-muted">No registrado</span>'; ?>
                </div>
            </div>
        </div>
        
        <?php if ($colegiado->observaciones): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="info-label">Observaciones</div>
                <div class="info-value"><?php echo nl2br(e($colegiado->observaciones)); ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Cambiar Estado -->
<?php if (hasPermission('colegiados', 'editar')): ?>
<div class="card mb-4">
    <div class="card-header bg-search">
        <i class="fas fa-exchange-alt me-2"></i> Cambiar Estado del Colegiado
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo url('colegiados/cambiar-estado/' . $colegiado->idColegiados); ?>" id="formEstado">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Nuevo Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="habilitado" <?php echo $colegiado->estado === 'habilitado' ? 'selected' : ''; ?>>
                            Habilitado
                        </option>
                        <option value="inhabilitado" <?php echo $colegiado->estado === 'inhabilitado' ? 'selected' : ''; ?>>
                            Inhabilitado
                        </option>
                    </select>
                </div>
                <div class="col-md-7">
                    <label class="form-label">Motivo del Cambio</label>
                    <input type="text" name="motivo" class="form-control" required 
                           placeholder="Indique el motivo del cambio de estado">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Cambiar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Historial de Pagos -->
<div class="card mb-4">
    <div class="card-header bg-success">
        <i class="fas fa-money-bill-wave me-2"></i> 
        Historial de Pagos
        <?php if (!empty($historial_pagos)): ?>
            <span>
                | Últimos <?php echo count($historial_pagos); ?> registros
            </span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (!empty($historial_pagos)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Estado</th>
                            <th>Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial_pagos as $pago): ?>
                            <tr>
                                <td data-label="Fecha"><?php echo formatDate($pago['fecha_pago']); ?></td>
                                <td data-label="Concepto">
                                    <?php if (!empty($pago['es_deuda_manual'])): ?>
                                        <div>
                                            <span class="badge bg-secondary mb-1">
                                                <i class="fas fa-edit"></i> Deuda Manual
                                            </span>
                                        </div>
                                        <strong><?php echo e($pago['concepto_manual'] ?? 'Sin concepto'); ?></strong>
                                    <?php else: ?>
                                        <strong><?php echo e($pago['concepto_nombre'] ?? 'Sin concepto'); ?></strong>
                                    <?php endif; ?>
                                    <br>
                                    <small class="text-muted"><?php echo e($pago['descripcion_deuda']); ?></small>
                                </td>
                                <td data-label="Monto"><strong><?php echo formatMoney($pago['monto']); ?></strong></td>
                                <td data-label="Método">
                                    <span class="badge bg-info">
                                        <?php echo e($pago['metodo_pago_nombre'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td data-label="Estado">
                                    <?php if ($pago['estado'] === 'confirmado'): ?>
                                        <span class="badge bg-success">Confirmado</span>
                                    <?php elseif ($pago['estado'] === 'registrado'): ?>
                                        <span class="badge bg-warning">Registrado</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Anulado</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Registrado por"><?php echo e($pago['nombre_usuario'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted text-center mb-0">
                <i class="fas fa-info-circle me-2"></i>
                No hay pagos registrados para este colegiado
            </p>
        <?php endif; ?>
    </div>
</div>

<!-- Deudas Pendientes -->
<div class="card mb-4">
    <div class="card-header bg-danger">
        <i class="fas fa-exclamation-triangle me-2"></i> 
        Deudas Pendientes
        <?php if (!empty($deudas)): ?>
            <span>
                | <?php echo count($deudas); ?> deuda(s)
            </span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (!empty($deudas)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th>Descripción</th>
                            <th>Monto Esperado</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalDeuda = 0;
                        foreach ($deudas as $deuda): 
                            if ($deuda['estado'] !== 'pagado') {
                                $totalDeuda += $deuda['saldo_pendiente'];
                            }
                        ?>
                            <tr>
                                <td data-label="Concepto">
                                    <?php if (!empty($deuda['es_deuda_manual'])): ?>
                                        <span class="badge bg-secondary mb-1">
                                            <i class="fas fa-edit"></i> Manual
                                        </span>
                                        <br>
                                        <strong><?php echo e($deuda['concepto_manual'] ?? 'Sin concepto'); ?></strong>
                                    <?php else: ?>
                                        <strong><?php echo e($deuda['concepto_nombre'] ?? 'Sin concepto'); ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Descripción">
                                    <?php echo e($deuda['descripcion_deuda']); ?>
                                </td>
                                <td data-label="Monto Esperado">
                                    <?php echo formatMoney($deuda['monto_esperado']); ?>
                                </td>
                                <td data-label="Pagado">
                                    <?php echo formatMoney($deuda['monto_pagado']); ?>
                                </td>
                                <td data-label="Saldo">
                                    <strong><?php echo formatMoney($deuda['saldo_pendiente']); ?></strong>
                                </td>
                                <td data-label="Vencimiento">
                                    <?php echo formatDate($deuda['fecha_vencimiento']); ?>
                                </td>
                                <td data-label="Estado">
                                    <?php if ($deuda['estado'] === 'pendiente'): ?>
                                        <span class="badge bg-warning">Pendiente</span>
                                    <?php elseif ($deuda['estado'] === 'parcial'): ?>
                                        <span class="badge bg-info">Parcial</span>
                                    <?php elseif ($deuda['estado'] === 'vencido'): ?>
                                        <span class="badge bg-danger">Vencido</span>
                                    <?php elseif ($deuda['estado'] === 'pagado'): ?>
                                        <span class="badge bg-success">Pagado</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Cancelado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <th colspan="4" class="text-end">TOTAL ADEUDADO:</th>
                            <th colspan="3"><?php echo formatMoney($totalDeuda); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted text-center mb-0">
                <i class="fas fa-check-circle me-2"></i>
                No hay deudas registradas para este colegiado
            </p>
        <?php endif; ?>
    </div>
</div>

<!-- Historial de Cambios de Estado -->
<?php if (!empty($historial_estados)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <i class="fas fa-history me-2"></i> 
            Historial de Cambios de Estado
            <span>
                | Últimos <?php echo count($historial_estados); ?> cambios
            </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Estado Anterior</th>
                            <th>Estado Nuevo</th>
                            <th>Motivo</th>
                            <th>Tipo</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial_estados as $cambio): ?>
                            <tr>
                                <td data-label="Fecha">
                                    <?php echo formatDateTime($cambio['fecha_cambio']); ?>
                                </td>
                                
                                <td data-label="Estado Anterior">
                                    <span class="badge <?php echo $cambio['estado_anterior'] === 'habilitado' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo ucfirst(e($cambio['estado_anterior'])); ?>
                                    </span>
                                </td>
                                
                                <td data-label="Estado Nuevo">
                                    <span class="badge <?php echo $cambio['estado_nuevo'] === 'habilitado' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo ucfirst(e($cambio['estado_nuevo'])); ?>
                                    </span>
                                </td>
                                
                                <td data-label="Motivo">
                                    <small><?php echo e($cambio['motivo']); ?></small>
                                </td>
                                
                                <td data-label="Tipo">
                                    <span class="badge <?php echo $cambio['tipo_cambio'] === 'manual' ? 'bg-primary' : 'bg-info'; ?>">
                                        <?php echo ucfirst(e($cambio['tipo_cambio'])); ?>
                                    </span>
                                </td>
                                
                                <td data-label="Usuario">
                                    <i class="fas fa-user-circle me-1 text-muted"></i>
                                    <?php echo e($cambio['nombre_usuario'] ?: 'Sistema'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>