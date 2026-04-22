<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_auth.php';

if (adminIsLoggedIn()) {
    header('Location: /MUBUGA-TSS/backend/admin/dashboard.php');
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
            header('Location: /MUBUGA-TSS/backend/admin/dashboard.php');
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --primary: #3d73d9;
            --primary-dark: #2e61c4;
            --text-main: #465379;
            --text-soft: #727a95;
            --field-border: #dfe6f2;
            --card-bg: rgba(255, 255, 255, 0.96);
            --card-shadow: 0 28px 80px rgba(44, 62, 106, 0.18);
        }

        html,
        body {
            min-height: 100%;
        }

        body.admin-login-page {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background:
                linear-gradient(rgba(245, 249, 255, 0.16), rgba(245, 249, 255, 0.26)),
                url('/MUBUGA-TSS/assets/images/school view 1.jpg') center center / cover no-repeat fixed;
            position: relative;
            overflow-x: hidden;
        }

        body.admin-login-page::before {
            content: "";
            position: fixed;
            inset: 0;
            backdrop-filter: blur(8px);
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.5), transparent 28%),
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.35), transparent 24%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(236, 243, 255, 0.18));
            pointer-events: none;
        }

        .login-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px 18px;
            position: relative;
            z-index: 1;
        }

        .login-card {
            width: min(1080px, 100%);
            position: relative;
            display: grid;
            grid-template-columns: minmax(280px, 0.92fr) minmax(320px, 1.08fr);
            border-radius: 30px;
            background: var(--card-bg);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .login-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 50%;
            width: 132px;
            height: 38px;
            transform: translateX(-50%);
            background: var(--card-bg);
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .login-showcase {
            position: relative;
            display: grid;
            gap: 22px;
            padding: 42px 34px 34px;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.18), transparent 34%),
                linear-gradient(160deg, rgba(15, 44, 83, 0.96), rgba(34, 92, 151, 0.92));
            color: #ffffff;
        }

        .login-showcase::after {
            content: "";
            position: absolute;
            inset: auto -70px -70px auto;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.18), transparent 66%);
            pointer-events: none;
        }

        .login-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            min-height: 34px;
            padding: 0 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .login-showcase-copy h1 {
            margin: 0 0 12px;
            font-size: clamp(2.1rem, 4vw, 3.5rem);
            line-height: 0.96;
            letter-spacing: -0.05em;
        }

        .login-showcase-copy p {
            margin: 0;
            max-width: 30rem;
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.98rem;
            line-height: 1.7;
        }

        .login-showcase-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .login-showcase-card {
            display: grid;
            gap: 8px;
            padding: 18px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(10px);
        }

        .login-showcase-card strong {
            font-size: 1.05rem;
            line-height: 1.2;
        }

        .login-showcase-card span {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.84rem;
            line-height: 1.55;
        }

        .login-showcase-footer {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .login-showcase-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 38px;
            padding: 0 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.14);
            color: rgba(255, 255, 255, 0.84);
            font-size: 0.8rem;
            font-weight: 600;
        }

        .login-panel {
            position: relative;
            padding: 64px 26px 0;
        }

        .login-header {
            text-align: center;
            margin-bottom: 18px;
        }

        .login-logo {
            width: 58px;
            height: 58px;
            margin: -30px auto 10px;
            border-radius: 15px;
            object-fit: contain;
            background: #ffffff;
            padding: 6px;
            box-shadow: 0 16px 34px rgba(61, 115, 217, 0.16);
            position: relative;
            z-index: 1;
        }

        .login-heading {
            margin: 0;
            color: var(--primary);
            font-size: clamp(1.55rem, 3.2vw, 2.25rem);
            line-height: 1.04;
            letter-spacing: -0.03em;
            font-weight: 800;
        }

        .login-subtitle {
            margin: 6px 0 0;
            color: var(--text-soft);
            font-size: 0.88rem;
            line-height: 1.35;
        }

        .login-highlights {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin: 16px 0 2px;
        }

        .login-highlight {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(239, 245, 255, 0.95);
            border: 1px solid rgba(61, 115, 217, 0.12);
            color: #587197;
            font-size: 0.77rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .alert.error {
            margin: 0 0 10px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid #f5c9c9;
            background: #fff2f2;
            color: #bf4343;
            font-size: 0.82rem;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            min-height: 54px;
            border: 2px solid var(--field-border);
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 8px 18px rgba(57, 76, 122, 0.04);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .input-wrapper.focused {
            border-color: #b6caef;
            box-shadow: 0 0 0 5px rgba(61, 115, 217, 0.08);
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #7580a0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .login-input {
            width: 100%;
            height: 50px;
            border: none;
            outline: none;
            background: transparent;
            color: var(--text-main);
            font-size: 0.9rem;
            font-weight: 600;
            padding: 0 16px 0 52px;
        }

        .login-input::placeholder {
            color: #6e7692;
            font-weight: 500;
        }

        .password-field .login-input {
            padding-right: 70px;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            border-radius: 12px;
            background: #f2f4fb;
            color: #707896;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 6px 10px;
            cursor: pointer;
        }

        .login-meta {
            display: flex;
            justify-content: flex-end;
            margin: 0 2px 2px;
        }

        .forgot-link {
            color: #6e7591;
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: none;
        }

        .login-button {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(180deg, #4f88ea 0%, #2f67cc 100%);
            color: #ffffff;
            font-size: 0.92rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(47, 103, 204, 0.28);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 34px rgba(47, 103, 204, 0.34);
        }

        .login-button:disabled {
            cursor: wait;
            opacity: 0.92;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-around;
            gap: 10px;
            margin: 14px -26px 0;
            padding: 12px 20px 14px;
            background: linear-gradient(180deg, #f8f9fd, #f2f5fb);
            border-top: 1px solid #e8edf7;
        }

        .footer-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #66708f;
            text-decoration: none;
            font-size: 0.84rem;
            font-weight: 500;
        }

        .footer-link svg {
            width: 20px;
            height: 20px;
        }

        @media (max-width: 920px) {
            .login-card {
                grid-template-columns: 1fr;
            }

            .login-showcase {
                padding: 36px 24px 26px;
            }

            .login-panel {
                padding-top: 30px;
            }
        }

        @media (max-width: 640px) {
            .login-shell {
                padding: 18px 12px;
            }

            .login-card {
                border-radius: 22px;
            }

            .login-card::before {
                width: 110px;
                height: 34px;
            }

            .login-showcase {
                padding: 28px 18px 22px;
                gap: 18px;
            }

            .login-showcase-grid {
                grid-template-columns: 1fr;
            }

            .login-panel {
                padding: 26px 14px 0;
            }

            .login-logo {
                width: 52px;
                height: 52px;
                margin-top: -26px;
            }

            .login-heading {
                font-size: 1.65rem;
            }

            .login-subtitle {
                font-size: 0.82rem;
            }

            .login-highlights {
                margin-top: 14px;
            }

            .input-wrapper {
                min-height: 50px;
            }

            .login-input {
                height: 46px;
                font-size: 0.86rem;
                padding-left: 48px;
            }

            .password-field .login-input {
                padding-right: 66px;
            }

            .input-icon {
                left: 13px;
            }

            .password-toggle {
                right: 8px;
                padding: 5px 8px;
            }

            .login-button {
                height: 48px;
                font-size: 0.9rem;
            }

            .card-footer {
                flex-direction: column;
                margin-left: -14px;
                margin-right: -14px;
            }
        }
    </style>
</head>

<body class="admin-login-page">

<div class="login-shell">
    <div class="login-card">
        <section class="login-showcase" aria-hidden="true">
            <span class="login-badge">Admin CMS</span>
            <div class="login-showcase-copy">
                <h1>Mubuga TSS control center.</h1>
                <p>Manage school pages, media, announcements, and public submissions from one focused dashboard built for a single administrator.</p>
            </div>
            <div class="login-showcase-grid">
                <div class="login-showcase-card">
                    <strong>Content publishing</strong>
                    <span>Update pages, post announcements, and keep the website current in one workflow.</span>
                </div>
                <div class="login-showcase-card">
                    <strong>Media oversight</strong>
                    <span>Organize images and videos with storage tracking and live content visibility.</span>
                </div>
                <div class="login-showcase-card">
                    <strong>Activity visibility</strong>
                    <span>Follow edits, uploads, and admin actions with a clear log and notifications.</span>
                </div>
                <div class="login-showcase-card">
                    <strong>School-first access</strong>
                    <span>Single-admin sign in with protected sessions and focused controls.</span>
                </div>
            </div>
            <div class="login-showcase-footer">
                <span class="login-showcase-chip">Single Admin</span>
                <span class="login-showcase-chip">Modern CMS</span>
                <span class="login-showcase-chip">Secure Access</span>
            </div>
        </section>

        <section class="login-panel">
            <div class="login-header">
                <img src="/MUBUGA-TSS/assets/images/MUBUGA%20LOGO%20SN.PNG" alt="Mubuga TSS" class="login-logo">
                <h1 class="login-heading">Welcome Admin</h1>
                <p class="login-subtitle">Single administrator access. Please sign in to continue.</p>
                <div class="login-highlights" aria-hidden="true">
                    <span class="login-highlight">Content Control</span>
                    <span class="login-highlight">Secure Access</span>
                    <span class="login-highlight">School CMS</span>
                </div>
            </div>

            <?php if ($error !== ''): ?>
                <div class="alert error" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="login-form" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">

                <div class="input-wrapper">
                    <span class="input-icon" aria-hidden="true">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.33 0-8 2.17-8 4.75A1.25 1.25 0 0 0 5.25 20h13.5A1.25 1.25 0 0 0 20 18.75C20 16.17 16.33 14 12 14Z"/>
                        </svg>
                    </span>
                    <input type="email" name="email" id="email" required autocomplete="email" placeholder="Email" class="login-input">
                </div>

                <div class="input-wrapper password-field">
                    <span class="input-icon" aria-hidden="true">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17 9h-1V7a4 4 0 1 0-8 0v2H7a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2Zm-6 0V7a2 2 0 1 1 4 0v2Zm1 7.73V18a1 1 0 1 0 2 0v-1.27a2 2 0 1 0-2 0Z"/>
                        </svg>
                    </span>
                    <input type="password" name="password" id="password" required autocomplete="current-password" placeholder="Password" class="login-input">
                    <button type="button" class="password-toggle" id="passwordToggle">Show</button>
                </div>

                <div class="login-meta">
                    <span class="forgot-link">Protected admin login</span>
                </div>

                <button type="submit" class="login-button">Sign In</button>
            </form>

            <div class="card-footer">
                <a href="/MUBUGA-TSS/" class="footer-link">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 3 2 11h2v9h6v-6h4v6h6v-9h2Z"/>
                    </svg>
                    <span>Back to Homepage</span>
                </a>
                <a href="mailto:support@mubugatss.local" class="footer-link">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm4.71 13.29a1 1 0 0 1-1.42 1.42l-1.18-1.18A5.92 5.92 0 0 1 12 16a6 6 0 1 1 6-6 5.92 5.92 0 0 1-.47 2.11Z"/>
                    </svg>
                    <span>Support</span>
                </a>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('passwordToggle');

    passwordToggle.addEventListener('click', function() {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        passwordToggle.textContent = isPassword ? 'Hide' : 'Show';
    });

    loginForm.addEventListener('submit', function() {
        const button = loginForm.querySelector('.login-button');
        button.disabled = true;
        button.textContent = 'Signing In...';
    });

    document.querySelectorAll('.login-input').forEach(function(input) {
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
