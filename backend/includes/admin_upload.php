<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function adminUploadSettings(): array
{
    static $settings = null;

    if (is_array($settings)) {
        return $settings;
    }

    $settings = [
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif', 'mp4', 'webm', 'ogg'],
        'max_file_size_bytes' => 50 * 1024 * 1024,
    ];

    $pdo = getDatabaseConnection();
    if (!$pdo instanceof PDO) {
        return $settings;
    }

    $statement = $pdo->query('SELECT setting_key, setting_value FROM settings WHERE setting_key IN ("allowed_file_types", "upload_size_limit_mb")');
    foreach ($statement->fetchAll() as $row) {
        $key = (string) ($row['setting_key'] ?? '');
        $value = trim((string) ($row['setting_value'] ?? ''));

        if ($key === 'allowed_file_types' && $value !== '') {
            $settings['allowed_extensions'] = array_values(array_unique(array_filter(array_map(
                static fn(string $item): string => strtolower(trim($item)),
                explode(',', $value)
            ))));
        }

        if ($key === 'upload_size_limit_mb') {
            $sizeMb = max(1, (int) $value);
            $settings['max_file_size_bytes'] = $sizeMb * 1024 * 1024;
        }
    }

    return $settings;
}

function adminUploadDirectoryForExtension(string $extension): string
{
    return in_array($extension, ['mp4', 'webm', 'ogg'], true)
        ? dirname(__DIR__) . '/assets/videos'
        : dirname(__DIR__) . '/assets/uploads';
}

function adminRelativeUploadPath(string $directory, string $fileName): string
{
    $normalizedDirectory = str_replace('\\', '/', $directory);
    $folder = str_ends_with($normalizedDirectory, '/assets/videos') ? 'videos' : 'uploads';
    return 'assets/' . $folder . '/' . $fileName;
}

function handleAdminMediaUpload(string $fieldName, string $existingPath = '', array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif']): string
{
    if (empty($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return $existingPath;
    }

    $file = $_FILES[$fieldName];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $existingPath;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return $existingPath;
    }

    $tmpPath = (string) ($file['tmp_name'] ?? '');
    if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
        return $existingPath;
    }

    $originalName = (string) ($file['name'] ?? 'upload.jpg');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $fileSize = (int) ($file['size'] ?? 0);
    $uploadSettings = adminUploadSettings();
    $configuredExtensions = $uploadSettings['allowed_extensions'] ?? [];
    $effectiveAllowedExtensions = $allowedExtensions === []
        ? $configuredExtensions
        : array_values(array_intersect($allowedExtensions, $configuredExtensions));

    if ($effectiveAllowedExtensions === []) {
        $effectiveAllowedExtensions = $allowedExtensions;
    }

    if (!in_array($extension, $effectiveAllowedExtensions, true)) {
        return $existingPath;
    }

    if ($fileSize <= 0 || $fileSize > (int) ($uploadSettings['max_file_size_bytes'] ?? 0)) {
        return $existingPath;
    }

    $uploadDirectory = adminUploadDirectoryForExtension($extension);
    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }

    $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($originalName, PATHINFO_FILENAME));
    $safeBaseName = trim((string) $safeBaseName, '-');
    if ($safeBaseName === '') {
        $safeBaseName = 'media';
    }

    $fileName = $safeBaseName . '-' . date('YmdHis') . '.' . $extension;
    $destinationPath = $uploadDirectory . '/' . $fileName;

    if (!move_uploaded_file($tmpPath, $destinationPath)) {
        return $existingPath;
    }

    return adminRelativeUploadPath($uploadDirectory, $fileName);
}

function handleAdminImageUpload(string $fieldName, string $existingPath = ''): string
{
    return handleAdminMediaUpload($fieldName, $existingPath);
}
