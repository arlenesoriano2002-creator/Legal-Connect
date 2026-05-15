<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Walk-in Logs Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #2c3e50; }
        .header .subtitle { color: #7f8c8d; font-size: 12pt; margin: 5px 0 15px 0; }
        .filter-info { 
            background-color: #f8f9fa; 
            padding: 10px; 
            margin-bottom: 15px;
            border-left: 4px solid #3498db;
            font-size: 9pt;
        }
        .filter-info strong { color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #2c3e50; color: white; text-align: left; padding: 8px; font-weight: bold; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .footer { margin-top: 30px; text-align: center; font-size: 9pt; color: #7f8c8d; }
        .page-break { page-break-before: always; }
        .no-data { text-align: center; padding: 40px; color: #7f8c8d; font-style: italic; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LegalConnect - Walk-in Logs</h1>
        <div class="subtitle">Cordon Branch Office</div>
        <div>Generated on: <?php echo e(date('Y-m-d h:i A')); ?></div>
    </div>
    
    <?php if(!empty($filterDetails)): ?>
    <div class="filter-info">
        <strong>Filter Applied:</strong>
        <?php if(isset($filterDetails['search'])): ?>
            <span>Search: "<?php echo e($filterDetails['search']); ?>"</span>
        <?php endif; ?>
        <?php if(isset($filterDetails['purpose'])): ?>
            <span>Purpose: <?php echo e($filterDetails['purpose']); ?></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php if($walkins->isEmpty()): ?>
        <div class="no-data">
            No walk-in records found with the current filters.
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>FULL NAME</th>
                    <th>ADDRESS</th>
                    <th>CONTACT</th>
                    <th>PURPOSE</th>
                    <th>BRANCH</th>
                    <th>DATE & TIME</th>
                    <th>CREATED</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $walkins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $walkin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($walkin->fullname); ?></td>
                    <td><?php echo e($walkin->address); ?></td>
                    <td><?php echo e($walkin->contact_number ?? 'N/A'); ?></td>
                    <td><?php echo e($walkin->purpose); ?></td>
                    <td><?php echo e($walkin->branch ?? 'Cordon Branch Office'); ?></td>
                    <td>
                        <?php if($walkin->date_time): ?>
                            <?php echo e(date('Y-m-d g:i A', strtotime($walkin->date_time))); ?>

                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td><?php echo e(date('m/d/Y', strtotime($walkin->created_at))); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        
        <div class="footer">
            Total Records: <?php echo e($walkins->count()); ?> | Page 1 of 1
        </div>
    <?php endif; ?>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\pdf\CordonWalkins.blade.php ENDPATH**/ ?>