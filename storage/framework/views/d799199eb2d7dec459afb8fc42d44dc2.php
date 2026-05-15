<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accepted Appointments Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background: #fff;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #0d6efd;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 13px;
            margin: 5px 0;
        }
        
        .filters {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        
        .filters h4 {
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }
        
        .filter-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }
        
        .filter-item strong {
            color: #0d6efd;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        table thead {
            background: #0d6efd;
            color: #fff;
        }
        
        table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #0d6efd;
        }
        
        table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        table tbody tr:nth-child(odd) {
            background: #f8f9fa;
        }
        
        table tbody tr:hover {
            background: #e9ecef;
        }
        
        .summary {
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 12px;
            margin-top: 20px;
            font-size: 13px;
        }
        
        .summary p {
            margin: 5px 0;
        }
        
        footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            font-size: 11px;
            color: #666;
        }
        
        .text-muted {
            color: #666;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo e($branch); ?> - Accepted Appointments Report</h1>
        <p>Generated on: <?php echo e(now()->format('F d, Y \a\t H:i:s')); ?></p>
    </div>

    <div class="filters">
        <h4>Applied Filters:</h4>
        <div class="filter-item">
            <strong>Date:</strong> <?php echo e($filterInfo['date']); ?>

        </div>
        <div class="filter-item">
            <strong>Time Slot:</strong> <?php echo e($filterInfo['time']); ?>

        </div>
        <div class="filter-item">
            <strong>Category:</strong> <?php echo e($filterInfo['category']); ?>

        </div>
        <div class="filter-item">
            <strong>Search:</strong> <?php echo e($filterInfo['search'] ?? 'No search filter'); ?>

        </div>
    </div>

    <?php if($appointments->count() > 0): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">ID</th>
                    <th style="width: 15%;">Client Name</th>
                    <th style="width: 12%;">Phone</th>
                    <th style="width: 15%;">Email</th>
                    <th style="width: 12%;">Category</th>
                    <th style="width: 12%;">Case</th>
                    <th style="width: 13%;">Date & Time</th>
                    <th style="width: 7%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($index + 1); ?></td>
                        <td><strong><?php echo e($appointment->fullname); ?></strong></td>
                        <td><?php echo e($appointment->phone); ?></td>
                        <td><?php echo e($appointment->email); ?></td>
                        <td><?php echo e($appointment->category); ?></td>
                        <td><?php echo e($appointment->case_name ?? 'N/A'); ?></td>
                        <td>
                            <?php echo e($appointment->selected_date ?? 'N/A'); ?>

                            <br>
                            <span class="text-muted"><?php echo e($appointment->selected_time ?? 'N/A'); ?></span>
                        </td>
                        <td>
                            <span class="status-approved"><?php echo e(ucfirst($appointment->appointment_approval)); ?></span>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <div class="summary">
            <p><strong>Report Summary:</strong></p>
            <p>Total Accepted Appointments: <strong><?php echo e($appointments->count()); ?></strong></p>
            <p>Report Scope: <strong><?php echo e($branch); ?></strong></p>
            <p style="margin-top: 10px; color: #999; font-size: 12px;">
                This report contains all appointments matching the applied filters.
            </p>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 30px; color: #666;">
            <p><strong>No appointments found</strong></p>
            <p>There are no accepted appointments matching the applied filters.</p>
        </div>
    <?php endif; ?>

    <footer>
        <p>LegalConnect - Accepted Appointments Report</p>
        <p>This is an automatically generated report. For more information, please contact your administrator.</p>
    </footer>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\reports\accepted_appointments_report.blade.php ENDPATH**/ ?>