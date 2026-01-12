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

console.log('🔍 Buscador Público iniciado');
console.log('🌐 URL Base:', window.APP_CONFIG.baseUrl);

// ========================================
// INICIALIZACIÓN
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    initBuscador();
});

function initBuscador() {
    const formBuscar = document.getElementById('formBuscar');
    const inputDni = document.getElementById('dni');
    
    // Validación en tiempo real del DNI
    inputDni.addEventListener('input', function() {
        // Solo permitir números
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Limitar a 8 dígitos
        if (this.value.length > 8) {
            this.value = this.value.slice(0, 8);
        }
        
        // Remover clase de error si existe
        this.classList.remove('error');
    });
    
    // Submit del formulario
    formBuscar.addEventListener('submit', function(e) {
        e.preventDefault();
        buscarColegiado();
    });
    
    // Enter en el input
    inputDni.addEventListener('keypress', function(e) {
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
    const inputDni = document.getElementById('dni');
    const btnBuscar = document.getElementById('btnBuscar');
    const dni = inputDni.value.trim();
    
    // Validar DNI
    if (!dni) {
        mostrarError(inputDni, 'Debe ingresar un número de DNI');
        return;
    }
    
    if (dni.length !== 8) {
        mostrarError(inputDni, 'El DNI debe tener 8 dígitos');
        return;
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
        const response = await fetch(getAppUrl(`buscador-publico/buscar?dni=${dni}`));
        
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
    
    // Remover clase not-found si existe
    resultHeader.classList.remove('not-found');
    
    // Determinar icono y clase de estado
    const esHabilitado = colegiado.estado === 'habilitado';
    const iconoEstado = esHabilitado ? 'fa-check-circle' : 'fa-times-circle';
    const claseEstado = esHabilitado ? 'habilitado' : 'inhabilitado';
    
    // Construir HTML del resultado
    resultContent.innerHTML = `
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
    
    // Mostrar card con animación
    resultCard.style.display = 'block';
    
    // Scroll suave hacia el resultado
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
                Verifique que el DNI sea correcto e intente nuevamente.
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
    const inputDni = document.getElementById('dni');
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
    
    // Limpiar y enfocar input
    inputDni.value = '';
    inputDni.classList.remove('error');
    inputDni.focus();
}

// Agregar animación de slideUp
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
    // Agregar clase de error
    input.classList.add('error');
    
    // Crear tooltip temporal
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
    
    // Agregar tooltip
    const parent = input.parentElement;
    parent.style.position = 'relative';
    parent.appendChild(tooltip);
    
    // Remover tooltip después de 3 segundos
    setTimeout(() => {
        tooltip.style.animation = 'fadeOutUp 0.3s ease';
        setTimeout(() => tooltip.remove(), 300);
    }, 3000);
    
    // Agregar animaciones si no existen
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
    
    // Enfocar input
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

console.log('✅ Buscador listo para usar');