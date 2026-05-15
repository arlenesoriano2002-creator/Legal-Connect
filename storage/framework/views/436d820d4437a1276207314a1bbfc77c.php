<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
    <title>Purpose Choices Management</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    
    <?php echo $__env->make('partials.global-error-handler', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        
        .purpose-card {
            transition: all 0.3s ease;
            border-left: 4px solid #007bff;
        }
        
        .purpose-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .purpose-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .purpose-item:last-child {
            border-bottom: none;
        }
        
        .purpose-number {
            background: #007bff;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .back-btn {
            margin-bottom: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        /* Toast Notification Styling */
        .toast-container {
            z-index: 9999;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Back Button -->
        <div class="back-btn">
            <a href="<?php echo e(route('staff.walkins.logs')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Walk-in Logs
            </a>
        </div>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-list-check me-2"></i>Purpose Choices Management
            </h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPurposeModal">
                <i class="fas fa-plus me-1"></i> Add New Purpose
            </button>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> Please fix the following errors:
                <ul class="mb-0 mt-2">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Purpose List -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Available Purposes (<?php echo e($purposes->count()); ?>)
                </h5>
                <small class="text-muted">These purposes will appear in walk-in forms</small>
            </div>
            <div class="card-body">
                <?php if($purposes->count() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Purpose</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $purposes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $purpose): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td><?php echo e($purpose->purpose); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn btn-sm btn-outline-primary edit-btn" 
                                                    data-id="<?php echo e($purpose->id); ?>" 
                                                    data-purpose="<?php echo e($purpose->purpose); ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-purpose-btn"
                                                    data-id="<?php echo e($purpose->id); ?>"
                                                    data-purpose="<?php echo e($purpose->purpose); ?>">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No purposes found</h5>
                        <p class="text-muted">Click "Add New Purpose" to create your first purpose</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Purpose Modal -->
    <div class="modal fade" id="addPurposeModal" tabindex="-1" aria-labelledby="addPurposeModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPurposeModalLabel">
                        <i class="fas fa-plus me-2"></i>Add New Purpose
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo e(route('staff.purpose.choices.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="purpose" class="form-label">Purpose Name</label>
                            <input type="text" class="form-control" id="purpose" name="purpose" 
                                   placeholder="Enter purpose (e.g., Legal Consultation)" required>
                            <div class="form-text">This will appear in the purpose dropdown list for walk-ins.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Purpose</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Purpose Modal -->
    <div class="modal fade" id="editPurposeModal" tabindex="-1" aria-labelledby="editPurposeModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPurposeModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Purpose
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editPurposeForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_purpose" class="form-label">Purpose Name</label>
                            <input type="text" class="form-control" id="edit_purpose" name="purpose" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Purpose</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Purpose Confirmation Modal -->
    <div class="modal fade" id="deletePurposeModal" tabindex="-1" aria-labelledby="deletePurposeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="deletePurposeModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Delete Purpose
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                        <h5 class="mb-3">Are you sure you want to delete this purpose?</h5>
                        <p class="mb-2">Purpose: <strong id="deletePurposeName"></strong></p>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <small>This action cannot be undone. Deleting this purpose will not affect existing walk-in records.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeletePurposeBtn">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Edit purpose functionality
            $('.edit-btn').click(function() {
                var id = $(this).data('id');
                var purpose = $(this).data('purpose');
                
                $('#edit_purpose').val(purpose);
                $('#editPurposeForm').attr('action', '<?php echo e(route("staff.purpose.choices.update", "")); ?>/' + id);
                
                $('#editPurposeModal').modal('show');
            });

            // Delete purpose functionality
            let currentDeletePurposeId = null;
            
            $('.delete-purpose-btn').click(function() {
                const purposeId = $(this).data('id');
                const purposeName = $(this).data('purpose');
                
                currentDeletePurposeId = purposeId;
                
                // Set purpose name in modal
                $('#deletePurposeName').text(purposeName);
                
                // Show modal
                const deletePurposeModal = new bootstrap.Modal(document.getElementById('deletePurposeModal'));
                deletePurposeModal.show();
            });

            // Handle delete button click in modal
            $('#confirmDeletePurposeBtn').click(function() {
                if (!currentDeletePurposeId) return;
                
                const deleteBtn = $(this);
                const originalHtml = deleteBtn.html();
                
                // Show loading state
                deleteBtn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Deleting...');
                deleteBtn.prop('disabled', true);
                
                // Send AJAX DELETE request - CORRECTED URL
                $.ajax({
                    url: '/staff/purpose-choices/' + currentDeletePurposeId,
                    type: 'DELETE',
                    data: {
                        _token: '<?php echo e(csrf_token()); ?>',
                        _method: 'DELETE'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Show success toast
                            showToast('success', 'Success', response.message || 'Purpose deleted successfully.');
                            
                            // Close modal
                            $('#deletePurposeModal').modal('hide');
                            
                            // Remove the row from the table
                            $(`.delete-purpose-btn[data-id="${currentDeletePurposeId}"]`).closest('tr').remove();
                            
                            // Update purpose count in header
                            const purposeCount = $('tbody tr').length;
                            $('.card-header h5').html(`<i class="fas fa-list me-2"></i>Available Purposes (${purposeCount})`);
                            
                            // Check if table is now empty
                            if (purposeCount === 0) {
                                $('tbody').html(`
                                    <tr>
                                        <td colspan="3" class="text-center">
                                            <div class="py-5">
                                                <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No purposes found</h5>
                                                <p class="text-muted">Click "Add New Purpose" to create your first purpose</p>
                                            </div>
                                        </td>
                                    </tr>
                                `);
                            }
                        } else {
                            showToast('error', 'Error', response.message || 'Failed to delete purpose.');
                            resetDeletePurposeButton(deleteBtn, originalHtml);
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = 'Error deleting purpose';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 404) {
                            errorMessage = 'Purpose not found.';
                        } else if (xhr.status === 405) {
                            errorMessage = 'Method not allowed. Please check the route configuration.';
                        }
                        showToast('error', 'Error', errorMessage);
                        resetDeletePurposeButton(deleteBtn, originalHtml);
                    }
                });
            });

            // Reset delete button state
            function resetDeletePurposeButton(button, originalHtml) {
                button.html(originalHtml);
                button.prop('disabled', false);
            }

            // Toast notification function
            function showToast(type, title, message) {
                const toastId = 'toast-' + Date.now();
                const toastHtml = `
                    <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <strong>${title}:</strong> ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
                
                $('#toastContainer').append(toastHtml);
                
                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement, {
                    autohide: true,
                    delay: 5000
                });
                
                toast.show();
                
                toastElement.addEventListener('hidden.bs.toast', function() {
                    this.remove();
                });
            }

            // Auto-close alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Close modal reset
            $('#deletePurposeModal').on('hidden.bs.modal', function() {
                currentDeletePurposeId = null;
                resetDeletePurposeButton($('#confirmDeletePurposeBtn'), '<i class="fas fa-trash-alt me-1"></i> Delete');
            });
        });
    </script>

    <script src="<?php echo e(asset('js/staff/walkInsLogs-table.js')); ?>"></script>
</body>
</html><?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\staff\purpose_choices.blade.php ENDPATH**/ ?>