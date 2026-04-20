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
renderInnerHero('GALLERY', $page['content'], $page['excerpt'], $page['image']);
?>
<main>
    <section class="section gallery-section">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Photo Gallery</p>
                <h2>Campus life and student activities</h2>
            </div>

            <div class="gallery-grid-masonry">
                <?php foreach ($gallery as $index => $item): ?>
                    <?php
                    $itemClass = 'gallery-grid-item';
                    
                    // Create a pattern: items at index 3, 7, 11 span 2 rows
                    if (in_array($index, [3, 7, 11], true)) {
                        $itemClass .= ' gallery-grid-item-tall';
                    }
                    ?>
                    <article class="<?php echo htmlspecialchars($itemClass); ?>" data-gallery-item="<?php echo $index; ?>">
                        <div class="gallery-grid-wrapper">
                            <img src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="gallery-grid-image" data-gallery-src="/MUBUGA-TSS/<?php echo htmlspecialchars((string) $item['image']); ?>" data-gallery-title="<?php echo htmlspecialchars($item['title']); ?>">
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Lightbox Modal -->
            <div class="gallery-lightbox" id="galleryLightbox">
                <div class="gallery-lightbox-overlay"></div>
                <div class="gallery-lightbox-container">
                    <button class="gallery-lightbox-close" aria-label="Close gallery" type="button">&times;</button>
                    <button class="gallery-lightbox-prev" aria-label="Previous image" type="button">&#8249;</button>
                    <button class="gallery-lightbox-next" aria-label="Next image" type="button">&#8250;</button>
                    <div class="gallery-lightbox-content">
                        <div class="gallery-lightbox-stage" id="galleryLightboxStage">
                            <img src="" alt="" class="gallery-lightbox-image" id="galleryLightboxImage">
                        </div>
                        <div class="gallery-lightbox-info">
                            <span class="gallery-lightbox-counter"><span id="galleryLightboxCurrent">1</span> / <span id="galleryLightboxTotal"><?php echo count($gallery); ?></span></span>
                            <span class="gallery-lightbox-hint">Scroll to zoom, drag to move, double-click to reset.</span>
                            <div class="gallery-lightbox-zoom-controls">
                                <button class="gallery-lightbox-zoom-button" id="galleryZoomOut" aria-label="Zoom out" type="button">-</button>
                                <button class="gallery-lightbox-zoom-button" id="galleryZoomReset" aria-label="Reset zoom" type="button">100%</button>
                                <button class="gallery-lightbox-zoom-button" id="galleryZoomIn" aria-label="Zoom in" type="button">+</button>
                            </div>
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
    const lightboxImage = document.getElementById('galleryLightboxImage');
    const lightboxCurrent = document.getElementById('galleryLightboxCurrent');
    const galleryItems = document.querySelectorAll('[data-gallery-item]');
    const zoomInButton = document.getElementById('galleryZoomIn');
    const zoomOutButton = document.getElementById('galleryZoomOut');
    const zoomResetButton = document.getElementById('galleryZoomReset');
    let currentIndex = 0;
    const galleryData = [];
    const minZoom = 1;
    const maxZoom = 4;
    const zoomStep = 0.25;
    const clickZoom = 2.5;
    let scale = 1;
    let pointX = 0;
    let pointY = 0;
    let startX = 0;
    let startY = 0;
    let isDragging = false;
    let hasDragged = false;

    if (!lightbox || !lightboxStage || !lightboxImage || galleryItems.length === 0) {
        return;
    }

    function getBaseImageSize() {
        const stageWidth = lightboxStage.clientWidth;
        const stageHeight = lightboxStage.clientHeight;
        const naturalWidth = lightboxImage.naturalWidth || stageWidth;
        const naturalHeight = lightboxImage.naturalHeight || stageHeight;
        const ratio = Math.min(stageWidth / naturalWidth, stageHeight / naturalHeight);

        return {
            width: naturalWidth * ratio,
            height: naturalHeight * ratio
        };
    }

    function clampOffsets() {
        const stageWidth = lightboxStage.clientWidth;
        const stageHeight = lightboxStage.clientHeight;
        const baseSize = getBaseImageSize();
        const scaledWidth = baseSize.width * scale;
        const scaledHeight = baseSize.height * scale;
        const maxOffsetX = Math.max(0, (scaledWidth - baseSize.width) / 2);
        const maxOffsetY = Math.max(0, (scaledHeight - baseSize.height) / 2);

        pointX = Math.min(maxOffsetX, Math.max(-maxOffsetX, pointX));
        pointY = Math.min(maxOffsetY, Math.max(-maxOffsetY, pointY));

        if (scaledWidth <= stageWidth) {
            pointX = 0;
        }

        if (scaledHeight <= stageHeight) {
            pointY = 0;
        }
    }

    function updateZoomUi() {
        const zoomPercent = `${Math.round(scale * 100)}%`;
        lightbox.classList.toggle('is-zoomed', scale > minZoom);
        lightbox.classList.toggle('is-panning', isDragging);
        zoomResetButton.textContent = zoomPercent;
        zoomOutButton.disabled = scale <= minZoom;
        zoomInButton.disabled = scale >= maxZoom;
        lightboxStage.style.touchAction = scale > minZoom ? 'none' : 'auto';
        lightboxImage.style.transform = `translate(${pointX}px, ${pointY}px) scale(${scale})`;
    }

    function resetZoom() {
        scale = minZoom;
        pointX = 0;
        pointY = 0;
        isDragging = false;
        hasDragged = false;
        updateZoomUi();
    }

    function setZoom(nextScale, originX, originY) {
        const clampedScale = Math.min(maxZoom, Math.max(minZoom, nextScale));
        const stageRect = lightboxStage.getBoundingClientRect();
        const centerX = stageRect.width / 2;
        const centerY = stageRect.height / 2;

        if (clampedScale === scale) {
            return;
        }

        if (typeof originX === 'number' && typeof originY === 'number' && scale > 0) {
            pointX -= (originX - centerX) * ((clampedScale - scale) / scale);
            pointY -= (originY - centerY) * ((clampedScale - scale) / scale);
        }

        scale = clampedScale;
        clampOffsets();
        updateZoomUi();
    }

    // Gather gallery data
    galleryItems.forEach((item, index) => {
        const img = item.querySelector('.gallery-grid-image');
        galleryData.push({
            src: img.dataset.gallerySrc,
            title: img.dataset.galleryTitle
        });
    });

    // Open lightbox
    function openLightbox(index) {
        currentIndex = index;
        updateLightbox();
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    // Close lightbox
    function closeLightbox() {
        lightbox.classList.remove('is-open');
        document.body.style.overflow = '';
        resetZoom();
    }

    // Update lightbox content
    function updateLightbox() {
        const item = galleryData[currentIndex];
        lightboxImage.src = item.src;
        lightboxImage.alt = item.title;
        lightboxCurrent.textContent = currentIndex + 1;
        resetZoom();
    }

    // Navigate next
    function nextImage() {
        currentIndex = (currentIndex + 1) % galleryData.length;
        updateLightbox();
    }

    // Navigate prev
    function prevImage() {
        currentIndex = (currentIndex - 1 + galleryData.length) % galleryData.length;
        updateLightbox();
    }

    // Event listeners
    galleryItems.forEach((item, index) => {
        item.addEventListener('click', () => openLightbox(index));
        item.style.cursor = 'pointer';
    });

    document.querySelector('.gallery-lightbox-close').addEventListener('click', closeLightbox);
    document.querySelector('.gallery-lightbox-prev').addEventListener('click', prevImage);
    document.querySelector('.gallery-lightbox-next').addEventListener('click', nextImage);
    document.querySelector('.gallery-lightbox-overlay').addEventListener('click', closeLightbox);
    zoomInButton.addEventListener('click', () => setZoom(scale + zoomStep));
    zoomOutButton.addEventListener('click', () => setZoom(scale - zoomStep));
    zoomResetButton.addEventListener('click', resetZoom);

    lightboxImage.addEventListener('load', resetZoom);

    lightboxStage.addEventListener('wheel', (event) => {
        if (!lightbox.classList.contains('is-open')) {
            return;
        }

        event.preventDefault();
        const stageRect = lightboxStage.getBoundingClientRect();
        const pointerX = event.clientX - stageRect.left;
        const pointerY = event.clientY - stageRect.top;
        const direction = event.deltaY < 0 ? zoomStep : -zoomStep;

        setZoom(scale + direction, pointerX, pointerY);
    }, { passive: false });

    lightboxStage.addEventListener('pointerdown', (event) => {
        if (scale <= minZoom) {
            return;
        }

        event.preventDefault();
        isDragging = true;
        hasDragged = false;
        startX = event.clientX - pointX;
        startY = event.clientY - pointY;
        lightboxStage.setPointerCapture(event.pointerId);
        updateZoomUi();
    });

    lightboxStage.addEventListener('pointermove', (event) => {
        if (!isDragging) {
            return;
        }

        pointX = event.clientX - startX;
        pointY = event.clientY - startY;
        hasDragged = true;
        clampOffsets();
        updateZoomUi();
    });

    const stopDragging = (event) => {
        if (!isDragging) {
            return;
        }

        isDragging = false;
        if (event && lightboxStage.hasPointerCapture(event.pointerId)) {
            lightboxStage.releasePointerCapture(event.pointerId);
        }
        updateZoomUi();
    };

    lightboxStage.addEventListener('pointerup', stopDragging);
    lightboxStage.addEventListener('pointercancel', stopDragging);

    lightboxStage.addEventListener('click', (event) => {
        if (!lightbox.classList.contains('is-open') || hasDragged) {
            hasDragged = false;
            return;
        }

        const stageRect = lightboxStage.getBoundingClientRect();
        const pointerX = event.clientX - stageRect.left;
        const pointerY = event.clientY - stageRect.top;

        if (scale > minZoom) {
            resetZoom();
            return;
        }

        setZoom(clickZoom, pointerX, pointerY);
    });

    lightboxStage.addEventListener('dblclick', (event) => {
        const stageRect = lightboxStage.getBoundingClientRect();
        const pointerX = event.clientX - stageRect.left;
        const pointerY = event.clientY - stageRect.top;

        if (scale > minZoom) {
            resetZoom();
            return;
        }

        setZoom(2, pointerX, pointerY);
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('is-open')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') nextImage();
        if (e.key === 'ArrowLeft') prevImage();
        if (e.key === '+' || e.key === '=') setZoom(scale + zoomStep);
        if (e.key === '-') setZoom(scale - zoomStep);
        if (e.key === '0') resetZoom();
    });
})();
</script>

