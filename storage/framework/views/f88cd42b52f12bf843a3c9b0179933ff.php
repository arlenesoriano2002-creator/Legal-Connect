<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>Administrator | Secretaries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/superadmin.css')); ?>">
</head>
<body>
    <div id="wrapper">
        <?php echo $__env->make('layouts.superadmin-sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div id="page-content-wrapper">
            <nav class="top-bar" role="banner">
                <button class="btn btn-primary" id="menu-toggle" type="button" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="top-bar-title">Administrator Dashboard</div>
                <div class="top-bar-spacer"></div>
                <form id="logout-form" action="<?php echo e(route('custom.logout')); ?>" method="POST" style="display: none;">
                    <?php echo csrf_field(); ?>
                </form>
                <button type="button" class="btn logout-btn" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt"></i> Log out
                </button>
            </nav>

            <main class="superadmin-content">
                <div class="dashboard-container">
                    <div class="dashboard-header">
                        <h1>Secretaries Directory</h1>
                        <p>Manage secretary records and assigned law offices from the users table.</p>
                    </div>

                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Please review the form fields.</strong>
                            <ul class="mb-0 mt-2">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="page-toolbar">
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <div class="search-input-group">
                                <i class="fas fa-search"></i>
                                <input type="search" id="secretarySearch" placeholder="Search secretaries by name, office, or contact info">
                            </div>
                            <select id="lawOfficeFilter" class="form-select" style="max-width: 200px;">
                                <option value="">All Law Offices</option>
                                <?php $__currentLoopData = $lawOffices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $office): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($office->law_office); ?>"><?php echo e($office->law_office); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <section class="entity-panel">
                        <div class="entity-table-wrapper">
                            <table class="entity-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Contact Info</th>
                                        <th>Law Office</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $secretaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $secretary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr class="secretary-row" data-search="<?php echo e(strtolower(trim(($secretary->name ?? '') . ' ' . ($secretary->address ?? '') . ' ' . ($secretary->username ?? '') . ' ' . ($secretary->email ?? '') . ' ' . ($secretary->cp_number ?? '') . ' ' . ($secretary->law_office ?? '')))); ?>" data-office="<?php echo e($secretary->law_office ?? ''); ?>">
                                            <td data-label="Name"><?php echo e($secretary->name ?? 'N/A'); ?></td>
                                            <td data-label="Address"><?php echo e($secretary->address ?? 'N/A'); ?></td>
                                            <td data-label="Contact Info">
                                                <div class="contact-meta">
                                                    <span><?php echo e($secretary->email ?? 'N/A'); ?></span>
                                                    <span><?php echo e($secretary->cp_number ?? 'N/A'); ?></span>
                                                </div>
                                            </td>
                                            <td data-label="Law Office"><?php echo e($secretary->lawOffice->law_office ?? $secretary->law_office ?? 'N/A'); ?></td>
                                            <td data-label="Actions">
                                                <div class="action-buttons">
                                                    <button class="action-btn action-btn-edit" type="button" data-bs-toggle="modal" data-bs-target="#editSecretaryModal<?php echo e($secretary->id); ?>">
                                                        <i class="fas fa-pen"></i> Edit
                                                    </button>
                                                    <button class="action-btn action-btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#deleteSecretaryModal<?php echo e($secretary->id); ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No secretaries found in the users table.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <!-- Edit & Delete Modals -->
    <?php $__currentLoopData = $secretaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $secretary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <!-- Edit Secretary Modal -->
        <div class="modal fade" id="editSecretaryModal<?php echo e($secretary->id); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="title-header">
                        <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Edit Secretary</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="content-modal">
                        <p class="modal-description">Update the current secretary profile information for <?php echo e($secretary->name ?? 'this secretary'); ?>.</p>
                        <form class="modal-form-grid" method="POST" action="<?php echo e(route('superadmin.secretaries.update', $secretary)); ?>" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <input type="hidden" name="form_mode" value="edit">
                            <div>
                                <label for="secretaryName<?php echo e($secretary->id); ?>">Name</label>
                                <input id="secretaryName<?php echo e($secretary->id); ?>" name="name" type="text" value="<?php echo e($secretary->name ?? ''); ?>" required>
                            </div>
                            <div>
                                <label for="secretaryAddress<?php echo e($secretary->id); ?>">Address</label>
                                <textarea id="secretaryAddress<?php echo e($secretary->id); ?>" name="address" required><?php echo e($secretary->address ?? ''); ?></textarea>
                            </div>
                            <div>
                                <label for="secretaryEmail<?php echo e($secretary->id); ?>">Email</label>
                                <input id="secretaryEmail<?php echo e($secretary->id); ?>" name="email" type="email" value="<?php echo e($secretary->email ?? ''); ?>" required>
                            </div>
                            <div>
                                <label for="secretaryPhone<?php echo e($secretary->id); ?>">Contact Number</label>
                                <input id="secretaryPhone<?php echo e($secretary->id); ?>" name="cp_number" type="text" inputmode="numeric" pattern="\d{11}" maxlength="11" value="<?php echo e($secretary->cp_number ?? ''); ?>" placeholder="09XXXXXXXXX" required>
                            </div>
                            <div>
                                <label for="secretaryOffice<?php echo e($secretary->id); ?>">Law Office</label>
                                <select id="secretaryOffice<?php echo e($secretary->id); ?>" name="law_office_id" required>
                                    <option value="">Select a law office</option>
                                    <?php $__currentLoopData = $lawOffices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $office): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($office->id); ?>" <?php echo e(($secretary->law_office_id ?? null) == $office->id ? 'selected' : ''); ?>>
                                            <?php echo e($office->law_office); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div>
                                <label for="secretaryImage<?php echo e($secretary->id); ?>">Image</label>
                                <input id="secretaryImage<?php echo e($secretary->id); ?>" name="image" type="file" accept=".jpg,.jpeg,.png,.gif">
                            </div>
                            <div class="modal-footer px-0 pb-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Update Secretary</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Secretary Modal -->
        <div class="modal fade" id="deleteSecretaryModal<?php echo e($secretary->id); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="title-header">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Delete Secretary</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="content-modal">
                        <p class="modal-description">Are you sure you want to delete <?php echo e($secretary->name ?? 'this secretary'); ?>? This action cannot be undone.</p>
                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form method="POST" action="<?php echo e(route('superadmin.secretaries.delete', $secretary)); ?>" style="display: inline;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger">Delete Secretary</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
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
                        <div class="logout-warning-icon">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showLogoutModal() {
            const modalElement = document.getElementById('logoutConfirmationModal');
            if (!modalElement) return;
            const logoutModal = bootstrap.Modal.getOrCreateInstance(modalElement);
            logoutModal.show();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('wrapper');
            const menuToggle = document.getElementById('menu-toggle');

            if (!wrapper || !menuToggle) return;

            menuToggle.addEventListener('click', function () {
                wrapper.classList.toggle('toggled');
            });

            // Search and Filter Functionality
            const searchInput = document.getElementById('secretarySearch');
            const officeFilter = document.getElementById('lawOfficeFilter');
            const rows = document.querySelectorAll('.secretary-row');

            function filterRows() {
                const searchTerm = searchInput.value.toLowerCase();
                const officeFilter = document.getElementById('lawOfficeFilter').value;

                rows.forEach(row => {
                    const searchData = row.getAttribute('data-search');
                    const office = row.getAttribute('data-office');
                    
                    const matchesSearch = searchData.includes(searchTerm) || searchTerm === '';
                    const matchesOffice = officeFilter === '' || office === officeFilter;
                    
                    row.style.display = (matchesSearch && matchesOffice) ? '' : 'none';
                });
            }

            searchInput.addEventListener('keyup', filterRows);
            officeFilter.addEventListener('change', filterRows);
        });
    </script>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\superadmin\secretaries.blade.php ENDPATH**/ ?>