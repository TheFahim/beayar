<x-dashboard.layout.default title="Edit Profile">
    <div class="max-w-4xl mx-auto py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('tenant.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <x-ui.svg.dashboard class="w-4 h-4 mr-2" />
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <x-ui.svg.right-arrow class="w-4 h-4 text-gray-400 mx-1" />
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Profile Settings</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Personal Information</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update your photo and personal details.</p>
            </div>

            <form action="{{ route('tenant.profile.update') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-8">
                @csrf
                @method('PUT')

                <!-- Avatar Upload Section -->
                <div class="flex flex-col md:flex-row gap-8 items-start">
                    <div class="w-full md:w-1/3 flex flex-col items-center">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2 self-start md:self-center">Profile Photo</label>
                        <div class="mt-1 flex flex-col items-center gap-4">
                            <div id="drop-zone" class="relative group cursor-pointer" onclick="document.getElementById('avatar-upload').click()">
                                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-gray-100 dark:border-gray-700 shadow-sm relative bg-gray-50 dark:bg-gray-700 transition-all duration-200 hover:border-blue-300 dark:hover:border-blue-600" id="avatar-container">
                                    @if($user->avatar)
                                        <img id="avatar-preview" src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                        <div id="avatar-placeholder" class="hidden w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                    @else
                                        <div id="avatar-placeholder" class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                            <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <img id="avatar-preview" src="#" alt="Preview" class="w-full h-full object-cover hidden">
                                    @endif

                                    <div id="avatar-drop-overlay" class="absolute inset-0 bg-blue-500 bg-opacity-20 rounded-full flex items-center justify-center opacity-0 transition-opacity pointer-events-none">
                                        <div class="text-center">
                                            <x-ui.svg.upload class="w-8 h-8 text-blue-600 mx-auto mb-1" />
                                            <p class="text-xs font-medium text-blue-600">Drop image here</p>
                                        </div>
                                    </div>
                                    <div class="absolute inset-0 bg-black bg-opacity-40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                        <x-ui.svg.upload class="w-8 h-8 text-white" />
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <input type="file" name="avatar" id="avatar-upload" class="hidden" accept="image/*" onchange="previewImage(this)">
                                <button type="button" onclick="document.getElementById('avatar-upload').click()" class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                                    Change photo
                                </button>
                                <p class="text-xs text-gray-500 mt-1">JPG, GIF or PNG. Max 2MB.</p>
                                <p class="text-xs text-gray-400 mt-1">Drag & drop or paste image</p>
                            </div>
                        </div>
                    </div>

                    <div class="w-full md:w-2/3 space-y-6">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Full Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" required>
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Phone Number</label>
                                <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('tenant.profile.show') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-sm">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    var preview = document.getElementById('avatar-preview');
                    var placeholder = document.getElementById('avatar-placeholder');

                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if (placeholder) {
                        placeholder.classList.add('hidden');
                    }
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        // Drag and Drop functionality
        const dropZone = document.getElementById('drop-zone');
        const avatarContainer = document.getElementById('avatar-container');
        const dropOverlay = document.getElementById('avatar-drop-overlay');
        const fileInput = document.getElementById('avatar-upload');

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        // Highlight drop zone when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        // Handle dropped files
        dropZone.addEventListener('drop', handleDrop, false);

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight(e) {
            avatarContainer.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
            avatarContainer.classList.remove('border-gray-100', 'dark:border-gray-700', 'bg-gray-50', 'dark:bg-gray-700');
            dropOverlay.classList.remove('opacity-0');
            dropOverlay.classList.add('opacity-100');
        }

        function unhighlight(e) {
            avatarContainer.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
            avatarContainer.classList.add('border-gray-100', 'dark:border-gray-700', 'bg-gray-50', 'dark:bg-gray-700');
            dropOverlay.classList.remove('opacity-100');
            dropOverlay.classList.add('opacity-0');
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    fileInput.files = files;
                    previewImage(fileInput);
                } else {
                    alert('Please drop an image file (JPG, GIF or PNG).');
                }
            }
        }

        // Paste functionality
        document.addEventListener('paste', function(e) {
            const items = e.clipboardData.items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const blob = items[i].getAsFile();
                    if (blob) {
                        const file = new File([blob], 'pasted-image.png', { type: blob.type });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        fileInput.files = dataTransfer.files;
                        previewImage(fileInput);

                        // Show success feedback
                        const originalBorder = avatarContainer.className;
                        avatarContainer.classList.add('ring-4', 'ring-green-400', 'ring-opacity-50');
                        setTimeout(() => {
                            avatarContainer.className = originalBorder;
                        }, 1000);
                    }
                    break;
                }
            }
        });

        // Add visual feedback for paste
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'v') {
                avatarContainer.classList.add('ring-2', 'ring-blue-400', 'ring-opacity-50');
            }
        });

        document.addEventListener('keyup', function(e) {
            if (e.key === 'v') {
                avatarContainer.classList.remove('ring-2', 'ring-blue-400', 'ring-opacity-50');
            }
        });
    </script>
</x-dashboard.layout.default>
