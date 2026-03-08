<x-dashboard.layout.default title="Edit Workspace">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('tenant.user-companies.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                Workspaces
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-900 md:ml-2 dark:text-white">Edit</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Workspace</h1>
                <p class="mt-2 text-lg text-gray-500 dark:text-gray-400">Update your company details and settings.</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <form action="{{ route('tenant.user-companies.update', $company->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="p-8 space-y-8">

                        <!-- Logo Section -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-4">Workspace Logo</label>
                            <div class="flex items-center gap-6">
                                <div class="shrink-0 relative group">
                                    <div class="h-24 w-24 rounded-2xl overflow-hidden ring-1 ring-gray-200 dark:ring-gray-700 bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                                        <img id="logo-preview" class="h-full w-full object-cover" src="{{ $company->logo ? asset('storage/'.$company->logo) : asset('assets/images/app-logo.jpeg') }}" alt="Logo preview">
                                    </div>
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl cursor-pointer pointer-events-none">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div id="logo-dropzone" class="flex justify-center rounded-lg border border-dashed border-gray-300 dark:border-gray-600 px-6 py-8 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors relative cursor-pointer">
                                        <div class="text-center">
                                            <svg class="mx-auto h-10 w-10 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                            </svg>
                                            <div class="mt-4 flex text-sm leading-6 text-gray-600 dark:text-gray-400 justify-center">
                                                <label for="logo" class="relative cursor-pointer rounded-md bg-white dark:bg-gray-800 font-semibold text-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-600 focus-within:ring-offset-2 hover:text-blue-500">
                                                    <span>Upload a file</span>
                                                    <input id="logo" name="logo" type="file" class="sr-only" accept="image/*">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs leading-5 text-gray-500 dark:text-gray-500 mt-1">PNG, JPG, GIF up to 2MB</p>
                                            <p class="text-xs leading-5 text-blue-500 dark:text-blue-400 mt-2">💡 You can also paste an image with Ctrl+V</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Info -->
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Company Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 py-2.5 px-3"
                                    placeholder="e.g. Acme Corp">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Email Address</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $company->email) }}"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 py-2.5 px-3"
                                        placeholder="info@company.com">
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Phone Number</label>
                                    <input type="text" name="phone" id="phone" value="{{ old('phone', $company->phone) }}"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 py-2.5 px-3"
                                        placeholder="+1 (555) 000-0000">
                                </div>

                                <div>
                                    <label for="bin_no" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">BIN No.</label>
                                    <input type="text" name="bin_no" id="bin_no" value="{{ old('bin_no', $company->bin_no) }}"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 py-2.5 px-3"
                                        placeholder="Enter BIN number">
                                </div>

                                <div>
                                    <label for="website" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Website</label>
                                    <input type="url" name="website" id="website" value="{{ old('website', $company->website) }}"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 py-2.5 px-3"
                                        placeholder="https://www.example.com">
                                </div>
                            </div>

                            <div>
                                <label for="address" class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Address</label>
                                <textarea id="address" name="address" rows="3"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 py-2.5 px-3"
                                    placeholder="123 Business St, City, Country">{{ old('address', $company->address) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 dark:bg-gray-700/30 px-8 py-5 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('tenant.user-companies.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors shadow-sm">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-colors">
                            Update Workspace
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Logo upload functionality
        const dropzone = document.getElementById('logo-dropzone');
        const fileInput = document.getElementById('logo');
        const logoPreview = document.getElementById('logo-preview');

        // Make entire dropzone clickable
        dropzone.addEventListener('click', function(e) {
            // Prevent click if clicking on the label (which already triggers the file input)
            if (!e.target.closest('label')) {
                e.preventDefault();
                fileInput.click();
            }
        });

        // Regular file input change
        fileInput.addEventListener('change', function(e) {
            handleFileSelect(e.target.files[0]);
        });

        // Drag and drop functionality
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        });

        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });

        // Paste functionality
        document.addEventListener('paste', function(e) {
            const items = e.clipboardData.items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    e.preventDefault();
                    const blob = items[i].getAsFile();
                    handleFileSelect(blob);
                    break;
                }
            }
        });

        // File handling function
        function handleFileSelect(file) {
            if (!file) return;

            // Validate file type
            if (!file.type.startsWith('image/')) {
                showNotification('Please select an image file (PNG, JPG, GIF)', 'error');
                return;
            }

            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                showNotification('File size must be less than 2MB', 'error');
                return;
            }

            // Update file input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;

            // Preview image
            const reader = new FileReader();
            reader.onload = function(e) {
                logoPreview.src = e.target.result;
                showNotification('Logo uploaded successfully!', 'success');
            };
            reader.readAsDataURL(file);
        }

        // Notification function
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            const existing = document.querySelector('.upload-notification');
            if (existing) {
                existing.remove();
            }

            // Create notification element
            const notification = document.createElement('div');
            notification.className = `upload-notification fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2 transition-all duration-300 transform translate-x-full`;

            // Set colors based on type
            if (type === 'success') {
                notification.classList.add('bg-green-500', 'text-white');
            } else if (type === 'error') {
                notification.classList.add('bg-red-500', 'text-white');
            } else {
                notification.classList.add('bg-blue-500', 'text-white');
            }

            // Add icon
            const icon = document.createElement('svg');
            icon.className = 'w-5 h-5';
            icon.setAttribute('fill', 'currentColor');
            icon.setAttribute('viewBox', '0 0 20 20');

            if (type === 'success') {
                icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>';
            } else if (type === 'error') {
                icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>';
            } else {
                icon.innerHTML = '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>';
            }

            // Add text
            const text = document.createElement('span');
            text.textContent = message;

            notification.appendChild(icon);
            notification.appendChild(text);

            // Add to page
            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
                notification.classList.add('translate-x-0');
            }, 10);

            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.classList.remove('translate-x-0');
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // Legacy previewImage function for compatibility
        function previewImage(input) {
            if (input.files && input.files[0]) {
                handleFileSelect(input.files[0]);
            }
        }
    </script>
</x-dashboard.layout.default>
