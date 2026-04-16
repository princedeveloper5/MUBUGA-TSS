<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_auth.php';

if (adminIsLoggedIn()) {
    header('Location: /MUBUGA-TSS/admin/dashboard.php');
    exit;
}

$error = '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (attemptAdminLogin($email, $password)) {
        header('Location: /MUBUGA-TSS/admin/dashboard.php');
        exit;
    }

    $error = 'Invalid login details. Please try again.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mubuga TSS Admin Login</title>

    <link rel="icon" type="image/png" href="/MUBUGA-TSS/assets/images/MUBUGA%20LOGO%20SN.PNG">
    <link rel="stylesheet" href="/MUBUGA-TSS/assets/css/admin.css">
</head>

<body class="admin-login-page">

<div class="login-shell">
    <div class="login-card">
        
        <!-- LEFT SIDE - Illustration -->
        <div class="login-left">
            <div class="login-illustration">
                <!-- Abstract shapes background -->
                <div class="abstract-shape shape-1"></div>
                <div class="abstract-shape shape-2"></div>
                <div class="abstract-shape shape-3"></div>
                
                <!-- Main illustration container -->
                <div class="illustration-content">
                    <!-- Speech bubble with dots -->
                    <div class="speech-bubble">
                        <div class="speech-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                    
                    <!-- Person at desk illustration -->
                    <div class="person-illustration">
                        <div class="person">
                            <div class="head"></div>
                            <div class="body"></div>
                            <div class="arms"></div>
                        </div>
                        <div class="desk">
                            <div class="laptop"></div>
                            <div class="lamp"></div>
                            <div class="plant plant-1"></div>
                            <div class="plant plant-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE - Login Form -->
        <div class="login-right">
            <div class="login-header">
                <div class="branding-section">
                    <img src="/MUBUGA-TSS/assets/images/MUBUGA%20LOGO%20SN.PNG" alt="Mubuga TSS Logo" class="branding-logo">
                    <div class="branding-text">
                        <h2 class="branding-title">MUBUGA TSS</h2>
                        <p class="branding-subtitle">ADMIN LOGIN</p>
                    </div>
                </div>
                <h1 class="login-heading">Login</h1>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert error" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="login-form" id="loginForm">
                
                <div class="input-group">
                    <div class="input-wrapper">
                        <input 
                            type="email" 
                            name="email" 
                            id="email"
                            required 
                            autocomplete="email"
                            placeholder="Email"
                            class="login-input"
                        >
                        <div class="input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            required 
                            autocomplete="current-password"
                            placeholder="Password"
                            class="login-input"
                        >
                        <div class="input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                        </div>
                        <label class="password-toggle-container">
                            <input type="checkbox" class="password-toggle-checkbox" id="passwordToggle">
                            <span class="radio-button"></span>
                            <span class="radio-label">Show</span>
                        </label>
                    </div>
                </div>

                <div class="forgot-password">
                    <a href="#" class="forgot-link">Forgot Password?</a>
                </div>

                <div class="login-actions">
                    <button type="submit" class="login-button">
                        Login
                    </button>
                </div>
            </form>

        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.getElementById('password');
    const loginForm = document.getElementById('loginForm');
    const radioLabel = document.querySelector('.radio-label');
    
    // Password visibility toggle
    passwordToggle.addEventListener('change', function() {
        const type = this.checked ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Update label text
        radioLabel.textContent = this.checked ? 'Hide' : 'Show';
    });
    
    // Form submission feedback
    loginForm.addEventListener('submit', function() {
        const button = loginForm.querySelector('.login-button');
        button.classList.add('loading');
        button.disabled = true;
        button.textContent = 'Signing In...';
    });
    
    // Input focus effects
    const inputs = document.querySelectorAll('.login-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
});
</script>

</body>
</html>