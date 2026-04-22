// Simple and robust Photo Viewer implementation
(function() {
    'use strict';

    // Create PhotoViewer class
    class PhotoViewer {
        constructor() {
            this.currentIndex = 0;
            this.mediaItems = [];
            this.currentScale = 1;
            this.minScale = 1;
            this.maxScale = 5;
            this.isDragging = false;
            this.startX = 0;
            this.startY = 0;
            this.translateX = 0;
            this.translateY = 0;
            this.overlay = null;
            this.content = null;
            this.image = null;
            this.video = null;
            
            this.init();
        }

        init() {
            this.createOverlay();
            this.bindEvents();
        }

        createOverlay() {
            // Check if overlay already exists
            if (document.querySelector('.photo-viewer-overlay')) {
                this.overlay = document.querySelector('.photo-viewer-overlay');
                this.setupElements();
                return;
            }

            // Create overlay
            this.overlay = document.createElement('div');
            this.overlay.className = 'photo-viewer-overlay';
            this.overlay.style.display = 'none';
            this.overlay.innerHTML = `
                <div class="photo-viewer-container">
                    <div class="photo-viewer-content">
                        <div class="photo-viewer-loading">Loading...</div>
                        <img class="photo-viewer-image" src="" alt="" style="display: none;">
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
                </div>
            `;
            
            document.body.appendChild(this.overlay);
            this.setupElements();
        }

        setupElements() {
            this.container = this.overlay.querySelector('.photo-viewer-container');
            this.content = this.overlay.querySelector('.photo-viewer-content');
            this.loading = this.overlay.querySelector('.photo-viewer-loading');
            this.image = this.overlay.querySelector('.photo-viewer-image');
            this.video = this.overlay.querySelector('.photo-viewer-video');
            this.closeBtn = this.overlay.querySelector('.photo-viewer-close');
            this.zoomInBtn = this.overlay.querySelector('.photo-viewer-zoom-in');
            this.zoomOutBtn = this.overlay.querySelector('.photo-viewer-zoom-out');
            this.resetBtn = this.overlay.querySelector('.photo-viewer-reset');
            this.prevBtn = this.overlay.querySelector('.photo-viewer-nav.prev');
            this.nextBtn = this.overlay.querySelector('.photo-viewer-nav.next');
            this.counter = this.overlay.querySelector('.photo-viewer-counter');
            this.zoomIndicator = this.overlay.querySelector('.photo-viewer-zoom-indicator');
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
        }

        open(mediaItems, startIndex = 0) {
            console.log('Opening photo viewer with items:', mediaItems);
            
            this.mediaItems = Array.isArray(mediaItems) ? mediaItems : [mediaItems];
            this.currentIndex = Math.max(0, Math.min(startIndex, this.mediaItems.length - 1));
            
            this.overlay.style.display = 'flex';
            setTimeout(() => {
                this.overlay.classList.add('active');
            }, 10);
            
            this.loadMedia();
            this.updateCounter();
            this.updateNavigationButtons();
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        close() {
            this.overlay.classList.remove('active');
            setTimeout(() => {
                this.overlay.style.display = 'none';
            }, 300);
            
            this.resetZoom();
            
            // Restore body scroll
            document.body.style.overflow = '';
            
            // Pause video if playing
            if (this.video && !this.video.paused) {
                this.video.pause();
            }
        }

        loadMedia() {
            const media = this.mediaItems[this.currentIndex];
            console.log('Loading media:', media);
            
            if (typeof media === 'object' && media.type === 'video') {
                this.loadVideo(media.src);
            } else {
                const src = typeof media === 'object' ? media.src : media;
                this.loadImage(src);
            }
        }

        loadImage(src) {
            console.log('Loading image:', src);
            if (!src) {
                console.error('No image source provided');
                if (this.loading) this.loading.style.display = 'none';
                return;
            }
            
            // Show loading, hide image/video initially
            if (this.loading) this.loading.style.display = 'block';
            this.image.style.display = 'none';
            this.video.style.display = 'none';
            
            this.image.onload = () => {
                console.log('Image loaded successfully');
                if (this.loading) this.loading.style.display = 'none';
                this.image.style.display = 'block';
                this.image.style.visibility = 'visible';
                this.image.style.opacity = '1';
                this.video.style.display = 'none';
                this.resetZoom();
            };
            this.image.onerror = () => {
                console.error('Failed to load image:', src);
                if (this.loading) this.loading.textContent = 'Failed to load image';
                this.image.style.display = 'none';
            };
            this.image.src = src;
        }

        loadVideo(src) {
            console.log('Loading video:', src);
            if (this.loading) this.loading.style.display = 'none';
            this.video.src = src;
            this.video.style.display = 'block';
            this.image.style.display = 'none';
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
            console.log('Zoom called with factor:', factor, 'current scale:', this.currentScale);
            const newScale = this.currentScale * factor;
            if (newScale >= this.minScale && newScale <= this.maxScale) {
                this.currentScale = newScale;
                this.applyTransform();
                this.updateZoomIndicator();
                console.log('New scale applied:', this.currentScale);
            }
        }

        resetZoom() {
            console.log('Reset zoom called');
            this.currentScale = 1;
            this.translateX = 0;
            this.translateY = 0;
            this.applyTransform();
            this.updateZoomIndicator();
        }

        applyTransform() {
            if (this.content) {
                const transform = `translate(${this.translateX}px, ${this.translateY}px) scale(${this.currentScale})`;
                this.content.style.transform = transform;
                console.log('Applied transform:', transform);
            }
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
            e.stopPropagation();
            console.log('Wheel event detected, deltaY:', e.deltaY);
            const delta = e.deltaY > 0 ? 0.9 : 1.1;
            this.zoom(delta);
        }

        handleDoubleClick(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Double-click detected, current scale:', this.currentScale);
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
                this.content.style.cursor = 'grabbing';
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
            this.content.style.cursor = 'grab';
        }

        // Touch events
        handleTouchStart(e) {
            if (e.touches.length === 1) {
                if (this.currentScale > 1) {
                    this.startX = e.touches[0].clientX - this.translateX;
                    this.startY = e.touches[0].clientY - this.translateY;
                }
            }
        }

        handleTouchMove(e) {
            if (e.touches.length === 1 && this.currentScale > 1) {
                e.preventDefault();
                this.translateX = e.touches[0].clientX - this.startX;
                this.translateY = e.touches[0].clientY - this.startY;
                this.applyTransform();
            }
        }

        handleTouchEnd(e) {
            // Touch end logic
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
            }
        }
    }

    // Initialize photo viewer
    let photoViewer = null;

    // Helper function to open photo viewer
    function openPhotoViewer(mediaItems, startIndex = 0) {
        if (!photoViewer) {
            photoViewer = new PhotoViewer();
        }
        photoViewer.open(mediaItems, startIndex);
    }

    // Auto-initialize for images with photo-viewer class
    function initializePhotoViewer() {
        console.log('Initializing photo viewer...');
        
        const images = document.querySelectorAll('img.photo-viewer, [data-photo-viewer]');
        console.log('Found images:', images.length);
        
        images.forEach((img, index) => {
            img.style.cursor = 'pointer';
            img.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Image clicked:', this.src);
                
                // Get all images in the same gallery
                const gallery = this.closest('[data-photo-gallery]');
                let galleryImages = [];
                
                if (gallery) {
                    galleryImages = Array.from(gallery.querySelectorAll('img.photo-viewer, [data-photo-viewer]'))
                        .map(img => img.src || img.getAttribute('data-src'));
                } else {
                    galleryImages = [this.src || this.getAttribute('data-src')];
                }
                
                const currentIndex = galleryImages.indexOf(this.src || this.getAttribute('data-src'));
                openPhotoViewer(galleryImages, currentIndex);
            });
        });
        
        // Auto-initialize for videos
        const videos = document.querySelectorAll('video.photo-viewer, [data-photo-viewer-video]');
        videos.forEach((video, index) => {
            video.style.cursor = 'pointer';
            video.addEventListener('click', function(e) {
                e.preventDefault();
                openPhotoViewer([{ src: this.src || this.getAttribute('data-src'), type: 'video' }], 0);
            });
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializePhotoViewer);
    } else {
        initializePhotoViewer();
    }

    // Also initialize after a short delay to catch dynamically loaded content
    setTimeout(initializePhotoViewer, 1000);

    // Export for global use
    window.PhotoViewer = PhotoViewer;
    window.openPhotoViewer = openPhotoViewer;
    window.initializePhotoViewer = initializePhotoViewer;

})();
