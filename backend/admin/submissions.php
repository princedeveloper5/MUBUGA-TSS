<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_auth.php';

requireAdminLogin();

$pdo = getDatabaseConnection();
$message = '';
$error = '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!$pdo instanceof PDO) {
    $error = 'Database connection failed.';
} else {
    $pdo->exec('CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(150) NOT NULL UNIQUE,
        source VARCHAR(100) NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
}

if ($requestMethod === 'POST' && $pdo instanceof PDO) {
    if (!adminVerifyCsrfToken((string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Security token mismatch. Refresh the page and try again.';
    } else {
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'mark_message_read') {
            $stmt = $pdo->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = :id');
            $stmt->execute(['id' => (int) ($_POST['id'] ?? 0)]);
            $message = 'Message marked as read.';
        }

        if ($action === 'delete_message') {
            $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = :id');
            $stmt->execute(['id' => (int) ($_POST['id'] ?? 0)]);
            $message = 'Message deleted.';
        }

        if ($action === 'unsubscribe_email') {
            $stmt = $pdo->prepare('UPDATE newsletter_subscribers SET is_active = 0 WHERE id = :id');
            $stmt->execute(['id' => (int) ($_POST['id'] ?? 0)]);
            $message = 'Subscriber marked as inactive.';
        }
    } catch (Throwable $exception) {
        $error = 'The submission update could not be saved.';
    }
    }
}

$contactMessages = [];
$newsletterSubscribers = [];
$logoPath = 'assets/images/MUBUGA%20LOGO%20SN.PNG';
$unreadMessages = 0;
$activeSubscribers = 0;

if ($pdo instanceof PDO) {
    $settings = [];
    foreach ($pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll() as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    $logoPath = (string) ($settings['school_logo'] ?? '');
    // Use fallback to the default logo if no custom logo is set
    if ($logoPath === '') {
        $logoPath = 'assets/images/MUBUGA%20LOGO%20SN.PNG';
    }
    $contactMessages = $pdo->query('SELECT id, full_name, email, phone, subject, message_body, is_read, created_at FROM contact_messages ORDER BY created_at DESC')->fetchAll();
    $newsletterSubscribers = $pdo->query('SELECT id, email, source, is_active, created_at FROM newsletter_subscribers ORDER BY created_at DESC')->fetchAll();
    $unreadMessages = count(array_filter($contactMessages, static fn(array $messageItem): bool => (int) ($messageItem['is_read'] ?? 0) !== 1));
    $activeSubscribers = count(array_filter($newsletterSubscribers, static fn(array $subscriber): bool => (int) ($subscriber['is_active'] ?? 0) === 1));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mubuga TSS Admin Submissions</title>
    <link rel="icon" type="image/png" href="/MUBUGA-TSS/assets/images/MUBUGA%20LOGO%20SN.PNG">
    <link rel="stylesheet" href="/MUBUGA-TSS/assets/css/admin.css">
</head>
<body class="admin-page" data-dashboard-initial="<?php echo htmlspecialchars((string) (($_GET['view'] ?? '') === 'newsletter' ? 'newsletter-panel' : 'messages-panel')); ?>">
    <div class="admin-loader" data-admin-loader>
        <div class="admin-loader-card">
            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="Mubuga TSS logo" class="admin-loader-logo">
            <div class="project-spinner" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span><span></span>
                <span></span><span></span><span></span><span></span><span></span><span></span>
            </div>
            <strong>Loading Submissions</strong>
            <span>Opening website messages and newsletter records...</span>
        </div>
    </div>
    <div class="admin-shell dashboard-shell submissions-shell">
        <aside class="admin-sidebar dashboard-sidebar submissions-sidebar">
            <div class="dashboard-sidebar-top">
                <a href="/MUBUGA-TSS/backend/admin/dashboard.php" class="dashboard-brand-icon" aria-label="Mubuga TSS dashboard home">
                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="Mubuga TSS logo" class="dashboard-brand-logo">
                </a>

                <nav class="dashboard-nav" aria-label="Submissions navigation">
                    <a href="/MUBUGA-TSS/backend/admin/dashboard.php" class="dashboard-nav-link" data-tooltip="Dashboard" title="Dashboard" aria-label="Dashboard">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h5A1.5 1.5 0 0 1 12 5.5v5A1.5 1.5 0 0 1 10.5 12h-5A1.5 1.5 0 0 1 4 10.5v-5zM4 15.5A1.5 1.5 0 0 1 5.5 14h5a1.5 1.5 0 0 1 1.5 1.5v3A1.5 1.5 0 0 1 10.5 20h-5A1.5 1.5 0 0 1 4 18.5v-3zM14 5.5A1.5 1.5 0 0 1 15.5 4h3A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 14 18.5v-13z" fill="currentColor"/></svg>
                    </a>
                    <a href="#messages-panel" class="dashboard-nav-link is-active" data-tooltip="Messages" title="Messages" aria-label="Messages">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 4.5C2 3.672 2.672 3 3.5 3h17c.828 0 1.5.672 1.5 1.5v15c0 .828-.672 1.5-1.5 1.5h-17C2.672 21 2 20.328 2 19.5v-15z M4 6l8 5 8-5v-.5H4V6zm0 2.5v11h16v-11l-8 5-8-5z" fill="currentColor"/></svg>
                    </a>
                    <a href="#newsletter-panel" class="dashboard-nav-link" data-tooltip="Newsletter" title="Newsletter" aria-label="Newsletter">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3l8 4v5c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V7l8-4z" fill="currentColor"/></svg>
                    </a>
                </nav>
            </div>

            <div class="dashboard-sidebar-footer">
                <a href="/MUBUGA-TSS/backend/admin/dashboard.php" class="dashboard-nav-link" data-tooltip="Back to Dashboard" title="Back to Dashboard" aria-label="Back to Dashboard">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M11 5 4 12l7 7 1.414-1.414L7.828 13H20v-2H7.828l4.586-4.586L11 5z" fill="currentColor"/></svg>
                </a>
                <a href="/MUBUGA-TSS/backend/admin/logout.php" class="dashboard-nav-link dashboard-nav-link-logout" data-tooltip="Log Out" title="Log Out" aria-label="Log Out">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M10 4.5A1.5 1.5 0 0 1 11.5 3h6A1.5 1.5 0 0 1 19 4.5v15a1.5 1.5 0 0 1-1.5 1.5h-6A1.5 1.5 0 0 1 10 19.5V17h2v2h5V5h-5v2h-2V4.5zM5.707 12.707 8.414 15.414 7 16.828l-5.121-5.12a1 1 0 0 1 0-1.415L7 5.172l1.414 1.414-2.707 2.707H15v2H5.707z" fill="currentColor"/></svg>
                </a>
            </div>
        </aside>

        <main class="admin-main submissions-main">
            <header class="admin-topbar">
                <div class="submissions-hero-copy">
                    <p class="admin-eyebrow">Submissions</p>
                    <h2>Public Submissions</h2>
                    <p>Track website messages and newsletter subscriptions in one place.</p>
                    <div class="submissions-hero-links">
                        <a href="#messages-panel" class="news-filter-link">Open Messages</a>
                        <a href="#newsletter-panel" class="news-filter-link">Open Newsletter</a>
                    </div>
                </div>
                <div class="admin-topbar-badges submissions-badges">
                    <span class="admin-topbar-badge">Messages</span>
                    <span class="admin-topbar-badge"><?php echo $unreadMessages; ?> unread</span>
                    <span class="admin-topbar-badge"><?php echo $activeSubscribers; ?> active subscribers</span>
                </div>
            </header>

            <?php if ($message !== ''): ?>
                <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <section class="dashboard-cards submission-cards">
                <article class="dashboard-card dashboard-card-blue">
                    <div class="dashboard-card-icon">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 4.5C2 3.672 2.672 3 3.5 3h17c.828 0 1.5.672 1.5 1.5v15c0 .828-.672 1.5-1.5 1.5h-17C2.672 21 2 20.328 2 19.5v-15z M4 6l8 5 8-5v-.5H4V6zm0 2.5v11h16v-11l-8 5-8-5z" fill="currentColor"/></svg>
                    </div>
                    <p>Total Messages</p>
                    <strong><?php echo count($contactMessages); ?></strong>
                </article>
                <article class="dashboard-card dashboard-card-pink">
                    <div class="dashboard-card-icon">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 5a2 2 0 0 1 2-2h10l6 6v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5zm4 6h10v2H7v-2zm0 4h7v2H7v-2z" fill="currentColor"/></svg>
                    </div>
                    <p>Unread Messages</p>
                    <strong><?php echo $unreadMessages; ?></strong>
                </article>
                <article class="dashboard-card dashboard-card-purple">
                    <div class="dashboard-card-icon">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3l8 4v5c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V7l8-4z" fill="currentColor"/></svg>
                    </div>
                    <p>Subscribers</p>
                    <strong><?php echo count($newsletterSubscribers); ?></strong>
                </article>
                <article class="dashboard-card dashboard-card-orange">
                    <div class="dashboard-card-icon">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2a7 7 0 1 0 7 7 7 7 0 0 0-7-7zm1 7V5h-2v6h5v-2zM5 20h14v2H5z" fill="currentColor"/></svg>
                    </div>
                    <p>Active Subscribers</p>
                    <strong><?php echo $activeSubscribers; ?></strong>
                </article>
            </section>

            <section class="admin-grid">
                <article class="panel dashboard-view-panel is-active" id="messages-panel" data-dashboard-view>
                    <h3>Contact Messages</h3>
                    <div class="table-list">
                        <?php if (empty($contactMessages)): ?>
                            <p>No messages received yet.</p>
                        <?php else: ?>
                            <?php foreach ($contactMessages as $messageItem): ?>
                                <div class="table-item">
                                    <div class="submission-item-head">
                                        <div class="submission-item-title">
                                            <strong><?php echo htmlspecialchars($messageItem['full_name']); ?></strong>
                                            <span class="submission-item-time"><?php echo htmlspecialchars(date('d M Y, H:i', strtotime((string) $messageItem['created_at']))); ?></span>
                                        </div>
                                        <span class="status status-<?php echo (int) $messageItem['is_read'] === 1 ? 'accepted' : 'pending'; ?>">
                                            <?php echo (int) $messageItem['is_read'] === 1 ? 'Read' : 'Unread'; ?>
                                        </span>
                                    </div>
                                    <div class="submission-meta-row">
                                        <span class="submission-meta-pill"><?php echo htmlspecialchars($messageItem['email']); ?></span>
                                        <?php if (!empty($messageItem['phone'])): ?>
                                            <span class="submission-meta-pill"><?php echo htmlspecialchars($messageItem['phone']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($messageItem['subject'])): ?>
                                            <span class="submission-meta-pill"><?php echo htmlspecialchars($messageItem['subject']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="table-paragraph"><?php echo htmlspecialchars($messageItem['message_body']); ?></p>
                                    <div class="item-actions">
                                        <a href="mailto:<?php echo rawurlencode((string) $messageItem['email']); ?>" class="action-link">Reply</a>
                                        <?php if ((int) $messageItem['is_read'] !== 1): ?>
                                            <form method="post" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                                                <input type="hidden" name="action" value="mark_message_read">
                                                <input type="hidden" name="id" value="<?php echo (int) $messageItem['id']; ?>">
                                                <button type="submit" class="action-link action-button">Mark Read</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="post" class="inline-form" onsubmit="return confirm('Delete this message?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                                            <input type="hidden" name="action" value="delete_message">
                                            <input type="hidden" name="id" value="<?php echo (int) $messageItem['id']; ?>">
                                            <button type="submit" class="danger-button">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>

                <article class="panel dashboard-view-panel" id="newsletter-panel" data-dashboard-view>
                    <h3>Newsletter Subscribers</h3>
                    <div class="table-list">
                        <?php if (empty($newsletterSubscribers)): ?>
                            <p>No newsletter subscribers yet.</p>
                        <?php else: ?>
                            <?php foreach ($newsletterSubscribers as $subscriber): ?>
                                <div class="table-item">
                                    <div class="submission-item-head">
                                        <div class="submission-item-title">
                                            <strong><?php echo htmlspecialchars($subscriber['email']); ?></strong>
                                            <span class="submission-item-time"><?php echo htmlspecialchars(date('d M Y, H:i', strtotime((string) $subscriber['created_at']))); ?></span>
                                        </div>
                                        <span class="status status-<?php echo (int) $subscriber['is_active'] === 1 ? 'accepted' : 'draft'; ?>">
                                            <?php echo (int) $subscriber['is_active'] === 1 ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                    <div class="submission-meta-row">
                                        <span class="submission-meta-pill"><?php echo htmlspecialchars((string) ($subscriber['source'] ?? 'website')); ?></span>
                                        <span class="submission-meta-pill"><?php echo (int) $subscriber['is_active'] === 1 ? 'Receiving updates' : 'Not receiving updates'; ?></span>
                                    </div>
                                    <div class="item-actions">
                                        <a href="mailto:<?php echo rawurlencode((string) $subscriber['email']); ?>" class="action-link">Email</a>
                                        <?php if ((int) $subscriber['is_active'] === 1): ?>
                                            <form method="post" class="inline-form" onsubmit="return confirm('Mark this subscriber as inactive?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                                                <input type="hidden" name="action" value="unsubscribe_email">
                                                <input type="hidden" name="id" value="<?php echo (int) $subscriber['id']; ?>">
                                                <button type="submit" class="danger-button">Unsubscribe</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>
            </section>
        </main>
    </div>
    <script src="/MUBUGA-TSS/assets/js/admin.js"></script>
</body>
</html>
