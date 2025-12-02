document.addEventListener('DOMContentLoaded', function() {
    const acceptCheckbox = document.getElementById('acceptTerms');
    const acceptButton = document.getElementById('acceptButton');
    const errorMessage = document.getElementById('errorMessage');
    const termsForm = document.getElementById('termsForm');
    const termsContent = document.getElementById('termsContent');
    const scrollIndicator = document.getElementById('scrollIndicator');

    // Show scroll indicator initially
    scrollIndicator.style.display = 'block';

    // Function to check if user has scrolled to the bottom of the terms
    function checkScroll() {
        const isBottom = termsContent.scrollHeight - termsContent.scrollTop <= termsContent.clientHeight + 5;
        
        if (isBottom) {
            acceptCheckbox.disabled = false;
            scrollIndicator.style.display = 'none';
        } else {
            acceptCheckbox.disabled = true;
            acceptCheckbox.checked = false;
            acceptButton.disabled = true;
            scrollIndicator.style.display = 'block';
        }
    }

    // Add scroll event listener to terms content
    termsContent.addEventListener('scroll', checkScroll);

    // Checkbox change event
    acceptCheckbox.addEventListener('change', function() {
        acceptButton.disabled = !this.checked;
    });

    // Form submission
    termsForm.addEventListener('submit', function(e) {
        if (!acceptCheckbox.checked) {
            e.preventDefault();
            errorMessage.style.display = 'block';
            errorMessage.scrollIntoView({ behavior: 'smooth' });
        }
    });

    // Also check scroll on page load in case content is short
    checkScroll();
});