</div> <!-- Cierre del contenido principal -->
</div> <!-- Cierre del row -->
</div> <!-- Cierre del container-fluid -->

<footer class="footer mt-5 py-3 bg-light">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0 text-muted">
                    <i class="fas fa-copyright me-1"></i> 
                    <?php echo date('Y'); ?> Colegio de Sociólogos de Huánuco
                </p>
            </div>
            <div class="col-md-6 text-end">
                <p class="mb-0 text-muted">
                    <i class="fas fa-code me-1"></i> 
                    Versión 1.0 - Desarrollado por SIAD
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Configuración global de DataTables en español
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        }
    });
    
    // Auto-cerrar alertas después de 5 segundos
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>

</body>
</html>