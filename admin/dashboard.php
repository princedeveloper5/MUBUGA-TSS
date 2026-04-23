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

function adminMediaAbsolutePath(string $path): ?string
{
    $normalized = trim(str_replace('\\', '/', $path));
    if ($normalized === '' || adminIsAbsoluteMediaPath($normalized) || str_contains($normalized, '..')) {
        return null;
    }

    $absolutePath = dirname(__DIR__) . '/' . ltrim($normalized, '/');
    return is_file($absolutePath) ? $absolutePath : null;
}

function adminMediaMetadata(string $path): array
{
    $normalized = trim(str_replace('\\', '/', $path));
    $extension = strtolower(pathinfo($normalized, PATHINFO_EXTENSION));
    $absolutePath = adminMediaAbsolutePath($normalized);
    $metadata = [
        'file_size_bytes' => $absolutePath !== null ? max(0, (int) @filesize($absolutePath)) : 0,
        'file_size_label' => $absolutePath !== null ? adminFormatBytes(max(0, (int) @filesize($absolutePath))) : 'Unknown size',
        'file_type' => $extension !== '' ? strtoupper($extension) : 'FILE',
        'dimensions' => '',
    ];

    if ($absolutePath !== null && in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'], true)) {
        $imageSize = @getimagesize($absolutePath);
        if (is_array($imageSize) && isset($imageSize[0], $imageSize[1])) {
            $metadata['dimensions'] = (int) $imageSize[0] . ' × ' . (int) $imageSize[1];
        }
    }

    return $metadata;
}

function adminEnsureDashboardTables(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS admin_activity_log (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NULL,
        action_type VARCHAR(60) NOT NULL,
        entity_type VARCHAR(60) NOT NULL,
        entity_id INT UNSIGNED NULL,
        title VARCHAR(190) NOT NULL,
        description TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_activity_created_at (created_at),
        INDEX idx_activity_entity (entity_type, entity_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    $pdo->exec('CREATE TABLE IF NOT EXISTS admin_notifications (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NULL,
        notification_type VARCHAR(60) NOT NULL,
        title VARCHAR(190) NOT NULL,
        message TEXT NULL,
        link_target VARCHAR(255) NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_notifications_read (is_read, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    $pdo->exec('CREATE TABLE IF NOT EXISTS news_admin_meta (
        news_id INT UNSIGNED PRIMARY KEY,
        is_pinned TINYINT(1) NOT NULL DEFAULT 0,
        scheduled_for DATETIME NULL,
        view_count INT UNSIGNED NOT NULL DEFAULT 0,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    $pdo->exec('CREATE TABLE IF NOT EXISTS gallery_admin_meta (
        gallery_id INT UNSIGNED PRIMARY KEY,
        album_name VARCHAR(120) NULL,
        view_count INT UNSIGNED NOT NULL DEFAULT 0,
        download_count INT UNSIGNED NOT NULL DEFAULT 0,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}

function adminLogActivity(PDO $pdo, int $userId, string $actionType, string $entityType, int $entityId, string $title, string $description = ''): void
{
    $statement = $pdo->prepare('INSERT INTO admin_activity_log (user_id, action_type, entity_type, entity_id, title, description) VALUES (:user_id, :action_type, :entity_type, :entity_id, :title, :description)');
    $statement->execute([
        'user_id' => $userId > 0 ? $userId : null,
        'action_type' => $actionType,
        'entity_type' => $entityType,
        'entity_id' => $entityId > 0 ? $entityId : null,
        'title' => $title,
        'description' => $description,
    ]);
}

function adminPushNotification(PDO $pdo, int $userId, string $type, string $title, string $message = '', string $linkTarget = ''): void
{
    $statement = $pdo->prepare('INSERT INTO admin_notifications (user_id, notification_type, title, message, link_target) VALUES (:user_id, :notification_type, :title, :message, :link_target)');
    $statement->execute([
        'user_id' => $userId > 0 ? $userId : null,
        'notification_type' => $type,
        'title' => $title,
        'message' => $message,
        'link_target' => $linkTarget !== '' ? $linkTarget : null,
    ]);
}

function adminRecordEvent(PDO $pdo, int $userId, string $actionType, string $entityType, int $entityId, string $title, string $description = '', string $linkTarget = ''): void
{
    adminLogActivity($pdo, $userId, $actionType, $entityType, $entityId, $title, $description);
    adminPushNotification($pdo, $userId, $actionType, $title, $description, $linkTarget);
}

function adminRelativeTime(?string $dateTime): string
{
    if ($dateTime === null || trim($dateTime) === '') {
        return 'just now';
    }

    try {
        $timestamp = new DateTimeImmutable($dateTime);
        $now = new DateTimeImmutable('now');
        $diff = $now->getTimestamp() - $timestamp->getTimestamp();
        if ($diff < 60) {
            return 'just now';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . ' min ago';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . ' hr ago';
        }
        if ($diff < 604800) {
            return floor($diff / 86400) . ' day' . (floor($diff / 86400) > 1 ? 's' : '') . ' ago';
        }

        return $timestamp->format('d M Y');
    } catch (Throwable $exception) {
        return (string) $dateTime;
    }
}

function adminGetFileEntries(array $directories): array
{
    $entries = [];

    foreach ($directories as $directory) {
        if (!is_dir($directory)) {
            continue;
        }

        $iterator = new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS);
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $absolutePath = str_replace('\\', '/', $fileInfo->getPathname());
            $rootPath = str_replace('\\', '/', dirname(__DIR__));
            $relativePath = ltrim(str_replace($rootPath, '', $absolutePath), '/');
            $entries[] = [
                'name' => $fileInfo->getFilename(),
                'absolute_path' => $absolutePath,
                'relative_path' => $relativePath,
                'size' => (int) $fileInfo->getSize(),
                'modified_at' => date('Y-m-d H:i:s', $fileInfo->getMTime()),
                'extension' => strtolower((string) $fileInfo->getExtension()),
            ];
        }
    }

    usort($entries, static function (array $left, array $right): int {
        return strcmp((string) $right['modified_at'], (string) $left['modified_at']);
    });

    return $entries;
}

function adminFormatBytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $size = max($bytes, 0);
    $power = $size > 0 ? (int) floor(log($size, 1024)) : 0;
    $power = min($power, count($units) - 1);
    $value = $size > 0 ? $size / (1024 ** $power) : 0;
    return number_format($value, $power === 0 ? 0 : 1) . ' ' . $units[$power];
}

function adminStorageBytes(array $entries): int
{
    return array_sum(array_map(static fn(array $entry): int => (int) ($entry['size'] ?? 0), $entries));
}

function adminDeleteRelativeFile(string $relativePath): bool
{
    $normalized = trim(str_replace('\\', '/', $relativePath));
    if ($normalized === '' || str_contains($normalized, '..')) {
        return false;
    }

    if (!preg_match('~^assets/(uploads|videos)/~', $normalized)) {
        return false;
    }

    $absolutePath = dirname(__DIR__) . '/' . $normalized;
    if (!is_file($absolutePath)) {
        return false;
    }

    return unlink($absolutePath);
}

function adminMonthlySeries(array $items, string $field, int $months = 6): array
{
    $series = [];
    $cursor = new DateTimeImmutable('first day of this month');

    for ($index = $months - 1; $index >= 0; $index--) {
        $key = $cursor->modify("-{$index} months")->format('Y-m');
        $series[$key] = 0;
    }

    foreach ($items as $item) {
        $rawValue = (string) ($item[$field] ?? '');
        if ($rawValue === '') {
            continue;
        }

        $key = substr($rawValue, 0, 7);
        if (isset($series[$key])) {
            $series[$key]++;
        }
    }

    return $series;
}

function adminSortNewsItems(array &$items): void
{
    usort($items, static function (array $left, array $right): int {
        $leftPinned = (int) ($left['is_pinned'] ?? 0);
        $rightPinned = (int) ($right['is_pinned'] ?? 0);
        $leftDate = (string) (($left['scheduled_for'] ?? '') !== '' ? $left['scheduled_for'] : ($left['published_at'] ?? ''));
        $rightDate = (string) (($right['scheduled_for'] ?? '') !== '' ? $right['scheduled_for'] : ($right['published_at'] ?? ''));

        return $rightPinned <=> $leftPinned
            ?: strcmp($rightDate, $leftDate)
            ?: ((int) ($right['id'] ?? 0)) <=> ((int) ($left['id'] ?? 0));
    });
}

function adminSearchIndex(array $sections): array
{
    $index = [];

    foreach ($sections as $sectionName => $items) {
        foreach ($items as $item) {
            $searchText = strtolower(trim(implode(' ', array_map(static function ($value): string {
                if (is_scalar($value) || $value === null) {
                    return (string) $value;
                }
                return '';
            }, $item))));

            if ($searchText === '') {
                continue;
            }

            $index[] = [
                'section' => $sectionName,
                'text' => $searchText,
                'label' => (string) ($item['title'] ?? $item['full_name'] ?? $item['slug'] ?? $sectionName),
                'link' => (string) ($item['link'] ?? '#'),
                'meta' => (string) ($item['meta'] ?? ''),
            ];
        }
    }

    return $index;
}

$pdo = getDatabaseConnection();
$admin = currentAdmin() ?? [];
$adminId = (int) ($admin['id'] ?? 0);
$message = '';
$error = '';
$editType = (string) ($_GET['edit'] ?? '');
$editId = (int) ($_GET['id'] ?? 0);
$newsFilter = strtolower(trim((string) ($_GET['news_filter'] ?? 'all')));
 $mediaFilter = strtolower(trim((string) ($_GET['media_filter'] ?? 'all')));
 $mediaSort = strtolower(trim((string) ($_GET['media_sort'] ?? 'newest')));
 $mediaSearch = trim((string) ($_GET['media_search'] ?? ''));
 $mediaDateFrom = trim((string) ($_GET['media_date_from'] ?? ''));
 $mediaDateTo = trim((string) ($_GET['media_date_to'] ?? ''));
 $mediaPage = max(1, (int) ($_GET['media_page'] ?? 1));
 $dashboardSearchQuery = trim((string) ($_GET['q'] ?? ''));
$notificationFilter = strtolower(trim((string) ($_GET['notification_filter'] ?? 'all')));
$notificationTypeFilter = strtolower(trim((string) ($_GET['notification_type'] ?? 'all')));
if (!in_array($newsFilter, ['all', 'published', 'draft'], true)) {
    $newsFilter = 'all';
}
if (!in_array($mediaFilter, ['all', 'image', 'video', 'events', 'exams', 'sports', 'general', 'campus'], true)) {
    $mediaFilter = 'all';
}
if (!in_array($mediaSort, ['newest', 'oldest', 'most_viewed'], true)) {
    $mediaSort = 'newest';
}
if (!in_array($notificationFilter, ['all', 'unread', 'read'], true)) {
    $notificationFilter = 'all';
}
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$isAjaxRequest = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

if (!$pdo instanceof PDO) {
    $error = 'Database connection failed.';
} else {
    adminEnsureDashboardTables($pdo);
}

if (isset($_GET['export']) && $_GET['export'] === 'json' && $pdo instanceof PDO) {
    $exportPayload = [
        'generated_at' => date('c'),
        'settings' => $pdo->query('SELECT setting_key, setting_value FROM settings ORDER BY setting_key ASC')->fetchAll(PDO::FETCH_KEY_PAIR),
        'pages' => $pdo->query('SELECT * FROM pages ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC),
        'news' => $pdo->query('SELECT * FROM news ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC),
        'gallery' => $pdo->query('SELECT * FROM gallery ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC),
        'programs' => $pdo->query('SELECT * FROM programs ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC),
    ];

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="mubuga-dashboard-export-' . date('Ymd-His') . '.json"');
    echo json_encode($exportPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($requestMethod === 'POST' && $pdo instanceof PDO) {
    if (!adminVerifyCsrfToken((string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Security token mismatch. Refresh the page and try again.';
    } else {
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'mark_notification_read') {
            $notificationId = (int) ($_POST['notification_id'] ?? 0);
            $statement = $pdo->prepare('UPDATE admin_notifications SET is_read = 1 WHERE id = :id');
            $statement->execute(['id' => $notificationId]);
            $message = 'Notification marked as read.';
        }

        if ($action === 'mark_all_notifications_read') {
            $pdo->exec('UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0');
            $message = 'All notifications marked as read.';
        }

        if ($action === 'change_password') {
            $currentPassword = (string) ($_POST['current_password'] ?? '');
            $newPassword = (string) ($_POST['new_password'] ?? '');
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

            if ($newPassword === '' || strlen($newPassword) < 8) {
                throw new RuntimeException('New password must be at least 8 characters.');
            }

            if ($newPassword !== $confirmPassword) {
                throw new RuntimeException('Password confirmation does not match.');
            }

            $statement = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id LIMIT 1');
            $statement->execute(['id' => $adminId]);
            $passwordHash = (string) ($statement->fetchColumn() ?: '');

            if ($passwordHash === '' || !password_verify($currentPassword, $passwordHash)) {
                throw new RuntimeException('Current password is incorrect.');
            }

            $statement = $pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
            $statement->execute([
                'id' => $adminId,
                'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            ]);

            adminRecordEvent($pdo, $adminId, 'security', 'user', $adminId, 'Password changed', 'Your admin password was updated successfully.', '#security-panel');
            $message = 'Password changed successfully.';
        }

        if ($action === 'delete_file') {
            $relativePath = trim((string) ($_POST['file_path'] ?? ''));
            if (!adminDeleteRelativeFile($relativePath)) {
                throw new RuntimeException('Selected file could not be deleted.');
            }

            adminRecordEvent($pdo, $adminId, 'delete', 'file', 0, 'File deleted', basename($relativePath) . ' was deleted from storage.', '#files-panel');
            $message = 'File deleted successfully.';
        }

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
                'theme_mode' => trim((string) ($_POST['theme_mode'] ?? 'light')),
                'upload_size_limit_mb' => (string) max(5, min(512, (int) ($_POST['upload_size_limit_mb'] ?? 50))),
                'allowed_file_types' => trim((string) ($_POST['allowed_file_types'] ?? 'jpg,jpeg,png,gif,webp,jfif,mp4,webm,ogg')),
                'session_timeout_minutes' => (string) max(5, min(240, (int) ($_POST['session_timeout_minutes'] ?? 30))),
                'homepage_notice' => trim((string) ($_POST['homepage_notice'] ?? '')),
            ];

            $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (:key_name, :key_value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            foreach ($settings as $key => $value) {
                $stmt->execute(['key_name' => $key, 'key_value' => $value]);
            }
            adminRecordEvent($pdo, $adminId, 'update', 'settings', 0, 'Settings updated', 'Branding, upload, and dashboard settings were updated.', '#settings-panel');
            $message = 'Settings updated successfully.';
        }

        if ($action === 'add_staff') {
            $fullName = trim((string) ($_POST['full_name'] ?? ''));
            $photoPath = trim((string) ($_POST['photo'] ?? 'assets/images/master.jpeg'));
            $photoPath = handleAdminImageUpload('photo_upload', $photoPath);
            $stmt = $pdo->prepare('INSERT INTO staff (full_name, job_title, bio, photo, display_order, is_featured, status) VALUES (:full_name, :job_title, :bio, :photo, :display_order, :is_featured, "active")');
            $stmt->execute([
                'full_name' => $fullName,
                'job_title' => trim((string) ($_POST['job_title'] ?? '')),
                'bio' => trim((string) ($_POST['bio'] ?? '')),
                'photo' => $photoPath,
                'display_order' => (int) ($_POST['display_order'] ?? 0),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ]);
            $staffId = (int) $pdo->lastInsertId();
            adminRecordEvent($pdo, $adminId, 'create', 'staff', $staffId, 'Staff profile created', $fullName . ' was added to the team directory.', '#staff-panel');
            $message = 'Staff member added.';
        }

        if ($action === 'update_staff') {
            $staffId = (int) ($_POST['id'] ?? 0);
            $fullName = trim((string) ($_POST['full_name'] ?? ''));
            $photoPath = trim((string) ($_POST['photo'] ?? 'assets/images/master.jpeg'));
            $photoPath = handleAdminImageUpload('photo_upload', $photoPath);
            $stmt = $pdo->prepare('UPDATE staff SET full_name = :full_name, job_title = :job_title, bio = :bio, photo = :photo, display_order = :display_order, is_featured = :is_featured WHERE id = :id');
            $stmt->execute([
                'id' => $staffId,
                'full_name' => $fullName,
                'job_title' => trim((string) ($_POST['job_title'] ?? '')),
                'bio' => trim((string) ($_POST['bio'] ?? '')),
                'photo' => $photoPath,
                'display_order' => (int) ($_POST['display_order'] ?? 0),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ]);
            adminRecordEvent($pdo, $adminId, 'update', 'staff', $staffId, 'Staff profile updated', $fullName . ' was updated.', '#staff-panel');
            $message = 'Staff member updated.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'delete_staff') {
            $staffId = (int) ($_POST['id'] ?? 0);
            $titleStatement = $pdo->prepare('SELECT full_name FROM staff WHERE id = :id LIMIT 1');
            $titleStatement->execute(['id' => $staffId]);
            $staffName = (string) ($titleStatement->fetchColumn() ?: 'Staff member');
            $stmt = $pdo->prepare('DELETE FROM staff WHERE id = :id');
            $stmt->execute(['id' => $staffId]);
            adminRecordEvent($pdo, $adminId, 'delete', 'staff', $staffId, 'Staff profile deleted', $staffName . ' was removed from the team directory.', '#staff-panel');
            $message = 'Staff member deleted.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'add_program') {
            $programTitle = trim((string) ($_POST['program_title'] ?? ''));
            $coverImage = trim((string) ($_POST['cover_image'] ?? 'assets/images/mb1.jfif'));
            $coverImage = handleAdminImageUpload('program_cover_upload', $coverImage);
            $stmt = $pdo->prepare('INSERT INTO programs (title, slug, short_description, description, duration, department, cover_image, status) VALUES (:title, :slug, :short_description, :description, :duration, :department, :cover_image, :status)');
            $stmt->execute([
                'title' => $programTitle,
                'slug' => trim((string) ($_POST['program_slug'] ?? '')),
                'short_description' => trim((string) ($_POST['program_summary'] ?? '')),
                'description' => trim((string) ($_POST['program_description'] ?? '')),
                'duration' => trim((string) ($_POST['program_duration'] ?? '')),
                'department' => trim((string) ($_POST['program_department'] ?? '')),
                'cover_image' => $coverImage,
                'status' => (string) ($_POST['program_status'] ?? 'active'),
            ]);
            $programId = (int) $pdo->lastInsertId();
            adminRecordEvent($pdo, $adminId, 'create', 'program', $programId, 'Program created', $programTitle . ' was added to the academic programs list.', '#programs-panel');
            $message = 'Program added.';
        }

        if ($action === 'update_program') {
            $programId = (int) ($_POST['id'] ?? 0);
            $programTitle = trim((string) ($_POST['program_title'] ?? ''));
            $coverImage = trim((string) ($_POST['cover_image'] ?? 'assets/images/mb1.jfif'));
            $coverImage = handleAdminImageUpload('program_cover_upload', $coverImage);
            $stmt = $pdo->prepare('UPDATE programs SET title = :title, slug = :slug, short_description = :short_description, description = :description, duration = :duration, department = :department, cover_image = :cover_image, status = :status WHERE id = :id');
            $stmt->execute([
                'id' => $programId,
                'title' => $programTitle,
                'slug' => trim((string) ($_POST['program_slug'] ?? '')),
                'short_description' => trim((string) ($_POST['program_summary'] ?? '')),
                'description' => trim((string) ($_POST['program_description'] ?? '')),
                'duration' => trim((string) ($_POST['program_duration'] ?? '')),
                'department' => trim((string) ($_POST['program_department'] ?? '')),
                'cover_image' => $coverImage,
                'status' => (string) ($_POST['program_status'] ?? 'active'),
            ]);
            adminRecordEvent($pdo, $adminId, 'update', 'program', $programId, 'Program updated', $programTitle . ' details were updated.', '#programs-panel');
            $message = 'Program updated.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'delete_program') {
            $programId = (int) ($_POST['id'] ?? 0);
            $titleStatement = $pdo->prepare('SELECT title FROM programs WHERE id = :id LIMIT 1');
            $titleStatement->execute(['id' => $programId]);
            $programTitle = (string) ($titleStatement->fetchColumn() ?: 'Program');
            $stmt = $pdo->prepare('DELETE FROM programs WHERE id = :id');
            $stmt->execute(['id' => $programId]);
            adminRecordEvent($pdo, $adminId, 'delete', 'program', $programId, 'Program deleted', $programTitle . ' was removed from the programs list.', '#programs-panel');
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
            $scheduledFor = trim((string) ($_POST['scheduled_for'] ?? ''));
            $publishedAt = $scheduledFor !== '' ? $scheduledFor : ($newsStatus === 'published' ? date('Y-m-d H:i:s') : null);
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
            $newsId = (int) $pdo->lastInsertId();
            $metaStatement = $pdo->prepare('INSERT INTO news_admin_meta (news_id, is_pinned, scheduled_for) VALUES (:news_id, :is_pinned, :scheduled_for) ON DUPLICATE KEY UPDATE is_pinned = VALUES(is_pinned), scheduled_for = VALUES(scheduled_for)');
            $metaStatement->execute([
                'news_id' => $newsId,
                'is_pinned' => isset($_POST['is_pinned']) ? 1 : 0,
                'scheduled_for' => $scheduledFor !== '' ? $scheduledFor : null,
            ]);
            adminRecordEvent($pdo, $adminId, 'publish', 'news', $newsId, 'Announcement published', $title . ' saved as ' . $newsStatus . '.', '#news-panel');
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
            $scheduledFor = trim((string) ($_POST['scheduled_for'] ?? ''));
            $publishedAt = $scheduledFor !== '' ? $scheduledFor : ($newsStatus === 'published' ? date('Y-m-d H:i:s') : null);
            $stmt = $pdo->prepare('UPDATE news SET title = :title, slug = :slug, summary = :summary, content = :content, featured_image = :featured_image, status = :status, published_at = :published_at WHERE id = :id');
            $stmt->execute([
                'id' => $id,
                'title' => $title,
                'slug' => $slug,
                'summary' => trim((string) ($_POST['summary'] ?? '')),
                'content' => adminEncodeNewsContent((string) ($_POST['content'] ?? ''), $newsCategory),
                'featured_image' => $featuredImage,
                'status' => $newsStatus,
                'published_at' => $publishedAt,
            ]);
            $metaStatement = $pdo->prepare('INSERT INTO news_admin_meta (news_id, is_pinned, scheduled_for) VALUES (:news_id, :is_pinned, :scheduled_for) ON DUPLICATE KEY UPDATE is_pinned = VALUES(is_pinned), scheduled_for = VALUES(scheduled_for)');
            $metaStatement->execute([
                'news_id' => $id,
                'is_pinned' => isset($_POST['is_pinned']) ? 1 : 0,
                'scheduled_for' => $scheduledFor !== '' ? $scheduledFor : null,
            ]);
            adminRecordEvent($pdo, $adminId, 'update', 'news', $id, 'Announcement updated', $title . ' was updated.', '#news-panel');
            $message = ucfirst($newsCategory) . ' item updated as ' . $newsStatus . '.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'delete_news') {
            $newsId = (int) ($_POST['id'] ?? 0);
            $titleStatement = $pdo->prepare('SELECT title FROM news WHERE id = :id LIMIT 1');
            $titleStatement->execute(['id' => $newsId]);
            $newsTitle = (string) ($titleStatement->fetchColumn() ?: 'News item');
            $stmt = $pdo->prepare('DELETE FROM news WHERE id = :id');
            $stmt->execute(['id' => $newsId]);
            $pdo->prepare('DELETE FROM news_admin_meta WHERE news_id = :id')->execute(['id' => $newsId]);
            adminRecordEvent($pdo, $adminId, 'delete', 'news', $newsId, 'Announcement deleted', $newsTitle . ' was removed.', '#news-panel');
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
            $galleryId = (int) $pdo->lastInsertId();
            $pdo->prepare('INSERT INTO gallery_admin_meta (gallery_id, album_name) VALUES (:gallery_id, :album_name) ON DUPLICATE KEY UPDATE album_name = VALUES(album_name)')
                ->execute([
                    'gallery_id' => $galleryId,
                    'album_name' => trim((string) ($_POST['album_name'] ?? 'General')) ?: 'General',
                ]);
            adminRecordEvent($pdo, $adminId, 'upload', 'gallery', $galleryId, 'Image uploaded successfully', trim((string) ($_POST['title'] ?? 'Media item')) . ' was added to the library.', '#gallery-panel');
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
            $galleryId = (int) ($_POST['id'] ?? 0);
            $pdo->prepare('INSERT INTO gallery_admin_meta (gallery_id, album_name) VALUES (:gallery_id, :album_name) ON DUPLICATE KEY UPDATE album_name = VALUES(album_name)')
                ->execute([
                    'gallery_id' => $galleryId,
                    'album_name' => trim((string) ($_POST['album_name'] ?? 'General')) ?: 'General',
                ]);
            adminRecordEvent($pdo, $adminId, 'update', 'gallery', $galleryId, 'Media updated', trim((string) ($_POST['title'] ?? 'Media item')) . ' details were updated.', '#gallery-panel');
            $message = 'Gallery media updated.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'delete_gallery') {
            $galleryId = (int) ($_POST['id'] ?? 0);
            $titleStatement = $pdo->prepare('SELECT title FROM gallery WHERE id = :id LIMIT 1');
            $titleStatement->execute(['id' => $galleryId]);
            $galleryTitle = (string) ($titleStatement->fetchColumn() ?: 'Media item');
            $stmt = $pdo->prepare('DELETE FROM gallery WHERE id = :id');
            $stmt->execute(['id' => $galleryId]);
            $pdo->prepare('DELETE FROM gallery_admin_meta WHERE gallery_id = :id')->execute(['id' => $galleryId]);
            adminRecordEvent($pdo, $adminId, 'delete', 'gallery', $galleryId, 'Video deleted', $galleryTitle . ' was removed from the library.', '#gallery-panel');
            $message = 'Gallery image deleted.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'bulk_delete_gallery') {
            $selectedIds = array_values(array_filter(array_map('intval', (array) ($_POST['selected_gallery_ids'] ?? []))));
            if ($selectedIds === []) {
                throw new RuntimeException('Select at least one media item first.');
            }

            $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
            $deleteGalleryStatement = $pdo->prepare('DELETE FROM gallery WHERE id IN (' . $placeholders . ')');
            $deleteGalleryStatement->execute($selectedIds);
            $deleteMetaStatement = $pdo->prepare('DELETE FROM gallery_admin_meta WHERE gallery_id IN (' . $placeholders . ')');
            $deleteMetaStatement->execute($selectedIds);
            adminRecordEvent($pdo, $adminId, 'delete', 'gallery', 0, 'Bulk media delete', count($selectedIds) . ' media items were removed from the library.', '#gallery-panel');
            $message = count($selectedIds) . ' media item(s) deleted.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'bulk_update_gallery_category') {
            $selectedIds = array_values(array_filter(array_map('intval', (array) ($_POST['selected_gallery_ids'] ?? []))));
            $newCategory = strtolower(trim((string) ($_POST['bulk_category'] ?? '')));
            if ($selectedIds === []) {
                throw new RuntimeException('Select at least one media item first.');
            }
            if (!in_array($newCategory, ['campus', 'events', 'exams', 'sports', 'general'], true)) {
                throw new RuntimeException('Select a valid category for the bulk update.');
            }

            $selectStatement = $pdo->prepare('SELECT id, category FROM gallery WHERE id = :id LIMIT 1');
            $updateStatement = $pdo->prepare('UPDATE gallery SET category = :category WHERE id = :id');
            foreach ($selectedIds as $galleryId) {
                $selectStatement->execute(['id' => $galleryId]);
                $row = $selectStatement->fetch();
                if (!$row) {
                    continue;
                }
                $galleryMeta = adminParseGalleryCategory((string) ($row['category'] ?? ''));
                $updateStatement->execute([
                    'category' => adminBuildGalleryCategory($galleryMeta['media_type'], $newCategory),
                    'id' => $galleryId,
                ]);
            }

            adminRecordEvent($pdo, $adminId, 'update', 'gallery', 0, 'Bulk media category update', count($selectedIds) . ' media items were moved to ' . ucfirst($newCategory) . '.', '#gallery-panel');
            $message = count($selectedIds) . ' media item(s) updated to ' . ucfirst($newCategory) . '.';
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
            adminRecordEvent($pdo, $adminId, 'create', 'page', (int) $pdo->lastInsertId(), 'Page created', trim((string) ($_POST['title'] ?? 'Page')) . ' was created.', '#pages-panel');
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
            adminRecordEvent($pdo, $adminId, 'update', 'page', (int) ($_POST['id'] ?? 0), 'Page updated', trim((string) ($_POST['title'] ?? 'Page')) . ' was updated.', '#pages-panel');
            $message = 'Page content updated.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'delete_page') {
            $pageId = (int) ($_POST['id'] ?? 0);
            $titleStatement = $pdo->prepare('SELECT title FROM pages WHERE id = :id LIMIT 1');
            $titleStatement->execute(['id' => $pageId]);
            $pageTitle = (string) ($titleStatement->fetchColumn() ?: 'Page');
            $stmt = $pdo->prepare('DELETE FROM pages WHERE id = :id');
            $stmt->execute(['id' => $pageId]);
            adminRecordEvent($pdo, $adminId, 'delete', 'page', $pageId, 'Page deleted', $pageTitle . ' was deleted.', '#pages-panel');
            $message = 'Page content deleted.';
            $editType = '';
            $editId = 0;
        }

        if ($action === 'update_admission_status') {
            $admissionId = (int) ($_POST['id'] ?? 0);
            $applicantStatement = $pdo->prepare('SELECT applicant_name FROM admissions WHERE id = :id LIMIT 1');
            $applicantStatement->execute(['id' => $admissionId]);
            $applicantName = (string) ($applicantStatement->fetchColumn() ?: 'Admission application');
            $status = (string) ($_POST['status'] ?? 'pending');
            $stmt = $pdo->prepare('UPDATE admissions SET status = :status WHERE id = :id');
            $stmt->execute([
                'id' => $admissionId,
                'status' => $status,
            ]);
            adminRecordEvent($pdo, $adminId, 'update', 'admission', $admissionId, 'Admission status updated', $applicantName . ' was marked as ' . $status . '.', '#admissions-panel');
            $message = 'Admission status updated.';
        }

        if ($action === 'delete_admission') {
            $admissionId = (int) ($_POST['id'] ?? 0);
            $applicantStatement = $pdo->prepare('SELECT applicant_name FROM admissions WHERE id = :id LIMIT 1');
            $applicantStatement->execute(['id' => $admissionId]);
            $applicantName = (string) ($applicantStatement->fetchColumn() ?: 'Admission application');
            $stmt = $pdo->prepare('DELETE FROM admissions WHERE id = :id');
            $stmt->execute(['id' => $admissionId]);
            adminRecordEvent($pdo, $adminId, 'delete', 'admission', $admissionId, 'Admission application deleted', $applicantName . ' was removed from admissions.', '#admissions-panel');
            $message = 'Admission application deleted.';
        }

        if ($action === 'mark_message_read') {
            $messageId = (int) ($_POST['id'] ?? 0);
            $senderStatement = $pdo->prepare('SELECT full_name FROM contact_messages WHERE id = :id LIMIT 1');
            $senderStatement->execute(['id' => $messageId]);
            $senderName = (string) ($senderStatement->fetchColumn() ?: 'Contact message');
            $stmt = $pdo->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = :id');
            $stmt->execute(['id' => $messageId]);
            adminRecordEvent($pdo, $adminId, 'review', 'message', $messageId, 'Message marked as read', $senderName . '\'s message was marked as read.', '/MUBUGA-TSS/admin/submissions.php#messages-panel');
            $message = 'Message marked as read.';
        }

        if ($action === 'delete_message') {
            $messageId = (int) ($_POST['id'] ?? 0);
            $senderStatement = $pdo->prepare('SELECT full_name FROM contact_messages WHERE id = :id LIMIT 1');
            $senderStatement->execute(['id' => $messageId]);
            $senderName = (string) ($senderStatement->fetchColumn() ?: 'Contact message');
            $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = :id');
            $stmt->execute(['id' => $messageId]);
            adminRecordEvent($pdo, $adminId, 'delete', 'message', $messageId, 'Message deleted', $senderName . '\'s message was deleted.', '/MUBUGA-TSS/admin/submissions.php#messages-panel');
            $message = 'Message deleted.';
        }

        if ($action === 'unsubscribe_email') {
            $subscriberId = (int) ($_POST['id'] ?? 0);
            $emailStatement = $pdo->prepare('SELECT email FROM newsletter_subscribers WHERE id = :id LIMIT 1');
            $emailStatement->execute(['id' => $subscriberId]);
            $subscriberEmail = (string) ($emailStatement->fetchColumn() ?: 'Subscriber');
            $stmt = $pdo->prepare('UPDATE newsletter_subscribers SET is_active = 0 WHERE id = :id');
            $stmt->execute(['id' => $subscriberId]);
            adminRecordEvent($pdo, $adminId, 'update', 'subscriber', $subscriberId, 'Subscriber deactivated', $subscriberEmail . ' was marked inactive.', '/MUBUGA-TSS/admin/submissions.php#newsletter-panel');
            $message = 'Subscriber marked as inactive.';
        }
    } catch (Throwable $exception) {
        $error = $exception instanceof RuntimeException
            ? $exception->getMessage()
            : 'The update could not be saved. Please check the values and try again.';
    }
    }

    if ($isAjaxRequest) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $error === '',
            'message' => $error === '' ? $message : '',
            'error' => $error,
            'redirect' => '/MUBUGA-TSS/admin/dashboard.php#gallery-panel',
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }
}

$settings = [];
$programs = [];
$staff = [];
$news = [];
$filteredNews = [];
$gallery = [];
$filteredGallery = [];
$admissions = [];
$pages = [];
$contactMessages = [];
$newsletterSubscribers = [];
$notifications = [];
$notificationHistory = [];
$activityLog = [];
$mediaFiles = [];
$searchIndex = [];
$chartSeries = [];
$filteredGalleryCount = 0;
$mediaPageCount = 1;
$mediaPerPage = 8;
$logoPath = 'assets/images/MUBUGA%20LOGO%20SN.PNG';
$activeDashboardPanel = 'dashboard-panel';
$adminName = trim((string) (($admin['name'] ?? $admin['full_name'] ?? 'Administrator')));
$currentDateLabel = date('l, d M Y');
$siteLogoSize = 52;
$adminLogoSize = 34;
$settingsLogoPath = $logoPath;
$settingsLogoPreviewUrl = adminResolveMediaUrl($logoPath);
$imageMediaItems = [];
$videoMediaItems = [];
$announcementItems = [];
$imageCount = 0;
$videoCount = 0;
$announcementCount = 0;
$userCount = 1;
$storageUsedBytes = 0;
$storageLimitBytes = 50 * 1024 * 1024;
$storageUsagePercent = 0;
$mostViewedMedia = [];
$summaryCards = [];
$availableNotificationTypes = [];
$unreadNotificationCount = 0;
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
    'scheduled_for' => '',
    'is_pinned' => 0,
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
    'album_name' => 'General',
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
    try {
        foreach ($pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        if (!empty($settings['school_logo'])) {
            $logoPath = (string) $settings['school_logo'];
        }
        $programs = $pdo->query('SELECT id, title, slug, short_description, duration, department, cover_image, status FROM programs ORDER BY id DESC')->fetchAll();
        $staff = $pdo->query('SELECT id, full_name, job_title, bio, photo, display_order, is_featured, status FROM staff ORDER BY display_order ASC, id DESC')->fetchAll();
        $news = $pdo->query('SELECT id, title, slug, summary, content, featured_image, published_at, status FROM news ORDER BY published_at DESC, id DESC')->fetchAll();
        foreach ($news as &$newsItem) {
            $decodedNewsContent = adminDecodeNewsContent((string) ($newsItem['content'] ?? ''));
            $newsItem['category'] = $decodedNewsContent['category'];
            $newsItem['content'] = $decodedNewsContent['content'];
            $metaStatement = $pdo->prepare('SELECT is_pinned, scheduled_for, view_count FROM news_admin_meta WHERE news_id = :news_id LIMIT 1');
            $metaStatement->execute(['news_id' => (int) $newsItem['id']]);
            $newsMeta = $metaStatement->fetch() ?: [];
            $newsItem['is_pinned'] = (int) ($newsMeta['is_pinned'] ?? 0);
            $newsItem['scheduled_for'] = (string) ($newsMeta['scheduled_for'] ?? '');
            $newsItem['view_count'] = (int) ($newsMeta['view_count'] ?? 0);
        }
        unset($newsItem);
        adminSortNewsItems($news);
        $filteredNews = array_values(array_filter($news, static function (array $item) use ($newsFilter): bool {
            if ($newsFilter === 'all') {
                return true;
            }

            return strtolower((string) ($item['status'] ?? 'published')) === $newsFilter;
        }));
        adminSortNewsItems($filteredNews);

        $gallery = $pdo->query('SELECT id, title, image_path, caption, category, is_featured, created_at FROM gallery ORDER BY id DESC')->fetchAll();
        foreach ($gallery as &$galleryItem) {
            $galleryMeta = adminParseGalleryCategory((string) ($galleryItem['category'] ?? ''));
            $galleryItem['media_type'] = $galleryMeta['media_type'];
            $galleryItem['category'] = $galleryMeta['category'];
            $metaStatement = $pdo->prepare('SELECT album_name, view_count, download_count FROM gallery_admin_meta WHERE gallery_id = :gallery_id LIMIT 1');
            $metaStatement->execute(['gallery_id' => (int) $galleryItem['id']]);
            $galleryAdminMeta = $metaStatement->fetch() ?: [];
            $galleryItem['album_name'] = (string) ($galleryAdminMeta['album_name'] ?? 'General');
            $galleryItem['view_count'] = (int) ($galleryAdminMeta['view_count'] ?? 0);
            $galleryItem['download_count'] = (int) ($galleryAdminMeta['download_count'] ?? 0);
            $galleryItem['download_url'] = adminResolveMediaUrl((string) $galleryItem['image_path']);
            $galleryItem['link'] = '/MUBUGA-TSS/admin/dashboard.php?edit=gallery&id=' . (int) $galleryItem['id'] . '#gallery-panel';
            $galleryItem['meta'] = ucfirst((string) $galleryItem['media_type']) . ' in ' . ucfirst((string) $galleryItem['category']);
            $galleryItem = array_merge($galleryItem, adminMediaMetadata((string) ($galleryItem['image_path'] ?? '')));
        }
        unset($galleryItem);
        $filteredGallery = array_values(array_filter($gallery, static function (array $item) use ($mediaFilter, $mediaSearch, $mediaDateFrom, $mediaDateTo): bool {
            if ($mediaFilter === 'all') {
                $matchesFilter = true;
            } elseif (in_array($mediaFilter, ['image', 'video'], true)) {
                $matchesFilter = (($item['media_type'] ?? 'image') === $mediaFilter);
            } else {
                $matchesFilter = strtolower((string) ($item['category'] ?? '')) === $mediaFilter;
            }

            if (!$matchesFilter) {
                return false;
            }

            $search = strtolower(trim($mediaSearch));
            if ($search !== '') {
                $haystack = strtolower(implode(' ', [
                    (string) ($item['title'] ?? ''),
                    (string) ($item['caption'] ?? ''),
                    (string) ($item['album_name'] ?? ''),
                    (string) ($item['category'] ?? ''),
                    (string) ($item['file_type'] ?? ''),
                ]));
                if (!str_contains($haystack, $search)) {
                    return false;
                }
            }

            $createdAt = trim((string) ($item['created_at'] ?? ''));
            if ($mediaDateFrom !== '' && $createdAt !== '' && strtotime($createdAt) < strtotime($mediaDateFrom . ' 00:00:00')) {
                return false;
            }
            if ($mediaDateTo !== '' && $createdAt !== '' && strtotime($createdAt) > strtotime($mediaDateTo . ' 23:59:59')) {
                return false;
            }

            return true;
        }));
        usort($filteredGallery, static function (array $left, array $right) use ($mediaSort): int {
            return match ($mediaSort) {
                'oldest' => strcmp((string) ($left['created_at'] ?? ''), (string) ($right['created_at'] ?? '')),
                'most_viewed' => ((int) ($right['view_count'] ?? 0)) <=> ((int) ($left['view_count'] ?? 0)),
                default => strcmp((string) ($right['created_at'] ?? ''), (string) ($left['created_at'] ?? '')),
            };
        });
        $mediaPerPage = 8;
        $filteredGalleryCount = count($filteredGallery);
        $mediaPageCount = max(1, (int) ceil($filteredGalleryCount / $mediaPerPage));
        $mediaPage = min($mediaPage, $mediaPageCount);
        $filteredGallery = array_slice($filteredGallery, ($mediaPage - 1) * $mediaPerPage, $mediaPerPage);
        $admissions = $pdo->query('SELECT id, applicant_name, email, preferred_program_id, status, created_at FROM admissions ORDER BY created_at DESC')->fetchAll();
        $pages = $pdo->query('SELECT id, title, slug, excerpt, content, banner_image, status, updated_at FROM pages ORDER BY slug ASC, id DESC')->fetchAll();
        foreach ($pages as &$pageItem) {
            $pageItem['link'] = '/MUBUGA-TSS/admin/dashboard.php?edit=page&id=' . (int) $pageItem['id'] . '#pages-panel';
            $pageItem['meta'] = ucfirst((string) ($pageItem['status'] ?? 'published')) . ' page';
        }
        unset($pageItem);
        $contactMessages = $pdo->query('SELECT id, full_name, email, phone, subject, message_body, is_read, created_at FROM contact_messages ORDER BY created_at DESC')->fetchAll();
        $notifications = $pdo->query('SELECT id, notification_type, title, message, link_target, is_read, created_at FROM admin_notifications ORDER BY created_at DESC LIMIT 12')->fetchAll();
        $notificationHistory = $pdo->query('SELECT id, notification_type, title, message, link_target, is_read, created_at FROM admin_notifications ORDER BY created_at DESC LIMIT 50')->fetchAll();
        $notificationHistory = array_values(array_filter($notificationHistory, static function (array $item) use ($notificationFilter, $notificationTypeFilter): bool {
            $matchesReadState = match ($notificationFilter) {
                'unread' => (int) ($item['is_read'] ?? 0) === 0,
                'read' => (int) ($item['is_read'] ?? 0) === 1,
                default => true,
            };

            $matchesType = $notificationTypeFilter === 'all'
                ? true
                : strtolower((string) ($item['notification_type'] ?? '')) === $notificationTypeFilter;

            return $matchesReadState && $matchesType;
        }));
        $activityLog = $pdo->query('SELECT id, action_type, entity_type, entity_id, title, description, created_at FROM admin_activity_log ORDER BY created_at DESC LIMIT 20')->fetchAll();
        $mediaFiles = adminGetFileEntries([
            dirname(__DIR__) . '/assets/uploads',
            dirname(__DIR__) . '/assets/videos',
        ]);

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
            $metaStatement = $pdo->prepare('SELECT is_pinned, scheduled_for FROM news_admin_meta WHERE news_id = :news_id LIMIT 1');
            $metaStatement->execute(['news_id' => $editId]);
            $newsMeta = $metaStatement->fetch() ?: [];
            $newsForm['is_pinned'] = (int) ($newsMeta['is_pinned'] ?? 0);
            $newsForm['scheduled_for'] = (string) ($newsMeta['scheduled_for'] ?? '');
        }

        if ($editType === 'gallery' && $editId > 0) {
            $stmt = $pdo->prepare('SELECT id, title, image_path, caption, category, is_featured FROM gallery WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $editId]);
            $galleryForm = $stmt->fetch() ?: $galleryForm;
            $galleryMeta = adminParseGalleryCategory((string) ($galleryForm['category'] ?? ''));
            $galleryForm['media_type'] = $galleryMeta['media_type'];
            $galleryForm['category'] = $galleryMeta['category'];
            $metaStatement = $pdo->prepare('SELECT album_name FROM gallery_admin_meta WHERE gallery_id = :gallery_id LIMIT 1');
            $metaStatement->execute(['gallery_id' => $editId]);
            $galleryForm['album_name'] = (string) ($metaStatement->fetchColumn() ?: 'General');
        }

        if ($editType === 'page' && $editId > 0) {
            $stmt = $pdo->prepare('SELECT id, title, slug, excerpt, content, banner_image, status FROM pages WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $editId]);
            $pageForm = $stmt->fetch() ?: $pageForm;
        }

        $newsletterSubscribers = $pdo->query('SELECT id, email, is_active, created_at FROM newsletter_subscribers ORDER BY created_at DESC')->fetchAll();
        $unreadNotificationCount = count(array_filter($notifications, static fn(array $item): bool => (int) ($item['is_read'] ?? 0) === 0));
        $availableNotificationTypes = array_values(array_unique(array_filter(array_map(
            static fn(array $item): string => strtolower((string) ($item['notification_type'] ?? '')),
            $notifications
        ))));
        sort($availableNotificationTypes);

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
        usort($announcementItems, static function (array $left, array $right): int {
        return ((int) ($right['is_pinned'] ?? 0)) <=> ((int) ($left['is_pinned'] ?? 0))
            ?: strcmp((string) ($right['published_at'] ?? ''), (string) ($left['published_at'] ?? ''));
        });
        $imageCount = count($imageMediaItems);
        $videoCount = count($videoMediaItems);
        $announcementCount = count($announcementItems);
        $userCount = count($staff) + 1;
        $searchIndex = adminSearchIndex([
        'Media' => $gallery,
        'Announcements' => array_map(static function (array $item): array {
            $item['link'] = '/MUBUGA-TSS/admin/dashboard.php?edit=news&id=' . (int) $item['id'] . '#news-panel';
            $item['meta'] = ucfirst((string) ($item['category'] ?? 'news')) . ' item';
            return $item;
        }, $news),
        'Pages' => $pages,
        'Programs' => array_map(static function (array $item): array {
            $item['link'] = '/MUBUGA-TSS/admin/dashboard.php?edit=program&id=' . (int) $item['id'] . '#programs-panel';
            $item['meta'] = ucfirst((string) ($item['status'] ?? 'active')) . ' program';
            return $item;
        }, $programs),
        'Team' => array_map(static function (array $item): array {
            $item['link'] = '/MUBUGA-TSS/admin/dashboard.php?edit=staff&id=' . (int) $item['id'] . '#staff-panel';
            $item['meta'] = (string) ($item['job_title'] ?? 'Team member');
            return $item;
        }, $staff),
        'Notifications' => array_map(static function (array $item): array {
            $item['link'] = !empty($item['link_target']) ? (string) $item['link_target'] : '#notifications-panel';
            $item['meta'] = ((int) ($item['is_read'] ?? 0) === 1 ? 'Read' : 'Unread') . ' notification';
            return $item;
        }, $notificationHistory),
        ]);
        $chartSeries = adminMonthlySeries($gallery, 'created_at');
        $storageUsedBytes = adminStorageBytes($mediaFiles);
        $storageLimitBytes = max(1, (int) ($settings['upload_size_limit_mb'] ?? 50)) * 1024 * 1024;
        $storageUsagePercent = min(100, (int) round(($storageUsedBytes / $storageLimitBytes) * 100));
        $mostViewedMedia = $gallery;
        usort($mostViewedMedia, static fn(array $left, array $right): int => ((int) ($right['view_count'] ?? 0)) <=> ((int) ($left['view_count'] ?? 0)));
        $mostViewedMedia = array_slice($mostViewedMedia, 0, 5);
    } catch (Throwable $exception) {
        if ($error === '') {
            $error = 'Dashboard data could not be loaded completely. Please verify that all required database tables exist.';
        }
    }
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
                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="Mubuga TSS logo" class="dashboard-brand-logo-image" style="width: <?php echo max(34, $adminLogoSize + 8); ?>px; height: <?php echo max(34, $adminLogoSize + 8); ?>px;">
                    <div class="dashboard-brand-copy">
                        <p class="admin-eyebrow">Mubuga TSS</p>
                        <h1>Admin Panel</h1>
                    </div>
                    <button type="button" class="dashboard-sidebar-toggle" data-dashboard-sidebar-toggle aria-label="Toggle sidebar" title="Toggle sidebar">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h11M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </div>

                <nav class="dashboard-nav" aria-label="Dashboard navigation">
                    <div class="dashboard-nav-section is-open" data-sidebar-section>
                        <button type="button" class="dashboard-nav-section-trigger" data-sidebar-section-trigger aria-expanded="true">
                            <span class="dashboard-nav-section-label">Core Workspace</span>
                            <span class="dashboard-nav-section-icon" aria-hidden="true">
                                <svg viewBox="0 0 20 20" fill="none"><path d="m6 8 4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </button>
                        <div class="dashboard-nav-links" data-sidebar-section-content>
                            <a href="#dashboard-panel" class="dashboard-nav-link is-active" data-tooltip="Dashboard" title="Dashboard" aria-label="Dashboard">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h5A1.5 1.5 0 0 1 12 5.5v5A1.5 1.5 0 0 1 10.5 12h-5A1.5 1.5 0 0 1 4 10.5v-5zM4 15.5A1.5 1.5 0 0 1 5.5 14h5a1.5 1.5 0 0 1 1.5 1.5v3A1.5 1.5 0 0 1 10.5 20h-5A1.5 1.5 0 0 1 4 18.5v-3zM14 5.5A1.5 1.5 0 0 1 15.5 4h3A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 14 18.5v-13z" fill="currentColor"/></svg>
                                <span>Dashboard</span>
                            </a>
                            <div class="dashboard-nav-item is-open" data-sidebar-item>
                                <div class="dashboard-nav-item-row">
                                    <a href="#gallery-panel" class="dashboard-nav-link" data-tooltip="Media Library" title="Media Library" aria-label="Media Library">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 5a2 2 0 0 0-2 2v10.5A2.5 2.5 0 0 0 5.5 20h13a2.5 2.5 0 0 0 2.5-2.5V7a2 2 0 0 0-2-2H5zm2.5 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3zm11.5 9.5H5.5a.5.5 0 0 1-.39-.813l3.254-4.067a1 1 0 0 1 1.53.017l1.617 2.055 2.75-3.261a1 1 0 0 1 1.55.047l3.58 4.299A.5.5 0 0 1 19 17.5z" fill="currentColor"/></svg>
                                        <span>Media Library</span>
                                    </a>
                                    <button type="button" class="dashboard-nav-item-toggle" data-sidebar-item-toggle aria-expanded="true" aria-label="Toggle Media Library menu">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m6 8 4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                                <div class="dashboard-nav-sublinks" data-sidebar-item-content>
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?media_filter=all#gallery-panel" class="dashboard-nav-sublink">All Media</a>
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?media_filter=image#gallery-panel" class="dashboard-nav-sublink">Images</a>
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?media_filter=video#gallery-panel" class="dashboard-nav-sublink">Videos</a>
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?media_filter=image&media_new=image#gallery-panel" class="dashboard-nav-sublink">New Image</a>
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?media_filter=video&media_new=video#gallery-panel" class="dashboard-nav-sublink">New Video</a>
                                </div>
                            </div>
                            <div class="dashboard-nav-item" data-sidebar-item>
                                <div class="dashboard-nav-item-row">
                                    <a href="#news-panel" class="dashboard-nav-link" data-tooltip="Announcements" title="Announcements" aria-label="Announcements">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 10.5 16.5 4v16L3 13.5v-3zm14.5 1.75h2.25a1.25 1.25 0 0 1 0 2.5H17.5v-2.5zM5.75 14.1h2.2l1.25 4.15H7.1L5.75 14.1z" fill="currentColor"/></svg>
                                        <span>Announcements</span>
                                    </a>
                                    <button type="button" class="dashboard-nav-item-toggle" data-sidebar-item-toggle aria-expanded="false" aria-label="Toggle Announcements menu">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m6 8 4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                                <div class="dashboard-nav-sublinks" data-sidebar-item-content hidden>
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?news_filter=all#news-panel" class="dashboard-nav-sublink">All Updates</a>
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?news_filter=published#news-panel" class="dashboard-nav-sublink">Published</a>
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?news_filter=draft#news-panel" class="dashboard-nav-sublink">Drafts</a>
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?compose=news#news-compose-panel" class="dashboard-nav-sublink">New Update</a>
                                </div>
                            </div>
                            <div class="dashboard-nav-item" data-sidebar-item>
                                <div class="dashboard-nav-item-row">
                                    <a href="#pages-panel" class="dashboard-nav-link" data-tooltip="Pages" title="Pages" aria-label="Pages">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h13A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 18.5v-13zM8 8h8v2H8zm0 4h8v2H8zm0 4h5v2H8z" fill="currentColor"/></svg>
                                        <span>Pages</span>
                                    </a>
                                    <button type="button" class="dashboard-nav-item-toggle" data-sidebar-item-toggle aria-expanded="false" aria-label="Toggle Pages menu">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m6 8 4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                                <div class="dashboard-nav-sublinks" data-sidebar-item-content hidden>
                                    <a href="#pages-panel" class="dashboard-nav-sublink">Site Pages</a>
                                    <a href="#settings-panel" class="dashboard-nav-sublink">Branding</a>
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?compose=page#pages-compose-panel" class="dashboard-nav-sublink">New Page</a>
                                </div>
                            </div>
                            <div class="dashboard-nav-item" data-sidebar-item>
                                <div class="dashboard-nav-item-row">
                                    <a href="#settings-panel" class="dashboard-nav-link" data-tooltip="Settings" title="Settings" aria-label="Settings">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.06-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.03 7.03 0 0 0-1.63-.94l-.36-2.54a.5.5 0 0 0-.5-.42h-3.84a.5.5 0 0 0-.5.42l-.36 2.54c-.58.23-1.13.54-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.71 8.84a.5.5 0 0 0 .12.64l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94L2.83 14.52a.5.5 0 0 0-.12.64l1.92 3.32a.5.5 0 0 0 .6.22l2.39-.96c.5.4 1.05.72 1.63.94l.36 2.54a.5.5 0 0 0 .5.42h3.84a.5.5 0 0 0 .5-.42l.36-2.54c.58-.23 1.13-.54 1.63-.94l2.39.96a.5.5 0 0 0 .6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58zM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5z" fill="currentColor"/></svg>
                                        <span>Settings</span>
                                    </a>
                                    <button type="button" class="dashboard-nav-item-toggle" data-sidebar-item-toggle aria-expanded="false" aria-label="Toggle Settings menu">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m6 8 4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                                <div class="dashboard-nav-sublinks" data-sidebar-item-content hidden>
                                    <a href="#settings-panel" class="dashboard-nav-sublink">General</a>
                                    <a href="#security-panel" class="dashboard-nav-sublink">Security</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-nav-section is-open" data-sidebar-section>
                        <button type="button" class="dashboard-nav-section-trigger" data-sidebar-section-trigger aria-expanded="true">
                            <span class="dashboard-nav-section-label">Monitoring</span>
                            <span class="dashboard-nav-section-icon" aria-hidden="true">
                                <svg viewBox="0 0 20 20" fill="none"><path d="m6 8 4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </button>
                        <div class="dashboard-nav-links" data-sidebar-section-content>
                            <a href="#activity-panel" class="dashboard-nav-link" data-tooltip="Activity Log" title="Activity Log" aria-label="Activity Log">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z" stroke="currentColor" stroke-width="2"/></svg>
                                <span>Activity Log</span>
                            </a>
                            <a href="#notifications-panel" class="dashboard-nav-link" data-tooltip="Notifications" title="Notifications" aria-label="Notifications">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3a4 4 0 0 0-4 4v2.1c0 .7-.24 1.39-.68 1.93L5.6 13.2A1 1 0 0 0 6.36 15h11.28a1 1 0 0 0 .76-1.8l-1.72-2.17A3 3 0 0 1 16 9.1V7a4 4 0 0 0-4-4Zm0 18a3 3 0 0 0 2.82-2H9.18A3 3 0 0 0 12 21Z" fill="currentColor"/></svg>
                                <span>Notifications</span>
                            </a>
                        </div>
                    </div>

                    <div class="dashboard-nav-section dashboard-nav-section-secondary is-open" data-sidebar-section>
                        <button type="button" class="dashboard-nav-section-trigger" data-sidebar-section-trigger aria-expanded="true">
                            <span class="dashboard-nav-section-label dashboard-nav-section-label-secondary">Operations</span>
                            <span class="dashboard-nav-section-icon" aria-hidden="true">
                                <svg viewBox="0 0 20 20" fill="none"><path d="m6 8 4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </button>
                        <div class="dashboard-nav-links" data-sidebar-section-content>
                            <div class="dashboard-nav-item" data-sidebar-item>
                                <div class="dashboard-nav-item-row">
                                    <a href="#files-panel" class="dashboard-nav-link dashboard-nav-link-secondary" data-tooltip="File Manager" title="File Manager" aria-label="File Manager">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h3.59a2.5 2.5 0 0 1 1.77.73l1.41 1.41c.47.47 1.1.73 1.77.73H17.5A2.5 2.5 0 0 1 20 9.5v8A2.5 2.5 0 0 1 17.5 20h-11A2.5 2.5 0 0 1 4 17.5v-11Z" fill="currentColor"/></svg>
                                        <span>File Manager</span>
                                    </a>
                                    <button type="button" class="dashboard-nav-item-toggle" data-sidebar-item-toggle aria-expanded="false" aria-label="Toggle File Manager menu">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m6 8 4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                                <div class="dashboard-nav-sublinks" data-sidebar-item-content hidden>
                                    <a href="#files-panel" class="dashboard-nav-sublink">All Files</a>
                                    <a href="/MUBUGA-TSS/admin/submissions.php" class="dashboard-nav-sublink">Submissions</a>
                                </div>
                            </div>
                            <div class="dashboard-nav-item" data-sidebar-item>
                                <div class="dashboard-nav-item-row">
                                    <a href="#programs-panel" class="dashboard-nav-link dashboard-nav-link-secondary" data-tooltip="Programs" title="Programs" aria-label="Programs">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h13A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 18.5v-13zM8 8h8v2H8zm0 4h8v2H8zm0 4h5v2H8z" fill="currentColor"/></svg>
                                        <span>Programs</span>
                                    </a>
                                    <button type="button" class="dashboard-nav-item-toggle" data-sidebar-item-toggle aria-expanded="false" aria-label="Toggle Programs menu">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m6 8 4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                                <div class="dashboard-nav-sublinks" data-sidebar-item-content hidden>
                                    <a href="#programs-panel" class="dashboard-nav-sublink">All Programs</a>
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?compose=program#programs-compose-panel" class="dashboard-nav-sublink">New Program</a>
                                </div>
                            </div>
                            <div class="dashboard-nav-item" data-sidebar-item>
                                <div class="dashboard-nav-item-row">
                                    <a href="#staff-panel" class="dashboard-nav-link dashboard-nav-link-secondary" data-tooltip="Team" title="Team" aria-label="Team">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm-7 8a7 7 0 1 1 14 0H5zm14.5-9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM18 20c0-2.02-.76-3.86-2.01-5.26A6 6 0 0 1 22 20h-4z" fill="currentColor"/></svg>
                                        <span>Team</span>
                                    </a>
                                    <button type="button" class="dashboard-nav-item-toggle" data-sidebar-item-toggle aria-expanded="false" aria-label="Toggle Team menu">
                                        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m6 8 4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                                <div class="dashboard-nav-sublinks" data-sidebar-item-content hidden>
                                    <a href="#staff-panel" class="dashboard-nav-sublink">Staff List</a>
                                    <a href="#admissions-panel" class="dashboard-nav-sublink">Admissions</a>
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?compose=staff#staff-compose-panel" class="dashboard-nav-sublink">New Staff</a>
                                </div>
                            </div>
                            <a href="#security-panel" class="dashboard-nav-link dashboard-nav-link-secondary" data-tooltip="Security" title="Security" aria-label="Security">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 5 6v6c0 4.3 2.75 8.18 7 9.5 4.25-1.32 7-5.2 7-9.5V6l-7-3Z" fill="currentColor"/></svg>
                                <span>Security</span>
                            </a>
                            <a href="#admissions-panel" class="dashboard-nav-link dashboard-nav-link-secondary" data-tooltip="Admissions" title="Admissions" aria-label="Admissions">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 2.5 4 6.5v5.8c0 4.8 3.12 9.18 8 10.7 4.88-1.52 8-5.9 8-10.7V6.5l-8-4zm-1 5h2v4h3v2h-5v-6z" fill="currentColor"/></svg>
                                <span>Admissions</span>
                            </a>
                        </div>
                    </div>
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
                    <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="Mubuga TSS logo" style="width: <?php echo max(24, $adminLogoSize - 4); ?>px; height: <?php echo max(24, $adminLogoSize - 4); ?>px;">
                </div>
                <form method="get" class="dashboard-global-search" id="dashboardGlobalSearch">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($dashboardSearchQuery); ?>" placeholder="Search media, announcements, and pages..." aria-label="Global dashboard search" data-dashboard-search-input>
                    <button type="submit">Search</button>
                    <div class="dashboard-search-results" hidden data-dashboard-search-results></div>
                </form>
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
                <div class="dashboard-header-tools">
                    <div class="notification-menu" data-notification-menu>
                        <button type="button" class="notification-trigger" aria-label="Notifications" data-notification-trigger>
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3a4 4 0 0 0-4 4v2.1c0 .7-.24 1.39-.68 1.93L5.6 13.2A1 1 0 0 0 6.36 15h11.28a1 1 0 0 0 .76-1.8l-1.72-2.17A3 3 0 0 1 16 9.1V7a4 4 0 0 0-4-4Zm0 18a3 3 0 0 0 2.82-2H9.18A3 3 0 0 0 12 21Z" fill="currentColor"/></svg>
                            <?php if (($unreadNotificationCount ?? 0) > 0): ?>
                                <span class="notification-badge"><?php echo min(99, (int) $unreadNotificationCount); ?></span>
                            <?php endif; ?>
                        </button>
                        <div class="notification-dropdown" hidden data-notification-dropdown>
                            <div class="notification-dropdown-top">
                                <strong>Notifications</strong>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                                    <input type="hidden" name="action" value="mark_all_notifications_read">
                                    <button type="submit" class="inline-link-button">Mark all read</button>
                                </form>
                            </div>
                            <div class="notification-list">
                                <?php foreach ($notifications as $notification): ?>
                                    <div class="notification-item<?php echo (int) ($notification['is_read'] ?? 0) === 0 ? ' is-unread' : ''; ?>">
                                        <div>
                                            <strong><?php echo htmlspecialchars((string) $notification['title']); ?></strong>
                                            <p><?php echo htmlspecialchars((string) ($notification['message'] ?? '')); ?></p>
                                            <span><?php echo htmlspecialchars(adminRelativeTime((string) ($notification['created_at'] ?? ''))); ?></span>
                                        </div>
                                        <div class="notification-actions">
                                            <?php if (!empty($notification['link_target'])): ?>
                                                <a href="<?php echo htmlspecialchars((string) $notification['link_target']); ?>" class="inline-link">Open</a>
                                            <?php endif; ?>
                                            <?php if ((int) ($notification['is_read'] ?? 0) === 0): ?>
                                                <form method="post">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                                                    <input type="hidden" name="action" value="mark_notification_read">
                                                    <input type="hidden" name="notification_id" value="<?php echo (int) $notification['id']; ?>">
                                                    <button type="submit" class="inline-link-button">Read</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($notifications === []): ?>
                                    <div class="notification-empty">No notifications yet.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-admin-pill">
                        <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($logoPath); ?>" alt="Admin avatar" class="dashboard-admin-avatar-image">
                        <div class="dashboard-admin-pill-copy">
                            <strong><?php echo htmlspecialchars($adminName); ?></strong>
                            <small><?php echo htmlspecialchars((string) ($admin['email'] ?? 'admin')); ?></small>
                        </div>
                    </div>
                </div>
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
                            <p>Search content instantly, manage media and announcements, track storage usage, and monitor every admin action from one place.</p>
                            <div class="dashboard-hero-meta">
                                <span><?php echo htmlspecialchars($currentDateLabel); ?></span>
                                <span><?php echo $imageCount; ?> images live</span>
                                <span><?php echo $videoCount; ?> videos live</span>
                                <span><?php echo adminFormatBytes($storageUsedBytes ?? 0); ?> used</span>
                            </div>
                        </div>
                        <div class="dashboard-hero-actions">
                            <a href="#gallery-panel" class="dashboard-hero-action dashboard-card-link">Upload Media</a>
                            <a href="#news-panel" class="dashboard-hero-action dashboard-hero-action-secondary dashboard-card-link">Post Announcement</a>
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
                                <small>Storage</small>
                                <strong><?php echo $storageUsagePercent ?? 0; ?>%</strong>
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
                            <span>Session timeout set to <?php echo (int) ($settings['session_timeout_minutes'] ?? 30); ?> minutes with activity logging enabled.</span>
                        </article>
                    </section>

                    <section class="dashboard-analytics-grid">
                        <article class="panel dashboard-showcase-panel">
                            <div class="panel-top dashboard-showcase-top">
                                <div>
                                    <h3>Upload Activity</h3>
                                    <p class="dashboard-panel-subtitle">Media uploads for the last six months.</p>
                                </div>
                            </div>
                            <div class="activity-chart">
                                <?php $chartMax = max($chartSeries ?: [1]); ?>
                                <?php foreach (($chartSeries ?? []) as $month => $count): ?>
                                    <div class="activity-bar-wrap">
                                        <div class="activity-bar" style="height: <?php echo max(12, (int) round(($count / max(1, $chartMax)) * 120)); ?>px;"></div>
                                        <strong><?php echo (int) $count; ?></strong>
                                        <span><?php echo htmlspecialchars(date('M', strtotime($month . '-01'))); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </article>
                        <article class="panel dashboard-showcase-panel">
                            <div class="panel-top dashboard-showcase-top">
                                <div>
                                    <h3>Performance Snapshot</h3>
                                    <p class="dashboard-panel-subtitle">Most viewed media and storage health.</p>
                                </div>
                            </div>
                            <div class="performance-list">
                                <div class="storage-meter">
                                    <div class="storage-meter-bar" style="width: <?php echo (int) ($storageUsagePercent ?? 0); ?>%;"></div>
                                </div>
                                <p class="storage-meter-copy"><?php echo htmlspecialchars(adminFormatBytes($storageUsedBytes ?? 0)); ?> of <?php echo htmlspecialchars(adminFormatBytes($storageLimitBytes ?? 0)); ?> used</p>
                                <?php foreach (($mostViewedMedia ?? []) as $mediaItem): ?>
                                    <div class="performance-item">
                                        <strong><?php echo htmlspecialchars((string) $mediaItem['title']); ?></strong>
                                        <span><?php echo (int) ($mediaItem['view_count'] ?? 0); ?> views • <?php echo htmlspecialchars((string) ($mediaItem['album_name'] ?? 'General')); ?></span>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (($mostViewedMedia ?? []) === []): ?>
                                    <div class="performance-item is-empty">
                                        <strong>No performance data yet</strong>
                                        <span>Upload media to start building analytics.</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    </section>

                    <section class="dashboard-showcase-grid">
                        <article class="panel dashboard-showcase-panel dashboard-history-panel">
                            <div class="panel-top dashboard-showcase-top">
                                <div>
                                    <h3>Recent History</h3>
                                    <p class="dashboard-panel-subtitle">A quick timeline of the latest admin actions and school updates.</p>
                                </div>
                            </div>
                            <div class="dashboard-history-stack">
                                <div class="dashboard-history-card">
                                    <div class="dashboard-history-card-top">
                                        <strong>Admin Activity</strong>
                                        <span class="inline-meta"><?php echo count($activityLog); ?> recent actions</span>
                                    </div>
                                    <div class="dashboard-history-timeline">
                                        <?php foreach (array_slice($activityLog, 0, 5) as $logItem): ?>
                                            <article class="dashboard-history-item">
                                                <span class="dashboard-history-dot"></span>
                                                <div class="dashboard-history-copy">
                                                    <strong><?php echo htmlspecialchars((string) ($logItem['title'] ?? 'Activity update')); ?></strong>
                                                    <p><?php echo htmlspecialchars((string) ($logItem['description'] ?? 'Dashboard activity recorded.')); ?></p>
                                                    <span><?php echo htmlspecialchars(ucfirst((string) ($logItem['action_type'] ?? 'update'))); ?> • <?php echo htmlspecialchars(adminRelativeTime((string) ($logItem['created_at'] ?? ''))); ?></span>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                        <?php if ($activityLog === []): ?>
                                            <article class="dashboard-history-item is-empty">
                                                <span class="dashboard-history-dot"></span>
                                                <div class="dashboard-history-copy">
                                                    <strong>No recent activity yet</strong>
                                                    <p>Uploads, edits, and settings changes will appear here.</p>
                                                </div>
                                            </article>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="dashboard-history-card dashboard-history-card-secondary">
                                    <div class="dashboard-history-card-top">
                                        <strong>Latest Announcements</strong>
                                        <span class="inline-meta"><?php echo $announcementCount; ?> live</span>
                                    </div>
                                    <div class="announcement-feed announcement-feed-compact">
                                        <?php foreach (array_slice($announcementItems, 0, 2) as $item): ?>
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
                                </div>
                            </div>
                        </article>

                        <article class="panel dashboard-showcase-panel dashboard-media-overview-panel">
                            <div class="panel-top dashboard-showcase-top">
                                <div>
                                    <h3>Media Highlights</h3>
                                    <p class="dashboard-panel-subtitle">Cleaner preview cards for the newest images and videos on the website.</p>
                                </div>
                            </div>
                            <div class="dashboard-media-actions">
                                <a href="#gallery-panel" class="dashboard-media-action dashboard-card-link">Upload New Image</a>
                                <a href="#gallery-panel" class="dashboard-media-action dashboard-media-action-video dashboard-card-link">Upload New Video</a>
                            </div>

                            <div class="dashboard-media-overview-grid">
                                <div class="dashboard-media-column">
                                    <div class="dashboard-media-group-top">
                                        <strong>Featured Videos</strong>
                                        <span class="inline-meta"><?php echo $videoCount; ?> total</span>
                                    </div>
                                    <div class="dashboard-media-thumbs dashboard-media-thumbs-video dashboard-media-thumbs-featured">
                                        <?php foreach (array_slice($videoMediaItems, 0, 2) as $item): ?>
                                            <article class="dashboard-thumb dashboard-thumb-video dashboard-thumb-card">
                                                <div class="dashboard-thumb-media">
                                                    <div class="dashboard-thumb-video-overlay">&#9658;</div>
                                                    <?php if (adminIsVideoPath((string) $item['image_path'])): ?>
                                                        <video controls playsinline preload="metadata" src="<?php echo htmlspecialchars(adminResolveMediaUrl((string) $item['image_path'])); ?>"></video>
                                                    <?php else: ?>
                                                        <img src="<?php echo htmlspecialchars(adminResolveMediaUrl((string) $item['image_path'])); ?>" alt="<?php echo htmlspecialchars((string) $item['title']); ?>" class="photo-viewer">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="dashboard-thumb-copy">
                                                    <strong><?php echo htmlspecialchars((string) $item['title']); ?></strong>
                                                    <span><?php echo htmlspecialchars(ucfirst((string) ($item['category'] ?? 'general'))); ?> • <?php echo (int) ($item['view_count'] ?? 0); ?> views</span>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                        <?php if ($videoMediaItems === []): ?>
                                            <article class="dashboard-thumb dashboard-thumb-video dashboard-thumb-card dashboard-thumb-empty-card">
                                                <div class="dashboard-thumb-media">
                                                    <div class="dashboard-thumb-video-overlay">&#9658;</div>
                                                    <img src="/MUBUGA-TSS/assets/images/school view 5.jpg" alt="Video placeholder" class="photo-viewer">
                                                </div>
                                                <div class="dashboard-thumb-copy">
                                                    <strong>No videos yet</strong>
                                                    <span>Upload the first school video to improve this section.</span>
                                                </div>
                                            </article>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="dashboard-media-column">
                                    <div class="dashboard-media-group-top">
                                        <strong>Latest Photos</strong>
                                        <span class="inline-meta"><?php echo $imageCount; ?> total</span>
                                    </div>
                                    <div class="dashboard-media-thumbs dashboard-media-thumbs-featured">
                                        <?php foreach (array_slice($imageMediaItems, 0, 3) as $item): ?>
                                            <article class="dashboard-thumb dashboard-thumb-card">
                                                <div class="dashboard-thumb-media">
                                                    <img src="<?php echo htmlspecialchars(adminResolveMediaUrl((string) $item['image_path'])); ?>" alt="<?php echo htmlspecialchars((string) $item['title']); ?>" class="photo-viewer">
                                                </div>
                                                <div class="dashboard-thumb-copy">
                                                    <strong><?php echo htmlspecialchars((string) $item['title']); ?></strong>
                                                    <span><?php echo htmlspecialchars(ucfirst((string) ($item['category'] ?? 'general'))); ?> • <?php echo (int) ($item['view_count'] ?? 0); ?> views</span>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                        <?php if ($imageMediaItems === []): ?>
                                            <article class="dashboard-thumb dashboard-thumb-card dashboard-thumb-empty-card">
                                                <div class="dashboard-thumb-media">
                                                    <img src="/MUBUGA-TSS/assets/images/school view 4.jpg" alt="Image placeholder" class="photo-viewer">
                                                </div>
                                                <div class="dashboard-thumb-copy">
                                                    <strong>No images yet</strong>
                                                    <span>Upload the first school image to populate this section.</span>
                                                </div>
                                            </article>
                                        <?php endif; ?>
                                    </div>
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
                            <h2>Branding, upload rules, and dashboard controls</h2>
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
                        <label><span>Theme</span>
                            <select name="theme_mode">
                                <option value="light"<?php echo (($settings['theme_mode'] ?? 'light') === 'light') ? ' selected' : ''; ?>>Light</option>
                                <option value="dark"<?php echo (($settings['theme_mode'] ?? '') === 'dark') ? ' selected' : ''; ?>>Dark</option>
                            </select>
                        </label>
                        <label><span>Upload Size Limit (MB)</span><input type="number" name="upload_size_limit_mb" min="5" max="512" value="<?php echo (int) ($settings['upload_size_limit_mb'] ?? 50); ?>"></label>
                        <label><span>Allowed File Types</span><input type="text" name="allowed_file_types" value="<?php echo htmlspecialchars((string) ($settings['allowed_file_types'] ?? 'jpg,jpeg,png,gif,webp,jfif,mp4,webm,ogg')); ?>"></label>
                        <label><span>Session Timeout (minutes)</span><input type="number" name="session_timeout_minutes" min="5" max="240" value="<?php echo (int) ($settings['session_timeout_minutes'] ?? 30); ?>"></label>
                        <label><span>Homepage Notice</span><textarea name="homepage_notice" rows="4"><?php echo htmlspecialchars((string) ($settings['homepage_notice'] ?? '')); ?></textarea></label>
                        <button type="submit">Save Settings</button>
                    </form>
                    <div class="settings-inline-actions">
                        <a href="/MUBUGA-TSS/admin/dashboard.php?export=json" class="button-secondary">Export / Backup Data</a>
                        <a href="#files-panel" class="button-secondary dashboard-card-link">Open File Manager</a>
                        <a href="#security-panel" class="button-secondary dashboard-card-link">Security Controls</a>
                    </div>
                </article>

                <article class="panel dashboard-view-panel" id="files-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Files</p>
                            <h2>File management and storage history</h2>
                        </div>
                    </div>
                    <div class="management-section" id="news-compose-panel">
                        <div class="management-section-header">
                            <div>
                                <strong>Storage locations</strong>
                                <span>Uploads are stored in <code>assets/uploads</code> and <code>assets/videos</code>.</span>
                            </div>
                            <span class="management-tag"><?php echo htmlspecialchars(adminFormatBytes($storageUsedBytes ?? 0)); ?> used</span>
                        </div>
                        <div class="table-list">
                            <?php foreach (array_slice($mediaFiles, 0, 12) as $fileEntry): ?>
                                <div class="table-item">
                                    <div class="table-item-content">
                                        <strong><?php echo htmlspecialchars((string) $fileEntry['name']); ?></strong>
                                        <span><?php echo htmlspecialchars((string) $fileEntry['relative_path']); ?> • <?php echo htmlspecialchars(adminFormatBytes((int) $fileEntry['size'])); ?> • <?php echo htmlspecialchars(adminRelativeTime((string) $fileEntry['modified_at'])); ?></span>
                                    </div>
                                    <div class="item-actions">
                                        <a href="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $fileEntry['relative_path']); ?>" class="action-link" download>Download</a>
                                        <form method="post" class="inline-form">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                                            <input type="hidden" name="action" value="delete_file">
                                            <input type="hidden" name="file_path" value="<?php echo htmlspecialchars((string) $fileEntry['relative_path']); ?>">
                                            <button type="submit" class="action-link action-button danger-button">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($mediaFiles === []): ?>
                                <div class="table-item empty-state-card">
                                    <strong>No files uploaded yet</strong>
                                    <span>Your upload history will appear here after the first media upload.</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>

                <article class="panel dashboard-view-panel" id="security-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Security</p>
                            <h2>Session protection and password controls</h2>
                        </div>
                    </div>
                    <div class="management-section">
                        <div class="management-section-header">
                            <div>
                                <strong>Admin safeguards</strong>
                                <span>Session timeout and activity logs are active. Use this form to change your password.</span>
                            </div>
                            <span class="management-tag"><?php echo (int) ($settings['session_timeout_minutes'] ?? 30); ?> min timeout</span>
                        </div>
                        <form method="post" class="admin-form">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                            <input type="hidden" name="action" value="change_password">
                            <label><span>Current Password</span><input type="password" name="current_password" required></label>
                            <label><span>New Password</span><input type="password" name="new_password" required minlength="8"></label>
                            <label><span>Confirm Password</span><input type="password" name="confirm_password" required minlength="8"></label>
                            <button type="submit">Change Password</button>
                        </form>
                    </div>
                </article>

                <article class="panel dashboard-view-panel" id="activity-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Activity</p>
                            <h2>Admin activity log</h2>
                        </div>
                    </div>
                    <div class="table-list">
                        <?php foreach ($activityLog as $logItem): ?>
                            <div class="table-item">
                                <div class="table-item-content">
                                    <strong><?php echo htmlspecialchars((string) $logItem['title']); ?></strong>
                                    <span><?php echo htmlspecialchars((string) ($logItem['description'] ?? '')); ?></span>
                                </div>
                                <div class="item-actions">
                                    <span class="activity-pill"><?php echo htmlspecialchars((string) strtoupper((string) ($logItem['action_type'] ?? 'log'))); ?></span>
                                    <span class="inline-meta"><?php echo htmlspecialchars(adminRelativeTime((string) ($logItem['created_at'] ?? ''))); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($activityLog === []): ?>
                            <div class="table-item empty-state-card">
                                <strong>No activity yet</strong>
                                <span>Uploads, edits, and deletes will be tracked here automatically.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>

                <article class="panel dashboard-view-panel" id="notifications-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Notifications</p>
                            <h2>System notification history</h2>
                        </div>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                            <input type="hidden" name="action" value="mark_all_notifications_read">
                            <button type="submit" class="button-secondary">Mark all read</button>
                        </form>
                    </div>
                    <div class="news-filter-bar">
                        <a href="/MUBUGA-TSS/admin/dashboard.php?notification_filter=all&notification_type=<?php echo urlencode($notificationTypeFilter); ?>#notifications-panel" class="news-filter-link<?php echo $notificationFilter === 'all' ? ' is-active' : ''; ?>">All</a>
                        <a href="/MUBUGA-TSS/admin/dashboard.php?notification_filter=unread&notification_type=<?php echo urlencode($notificationTypeFilter); ?>#notifications-panel" class="news-filter-link<?php echo $notificationFilter === 'unread' ? ' is-active' : ''; ?>">Unread</a>
                        <a href="/MUBUGA-TSS/admin/dashboard.php?notification_filter=read&notification_type=<?php echo urlencode($notificationTypeFilter); ?>#notifications-panel" class="news-filter-link<?php echo $notificationFilter === 'read' ? ' is-active' : ''; ?>">Read</a>
                    </div>
                    <div class="news-filter-bar">
                        <a href="/MUBUGA-TSS/admin/dashboard.php?notification_filter=<?php echo urlencode($notificationFilter); ?>&notification_type=all#notifications-panel" class="news-filter-link<?php echo $notificationTypeFilter === 'all' ? ' is-active' : ''; ?>">All Types</a>
                        <?php foreach ($availableNotificationTypes as $notificationType): ?>
                            <a href="/MUBUGA-TSS/admin/dashboard.php?notification_filter=<?php echo urlencode($notificationFilter); ?>&notification_type=<?php echo urlencode($notificationType); ?>#notifications-panel" class="news-filter-link<?php echo $notificationTypeFilter === $notificationType ? ' is-active' : ''; ?>"><?php echo htmlspecialchars(ucfirst($notificationType)); ?></a>
                        <?php endforeach; ?>
                    </div>
                    <div class="table-list">
                        <?php foreach ($notificationHistory as $notification): ?>
                            <div class="table-item">
                                <div class="table-item-content">
                                    <strong><?php echo htmlspecialchars((string) $notification['title']); ?></strong>
                                    <span><?php echo htmlspecialchars((string) ($notification['message'] ?? '')); ?></span>
                                    <span><?php echo htmlspecialchars(adminRelativeTime((string) ($notification['created_at'] ?? ''))); ?> - <?php echo htmlspecialchars(ucfirst((string) ($notification['notification_type'] ?? 'update'))); ?> - <?php echo ((int) ($notification['is_read'] ?? 0) === 1) ? 'Read' : 'Unread'; ?></span>
                                </div>
                                <div class="item-actions">
                                    <?php if (!empty($notification['link_target'])): ?>
                                        <a href="<?php echo htmlspecialchars((string) $notification['link_target']); ?>" class="action-link">Open</a>
                                    <?php endif; ?>
                                    <?php if ((int) ($notification['is_read'] ?? 0) === 0): ?>
                                        <form method="post" class="inline-form">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                                            <input type="hidden" name="action" value="mark_notification_read">
                                            <input type="hidden" name="notification_id" value="<?php echo (int) $notification['id']; ?>">
                                            <button type="submit" class="action-link action-button">Mark Read</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($notificationHistory === []): ?>
                            <div class="table-item empty-state-card">
                                <strong>No notifications yet</strong>
                                <span>Uploads, updates, and deletes will appear here automatically.</span>
                            </div>
                        <?php endif; ?>
                    </div>
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
                                        <span><?php echo htmlspecialchars(ucfirst((string) ($newsItem['category'] ?? 'news'))); ?> - <?php echo htmlspecialchars(ucfirst((string) ($newsItem['status'] ?? 'published'))); ?><?php echo !empty($newsItem['published_at']) ? ' - ' . htmlspecialchars((string) $newsItem['published_at']) : ''; ?><?php echo !empty($newsItem['scheduled_for']) ? ' - Scheduled ' . htmlspecialchars((string) $newsItem['scheduled_for']) : ''; ?><?php echo ((int) ($newsItem['is_pinned'] ?? 0) === 1) ? ' - Pinned' : ''; ?> - Views: <?php echo (int) ($newsItem['view_count'] ?? 0); ?></span>
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
                        <label><span>Publish Later</span><input type="datetime-local" name="scheduled_for" value="<?php echo htmlspecialchars(!empty($newsForm['scheduled_for']) ? date('Y-m-d\TH:i', strtotime((string) $newsForm['scheduled_for'])) : ''); ?>"></label>
                        <label class="checkbox"><input type="checkbox" name="is_pinned"<?php echo ((int) ($newsForm['is_pinned'] ?? 0) === 1) ? ' checked' : ''; ?>> <span>Pin this announcement</span></label>
                        <label><span>Summary</span><textarea name="summary" rows="4" data-editor><?php echo htmlspecialchars((string) $newsForm['summary']); ?></textarea></label>
                        <label><span>Full Content</span><textarea name="content" rows="7" data-editor><?php echo htmlspecialchars((string) $newsForm['content']); ?></textarea></label>
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
                            <h2>Media Library</h2>
                            <p>Browse, preview, filter, and organize uploaded media. Uploading now happens in a dedicated modal so this screen stays focused on management.</p>
                        </div>
                        <div class="gallery-panel-actions">
                            <button type="button" class="button-secondary" data-modal-open="gallery-upload-modal"><?php echo $editType === 'gallery' ? 'Edit Selected Media' : 'Upload Media'; ?></button>
                        </div>
                    </div>
                    <div class="management-section gallery-library gallery-library-expanded">
                        <form method="get" class="gallery-toolbar" action="/MUBUGA-TSS/admin/dashboard.php#gallery-panel">
                            <input type="hidden" name="media_page" value="1">
                            <div class="gallery-toolbar-search">
                                <input type="text" name="media_search" value="<?php echo htmlspecialchars($mediaSearch); ?>" placeholder="Search title, caption, album, file type...">
                            </div>
                            <div class="gallery-toolbar-filters">
                                <select name="media_filter">
                                    <?php foreach ([
                                        'all' => 'All media',
                                        'image' => 'Images',
                                        'video' => 'Videos',
                                        'events' => 'Events',
                                        'exams' => 'Exams',
                                        'sports' => 'Sports',
                                        'general' => 'General',
                                        'campus' => 'Campus',
                                    ] as $filterValue => $filterLabel): ?>
                                        <option value="<?php echo htmlspecialchars($filterValue); ?>"<?php echo $mediaFilter === $filterValue ? ' selected' : ''; ?>><?php echo htmlspecialchars($filterLabel); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="media_sort">
                                    <option value="newest"<?php echo $mediaSort === 'newest' ? ' selected' : ''; ?>>Newest</option>
                                    <option value="oldest"<?php echo $mediaSort === 'oldest' ? ' selected' : ''; ?>>Oldest</option>
                                    <option value="most_viewed"<?php echo $mediaSort === 'most_viewed' ? ' selected' : ''; ?>>Most Viewed</option>
                                </select>
                                <input type="date" name="media_date_from" value="<?php echo htmlspecialchars($mediaDateFrom); ?>" aria-label="Filter from date">
                                <input type="date" name="media_date_to" value="<?php echo htmlspecialchars($mediaDateTo); ?>" aria-label="Filter to date">
                                <button type="submit">Apply</button>
                            </div>
                        </form>

                        <form method="post" class="gallery-bulk-form" data-gallery-bulk-form>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                            <div class="management-section-header">
                                <div>
                                    <strong>Current Library</strong>
                                    <span><?php echo (int) $filteredGalleryCount; ?> result(s) found. Use multi-select to assign categories or remove items in bulk.</span>
                                </div>
                                <span class="management-tag">Page <?php echo (int) $mediaPage; ?> of <?php echo (int) $mediaPageCount; ?></span>
                            </div>
                            <div class="gallery-bulk-bar">
                                <label class="checkbox gallery-select-all">
                                    <input type="checkbox" data-gallery-select-all>
                                    <span>Select all on this page</span>
                                </label>
                                <div class="gallery-bulk-actions">
                                    <select name="bulk_category">
                                        <option value="">Bulk category</option>
                                        <?php foreach (['campus', 'events', 'exams', 'sports', 'general'] as $mediaCategory): ?>
                                            <option value="<?php echo htmlspecialchars($mediaCategory); ?>"><?php echo htmlspecialchars(ucfirst($mediaCategory)); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="action" value="bulk_update_gallery_category" class="button-secondary">Assign Category</button>
                                    <button type="submit" name="action" value="bulk_delete_gallery" class="danger-button" data-gallery-bulk-delete>Delete Selected</button>
                                </div>
                            </div>
                            <div class="table-list gallery-media-grid">
                            <?php foreach ($filteredGallery as $galleryItem): ?>
                                <div class="table-item gallery-card" data-gallery-item>
                                    <div class="table-item-layout gallery-card-layout">
                                        <label class="gallery-card-check">
                                            <input type="checkbox" name="selected_gallery_ids[]" value="<?php echo (int) $galleryItem['id']; ?>" data-gallery-select>
                                        </label>
                                        <?php if (($galleryItem['media_type'] ?? 'image') === 'video' && adminIsVideoPath((string) $galleryItem['image_path'])): ?>
                                            <button type="button" class="gallery-preview-trigger" data-gallery-preview-open
                                                data-media-id="<?php echo (int) $galleryItem['id']; ?>"
                                                data-media-url="<?php echo htmlspecialchars(adminResolveMediaUrl((string) $galleryItem['image_path'])); ?>"
                                                data-media-type="<?php echo htmlspecialchars((string) ($galleryItem['media_type'] ?? 'image')); ?>"
                                                data-media-title="<?php echo htmlspecialchars((string) $galleryItem['title']); ?>"
                                                data-media-caption="<?php echo htmlspecialchars((string) ($galleryItem['caption'] ?? '')); ?>"
                                                data-media-category="<?php echo htmlspecialchars((string) $galleryItem['category']); ?>"
                                                data-media-album="<?php echo htmlspecialchars((string) ($galleryItem['album_name'] ?? 'General')); ?>"
                                                data-media-size="<?php echo htmlspecialchars((string) ($galleryItem['file_size_label'] ?? 'Unknown size')); ?>"
                                                data-media-filetype="<?php echo htmlspecialchars((string) ($galleryItem['file_type'] ?? 'FILE')); ?>"
                                                data-media-dimensions="<?php echo htmlspecialchars((string) ($galleryItem['dimensions'] ?? '')); ?>"
                                                data-media-date="<?php echo htmlspecialchars(date('d M Y, H:i', strtotime((string) ($galleryItem['created_at'] ?? 'now')))); ?>"
                                                data-media-download="<?php echo htmlspecialchars((string) ($galleryItem['download_url'] ?? '#')); ?>">
                                                <video class="table-thumb" muted playsinline preload="metadata" src="<?php echo htmlspecialchars(adminResolveMediaUrl((string) $galleryItem['image_path'])); ?>"></video>
                                                <span class="gallery-card-type-badge">Video</span>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="gallery-preview-trigger" data-gallery-preview-open
                                                data-media-id="<?php echo (int) $galleryItem['id']; ?>"
                                                data-media-url="<?php echo htmlspecialchars(adminResolveMediaUrl((string) $galleryItem['image_path'])); ?>"
                                                data-media-type="<?php echo htmlspecialchars((string) ($galleryItem['media_type'] ?? 'image')); ?>"
                                                data-media-title="<?php echo htmlspecialchars((string) $galleryItem['title']); ?>"
                                                data-media-caption="<?php echo htmlspecialchars((string) ($galleryItem['caption'] ?? '')); ?>"
                                                data-media-category="<?php echo htmlspecialchars((string) $galleryItem['category']); ?>"
                                                data-media-album="<?php echo htmlspecialchars((string) ($galleryItem['album_name'] ?? 'General')); ?>"
                                                data-media-size="<?php echo htmlspecialchars((string) ($galleryItem['file_size_label'] ?? 'Unknown size')); ?>"
                                                data-media-filetype="<?php echo htmlspecialchars((string) ($galleryItem['file_type'] ?? 'FILE')); ?>"
                                                data-media-dimensions="<?php echo htmlspecialchars((string) ($galleryItem['dimensions'] ?? '')); ?>"
                                                data-media-date="<?php echo htmlspecialchars(date('d M Y, H:i', strtotime((string) ($galleryItem['created_at'] ?? 'now')))); ?>"
                                                data-media-download="<?php echo htmlspecialchars((string) ($galleryItem['download_url'] ?? '#')); ?>">
                                                <img src="<?php echo htmlspecialchars(adminResolveMediaUrl((string) $galleryItem['image_path'])); ?>" alt="" class="table-thumb photo-viewer">
                                                <span class="gallery-card-type-badge">Image</span>
                                            </button>
                                        <?php endif; ?>
                                        <div class="table-item-content gallery-card-content">
                                            <strong class="gallery-card-title"><?php echo htmlspecialchars(basename((string) ($galleryItem['image_path'] ?? (string) $galleryItem['title']))); ?></strong>
                                            <span class="gallery-card-size"><?php echo htmlspecialchars((string) ($galleryItem['file_size_label'] ?? 'Unknown size')); ?></span>
                                            <div class="gallery-media-meta">
                                                <span class="inline-meta"><?php echo htmlspecialchars(ucfirst((string) ($galleryItem['media_type'] ?? 'image'))); ?></span>
                                                <span class="inline-meta"><?php echo htmlspecialchars(ucfirst((string) $galleryItem['category'])); ?></span>
                                                <span class="inline-meta">Album: <?php echo htmlspecialchars((string) ($galleryItem['album_name'] ?? 'General')); ?></span>
                                                <span class="inline-meta"><?php echo (int) ($galleryItem['view_count'] ?? 0); ?> views</span>
                                                <span class="inline-meta"><?php echo (int) ($galleryItem['download_count'] ?? 0); ?> downloads</span>
                                                <span class="inline-meta"><?php echo htmlspecialchars((string) ($galleryItem['file_size_label'] ?? 'Unknown size')); ?></span>
                                                <span class="inline-meta"><?php echo htmlspecialchars((string) ($galleryItem['file_type'] ?? 'FILE')); ?></span>
                                                <?php if (trim((string) ($galleryItem['dimensions'] ?? '')) !== ''): ?>
                                                    <span class="inline-meta"><?php echo htmlspecialchars((string) $galleryItem['dimensions']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (trim((string) ($galleryItem['caption'] ?? '')) !== ''): ?>
                                                <p class="table-paragraph"><?php echo htmlspecialchars((string) $galleryItem['caption']); ?></p>
                                            <?php endif; ?>
                                            <span class="gallery-meta-date">Uploaded <?php echo htmlspecialchars(date('d M Y, H:i', strtotime((string) ($galleryItem['created_at'] ?? 'now')))); ?></span>
                                        </div>
                                        <details class="gallery-action-menu">
                                            <summary aria-label="Media actions">⋮</summary>
                                            <div class="gallery-action-dropdown">
                                                <button type="button" class="action-link" data-gallery-preview-open
                                                    data-media-id="<?php echo (int) $galleryItem['id']; ?>"
                                                    data-media-url="<?php echo htmlspecialchars(adminResolveMediaUrl((string) $galleryItem['image_path'])); ?>"
                                                    data-media-type="<?php echo htmlspecialchars((string) ($galleryItem['media_type'] ?? 'image')); ?>"
                                                    data-media-title="<?php echo htmlspecialchars((string) $galleryItem['title']); ?>"
                                                    data-media-caption="<?php echo htmlspecialchars((string) ($galleryItem['caption'] ?? '')); ?>"
                                                    data-media-category="<?php echo htmlspecialchars((string) $galleryItem['category']); ?>"
                                                    data-media-album="<?php echo htmlspecialchars((string) ($galleryItem['album_name'] ?? 'General')); ?>"
                                                    data-media-size="<?php echo htmlspecialchars((string) ($galleryItem['file_size_label'] ?? 'Unknown size')); ?>"
                                                    data-media-filetype="<?php echo htmlspecialchars((string) ($galleryItem['file_type'] ?? 'FILE')); ?>"
                                                    data-media-dimensions="<?php echo htmlspecialchars((string) ($galleryItem['dimensions'] ?? '')); ?>"
                                                    data-media-date="<?php echo htmlspecialchars(date('d M Y, H:i', strtotime((string) ($galleryItem['created_at'] ?? 'now')))); ?>"
                                                    data-media-download="<?php echo htmlspecialchars((string) ($galleryItem['download_url'] ?? '#')); ?>">Preview</button>
                                                <a href="<?php echo htmlspecialchars((string) ($galleryItem['download_url'] ?? '#')); ?>" class="action-link" download>Download</a>
                                                <a href="/MUBUGA-TSS/admin/dashboard.php?edit=gallery&id=<?php echo (int) $galleryItem['id']; ?>#gallery-panel" class="action-link">Edit</a>
                                                <button type="button" class="danger-button" data-gallery-delete-open data-media-id="<?php echo (int) $galleryItem['id']; ?>" data-media-title="<?php echo htmlspecialchars((string) $galleryItem['title']); ?>">Delete</button>
                                            </div>
                                        </details>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($filteredGallery === []): ?>
                                <div class="table-item empty-state-card gallery-empty-state">
                                    <strong>No media uploaded yet</strong>
                                    <span>Start building the library by opening the upload modal and adding your first image or video.</span>
                                    <button type="button" class="button-secondary" data-modal-open="gallery-upload-modal">Upload first media</button>
                                </div>
                            <?php endif; ?>
                            </div>
                        </form>
                        <?php if ($mediaPageCount > 1): ?>
                            <div class="gallery-pagination">
                                <?php for ($pageNumber = 1; $pageNumber <= $mediaPageCount; $pageNumber++): ?>
                                    <a href="/MUBUGA-TSS/admin/dashboard.php?media_filter=<?php echo urlencode($mediaFilter); ?>&media_sort=<?php echo urlencode($mediaSort); ?>&media_search=<?php echo urlencode($mediaSearch); ?>&media_date_from=<?php echo urlencode($mediaDateFrom); ?>&media_date_to=<?php echo urlencode($mediaDateTo); ?>&media_page=<?php echo $pageNumber; ?>#gallery-panel" class="news-filter-link<?php echo $pageNumber === $mediaPage ? ' is-active' : ''; ?>"><?php echo $pageNumber; ?></a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="admin-modal" id="gallery-upload-modal" hidden data-admin-modal>
                        <div class="admin-modal-backdrop" data-modal-close></div>
                        <div class="admin-modal-dialog admin-modal-dialog-wide">
                            <div class="admin-modal-header">
                                <div>
                                    <p class="admin-eyebrow">Upload</p>
                                    <h3><?php echo $editType === 'gallery' ? 'Update media item' : 'Upload New Media'; ?></h3>
                                    <p>Allowed file types: <?php echo htmlspecialchars((string) ($settings['allowed_file_types'] ?? 'jpg,jpeg,png,gif,webp,jfif,mp4,webm,ogg')); ?>. Max upload size: <?php echo htmlspecialchars((string) ($settings['upload_size_limit_mb'] ?? '50')); ?> MB.</p>
                                </div>
                                <button type="button" class="admin-modal-close" data-modal-close aria-label="Close upload modal">×</button>
                            </div>
                            <form method="post" class="admin-form gallery-admin-form" enctype="multipart/form-data" data-gallery-upload-form>
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                                <input type="hidden" name="action" value="<?php echo $editType === 'gallery' ? 'update_gallery' : 'add_gallery'; ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $galleryForm['id']; ?>">
                                <div class="gallery-form-grid">
                                    <label>
                                        <span>Title</span>
                                        <input type="text" name="title" value="<?php echo htmlspecialchars((string) $galleryForm['title']); ?>">
                                    </label>
                                    <label>
                                        <span>Media Type</span>
                                        <select name="media_type">
                                            <option value="image"<?php echo (($galleryForm['media_type'] ?? 'image') === 'image') ? ' selected' : ''; ?>>Image</option>
                                            <option value="video"<?php echo (($galleryForm['media_type'] ?? '') === 'video') ? ' selected' : ''; ?>>Video</option>
                                        </select>
                                    </label>
                                    <label class="gallery-field-wide">
                                        <span>Media Path or Video URL</span>
                                        <input type="text" name="image_path" value="<?php echo htmlspecialchars((string) $galleryForm['image_path']); ?>">
                                    </label>
                                    <label class="gallery-field-wide">
                                        <span>Upload Image or Video</span>
                                        <input type="file" name="gallery_image_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif,.mp4,.webm,.ogg" class="upload-input" data-upload-drop>
                                    </label>
                                    <label>
                                        <span>Category</span>
                                        <select name="category">
                                            <?php foreach (['campus', 'events', 'exams', 'sports', 'general'] as $mediaCategory): ?>
                                                <option value="<?php echo htmlspecialchars($mediaCategory); ?>"<?php echo (($galleryForm['category'] ?? 'campus') === $mediaCategory) ? ' selected' : ''; ?>><?php echo htmlspecialchars(ucfirst($mediaCategory)); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <label>
                                        <span>Album</span>
                                        <input type="text" name="album_name" value="<?php echo htmlspecialchars((string) ($galleryForm['album_name'] ?? 'General')); ?>">
                                    </label>
                                    <label class="gallery-field-wide">
                                        <span>Caption / Description</span>
                                        <textarea name="caption" rows="4"><?php echo htmlspecialchars((string) $galleryForm['caption']); ?></textarea>
                                    </label>
                                </div>
                                <div class="gallery-upload-guidance">
                                    <span>Drag and drop is supported in this upload area.</span>
                                    <span>Large files may take longer depending on your connection and server speed.</span>
                                </div>
                                <div class="gallery-upload-feedback" hidden data-gallery-upload-feedback></div>
                                <div class="gallery-upload-progress" hidden data-gallery-upload-progress>
                                    <div class="gallery-upload-progress-bar" data-gallery-upload-progress-bar></div>
                                    <span data-gallery-upload-progress-label>Preparing upload...</span>
                                </div>
                                <div class="gallery-form-actions">
                                    <label class="checkbox">
                                        <input type="checkbox" name="is_featured"<?php echo ((int) $galleryForm['is_featured'] === 1) ? ' checked' : ''; ?>>
                                        <span>Featured image</span>
                                    </label>
                                    <button type="submit"><?php echo $editType === 'gallery' ? 'Update Media' : 'Add Media'; ?></button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="admin-modal" id="gallery-preview-modal" hidden data-admin-modal>
                        <div class="admin-modal-backdrop" data-modal-close></div>
                        <div class="admin-modal-dialog admin-modal-dialog-preview">
                            <div class="admin-modal-header">
                                <div>
                                    <p class="admin-eyebrow">Preview</p>
                                    <h3 data-gallery-preview-title>Media preview</h3>
                                </div>
                                <button type="button" class="admin-modal-close" data-modal-close aria-label="Close preview modal">×</button>
                            </div>
                            <div class="gallery-preview-layout">
                                <div class="gallery-preview-stage" data-gallery-preview-stage></div>
                                <div class="gallery-preview-sidebar">
                                    <p class="gallery-preview-caption" data-gallery-preview-caption></p>
                                    <div class="gallery-preview-meta" data-gallery-preview-meta></div>
                                    <div class="gallery-preview-actions">
                                        <a href="#" class="action-link" data-gallery-preview-download download>Download</a>
                                        <a href="#" class="action-link" data-gallery-preview-edit>Edit</a>
                                        <button type="button" class="danger-button" data-gallery-preview-delete>Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-modal" id="gallery-delete-modal" hidden data-admin-modal>
                        <div class="admin-modal-backdrop" data-modal-close></div>
                        <div class="admin-modal-dialog admin-modal-dialog-small">
                            <div class="admin-modal-header">
                                <div>
                                    <p class="admin-eyebrow">Confirm</p>
                                    <h3>Delete media item</h3>
                                    <p data-gallery-delete-message>Are you sure you want to remove this media item?</p>
                                </div>
                                <button type="button" class="admin-modal-close" data-modal-close aria-label="Close delete modal">×</button>
                            </div>
                            <form method="post" class="admin-modal-actions">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                                <input type="hidden" name="action" value="delete_gallery">
                                <input type="hidden" name="id" value="0" data-gallery-delete-id>
                                <button type="button" class="button-secondary" data-modal-close>Cancel</button>
                                <button type="submit" class="danger-button">Delete media</button>
                            </form>
                        </div>
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
                    <div class="dashboard-editor-layout">
                        <div class="management-section editor-library">
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
                                            <div class="gallery-media-meta">
                                                <span class="inline-meta"><?php echo htmlspecialchars((string) $pageItem['slug']); ?></span>
                                                <span class="inline-meta"><?php echo htmlspecialchars(ucfirst((string) $pageItem['status'])); ?></span>
                                            </div>
                                            <?php if (trim((string) ($pageItem['excerpt'] ?? '')) !== ''): ?>
                                                <p class="table-paragraph"><?php echo htmlspecialchars((string) $pageItem['excerpt']); ?></p>
                                            <?php endif; ?>
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
                            <?php if ($pages === []): ?>
                                <div class="table-item empty-state-card">
                                    <strong>No website pages yet</strong>
                                    <span>Create Home, About, Contact, or Programs pages from the form on the right.</span>
                                </div>
                            <?php endif; ?>
                            </div>
                        </div>
                        <div class="management-section editor-form-card" id="pages-compose-panel">
                            <div class="management-section-header">
                                <div>
                                    <strong><?php echo $editType === 'page' ? 'Update page content' : 'Create page content'; ?></strong>
                                    <span><?php echo $editType === 'page' ? 'Refine the selected page content below.' : 'Add a new page once you finish reviewing the existing page list.'; ?></span>
                                </div>
                                <span class="management-tag"><?php echo $editType === 'page' ? 'Edit' : 'Create'; ?></span>
                            </div>
                            <form method="post" class="admin-form editor-admin-form" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                            <input type="hidden" name="action" value="<?php echo $editType === 'page' ? 'update_page' : 'add_page'; ?>">
                            <input type="hidden" name="id" value="<?php echo (int) $pageForm['id']; ?>">
                            <div class="editor-form-grid">
                                <label><span>Title</span><input type="text" name="title" value="<?php echo htmlspecialchars((string) $pageForm['title']); ?>"></label>
                                <label><span>Slug</span><input type="text" name="slug" value="<?php echo htmlspecialchars((string) $pageForm['slug']); ?>"></label>
                                <label class="editor-field-wide"><span>Excerpt</span><textarea name="excerpt" rows="3"><?php echo htmlspecialchars((string) $pageForm['excerpt']); ?></textarea></label>
                                <label class="editor-field-wide"><span>Main Content</span><textarea name="content" rows="7" data-editor><?php echo htmlspecialchars((string) $pageForm['content']); ?></textarea></label>
                                <label class="editor-field-wide"><span>Banner Image Path</span><input type="text" name="banner_image" value="<?php echo htmlspecialchars((string) $pageForm['banner_image']); ?>"></label>
                                <label class="editor-field-wide"><span>Upload Banner Image</span><input type="file" name="banner_image_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" class="upload-input"></label>
                                <label><span>Status</span>
                                    <select name="status">
                                        <option value="published"<?php echo ((string) $pageForm['status'] === 'published') ? ' selected' : ''; ?>>Published</option>
                                        <option value="draft"<?php echo ((string) $pageForm['status'] === 'draft') ? ' selected' : ''; ?>>Draft</option>
                                    </select>
                                </label>
                            </div>
                            <div class="editor-form-actions">
                                <button type="submit"><?php echo $editType === 'page' ? 'Update Page' : 'Add Page'; ?></button>
                            </div>
                            </form>
                        </div>
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
                    <div class="dashboard-editor-layout">
                    <div class="management-section editor-library">
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
                                        <div class="gallery-media-meta">
                                            <span class="inline-meta"><?php echo htmlspecialchars((string) $program['department']); ?></span>
                                            <span class="inline-meta"><?php echo htmlspecialchars(ucfirst((string) $program['status'])); ?></span>
                                            <?php if (trim((string) ($program['duration'] ?? '')) !== ''): ?>
                                                <span class="inline-meta"><?php echo htmlspecialchars((string) $program['duration']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (trim((string) ($program['short_description'] ?? '')) !== ''): ?>
                                            <p class="table-paragraph"><?php echo htmlspecialchars((string) $program['short_description']); ?></p>
                                        <?php endif; ?>
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
                    <div class="management-section editor-form-card" id="programs-compose-panel">
                        <div class="management-section-header">
                            <div>
                                <strong><?php echo $editType === 'program' ? 'Update program details' : 'Create new program'; ?></strong>
                                <span><?php echo $editType === 'program' ? 'Edit the selected program details below.' : 'Add a new program after reviewing what is already published.'; ?></span>
                            </div>
                            <span class="management-tag"><?php echo $editType === 'program' ? 'Edit' : 'Create'; ?></span>
                        </div>
                        <form method="post" class="admin-form editor-admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                        <input type="hidden" name="action" value="<?php echo $editType === 'program' ? 'update_program' : 'add_program'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $programForm['id']; ?>">
                        <div class="editor-form-grid">
                            <label><span>Title</span><input type="text" name="program_title" value="<?php echo htmlspecialchars((string) $programForm['title']); ?>"></label>
                            <label><span>Slug</span><input type="text" name="program_slug" value="<?php echo htmlspecialchars((string) $programForm['slug']); ?>"></label>
                            <label><span>Duration</span><input type="text" name="program_duration" value="<?php echo htmlspecialchars((string) $programForm['duration']); ?>"></label>
                            <label><span>Department</span><input type="text" name="program_department" value="<?php echo htmlspecialchars((string) $programForm['department']); ?>"></label>
                            <label class="editor-field-wide"><span>Summary</span><textarea name="program_summary" rows="4"><?php echo htmlspecialchars((string) $programForm['short_description']); ?></textarea></label>
                            <label class="editor-field-wide"><span>Description</span><textarea name="program_description" rows="5"><?php echo htmlspecialchars((string) $programForm['description']); ?></textarea></label>
                            <label class="editor-field-wide"><span>Cover Image Path</span><input type="text" name="cover_image" value="<?php echo htmlspecialchars((string) $programForm['cover_image']); ?>"></label>
                            <label class="editor-field-wide"><span>Upload Cover Image</span><input type="file" name="program_cover_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" class="upload-input"></label>
                            <label><span>Status</span>
                                <select name="program_status">
                                    <option value="active"<?php echo ((string) $programForm['status'] === 'active') ? ' selected' : ''; ?>>Active</option>
                                    <option value="inactive"<?php echo ((string) $programForm['status'] === 'inactive') ? ' selected' : ''; ?>>Inactive</option>
                                </select>
                            </label>
                        </div>
                        <div class="editor-form-actions">
                            <button type="submit"><?php echo $editType === 'program' ? 'Update Program' : 'Add Program'; ?></button>
                        </div>
                        </form>
                    </div>
                    </div>
                </article>

                <article class="panel dashboard-view-panel" id="staff-panel" data-dashboard-view>
                    <div class="panel-top">
                        <div>
                            <p class="admin-eyebrow">Staff</p>
                            <h2><?php echo $editType === 'staff' ? 'Edit Team Member' : 'Manage Team Members'; ?></h2>
                        </div>
                    </div>
                    <div class="dashboard-editor-layout">
                    <div class="management-section editor-library">
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
                                        <div class="gallery-media-meta">
                                            <span class="inline-meta"><?php echo htmlspecialchars((string) $member['job_title']); ?></span>
                                            <?php if ((int) ($member['is_featured'] ?? 0) === 1): ?>
                                                <span class="inline-meta">Featured</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (trim((string) ($member['bio'] ?? '')) !== ''): ?>
                                            <p class="table-paragraph"><?php echo htmlspecialchars((string) $member['bio']); ?></p>
                                        <?php endif; ?>
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
                    <div class="management-section editor-form-card" id="staff-compose-panel">
                        <div class="management-section-header">
                            <div>
                                <strong><?php echo $editType === 'staff' ? 'Update team member' : 'Add team member'; ?></strong>
                                <span><?php echo $editType === 'staff' ? 'Edit the selected profile details below.' : 'Create a new staff profile after reviewing the current team list.'; ?></span>
                            </div>
                            <span class="management-tag"><?php echo $editType === 'staff' ? 'Edit' : 'Create'; ?></span>
                        </div>
                        <form method="post" class="admin-form editor-admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(adminCsrfToken()); ?>">
                        <input type="hidden" name="action" value="<?php echo $editType === 'staff' ? 'update_staff' : 'add_staff'; ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $staffForm['id']; ?>">
                        <div class="editor-form-grid">
                            <label><span>Full Name</span><input type="text" name="full_name" value="<?php echo htmlspecialchars((string) $staffForm['full_name']); ?>"></label>
                            <label><span>Job Title</span><input type="text" name="job_title" value="<?php echo htmlspecialchars((string) $staffForm['job_title']); ?>"></label>
                            <label><span>Display Order</span><input type="number" name="display_order" value="<?php echo (int) $staffForm['display_order']; ?>"></label>
                            <label class="checkbox editor-checkbox"><input type="checkbox" name="is_featured"<?php echo ((int) $staffForm['is_featured'] === 1) ? ' checked' : ''; ?>> <span>Featured on website</span></label>
                            <label class="editor-field-wide"><span>Bio</span><textarea name="bio" rows="5"><?php echo htmlspecialchars((string) $staffForm['bio']); ?></textarea></label>
                            <label class="editor-field-wide"><span>Photo Path</span><input type="text" name="photo" value="<?php echo htmlspecialchars((string) $staffForm['photo']); ?>"></label>
                            <label class="editor-field-wide"><span>Upload Photo</span><input type="file" name="photo_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.jfif" class="upload-input"></label>
                        </div>
                        <div class="editor-form-actions">
                            <button type="submit"><?php echo $editType === 'staff' ? 'Update Staff' : 'Add Staff'; ?></button>
                        </div>
                        </form>
                    </div>
                    </div>
                </article>
            </section>
        </main>
    </div>
    <script id="dashboard-search-data" type="application/json"><?php echo json_encode($searchIndex, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?></script>
    <script src="/MUBUGA-TSS/assets/js/admin.js"></script>
    <script src="/MUBUGA-TSS/assets/js/photo-viewer.js"></script>
</body>
</html>

