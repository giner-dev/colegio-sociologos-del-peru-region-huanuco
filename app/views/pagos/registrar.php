<div class="page-header">
    <h2>
        <i class="fas fa-plus-circle me-2"></i>
        Registrar Nuevo Pago
    </h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('pagos/guardar'); ?>" id="formRegistrarPago">
            <div class="row">
                <!-- Selección de Colegiado -->
                <div class="col-md-12 mb-3">
                    <label class="form-label required">Colegiado</label>
                    <select name="colegiados_id" id="selectColegiado" class="form-select" required>
                        <option value="">Seleccione un colegiado...</option>
                        <?php foreach ($colegiados as $colegiado): ?>
                            <option value="<?php echo $colegiado->idColegiados; ?>"
                                    data-numero="<?php echo e($colegiado->numero_colegiatura); ?>"
                                    data-dni="<?php echo e($colegiado->dni); ?>">
                                <?php echo e($colegiado->numero_colegiatura); ?> - 
                                <?php echo e($colegiado->getNombreCompleto()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Busque por número de colegiatura o nombre</small>
                </div>
            </div>
            
            <div class="row">
                <!-- Concepto de Pago -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Concepto (Predefinido)</label>
                    <select name="concepto_id" id="selectConcepto" class="form-select">
                        <option value="">Seleccione un concepto...</option>
                        <?php foreach ($conceptos as $concepto): ?>
                            <option value="<?php echo $concepto['idConcepto']; ?>"
                                    data-monto="<?php echo $concepto['monto_sugerido']; ?>">
                                <?php echo e($concepto['nombre_completo']); ?> - 
                                S/ <?php echo number_format($concepto['monto_sugerido'], 2); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Concepto Personalizado -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">O ingrese concepto personalizado</label>
                    <input type="text" name="concepto_texto" id="conceptoTexto" class="form-control"
                           placeholder="Ej: Pago extraordinario">
                    <small class="text-muted">Deje vacío si usa concepto predefinido</small>
                </div>
            </div>
            
            <div class="row">
                <!-- Monto -->
                <div class="col-md-3 mb-3">
                    <label class="form-label required">Monto</label>
                    <div class="input-group">
                        <span class="input-group-text">S/</span>
                        <input type="number" name="monto" id="inputMonto" class="form-control" 
                               step="0.01" min="0.01" required placeholder="0.00">
                    </div>
                </div>
                
                <!-- Fecha de Pago -->
                <div class="col-md-3 mb-3">
                    <label class="form-label required">Fecha de Pago</label>
                    <input type="date" name="fecha_pago" class="form-control" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <!-- Método de Pago -->
                <div class="col-md-3 mb-3">
                    <label class="form-label required">Método de Pago</label>
                    <select name="metodo_pago_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($metodos as $metodo): ?>
                            <option value="<?php echo $metodo['idMetodo']; ?>">
                                <?php echo e($metodo['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Número de Comprobante -->
                <div class="col-md-3 mb-3">
                    <label class="form-label">N° Comprobante</label>
                    <input type="text" name="numero_comprobante" class="form-control"
                           placeholder="Ej: 001-00123">
                </div>
            </div>
            
            <div class="row">
                <!-- Observaciones -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3"
                              placeholder="Ingrese observaciones adicionales si es necesario"></textarea>
                </div>
            </div>
            
            <!-- Información del total -->
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Total a registrar:</strong> 
                        <span class="total-display">S/ 0.00</span>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <a href="<?php echo url('pagos'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Registrar Pago
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?php echo url('assets/js/pagos.js'); ?>"></script>
<script>
$(document).ready(function() {
    // Inicializar Select2 para búsqueda de colegiado
    $('#selectColegiado').select2({
        theme: 'bootstrap-5',
        placeholder: 'Busque por número o nombre...',
        allowClear: true
    });
    
    // Cuando se selecciona un concepto predefinido, llenar el monto
    $('#selectConcepto').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const monto = selectedOption.data('monto');
        
        if (monto) {
            $('#inputMonto').val(parseFloat(monto).toFixed(2));
            $('#conceptoTexto').val('').prop('disabled', true);
            actualizarTotal();
        } else {
            $('#conceptoTexto').prop('disabled', false);
        }
    });
    
    // Si se escribe en concepto personalizado, limpiar concepto predefinido
    $('#conceptoTexto').on('input', function() {
        if ($(this).val().trim() !== '') {
            $('#selectConcepto').val('').trigger('change');
        }
    });
    
    // Actualizar total cuando cambia el monto
    $('#inputMonto').on('input', actualizarTotal);
    
    function actualizarTotal() {
        const monto = parseFloat($('#inputMonto').val()) || 0;
        $('.total-display').text('S/ ' + monto.toFixed(2));
    }
    
    // Validación del formulario
    $('#formRegistrarPago').on('submit', function(e) {
        const colegiadoId = $('#selectColegiado').val();
        const concepto = $('#selectConcepto').val();
        const conceptoTexto = $('#conceptoTexto').val().trim();
        const monto = parseFloat($('#inputMonto').val());
        
        if (!colegiadoId) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe seleccionar un colegiado'
            });
            return false;
        }
        
        if (!concepto && !conceptoTexto) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe especificar un concepto de pago'
            });
            return false;
        }
        
        if (!monto || monto <= 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El monto debe ser mayor a 0'
            });
            return false;
        }
    });
});
</script>