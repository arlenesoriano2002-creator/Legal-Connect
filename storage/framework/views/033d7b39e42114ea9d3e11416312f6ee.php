<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>Admin Dashboard</title>
    
    <!-- Remove the Tailwind CDN and use only Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    
    <?php echo $__env->make('partials.global-error-handler', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    <link rel="stylesheet" href="<?php echo e(asset('css/staff/feedbackReports.blade.css')); ?>">
</head>
<body>
    <div id="wrapper">
       <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <img src="<?php echo e(asset('logo6.png')); ?>" alt="LegalConnect logo" width="40" height="40" style="border-radius: 50%;">
                <span>LegalConnect</span>
            </div>
            <div class="list-group list-group-flush">
                <!-- Dashboard link - Already correct -->
                <a href="<?php echo e(route('dashboardStaff')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('dashboardStaff') ? 'active' : ''); ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <!-- Set Time link - Already correct -->
                <a href="<?php echo e(route('staff')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('staff') ? 'active' : ''); ?>">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Set Time</span>
                </a>
                
                <!-- Walk-ins logs - Need to create route in web.php -->
                <a href="<?php echo e(route('staff.walkins.logs')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('staff.walkins.logs') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-clipboard" style="color: #cdd3df;"></i>
                    <span>Walk-ins logs</span>
                </a>
                
                <!-- Feedbacks - Need to create route in web.php -->
                <a href="<?php echo e(route('staff.feedback.reports')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('staff.feedback.reports') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-comments" style="color: #d7dae0;"></i>
                    <span>Feedbacks</span>
                </a>
                
                <!-- Pending Requests - Already has route -->
                <a href="<?php echo e(route('staff.clients.pending')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('staff.clients.pending') ? 'active' : ''); ?>">
                    <i class="fas fa-clock"></i>
                    <span>Pending Requests</span>
                </a>
                
                <!-- Accepted Requests - Already has route -->
                <a href="<?php echo e(route('staff.acceptedRequests')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('staff.acceptedRequests') ? 'active' : ''); ?>">
                    <i class="fas fa-check-circle"></i>
                    <span>Accepted Requests</span>
                </a>
                
                <!-- Denied Requests - Already has route -->
                <a href="<?php echo e(route('staff.deniedRequests')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('staff.deniedRequests') ? 'active' : ''); ?>">
                    <i class="fas fa-times-circle"></i>
                    <span>Denied Requests</span>
                </a>
                <a href="<?php echo e(route('diffun.message.inquiries')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('diffun.message.inquiries') ? 'active' : ''); ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Message Inquiries</span>
                </a>

                <!-- Account Setting - Need to create route in web.php -->
                <a href="<?php echo e(route('staff.account.settings')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('staff.account.settings') ? 'active' : ''); ?>">
                    <i class="fas fa-user-cog"></i>
                    <span>Account Setting</span>
                </a>
            </div>
        </div>
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="top-bar" role="banner">
                <button class="btn btn-primary" id="menu-toggle">
                    <i class="fas fa-bars"></i> 
                </button>
                
                <div class="top-bar-spacer"></div>
                <!-- Notification container (ensures diffunNotifications bell appears) -->
                <div class="notification-container" id="diffun-notification-container" style="position:relative;margin-left:12px">
                    <button id="diffunNotificationBtn" class="notification-btn btn btn-light" style="position:relative">
                        <i class="fas fa-bell"></i>
                        <span id="diffunNotificationBadge" class="badge" style="display:none;position:absolute;top:-6px;right:-6px;background:#ff4757;color:#fff;padding:2px 6px;border-radius:12px;font-size:11px">0</span>
                    </button>
                    <div id="diffunNotificationDropdown" class="notification-dropdown" style="display:none;position:absolute;right:0;top:40px;z-index:9999;width:360px;background:#fff;border:1px solid #e6e6e6;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.08);overflow:hidden">
                        <div class="notification-header" style="padding:8px 12px;border-bottom:1px solid #f0f0f0;background:#fafafa;display:flex;justify-content:space-between;align-items:center">
                            <strong>Notifications</strong>
                            <div style="display:flex;align-items:center;gap:8px">
                                <button id="diffunMarkAllBtn" class="btn btn-sm btn-outline-secondary" style="font-size:11px;padding:3px 8px">Mark all as read</button>
                                <small id="diffunNotificationTime" style="color:#888;font-size:12px"></small>
                            </div>
                        </div>
                        <div id="diffunNotificationList" class="notification-list" style="max-height:320px;overflow:auto;padding:8px">No new notifications</div>
                        <div style="padding:8px;border-top:1px solid #f0f0f0;background:#fafafa;text-align:center;font-size:13px;color:#666">
                            <a href="/StaffClientstbl" style="text-decoration:none">View all</a>
                        </div>
                    </div>
                </div>
                <!-- Message Notification Dropdown - COMMENTED OUT 
                -->

                <!-- Notification Dropdown -->
                <div class="notification-container">
                     <!--<button class="notification-btn" id="notificationBtn">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="notificationBadge">0</span>
                    </button>-->
                    
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">
                            <h4>Appointment Request Notifications</h4>
                            <div class="notification-actions">
                                <button class="btn btn-sm btn-link" id="markAllReadBtn">Mark all as read</button>
                                <button class="btn btn-sm btn-link" onclick="refreshNotifications()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="notification-list" id="notificationList">
                            <div class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p>No new notifications</p>
                            </div>
                        </div>
                        
                        <div class="notification-footer">
                            <a href="<?php echo e(route('clientstbl')); ?>" class="btn btn-sm btn-primary w-100">
                                View All Pending Requests
                            </a>
                        </div>
                    </div>
                </div>
                   <!-- Add this button temporarily for testing 
                <div style="position: fixed; bottom: 10px; right: 10px; z-index: 9999; display: flex; gap: 10px;">
                    <button onclick="testNotification()" class="btn btn-info btn-sm">
                        Test Appt Notif
                    </button>
                    
                </div>-->
                <!-- Log Out -->
                <form id="logout-form" action="<?php echo e(route('custom.logout')); ?>" method="POST" style="display: none;">
                    <?php echo csrf_field(); ?>
                </form>
                <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>
            
            <!-- Rest of your dashboard content remains the same -->
            <div class="dashboard-container">
                <!-- Main Content -->
               <div class="container-fluid py-4 px-4">
                    <!-- Debug info - remove after fixing 
                    <div class="alert alert-info mb-4 d-none" id="debugInfo">
                        <h6>Debug Info:</h6>
                        <pre id="debugData"></pre>
                    </div>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Show debug info
                        const debugDiv = document.getElementById('debugInfo');
                        const debugData = document.getElementById('debugData');
                        
                        debugDiv.classList.remove('d-none');
                        debugData.textContent = JSON.stringify({
                            stats: <?php echo json_encode($stats, 15, 512) ?>,
                            totalReviews: <?php echo e($stats['total_reviews'] ?? 0); ?>,
                            averageRating: <?php echo e($stats['average_rating'] ?? 0); ?>,
                            positiveReviews: <?php echo e($stats['positive_reviews'] ?? 0); ?>,
                            negativeReviews: <?php echo e($stats['negative_reviews'] ?? 0); ?>,
                            neutralReviews: <?php echo e($stats['neutral_reviews'] ?? 0); ?>,
                            ratingDistribution: <?php echo json_encode($stats['rating_distribution'] ?? [], 15, 512) ?>,
                            hasChartJS: typeof Chart !== 'undefined'
                        }, null, 2);
                    });
                    </script>-->
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 mb-0">
                            <i class="fas fa-star text-warning me-2"></i>Feedbacks Dashboard
                        </h1>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success" id="generatePdfBtn">
                                <i class="fas fa-file-pdf me-2"></i> Generate PDF Report
                            </button>
                            <button class="btn btn-info" id="exportCsvBtn">
                                <i class="fas fa-file-excel me-2"></i> Export CSV
                            </button>
                            <button class="btn btn-primary" id="refreshDataBtn">
                                <i class="fas fa-sync-alt me-2"></i> Refresh Data
                            </button>
                        </div>
                    </div>

                    <!-- Filters Card -->
                    <div class="filter-card">
                        <form id="filterForm" method="GET" action="<?php echo e(route('staff.feedback.reports')); ?>">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Star Rating</label>
                                    <select name="rating" class="form-select" id="ratingFilter">
                                        <option value="all" <?php echo e(($filters['rating'] ?? 'all') == 'all' ? 'selected' : ''); ?>>All Ratings</option>
                                        <option value="5" <?php echo e(($filters['rating'] ?? '') == '5' ? 'selected' : ''); ?>>★★★★★ (5 Stars)</option>
                                        <option value="4" <?php echo e(($filters['rating'] ?? '') == '4' ? 'selected' : ''); ?>>★★★★ (4 Stars)</option>
                                        <option value="3" <?php echo e(($filters['rating'] ?? '') == '3' ? 'selected' : ''); ?>>★★★ (3 Stars)</option>
                                        <option value="2" <?php echo e(($filters['rating'] ?? '') == '2' ? 'selected' : ''); ?>>★★ (2 Stars)</option>
                                        <option value="1" <?php echo e(($filters['rating'] ?? '') == '1' ? 'selected' : ''); ?>>★ (1 Star)</option>
                                        <option value="4-5" <?php echo e(($filters['rating'] ?? '') == '4-5' ? 'selected' : ''); ?>>★★★★★ & ★★★★ (4-5 Stars)</option>
                                        <option value="1-3" <?php echo e(($filters['rating'] ?? '') == '1-3' ? 'selected' : ''); ?>>★★★ & Below (1-3 Stars)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" class="form-control" id="startDateFilter"
                                        value="<?php echo e($filters['start_date'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control" id="endDateFilter"
                                        value="<?php echo e($filters['end_date'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" id="searchFilter"
                                            placeholder="Search reviews..." value="<?php echo e($filters['search'] ?? ''); ?>">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <?php if(($filters['rating'] ?? false) || ($filters['start_date'] ?? false) || ($filters['end_date'] ?? false) || ($filters['search'] ?? false)): ?>
                                        <a href="<?php echo e(route('staff.feedback.reports')); ?>" class="btn btn-outline-danger">
                                            <i class="fas fa-times"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: #e3f2fd;">
                                    <i class="fas fa-star text-primary"></i>
                                </div>
                                <div class="stat-value"><?php echo e($stats['total_reviews'] ?? 0); ?></div>
                                <div class="stat-label">Total Reviews</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: #fff3cd;">
                                    <i class="fas fa-chart-line text-warning"></i>
                                </div>
                                <div class="stat-value"><?php echo e($stats['average_rating'] ?? 0); ?><small>/5</small></div>
                                <div class="stat-label">Average Rating</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: #d4edda;">
                                    <i class="fas fa-thumbs-up text-success"></i>
                                </div>
                                <div class="stat-value"><?php echo e($stats['positive_reviews'] ?? 0); ?></div>
                                <div class="stat-label">Positive Reviews (4-5★)</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon" style="background-color: #f8d7da;">
                                    <i class="fas fa-thumbs-down text-danger"></i>
                                </div>
                                <div class="stat-value"><?php echo e($stats['negative_reviews'] ?? 0); ?></div>
                                <div class="stat-label">Negative Reviews (1-2★)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts -->
                    <div class="row mb-4">
                        <div class="chart-section">
                            <div class="col-md-6">
                            <div class="chart-container1">
                                <h5 class="mb-3">
                                    <i class="fas fa-chart-bar me-2"></i>Rating Distribution
                                </h5>
                                <canvas id="ratingDistributionChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-container2">
                                <h5 class="mb-3">
                                    <i class="fas fa-chart-pie me-2"></i>Sentiment Analysis
                                </h5>
                                <canvas id="sentimentChart"></canvas>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Rating Distribution Details -->
                    <div class="chart-container mb-4">
                        <h5 class="mb-3">
                            <i class="fas fa-percentage me-2"></i>Detailed Rating Breakdown
                        </h5>
                        <div class="row">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                            <div class="col-md-12 mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-3" style="width: 80px;">
                                        <span class="rating-badge rating-<?php echo e($i); ?>">
                                            <?php for($j = 0; $j < $i; $j++): ?>★<?php endfor; ?>
                                            <span class="ms-1"><?php echo e($i); ?>★</span>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <?php
                                            $total = $stats['total_reviews'] ?? 1;
                                            $count = $stats['rating_distribution'][$i] ?? 0;
                                            $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                                            $color = match($i) {
                                                5 => '#A8DF8E', // 5 stars - Light Green
                                                4 => '#B0FFFA', // 4 stars - Light Teal/Cyan
                                                3 => '#FEEE91', // 3 stars - Light Yellow
                                                2 => '#FDACAC', // 2 stars - Light Pink/Red
                                                1 => '#FD7979', // 1 star - Light Red
                                                default => '#6c757d'
                                            };
                                        ?>
                                        <div class="progress progress-bar-custom">
                                            <div class="progress-bar" 
                                                style="width: <?php echo e($percentage); ?>%; background-color: <?php echo e($color); ?>;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ms-3" style="width: 100px; text-align: right;">
                                        <strong><?php echo e($count); ?></strong>
                                        <span class="text-muted">(<?php echo e(number_format($percentage, 1)); ?>%)</span>
                                    </div>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Reviews List -->
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-comments me-2"></i>User Reviews
                                <span class="badge bg-primary ms-2"><?php echo e($reviews->total()); ?></span>
                            </h5>
                            <small class="text-muted">Showing <?php echo e($reviews->firstItem() ?? 0); ?>-<?php echo e($reviews->lastItem() ?? 0); ?> of <?php echo e($reviews->total()); ?> reviews</small>
                        </div>
                        <div class="card-body">
                            <?php if($reviews->count() > 0): ?>
                                <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="card review-card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="mb-1"><?php echo e($review->name); ?></h6>
                                                <small class="text-muted"><?php echo e($review->email); ?></small>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="rating-badge rating-<?php echo e($review->rating); ?> me-2">
                                                    <?php for($k = 0; $k < $review->rating; $k++): ?>★<?php endfor; ?>
                                                </span>
                                                <small class="text-muted">
                                                    <?php echo e($review->created_at->format('M d, Y h:i A')); ?>

                                                </small>
                                            </div>
                                        </div>
                                        
                                        <p class="review-comment mb-0"><?php echo e($review->review); ?></p>
                                        
                                       <!-- <?php if($review->image): ?>
                                        <div class="mt-3">
                                            <small class="text-muted d-block mb-1">Attached Image:</small>
                                            <a href="<?php echo e(Storage::url($review->image)); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-image me-1"></i> View Image
                                            </a>
                                        </div>
                                        <?php endif; ?>-->
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <!-- Pagination -->
                                <nav aria-label="Reviews pagination">
                                    <?php echo e($reviews->appends(request()->query())->links('pagination::bootstrap-5')); ?>

                                </nav>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-comment-slash"></i>
                                    <h5>No reviews found</h5>
                                    <p class="text-muted">
                                        <?php if(($filters['rating'] ?? false) || ($filters['start_date'] ?? false) || ($filters['end_date'] ?? false) || ($filters['search'] ?? false)): ?>
                                            Try adjusting your filters to see more results
                                        <?php else: ?>
                                            No feedback has been submitted yet
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                    
            </div>
        </div>  
    </div>
<!-- Bootstrap Modal for Logout Confirmation -->
    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="title-header">
                    <h5 class="modal-title" id="logoutModalLabel">
                        <i class="fas fa-sign-out-alt me-2"></i>Confirm Logout
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <center>
                <div class="content-modal">
                    <div style="font-size: 48px; color: #ffc107; margin-bottom: 15px;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                   
                    <h4 class="mb-3">Confirm Logout</h4>
                    <p>Are you sure you want to log out?<br>You will be redirected to the login page.</p>
                </div>
                 </center>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt me-1"></i> Log Out
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?php echo e(asset('js/staff/feedbacks.js')); ?>"></script>
        <script src="<?php echo e(asset('js/staff/diffunNotifications.js')); ?>"></script>
  <script>
    // Pass PHP data to JavaScript
    window.feedbackData = {
        stats: <?php echo json_encode($stats, 15, 512) ?>,
        rating_distribution: <?php echo json_encode($stats['rating_distribution'] ?? [], 15, 512) ?>,
        filters: <?php echo json_encode($filters ?? [], 15, 512) ?>,
        routes: {
            generatePdf: "<?php echo e(route('staff.feedback.reports.generate-pdf')); ?>",
            exportCsv: "<?php echo e(route('staff.feedback.reports.export-csv')); ?>",
            chartData: "<?php echo e(route('staff.feedback.reports.chart-data')); ?>"
        }
    };
</script>

</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\staff\feedbackReports.blade.php ENDPATH**/ ?>