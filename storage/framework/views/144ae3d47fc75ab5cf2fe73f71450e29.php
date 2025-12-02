<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" href="<?php echo e(asset('KG2025 (2).png')); ?>" type="image/png">
  <link rel="stylesheet" href="<?php echo e(asset('css/appointment1.blade.css')); ?>">
  <title>Appointment Form</title>
</head>
<body>
  <div class="container" role="main">
    <div class="left">
      <div class="form-header">
        <div class="img-header">
          <img src="<?php echo e(asset('logo6.png')); ?>" alt="Legal Council logo" class="logo" width="50" height="50" />
        </div>
        <div class="title-header">
          <h1>LEGAL CONNECT</h1>
          <p>Your Trusted Legal Partner</p>
        </div>
      </div>
      
      <?php if($errors->any()): ?>
        <div class="alert alert-error">
          <ul>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </ul>
        </div>
      <?php endif; ?>

     <form method="POST" action="<?php echo e(route('appointment.storeStep1')); ?>">
        <?php echo csrf_field(); ?>

        <div class="form-appointment">
          <div class="personal-info">
            <h2>Personal Information</h2>
            
            <label for="fullname">Full name</label>
            <input type="text" id="fullname" name="fullname" value="<?php echo e(old('fullname', $fullname)); ?>" required />

            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo e(old('address', $address)); ?>" required />

            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" value="<?php echo e(old('phone', $phone)); ?>" required />

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo e(old('email', $email)); ?>" required />
          </div>

          <div class="categories-case">
            <h2>Select Your Case</h2>
            
            <!-- Category Selection -->
            <label for="category">Category</label>
            <select id="category" name="category" required>
              <option value="">Select Category</option>
              <?php $__currentLoopData = $caseCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($category->category); ?>" <?php echo e(old('category') == $category->category ? 'selected' : ''); ?>>
                  <?php echo e($category->category); ?>

                </option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <!-- Case Type Selection (Dynamic) -->
            <label for="case_id">Case Type</label>
            <select id="case_id" name="case_id" required>
              <option value="">Please select a category first</option>
              <!-- Dynamic options will be loaded here via JavaScript -->
            </select>
          </div>
        </div>

        <div class="buttons">
          <button type="button" onclick="window.location.href='<?php echo e(route('Terms')); ?>'">Back</button>
          <button type="submit" class="btn btn-primary1">Next</button>
      </div>
      </form>
    </div>
  </div>

  <script>
    // Name validation
    const fullNameInput = document.getElementById('fullname');
    fullNameInput.addEventListener('input', function() {
        let value = this.value.replace(/[^a-zA-Z\s,\.]/g, '');
        if (value.length > 0) {
            value = value.charAt(0).toUpperCase() + value.slice(1);
        }
        this.value = value;
    });

    // Phone: only numbers
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Case data from PHP to JavaScript
    const caseData = <?php echo json_encode($caseCategories->map(function($category) {
        return [
            'category' => $category->category,
            'cases' => $category->cases->map(function($case) {
                return [
                    'id' => $case->id,
                    'case_name' => $case->case_name
                ];
            })
        ];
    })); ?>;

    // Function to update case types based on selected category
    function updateCaseTypes() {
        const categorySelect = document.getElementById('category');
        const caseTypeSelect = document.getElementById('case_id');
        const selectedCategory = categorySelect.value;
        
        // Clear current options
        caseTypeSelect.innerHTML = '<option value="">Select Case Type</option>';
        
        if (selectedCategory) {
            // Find the selected category in our caseData
            const category = caseData.find(cat => cat.category === selectedCategory);
            
            if (category && category.cases.length > 0) {
                // Add case options for the selected category
                category.cases.forEach(caseItem => {
                    const option = document.createElement('option');
                    option.value = caseItem.id;
                    option.textContent = caseItem.case_name;
                    caseTypeSelect.appendChild(option);
                });
                
                // If there's a previously selected case_id, try to select it
                const oldCaseId = "<?php echo e(old('case_id')); ?>";
                if (oldCaseId) {
                    caseTypeSelect.value = oldCaseId;
                }
            } else {
                caseTypeSelect.innerHTML = '<option value="">No cases available for this category</option>';
            }
        } else {
            caseTypeSelect.innerHTML = '<option value="">Please select a category first</option>';
        }
    }

    // Event listener for category change
    document.getElementById('category').addEventListener('change', updateCaseTypes);

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // If there's a previously selected category, update case types
        const oldCategory = "<?php echo e(old('category')); ?>";
        if (oldCategory) {
            updateCaseTypes();
        }
        
        // Also update if category is already selected (from session or form submission)
        const currentCategory = document.getElementById('category').value;
        if (currentCategory) {
            updateCaseTypes();
        }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const category = document.getElementById('category').value;
        const caseId = document.getElementById('case_id').value;
        
        if (!category || !caseId) {
            e.preventDefault();
            alert('Please select both category and case type.');
            return;
        }

        console.log('Form submitted with:', {
            category: category,
            case_id: caseId
        });
    });
  </script>
</body>
</html><?php /**PATH D:\xampp\htdocs\LEGAL CONNECT\resources\views/appointment1.blade.php ENDPATH**/ ?>