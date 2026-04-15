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

$contactMessages = [];
$newsletterSubscribers = [];

if ($pdo instanceof PDO) {
    $contactMessages = $pdo->query('SELECT id, full_name, email, phone, subject, message_body, is_read, created_at FROM contact_messages ORDER BY created_at DESC')->fetchAll();
    $newsletterSubscribers = $pdo->query('SELECT id, email, source, is_active, created_at FROM newsletter_subscribers ORDER BY created_at DESC')->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mubuga TSS Admin Submissions</title>
    <link rel="stylesheet" href="/MUBUGA-TSS/assets/css/admin.css">
</head>
<body class="admin-page">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <p class="admin-eyebrow">Mubuga TSS</p>
            <h1>Admin Panel</h1>
            <p>Review contact form messages and mailing list signups.</p>
            <a href="/MUBUGA-TSS/admin/dashboard.php" class="logout-link">Back to Dashboard</a>
            <a href="/MUBUGA-TSS/admin/logout.php" class="logout-link">Log Out</a>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <div>
                    <h2>Public Submissions</h2>
                    <p>Track website messages and newsletter subscriptions in one place.</p>
                </div>
            </header>

            <?php if ($message !== ''): ?>
                <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <section class="admin-grid">
                <article class="panel">
                    <h3>Contact Messages</h3>
                    <div class="table-list">
                        <?php if (empty($contactMessages)): ?>
                            <p>No messages received yet.</p>
                        <?php else: ?>
                            <?php foreach ($contactMessages as $messageItem): ?>
                                <div class="table-item">
                                    <strong><?php echo htmlspecialchars($messageItem['full_name']); ?></strong>
                                    <span><?php echo htmlspecialchars($messageItem['email']); ?></span>
                                    <?php if (!empty($messageItem['phone'])): ?>
                                        <span><?php echo htmlspecialchars($messageItem['phone']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($messageItem['subject'])): ?>
                                        <span><?php echo htmlspecialchars($messageItem['subject']); ?></span>
                                    <?php endif; ?>
                                    <p class="table-paragraph"><?php echo htmlspecialchars($messageItem['message_body']); ?></p>
                                    <span class="status status-<?php echo (int) $messageItem['is_read'] === 1 ? 'accepted' : 'pending'; ?>">
                                        <?php echo (int) $messageItem['is_read'] === 1 ? 'Read' : 'Unread'; ?>
                                    </span>
                                    <div class="item-actions">
                                        <?php if ((int) $messageItem['is_read'] !== 1): ?>
                                            <form method="post" class="inline-form">
                                                <input type="hidden" name="action" value="mark_message_read">
                                                <input type="hidden" name="id" value="<?php echo (int) $messageItem['id']; ?>">
                                                <button type="submit" class="action-link action-button">Mark Read</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="post" class="inline-form" onsubmit="return confirm('Delete this message?');">
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

                <article class="panel">
                    <h3>Newsletter Subscribers</h3>
                    <div class="table-list">
                        <?php if (empty($newsletterSubscribers)): ?>
                            <p>No newsletter subscribers yet.</p>
                        <?php else: ?>
                            <?php foreach ($newsletterSubscribers as $subscriber): ?>
                                <div class="table-item">
                                    <strong><?php echo htmlspecialchars($subscriber['email']); ?></strong>
                                    <span><?php echo htmlspecialchars((string) ($subscriber['source'] ?? 'website')); ?></span>
                                    <span class="status status-<?php echo (int) $subscriber['is_active'] === 1 ? 'accepted' : 'draft'; ?>">
                                        <?php echo (int) $subscriber['is_active'] === 1 ? 'Active' : 'Inactive'; ?>
                                    </span>
                                    <div class="item-actions">
                                        <?php if ((int) $subscriber['is_active'] === 1): ?>
                                            <form method="post" class="inline-form" onsubmit="return confirm('Mark this subscriber as inactive?');">
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
</body>
</html>
