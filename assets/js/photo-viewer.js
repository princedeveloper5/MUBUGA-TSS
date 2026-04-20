(function() {
    'use strict';

    // Check if elements exist, if not create them
    let lightbox = document.getElementById('galleryLightbox');
    let lightboxStage = document.getElementById('galleryLightboxStage');
    let lightboxCurrent = document.getElementById('galleryLightboxCurrent');

    // Create lightbox structure if it doesn't exist
    if (!lightbox) {
        lightbox = document.createElement('div');
        lightbox.id = 'galleryLightbox';
        lightbox.className = 'gallery-lightbox';
        lightbox.innerHTML = `
            <div class="gallery-lightbox-overlay"></div>
            <div class="gallery-lightbox-content">
                <button class="gallery-lightbox-close">&times;</button>
                <div class="gallery-lightbox-stage" id="galleryLightboxStage"></div>
                <div class="gallery-lightbox-controls">
                    <button class="gallery-lightbox-prev">&lt;</button>
                    <span class="gallery-lightbox-counter">
                        <span id="galleryLightboxCurrent">1</span> / <span id="galleryLightboxTotal">1</span>
                    </span>
                    <button class="gallery-lightbox-next">&gt;</button>
                </div>
                <div class="gallery-lightbox-zoom-controls">
                    <button id="galleryZoomOut">-</button>
                    <button id="galleryZoomReset">100%</button>
                    <button id="galleryZoomIn">+</button>
                </div>
            </div>
        `;
        document.body.appendChild(lightbox);
        lightboxStage = document.getElementById('galleryLightboxStage');
        lightboxCurrent = document.getElementById('galleryLightboxCurrent');
    }

    const zoomInButton = document.getElementById('galleryZoomIn');
    const zoomOutButton = document.getElementById('galleryZoomOut');
    const zoomResetButton = document.getElementById('galleryZoomReset');

    const galleryItems = document.querySelectorAll('[data-gallery-item], img.photo-viewer, [data-photo-viewer]');
    
    let currentIndex = 0;
    let galleryData = [];
    let currentMedia = null;

    let scale = 1;
    let pointX = 0;
    let pointY = 0;
    let startX = 0;
    let startY = 0;
    let isDragging = false;

    const minZoom = 1;
    const maxZoom = 4;
    const zoomStep = 0.25;

    // =========================
    // BUILD DATA
    // =========================
    function buildGalleryData() {
        galleryData = [];
        galleryItems.forEach((item, index) => {
            const el = item.querySelector('.gallery-grid-image') || item;
            const src = el.dataset.gallerySrc || el.dataset.src || el.src || el.getAttribute('data-src');
            const title = el.dataset.galleryTitle || el.alt || 'Image';
            const type = src && src.endsWith('.mp4') ? 'video' : 'image';

            if (src) {
                galleryData.push({
                    src: src,
                    title: title,
                    type: type
                });

                item.addEventListener('click', () => openLightbox(index));
                item.style.cursor = 'pointer';
            }
        });
        
        // Update total counter
        const totalElement = document.getElementById('galleryLightboxTotal');
        if (totalElement) {
            totalElement.textContent = galleryData.length;
        }
    }

    // =========================
    // OPEN / CLOSE
    // =========================
    function openLightbox(index) {
        if (galleryData.length === 0) {
            console.warn('No gallery items found');
            return;
        }
        currentIndex = index;
        updateLightbox();
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.remove('is-open');
        document.body.style.overflow = '';
        resetZoom();
    }

    // =========================
    // UPDATE CONTENT
    // =========================
    function updateLightbox() {
        resetZoom();

        if (currentIndex < 0 || currentIndex >= galleryData.length) {
            console.error('Invalid gallery index:', currentIndex);
            return;
        }

        const item = galleryData[currentIndex];

        if (item.type === 'video') {
            lightboxStage.innerHTML = `
                <video controls autoplay class="gallery-lightbox-media">
                    <source src="${item.src}" type="video/mp4">
                </video>
            `;
        } else {
            lightboxStage.innerHTML = `
                <img src="${item.src}" alt="${item.title}" class="gallery-lightbox-media">
            `;
        }

        currentMedia = lightboxStage.querySelector('.gallery-lightbox-media');

        // Reset zoom after load
        if (item.type === 'image' && currentMedia) {
            currentMedia.onload = () => resetZoom();
        }

        if (lightboxCurrent) {
            lightboxCurrent.textContent = currentIndex + 1;
        }
    }

    // =========================
    // NAVIGATION
    // =========================
    function nextImage() {
        if (galleryData.length === 0) return;
        currentIndex = (currentIndex + 1) % galleryData.length;
        updateLightbox();
    }

    function prevImage() {
        if (galleryData.length === 0) return;
        currentIndex = (currentIndex - 1 + galleryData.length) % galleryData.length;
        updateLightbox();
    }

    // =========================
    // ZOOM FUNCTIONS
    // =========================
    function updateZoom() {
        if (!currentMedia || currentMedia.tagName === 'VIDEO') return;

        currentMedia.style.transform =
            `translate(${pointX}px, ${pointY}px) scale(${scale})`;

        if (zoomResetButton) {
            zoomResetButton.textContent = Math.round(scale * 100) + "%";
        }
    }

    function resetZoom() {
        scale = 1;
        pointX = 0;
        pointY = 0;
        updateZoom();
    }

    function setZoom(newScale) {
        scale = Math.max(minZoom, Math.min(maxZoom, newScale));
        updateZoom();
    }

    // =========================
    // EVENTS
    // =========================
    function setupEventListeners() {
        const closeBtn = document.querySelector('.gallery-lightbox-close');
        const prevBtn = document.querySelector('.gallery-lightbox-prev');
        const nextBtn = document.querySelector('.gallery-lightbox-next');
        const overlay = document.querySelector('.gallery-lightbox-overlay');

        if (closeBtn) closeBtn.onclick = closeLightbox;
        if (prevBtn) prevBtn.onclick = prevImage;
        if (nextBtn) nextBtn.onclick = nextImage;
        if (overlay) overlay.onclick = closeLightbox;

        if (zoomInButton) zoomInButton.onclick = () => setZoom(scale + zoomStep);
        if (zoomOutButton) zoomOutButton.onclick = () => setZoom(scale - zoomStep);
        if (zoomResetButton) zoomResetButton.onclick = resetZoom;

        // Scroll zoom
        lightboxStage.addEventListener('wheel', (e) => {
            if (!lightbox.classList.contains('is-open')) return;
            if (!currentMedia || currentMedia.tagName === 'VIDEO') return;

            e.preventDefault();
            const delta = e.deltaY < 0 ? zoomStep : -zoomStep;
            setZoom(scale + delta);
        });

        // Drag (pan)
        lightboxStage.addEventListener('pointerdown', (e) => {
            if (scale <= 1) return;
            isDragging = true;
            startX = e.clientX - pointX;
            startY = e.clientY - pointY;
            lightboxStage.setPointerCapture(e.pointerId);
            if (currentMedia) currentMedia.style.cursor = 'grabbing';
        });

        lightboxStage.addEventListener('pointermove', (e) => {
            if (!isDragging) return;
            pointX = e.clientX - startX;
            pointY = e.clientY - startY;
            updateZoom();
        });

        function stopDrag(e) {
            isDragging = false;
            if (currentMedia) currentMedia.style.cursor = 'grab';
            if (e && lightboxStage.hasPointerCapture(e.pointerId)) {
                lightboxStage.releasePointerCapture(e.pointerId);
            }
        }

        lightboxStage.addEventListener('pointerup', stopDrag);
        lightboxStage.addEventListener('pointercancel', stopDrag);

        // Keyboard
        document.addEventListener('keydown', (e) => {
            if (!lightbox.classList.contains('is-open')) return;

            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') nextImage();
            if (e.key === 'ArrowLeft') prevImage();
            if (e.key === '+' || e.key === '=') setZoom(scale + zoomStep);
            if (e.key === '-') setZoom(scale - zoomStep);
            if (e.key === '0') resetZoom();
        });
    }

    // Initialize
    function init() {
        buildGalleryData();
        setupEventListeners();
    }

    // Run initialization when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-initialize after a short delay to catch dynamically loaded content
    setTimeout(init, 1000);

    // Expose global functions for manual use
    window.openPhotoViewer = function(src, title) {
        if (typeof src === 'string') {
            galleryData = [{ src: src, title: title || 'Image', type: src.endsWith('.mp4') ? 'video' : 'image' }];
            currentIndex = 0;
        } else if (Array.isArray(src)) {
            galleryData = src.map((item, idx) => ({
                src: typeof item === 'string' ? item : item.src,
                title: typeof item === 'string' ? `Image ${idx + 1}` : (item.title || `Image ${idx + 1}`),
                type: (typeof item === 'string' ? item : item.src).endsWith('.mp4') ? 'video' : 'image'
            }));
            currentIndex = title || 0;
        }
        
        const totalElement = document.getElementById('galleryLightboxTotal');
        if (totalElement) {
            totalElement.textContent = galleryData.length;
        }
        openLightbox(currentIndex);
    };

})();
