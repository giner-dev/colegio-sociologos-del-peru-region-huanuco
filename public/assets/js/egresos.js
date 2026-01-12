window.EgresosModule = (function() {
    'use strict';
    
    let initialized = false;
    
    function init() {
        if (initialized) return;
        
        const currentPath = window.location.pathname;
        
        if (currentPath.includes('/egresos')) {
            console.log('Inicializando módulo Egresos...');
            
            if (currentPath.includes('/egresos/registrar') || 
                currentPath.includes('/egresos/editar')) {
                initFormulario();
            }
            
            initialized = true;
            console.log('Módulo Egresos inicializado');
        }
    }
    
    function initFormulario() {
        initMoneyInputs();
        initDateValidation();
        initFileInput();
        
        const form = document.getElementById('formEgreso');
        if (form) {
            form.addEventListener('submit', validarFormulario);
        }
    }
    
    function initMoneyInputs() {
        const montoInput = document.querySelector('input[name="monto"]');
        
        if (montoInput) {
            montoInput.addEventListener('blur', function() {
                if (this.value) {
                    const value = parseFloat(this.value);
                    if (!isNaN(value) && value >= 0) {
                        this.value = value.toFixed(2);
                    }
                }
            });
            
            montoInput.addEventListener('input', function() {
                if (parseFloat(this.value) < 0) {
                    this.value = 0;
                }
            });
        }
    }
    
    function initDateValidation() {
        const fechaEgreso = document.querySelector('input[name="fecha_egreso"]');
        
        if (fechaEgreso) {
            const hoy = new Date().toISOString().split('T')[0];
            
            fechaEgreso.addEventListener('change', function() {
                if (this.value > hoy) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Fecha futura',
                            text: 'La fecha del egreso es posterior a hoy. ¿Es correcto?',
                            confirmButtonColor: '#B91D22'
                        });
                    }
                }
            });
        }
    }
    
    function initFileInput() {
        const fileInput = document.querySelector('input[name="comprobante"]');
        
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                
                if (file) {
                    const maxSize = 5 * 1024 * 1024;
                    const validTypes = ['image/jpeg', 'image/png', 'application/pdf'];
                    
                    if (!validTypes.includes(file.type)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Archivo inválido',
                                text: 'Solo se permiten archivos JPG, PNG o PDF',
                                confirmButtonColor: '#B91D22'
                            });
                        } else {
                            alert('Solo se permiten archivos JPG, PNG o PDF');
                        }
                        this.value = '';
                        return;
                    }
                    
                    if (file.size > maxSize) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Archivo muy grande',
                                text: 'El archivo no debe exceder los 5MB',
                                confirmButtonColor: '#B91D22'
                            });
                        } else {
                            alert('El archivo no debe exceder los 5MB');
                        }
                        this.value = '';
                        return;
                    }
                }
            });
        }
    }
    
    function validarFormulario(e) {
        const descripcion = document.querySelector('textarea[name="descripcion"]')?.value;
        const monto = parseFloat(document.querySelector('input[name="monto"]')?.value);
        const fecha = document.querySelector('input[name="fecha_egreso"]')?.value;
        
        const errores = [];
        
        if (!descripcion || descripcion.trim() === '') {
            errores.push('La descripción es obligatoria');
        }
        
        if (!monto || monto <= 0) {
            errores.push('El monto debe ser mayor a 0');
        }
        
        if (!fecha) {
            errores.push('La fecha del egreso es obligatoria');
        }
        
        if (errores.length > 0) {
            e.preventDefault();
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Errores de validación',
                    html: '<ul style="text-align: left; margin: 0;">' + 
                          errores.map(e => '<li>' + e + '</li>').join('') + 
                          '</ul>',
                    confirmButtonColor: '#B91D22'
                });
            } else {
                alert('Errores:\n' + errores.join('\n'));
            }
            return false;
        }
        
        const btnSubmit = e.target.querySelector('button[type="submit"]');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
        }
        
        return true;
    }

    function eliminarTipoGasto(id) {
        if (typeof Swal === 'undefined') {
            if (!confirm('¿Eliminar este tipo de gasto?')) return;
            
            fetch(getAppUrl(`egresos/tipos-gasto/eliminar/${id}`), {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Tipo de gasto eliminado correctamente');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
            return;
        }
        
        Swal.fire({
            title: '¿Eliminar este tipo de gasto?',
            text: 'Esta acción lo desactivará',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(getAppUrl(`egresos/tipos-gasto/eliminar/${id}`), {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: data.message,
                            confirmButtonColor: '#28a745'
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                            confirmButtonColor: '#B91D22'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor',
                        confirmButtonColor: '#B91D22'
                    });
                });
            }
        });
    }

    return {
        init: init,
        eliminarTipoGasto: eliminarTipoGasto
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('/egresos')) {
        EgresosModule.init();
    }
});

window.eliminarTipoGasto = function(id) {
    EgresosModule.eliminarTipoGasto(id);
};
