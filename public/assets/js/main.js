// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // INICIALIZACIONES
    // ========================================
    initDropdowns();
    initAlerts();
    initSidebarToggle();
    initTooltips();
    
    console.log('Sistema SIAD inicializado correctamente');
});

// ========================================
// SIDEBAR TOGGLE
// ========================================
function initSidebarToggle() {
    const menuToggleBtn = document.querySelector('.menu-toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    const contentWrapper = document.querySelector('.content-wrapper');
    
    if (!menuToggleBtn || !sidebar) return;
    
    // Crear overlay para móvil
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
    
    // Verificar si estamos en móvil
    function isMobile() {
        return window.innerWidth <= 768;
    }
    
    // Inicializar estado según dispositivo
    function initSidebarState() {
        if (isMobile()) {
            // En móvil: oculto por defecto
            sidebar.classList.remove('active');
            sidebar.classList.remove('collapsed');
            overlay.classList.remove('active');
        } else {
            // En escritorio: visible por defecto
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('active');
        }
    }
    
    // Inicializar al cargar
    initSidebarState();
    
    // Toggle del sidebar
    menuToggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        if (isMobile()) {
            // En móvil: toggle active
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        } else {
            // En escritorio: toggle collapsed
            sidebar.classList.toggle('collapsed');
        }
    });
    
    // Cerrar sidebar al hacer clic en overlay (solo móvil)
    overlay.addEventListener('click', function() {
        if (isMobile()) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
    });
    
    // Cerrar sidebar al hacer clic en un enlace (solo móvil)
    const sidebarLinks = sidebar.querySelectorAll('.sidebar-menu a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (isMobile()) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });
    });
    
    // Reinicializar al cambiar tamaño de ventana
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            initSidebarState();
        }, 250);
    });
    
    // Cerrar con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (isMobile()) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        }
    });
}

// ========================================
// DROPDOWN MENU
// ========================================
function initDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        
        if (toggle) {
            // Toggle al hacer clic
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Cerrar otros dropdowns
                dropdowns.forEach(d => {
                    if (d !== dropdown) {
                        d.classList.remove('active');
                    }
                });
                
                // Toggle del dropdown actual
                dropdown.classList.toggle('active');
            });
        }
    });
    
    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
    
    // Cerrar dropdown al presionar ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
}

// ========================================
// ALERTS AUTO-CLOSE
// ========================================
function initAlerts() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    
    alerts.forEach(alert => {
        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            fadeOut(alert);
        }, 5000);
        
        // Botón de cerrar
        const closeBtn = alert.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                fadeOut(alert);
            });
        }
    });
}

// Función para desvanecer y eliminar elementos
function fadeOut(element) {
    if (!element) return;
    
    element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    element.style.opacity = '0';
    element.style.transform = 'translateY(-20px)';
    
    setTimeout(() => {
        element.remove();
    }, 500);
}

// ========================================
// TOOLTIPS
// ========================================
function initTooltips() {
    const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
    
    tooltipTriggers.forEach(trigger => {
        trigger.addEventListener('mouseenter', function() {
            showTooltip(this);
        });
        
        trigger.addEventListener('mouseleave', function() {
            hideTooltip(this);
        });
    });
}

function showTooltip(element) {
    const text = element.getAttribute('data-tooltip');
    if (!text) return;
    
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        z-index: 10000;
        pointer-events: none;
        white-space: nowrap;
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
    tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
    
    element._tooltip = tooltip;
}

function hideTooltip(element) {
    if (element._tooltip) {
        element._tooltip.remove();
        delete element._tooltip;
    }
}

// ========================================
// NOTIFICACIONES TOAST
// ========================================
function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    toast.innerHTML = `
        <i class="fas ${icons[type] || icons.info}"></i>
        <span>${message}</span>
    `;
    
    toast.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        min-width: 250px;
    `;
    
    // Colores según tipo
    const colors = {
        success: 'linear-gradient(135deg, #28a745, #218838)',
        error: 'linear-gradient(135deg, #dc3545, #c82333)',
        warning: 'linear-gradient(135deg, #ffc107, #e0a800)',
        info: 'linear-gradient(135deg, #17a2b8, #138496)'
    };
    
    toast.style.background = colors[type] || colors.info;
    
    document.body.appendChild(toast);
    
    // Auto-eliminar
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Agregar animaciones CSS si no existen
if (!document.querySelector('#toast-animations')) {
    const style = document.createElement('style');
    style.id = 'toast-animations';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// ========================================
// CONFIRMACIÓN DE ACCIONES
// ========================================
function confirmAction(message, callback) {
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        animation: fadeIn 0.2s ease;
    `;
    
    const modal = document.createElement('div');
    modal.style.cssText = `
        background: white;
        padding: 30px;
        border-radius: 12px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        animation: scaleIn 0.2s ease;
    `;
    
    modal.innerHTML = `
        <div style="text-align: center; margin-bottom: 25px;">
            <i class="fas fa-question-circle" style="font-size: 3rem; color: #ffc107;"></i>
        </div>
        <p style="text-align: center; font-size: 1.1rem; margin-bottom: 25px; color: #333;">
            ${message}
        </p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button class="btn-cancel" style="
                padding: 10px 25px;
                border: 2px solid #6c757d;
                background: white;
                color: #6c757d;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s;
            ">Cancelar</button>
            <button class="btn-confirm" style="
                padding: 10px 25px;
                border: none;
                background: linear-gradient(135deg, #B91D22, #8a1519);
                color: white;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s;
            ">Confirmar</button>
        </div>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Estilos de animación
    if (!document.querySelector('#modal-animations')) {
        const style = document.createElement('style');
        style.id = 'modal-animations';
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes scaleIn {
                from { transform: scale(0.8); opacity: 0; }
                to { transform: scale(1); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Event listeners
    modal.querySelector('.btn-cancel').addEventListener('click', () => {
        overlay.remove();
    });
    
    modal.querySelector('.btn-confirm').addEventListener('click', () => {
        overlay.remove();
        if (callback) callback();
    });
    
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.remove();
        }
    });
}

// ========================================
// FORMATO DE NÚMEROS
// ========================================
function formatNumber(number) {
    return new Intl.NumberFormat('es-PE').format(number);
}

function formatMoney(amount) {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN'
    }).format(amount);
}

// ========================================
// EXPORTS PARA USO GLOBAL
// ========================================
window.showToast = showToast;
window.confirmAction = confirmAction;
window.formatNumber = formatNumber;
window.formatMoney = formatMoney;