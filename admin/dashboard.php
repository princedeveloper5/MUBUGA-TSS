<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/admin_upload.php';

requireAdminLogin();

function adminNormalizeNewsCategory(string $category): string
{
    $normalized = strtolower(trim($category));
    return match ($normalized) {
        'event', 'events' => 'events',
        'announcement', 'announcements' => 'announcements',
        default => 'news',
    };
}

function adminEncodeNewsContent(string $content, string $category): string
{
    return '[category:' . adminNormalizeNewsCategory($category) . ']' . "\n" . trim($content);
}

function adminDecodeNewsContent(?string $content): array
{
    $rawContent = trim((string) $content);
    $category = 'news';

    if ($rawContent !== '' && preg_match('/^\[category:(events|announcements|news)\]\s*/i', $rawContent, $matches) === 1) {
        $category = adminNormalizeNewsCategory($matches[1]);
        $rawContent = trim((string) preg_replace('/^\[category:(events|announcements|news)\]\s*/i', '', $rawContent, 1));
    }

    return [
        'category' => $category,
        'content' => $rawContent,
    ];
}

function adminParseGalleryCategory(?string $category): array
{
    $raw = strtolower(trim((string) $category));
    $mediaType = 'image';
    $topic = 'campus';

    if ($raw !== '') {
        $parts = explode(':', $raw, 2);
        if (in_array($parts[0], ['image', 'video'], true)) {
            $mediaType = $parts[0];
            $topic = trim((string) ($parts[1] ?? '')) ?: $topic;
        } else {
            $topic = $raw;
        }
    }

    return [
        'media_type' => $mediaType,
        'category' => $topic,
    ];
}

function adminBuildGalleryCategory(string $mediaType, string $category): string
{
    $resolvedMediaType = strtolower(trim($mediaType)) === 'video' ? 'video' : 'image';
    $resolvedCategory = strtolower(trim($category)) ?: 'campus';
    return $resolvedMediaType . ':' . $resolvedCategory;
}

function adminIsAbsoluteMediaPath(string $path): bool
{
    return preg_match('~^(?:https?:)?//~i', trim($path)) === 1;
}

function adminIsVideoPath(string $path): bool
{
    return preg_match('/\.(mp4|webm|ogg)$/i', trim($path)) === 1;
}

function adminResolveMediaUrl(string $path): string
{
    $normalized = trim(str_replace('\\', '/', $path));
    if ($normalized === '') {
        return '/MUBUGA-TSS/assets/images/school view 1.jpg';
    }

    if (adminIsAbsoluteMediaPath($normalized)) {
        return $normalized;
    }

    return '/MUBUGA-TSS/' . ltrim($normalized, '/');
}

$pdo = getDatabaseConnection();
$message = '';
$error = '';
$editType = (string) ($_GET['edit'] ?? '');
$editId = (int) ($_GET['id'] ?? 0);
$newsFilter = strtolower(trim((string) ($_GET['news_filter'] ?? 'all')));
if (!in_array($newsFilter, ['all', 'published', 'draft'], true)) {
    $newsFilter = 'all';
}
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!$pdo instanceof PDO) {
    $error = 'Database connection failed.';
}

if ($requestMethod === 'POST' && $pdo instanceof PDO) {
    if (!adminVerifyCsrfToken((string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Security token mismatch. Refresh the page and try again.';
    } else {
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'update_settings') {
            $logoPath = trim((string) ($_POST['school_logo'] ?? ''));
            $logoPath = handleAdminImageUpload('school_logo_upload', $logoPath);
            $siteLogoSize = max(32, min(140, (int) ($_POST['site_logo_size'] ?? 52)));
            $adminLogoSize = max(20, min(80, (int) ($_POST['admin_logo_size'] ?? 34)));
            $settings = [
                'school_name' => trim((string) ($_POST['school_name'] ?? '')),
                'school_motto' => trim((string) ($_POST['school_motto'] ?? '')),
                'school_email' => trim((string) ($_POST['school_email'] ?? '')),
                'school_phone' => trim((string) ($_POST['school_phone'] ?? '')),
                'school_address' => trim((string) ($_POST['school_address'] ?? '')),
                'school_logo' => $logoPath,
                'site_logo_size' => (string) $siteLogoSize,
                'admin_logo_size' => (string) $adminLogoSize,
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
            $newsCategory = adminNormalizeNewsCategory((string) ($_POST['news_category'] ?? 'news'));
            $newsStatus = strtolower(trim((string) ($_POST['news_status'] ?? 'published'))) === 'draft' ? 'draft' : 'published';
            $publishedAt = $newsStatus === 'published' ? date('Y-m-d H:i:s') : null;
            $stmt = $pdo->prepare('INSERT INTO news (title, slug, summary, content, featured_image, published_at, status) VALUES (:title, :slug, :summary, :content, :featured_image, :published_at, :status)');
            $stmt->execute([
                'title' => $title,
                'slug' => $slug,
                'summary' => trim((string) ($_POST['summary'] ?? '')),
                'content' => adminEncodeNewsContent((string) ($_POST['content'] ?? ''), $newsCategory),
                'featured_image' => $featuredImage,
                'published_at' => $publishedAt,
                'status' => $newsStatus,
            ]);
            $message = ucfirst($newsCategory) . ' item saved as ' . $newsStatus . '.';
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
            $newsCategory = adminNormalizeNewsCategory((string) ($_POST['news_category'] ?? 'news'));
            $newsStatus = strtolower(trim((string) ($_POST['news_status'] ?? 'published'))) === 'draft' ? 'draft' : 'published';
            $stmt = $pdo->prepare('UPDATE news SET title = :title, slug = :slug, summary = :summary, content = :content, featured_image = :featured_image, status = :status, published_at = IF(:status = "published", COALESCE(published_at, NOW()), published_at) WHERE id = :id');
            $stmt->execute([
                'id' => $id,
                'title' => $title,
                'slug' => $slug,
                'summary' => trim((string) ($_POST['summary'] ?? '')),
                'content' => adminEncodeNewsContent((string) ($_POST['content'] ?? ''), $newsCategory),
                'featured_image' => $featuredImage,
                'status' => $newsStatus,
            ]);
            $message = ucfirst($newsCategory) . ' item updated as ' . $newsStatus . '.';
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
            $imagePath = handleAdminMediaUpload('gallery_image_upload', $imagePath, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif', 'mp4', 'webm', 'ogg']);
            $galleryCategory = adminBuildGalleryCategory((string) ($_POST['media_type'] ?? 'image'), (string) ($_POST['category'] ?? 'campus'));
            $stmt = $pdo->prepare('INSERT INTO gallery (title, image_path, caption, category, is_featured) VALUES (:title, :image_path, :caption, :category, :is_featured)');
            $stmt->execute([
                'title' => trim((string) ($_POST['title'] ?? '')),
                'image_path' => $imagePath,
                'caption' => trim((string) ($_POST['caption'] ?? '')),
                'category' => $galleryCategory,
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ]);
            $message = 'Gallery media added.';
        }

        if ($action === 'update_gallery') {
            $imagePath = trim((string) ($_POST['image_path'] ?? ''));
            $imagePath = handleAdminMediaUpload('gallery_image_upload', $imagePath, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif', 'mp4', 'webm', 'ogg']);
            $galleryCategory = adminBuildGalleryCategory((string) ($_POST['media_type'] ?? 'image'), (string) ($_POST['category'] ?? 'campus'));
            $stmt = $pdo->prepare('UPDATE gallery SET title = :title, image_path = :image_path, caption = :caption, category = :category, is_featured = :is_featured WHERE id = :id');
            $stmt->execute([
                'id' => (int) ($_POST['id'] ?? 0),
                'title' => trim((string) ($_POST['title'] ?? '')),
                'image_path' => $imagePath,
                'caption' => trim((string) ($_POST['caption'] ?? '')),
                'category' => $galleryCategory,
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ]);
            $message = 'Gallery media updated.';
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
}

$settings = [];
$programs = [];
$staff = [];
$news = [];
$filteredNews = [];
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
    'news_category' => 'news',
    'status' => 'published',
    'summary' => '',
    'content' => '',
    'featured_image' => 'assets/images/mb1.jfif',
];
$galleryForm = [
    'id' => 0,
    'title' => '',
    'image_path' => '',
    'caption' => '',
    'category' => 'campus',
    'media_type' => 'image',
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
    $news = $pdo->query('SELECT id, title, slug, summary, content, featured_image, published_at, status FROM news ORDER BY id DESC')->fetchAll();
    foreach ($news as &$newsItem) {
        $decodedNewsContent = adminDecodeNewsContent((string) ($newsItem['content'] ?? ''));
        $newsItem['category'] = $decodedNewsContent['category'];
        $newsItem['content'] = $decodedNewsContent['content'];
    }
    unset($newsItem);
    $filteredNews = array_values(array_filter($news, static function (array $item) use ($newsFilter): bool {
        if ($newsFilter === 'all') {
            return true;
        }

        return strtolower((string) ($item['status'] ?? 'published')) === $newsFilter;
    }));

    $gallery = $pdo->query('SELECT id, title, image_path, caption, category, is_featured FROM gallery ORDER BY id DESC')->fetchAll();
    foreach ($gallery as &$galleryItem) {
        $galleryMeta = adminParseGalleryCategory((string) ($galleryItem['category'] ?? ''));
        $galleryItem['media_type'] = $galleryMeta['media_type'];
        $galleryItem['category'] = $galleryMeta['category'];
    }
    unset($galleryItem);
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
        $stmt = $pdo->prepare('SELECT id, title, slug, summary, content, featured_image, status FROM news WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $editId]);
        $newsForm = $stmt->fetch() ?: $newsForm;
        $decodedNewsContent = adminDecodeNewsContent((string) ($newsForm['content'] ?? ''));
        $newsForm['news_category'] = $decodedNewsContent['category'];
        $newsForm['content'] = $decodedNewsContent['content'];
    }

    if ($editType === 'gallery' && $editId > 0) {
        $stmt = $pdo->prepare('SELECT id, title, image_path, caption, category, is_featured FROM gallery WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $editId]);
        $galleryForm = $stmt->fetch() ?: $galleryForm;
        $galleryMeta = adminParseGalleryCategory((string) ($galleryForm['category'] ?? ''));
        $galleryForm['media_type'] = $galleryMeta['media_type'];
        $galleryForm['category'] = $galleryMeta['category'];
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
$siteLogoSize = max(32, min(140, (int) ($settings['site_logo_size'] ?? 52)));
$adminLogoSize = max(20, min(80, (int) ($settings['admin_logo_size'] ?? 34)));
$settingsLogoPath = trim((string) ($settings['school_logo'] ?? $logoPath));
$settingsLogoPreviewUrl = adminResolveMediaUrl($settingsLogoPath !== '' ? $settingsLogoPath : $logoPath);
$imageMediaItems = array_values(array_filter($gallery, static function (array $item): bool {
        return ($item['media_type'] ?? 'image') === 'image';
    }));
    $videoMediaItems = array_values(array_filter($gallery, static function (array $item): bool {
        return ($item['media_type'] ?? 'image') === 'video';
    }));
    $announcementItems = array_values(array_filter($news, static function (array $item): bool {
        return (($item['category'] ?? 'news') === 'announcements');
    }));
    $imageCount = count($imageMediaItems);
    $videoCount = count($videoMediaItems);
    $announcementCount = count($announcementItems);
    $userCount = count($staff) + 1;
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mubuga TSS Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/MUBUGA-TSS/assets/css/admin.css">
    <link rel="stylesheet" href="/MUBUGA-TSS/assets/css/photo-viewer.css">
    <style>
        .dashboard-shell {
            grid-template-columns: 260px minmax(0, 1fr) !important;
            gap: 22px !important;
        }

        .dashboard-sidebar {
            width: 260px !important;
            min-width: 260px !important;
            padding: 24px 18px 20px !important;
            border-radius: 26px !important;
            background:
                radial-gradient(circle at top right, rgba(255,255,255,0.14), transparent 28%),
                linear-gradient(180deg, #2c5f94 0%, #18436e 100%) !important;
            box-shadow: 0 26px 60px rgba(7, 19, 33, 0.24) !important;
        }

        .dashboard-brand-block {
            display: grid !important;
            grid-template-columns: auto 1fr !important;
            gap: 14px !important;
            align-items: center !important;
            padding: 18px !important;
            border-radius: 22px !important;
            background: linear-gradient(180deg, rgba(255,255,255,0.15), rgba(255,255,255,0.08)) !important;
            border: 1px solid rgba(255,255,255,0.12) !important;
        }

        .dashboard-brand-copy {
            display: grid !important;
            gap: 2px !important;
        }

        .dashboard-brand-logo-image {
            display: block !important;
            width: <?php echo max(34, $adminLogoSize + 8); ?>px !important;
            height: <?php echo max(34, $adminLogoSize + 8); ?>px !important;
            object-fit: contain !important;
            border-radius: 14px !important;
            background: rgba(255,255,255,0.96) !important;
            padding: 5px !important;
            box-shadow: 0 10px 24px rgba(7, 19, 33, 0.22) !important;
        }

        .dashboard-brand-copy .admin-eyebrow,
        .dashboard-brand-copy p {
            display: block !important;
        }

        .dashboard-nav-section-label {
            display: none !important;
        }

        .dashboard-brand-copy h1 {
            color: #fff !important;
            font-size: 1.14rem !important;
        }

        .dashboard-brand-copy p,
        .dashboard-brand-copy .admin-eyebrow {
            color: rgba(255,255,255,0.78) !important;
        }

        .dashboard-header.dashboard-header-compact {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            gap: 16px !important;
            min-height: auto !important;
            padding: 16px 20px !important;
            border-radius: 24px !important;
            background:
                radial-gradient(circle at top right, rgba(144, 200, 255, 0.28), transparent 34%),
                linear-gradient(135deg, #f6fbff 0%, #e7f2ff 100%) !important;
            border: 1px solid rgba(61, 142, 232, 0.16) !important;
            box-shadow: 0 22px 48px rgba(15, 23, 42, 0.08) !important;
        }

        .dashboard-top-menu {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            flex-wrap: wrap !important;
            justify-content: flex-end !important;
            flex: 1 1 auto !important;
        }

        .dashboard-top-link {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            min-height: 44px !important;
            padding: 0.72rem 1rem !important;
            border-radius: 999px !important;
            background: rgba(255,255,255,0.92) !important;
            border: 1px solid rgba(61, 142, 232, 0.14) !important;
            color: #235789 !important;
            font-size: 0.88rem !important;
            font-weight: 700 !important;
            text-decoration: none !important;
            box-shadow: 0 10px 20px rgba(26, 72, 122, 0.08) !important;
            white-space: nowrap !important;
        }

        .dashboard-top-link svg {
            width: 16px !important;
            height: 16px !important;
            flex: 0 0 16px !important;
        }

        .dashboard-top-link:hover,
        .dashboard-top-link.is-active {
            background: linear-gradient(135deg, #3d8ee8 0%, #2163a6 100%) !important;
            color: #fff !important;
            border-color: transparent !important;
            box-shadow: 0 14px 28px rgba(33, 99, 166, 0.18) !important;
        }

        .dashboard-top-link-home {
            background: linear-gradient(135deg, #1f5fa3 0%, #153f6b 100%) !important;
            color: #fff !important;
            border-color: transparent !important;
            box-shadow: 0 14px 28px rgba(21, 63, 107, 0.22) !important;
            margin-left: 6px !important;
        }

        .dashboard-top-link-home:hover {
            background: linear-gradient(135deg, #2f7dd1 0%, #1f5fa3 100%) !important;
            color: #fff !important;
        }

        .dashboard-header-brand {
            display: inline-flex !important;
            align-items: center !important;
            gap: 12px !important;
            min-width: 0 !important;
        }

        .dashboard-header-brand img {
            width: <?php echo max(24, $adminLogoSize - 4); ?>px !important;
            height: <?php echo max(24, $adminLogoSize - 4); ?>px !important;
            object-fit: contain !important;
            border-radius: 10px !important;
            background: #fff !important;
            padding: 3px !important;
            box-shadow: 0 8px 18px rgba(26, 72, 122, 0.12) !important;
        }

        .dashboard-header-brand span {
            color: #1f4b79 !important;
            font-size: 0.92rem !important;
            font-weight: 800 !important;
            letter-spacing: 0.01em !important;
        }

        .dashboard-hero-panel {
            display: block !important;
            background: linear-gradient(135deg, #1d5d98 0%, #3d8ee8 100%) !important;
            color: #fff !important;
            padding: 28px 32px !important;
            border-radius: 24px !important;
            box-shadow: 0 22px 46px rgba(19, 71, 121, 0.28) !important;
            overflow: hidden !important;
        }

        .dashboard-hero-panel h2,
        .dashboard-hero-panel p,
        .dashboard-hero-panel span {
            color: inherit !important;
        }

        .dashboard-welcome {
            display: grid !important;
            gap: 12px !important;
            max-width: 760px !important;
        }

        .dashboard-welcome h2 {
            margin: 0 !important;
            font-size: clamp(2rem, 3.2vw, 3rem) !important;
            line-height: 1.06 !important;
            letter-spacing: -0.03em !important;
        }

        .dashboard-welcome p {
            margin: 0 !important;
            max-width: 620px !important;
            font-size: 1rem !important;
            line-height: 1.45 !important;
            color: rgba(255,255,255,0.92) !important;
        }

        .dashboard-hero-actions {
            display: none !important;
        }

        .dashboard-stats-strip {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 14px !important;
        }

        .dashboard-hero-meta {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 10px !important;
            margin-top: 4px !important;
        }

        .dashboard-hero-meta span {
            display: inline-flex !important;
            align-items: center !important;
            padding: 0.55rem 0.85rem !important;
            border-radius: 999px !important;
            background: rgba(255,255,255,0.16) !important;
            border: 1px solid rgba(255,255,255,0.14) !important;
            color: #fff !important;
            font-size: 0.84rem !important;
            font-weight: 600 !important;
        }

        .dashboard-view-panel,
        .dashboard-showcase-panel,
        .panel {
            border-radius: 24px !important;
            box-shadow: 0 22px 54px rgba(15, 23, 42, 0.06) !important;
        }

        .panel-top {
            padding-bottom: 16px !important;
            border-bottom: 1px solid rgba(148, 163, 184, 0.16) !important;
        }

        .admin-form {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 16px 18px !important;
            align-items: start !important;
        }

        .admin-form label {
            display: grid !important;
            gap: 6px !important;
            padding: 16px !important;
            border-radius: 18px !important;
            background: linear-gradient(180deg, rgba(248,251,255,0.98), rgba(255,255,255,0.98)) !important;
            border: 1px solid rgba(148, 163, 184, 0.16) !important;
        }

        .admin-form label > span {
            color: #1b4876 !important;
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.01em !important;
        }

        .admin-form label.checkbox {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }

        .admin-form input[type="text"],
        .admin-form input[type="email"],
        .admin-form input[type="url"],
        .admin-form input[type="number"],
        .admin-form select,
        .admin-form textarea {
            width: 100% !important;
            padding: 0.95rem 1rem !important;
            border-radius: 14px !important;
            border: 1px solid rgba(148, 163, 184, 0.22) !important;
            background: #ffffff !important;
            color: #173c61 !important;
            font-size: 0.95rem !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.88) !important;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease !important;
        }

        .admin-form input:focus,
        .admin-form select:focus,
        .admin-form textarea:focus {
            outline: none !important;
            border-color: rgba(61, 142, 232, 0.5) !important;
            box-shadow: 0 0 0 4px rgba(61, 142, 232, 0.12) !important;
            transform: translateY(-1px) !important;
        }

        .admin-form textarea,
        .admin-form .upload-input,
        .admin-form button[type="submit"] {
            grid-column: 1 / -1 !important;
        }

        .upload-input {
            padding: 14px !important;
            border-radius: 14px !important;
            border: 1px dashed rgba(59, 130, 246, 0.28) !important;
            background: rgba(239, 246, 255, 0.88) !important;
        }

        .admin-form button[type="submit"] {
            min-height: 52px !important;
            padding: 0.95rem 1.3rem !important;
            border: 0 !important;
            border-radius: 16px !important;
            background: linear-gradient(135deg, #2f7dd1 0%, #1f5fa3 100%) !important;
            color: #fff !important;
            font-size: 0.95rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.01em !important;
            box-shadow: 0 16px 30px rgba(31, 95, 163, 0.2) !important;
            cursor: pointer !important;
        }

        .admin-form button[type="submit"]:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 18px 32px rgba(31, 95, 163, 0.24) !important;
        }

        .logo-preview-grid {
            grid-column: 1 / -1 !important;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 16px !important;
        }

        .logo-preview-card {
            display: grid !important;
            gap: 14px !important;
            padding: 18px !important;
            border-radius: 20px !important;
            background: linear-gradient(180deg, rgba(244, 249, 255, 0.98), rgba(255, 255, 255, 0.98)) !important;
            border: 1px solid rgba(148, 163, 184, 0.16) !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.85) !important;
        }

        .logo-preview-card strong {
            color: #113457 !important;
            font-size: 0.96rem !important;
        }

        .logo-preview-card p {
            margin: 0 !important;
            color: #6b7f95 !important;
            font-size: 0.82rem !important;
        }

        .logo-preview-stage {
            min-height: 168px !important;
            display: grid !important;
            place-items: center !important;
            padding: 20px !important;
            border-radius: 18px !important;
            background:
                linear-gradient(135deg, rgba(27, 74, 124, 0.06), rgba(61, 142, 232, 0.12)),
                linear-gradient(180deg, #ffffff, #eef6ff) !important;
            border: 1px dashed rgba(59, 130, 246, 0.22) !important;
            overflow: hidden !important;
        }

        .logo-preview-stage.admin-stage {
            background:
                radial-gradient(circle at top right, rgba(255,255,255,0.12), transparent 30%),
                linear-gradient(180deg, #2c5f94 0%, #18436e 100%) !important;
        }

        .logo-preview-stage img {
            display: block !important;
            max-width: 100% !important;
            height: auto !important;
            object-fit: contain !important;
            transition: width 0.18s ease !important;
        }

        .logo-preview-meta {
            display: flex !important;
            justify-content: space-between !important;
            gap: 12px !important;
            align-items: center !important;
            flex-wrap: wrap !important;
        }

        .logo-preview-meta code {
            padding: 6px 10px !important;
            border-radius: 999px !important;
            background: rgba(59, 130, 246, 0.08) !important;
            color: #1e63a6 !important;
            font-size: 0.76rem !important;
        }

        .table-item {
            border-radius: 18px !important;
            border: 1px solid rgba(148, 163, 184, 0.16) !important;
            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,251,255,0.98)) !important;
        }

        .management-section {
            margin-top: 22px !important;
            padding: 18px !important;
            border-radius: 22px !important;
            background: linear-gradient(180deg, rgba(248, 251, 255, 0.96), rgba(255, 255, 255, 0.96)) !important;
            border: 1px solid rgba(148, 163, 184, 0.14) !important;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.88) !important;
        }

        .management-section-header {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 12px !important;
            margin-bottom: 16px !important;
        }

        .management-section-header strong {
            color: #153e69 !important;
            font-size: 1rem !important;
            letter-spacing: -0.01em !important;
        }

        .management-section-header span {
            color: #6f87a0 !important;
            font-size: 0.82rem !important;
        }

        .management-section-header .management-tag {
            display: inline-flex !important;
            align-items: center !important;
            padding: 0.35rem 0.7rem !important;
            border-radius: 999px !important;
            background: rgba(61, 142, 232, 0.08) !important;
            color: #2e6da9 !important;
            font-weight: 700 !important;
            font-size: 0.72rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.06em !important;
        }

        .admin-form {
            margin-top: 0 !important;
        }

        .table-list {
            margin-top: 0 !important;
            display: grid !important;
            gap: 14px !important;
        }

        .table-item {
            padding: 16px !important;
        }

        .table-item-layout {
            display: grid !important;
            grid-template-columns: 88px minmax(0, 1fr) !important;
            gap: 14px !important;
            align-items: center !important;
        }

        .table-thumb {
            width: 88px !important;
            height: 72px !important;
            border-radius: 16px !important;
            object-fit: cover !important;
            background: #dbeaff !important;
            border: 1px solid rgba(61, 142, 232, 0.14) !important;
            box-shadow: 0 10px 18px rgba(25, 74, 124, 0.08) !important;
        }

        .table-item-content {
            display: grid !important;
            gap: 5px !important;
            min-width: 0 !important;
        }

        .table-item-content strong {
            color: #173c61 !important;
            font-size: 1rem !important;
            line-height: 1.25 !important;
        }

        .table-item-content span {
            color: #6c839b !important;
            font-size: 0.84rem !important;
            line-height: 1.45 !important;
        }

        .item-actions {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            flex-wrap: wrap !important;
            margin-top: 14px !important;
        }

        .action-link,
        .action-button {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 40px !important;
            padding: 0.7rem 1rem !important;
            border-radius: 12px !important;
            border: 1px solid rgba(61, 142, 232, 0.14) !important;
            background: rgba(239, 246, 255, 0.92) !important;
            color: #225b92 !important;
            font-size: 0.84rem !important;
            font-weight: 700 !important;
            text-decoration: none !important;
            box-shadow: 0 8px 18px rgba(31, 95, 163, 0.06) !important;
            cursor: pointer !important;
        }

        .danger-button {
            background: rgba(255, 241, 242, 0.98) !important;
            border-color: rgba(244, 114, 182, 0.12) !important;
            color: #c24168 !important;
        }

        .news-filter-bar {
            display: flex !important;
            gap: 10px !important;
            flex-wrap: wrap !important;
            margin-top: 18px !important;
        }

        .news-filter-link {
            display: inline-flex !important;
            align-items: center !important;
            padding: 0.58rem 0.92rem !important;
            border-radius: 999px !important;
            background: rgba(239, 246, 255, 0.9) !important;
            border: 1px solid rgba(61, 142, 232, 0.12) !important;
            color: #255a8f !important;
            font-size: 0.8rem !important;
            font-weight: 700 !important;
            text-decoration: none !important;
        }

        .news-filter-link.is-active {
            background: linear-gradient(135deg, #3d8ee8 0%, #2163a6 100%) !important;
            color: #fff !important;
            border-color: transparent !important;
        }

        .alert {
            border-radius: 18px !important;
            padding: 14px 16px !important;
            margin-bottom: 18px !important;
            font-weight: 600 !important;
            box-shadow: 0 14px 24px rgba(15, 23, 42, 0.05) !important;
        }

        .alert.success {
            background: linear-gradient(180deg, rgba(236, 253, 245, 0.96), rgba(255,255,255,0.96)) !important;
            border: 1px solid rgba(34, 197, 94, 0.16) !important;
            color: #17603f !important;
        }

        .alert.error {
            background: linear-gradient(180deg, rgba(255, 241, 242, 0.96), rgba(255,255,255,0.96)) !important;
            border: 1px solid rgba(244, 63, 94, 0.16) !important;
            color: #a63453 !important;
        }

        .dashboard-status-ribbon {
            display: none !important;
        }

        .dashboard-panel-subtitle {
            display: none !important;
        }

        .dashboard-showcase-top h3 {
            color: #123c67 !important;
            font-size: 1.06rem !important;
        }

        .dashboard-media-actions {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 12px !important;
            margin-bottom: 18px !important;
        }

        .dashboard-media-action {
            display: grid !important;
            gap: 8px !important;
            align-content: start !important;
            min-height: 116px !important;
            padding: 16px !important;
            border-radius: 20px !important;
            background: linear-gradient(135deg, #3d8ee8 0%, #2a71bf 100%) !important;
            color: #fff !important;
            box-shadow: 0 18px 30px rgba(33, 99, 166, 0.2) !important;
        }

        .dashboard-media-action::after {
            content: "Open upload panel" !important;
            color: rgba(255,255,255,0.76) !important;
            font-size: 0.78rem !important;
            font-weight: 500 !important;
        }

        .dashboard-media-action.dashboard-media-action-video {
            background: linear-gradient(135deg, #2163a6 0%, #174e84 100%) !important;
        }

        .dashboard-media-group {
            padding: 14px !important;
            border-radius: 20px !important;
            background: linear-gradient(180deg, #f8fbff, #eef6ff) !important;
            border: 1px solid rgba(61, 142, 232, 0.12) !important;
        }

        .dashboard-media-group-top {
            margin-bottom: 12px !important;
        }

        .dashboard-media-group-top .inline-link,
        .announcement-feed .feed-read-link {
            display: none !important;
        }

        .dashboard-media-thumbs {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 12px !important;
        }

        .dashboard-media-thumbs-video {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        .dashboard-thumb,
        .dashboard-thumb-video {
            overflow: hidden !important;
            border-radius: 16px !important;
            background: #dcecff !important;
            min-height: 150px !important;
            position: relative !important;
            border: 1px solid rgba(61, 142, 232, 0.12) !important;
            box-shadow: 0 12px 20px rgba(20, 59, 98, 0.08) !important;
        }

        .dashboard-thumb img,
        .dashboard-thumb video,
        .dashboard-thumb-video img,
        .dashboard-thumb-video video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            display: block !important;
        }

        .dashboard-thumb span,
        .dashboard-thumb-video span {
            position: absolute !important;
            left: 10px !important;
            right: 10px !important;
            bottom: 10px !important;
            padding: 8px 10px !important;
            border-radius: 12px !important;
            background: rgba(8, 28, 48, 0.72) !important;
            color: #fff !important;
            font-size: 0.76rem !important;
            line-height: 1.35 !important;
        }

        .dashboard-thumb-video-overlay {
            top: 12px !important;
            left: 12px !important;
            right: auto !important;
            bottom: auto !important;
            width: 38px !important;
            height: 38px !important;
            border-radius: 50% !important;
            background: rgba(8, 28, 48, 0.7) !important;
            color: #fff !important;
            display: grid !important;
            place-items: center !important;
        }

        @media (max-width: 1100px) {
            .dashboard-shell {
                grid-template-columns: 1fr !important;
            }

            .dashboard-sidebar {
                width: 100% !important;
                min-width: 0 !important;
            }

            .admin-form {
                grid-template-columns: 1fr !important;
            }

            .logo-preview-grid {
                grid-template-columns: 1fr !important;
            }

            .dashboard-header.dashboard-header-compact {
                justify-content: flex-start !important;
                gap: 14px !important;
                flex-direction: column !important;
                align-items: flex-start !important;
            }

            .dashboard-top-menu {
                width: 100% !important;
                justify-content: flex-start !important;
            }

            .dashboard-stats-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .dashboard-media-actions {
                grid-template-columns: 1fr !important;
            }
        }

        @media (max-width: 700px) {
            .dashboard-brand-block {
                grid-template-columns: 1fr !important;
                justify-items: center !important;
                text-align: center !important;
            }

            .dashboard-top-menu {
                width: 100% !important;
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }

            .dashboard-top-link {
                width: 100% !important;
            }

            .dashboard-top-link-home {
                margin-left: 0 !important;
                grid-column: 1 / -1 !important;
            }

            .dashboard-welcome h2 {
                font-size: 1.9rem !important;
            }

            .dashboard-stats-strip {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
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
                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="Mubuga TSS logo" class="dashboard-brand-logo-image">
                    <div class="dashboard-brand-copy">
                        <p class="admin-eyebrow">Mubuga TSS</p>
                        <h1>Admin Panel</h1>
                    </div>
                </div>

                <nav class="dashboard-nav" aria-label="Dashboard navigation">
                    <p class="dashboard-nav-section-label">Main</p>
                    <a href="#dashboard-panel" class="dashboard-nav-link is-active" data-tooltip="Dashboard" title="Dashboard" aria-label="Dashboard">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h5A1.5 1.5 0 0 1 12 5.5v5A1.5 1.5 0 0 1 10.5 12h-5A1.5 1.5 0 0 1 4 10.5v-5zM4 15.5A1.5 1.5 0 0 1 5.5 14h5a1.5 1.5 0 0 1 1.5 1.5v3A1.5 1.5 0 0 1 10.5 20h-5A1.5 1.5 0 0 1 4 18.5v-3zM14 5.5A1.5 1.5 0 0 1 15.5 4h3A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 14 18.5v-13z" fill="currentColor"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="#gallery-panel" class="dashboard-nav-link" data-tooltip="Media Library" title="Media Library" aria-label="Media Library">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 5a2 2 0 0 0-2 2v10.5A2.5 2.5 0 0 0 5.5 20h13a2.5 2.5 0 0 0 2.5-2.5V7a2 2 0 0 0-2-2H5zm2.5 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm11.5 9.5H5.5a.5.5 0 0 1-.39-.813l3.254-4.067a1 1 0 0 1 1.53.017l1.617 2.055 2.75-3.261a1 1 0 0 1 1.55.047l3.58 4.299A.5.5 0 0 1 19 17.5z" fill="currentColor"/></svg>
                        <span>Media Library</span>
                    </a>
                    <a href="#news-panel" class="dashboard-nav-link" data-tooltip="Announcements" title="Announcements" aria-label="Announcements">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 10.5 16.5 4v16L3 13.5v-3zm14.5 1.75h2.25a1.25 1.25 0 0 1 0 2.5H17.5v-2.5zM5.75 14.1h2.2l1.25 4.15H7.1L5.75 14.1z" fill="currentColor"/></svg>
                        <span>Announcements</span>
                    </a>
                    <a href="#staff-panel" class="dashboard-nav-link" data-tooltip="Manage Users" title="Manage Users" aria-label="Manage Users">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm-7 8a7 7 0 1 1 14 0H5zm14.5-9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM18 20c0-2.02-.76-3.86-2.01-5.26A6 6 0 0 1 22 20h-4z" fill="currentColor"/></svg>
                        <span>Manage Users</span>
                    </a>
                    <a href="#settings-panel" class="dashboard-nav-link" data-tooltip="Settings" title="Settings" aria-label="Settings">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.06-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.03 7.03 0 0 0-1.63-.94l-.36-2.54a.5.5 0 0 0-.5-.42h-3.84a.5.5 0 0 0-.5.42l-.36 2.54c-.58.23-1.13.54-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.71 8.84a.5.5 0 0 0 .12.64l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94L2.83 14.52a.5.5 0 0 0-.12.64l1.92 3.32a.5.5 0 0 0 .6.22l2.39-.96c.5.4 1.05.72 1.63.94l.36 2.54a.5.5 0 0 0 .5.42h3.84a.5.5 0 0 0 .5-.42l.36-2.54c.58-.23 1.13-.54 1.63-.94l2.39.96a.5.5 0 0 0 .6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58zM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5z" fill="currentColor"/></svg>
                        <span>Settings</span>
                    </a>
                    <p class="dashboard-nav-section-label dashboard-nav-section-label-secondary">More</p>
                    <a href="#admissions-panel" class="dashboard-nav-link dashboard-nav-link-secondary" data-tooltip="Admissions" title="Admissions" aria-label="Admissions">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2.5 4 6.5v5.8c0 4.8 3.12 9.18 8 10.7 4.88-1.52 8-5.9 8-10.7V6.5l-8-4zm-1 5h2v4h3v2h-5v-6z" fill="currentColor"/></svg>
                        <span>Admissions</span>
                    </a>
                </nav>
            </div>

            <div class="dashboard-sidebar-footer">
                <a href="/MUBUGA-TSS/admin/submissions.php" class="dashboard-nav-link dashboard-nav-link-secondary" data-tooltip="Submissions" title="Submissions" aria-label="Submissions">
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
            <header class="dashboard-header dashboard-header-compact">
                <div class="dashboard-header-brand">
                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="Mubuga TSS logo">
                    <span>Admin Navigation</span>
                </div>
                <nav class="dashboard-top-menu" aria-label="Admin quick navigation">
                    <a href="#dashboard-panel" class="dashboard-top-link dashboard-card-link is-active">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h5A1.5 1.5 0 0 1 12 5.5v5A1.5 1.5 0 0 1 10.5 12h-5A1.5 1.5 0 0 1 4 10.5v-5zM4 15.5A1.5 1.5 0 0 1 5.5 14h5a1.5 1.5 0 0 1 1.5 1.5v3A1.5 1.5 0 0 1 10.5 20h-5A1.5 1.5 0 0 1 4 18.5v-3zM14 5.5A1.5 1.5 0 0 1 15.5 4h3A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 14 18.5v-13z" fill="currentColor"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="#gallery-panel" class="dashboard-top-link dashboard-card-link">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 5a2 2 0 0 0-2 2v10.5A2.5 2.5 0 0 0 5.5 20h13a2.5 2.5 0 0 0 2.5-2.5V7a2 2 0 0 0-2-2H5zm2.5 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm11.5 9.5H5.5a.5.5 0 0 1-.39-.813l3.254-4.067a1 1 0 0 1 1.53.017l1.617 2.055 2.75-3.261a1 1 0 0 1 1.55.047l3.58 4.299A.5.5 0 0 1 19 17.5z" fill="currentColor"/></svg>
                        <span>Media</span>
                    </a>
                    <a href="#news-panel" class="dashboard-top-link dashboard-card-link">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 10.5 16.5 4v16L3 13.5v-3zm14.5 1.75h2.25a1.25 1.25 0 0 1 0 2.5H17.5v-2.5zM5.75 14.1h2.2l1.25 4.15H7.1L5.75 14.1z" fill="currentColor"/></svg>
                        <span>Updates</span>
                    </a>
                    <a href="#settings-panel" class="dashboard-top-link dashboard-card-link">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.06-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.03 7.03 0 0 0-1.63-.94l-.36-2.54a.5.5 0 0 0-.5-.42h-3.84a.5.5 0 0 0-.5.42l-.36 2.54c-.58.23-1.13.54-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.71 8.84a.5.5 0 0 0 .12.64l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94L2.83 14.52a.5.5 0 0 0-.12.64l1.92 3.32a.5.5 0 0 0 .6.22l2.39-.96c.5.4 1.05.72 1.63.94l.36 2.54a.5.5 0 0 0 .5.42h3.84a.5.5 0 0 0 .5-.42l.36-2.54c.58-.23 1.13-.54 1.63-.94l2.39.96a.5.5 0 0 0 .6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58zM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5z" fill="currentColor"/></svg>
                        <span>Settings</span>
                    </a>
                    <a href="/MUBUGA-TSS/index.php" class="dashboard-top-link dashboard-top-link-home" title="Open user homepage" aria-label="Open user homepage">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 5h5v5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 14 19 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19 14v4a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>User Homepage</span>
                    </a>
                </nav>
            </header>

            <?php if ($message !== ''): ?>
                <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="dashboard-home-view is-active" id="dashboard-panel" data-dashboard-view>
                <section class="dashboard-showcase">
                    <div class="dashboard-hero-panel">
                        <div class="dashboard-welcome">
                            <h2>Dashboard overview</h2>
                            <p>Upload media, post updates, and manage users.</p>
                            <div class="dashboard-hero-meta">
                                <span><?php echo htmlspecialchars($currentDateLabel); ?></span>
                                <span><?php echo $imageCount; ?> images live</span>
                                <span><?php echo $videoCount; ?> videos live</span>
                            </div>
                        </div>
                    </div>

                    <section class="dashboard-stats-strip">
                        <a href="#gallery-panel" class="dashboard-mini-stat dashboard-card-link">
                            <span class="dashboard-mini-stat-icon dashboard-mini-stat-icon-green" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M5 5a2 2 0 0 0-2 2v10.5A2.5 2.5 0 0 0 5.5 20h13a2.5 2.5 0 0 0 2.5-2.5V7a2 2 0 0 0-2-2H5zm2.5 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm11.5 9.5H5.5a.5.5 0 0 1-.39-.813l3.254-4.067a1 1 0 0 1 1.53.017l1.617 2.055 2.75-3.261a1 1 0 0 1 1.55.047l3.58 4.299A.5.5 0 0 1 19 17.5z" fill="currentColor"/></svg>
                            </span>
                            <div>
                                <small>Total Images</small>
                                <strong><?php echo $imageCount; ?></strong>
                            </div>
                        </a>
                        <a href="#gallery-panel" class="dashboard-mini-stat dashboard-card-link">
                            <span class="dashboard-mini-stat-icon dashboard-mini-stat-icon-blue" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M4.5 5h10A1.5 1.5 0 0 1 16 6.5v2.586l3.293-3.293A1 1 0 0 1 21 6.5v11a1 1 0 0 1-1.707.707L16 14.914V17.5A1.5 1.5 0 0 1 14.5 19h-10A1.5 1.5 0 0 1 3 17.5v-11A1.5 1.5 0 0 1 4.5 5z" fill="currentColor"/></svg>
                            </span>
                            <div>
                                <small>Total Videos</small>
                                <strong><?php echo $videoCount; ?></strong>
                            </div>
                        </a>
                        <a href="#news-panel" class="dashboard-mini-stat dashboard-card-link">
                            <span class="dashboard-mini-stat-icon dashboard-mini-stat-icon-orange" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M3 10.5 16.5 4v16L3 13.5v-3zm14.5 1.75h2.25a1.25 1.25 0 0 1 0 2.5H17.5v-2.5zM5.75 14.1h2.2l1.25 4.15H7.1L5.75 14.1z" fill="currentColor"/></svg>
                            </span>
                            <div>
                                <small>Announcements</small>
                                <strong><?php echo $announcementCount; ?></strong>
                            </div>
                        </a>
                        <a href="#staff-panel" class="dashboard-mini-stat dashboard-card-link">
                            <span class="dashboard-mini-stat-icon dashboard-mini-stat-icon-purple" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm-7 8a7 7 0 1 1 14 0H5zm14.5-9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM18 20c0-2.02-.76-3.86-2.01-5.26A6 6 0 0 1 22 20h-4z" fill="currentColor"/></svg>
                            </span>
                            <div>
                                <small>Users</small>
                                <strong><?php echo $userCount; ?></strong>
                            </div>
                        </a>
                    </section>

                    <section class="dashboard-status-ribbon">
                        <article class="dashboard-status-card">
                            <strong>Media Library</strong>
                            <span><?php echo $imageCount + $videoCount; ?> total items ready for visitors.</span>
                        </article>
                        <article class="dashboard-status-card">
                            <strong>Announcements</strong>
                            <span><?php echo $announcementCount; ?> important notices currently highlighted.</span>
                        </article>
                        <article class="dashboard-status-card">
                            <strong>User Access</strong>
                            <span><?php echo $userCount; ?> dashboard accounts and staff records available.</span>
                        </article>
                    </section>

                    <section class="dashboard-showcase-grid">
                        <article class="panel dashboard-showcase-panel">
                            <div class="panel-top dashboard-showcase-top">
                                <div>
                                    <h3>Latest Announcements</h3>
                                    <p class="dashboard-panel-subtitle">Recent notices and school updates.</p>
                                </div>
                            </div>
                            <div class="announcement-feed">
                                <?php foreach (array_slice($announcementItems, 0, 3) as $item): ?>
                                    <article class="announcement-feed-item">
                                        <strong><?php echo htmlspecialchars((string) $item['title']); ?></strong>
                                        <p><?php echo htmlspecialchars((string) ($item['summary'] ?? $item['content'] ?? 'School update.')); ?></p>
                                    </article>
                                <?php endforeach; ?>
                                <?php if ($announcementItems === []): ?>
                                    <article class="announcement-feed-item">
                                        <strong>No announcements yet</strong>
                                        <p>Use the announcements panel to publish the first school notice.</p>
                                    </article>
                                <?php endif; ?>
                            </div>
                        </article>

                        <article class="panel dashboard-showcase-panel">
                            <div class="panel-top dashboard-showcase-top">
                                <div>
                                    <h3>Media Uploads</h3>
                                    <p class="dashboard-panel-subtitle">Quick access to your latest gallery content.</p>
                                </div>
                            </div>
                            <div class="dashboard-media-actions">
                                <a href="#gallery-panel" class="dashboard-media-action dashboard-card-link">Upload New Image</a>
                                <a href="#gallery-panel" class="dashboard-media-action dashboard-media-action-video dashboard-card-link">Upload New Video</a>
                            </div>

                            <div class="dashboard-media-group">
                                <div class="dashboard-media-group-top">
                                    <strong>Image Gallery</strong>
                                </div>
                                <div class="dashboard-media-thumbs">
                                    <?php foreach (array_slice($imageMediaItems, 0, 3) as $item): ?>
                                        <div class="dashboard-thumb">
                                            <img src="<?php echo htmlspecialchars(adminResolveMediaUrl((string) $item['image_path'])); ?>" alt="<?php echo htmlspecialchars((string) $item['title']); ?>" class="photo-viewer">
                                            <span><?php echo htmlspecialchars((string) $item['title']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="dashboard-media-group">
                                <div class="dashboard-media-group-top">
                                    <strong>Video Gallery</strong>
                                </div>
                                <div class="dashboard-media-thumbs dashboard-media-thumbs-video">
                                    <?php foreach (array_slice($videoMediaItems, 0, 2) as $item): ?>
                                        <div class="dashboard-thumb dashboard-thumb-video">
                                            <div class="dashboard-thumb-video-overlay">&#9658;</div>
                                            <?php if (adminIsVideoPath((string) $item['image_path'])): ?>
                                                <video muted playsinline preload="metadata" src="<?php echo htmlspecialchars(adminResolveMediaUrl((string) $item['image_path'])); ?>"></video>
                                            <?php else: ?>
                                                <img src="<?php echo htmlspecialchars(adminResolveMediaUrl((string) $item['image_path'])); ?>" alt="<?php echo htmlspecialchars((string) $item['title']); ?>" class="photo-viewer">
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars((string) $item['title']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if ($videoMediaItems === []): ?>
                                        <div class="dashboard-thumb dashboard-thumb-video dashboard-thumb-video-empty">
                                            <div class="dashboard-thumb-video-overlay">&#9658;</div>
                                            <img src="/MUBUGA-TSS/assets/images/school view 5.jpg" alt="Video placeholder" class="photo-viewer">
                                            <span>No videos yet</span>
                                        </div>
                                        <div class="dashboard-thumb dashboard-thumb-video dashboard-thumb-video-empty">
                                            <div class="dashboard-thumb-video-overlay">&#9658;</div>
                                            <img src="/MUBUGA-TSS/assets/images/school view 4.jpg" alt="Video placeholder" class="photo-viewer">
                                            <span>Upload first video</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    </section>
                </section>
            </div>

            <section class="admin-grid">
                <article class="panel dashboard-view-panel" id="settings-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Settings</p>
                            <h2>Branding and contact details</h2>
                        </div>
                    </div>
                    <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                        <input type="hidden" name="action" value="update_settings">
                        <div class="logo-preview-grid">
                            <div class="logo-preview-card">
                                <div>
                                    <strong>Site logo preview</strong>
                                    <p>Used on the homepage, inner pages, and footer.</p>
                                </div>
                                <div class="logo-preview-stage">
                                    <img
                                        src="<?php echo htmlspecialchars($settingsLogoPreviewUrl); ?>"
                                        alt="Site logo preview"
                                        data-logo-preview-image="site"
                                        style="width: <?php echo $siteLogoSize; ?>px;"
                                    >
                                </div>
                                <div class="logo-preview-meta">
                                    <span>Public website</span>
                                    <code data-logo-preview-size-label="site"><?php echo $siteLogoSize; ?>px</code>
                                </div>
                            </div>
                            <div class="logo-preview-card">
                                <div>
                                    <strong>Admin logo preview</strong>
                                    <p>Used in the dashboard sidebar and admin header.</p>
                                </div>
                                <div class="logo-preview-stage admin-stage">
                                    <img
                                        src="<?php echo htmlspecialchars($settingsLogoPreviewUrl); ?>"
                                        alt="Admin logo preview"
                                        data-logo-preview-image="admin"
                                        style="width: <?php echo $adminLogoSize; ?>px;"
                                    >
                                </div>
                                <div class="logo-preview-meta">
                                    <span>Admin dashboard</span>
                                    <code data-logo-preview-size-label="admin"><?php echo $adminLogoSize; ?>px</code>
                                </div>
                            </div>
                        </div>
                        <label><span>School Name</span><input type="text" name="school_name" value="<?php echo htmlspecialchars((string) ($settings['school_name'] ?? 'Mubuga TSS')); ?>"></label>
                        <label><span>Motto</span><input type="text" name="school_motto" value="<?php echo htmlspecialchars((string) ($settings['school_motto'] ?? '')); ?>"></label>
                        <label><span>Email</span><input type="email" name="school_email" value="<?php echo htmlspecialchars((string) ($settings['school_email'] ?? '')); ?>"></label>
                        <label><span>Phone</span><input type="text" name="school_phone" value="<?php echo htmlspecialchars((string) ($settings['school_phone'] ?? '')); ?>"></label>
                        <label><span>Address</span><input type="text" name="school_address" value="<?php echo htmlspecialchars((string) ($settings['school_address'] ?? '')); ?>"></label>
                        <label><span>Logo Path</span><input type="text" name="school_logo" value="<?php echo htmlspecialchars((string) ($settings['school_logo'] ?? '')); ?>" data-logo-path-input></label>
                        <label><span>Site Logo Size (px)</span><input type="number" name="site_logo_size" min="32" max="140" value="<?php echo (int) ($settings['site_logo_size'] ?? 52); ?>" data-logo-size-input="site"></label>
                        <label><span>Admin Logo Size (px)</span><input type="number" name="admin_logo_size" min="20" max="80" value="<?php echo (int) ($settings['admin_logo_size'] ?? 34); ?>" data-logo-size-input="admin"></label>
                        <label><span>Upload Logo</span><input type="file" name="school_logo_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" class="upload-input" data-logo-upload-input></label>
                        <label><span>Facebook URL</span><input type="url" name="school_facebook" value="<?php echo htmlspecialchars((string) ($settings['school_facebook'] ?? '')); ?>"></label>
                        <label><span>Instagram URL</span><input type="url" name="school_instagram" value="<?php echo htmlspecialchars((string) ($settings['school_instagram'] ?? '')); ?>"></label>
                        <button type="submit">Save Settings</button>
                    </form>
                </article>

                <article class="panel dashboard-view-panel" id="news-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">News</p>
                            <h2><?php echo $editType === 'news' ? 'Edit News Item' : 'Manage News, Events, and Announcements'; ?></h2>
                        </div>
                    </div>
                    <div class="news-filter-bar">
                        <a href="/MUBUGA-TSS/admin/dashboard.php?news_filter=all#news-panel" class="news-filter-link<?php echo $newsFilter === 'all' ? ' is-active' : ''; ?>">All</a>
                        <a href="/MUBUGA-TSS/admin/dashboard.php?news_filter=published#news-panel" class="news-filter-link<?php echo $newsFilter === 'published' ? ' is-active' : ''; ?>">Published</a>
                        <a href="/MUBUGA-TSS/admin/dashboard.php?news_filter=draft#news-panel" class="news-filter-link<?php echo $newsFilter === 'draft' ? ' is-active' : ''; ?>">Draft</a>
                    </div>
                    <div class="management-section">
                        <div class="management-section-header">
                            <div>
                                <strong>Current updates</strong>
                                <span>Review published items before creating a new one.</span>
                            </div>
                            <span class="management-tag">Library</span>
                        </div>
                        <div class="table-list">
                        <?php foreach ($filteredNews as $newsItem): ?>
                            <div class="table-item">
                                <div class="table-item-layout">
                                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $newsItem['featured_image']); ?>" alt="" class="table-thumb photo-viewer">
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars((string) $newsItem['title']); ?></strong>
                                        <span><?php echo htmlspecialchars(ucfirst((string) ($newsItem['category'] ?? 'news'))); ?> - <?php echo htmlspecialchars(ucfirst((string) ($newsItem['status'] ?? 'published'))); ?><?php echo !empty($newsItem['published_at']) ? ' - ' . htmlspecialchars((string) $newsItem['published_at']) : ''; ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?edit=news&id=<?php echo (int) $newsItem['id']; ?>&news_filter=<?php echo htmlspecialchars($newsFilter); ?>#news-panel" class="action-link">Edit</a>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                                        <input type="hidden" name="action" value="delete_news">
                                        <input type="hidden" name="id" value="<?php echo (int) $newsItem['id']; ?>">
                                        <button type="submit" class="action-link action-button danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($filteredNews === []): ?>
                            <div class="table-item">
                                <strong>No news items in this filter.</strong>
                                <span>Try another filter or create a new item.</span>
                            </div>
                        <?php endif; ?>
                        </div>
                    </div>
                    <div class="management-section">
                        <div class="management-section-header">
                            <div>
                                <strong><?php echo $editType === 'news' ? 'Update selected item' : 'Create new update'; ?></strong>
                                <span><?php echo $editType === 'news' ? 'Refine the selected announcement, event, or news post.' : 'Add a fresh update after reviewing the current list above.'; ?></span>
                            </div>
                            <span class="management-tag"><?php echo $editType === 'news' ? 'Edit' : 'Create'; ?></span>
                        </div>
                        <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                        <input type="hidden" name="action" value="<?php echo $editType === 'news' ? 'update_news' : 'add_news'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $newsForm['id']; ?>">
                        <label><span>Title</span><input type="text" name="title" value="<?php echo htmlspecialchars((string) $newsForm['title']); ?>"></label>
                        <label><span>Slug</span><input type="text" name="slug" value="<?php echo htmlspecialchars((string) $newsForm['slug']); ?>"></label>
                        <label>
                            <span>Type</span>
                            <select name="news_category">
                                <option value="news"<?php echo (($newsForm['news_category'] ?? 'news') === 'news') ? ' selected' : ''; ?>>News</option>
                                <option value="events"<?php echo (($newsForm['news_category'] ?? '') === 'events') ? ' selected' : ''; ?>>Event</option>
                                <option value="announcements"<?php echo (($newsForm['news_category'] ?? '') === 'announcements') ? ' selected' : ''; ?>>Announcement</option>
                            </select>
                        </label>
                        <label>
                            <span>Status</span>
                            <select name="news_status">
                                <option value="published"<?php echo (($newsForm['status'] ?? 'published') === 'published') ? ' selected' : ''; ?>>Published</option>
                                <option value="draft"<?php echo (($newsForm['status'] ?? '') === 'draft') ? ' selected' : ''; ?>>Draft</option>
                            </select>
                        </label>
                        <label><span>Summary</span><textarea name="summary" rows="4"><?php echo htmlspecialchars((string) $newsForm['summary']); ?></textarea></label>
                        <label><span>Full Content</span><textarea name="content" rows="7"><?php echo htmlspecialchars((string) $newsForm['content']); ?></textarea></label>
                        <label><span>Featured Photo Path</span><input type="text" name="featured_image" value="<?php echo htmlspecialchars((string) $newsForm['featured_image']); ?>"></label>
                        <label><span>Upload Featured Photo</span><input type="file" name="featured_image_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" class="upload-input"></label>
                        <button type="submit"><?php echo $editType === 'news' ? 'Update Item' : 'Publish Item'; ?></button>
                        </form>
                    </div>
                </article>

                <article class="panel dashboard-view-panel" id="gallery-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Gallery</p>
                            <h2><?php echo $editType === 'gallery' ? 'Edit Gallery Media' : 'Manage Gallery Images and Videos'; ?></h2>
                        </div>
                    </div>
                    <div class="management-section">
                        <div class="management-section-header">
                            <div>
                                <strong>Uploaded media</strong>
                                <span>Review the current gallery before adding new files.</span>
                            </div>
                            <span class="management-tag">Library</span>
                        </div>
                        <div class="table-list">
                        <?php foreach ($gallery as $galleryItem): ?>
                            <div class="table-item">
                                <div class="table-item-layout">
                                    <?php if (($galleryItem['media_type'] ?? 'image') === 'video' && adminIsVideoPath((string) $galleryItem['image_path'])): ?>
                                        <video class="table-thumb" muted playsinline preload="metadata" src="<?php echo htmlspecialchars(adminResolveMediaUrl((string) $galleryItem['image_path'])); ?>"></video>
                                    <?php else: ?>
                                        <img src="<?php echo htmlspecialchars(adminResolveMediaUrl((string) $galleryItem['image_path'])); ?>" alt="" class="table-thumb photo-viewer">
                                    <?php endif; ?>
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars((string) $galleryItem['title']); ?></strong>
                                        <span><?php echo htmlspecialchars((string) $galleryItem['category']); ?> - <?php echo htmlspecialchars(ucfirst((string) ($galleryItem['media_type'] ?? 'image'))); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?edit=gallery&id=<?php echo (int) $galleryItem['id']; ?>" class="action-link">Edit</a>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                                        <input type="hidden" name="action" value="delete_gallery">
                                        <input type="hidden" name="id" value="<?php echo (int) $galleryItem['id']; ?>">
                                        <button type="submit" class="action-link action-button danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="management-section">
                        <div class="management-section-header">
                            <div>
                                <strong><?php echo $editType === 'gallery' ? 'Update media item' : 'Add new media'; ?></strong>
                                <span><?php echo $editType === 'gallery' ? 'Adjust the selected item details below.' : 'Upload a new image or video after reviewing the gallery list above.'; ?></span>
                            </div>
                            <span class="management-tag"><?php echo $editType === 'gallery' ? 'Edit' : 'Upload'; ?></span>
                        </div>
                        <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                        <input type="hidden" name="action" value="<?php echo $editType === 'gallery' ? 'update_gallery' : 'add_gallery'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $galleryForm['id']; ?>">
                        <label><span>Title</span><input type="text" name="title" value="<?php echo htmlspecialchars((string) $galleryForm['title']); ?>"></label>
                        <label>
                            <span>Media Type</span>
                            <select name="media_type">
                                <option value="image"<?php echo (($galleryForm['media_type'] ?? 'image') === 'image') ? ' selected' : ''; ?>>Image</option>
                                <option value="video"<?php echo (($galleryForm['media_type'] ?? '') === 'video') ? ' selected' : ''; ?>>Video</option>
                            </select>
                        </label>
                        <label><span>Media Path or Video URL</span><input type="text" name="image_path" value="<?php echo htmlspecialchars((string) $galleryForm['image_path']); ?>"></label>
                        <label><span>Upload Image or Video</span><input type="file" name="gallery_image_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif,.mp4,.webm,.ogg" class="upload-input"></label>
                        <label><span>Caption</span><textarea name="caption" rows="4"><?php echo htmlspecialchars((string) $galleryForm['caption']); ?></textarea></label>
                        <label><span>Topic</span><input type="text" name="category" value="<?php echo htmlspecialchars((string) $galleryForm['category']); ?>"></label>
                        <label class="checkbox"><input type="checkbox" name="is_featured"<?php echo ((int) $galleryForm['is_featured'] === 1) ? ' checked' : ''; ?>> <span>Featured image</span></label>
                        <button type="submit"><?php echo $editType === 'gallery' ? 'Update Media' : 'Add Media'; ?></button>
                        </form>
                    </div>
                </article>
            </section>

            <section class="admin-grid">
                <article class="panel dashboard-view-panel" id="pages-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Pages</p>
                            <h2><?php echo $editType === 'page' ? 'Edit Page Content' : 'Manage Page Content'; ?></h2>
                        </div>
                    </div>
                    <div class="management-section">
                        <div class="management-section-header">
                            <div>
                                <strong>Saved pages</strong>
                                <span>Check the current pages before creating or editing one.</span>
                            </div>
                            <span class="management-tag">Library</span>
                        </div>
                        <div class="table-list">
                        <?php foreach ($pages as $pageItem): ?>
                            <div class="table-item">
                                <div class="table-item-layout">
                                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $pageItem['banner_image']); ?>" alt="" class="table-thumb photo-viewer">
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars((string) $pageItem['title']); ?></strong>
                                        <span><?php echo htmlspecialchars((string) $pageItem['slug']); ?> - <?php echo htmlspecialchars((string) $pageItem['status']); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?edit=page&id=<?php echo (int) $pageItem['id']; ?>" class="action-link">Edit</a>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                                        <input type="hidden" name="action" value="delete_page">
                                        <input type="hidden" name="id" value="<?php echo (int) $pageItem['id']; ?>">
                                        <button type="submit" class="action-link action-button danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="management-section">
                        <div class="management-section-header">
                            <div>
                                <strong><?php echo $editType === 'page' ? 'Update page content' : 'Create page content'; ?></strong>
                                <span><?php echo $editType === 'page' ? 'Refine the selected page content below.' : 'Add a new page once you finish reviewing the existing page list.'; ?></span>
                            </div>
                            <span class="management-tag"><?php echo $editType === 'page' ? 'Edit' : 'Create'; ?></span>
                        </div>
                        <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
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
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
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
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
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
                            <h2><?php echo $editType === 'program' ? 'Edit Program' : 'Manage Programs'; ?></h2>
                        </div>
                    </div>
                    <div class="management-section">
                        <div class="management-section-header">
                            <div>
                                <strong>Available programs</strong>
                                <span>Review the current academic offers before adding another one.</span>
                            </div>
                            <span class="management-tag">Library</span>
                        </div>
                        <div class="table-list">
                        <?php foreach ($programs as $program): ?>
                            <div class="table-item">
                                <div class="table-item-layout">
                                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $program['cover_image']); ?>" alt="" class="table-thumb photo-viewer">
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars((string) $program['title']); ?></strong>
                                        <span><?php echo htmlspecialchars((string) $program['department']); ?> - <?php echo htmlspecialchars((string) $program['status']); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?edit=program&id=<?php echo (int) $program['id']; ?>" class="action-link">Edit</a>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                                        <input type="hidden" name="action" value="delete_program">
                                        <input type="hidden" name="id" value="<?php echo (int) $program['id']; ?>">
                                        <button type="submit" class="action-link action-button danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="management-section">
                        <div class="management-section-header">
                            <div>
                                <strong><?php echo $editType === 'program' ? 'Update program details' : 'Create new program'; ?></strong>
                                <span><?php echo $editType === 'program' ? 'Edit the selected program details below.' : 'Add a new program after reviewing what is already published.'; ?></span>
                            </div>
                            <span class="management-tag"><?php echo $editType === 'program' ? 'Edit' : 'Create'; ?></span>
                        </div>
                        <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
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
                    </div>
                </article>

                <article class="panel dashboard-view-panel" id="staff-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Staff</p>
                            <h2><?php echo $editType === 'staff' ? 'Edit Team Member' : 'Manage Team Members'; ?></h2>
                        </div>
                    </div>
                    <div class="management-section">
                        <div class="management-section-header">
                            <div>
                                <strong>Current team members</strong>
                                <span>See who is already listed before adding another profile.</span>
                            </div>
                            <span class="management-tag">Library</span>
                        </div>
                        <div class="table-list">
                        <?php foreach ($staff as $member): ?>
                            <div class="table-item">
                                <div class="table-item-layout">
                                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $member['photo']); ?>" alt="" class="table-thumb photo-viewer">
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars((string) $member['full_name']); ?></strong>
                                        <span><?php echo htmlspecialchars((string) $member['job_title']); ?></span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?edit=staff&id=<?php echo (int) $member['id']; ?>" class="action-link">Edit</a>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                                        <input type="hidden" name="action" value="delete_staff">
                                        <input type="hidden" name="id" value="<?php echo (int) $member['id']; ?>">
                                        <button type="submit" class="action-link action-button danger-button">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="management-section">
                        <div class="management-section-header">
                            <div>
                                <strong><?php echo $editType === 'staff' ? 'Update team member' : 'Add team member'; ?></strong>
                                <span><?php echo $editType === 'staff' ? 'Edit the selected profile details below.' : 'Create a new staff profile after reviewing the current team list.'; ?></span>
                            </div>
                            <span class="management-tag"><?php echo $editType === 'staff' ? 'Edit' : 'Create'; ?></span>
                        </div>
                        <form method="post" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
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
                    </div>
                </article>
            </section>
        </main>
    </div>
    <script src="/MUBUGA-TSS/assets/js/admin.js"></script>
    <script src="/MUBUGA-TSS/assets/js/photo-viewer.js"></script>
</body>
</html>

