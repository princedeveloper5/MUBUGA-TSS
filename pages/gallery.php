<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';

function galleryEmbedUrl(string $path): string
{
    $trimmed = trim($path);
    if (str_contains($trimmed, 'youtube.com/watch?v=')) {
        return str_replace('watch?v=', 'embed/', $trimmed);
    }
    if (str_contains($trimmed, 'youtu.be/')) {
        $videoId = basename(parse_url($trimmed, PHP_URL_PATH) ?: '');
        return 'https://www.youtube.com/embed/' . $videoId;
    }
    if (str_contains($trimmed, 'vimeo.com/')) {
        $videoId = basename(parse_url($trimmed, PHP_URL_PATH) ?: '');
        return 'https://player.vimeo.com/video/' . $videoId;
    }
    return $trimmed;
}

$gallery = array_map(static function (array $item): array {
    $media = parseGalleryCategory((string) ($item['category'] ?? ''), (string) ($item['image'] ?? ''));
    $item['media_type'] = $item['media_type'] ?? $media['media_type'];
    $item['category'] = $item['category'] ?? $media['category'];
    $item['category_label'] = $item['category_label'] ?? $media['category_label'];
    return $item;
}, $gallery);

$galleryLead = $gallery[0] ?? null;
$galleryHighlights = array_slice(array_values(array_filter($gallery, static fn (array $item): bool => ($item['media_type'] ?? 'image') === 'image')), 1, 4);
$galleryVideos = array_values(array_filter($gallery, static fn (array $item): bool => ($item['media_type'] ?? 'image') === 'video'));
$galleryCollection = array_values(array_filter($gallery, static fn (array $item): bool => ($item['media_type'] ?? 'image') === 'image'));

$page = sitePageContent('gallery', [
    'title' => 'Gallery',
    'excerpt' => 'Explore campus videos, workshops, and student activities.',
    'content' => 'School gallery',
    'image' => 'assets/images/school view 1.jpg',
]);
renderSiteHeader($page['title'], $schoolName, $contacts, 'gallery');
renderInnerHero('GALLERY', $page['content'], $page['excerpt'], $page['image']);
?>
<main>
    <section class="section gallery-section">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Media Stories</p>
                <h2>Watch videos and view school images.</h2>
                <p>Campus life, workshops, classrooms, and student activity in one place.</p>
            </div>

            <?php if ($galleryLead !== null): ?>
                <div class="gallery-showcase gallery-showcase-page">
                    <article class="gallery-feature-card gallery-card">
                        <?php if (($galleryLead['media_type'] ?? 'image') === 'video'): ?>
                            <div class="gallery-video-frame gallery-image-feature">
                                <?php if (isVideoMediaPath((string) $galleryLead['image']) && preg_match('/\.(mp4|webm|ogg)$/i', (string) $galleryLead['image']) === 1): ?>
                                    <video controls class="gallery-video">
                                        <source src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $galleryLead['image']); ?>">
                                    </video>
                                <?php else: ?>
                                    <iframe src="<?php echo htmlspecialchars(galleryEmbedUrl((string) $galleryLead['image'])); ?>" title="<?php echo htmlspecialchars($galleryLead['title']); ?>" loading="lazy" allowfullscreen></iframe>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $galleryLead['image']); ?>" alt="<?php echo htmlspecialchars($galleryLead['title']); ?>" class="gallery-image gallery-image-feature">
                        <?php endif; ?>
                        <div class="gallery-copy gallery-copy-feature">
                            <p class="gallery-kicker">Featured <?php echo htmlspecialchars(($galleryLead['media_type'] ?? 'image') === 'video' ? 'Video' : 'Photo'); ?></p>
                            <h3><?php echo htmlspecialchars($galleryLead['title']); ?></h3>
                            <p><?php echo htmlspecialchars($galleryLead['text']); ?></p>
                        </div>
                    </article>
                    <div class="gallery-side-grid">
                        <?php foreach ($galleryHighlights as $item): ?>
                            <article class="gallery-mini-card gallery-card">
                                <img src="/MUBUGA-TSS/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="gallery-image">
                                <div class="gallery-copy">
                                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($item['text']); ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($galleryVideos)): ?>
                <section class="gallery-video-section" id="videos">
                    <div class="gallery-video-section-top">
                        <p class="eyebrow">Videos</p>
                        <h3>Watch Mubuga TSS</h3>
                    </div>
                    <div class="gallery-video-grid">
                        <?php foreach ($galleryVideos as $item): ?>
                            <article class="gallery-card gallery-video-card">
                                <div class="gallery-video-frame">
                                    <?php if (preg_match('/\.(mp4|webm|ogg)$/i', (string) $item['image']) === 1): ?>
                                        <video controls class="gallery-video">
                                            <source src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $item['image']); ?>">
                                        </video>
                                    <?php else: ?>
                                        <iframe src="<?php echo htmlspecialchars(galleryEmbedUrl((string) $item['image'])); ?>" title="<?php echo htmlspecialchars($item['title']); ?>" loading="lazy" allowfullscreen></iframe>
                                    <?php endif; ?>
                                </div>
                                <div class="gallery-copy">
                                    <p class="gallery-kicker"><?php echo htmlspecialchars((string) ($item['category_label'] ?? 'Video')); ?></p>
                                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($item['text']); ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <div class="gallery-grid gallery-grid-page" id="pictures">
                <?php foreach ($galleryCollection as $index => $item): ?>
                    <?php
                    $cardClass = 'gallery-card gallery-wall-card';
                    if ($index === 0) {
                        $cardClass .= ' is-hero';
                    } elseif ($index % 5 === 1 || $index % 5 === 4) {
                        $cardClass .= ' is-tall';
                    }
                    ?>
                    <article class="<?php echo htmlspecialchars($cardClass); ?>">
                        <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="gallery-image">
                        <div class="gallery-copy">
                            <p class="gallery-kicker"><?php echo htmlspecialchars((string) ($item['category_label'] ?? 'Photo')); ?></p>
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p><?php echo htmlspecialchars($item['text']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>
