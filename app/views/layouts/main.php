<?php
include __DIR__ . '/header.php';
?>
<div class="main-wrapper">
    <!-- Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <div class="content-wrapper">
        <?php
        // Mostrar mensajes flash
        $success = flash('success');
        if ($success):
        ?>
            <div class="alert alert-success alert-dismissible" role="alert">
                <i class="fas fa-check-circle"></i>
                <span><?php echo e($success); ?></span>
                <button type="button" class="btn-close"></button>
            </div>
        <?php endif; ?>
        
        <?php
        $error = flash('error');
        if ($error):
        ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo e($error); ?></span>
                <button type="button" class="btn-close"></button>
            </div>
        <?php endif; ?>
        
        <?php
        $warning = flash('warning');
        if ($warning):
        ?>
            <div class="alert alert-warning alert-dismissible" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?php echo e($warning); ?></span>
                <button type="button" class="btn-close"></button>
            </div>
        <?php endif; ?>
        
        <?php
        $info = flash('info');
        if ($info):
        ?>
            <div class="alert alert-info alert-dismissible" role="alert">
                <i class="fas fa-info-circle"></i>
                <span><?php echo e($info); ?></span>
                <button type="button" class="btn-close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Aquí se inyecta el contenido de cada vista -->
        <?php echo $content; ?>
    </div>
</div>

<!-- ✅ ORDEN CORRECTO DE SCRIPTS -->
<!-- 1. main.js primero (configuración global y utilidades) -->
<script src="<?php echo url('assets/js/main.js'); ?>"></script>

<!-- 2. Módulos específicos después (dependen de main.js) -->
<script src="<?php echo url('assets/js/dashboard.js'); ?>"></script>
<script src="<?php echo url('assets/js/colegiados.js'); ?>"></script>
<script src="<?php echo url('assets/js/deudas.js'); ?>"></script>
<script src="<?php echo url('assets/js/pagos.js'); ?>"></script>

<!-- ✅ Inyectar BASE_URL desde PHP a JavaScript -->
<script>
    // Configurar BASE_URL desde PHP
    window.PHP_BASE_URL = '<?php echo url(); ?>';
    console.log('📍 BASE_URL inyectado desde PHP:', window.PHP_BASE_URL);
</script>

</body>
</html>