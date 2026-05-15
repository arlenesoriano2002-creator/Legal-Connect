// Function to generate and send verification code
function sendVerificationCode(email, phone) {
    const verificationCode = Math.floor(1000 + Math.random() * 9000); // Generate 4-digit code

    // Save the code temporarily (for demo purposes, use a secure backend in production)
    sessionStorage.setItem('verificationCode', verificationCode);

    // Send the code via email
    fetch('/send-verification-email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ email, code: verificationCode })
    });

    // Send the code via SMS
    fetch('/send-verification-sms', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ phone, code: verificationCode })
    });
}

// Event listener for Create/Update buttons
function handleVerification(email, phone) {
    sendVerificationCode(email, phone);
    const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
    verificationModal.show();
}

// Verify the code entered by the user
document.getElementById('verifyCodeBtn').addEventListener('click', function () {
    const enteredCode = document.getElementById('verificationCode').value;
    const storedCode = sessionStorage.getItem('verificationCode');

    if (enteredCode === storedCode) {
        sessionStorage.removeItem('verificationCode'); // Clear the code
        document.getElementById('verificationError').classList.add('d-none');
        // Proceed with form submission
        document.getElementById('createStaffForm').submit();
    } else {
        document.getElementById('verificationError').classList.remove('d-none');
    }
});