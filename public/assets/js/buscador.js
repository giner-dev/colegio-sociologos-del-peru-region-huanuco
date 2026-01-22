// ========================================
// CONFIGURACIÓN
// ========================================
window.APP_CONFIG = {
    baseUrl: (() => {
        if (typeof window.PHP_BASE_URL !== 'undefined') {
            return window.PHP_BASE_URL.replace(/\/$/, '');
        }
        return window.location.origin;
    })()
};

function getAppUrl(path = '') {
    const cleanPath = path.toString().replace(/^\//, '');
    return window.APP_CONFIG.baseUrl + '/' + cleanPath;
}

// ========================================
// INICIALIZACIÓN
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    initBuscador();
});

function initBuscador() {
    const formBuscar = document.getElementById('formBuscar');
    const tipoBusqueda = document.getElementById('tipoBusqueda');
    const inputDni = document.getElementById('dni');
    const inputNumero = document.getElementById('numero_colegiatura');
    const groupDni = document.getElementById('groupDni');
    const groupNumero = document.getElementById('groupNumero');
    
    // Cambiar tipo de búsqueda
    tipoBusqueda.addEventListener('change', function() {
        if (this.value === 'dni') {
            groupDni.style.display = 'block';
            groupNumero.style.display = 'none';
            inputDni.value = '';
            inputDni.classList.remove('error');
            inputNumero.value = '';
            inputNumero.classList.remove('error');
        } else {
            groupDni.style.display = 'none';
            groupNumero.style.display = 'block';
            inputDni.value = '';
            inputDni.classList.remove('error');
            inputNumero.value = '';
            inputNumero.classList.remove('error');
        }
    });
    
    // Validación en tiempo real del DNI
    inputDni.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 8) {
            this.value = this.value.slice(0, 8);
        }
        this.classList.remove('error');
    });
    
    // Validación en tiempo real del número de colegiatura
    inputNumero.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        this.classList.remove('error');
    });
    
    // Submit del formulario
    formBuscar.addEventListener('submit', function(e) {
        e.preventDefault();
        buscarColegiado();
    });
    
    // Enter en los inputs
    inputDni.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarColegiado();
        }
    });
    
    inputNumero.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarColegiado();
        }
    });
}

// ========================================
// BÚSQUEDA
// ========================================
async function buscarColegiado() {
    const tipoBusqueda = document.getElementById('tipoBusqueda').value;
    const inputDni = document.getElementById('dni');
    const inputNumero = document.getElementById('numero_colegiatura');
    const btnBuscar = document.getElementById('btnBuscar');
    
    let valorBusqueda = '';
    let parametro = '';
    let inputActual = null;
    
    // Determinar qué se está buscando
    if (tipoBusqueda === 'dni') {
        valorBusqueda = inputDni.value.trim();
        parametro = 'dni';
        inputActual = inputDni;
        
        // Validar DNI
        if (!valorBusqueda) {
            mostrarError(inputActual, 'Debe ingresar un número de DNI');
            return;
        }
        
        if (valorBusqueda.length !== 8) {
            mostrarError(inputActual, 'El DNI debe tener 8 dígitos');
            return;
        }
    } else {
        valorBusqueda = inputNumero.value.trim();
        parametro = 'numero_colegiatura';
        inputActual = inputNumero;
        
        // Validar número de colegiatura
        if (!valorBusqueda) {
            mostrarError(inputActual, 'Debe ingresar un número de colegiatura');
            return;
        }
    }
    
    // Mostrar loading
    btnBuscar.disabled = true;
    btnBuscar.classList.add('loading');
    const iconBtn = btnBuscar.querySelector('i');
    const spanBtn = btnBuscar.querySelector('span');
    iconBtn.className = 'fas fa-spinner';
    spanBtn.textContent = 'Buscando...';
    
    try {
        // Realizar petición
        const url = getAppUrl(`buscador-publico/buscar?${parametro}=${encodeURIComponent(valorBusqueda)}`);
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        
        const data = await response.json();
        
        // Mostrar resultado
        if (data.success && data.found) {
            mostrarResultadoEncontrado(data.colegiado);
        } else {
            mostrarResultadoNoEncontrado(data.message || 'No se encontró el colegiado');
        }
        
    } catch (error) {
        console.error('Error en búsqueda:', error);
        mostrarResultadoNoEncontrado('Ocurrió un error al realizar la búsqueda. Intente nuevamente.');
    } finally {
        // Restaurar botón
        btnBuscar.disabled = false;
        btnBuscar.classList.remove('loading');
        iconBtn.className = 'fas fa-search';
        spanBtn.textContent = 'Buscar';
    }
}

// ========================================
// MOSTRAR RESULTADOS
// ========================================
function mostrarResultadoEncontrado(colegiado) {
    const resultCard = document.getElementById('resultCard');
    const resultContent = document.getElementById('resultContent');
    const resultHeader = resultCard.querySelector('.result-header');
    
    resultHeader.classList.remove('not-found');
    
    const headerIcon = resultHeader.querySelector('i');
    const headerTitle = resultHeader.querySelector('h3');
    headerIcon.className = 'fas fa-user-check';
    headerTitle.textContent = 'Resultado de la Búsqueda';
    
    // Determinar icono y clase según el estado
    let iconoEstado = 'fa-times-circle';
    let claseEstado = 'inhabilitado';
    
    if (colegiado.estado === 'habilitado') {
        iconoEstado = 'fa-check-circle';
        claseEstado = 'habilitado';
    } else if (colegiado.estado === 'inactivo_cese') {
        iconoEstado = 'fa-user-slash';
        claseEstado = 'inactivo-cese';
    } else if (colegiado.estado === 'inactivo_traslado') {
        iconoEstado = 'fa-exchange-alt';
        claseEstado = 'inactivo-traslado';
    }
    
    // Construir HTML del resultado
    let htmlResultado = `
        <div class="result-item">
            <div class="result-label">
                <i class="fas fa-id-card"></i>
                Número de Colegiatura
            </div>
            <div class="result-value">${escapeHtml(colegiado.numero_colegiatura)}</div>
        </div>
        
        <div class="result-item">
            <div class="result-label">
                <i class="fas fa-user"></i>
                Apellidos y Nombres
            </div>
            <div class="result-value">${escapeHtml(colegiado.nombre_completo)}</div>
        </div>
        
        <div class="result-item">
            <div class="result-label">
                <i class="fas fa-calendar-alt"></i>
                Fecha de Colegiatura
            </div>
            <div class="result-value">${escapeHtml(colegiado.fecha_colegiatura)}</div>
        </div>
    `;
    
    // Si existe fecha de cese, mostrarla
    if (colegiado.fecha_cese) {
        htmlResultado += `
        <div class="result-item">
            <div class="result-label">
                <i class="fas fa-calendar-times"></i>
                Fecha de Cese
            </div>
            <div class="result-value">${escapeHtml(colegiado.fecha_cese)}</div>
        </div>
        `;
    }

    // Mostrar fecha de traslado y colegio destino si existe
    if (colegiado.fecha_traslado) {
        htmlResultado += `
        <div class="result-item">
            <div class="result-label">
                <i class="fas fa-calendar-times"></i>
                Fecha de Traslado
            </div>
            <div class="result-value">${escapeHtml(colegiado.fecha_traslado)}</div>
        </div>
        `;
    }
    
    // Estado del colegiado
    htmlResultado += `
        <div class="result-item" style="text-align: center; background: transparent; margin-top: 10px;">
            <div class="result-label" style="justify-content: center; margin-bottom: 10px;">
                <i class="fas fa-info-circle"></i>
                Estado del Colegiado
            </div>
            <span class="estado-badge ${claseEstado}">
                <i class="fas ${iconoEstado}"></i>
                ${escapeHtml(colegiado.estado_texto)}
            </span>
        </div>
    `;
    
    resultContent.innerHTML = htmlResultado;
    
    resultCard.style.display = 'block';
    
    setTimeout(() => {
        resultCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 100);
}

function mostrarResultadoNoEncontrado(mensaje) {
    const resultCard = document.getElementById('resultCard');
    const resultContent = document.getElementById('resultContent');
    const resultHeader = resultCard.querySelector('.result-header');
    
    // Agregar clase not-found al header
    resultHeader.classList.add('not-found');
    
    // Cambiar contenido del header
    const headerIcon = resultHeader.querySelector('i');
    const headerTitle = resultHeader.querySelector('h3');
    headerIcon.className = 'fas fa-exclamation-circle';
    headerTitle.textContent = 'No Encontrado';
    
    // Construir HTML de no encontrado
    resultContent.innerHTML = `
        <div class="not-found-content">
            <div class="not-found-icon">
                <i class="fas fa-search-minus"></i>
            </div>
            <h4>No se encontró el colegiado</h4>
            <p>${escapeHtml(mensaje)}</p>
            <p style="margin-top: 15px; font-size: 0.9rem; color: #6c757d;">
                Verifique que los datos sean correctos e intente nuevamente.
            </p>
        </div>
    `;
    
    // Mostrar card con animación
    resultCard.style.display = 'block';
    
    // Scroll suave hacia el resultado
    setTimeout(() => {
        resultCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 100);
}

// ========================================
// NUEVA BÚSQUEDA
// ========================================
function nuevaBusqueda() {
    const resultCard = document.getElementById('resultCard');
    const tipoBusqueda = document.getElementById('tipoBusqueda');
    const inputDni = document.getElementById('dni');
    const inputNumero = document.getElementById('numero_colegiatura');
    const resultHeader = resultCard.querySelector('.result-header');
    
    // Ocultar resultado con animación
    resultCard.style.animation = 'slideUp 0.4s ease-out';
    
    setTimeout(() => {
        resultCard.style.display = 'none';
        resultCard.style.animation = '';
        
        // Restaurar header original
        resultHeader.classList.remove('not-found');
        const headerIcon = resultHeader.querySelector('i');
        const headerTitle = resultHeader.querySelector('h3');
        headerIcon.className = 'fas fa-user-check';
        headerTitle.textContent = 'Resultado de la Búsqueda';
    }, 400);
    
    // Limpiar inputs
    inputDni.value = '';
    inputDni.classList.remove('error');
    inputNumero.value = '';
    inputNumero.classList.remove('error');
    
    // Enfocar el input correspondiente
    if (tipoBusqueda.value === 'dni') {
        inputDni.focus();
    } else {
        inputNumero.focus();
    }
}

// Agregar animación de slideUp si no existe
if (!document.querySelector('#slide-animations')) {
    const style = document.createElement('style');
    style.id = 'slide-animations';
    style.textContent = `
        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-30px);
            }
        }
    `;
    document.head.appendChild(style);
}

// ========================================
// UTILIDADES
// ========================================
function mostrarError(input, mensaje) {
    input.classList.add('error');
    
    const tooltip = document.createElement('div');
    tooltip.className = 'error-tooltip';
    tooltip.textContent = mensaje;
    tooltip.style.cssText = `
        position: absolute;
        top: -40px;
        left: 50%;
        transform: translateX(-50%);
        background: #dc3545;
        color: white;
        padding: 8px 15px;
        border-radius: 8px;
        font-size: 0.875rem;
        white-space: nowrap;
        z-index: 1000;
        animation: fadeInDown 0.3s ease;
    `;
    
    const parent = input.parentElement;
    parent.style.position = 'relative';
    parent.appendChild(tooltip);
    
    setTimeout(() => {
        tooltip.style.animation = 'fadeOutUp 0.3s ease';
        setTimeout(() => tooltip.remove(), 300);
    }, 3000);
    
    if (!document.querySelector('#tooltip-animations')) {
        const style = document.createElement('style');
        style.id = 'tooltip-animations';
        style.textContent = `
            @keyframes fadeInDown {
                from { opacity: 0; transform: translate(-50%, -10px); }
                to { opacity: 1; transform: translate(-50%, 0); }
            }
            @keyframes fadeOutUp {
                from { opacity: 1; transform: translate(-50%, 0); }
                to { opacity: 0; transform: translate(-50%, -10px); }
            }
        `;
        document.head.appendChild(style);
    }
    
    input.focus();
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ========================================
// EXPONER FUNCIÓN GLOBAL
// ========================================
window.nuevaBusqueda = nuevaBusqueda;