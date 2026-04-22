<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/content_metrics.php';

$pdo = getDatabaseConnection();
$action = trim((string) ($_POST['action'] ?? ''));
$id = (int) ($_POST['id'] ?? 0);

if (!$pdo instanceof PDO || $id <= 0) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false]);
    exit;
}

if ($action === 'media_view') {
    incrementGalleryViewCount($pdo, $id);
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
header('Content-Type: application/json');
echo json_encode(['ok' => false]);
