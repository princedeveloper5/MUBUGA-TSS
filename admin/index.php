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
    <link rel="stylesheet" href="/MUBUGA-TSS/assets/css/admin.css">
</head>
<body class="admin-login-page">
    <main class="login-shell">
        <section class="login-card">
            <p class="admin-eyebrow">Mubuga TSS</p>
            <h1>Admin Login</h1>
            <p class="login-text">Use the school admin account to manage homepage content.</p>

            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" class="login-form">
                <label>
                    <span>Email</span>
                    <input type="email" name="email" required>
                </label>
                <label>
                    <span>Password</span>
                    <input type="password" name="password" required>
                </label>
                <button type="submit">Sign In</button>
            </form>

            <div class="default-note">
                <strong>Default login</strong>
                <p>Email: `admin@mubugatss.rw`</p>
                <p>Password: `admin123`</p>
            </div>
        </section>
    </main>
</body>
</html>
