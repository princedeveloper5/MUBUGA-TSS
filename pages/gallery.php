<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/site_data.php';
require_once __DIR__ . '/../includes/site_layout.php';

$page = sitePageContent('gallery', [
    'title' => 'Gallery',
    'excerpt' => 'Explore campus, workshops, and student activities.',
    'content' => 'School gallery',
    'image' => 'assets/images/school view 1.jpg',
]);
renderSiteHeader($page['title'], $schoolName, $contacts, 'gallery', [
    'description' => $page['excerpt'],
    'image' => $page['image'],
]);
?>
<style>
    .kha-gallery-page .container {
        width: min(1280px, calc(100% - 40px));
    }

    .kha-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 18px;
        width: 100%;
    }

    .kha-gallery-card,
    .kha-video-card,
    .kha-video-trigger {
        display: block;
        width: 100%;
    }

    .kha-video-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 18px;
        width: 100%;
    }

    .gallery-lightbox {
        z-index: 10000;
    }

    .gallery-lightbox.is-open,
    .gallery-lightbox.active {
        display: flex !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
</style>
<?php
renderInnerHero('GALLERY', $page['content'], $page['excerpt'], $page['image']);

$galleryMedia = array_values(array_map(
    static function (array $item, int $index): array {
        $src = (string) ($item['image'] ?? '');
        $resolvedSrc = preg_match('~^(?:https?:)?/~', $src) ? $src : '/MUBUGA-TSS/' . ltrim($src, '/');

        return [
            'index' => $index,
            'title' => (string) ($item['title'] ?? 'Gallery item'),
            'text' => (string) ($item['text'] ?? 'A moment from Mubuga TSS.'),
            'src' => $resolvedSrc,
            'category_label' => (string) ($item['category_label'] ?? 'Campus'),
            'media_type' => (string) ($item['media_type'] ?? 'image'),
        ];
    },
    $gallery,
    array_keys($gallery)
));

$photoItems = array_values(array_filter($galleryMedia, static function (array $item): bool {
    return ($item['media_type'] ?? 'image') === 'image';
}));

$videoItems = array_values(array_filter($galleryMedia, static function (array $item): bool {
    return ($item['media_type'] ?? 'image') === 'video';
}));
?>
<main>
    <section class="section kha-gallery-page" id="pictures">
        <div class="container">
            <div class="section-heading kha-gallery-heading">
                <p class="eyebrow">Photo Gallery</p>
                <h2>School moments from classrooms, workshops, and campus life</h2>
                <p class="section-intro">A simple picture gallery inspired by the reference page, with clean image cards and a full-screen viewer when you click.</p>
            </div>

            <?php if ($photoItems !== []): ?>
                <div class="kha-gallery-grid" data-photo-gallery>
                    <?php foreach ($photoItems as $item): ?>
                        <button
                            class="kha-gallery-card"
                            type="button"
                            data-gallery-item
                            data-gallery-src="<?php echo htmlspecialchars((string) $item['src']); ?>"
                            data-gallery-title="<?php echo htmlspecialchars((string) $item['title']); ?>"
                            data-gallery-text="<?php echo htmlspecialchars((string) $item['text']); ?>"
                            data-gallery-type="image"
                        >
                            <img src="<?php echo htmlspecialchars((string) $item['src']); ?>" alt="<?php echo htmlspecialchars((string) $item['title']); ?>" class="kha-gallery-image">
                            <span class="kha-gallery-overlay" aria-hidden="true">
                                <span class="kha-gallery-zoom-icon">&#128269;</span>
                            </span>
                            <span class="kha-gallery-badge"><?php echo htmlspecialchars((string) $item['category_label']); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="kha-gallery-empty">
                    <h3>No gallery photos yet</h3>
                    <p>Add images from the admin dashboard and they will appear here in the new gallery layout.</p>
                </div>
            <?php endif; ?>

            <?php if ($videoItems !== []): ?>
                <section class="kha-video-section" id="videos">
                    <div class="section-heading kha-gallery-heading kha-video-heading">
                        <p class="eyebrow">Videos</p>
                        <h2>School video highlights</h2>
                    </div>
                    <div class="kha-video-grid">
                        <?php foreach ($videoItems as $item): ?>
                            <article class="kha-video-card">
                                <button
                                    class="kha-video-trigger"
                                    type="button"
                                    data-gallery-item
                                    data-gallery-src="<?php echo htmlspecialchars((string) $item['src']); ?>"
                                    data-gallery-title="<?php echo htmlspecialchars((string) $item['title']); ?>"
                                    data-gallery-text="<?php echo htmlspecialchars((string) $item['text']); ?>"
                                    data-gallery-type="video"
                                >
                                    <video class="kha-video-preview" muted playsinline preload="metadata" src="<?php echo htmlspecialchars((string) $item['src']); ?>"></video>
                                    <span class="kha-video-play">&#9658;</span>
                                </button>
                                <div class="kha-video-copy">
                                    <strong><?php echo htmlspecialchars((string) $item['title']); ?></strong>
                                    <span><?php echo htmlspecialchars((string) $item['category_label']); ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <div class="gallery-lightbox" id="galleryLightbox">
                <div class="gallery-lightbox-overlay"></div>
                <div class="gallery-lightbox-container">
                    <button class="gallery-lightbox-close" aria-label="Close gallery" type="button">&times;</button>
                    <button class="gallery-lightbox-prev" aria-label="Previous image" type="button">&#8249;</button>
                    <button class="gallery-lightbox-next" aria-label="Next image" type="button">&#8250;</button>
                    <div class="gallery-lightbox-content">
                        <div class="gallery-lightbox-stage" id="galleryLightboxStage"></div>
                        <div class="gallery-lightbox-info">
                            <div class="gallery-lightbox-copy">
                                <strong id="galleryLightboxTitle">Gallery item</strong>
                                <span id="galleryLightboxText">Browse school moments in a larger view.</span>
                            </div>
                            <span class="gallery-lightbox-counter"><span id="galleryLightboxCurrent">1</span> / <span id="galleryLightboxTotal"><?php echo count($galleryMedia); ?></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php renderSiteFooter($schoolName); ?>

<script>
(function() {
    const lightbox = document.getElementById('galleryLightbox');
    const lightboxStage = document.getElementById('galleryLightboxStage');
    const lightboxCurrent = document.getElementById('galleryLightboxCurrent');
    const lightboxTitle = document.getElementById('galleryLightboxTitle');
    const lightboxText = document.getElementById('galleryLightboxText');
    const galleryItems = Array.from(document.querySelectorAll('[data-gallery-item]'));
    const prevButton = document.querySelector('.gallery-lightbox-prev');
    const nextButton = document.querySelector('.gallery-lightbox-next');
    const closeButton = document.querySelector('.gallery-lightbox-close');
    const overlay = document.querySelector('.gallery-lightbox-overlay');
    const galleryData = galleryItems.map((item) => ({
        src: item.dataset.gallerySrc || '',
        title: item.dataset.galleryTitle || 'Gallery item',
        text: item.dataset.galleryText || 'A moment from Mubuga TSS.',
        type: item.dataset.galleryType || 'image'
    }));
    let currentIndex = 0;

    if (!lightbox || !lightboxStage || galleryData.length === 0) {
        return;
    }

    function renderItem(index) {
        const item = galleryData[index];
        currentIndex = index;
        lightboxTitle.textContent = item.title;
        lightboxText.textContent = item.text;
        lightboxCurrent.textContent = String(index + 1);

        if (item.type === 'video') {
            lightboxStage.innerHTML = `<video class="gallery-lightbox-media" controls autoplay playsinline src="${item.src}"></video>`;
        } else {
            lightboxStage.innerHTML = `<img src="${item.src}" alt="${item.title}" class="gallery-lightbox-media gallery-lightbox-image">`;
        }
    }

    function openLightbox(index) {
        renderItem(index);
        lightbox.classList.add('is-open');
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.remove('is-open');
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }

    function showNext() {
        renderItem((currentIndex + 1) % galleryData.length);
    }

    function showPrev() {
        renderItem((currentIndex - 1 + galleryData.length) % galleryData.length);
    }

    galleryItems.forEach((item, index) => {
        item.addEventListener('click', () => openLightbox(index));
    });

    if (closeButton) {
        closeButton.addEventListener('click', closeLightbox);
    }
    if (overlay) {
        overlay.addEventListener('click', closeLightbox);
    }
    if (nextButton) {
        nextButton.addEventListener('click', showNext);
    }
    if (prevButton) {
        prevButton.addEventListener('click', showPrev);
    }

    document.addEventListener('keydown', (event) => {
        if (!lightbox.classList.contains('is-open')) {
            return;
        }
        if (event.key === 'Escape') {
            closeLightbox();
        }
        if (event.key === 'ArrowRight') {
            showNext();
        }
        if (event.key === 'ArrowLeft') {
            showPrev();
        }
    });
})();
</script>
