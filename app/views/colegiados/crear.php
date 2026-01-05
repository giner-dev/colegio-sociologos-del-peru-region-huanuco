<div class="page-header">
    <h2>
        <i class="fas fa-user-plus me-2" style="color: #B91D22;"></i>
        Registrar Nuevo Colegiado
    </h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo url('colegiados/guardar'); ?>" id="formColegiado">
            <div class="row">
                <!-- Número de Colegiatura -->
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Número de Colegiatura</label>
                    <input type="text" name="numero_colegiatura" class="form-control" required>
                </div>
                
                <!-- DNI -->
                <div class="col-md-4 mb-3">
                    <label class="form-label required">DNI</label>
                    <input type="text" name="dni" class="form-control" maxlength="8" required>
                </div>
                
                <!-- Fecha de Colegiatura -->
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Fecha de Colegiatura</label>
                    <input type="date" name="fecha_colegiatura" class="form-control" required>
                </div>
            </div>
            
            <div class="row">
                <!-- Apellido Paterno -->
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Apellido Paterno</label>
                    <input type="text" name="apellido_paterno" class="form-control" required>
                </div>
                
                <!-- Apellido Materno -->
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Apellido Materno</label>
                    <input type="text" name="apellido_materno" class="form-control" required>
                </div>
                
                <!-- Nombres -->
                <div class="col-md-4 mb-3">
                    <label class="form-label required">Nombres</label>
                    <input type="text" name="nombres" class="form-control" required>
                </div>
            </div>
            
            <div class="row">
                <!-- Fecha de Nacimiento -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="form-control">
                </div>
                
                <!-- Teléfono -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control">
                </div>
                
                <!-- Correo -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control">
                </div>
            </div>
            
            <div class="row">
                <!-- Dirección -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">Dirección</label>
                    <input type="text" name="direccion" class="form-control">
                </div>
            </div>
            
            <div class="row">
                <!-- Observaciones -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3"></textarea>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <a href="<?php echo url('colegiados'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Colegiado
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>