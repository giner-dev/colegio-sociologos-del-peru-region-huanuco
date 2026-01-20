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

    // NUEVAS VARIABLES PARA LAS FUNCIONALIDADES
    let deudasSeleccionadas = [];
    let tipoSeleccionActual = null;
    let currentDeudaPage = 1;
    let deudasPerPage = 5;
    let searchTermDeudas = '';
    let deudasFiltradas = [];
    
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
            
            if (currentPath.includes('/pagos/registrar-adelantado')) {
                initRegistrarAdelantado();
            } else if (currentPath.includes('/pagos/registrar')) {
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
                    const contentType = response.headers.get("content-type");
                    if (!response.ok || !contentType || !contentType.includes("application/json")) {
                        throw new Error("Respuesta no válida");
                    }
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
        
        // Verificar que el elemento existe
        if (!tbody) {
            console.error('❌ No se encontró el elemento tbody con ID "deudas-body"');
            showToast('Error: No se pudo cargar la tabla de deudas', 'error');
            return;
        }
        
        // Mostrar spinner de carga
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
                // Verificar si la respuesta es JSON
                const contentType = response.headers.get("content-type");
                
                if (!response.ok) {
                    throw new Error(`Error ${response.status}: ${response.statusText}`);
                }
                
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error('El servidor no devolvió datos en formato JSON');
                }
                
                return response.json();
            })
            .then(data => {
                console.log('✅ Datos procesados correctamente:', data);
                
                if (data.success && data.deudas && data.deudas.length > 0) {
                    deudasData = data.deudas;
                    mostrarDeudas(deudasData);
                    
                    // Ocultar mensaje de sin deudas si existe
                    const mensajeSinDeudas = document.getElementById('mensaje-sin-deudas');
                    if (mensajeSinDeudas) {
                        mensajeSinDeudas.style.display = 'none';
                    }
                    
                    // Mostrar tabla
                    const tablaContainer = document.getElementById('tabla-deudas-container');
                    if (tablaContainer) {
                        tablaContainer.style.display = 'block';
                    }
                } else {
                    console.warn('⚠️ El servidor respondió éxito pero sin deudas');
                    
                    // Mostrar mensaje de sin deudas
                    const mensajeSinDeudas = document.getElementById('mensaje-sin-deudas');
                    if (mensajeSinDeudas) {
                        mensajeSinDeudas.style.display = 'block';
                    }
                    
                    // Ocultar tabla
                    const tablaContainer = document.getElementById('tabla-deudas-container');
                    if (tablaContainer) {
                        tablaContainer.style.display = 'none';
                    }
                    
                    tbody.innerHTML = '';
                }
            })
            .catch(error => {
                console.error('❌ Error al cargar deudas:', error);
                
                // Mostrar error en la tabla
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center text-danger py-4">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                <p class="mb-0"><strong>Error al cargar deudas:</strong> ${error.message}</p>
                                <small>Verifica la consola (F12) para más detalles.</small>
                            </td>
                        </tr>
                    `;
                }
                
                if (typeof showToast === 'function') {
                    showToast('No se pudieron cargar las deudas', 'error');
                }
            });
    }
    
    function mostrarDeudas(deudas) {
        console.log(`📋 Mostrando ${deudas.length} deudas`);
        
        // Ordenar por fecha de vencimiento (ascendente)
        deudas.sort((a, b) => {
            return new Date(a.fecha_vencimiento) - new Date(b.fecha_vencimiento);
        });

        deudasData = deudas;
        deudasFiltradas = [...deudas];

        // Resetear selección
        deudasSeleccionadas = [];
        tipoSeleccionActual = null;
        currentDeudaPage = 1;
        searchTermDeudas = ''; // Resetear término de búsqueda

        // Limpiar campo de búsqueda si existe
        const searchInput = document.getElementById('searchDeuda');
        if (searchInput) {
            searchInput.value = '';
        }

        // Renderizar
        renderDeudasPaginated();
        initDeudasPagination();
        initDeudasSearch(); // Inicializar eventos de búsqueda
    }

    function renderDeudasPaginated() {
        const tbody = document.getElementById('deudas-body');
        
        if (deudasFiltradas.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-info-circle me-2"></i>
                        No se encontraron deudas con los filtros aplicados
                    </td>
                </tr>
            `;
            return;
        }

        // Calcular índices para paginación
        const start = (currentDeudaPage - 1) * deudasPerPage;
        const end = start + deudasPerPage;
        const deudasPagina = deudasFiltradas.slice(start, end);

        tbody.innerHTML = '';

        deudasPagina.forEach((deuda, index) => {
            const idxOriginal = deudasData.findIndex(d => d.idDeuda === deuda.idDeuda);
            const vencimiento = new Date(deuda.fecha_vencimiento);
            const hoy = new Date();
            const diasVencidos = Math.ceil((hoy - vencimiento) / (1000 * 3600 * 24));
            const estaVencida = diasVencidos > 0;

            const row = document.createElement('tr');
            row.className = estaVencida ? 'table-danger' : '';

            // Determinar tipo para icono
            const esConceptoDefinido = deuda.concepto_id !== null && deuda.concepto_id !== '';
            const iconoTipo = esConceptoDefinido ? 'fa-tag' : 'fa-edit';
            const colorTipo = esConceptoDefinido ? 'text-primary' : 'text-warning';

            row.innerHTML = `
                <td>
                    <input type="checkbox" name="deuda_checkbox" 
                           data-index="${idxOriginal}" class="form-check-input">
                </td>
                <td>
                    <i class="fas ${iconoTipo} ${colorTipo} me-1" 
                       title="${esConceptoDefinido ? 'Concepto definido' : 'Deuda manual'}"></i>
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
                
            const checkbox = row.querySelector('input[type="checkbox"]');
            checkbox.addEventListener('change', () => seleccionarDeuda(idxOriginal));
        });
    }

    function initDeudasPagination() {
        const totalPages = Math.ceil(deudasFiltradas.length / deudasPerPage);
        const pagination = document.getElementById('deudasPagination');
        const container = document.getElementById('deudasPaginationContainer');
        
        if (!pagination || totalPages <= 1) {
            if (container) container.style.display = 'none';
            return;
        }

        if (container) container.style.display = 'block';
        pagination.innerHTML = '';

        // Botón Anterior
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentDeudaPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a>`;
        if (currentDeudaPage > 1) {
            prevLi.addEventListener('click', (e) => {
                e.preventDefault();
                currentDeudaPage--;
                renderDeudasPaginated();
                initDeudasPagination();
            });
        }
        pagination.appendChild(prevLi);

        // Números de página
        const maxPagesToShow = 5;
        let startPage = Math.max(1, currentDeudaPage - Math.floor(maxPagesToShow / 2));
        let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

        if (endPage - startPage + 1 < maxPagesToShow) {
            startPage = Math.max(1, endPage - maxPagesToShow + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentDeudaPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener('click', (e) => {
                e.preventDefault();
                currentDeudaPage = i;
                renderDeudasPaginated();
                initDeudasPagination();
            });
            pagination.appendChild(li);
        }

        // Botón Siguiente
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentDeudaPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a>`;
        if (currentDeudaPage < totalPages) {
            nextLi.addEventListener('click', (e) => {
                e.preventDefault();
                currentDeudaPage++;
                renderDeudasPaginated();
                initDeudasPagination();
            });
        }
        pagination.appendChild(nextLi);

        // Información de página
        const infoElement = document.getElementById('deudasPageInfo');
        if (infoElement) {
            const startItem = (currentDeudaPage - 1) * deudasPerPage + 1;
            const endItem = Math.min(currentDeudaPage * deudasPerPage, deudasFiltradas.length);
            infoElement.textContent = `Mostrando ${startItem}-${endItem} de ${deudasFiltradas.length} deudas`;
        }
    }

    function initDeudasSearch() {
        // Configurar evento de búsqueda (los elementos YA existen en el HTML)
        const searchDeuda = document.getElementById('searchDeuda');
        const clearSearch = document.getElementById('clearSearchDeuda');

        if (searchDeuda) {
            searchDeuda.addEventListener('input', function() {
                searchTermDeudas = this.value.toLowerCase().trim();
                filtrarDeudas();
            });
        }

        if (clearSearch) {
            clearSearch.addEventListener('click', function() {
                searchTermDeudas = '';
                if (searchDeuda) searchDeuda.value = '';
                filtrarDeudas();
            });
        }
    }

    function filtrarDeudas() {
        if (!searchTermDeudas) {
            deudasFiltradas = [...deudasData];
        } else {
            deudasFiltradas = deudasData.filter(deuda => {
                const concepto = (deuda.concepto_nombre || '').toLowerCase();
                const descripcion = (deuda.descripcion_deuda || '').toLowerCase();
                return concepto.includes(searchTermDeudas) || descripcion.includes(searchTermDeudas);
            });
        }

        currentDeudaPage = 1;
        renderDeudasPaginated();
        initDeudasPagination();
    }
    
    function seleccionarDeuda(index) {
        const deuda = deudasData[index];
        
        // Determinar tipo de deuda
        const esConceptoDefinido = deuda.concepto_id !== null && deuda.concepto_id !== '';
        const tipoDeuda = esConceptoDefinido ? 'concepto' : 'manual';
        
        // Si es la primera selección, establecer el tipo
        if (deudasSeleccionadas.length === 0) {
            tipoSeleccionActual = tipoDeuda;
        }

        // Verificar si se intenta mezclar tipos
        if (tipoSeleccionActual !== null && tipoSeleccionActual !== tipoDeuda) {
            showToast('No puede seleccionar deudas de concepto definido y manuales a la vez', 'warning');
            return;
        }

        // Toggle selección
        const idx = deudasSeleccionadas.findIndex(d => d.idDeuda === deuda.idDeuda);

        if (idx === -1) {
            // Agregar a selección
            deudasSeleccionadas.push(deuda);
        } else {
            // Remover de selección
            deudasSeleccionadas.splice(idx, 1);
        }

        // Si no hay selecciones, resetear tipo
        if (deudasSeleccionadas.length === 0) {
            tipoSeleccionActual = null;
        }

        // Actualizar UI
        actualizarSeleccionDeudasUI();
        actualizarResumenSeleccion();
    }

    function actualizarSeleccionDeudasUI() {
        const checkboxes = document.querySelectorAll('input[name="deuda_checkbox"]');
        
        checkboxes.forEach((checkbox, index) => {
            const deudaId = deudasData[index]?.idDeuda;
            if (deudaId) {
                const estaSeleccionada = deudasSeleccionadas.some(d => d.idDeuda === deudaId);
                checkbox.checked = estaSeleccionada;

                // Marcar fila visualmente
                const row = checkbox.closest('tr');
                if (row) {
                    if (estaSeleccionada) {
                        row.classList.add('table-primary');
                    } else {
                        row.classList.remove('table-primary');
                    }
                }
            }
        });
    }

    function actualizarResumenSeleccion() {
        const container = document.getElementById('deuda-seleccionada-container');
        const btnSiguiente = document.getElementById('btnSiguientePaso3');
        
        if (deudasSeleccionadas.length === 0) {
            container.style.display = 'none';
            btnSiguiente.disabled = true;
            return;
        }

        container.style.display = 'block';
        btnSiguiente.disabled = false;

        // Calcular totales
        let totalSaldo = 0;
        let conceptos = [];
        let fechasVencimiento = [];

        deudasSeleccionadas.forEach(deuda => {
            totalSaldo += parseFloat(deuda.saldo_pendiente || 0);
            conceptos.push(deuda.concepto_nombre || 'Sin concepto');

            // Obtener fecha de vencimiento formateada
            if (deuda.fecha_vencimiento) {
                const fecha = new Date(deuda.fecha_vencimiento);
                fechasVencimiento.push(window.AppUtils.formatDate(fecha));
            }
        });

        // Mostrar resumen
        document.getElementById('deuda-concepto').textContent = 
            conceptos.join(', ');
        document.getElementById('deuda-saldo').textContent = 
            `S/ ${totalSaldo.toFixed(2)}`;

        // Mostrar fecha(s) de vencimiento
        const fechaElement = document.getElementById('deuda-vencimiento');
        if (fechasVencimiento.length > 0) {
            if (deudasSeleccionadas.length === 1) {
                fechaElement.textContent = fechasVencimiento[0];
            } else {
                const fechaMin = fechasVencimiento.sort()[0];
                fechaElement.textContent = `${fechasVencimiento.length} fechas (primera: ${fechaMin})`;
            }
        } else {
            fechaElement.textContent = 'Sin fecha';
        }

        // Mostrar cantidad seleccionada y tipo
        const cantidadElement = document.getElementById('deuda-cantidad');
        if (!cantidadElement) {
            const nuevoElemento = document.createElement('div');
            nuevoElemento.id = 'deuda-cantidad';
            nuevoElemento.className = 'mb-2';
            document.querySelector('#deuda-seleccionada-container .card-body').prepend(nuevoElemento);
        }

        const tipoTexto = tipoSeleccionActual === 'concepto' ? 'Conceptos definidos' : 'Deudas manuales';
        const iconoTipo = tipoSeleccionActual === 'concepto' ? 'fa-tag' : 'fa-edit';

        document.getElementById('deuda-cantidad').innerHTML = 
            `<strong>Cantidad:</strong> ${deudasSeleccionadas.length} deuda(s) 
             <span class="badge bg-${tipoSeleccionActual === 'concepto' ? 'primary' : 'warning'} ms-2">
                <i class="fas ${iconoTipo} me-1"></i>${tipoTexto}
             </span>`;

        // Configurar inputs para el paso 3
        const inputMonto = document.getElementById('inputMonto');
        const maxMontoElement = document.getElementById('max-monto');

        if (inputMonto && maxMontoElement) {
            if (tipoSeleccionActual === 'concepto') {
                // Para conceptos definidos, establecer monto total automáticamente
                inputMonto.value = totalSaldo.toFixed(2);
                inputMonto.readOnly = true;
                maxMontoElement.textContent = `Total fijo: S/ ${totalSaldo.toFixed(2)}`;
                maxMontoElement.dataset.max = totalSaldo;
            } else {
                // Para deudas manuales, permitir pago en partes
                inputMonto.max = totalSaldo;
                inputMonto.readOnly = false;
                maxMontoElement.textContent = `Máximo: S/ ${totalSaldo.toFixed(2)}`;
                maxMontoElement.dataset.max = totalSaldo;
            }
        }
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
        // NO hacer e.preventDefault() aquí, déjalo que el formulario se envíe normalmente
        
        const montoInput = document.getElementById('inputMonto');
        const selectMetodo = document.getElementById('selectMetodo');
        
        const monto = parseFloat(montoInput?.value) || 0;
        
        // Validaciones básicas
        if (monto <= 0) {
            showToast('El monto debe ser mayor a cero', 'error');
            montoInput.focus();
            e.preventDefault(); // Solo aquí prevenir si hay error
            return false;
        }

        if (!selectMetodo?.value) {
            showToast('Debe seleccionar un método de pago', 'error');
            selectMetodo.focus();
            e.preventDefault(); // Solo aquí prevenir si hay error
            return false;
        }

        // Validar según tipo de deuda
        const totalSaldo = deudasSeleccionadas.reduce((sum, d) => 
            sum + parseFloat(d.saldo_pendiente || 0), 0);

        if (tipoSeleccionActual === 'concepto') {
            // Para conceptos definidos, debe pagarse el total exacto
            if (Math.abs(monto - totalSaldo) > 0.01) {
                showToast(`Debe pagar el monto exacto: S/ ${totalSaldo.toFixed(2)}`, 'error');
                montoInput.focus();
                e.preventDefault(); // Solo aquí prevenir si hay error
                return false;
            }
        } else {
            // Para deudas manuales, validar límite máximo
            if (monto > totalSaldo) {
                showToast(`El monto excede el saldo pendiente (S/ ${totalSaldo.toFixed(2)})`, 'error');
                montoInput.focus();
                e.preventDefault(); // Solo aquí prevenir si hay error
                return false;
            }
        }

        // Log para debugging
        console.log('Formulario validado correctamente');
        console.log('Deudas a pagar:', deudasSeleccionadas.map(d => d.idDeuda));

        // Devolver true para permitir el envío normal del formulario
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
            // Resetear selecciones
            deudasSeleccionadas = [];
            tipoSeleccionActual = null;
            currentDeudaPage = 1;
            searchTermDeudas = '';

            // Resetear inputs
            const inputMonto = document.getElementById('inputMonto');
            if (inputMonto) {
                inputMonto.readOnly = false;
                inputMonto.value = '';
            }

            const inputDeudaId = document.getElementById('inputDeudaId');
            if (inputDeudaId) {
                inputDeudaId.disabled = false;
                inputDeudaId.value = '';
            }

            const inputDeudasIds = document.getElementById('inputDeudasIds');
            if (inputDeudasIds) {
                inputDeudasIds.remove();
            }

            // Ocultar/mostrar pasos
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
            if (deudasSeleccionadas.length === 0) {
                showToast('Debe seleccionar al menos una deuda', 'warning');
                return;
            }
        
            // Configurar formulario para manejar múltiples deudas
            const form = document.getElementById('formRegistrarPago');
            if (!form) {
                console.error('Formulario no encontrado');
                showToast('Error: Formulario no encontrado', 'error');
                return;
            }
        
            // 1. Establecer colegiado ID
            const inputColegiadoId = document.getElementById('inputColegiadoId');
            if (inputColegiadoId && colegiadoSeleccionado) {
                inputColegiadoId.value = colegiadoSeleccionado.id;
            }
        
            // 2. Manejar IDs de deudas
            // Eliminar inputs de deudas anteriores si existen
            const existingDeudaInputs = form.querySelectorAll('input[name^="deudas"]');
            existingDeudaInputs.forEach(input => input.remove());

            // Eliminar también otros campos de deuda
            const existingDeudaId = form.querySelector('input[name="deuda_id"]');
            if (existingDeudaId) {
                existingDeudaId.remove();
            }
        
            if (deudasSeleccionadas.length === 1) {
                // Caso simple: una sola deuda
                const inputDeudaId = document.createElement('input');
                inputDeudaId.type = 'hidden';
                inputDeudaId.name = 'deuda_id';
                inputDeudaId.value = deudasSeleccionadas[0].idDeuda;
                form.appendChild(inputDeudaId);

                console.log('Deuda única seleccionada:', deudasSeleccionadas[0].idDeuda);
            } else {
                // Caso múltiple: varias deudas
                deudasSeleccionadas.forEach((deuda, index) => {
                    const inputDeuda = document.createElement('input');
                    inputDeuda.type = 'hidden';
                    inputDeuda.name = 'deudas_ids[]'; // Array para PHP
                    inputDeuda.value = deuda.idDeuda;
                    form.appendChild(inputDeuda);

                    console.log(`Deuda ${index + 1} añadida:`, deuda.idDeuda);
                });

                // Agregar campo para indicar que son múltiples
                const inputMultiple = document.createElement('input');
                inputMultiple.type = 'hidden';
                inputMultiple.name = 'es_pago_multiple';
                inputMultiple.value = '1';
                form.appendChild(inputMultiple);

                // Si es pago de conceptos definidos (pago completo)
                if (tipoSeleccionActual === 'concepto') {
                    const inputPagoCompleto = document.createElement('input');
                    inputPagoCompleto.type = 'hidden';
                    inputPagoCompleto.name = 'pago_completo';
                    inputPagoCompleto.value = '1';
                    form.appendChild(inputPagoCompleto);
                }
            }
        
            // 3. Configurar monto según tipo
            const totalSaldo = deudasSeleccionadas.reduce((sum, d) => 
                sum + parseFloat(d.saldo_pendiente || 0), 0);

            const inputMonto = document.getElementById('inputMonto');
            const maxMontoElement = document.getElementById('max-monto');

            if (inputMonto && maxMontoElement) {
                if (tipoSeleccionActual === 'concepto') {
                    // Para conceptos definidos: monto fijo
                    inputMonto.value = totalSaldo.toFixed(2);
                    inputMonto.readOnly = true;
                    maxMontoElement.textContent = `Total fijo: S/ ${totalSaldo.toFixed(2)}`;
                } else {
                    // Para deudas manuales: permitir pago parcial
                    inputMonto.max = totalSaldo;
                    inputMonto.readOnly = false;
                    maxMontoElement.textContent = `Máximo: S/ ${totalSaldo.toFixed(2)}`;

                    // Valor por defecto: el total completo
                    inputMonto.value = totalSaldo.toFixed(2);
                }

                // Actualizar total visual
                actualizarTotal();
            }
        
            // 4. Mostrar paso 3
            document.getElementById('paso2').style.display = 'none';
            document.getElementById('paso3').style.display = 'block';
            document.getElementById('paso3').scrollIntoView({ behavior: 'smooth' });
        
            // 5. Log para debugging
            console.log('=== PASO 3 CONFIGURADO ===');
            console.log('Deudas seleccionadas:', deudasSeleccionadas.length);
            console.log('Tipo:', tipoSeleccionActual);
            console.log('Total saldo:', totalSaldo);
            console.log('IDs de deudas:', deudasSeleccionadas.map(d => d.idDeuda));
            console.log('Inputs creados:', form.querySelectorAll('input[name^="deudas"]').length);
        },
        confirmarPago: confirmarPago,
        anularPago: anularPago,
        eliminarConcepto: eliminarConcepto,
        eliminarMetodo: eliminarMetodo
    };
})();


function initRegistrarAdelantado() {
    console.log('Inicializando registro de pago adelantado...');
    
    // Ya no se necesita búsqueda en JS porque se hace en servidor
    // Solo se mantiene la selección de colegiado
    document.querySelectorAll('.btn-seleccionar-colegiado').forEach(btn => {
        btn.addEventListener('click', function() {
            const colegiadoId = this.dataset.colegiadoId;
            const nombre = this.dataset.nombre;
            const numero = this.dataset.numero;
            seleccionarColegiadoAdelantado(colegiadoId, nombre, numero);
        });
    });
    
    const selectProgramacion = document.getElementById('selectProgramacion');
    const inputMeses = document.getElementById('inputMesesAdelantado');
    
    if (selectProgramacion && inputMeses) {
        selectProgramacion.addEventListener('change', calcularMontoAdelantado);
        inputMeses.addEventListener('input', calcularMontoAdelantado);
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
}

function seleccionarColegiadoAdelantado(colegiadoId, nombre, numero) {
    console.log(`👤 Colegiado seleccionado para pago adelantado: ${numero} - ${nombre}`);
    
    document.getElementById('colegiadoSeleccionadoInfo').innerHTML = 
        `<strong>${numero}</strong> - ${nombre}`;
    
    document.getElementById('inputColegiadoId').value = colegiadoId;
    
    document.getElementById('tablaColegiados').style.display = 'none';
    document.getElementById('paso2').style.display = 'block';
    
    cargarProgramaciones(colegiadoId);
    
    document.getElementById('paso2').scrollIntoView({ behavior: 'smooth' });
}

function cargarProgramaciones(colegiadoId) {
    const select = document.getElementById('selectProgramacion');
    const url = getAppUrl(`pagos/api-programaciones/${colegiadoId}`);
    
    console.log('🔄 Cargando programaciones para colegiado', colegiadoId);
    
    select.innerHTML = '<option value="">Cargando...</option>';
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log('✅ Programaciones cargadas:', data);
            
            if (data.success && data.programaciones && data.programaciones.length > 0) {
                select.innerHTML = '<option value="">Seleccione un concepto...</option>';
                
                data.programaciones.forEach(prog => {
                    const option = document.createElement('option');
                    option.value = prog.idProgramacion;
                    option.textContent = `${prog.concepto_nombre} (${prog.frecuencia}) - S/ ${parseFloat(prog.monto).toFixed(2)}`;
                    option.dataset.monto = prog.monto;
                    option.dataset.frecuencia = prog.frecuencia;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">No hay programaciones activas</option>';
                showToast('Este colegiado no tiene programaciones de pago activas', 'warning');
            }
        })
        .catch(error => {
            console.error('❌ Error al cargar programaciones:', error);
            select.innerHTML = '<option value="">Error al cargar</option>';
            showToast('Error al cargar programaciones', 'error');
        });
}

function calcularMontoAdelantado() {
    const select = document.getElementById('selectProgramacion');
    const inputMeses = document.getElementById('inputMesesAdelantado');
    const inputMonto = document.getElementById('inputMonto');
    const container = document.getElementById('infoCalculoContainer');
    
    if (!select.value || !inputMeses.value) {
        container.style.display = 'none';
        return;
    }
    
    const selectedOption = select.options[select.selectedIndex];
    const montoPorPeriodo = parseFloat(selectedOption.dataset.monto || 0);
    const meses = parseInt(inputMeses.value || 0);
    
    const montoTotal = montoPorPeriodo * meses;
    
    document.getElementById('montoPorPeriodo').textContent = montoPorPeriodo.toFixed(2);
    document.getElementById('cantidadPeriodos').textContent = meses;
    document.getElementById('montoTotalCalculado').textContent = montoTotal.toFixed(2);
    
    if (inputMonto) {
        inputMonto.value = montoTotal.toFixed(2);
        inputMonto.min = montoTotal.toFixed(2);
    }
    
    container.style.display = 'block';
}

function validarMonto(input) {
    const monto = parseFloat(input.value) || 0;
    const maxMonto = parseFloat(document.getElementById('max-monto')?.dataset?.max) || 0;
    const tipoInfo = document.getElementById('tipo-monto-info');
    
    if (tipoSeleccionActual === 'concepto') {
        // Para conceptos definidos: mostrar mensaje específico
        if (tipoInfo) {
            tipoInfo.textContent = 'Pago completo de conceptos definidos. ';
            tipoInfo.className = 'text-info';
        }
        
        if (Math.abs(monto - maxMonto) > 0.01) {
            showToast(`Para conceptos definidos debe pagar el monto exacto: S/ ${maxMonto.toFixed(2)}`, 'warning');
            input.value = maxMonto.toFixed(2);
        }
    } else {
        // Para deudas manuales: validar límite
        if (tipoInfo) {
            tipoInfo.textContent = 'Puede pagar parcialmente. ';
            tipoInfo.className = 'text-warning';
        }
        
        if (monto > maxMonto && maxMonto > 0) {
            showToast(`El monto no puede exceder S/ ${maxMonto.toFixed(2)}`, 'warning');
            input.value = maxMonto.toFixed(2);
        }
    }
    
    actualizarTotal();
}

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