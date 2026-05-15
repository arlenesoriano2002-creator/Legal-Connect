 // Image preview and compression
        function previewImage(input, previewId, sizeInfoId) {
            const file = input.files[0];
            const preview = document.getElementById(previewId);
            const sizeInfo = document.getElementById(sizeInfoId);
            
            if (!file) {
                preview.src = '';
                preview.style.display = 'none';
                sizeInfo.textContent = '';
                return;
            }
            
            // Show file size info
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            sizeInfo.textContent = `File size: ${fileSizeMB} MB`;
            
            // Check if file is too large
            if (file.size > 2 * 1024 * 1024) { // 2MB in bytes
                sizeInfo.textContent += ' - Compressing...';
                sizeInfo.style.color = 'orange';
                
                // Compress image
                compressImage(file, 0.7, 1024, function(compressedFile) {
                    // Create a new FileList to replace the original file
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(compressedFile);
                    input.files = dataTransfer.files;
                    
                    // Update size info
                    const compressedSizeMB = (compressedFile.size / (1024 * 1024)).toFixed(2);
                    sizeInfo.textContent = `File size: ${compressedSizeMB} MB (compressed)`;
                    sizeInfo.style.color = compressedSizeMB > 2 ? 'red' : 'green';
                    
                    // Preview the compressed image
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(compressedFile);
                });
            } else {
                sizeInfo.style.color = fileSizeMB > 2 ? 'red' : 'green';
                
                // Preview the original image
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        // Image compression function
        function compressImage(file, quality, maxWidth, callback) {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function(event) {
                const img = new Image();
                img.src = event.target.result;
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    
                    // Calculate new dimensions
                    if (width > maxWidth) {
                        height = (height * maxWidth) / width;
                        width = maxWidth;
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    // Convert to compressed Blob
                    canvas.toBlob(function(blob) {
                        const compressedFile = new File([blob], file.name, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                        callback(compressedFile);
                    }, 'image/jpeg', quality);
                };
            };
        }

        // Event listeners for file inputs
        document.getElementById('id_front').addEventListener('change', function(e) {
            previewImage(e.target, 'preview_front', 'frontSizeInfo');
        });

        document.getElementById('id_back').addEventListener('change', function(e) {
            previewImage(e.target, 'preview_back', 'backSizeInfo');
        });

        function resetImage(inputId, previewId) {
            document.getElementById(inputId).value = '';
            document.getElementById(previewId).src = '';
            document.getElementById(previewId).style.display = 'none';
            
            // Clear size info
            if (inputId === 'id_front') {
                document.getElementById('frontSizeInfo').textContent = '';
            } else {
                document.getElementById('backSizeInfo').textContent = '';
            }
        }

        // Back button: unbook slot then go back
        function cancelAndBack(){
            const date = "{{ $date }}";
            const time = "{{ $time }}";

            fetch('/unbook-slot', {
                method: 'POST',
                headers: {
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':'{{ csrf_token() }}'
                },
                body: JSON.stringify({date, time})
            }).finally(() => window.location.href = backUrl);
        }


        // Form validation
        document.getElementById('finalizeForm').addEventListener('submit', function(e) {
            const frontFile = document.getElementById('id_front').files[0];
            const backFile = document.getElementById('id_back').files[0];
            const submitBtn = document.getElementById('submitBtn');
            
            // Check file sizes
            if (frontFile && frontFile.size > 2 * 1024 * 1024) {
                e.preventDefault();
                alert('Front image is still too large after compression. Please choose a smaller image.');
                return;
            }
            
            if (backFile && backFile.size > 2 * 1024 * 1024) {
                e.preventDefault();
                alert('Back image is still too large after compression. Please choose a smaller image.');
                return;
            }
            
            // Disable button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
        });

       document.getElementById('finalizeForm').addEventListener('submit', function(e) {

            const branchTextEl = document.getElementById('selectedBranchText');
            const branchInput = document.getElementById('selected_branch');

            if (branchTextEl && branchInput) {
                branchInput.value = branchTextEl.textContent.trim();
                console.log('Submitting selected_branch:', branchInput.value);
            }
        });


        