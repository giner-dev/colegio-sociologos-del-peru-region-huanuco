// Esperar a que el DOM esté cargado
document.addEventListener('DOMContentLoaded', function() {
    
    // Toggle mostrar/ocultar contraseña
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }
    
    // Validación del formulario
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            // Validar campos vacíos
            if (username === '' || password === '') {
                e.preventDefault();
                showAlert('Por favor, complete todos los campos', 'error');
                return false;
            }
            
            // Validar longitud mínima
            if (username.length < 3) {
                e.preventDefault();
                showAlert('El usuario debe tener al menos 3 caracteres', 'error');
                return false;
            }
            
            if (password.length < 4) {
                e.preventDefault();
                showAlert('La contraseña debe tener al menos 4 caracteres', 'error');
                return false;
            }
            
            // Mostrar indicador de carga
            showLoadingState();
        });
    }
    
    // Auto-ocultar alertas después de 5 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            fadeOut(alert);
        }, 5000);
    });
    
    // Animación de entrada para los inputs
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(function(input) {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
});

// Función para mostrar alertas personalizadas
function showAlert(message, type) {
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'}`;
    alertDiv.setAttribute('role', 'alert');
    
    const icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
    alertDiv.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
    `;
    
    const loginBody = document.querySelector('.login-body');
    const form = document.getElementById('loginForm');
    loginBody.insertBefore(alertDiv, form);
    
    // Auto-ocultar después de 5 segundos
    setTimeout(function() {
        fadeOut(alertDiv);
    }, 5000);
}

// Función para desvanecer elementos
function fadeOut(element) {
    let opacity = 1;
    const timer = setInterval(function() {
        if (opacity <= 0.1) {
            clearInterval(timer);
            element.remove();
        }
        element.style.opacity = opacity;
        opacity -= 0.1;
    }, 50);
}

// Función para mostrar estado de carga en el botón
function showLoadingState() {
    const submitBtn = document.querySelector('.btn-login');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando sesión...';
    }
}

// Prevenir doble envío del formulario
let formSubmitted = false;
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        if (formSubmitted) {
            e.preventDefault();
            return false;
        }
        formSubmitted = true;
    });
}