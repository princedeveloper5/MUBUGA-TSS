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

    if ($action === 'student_registration') {
        // Handle admissions form fields
        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $program = trim((string) ($_POST['preferred_program_id'] ?? ''));
        $programLevel = trim((string) ($_POST['program_level'] ?? ''));
        $guardianName = trim((string) ($_POST['guardian_name'] ?? ''));
        $guardianPhone = trim((string) ($_POST['guardian_phone'] ?? ''));
        $fatherName = trim((string) ($_POST['father_name'] ?? ''));
        $motherName = trim((string) ($_POST['mother_name'] ?? ''));
        $sponsor = trim((string) ($_POST['sponsor'] ?? ''));
        $sponsorOther = trim((string) ($_POST['sponsor_other'] ?? ''));

        // Handle file upload
        $filePath = '';
        if (!empty($_FILES['results_report']) && isset($_FILES['results_report']['tmp_name']) && is_uploaded_file($_FILES['results_report']['tmp_name'])) {
            $file = $_FILES['results_report'];
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
            
            if (in_array($extension, $allowedExtensions, true) && $file['size'] <= 5242880) { // 5MB limit
                $uploadDir = dirname(__DIR__) . '/assets/uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $baseName = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo((string) ($file['name'] ?? ''), PATHINFO_FILENAME));
                $baseName = trim((string) $baseName, '-');
                if ($baseName === '') {
                    $baseName = 'results_report';
                }
                
                $fileName = $baseName . '_' . time() . '.' . $extension;
                $filePath = 'assets/uploads/' . $fileName;
                
                if (move_uploaded_file($file['tmp_name'], dirname(__DIR__) . '/' . $filePath)) {
                    // File uploaded successfully
                } else {
                    redirectWithStatus($redirectTo, 'error', 'Failed to upload results file.');
                }
            } else {
                redirectWithStatus($redirectTo, 'error', 'Invalid file format or size. Please upload JPG or PNG under 5MB.');
            }
        }

        // Validate required fields
        $requiredFields = [$firstName, $lastName, $program, $guardianName, $guardianPhone];
        foreach ($requiredFields as $field) {
            if ($field === '') {
                redirectWithStatus($redirectTo, 'error', 'Please fill in all required fields.');
            }
        }

        // Create admissions_applications table if it doesn't exist
        $pdo->exec('CREATE TABLE IF NOT EXISTS admissions_applications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            program VARCHAR(100) NOT NULL,
            program_level VARCHAR(20) NULL,
            guardian_name VARCHAR(200) NOT NULL,
            guardian_phone VARCHAR(20) NOT NULL,
            father_name VARCHAR(100) NULL,
            mother_name VARCHAR(100) NULL,
            sponsor VARCHAR(50) NULL,
            sponsor_other VARCHAR(200) NULL,
            results_file VARCHAR(255) NULL,
            status ENUM(\'pending\', \'reviewing\', \'accepted\', \'rejected\') DEFAULT \'pending\',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');

        $stmt = $pdo->prepare('INSERT INTO admissions_applications 
            (first_name, last_name, program, program_level, guardian_name, guardian_phone, 
             father_name, mother_name, sponsor, sponsor_other, results_file) 
            VALUES (:first_name, :last_name, :program, :program_level, :guardian_name, :guardian_phone, 
                    :father_name, :mother_name, :sponsor, :sponsor_other, :results_file)');
        
        $stmt->execute([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'program' => $program,
            'program_level' => $programLevel !== '' ? $programLevel : null,
            'guardian_name' => $guardianName,
            'guardian_phone' => $guardianPhone,
            'father_name' => $fatherName !== '' ? $fatherName : null,
            'mother_name' => $motherName !== '' ? $motherName : null,
            'sponsor' => $sponsor !== '' ? $sponsor : null,
            'sponsor_other' => $sponsorOther !== '' ? $sponsorOther : null,
            'results_file' => $filePath !== '' ? $filePath : null,
        ]);

        redirectWithStatus($redirectTo, 'success', 'Your application has been submitted successfully! We will contact you soon.');
    }

    redirectWithStatus($redirectTo, 'error', 'Unknown form action.');
} catch (Throwable $exception) {
    redirectWithStatus($redirectTo, 'error', 'The form could not be submitted right now.');
}
