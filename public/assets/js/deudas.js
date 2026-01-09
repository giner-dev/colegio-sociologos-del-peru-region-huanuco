/**
 * MÓDULO DE DEUDAS
 */

// ===================================
// NAMESPACE DEL MÓDULO
// ===================================
window.DeudasModule = (function() {
    'use strict';
    
    // Variables privadas del módulo
    let initialized = false;
    
    // ===================================
    // INICIALIZACIÓN
    // ===================================
    function init() {
        if (initialized) {
            console.warn('⚠️ Módulo Deudas ya inicializado');
            return;
        }
        
        const currentPath = window.location.pathname;
        
        if (currentPath.includes('/deudas')) {
            console.log('📋 Inicializando módulo Deudas...');
            
            if (currentPath.includes('/deudas/registrar')) {
                initRegistrarDeuda();
            } else if (currentPath.includes('/deudas/morosos') || 
                       currentPath.includes('/deudas/colegiado')) {
                // No requiere inicialización especial
                console.log('✅ Vista de deudas cargada');
            } else {
                initIndexDeudas();
            }
            
            initialized = true;
            console.log('✅ Módulo Deudas inicializado');
        }
    }
    
    // ===================================
    // REGISTRAR DEUDA
    // ===================================
    function initRegistrarDeuda() {
        initDateValidation();
        initMoneyInputs();
        initSelect2Deudas();
        initConceptoAutoFill();
        initConceptoRecurrenteLogic();
        initFormValidation();
    }
    
    function initSelect2Deudas() {
        if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
            console.warn('⚠️ jQuery o Select2 no disponibles');
            return;
        }
        
        const selectColegiado = $('#selectColegiado');
        if (selectColegiado.length) {
            selectColegiado.select2({
                theme: 'bootstrap-5',
                placeholder: 'Busque por número o nombre...',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: () => "No se encontraron resultados",
                    searching: () => "Buscando..."
                }
            });
        }
        
        const selectConcepto = $('#selectConcepto');
        if (selectConcepto.length) {
            selectConcepto.select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione un concepto...',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: () => "No se encontraron resultados"
                }
            });
        }
    }
    
    function initConceptoRecurrenteLogic() {
        const selectConcepto = document.getElementById('selectConcepto');
        const grupoFechaVencimiento = document.getElementById('grupoFechaVencimiento');
        const grupoFechaMaxima = document.getElementById('grupoFechaMaxima');
        const inputFechaVencimiento = document.querySelector('input[name="fecha_vencimiento"]');
        const inputFechaMaxima = document.querySelector('input[name="fecha_maxima_pago"]');
        
        if (!selectConcepto) return;
        
        selectConcepto.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const esRecurrente = selectedOption.getAttribute('data-recurrente') === '1';
            const diaVencimiento = selectedOption.getAttribute('data-dia-vencimiento');
            const frecuencia = selectedOption.getAttribute('data-frecuencia');
            
            if (esRecurrente && diaVencimiento) {
                if (grupoFechaVencimiento) grupoFechaVencimiento.style.display = 'none';
                if (grupoFechaMaxima) grupoFechaMaxima.style.display = 'none';
                
                const proximaFecha = calcularProximaFechaVencimiento(parseInt(diaVencimiento), frecuencia);
                
                if (inputFechaVencimiento) {
                    inputFechaVencimiento.value = proximaFecha;
                    inputFechaVencimiento.removeAttribute('required');
                }
                if (inputFechaMaxima) {
                    inputFechaMaxima.value = proximaFecha;
                }
                
                mostrarMensajeRecurrente(frecuencia, diaVencimiento, proximaFecha);
            } else {
                if (grupoFechaVencimiento) grupoFechaVencimiento.style.display = 'block';
                if (grupoFechaMaxima) grupoFechaMaxima.style.display = 'block';
                
                if (inputFechaVencimiento) {
                    inputFechaVencimiento.value = '';
                    inputFechaVencimiento.setAttribute('required', 'required');
                }
                if (inputFechaMaxima) {
                    inputFechaMaxima.value = '';
                }
                
                ocultarMensajeRecurrente();
            }
        });
    }
    
    function calcularProximaFechaVencimiento(diaVencimiento, frecuencia) {
        const hoy = new Date();
        let proximaFecha = new Date();
        
        switch(frecuencia) {
            case 'mensual':
                if (hoy.getDate() > diaVencimiento) {
                    proximaFecha.setMonth(proximaFecha.getMonth() + 1);
                }
                break;
            case 'trimestral':
                proximaFecha.setMonth(proximaFecha.getMonth() + 3);
                break;
            case 'semestral':
                proximaFecha.setMonth(proximaFecha.getMonth() + 6);
                break;
            case 'anual':
                proximaFecha.setFullYear(proximaFecha.getFullYear() + 1);
                break;
        }
        
        proximaFecha.setDate(diaVencimiento);
        return proximaFecha.toISOString().split('T')[0];
    }
    
    function mostrarMensajeRecurrente(frecuencia, dia, fecha) {
        let mensajeDiv = document.getElementById('mensajeRecurrente');
        
        if (!mensajeDiv) {
            mensajeDiv = document.createElement('div');
            mensajeDiv.id = 'mensajeRecurrente';
            mensajeDiv.className = 'alert alert-info mt-3';
            
            const selectConcepto = document.getElementById('selectConcepto');
            if (selectConcepto?.parentNode) {
                selectConcepto.parentNode.appendChild(mensajeDiv);
            }
        }
        
        const textoFrecuencia = {
            'mensual': 'mensual',
            'trimestral': 'trimestral',
            'semestral': 'semestral',
            'anual': 'anual'
        };
        
        mensajeDiv.innerHTML = `
            <i class="fas fa-info-circle me-2"></i>
            <strong>Concepto Recurrente:</strong> Este concepto se genera automáticamente cada 
            <strong>${textoFrecuencia[frecuencia] || frecuencia}</strong> el día <strong>${dia}</strong> de cada período.
            <br>
            <small class="text-muted">Próximo vencimiento calculado: ${window.AppUtils.formatDate(fecha)}</small>
        `;
        
        mensajeDiv.style.display = 'block';
    }
    
    function ocultarMensajeRecurrente() {
        const mensajeDiv = document.getElementById('mensajeRecurrente');
        if (mensajeDiv) {
            mensajeDiv.style.display = 'none';
        }
    }
    
    function initConceptoAutoFill() {
        const selectConcepto = document.getElementById('selectConcepto');
        const montoInput = document.getElementById('montoDeuda');
        const descripcionInput = document.querySelector('input[name="descripcion_deuda"]');
        
        if (selectConcepto && montoInput) {
            selectConcepto.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const montoSugerido = selectedOption.getAttribute('data-monto');
                const nombreConcepto = selectedOption.text.split('(')[0].trim();
                
                if (montoSugerido && parseFloat(montoSugerido) > 0) {
                    montoInput.value = parseFloat(montoSugerido).toFixed(2);
                    
                    montoInput.classList.add('highlight-input');
                    setTimeout(() => {
                        montoInput.classList.remove('highlight-input');
                    }, 1000);
                }
                
                if (descripcionInput && !descripcionInput.value && nombreConcepto) {
                    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                                   'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                    const mesActual = meses[new Date().getMonth()];
                    const anioActual = new Date().getFullYear();
                    descripcionInput.value = `${nombreConcepto} - ${mesActual} ${anioActual}`;
                }
            });
        }
    }
    
    function initDateValidation() {
        const fechaVencimiento = document.querySelector('input[name="fecha_vencimiento"]');
        const fechaMaxima = document.querySelector('input[name="fecha_maxima_pago"]');
        const hoy = new Date().toISOString().split('T')[0];
        
        if (fechaVencimiento) {
            fechaVencimiento.addEventListener('change', function() {
                if (this.value < hoy) {
                    const diasVencidos = Math.ceil((new Date() - new Date(this.value + 'T00:00:00')) / (1000 * 3600 * 24));
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Fecha Vencida',
                            text: `La fecha seleccionada está vencida hace ${diasVencidos} día(s). La deuda se registrará como vencida.`,
                            confirmButtonColor: '#B91D22',
                            confirmButtonText: 'Entendido'
                        });
                    } else {
                        alert(`Advertencia: La fecha está vencida hace ${diasVencidos} días`);
                    }
                }
            });
        }
        
        if (fechaMaxima && fechaVencimiento) {
            fechaMaxima.addEventListener('change', function() {
                if (fechaVencimiento.value && this.value && this.value < fechaVencimiento.value) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Fecha inválida',
                            text: 'La fecha máxima de pago debe ser posterior o igual a la fecha de vencimiento',
                            confirmButtonColor: '#B91D22'
                        });
                    } else {
                        alert('Error: La fecha máxima debe ser posterior a la fecha de vencimiento');
                    }
                    this.value = fechaVencimiento.value;
                }
            });
        }
    }
    
    function initMoneyInputs() {
        const moneyInputs = document.querySelectorAll('input[type="number"][step="0.01"]');
        
        moneyInputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value) {
                    const value = parseFloat(this.value);
                    if (!isNaN(value) && value >= 0) {
                        this.value = value.toFixed(2);
                    }
                }
            });
            
            input.addEventListener('input', function() {
                if (parseFloat(this.value) < 0) {
                    this.value = 0;
                }
            });
            
            input.addEventListener('keypress', function(e) {
                const char = String.fromCharCode(e.which);
                if (!/[\d.]/.test(char)) {
                    e.preventDefault();
                }
                if (char === '.' && this.value.includes('.')) {
                    e.preventDefault();
                }
            });
        });
    }
    
    function initFormValidation() {
        const form = document.getElementById('formRegistrarDeuda');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!validarFormularioDeuda(this)) {
                    e.preventDefault();
                    return false;
                }
                
                const btnSubmit = this.querySelector('button[type="submit"]');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
                }
            });
        }
    }
    
    function validarFormularioDeuda(form) {
        const colegiado = form.querySelector('[name="colegiado_id"]').value;
        const concepto = form.querySelector('[name="concepto_id"]').value;
        const monto = parseFloat(form.querySelector('[name="monto_esperado"]').value);
        const fecha = form.querySelector('[name="fecha_vencimiento"]').value;
        
        const errores = [];
        
        if (!colegiado) errores.push('Debe seleccionar un colegiado');
        if (!concepto) errores.push('Debe seleccionar un concepto');
        if (!monto || monto <= 0) errores.push('El monto debe ser mayor a 0');
        if (!fecha) errores.push('La fecha de vencimiento es obligatoria');
        
        if (errores.length > 0) {
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
        
        return true;
    }
    
    // ===================================
    // INDEX DEUDAS
    // ===================================
    function initIndexDeudas() {
        highlightVencidas();
    }
    
    function highlightVencidas() {
        const filasDeudas = document.querySelectorAll('tbody tr[data-estado]');
        
        filasDeudas.forEach(fila => {
            const estado = fila.getAttribute('data-estado');
            
            if (estado === 'vencido') {
                fila.style.backgroundColor = '#ffe6e6';
                fila.style.transition = 'background-color 0.3s';
                
                fila.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#ffcccc';
                });
                fila.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '#ffe6e6';
                });
            } else if (estado === 'pendiente') {
                const diasVencimiento = fila.querySelector('.text-warning');
                if (diasVencimiento) {
                    fila.style.backgroundColor = '#fff9e6';
                    
                    fila.addEventListener('mouseenter', function() {
                        this.style.backgroundColor = '#fff3cd';
                    });
                    fila.addEventListener('mouseleave', function() {
                        this.style.backgroundColor = '#fff9e6';
                    });
                }
            }
        });
    }
    
    // ===================================
    // ACCIONES GLOBALES
    // ===================================
    function cancelarDeuda(id) {
        if (typeof Swal === 'undefined') {
            if (!confirm('¿Cancelar esta deuda?')) return;
            
            const motivo = prompt('Ingrese el motivo de cancelación:');
            if (!motivo) return;
            
            procesarCancelacion(id, motivo);
            return;
        }
        
        Swal.fire({
            title: '¿Cancelar esta deuda?',
            text: "Esta acción marcará la deuda como cancelada. ¿Está seguro?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No, volver'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Motivo de cancelación',
                    input: 'textarea',
                    inputLabel: 'Ingrese el motivo de la cancelación',
                    inputPlaceholder: 'Ej: Error en el registro, duplicado, solicitud del colegiado, etc.',
                    inputAttributes: { 'aria-label': 'Motivo de cancelación', 'rows': 3 },
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar cancelación',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#3085d6',
                    showLoaderOnConfirm: true,
                    preConfirm: (motivo) => {
                        if (!motivo || motivo.trim() === '') {
                            Swal.showValidationMessage('Debe ingresar un motivo');
                            return false;
                        }
                        return motivo.trim();
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        procesarCancelacion(id, result.value);
                    }
                });
            }
        });
    }
    
    function procesarCancelacion(id, motivo) {
        fetch(getAppUrl(`deudas/cancelar/${id}`), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ motivo: motivo })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Cancelada!',
                        text: 'La deuda ha sido cancelada correctamente.',
                        confirmButtonColor: '#28a745'
                    }).then(() => window.location.reload());
                } else {
                    alert('Deuda cancelada correctamente');
                    window.location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudo cancelar la deuda',
                        confirmButtonColor: '#B91D22'
                    });
                } else {
                    alert('Error: ' + (data.message || 'No se pudo cancelar'));
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor. Intente nuevamente.',
                    confirmButtonColor: '#B91D22'
                });
            } else {
                alert('Error de conexión');
            }
        });
    }
    
    function eliminarDeuda(id) {
        const mensaje = '¿Eliminar esta deuda? Esta acción no se puede deshacer.';
        
        if (typeof Swal === 'undefined') {
            if (!confirm(mensaje)) return;
            
            fetch(getAppUrl(`deudas/eliminar/${id}`), {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Deuda eliminada correctamente');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo eliminar'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
            return;
        }
        
        Swal.fire({
            title: '¿Eliminar esta deuda?',
            html: '<p>Esta acción <strong>no se puede deshacer</strong>.</p>' +
                  '<p class="text-muted">Solo se pueden eliminar deudas pendientes sin pagos asociados.</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(getAppUrl(`deudas/eliminar/${id}`), {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminada!',
                            text: 'La deuda ha sido eliminada correctamente.',
                            confirmButtonColor: '#28a745'
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'No se pudo eliminar la deuda',
                            confirmButtonColor: '#B91D22'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor.',
                        confirmButtonColor: '#B91D22'
                    });
                });
            }
        });
    }
    
    // ===================================
    // API PÚBLICA DEL MÓDULO
    // ===================================
    return {
        init: init,
        cancelarDeuda: cancelarDeuda,
        eliminarDeuda: eliminarDeuda
    };
})();

// ===================================
// AUTO-INICIALIZACIÓN
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('/deudas')) {
        DeudasModule.init();
    }
});

// ===================================
// EXPORTAR FUNCIONES GLOBALES
// ===================================
window.cancelarDeuda = function(id) {
    DeudasModule.cancelarDeuda(id);
};

window.eliminarDeuda = function(id) {
    DeudasModule.eliminarDeuda(id);
};