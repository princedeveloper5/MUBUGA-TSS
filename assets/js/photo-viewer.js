class PhotoViewer {
    constructor() {
        this.currentIndex = 0;
        this.mediaItems = [];
        this.currentScale = 1;
        this.minScale = 1;
        this.maxScale = 5;
        this.isDragging = false;
        this.isPanning = false;
        this.startX = 0;
        this.startY = 0;
        this.translateX = 0;
        this.translateY = 0;
        this.lastTap = 0;
        this.pinchDistance = 0;
        
        this.init();
    }

    init() {
        this.createOverlay();
        this.bindEvents();
    }

    createOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'photo-viewer-overlay';
        overlay.innerHTML = `
            <div class="photo-viewer-container">
                <div class="photo-viewer-content">
                    <img class="photo-viewer-image" src="" alt="">
                    <video class="photo-viewer-video" controls style="display: none;"></video>
                </div>
                
                <button class="photo-viewer-close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
                
                <div class="photo-viewer-controls">
                    <button class="photo-viewer-btn photo-viewer-zoom-in">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                            <line x1="11" y1="8" x2="11" y2="14"></line>
                            <line x1="8" y1="11" x2="14" y2="11"></line>
                        </svg>
                    </button>
                    <button class="photo-viewer-btn photo-viewer-zoom-out">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                            <line x1="8" y1="11" x2="14" y2="11"></line>
                        </svg>
                    </button>
                    <button class="photo-viewer-btn photo-viewer-reset">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                            <path d="M3 3v5h5"></path>
                        </svg>
                    </button>
                    <button class="photo-viewer-btn photo-viewer-fullscreen">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                        </svg>
                    </button>
                </div>
                
                <button class="photo-viewer-nav prev">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
                <button class="photo-viewer-nav next">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
                
                <div class="photo-viewer-counter">1 / 1</div>
                <div class="photo-viewer-zoom-indicator">100%</div>
                <div class="photo-viewer-loading" style="display: none;"></div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        this.overlay = overlay;
        this.setupElements();
    }

    setupElements() {
        this.container = this.overlay.querySelector('.photo-viewer-container');
        this.content = this.overlay.querySelector('.photo-viewer-content');
        this.image = this.overlay.querySelector('.photo-viewer-image');
        this.video = this.overlay.querySelector('.photo-viewer-video');
        this.closeBtn = this.overlay.querySelector('.photo-viewer-close');
        this.zoomInBtn = this.overlay.querySelector('.photo-viewer-zoom-in');
        this.zoomOutBtn = this.overlay.querySelector('.photo-viewer-zoom-out');
        this.resetBtn = this.overlay.querySelector('.photo-viewer-reset');
        this.fullscreenBtn = this.overlay.querySelector('.photo-viewer-fullscreen');
        this.prevBtn = this.overlay.querySelector('.photo-viewer-nav.prev');
        this.nextBtn = this.overlay.querySelector('.photo-viewer-nav.next');
        this.counter = this.overlay.querySelector('.photo-viewer-counter');
        this.zoomIndicator = this.overlay.querySelector('.photo-viewer-zoom-indicator');
        this.loading = this.overlay.querySelector('.photo-viewer-loading');
    }

    bindEvents() {
        // Close events
        this.closeBtn.addEventListener('click', () => this.close());
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) this.close();
        });

        // Navigation events
        this.prevBtn.addEventListener('click', () => this.navigate(-1));
        this.nextBtn.addEventListener('click', () => this.navigate(1));

        // Zoom controls
        this.zoomInBtn.addEventListener('click', () => this.zoom(1.2));
        this.zoomOutBtn.addEventListener('click', () => this.zoom(0.8));
        this.resetBtn.addEventListener('click', () => this.resetZoom());
        this.fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());

        // Mouse events
        this.content.addEventListener('wheel', (e) => this.handleWheel(e));
        this.content.addEventListener('dblclick', (e) => this.handleDoubleClick(e));
        this.content.addEventListener('mousedown', (e) => this.handleMouseDown(e));
        this.content.addEventListener('mousemove', (e) => this.handleMouseMove(e));
        this.content.addEventListener('mouseup', () => this.handleMouseUp());
        this.content.addEventListener('mouseleave', () => this.handleMouseUp());

        // Touch events
        this.content.addEventListener('touchstart', (e) => this.handleTouchStart(e));
        this.content.addEventListener('touchmove', (e) => this.handleTouchMove(e));
        this.content.addEventListener('touchend', (e) => this.handleTouchEnd(e));

        // Keyboard events
        document.addEventListener('keydown', (e) => this.handleKeyboard(e));

        // Prevent context menu on images
        this.content.addEventListener('contextmenu', (e) => e.preventDefault());
    }

    open(mediaItems, startIndex = 0) {
        this.mediaItems = Array.isArray(mediaItems) ? mediaItems : [mediaItems];
        this.currentIndex = Math.max(0, Math.min(startIndex, this.mediaItems.length - 1));
        
        this.overlay.classList.add('active');
        this.loadMedia();
        this.updateCounter();
        this.updateNavigationButtons();
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }

    close() {
        this.overlay.classList.remove('active');
        this.resetZoom();
        
        // Restore body scroll
        document.body.style.overflow = '';
        
        // Pause video if playing
        if (!this.video.paused) {
            this.video.pause();
        }
    }

    loadMedia() {
        const media = this.mediaItems[this.currentIndex];
        this.loading.style.display = 'block';
        
        if (media.type === 'video') {
            this.loadVideo(media.src);
        } else {
            this.loadImage(media.src);
        }
    }

    loadImage(src) {
        const img = new Image();
        img.onload = () => {
            this.image.src = src;
            this.image.style.display = 'block';
            this.video.style.display = 'none';
            this.loading.style.display = 'none';
            this.resetZoom();
        };
        img.onerror = () => {
            this.loading.style.display = 'none';
            console.error('Failed to load image:', src);
        };
        img.src = src;
    }

    loadVideo(src) {
        this.video.src = src;
        this.video.style.display = 'block';
        this.image.style.display = 'none';
        this.loading.style.display = 'none';
        this.resetZoom();
    }

    navigate(direction) {
        const newIndex = this.currentIndex + direction;
        if (newIndex >= 0 && newIndex < this.mediaItems.length) {
            this.currentIndex = newIndex;
            this.loadMedia();
            this.updateCounter();
            this.updateNavigationButtons();
        }
    }

    updateCounter() {
        this.counter.textContent = `${this.currentIndex + 1} / ${this.mediaItems.length}`;
    }

    updateNavigationButtons() {
        this.prevBtn.style.display = this.mediaItems.length > 1 ? 'flex' : 'none';
        this.nextBtn.style.display = this.mediaItems.length > 1 ? 'flex' : 'none';
        this.prevBtn.style.opacity = this.currentIndex === 0 ? '0.5' : '1';
        this.nextBtn.style.opacity = this.currentIndex === this.mediaItems.length - 1 ? '0.5' : '1';
    }

    // Zoom functionality
    zoom(factor) {
        const newScale = this.currentScale * factor;
        if (newScale >= this.minScale && newScale <= this.maxScale) {
            this.currentScale = newScale;
            this.applyTransform();
            this.updateZoomIndicator();
        }
    }

    resetZoom() {
        this.currentScale = 1;
        this.translateX = 0;
        this.translateY = 0;
        this.applyTransform();
        this.updateZoomIndicator();
    }

    applyTransform() {
        this.content.style.transform = `translate(${this.translateX}px, ${this.translateY}px) scale(${this.currentScale})`;
    }

    updateZoomIndicator() {
        const percentage = Math.round(this.currentScale * 100);
        this.zoomIndicator.textContent = `${percentage}%`;
        this.zoomIndicator.classList.add('visible');
        
        clearTimeout(this.zoomIndicatorTimeout);
        this.zoomIndicatorTimeout = setTimeout(() => {
            this.zoomIndicator.classList.remove('visible');
        }, 2000);
    }

    // Mouse events
    handleWheel(e) {
        e.preventDefault();
        const delta = e.deltaY > 0 ? 0.9 : 1.1;
        this.zoom(delta);
    }

    handleDoubleClick(e) {
        e.preventDefault();
        if (this.currentScale === 1) {
            this.zoom(2);
        } else {
            this.resetZoom();
        }
    }

    handleMouseDown(e) {
        if (this.currentScale > 1) {
            this.isDragging = true;
            this.startX = e.clientX - this.translateX;
            this.startY = e.clientY - this.translateY;
            this.content.classList.add('dragging');
        }
    }

    handleMouseMove(e) {
        if (this.isDragging) {
            e.preventDefault();
            this.translateX = e.clientX - this.startX;
            this.translateY = e.clientY - this.startY;
            this.applyTransform();
        }
    }

    handleMouseUp() {
        this.isDragging = false;
        this.content.classList.remove('dragging');
    }

    // Touch events for mobile
    handleTouchStart(e) {
        if (e.touches.length === 1) {
            // Single touch - start panning
            if (this.currentScale > 1) {
                this.isPanning = true;
                this.startX = e.touches[0].clientX - this.translateX;
                this.startY = e.touches[0].clientY - this.translateY;
                this.content.classList.add('panning');
            }
            
            // Detect double tap
            const currentTime = new Date().getTime();
            const tapLength = currentTime - this.lastTap;
            if (tapLength < 500 && tapLength > 0) {
                e.preventDefault();
                if (this.currentScale === 1) {
                    this.zoom(2);
                } else {
                    this.resetZoom();
                }
            }
            this.lastTap = currentTime;
        } else if (e.touches.length === 2) {
            // Pinch zoom
            this.isPanning = false;
            this.pinchDistance = this.getPinchDistance(e.touches);
        }
    }

    handleTouchMove(e) {
        if (e.touches.length === 1 && this.isPanning) {
            // Single touch panning
            e.preventDefault();
            this.translateX = e.touches[0].clientX - this.startX;
            this.translateY = e.touches[0].clientY - this.startY;
            this.applyTransform();
        } else if (e.touches.length === 2) {
            // Pinch zoom
            e.preventDefault();
            const currentDistance = this.getPinchDistance(e.touches);
            const scale = currentDistance / this.pinchDistance;
            this.zoom(scale);
            this.pinchDistance = currentDistance;
        }
    }

    handleTouchEnd(e) {
        this.isPanning = false;
        this.content.classList.remove('panning');
        
        if (e.touches.length === 0) {
            this.pinchDistance = 0;
        }
    }

    getPinchDistance(touches) {
        const dx = touches[0].clientX - touches[1].clientX;
        const dy = touches[0].clientY - touches[1].clientY;
        return Math.sqrt(dx * dx + dy * dy);
    }

    // Keyboard navigation
    handleKeyboard(e) {
        if (!this.overlay.classList.contains('active')) return;
        
        switch(e.key) {
            case 'Escape':
                this.close();
                break;
            case 'ArrowLeft':
                this.navigate(-1);
                break;
            case 'ArrowRight':
                this.navigate(1);
                break;
            case '+':
            case '=':
                this.zoom(1.2);
                break;
            case '-':
                this.zoom(0.8);
                break;
            case '0':
                this.resetZoom();
                break;
            case 'f':
            case 'F':
                this.toggleFullscreen();
                break;
        }
    }

    toggleFullscreen() {
        const media = this.mediaItems[this.currentIndex];
        if (media.type === 'video') {
            if (this.video.requestFullscreen) {
                this.video.requestFullscreen();
            } else if (this.video.webkitRequestFullscreen) {
                this.video.webkitRequestFullscreen();
            } else if (this.video.msRequestFullscreen) {
                this.video.msRequestFullscreen();
            }
        }
    }
}

// Initialize the photo viewer
const photoViewer = new PhotoViewer();

// Helper function to open photo viewer
function openPhotoViewer(mediaItems, startIndex = 0) {
    // Convert string URLs to media objects
    const items = Array.isArray(mediaItems) ? mediaItems : [mediaItems];
    const processedItems = items.map(item => {
        if (typeof item === 'string') {
            // Determine if it's a video or image based on extension
            const videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi'];
            const extension = item.split('.').pop().toLowerCase();
            return {
                src: item,
                type: videoExtensions.includes(extension) ? 'video' : 'image'
            };
        }
        return item;
    });
    
    photoViewer.open(processedItems, startIndex);
}

// Auto-initialize for images with photo-viewer class
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img.photo-viewer, [data-photo-viewer]');
    images.forEach((img, index) => {
        img.style.cursor = 'pointer';
        img.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Get all images in the same gallery
            const gallery = img.closest('[data-photo-gallery]');
            let galleryImages = [];
            
            if (gallery) {
                galleryImages = Array.from(gallery.querySelectorAll('img.photo-viewer, [data-photo-viewer]'))
                    .map(img => img.src || img.getAttribute('data-src'));
            } else {
                galleryImages = [img.src || img.getAttribute('data-src')];
            }
            
            const currentIndex = galleryImages.indexOf(img.src || img.getAttribute('data-src'));
            openPhotoViewer(galleryImages, currentIndex);
        });
    });
    
    // Auto-initialize for videos with photo-viewer class
    const videos = document.querySelectorAll('video.photo-viewer, [data-photo-viewer-video]');
    videos.forEach((video, index) => {
        video.style.cursor = 'pointer';
        video.addEventListener('click', (e) => {
            e.preventDefault();
            openPhotoViewer([{ src: video.src || video.getAttribute('data-src'), type: 'video' }], 0);
        });
    });
});

// Export for global use
window.PhotoViewer = PhotoViewer;
window.openPhotoViewer = openPhotoViewer;
