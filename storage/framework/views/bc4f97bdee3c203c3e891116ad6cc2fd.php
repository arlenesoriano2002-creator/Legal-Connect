<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Feedback Report - <?php echo e(date('Y-m-d')); ?></title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        
        .header .subtitle {
            color: #666;
            margin-top: 5px;
            font-size: 14px;
        }
        
        .report-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .statistics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .rating-distribution {
            margin-bottom: 30px;
        }
        
        .distribution-item {
            margin-bottom: 10px;
        }
        
        .distribution-label {
            display: inline-block;
            width: 120px;
            font-weight: bold;
        }
        
        .distribution-bar {
            display: inline-block;
            height: 15px;
            background: #007bff;
            margin-left: 10px;
            vertical-align: middle;
        }
        
        .distribution-count {
            display: inline-block;
            margin-left: 10px;
            font-weight: bold;
        }
        
        .review-section {
            margin-top: 30px;
        }
        
        .review-card {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .reviewer-info {
            flex: 1;
        }
        
        .reviewer-name {
            font-weight: bold;
            font-size: 14px;
        }
        
        .reviewer-email {
            color: #666;
            font-size: 11px;
        }
        
        .review-rating {
            text-align: right;
            font-weight: bold;
            color: #ff9800;
        }
        
        .review-content {
            margin-top: 10px;
            font-size: 12px;
            line-height: 1.5;
        }
        
        .review-date {
            color: #999;
            font-size: 11px;
            text-align: right;
            margin-top: 10px;
        }
        
        .star {
            color: #FFD700;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            color: #666;
            font-size: 10px;
        }
        
        .filters {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        .filters strong {
            color: #495057;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .rating-summary {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-weight: bold;
        }
        
        .summary-value {
            font-weight: bold;
            color: #007bff;
        }
        
        .report-meta {
            text-align: right;
            font-size: 10px;
            color: #666;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="report-meta">
        Generated: <?php echo e($filters['generated_at'] ?? now()->format('Y-m-d H:i:s')); ?>

    </div>
    
    <div class="header">
        <h1>LegalConnect Feedback Report</h1>
        <div class="subtitle">
            Comprehensive Feedback Analysis Report
        </div>
    </div>

    <div class="report-info">
        <h3>Report Summary</h3>
        <p>This report contains feedback analysis for LegalConnect platform based on user reviews and ratings.</p>
    </div>

    <!-- Applied Filters -->
    <div class="filters">
        <h4>Applied Filters:</h4>
        <div class="summary-row">
            <span><strong>Rating:</strong></span>
            <span>
                <?php if(($filters['rating'] ?? 'all') == 'all' || !($filters['rating'] ?? '')): ?>
                    All Ratings
                <?php elseif($filters['rating'] == '4-5'): ?>
                    4-5 Stars
                <?php elseif($filters['rating'] == '1-3'): ?>
                    1-3 Stars
                <?php else: ?>
                    <?php echo e($filters['rating'] ?? ''); ?> Star(s)
                <?php endif; ?>
            </span>
        </div>
        <div class="summary-row">
            <span><strong>Date Range:</strong></span>
            <span>
                <?php echo e($filters['startDate'] ?? 'Start Date'); ?> 
                to 
                <?php echo e($filters['endDate'] ?? 'End Date'); ?>

            </span>
        </div>
        <div class="summary-row">
            <span><strong>Search:</strong></span>
            <span><?php echo e($filters['search'] ?? 'None'); ?></span>
        </div>
    </div>

    <!-- Statistics -->
    <div class="rating-summary">
        <h4>Quick Statistics</h4>
        <div class="summary-row">
            <span class="summary-label">Total Reviews:</span>
            <span class="summary-value"><?php echo e($stats['total_reviews'] ?? 0); ?></span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Average Rating:</span>
            <span class="summary-value"><?php echo e($stats['average_rating'] ?? 0); ?>/5</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Positive Reviews (4-5★):</span>
            <span class="summary-value"><?php echo e($stats['positive_reviews'] ?? 0); ?></span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Negative Reviews (1-2★):</span>
            <span class="summary-value"><?php echo e($stats['negative_reviews'] ?? 0); ?></span>
        </div>
    </div>

    <!-- Detailed Statistics -->
    <div class="statistics-grid">
        <div class="stat-box">
            <div class="stat-label">Total Reviews</div>
            <div class="stat-value"><?php echo e($stats['total_reviews'] ?? 0); ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Average Rating</div>
            <div class="stat-value"><?php echo e($stats['average_rating'] ?? 0); ?>/5</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Positive Reviews</div>
            <div class="stat-value"><?php echo e($stats['positive_reviews'] ?? 0); ?></div>
            <div class="stat-label">(4-5 Stars)</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Negative Reviews</div>
            <div class="stat-value"><?php echo e($stats['negative_reviews'] ?? 0); ?></div>
            <div class="stat-label">(1-2 Stars)</div>
        </div>
    </div>

    <!-- Rating Distribution -->
    <div class="rating-distribution">
        <h3>Rating Distribution</h3>
        <?php for($i = 5; $i >= 1; $i--): ?>
        <div class="distribution-item">
            <span class="distribution-label">
                <?php for($j = 0; $j < $i; $j++): ?><span class="star">★</span><?php endfor; ?>
                (<?php echo e($i); ?> Stars)
            </span>
            <?php
                $count = $stats['rating_distribution'][$i] ?? 0;
                $percentage = ($stats['total_reviews'] ?? 0) > 0 
                    ? ($count / ($stats['total_reviews'] ?? 1)) * 100 
                    : 0;
                $barWidth = $percentage * 1.5; // Scale for visual representation
                $color = match($i) {
                    5 => '#28a745',
                    4 => '#17a2b8',
                    3 => '#ffc107',
                    2 => '#fd7e14',
                    default => '#dc3545'
                };
            ?>
            <div class="distribution-bar" style="width: <?php echo e($barWidth); ?>px; background-color: <?php echo e($color); ?>;"></div>
            <span class="distribution-count"><?php echo e($count); ?> (<?php echo e(number_format($percentage, 1)); ?>%)</span>
        </div>
        <?php endfor; ?>
    </div>

    <!-- Detailed Reviews Table -->
    <div class="review-section">
        <h3>Detailed Reviews (<?php echo e(count($reviews)); ?>)</h3>
        
        <?php if(count($reviews) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Reviewer</th>
                        <th style="width: 20%;">Email</th>
                        <th style="width: 10%;">Rating</th>
                        <th style="width: 40%;">Review</th>
                        <th style="width: 15%;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($index > 0 && $index % 15 == 0): ?>
                    </tbody>
                    </table>
                    <div class="page-break"></div>
                    <h4>Detailed Reviews (Continued)</h4>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 15%;">Reviewer</th>
                                <th style="width: 20%;">Email</th>
                                <th style="width: 10%;">Rating</th>
                                <th style="width: 40%;">Review</th>
                                <th style="width: 15%;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php endif; ?>
                    <tr>
                        <td><?php echo e($review->name); ?></td>
                        <td><?php echo e($review->email); ?></td>
                        <td>
                            <?php for($k = 0; $k < $review->rating; $k++): ?>★<?php endfor; ?>
                            (<?php echo e($review->rating); ?>)
                        </td>
                        <td><?php echo e(\Illuminate\Support\Str::limit($review->review, 100)); ?></td>
                        <td><?php echo e($review->created_at->format('Y-m-d')); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No reviews found with the selected filters.</p>
        <?php endif; ?>
    </div>

    <!-- Full Reviews (Detailed) -->
    <?php if(count($reviews) > 0): ?>
    <div class="page-break"></div>
    <h3>Complete Review Details</h3>
    
    <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($index > 0 && $index % 3 == 0): ?>
            <div class="page-break"></div>
        <?php endif; ?>
        
        <div class="review-card">
            <div class="review-header">
                <div class="reviewer-info">
                    <div class="reviewer-name"><?php echo e($review->name); ?></div>
                    <div class="reviewer-email"><?php echo e($review->email); ?></div>
                </div>
                <div class="review-rating">
                    <?php for($k = 0; $k < $review->rating; $k++): ?><span class="star">★</span><?php endfor; ?>
                    <span>(<?php echo e($review->rating); ?> / 5)</span>
                </div>
            </div>
            
            <div class="review-content">
                <?php echo e($review->review); ?>

            </div>
            
            <div class="review-date">
                Submitted: <?php echo e($review->created_at->format('F d, Y h:i A')); ?>

            </div>
            
            <?php if($review->image): ?>
            <div style="margin-top: 10px; font-size: 10px; color: #666;">
                <strong>Image Attachment:</strong> Available
            </div>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    <div class="footer">
        <p>LegalConnect Feedback Report | Generated by: <?php echo e(Auth::user()->name ?? 'System'); ?></p>
        <p>Confidential - For internal use only</p>
        <p>Page <span class="pageNumber"></span> of <span class="totalPages"></span></p>
    </div>
</body>
</html><?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\pdf\feedback-report.blade.php ENDPATH**/ ?>