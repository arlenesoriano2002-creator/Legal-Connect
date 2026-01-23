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
                    <a href="<?php echo e(route('messages.sms')); ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-sms"></i>
                        <span>SMS</span>
                    </a>
                    <a href="<?php echo e(route('messages.system-chat')); ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-comments"></i>
                        <span>System Chatting</span>
                    </a>
                </div>
                <a href="<?php echo e(url('/practice-areas')); ?>" class="list-group-item list-group-item-action <?php echo e(request()->is('practice-areas') ? 'active' : ''); ?>">
                    <i class="fa-solid fa-suitcase"></i>
                    <span>Practice Areas</span>
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
                    <i class="fas fa-user-cog"></i>
                    <span>All Accounts</span>
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

                <!-- Log Out -->
                <form id="logout-form" action="<?php echo e(route('custom.logout')); ?>" method="POST" style="display: none;">
                    <?php echo csrf_field(); ?>
                </form>
                <button type="button" class="btn logout-btn" aria-label="Log out" onclick="document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>
            
            <!-- Practice Areas Content -->
            <div class="practice-areas-container">
                <div class="page-header">
                    <div class="description-page">
                        <h1 class="page-title">Practice Areas Management</h1>
                        <p>Organizing, viewing, and updating legal practice categories and their case types.</p>
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
                                            <span><?php echo e($case->case_name); ?></span>
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
                                    <input type="text" class="form-control" name="category" required placeholder="e.g., Criminal Law, Civil Law">
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
            const menuToggle = document.getElementById('menu-toggle');
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    document.getElementById('wrapper').classList.toggle('toggled');
                });
            }
            
            // Toast initialization
            const toast = new bootstrap.Toast(document.getElementById('successToast'));
            
            // Add Category Form
            document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
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
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(error => console.error('Error:', error));
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
        
        // Delete category
        function deleteCategory(category) {
            if (confirm(`Are you sure you want to delete "${category}" and all its cases?`)) {
                fetch(`/practice-areas/category/${encodeURIComponent(category)}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Category deleted successfully');
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }
        
        // Delete category from table
        function deleteCategoryFromTable(category) {
            if (confirm(`Are you sure you want to delete "${category}" and all its cases?`)) {
                fetch(`/practice-areas/category/${encodeURIComponent(category)}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Category deleted successfully');
                        document.getElementById(`category-row-${md5(category)}`).remove();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
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
                                <td>${caseItem.case_name}</td>
                                <td>${new Date(caseItem.created_at).toLocaleDateString()}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-warning" onclick="editCase(${caseItem.id}, '${caseItem.case_name.replace(/'/g, "\\'")}')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteCase(${caseItem.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Set category for adding case
        function setAddCaseCategory(category) {
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
        
        // Delete case
        function deleteCase(id) {
            if (confirm('Are you sure you want to delete this case?')) {
                fetch(`/practice-areas/case/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Case deleted successfully');
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
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
</body>
</html><?php /**PATH D:\xampp\htdocs\LEGAL CONNECT\resources\views/practice-areas.blade.php ENDPATH**/ ?>