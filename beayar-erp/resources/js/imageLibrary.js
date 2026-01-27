import Swal from 'sweetalert2';

document.addEventListener('alpine:init', () => {
    Alpine.data('imageLibrary', () => ({
        // --- STATE MANAGEMENT ---
        images: [],
        loading: false,
        uploading: false,
        compressing: false,
        page: 1,
        hasMore: true,
        searchQuery: '',
        searchTimeout: null,
        observer: null,

        // --- MODAL & PREVIEW ---
        showUploadModal: false,
        showPreviewModal: false,
        previewImage: '',
        imageName: '',

        // --- CONFIGURATION ---
        maxFileSize: 50 * 1024 * 1024, // 50MB max raw size before compression
        maxWidth: 1920,
        maxHeight: 1080,
        quality: 0.8, // Compression quality for WebP/JPEG
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),

        // --- INITIALIZATION ---
        init() {
            // Get initial images loaded by the server
            const gridElement = document.getElementById('image-grid');
            if (gridElement) {
                this.images = Array.from(gridElement.querySelectorAll('.image-item')).map(el => ({
                    id: el.dataset.id
                }));
            }
            // Initialize the infinite scroll observer
            this.setupInfiniteScroll();
        },

        // --- INFINITE SCROLL (using IntersectionObserver) ---
        setupInfiniteScroll() {
            const grid = document.querySelector('#image-grid');
            if (!grid) return;

            // Initialize the observer to watch for the sentinel element
            this.observer = new IntersectionObserver(entries => {
                if (entries[0]?.isIntersecting && !this.loading && this.hasMore) {
                    this.loadMoreImages();
                }
            }, {
                root: null, // viewport
                rootMargin: '200px', // trigger when 200px from the bottom
                threshold: 0.1
            });

            // Create and observe the sentinel element
            this.createAndObserveSentinel(grid);
        },

        createAndObserveSentinel(gridElement) {
            if (!gridElement) return;

            // Remove any old sentinel to avoid duplicates
            const existingSentinel = document.getElementById('scroll-sentinel');
            if (existingSentinel) {
                if (this.observer) this.observer.unobserve(existingSentinel);
                existingSentinel.remove();
            }

            // Create a new sentinel, append it, and observe it
            const sentinel = document.createElement('div');
            sentinel.id = 'scroll-sentinel';
            gridElement.appendChild(sentinel);

            if (this.observer) {
                this.observer.observe(sentinel);
            }
        },

        // --- DATA FETCHING & MANIPULATION ---
        async loadMoreImages() {
            if (this.loading || !this.hasMore) return;

            this.loading = true;
            this.page++;

            try {
                const params = new URLSearchParams({
                    page: this.page,
                    search: this.searchQuery
                });

                const response = await fetch(`${window.location.pathname}?${params}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.html) {
                    const gridElement = document.getElementById('image-grid');
                    const sentinel = document.getElementById('scroll-sentinel');
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;

                    // Append new items before the sentinel for efficiency
                    if (sentinel && gridElement) {
                        while (tempDiv.firstChild) {
                            gridElement.insertBefore(tempDiv.firstChild, sentinel);
                        }
                    }

                    this.hasMore = data.hasMore;

                    // If there are no more pages, disconnect the observer to save resources
                    if (!this.hasMore && this.observer && sentinel) {
                        this.observer.unobserve(sentinel);
                        sentinel.remove();
                    }
                }
            } catch (error) {
                console.error('Error loading more images:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load more images. Please try again later.',
                    timer: 3000,
                    showConfirmButton: false
                });
            } finally {
                this.loading = false;
            }
        },

        async searchImages() {
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            // Debounce the search input to avoid rapid API calls
            this.searchTimeout = setTimeout(async () => {
                this.loading = true;
                this.page = 1;
                this.hasMore = true;

                try {
                    const params = new URLSearchParams({
                        page: 1,
                        search: this.searchQuery
                    });

                    const response = await fetch(`${window.location.pathname}?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.html) {
                        const gridElement = document.getElementById('image-grid');
                        gridElement.innerHTML = data.html; // Replace content with search results

                        // Re-create the sentinel for the new content
                        this.createAndObserveSentinel(gridElement);

                        this.hasMore = data.hasMore;

                        // Update the internal images array
                        this.images = Array.from(gridElement.querySelectorAll('.image-item')).map(el => ({
                            id: el.dataset.id
                        }));
                    }
                } catch (error) {
                    console.error('Error searching images:', error);
                } finally {
                    this.loading = false;
                }
            }, 500); // 500ms debounce delay
        },

        // --- IMAGE UPLOAD & COMPRESSION ---
        async uploadImages() {
            const fileInput = document.getElementById('image-upload');
            const file = fileInput.files?.[0];

            if (!file || !this.imageName.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please provide a name and select an image.',
                    timer: 3000,
                    showConfirmButton: false
                });
                return;
            }

            try {
                // 1. Validate the file before processing
                this.validateFile(file);

                this.uploading = true;
                this.compressing = true;

                Swal.fire({
                    title: 'Processing Image...',
                    text: 'Compressing image for upload, please wait.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => Swal.showLoading()
                });

                // 2. Compress the image
                const compressedFile = await this.compressImage(file);
                this.compressing = false;
                Swal.update({ text: 'Uploading compressed image...' });

                // 3. Upload the compressed file
                const formData = new FormData();
                formData.append('name', this.imageName);
                formData.append('image', compressedFile, compressedFile.name);

                const response = await fetch('/dashboard/images', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: `
                            <p>${data.message}</p>
                            ${data.compression_ratio ? `<small>Compressed by ${data.compression_ratio}</small>` : ''}
                        `,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Reset form state and reload to see new image
                    this.resetUploadForm();
                    setTimeout(() => window.location.reload(), 2000);

                } else {
                    throw new Error(data.message || 'Upload failed due to a server error.');
                }
            } catch (error) {
                console.error('Upload error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Failed',
                    text: error.message || 'An unexpected error occurred.',
                    showConfirmButton: true
                });
            } finally {
                this.uploading = false;
                this.compressing = false;
            }
        },

        validateFile(file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                throw new Error('Invalid file type. Please select a JPEG, PNG, WebP, or GIF file.');
            }
            if (file.size > this.maxFileSize) {
                throw new Error(`Image is too large to process. Maximum size is ${this.maxFileSize / 1024 / 1024}MB.`);
            }
        },

        async compressImage(file) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                const objectUrl = URL.createObjectURL(file);

                img.onload = async () => {
                    try {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        const { width, height } = this.calculateDimensions(img.naturalWidth, img.naturalHeight);
                        canvas.width = width;
                        canvas.height = height;
                        ctx.drawImage(img, 0, 0, width, height);

                        // Try to convert to WebP first, then JPEG, then fallback to original type
                        const originalType = file.type;
                        let blob = null;
                        let outputType = 'image/webp';
                        let ext = 'webp';

                        // Try WebP
                        blob = await new Promise(res => canvas.toBlob(res, 'image/webp', this.quality));

                        // If WebP fails or is larger than JPEG (for non-PNGs), try JPEG
                        if (!blob || (originalType !== 'image/png' && blob.size > file.size)) {
                            outputType = 'image/jpeg';
                            ext = 'jpg';
                            blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg', this.quality));
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

        calculateDimensions(originalWidth, originalHeight) {
            let width = originalWidth;
            let height = originalHeight;

            if (width > this.maxWidth || height > this.maxHeight) {
                const widthRatio = this.maxWidth / width;
                const heightRatio = this.maxHeight / height;
                const ratio = Math.min(widthRatio, heightRatio);
                width = Math.floor(width * ratio);
                height = Math.floor(height * ratio);
            }
            return { width, height };
        },

        resetUploadForm() {
            this.imageName = '';
            const fileInput = document.getElementById('image-upload');
            if (fileInput) fileInput.value = '';
            this.showUploadModal = false;

            // Optional: Clear custom file input UI if you have one
            const uploadComponent = document.querySelector('[x-data="dragableImage"]');
            if (uploadComponent?.__x) {
                uploadComponent.__x.$data.files = [];
            }
        },

        // --- IMAGE DELETION ---
        async deleteImage(imageId) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/dashboard/images/${imageId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        const imageElement = document.querySelector(`.image-item[data-id="${imageId}"]`);
                        if (imageElement) {
                            // Animate out before removing
                            imageElement.style.transition = 'opacity 0.3s, transform 0.3s';
                            imageElement.style.opacity = '0';
                            imageElement.style.transform = 'scale(0.8)';
                            setTimeout(() => {
                                imageElement.remove();
                                this.images = this.images.filter(img => img.id != imageId);
                            }, 300);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: data.message,
                            timer: 500,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error(data.message || 'Deletion failed.');
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Delete Failed',
                        text: error.message || 'Failed to delete the image.',
                        showConfirmButton: true
                    });
                }
            }
        }
    }));

    // Reusable Image Library Modal component (migrated from create view)
    Alpine.data('imageLibraryModal', () => ({
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
                const url = new URL('/dashboard/images/search', window.location.origin);
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

                    this.hasMore = (payload.current_page ?? this.page) < (payload.last_page ?? (payload.current_page ?? this.page));
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
    }));
});
