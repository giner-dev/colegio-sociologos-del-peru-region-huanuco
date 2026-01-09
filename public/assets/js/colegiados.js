
document.addEventListener('DOMContentLoaded', function() {
    console.log('Módulo de colegiados cargado');
    
    // Inicializar funcionalidades
    initFormValidation();
    initTableSearch();
    initTableMobileLabels();
    initFileUpload();
    initConfirmations();
    initPhoneFormatting();
    initDNIValidation();
});

// ===================================
// VALIDACIÓN DE FORMULARIOS
// ===================================
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showToast('Por favor complete todos los campos requeridos', 'warning');
            }
        });
        
        // Validación en tiempo real
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateInput(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateInput(this);
                }
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        // Saltar validación para numero_colegiatura ya que ahora es opcional
        if (input.name === 'numero_colegiatura') {
            return;
        }
        
        if (!validateInput(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateInput(input) {
    const value = input.value.trim();
    let isValid = true;
    
    // Eliminar clases previas
    input.classList.remove('is-invalid', 'is-valid');
    
    // Validar campo requerido
    if (input.hasAttribute('required') && !value) {
        isValid = false;
    }
    
    // Validación específica por tipo
    if (value && input.type === 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
        }
    }
    
    if (value && input.name === 'dni') {
        if (value.length !== 8 || !/^\d+$/.test(value)) {
            isValid = false;
        }
    }
    
    // Aplicar clase visual
    if (!isValid) {
        input.classList.add('is-invalid');
        input.style.borderColor = '#dc3545';
    } else if (value) {
        input.classList.add('is-valid');
        input.style.borderColor = '#28a745';
    } else {
        input.style.borderColor = '';
    }
    
    return isValid;
}

// ===================================
// BÚSQUEDA EN TABLA
// ===================================
function initTableSearch() {
    const searchInput = document.querySelector('input[placeholder="Buscar..."]');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            filterTable(this.value);
        }, 300));
    }
}

function filterTable(searchTerm) {
    const table = document.querySelector('.table tbody');
    if (!table) return;
    
    const rows = table.querySelectorAll('tr');
    const term = searchTerm.toLowerCase();
    let visibleCount = 0;
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(term)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Actualizar contador si existe
    const counter = document.querySelector('[class*="Resultados"]');
    if (counter) {
        const match = counter.textContent.match(/\d+/);
        if (match) {
            counter.innerHTML = counter.innerHTML.replace(/\d+/, visibleCount);
        }
    }
}

// ===================================
// LABELS PARA TABLA MÓVIL
// ===================================
function initTableMobileLabels() {
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(table => {
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (headers[index]) {
                    cell.setAttribute('data-label', headers[index]);
                }
            });
        });
    });
}

// ===================================
// CARGA DE ARCHIVOS
// ===================================
function initFileUpload() {
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('archivoExcel');
    
    if (!uploadZone || !fileInput) return;
    
    // Drag and drop
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('dragover');
    });
    
    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');
    });
    
    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });
    
    // Click para seleccionar
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });
}

function handleFileSelect(file) {
    const allowedExtensions = ['xlsx', 'xls'];
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    if (!allowedExtensions.includes(fileExtension)) {
        showToast('Solo se permiten archivos Excel (.xlsx o .xls)', 'error');
        return;
    }
    
    // Validar tamaño (máximo 10MB)
    const maxSize = 10 * 1024 * 1024; // 10MB
    if (file.size > maxSize) {
        showToast('El archivo es demasiado grande. Máximo 10MB', 'error');
        return;
    }
    
    // Mostrar archivo seleccionado
    const archivoSeleccionado = document.getElementById('archivoSeleccionado');
    const nombreArchivo = document.getElementById('nombreArchivo');
    const btnImportar = document.getElementById('btnImportar');
    
    if (nombreArchivo) {
        nombreArchivo.textContent = file.name;
    }
    
    if (archivoSeleccionado) {
        archivoSeleccionado.classList.remove('d-none');
    }
    
    if (btnImportar) {
        btnImportar.disabled = false;
    }
    
    showToast(`Archivo "${file.name}" seleccionado correctamente`, 'success');
}

// ===================================
// CONFIRMACIONES
// ===================================
function initConfirmations() {
    // Confirmación para cambio de estado
    const formEstado = document.getElementById('formEstado');
    if (formEstado) {
        formEstado.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const estadoSelect = this.querySelector('select[name="estado"]');
            const estadoNuevo = estadoSelect.value;
            const estadoTexto = estadoNuevo === 'habilitado' ? 'HABILITAR' : 'INHABILITAR';
            
            confirmAction(
                `¿Está seguro que desea ${estadoTexto} a este colegiado?`,
                () => {
                    formEstado.submit();
                }
            );
        });
    }
    
    // Confirmación para importación
    const formImportar = document.getElementById('formImportar');
    if (formImportar) {
        formImportar.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('archivoExcel');
            if (!fileInput || !fileInput.files.length) {
                showToast('Debe seleccionar un archivo', 'warning');
                return;
            }
            
            // Mostrar loading
            showLoadingModal('Procesando archivo...', 'Por favor espere mientras se importan los registros');
            
            // Submit después de un pequeño delay para que se vea el modal
            setTimeout(() => {
                formImportar.submit();
            }, 500);
        });
    }
}

// ===================================
// FORMATEO DE TELÉFONO
// ===================================
function initPhoneFormatting() {
    const phoneInputs = document.querySelectorAll('input[name="telefono"]');
    
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Solo permitir números
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Limitar a 9 dígitos
            if (this.value.length > 9) {
                this.value = this.value.slice(0, 9);
            }
        });
    });
}

// ===================================
// VALIDACIÓN DE DNI
// ===================================
function initDNIValidation() {
    const dniInputs = document.querySelectorAll('input[name="dni"]');
    
    dniInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Solo permitir números
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Limitar a 8 dígitos
            if (this.value.length > 8) {
                this.value = this.value.slice(0, 8);
            }
        });
        
        input.addEventListener('blur', function() {
            if (this.value && this.value.length !== 8) {
                this.style.borderColor = '#dc3545';
                showToast('El DNI debe tener 8 dígitos', 'warning');
            }
        });
    });
}

// ===================================
// MODAL DE CARGA
// ===================================
function showLoadingModal(title, message) {
    const overlay = document.createElement('div');
    overlay.id = 'loadingModal';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        animation: fadeIn 0.3s ease;
    `;
    
    const modal = document.createElement('div');
    modal.style.cssText = `
        background: white;
        padding: 40px;
        border-radius: 12px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        animation: scaleIn 0.3s ease;
    `;
    
    modal.innerHTML = `
        <div class="spinner" style="
            border: 4px solid #f3f3f3;
            border-top: 4px solid #B91D22;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        "></div>
        <h4 style="margin-bottom: 10px; color: #333;">${title}</h4>
        <p style="color: #666; margin: 0;">${message}</p>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Agregar animación de spinner si no existe
    if (!document.querySelector('#spinner-animation')) {
        const style = document.createElement('style');
        style.id = 'spinner-animation';
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    }
}

function hideLoadingModal() {
    const modal = document.getElementById('loadingModal');
    if (modal) {
        modal.remove();
    }
}

// ===================================
// UTILIDADES
// ===================================
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ===================================
// ANIMACIONES DE ENTRADA
// ===================================
function animateElements() {
    const elements = document.querySelectorAll('.card, .alert');
    
    elements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            element.style.transition = 'all 0.5s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

// Iniciar animaciones
setTimeout(animateElements, 100);

// ===================================
// FORMATEO DE NÚMERO DE COLEGIATURA
// ===================================
function formatNumeroColegiatura(numero, digitos = 5) {
    if (!numero) {
        return '0'.repeat(digitos);
    }
    return numero.toString().padStart(digitos, '0');
}

// Aplica formato a todos los números de colegiatura en la página
function formatAllNumeroColegiatura() {
    // Buscar todos los elementos que muestren número de colegiatura
    const elementos = document.querySelectorAll('[data-numero-colegiatura]');
    
    elementos.forEach(elemento => {
        const numero = elemento.getAttribute('data-numero-colegiatura');
        if (numero) {
            elemento.textContent = formatNumeroColegiatura(numero);
        }
    });
}

// Ejecutar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    formatAllNumeroColegiatura();
});


// ===================================
// EXPORTAR FUNCIONES GLOBALES
// ===================================
window.showLoadingModal = showLoadingModal;
window.hideLoadingModal = hideLoadingModal;
window.formatNumeroColegiatura = formatNumeroColegiatura;