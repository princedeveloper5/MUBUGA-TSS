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
    $programs = $pdo->query('SELECT id, title, slug, short_description, duration, department, cover_image, status FROM programs ORDER BY id DESC')->fetchAll();
    $staff = $pdo->query('SELECT id, full_name, job_title, bio, photo, display_order, is_featured, status FROM staff ORDER BY display_order ASC, id DESC')->fetchAll();
    $news = $pdo->query('SELECT id, title, slug, summary, content, featured_image, published_at FROM news ORDER BY id DESC')->fetchAll();
    $gallery = $pdo->query('SELECT id, title, image_path, caption, category, is_featured FROM gallery ORDER BY id DESC')->fetchAll();
    $admissions = $pdo->query('SELECT id, applicant_name, email, preferred_program_id, status, created_at FROM admissions ORDER BY created_at DESC')->fetchAll();
    $pages = $pdo->query('SELECT id, title, slug, banner_image, status FROM pages ORDER BY slug ASC, id DESC')->fetchAll();

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
<body class="admin-page">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <p class="admin-eyebrow">Mubuga TSS</p>
            <h1>Admin Panel</h1>
            <p>Manage the main website content from one place.</p>
            <a href="/MUBUGA-TSS/admin/submissions.php" class="logout-link">View Submissions</a>
            <a href="/MUBUGA-TSS/admin/logout.php" class="logout-link">Log Out</a>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <div>
                    <h2>Welcome, <?php echo htmlspecialchars(currentAdmin()['full_name'] ?? 'Admin'); ?></h2>
                    <p>Update settings, staff, news, and gallery content.</p>
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
                    <h3>School Settings</h3>
                    <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_settings">
                        <label><span>School Name</span><input type="text" name="school_name" value="<?php echo htmlspecialchars($settings['school_name'] ?? ''); ?>"></label>
                        <label><span>Motto</span><input type="text" name="school_motto" value="<?php echo htmlspecialchars($settings['school_motto'] ?? ''); ?>"></label>
                        <label><span>Email</span><input type="email" name="school_email" value="<?php echo htmlspecialchars($settings['school_email'] ?? ''); ?>"></label>
                        <label><span>Phone</span><input type="text" name="school_phone" value="<?php echo htmlspecialchars($settings['school_phone'] ?? ''); ?>"></label>
                        <label><span>Address</span><input type="text" name="school_address" value="<?php echo htmlspecialchars($settings['school_address'] ?? ''); ?>"></label>
                        <label><span>Logo Path</span><input type="text" name="school_logo" value="<?php echo htmlspecialchars($settings['school_logo'] ?? ''); ?>" data-image-path="school-logo"></label>
                        <label><span>Upload Logo</span><input type="file" name="school_logo_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" data-image-upload="school-logo" data-upload-drop></label>
                        <div class="image-preview-card" data-image-preview="school-logo"></div>
                        <label><span>Facebook URL</span><input type="url" name="school_facebook" value="<?php echo htmlspecialchars($settings['school_facebook'] ?? ''); ?>"></label>
                        <label><span>Instagram URL</span><input type="url" name="school_instagram" value="<?php echo htmlspecialchars($settings['school_instagram'] ?? ''); ?>"></label>
                        <button type="submit">Save Settings</button>
                    </form>
                </article>

                <article class="panel">
                    <h3><?php echo $editType === 'program' ? 'Edit Program' : 'Add Program'; ?></h3>
                    <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editType === 'program' ? 'update_program' : 'add_program'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $programForm['id']; ?>">
                        <label><span>Program Title</span><input type="text" name="program_title" value="<?php echo htmlspecialchars((string) $programForm['title']); ?>" required></label>
                        <label><span>Slug</span><input type="text" name="program_slug" value="<?php echo htmlspecialchars((string) $programForm['slug']); ?>" required></label>
                        <label><span>Summary</span><textarea name="program_summary" rows="3"><?php echo htmlspecialchars((string) $programForm['short_description']); ?></textarea></label>
                        <label><span>Description</span><textarea name="program_description" rows="4"><?php echo htmlspecialchars((string) $programForm['description']); ?></textarea></label>
                        <label><span>Duration</span><input type="text" name="program_duration" value="<?php echo htmlspecialchars((string) $programForm['duration']); ?>"></label>
                        <label><span>Department</span><input type="text" name="program_department" value="<?php echo htmlspecialchars((string) $programForm['department']); ?>"></label>
                        <label><span>Cover Image Path</span><input type="text" name="cover_image" value="<?php echo htmlspecialchars((string) $programForm['cover_image']); ?>" data-image-path="program-cover"></label>
                        <label><span>Upload Cover Image</span><input type="file" name="program_cover_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" data-image-upload="program-cover" data-upload-drop></label>
                        <div class="image-preview-card" data-image-preview="program-cover"></div>
                        <label><span>Status</span>
                            <select name="program_status">
                                <option value="active" <?php echo ($programForm['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($programForm['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </label>
                        <button type="submit"><?php echo $editType === 'program' ? 'Update Program' : 'Add Program'; ?></button>
                    </form>
                    <div class="table-list">
                        <?php foreach ($programs as $program): ?>
                            <div class="table-item">
                                <div class="table-item-layout">
                                    <?php if (!empty($program['cover_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($program['cover_image']); ?>" alt="<?php echo htmlspecialchars($program['title']); ?>" class="table-thumb">
                                    <?php endif; ?>
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars($program['title']); ?></strong>
                                        <span><?php echo htmlspecialchars($program['slug']); ?></span>
                                        <?php if (!empty($program['duration'])): ?>
                                            <span><?php echo htmlspecialchars($program['duration']); ?></span>
                                        <?php endif; ?>
                                        <span class="status status-<?php echo htmlspecialchars($program['status'] === 'active' ? 'accepted' : 'draft'); ?>">
                                            <?php echo htmlspecialchars(ucfirst($program['status'])); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?edit=program&id=<?php echo (int) $program['id']; ?>" class="action-link">Edit</a>
                                    <form method="post" class="inline-form" onsubmit="return confirm('Delete this program?');">
                                        <input type="hidden" name="action" value="delete_program">
                                        <input type="hidden" name="id" value="<?php echo (int) $program['id']; ?>">
                                        <button type="submit" class="danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="panel">
                    <h3><?php echo $editType === 'staff' ? 'Edit Staff' : 'Add Staff'; ?></h3>
                    <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editType === 'staff' ? 'update_staff' : 'add_staff'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $staffForm['id']; ?>">
                        <label><span>Full Name</span><input type="text" name="full_name" value="<?php echo htmlspecialchars((string) $staffForm['full_name']); ?>" required></label>
                        <label><span>Job Title</span><input type="text" name="job_title" value="<?php echo htmlspecialchars((string) $staffForm['job_title']); ?>" required></label>
                        <label><span>Bio</span><textarea name="bio" rows="4"><?php echo htmlspecialchars((string) $staffForm['bio']); ?></textarea></label>
                        <label><span>Photo Path</span><input type="text" name="photo" value="<?php echo htmlspecialchars((string) $staffForm['photo']); ?>" data-image-path="staff-photo"></label>
                        <label><span>Upload Photo</span><input type="file" name="photo_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" data-image-upload="staff-photo" data-upload-drop></label>
                        <div class="image-preview-card" data-image-preview="staff-photo"></div>
                        <label><span>Display Order</span><input type="number" name="display_order" value="<?php echo (int) $staffForm['display_order']; ?>"></label>
                        <label class="checkbox"><input type="checkbox" name="is_featured" value="1" <?php echo (int) $staffForm['is_featured'] === 1 ? 'checked' : ''; ?>> Featured leader</label>
                        <button type="submit"><?php echo $editType === 'staff' ? 'Update Staff' : 'Add Staff'; ?></button>
                    </form>
                    <div class="table-list">
                        <?php foreach ($staff as $member): ?>
                            <div class="table-item">
                                <div class="table-item-layout">
                                    <?php if (!empty($member['photo'])): ?>
                                        <img src="<?php echo htmlspecialchars($member['photo']); ?>" alt="<?php echo htmlspecialchars($member['full_name']); ?>" class="table-thumb">
                                    <?php endif; ?>
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars($member['full_name']); ?></strong>
                                        <span><?php echo htmlspecialchars($member['job_title']); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?edit=staff&id=<?php echo (int) $member['id']; ?>" class="action-link">Edit</a>
                                    <form method="post" class="inline-form" onsubmit="return confirm('Delete this staff member?');">
                                        <input type="hidden" name="action" value="delete_staff">
                                        <input type="hidden" name="id" value="<?php echo (int) $member['id']; ?>">
                                        <button type="submit" class="danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="panel">
                    <h3><?php echo $editType === 'news' ? 'Edit News' : 'Publish News'; ?></h3>
                    <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editType === 'news' ? 'update_news' : 'add_news'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $newsForm['id']; ?>">
                        <label><span>Title</span><input type="text" name="title" value="<?php echo htmlspecialchars((string) $newsForm['title']); ?>" required></label>
                        <label><span>Slug</span><input type="text" name="slug" value="<?php echo htmlspecialchars((string) $newsForm['slug']); ?>"></label>
                        <label><span>Summary</span><textarea name="summary" rows="4" data-editor><?php echo htmlspecialchars((string) $newsForm['summary']); ?></textarea></label>
                        <label><span>Full Content</span><textarea name="content" rows="8" data-editor><?php echo htmlspecialchars((string) $newsForm['content']); ?></textarea></label>
                        <label><span>Featured Image Path</span><input type="text" name="featured_image" value="<?php echo htmlspecialchars((string) $newsForm['featured_image']); ?>" data-image-path="news-featured"></label>
                        <label><span>Upload Featured Image</span><input type="file" name="featured_image_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" data-image-upload="news-featured" data-upload-drop></label>
                        <div class="image-preview-card" data-image-preview="news-featured"></div>
                        <button type="submit"><?php echo $editType === 'news' ? 'Update News' : 'Publish News'; ?></button>
                    </form>
                    <div class="table-list">
                        <?php foreach ($news as $item): ?>
                            <div class="table-item">
                                <div class="table-item-layout">
                                    <?php if (!empty($item['featured_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['featured_image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="table-thumb">
                                    <?php endif; ?>
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                        <span><?php echo htmlspecialchars((string) $item['published_at']); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?edit=news&id=<?php echo (int) $item['id']; ?>" class="action-link">Edit</a>
                                    <form method="post" class="inline-form" onsubmit="return confirm('Delete this news item?');">
                                        <input type="hidden" name="action" value="delete_news">
                                        <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                                        <button type="submit" class="danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="panel">
                    <h3><?php echo $editType === 'gallery' ? 'Edit Gallery Image' : 'Add Gallery Image'; ?></h3>
                    <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editType === 'gallery' ? 'update_gallery' : 'add_gallery'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $galleryForm['id']; ?>">
                        <label><span>Title</span><input type="text" name="title" value="<?php echo htmlspecialchars((string) $galleryForm['title']); ?>" required></label>
                        <label><span>Image Path</span><input type="text" name="image_path" value="<?php echo htmlspecialchars((string) $galleryForm['image_path']); ?>" required data-image-path="gallery-image"></label>
                        <label><span>Upload Image</span><input type="file" name="gallery_image_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" data-image-upload="gallery-image" data-upload-drop></label>
                        <div class="image-preview-card" data-image-preview="gallery-image"></div>
                        <label><span>Caption</span><textarea name="caption" rows="3"><?php echo htmlspecialchars((string) $galleryForm['caption']); ?></textarea></label>
                        <label><span>Category</span><input type="text" name="category" value="<?php echo htmlspecialchars((string) $galleryForm['category']); ?>"></label>
                        <label class="checkbox"><input type="checkbox" name="is_featured" value="1" <?php echo (int) $galleryForm['is_featured'] === 1 ? 'checked' : ''; ?>> Featured image</label>
                        <button type="submit"><?php echo $editType === 'gallery' ? 'Update Image' : 'Add Image'; ?></button>
                    </form>
                    <div class="table-list">
                        <?php foreach ($gallery as $image): ?>
                            <div class="table-item">
                                <div class="table-item-layout">
                                    <?php if (!empty($image['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($image['title']); ?>" class="table-thumb">
                                    <?php endif; ?>
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars($image['title']); ?></strong>
                                        <span><?php echo htmlspecialchars($image['image_path']); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?edit=gallery&id=<?php echo (int) $image['id']; ?>" class="action-link">Edit</a>
                                    <form method="post" class="inline-form" onsubmit="return confirm('Delete this gallery item?');">
                                        <input type="hidden" name="action" value="delete_gallery">
                                        <input type="hidden" name="id" value="<?php echo (int) $image['id']; ?>">
                                        <button type="submit" class="danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="panel">
                    <h3><?php echo $editType === 'page' ? 'Edit Page Content' : 'Manage Page Content'; ?></h3>
                    <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $editType === 'page' ? 'update_page' : 'add_page'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $pageForm['id']; ?>">
                        <label><span>Page Title</span><input type="text" name="title" value="<?php echo htmlspecialchars((string) $pageForm['title']); ?>" required></label>
                        <label><span>Slug</span><input type="text" name="slug" value="<?php echo htmlspecialchars((string) $pageForm['slug']); ?>" placeholder="about-us" required></label>
                        <label><span>Excerpt</span><textarea name="excerpt" rows="3" data-editor><?php echo htmlspecialchars((string) $pageForm['excerpt']); ?></textarea></label>
                        <label><span>Main Content</span><textarea name="content" rows="5" data-editor><?php echo htmlspecialchars((string) $pageForm['content']); ?></textarea></label>
                        <label><span>Banner Image Path</span><input type="text" name="banner_image" value="<?php echo htmlspecialchars((string) $pageForm['banner_image']); ?>" data-image-path="page-banner"></label>
                        <label><span>Upload Banner Image</span><input type="file" name="banner_image_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" data-image-upload="page-banner" data-upload-drop></label>
                        <div class="image-preview-card" data-image-preview="page-banner"></div>
                        <label><span>Status</span>
                            <select name="status">
                                <option value="published" <?php echo ($pageForm['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo ($pageForm['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </label>
                        <button type="submit"><?php echo $editType === 'page' ? 'Update Page' : 'Add Page'; ?></button>
                    </form>
                    <div class="table-list">
                        <?php if (empty($pages)): ?>
                            <p>No page records yet.</p>
                        <?php else: ?>
                            <?php foreach ($pages as $page): ?>
                                <div class="table-item">
                                    <div class="table-item-layout">
                                        <?php if (!empty($page['banner_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($page['banner_image']); ?>" alt="<?php echo htmlspecialchars($page['title']); ?>" class="table-thumb">
                                        <?php endif; ?>
                                        <div class="table-item-content">
                                            <strong><?php echo htmlspecialchars($page['title']); ?></strong>
                                            <span><?php echo htmlspecialchars($page['slug']); ?></span>
                                            <span class="status status-<?php echo htmlspecialchars($page['status']); ?>"><?php echo htmlspecialchars(ucfirst($page['status'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="item-actions">
                                        <a href="/MUBUGA-TSS/admin/dashboard.php?edit=page&id=<?php echo (int) $page['id']; ?>" class="action-link">Edit</a>
                                        <form method="post" class="inline-form" onsubmit="return confirm('Delete this page content?');">
                                            <input type="hidden" name="action" value="delete_page">
                                            <input type="hidden" name="id" value="<?php echo (int) $page['id']; ?>">
                                            <button type="submit" class="danger-button">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>

                <article class="panel">
                    <h3>Admission Applications</h3>
                    <div class="table-list">
                        <?php if (empty($admissions)): ?>
                            <p>No admission applications yet.</p>
                        <?php else: ?>
                            <?php foreach ($admissions as $application): ?>
                                <div class="table-item">
                                    <strong><?php echo htmlspecialchars($application['applicant_name']); ?></strong>
                                    <span><?php echo htmlspecialchars($application['email']); ?></span>
                                    <span class="status status-<?php echo htmlspecialchars($application['status']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($application['status'])); ?>
                                    </span>
                                    <div class="item-actions">
                                        <form method="post" class="inline-form">
                                            <input type="hidden" name="action" value="update_admission_status">
                                            <input type="hidden" name="id" value="<?php echo (int) $application['id']; ?>">
                                            <select name="status" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $application['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="reviewed" <?php echo $application['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                                <option value="accepted" <?php echo $application['status'] === 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                                                <option value="rejected" <?php echo $application['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                        </form>
                                        <form method="post" class="inline-form" onsubmit="return confirm('Delete this application?');">
                                            <input type="hidden" name="action" value="delete_admission">
                                            <input type="hidden" name="id" value="<?php echo (int) $application['id']; ?>">
                                            <button type="submit" class="danger-button">Delete</button>
                                        </form>
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
