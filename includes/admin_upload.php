<?php

declare(strict_types=1);

function handleAdminImageUpload(string $fieldName, string $existingPath = ''): string
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

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];
    $originalName = (string) ($file['name'] ?? 'upload.jpg');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        return $existingPath;
    }

    $uploadDirectory = dirname(__DIR__) . '/assets/uploads';
    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }

    $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($originalName, PATHINFO_FILENAME));
    $safeBaseName = trim((string) $safeBaseName, '-');
    if ($safeBaseName === '') {
        $safeBaseName = 'image';
    }

    $fileName = $safeBaseName . '-' . date('YmdHis') . '.' . $extension;
    $destinationPath = $uploadDirectory . '/' . $fileName;

    if (!move_uploaded_file($tmpPath, $destinationPath)) {
        return $existingPath;
    }

    return 'assets/uploads/' . $fileName;
}
