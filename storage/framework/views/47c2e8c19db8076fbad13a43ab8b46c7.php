<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>Practice Areas - Admin Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="<?php echo e(asset('css/practice-areas.css')); ?>">
    
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
                <a href="<?php echo e(url('/admindashboard')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('admindashboard') ? 'active' : ''); ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <a href="<?php echo e(url('/administrator')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('administrator') ? 'active' : ''); ?>">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Set Time</span>
                </a>
                <a href="<?php echo e(url('/appointments')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('appointments') ? 'active' : ''); ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Logs Requests</span>
                </a>
                <a href="<?php echo e(route('admin.walkins')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->routeIs('admin.walkins') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-clipboard" style="color: #cdd3df;"></i>
                    <span>Walk-Ins logs</span>
                </a>
                <a href="#messagesSubmenu" 
                class="list-group-item list-group-item-action <?php echo e(request()->is('email-chat') || request()->is('messages/*') ? 'active' : ''); ?>"
                data-bs-toggle="collapse" 
                aria-expanded="<?php echo e(request()->is('email-chat') || request()->is('messages/*') ? 'true' : 'false'); ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse <?php echo e(request()->is('email-chat') || request()->is('messages/*') ? 'show' : ''); ?> list-group" id="messagesSubmenu">
                    <a href="<?php echo e(route('messages.email')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('email-chat') ? 'active' : ''); ?>">
                        <i class="fas fa-envelope"></i>
                        <span>Email</span>
                    </a>
                    <a href="<?php echo e(route('messages.sms')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('sms-chat') ? 'active' : ''); ?>">
                        <i class="fas fa-sms"></i>
                        <span>SMS</span>
                    </a>
                    <a href="<?php echo e(route('admin.system-chat')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('admin/system-chat') ? 'active' : ''); ?>">
                        <i class="fas fa-comments"></i>
                        <span>System Chatting</span>
                    </a>
                </div>
                <a href="<?php echo e(url('/practice-areas')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('practice-areas') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-suitcase"></i>
                    <span>Services</span>
                </a>
                <a href="#requestsSubmenu" class="list-group-item list-group-item-action" data-bs-toggle="collapse" aria-expanded="false">
                    <i class="fas fa-list-alt"></i>
                    <span>Appointment Requests</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="submenu collapse list-group <?php echo e(request()->is('clientstbl') || request()->is('adminAcceptedRequest') || request()->is('adminDeniedRequest') ? 'show' : ''); ?>" id="requestsSubmenu">
                    <a href="<?php echo e(url('/clientstbl')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('clientstbl') ? 'active' : ''); ?>">
                        <i class="fas fa-clock"></i>
                        <span>Pending Requests</span>
                    </a>
                    <a href="<?php echo e(url('/adminAcceptedRequest')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('adminAcceptedRequest') ? 'active' : ''); ?>">
                        <i class="fas fa-check-circle"></i>
                        <span>Accepted Requests</span>
                    </a>
                    <a href="<?php echo e(url('/adminDeniedRequest')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('adminDeniedRequest') ? 'active' : ''); ?>">
                        <i class="fas fa-times-circle"></i>
                        <span>Denied Requests</span>
                    </a>
                </div>

                <a href="<?php echo e(url('/adminAccount')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('adminAccount') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-user-group"></i>
                    <span>All Staff Accounts</span>
                </a>
                <a href="<?php echo e(route('admin.account.settings')); ?>"
                class="list-group-item list-group-item-action <?php echo e(request()->routeIs('admin.account.settings') ? 'active' : ''); ?>">
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

                <!-- notification dropdown-->
                <div class="notification-container">
                    <button class="notification-btn" id="notificationBtn">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="notificationBadge">0</span>
                    </button>
                    
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

                <form id="logout-form" action="<?php echo e(route('custom.logout')); ?>" method="POST" style="display: none;">
                    <?php echo csrf_field(); ?>
                </form>
                    <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                        <i class="fas fa-sign-out-alt"></i> Log out
                    </button>
            </nav>
            
            <!-- Practice Areas Content -->
            <div class="practice-areas-container">
                <div class="page-header">
                    <div class="description-page">
                        <h1 class="page-title">Services Management</h1>
                        <p>Organizing, viewing, and updating legal services categories and their case types.</p>
                    </div>
                    <button type="button" class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal">
                        <i class="fas fa-plus"></i> Manage Categories
                    </button>
                </div>
                
                <div class="row">
                    <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="category-card">
                                <div class="category-header">
                                    <h3 class="category-title"><?php echo e($category->category); ?></h3>
                                    <span class="category-stats"><?php echo e($category->case_count); ?> cases</span>
                                </div>
                                
                                <!-- Cases List Preview -->
                                <div class="cases-list">
                                    <?php $__currentLoopData = $casesByCategory[$category->category]->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="case-item">
                                            <span class="case-name"><?php echo e($case->case_name); ?></span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($category->case_count > 3): ?>
                                        <div class="text-center mt-2">
                                            <small class="text-muted">+<?php echo e($category->case_count - 3); ?> more cases</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="action-buttons">
                                    <button class="btn-action btn-delete" onclick="deleteCategory('<?php echo e($category->category); ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                    <button class="btn-action btn-view" onclick="viewCategoryCases('<?php echo e($category->category); ?>')" data-bs-toggle="modal" data-bs-target="#viewCasesModal">
                                        <i class="fas fa-eye"></i> View Cases
                                    </button>
                                    <button class="btn-action btn-add" onclick="setAddCaseCategory('<?php echo e($category->category); ?>')" data-bs-toggle="modal" data-bs-target="#addCaseModal">
                                        <i class="fas fa-plus"></i> Add Case
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="fas fa-suitcase"></i>
                                <h4>No Practice Areas Yet</h4>
                                <p>Start by adding your first practice area category.</p>
                                <button type="button" class="btn btn-primary-custom mt-3" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal">
                                    <i class="fas fa-plus"></i> Add First Category
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Categories Modal -->
    <div class="modal fade" id="manageCategoriesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Practice Areas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add New Category Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Add New Category</h6>
                            <form id="addCategoryForm">
                                <?php echo csrf_field(); ?>
                                <div class="form-group">
                                    <label class="form-label">Category Name</label>
                                    <input type="text" class="form-control" name="category" id="categoryName" required placeholder="e.g., Criminal Law, Civil Law">
                                </div>
                                
                                <!-- Cases Section -->
                                <div class="form-group mt-4">
                                    <label class="form-label">Add Cases (Optional)</label>
                                    <div id="casesContainer">
                                        <div class="case-input-group mb-2">
                                            <div class="row g-2 align-items-end">
                                                <div class="col-md-10">
                                                    <input type="text" class="form-control case-input" placeholder="Case name" maxlength="255">
                                                </div>
                                                <div class="col-md-2 d-grid">
                                                    <button type="button" class="btn btn-outline-danger" onclick="removeCaseInput(this)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addCaseInput()">
                                        <i class="fas fa-plus"></i> Add Another Case
                                    </button>
                                </div>
                                
                                <button type="submit" class="btn btn-primary-custom w-100">
                                    <i class="fas fa-plus"></i> Add Category
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Existing Categories Table -->
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Existing Categories</h6>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Cases Count</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr id="category-row-<?php echo e(md5($category->category)); ?>">
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" 
                                                           id="category-<?php echo e(md5($category->category)); ?>" 
                                                           value="<?php echo e($category->category); ?>">
                                                </td>
                                                <td><?php echo e($category->case_count); ?></td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <!--<button class="btn btn-sm btn-success" 
                                                                onclick="updateCategory('<?php echo e($category->category); ?>')">
                                                            <i class="fas fa-save"></i>
                                                        </button>-->
                                                        <button class="btn btn-sm btn-danger" 
                                                                onclick="deleteCategoryFromTable('<?php echo e($category->category); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Cases Modal -->
    <div class="modal fade" id="viewCasesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewCasesModalTitle">Cases in Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Case Name</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="casesTableBody">
                                <!-- Cases will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Case Modal -->
    <div class="modal fade" id="addCaseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCaseForm">
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control" id="addCaseCategory" readonly>
                            <input type="hidden" name="category" id="addCaseCategoryHidden">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Case Name</label>
                            <input type="text" class="form-control" name="case_name" required placeholder="e.g., Murder, Theft, Assault">
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-plus"></i> Add Case
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Case Modal -->
    <div class="modal fade" id="editCaseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editCaseForm">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <input type="hidden" id="editCaseId">
                        <div class="form-group">
                            <label class="form-label">Case Name</label>
                            <input type="text" class="form-control" id="editCaseName" name="case_name" required>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="fas fa-save"></i> Update Case
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
     <!-- Bootstrap Modal for Logout Confirmation -->
    <!-- Generic Confirm Delete Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmDeleteMessage">Are you sure you want to delete this item?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Success Toast -->
    <div class="toast align-items-center text-white bg-success border-0" id="successToast" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toast initialization
            const toast = new bootstrap.Toast(document.getElementById('successToast'));
            
            // Add Category Form
            document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const categoryName = document.getElementById('categoryName').value.trim();
                
                if (!categoryName) {
                    alert('Please enter a category name');
                    return;
                }
                
                const cases = collectCaseEntries();
                if (cases === null) {
                    return;
                }
                
                const formData = new FormData();
                formData.append('category', categoryName);
                formData.append('_token', '<?php echo e(csrf_token()); ?>');
                cases.forEach((caseItem, index) => {
                    formData.append(`cases[${index}][case_name]`, caseItem.case_name);
                });
                
                fetch('<?php echo e(route("practice-areas.storeCategory")); ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Category added successfully');
                        document.getElementById('addCategoryForm').reset();
                        resetCasesContainer();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        alert('Error creating category: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error creating category');
                });
            });
            
            // Add Case Form
            document.getElementById('addCaseForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('<?php echo e(route("practice-areas.storeCase")); ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Case added successfully');
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addCaseModal'));
                        modal.hide();
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
            
            // Edit Case Form
            document.getElementById('editCaseForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const caseId = document.getElementById('editCaseId').value;
                const formData = new FormData(this);
                
                fetch(`/practice-areas/case/${caseId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'X-HTTP-Method-Override': 'PUT',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Case updated successfully');
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editCaseModal'));
                        modal.hide();
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
        
        // Show toast function
        function showToast(message) {
            document.getElementById('toastMessage').textContent = message;
            const toast = new bootstrap.Toast(document.getElementById('successToast'));
            toast.show();
        }

        function getCaseInputGroupHtml() {
            return `
                <div class="case-input-group mb-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-10">
                            <input type="text" class="form-control case-input" placeholder="Case name" maxlength="255">
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="button" class="btn btn-outline-danger" onclick="removeCaseInput(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function resetCasesContainer() {
            document.getElementById('casesContainer').innerHTML = getCaseInputGroupHtml();
        }

        function collectCaseEntries() {
            const cases = [];
            const caseGroups = document.querySelectorAll('#casesContainer .case-input-group');

            for (const group of caseGroups) {
                const caseNameInput = group.querySelector('.case-input');
                const caseName = caseNameInput ? caseNameInput.value.trim() : '';

                if (!caseName) {
                    continue;
                }

                cases.push({
                    case_name: caseName
                });
            }

            return cases;
        }
        
        // Add case input field dynamically
        function addCaseInput() {
            const container = document.getElementById('casesContainer');
            const newInput = document.createElement('div');
            newInput.innerHTML = getCaseInputGroupHtml();
            const caseGroup = newInput.firstElementChild;
            container.appendChild(caseGroup);
            // Focus on the new input
            caseGroup.querySelector('.case-input').focus();
        }
        
        // Remove case input field
        function removeCaseInput(button) {
            const groups = document.querySelectorAll('#casesContainer .case-input-group');
            if (groups.length === 1) {
                const caseNameInput = groups[0].querySelector('.case-input');
                if (caseNameInput) caseNameInput.value = '';
                return;
            }

            const group = button.closest('.case-input-group');
            group.remove();
        }
        
        // Generic prepareDelete - opens confirmation modal and stores action
        let _pendingDelete = null;

        function prepareDelete(url, method, message, onSuccess) {
            _pendingDelete = { url, method, onSuccess };
            const msgEl = document.getElementById('confirmDeleteMessage');
            if (msgEl) msgEl.textContent = message;
            const modalEl = document.getElementById('confirmDeleteModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        // Specific delete helpers that use the modal
        function deleteCategory(category) {
            const url = `/practice-areas/category/${encodeURIComponent(category)}`;
            prepareDelete(url, 'DELETE', `Are you sure you want to delete "${category}" and all its cases?`, function(data) {
                showToast('Category deleted successfully');
                setTimeout(() => location.reload(), 1500);
            });
        }

        function deleteCategoryFromTable(category) {
            const url = `/practice-areas/category/${encodeURIComponent(category)}`;
            prepareDelete(url, 'DELETE', `Are you sure you want to delete "${category}" and all its cases?`, function(data) {
                showToast('Category deleted successfully');
                const row = document.getElementById(`category-row-${md5(category)}`);
                if (row) row.remove();
            });
        }
        
        // Update category
        function updateCategory(oldCategory) {
            const newCategory = document.getElementById(`category-${md5(oldCategory)}`).value;
            
            if (!newCategory.trim()) {
                alert('Category name cannot be empty');
                return;
            }
            
            fetch(`/practice-areas/category/${encodeURIComponent(oldCategory)}`, {
                method: 'POST',
                body: JSON.stringify({ new_category: newCategory }),
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Content-Type': 'application/json',
                    'X-HTTP-Method-Override': 'PUT',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Category updated successfully');
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // View category cases
        function viewCategoryCases(category) {
            document.getElementById('viewCasesModalTitle').textContent = `Cases in ${category}`;
            
            fetch(`/practice-areas/category/${encodeURIComponent(category)}/cases`)
                .then(response => response.json())
                .then(cases => {
                    const tbody = document.getElementById('casesTableBody');
                    tbody.innerHTML = '';
                    
                    if (cases.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    No cases found for this category.
                                </td>
                            </tr>
                        `;
                    } else {
                        cases.forEach(caseItem => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${escapeHtml(caseItem.case_name)}</td>
                                <td>${caseItem.created_at ? new Date(caseItem.created_at).toLocaleDateString() : 'N/A'}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-warning case-edit-btn">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteCase(${caseItem.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            `;
                            row.querySelector('.case-edit-btn').addEventListener('click', function() {
                                editCase(caseItem.id, caseItem.case_name);
                            });
                            tbody.appendChild(row);
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Set category for adding case
        function setAddCaseCategory(category) {
            document.getElementById('addCaseForm').reset();
            document.getElementById('addCaseCategory').value = category;
            document.getElementById('addCaseCategoryHidden').value = category;
        }
        
        // Edit case
        function editCase(id, caseName) {
            document.getElementById('editCaseId').value = id;
            document.getElementById('editCaseName').value = caseName;
            
            const modal = new bootstrap.Modal(document.getElementById('editCaseModal'));
            modal.show();
        }
        
        // Delete case via modal
        function deleteCase(id) {
            const url = `/practice-areas/case/${id}`;
            prepareDelete(url, 'DELETE', 'Are you sure you want to delete this case?', function(data) {
                showToast('Case deleted successfully');
                setTimeout(() => location.reload(), 1500);
            });
        }
        
        // Simple MD5 function for unique IDs
        function md5(inputString) {
            let h1, h2, h3, h4, k1, k2, k3, k4;
            const c1 = 0x5a827999, c2 = 0x6ed9eba1, c3 = 0x8f1bbcdc, c4 = 0xca62c1d6;
            
            // Initialize variables
            h1 = 0x67452301;
            h2 = 0xefcdab89;
            h3 = 0x98badcfe;
            h4 = 0x10325476;
            
            // Pre-processing: padding
            let padded = inputString + '\x80';
            while (padded.length % 64 !== 56) {
                padded += '\x00';
            }
            padded += String.fromCharCode((inputString.length * 8) & 0xff);
            padded += String.fromCharCode((inputString.length * 8) >>> 8);
            padded += String.fromCharCode((inputString.length * 8) >>> 16);
            padded += String.fromCharCode((inputString.length * 8) >>> 24);
            
            // Process the message in 16-word blocks
            for (let i = 0; i < padded.length; i += 64) {
                const block = padded.substr(i, 64);
                const words = new Array(16);
                
                for (let j = 0; j < 16; j++) {
                    words[j] = (block.charCodeAt(j * 4) & 0xff) |
                               ((block.charCodeAt(j * 4 + 1) & 0xff) << 8) |
                               ((block.charCodeAt(j * 4 + 2) & 0xff) << 16) |
                               ((block.charCodeAt(j * 4 + 3) & 0xff) << 24);
                }
                
                // Extended to 80 words
                const extendedWords = new Array(80);
                for (let j = 0; j < 16; j++) {
                    extendedWords[j] = words[j];
                }
                for (let j = 16; j < 80; j++) {
                    extendedWords[j] = (extendedWords[j - 3] ^ extendedWords[j - 8] ^ extendedWords[j - 14] ^ extendedWords[j - 16]);
                    extendedWords[j] = (extendedWords[j] << 1) | (extendedWords[j] >>> 31);
                }
                
                // Initialize hash value for this chunk
                let a = h1, b = h2, c = h3, d = h4;
                
                // Main loop
                for (let j = 0; j < 80; j++) {
                    let f, k;
                    if (j < 20) {
                        f = (b & c) | ((~b) & d);
                        k = c1;
                    } else if (j < 40) {
                        f = b ^ c ^ d;
                        k = c2;
                    } else if (j < 60) {
                        f = (b & c) | (b & d) | (c & d);
                        k = c3;
                    } else {
                        f = b ^ c ^ d;
                        k = c4;
                    }
                    
                    const temp = ((a << 5) | (a >>> 27)) + f + extendedWords[j] + k;
                    a = d;
                    d = c;
                    c = (b << 30) | (b >>> 2);
                    b = temp;
                }
                
                // Add this chunk's hash to result so far
                h1 = (h1 + a) >>> 0;
                h2 = (h2 + b) >>> 0;
                h3 = (h3 + c) >>> 0;
                h4 = (h4 + d) >>> 0;
            }
            
            // Produce the final hash
            return ((h1 >>> 0).toString(16).padStart(8, '0') +
                    (h2 >>> 0).toString(16).padStart(8, '0') +
                    (h3 >>> 0).toString(16).padStart(8, '0') +
                    (h4 >>> 0).toString(16).padStart(8, '0')).substr(0, 8);
        }
    </script>
        <script>
    // Utility functions
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function formatPhoneForDisplay(phone) {
    if (!phone) return '';
    
    // Remove non-numeric characters
    phone = phone.replace(/\D/g, '');
    
    if (phone.length === 10) {
        return '(' + phone.substring(0, 3) + ') ' + phone.substring(3, 6) + '-' + phone.substring(6);
    } else if (phone.length === 11 && phone.startsWith('0')) {
        return '(' + phone.substring(1, 4) + ') ' + phone.substring(4, 7) + '-' + phone.substring(7);
    } else if (phone.length === 12 && phone.startsWith('63')) {
        return '+63 ' + phone.substring(2, 5) + ' ' + phone.substring(5, 8) + ' ' + phone.substring(8);
    }
    
    return phone;
}

// Optional: Add keyboard shortcut (Ctrl+Q) for logout
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'q') {
        e.preventDefault();
        // Use Bootstrap's modal directly
        const logoutModal = new bootstrap.Modal(document.getElementById('logoutConfirmationModal'));
        logoutModal.show();
    }
});
// Simplified logout modal function without aria issues
function showLogoutModal() {
    // Create modal instance
    const modalElement = document.getElementById('logoutConfirmationModal');
    
    // Remove any aria-hidden attributes that might conflict
    modalElement.removeAttribute('aria-hidden');
    modalElement.setAttribute('aria-modal', 'true');
    
    // Use Bootstrap's modal properly
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: true,
        focus: true
    });
    
    // Show modal
    modal.show();
    
    // Listen for modal events to fix aria attributes
    modalElement.addEventListener('shown.bs.modal', function() {
        // Ensure proper accessibility
        this.removeAttribute('aria-hidden');
        this.setAttribute('aria-modal', 'true');
        
        // Focus on the cancel button
        setTimeout(() => {
            const cancelBtn = this.querySelector('.btn-secondary');
            if (cancelBtn) {
                cancelBtn.focus();
            }
        }, 100);
    });
    
    modalElement.addEventListener('hidden.bs.modal', function() {
        // When hidden, let Bootstrap handle aria-hidden
        this.removeAttribute('aria-modal');
    });
}

// Keyboard shortcut - use the button click instead of direct modal
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'q') {
        e.preventDefault();
        // Find and click the logout button
        const logoutBtn = document.querySelector('.logout-btn[onclick*="showLogoutModal"]');
        if (logoutBtn) {
            logoutBtn.click();
        } else {
            // Fallback to calling the function directly
            showLogoutModal();
        }
    }
});
</script>
<script>
// Smooth Sidebar Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    const wrapper = document.getElementById('wrapper');
    
    if (!menuToggle || !wrapper) return;
    
    // Load saved state from localStorage (sidebar should be open by default)
    const savedState = localStorage.getItem('sidebarCollapsed');
    const screenWidth = window.innerWidth;
    
    // Desktop: sidebar open by default unless saved as collapsed
    // Tablet: sidebar collapsed by default unless saved as open
    // Mobile: sidebar hidden by default
    if (screenWidth > 900) { // Desktop
        if (savedState === 'true') {
            wrapper.classList.add('toggled');
        }
        // If no saved state, sidebar stays open (default)
    } else if (screenWidth > 640) { // Tablet (900px and below, but above 640px)
        if (savedState !== 'false') { // Default to collapsed on tablet
            wrapper.classList.add('toggled');
        }
    } else { // Mobile (640px and below)
        if (savedState === 'true') {
            wrapper.classList.add('toggled');
        }
        // If no saved state, sidebar stays hidden on mobile
    }
    
    // Toggle sidebar
    menuToggle.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Toggle the collapsed state
        wrapper.classList.toggle('toggled');
        
        // Save state to localStorage
        const isCollapsed = wrapper.classList.contains('toggled');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
        
        // On mobile, close sidebar when clicking outside
        if (screenWidth <= 640 && isCollapsed) {
            // Add click listener to close sidebar when clicking outside
            setTimeout(() => {
                const closeSidebarOnClick = function(e) {
                    if (!e.target.closest('#sidebar-wrapper') && e.target !== menuToggle) {
                        wrapper.classList.remove('toggled');
                        localStorage.setItem('sidebarCollapsed', 'false');
                        document.removeEventListener('click', closeSidebarOnClick);
                    }
                };
                
                // Add listener after a short delay to avoid immediate trigger
                setTimeout(() => {
                    document.addEventListener('click', closeSidebarOnClick);
                }, 100);
            }, 10);
        }
    });
    
    // Handle responsive behavior on resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            const width = window.innerWidth;
            const savedState = localStorage.getItem('sidebarCollapsed');
            
            if (width > 900) { // Desktop
                if (savedState === 'true') {
                    wrapper.classList.add('toggled');
                } else {
                    wrapper.classList.remove('toggled');
                }
            } else if (width > 640) { // Tablet
                if (savedState !== 'false') {
                    wrapper.classList.add('toggled');
                } else {
                    wrapper.classList.remove('toggled');
                }
            } else { // Mobile
                if (savedState === 'true') {
                    wrapper.classList.add('toggled');
                } else {
                    wrapper.classList.remove('toggled');
                }
            }
        }, 250);
    });
});

// Notification System Functions
function initializeNotificationSystem() {
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    
    // Toggle notification dropdown
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
            // If dropdown opened, immediately hide badge and mark as read (user viewed notifications)
            if (notificationDropdown.classList.contains('show')) {
                try {
                    // Visual hide immediately
                    updateNotificationBadge(0);
                } catch (err) {
                    console.error('updateNotificationBadge not available', err);
                }
                try {
                    // Mark all as read on server (non-blocking)
                    markAllNotificationsAsRead();
                } catch (err) {
                    console.error('markAllNotificationsAsRead not available', err);
                }
            }
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (notificationBtn && notificationDropdown &&
            !notificationBtn.contains(e.target) && 
            !notificationDropdown.contains(e.target)) {
            notificationDropdown.classList.remove('show');
        }
    });
    
    // Mark all as read
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            markAllNotificationsAsRead();
        });
    }
    
    // Initialize notification system
    loadNotifications();
    
    // Real-time polling every 10 seconds
    setInterval(() => {
        if (!notificationDropdown.classList.contains('show')) {
            fetch('/admin/notifications/count')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const currentCount = parseInt(document.getElementById('notificationBadge').textContent);
                        if (data.unread_count > currentCount) {
                            loadNotifications();
                        }
                        updateNotificationBadge(data.unread_count);
                    }
                })
                .catch(error => {
                    console.error('Real-time polling error:', error);
                });
        }
    }, 10000); // 10 seconds
}

function loadNotifications() {
    const notificationList = document.getElementById('notificationList');
    if (!notificationList) return;
    
    fetch('/admin/notifications/unread')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateNotificationBadge(data.unread_count);
                renderNotifications(data.notifications);
            } else {
                console.error('Notification error:', data.error || 'Unknown error');
                showFallbackNotifications();
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            showFallbackNotifications();
        });
}

function updateNotificationBadge(count) {
    const notificationBadge = document.getElementById('notificationBadge');
    if (notificationBadge) {
        notificationBadge.textContent = count;
        notificationBadge.style.display = count > 0 ? 'block' : 'none';
    }
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    
    // Check if date is valid
    if (isNaN(date.getTime())) {
        return 'Recently';
    }
    
    const seconds = Math.floor((now - date) / 1000);
    
    let interval = Math.floor(seconds / 31536000);
    if (interval >= 1) return interval + ' year' + (interval > 1 ? 's' : '') + ' ago';
    
    interval = Math.floor(seconds / 2592000);
    if (interval >= 1) return interval + ' month' + (interval > 1 ? 's' : '') + ' ago';
    
    interval = Math.floor(seconds / 86400);
    if (interval >= 1) return interval + ' day' + (interval > 1 ? 's' : '') + ' ago';
    
    interval = Math.floor(seconds / 3600);
    if (interval >= 1) return interval + ' hour' + (interval > 1 ? 's' : '') + ' ago';
    
    interval = Math.floor(seconds / 60);
    if (interval >= 1) return interval + ' minute' + (interval > 1 ? 's' : '') + ' ago';
    
    return 'Just now';
}

function renderNotifications(notifications) {
    const notificationList = document.getElementById('notificationList');
    if (!notificationList) return;
    
    if (!notifications || notifications.length === 0) {
        notificationList.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-bell-slash"></i>
                <p>No new notifications</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    notifications.forEach(notification => {
        const timeAgo = formatTimeAgo(notification.created_at);
        const isUnread = !notification.is_read;
        
        // Determine icon and redirect URL based on notification type
        let iconClass = 'fas fa-calendar-plus';
        let redirectUrl = '<?php echo e(route("clientstbl")); ?>';
        let seeMoreText = 'See More';
        
        if (notification.type === 'message') {
            switch (notification.icon_type) {
                case 'envelope':
                    iconClass = 'fas fa-envelope';
                    seeMoreText = 'View Email';
                    break;
                case 'sms':
                    iconClass = 'fas fa-sms';
                    seeMoreText = 'View SMS';
                    break;
                case 'comments':
                    iconClass = 'fas fa-comments';
                    seeMoreText = 'View Chat';
                    break;
                default:
                    iconClass = 'fas fa-comments';
                    seeMoreText = 'View Message';
                    break;
            }
            redirectUrl = notification.redirect_url;
        }
        
        html += `
            <div class="notification-item ${isUnread ? 'unread' : ''}" 
                 data-id="${notification.id}" 
                 onclick="markNotificationAsRead('${notification.id}', this)">
                <div class="notification-icon">
                    <i class="${iconClass}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${escapeHtml(notification.title)}</div>
                    <div class="notification-message">${escapeHtml(notification.message)}</div>
                    <div class="notification-time">
                        <i class="far fa-clock"></i>
                        ${timeAgo}
                    </div>
                    <div class="notification-actions-row">
                        <button class="btn btn-sm btn-outline-primary see-more-btn" 
                                onclick="event.stopPropagation(); window.location.href='${redirectUrl}'">
                            <i class="fas fa-external-link-alt"></i> ${seeMoreText}
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    notificationList.innerHTML = html;
}

function showFallbackNotifications() {
    const notificationList = document.getElementById('notificationList');
    if (notificationList) {
        notificationList.innerHTML = `
            <div class="notification-empty">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Unable to load notifications</p>
                <small>Please check your connection</small>
            </div>
        `;
    }
}

// Mark notification as read
function markNotificationAsRead(id, element) {
    fetch(`/admin/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (element) {
                element.classList.remove('unread');
            }
            updateNotificationBadge(data.unread_count);
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

// Mark all notifications as read
function markAllNotificationsAsRead() {
    fetch('/admin/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove unread class from all items
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            updateNotificationBadge(0);
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

// Refresh notifications function
function refreshNotifications() {
    loadNotifications();
}

// Initialize the notification system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Existing code...
    
    // Initialize notification system
    initializeNotificationSystem();
    
    // Existing code...
});
</script>
<script>
// Wire confirm delete modal button
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (!confirmBtn) return;

    confirmBtn.addEventListener('click', function() {
        if (!_pendingDelete == null) {
            // nothing to do
        }

        if (!_pendingDelete) return;

        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

        fetch(_pendingDelete.url, {
            method: _pendingDelete.method || 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // hide modal
            const modalEl = document.getElementById('confirmDeleteModal');
            try { bootstrap.Modal.getInstance(modalEl).hide(); } catch (e) {}

            if (data && data.success) {
                try {
                    if (typeof _pendingDelete.onSuccess === 'function') {
                        _pendingDelete.onSuccess(data);
                    }
                } catch (e) {
                    console.error('onSuccess callback error', e);
                }
            } else {
                console.error('Delete failed', data);
            }
        })
        .catch(error => {
            console.error('Error executing delete:', error);
        })
        .finally(() => { _pendingDelete = null; });
    });
});
</script>
<?php echo $__env->make('partials.notification-badge-visibility', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\practice-areas.blade.php ENDPATH**/ ?>