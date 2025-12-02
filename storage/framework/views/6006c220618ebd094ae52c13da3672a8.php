<link rel="stylesheet" href="<?php echo e(asset('css/partials/backup-manager.css')); ?>">

<div id="backupCardsContainer" class="backup-card-container">
    <?php $__empty_1 = true; $__currentLoopData = $backups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="backup-card">
            <div class="backup-card-info">
                <!-- Handle both cases: model with accessor or object with property -->
                <?php if(isset($backup->decrypted_file_name)): ?>
                    <h5 class="backup-name"><?php echo e($backup->decrypted_file_name); ?></h5>
                <?php else: ?>
                    <!-- Fallback: try to decrypt manually or show encrypted -->
                    <h6 class="backup-name">
                        <?php
                            try {
                                echo \Illuminate\Support\Facades\Crypt::decryptString($backup->file_name);
                            } catch (Exception $e) {
                                echo 'Encrypted Backup';
                            }
                        ?>
                    </h5>
                <?php endif; ?>
                <p class="backup-date"><?php echo e(\Carbon\Carbon::parse($backup->created_at)->format('M d, Y h:i A')); ?></p>
            </div>

            <div class="backup-card-actions">
                <!-- NEW: View Button -->
                <button class="backup-btn view-btn" data-backup-id="<?php echo e($backup->id); ?>">
                    <i class="fa-solid fa-eye"></i> View
                </button>

                <!-- FIX: Update download route to use backup ID instead of filename -->
                <a href="<?php echo e(route('backup.download', $backup->id)); ?>" class="backup-btn download-btn">
                    <i class="fa-solid fa-file-arrow-down"></i> Download
                </a>

                <!-- FIX: Update delete route to use backup ID instead of filename -->
                <form action="<?php echo e(route('admin.backup.delete.byid', $backup->id)); ?>"
                    method="POST"
                    class="delete-backup-form"
                    data-file="<?php echo e($backup->decrypted_file_name ?? 'Backup'); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="button" class="backup-btn delete-btn backup-delete-btn">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="backup-empty-message">No backups stored yet.</p>
    <?php endif; ?>
</div> 

<!-- BACKUP DELETE CONFIRMATION MODAL -->
<div id="backupDeleteModal">
  <div class="backup-delete-backdrop"></div>
  <div class="backup-delete-box">
      <h3>Confirm Backup Deletion</h3>
      <p id="backupDeleteFileName" class="backup-delete-message"></p>
      <div class="backup-delete-actions">
          <button type="button" class="backup-delete-cancel">Cancel</button>
          <button type="button" class="backup-delete-confirm">Delete</button>
      </div>
  </div>
</div>

<!-- NEW: PDF VIEWER MODAL -->
<div id="pdfViewerModal" class="pdf-viewer-modal">
  <div class="pdf-viewer-backdrop"></div>
  <div class="pdf-viewer-container">
      <div class="pdf-viewer-header">
          <h3 id="pdfViewerTitle">Backup File Preview</h3>
          <button type="button" class="pdf-viewer-close">
              <i class="fa-solid fa-times"></i>
          </button>
      </div>
      <div class="pdf-viewer-body">
          <iframe id="pdfViewerFrame" src="" width="100%" height="100%" frameborder="0"></iframe>
      </div>
      <div class="pdf-viewer-footer">
          <button id="pdfDownloadBtn" class="backup-btn download-btn">
              <i class="fa-solid fa-file-arrow-down"></i> Download
          </button>
          <button type="button" class="backup-btn close-pdf-viewer">
              Close
          </button>
      </div>
  </div>
</div>

<script src="<?php echo e(asset('js/backup-manager.js')); ?>"></script><?php /**PATH D:\xampp\htdocs\LEGAL CONNECT\resources\views/partials/backup-manager.blade.php ENDPATH**/ ?>