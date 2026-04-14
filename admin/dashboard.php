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
            $settings = [
                'school_name' => trim((string) ($_POST['school_name'] ?? '')),
                'school_motto' => trim((string) ($_POST['school_motto'] ?? '')),
                'school_email' => trim((string) ($_POST['school_email'] ?? '')),
                'school_phone' => trim((string) ($_POST['school_phone'] ?? '')),
                'school_address' => trim((string) ($_POST['school_address'] ?? '')),
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

        if ($action === 'add_news') {
            $title = trim((string) ($_POST['title'] ?? ''));
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
            $featuredImage = trim((string) ($_POST['featured_image'] ?? 'assets/images/mb1.jfif'));
            $featuredImage = handleAdminImageUpload('featured_image_upload', $featuredImage);
            $stmt = $pdo->prepare('INSERT INTO news (title, slug, summary, featured_image, published_at, status) VALUES (:title, :slug, :summary, :featured_image, NOW(), "published")');
            $stmt->execute([
                'title' => $title,
                'slug' => $slug,
                'summary' => trim((string) ($_POST['summary'] ?? '')),
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
            $stmt = $pdo->prepare('UPDATE news SET title = :title, slug = :slug, summary = :summary, featured_image = :featured_image WHERE id = :id');
            $stmt->execute([
                'id' => $id,
                'title' => $title,
                'slug' => $slug,
                'summary' => trim((string) ($_POST['summary'] ?? '')),
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
    } catch (Throwable $exception) {
        $error = 'The update could not be saved. Please check the values and try again.';
    }
}

$settings = [];
$staff = [];
$news = [];
$gallery = [];
$staffForm = [
    'id' => 0,
    'full_name' => '',
    'job_title' => '',
    'bio' => '',
    'photo' => 'assets/images/master.jpeg',
    'display_order' => 0,
    'is_featured' => 0,
];
$newsForm = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'summary' => '',
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

if ($pdo instanceof PDO) {
    foreach ($pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll() as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    $staff = $pdo->query('SELECT id, full_name, job_title, bio, photo, display_order, is_featured, status FROM staff ORDER BY display_order ASC, id DESC')->fetchAll();
    $news = $pdo->query('SELECT id, title, slug, summary, featured_image, published_at FROM news ORDER BY id DESC')->fetchAll();
    $gallery = $pdo->query('SELECT id, title, image_path, caption, category, is_featured FROM gallery ORDER BY id DESC')->fetchAll();

    if ($editType === 'staff' && $editId > 0) {
        $stmt = $pdo->prepare('SELECT id, full_name, job_title, bio, photo, display_order, is_featured FROM staff WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $editId]);
        $staffForm = $stmt->fetch() ?: $staffForm;
    }

    if ($editType === 'news' && $editId > 0) {
        $stmt = $pdo->prepare('SELECT id, title, slug, summary, featured_image FROM news WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $editId]);
        $newsForm = $stmt->fetch() ?: $newsForm;
    }

    if ($editType === 'gallery' && $editId > 0) {
        $stmt = $pdo->prepare('SELECT id, title, image_path, caption, category, is_featured FROM gallery WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $editId]);
        $galleryForm = $stmt->fetch() ?: $galleryForm;
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
                        <button type="submit">Save Settings</button>
                    </form>
                </article>

                <article class="panel">
                    <h3><?php echo $editType === 'staff' ? 'Edit Staff' : 'Add Staff'; ?></h3>
                    <form method="post" class="admin-form">
                        <input type="hidden" name="action" value="<?php echo $editType === 'staff' ? 'update_staff' : 'add_staff'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $staffForm['id']; ?>">
                        <label><span>Full Name</span><input type="text" name="full_name" value="<?php echo htmlspecialchars((string) $staffForm['full_name']); ?>" required></label>
                        <label><span>Job Title</span><input type="text" name="job_title" value="<?php echo htmlspecialchars((string) $staffForm['job_title']); ?>" required></label>
                        <label><span>Bio</span><textarea name="bio" rows="4"><?php echo htmlspecialchars((string) $staffForm['bio']); ?></textarea></label>
                        <label><span>Photo Path</span><input type="text" name="photo" value="<?php echo htmlspecialchars((string) $staffForm['photo']); ?>"></label>
                        <label><span>Upload Photo</span><input type="file" name="photo_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif"></label>
                        <label><span>Display Order</span><input type="number" name="display_order" value="<?php echo (int) $staffForm['display_order']; ?>"></label>
                        <label class="checkbox"><input type="checkbox" name="is_featured" value="1" <?php echo (int) $staffForm['is_featured'] === 1 ? 'checked' : ''; ?>> Featured leader</label>
                        <button type="submit"><?php echo $editType === 'staff' ? 'Update Staff' : 'Add Staff'; ?></button>
                    </form>
                    <div class="table-list">
                        <?php foreach ($staff as $member): ?>
                            <div class="table-item">
                                <strong><?php echo htmlspecialchars($member['full_name']); ?></strong>
                                <span><?php echo htmlspecialchars($member['job_title']); ?></span>
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
                        <label><span>Summary</span><textarea name="summary" rows="4"><?php echo htmlspecialchars((string) $newsForm['summary']); ?></textarea></label>
                        <label><span>Featured Image Path</span><input type="text" name="featured_image" value="<?php echo htmlspecialchars((string) $newsForm['featured_image']); ?>"></label>
                        <label><span>Upload Featured Image</span><input type="file" name="featured_image_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif"></label>
                        <button type="submit"><?php echo $editType === 'news' ? 'Update News' : 'Publish News'; ?></button>
                    </form>
                    <div class="table-list">
                        <?php foreach ($news as $item): ?>
                            <div class="table-item">
                                <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                <span><?php echo htmlspecialchars((string) $item['published_at']); ?></span>
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
                        <label><span>Image Path</span><input type="text" name="image_path" value="<?php echo htmlspecialchars((string) $galleryForm['image_path']); ?>" required></label>
                        <label><span>Upload Image</span><input type="file" name="gallery_image_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif"></label>
                        <label><span>Caption</span><textarea name="caption" rows="3"><?php echo htmlspecialchars((string) $galleryForm['caption']); ?></textarea></label>
                        <label><span>Category</span><input type="text" name="category" value="<?php echo htmlspecialchars((string) $galleryForm['category']); ?>"></label>
                        <label class="checkbox"><input type="checkbox" name="is_featured" value="1" <?php echo (int) $galleryForm['is_featured'] === 1 ? 'checked' : ''; ?>> Featured image</label>
                        <button type="submit"><?php echo $editType === 'gallery' ? 'Update Image' : 'Add Image'; ?></button>
                    </form>
                    <div class="table-list">
                        <?php foreach ($gallery as $image): ?>
                            <div class="table-item">
                                <strong><?php echo htmlspecialchars($image['title']); ?></strong>
                                <span><?php echo htmlspecialchars($image['image_path']); ?></span>
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
            </section>
        </main>
    </div>
</body>
</html>
