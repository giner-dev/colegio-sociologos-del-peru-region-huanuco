// ============================================
// INICIALIZACIÓN
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    initDeudasModule();
});

function initDeudasModule() {
    initDateValidation();
    initMoneyInputs();
    initSelect2();
    initConceptoAutoFill();
    initConceptoRecurrenteLogic();
    initFormValidation();
    highlightVencidas();
    
    console.log('Módulo de Deudas inicializado correctamente');
}

// ============================================
// INICIALIZACIÓN DE SELECT2
// ============================================

function initSelect2() {
    // Verificar si jQuery y Select2 están disponibles
    if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
        console.warn('jQuery o Select2 no están disponibles');
        return;
    }
    
    // Select2 para colegiados
    const selectColegiado = $('#selectColegiado');
    if (selectColegiado.length) {
        selectColegiado.select2({
            theme: 'bootstrap-5',
            placeholder: 'Busque por número o nombre...',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });
    }
    
    // Select2 para conceptos
    const selectConcepto = $('#selectConcepto');
    if (selectConcepto.length) {
        selectConcepto.select2({
            theme: 'bootstrap-5',
            placeholder: 'Seleccione un concepto...',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                }
            }
        });
    }
}

// ============================================
// LÓGICA DE CONCEPTOS RECURRENTES
// ============================================

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
            // Ocultar campos de fecha
            if (grupoFechaVencimiento) grupoFechaVencimiento.style.display = 'none';
            if (grupoFechaMaxima) grupoFechaMaxima.style.display = 'none';
            
            // Calcular próxima fecha de vencimiento
            const proximaFecha = calcularProximaFechaVencimiento(parseInt(diaVencimiento), frecuencia);
            
            // Asignar fecha automáticamente
            if (inputFechaVencimiento) {
                inputFechaVencimiento.value = proximaFecha;
                inputFechaVencimiento.removeAttribute('required');
            }
            if (inputFechaMaxima) {
                inputFechaMaxima.value = proximaFecha;
                inputFechaMaxima.removeAttribute('required');
            }
            
            // Mostrar mensaje informativo
            mostrarMensajeRecurrente(frecuencia, diaVencimiento, proximaFecha);
            
        } else {
            // Mostrar campos de fecha para conceptos no recurrentes
            if (grupoFechaVencimiento) grupoFechaVencimiento.style.display = 'block';
            if (grupoFechaMaxima) grupoFechaMaxima.style.display = 'block';
            
            // Hacer requeridos
            if (inputFechaVencimiento) {
                inputFechaVencimiento.value = '';
                inputFechaVencimiento.setAttribute('required', 'required');
            }
            if (inputFechaMaxima) {
                inputFechaMaxima.value = '';
            }
            
            // Ocultar mensaje informativo
            ocultarMensajeRecurrente();
        }
    });
}

function calcularProximaFechaVencimiento(diaVencimiento, frecuencia) {
    const hoy = new Date();
    let proximaFecha = new Date();
    
    // Ajustar según la frecuencia
    switch(frecuencia) {
        case 'mensual':
            // Si ya pasó el día este mes, ir al siguiente mes
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
    
    // Establecer el día de vencimiento
    proximaFecha.setDate(diaVencimiento);
    
    // Formatear como YYYY-MM-DD
    return proximaFecha.toISOString().split('T')[0];
}

function mostrarMensajeRecurrente(frecuencia, dia, fecha) {
    // Buscar o crear contenedor de mensaje
    let mensajeDiv = document.getElementById('mensajeRecurrente');
    
    if (!mensajeDiv) {
        mensajeDiv = document.createElement('div');
        mensajeDiv.id = 'mensajeRecurrente';
        mensajeDiv.className = 'alert alert-info mt-3';
        
        const selectConcepto = document.getElementById('selectConcepto');
        if (selectConcepto && selectConcepto.parentNode) {
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
        <small class="text-muted">Próximo vencimiento calculado: ${formatearFecha(fecha)}</small>
    `;
    
    mensajeDiv.style.display = 'block';
}

function ocultarMensajeRecurrente() {
    const mensajeDiv = document.getElementById('mensajeRecurrente');
    if (mensajeDiv) {
        mensajeDiv.style.display = 'none';
    }
}

// ============================================
// AUTO-COMPLETAR MONTO SEGÚN CONCEPTO
// ============================================

function initConceptoAutoFill() {
    const selectConcepto = document.getElementById('selectConcepto');
    const montoInput = document.getElementById('montoDeuda');
    const descripcionInput = document.querySelector('input[name="descripcion_deuda"]');
    
    if (selectConcepto && montoInput) {
        selectConcepto.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const montoSugerido = selectedOption.getAttribute('data-monto');
            const nombreConcepto = selectedOption.text;
            
            // Auto-completar monto
            if (montoSugerido && parseFloat(montoSugerido) > 0) {
                montoInput.value = parseFloat(montoSugerido).toFixed(2);
                
                // Efecto visual
                montoInput.classList.add('highlight-input');
                setTimeout(() => {
                    montoInput.classList.remove('highlight-input');
                }, 1000);
            }
            
            // Auto-completar descripción si está vacía
            if (descripcionInput && !descripcionInput.value && nombreConcepto) {
                const mesActual = obtenerNombreMesActual();
                const anioActual = new Date().getFullYear();
                descripcionInput.value = `${nombreConcepto} - ${mesActual} ${anioActual}`;
            }
        });
    }
}

function obtenerNombreMesActual() {
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                   'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    return meses[new Date().getMonth()];
}

// ============================================
// VALIDACIÓN DE FECHAS
// ============================================

function initDateValidation() {
    const fechaVencimiento = document.querySelector('input[name="fecha_vencimiento"]');
    const fechaMaxima = document.querySelector('input[name="fecha_maxima_pago"]');
    
    // Establecer fecha mínima como hoy
    const hoy = new Date().toISOString().split('T')[0];
    
    if (fechaVencimiento) {
        // No establecer mínimo para permitir fechas retroactivas
        // fechaVencimiento.setAttribute('min', hoy);
        
        fechaVencimiento.addEventListener('change', function() {
            if (this.value < hoy) {
                const diasVencidos = calcularDiasVencidos(this.value);
                
                Swal.fire({
                    icon: 'warning',
                    title: 'Fecha Vencida',
                    text: `La fecha seleccionada está vencida hace ${diasVencidos} día(s). La deuda se registrará como vencida.`,
                    confirmButtonColor: '#B91D22',
                    confirmButtonText: 'Entendido'
                });
            }
        });
    }
    
    if (fechaMaxima) {
        // No establecer mínimo
        // fechaMaxima.setAttribute('min', hoy);
    }
    
    // Validar que fecha_maxima_pago sea posterior a fecha_vencimiento
    if (fechaVencimiento && fechaMaxima) {
        fechaMaxima.addEventListener('change', function() {
            if (fechaVencimiento.value && this.value && this.value < fechaVencimiento.value) {
                Swal.fire({
                    icon: 'error',
                    title: 'Fecha inválida',
                    text: 'La fecha máxima de pago debe ser posterior o igual a la fecha de vencimiento',
                    confirmButtonColor: '#B91D22'
                });
                this.value = fechaVencimiento.value;
            }
        });
    }
}

// ============================================
// FORMATEAR INPUTS DE DINERO
// ============================================

function initMoneyInputs() {
    const moneyInputs = document.querySelectorAll('input[type="number"][step="0.01"]');
    
    moneyInputs.forEach(input => {
        // Formatear al perder el foco
        input.addEventListener('blur', function() {
            if (this.value) {
                const value = parseFloat(this.value);
                if (!isNaN(value) && value >= 0) {
                    this.value = value.toFixed(2);
                }
            }
        });
        
        // Evitar valores negativos
        input.addEventListener('input', function() {
            if (parseFloat(this.value) < 0) {
                this.value = 0;
            }
        });
        
        // Permitir solo números y punto decimal
        input.addEventListener('keypress', function(e) {
            const char = String.fromCharCode(e.which);
            if (!/[\d.]/.test(char)) {
                e.preventDefault();
            }
            
            // Solo un punto decimal
            if (char === '.' && this.value.includes('.')) {
                e.preventDefault();
            }
        });
    });
}

// ============================================
// VALIDACIÓN DEL FORMULARIO
// ============================================

function initFormValidation() {
    const form = document.getElementById('formRegistrarDeuda');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validarFormularioDeuda(this)) {
                e.preventDefault();
                return false;
            }
            
            // Mostrar loading
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
    
    if (!colegiado) {
        errores.push('Debe seleccionar un colegiado');
    }
    
    if (!concepto) {
        errores.push('Debe seleccionar un concepto');
    }
    
    if (!monto || monto <= 0) {
        errores.push('El monto debe ser mayor a 0');
    }
    
    if (!fecha) {
        errores.push('La fecha de vencimiento es obligatoria');
    }
    
    if (errores.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Errores de validación',
            html: '<ul style="text-align: left; margin: 0;">' + 
                  errores.map(e => '<li>' + e + '</li>').join('') + 
                  '</ul>',
            confirmButtonColor: '#B91D22'
        });
        return false;
    }
    
    return true;
}

// ============================================
// RESALTAR DEUDAS VENCIDAS
// ============================================

function highlightVencidas() {
    const filasDeudas = document.querySelectorAll('tbody tr[data-estado]');
    
    filasDeudas.forEach(fila => {
        const estado = fila.getAttribute('data-estado');
        
        if (estado === 'vencido') {
            fila.style.backgroundColor = '#ffe6e6';
            fila.style.transition = 'background-color 0.3s';
            
            // Hover effect
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

// ============================================
// CANCELAR DEUDA
// ============================================

function cancelarDeuda(id) {
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
                inputAttributes: {
                    'aria-label': 'Motivo de cancelación',
                    'rows': 3
                },
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
    const baseUrl = getBaseUrl();
    
    fetch(`${baseUrl}/deudas/cancelar/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            motivo: motivo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Cancelada!',
                text: 'La deuda ha sido cancelada correctamente.',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'No se pudo cancelar la deuda',
                confirmButtonColor: '#B91D22'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor. Intente nuevamente.',
            confirmButtonColor: '#B91D22'
        });
    });
}

// ============================================
// ELIMINAR DEUDA
// ============================================

function eliminarDeuda(id) {
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
            const baseUrl = getBaseUrl();
            
            fetch(`${baseUrl}/deudas/eliminar/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminada!',
                        text: 'La deuda ha sido eliminada correctamente.',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        window.location.reload();
                    });
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
                    text: 'No se pudo conectar con el servidor. Intente nuevamente.',
                    confirmButtonColor: '#B91D22'
                });
            });
        }
    });
}

// ============================================
// FUNCIONES DE API
// ============================================

/**
 * Cargar deudas pendientes de un colegiado
 */
function cargarDeudasPendientes(colegiadoId) {
    const baseUrl = getBaseUrl();
    
    return fetch(`${baseUrl}/deudas/api-deudas-pendientes/${colegiadoId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la solicitud');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                return data.deudas;
            } else {
                throw new Error(data.message || 'Error al cargar deudas');
            }
        })
        .catch(error => {
            console.error('Error al cargar deudas:', error);
            throw error;
        });
}

// ============================================
// FUNCIONES DE UTILIDAD
// ============================================

/**
 * Obtener la URL base de la aplicación
 */
function getBaseUrl() {
    // Intentar obtener de una variable global
    if (typeof APP_URL !== 'undefined') {
        return APP_URL;
    }
    
    // Fallback: construir desde window.location
    return window.location.origin;
}

/**
 * Calcular días vencidos desde una fecha
 */
function calcularDiasVencidos(fechaVencimiento) {
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    const vencimiento = new Date(fechaVencimiento + 'T00:00:00');
    
    const diff = Math.abs(hoy - vencimiento);
    return Math.ceil(diff / (1000 * 60 * 60 * 24));
}

/**
 * Calcular días restantes hasta el vencimiento
 */
function calcularDiasRestantes(fechaVencimiento) {
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    const vencimiento = new Date(fechaVencimiento + 'T00:00:00');
    
    const diferencia = vencimiento.getTime() - hoy.getTime();
    return Math.ceil(diferencia / (1000 * 3600 * 24));
}

/**
 * Formatear fecha DD/MM/YYYY
 */
function formatearFecha(fecha) {
    if (!fecha) return '-';
    
    const partes = fecha.split('-');
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    return fecha;
}

/**
 * Formatear estado de deuda con badge HTML
 */
function formatearEstado(estado, saldo, montoTotal) {
    const estados = {
        'pendiente': { clase: 'warning', texto: 'Pendiente' },
        'parcial': { clase: 'info', texto: 'Pago Parcial' },
        'vencido': { clase: 'danger', texto: 'Vencido' },
        'pagado': { clase: 'success', texto: 'Pagado' },
        'cancelado': { clase: 'secondary', texto: 'Cancelado' }
    };
    
    const info = estados[estado] || { clase: 'light', texto: estado };
    let html = `<span class="badge bg-${info.clase}">${info.texto}</span>`;
    
    if (estado === 'parcial' && montoTotal > 0) {
        const porcentaje = ((montoTotal - saldo) / montoTotal * 100).toFixed(1);
        html += `<br><small class="text-muted">${porcentaje}% pagado</small>`;
    }
    
    return html;
}

/**
 * Formatear monto como moneda
 */
function formatearMoneda(monto) {
    const numero = parseFloat(monto);
    if (isNaN(numero)) return 'S/ 0.00';
    
    return 'S/ ' + numero.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// ============================================
// EXPORTAR FUNCIONES GLOBALES
// ============================================

window.cancelarDeuda = cancelarDeuda;
window.eliminarDeuda = eliminarDeuda;
window.cargarDeudasPendientes = cargarDeudasPendientes;
window.calcularDiasVencidos = calcularDiasVencidos;
window.calcularDiasRestantes = calcularDiasRestantes;
window.formatearEstado = formatearEstado;
window.formatearMoneda = formatearMoneda;
window.formatearFecha = formatearFecha;