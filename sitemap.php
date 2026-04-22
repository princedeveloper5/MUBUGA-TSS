<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/site_data.php';

header('Content-Type: application/xml; charset=UTF-8');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
$baseUrl = $scheme . '://' . $host;

$urls = [
    ['/MUBUGA-TSS/', 'weekly', '1.0'],
    ['/MUBUGA-TSS/pages/about.php', 'monthly', '0.8'],
    ['/MUBUGA-TSS/pages/programs.php', 'weekly', '0.9'],
    ['/MUBUGA-TSS/pages/facilities.php', 'monthly', '0.7'],
    ['/MUBUGA-TSS/pages/admissions.php', 'weekly', '0.9'],
    ['/MUBUGA-TSS/pages/fees.php', 'weekly', '0.7'],
    ['/MUBUGA-TSS/pages/registration.php', 'weekly', '0.9'],
    ['/MUBUGA-TSS/pages/team.php', 'monthly', '0.6'],
    ['/MUBUGA-TSS/pages/news.php', 'daily', '0.9'],
    ['/MUBUGA-TSS/pages/events.php', 'weekly', '0.8'],
    ['/MUBUGA-TSS/pages/announcements.php', 'weekly', '0.8'],
    ['/MUBUGA-TSS/pages/gallery.php', 'weekly', '0.8'],
    ['/MUBUGA-TSS/pages/contact.php', 'monthly', '0.7'],
];

$newsUrls = [];
foreach ($news as $item) {
    $slug = trim((string) ($item['slug'] ?? ''));
    if ($slug === '') {
        continue;
    }

    $newsUrls[] = ['/MUBUGA-TSS/pages/news.php?slug=' . rawurlencode($slug), 'weekly', '0.7'];
}

$allUrls = array_merge($urls, $newsUrls);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";

foreach ($allUrls as [$path, $changeFrequency, $priority]) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($baseUrl . $path, ENT_QUOTES, 'UTF-8') . "</loc>\n";
    echo '    <changefreq>' . htmlspecialchars((string) $changeFrequency, ENT_QUOTES, 'UTF-8') . "</changefreq>\n";
    echo '    <priority>' . htmlspecialchars((string) $priority, ENT_QUOTES, 'UTF-8') . "</priority>\n";

    // Add video entries for gallery page
    if (str_contains($path, 'gallery.php')) {
        foreach ($gallery as $item) {
            if (($item['media_type'] ?? 'image') === 'video') {
                $videoUrl = $baseUrl . '/MUBUGA-TSS/' . ltrim((string) ($item['image_path'] ?? ''), '/');
                $thumbnailUrl = $baseUrl . '/MUBUGA-TSS/assets/images/video-thumbnail.jpg';
                echo "    <video:video>\n";
                echo '      <video:content_loc>' . htmlspecialchars($videoUrl, ENT_QUOTES, 'UTF-8') . "</video:content_loc>\n";
                echo '      <video:title>' . htmlspecialchars((string) ($item['title'] ?? 'School Video'), ENT_QUOTES, 'UTF-8') . "</video:title>\n";
                echo '      <video:description>' . htmlspecialchars((string) ($item['caption'] ?? 'Mubuga TSS video content'), ENT_QUOTES, 'UTF-8') . "</video:description>\n";
                echo '      <video:thumbnail_loc>' . htmlspecialchars($thumbnailUrl, ENT_QUOTES, 'UTF-8') . "</video:thumbnail_loc>\n";
                echo "    </video:video>\n";
            }
        }
    }

    echo "  </url>\n";
}

echo "</urlset>\n";
