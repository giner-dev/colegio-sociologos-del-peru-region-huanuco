document.addEventListener('DOMContentLoaded', function() {
    initUsuariosModule();
});

function initUsuariosModule() {
    initFormValidation();
    initPasswordValidation();
}

function initFormValidation() {
    const formUsuario = document.getElementById('formUsuario');
    
    if (formUsuario) {
        formUsuario.addEventListener('submit', function(e) {
            const password = this.querySelector('input[name="contrasenia"]');
            const confirmar = this.querySelector('input[name="confirmar_contrasenia"]');
            
            if (password && confirmar && password.value !== confirmar.value) {
                e.preventDefault();
                showToast('Las contraseñas no coinciden', 'error');
                confirmar.focus();
                return false;
            }
        });
    }
}

function initPasswordValidation() {
    const formPassword = document.getElementById('formPassword');
    
    if (formPassword) {
        formPassword.addEventListener('submit', function(e) {
            const nueva = this.querySelector('input[name="password_nueva"]');
            const confirmar = this.querySelector('input[name="password_confirmar"]');
            
            if (nueva.value !== confirmar.value) {
                e.preventDefault();
                showToast('Las contraseñas nuevas no coinciden', 'error');
                confirmar.focus();
                return false;
            }
            
            if (nueva.value.length < 6) {
                e.preventDefault();
                showToast('La contraseña debe tener al menos 6 caracteres', 'error');
                nueva.focus();
                return false;
            }
        });
    }
}

function cambiarEstadoUsuario(id, nuevoEstado) {
    const mensaje = nuevoEstado === 'activo' 
        ? '¿Está seguro que desea ACTIVAR este usuario?' 
        : '¿Está seguro que desea DESACTIVAR este usuario?';
    
    confirmAction(mensaje, function() {
        const form = document.getElementById('formCambiarEstado');
        document.getElementById('nuevoEstado').value = nuevoEstado;
        form.action = getAppUrl('usuarios/cambiar-estado/' + id);
        form.submit();
    });
}