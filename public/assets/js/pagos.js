// Módulo de Pagos - JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initPagosModule();
});

function initPagosModule() {
    initDateFilters();
    initMoneyInputs();
    initTableSorting();
    console.log('Módulo de Pagos inicializado');
}

// Inicializar filtros de fecha
function initDateFilters() {
    const fechaInicio = document.querySelector('input[name="fecha_inicio"]');
    const fechaFin = document.querySelector('input[name="fecha_fin"]');
    
    if (fechaInicio && fechaFin) {
        fechaInicio.addEventListener('change', function() {
            if (fechaFin.value && this.value > fechaFin.value) {
                showToast('La fecha de inicio no puede ser mayor a la fecha fin', 'warning');
                this.value = '';
            }
        });
        
        fechaFin.addEventListener('change', function() {
            if (fechaInicio.value && this.value < fechaInicio.value) {
                showToast('La fecha fin no puede ser menor a la fecha de inicio', 'warning');
                this.value = '';
            }
        });
    }
}

// Formatear inputs de dinero
function initMoneyInputs() {
    const moneyInputs = document.querySelectorAll('input[type="number"][step="0.01"]');
    
    moneyInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                const value = parseFloat(this.value);
                if (!isNaN(value)) {
                    this.value = value.toFixed(2);
                }
            }
        });
        
        input.addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });
    });
}

// Ordenamiento de tabla
function initTableSorting() {
    const tableHeaders = document.querySelectorAll('.table thead th[data-sortable]');
    
    tableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.innerHTML += ' <i class="fas fa-sort"></i>';
        
        header.addEventListener('click', function() {
            const table = this.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const index = Array.from(this.parentElement.children).indexOf(this);
            const isAscending = this.classList.contains('sort-asc');
            
            tableHeaders.forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
                h.querySelector('i').className = 'fas fa-sort';
            });
            
            rows.sort((a, b) => {
                const aValue = a.children[index].textContent.trim();
                const bValue = b.children[index].textContent.trim();
                
                if (!isNaN(aValue) && !isNaN(bValue)) {
                    return isAscending ? 
                        parseFloat(bValue) - parseFloat(aValue) : 
                        parseFloat(aValue) - parseFloat(bValue);
                }
                
                return isAscending ? 
                    bValue.localeCompare(aValue) : 
                    aValue.localeCompare(bValue);
            });
            
            rows.forEach(row => tbody.appendChild(row));
            
            this.classList.toggle('sort-asc', !isAscending);
            this.classList.toggle('sort-desc', isAscending);
            this.querySelector('i').className = isAscending ? 
                'fas fa-sort-up' : 'fas fa-sort-down';
        });
    });
}

// Calcular totales en tiempo real
function calcularTotal() {
    const monto = parseFloat(document.getElementById('inputMonto')?.value) || 0;
    const totalDisplay = document.querySelector('.total-display');
    
    if (totalDisplay) {
        totalDisplay.textContent = formatMoney(monto);
    }
}

// Validar formulario de registro
function validarFormularioPago(form) {
    const colegiado = form.querySelector('[name="colegiados_id"]').value;
    const concepto = form.querySelector('[name="concepto_id"]').value;
    const conceptoTexto = form.querySelector('[name="concepto_texto"]').value;
    const monto = parseFloat(form.querySelector('[name="monto"]').value);
    const fecha = form.querySelector('[name="fecha_pago"]').value;
    const metodo = form.querySelector('[name="metodo_pago_id"]').value;
    
    const errores = [];
    
    if (!colegiado) {
        errores.push('Debe seleccionar un colegiado');
    }
    
    if (!concepto && !conceptoTexto) {
        errores.push('Debe especificar un concepto de pago');
    }
    
    if (!monto || monto <= 0) {
        errores.push('El monto debe ser mayor a 0');
    }
    
    if (!fecha) {
        errores.push('La fecha de pago es obligatoria');
    }
    
    if (!metodo) {
        errores.push('Debe seleccionar un método de pago');
    }
    
    if (errores.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Errores de validación',
            html: '<ul style="text-align: left;">' + 
                  errores.map(e => '<li>' + e + '</li>').join('') + 
                  '</ul>'
        });
        return false;
    }
    
    return true;
}

// Exportar a Excel
function exportarPagosExcel(filtros = {}) {
    const params = new URLSearchParams(filtros);
    window.location.href = '/pagos/exportar-excel?' + params.toString();
}

// Imprimir comprobante
function imprimirComprobante(idPago) {
    const ventana = window.open('/pagos/comprobante/' + idPago, '_blank');
    ventana.onload = function() {
        ventana.print();
    };
}

// Confirmar anulación de pago
function confirmarAnulacion(idPago) {
    return new Promise((resolve, reject) => {
        Swal.fire({
            title: '¿Anular este pago?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#B91D22',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar',
            input: 'textarea',
            inputPlaceholder: 'Motivo de la anulación (opcional)',
            inputAttributes: {
                'aria-label': 'Motivo de anulación'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                resolve(result.value);
            } else {
                reject();
            }
        });
    });
}

// Filtros rápidos predefinidos
function aplicarFiltroRapido(tipo) {
    const hoy = new Date();
    let fechaInicio, fechaFin;
    
    switch(tipo) {
        case 'hoy':
            fechaInicio = fechaFin = formatDate(hoy);
            break;
        case 'semana':
            const primerDia = new Date(hoy.setDate(hoy.getDate() - hoy.getDay()));
            const ultimoDia = new Date(hoy.setDate(hoy.getDate() - hoy.getDay() + 6));
            fechaInicio = formatDate(primerDia);
            fechaFin = formatDate(ultimoDia);
            break;
        case 'mes':
            fechaInicio = formatDate(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
            fechaFin = formatDate(new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0));
            break;
        case 'anio':
            fechaInicio = formatDate(new Date(hoy.getFullYear(), 0, 1));
            fechaFin = formatDate(new Date(hoy.getFullYear(), 11, 31));
            break;
    }
    
    document.querySelector('input[name="fecha_inicio"]').value = fechaInicio;
    document.querySelector('input[name="fecha_fin"]').value = fechaFin;
    document.getElementById('formFiltros').submit();
}

// Formatear fecha para input
function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Formatear dinero
function formatMoney(amount) {
    return 'S/ ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Limpiar filtros
function limpiarFiltros() {
    document.getElementById('formFiltros').reset();
    window.location.href = window.location.pathname;
}

// Gestión de Conceptos y Métodos
function validarFormularioConcepto(form) {
    const nombre = form.querySelector('[name="nombre"]').value.trim();
    const monto = parseFloat(form.querySelector('[name="monto"]').value);
    
    if (!nombre) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El nombre del concepto es obligatorio'
        });
        return false;
    }
    
    if (isNaN(monto) || monto < 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El monto debe ser un número válido mayor o igual a 0'
        });
        return false;
    }
    
    return true;
}

function validarFormularioMetodo(form) {
    const nombre = form.querySelector('[name="nombre"]').value.trim();
    
    if (!nombre) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El nombre del método es obligatorio'
        });
        return false;
    }
    
    return true;
}

// Confirmar eliminación
function confirmarEliminacion(tipo, id) {
    return new Promise((resolve, reject) => {
        Swal.fire({
            title: `¿Desactivar este ${tipo}?`,
            text: 'Los registros existentes no se verán afectados',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#B91D22',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                resolve(true);
            } else {
                reject(false);
            }
        });
    });
}

// Validar campos numéricos
document.addEventListener('DOMContentLoaded', function() {
    const numberInputs = document.querySelectorAll('input[type="number"]');
    
    numberInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (this.getAttribute('step') === '0.01') {
                const charCode = e.which ? e.which : e.keyCode;
                if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
                    e.preventDefault();
                }
            }
        });
    });
});