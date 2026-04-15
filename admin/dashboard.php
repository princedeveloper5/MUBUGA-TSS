<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/admin_upload.php';

requireAdminLogin();

$pdo = getDatabaseConnection();
$message = '';
$error = '';
$editType = (string) ($_GET['edit'] ?? '');
$editId = (int) ($_GET['id'] ?? 0);
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!$pdo instanceof PDO) {
    $error = 'Database connection failed.';
}

if ($requestMethod === 'POST' && $pdo instanceof PDO) {
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'update_settings') {
            $logoPath = trim((string) ($_POST['school_logo'] ?? ''));
            $logoPath = handleAdminImageUpload('school_logo_upload', $logoPath);
            $settings = [
                'school_name' => trim((string) ($_POST['school_name'] ?? '')),
                'school_motto' => trim((string) ($_POST['school_motto'] ?? '')),
                'school_email' => trim((string) ($_POST['school_email'] ?? '')),
                'school_phone' => trim((string) ($_POST['school_phone'] ?? '')),
                'school_address' => trim((string) ($_POST['school_address'] ?? '')),
                'school_logo' => $logoPath,
                'school_facebook' => trim((string) ($_POST['school_facebook'] ?? '')),
                'school_instagram' => trim((string) ($_POST['school_instagram'] ?? '')),
            ];

            $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (:key_name, :key_value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            foreach ($settings as $key => $value) {
                $stmt->execute(['key_name' => $key, 'key_value' => $value]);
            }
            $message = 'Settings updated successfully.';
        }

        if ($action === 'add_staff') {
            $photoPath = trim((string) ($_POST['photo'] ?? 'assets/images/master.jpeg'));
            $photoPath = handleAdminImageUpload('photo_upload', $photoPath);
            $stmt = $pdo->prepare('INSERT INTO staff (full_name, job_title, bio, photo, display_order, is_featured, status) VALUES (:full_name, :job_title, :bio, :photo, :display_order, :is_featured, "active")');
            $stmt->execute([
                'full_name' => trim((string) ($_POST['full_name'] ?? '')),
                'job_title' => trim((string) ($_POST['job_title'] ?? '')),
                'bio' => trim((string) ($_POST['bio'] ?? '')),
                'photo' => $photoPath,
                'display_order' => (int) ($_POST['display_order'] ?? 0),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ]);
            $message = 'Staff member added.';
        }

        if ($action === 'update_staff') {
            $photoPath = trim((string) ($_POST['photo'] ?? 'assets/images/master.jpeg'));
            $photoPath = handleAdminImageUpload('photo_upload', $photoPath);
            $stmt = $pdo->prepare('UPDATE staff SET full_name = :full_name, job_title = :job_title, bio = :bio, photo = :photo, display_order = :display_order, is_featured = :is_featured WHERE id = :id');
            $stmt->execute([
                'id' => (int) ($_POST['id'] ?? 0),
                'full_name' => trim((string) ($_POST['full_name'] ?? '')),
                'job_title' => trim((string) ($_POST['job_title'] ?? '')),
                'bio' => trim((string) ($_POST['bio'] ?? '')),
                'photo' => $photoPath,
                'display_order' => (int) ($_POST['display_order'] ?? 0),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ]);
            $message = 'Staff member updated.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'delete_staff') {
            $stmt = $pdo->prepare('DELETE FROM staff WHERE id = :id');
            $stmt->execute(['id' => (int) ($_POST['id'] ?? 0)]);
            $message = 'Staff member deleted.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'add_program') {
            $coverImage = trim((string) ($_POST['cover_image'] ?? 'assets/images/mb1.jfif'));
            $coverImage = handleAdminImageUpload('program_cover_upload', $coverImage);
            $stmt = $pdo->prepare('INSERT INTO programs (title, slug, short_description, description, duration, department, cover_image, status) VALUES (:title, :slug, :short_description, :description, :duration, :department, :cover_image, :status)');
            $stmt->execute([
                'title' => trim((string) ($_POST['program_title'] ?? '')),
                'slug' => trim((string) ($_POST['program_slug'] ?? '')),
                'short_description' => trim((string) ($_POST['program_summary'] ?? '')),
                'description' => trim((string) ($_POST['program_description'] ?? '')),
                'duration' => trim((string) ($_POST['program_duration'] ?? '')),
                'department' => trim((string) ($_POST['program_department'] ?? '')),
                'cover_image' => $coverImage,
                'status' => (string) ($_POST['program_status'] ?? 'active'),
            ]);
            $message = 'Program added.';
        }

        if ($action === 'update_program') {
            $coverImage = trim((string) ($_POST['cover_image'] ?? 'assets/images/mb1.jfif'));
            $coverImage = handleAdminImageUpload('program_cover_upload', $coverImage);
            $stmt = $pdo->prepare('UPDATE programs SET title = :title, slug = :slug, short_description = :short_description, description = :description, duration = :duration, department = :department, cover_image = :cover_image, status = :status WHERE id = :id');
            $stmt->execute([
                'id' => (int) ($_POST['id'] ?? 0),
                'title' => trim((string) ($_POST['program_title'] ?? '')),
                'slug' => trim((string) ($_POST['program_slug'] ?? '')),
                'short_description' => trim((string) ($_POST['program_summary'] ?? '')),
                'description' => trim((string) ($_POST['program_description'] ?? '')),
                'duration' => trim((string) ($_POST['program_duration'] ?? '')),
                'department' => trim((string) ($_POST['program_department'] ?? '')),
                'cover_image' => $coverImage,
                'status' => (string) ($_POST['program_status'] ?? 'active'),
            ]);
            $message = 'Program updated.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'delete_program') {
            $stmt = $pdo->prepare('DELETE FROM programs WHERE id = :id');
            $stmt->execute(['id' => (int) ($_POST['id'] ?? 0)]);
            $message = 'Program deleted.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'add_news') {
            $title = trim((string) ($_POST['title'] ?? ''));
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
            $featuredImage = trim((string) ($_POST['featured_image'] ?? 'assets/images/mb1.jfif'));
            $featuredImage = handleAdminImageUpload('featured_image_upload', $featuredImage);
            $stmt = $pdo->prepare('INSERT INTO news (title, slug, summary, content, featured_image, published_at, status) VALUES (:title, :slug, :summary, :content, :featured_image, NOW(), "published")');
            $stmt->execute([
                'title' => $title,
                'slug' => $slug,
                'summary' => trim((string) ($_POST['summary'] ?? '')),
                'content' => trim((string) ($_POST['content'] ?? '')),
                'featured_image' => $featuredImage,
            ]);
            $message = 'News item published.';
        }

        if ($action === 'update_news') {
            $id = (int) ($_POST['id'] ?? 0);
            $title = trim((string) ($_POST['title'] ?? ''));
            $slug = strtolower(trim((string) ($_POST['slug'] ?? ''), '-'));
            if ($slug === '') {
                $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
            }
            $featuredImage = trim((string) ($_POST['featured_image'] ?? 'assets/images/mb1.jfif'));
            $featuredImage = handleAdminImageUpload('featured_image_upload', $featuredImage);
            $stmt = $pdo->prepare('UPDATE news SET title = :title, slug = :slug, summary = :summary, content = :content, featured_image = :featured_image WHERE id = :id');
            $stmt->execute([
                'id' => $id,
                'title' => $title,
                'slug' => $slug,
                'summary' => trim((string) ($_POST['summary'] ?? '')),
                'content' => trim((string) ($_POST['content'] ?? '')),
                'featured_image' => $featuredImage,
            ]);
            $message = 'News item updated.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'delete_news') {
            $stmt = $pdo->prepare('DELETE FROM news WHERE id = :id');
            $stmt->execute(['id' => (int) ($_POST['id'] ?? 0)]);
            $message = 'News item deleted.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'add_gallery') {
            $imagePath = trim((string) ($_POST['image_path'] ?? ''));
            $imagePath = handleAdminImageUpload('gallery_image_upload', $imagePath);
            $stmt = $pdo->prepare('INSERT INTO gallery (title, image_path, caption, category, is_featured) VALUES (:title, :image_path, :caption, :category, :is_featured)');
            $stmt->execute([
                'title' => trim((string) ($_POST['title'] ?? '')),
                'image_path' => $imagePath,
                'caption' => trim((string) ($_POST['caption'] ?? '')),
                'category' => trim((string) ($_POST['category'] ?? 'general')),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ]);
            $message = 'Gallery image added.';
        }

        if ($action === 'update_gallery') {
            $imagePath = trim((string) ($_POST['image_path'] ?? ''));
            $imagePath = handleAdminImageUpload('gallery_image_upload', $imagePath);
            $stmt = $pdo->prepare('UPDATE gallery SET title = :title, image_path = :image_path, caption = :caption, category = :category, is_featured = :is_featured WHERE id = :id');
            $stmt->execute([
                'id' => (int) ($_POST['id'] ?? 0),
                'title' => trim((string) ($_POST['title'] ?? '')),
                'image_path' => $imagePath,
                'caption' => trim((string) ($_POST['caption'] ?? '')),
                'category' => trim((string) ($_POST['category'] ?? 'general')),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ]);
            $message = 'Gallery image updated.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'delete_gallery') {
            $stmt = $pdo->prepare('DELETE FROM gallery WHERE id = :id');
            $stmt->execute(['id' => (int) ($_POST['id'] ?? 0)]);
            $message = 'Gallery image deleted.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'add_page') {
            $bannerImage = trim((string) ($_POST['banner_image'] ?? 'assets/images/mb1.jfif'));
            $bannerImage = handleAdminImageUpload('banner_image_upload', $bannerImage);
            $stmt = $pdo->prepare('INSERT INTO pages (title, slug, excerpt, content, banner_image, status) VALUES (:title, :slug, :excerpt, :content, :banner_image, :status)');
            $stmt->execute([
                'title' => trim((string) ($_POST['title'] ?? '')),
                'slug' => trim((string) ($_POST['slug'] ?? '')),
                'excerpt' => trim((string) ($_POST['excerpt'] ?? '')),
                'content' => trim((string) ($_POST['content'] ?? '')),
                'banner_image' => $bannerImage,
                'status' => (string) ($_POST['status'] ?? 'published'),
            ]);
            $message = 'Page content added.';
        }

        if ($action === 'update_page') {
            $bannerImage = trim((string) ($_POST['banner_image'] ?? 'assets/images/mb1.jfif'));
            $bannerImage = handleAdminImageUpload('banner_image_upload', $bannerImage);
            $stmt = $pdo->prepare('UPDATE pages SET title = :title, slug = :slug, excerpt = :excerpt, content = :content, banner_image = :banner_image, status = :status WHERE id = :id');
            $stmt->execute([
                'id' => (int) ($_POST['id'] ?? 0),
                'title' => trim((string) ($_POST['title'] ?? '')),
                'slug' => trim((string) ($_POST['slug'] ?? '')),
                'excerpt' => trim((string) ($_POST['excerpt'] ?? '')),
                'content' => trim((string) ($_POST['content'] ?? '')),
                'banner_image' => $bannerImage,
                'status' => (string) ($_POST['status'] ?? 'published'),
            ]);
            $message = 'Page content updated.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'delete_page') {
            $stmt = $pdo->prepare('DELETE FROM pages WHERE id = :id');
            $stmt->execute(['id' => (int) ($_POST['id'] ?? 0)]);
            $message = 'Page content deleted.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'update_admission_status') {
            $stmt = $pdo->prepare('UPDATE admissions SET status = :status WHERE id = :id');
            $stmt->execute([
                'id' => (int) ($_POST['id'] ?? 0),
                'status' => (string) ($_POST['status'] ?? 'pending'),
            ]);
            $message = 'Admission status updated.';
        }

        if ($action === 'delete_admission') {
            $stmt = $pdo->prepare('DELETE FROM admissions WHERE id = :id');
            $stmt->execute(['id' => (int) ($_POST['id'] ?? 0)]);
            $message = 'Admission application deleted.';
        }

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
        $error = 'The update could not be saved. Please check the values and try again.';
    }
}

$settings = [];
$programs = [];
$staff = [];
$news = [];
$gallery = [];
$admissions = [];
$pages = [];
$contactMessages = [];
$newsletterSubscribers = [];
$logoPath = 'assets/images/MUBUGA%20LOGO%20SN.PNG';
$staffForm = [
    'id' => 0,
    'full_name' => '',
    'job_title' => '',
    'bio' => '',
    'photo' => 'assets/images/master.jpeg',
    'display_order' => 0,
    'is_featured' => 0,
];
$programForm = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'short_description' => '',
    'description' => '',
    'duration' => '',
    'department' => '',
    'cover_image' => 'assets/images/mb1.jfif',
    'status' => 'active',
];
$newsForm = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'summary' => '',
    'content' => '',
    'featured_image' => 'assets/images/mb1.jfif',
];
$galleryForm = [
    'id' => 0,
    'title' => '',
    'image_path' => '',
    'caption' => '',
    'category' => 'general',
    'is_featured' => 0,
];
$pageForm = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content' => '',
    'banner_image' => 'assets/images/mb1.jfif',
    'status' => 'published',
];

if ($pdo instanceof PDO) {
    foreach ($pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll() as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    if (!empty($settings['school_logo'])) {
        $logoPath = (string) $settings['school_logo'];
    }
    $programs = $pdo->query('SELECT id, title, slug, short_description, duration, department, cover_image, status FROM programs ORDER BY id DESC')->fetchAll();
    $staff = $pdo->query('SELECT id, full_name, job_title, bio, photo, display_order, is_featured, status FROM staff ORDER BY display_order ASC, id DESC')->fetchAll();
    $news = $pdo->query('SELECT id, title, slug, summary, content, featured_image, published_at FROM news ORDER BY id DESC')->fetchAll();
    $gallery = $pdo->query('SELECT id, title, image_path, caption, category, is_featured FROM gallery ORDER BY id DESC')->fetchAll();
    $admissions = $pdo->query('SELECT id, applicant_name, email, preferred_program_id, status, created_at FROM admissions ORDER BY created_at DESC')->fetchAll();
    $pages = $pdo->query('SELECT id, title, slug, banner_image, status FROM pages ORDER BY slug ASC, id DESC')->fetchAll();
    $contactMessages = $pdo->query('SELECT id, full_name, email, phone, subject, message_body, is_read, created_at FROM contact_messages ORDER BY created_at DESC')->fetchAll();

    if ($editType === 'staff' && $editId > 0) {
        $stmt = $pdo->prepare('SELECT id, full_name, job_title, bio, photo, display_order, is_featured FROM staff WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $editId]);
        $staffForm = $stmt->fetch() ?: $staffForm;
    }

    if ($editType === 'program' && $editId > 0) {
        $stmt = $pdo->prepare('SELECT id, title, slug, short_description, description, duration, department, cover_image, status FROM programs WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $editId]);
        $programForm = $stmt->fetch() ?: $programForm;
    }

    if ($editType === 'news' && $editId > 0) {
        $stmt = $pdo->prepare('SELECT id, title, slug, summary, content, featured_image FROM news WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $editId]);
        $newsForm = $stmt->fetch() ?: $newsForm;
    }

    if ($editType === 'gallery' && $editId > 0) {
        $stmt = $pdo->prepare('SELECT id, title, image_path, caption, category, is_featured FROM gallery WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $editId]);
        $galleryForm = $stmt->fetch() ?: $galleryForm;
    }

    if ($editType === 'page' && $editId > 0) {
        $stmt = $pdo->prepare('SELECT id, title, slug, excerpt, content, banner_image, status FROM pages WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $editId]);
        $pageForm = $stmt->fetch() ?: $pageForm;
    }

    $newsletterSubscribers = $pdo->query('SELECT id, email, is_active, created_at FROM newsletter_subscribers ORDER BY created_at DESC')->fetchAll();

    $summaryCards = [
        ['title' => 'Programs', 'value' => count($programs), 'color' => 'purple', 'icon' => 'M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z', 'link' => '#programs-panel'],
        ['title' => 'Staff', 'value' => count($staff), 'color' => 'blue', 'icon' => 'M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z M4 22c0-4.418 3.582-8 8-8s8 3.582 8 8H4z', 'link' => '#staff-panel'],
        ['title' => 'Admissions', 'value' => count($admissions), 'color' => 'pink', 'icon' => 'M12 2C8.691 2 6 4.691 6 8c0 5.25 6 12 6 12s6-6.75 6-12c0-3.309-2.691-6-6-6z', 'link' => '#admissions-panel'],
        ['title' => 'Messages', 'value' => count($contactMessages), 'color' => 'orange', 'icon' => 'M2 4.5C2 3.672 2.672 3 3.5 3h17c.828 0 1.5.672 1.5 1.5v15c0 .828-.672 1.5-1.5 1.5h-17C2.672 21 2 20.328 2 19.5v-15z M4 6l8 5 8-5v-.5H4V6zm0 2.5v11h16v-11l-8 5-8-5z', 'link' => '/MUBUGA-TSS/admin/submissions.php#messages-panel'],
        ['title' => 'News', 'value' => count($news), 'color' => 'blue', 'icon' => 'M4 4h16v16H4z M7 8h10v2H7zm0 4h10v2H7zm0 4h6v2H7z', 'link' => '#news-panel'],
        ['title' => 'Subscribers', 'value' => count($newsletterSubscribers), 'color' => 'purple', 'icon' => 'M12 3l8 4v5c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V7l8-4z', 'link' => '/MUBUGA-TSS/admin/submissions.php#newsletter-panel'],
    ];

    $activeDashboardPanel = match ($editType) {
        'program' => 'programs-panel',
        'staff' => 'staff-panel',
        'news' => 'news-panel',
        'gallery' => 'gallery-panel',
        'page' => 'pages-panel',
        default => 'dashboard-panel',
    };

    $adminName = trim((string) (($admin['name'] ?? $admin['full_name'] ?? 'Administrator')));
    $currentDateLabel = date('l, d M Y');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mubuga TSS Admin Dashboard</title>
    <link rel="stylesheet" href="/MUBUGA-TSS/assets/css/admin.css">
</head>
<body class="admin-page" data-dashboard-initial="<?php echo htmlspecialchars($activeDashboardPanel); ?>">
    <div class="admin-loader" data-admin-loader>
        <div class="admin-loader-card">
            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="Mubuga TSS logo" class="admin-loader-logo">
            <div class="project-spinner" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span><span></span>
                <span></span><span></span><span></span><span></span><span></span><span></span>
            </div>
            <strong>Mubuga TSS Admin</strong>
            <span>DISCIPLINE.RESILIENCE.INNOVATION</span>
        </div>
    </div>
    <div class="admin-shell dashboard-shell">
        <aside class="admin-sidebar dashboard-sidebar">
            <div class="dashboard-sidebar-top">
                <div class="dashboard-brand-block">
                    <a href="/MUBUGA-TSS/admin/dashboard.php" class="dashboard-brand-icon" aria-label="Mubuga TSS dashboard home">
                        <img src="/MUBUGA-TSS/assets/images/MUBUGA%20LOGO%20SN.PNG" alt="Mubuga TSS logo" class="dashboard-brand-logo">
                    </a>
                    <div class="dashboard-brand-copy">
                        <p class="admin-eyebrow">Admin Console</p>
                        <h1>Mubuga TSS</h1>
                        <p>Manage school content, staff updates, admissions, and public communication.</p>
                    </div>
                </div>

                <nav class="dashboard-nav" aria-label="Dashboard navigation">
                    <a href="#dashboard-panel" class="dashboard-nav-link is-active" data-tooltip="Dashboard" title="Dashboard" aria-label="Dashboard">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h5A1.5 1.5 0 0 1 12 5.5v5A1.5 1.5 0 0 1 10.5 12h-5A1.5 1.5 0 0 1 4 10.5v-5zM4 15.5A1.5 1.5 0 0 1 5.5 14h5a1.5 1.5 0 0 1 1.5 1.5v3A1.5 1.5 0 0 1 10.5 20h-5A1.5 1.5 0 0 1 4 18.5v-3zM14 5.5A1.5 1.5 0 0 1 15.5 4h3A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 14 18.5v-13z" fill="currentColor"/></svg>
                        <span>Overview</span>
                    </a>
                    <a href="#programs-panel" class="dashboard-nav-link" data-tooltip="Manage Programs" title="Programs" aria-label="Programs">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 4h14a1 1 0 0 1 1 1v12.5a1 1 0 0 1-1.447.894L12 15.118l-6.553 3.276A1 1 0 0 1 4 17.5V5a1 1 0 0 1 1-1zm2 3v2h10V7H7zm0 4v2h7v-2H7z" fill="currentColor"/></svg>
                        <span>Programs</span>
                    </a>
                    <a href="#staff-panel" class="dashboard-nav-link" data-tooltip="Manage Staff" title="Staff" aria-label="Staff">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm-7 8a7 7 0 1 1 14 0H5zm14.5-9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM18 20c0-2.02-.76-3.86-2.01-5.26A6 6 0 0 1 22 20h-4z" fill="currentColor"/></svg>
                        <span>Staff</span>
                    </a>
                    <a href="#news-panel" class="dashboard-nav-link" data-tooltip="Manage News" title="News" aria-label="News">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 5.5A1.5 1.5 0 0 1 7.5 4h8A1.5 1.5 0 0 1 17 5.5V6h1.5A1.5 1.5 0 0 1 20 7.5v10a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 6 17.5v-12zM8 8v2h7V8H8zm0 4v2h8v-2H8zm0 4v2h5v-2H8z" fill="currentColor"/></svg>
                        <span>News</span>
                    </a>
                    <a href="#gallery-panel" class="dashboard-nav-link" data-tooltip="Manage Gallery" title="Gallery" aria-label="Gallery">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 5a2 2 0 0 0-2 2v10.5A2.5 2.5 0 0 0 5.5 20h13a2.5 2.5 0 0 0 2.5-2.5V7a2 2 0 0 0-2-2H5zm2.5 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm11.5 9.5H5.5a.5.5 0 0 1-.39-.813l3.254-4.067a1 1 0 0 1 1.53.017l1.617 2.055 2.75-3.261a1 1 0 0 1 1.55.047l3.58 4.299A.5.5 0 0 1 19 17.5z" fill="currentColor"/></svg>
                        <span>Gallery</span>
                    </a>
                    <a href="#pages-panel" class="dashboard-nav-link" data-tooltip="Manage Pages" title="Pages" aria-label="Pages">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 4h8l4 4v11.5A1.5 1.5 0 0 1 17.5 21h-10A1.5 1.5 0 0 1 6 19.5v-14A1.5 1.5 0 0 1 7.5 4H7zm7 1.5V9h3.5L14 5.5zM8 11v2h8v-2H8zm0 4v2h6v-2H8z" fill="currentColor"/></svg>
                        <span>Pages</span>
                    </a>
                    <a href="#admissions-panel" class="dashboard-nav-link" data-tooltip="View Admissions" title="Admissions" aria-label="Admissions">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2.5 4 6.5v5.8c0 4.8 3.12 9.18 8 10.7 4.88-1.52 8-5.9 8-10.7V6.5l-8-4zm-1 5h2v4h3v2h-5v-6z" fill="currentColor"/></svg>
                        <span>Admissions</span>
                    </a>
                </nav>
            </div>

            <div class="dashboard-sidebar-footer">
                <a href="/MUBUGA-TSS/admin/submissions.php" class="dashboard-nav-link" data-tooltip="View Submissions" title="Submissions" aria-label="Submissions">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h13A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 18.5v-13zM7 8v2h10V8H7zm0 4v2h7v-2H7z" fill="currentColor"/></svg>
                    <span>Submissions</span>
                </a>
                <a href="/MUBUGA-TSS/admin/logout.php" class="dashboard-nav-link dashboard-nav-link-logout" data-tooltip="Log Out" title="Log Out" aria-label="Log Out">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M10 4.5A1.5 1.5 0 0 1 11.5 3h6A1.5 1.5 0 0 1 19 4.5v15a1.5 1.5 0 0 1-1.5 1.5h-6A1.5 1.5 0 0 1 10 19.5V17h2v2h5V5h-5v2h-2V4.5zM5.707 12.707 8.414 15.414 7 16.828l-5.121-5.12a1 1 0 0 1 0-1.415L7 5.172l1.414 1.414-2.707 2.707H15v2H5.707z" fill="currentColor"/></svg>
                    <span>Log Out</span>
                </a>
            </div>
        </aside>

        <main class="admin-main dashboard-main">
            <header class="dashboard-header">
                <div class="dashboard-header-copy">
                    <p class="admin-eyebrow">Dashboard</p>
                    <h1>Welcome back, <?php echo htmlspecialchars($adminName); ?></h1>
                    <p class="dashboard-header-text">Keep the website fresh, review submissions quickly, and manage school information from one clean workspace.</p>
                    <div class="dashboard-header-meta">
                        <span><?php echo htmlspecialchars($currentDateLabel); ?></span>
                        <span><?php echo count($summaryCards); ?> key areas</span>
                        <span><?php echo count($contactMessages); ?> public messages</span>
                    </div>
                </div>
                <div class="dashboard-filters">
                    <a href="/MUBUGA-TSS/" class="button-secondary">Open Website</a>
                    <a href="/MUBUGA-TSS/admin/submissions.php" class="logout-link">View Submissions</a>
                </div>
            </header>

            <?php if ($message !== ''): ?>
                <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="dashboard-home-view is-active" id="dashboard-panel" data-dashboard-view>
            <section class="dashboard-hero">
                <div class="dashboard-hero-card dashboard-hero-primary">
                    <p class="admin-eyebrow">Control Center</p>
                    <h2>Everything important is visible at a glance.</h2>
                    <p>Use the overview cards for quick navigation, then jump into each section to update website content and school records.</p>
                    <div class="dashboard-hero-actions">
                        <a href="#news-panel" class="button-secondary dashboard-inline-action dashboard-card-link">Publish News</a>
                        <a href="#admissions-panel" class="button-secondary dashboard-inline-action dashboard-card-link">Review Admissions</a>
                    </div>
                </div>
                <div class="dashboard-hero-card dashboard-hero-secondary">
                    <p class="admin-eyebrow">Today</p>
                    <div class="dashboard-hero-stat">
                        <strong><?php echo count($admissions); ?></strong>
                        <span>admission records currently available</span>
                    </div>
                    <div class="dashboard-hero-list">
                        <span><?php echo count($programs); ?> active program entries</span>
                        <span><?php echo count($staff); ?> staff profiles on the site</span>
                        <span><?php echo count($news); ?> news stories published</span>
                    </div>
                </div>
            </section>

            <section class="dashboard-cards">
                <?php foreach ($summaryCards as $card): ?>
                    <a href="<?php echo htmlspecialchars((string) $card['link']); ?>" class="dashboard-card dashboard-card-<?php echo htmlspecialchars($card['color']); ?> dashboard-card-link">
                        <div class="dashboard-card-icon">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="<?php echo htmlspecialchars($card['icon']); ?>" fill="currentColor"/></svg>
                        </div>
                        <p><?php echo htmlspecialchars($card['title']); ?></p>
                        <strong><?php echo htmlspecialchars((string) $card['value']); ?></strong>
                    </a>
                <?php endforeach; ?>
            </section>

            <section class="admin-grid">
                <article class="panel">
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">School Settings</p>
                            <h2>Branding and contact details</h2>
                        </div>
                    </div>
                    <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_settings">
                        <label><span>School Name</span><input type="text" name="school_name" value="<?php echo htmlspecialchars((string) ($settings['school_name'] ?? 'Mubuga TSS')); ?>"></label>
                        <label><span>Motto</span><input type="text" name="school_motto" value="<?php echo htmlspecialchars((string) ($settings['school_motto'] ?? '')); ?>"></label>
                        <label><span>Email</span><input type="email" name="school_email" value="<?php echo htmlspecialchars((string) ($settings['school_email'] ?? '')); ?>"></label>
                        <label><span>Phone</span><input type="text" name="school_phone" value="<?php echo htmlspecialchars((string) ($settings['school_phone'] ?? '')); ?>"></label>
                        <label><span>Address</span><input type="text" name="school_address" value="<?php echo htmlspecialchars((string) ($settings['school_address'] ?? '')); ?>"></label>
                        <label><span>Logo Path</span><input type="text" name="school_logo" value="<?php echo htmlspecialchars((string) ($settings['school_logo'] ?? '')); ?>"></label>
                        <label><span>Upload Logo</span><input type="file" name="school_logo_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" class="upload-input"></label>
                        <label><span>Facebook URL</span><input type="url" name="school_facebook" value="<?php echo htmlspecialchars((string) ($settings['school_facebook'] ?? '')); ?>"></label>
                        <label><span>Instagram URL</span><input type="url" name="school_instagram" value="<?php echo htmlspecialchars((string) ($settings['school_instagram'] ?? '')); ?>"></label>
                        <button type="submit">Save Settings</button>
                    </form>
                </article>

                <article class="panel">
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Latest Activity</p>
                            <h2>Newest admissions, news, and messages</h2>
                        </div>
                    </div>
                    <div class="table-list">
                        <?php foreach (array_slice($admissions, 0, 3) as $admission): ?>
                            <div class="table-item">
                                <strong><?php echo htmlspecialchars((string) $admission['applicant_name']); ?></strong>
                                <span>Admission - <?php echo htmlspecialchars((string) $admission['status']); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php foreach (array_slice($news, 0, 2) as $newsItem): ?>
                            <div class="table-item">
                                <strong><?php echo htmlspecialchars((string) $newsItem['title']); ?></strong>
                                <span>News post</span>
                            </div>
                        <?php endforeach; ?>
                        <?php foreach (array_slice($contactMessages, 0, 2) as $messageItem): ?>
                            <div class="table-item">
                                <strong><?php echo htmlspecialchars((string) $messageItem['full_name']); ?></strong>
                                <span>Contact message</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            </section>
            </div>

            <section class="admin-grid">
                <article class="panel dashboard-view-panel" id="news-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">News</p>
                            <h2><?php echo $editType === 'news' ? 'Edit News Post' : 'Publish News'; ?></h2>
                        </div>
                    </div>
                    <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editType === 'news' ? 'update_news' : 'add_news'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $newsForm['id']; ?>">
                        <label><span>Title</span><input type="text" name="title" value="<?php echo htmlspecialchars((string) $newsForm['title']); ?>"></label>
                        <label><span>Slug</span><input type="text" name="slug" value="<?php echo htmlspecialchars((string) $newsForm['slug']); ?>"></label>
                        <label><span>Summary</span><textarea name="summary" rows="4"><?php echo htmlspecialchars((string) $newsForm['summary']); ?></textarea></label>
                        <label><span>Full Content</span><textarea name="content" rows="7"><?php echo htmlspecialchars((string) $newsForm['content']); ?></textarea></label>
                        <label><span>Featured Image Path</span><input type="text" name="featured_image" value="<?php echo htmlspecialchars((string) $newsForm['featured_image']); ?>"></label>
                        <label><span>Upload Featured Image</span><input type="file" name="featured_image_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" class="upload-input"></label>
                        <button type="submit"><?php echo $editType === 'news' ? 'Update News' : 'Publish News'; ?></button>
                    </form>
                    <div class="table-list">
                        <?php foreach ($news as $newsItem): ?>
                            <div class="table-item">
                                <div class="table-item-layout">
                                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $newsItem['featured_image']); ?>" alt="" class="table-thumb">
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars((string) $newsItem['title']); ?></strong>
                                        <span><?php echo htmlspecialchars((string) ($newsItem['published_at'] ?? '')); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?edit=news&id=<?php echo (int) $newsItem['id']; ?>" class="action-link">Edit</a>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="action" value="delete_news">
                                        <input type="hidden" name="id" value="<?php echo (int) $newsItem['id']; ?>">
                                        <button type="submit" class="action-link action-button danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="panel dashboard-view-panel" id="gallery-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Gallery</p>
                            <h2><?php echo $editType === 'gallery' ? 'Edit Gallery Image' : 'Add Gallery Image'; ?></h2>
                        </div>
                    </div>
                    <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editType === 'gallery' ? 'update_gallery' : 'add_gallery'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $galleryForm['id']; ?>">
                        <label><span>Title</span><input type="text" name="title" value="<?php echo htmlspecialchars((string) $galleryForm['title']); ?>"></label>
                        <label><span>Image Path</span><input type="text" name="image_path" value="<?php echo htmlspecialchars((string) $galleryForm['image_path']); ?>"></label>
                        <label><span>Upload Image</span><input type="file" name="gallery_image_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" class="upload-input"></label>
                        <label><span>Caption</span><textarea name="caption" rows="4"><?php echo htmlspecialchars((string) $galleryForm['caption']); ?></textarea></label>
                        <label><span>Category</span><input type="text" name="category" value="<?php echo htmlspecialchars((string) $galleryForm['category']); ?>"></label>
                        <label class="checkbox"><input type="checkbox" name="is_featured"<?php echo ((int) $galleryForm['is_featured'] === 1) ? ' checked' : ''; ?>> <span>Featured image</span></label>
                        <button type="submit"><?php echo $editType === 'gallery' ? 'Update Image' : 'Add Image'; ?></button>
                    </form>
                    <div class="table-list">
                        <?php foreach ($gallery as $galleryItem): ?>
                            <div class="table-item">
                                <div class="table-item-layout">
                                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $galleryItem['image_path']); ?>" alt="" class="table-thumb">
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars((string) $galleryItem['title']); ?></strong>
                                        <span><?php echo htmlspecialchars((string) $galleryItem['category']); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?edit=gallery&id=<?php echo (int) $galleryItem['id']; ?>" class="action-link">Edit</a>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="action" value="delete_gallery">
                                        <input type="hidden" name="id" value="<?php echo (int) $galleryItem['id']; ?>">
                                        <button type="submit" class="action-link action-button danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            </section>

            <section class="admin-grid">
                <article class="panel dashboard-view-panel" id="pages-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Pages</p>
                            <h2><?php echo $editType === 'page' ? 'Edit Page Content' : 'Add Page Content'; ?></h2>
                        </div>
                    </div>
                    <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editType === 'page' ? 'update_page' : 'add_page'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $pageForm['id']; ?>">
                        <label><span>Title</span><input type="text" name="title" value="<?php echo htmlspecialchars((string) $pageForm['title']); ?>"></label>
                        <label><span>Slug</span><input type="text" name="slug" value="<?php echo htmlspecialchars((string) $pageForm['slug']); ?>"></label>
                        <label><span>Excerpt</span><textarea name="excerpt" rows="3"><?php echo htmlspecialchars((string) $pageForm['excerpt']); ?></textarea></label>
                        <label><span>Main Content</span><textarea name="content" rows="7"><?php echo htmlspecialchars((string) $pageForm['content']); ?></textarea></label>
                        <label><span>Banner Image Path</span><input type="text" name="banner_image" value="<?php echo htmlspecialchars((string) $pageForm['banner_image']); ?>"></label>
                        <label><span>Upload Banner Image</span><input type="file" name="banner_image_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" class="upload-input"></label>
                        <label><span>Status</span>
                            <select name="status">
                                <option value="published"<?php echo ((string) $pageForm['status'] === 'published') ? ' selected' : ''; ?>>Published</option>
                                <option value="draft"<?php echo ((string) $pageForm['status'] === 'draft') ? ' selected' : ''; ?>>Draft</option>
                            </select>
                        </label>
                        <button type="submit"><?php echo $editType === 'page' ? 'Update Page' : 'Add Page'; ?></button>
                    </form>
                    <div class="table-list">
                        <?php foreach ($pages as $pageItem): ?>
                            <div class="table-item">
                                <div class="table-item-layout">
                                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $pageItem['banner_image']); ?>" alt="" class="table-thumb">
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars((string) $pageItem['title']); ?></strong>
                                        <span><?php echo htmlspecialchars((string) $pageItem['slug']); ?> - <?php echo htmlspecialchars((string) $pageItem['status']); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?edit=page&id=<?php echo (int) $pageItem['id']; ?>" class="action-link">Edit</a>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="action" value="delete_page">
                                        <input type="hidden" name="id" value="<?php echo (int) $pageItem['id']; ?>">
                                        <button type="submit" class="action-link action-button danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="panel dashboard-view-panel" id="admissions-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Admissions</p>
                            <h2>Applications received</h2>
                        </div>
                    </div>
                    <div class="table-list">
                        <?php foreach ($admissions as $admission): ?>
                            <div class="table-item">
                                <strong><?php echo htmlspecialchars((string) $admission['applicant_name']); ?></strong>
                                <span><?php echo htmlspecialchars((string) ($admission['email'] ?? '')); ?> - <?php echo htmlspecialchars((string) $admission['created_at']); ?></span>
                                <div class="item-actions">
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="action" value="update_admission_status">
                                        <input type="hidden" name="id" value="<?php echo (int) $admission['id']; ?>">
                                        <select name="status">
                                            <option value="pending"<?php echo ((string) $admission['status'] === 'pending') ? ' selected' : ''; ?>>Pending</option>
                                            <option value="reviewed"<?php echo ((string) $admission['status'] === 'reviewed') ? ' selected' : ''; ?>>Reviewed</option>
                                            <option value="accepted"<?php echo ((string) $admission['status'] === 'accepted') ? ' selected' : ''; ?>>Accepted</option>
                                            <option value="rejected"<?php echo ((string) $admission['status'] === 'rejected') ? ' selected' : ''; ?>>Rejected</option>
                                        </select>
                                        <button type="submit" class="action-link action-button">Save</button>
                                    </form>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="action" value="delete_admission">
                                        <input type="hidden" name="id" value="<?php echo (int) $admission['id']; ?>">
                                        <button type="submit" class="action-link action-button danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            </section>

            <section class="admin-grid">
                <article class="panel dashboard-view-panel" id="programs-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Programs</p>
                            <h2><?php echo $editType === 'program' ? 'Edit Program' : 'Add Program'; ?></h2>
                        </div>
                    </div>
                    <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editType === 'program' ? 'update_program' : 'add_program'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $programForm['id']; ?>">
                        <label><span>Title</span><input type="text" name="program_title" value="<?php echo htmlspecialchars((string) $programForm['title']); ?>"></label>
                        <label><span>Slug</span><input type="text" name="program_slug" value="<?php echo htmlspecialchars((string) $programForm['slug']); ?>"></label>
                        <label><span>Summary</span><textarea name="program_summary" rows="4"><?php echo htmlspecialchars((string) $programForm['short_description']); ?></textarea></label>
                        <label><span>Description</span><textarea name="program_description" rows="5"><?php echo htmlspecialchars((string) $programForm['description']); ?></textarea></label>
                        <label><span>Duration</span><input type="text" name="program_duration" value="<?php echo htmlspecialchars((string) $programForm['duration']); ?>"></label>
                        <label><span>Department</span><input type="text" name="program_department" value="<?php echo htmlspecialchars((string) $programForm['department']); ?>"></label>
                        <label><span>Cover Image Path</span><input type="text" name="cover_image" value="<?php echo htmlspecialchars((string) $programForm['cover_image']); ?>"></label>
                        <label><span>Upload Cover Image</span><input type="file" name="program_cover_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" class="upload-input"></label>
                        <label><span>Status</span>
                            <select name="program_status">
                                <option value="active"<?php echo ((string) $programForm['status'] === 'active') ? ' selected' : ''; ?>>Active</option>
                                <option value="inactive"<?php echo ((string) $programForm['status'] === 'inactive') ? ' selected' : ''; ?>>Inactive</option>
                            </select>
                        </label>
                        <button type="submit"><?php echo $editType === 'program' ? 'Update Program' : 'Add Program'; ?></button>
                    </form>
                    <div class="table-list">
                        <?php foreach ($programs as $program): ?>
                            <div class="table-item">
                                <div class="table-item-layout">
                                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $program['cover_image']); ?>" alt="" class="table-thumb">
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars((string) $program['title']); ?></strong>
                                        <span><?php echo htmlspecialchars((string) $program['department']); ?> - <?php echo htmlspecialchars((string) $program['status']); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?edit=program&id=<?php echo (int) $program['id']; ?>" class="action-link">Edit</a>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="action" value="delete_program">
                                        <input type="hidden" name="id" value="<?php echo (int) $program['id']; ?>">
                                        <button type="submit" class="action-link action-button danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="panel dashboard-view-panel" id="staff-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Staff</p>
                            <h2><?php echo $editType === 'staff' ? 'Edit Team Member' : 'Add Team Member'; ?></h2>
                        </div>
                    </div>
                    <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editType === 'staff' ? 'update_staff' : 'add_staff'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $staffForm['id']; ?>">
                        <label><span>Full Name</span><input type="text" name="full_name" value="<?php echo htmlspecialchars((string) $staffForm['full_name']); ?>"></label>
                        <label><span>Job Title</span><input type="text" name="job_title" value="<?php echo htmlspecialchars((string) $staffForm['job_title']); ?>"></label>
                        <label><span>Bio</span><textarea name="bio" rows="5"><?php echo htmlspecialchars((string) $staffForm['bio']); ?></textarea></label>
                        <label><span>Photo Path</span><input type="text" name="photo" value="<?php echo htmlspecialchars((string) $staffForm['photo']); ?>"></label>
                        <label><span>Upload Photo</span><input type="file" name="photo_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" class="upload-input"></label>
                        <label><span>Display Order</span><input type="number" name="display_order" value="<?php echo (int) $staffForm['display_order']; ?>"></label>
                        <label class="checkbox"><input type="checkbox" name="is_featured"<?php echo ((int) $staffForm['is_featured'] === 1) ? ' checked' : ''; ?>> <span>Featured on website</span></label>
                        <button type="submit"><?php echo $editType === 'staff' ? 'Update Staff' : 'Add Staff'; ?></button>
                    </form>
                    <div class="table-list">
                        <?php foreach ($staff as $member): ?>
                            <div class="table-item">
                                <div class="table-item-layout">
                                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $member['photo']); ?>" alt="" class="table-thumb">
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars((string) $member['full_name']); ?></strong>
                                        <span><?php echo htmlspecialchars((string) $member['job_title']); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?edit=staff&id=<?php echo (int) $member['id']; ?>" class="action-link">Edit</a>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="action" value="delete_staff">
                                        <input type="hidden" name="id" value="<?php echo (int) $member['id']; ?>">
                                        <button type="submit" class="action-link action-button danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            </section>
        </main>
    </div>
    <script src="/MUBUGA-TSS/assets/js/admin.js"></script>
</body>
</html>
