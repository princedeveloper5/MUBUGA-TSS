<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/site_data.php';
require_once __DIR__ . '/../../shared/site_layout.php';

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

    .school-gallery-shell {
        display: grid;
        gap: 30px;
    }

    .school-gallery-intro {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(300px, 0.8fr);
        gap: 24px;
        align-items: stretch;
    }

    .school-gallery-hero-card,
    .school-gallery-summary-card {
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgba(13, 53, 77, 0.08);
        border-radius: 24px;
        box-shadow: 0 22px 54px rgba(10, 29, 44, 0.08);
    }

    .school-gallery-hero-card {
        padding: 30px;
    }

    .school-gallery-summary-card {
        padding: 24px;
        display: grid;
        gap: 18px;
        align-content: start;
    }

    .school-gallery-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: var(--gold-deep);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .school-gallery-lead {
        margin: 0;
        color: var(--muted);
        line-height: 1.75;
    }

    .school-gallery-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 24px;
    }

    .school-gallery-metric {
        padding: 16px 18px;
        border-radius: 18px;
        background: linear-gradient(180deg, rgba(240, 247, 255, 0.98), rgba(255, 255, 255, 0.98));
        border: 1px solid rgba(61, 142, 232, 0.14);
    }

    .school-gallery-metric strong {
        display: block;
        margin-bottom: 4px;
        color: var(--green);
        font-size: 1.4rem;
    }

    .school-gallery-metric span {
        color: var(--muted);
        font-size: 0.92rem;
    }

    .school-gallery-summary-card h3 {
        margin-bottom: 0;
        color: var(--green);
        font-size: 1.25rem;
    }

    .school-gallery-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .school-gallery-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.55rem 0.9rem;
        border-radius: 999px;
        background: rgba(222, 239, 255, 0.8);
        border: 1px solid rgba(61, 142, 232, 0.14);
        color: #19466c;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .school-gallery-chip strong {
        margin-left: 6px;
        font-size: 0.86rem;
    }

    .school-gallery-filter-bar {
        display: grid;
        gap: 12px;
        justify-content: center;
        margin: -4px auto 0;
        padding: 10px;
        width: min(320px, 100%);
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(13, 53, 77, 0.08);
        box-shadow: 0 16px 38px rgba(10, 29, 44, 0.07);
    }

    .school-gallery-filter {
        display: block;
        border: 1px solid transparent;
        background: transparent;
        color: #19466c;
        width: 100%;
        border-radius: 16px;
        padding: 0.82rem 1.2rem;
        font: inherit;
        font-weight: 700;
        text-decoration: none;
        text-align: center;
        cursor: pointer;
        transition: background 180ms ease, color 180ms ease, transform 180ms ease, box-shadow 180ms ease;
    }

    .school-gallery-filter:hover,
    .school-gallery-filter.is-active {
        background: linear-gradient(135deg, var(--gold) 0%, var(--clay) 100%);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 12px 26px rgba(33, 99, 166, 0.22);
    }

    .school-gallery-panel.is-hidden {
        display: none;
    }

    .kha-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(255px, 1fr));
        gap: 20px;
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

    .kha-gallery-card {
        position: relative;
        overflow: hidden;
        border: 0;
        padding: 0;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 18px 42px rgba(10, 29, 44, 0.08);
        transition: transform 220ms ease, box-shadow 220ms ease;
        text-align: left;
    }

    .kha-gallery-card::before {
        content: '';
        display: block;
        aspect-ratio: 1 / 0.82;
    }

    .kha-gallery-media-frame {
        position: absolute;
        inset: 0;
    }

    .kha-gallery-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 22px 48px rgba(10, 29, 44, 0.14);
    }

    .kha-gallery-card-body {
        position: relative;
        z-index: 2;
        display: grid;
        gap: 8px;
        padding: 18px 18px 20px;
        background: rgba(255, 255, 255, 0.98);
    }

    .kha-gallery-card-title {
        margin: 0;
        color: var(--green);
        font-size: 1.05rem;
        line-height: 1.35;
    }

    .kha-gallery-card-text {
        margin: 0;
        color: var(--muted);
        font-size: 0.94rem;
        line-height: 1.6;
    }

    .kha-gallery-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 260ms ease;
    }

    .kha-gallery-card:hover .kha-gallery-image {
        transform: scale(1.12);
    }

    .kha-gallery-badge {
        position: absolute;
        left: 16px;
        top: 16px;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.92);
        color: var(--green);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .kha-gallery-overlay {
        position: absolute;
        inset: 0;
        z-index: 1;
        display: grid;
        place-items: center;
        background: linear-gradient(180deg, rgba(7, 24, 41, 0.08), rgba(7, 24, 41, 0.38));
        opacity: 0;
        transition: opacity 220ms ease;
    }

    .kha-gallery-card:hover .kha-gallery-overlay {
        opacity: 1;
    }

    .kha-gallery-zoom-icon {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.96);
        color: #103452;
        font-size: 1.2rem;
        box-shadow: 0 16px 30px rgba(0, 0, 0, 0.18);
        transform: scale(0.9);
        transition: transform 220ms ease;
    }

    .kha-gallery-card:hover .kha-gallery-zoom-icon {
        transform: scale(1);
    }

    .kha-video-section {
        margin-top: 4px;
    }

    .kha-video-card {
        overflow: hidden;
        border-radius: 22px;
        background: #fff;
        border: 1px solid rgba(13, 53, 77, 0.08);
        box-shadow: 0 16px 38px rgba(10, 29, 44, 0.08);
    }

    .kha-video-copy {
        display: grid;
        gap: 6px;
        padding: 16px 18px 18px;
    }

    .kha-video-copy strong {
        color: var(--green);
    }

    .kha-video-copy span {
        color: var(--muted);
        font-size: 0.92rem;
    }

    .gallery-lightbox {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        padding: 24px;
        z-index: 12000 !important;
        align-items: center;
        justify-content: center;
    }

    .gallery-lightbox.is-open,
    .gallery-lightbox.active {
        display: flex !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .gallery-lightbox-overlay {
        position: absolute;
        inset: 0;
        background: rgba(6, 12, 22, 0.92);
        backdrop-filter: blur(4px);
    }

    .gallery-lightbox-container {
        position: relative;
        z-index: 2;
        width: min(1180px, calc(100vw - 48px));
        height: min(86vh, 900px);
        max-height: 86vh;
        display: grid;
        align-items: center;
    }

    .gallery-lightbox-content {
        height: 100%;
        display: grid;
        grid-template-rows: minmax(0, 1fr) auto;
        gap: 14px;
    }

    .gallery-lightbox-stage {
        width: 100%;
        height: 100%;
        display: grid;
        place-items: center;
        border-radius: 24px;
        background: rgba(10, 17, 27, 0.98);
        overflow: hidden;
        box-shadow: 0 30px 70px rgba(0, 0, 0, 0.35);
    }

    .gallery-lightbox-media {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        display: block;
        margin: 0 auto;
    }

    .gallery-lightbox-stage video.gallery-lightbox-media {
        width: min(100%, 980px);
        max-height: 100%;
        background: #000;
    }

    .gallery-lightbox-close,
    .gallery-lightbox-prev,
    .gallery-lightbox-next {
        z-index: 3;
    }

    .gallery-lightbox-info {
        justify-self: center;
        width: min(760px, 100%);
        padding: 14px 18px;
        border-radius: 18px;
        background: rgba(9, 18, 30, 0.88);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #fff;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.22);
    }

    @media (max-width: 960px) {
        .school-gallery-intro {
            grid-template-columns: 1fr;
        }

        .school-gallery-metrics {
            grid-template-columns: 1fr;
        }

        .school-gallery-filter-bar {
            width: 100%;
        }

        .gallery-lightbox {
            padding: 14px;
        }

        .gallery-lightbox-container {
            width: calc(100vw - 28px);
            height: min(82vh, 760px);
        }
    }
</style>
<?php
renderInnerHero('GALLERY', $page['content'], $page['excerpt'], $page['image']);

$galleryMedia = array_values(array_map(
    static function (array $item, int $index): array {
        $src = (string) ($item['image'] ?? '');
        $resolvedSrc = preg_match('~^(?:https?:)?/~', $src) ? $src : '/MUBUGA-TSS/' . ltrim($src, '/');

        return [
            'id' => (int) ($item['id'] ?? 0),
            'index' => $index,
            'title' => (string) ($item['title'] ?? 'Gallery item'),
            'text' => (string) ($item['text'] ?? 'A moment from Mubuga TSS.'),
            'src' => $resolvedSrc,
            'category_label' => (string) ($item['category_label'] ?? 'Campus'),
            'media_type' => (string) ($item['media_type'] ?? 'image'),
            'view_count' => (int) ($item['view_count'] ?? 0),
            'download_count' => (int) ($item['download_count'] ?? 0),
            'download_link' => '/MUBUGA-TSS/backend/handlers/media_download.php?id=' . (int) ($item['id'] ?? 0),
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

$categoryCounts = [];
foreach ($photoItems as $item) {
    $label = (string) ($item['category_label'] ?? 'Campus');
    $categoryCounts[$label] = ($categoryCounts[$label] ?? 0) + 1;
}
?>
<main>
    <section class="section kha-gallery-page" id="pictures">
        <div class="container">
            <div class="school-gallery-shell">
                <div class="school-gallery-intro">
                    <div class="school-gallery-hero-card">
                        <span class="school-gallery-kicker">School Gallery Archive</span>
                        <h2>Highlights from classrooms, workshops, and campus life</h2>
                        <p class="school-gallery-lead">A quick look at learning spaces, student activity, and practical training at Mubuga TSS.</p>
                        <div class="school-gallery-metrics">
                            <div class="school-gallery-metric">
                                <strong><?php echo count($photoItems); ?></strong>
                                <span>Photo highlights</span>
                            </div>
                            <div class="school-gallery-metric">
                                <strong><?php echo count($videoItems); ?></strong>
                                <span>Video stories</span>
                            </div>
                            <div class="school-gallery-metric">
                                <strong><?php echo count($categoryCounts); ?></strong>
                                <span>School themes</span>
                            </div>
                        </div>
                    </div>

                    <aside class="school-gallery-summary-card">
                        <h3>Gallery Topics</h3>
                        <p class="school-gallery-lead">Browse the main gallery topics below.</p>
                        <div class="school-gallery-chip-list">
                            <?php foreach ($categoryCounts as $label => $count): ?>
                                <span class="school-gallery-chip"><?php echo htmlspecialchars($label); ?><strong><?php echo (int) $count; ?></strong></span>
                            <?php endforeach; ?>
                            <?php if ($categoryCounts === []): ?>
                                <span class="school-gallery-chip">Campus<strong>0</strong></span>
                            <?php endif; ?>
                        </div>
                    </aside>
                </div>

                <div class="section-heading kha-gallery-heading">
                    <p class="eyebrow">Photo Gallery</p>
                    <h2>Featured school moments</h2>
                    <p class="section-intro">Click any image to view it clearly.</p>
                </div>

                <div class="school-gallery-filter-bar" aria-label="Gallery filters">
                    <a href="/MUBUGA-TSS/pages/gallery.php#pictures" class="school-gallery-filter" data-gallery-filter="images">Images</a>
                    <a href="/MUBUGA-TSS/pages/gallery.php#videos" class="school-gallery-filter" data-gallery-filter="videos">Videos</a>
                </div>

                <?php if ($photoItems !== []): ?>
                    <div class="school-gallery-panel" data-gallery-panel="images">
                    <div class="kha-gallery-grid" data-photo-gallery>
                        <?php foreach ($photoItems as $item): ?>
                            <button
                                class="kha-gallery-card"
                                type="button"
                                data-gallery-item
                                data-gallery-id="<?php echo (int) ($item['id'] ?? 0); ?>"
                                data-gallery-src="<?php echo htmlspecialchars((string) $item['src']); ?>"
                                data-gallery-title="<?php echo htmlspecialchars((string) $item['title']); ?>"
                                data-gallery-text="<?php echo htmlspecialchars((string) $item['text']); ?>"
                                data-gallery-type="image"
                                data-gallery-download="<?php echo htmlspecialchars((string) ($item['download_link'] ?? '#')); ?>"
                            >
                                <div class="kha-gallery-media-frame">
                                    <img src="<?php echo htmlspecialchars((string) $item['src']); ?>" alt="<?php echo htmlspecialchars((string) $item['title']); ?>" class="kha-gallery-image">
                                    <span class="kha-gallery-overlay" aria-hidden="true">
                                        <span class="kha-gallery-zoom-icon">&#128269;</span>
                                    </span>
                                    <span class="kha-gallery-badge"><?php echo htmlspecialchars((string) $item['category_label']); ?></span>
                                </div>
                                <div class="kha-gallery-card-body">
                                    <h3 class="kha-gallery-card-title"><?php echo htmlspecialchars((string) $item['title']); ?></h3>
                                    <p class="kha-gallery-card-text"><?php echo htmlspecialchars((string) $item['text']); ?></p>
                                    <p class="kha-gallery-card-text">Views: <?php echo (int) ($item['view_count'] ?? 0); ?> | Downloads: <?php echo (int) ($item['download_count'] ?? 0); ?></p>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    </div>
                <?php else: ?>
                    <div class="kha-gallery-empty school-gallery-panel" data-gallery-panel="images">
                        <h3>No gallery photos yet</h3>
                        <p>Add images from the admin dashboard and they will appear here in the new gallery layout.</p>
                    </div>
                <?php endif; ?>

                <?php if ($videoItems !== []): ?>
                    <section class="kha-video-section school-gallery-panel" id="videos" data-gallery-panel="videos">
                        <div class="section-heading kha-gallery-heading kha-video-heading">
                            <p class="eyebrow">Videos</p>
                            <h2>School video highlights</h2>
                            <p class="section-intro">School videos and moving highlights.</p>
                        </div>
                        <div class="kha-video-grid">
                            <?php foreach ($videoItems as $item): ?>
                                <article class="kha-video-card">
                                    <button
                                        class="kha-video-trigger"
                                        type="button"
                                        data-gallery-item
                                        data-gallery-id="<?php echo (int) ($item['id'] ?? 0); ?>"
                                        data-gallery-src="<?php echo htmlspecialchars((string) $item['src']); ?>"
                                        data-gallery-title="<?php echo htmlspecialchars((string) $item['title']); ?>"
                                        data-gallery-text="<?php echo htmlspecialchars((string) $item['text']); ?>"
                                        data-gallery-type="video"
                                        data-gallery-download="<?php echo htmlspecialchars((string) ($item['download_link'] ?? '#')); ?>"
                                    >
                                        <video class="kha-video-preview" muted playsinline preload="metadata" src="<?php echo htmlspecialchars((string) $item['src']); ?>"></video>
                                        <span class="kha-video-play">&#9658;</span>
                                    </button>
                                    <div class="kha-video-copy">
                                        <strong><?php echo htmlspecialchars((string) $item['title']); ?></strong>
                                        <span><?php echo htmlspecialchars((string) $item['category_label']); ?> | Views: <?php echo (int) ($item['view_count'] ?? 0); ?> | Downloads: <?php echo (int) ($item['download_count'] ?? 0); ?></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php else: ?>
                    <div class="kha-gallery-empty school-gallery-panel is-hidden" data-gallery-panel="videos">
                        <h3>No gallery videos yet</h3>
                        <p>Add videos from the admin dashboard and they will appear in the video section here.</p>
                    </div>
                <?php endif; ?>
            </div>

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
                                <span id="galleryLightboxText">View school moments clearly.</span>
                            </div>
                            <a href="#" class="inline-link" id="galleryLightboxDownload" download>Download media</a>
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
    const lightboxDownload = document.getElementById('galleryLightboxDownload');
    const filterButtons = Array.from(document.querySelectorAll('[data-gallery-filter]'));
    const panels = Array.from(document.querySelectorAll('[data-gallery-panel]'));
    const galleryItems = Array.from(document.querySelectorAll('[data-gallery-item]'));
    const prevButton = document.querySelector('.gallery-lightbox-prev');
    const nextButton = document.querySelector('.gallery-lightbox-next');
    const closeButton = document.querySelector('.gallery-lightbox-close');
    const overlay = document.querySelector('.gallery-lightbox-overlay');
    const galleryData = galleryItems.map((item) => ({
        id: Number(item.dataset.galleryId || '0'),
        src: item.dataset.gallerySrc || '',
        title: item.dataset.galleryTitle || 'Gallery item',
        text: item.dataset.galleryText || 'A moment from Mubuga TSS.',
        type: item.dataset.galleryType || 'image',
        downloadLink: item.dataset.galleryDownload || '#'
    }));
    let currentIndex = 0;
    const viewedMedia = new Set();

    if (lightbox && lightbox.parentElement !== document.body) {
        document.body.appendChild(lightbox);
    }

    function setGalleryFilter(filter) {
        panels.forEach((panel) => {
            const panelType = panel.getAttribute('data-gallery-panel');
            const shouldShow = panelType === filter;
            panel.classList.toggle('is-hidden', !shouldShow);
        });

        filterButtons.forEach((button) => {
            button.classList.toggle('is-active', button.getAttribute('data-gallery-filter') === filter);
        });
    }

    function syncFilterFromHash() {
        const hash = String(window.location.hash || '').toLowerCase();
        const filter = hash === '#videos' ? 'videos' : 'images';
        setGalleryFilter(filter);
    }

    window.addEventListener('hashchange', syncFilterFromHash);
    syncFilterFromHash();

    if (!lightbox || !lightboxStage || galleryData.length === 0) {
        return;
    }

    function renderItem(index) {
        const item = galleryData[index];
        currentIndex = index;
        lightboxTitle.textContent = item.title;
        lightboxText.textContent = item.text;
        lightboxCurrent.textContent = String(index + 1);
        if (lightboxDownload) {
            lightboxDownload.href = item.downloadLink || item.src;
        }

        if (item.type === 'video') {
            lightboxStage.innerHTML = `<video class="gallery-lightbox-media" controls autoplay playsinline src="${item.src}"></video>`;
        } else {
            lightboxStage.innerHTML = `<img src="${item.src}" alt="${item.title}" class="gallery-lightbox-media gallery-lightbox-image">`;
        }

        if (item.id > 0 && !viewedMedia.has(item.id)) {
            viewedMedia.add(item.id);
            const payload = new URLSearchParams();
            payload.set('action', 'media_view');
            payload.set('id', String(item.id));

            fetch('/MUBUGA-TSS/backend/handlers/content_analytics.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: payload.toString(),
                keepalive: true
            }).catch(() => {});
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
