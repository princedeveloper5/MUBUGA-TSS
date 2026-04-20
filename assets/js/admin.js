document.addEventListener('DOMContentLoaded', () => {
    const adminLoader = document.querySelector('[data-admin-loader]');
    const editors = document.querySelectorAll('[data-editor]');
    const imagePreviews = document.querySelectorAll('[data-image-preview]');
    const dropInputs = document.querySelectorAll('[data-upload-drop]');
    const dashboardViews = document.querySelectorAll('[data-dashboard-view]');
    const dashboardLinks = document.querySelectorAll('.dashboard-nav-link[href^="#"]');
    const dashboardCardLinks = document.querySelectorAll('.dashboard-card-link[href^="#"]');
    const logoPathInput = document.querySelector('[data-logo-path-input]');
    const logoUploadInput = document.querySelector('[data-logo-upload-input]');
    const logoSizeInputs = document.querySelectorAll('[data-logo-size-input]');
    const logoPreviewImages = document.querySelectorAll('[data-logo-preview-image]');
    const initialDashboardView = document.body.dataset.dashboardInitial || 'dashboard-panel';

    if (adminLoader) {
        const hideAdminLoader = () => {
            adminLoader.classList.add('is-hidden');
        };

        // Initial hide and safety fallback
        window.setTimeout(hideAdminLoader, 250);
        window.addEventListener('load', hideAdminLoader);

        let loaderTimeout = null;

        const showLoader = () => {
            adminLoader.classList.remove('is-hidden');
            clearTimeout(loaderTimeout);
            loaderTimeout = setTimeout(() => {
                adminLoader.classList.add('is-hidden');
            }, 5000);
        };

        const adminForms = document.querySelectorAll('form');
        const adminLinks = document.querySelectorAll('a[href]');

        adminForms.forEach((form) => {
            form.addEventListener('submit', () => {
                showLoader();
            });
        });

        adminLinks.forEach((link) => {
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

            link.addEventListener('click', (e) => {
                // Ignore clicks with modifier keys (Ctrl/Cmd/Shift)
                if (e.ctrlKey || e.shiftKey || e.metaKey || link.target === '_blank') return;
                showLoader();
            });
        });

        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                hideAdminLoader();
            }
        });
    }

    if (dashboardViews.length > 0 && (dashboardLinks.length > 0 || dashboardCardLinks.length > 0)) {
        const showDashboardView = (viewId) => {
            const targetId = viewId && document.getElementById(viewId) ? viewId : initialDashboardView;

            dashboardViews.forEach((view) => {
                view.classList.toggle('is-active', view.id === targetId);
            });

            dashboardLinks.forEach((link) => {
                const isActive = link.getAttribute('href') === `#${targetId}`;
                link.classList.toggle('is-active', isActive);
            });
        };

        dashboardLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                const href = link.getAttribute('href') || '';
                const targetId = href.replace('#', '');

                if (targetId === '') {
                    return;
                }

                event.preventDefault();
                showDashboardView(targetId);
                window.history.replaceState(null, '', `#${targetId}`);
            });
        });

        dashboardCardLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                const href = link.getAttribute('href') || '';
                const targetId = href.replace('#', '');

                if (targetId === '') {
                    return;
                }

                event.preventDefault();
                showDashboardView(targetId);
                window.history.replaceState(null, '', `#${targetId}`);
            });
        });

        const hashView = window.location.hash.replace('#', '');
        showDashboardView(hashView || initialDashboardView);
    }

    editors.forEach((textarea) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'editor-wrapper';
        textarea.parentNode.insertBefore(wrapper, textarea);
        wrapper.appendChild(textarea);

        const toolbar = document.createElement('div');
        toolbar.className = 'editor-toolbar';

        const buttons = [
            { label: 'H2', wrap: ['<h2>', '</h2>'] },
            { label: 'H3', wrap: ['<h3>', '</h3>'] },
            { label: 'P', wrap: ['<p>', '</p>'] },
            { label: 'Bold', wrap: ['<strong>', '</strong>'] },
            { label: 'List', wrap: ['<ul>\n<li>', '</li>\n</ul>'] },
            { label: 'Link', wrap: ['<a href="https://">', '</a>'] },
        ];

        buttons.forEach((config) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'editor-button';
            button.textContent = config.label;
            button.addEventListener('click', () => wrapSelection(textarea, config.wrap[0], config.wrap[1]));
            toolbar.appendChild(button);
        });

        const previewToggle = document.createElement('button');
        previewToggle.type = 'button';
        previewToggle.className = 'editor-button editor-preview-toggle';
        previewToggle.textContent = 'Preview';
        toolbar.appendChild(previewToggle);

        wrapper.insertBefore(toolbar, textarea);
        textarea.classList.add('editor-area');

        const preview = document.createElement('div');
        preview.className = 'editor-preview';
        preview.hidden = true;
        wrapper.appendChild(preview);

        const renderPreview = () => {
            preview.innerHTML = textarea.value.trim() === ''
                ? '<p class="editor-preview-empty">Preview will appear here.</p>'
                : textarea.value;
        };

        previewToggle.addEventListener('click', () => {
            const isPreviewing = !preview.hidden;
            preview.hidden = isPreviewing;
            textarea.hidden = !isPreviewing;
            previewToggle.textContent = isPreviewing ? 'Preview' : 'Edit';

            if (!isPreviewing) {
                renderPreview();
            }
        });

        textarea.addEventListener('input', () => {
            if (!preview.hidden) {
                renderPreview();
            }
        });
    });

    imagePreviews.forEach((preview) => {
        const key = preview.dataset.imagePreview;
        const pathInput = document.querySelector(`[data-image-path="${key}"]`);
        const uploadInput = document.querySelector(`[data-image-upload="${key}"]`);

        if (!pathInput && !uploadInput) {
            return;
        }

        const image = document.createElement('img');
        image.className = 'image-preview';
        image.alt = 'Selected preview';

        const caption = document.createElement('p');
        caption.className = 'image-preview-caption';

        preview.appendChild(image);
        preview.appendChild(caption);

        const renderFromPath = () => {
            const value = pathInput ? pathInput.value.trim() : '';
            if (value === '') {
                image.removeAttribute('src');
                image.hidden = true;
                caption.textContent = 'No image selected yet.';
                preview.classList.add('is-empty');
                return;
            }

            image.src = value;
            image.hidden = false;
            caption.textContent = value;
            preview.classList.remove('is-empty');
        };

        if (pathInput) {
            pathInput.addEventListener('input', renderFromPath);
        }

        if (uploadInput) {
            uploadInput.addEventListener('change', () => {
                const [file] = uploadInput.files || [];

                if (!file) {
                    renderFromPath();
                    return;
                }

                image.src = URL.createObjectURL(file);
                image.hidden = false;
                caption.textContent = file.name;
                preview.classList.remove('is-empty');
            });
        }

        image.addEventListener('error', () => {
            image.hidden = true;
            caption.textContent = 'Preview unavailable for this path.';
            preview.classList.add('is-empty');
        });

        renderFromPath();
    });

    if (logoPreviewImages.length > 0) {
        let currentLogoObjectUrl = null;

        const updateLogoSizes = () => {
            logoSizeInputs.forEach((input) => {
                const key = input.dataset.logoSizeInput;
                const previewImage = document.querySelector(`[data-logo-preview-image="${key}"]`);
                const sizeLabel = document.querySelector(`[data-logo-preview-size-label="${key}"]`);

                if (!previewImage) {
                    return;
                }

                const min = Number(input.min || 0);
                const max = Number(input.max || 999);
                const fallback = key === 'admin' ? 34 : 52;
                const rawSize = Number(input.value || fallback);
                const clampedSize = Number.isFinite(rawSize)
                    ? Math.min(max, Math.max(min, rawSize))
                    : fallback;

                previewImage.style.width = `${clampedSize}px`;
                if (sizeLabel) {
                    sizeLabel.textContent = `${clampedSize}px`;
                }
            });
        };

        const resolveLogoPreviewPath = (value) => {
            const trimmed = (value || '').trim();
            if (trimmed === '') {
                return '';
            }

            if (/^(blob:|data:|https?:)?\/\//i.test(trimmed)) {
                return trimmed;
            }

            return `/MUBUGA-TSS/${trimmed.replace(/^\/+/, '')}`;
        };

        const updateLogoSource = (source) => {
            logoPreviewImages.forEach((image) => {
                if (source === '') {
                    image.removeAttribute('src');
                    return;
                }

                image.src = source;
            });
        };

        const syncLogoPreviewFromPath = () => {
            if (!logoPathInput) {
                return;
            }

            if (currentLogoObjectUrl) {
                URL.revokeObjectURL(currentLogoObjectUrl);
                currentLogoObjectUrl = null;
            }

            const resolvedPath = resolveLogoPreviewPath(logoPathInput.value);
            if (resolvedPath !== '') {
                updateLogoSource(resolvedPath);
            }
        };

        if (logoPathInput) {
            logoPathInput.addEventListener('input', syncLogoPreviewFromPath);
        }

        if (logoUploadInput) {
            logoUploadInput.addEventListener('change', () => {
                const [file] = logoUploadInput.files || [];

                if (!file) {
                    syncLogoPreviewFromPath();
                    return;
                }

                if (currentLogoObjectUrl) {
                    URL.revokeObjectURL(currentLogoObjectUrl);
                }

                currentLogoObjectUrl = URL.createObjectURL(file);
                updateLogoSource(currentLogoObjectUrl);
            });
        }

        logoSizeInputs.forEach((input) => {
            input.addEventListener('input', updateLogoSizes);
            input.addEventListener('change', updateLogoSizes);
        });

        logoPreviewImages.forEach((image) => {
            image.addEventListener('error', () => {
                syncLogoPreviewFromPath();
            });
        });

        updateLogoSizes();
        syncLogoPreviewFromPath();
    }

    dropInputs.forEach((input) => {
        const dropzone = document.createElement('button');
        dropzone.type = 'button';
        dropzone.className = 'upload-dropzone';
        dropzone.innerHTML = '<strong>Drop image here</strong><span>or click to choose a file</span>';

        input.insertAdjacentElement('afterend', dropzone);
        input.classList.add('upload-input');

        const syncDropLabel = () => {
            const [file] = input.files || [];
            if (file) {
                dropzone.innerHTML = `<strong>${escapeHtml(file.name)}</strong><span>Ready to upload</span>`;
                dropzone.classList.add('has-file');
                return;
            }

            dropzone.innerHTML = '<strong>Drop image here</strong><span>or click to choose a file</span>';
            dropzone.classList.remove('has-file');
        };

        dropzone.addEventListener('click', () => input.click());
        dropzone.addEventListener('dragover', (event) => {
            event.preventDefault();
            dropzone.classList.add('is-dragging');
        });
        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('is-dragging');
        });
        dropzone.addEventListener('drop', (event) => {
            event.preventDefault();
            dropzone.classList.remove('is-dragging');

            const files = event.dataTransfer ? event.dataTransfer.files : null;
            if (!files || files.length === 0) {
                return;
            }

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            input.files = dataTransfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
        input.addEventListener('change', syncDropLabel);
        syncDropLabel();
    });
});

function wrapSelection(textarea, before, after) {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const value = textarea.value;
    const selected = value.slice(start, end);
    const replacement = before + selected + after;

    textarea.value = value.slice(0, start) + replacement + value.slice(end);
    textarea.focus();
    textarea.setSelectionRange(start + before.length, start + before.length + selected.length);
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value;
    return div.innerHTML;
}
