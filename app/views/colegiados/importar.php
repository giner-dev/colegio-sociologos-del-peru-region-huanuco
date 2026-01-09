<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>
            <i class="fas fa-file-excel me-2"></i>
            Importar Colegiados desde Excel
        </h2>
        <a href="<?php echo url('colegiados'); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<!-- Instrucciones -->
<div class="alert alert-info">
    <h5 class="alert-heading">
        <i class="fas fa-info-circle"></i> Instrucciones
    </h5>
    <ul class="mb-0">
        <li>El archivo debe estar en formato Excel (.xlsx o .xls)</li>
        <li>La primera fila debe contener los encabezados de las columnas</li>
        <li><strong>Las columnas obligatorias son:</strong> DNI, Nombres, Apellido Paterno, Apellido Materno, Fecha de Colegiatura</li>
        <li><strong>Número de Colegiatura:</strong> Si se deja vacío, se generará automáticamente</li>
        <li>Puede incluir columnas opcionales: Teléfono, Correo, Dirección, Fecha de Nacimiento, Observaciones</li>
        <li>El sistema validará cada registro antes de importarlo</li>
    </ul>
</div>

<!-- Plantilla de ejemplo -->
<div class="card mb-4">
    <div class="card-header bg-search">
        <i class="fas fa-download me-2"></i> Plantilla de Ejemplo
    </div>
    <div class="card-body">
        <p>Descargue la plantilla de Excel para asegurar que su archivo tenga el formato correcto:</p>
        <a href="<?php echo url('colegiados/descargar-plantilla'); ?>" class="btn btn-success">
            <i class="fas fa-file-excel me-2"></i> Descargar Plantilla Excel
        </a>
    </div>
</div>

<!-- Formulario de carga -->
<div class="card">
    <div class="card-header bg-search">
        <i class="fas fa-upload me-2"></i> Cargar Archivo
    </div>
    <div class="card-body">
        <form method="POST" action="<?php echo url('colegiados/procesar-excel'); ?>" enctype="multipart/form-data" id="formImportar">
            <div class="upload-zone" id="uploadZone">
                <i class="fas fa-cloud-upload-alt fa-4x mb-3"></i>
                <h5>Arrastra tu archivo Excel aquí</h5>
                <p class="text-muted">o haz clic para seleccionar</p>
                <input type="file" name="archivo_excel" id="archivoExcel" accept=".xlsx,.xls" class="d-none" required>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('archivoExcel').click();">
                    <i class="fas fa-folder-open me-2"></i> Seleccionar Archivo
                </button>
            </div>
            
            <div id="archivoSeleccionado" class="mt-3 d-none">
                <div class="alert alert-success">
                    <i class="fas fa-file-excel me-2"></i>
                    <strong>Archivo seleccionado:</strong> <span id="nombreArchivo"></span>
                </div>
            </div>
            
            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary btn-lg" id="btnImportar" disabled>
                    <i class="fas fa-upload me-2"></i> Importar Colegiados
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Información adicional -->
<div class="card mt-4">
    <div class="card-header bg-light">
        <i class="fas fa-question-circle me-2"></i> Preguntas Frecuentes
    </div>
    <div class="card-body">
        <h6>¿Qué sucede si un colegiado ya existe?</h6>
        <p class="text-muted">El sistema detectará registros duplicados por DNI o Número de Colegiatura y los omitirá, mostrando un reporte al final.</p>
        
        <h6>¿Puedo importar archivos grandes?</h6>
        <p class="text-muted">Sí, el sistema puede procesar archivos con miles de registros, pero el proceso puede tomar algunos minutos.</p>
        
        <h6>¿Se validarán los datos antes de importar?</h6>
        <p class="text-muted">Sí, cada registro será validado. Los registros con errores serán reportados pero no se importarán.</p>
    </div>
</div>