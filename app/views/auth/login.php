<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?php echo env('APP_NAME'); ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo url('assets/css/login.css'); ?>">
    <link rel="icon" type="image/png" href="<?php echo url('uploads/fondos/favicon.png'); ?>">

</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-circle">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1><?php echo env('APP_NAME'); ?></h1>
                <p>Colegio de Sociólogos del Perú - Región Huánuco</p>
            </div>
            
            <div class="login-body">
                <?php
                // Mostrar mensajes de error
                $error = flash('error');
                if ($error):
                ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo e($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php
                // Mostrar mensajes de éxito
                $success = flash('success');
                if ($success):
                ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo e($success); ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo url('login'); ?>" id="loginForm">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> Usuario
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="fas fa-user"></i>
                            </span>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="username" 
                                name="username" 
                                placeholder="Ingrese su usuario"
                                required
                                autofocus
                            >
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Contraseña
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                placeholder="Ingrese su contraseña"
                                required
                            >
                            <button 
                                class="toggle-password" 
                                type="button" 
                                id="togglePassword"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        Iniciar Sesión
                    </button>
                </form>
            </div>
            
            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> Todos los derechos reservados CSP-RHCO</p>
                <p class="des">Desarrollado por 
                    <a href="https://corporacionbalta.com.pe/" target="_blank" rel="noopener">CORPORACIÓN BALTA S.A.C.</a> & 
                    <a href="http://giner.dev/" target="_blank" rel="noopener">Giner Dev</a>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo url('assets/js/login.js'); ?>"></script>
</body>
</html>