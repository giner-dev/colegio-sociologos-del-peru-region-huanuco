<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo) ? $titulo . ' - ' : ''; ?><?php echo env('APP_NAME'); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        :root {
            --primary-color: #B91D22;
            --primary-dark: #8a1519;
            --primary-light: #d42429;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .navbar-custom {
            background-color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-custom .navbar-brand {
            color: white;
            font-weight: 600;
            font-size: 1.3rem;
        }
        
        .navbar-custom .nav-link {
            color: rgba(255,255,255,0.9);
            transition: color 0.3s;
        }
        
        .navbar-custom .nav-link:hover {
            color: white;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .user-info {
            color: white;
            margin-right: 15px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            background: white;
            color: var(--primary-color);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo url('dashboard'); ?>">
                <i class="fas fa-university me-2"></i>
                <?php echo env('APP_NAME'); ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <span class="user-info">
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
                            <strong><?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?></strong>
                            <br>
                            <small style="opacity: 0.8;"><?php echo $_SESSION['usuario_rol'] ?? 'Sin rol'; ?></small>
                        </span>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fa-lg"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user me-2"></i> Mi Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cog me-2"></i> Configuración
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo url('logout'); ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>