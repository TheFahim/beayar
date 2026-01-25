<script>
    // Shared Image Library Modal Component
    function imageLibraryModal() {
        return {
            open: false,
            query: '',
            images: [],
            page: 1,
            loading: false,
            hasMore: true,
            observer: null,
            sentinelId: 'modal-scroll-sentinel',

            init() {
                this._openHandler = () => {
                    this.open = true;
                    this.page = 1;
                    this.images = [];
                    this.hasMore = true;
                    this.$nextTick(() => {
                        this.fetchImages().then(() => this.setupObserver());
                    });
                };
                window.addEventListener('open-image-library', this._openHandler);
            },

            destroy() {
                window.removeEventListener('open-image-library', this._openHandler);
                this.disconnectObserver();
            },

            close() {
                this.open = false;
                this.query = '';
                this.page = 1;
                this.images = [];
                this.hasMore = true;
                this.disconnectObserver();
            },

            async search() {
                this.page = 1;
                this.images = [];
                this.hasMore = true;
                this.disconnectObserver();
                await this.fetchImages();
                this.setupObserver();
            },

            async loadMore() {
                if (this.loading || !this.hasMore) return;
                this.page++;
                await this.fetchImages();
            },

            async fetchImages() {
                this.loading = true;
                try {
                    const url = new URL("{{ route('images.search') }}", window.location.origin);
                    url.searchParams.set('query', this.query || '');
                    url.searchParams.set('page', this.page);

                    const res = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (!res.ok) {
                        console.error('Image search failed', res.status, await res.text());
                        return;
                    }

                    const payload = await res.json();

                    if (payload.data && Array.isArray(payload.data)) {
                        const incoming = payload.data.map(img => ({
                            id: img.id,
                            name: img.name,
                            original_name: img.original_name ?? img.file_name ?? '',
                            path: img.path && (img.path.startsWith('http') ? img.path : (
                                `${window.location.origin}/${img.path.replace(/^\/+/,'')}`))
                        }));

                        if (this.page === 1) {
                            this.images = incoming;
                        } else {
                            this.images.push(...incoming);
                        }

                        this.hasMore = (payload.current_page ?? this.page) < (payload.last_page ?? (payload
                            .current_page ?? this.page));
                    } else {
                        this.hasMore = false;
                    }
                } catch (err) {
                    console.error(err);
                } finally {
                    this.loading = false;
                }
            },

            setupObserver() {
                this.disconnectObserver();

                const gridContainer = document.getElementById('modal-image-grid-container');
                const grid = document.getElementById('modal-image-grid');
                if (!gridContainer || !grid) return;

                this.removeSentinel();
                const sentinel = document.createElement('div');
                sentinel.id = this.sentinelId;
                grid.appendChild(sentinel);

                this.observer = new IntersectionObserver(entries => {
                    if (entries[0] && entries[0].isIntersecting && !this.loading && this.hasMore) {
                        this.loadMore();
                    }
                }, {
                    root: gridContainer,
                    rootMargin: '200px',
                });

                this.observer.observe(sentinel);
            },

            disconnectObserver() {
                if (this.observer) {
                    this.observer.disconnect();
                    this.observer = null;
                }
                this.removeSentinel();
            },

            removeSentinel() {
                const old = document.getElementById(this.sentinelId);
                if (old) old.remove();
            },

            select(image) {
                if (typeof window.__selectImageCallback === 'function') {
                    window.__selectImageCallback(image);
                }
                this.close();
            }
        }
    }

    // Shared Image Utilities
    const ImageUtils = {
        validateFile(file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                throw new Error('Invalid file type. Please select a JPEG, PNG, WebP, or GIF file.');
            }
            const maxFileSize = 50 * 1024 * 1024; // 50MB
            if (file.size > maxFileSize) {
                throw new Error(`Image is too large to process. Maximum size is ${maxFileSize / 1024 / 1024}MB.`);
            }
        },

        async compressImage(file) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                const objectUrl = URL.createObjectURL(file);

                img.onload = async () => {
                    try {
                        const maxWidth = 1920;
                        const maxHeight = 1080;
                        const quality = 0.8;

                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        const {
                            width,
                            height
                        } = this.calculateDimensions(img.naturalWidth, img.naturalHeight,
                            maxWidth, maxHeight);
                        canvas.width = width;
                        canvas.height = height;
                        ctx.drawImage(img, 0, 0, width, height);

                        // Try to convert to WebP first, then JPEG, then fallback to original type
                        const originalType = file.type;
                        let blob = null;
                        let outputType = 'image/webp';
                        let ext = 'webp';

                        // Try WebP
                        blob = await new Promise(res => canvas.toBlob(res, 'image/webp',
                            quality));

                        // If WebP fails or is larger than JPEG (for non-PNGs), try JPEG
                        if (!blob || (originalType !== 'image/png' && blob.size > file.size)) {
                            outputType = 'image/jpeg';
                            ext = 'jpg';
                            blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg',
                                quality));
                        }

                        // If all else fails, use the original file
                        if (!blob || blob.size > file.size) {
                            resolve(file);
                            return;
                        }

                        const baseName = file.name.replace(/\.[^/.]+$/, '');
                        const newName = `${baseName}.${ext}`;
                        const compressedFile = new File([blob], newName, {
                            type: outputType,
                            lastModified: Date.now()
                        });

                        // Ensure the compressed file is not larger than the original
                        resolve(compressedFile.size < file.size ? compressedFile : file);
                    } catch (err) {
                        reject(err);
                    } finally {
                        URL.revokeObjectURL(objectUrl);
                    }
                };

                img.onerror = () => {
                    URL.revokeObjectURL(objectUrl);
                    reject(new Error('Failed to load image for compression.'));
                };

                img.src = objectUrl;
            });
        },

        calculateDimensions(originalWidth, originalHeight, maxWidth, maxHeight) {
            let width = originalWidth;
            let height = originalHeight;

            if (width > maxWidth || height > maxHeight) {
                const widthRatio = maxWidth / width;
                const heightRatio = maxHeight / height;
                const ratio = Math.min(widthRatio, heightRatio);
                width = Math.floor(width * ratio);
                height = Math.floor(height * ratio);
            }
            return {
                width,
                height
            };
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    };

    /**
     * Robust Cleanup for Image Upload Modal
     * Handles clearing of state, DOM elements, and listeners
     */
    window.cleanupUploadModal = function() {
        // 1. Clear all file inputs
        const fileInputs = document.querySelectorAll('input[type="file"]');
        const dataTransfer = new DataTransfer();

        fileInputs.forEach(input => {
            // Only target upload modal inputs (check ID or context)
            if (input.id.includes('upload') || input.closest('[x-show*="showUploadModal"]')) {
                input.value = '';
                try {
                    input.files = dataTransfer.files;
                } catch (e) {
                    console.error('Failed to clear file input:', e);
                }
            }
        });

        // 2. Reset all dragableImage components
        const dragableComponents = document.querySelectorAll('[x-data*="dragableImage"]');
        dragableComponents.forEach(component => {
            if (component.__x) {
                const data = component.__x.$data;

                // Use standardized clearState if available
                if (typeof data.clearState === 'function') {
                    data.clearState();
                } else {
                    // Fallback manual clear
                    data.files = [];
                    data.isDragging = false;
                    if (data.inputElement) {
                        data.inputElement.value = '';
                        try {
                            data.inputElement.files = dataTransfer.files;
                        } catch(e) {}
                    }
                }
            }
        });

        // 3. Clear any standalone preview images
        const previews = document.querySelectorAll('.upload-preview-image, [id*="preview"] img');
        previews.forEach(img => {
            if (img.closest('[x-show*="showUploadModal"]')) {
                img.src = '';
                img.remove();
            }
        });

        // 4. Reset any progress bars
        const progressBars = document.querySelectorAll('.upload-progress-bar');
        progressBars.forEach(bar => {
            bar.style.width = '0%';
            bar.setAttribute('aria-valuenow', '0');
        });

        // 5. Dispatch cleanup complete event
        window.dispatchEvent(new CustomEvent('upload-modal-cleaned'));
    };

    // Listen for the open trigger
    window.addEventListener('trigger-upload-modal', function(e) {
        window.cleanupUploadModal();
    });
</script>
