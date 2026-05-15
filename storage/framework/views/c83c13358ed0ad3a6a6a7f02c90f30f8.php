<link rel="stylesheet" href="<?php echo e(asset('css/partials/backup-manager.css')); ?>">

<div id="backupCardsContainer" class="backup-card-container">
    <?php $__empty_1 = true; $__currentLoopData = $backups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="backup-card">
            <div class="backup-card-info">
                <!-- Handle both cases: model with accessor or object with property -->
                <?php if(isset($backup->decrypted_file_name)): ?>
                    <div class="backup-name-container">
                        <?php
                            $extension = pathinfo($backup->decrypted_file_name, PATHINFO_EXTENSION);
                            $icon = '';
                            switch(strtolower($extension)) {
                                case 'pdf':
                                    $icon = 'fa-file-pdf';
                                    break;
                                case 'csv':
                                    $icon = 'fa-file-excel';
                                    break;
                                case 'sql':
                                    $icon = 'fa-database';
                                    break;
                                default:
                                    $icon = 'fa-file';
                            }
                        ?>
                        <i class="fas <?php echo e($icon); ?> me-2"></i>
                        <h5 class="backup-name"><?php echo e($backup->decrypted_file_name); ?></h5>
                    </div>
                <?php else: ?>
                    <!-- Fallback: try to decrypt manually or show encrypted -->
                    <div class="backup-name-container">
                        <i class="fas fa-file me-2"></i>
                        <h6 class="backup-name">
                            <?php
                                try {
                                    echo \Illuminate\Support\Facades\Crypt::decryptString($backup->file_name);
                                } catch (Exception $e) {
                                    echo 'Encrypted Backup';
                                }
                            ?>
                        </h6>
                    </div>
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
      <div class="pdf-viewer-body" style="position: relative;">
          <!-- Loading indicator will be inserted here by JavaScript -->
          <iframe id="pdfViewerFrame" src="" 
                  width="100%" 
                  height="500px" 
                  frameborder="0"
                  style="border: 1px solid #dee2e6; border-radius: 4px;"></iframe>
      </div>
      <div class="pdf-viewer-footer">
          <button id="pdfDownloadBtn" class="backup-btn download-btn">
              <i class="fa-solid fa-file-arrow-down"></i> Download
          </button>
          <button type="button" class="backup-btn close-pdf-viewer">
              Close
          </button>
          <button id="debugPdfBtn" class="btn btn-sm btn-info mt-2">
            <i class="fa-solid fa-bug"></i> Debug PDF View
          </button>
      </div>
  </div>
</div>
<!-- Add this debug button somewhere in the file -->

<div id="csvPreviewContainer" style="max-height:70vh; overflow:auto;"></div>

<!-- Add this after the PDF Viewer Modal -->
<!-- NEW: CSV VIEWER MODAL -->
<div id="csvViewerModal" class="pdf-viewer-modal">
  <div class="pdf-viewer-backdrop"></div>
  <div class="pdf-viewer-container">
      <div class="pdf-viewer-header">
          <h3 id="csvViewerTitle">CSV File Preview</h3>
          <button type="button" class="csv-viewer-close">
              <i class="fa-solid fa-times"></i>
          </button>
      </div>
      <div class="pdf-viewer-body" style="position: relative; max-height: 70vh; overflow-y: auto;">
          <div id="csvViewerBody"></div>
      </div>
      <div class="pdf-viewer-footer">
          <button id="csvDownloadBtn" class="backup-btn download-btn">
              <i class="fa-solid fa-file-arrow-down"></i> Download
          </button>
          <button type="button" class="backup-btn close-csv-viewer">
              Close
          </button>
      </div>
  </div>
</div>

<!-- Remove or modify the existing container -->
<div id="csvPreviewContainer" style="display: none;"></div>

<script>
document.getElementById('debugPdfBtn').addEventListener('click', function() {
    // Get the first backup card with PDF
    const firstPdfCard = document.querySelector('.backup-card');
    if (firstPdfCard) {
        const backupId = firstPdfCard.querySelector('.view-btn').getAttribute('data-backup-id');
        const fileName = firstPdfCard.querySelector('.backup-name').textContent;
        
        console.log('Debug Info:', {
            backupId,
            fileName,
            viewUrl: `/backup/view/${backupId}?inline=true`,
            downloadUrl: `/backup/download/${backupId}`
        });
        
        // Test the URL
        window.open(`/debug-pdf-view/${backupId}`, '_blank');
    }
});
</script>
<script src="<?php echo e(asset('js/backup-manager.js')); ?>"></script><?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\partials\backup-manager.blade.php ENDPATH**/ ?>