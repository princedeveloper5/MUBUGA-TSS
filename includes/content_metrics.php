<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function ensureContentMetricTables(PDO $pdo): void
{
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

function incrementNewsViewCount(PDO $pdo, int $newsId): void
{
    if ($newsId <= 0) {
        return;
    }

    ensureContentMetricTables($pdo);

    $statement = $pdo->prepare('
        INSERT INTO news_admin_meta (news_id, view_count)
        VALUES (:news_id, 1)
        ON DUPLICATE KEY UPDATE view_count = view_count + 1
    ');
    $statement->execute(['news_id' => $newsId]);
}

function incrementGalleryViewCount(PDO $pdo, int $galleryId): void
{
    if ($galleryId <= 0) {
        return;
    }

    ensureContentMetricTables($pdo);

    $statement = $pdo->prepare('
        INSERT INTO gallery_admin_meta (gallery_id, view_count)
        VALUES (:gallery_id, 1)
        ON DUPLICATE KEY UPDATE view_count = view_count + 1
    ');
    $statement->execute(['gallery_id' => $galleryId]);
}

function incrementGalleryDownloadCount(PDO $pdo, int $galleryId): void
{
    if ($galleryId <= 0) {
        return;
    }

    ensureContentMetricTables($pdo);

    $statement = $pdo->prepare('
        INSERT INTO gallery_admin_meta (gallery_id, download_count)
        VALUES (:gallery_id, 1)
        ON DUPLICATE KEY UPDATE download_count = download_count + 1
    ');
    $statement->execute(['gallery_id' => $galleryId]);
}
