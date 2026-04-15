const navToggle = document.querySelector('.nav-toggle');
const nav = document.querySelector('.site-nav');
const backToTop = document.querySelector('.back-to-top');
const progressBar = document.querySelector('.scroll-progress-bar');

if (navToggle && nav) {
    navToggle.addEventListener('click', () => {
        const isOpen = nav.classList.toggle('is-open');
        navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
}

function updateScrollUI() {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;

    if (progressBar) {
        progressBar.style.width = `${Math.min(progress, 100)}%`;
    }

    if (backToTop) {
        backToTop.classList.toggle('is-visible', scrollTop > 420);
    }
}

window.addEventListener('scroll', updateScrollUI, { passive: true });
updateScrollUI();

if (backToTop) {
    backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

const revealTargets = document.querySelectorAll('.section, .stat-card, .featured-card, .program-card, .value-card, .leader-card, .news-card, .facility-card, .gallery-card, .admission-step, .contact-card');

if ('IntersectionObserver' in window && revealTargets.length > 0) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('reveal-in');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    revealTargets.forEach((target) => {
        target.classList.add('reveal-ready');
        observer.observe(target);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const heroSlider = document.querySelector('[data-hero-slider]');
    const galleryCards = document.querySelectorAll('.gallery-card');
    const mailingForm = document.querySelector('.mailing-form');
    const body = document.body;

    if (heroSlider) {
        const eyebrow = heroSlider.querySelector('[data-hero-eyebrow]');
        const title = heroSlider.querySelector('[data-hero-title]');
        const text = heroSlider.querySelector('[data-hero-text]');
        const button = heroSlider.querySelector('[data-hero-button]');
        const spotlight = document.querySelector('[data-hero-spotlight]');
        const triggers = Array.from(heroSlider.querySelectorAll('[data-hero-trigger]'));
        const images = Array.from(document.querySelectorAll('[data-hero-image]'));
        const dots = Array.from(document.querySelectorAll('[data-hero-dot]'));
        const prevButton = document.querySelector('[data-hero-prev]');
        const nextButton = document.querySelector('[data-hero-next]');
        let currentIndex = Math.max(0, triggers.findIndex((trigger) => trigger.classList.contains('is-active')));
        let sliderTimer = null;

        const setSlide = (index) => {
            const trigger = triggers[index];
            if (!trigger) {
                return;
            }

            currentIndex = index;

            triggers.forEach((item, itemIndex) => {
                const isActive = itemIndex === index;
                item.classList.toggle('is-active', isActive);
                item.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            images.forEach((image, imageIndex) => {
                image.classList.toggle('is-active', imageIndex === index);
            });

            dots.forEach((dot, dotIndex) => {
                const isActive = dotIndex === index;
                dot.classList.toggle('is-active', isActive);
                dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            if (eyebrow) {
                eyebrow.textContent = trigger.dataset.eyebrow || '';
            }
            if (title) {
                title.textContent = trigger.dataset.title || '';
            }
            if (text) {
                text.textContent = trigger.dataset.text || '';
            }
            if (button) {
                button.textContent = trigger.dataset.button || 'Register';
            }
            if (spotlight) {
                spotlight.textContent = trigger.dataset.spotlight || '';
            }
        };

        const restartSlider = () => {
            if (sliderTimer) {
                window.clearInterval(sliderTimer);
            }

            sliderTimer = window.setInterval(() => {
                const nextIndex = (currentIndex + 1) % triggers.length;
                setSlide(nextIndex);
            }, 4500);
        };

        triggers.forEach((trigger, index) => {
            trigger.addEventListener('click', () => {
                setSlide(index);
                restartSlider();
            });
        });

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                setSlide(index);
                restartSlider();
            });
        });

        if (prevButton) {
            prevButton.addEventListener('click', () => {
                const nextIndex = (currentIndex - 1 + triggers.length) % triggers.length;
                setSlide(nextIndex);
                restartSlider();
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', () => {
                const nextIndex = (currentIndex + 1) % triggers.length;
                setSlide(nextIndex);
                restartSlider();
            });
        }

        if (triggers.length > 1) {
            setSlide(currentIndex >= 0 ? currentIndex : 0);
            restartSlider();
        }
    }

    if (mailingForm) {
        const button = mailingForm.querySelector('button');
        const input = mailingForm.querySelector('input[type="email"]');

        if (button && input) {
            mailingForm.addEventListener('submit', () => {
                if (input.value.trim() === '') {
                    input.focus();
                    return;
                }

                button.textContent = 'Submitting...';
                button.disabled = true;
            });
        }
    }

    if (galleryCards.length === 0) {
        return;
    }

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
    const image = lightbox.querySelector('.lightbox-image');
    const title = lightbox.querySelector('.lightbox-title');
    const text = lightbox.querySelector('.lightbox-text');
    const closeBtn = lightbox.querySelector('.lightbox-close');
    const prevBtn = lightbox.querySelector('.lightbox-prev');
    const nextBtn = lightbox.querySelector('.lightbox-next');

    let currentIndex = 0;
    const galleryItems = [];

    galleryCards.forEach((card, index) => {
        const img = card.querySelector('.gallery-image');
        const cardTitle = card.querySelector('h3');
        const cardText = card.querySelector('p');

        if (!img || !cardTitle || !cardText) {
            return;
        }

        galleryItems.push({
            src: img.src,
            alt: img.alt,
            title: cardTitle.textContent,
            text: cardText.textContent,
        });

        card.addEventListener('click', () => openLightbox(index));
    });

    function updateLightbox() {
        const item = galleryItems[currentIndex];
        if (!item) {
            return;
        }

        image.src = item.src;
        image.alt = item.alt;
        title.textContent = item.title;
        text.textContent = item.text;
        prevBtn.style.display = currentIndex > 0 ? 'block' : 'none';
        nextBtn.style.display = currentIndex < galleryItems.length - 1 ? 'block' : 'none';
    }

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

    function showPrev() {
        if (currentIndex > 0) {
            currentIndex -= 1;
            updateLightbox();
        }
    }

    function showNext() {
        if (currentIndex < galleryItems.length - 1) {
            currentIndex += 1;
            updateLightbox();
        }
    }

    overlay.addEventListener('click', closeLightbox);
    closeBtn.addEventListener('click', closeLightbox);
    prevBtn.addEventListener('click', showPrev);
    nextBtn.addEventListener('click', showNext);

    document.addEventListener('keydown', (event) => {
        if (!lightbox.classList.contains('active')) {
            return;
        }

        if (event.key === 'Escape') {
            closeLightbox();
        }
        if (event.key === 'ArrowLeft') {
            showPrev();
        }
        if (event.key === 'ArrowRight') {
            showNext();
        }
    });
});

const admissionForm = document.getElementById('admissionForm');

if (admissionForm) {
    const phoneInput = document.getElementById('parent_phone');
    const emailInput = document.getElementById('email');
    const dobInput = document.getElementById('date_of_birth');

    if (phoneInput) {
        phoneInput.addEventListener('input', (event) => {
            let value = event.target.value.replace(/\D/g, '');
            if (value.startsWith('250')) {
                value = '+250 ' + value.slice(3);
            } else if (value.length >= 3) {
                value = value.slice(0, 3) + ' ' + value.slice(3);
            }
            if (value.length > 12) {
                value = value.slice(0, 12);
            }
            event.target.value = value;
        });
    }

    if (emailInput) {
        emailInput.addEventListener('blur', (event) => {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (event.target.value && !emailRegex.test(event.target.value)) {
                showFieldError(event.target, 'Please enter a valid email address');
            } else {
                clearFieldError(event.target);
            }
        });
    }

    if (dobInput) {
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() - 14);
        dobInput.max = maxDate.toISOString().split('T')[0];

        const minDate = new Date();
        minDate.setFullYear(minDate.getFullYear() - 25);
        dobInput.min = minDate.toISOString().split('T')[0];
    }

    admissionForm.addEventListener('submit', (event) => {
        let isValid = true;
        const requiredFields = admissionForm.querySelectorAll('[required]');

        requiredFields.forEach((field) => {
            if (!field.value.trim()) {
                showFieldError(field, 'This field is required');
                isValid = false;
            } else {
                clearFieldError(field);
            }
        });

        if (emailInput && emailInput.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value)) {
                showFieldError(emailInput, 'Please enter a valid email address');
                isValid = false;
            }
        }

        if (!isValid) {
            event.preventDefault();
            const firstError = admissionForm.querySelector('.field-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    const requiredInputs = admissionForm.querySelectorAll('[required]');
    requiredInputs.forEach((input) => {
        input.addEventListener('blur', function () {
            if (!this.value.trim()) {
                showFieldError(this, 'This field is required');
            } else {
                clearFieldError(this);
            }
        });

        input.addEventListener('input', function () {
            if (this.value.trim()) {
                clearFieldError(this);
            }
        });
    });
}

function showFieldError(field, message) {
    clearFieldError(field);
    field.style.borderColor = '#c53838';

    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
        color: #c53838;
        font-size: 0.85rem;
        margin-top: 4px;
        font-weight: 500;
    `;

    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.style.borderColor = '';
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}
