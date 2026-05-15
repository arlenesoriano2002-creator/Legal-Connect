<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo e(asset('css/partials/archived-table.blade.css')); ?>">
</head>
<body>
    

<h2 class="page-title">Archived Appointments</h2>

<div class="table-container">
  <div class="table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Address</th>
          <th>Phone</th>
          <th>Email</th>
          <th>Consulting</th>
          <th>Selected Date</th>
          <th>Selected Time</th>
          <th>Terms Status</th>
          <th>Approval Status</th>
          <th>ID Front</th>
          <th>ID Back</th>
          <th>Delete</th> <!-- NEW -->
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $recentArchived; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td><?php echo e($appointment->fullname); ?></td>
            <td><?php echo e($appointment->address); ?></td>
            <td><?php echo e($appointment->phone); ?></td>
            <td><?php echo e($appointment->email); ?></td>
            <td><?php echo e($appointment->consulting); ?></td>
            <td><?php echo e($appointment->selected_date); ?></td>
            <td><?php echo e($appointment->selected_time); ?></td>
            <td><?php echo e($appointment->term_status); ?></td>
            <td><?php echo e($appointment->appointment_approval); ?></td>

            <!-- Keep these two -->
            <td><button class="image-btn" data-src="<?php echo e(asset('storage/' . $appointment->id_front)); ?>">View Front</button></td>
            <td><button class="image-btn" data-src="<?php echo e(asset('storage/' . $appointment->id_back)); ?>">View Back</button></td>

            <!-- NEW: Delete icon button (does NOT auto-submit) -->
            <td>
              <form action="<?php echo e(route('archived.delete', $appointment->id)); ?>"
                    method="POST"
                    class="delete-archived-form"
                    data-name="<?php echo e($appointment->fullname); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="button" class="icon-delete-btn" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>


      <!--  <h2 class="page-title">Database Backups</h2>

      <div class="table-container">
        <div class="table-wrapper">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Filename</th>
                <th>Date Created</th>
                <th style="text-align:center;">Actions</th>
              </tr>
            </thead>

            <tbody>
              <?php $__empty_1 = true; $__currentLoopData = $backups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                  <td><?php echo e($backup->file_name); ?></td>
                  <td><?php echo e($backup->created_at); ?></td>

                  <td style="text-align:center; display:flex; gap:10px; justify-content:center;">-->

                    <!-- ✅ Download Backup as ENCRYPTED PDF -->
                   <!-- <a href="<?php echo e(route('admin.backup.download', $backup->file_name)); ?>"
                      class="btn btn-small" 
                      style="background:#007bff;color:#fff;border-radius:4px;padding:6px 10px;">
                      <i class="fa-solid fa-file-pdf"></i> PDF
                    </a>-->

                    <!-- ✅ DELETE Backup (file + DB row) 
                    <form action="<?php echo e(route('admin.backup.delete', $backup->file_name)); ?>"
                        method="POST"
                        class="delete-backup-form"
                        data-file="<?php echo e($backup->file_name); ?>">
                      <?php echo csrf_field(); ?>
                      <?php echo method_field('DELETE'); ?>

                      <button type="button" class="btn btn-small backup-delete-btn"
                              style="background:#d9534f;color:#fff;border-radius:4px;padding:6px 10px;">
                          <i class="fa-solid fa-trash"></i>
                      </button>
                  </form>


                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                  <td colspan="3" style="text-align:center;">No backups stored yet.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>-->

  </div>
</div>

<!-- Existing Image Modal -->
<div id="archiveImageModal" class="image-modal">
  <div class="image-modal-backdrop"></div>
  <div class="image-modal-content">
    <button type="button" class="image-modal-close" aria-label="Close">&times;</button>
    <figure class="image-modal-figure">
      <img id="popupImage" class="image-modal-img" src="" alt="ID Image">
    </figure>
  </div>
</div>

<!-- NEW: Delete Confirmation Modal--> 
<div id="deleteConfirmModal" class="delete-modal">
  <div class="delete-modal-backdrop"></div>
  <div class="delete-modal-box">
    <div class="delete-modal-header">
      <h3>Confirm Delete</h3>
    </div>

    <p id="deleteItemName" class="delete-modal-message"></p>

    <div class="delete-modal-actions">
      <button type="button" class="cancel-delete-btn">Cancel</button>
      <button type="button" class="confirm-delete-btn">Delete</button>
    </div>
  </div>
</div>

<!-- BACKUP DELETE CONFIRMATION MODAL -->
<div id="deleteBackupModal" class="delete-modal">
  <div class="delete-modal-backdrop"></div>
  <div class="delete-modal-box">
    <div class="delete-modal-header">
      <h3>Confirm Backup Deletion</h3>
    </div>

    <p id="deleteBackupName" class="delete-modal-message"></p>

    <div class="delete-modal-actions">
      <button type="button" class="cancel-backup-delete-btn">Cancel</button>
      <button type="button" class="confirm-backup-delete-btn">Delete</button>
    </div>
  </div>
</div>


<script src="<?php echo e(asset('js/archived-table.js')); ?>"></script>

</body>
</html><?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\partials\archived-table.blade.php ENDPATH**/ ?>