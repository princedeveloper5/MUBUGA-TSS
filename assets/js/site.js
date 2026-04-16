const navToggle = document.querySelector('.nav-toggle');
const nav = document.querySelector('.site-nav');
const backToTop = document.querySelector('.back-to-top');
const progressBar = document.querySelector('.scroll-progress-bar');
const projectLoader = document.querySelector('[data-project-loader]');

let loaderTimeout = null;

const hideLoader = () => {
    if (projectLoader) {
        projectLoader.classList.add('is-hidden');
    }
};

const showLoader = () => {
    if (!projectLoader) {
        return;
    }

    projectLoader.classList.remove('is-hidden');
    clearTimeout(loaderTimeout);
    loaderTimeout = window.setTimeout(() => {
        projectLoader.classList.add('is-hidden');
    }, 5000);
};

const updateScrollUI = () => {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;

    if (progressBar) {
        progressBar.style.width = `${Math.min(progress, 100)}%`;
    }

    if (backToTop) {
        backToTop.classList.toggle('is-visible', scrollTop > 420);
    }
};

const initSite = () => {
    hideLoader();

    if (navToggle && nav) {
        navToggle.addEventListener('click', () => {
            const isOpen = nav.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    document.querySelectorAll('.nav-dropdown').forEach((dropdown) => {
        const toggle = dropdown.querySelector('.nav-dropdown-toggle');
        if (!toggle) {
            return;
        }

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            const isOpen = dropdown.classList.toggle('is-open');

            document.querySelectorAll('.nav-dropdown').forEach((item) => {
                if (item !== dropdown) {
                    item.classList.remove('is-open');
                    const itemToggle = item.querySelector('.nav-dropdown-toggle');
                    if (itemToggle) {
                        itemToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });

            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element) || target.closest('.nav-dropdown')) {
            return;
        }

        document.querySelectorAll('.nav-dropdown.is-open').forEach((dropdown) => {
            dropdown.classList.remove('is-open');
            const toggle = dropdown.querySelector('.nav-dropdown-toggle');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 960) {
            nav?.classList.remove('is-open');
            navToggle?.setAttribute('aria-expanded', 'false');
            document.querySelectorAll('.nav-dropdown.is-open').forEach((dropdown) => {
                dropdown.classList.remove('is-open');
                const toggle = dropdown.querySelector('.nav-dropdown-toggle');
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });

    nav?.querySelectorAll('a[href]').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth > 960) {
                return;
            }

            nav.classList.remove('is-open');
            navToggle?.setAttribute('aria-expanded', 'false');
        });
    });

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

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            showLoader();
        });
    });

    document.querySelectorAll('a[href]').forEach((link) => {
        const href = link.getAttribute('href') || '';

        if (
            href === '' ||
            href.startsWith('#') ||
            href.startsWith('mailto:') ||
            href.startsWith('tel:') ||
            href.startsWith('javascript:')
        ) {
            return;
        }

        link.addEventListener('click', (event) => {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
                return;
            }

            if (link.target === '_blank' || link.hasAttribute('download')) {
                return;
            }

            showLoader();
        });
    });

    const heroSlider = document.querySelector('[data-hero-slider]');
    const galleryCards = document.querySelectorAll('.gallery-card');
    const mailingForm = document.querySelector('.mailing-form');
    const body = document.body;

    if (heroSlider) {
        const triggers = heroSlider.querySelectorAll('[data-hero-trigger]');
        const images = document.querySelectorAll('[data-hero-image]');
        const dots = document.querySelectorAll('[data-hero-dot]');
        const prevButton = document.querySelector('[data-hero-prev]');
        const nextButton = document.querySelector('[data-hero-next]');
        const heroEyebrow = document.querySelector('[data-hero-eyebrow]');
        const heroTitle = document.querySelector('[data-hero-title]');
        const heroText = document.querySelector('[data-hero-text]');
        const heroButton = document.querySelector('[data-hero-button]');
        const heroSpotlight = document.querySelector('[data-hero-spotlight]');

        let currentIndex = 0;
        let sliderTimer = null;

        const setSlide = (index) => {
            currentIndex = index;

            triggers.forEach((item, i) => {
                const isActive = i === index;
                item.classList.toggle('is-active', isActive);
                item.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            images.forEach((img, i) => {
                img.classList.toggle('is-active', i === index);
            });

            dots.forEach((dot, i) => {
                const isActive = i === index;
                dot.classList.toggle('is-active', isActive);
                dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            const activeTrigger = triggers[index];
            if (!activeTrigger) {
                return;
            }

            if (heroEyebrow) {
                heroEyebrow.textContent = activeTrigger.dataset.eyebrow || '';
            }

            if (heroTitle) {
                heroTitle.textContent = activeTrigger.dataset.title || '';
            }

            if (heroText) {
                heroText.textContent = activeTrigger.dataset.text || '';
            }

            if (heroButton) {
                heroButton.textContent = activeTrigger.dataset.button || 'Learn More';
                heroButton.setAttribute('href', activeTrigger.dataset.link || '/MUBUGA-TSS/pages/admissions.php');
            }

            if (heroSpotlight) {
                heroSpotlight.textContent = activeTrigger.dataset.spotlight || '';
            }
        };

        const restartSlider = () => {
            clearInterval(sliderTimer);
            sliderTimer = window.setInterval(() => {
                setSlide((currentIndex + 1) % triggers.length);
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
                setSlide((currentIndex - 1 + triggers.length) % triggers.length);
                restartSlider();
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', () => {
                setSlide((currentIndex + 1) % triggers.length);
                restartSlider();
            });
        }

        if (triggers.length > 1) {
            setSlide(0);
            restartSlider();
        }
    }

    if (mailingForm) {
        const button = mailingForm.querySelector('button');
        const input = mailingForm.querySelector('input[type="email"]');

        mailingForm.addEventListener('submit', () => {
            if (!input || !button || !input.value.trim()) {
                if (input) {
                    input.focus();
                }
                return;
            }

            button.textContent = 'Submitting...';
            button.disabled = true;
        });
    }

    if (galleryCards.length > 0) {
        const lightbox = document.createElement('div');
        lightbox.className = 'gallery-lightbox';
        lightbox.innerHTML = `
            <div class="lightbox-overlay"></div>
            <div class="lightbox-content">
                <button class="lightbox-close">&times;</button>
                <img class="lightbox-image">
            </div>
        `;

        body.appendChild(lightbox);

        const image = lightbox.querySelector('.lightbox-image');
        const overlay = lightbox.querySelector('.lightbox-overlay');
        const closeBtn = lightbox.querySelector('.lightbox-close');

        galleryCards.forEach((card) => {
            if (card.classList.contains('gallery-video-card')) {
                return;
            }

            const img = card.querySelector('img');
            if (!img) {
                return;
            }

            card.addEventListener('click', () => {
                image.src = img.src;
                lightbox.classList.add('active');
                body.style.overflow = 'hidden';
            });
        });

        const closeLightbox = () => {
            lightbox.classList.remove('active');
            body.style.overflow = '';
        };

        if (overlay) {
            overlay.addEventListener('click', closeLightbox);
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeLightbox);
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeLightbox();
            }
        });
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSite);
} else {
    initSite();
}

window.addEventListener('load', hideLoader);
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        hideLoader();
    }
});
