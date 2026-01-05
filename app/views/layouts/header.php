<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo) ? $titulo . ' - ' : ''; ?><?php echo env('APP_NAME'); ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo url('assets/css/main.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/dashboard.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/colegiados.css'); ?>">
    
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar-custom">
        <div class="navbar-container">
            <div class="navbar-left">
                <!-- Botón Hamburguesa -->
                <button class="menu-toggle-btn" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <a class="navbar-brand" href="<?php echo url('dashboard'); ?>">
                    <i class="fas fa-university"></i>
                    <?php echo env('APP_NAME_MOVIL'); ?>
                </a>
            </div>
            
            <div class="navbar-menu">
                <div class="user-info">
                    <strong><?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?></strong>
                    <small><?php echo $_SESSION['usuario_rol'] ?? 'Sin rol'; ?></small>
                </div>
                
                <span class="user-avatar">
                    <?php 
                        $iniciales = '';
                        if (isset($_SESSION['usuario_nombre'])) {
                            $nombres = explode(' ', $_SESSION['usuario_nombre']);
                            $iniciales = strtoupper(substr($nombres[0], 0, 1));
                            if (isset($nombres[1])) {
                                $iniciales .= strtoupper(substr($nombres[1], 0, 1));
                            }
                        }
                        echo $iniciales;
                    ?>
                </span>
                
                <div class="dropdown">
                    <button class="dropdown-toggle">
                        <i class="fas fa-user-circle"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-user"></i> Mi Perfil
                        </a>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-cog"></i> Configuración
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo url('logout'); ?>" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>