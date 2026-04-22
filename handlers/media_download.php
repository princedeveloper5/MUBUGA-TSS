<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/content_metrics.php';

$galleryId = (int) ($_GET['id'] ?? 0);
$pdo = getDatabaseConnection();

if (!$pdo instanceof PDO || $galleryId <= 0) {
    http_response_code(404);
    exit('Media not found.');
}

$statement = $pdo->prepare('SELECT image_path FROM gallery WHERE id = :id LIMIT 1');
$statement->execute(['id' => $galleryId]);
$mediaPath = trim((string) ($statement->fetchColumn() ?: ''));

if ($mediaPath === '') {
    http_response_code(404);
    exit('Media not found.');
}

incrementGalleryDownloadCount($pdo, $galleryId);

$normalizedPath = str_replace('\\', '/', $mediaPath);
if (preg_match('~^(?:https?:)?//~i', $normalizedPath) === 1) {
    header('Location: ' . $normalizedPath);
    exit;
}

header('Location: /MUBUGA-TSS/' . ltrim($normalizedPath, '/'));
exit;
