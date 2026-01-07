<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-credit-card me-2" style="color: #B91D22;"></i>
            Gestión de Métodos de Pago
        </h2>
        <a href="<?php echo url('pagos/metodos/crear'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nuevo Método
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-results">
        <i class="fas fa-list me-2"></i> 
        Métodos Registrados: <strong><?php echo count($metodos); ?></strong>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th width="30%">Método de Pago</th>
                        <th width="50%">Descripción</th>
                        <th width="10%">Estado</th>
                        <th width="10%" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($metodos)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle me-2"></i>
                                No hay métodos de pago registrados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($metodos as $metodo): ?>
                            <tr>
                                <td><strong><?php echo e($metodo['nombre']); ?></strong></td>
                                <td><?php echo e($metodo['descripcion'] ?: '-'); ?></td>
                                <td>
                                    <?php if ($metodo['activo'] === 'activo'): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo url('pagos/metodos/editar/' . $metodo['idMetodo']); ?>" 
                                       class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($metodo['activo'] === 'activo'): ?>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="eliminarMetodo(<?php echo $metodo['idMetodo']; ?>)"
                                                title="Desactivar">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?php echo url('assets/js/pagos.js'); ?>"></script>
<script>
function eliminarMetodo(id) {
    Swal.fire({
        title: '¿Desactivar este método?',
        text: 'Los pagos existentes no se verán afectados',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#B91D22',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo url('pagos/metodos/eliminar/'); ?>' + id;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>