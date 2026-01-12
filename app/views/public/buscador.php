<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscador de Colegiados - CSP Huánuco</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="icon" type="image/png" href="<?php echo url('uploads/fondos/favicon.png'); ?>">
    
    <link rel="stylesheet" href="<?php echo url('assets/css/buscador.css'); ?>">
</head>
<body>
    <div class="background-animated"></div>
    
    <div class="buscador-container">
        <!-- Header -->
        <header class="buscador-header">
            <div class="logo-container">
                <img src="<?php echo url('uploads/fondos/favicon_2.webp'); ?>" alt="">
            </div>
            <h1>Colegio de Sociólogos del Perú</h1>
            <h2>Región Huánuco - SIAD</h2>
            <p class="subtitle">Sistema de Verificación de Colegiados</p>
        </header>
        
        <!-- Buscador Card -->
        <div class="search-card">
            <div class="search-card-header">
                <i class="fas fa-search"></i>
                <h3>Buscar Colegiado</h3>
            </div>
            
            <div class="search-card-body">
                <form id="formBuscar">
                    <div class="form-group">
                        <label for="dni">
                            <i class="fas fa-id-card"></i>
                            Ingrese el DNI
                        </label>
                        <input 
                            type="text" 
                            id="dni" 
                            name="dni" 
                            class="form-control" 
                            placeholder="Ej: 12345678"
                            maxlength="8"
                            autocomplete="off"
                            required
                        >
                        <small class="form-text">Ingrese el número de DNI de 8 dígitos</small>
                    </div>
                    
                    <button type="submit" class="btn-search" id="btnBuscar">
                        <i class="fas fa-search"></i>
                        <span>Buscar</span>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Resultado -->
        <div class="result-card" id="resultCard" style="display: none;">
            <div class="result-header">
                <i class="fas fa-user-check"></i>
                <h3>Resultado de la Búsqueda</h3>
            </div>
            
            <div class="result-body" id="resultContent">
                <!-- El contenido se inyectará dinámicamente -->
            </div>
            
            <button class="btn-new-search" onclick="nuevaBusqueda()">
                <i class="fas fa-redo"></i>
                Nueva Búsqueda
            </button>
        </div>
        
        <!-- Footer -->
        <footer class="buscador-footer">
            <p>&copy; <?php echo date('Y'); ?> Todos los derechos reservados CSP-RHCO</p>
            <p class="des">
                Desarrollado por 
                <a href="https://corporacionbalta.com.pe/" target="_blank" rel="noopener">CORPORACIÓN BALTA S.A.C.</a> & 
                <a href="http://giner.dev/" target="_blank" rel="noopener">Giner Dev</a>
            </p>
        </footer>
    </div>
    
    <script>
        window.PHP_BASE_URL = '<?php echo url(); ?>';
    </script>
    <script src="<?php echo url('assets/js/buscador.js'); ?>"></script>
</body>
</html>