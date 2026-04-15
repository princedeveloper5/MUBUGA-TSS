<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function redirectWithStatus(string $target, string $status, string $message): never
{
    $separator = str_contains($target, '?') ? '&' : '?';
    header('Location: ' . $target . $separator . 'form_status=' . urlencode($status) . '&form_message=' . urlencode($message));
    exit;
}

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($requestMethod !== 'POST') {
    redirectWithStatus('/MUBUGA-TSS/', 'error', 'Invalid request.');
}

$action = (string) ($_POST['form_action'] ?? '');
$redirectTo = (string) ($_POST['redirect_to'] ?? '/MUBUGA-TSS/');
$pdo = getDatabaseConnection();

if (!$pdo instanceof PDO) {
    redirectWithStatus($redirectTo, 'error', 'Database is unavailable right now.');
}

try {
    if ($action === 'newsletter_subscribe') {
        $email = trim((string) ($_POST['email'] ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            redirectWithStatus($redirectTo, 'error', 'Please enter a valid email address.');
        }

        $pdo->exec('CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(150) NOT NULL UNIQUE,
            source VARCHAR(100) NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');

        $stmt = $pdo->prepare('INSERT INTO newsletter_subscribers (email, source, is_active) VALUES (:email, :source, 1)
            ON DUPLICATE KEY UPDATE is_active = 1, source = VALUES(source)');
        $stmt->execute([
            'email' => $email,
            'source' => trim((string) ($_POST['source'] ?? 'website')),
        ]);

        redirectWithStatus($redirectTo, 'success', 'You have been subscribed successfully.');
    }

    if ($action === 'contact_message') {
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $messageBody = trim((string) ($_POST['message_body'] ?? ''));

        if ($fullName === '' || $email === '' || $messageBody === '') {
            redirectWithStatus($redirectTo, 'error', 'Please fill in the required contact fields.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            redirectWithStatus($redirectTo, 'error', 'Please enter a valid email address.');
        }

        $stmt = $pdo->prepare('INSERT INTO contact_messages (full_name, email, phone, subject, message_body) VALUES (:full_name, :email, :phone, :subject, :message_body)');
        $stmt->execute([
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'subject' => $subject !== '' ? $subject : null,
            'message_body' => $messageBody,
        ]);

        redirectWithStatus($redirectTo, 'success', 'Your message has been sent successfully.');
    }

    redirectWithStatus($redirectTo, 'error', 'Unknown form action.');
} catch (Throwable $exception) {
    redirectWithStatus($redirectTo, 'error', 'The form could not be submitted right now.');
}
