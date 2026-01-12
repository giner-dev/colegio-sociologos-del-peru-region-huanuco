// ========================================
// CONFIGURACIÓN GLOBAL Y BASE URL
// ========================================

// Configurar BASE_URL una sola vez al inicio
window.APP_CONFIG = {
    baseUrl: (() => {
        // Usar la variable inyectada desde layouts/main.php
        if (typeof window.PHP_BASE_URL !== 'undefined') {
            return window.PHP_BASE_URL.replace(/\/$/, '');
        }
        
        // Fallback extremo solo por si acaso
        return window.location.origin;
    })(),
    
    sessionCheckInterval: 60000, 
    sessionWarningTime: 5
};

// Función global para obtener URL completa
window.getAppUrl = function(path = '') {
    // Limpiamos el path de barras iniciales y concatenamos
    const cleanPath = path.toString().replace(/^\//, '');
    return window.APP_CONFIG.baseUrl + '/' + cleanPath;
};

console.log('🌐 URL Base del Sistema:', window.APP_CONFIG.baseUrl);

// ========================================
// INICIALIZACIONES GLOBALES
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    initDropdowns();
    initAlerts();
    initSidebarToggle();
    initTooltips();
    initSessionMonitor();
    
    console.log('✅ Sistema SIAD inicializado correctamente');
});

// ========================================
// SIDEBAR TOGGLE
// ========================================
function initSidebarToggle() {
    const menuToggleBtn = document.querySelector('.menu-toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    
    if (!menuToggleBtn || !sidebar) return;
    
    // Crear overlay para móvil
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
    
    function isMobile() {
        return window.innerWidth <= 768;
    }
    
    function initSidebarState() {
        if (isMobile()) {
            sidebar.classList.remove('active');
            sidebar.classList.remove('collapsed');
            overlay.classList.remove('active');
        } else {
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('active');
        }
    }
    
    initSidebarState();
    
    menuToggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        if (isMobile()) {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        } else {
            sidebar.classList.toggle('collapsed');
        }
    });
    
    overlay.addEventListener('click', function() {
        if (isMobile()) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
    });
    
    const sidebarLinks = sidebar.querySelectorAll('.sidebar-menu a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (isMobile()) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });
    });
    
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(initSidebarState, 250);
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isMobile()) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
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
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                
                dropdowns.forEach(d => {
                    if (d !== dropdown) {
                        d.classList.remove('active');
                    }
                });
                
                dropdown.classList.toggle('active');
            });
        }
    });
    
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
    
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
        setTimeout(() => {
            fadeOut(alert);
        }, 5000);
        
        const closeBtn = alert.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                fadeOut(alert);
            });
        }
    });
}

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
// MONITOR DE SESIÓN
// ========================================
function initSessionMonitor() {
    // Verificar tiempo de sesión cada minuto
    setInterval(checkSessionTime, window.APP_CONFIG.sessionCheckInterval);
    
    // Verificar inmediatamente
    checkSessionTime();
}

function checkSessionTime() {
    fetch(getAppUrl('session/time-left'))
        .then(response => {
            // Escudo: Verificar que la respuesta sea JSON
            const contentType = response.headers.get("content-type");
            if (!response.ok || !contentType || !contentType.includes("application/json")) {
                throw new Error("Sesión no disponible");
            }
            return response.json();
        })
        .then(data => {
            const timeLeft = data.timeLeft;
            
            if (timeLeft > 0 && timeLeft <= window.APP_CONFIG.sessionWarningTime) {
                showSessionWarning(timeLeft);
            }
            
            if (timeLeft <= 0) {
                window.location.href = getAppUrl('login?expired=1');
            }
        })
        .catch(error => {
            // Silencioso para no molestar al usuario
            console.warn('Monitor de sesión:', error.message);
        });
}

function showSessionWarning(minutesLeft) {
    // Evitar mostrar múltiples warnings
    if (document.getElementById('session-warning-toast')) {
        return;
    }
    
    const toast = document.createElement('div');
    toast.id = 'session-warning-toast';
    toast.className = 'toast toast-warning';
    toast.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        padding: 15px 20px;
        background: linear-gradient(135deg, #ffc107, #e0a800);
        color: white;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        min-width: 300px;
    `;
    
    toast.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-clock" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Sesión por expirar</strong>
                <p style="margin: 5px 0 0 0; font-size: 0.9rem;">
                    Tu sesión expirará en ${minutesLeft} minuto${minutesLeft !== 1 ? 's' : ''}
                </p>
            </div>
        </div>
        <button onclick="renewSessionNow()" style="
            background: white;
            color: #e0a800;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        ">
            Renovar Sesión
        </button>
    `;
    
    document.body.appendChild(toast);
}

window.renewSessionNow = function() {
    fetch(getAppUrl('session/renew'), { method: 'POST' })
        .then(() => {
            const toast = document.getElementById('session-warning-toast');
            if (toast) toast.remove();
            
            showToast('Sesión renovada exitosamente', 'success');
        })
        .catch(error => {
            console.error('Error al renovar sesión:', error);
        });
};

// ========================================
// NOTIFICACIONES TOAST
// ========================================
window.showToast = function(message, type = 'info', duration = 3000) {
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
    
    const colors = {
        success: 'linear-gradient(135deg, #28a745, #218838)',
        error: 'linear-gradient(135deg, #dc3545, #c82333)',
        warning: 'linear-gradient(135deg, #ffc107, #e0a800)',
        info: 'linear-gradient(135deg, #17a2b8, #138496)'
    };
    
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
        background: ${colors[type] || colors.info};
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
};

// Agregar animaciones CSS si no existen
if (!document.querySelector('#toast-animations')) {
    const style = document.createElement('style');
    style.id = 'toast-animations';
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
}

// ========================================
// CONFIRMACIÓN DE ACCIONES
// ========================================
window.confirmAction = function(message, callback) {
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
};

// ========================================
// FORMATO DE NÚMEROS
// ========================================
window.formatNumber = function(number) {
    return new Intl.NumberFormat('es-PE').format(number);
};

window.formatMoney = function(amount) {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN'
    }).format(amount);
};

// ========================================
// UTILIDADES COMUNES PARA MÓDULOS
// ========================================
window.AppUtils = {
    // Formatear fecha DD/MM/YYYY
    formatDate: function(date) {
        if (!date) return '-';
        
        const d = date instanceof Date ? date : new Date(date);
        if (isNaN(d.getTime())) return '-';
        
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    },
    
    // Escapar HTML
    escapeHtml: function(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },
    
    // Submit form por POST
    submitForm: function(action, data = {}) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        
        for (const key in data) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = data[key];
            form.appendChild(input);
        }
        
        document.body.appendChild(form);
        form.submit();
    },
    
    // Mostrar loading
    showLoading: function(message = 'Cargando...') {
        const loading = document.createElement('div');
        loading.id = 'app-loading';
        loading.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10001;
        `;
        loading.innerHTML = `
            <div style="background: white; padding: 30px; border-radius: 10px; text-align: center;">
                <i class="fas fa-spinner fa-spin fa-3x" style="color: #B91D22;"></i>
                <p style="margin-top: 15px; font-size: 1.1rem; color: #333;">${message}</p>
            </div>
        `;
        document.body.appendChild(loading);
    },
    
    // Ocultar loading
    hideLoading: function() {
        const loading = document.getElementById('app-loading');
        if (loading) loading.remove();
    }
};

console.log('✅ Utilidades globales cargadas');