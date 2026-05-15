<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>Administrator | Lawyers</title>
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
                        <h1>Lawyers Directory</h1>
                        <p>Manage lawyer records and assigned law offices from the users table.</p>
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
                        <div class="search-input-group">
                            <i class="fas fa-search"></i>
                            <input type="search" id="lawyerSearch" placeholder="Search lawyers by name, office, or contact info">
                        </div>
                        <button class="action-btn action-btn-edit" type="button" data-bs-toggle="modal" data-bs-target="#createLawyerModal">
                            <i class="fas fa-plus"></i> Add Lawyer
                        </button>
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
                                    <?php $__empty_1 = true; $__currentLoopData = $lawyers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lawyer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr class="lawyer-row" data-search="<?php echo e(strtolower(trim(($lawyer->name ?? '') . ' ' . ($lawyer->address ?? '') . ' ' . ($lawyer->username ?? '') . ' ' . ($lawyer->email ?? '') . ' ' . ($lawyer->cp_number ?? '') . ' ' . ($lawyer->law_office ?? '')))); ?>">
                                            <td data-label="Name"><?php echo e($lawyer->name ?? 'N/A'); ?></td>
                                            <td data-label="Address"><?php echo e($lawyer->address ?? 'N/A'); ?></td>
                                            <td data-label="Contact Info">
                                                <div class="contact-meta">
                                                    <span><?php echo e($lawyer->email ?? 'N/A'); ?></span>
                                                    <span><?php echo e($lawyer->cp_number ?? 'N/A'); ?></span>
                                                </div>
                                            </td>
                                            <td data-label="Law Office"><?php echo e($lawyer->lawOffice->law_office ?? $lawyer->law_office ?? 'N/A'); ?></td>
                                            <td data-label="Actions">
                                                <div class="action-buttons">
                                                    <button class="action-btn action-btn-edit" type="button" data-bs-toggle="modal" data-bs-target="#editLawyerModal<?php echo e($lawyer->id); ?>">
                                                        <i class="fas fa-pen"></i> Edit
                                                    </button>
                                                    <button class="action-btn action-btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#deleteLawyerModal<?php echo e($lawyer->id); ?>">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No lawyers found in the users table.</td>
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

    <div class="modal fade" id="createLawyerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="title-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add Lawyer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="content-modal">
                    <p class="modal-description">Create a new lawyer account and store it in the users table.</p>
                    <form class="modal-form-grid" method="POST" action="<?php echo e(route('superadmin.lawyers.store')); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="form_mode" value="create">
                        <div>
                            <label for="createLawyerName">Name</label>
                            <input id="createLawyerName" name="name" type="text" value="<?php echo e(old('name')); ?>" required>
                        </div>
                        <div>
                            <label for="createLawyerAddress">Address</label>
                            <textarea id="createLawyerAddress" name="address" required><?php echo e(old('address')); ?></textarea>
                        </div>
                        <div>
                            <label for="createLawyerUsername">Username</label>
                            <input id="createLawyerUsername" name="username" type="text" value="<?php echo e(old('username')); ?>" required>
                        </div>
                        <div>
                            <label for="createLawyerPhone">Contact Number</label>
                            <input id="createLawyerPhone" name="cp_number" type="text" inputmode="numeric" pattern="\d{11}" maxlength="11" value="<?php echo e(old('cp_number')); ?>" placeholder="09XXXXXXXXX" required>
                        </div>
                        <div>
                            <label for="createLawyerEmail">Email</label>
                            <input id="createLawyerEmail" name="email" type="email" value="<?php echo e(old('email')); ?>" required>
                        </div>
                        <div>
                            <label for="createLawyerPassword">Password</label>
                            <input id="createLawyerPassword" name="password" type="password" required>
                        </div>
                        <div>
                            <label for="createLawyerOffice">Law Office</label>
                            <select id="createLawyerOffice" name="law_office_id" required>
                                <option value="">Select a law office</option>
                                <?php $__currentLoopData = $lawOffices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $office): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($office->id); ?>" <?php echo e(old('law_office_id') == $office->id ? 'selected' : ''); ?>>
                                        <?php echo e($office->law_office); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label for="createLawyerImage">Image</label>
                            <input id="createLawyerImage" name="image" type="file" accept=".jpg,.jpeg,.png,.gif">
                        </div>
                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Add Lawyer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php $__currentLoopData = $lawyers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lawyer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="modal fade" id="editLawyerModal<?php echo e($lawyer->id); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="title-header">
                        <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Edit Lawyer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="content-modal">
                        <p class="modal-description">Update the current lawyer profile information for <?php echo e($lawyer->name ?? 'this lawyer'); ?>.</p>
                        <form class="modal-form-grid" method="POST" action="<?php echo e(route('superadmin.lawyers.update', $lawyer)); ?>" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <input type="hidden" name="form_mode" value="edit">
                            <div>
                                <label for="lawyerName<?php echo e($lawyer->id); ?>">Name</label>
                                <input id="lawyerName<?php echo e($lawyer->id); ?>" name="name" type="text" value="<?php echo e($lawyer->name ?? ''); ?>" required>
                            </div>
                            <div>
                                <label for="lawyerAddress<?php echo e($lawyer->id); ?>">Address</label>
                                <textarea id="lawyerAddress<?php echo e($lawyer->id); ?>" name="address" required><?php echo e($lawyer->address ?? ''); ?></textarea>
                            </div>
                            <div>
                                <label for="lawyerUsername<?php echo e($lawyer->id); ?>">Username</label>
                                <input id="lawyerUsername<?php echo e($lawyer->id); ?>" name="username" type="text" value="<?php echo e($lawyer->username ?? ''); ?>" required>
                            </div>
                            <div>
                                <label for="lawyerContact<?php echo e($lawyer->id); ?>">Contact Info</label>
                                <input id="lawyerContact<?php echo e($lawyer->id); ?>" name="cp_number" type="text" inputmode="numeric" pattern="\d{11}" maxlength="11" value="<?php echo e($lawyer->cp_number ?? ''); ?>" placeholder="09XXXXXXXXX" required>
                            </div>
                            <div>
                                <label for="lawyerEmail<?php echo e($lawyer->id); ?>">Email</label>
                                <input id="lawyerEmail<?php echo e($lawyer->id); ?>" name="email" type="email" value="<?php echo e($lawyer->email ?? ''); ?>" required>
                            </div>
                            <div>
                                <label for="lawyerOffice<?php echo e($lawyer->id); ?>">Law Office</label>
                                <select id="lawyerOffice<?php echo e($lawyer->id); ?>" name="law_office_id" required>
                                    <option value="">Select a law office</option>
                                    <?php $__currentLoopData = $lawOffices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $office): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($office->id); ?>" <?php echo e(($lawyer->law_office_id ?? '') == $office->id ? 'selected' : ''); ?>>
                                            <?php echo e($office->law_office); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div>
                                <label for="lawyerPassword<?php echo e($lawyer->id); ?>">Password</label>
                                <input id="lawyerPassword<?php echo e($lawyer->id); ?>" name="password" type="password" value="" placeholder="Leave blank to keep current password">
                            </div>
                            <div>
                                <label for="lawyerImage<?php echo e($lawyer->id); ?>">Image</label>
                                <input id="lawyerImage<?php echo e($lawyer->id); ?>" name="image" type="file" accept=".jpg,.jpeg,.png,.gif">
                            </div>
                            <div class="modal-footer px-0 pb-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteLawyerModal<?php echo e($lawyer->id); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="title-header">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Delete Lawyer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="content-modal">
                        <p class="modal-description">Are you sure you want to delete <?php echo e($lawyer->name ?? 'this lawyer'); ?>? This action cannot be undone.</p>
                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form method="POST" action="<?php echo e(route('superadmin.lawyers.delete', $lawyer)); ?>" style="display: inline;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger">Delete Lawyer</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt me-1"></i> Log Out</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showLogoutModal() {
            const modalElement = document.getElementById('logoutConfirmationModal');
            if (modalElement) bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('wrapper');
            const menuToggle = document.getElementById('menu-toggle');
            const lawyerSearch = document.getElementById('lawyerSearch');
            const lawyerRows = document.querySelectorAll('.lawyer-row');

            if (wrapper && menuToggle) {
                menuToggle.addEventListener('click', function () {
                    wrapper.classList.toggle('toggled');
                });
            }

            if (lawyerSearch) {
                lawyerSearch.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();
                    lawyerRows.forEach(function (row) {
                        const haystack = row.dataset.search || row.textContent.toLowerCase();
                        row.style.display = haystack.includes(query) ? '' : 'none';
                    });
                });
            }

            <?php if($errors->any() && old('form_mode') === 'create'): ?>
                const createLawyerModal = document.getElementById('createLawyerModal');
                if (createLawyerModal) {
                    bootstrap.Modal.getOrCreateInstance(createLawyerModal).show();
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\superadmin\lawyers.blade.php ENDPATH**/ ?>