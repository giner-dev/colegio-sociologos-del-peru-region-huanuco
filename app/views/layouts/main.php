<?php
// Incluir header
include __DIR__ . '/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 col-lg-2 px-0">
            <?php include __DIR__ . '/sidebar.php'; ?>
        </div>
        
        <!-- Contenido principal -->
        <div class="col-md-10 col-lg-10">
            <div class="content-wrapper p-4">
                <?php
                // Mostrar mensajes flash
                $success = flash('success');
                if ($success):
                ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo e($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php
                $error = flash('error');
                if ($error):
                ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo e($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php
                $warning = flash('warning');
                if ($warning):
                ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo e($warning); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php
                $info = flash('info');
                if ($info):
                ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo e($info); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Aquí se inyecta el contenido de cada vista -->
                <?php echo $content; ?>

<?php
// Incluir footer
include __DIR__ . '/footer.php';
?>