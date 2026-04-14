const navToggle = document.querySelector('.nav-toggle');
const nav = document.querySelector('.site-nav');

if (navToggle && nav) {
    navToggle.addEventListener('click', () => {
        const isOpen = nav.classList.toggle('is-open');
        navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
}

// Gallery Lightbox Functionality
document.addEventListener('DOMContentLoaded', function() {
    const galleryCards = document.querySelectorAll('.gallery-card');
    const body = document.body;

    // Create lightbox elements
    const lightbox = document.createElement('div');
    lightbox.className = 'gallery-lightbox';
    lightbox.innerHTML = `
        <div class="lightbox-overlay"></div>
        <div class="lightbox-content">
            <button class="lightbox-close" aria-label="Close gallery">&times;</button>
            <button class="lightbox-prev" aria-label="Previous image">&#10094;</button>
            <button class="lightbox-next" aria-label="Next image">&#10095;</button>
            <img class="lightbox-image" src="" alt="">
            <div class="lightbox-caption">
                <h3 class="lightbox-title"></h3>
                <p class="lightbox-text"></p>
            </div>
        </div>
    `;

    body.appendChild(lightbox);

    const overlay = lightbox.querySelector('.lightbox-overlay');
    const content = lightbox.querySelector('.lightbox-content');
    const image = lightbox.querySelector('.lightbox-image');
    const title = lightbox.querySelector('.lightbox-title');
    const text = lightbox.querySelector('.lightbox-text');
    const closeBtn = lightbox.querySelector('.lightbox-close');
    const prevBtn = lightbox.querySelector('.lightbox-prev');
    const nextBtn = lightbox.querySelector('.lightbox-next');

    let currentIndex = 0;
    let galleryItems = [];

    // Initialize gallery items
    galleryCards.forEach((card, index) => {
        const img = card.querySelector('.gallery-image');
        const cardTitle = card.querySelector('h3').textContent;
        const cardText = card.querySelector('p').textContent;

        galleryItems.push({
            src: img.src,
            alt: img.alt,
            title: cardTitle,
            text: cardText
        });

        card.addEventListener('click', () => {
            openLightbox(index);
        });
    });

    function openLightbox(index) {
        currentIndex = index;
        updateLightbox();
        lightbox.classList.add('active');
        body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.remove('active');
        body.style.overflow = '';
    }

    function updateLightbox() {
        const item = galleryItems[currentIndex];
        image.src = item.src;
        image.alt = item.alt;
        title.textContent = item.title;
        text.textContent = item.text;

        // Update navigation buttons
        prevBtn.style.display = currentIndex > 0 ? 'block' : 'none';
        nextBtn.style.display = currentIndex < galleryItems.length - 1 ? 'block' : 'none';
    }

    function showPrev() {
        if (currentIndex > 0) {
            currentIndex--;
            updateLightbox();
        }
    }

    function showNext() {
        if (currentIndex < galleryItems.length - 1) {
            currentIndex++;
            updateLightbox();
        }
    }

    // Event listeners
    overlay.addEventListener('click', closeLightbox);
    closeBtn.addEventListener('click', closeLightbox);
    prevBtn.addEventListener('click', showPrev);
    nextBtn.addEventListener('click', showNext);

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('active')) return;

        switch(e.key) {
            case 'Escape':
                closeLightbox();
                break;
            case 'ArrowLeft':
                showPrev();
                break;
            case 'ArrowRight':
                showNext();
                break;
        }
    });
});
