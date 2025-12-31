<style>
    .sidebar {
        min-height: calc(100vh - 56px);
        background: white;
        box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        padding: 0;
    }
    
    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .sidebar-menu li {
        border-bottom: 1px solid #f0f0f0;
    }
    
    .sidebar-menu a {
        display: block;
        padding: 15px 20px;
        color: #333;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .sidebar-menu a:hover {
        background-color: #f8f9fa;
        color: var(--primary-color);
        padding-left: 25px;
    }
    
    .sidebar-menu a.active {
        background-color: var(--primary-color);
        color: white;
        border-left: 4px solid var(--primary-dark);
    }
    
    .sidebar-menu a i {
        width: 20px;
        margin-right: 10px;
        text-align: center;
    }
    
    .sidebar-header {
        padding: 20px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        text-align: center;
        font-weight: 600;
        font-size: 1.1rem;
    }
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-bars me-2"></i> MENÚ PRINCIPAL
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo url('dashboard'); ?>" class="<?php echo ($_SERVER['REQUEST_URI'] == url('dashboard')) ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        
        <li>
            <a href="<?php echo url('colegiados'); ?>">
                <i class="fas fa-users"></i> Colegiados
            </a>
        </li>
        
        <li>
            <a href="<?php echo url('pagos'); ?>">
                <i class="fas fa-money-bill-wave"></i> Pagos
            </a>
        </li>
        
        <li>
            <a href="<?php echo url('deudas'); ?>">
                <i class="fas fa-file-invoice-dollar"></i> Deudas
            </a>
        </li>
        
        <li>
            <a href="<?php echo url('egresos'); ?>">
                <i class="fas fa-receipt"></i> Egresos
            </a>
        </li>
        
        <li>
            <a href="<?php echo url('reportes'); ?>">
                <i class="fas fa-chart-bar"></i> Reportes
            </a>
        </li>
        
        <?php if (hasRole('administrador')): ?>
        <li>
            <a href="<?php echo url('usuarios'); ?>">
                <i class="fas fa-user-cog"></i> Usuarios
            </a>
        </li>
        <?php endif; ?>
        
        <li>
            <a href="<?php echo url('buscador-publico'); ?>" target="_blank">
                <i class="fas fa-search"></i> Buscador Público
            </a>
        </li>
    </ul>
</div>