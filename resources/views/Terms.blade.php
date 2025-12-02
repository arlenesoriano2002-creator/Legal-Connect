<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/TermsOfService.blade.css') }}">
    <title>Terms of Service</title>
</head>
<body>
    <div class="container" role="main" aria-label="Terms of Service">
        <div class="header">
            <img src="logo6.png" alt="Document icon" class="icon" />
            <h1>TERMS OF SERVICE & PRIVACY POLICY</h1>
        </div>
        
        <div class="content">
            <div class="error-message" id="errorMessage" style="display: none;">
                You must accept the terms to continue.
            </div>
            <p>By using Legal Connect, you agree to our Terms of Service and Privacy Policy. These terms outline how we collect, use, and protect your information, as well as your rights and responsibilities.</p>
            
            <div class="terms-content" id="termsContent">
                <h2>Full Terms of Service & Privacy Policy</h2>
                <h4>1. About Legal Connect</h4>
                <p>Legal Connect helps clients, lawyers, and staff manage appointments securely and efficiently.</p>

                <h4>2. Your Agreement</h4>
                <p>By using the system, you agree to use it responsibly and provide true information. Misuse is not allowed.</p>

                <h4>3. What We Collect</h4>
                <p>We collect only necessary data such as your name, contact info, appointment details, and uploaded IDs.</p>

                <h4>4. How We Use Your Information</h4>
                <p>Your data is used only to manage appointments, verify accounts, and improve system performance.</p>

                <h4>5. Data Security</h4>
                <p>All information is encrypted, protected, and accessible only to authorized personnel.</p>

                <h4>6. Appointment Policies</h4>
                <p>Clients may request, cancel, or reschedule appointments. Lawyers and admins may approve or modify requests.</p>

                <h4>7. Confidentiality</h4>
                <p>All consultations and records are confidential and follow legal and ethical guidelines.</p>

                <h4>8. Your Rights</h4>
                <p>You may view, update, or delete your data anytime. Contact the administrator for assistance.</p>

                <h4>9. System Maintenance</h4>
                <p>Legal Connect may occasionally go offline for updates or maintenance.</p>

                <h4>10. Limitation of Liability</h4>
                <p>Legal Connect is provided "as is" and not liable for technical issues or user input errors.</p>

                <h4>11. Updates to Terms</h4>
                <p>We may update this page when policies change. Continued use means you accept the new terms.</p>

                <h4>12. Contact Us</h4>
                <p>For questions or privacy concerns, email or call the Legal Connect administrator.</p>

                <h4>13. User Consent</h4>
                <p>By using this system, you confirm that you understand and agree to our Terms and Privacy Policy.</p>

                <h4>14. ID Submission</h4>
                <p>Users are required to submit valid ID photos for verification purposes to ensure account security and compliance with legal standards.</p>
            </div>
            
            <div class="scroll-indicator" id="scrollIndicator">
                Please read and scroll to the bottom of the terms to enable the checkbox
            </div>

            <form method="POST" action="{{ route('terms.accept') }}" id="termsForm">
                @csrf
                <div class="terms-checkbox">
                    <input type="checkbox" id="acceptTerms" name="acceptTerms" value="1" disabled>
                    <label for="acceptTerms">I have read and agree to the Terms of Service</label>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-accept" id="acceptButton" disabled>Accept</button>
                    <button class="btn btn-decline" type="button" onclick="window.location.href='{{ route('welcome') }}'">BACK</button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/terms.js') }}"></script>
</body>
</html>