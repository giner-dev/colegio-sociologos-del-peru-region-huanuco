<div class="page-header">
    <h2>
        <i class="fas fa-check-circle me-2"></i>
        Resultado de Importación
    </h2>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="stat-box stat-box-success">
            <div class="number"><?php echo $resultado['importados']; ?></div>
            <div class="label">Registros Importados</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-box stat-box-warning">
            <div class="number"><?php echo $resultado['omitidos']; ?></div>
            <div class="label">Registros Omitidos</div>
        </div>
    </div>
</div>

<!-- Errores -->
<?php if (!empty($resultado['errores'])): ?>
<div class="card mb-4">
    <div class="card-header bg-danger">
        <i class="fas fa-exclamation-circle me-2"></i>
        Errores Encontrados (<?php echo count($resultado['errores']); ?>)
    </div>
    <div class="card-body">
        <div class="alert alert-danger">
            <strong>Los siguientes registros no pudieron ser importados debido a errores:</strong>
        </div>
        <ul class="list-group">
            <?php foreach ($resultado['errores'] as $error): ?>
                <li class="list-group-item list-group-item-danger">
                    <i class="fas fa-times-circle me-2"></i>
                    <?php echo e($error); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<!-- Advertencias -->
<?php if (!empty($resultado['advertencias'])): ?>
<div class="card mb-4">
    <div class="card-header bg-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Advertencias (<?php echo count($resultado['advertencias']); ?>)
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <strong>Los siguientes registros fueron omitidos por duplicados:</strong>
        </div>
        <ul class="list-group">
            <?php foreach ($resultado['advertencias'] as $advertencia): ?>
                <li class="list-group-item list-group-item-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <?php echo e($advertencia); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<!-- Mensaje de éxito si todo salió bien -->
<?php if (empty($resultado['errores']) && empty($resultado['advertencias'])): ?>
<div class="card mb-4">
    <div class="card-body text-center">
        <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
        <h3 class="text-success">Importación Exitosa</h3>
        <p class="lead">Todos los registros fueron importados correctamente</p>
    </div>
</div>
<?php endif; ?>

<!-- Acciones -->
<div class="card">
    <div class="card-body text-center">
        <a href="<?php echo url('colegiados'); ?>" class="btn btn-primary btn-lg me-2">
            <i class="fas fa-users me-2"></i> Ver Lista de Colegiados
        </a>
        <a href="<?php echo url('colegiados/importar'); ?>" class="btn btn-secondary btn-lg">
            <i class="fas fa-file-excel me-2"></i> Importar Otro Archivo
        </a>
    </div>
</div>

<!-- Recomendaciones -->
<?php if (!empty($resultado['errores']) || !empty($resultado['advertencias'])): ?>
<div class="alert alert-info mt-4">
    <h5 class="alert-heading">
        <i class="fas fa-lightbulb me-2"></i> Recomendaciones
    </h5>
    <ul class="mb-0">
        <li>Revise los errores y advertencias listados arriba</li>
        <li>Corrija los datos en su archivo Excel original</li>
        <li>Asegúrese de que no haya DNIs o números de colegiatura duplicados</li>
        <li>Vuelva a importar solo los registros que fallaron</li>
    </ul>
</div>
<?php endif; ?>