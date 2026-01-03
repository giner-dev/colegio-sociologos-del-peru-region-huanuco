document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // ANIMACIONES DE TARJETAS
    // ========================================
    animateStatCards();
    
    // ========================================
    // CONTADOR ANIMADO
    // ========================================
    animateCounters();
    
    // ========================================
    // HOVER EFFECTS
    // ========================================
    initCardHoverEffects();
    
    // ========================================
    // ACTUALIZACIÓN DE FECHA
    // ========================================
    updateDateTime();
    
    console.log('Dashboard inicializado correctamente');
});

// ========================================
// ANIMACIÓN DE TARJETAS AL CARGAR
// ========================================
function animateStatCards() {
    const statCards = document.querySelectorAll('.stat-card');
    
    statCards.forEach((card, index) => {
        // Iniciar ocultas
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        // Animar con delay
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Animar tarjetas de contenido
    const contentCards = document.querySelectorAll('.content-card');
    contentCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, (statCards.length * 100) + (index * 100));
    });
}

// ========================================
// CONTADOR ANIMADO DE NÚMEROS
// ========================================
function animateCounters() {
    const counters = document.querySelectorAll('.stat-card .number');
    
    counters.forEach(counter => {
        const target = parseFloat(counter.textContent.replace(/,/g, ''));
        
        if (isNaN(target)) return;
        
        const duration = 2000; // 2 segundos
        const step = target / (duration / 16); // 60fps
        let current = 0;
        
        counter.textContent = '0';
        
        const timer = setInterval(() => {
            current += step;
            
            if (current >= target) {
                counter.textContent = formatDisplayNumber(target);
                clearInterval(timer);
            } else {
                counter.textContent = formatDisplayNumber(Math.floor(current));
            }
        }, 16);
    });
}

function formatDisplayNumber(num) {
    return new Intl.NumberFormat('es-PE').format(num);
}

// ========================================
// EFECTOS HOVER EN TARJETAS
// ========================================
function initCardHoverEffects() {
    const cards = document.querySelectorAll('.stat-card, .content-card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
        });
        
        // Efecto de movimiento parallax suave
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const deltaX = (x - centerX) / centerX;
            const deltaY = (y - centerY) / centerY;
            
            const tiltX = deltaY * 5;
            const tiltY = deltaX * -5;
            
            if (this.classList.contains('stat-card')) {
                this.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) translateY(-8px)`;
            }
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
}

// ========================================
// ACTUALIZAR FECHA Y HORA
// ========================================
function updateDateTime() {
    const dateElement = document.querySelector('.page-header-date');
    
    if (dateElement) {
        setInterval(() => {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            dateElement.innerHTML = `<i class="fas fa-calendar me-1"></i> ${now.toLocaleDateString('es-PE', options)}`;
        }, 1000);
    }
}

// ========================================
// ANIMACIÓN DE TABLAS AL HACER SCROLL
// ========================================
function initScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });
    
    const tables = document.querySelectorAll('.table-responsive');
    tables.forEach(table => {
        table.style.opacity = '0';
        table.style.transform = 'translateY(30px)';
        table.style.transition = 'all 0.6s ease';
        observer.observe(table);
    });
}

// Inicializar animaciones de scroll
setTimeout(initScrollAnimations, 100);

// ========================================
// FILTROS Y BÚSQUEDA EN TABLAS
// ========================================
function initTableSearch() {
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(table => {
        // Crear input de búsqueda si no existe
        const wrapper = table.closest('.table-responsive');
        if (wrapper && !wrapper.querySelector('.table-search')) {
            const searchDiv = document.createElement('div');
            searchDiv.className = 'table-search';
            searchDiv.style.cssText = 'margin-bottom: 15px;';
            
            searchDiv.innerHTML = `
                <input type="text" 
                       placeholder="Buscar..." 
                       style="
                           padding: 10px 15px;
                           border: 2px solid #e0e0e0;
                           border-radius: 8px;
                           width: 100%;
                           font-size: 0.9rem;
                           transition: all 0.3s;
                       "
                       class="table-search-input">
            `;
            
            wrapper.insertBefore(searchDiv, table);
            
            // Funcionalidad de búsqueda
            const input = searchDiv.querySelector('.table-search-input');
            input.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
            
            // Efecto focus
            input.addEventListener('focus', function() {
                this.style.borderColor = '#B91D22';
                this.style.boxShadow = '0 0 0 3px rgba(185, 29, 34, 0.1)';
            });
            
            input.addEventListener('blur', function() {
                this.style.borderColor = '#e0e0e0';
                this.style.boxShadow = 'none';
            });
        }
    });
}

// Inicializar búsqueda en tablas después de un delay
setTimeout(initTableSearch, 500);

// ========================================
// REFRESH DE ESTADÍSTICAS
// ========================================
function refreshStats() {
    const statCards = document.querySelectorAll('.stat-card');
    
    // Animación de recarga
    statCards.forEach(card => {
        card.style.transition = 'opacity 0.3s ease';
        card.style.opacity = '0.5';
    });
    
    // Simular recarga (aquí iría la llamada AJAX real)
    setTimeout(() => {
        statCards.forEach(card => {
            card.style.opacity = '1';
        });
        
        // Reanimar contadores
        animateCounters();
        
        showToast('Estadísticas actualizadas', 'success');
    }, 1000);
}

// Botón de refresh si existe
const refreshBtn = document.querySelector('[data-action="refresh"]');
if (refreshBtn) {
    refreshBtn.addEventListener('click', refreshStats);
}

// ========================================
// TOOLTIP PARA ESTADÍSTICAS
// ========================================
function initStatTooltips() {
    const statCards = document.querySelectorAll('.stat-card');
    
    statCards.forEach(card => {
        const label = card.querySelector('.label');
        if (label) {
            label.style.cursor = 'help';
            label.setAttribute('data-tooltip', 'Ver detalles');
        }
    });
}

initStatTooltips();

// ========================================
// EXPORT FUNCTIONS
// ========================================
window.refreshStats = refreshStats;
window.animateCounters = animateCounters;