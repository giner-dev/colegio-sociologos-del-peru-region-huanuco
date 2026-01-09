/**
 * MÓDULO DE PAGOS
 */

// ===================================
// NAMESPACE DEL MÓDULO
// ===================================
window.PagosModule = (function() {
    'use strict';
    
    // Variables privadas del módulo
    let initialized = false;
    let deudasData = [];
    let deudaSeleccionada = null;
    let colegiadoSeleccionado = null;
    let currentPage = 1;
    let rowsPerPage = 10;
    let allRows = [];
    
    // ===================================
    // INICIALIZACIÓN
    // ===================================
    function init() {
        if (initialized) {
            console.warn('⚠️ Módulo Pagos ya inicializado');
            return;
        }
        
        const currentPath = window.location.pathname;
        
        if (currentPath.includes('/pagos')) {
            console.log('💰 Inicializando módulo Pagos...');
            
            if (currentPath.includes('/pagos/registrar')) {
                initRegistrarPago();
            } else if (currentPath.includes('/pagos/ver/')) {
                initVerPago();
            } else if (currentPath.includes('/pagos/conceptos')) {
                initConceptos();
            } else if (currentPath.includes('/pagos/metodos')) {
                initMetodos();
            } else if (currentPath.includes('/pagos')) {
                initIndexPagos();
            }
            
            initialized = true;
            console.log('✅ Módulo Pagos inicializado');
        }
    }
    
    // ===================================
    // REGISTRAR PAGO
    // ===================================
    function initRegistrarPago() {
        console.log('📝 Inicializando registro de pago...');
        
        cargarMetodosPago();
        cargarEstadisticasDeudas();
        initBuscadorColegiados();
        initPaginacion();
        
        document.querySelectorAll('.btn-seleccionar-colegiado').forEach(btn => {
            btn.addEventListener('click', function() {
                const colegiadoId = this.dataset.colegiadoId;
                const nombre = this.dataset.nombre;
                const numero = this.dataset.numero;
                seleccionarColegiado(colegiadoId, nombre, numero);
            });
        });
        
        const inputMonto = document.getElementById('inputMonto');
        if (inputMonto) {
            inputMonto.addEventListener('input', function() {
                const monto = parseFloat(this.value) || 0;
                const maxMonto = parseFloat(document.getElementById('max-monto')?.dataset?.max) || 0;
                
                if (monto > maxMonto && maxMonto > 0) {
                    this.value = maxMonto.toFixed(2);
                    showToast('El monto no puede exceder el saldo pendiente', 'warning');
                }
                
                actualizarTotal();
            });
        }
        
        const selectMetodo = document.getElementById('selectMetodo');
        if (selectMetodo) {
            selectMetodo.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const requiereComprobante = selectedOption?.dataset?.requiere === '1';
                
                const comprobanteRequerido = document.getElementById('comprobante-requerido');
                const inputComprobante = document.getElementById('inputComprobante');
                
                if (comprobanteRequerido && inputComprobante) {
                    if (requiereComprobante) {
                        comprobanteRequerido.style.display = 'block';
                        inputComprobante.required = true;
                    } else {
                        comprobanteRequerido.style.display = 'none';
                        inputComprobante.required = false;
                    }
                }
            });
        }
        
        const form = document.getElementById('formRegistrarPago');
        if (form) {
            form.addEventListener('submit', validarFormularioPago);
        }
    }
    
    function cargarEstadisticasDeudas() {
        console.log('📊 Cargando estadísticas de deudas...');
        
        const rows = document.querySelectorAll('.colegiado-row');
        
        if (rows.length === 0) {
            console.warn('⚠️ No se encontraron filas de colegiados');
            return;
        }
        
        rows.forEach(row => {
            const colegiadoId = row.dataset.colegiadoId;
            const url = getAppUrl(`pagos/api-deudas-pendientes/${colegiadoId}`);
            
            console.log(`🔍 Consultando deudas para colegiado ${colegiadoId}:`, url);
            
            fetch(url)
                .then(response => {
                    console.log(`📥 Respuesta recibida para colegiado ${colegiadoId}:`, response.status);
                    return response.json();
                })
                .then(data => {
                    console.log(`✅ Datos procesados para colegiado ${colegiadoId}:`, data);
                    
                    if (data.success && data.deudas) {
                        const countElement = document.getElementById(`count-deudas-${colegiadoId}`);
                        const montoElement = document.getElementById(`monto-total-${colegiadoId}`);
                        
                        const totalDeudas = data.deudas.length;
                        const montoTotal = data.deudas.reduce((sum, deuda) => {
                            return sum + parseFloat(deuda.saldo_pendiente || 0);
                        }, 0);
                        
                        if (countElement) {
                            countElement.innerHTML = totalDeudas;
                        }
                        
                        if (montoElement) {
                            montoElement.innerHTML = `S/ ${montoTotal.toFixed(2)}`;
                        }
                    } else {
                        console.warn(`⚠️ Sin deudas para colegiado ${colegiadoId}`);
                        
                        const countElement = document.getElementById(`count-deudas-${colegiadoId}`);
                        const montoElement = document.getElementById(`monto-total-${colegiadoId}`);
                        
                        if (countElement) countElement.innerHTML = '0';
                        if (montoElement) montoElement.innerHTML = 'S/ 0.00';
                    }
                })
                .catch(error => {
                    console.error(`❌ Error al cargar estadísticas para ${colegiadoId}:`, error);
                    
                    const countElement = document.getElementById(`count-deudas-${colegiadoId}`);
                    const montoElement = document.getElementById(`monto-total-${colegiadoId}`);
                    
                    if (countElement) {
                        countElement.innerHTML = '<i class="fas fa-exclamation-triangle text-danger"></i>';
                    }
                    if (montoElement) {
                        montoElement.innerHTML = '<span class="text-danger">Error</span>';
                    }
                });
        });
    }
    
    function initBuscadorColegiados() {
        const searchInput = document.getElementById('searchColegiado');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                filterColegiados(searchTerm);
            });
        }
    }
    
    function filterColegiados(searchTerm) {
        const rows = document.querySelectorAll('.colegiado-row');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const numero = (row.dataset.numero || '').toLowerCase();
            const dni = (row.dataset.dni || '').toLowerCase();
            const nombre = (row.dataset.nombre || '').toLowerCase();
            
            const matches = numero.includes(searchTerm) || 
                           dni.includes(searchTerm) || 
                           nombre.includes(searchTerm);
            
            if (matches || searchTerm === '') {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        const totalElement = document.getElementById('totalColegiadosConDeudas');
        if (totalElement) {
            totalElement.textContent = visibleCount;
        }
        
        initPaginacion();
    }
    
    function initPaginacion() {
        allRows = Array.from(document.querySelectorAll('.colegiado-row'))
            .filter(row => row.style.display !== 'none');
        
        currentPage = 1;
        renderPage();
        renderPagination();
    }
    
    function renderPage() {
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        
        allRows.forEach((row, index) => {
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });
    }
    
    function renderPagination() {
        const totalPages = Math.ceil(allRows.length / rowsPerPage);
        const pagination = document.getElementById('pagination');
        const container = document.getElementById('paginationContainer');
        
        if (!pagination || totalPages <= 1) {
            if (container) container.style.display = 'none';
            return;
        }
        
        if (container) container.style.display = 'block';
        pagination.innerHTML = '';
        
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a>`;
        if (currentPage > 1) {
            prevLi.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage--;
                renderPage();
                renderPagination();
            });
        }
        pagination.appendChild(prevLi);
        
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage = i;
                renderPage();
                renderPagination();
            });
            pagination.appendChild(li);
        }
        
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a>`;
        if (currentPage < totalPages) {
            nextLi.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage++;
                renderPage();
                renderPagination();
            });
        }
        pagination.appendChild(nextLi);
    }
    
    function seleccionarColegiado(colegiadoId, nombre, numero) {
        console.log(`👤 Colegiado seleccionado: ${numero} - ${nombre}`);
        
        colegiadoSeleccionado = { id: colegiadoId, nombre, numero };
        
        document.getElementById('colegiadoSeleccionadoInfo').innerHTML = 
            `<strong>${numero}</strong> - ${nombre}`;
        
        document.getElementById('inputColegiadoId').value = colegiadoId;
        
        document.getElementById('paso1').style.display = 'none';
        document.getElementById('paso2').style.display = 'block';
        
        cargarDeudasPendientes(colegiadoId);
        
        document.getElementById('paso2').scrollIntoView({ behavior: 'smooth' });
    }
    
    function cargarDeudasPendientes(colegiadoId) {
        const tbody = document.getElementById('deudas-body');
        const url = getAppUrl(`pagos/api-deudas-pendientes/${colegiadoId}`);
        
        console.log(`🔄 Cargando deudas para colegiado ${colegiadoId}...`);
        console.log(`📡 URL completa: ${url}`);
        
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 mb-0">Cargando deudas...</p>
                </td>
            </tr>
        `;
        
        fetch(url)
            .then(response => {
                console.log('📥 Respuesta recibida:', response.status, response.statusText);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('✅ Datos de deudas:', data);
                
                if (data.success && data.deudas && data.deudas.length > 0) {
                    deudasData = data.deudas;
                    mostrarDeudas(deudasData);
                    document.getElementById('mensaje-sin-deudas').style.display = 'none';
                    document.getElementById('tabla-deudas-container').style.display = 'block';
                } else {
                    console.warn('⚠️ No hay deudas pendientes');
                    document.getElementById('mensaje-sin-deudas').style.display = 'block';
                    document.getElementById('tabla-deudas-container').style.display = 'none';
                    tbody.innerHTML = '';
                }
            })
            .catch(error => {
                console.error('❌ Error al cargar deudas:', error);
                showToast('Error al cargar las deudas: ' + error.message, 'error');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-danger py-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error al cargar las deudas: ${error.message}
                        </td>
                    </tr>
                `;
            });
    }
    
    function mostrarDeudas(deudas) {
        console.log(`📋 Mostrando ${deudas.length} deudas`);
        
        const tbody = document.getElementById('deudas-body');
        tbody.innerHTML = '';
        
        deudas.forEach((deuda, index) => {
            const vencimiento = new Date(deuda.fecha_vencimiento);
            const hoy = new Date();
            const diasVencidos = Math.ceil((hoy - vencimiento) / (1000 * 3600 * 24));
            const estaVencida = diasVencidos > 0;
            
            const row = document.createElement('tr');
            row.className = estaVencida ? 'table-danger' : '';
            row.innerHTML = `
                <td>
                    <input type="radio" name="deuda_radio" value="${deuda.idDeuda}" 
                           data-index="${index}" class="form-check-input">
                </td>
                <td>
                    <strong>${window.AppUtils.escapeHtml(deuda.concepto_nombre || 'Sin concepto')}</strong>
                    ${estaVencida ? '<br><span class="badge bg-danger">Vencida</span>' : ''}
                </td>
                <td>${window.AppUtils.escapeHtml(deuda.descripcion_deuda || '-')}</td>
                <td class="text-end">S/ ${parseFloat(deuda.monto_esperado || 0).toFixed(2)}</td>
                <td class="text-end text-success">S/ ${parseFloat(deuda.monto_pagado || 0).toFixed(2)}</td>
                <td class="text-end">
                    <strong class="text-danger">S/ ${parseFloat(deuda.saldo_pendiente || 0).toFixed(2)}</strong>
                </td>
                <td>
                    ${window.AppUtils.formatDate(vencimiento)}
                    ${estaVencida ? 
                        `<br><small class="text-danger">Hace ${diasVencidos} días</small>` : 
                        `<br><small class="text-muted">En ${Math.abs(diasVencidos)} días</small>`
                    }
                </td>
                <td>
                    <span class="badge bg-${deuda.estado === 'vencido' ? 'danger' : 'warning'}">
                        ${(deuda.estado || 'pendiente').toUpperCase()}
                    </span>
                </td>
            `;
            
            tbody.appendChild(row);
            
            const radio = row.querySelector('input[type="radio"]');
            radio.addEventListener('change', () => seleccionarDeuda(index));
        });
    }
    
    function seleccionarDeuda(index) {
        deudaSeleccionada = deudasData[index];
        
        console.log('💰 Deuda seleccionada:', deudaSeleccionada);
        
        document.getElementById('deuda-concepto').textContent = 
            deudaSeleccionada.concepto_nombre || 'Sin concepto';
        document.getElementById('deuda-saldo').textContent = 
            `S/ ${parseFloat(deudaSeleccionada.saldo_pendiente || 0).toFixed(2)}`;
        document.getElementById('deuda-vencimiento').textContent = 
            window.AppUtils.formatDate(new Date(deudaSeleccionada.fecha_vencimiento));
        
        document.getElementById('inputDeudaId').value = deudaSeleccionada.idDeuda;
        
        const maxMonto = parseFloat(deudaSeleccionada.saldo_pendiente || 0);
        const maxMontoElement = document.getElementById('max-monto');
        maxMontoElement.textContent = `S/ ${maxMonto.toFixed(2)}`;
        maxMontoElement.dataset.max = maxMonto;
        
        const inputMonto = document.getElementById('inputMonto');
        inputMonto.value = maxMonto.toFixed(2);
        inputMonto.max = maxMonto;
        
        document.getElementById('deuda-seleccionada-container').style.display = 'block';
        document.getElementById('btnSiguientePaso3').disabled = false;
        
        actualizarTotal();
    }
    
    function cargarMetodosPago() {
        const url = getAppUrl('pagos/api-metodos');
        
        console.log('💳 Cargando métodos de pago desde:', url);
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log('✅ Métodos de pago cargados:', data);
                
                if (data.success && data.metodos) {
                    const select = document.getElementById('selectMetodo');
                    if (select) {
                        select.innerHTML = '<option value="">Seleccione...</option>';
                        
                        data.metodos.forEach(metodo => {
                            const option = document.createElement('option');
                            option.value = metodo.idMetodo;
                            option.textContent = metodo.nombre;
                            option.dataset.requiere = metodo.requiere_comprobante ? '1' : '0';
                            select.appendChild(option);
                        });
                    }
                }
            })
            .catch(error => {
                console.error('❌ Error al cargar métodos:', error);
                showToast('Error al cargar métodos de pago', 'error');
            });
    }
    
    function actualizarTotal() {
        const monto = parseFloat(document.getElementById('inputMonto')?.value) || 0;
        const totalElement = document.getElementById('total-registrar');
        if (totalElement) {
            totalElement.textContent = `S/ ${monto.toFixed(2)}`;
        }
    }
    
    function validarFormularioPago(e) {
        const montoInput = document.getElementById('inputMonto');
        const maxMontoElement = document.getElementById('max-monto');
        const selectMetodo = document.getElementById('selectMetodo');
        
        const monto = parseFloat(montoInput?.value) || 0;
        const maxMonto = parseFloat(maxMontoElement?.dataset?.max) || 0;
        
        if (monto > maxMonto) {
            e.preventDefault();
            showToast('El monto excede el saldo pendiente', 'error');
            montoInput.focus();
            return false;
        }
        
        if (monto <= 0) {
            e.preventDefault();
            showToast('El monto debe ser mayor a cero', 'error');
            montoInput.focus();
            return false;
        }
        
        if (!selectMetodo?.value) {
            e.preventDefault();
            showToast('Debe seleccionar un método de pago', 'error');
            selectMetodo.focus();
            return false;
        }
        
        return true;
    }
    
    // ===================================
    // INDEX PAGOS
    // ===================================
    function initIndexPagos() {
        // Funciones globales definidas al final
    }
    
    // ===================================
    // VER PAGO
    // ===================================
    function initVerPago() {
        // Usar las mismas funciones que index
    }
    
    // ===================================
    // CONCEPTOS
    // ===================================
    function initConceptos() {
        const esRecurrente = document.getElementById('esRecurrente');
        const frecuenciaContainer = document.getElementById('frecuenciaContainer');
        const diaVencimientoContainer = document.getElementById('diaVencimientoContainer');
        const selectFrecuencia = document.getElementById('selectFrecuencia');
        const inputDiaVencimiento = document.getElementById('inputDiaVencimiento');
        
        if (esRecurrente) {
            esRecurrente.addEventListener('change', function() {
                const mostrar = this.value == '1';
                
                if (frecuenciaContainer) frecuenciaContainer.style.display = mostrar ? 'block' : 'none';
                if (diaVencimientoContainer) diaVencimientoContainer.style.display = mostrar ? 'block' : 'none';
                
                if (selectFrecuencia) {
                    selectFrecuencia.required = mostrar;
                    if (!mostrar) selectFrecuencia.value = '';
                }
                
                if (inputDiaVencimiento) {
                    inputDiaVencimiento.required = mostrar;
                    if (!mostrar) inputDiaVencimiento.value = '';
                }
            });
        }
        
        const formConcepto = document.getElementById('formConcepto');
        if (formConcepto) {
            formConcepto.addEventListener('submit', function(e) {
                if (esRecurrente?.value == '1') {
                    if (!selectFrecuencia?.value) {
                        e.preventDefault();
                        showToast('Debe seleccionar una frecuencia', 'warning');
                        return false;
                    }
                    
                    const dia = parseInt(inputDiaVencimiento?.value);
                    if (!dia || dia < 1 || dia > 31) {
                        e.preventDefault();
                        showToast('Día de vencimiento debe estar entre 1 y 31', 'warning');
                        return false;
                    }
                }
            });
        }
    }
    
    // ===================================
    // MÉTODOS
    // ===================================
    function initMetodos() {
        const nombreInput = document.querySelector('input[name="nombre"]');
        const codigoInput = document.querySelector('input[name="codigo"]');
        
        if (nombreInput && codigoInput) {
            nombreInput.addEventListener('blur', function() {
                if (!codigoInput.value) {
                    const codigo = this.value.substring(0, 3).toUpperCase();
                    codigoInput.value = codigo;
                }
            });
        }
    }
    
    // ===================================
    // ACCIONES GLOBALES
    // ===================================
    function confirmarPago(id) {
        if (typeof Swal === 'undefined') {
            if (!confirm('¿Confirmar este pago?')) return;
            window.AppUtils.submitForm(getAppUrl(`pagos/confirmar/${id}`));
            return;
        }
        
        Swal.fire({
            title: '¿Confirmar este pago?',
            text: 'Esta acción actualizará el estado de la deuda',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.AppUtils.submitForm(getAppUrl(`pagos/confirmar/${id}`));
            }
        });
    }
    
    function anularPago(id) {
        if (typeof Swal === 'undefined') {
            if (!confirm('¿Anular este pago? Esta acción revertirá el pago en la deuda.')) return;
            window.AppUtils.submitForm(getAppUrl(`pagos/anular/${id}`));
            return;
        }
        
        Swal.fire({
            title: '¿Anular este pago?',
            text: 'Esta acción revertirá el pago en la deuda',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.AppUtils.submitForm(getAppUrl(`pagos/anular/${id}`));
            }
        });
    }
    
    function eliminarConcepto(id) {
        if (typeof Swal === 'undefined') {
            if (!confirm('¿Desactivar este concepto?')) return;
            window.AppUtils.submitForm(getAppUrl(`pagos/conceptos/eliminar/${id}`));
            return;
        }
        
        Swal.fire({
            title: '¿Desactivar este concepto?',
            text: 'Los pagos existentes no se verán afectados',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#B91D22',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.AppUtils.submitForm(getAppUrl(`pagos/conceptos/eliminar/${id}`));
            }
        });
    }
    
    function eliminarMetodo(id) {
        if (typeof Swal === 'undefined') {
            if (!confirm('¿Desactivar este método?')) return;
            window.AppUtils.submitForm(getAppUrl(`pagos/metodos/eliminar/${id}`));
            return;
        }
        
        Swal.fire({
            title: '¿Desactivar este método?',
            text: 'Los pagos existentes no se verán afectados',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#B91D22',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.AppUtils.submitForm(getAppUrl(`pagos/metodos/eliminar/${id}`));
            }
        });
    }
    
    // ===================================
    // API PÚBLICA DEL MÓDULO
    // ===================================
    return {
        init: init,
        volverPaso1: function() {
            document.getElementById('paso2').style.display = 'none';
            document.getElementById('paso3').style.display = 'none';
            document.getElementById('paso1').style.display = 'block';
            document.getElementById('paso1').scrollIntoView({ behavior: 'smooth' });
        },
        volverPaso2: function() {
            document.getElementById('paso3').style.display = 'none';
            document.getElementById('paso2').style.display = 'block';
            document.getElementById('paso2').scrollIntoView({ behavior: 'smooth' });
        },
        irPaso3: function() {
            if (!deudaSeleccionada) {
                showToast('Debe seleccionar una deuda', 'warning');
                return;
            }
            
            document.getElementById('paso2').style.display = 'none';
            document.getElementById('paso3').style.display = 'block';
            document.getElementById('paso3').scrollIntoView({ behavior: 'smooth' });
        },
        confirmarPago: confirmarPago,
        anularPago: anularPago,
        eliminarConcepto: eliminarConcepto,
        eliminarMetodo: eliminarMetodo
    };
})();

// ===================================
// AUTO-INICIALIZACIÓN
// ===================================
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('/pagos')) {
        PagosModule.init();
    }
});

// ===================================
// EXPORTAR FUNCIONES GLOBALES
// ===================================
window.volverPaso1 = function() { PagosModule.volverPaso1(); };
window.volverPaso2 = function() { PagosModule.volverPaso2(); };
window.irPaso3 = function() { PagosModule.irPaso3(); };
window.confirmarPago = function(id) { PagosModule.confirmarPago(id); };
window.anularPago = function(id) { PagosModule.anularPago(id); };
window.eliminarConcepto = function(id) { PagosModule.eliminarConcepto(id); };
window.eliminarMetodo = function(id) { PagosModule.eliminarMetodo(id); };