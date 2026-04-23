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
    const searchInput = document.querySelector('[data-dashboard-search-input]');
    const searchResults = document.querySelector('[data-dashboard-search-results]');
    const notificationMenu = document.querySelector('[data-notification-menu]');
    const notificationTrigger = document.querySelector('[data-notification-trigger]');
    const notificationDropdown = document.querySelector('[data-notification-dropdown]');
    const adminModals = document.querySelectorAll('[data-admin-modal]');
    const initialDashboardView = document.body.dataset.dashboardInitial || 'dashboard-panel';
    const dashboardShell = document.querySelector('.dashboard-shell');
    const dashboardSidebarToggle = document.querySelector('[data-dashboard-sidebar-toggle]');
    const sidebarPreferenceKey = 'mubuga-dashboard-sidebar-collapsed';
    const sidebarSections = document.querySelectorAll('[data-sidebar-section]');
    const sidebarItems = document.querySelectorAll('[data-sidebar-item]');
    const sidebarSublinks = document.querySelectorAll('.dashboard-nav-sublink');
    const currentUrl = new URL(window.location.href);
    const currentSearchParams = currentUrl.searchParams;

    const openModal = (modalId) => {
        const modal = document.getElementById(modalId);
        if (!modal) {
            return;
        }

        modal.hidden = false;
        document.body.classList.add('has-admin-modal');
    };

    const closeModal = (modal) => {
        if (!modal) {
            return;
        }

        modal.hidden = true;
        if (![...adminModals].some((item) => !item.hidden)) {
            document.body.classList.remove('has-admin-modal');
        }
    };

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
        const resolveDashboardView = (viewId) => {
            if (viewId && document.getElementById(viewId)) {
                const directView = document.getElementById(viewId);
                if (directView && directView.hasAttribute('data-dashboard-view')) {
                    return { viewId, anchorId: viewId };
                }

                const parentView = directView ? directView.closest('[data-dashboard-view]') : null;
                if (parentView) {
                    return { viewId: parentView.id, anchorId: viewId };
                }
            }

            return { viewId: initialDashboardView, anchorId: '' };
        };

        const showDashboardView = (viewId) => {
            const resolved = resolveDashboardView(viewId);
            const targetId = resolved.viewId;

            dashboardViews.forEach((view) => {
                view.classList.toggle('is-active', view.id === targetId);
            });

            dashboardLinks.forEach((link) => {
                const isActive = link.getAttribute('href') === `#${targetId}`;
                link.classList.toggle('is-active', isActive);
            });

            dashboardCardLinks.forEach((link) => {
                const isActive = link.getAttribute('href') === `#${targetId}`;
                link.classList.toggle('is-active', isActive);
            });

            sidebarSections.forEach((section) => {
                const trigger = section.querySelector('[data-sidebar-section-trigger]');
                const content = section.querySelector('[data-sidebar-section-content]');
                const hasActiveLink = !!section.querySelector(`.dashboard-nav-link[href="#${targetId}"]`);

                if (!trigger || !content) {
                    return;
                }

                if (hasActiveLink) {
                    section.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                    content.hidden = false;
                }
            });

            sidebarItems.forEach((item) => {
                const trigger = item.querySelector('[data-sidebar-item-toggle]');
                const content = item.querySelector('[data-sidebar-item-content]');
                const mainLink = item.querySelector('.dashboard-nav-link[href^="#"]');

                if (!trigger || !content || !mainLink) {
                    return;
                }

                const isCurrentItem = mainLink.getAttribute('href') === `#${targetId}`;
                item.classList.toggle('is-current', isCurrentItem);

                if (isCurrentItem) {
                    item.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                    content.hidden = false;
                }
            });

            if (resolved.anchorId && resolved.anchorId !== targetId) {
                window.setTimeout(() => {
                    const anchorTarget = document.getElementById(resolved.anchorId);
                    if (anchorTarget) {
                        anchorTarget.scrollIntoView({ block: 'start', behavior: 'smooth' });
                    }
                }, 60);
            }
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
        window.addEventListener('hashchange', () => {
            showDashboardView(window.location.hash.replace('#', '') || initialDashboardView);
        });
    }

    if (sidebarSublinks.length > 0) {
        const normalizePath = (path) => path.replace(/\/+$/, '');
        const currentPath = normalizePath(currentUrl.pathname);
        const currentHash = currentUrl.hash || '';
        const currentCompose = currentSearchParams.get('compose') || '';
        const currentMediaFilter = currentSearchParams.get('media_filter') || '';
        const currentMediaNew = currentSearchParams.get('media_new') || '';
        const currentNewsFilter = currentSearchParams.get('news_filter') || '';

        sidebarSublinks.forEach((link) => {
            let isActive = false;

            try {
                const targetUrl = new URL(link.getAttribute('href'), currentUrl.origin);
                const targetPath = normalizePath(targetUrl.pathname);
                const targetHash = targetUrl.hash || '';
                const targetCompose = targetUrl.searchParams.get('compose') || '';
                const targetMediaFilter = targetUrl.searchParams.get('media_filter') || '';
                const targetMediaNew = targetUrl.searchParams.get('media_new') || '';
                const targetNewsFilter = targetUrl.searchParams.get('news_filter') || '';

                if (targetCompose !== '') {
                    isActive = currentPath === targetPath && currentCompose === targetCompose && currentHash === targetHash;
                } else if (targetMediaNew !== '') {
                    isActive = currentPath === targetPath
                        && currentHash === '#gallery-panel'
                        && currentMediaNew === targetMediaNew;
                } else if (targetMediaFilter !== '') {
                    isActive = currentPath === targetPath
                        && currentHash === '#gallery-panel'
                        && currentMediaFilter === targetMediaFilter
                        && currentMediaNew === '';
                } else if (targetNewsFilter !== '') {
                    isActive = currentPath === targetPath
                        && currentHash === '#news-panel'
                        && currentNewsFilter === targetNewsFilter;
                } else {
                    isActive = currentPath === targetPath && currentHash === targetHash;
                }
            } catch (error) {
                isActive = false;
            }

            link.classList.toggle('is-active', isActive);
        });
    }

    if (dashboardShell && dashboardSidebarToggle) {
        const applySidebarState = (collapsed) => {
            dashboardShell.classList.toggle('is-sidebar-collapsed', collapsed);
            dashboardSidebarToggle.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
        };

        const desktopMediaQuery = window.matchMedia('(min-width: 1101px)');
        const storedPreference = window.localStorage.getItem(sidebarPreferenceKey);

        if (desktopMediaQuery.matches && storedPreference === '1') {
            applySidebarState(true);
        }

        dashboardSidebarToggle.addEventListener('click', () => {
            if (!desktopMediaQuery.matches) {
                dashboardShell.scrollIntoView({ block: 'start', behavior: 'smooth' });
                return;
            }

            const collapsed = !dashboardShell.classList.contains('is-sidebar-collapsed');
            applySidebarState(collapsed);
            window.localStorage.setItem(sidebarPreferenceKey, collapsed ? '1' : '0');
        });

        const syncSidebarOnResize = (event) => {
            if (!event.matches) {
                applySidebarState(false);
            } else if (window.localStorage.getItem(sidebarPreferenceKey) === '1') {
                applySidebarState(true);
            }
        };

        if (typeof desktopMediaQuery.addEventListener === 'function') {
            desktopMediaQuery.addEventListener('change', syncSidebarOnResize);
        } else if (typeof desktopMediaQuery.addListener === 'function') {
            desktopMediaQuery.addListener(syncSidebarOnResize);
        }
    }

    if (sidebarSections.length > 0) {
        sidebarSections.forEach((section) => {
            const trigger = section.querySelector('[data-sidebar-section-trigger]');
            const content = section.querySelector('[data-sidebar-section-content]');

            if (!trigger || !content) {
                return;
            }

            trigger.addEventListener('click', () => {
                const isOpen = section.classList.contains('is-open');
                section.classList.toggle('is-open', !isOpen);
                trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                content.hidden = isOpen;
            });
        });
    }

    if (sidebarItems.length > 0) {
        sidebarItems.forEach((item) => {
            const trigger = item.querySelector('[data-sidebar-item-toggle]');
            const content = item.querySelector('[data-sidebar-item-content]');

            if (!trigger || !content) {
                return;
            }

            trigger.addEventListener('click', () => {
                const isOpen = item.classList.contains('is-open');
                item.classList.toggle('is-open', !isOpen);
                trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                content.hidden = isOpen;
            });
        });
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
            { label: 'Italic', wrap: ['<em>', '</em>'] },
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
        dropzone.innerHTML = '<strong>Drop file here</strong><span>or click to choose a file</span>';

        input.insertAdjacentElement('afterend', dropzone);
        input.classList.add('upload-input');

        const syncDropLabel = () => {
            const [file] = input.files || [];
            if (file) {
                dropzone.innerHTML = `<strong>${escapeHtml(file.name)}</strong><span>Ready to upload</span>`;
                dropzone.classList.add('has-file');
                return;
            }

            dropzone.innerHTML = '<strong>Drop file here</strong><span>or click to choose a file</span>';
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

    document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.getAttribute('data-modal-open');
            if (modalId) {
                openModal(modalId);
            }
        });
    });

    adminModals.forEach((modal) => {
        modal.querySelectorAll('[data-modal-close]').forEach((closeButton) => {
            closeButton.addEventListener('click', () => closeModal(modal));
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        adminModals.forEach((modal) => {
            if (!modal.hidden) {
                closeModal(modal);
            }
        });
    });

    const previewModal = document.getElementById('gallery-preview-modal');
    const previewStage = previewModal ? previewModal.querySelector('[data-gallery-preview-stage]') : null;
    const previewTitle = previewModal ? previewModal.querySelector('[data-gallery-preview-title]') : null;
    const previewCaption = previewModal ? previewModal.querySelector('[data-gallery-preview-caption]') : null;
    const previewMeta = previewModal ? previewModal.querySelector('[data-gallery-preview-meta]') : null;
    const previewDownload = previewModal ? previewModal.querySelector('[data-gallery-preview-download]') : null;
    const previewEdit = previewModal ? previewModal.querySelector('[data-gallery-preview-edit]') : null;
    const previewDelete = previewModal ? previewModal.querySelector('[data-gallery-preview-delete]') : null;
    const deleteModal = document.getElementById('gallery-delete-modal');
    const deleteIdInput = deleteModal ? deleteModal.querySelector('[data-gallery-delete-id]') : null;
    const deleteMessage = deleteModal ? deleteModal.querySelector('[data-gallery-delete-message]') : null;

    const setDeleteTarget = (id, title) => {
        if (deleteIdInput) {
            deleteIdInput.value = id || '0';
        }
        if (deleteMessage) {
            deleteMessage.textContent = title
                ? `Are you sure you want to remove "${title}" from the media library?`
                : 'Are you sure you want to remove this media item?';
        }
    };

    document.querySelectorAll('[data-gallery-delete-open]').forEach((button) => {
        button.addEventListener('click', () => {
            setDeleteTarget(button.dataset.mediaId || '0', button.dataset.mediaTitle || '');
            openModal('gallery-delete-modal');
        });
    });

    document.querySelectorAll('[data-gallery-preview-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            if (!previewModal || !previewStage || !previewTitle || !previewCaption || !previewMeta || !previewDownload || !previewEdit || !previewDelete) {
                return;
            }

            const type = trigger.dataset.mediaType || 'image';
            const url = trigger.dataset.mediaUrl || '';
            const title = trigger.dataset.mediaTitle || 'Media preview';
            const caption = trigger.dataset.mediaCaption || '';
            const category = trigger.dataset.mediaCategory || 'General';
            const album = trigger.dataset.mediaAlbum || 'General';
            const size = trigger.dataset.mediaSize || 'Unknown size';
            const fileType = trigger.dataset.mediaFiletype || 'FILE';
            const dimensions = trigger.dataset.mediaDimensions || '';
            const createdAt = trigger.dataset.mediaDate || '';
            const mediaId = trigger.dataset.mediaId || '0';
            const downloadUrl = trigger.dataset.mediaDownload || '#';

            previewTitle.textContent = title;
            previewCaption.textContent = caption || 'No description provided for this media item.';
            previewMeta.innerHTML = [
                `Type: ${fileType}`,
                `Category: ${category}`,
                `Album: ${album}`,
                `File size: ${size}`,
                dimensions ? `Dimensions: ${dimensions}` : '',
                createdAt ? `Uploaded: ${createdAt}` : '',
            ].filter(Boolean).map((item) => `<span class="inline-meta">${escapeHtml(item)}</span>`).join('');

            previewDownload.href = downloadUrl;
            previewEdit.href = `/MUBUGA-TSS/admin/dashboard.php?edit=gallery&id=${encodeURIComponent(mediaId)}#gallery-panel`;
            previewDelete.onclick = () => {
                setDeleteTarget(mediaId, title);
                closeModal(previewModal);
                openModal('gallery-delete-modal');
            };

            previewStage.innerHTML = '';
            if (type === 'video') {
                const video = document.createElement('video');
                video.src = url;
                video.controls = true;
                video.playsInline = true;
                video.preload = 'metadata';
                previewStage.appendChild(video);
            } else {
                const image = document.createElement('img');
                image.src = url;
                image.alt = title;
                previewStage.appendChild(image);
            }

            openModal('gallery-preview-modal');
        });
    });

    const selectAllCheckbox = document.querySelector('[data-gallery-select-all]');
    const selectedCheckboxes = document.querySelectorAll('[data-gallery-select]');
    if (selectAllCheckbox && selectedCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', () => {
            selectedCheckboxes.forEach((checkbox) => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }

    const bulkDeleteButton = document.querySelector('[data-gallery-bulk-delete]');
    if (bulkDeleteButton) {
        bulkDeleteButton.addEventListener('click', (event) => {
            const selectedCount = [...document.querySelectorAll('[data-gallery-select]:checked')].length;
            if (selectedCount === 0) {
                event.preventDefault();
                window.alert('Select at least one media item first.');
                return;
            }

            if (!window.confirm(`Delete ${selectedCount} selected media item(s)?`)) {
                event.preventDefault();
            }
        });
    }

    const galleryUploadForm = document.querySelector('[data-gallery-upload-form]');
    const galleryUploadProgress = document.querySelector('[data-gallery-upload-progress]');
    const galleryUploadProgressBar = document.querySelector('[data-gallery-upload-progress-bar]');
    const galleryUploadProgressLabel = document.querySelector('[data-gallery-upload-progress-label]');
    const galleryUploadFeedback = document.querySelector('[data-gallery-upload-feedback]');
    if (galleryUploadForm && galleryUploadProgress && galleryUploadProgressBar && galleryUploadProgressLabel) {
        galleryUploadForm.addEventListener('submit', (event) => {
            event.preventDefault();

            const formData = new FormData(galleryUploadForm);
            const submitButton = galleryUploadForm.querySelector('button[type="submit"]');
            const request = new XMLHttpRequest();

            if (galleryUploadFeedback) {
                galleryUploadFeedback.hidden = true;
                galleryUploadFeedback.className = 'gallery-upload-feedback';
                galleryUploadFeedback.textContent = '';
            }

            galleryUploadProgress.hidden = false;
            galleryUploadProgressBar.style.width = '0%';
            galleryUploadProgressLabel.textContent = 'Starting upload...';

            if (submitButton) {
                submitButton.disabled = true;
            }

            request.open('POST', galleryUploadForm.getAttribute('action') || window.location.href, true);
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            request.upload.addEventListener('progress', (progressEvent) => {
                if (!progressEvent.lengthComputable) {
                    galleryUploadProgressBar.style.width = '45%';
                    galleryUploadProgressLabel.textContent = 'Uploading media...';
                    return;
                }

                const percent = Math.max(4, Math.min(100, Math.round((progressEvent.loaded / progressEvent.total) * 100)));
                galleryUploadProgressBar.style.width = `${percent}%`;
                galleryUploadProgressLabel.textContent = `Uploading media... ${percent}%`;
            });

            request.addEventListener('load', () => {
                if (submitButton) {
                    submitButton.disabled = false;
                }

                let payload = null;
                try {
                    payload = JSON.parse(request.responseText || '{}');
                } catch (error) {
                    payload = null;
                }

                if (!payload || request.status < 200 || request.status >= 300 || payload.success !== true) {
                    galleryUploadProgressBar.style.width = '100%';
                    galleryUploadProgressLabel.textContent = 'Upload failed.';
                    if (galleryUploadFeedback) {
                        galleryUploadFeedback.hidden = false;
                        galleryUploadFeedback.className = 'gallery-upload-feedback is-error';
                        galleryUploadFeedback.textContent = payload && payload.error
                            ? payload.error
                            : 'The upload could not be completed. Please try again.';
                    }
                    return;
                }

                galleryUploadProgressBar.style.width = '100%';
                galleryUploadProgressLabel.textContent = 'Upload complete.';
                if (galleryUploadFeedback) {
                    galleryUploadFeedback.hidden = false;
                    galleryUploadFeedback.className = 'gallery-upload-feedback is-success';
                    galleryUploadFeedback.textContent = payload.message || 'Media uploaded successfully.';
                }

                window.setTimeout(() => {
                    window.location.href = payload.redirect || '/MUBUGA-TSS/admin/dashboard.php#gallery-panel';
                }, 500);
            });

            request.addEventListener('error', () => {
                if (submitButton) {
                    submitButton.disabled = false;
                }
                galleryUploadProgressBar.style.width = '100%';
                galleryUploadProgressLabel.textContent = 'Upload failed.';
                if (galleryUploadFeedback) {
                    galleryUploadFeedback.hidden = false;
                    galleryUploadFeedback.className = 'gallery-upload-feedback is-error';
                    galleryUploadFeedback.textContent = 'A network error interrupted the upload. Please try again.';
                }
            });

            request.send(formData);
        });
    }

    if (window.location.hash === '#gallery-panel' && window.location.search.includes('edit=gallery')) {
        openModal('gallery-upload-modal');
    }

    const requestedMediaType = currentSearchParams.get('media_new');
    if (requestedMediaType && window.location.hash === '#gallery-panel') {
        const mediaTypeSelect = document.querySelector('#gallery-upload-modal select[name="media_type"]');
        if (mediaTypeSelect && (requestedMediaType === 'image' || requestedMediaType === 'video')) {
            mediaTypeSelect.value = requestedMediaType;
        }
        openModal('gallery-upload-modal');
    }

    if (notificationMenu && notificationTrigger && notificationDropdown) {
        notificationTrigger.addEventListener('click', () => {
            const isHidden = notificationDropdown.hidden;
            notificationDropdown.hidden = !isHidden;
        });

        document.addEventListener('click', (event) => {
            if (!notificationMenu.contains(event.target)) {
                notificationDropdown.hidden = true;
            }
        });
    }

    if (searchInput && searchResults) {
        const dataElement = document.getElementById('dashboard-search-data');
        let searchData = [];

        if (dataElement && dataElement.textContent) {
            try {
                searchData = JSON.parse(dataElement.textContent);
            } catch (error) {
                searchData = [];
            }
        }

        const renderSearchResults = (query) => {
            const normalized = query.trim().toLowerCase();

            if (normalized.length < 2) {
                searchResults.hidden = true;
                searchResults.innerHTML = '';
                return;
            }

            const matches = searchData
                .filter((item) => item.text.includes(normalized))
                .slice(0, 8);

            if (matches.length === 0) {
                searchResults.hidden = false;
                searchResults.innerHTML = '<div class="notification-empty">No matches found.</div>';
                return;
            }

            searchResults.hidden = false;
            searchResults.innerHTML = matches.map((item) => `
                <a href="${escapeHtml(item.link)}" class="dashboard-search-result">
                    <strong>${escapeHtml(item.label)}</strong>
                    <small>${escapeHtml(item.section)}${item.meta ? ` • ${escapeHtml(item.meta)}` : ''}</small>
                </a>
            `).join('');
        };

        searchInput.addEventListener('input', (event) => {
            renderSearchResults(event.target.value || '');
        });

        document.addEventListener('click', (event) => {
            if (!searchResults.contains(event.target) && event.target !== searchInput) {
                searchResults.hidden = true;
            }
        });
    }
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
