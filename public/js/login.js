/**
 * Login Form Helper
 * 
 * Provides simple form utilities for the login page.
 * SPA login behavior is handled by spa-login-handler.js
 * History management is handled by spa-navigation-manager.js
 */

/**
 * Toggle password visibility
 */
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeOpen = document.getElementById('eye-open');
    const eyeClosed = document.getElementById('eye-closed');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'block';
    } else {
        passwordInput.type = 'password';
        eyeOpen.style.display = 'block';
        eyeClosed.style.display = 'none';
    }
}

console.log('Login form helper loaded - SPA behavior delegated to spa-login-handler.js');