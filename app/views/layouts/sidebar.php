<div class="sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo url('dashboard'); ?>" class="<?php echo (isset($active_menu) && $active_menu === 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Escritorio</span>
            </a>
        </li>
        
        <li>
            <a href="<?php echo url('colegiados'); ?>" class="<?php echo (isset($active_menu) && $active_menu === 'colegiados') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Colegiados</span>
            </a>
        </li>
        
        <li>
            <a href="<?php echo url('pagos'); ?>" class="<?php echo (isset($active_menu) && $active_menu === 'pagos') ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Pagos</span>
            </a>
        </li>
        
        <li>
            <a href="<?php echo url('deudas'); ?>" class="<?php echo (isset($active_menu) && $active_menu === 'deudas') ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Deudas</span>
            </a>
        </li>
        
        <li>
            <a href="<?php echo url('egresos'); ?>" class="<?php echo (isset($active_menu) && $active_menu === 'egresos') ? 'active' : ''; ?>">
                <i class="fas fa-receipt"></i>
                <span>Egresos</span>
            </a>
        </li>
        
        <li>
            <a href="<?php echo url('reportes'); ?>" class="<?php echo (isset($active_menu) && $active_menu === 'reportes') ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reportes</span>
            </a>
        </li>
        
        <?php if (hasRole('administrador')): ?>
        <li>
            <a href="<?php echo url('usuarios'); ?>" class="<?php echo (isset($active_menu) && $active_menu === 'usuarios') ? 'active' : ''; ?>">
                <i class="fas fa-user-cog"></i>
                <span>Usuarios</span>
            </a>
        </li>
        <?php endif; ?>
        
        <li>
            <a href="<?php echo url('buscador-publico'); ?>" target="_blank">
                <i class="fas fa-search"></i>
                <span>Buscador Público</span>
            </a>
        </li>
    </ul>
</div>