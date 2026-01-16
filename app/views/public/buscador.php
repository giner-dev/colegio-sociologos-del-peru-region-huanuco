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
                        <label for="tipoBusqueda">
                            <i class="fas fa-filter"></i>
                            Buscar por
                        </label>
                        <select id="tipoBusqueda" class="form-control" style="margin-bottom: 15px;">
                            <option value="dni">DNI</option>
                            <option value="numero">Número de Colegiatura</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="groupDni">
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
                        >
                        <small class="form-text">Ingrese el número de DNI de 8 dígitos</small>
                    </div>
                    
                    <div class="form-group" id="groupNumero" style="display: none;">
                        <label for="numero_colegiatura">
                            <i class="fas fa-hashtag"></i>
                            Ingrese el Número de Colegiatura
                        </label>
                        <input 
                            type="text" 
                            id="numero_colegiatura" 
                            name="numero_colegiatura" 
                            class="form-control" 
                            placeholder="Ej: 123 o 00123"
                            maxlength="10"
                            autocomplete="off"
                        >
                        <small class="form-text">Puede ingresar con o sin ceros a la izquierda</small>
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
    </div>
    
    <script>
        window.PHP_BASE_URL = '<?php echo url(); ?>';
    </script>
    <script src="<?php echo url('assets/js/buscador.js'); ?>"></script>
</body>
</html>