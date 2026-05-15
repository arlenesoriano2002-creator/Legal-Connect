



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Legal Connect'); ?></title>

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    
    <?php echo $__env->yieldContent('styles'); ?>

    
    <?php echo $__env->make('partials.global-error-handler', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</head>
<body>
    
    <?php echo $__env->yieldContent('nav'); ?>

    
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    
    <?php echo $__env->yieldContent('footer'); ?>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\Legal connect final\LegalConnect\resources\views\layouts\app.blade.php ENDPATH**/ ?>