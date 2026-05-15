

<?php $__env->startSection('title', 'Lawyer Office Requests'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .dashboard-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 2rem;
        color: white;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
    }

    .dashboard-card.pending {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .dashboard-card.approved {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .dashboard-card.denied {
        background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
    }

    .card-number {
        font-size: 3rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .card-title {
        font-size: 1.2rem;
        margin-bottom: 0;
        opacity: 0.9;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 15px 15px;
    }

    .page-title {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .page-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('nav'); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="<?php echo e(asset('logo6.png')); ?>" alt="LegalConnect" width="30" height="30" class="me-2">
            LegalConnect - Lawyer Dashboard
        </a>
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3">
                Welcome, <?php echo e(Auth::check() ? Auth::user()->name : 'Lawyer'); ?> (<?php echo e(Auth::check() ? Auth::user()->role : 'lawyer'); ?>)
            </span>
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </button>
            </form>
        </div>
    </div>
</nav>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="page-title">
                <i class="fas fa-gavel me-3"></i>Office Appointment Requests
            </h1>
            <p class="page-subtitle">
                View appointment requests for your law office with scheduled times
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="container">
        <div class="stats-container">
            <!-- Pending Requests -->
            <div class="dashboard-card pending">
                <div class="card-number"><?php echo e($pendingAppointments); ?></div>
                <div class="card-title">
                    <i class="fas fa-clock me-2"></i>Pending Requests
                </div>
                <small>Appointments awaiting approval</small>
            </div>

            <!-- Approved Requests -->
            <div class="dashboard-card approved">
                <div class="card-number"><?php echo e($approvedAppointments); ?></div>
                <div class="card-title">
                    <i class="fas fa-check-circle me-2"></i>Approved Requests
                </div>
                <small>Successfully approved appointments</small>
            </div>

            <!-- Denied Requests -->
            <div class="dashboard-card denied">
                <div class="card-number"><?php echo e($deniedAppointments); ?></div>
                <div class="card-title">
                    <i class="fas fa-times-circle me-2"></i>Denied Requests
                </div>
                <small>Appointments that were denied</small>
            </div>
        </div>

        <!-- Info Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-info-circle me-2"></i>About This Dashboard
                        </h5>
                        <p class="card-text">
                            This dashboard shows appointment requests specifically for your assigned law office.
                            Only appointments with scheduled times are included in these counts.
                        </p>
                        <div class="alert alert-info">
                            <strong>Note:</strong> These statistics are filtered to show only appointments
                            from your law office that have a selected time scheduled.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
// Auto-refresh the page every 30 seconds to show updated counts
setInterval(function() {
    location.reload();
}, 30000);

// Add loading animation on refresh
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.dashboard-card');
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\lawyer\office_requests.blade.php ENDPATH**/ ?>