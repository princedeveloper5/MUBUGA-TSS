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
    if (!adminVerifyCsrfToken((string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Your session token is invalid. Please try again.';
    } else {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (attemptAdminLogin($email, $password)) {
        header('Location: /MUBUGA-TSS/admin/dashboard.php');
        exit;
    }

    $error = 'Invalid login details. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mubuga TSS Admin Login</title>

    <link rel="icon" type="image/png" href="/MUBUGA-TSS/assets/images/MUBUGA%20LOGO%20SN.PNG">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body.admin-login-page {
            margin: 0;
            min-height: 100vh;
            background:
                linear-gradient(180deg, rgba(91, 33, 182, 0.7) 0%, rgba(109, 40, 217, 0.6) 50%, rgba(91, 33, 182, 0.8) 100%),
                url('/MUBUGA-TSS/assets/images/school view 1.jpg') center/cover no-repeat fixed;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        /* Stars overlay */
        body.admin-login-page::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(2px 2px at 20px 30px, rgba(255,255,255,0.8), transparent),
                radial-gradient(2px 2px at 40px 70px, rgba(255,255,255,0.6), transparent),
                radial-gradient(1px 1px at 90px 40px, rgba(255,255,255,0.8), transparent),
                radial-gradient(2px 2px at 130px 80px, rgba(255,255,255,0.7), transparent),
                radial-gradient(1px 1px at 160px 20px, rgba(255,255,255,0.8), transparent),
                radial-gradient(2px 2px at 200px 60px, rgba(255,255,255,0.6), transparent),
                radial-gradient(1px 1px at 250px 30px, rgba(255,255,255,0.8), transparent),
                radial-gradient(2px 2px at 300px 90px, rgba(255,255,255,0.7), transparent),
                radial-gradient(1px 1px at 350px 50px, rgba(255,255,255,0.8), transparent),
                radial-gradient(2px 2px at 400px 20px, rgba(255,255,255,0.6), transparent),
                radial-gradient(1px 1px at 450px 70px, white, transparent),
                radial-gradient(2px 2px at 500px 40px, rgba(255,255,255,0.9), transparent),
                radial-gradient(1px 1px at 550px 80px, white, transparent),
                radial-gradient(2px 2px at 600px 25px, rgba(255,255,255,0.8), transparent),
                radial-gradient(1px 1px at 650px 55px, white, transparent),
                radial-gradient(2px 2px at 700px 85px, rgba(255,255,255,0.9), transparent),
                radial-gradient(1px 1px at 750px 15px, white, transparent),
                radial-gradient(2px 2px at 800px 45px, rgba(255,255,255,0.8), transparent),
                radial-gradient(1px 1px at 850px 75px, white, transparent),
                radial-gradient(2px 2px at 900px 35px, rgba(255,255,255,0.9), transparent),
                radial-gradient(1px 1px at 950px 65px, rgba(255,255,255,0.7), transparent);
            background-size: 1000px 100px;
            opacity: 0.5;
        }

        /* Mountain silhouettes - smaller at bottom */
        body.admin-login-page::after {
            content: "";
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 120px;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M0,120 L0,80 Q60,40 120,70 T240,50 Q300,20 360,60 T480,40 Q540,10 600,55 T720,35 Q780,15 840,50 T960,30 Q1020,20 1080,55 T1200,25 L1200,120 Z' fill='%231e1b4b'/%3E%3Cpath d='M0,120 L0,100 Q80,70 160,90 T320,75 Q400,55 480,85 T640,70 Q720,50 800,80 T960,65 Q1040,45 1120,75 T1280,60 L1280,120 Z' fill='%232e1065' opacity='0.9'/%3E%3C/svg%3E");
            background-size: 100% 100%;
            background-repeat: no-repeat;
            background-position: bottom;
            z-index: 0;
        }

        .login-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .login-card {
            width: min(430px, 92%);
            padding: 36px 30px !important;
            border-radius: 22px !important;
            background: rgba(255, 255, 255, 0.12) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.1) inset !important;
            backdrop-filter: blur(30px) saturate(180%);
        }

        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-logo {
            width: 64px;
            height: 64px;
            margin: 0 auto 14px;
            border-radius: 14px;
            object-fit: contain;
            background: rgba(255,255,255,0.1);
            padding: 7px;
        }

        .login-heading {
            margin: 0;
            color: #ffffff;
            font-size: 2.2rem;
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .input-group {
            margin: 0;
            background: transparent !important;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper .login-input {
            padding-right: 48px;
        }

        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .login-input {
            height: 56px;
            padding: 0 18px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            font-size: 1rem;
            width: 100%;
            outline: none;
            transition: all 0.2s;
        }

        .login-input:focus {
            border-color: rgba(167, 139, 250, 0.8);
            background: rgba(255, 255, 255, 0.18);
        }

        .login-input::placeholder {
            color: rgba(255,255,255,0.7);
        }

        .login-meta {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 12px;
            margin-top: 4px;
            padding: 0 6px;
            font-size: 0.84rem;
        }

        .remember-check {
            display: inline-flex;
            align-items: center;
            justify-self: start;
            gap: 8px;
            cursor: pointer;
            color: rgba(255,255,255,0.9);
            font-weight: 500;
        }

        .remember-check input {
            width: 14px;
            height: 14px;
            accent-color: #a78bfa;
            cursor: pointer;
        }

        .forgot-link {
            justify-self: end;
            color: rgba(255,255,255,0.92);
            text-decoration: none;
            font-size: 0.84rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .login-button {
            width: 100% !important;
            height: 54px !important;
            border-radius: 999px !important;
            background: #ffffff !important;
            color: #581c87 !important;
            border: none !important;
            font-size: 1rem !important;
            font-weight: 700 !important;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 10px;
        }

        .login-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .register-link {
            text-align: center;
            margin-top: 16px;
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
        }

        .register-link a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .alert.error {
            margin-bottom: 16px;
            padding: 12px 16px;
            border-radius: 12px;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ffffff;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .login-shell {
                padding: 16px;
            }

            .login-card {
                padding: 28px 20px !important;
                border-radius: 18px !important;
            }

            .login-heading {
                font-size: 1.8rem;
            }

            .login-meta {
                grid-template-columns: 1fr;
                justify-items: start;
                gap: 10px;
                padding: 0;
            }

            .forgot-link {
                justify-self: start;
            }
        }
    </style>
</head>

<body class="admin-login-page">

<div class="login-shell">
    <div class="login-card">
        <div class="login-header">
            <img src="/MUBUGA-TSS/assets/images/MUBUGA%20LOGO%20SN.PNG" alt="Mubuga TSS" class="login-logo">
            <h1 class="login-heading">Login</h1>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert error" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="login-form" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">

            <div class="input-group">
                <div class="input-wrapper">
                    <input type="email" name="email" id="email" required autocomplete="email" placeholder="Email" class="login-input">
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16v12H4z"/>
                            <path d="m4 8 8 6 8-6"/>
                        </svg>
                    </span>
                </div>
            </div>

            <div class="input-group">
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" required autocomplete="current-password" placeholder="Password" class="login-input">
                    <span class="input-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                </div>
            </div>

            <div class="login-meta">
                <label class="remember-check">
                    <input type="checkbox" name="remember_me">
                    <span>Remember me</span>
                </label>
                <span class="forgot-link">Forgot password?</span>
            </div>

            <button type="submit" class="login-button">
                Login
        </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const loginForm = document.getElementById('loginForm');

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

<script src="/MUBUGA-TSS/assets/js/photo-viewer.js"></script>

</body>
</html>
