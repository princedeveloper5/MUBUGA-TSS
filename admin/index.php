<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_auth.php';

// If already logged in, redirect to dashboard
if (adminIsLoggedIn()) {
    header('Location: /MUBUGA-TSS/admin/dashboard.php');
    exit;
}

// Handle login submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } elseif (attemptAdminLogin($email, $password)) {
        header('Location: /MUBUGA-TSS/admin/dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Mubuga TSS</title>
    <link rel="stylesheet" href="/MUBUGA-TSS/assets/css/admin.css">
</head>
<body class="admin-login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Admin Login</h1>
                <p>Mubuga Technical Secondary School</p>
            </div>
            
            <?php if ($error !== ''): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" class="login-btn">Login</button>
            </form>
            
            <div class="login-footer">
                <p>Access restricted to authorized administrators only.</p>
            </div>
        </div>
    </div>
</body>
</html>
