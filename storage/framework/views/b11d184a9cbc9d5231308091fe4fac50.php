<div id="sidebar-wrapper">
    <div class="sidebar-heading">
        <div class="head-content">
            <img src="<?php echo e(asset('logo6.png')); ?>" alt="LegalConnect logo" width="40" height="40">
            <span>LegalConnect</span>
        </div>
    </div>

    <div class="list-group list-group-flush">
        <a href="<?php echo e(route('superadmin.page')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('superadmin.page') ? 'active' : ''); ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?php echo e(route('superadmin.lawyers')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('superadmin.lawyers') ? 'active' : ''); ?>">
            <i class="fas fa-scale-balanced"></i>
            <span>Lawyers</span>
        </a>
        <a href="<?php echo e(route('superadmin.secretaries')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('superadmin.secretaries') ? 'active' : ''); ?>">
            <i class="fas fa-user-tie"></i>
            <span>Secretaries</span>
        </a>
        <a href="<?php echo e(route('superadmin.lawoffices')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('superadmin.lawoffices') ? 'active' : ''); ?>">
            <i class="fas fa-building"></i>
            <span>Law Offices</span>
        </a>
        <a href="<?php echo e(route('superadmin.clients')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('superadmin.clients') ? 'active' : ''); ?>">
            <i class="fas fa-users"></i>
            <span>Clients</span>
        </a>
        <a href="<?php echo e(route('superadmin.statistics')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('superadmin.statistics') ? 'active' : ''); ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Statistics</span>
        </a>
        <a href="<?php echo e(route('superadmin.message-inquiries')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('superadmin.message-inquiries') ? 'active' : ''); ?>">
            <i class="fas fa-envelope"></i>
            <span>Message Inquiries</span>
        </a>
    </div>
</div>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\layouts\superadmin-sidebar.blade.php ENDPATH**/ ?>