document.addEventListener('DOMContentLoaded', function() {
    console.log('Módulo de roles cargado');
    
    initFormValidation();
    initPermisosSync();
});

function initFormValidation() {
    const form = document.getElementById('formRol');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateRolForm()) {
                e.preventDefault();
                showToast('Por favor complete todos los campos requeridos', 'warning');
            }
        });
    }
}

function validateRolForm() {
    const nombreRol = document.querySelector('input[name="nombre_rol"]');
    
    if (!nombreRol || !nombreRol.value.trim()) {
        return false;
    }
    
    return true;
}

function initPermisosSync() {
    const moduloCheckboxes = document.querySelectorAll('.modulo-checkbox');
    
    moduloCheckboxes.forEach(checkbox => {
        const modulo = checkbox.dataset.modulo;
        const accionCheckboxes = document.querySelectorAll(`.accion-${modulo}`);
        
        accionCheckboxes.forEach(accionCheckbox => {
            accionCheckbox.addEventListener('change', function() {
                updateModuloCheckbox(modulo);
            });
        });
    });
}

function updateModuloCheckbox(modulo) {
    const moduloCheckbox = document.querySelector(`[data-modulo="${modulo}"]`);
    const accionCheckboxes = document.querySelectorAll(`.accion-${modulo}`);
    
    const algunaAccionMarcada = Array.from(accionCheckboxes).some(cb => cb.checked);
    
    if (moduloCheckbox) {
        moduloCheckbox.checked = algunaAccionMarcada;
    }
}