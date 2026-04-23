// Performance Optimizations
document.addEventListener('DOMContentLoaded', function() {
    // Lazy Loading for Images
    const lazyImages = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    lazyImages.forEach(img => imageObserver.observe(img));

    // Preload Critical Resources
    const criticalResources = [
        '/MUBUGA-TSS/assets/css/site.min.css',
        '/MUBUGA-TSS/assets/css/photo-viewer.css'
    ];

    criticalResources.forEach(resource => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = resource.endsWith('.css') ? 'style' : 'script';
        link.href = resource;
        document.head.appendChild(link);
    });

    // Optimize Font Loading
    if ('fonts' in document) {
        Promise.all([
            document.fonts.load('400 1em Space Grotesk'),
            document.fonts.load('600 1em Space Grotesk'),
            document.fonts.load('700 1em Space Grotesk'),
            document.fonts.load('400 1em Source Sans 3'),
            document.fonts.load('600 1em Source Sans 3'),
            document.fonts.load('700 1em Source Sans 3')
        ]).then(() => {
            document.documentElement.classList.add('fonts-loaded');
        });
    }

    // Remove Project Loader Early
    const loader = document.querySelector('.project-loader');
    if (loader) {
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 300);
        }, 500);
    }

    // Optimize Scroll Performance
    let ticking = false;
    function updateScrollPosition() {
        // Add scroll-based optimizations here
        ticking = false;
    }

    function requestTick() {
        if (!ticking) {
            requestAnimationFrame(updateScrollPosition);
            ticking = true;
        }
    }

    window.addEventListener('scroll', requestTick);

    // Debounce Resize Events
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            // Handle resize optimizations
            console.log('Optimized resize handled');
        }, 250);
    });

    // Cache DOM Elements
    const cache = {
        body: document.body,
        header: document.querySelector('header'),
        footer: document.querySelector('footer'),
        main: document.querySelector('main')
    };

    // Performance Monitoring
    if ('performance' in window) {
        window.addEventListener('load', () => {
            const perfData = performance.getEntriesByType('navigation')[0];
            console.log('Page Load Time:', perfData.loadEventEnd - perfData.loadEventStart, 'ms');
        });
    }
});

// Service Worker Registration for Caching
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/MUBUGA-TSS/sw.js')
            .then(registration => {
                console.log('SW registered: ', registration);
            })
            .catch(registrationError => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}
