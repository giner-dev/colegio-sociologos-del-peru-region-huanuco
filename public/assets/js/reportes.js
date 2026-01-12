// ========================================
// MÓDULO DE REPORTES - MEJORADO
// ========================================
window.ReportesModule = (function() {
    'use strict';
    
    let initialized = false;
    let chartInstances = {};
    
    function init() {
        if (initialized) return;
        
        const currentPath = window.location.pathname;
        
        if (currentPath.includes('/reportes')) {
            console.log('🔄 Inicializando módulo Reportes...');
            
            // Inicializar filtros de fecha
            initFechaFiltros();
            
            // Inicializar búsqueda en tablas
            initBusquedaTabla();
            
            // Inicializar gráficos si estamos en reportes financieros
            if (currentPath.includes('/ingresos') || 
                currentPath.includes('/egresos') || 
                currentPath.includes('/balance')) {
                // Esperar a que Chart.js esté disponible
                if (typeof Chart !== 'undefined') {
                    setTimeout(initGraficos, 500);
                } else {
                    console.warn('⚠️ Chart.js no está cargado');
                }
            }
            
            // Inicializar tooltips si bootstrap está disponible
            if (typeof bootstrap !== 'undefined') {
                initTooltips();
            }
            
            initialized = true;
            console.log('✅ Módulo Reportes inicializado');
        }
    }
    
    // ========================================
    // FILTROS DE FECHA
    // ========================================
    function initFechaFiltros() {
        const formFiltros = document.getElementById('formFiltros');
        
        if (formFiltros) {
            const fechaInicio = formFiltros.querySelector('input[name="fecha_inicio"]');
            const fechaFin = formFiltros.querySelector('input[name="fecha_fin"]');
            
            if (fechaInicio && fechaFin) {
                // Validar que fecha inicio no sea mayor a fecha fin
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
    }
    
    // ========================================
    // BÚSQUEDA EN TABLAS
    // ========================================
    function initBusquedaTabla() {
        const tablas = ['tablaIngresos', 'tablaEgresos', 'tablaHabilitados', 'tablaInhabilitados', 'tablaMorosos'];
        
        tablas.forEach(idTabla => {
            const tabla = document.getElementById(idTabla);
            
            if (tabla) {
                const cardBody = tabla.closest('.card-body');
                const printableArea = cardBody.querySelector('#printableArea');
                
                if (cardBody && printableArea) {
                    // Crear input de búsqueda
                    const searchDiv = document.createElement('div');
                    searchDiv.className = 'mb-3 no-print';
                    searchDiv.innerHTML = `
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   placeholder="Buscar en la tabla..."
                                   id="search_${idTabla}">
                        </div>
                    `;
                    
                    cardBody.insertBefore(searchDiv, printableArea);
                    
                    // Funcionalidad de búsqueda
                    const searchInput = document.getElementById(`search_${idTabla}`);
                    let searchTimeout;
                    
                    searchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        
                        searchTimeout = setTimeout(() => {
                            const searchTerm = this.value.toLowerCase();
                            const rows = tabla.querySelectorAll('tbody tr');
                            let visibleCount = 0;
                            
                            rows.forEach(row => {
                                const text = row.textContent.toLowerCase();
                                const shouldShow = text.includes(searchTerm);
                                
                                row.style.display = shouldShow ? '' : 'none';
                                if (shouldShow) visibleCount++;
                            });
                            
                            // Actualizar contador si existe
                            updateResultCounter(idTabla, visibleCount);
                        }, 300);
                    });
                }
            }
        });
    }
    
    function updateResultCounter(idTabla, count) {
        const cardHeader = document.querySelector(`#${idTabla}`).closest('.card').querySelector('.card-header');
        
        if (cardHeader) {
            const counterText = cardHeader.querySelector('.result-counter') || cardHeader.querySelector('strong');
            
            if (counterText) {
                counterText.textContent = count;
            }
        }
    }
    
    // ========================================
    // INICIALIZACIÓN DE GRÁFICOS
    // ========================================
    function initGraficos() {
        console.log('📊 Inicializando gráficos...');
        
        // Configuración global de Chart.js
        if (typeof Chart !== 'undefined') {
            Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
            Chart.defaults.responsive = true;
            Chart.defaults.maintainAspectRatio = true;
        }
        
        // Gráfico de Método de Pago (Ingresos)
        initChartMetodoPago();
        
        // Gráfico de Concepto (Ingresos)
        initChartConcepto();
        
        // Gráfico de Tipo de Gasto (Egresos)
        initChartTipoGasto();
        
        // Gráfico de Balance
        initChartBalance();
    }
    
    function initChartMetodoPago() {
        const canvas = document.getElementById('chartMetodoPago');
        
        if (!canvas) return;
        
        // Obtener datos del DOM (pasados desde PHP)
        const datosMetodoPago = window.datosMetodoPago || [];
        
        if (datosMetodoPago.length === 0) {
            showEmptyChartState(canvas, 'No hay datos de métodos de pago');
            return;
        }
        
        const ctx = canvas.getContext('2d');
        
        chartInstances.metodoPago = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: datosMetodoPago.map(d => d.metodo),
                datasets: [{
                    data: datosMetodoPago.map(d => parseFloat(d.total)),
                    backgroundColor: [
                        '#28a745',
                        '#17a2b8',
                        '#ffc107',
                        '#dc3545',
                        '#6f42c1',
                        '#fd7e14'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = formatMoney(context.parsed);
                                return label + ': ' + value;
                            }
                        }
                    }
                }
            }
        });
    }
    
    function initChartConcepto() {
        const canvas = document.getElementById('chartConcepto');
        
        if (!canvas) return;
        
        const datosConcepto = window.datosConcepto || [];
        
        if (datosConcepto.length === 0) {
            showEmptyChartState(canvas, 'No hay datos de conceptos');
            return;
        }
        
        const ctx = canvas.getContext('2d');
        
        chartInstances.concepto = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: datosConcepto.map(d => d.concepto),
                datasets: [{
                    label: 'Monto (S/)',
                    data: datosConcepto.map(d => parseFloat(d.total)),
                    backgroundColor: '#B91D22',
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Monto: ' + formatMoney(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
    
    function initChartTipoGasto() {
        const canvas = document.getElementById('chartTipoGasto');
        
        if (!canvas) return;
        
        const datosTipoGasto = window.datosTipoGasto || [];
        
        if (datosTipoGasto.length === 0) {
            showEmptyChartState(canvas, 'No hay datos de tipos de gasto');
            return;
        }
        
        const ctx = canvas.getContext('2d');
        
        chartInstances.tipoGasto = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: datosTipoGasto.map(d => d.tipo),
                datasets: [{
                    label: 'Monto (S/)',
                    data: datosTipoGasto.map(d => parseFloat(d.total)),
                    backgroundColor: '#dc3545',
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Monto: ' + formatMoney(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
    
    function initChartBalance() {
        const canvas = document.getElementById('chartBalance');
        
        if (!canvas) return;
        
        const totalIngresos = window.totalIngresos || 0;
        const totalEgresos = window.totalEgresos || 0;
        
        if (totalIngresos === 0 && totalEgresos === 0) {
            showEmptyChartState(canvas, 'No hay datos de balance');
            return;
        }
        
        const ctx = canvas.getContext('2d');
        
        chartInstances.balance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Ingresos', 'Egresos'],
                datasets: [{
                    label: 'Monto (S/)',
                    data: [parseFloat(totalIngresos), parseFloat(totalEgresos)],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Monto: ' + formatMoney(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
    
    function showEmptyChartState(canvas, message) {
        const container = canvas.parentElement;
        const emptyState = document.createElement('div');
        emptyState.className = 'chart-empty-state';
        emptyState.innerHTML = `
            <i class="fas fa-chart-bar"></i>
            <p>${message}</p>
        `;
        
        canvas.style.display = 'none';
        container.appendChild(emptyState);
    }
    
    // ========================================
    // TOOLTIPS
    // ========================================
    function initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // ========================================
    // DESTRUIR GRÁFICOS (cleanup)
    // ========================================
    function destroyCharts() {
        Object.keys(chartInstances).forEach(key => {
            if (chartInstances[key]) {
                chartInstances[key].destroy();
                delete chartInstances[key];
            }
        });
    }
    
    return {
        init: init,
        destroyCharts: destroyCharts
    };
})();

// ========================================
// FUNCIONES GLOBALES PARA REPORTES
// ========================================

window.exportarExcel = function() {
    const fechaInicio = document.querySelector('input[name="fecha_inicio"]')?.value;
    const fechaFin = document.querySelector('input[name="fecha_fin"]')?.value;
    const currentPath = window.location.pathname;
    
    let tipo = '';
    if (currentPath.includes('/ingresos')) tipo = 'ingresos';
    else if (currentPath.includes('/egresos')) tipo = 'egresos';
    else if (currentPath.includes('/balance')) tipo = 'balance';
    else if (currentPath.includes('/habilitados')) tipo = 'habilitados';
    else if (currentPath.includes('/inhabilitados')) tipo = 'inhabilitados';
    else if (currentPath.includes('/morosos')) tipo = 'morosos';
    
    if (!tipo) {
        showToast('No se pudo determinar el tipo de reporte', 'error');
        return;
    }
    
    // Mostrar loading
    showLoadingExport();
    
    let url = `reportes/exportar-excel?tipo=${tipo}`;
    
    if (fechaInicio && fechaFin) {
        url += `&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    }
    
    // Crear iframe oculto para descargar
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = getAppUrl(url);
    document.body.appendChild(iframe);
    
    // Remover iframe y loading después de 3 segundos
    setTimeout(() => {
        document.body.removeChild(iframe);
        hideLoadingExport();
        showToast('Reporte exportado correctamente', 'success');
    }, 3000);
};

window.imprimirReporte = function() {
    // Preparar para impresión
    prepareForPrint();
    
    // Imprimir
    window.print();
    
    // Restaurar después de imprimir
    setTimeout(restoreAfterPrint, 500);
};

function prepareForPrint() {
    // Agregar clase de impresión al body
    document.body.classList.add('printing');
    
    // Ocultar gráficos vacíos
    document.querySelectorAll('.chart-empty-state').forEach(el => {
        el.style.display = 'none';
    });
    
    // Ajustar altura de canvas para impresión
    document.querySelectorAll('canvas').forEach(canvas => {
        canvas.style.maxHeight = '400px';
    });
}

function restoreAfterPrint() {
    document.body.classList.remove('printing');
    
    document.querySelectorAll('.chart-empty-state').forEach(el => {
        el.style.display = '';
    });
}

function showLoadingExport() {
    const overlay = document.createElement('div');
    overlay.id = 'exportLoadingOverlay';
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-file-excel fa-3x text-success mb-3"></i>
            <h5>Generando reporte Excel...</h5>
            <p class="text-muted">Por favor espere</p>
        </div>
    `;
    document.body.appendChild(overlay);
}

function hideLoadingExport() {
    const overlay = document.getElementById('exportLoadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

// ========================================
// INICIALIZACIÓN AUTOMÁTICA
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('/reportes')) {
        ReportesModule.init();
    }
});

// Limpiar gráficos al cambiar de página
window.addEventListener('beforeunload', function() {
    if (window.ReportesModule) {
        window.ReportesModule.destroyCharts();
    }
});

console.log('✅ Módulo de Reportes cargado');