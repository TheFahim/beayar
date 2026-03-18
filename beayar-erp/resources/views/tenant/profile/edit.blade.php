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

        <!-- Password Change Section -->
        <div class="mt-8 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Password & Security</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your password and account security.</p>
            </div>

            <div class="p-6 md:p-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Password</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Last changed {{ $user->password_changed_at ? $user->password_changed_at->diffForHumans() : 'Never' }}</p>
                    </div>
                    <button type="button" onclick="openPasswordModal()" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        Change Password
                    </button>
                </div>

                @if(session('success'))
                    <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <div class="flex">
                            <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <div class="flex">
                            <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Debug button (remove in production) -->
                @if(config('app.debug'))
                    <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">Debug: Test password modal</p>
                            <button type="button" onclick="openPasswordModal()" class="px-3 py-1 text-xs bg-yellow-600 text-white rounded hover:bg-yellow-700">
                                Open Modal
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Password Verification Modal -->
    <div id="passwordModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closePasswordModal()"></div>

            <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl dark:bg-gray-800">
                <!-- Verification Step -->
                <div id="verificationStep">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-blue-100 rounded-full dark:bg-blue-900">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                        </svg>
                    </div>

                    <h3 class="mt-4 text-lg font-medium text-center text-gray-900 dark:text-white">Verify Your Identity</h3>
                    <p class="mt-2 text-sm text-center text-gray-500 dark:text-gray-400">
                        For security purposes, we need to verify your identity before allowing password changes.
                    </p>

                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $user->email }}</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <form id="verificationForm" action="{{ route('tenant.profile.password.send-verification') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-3 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Verify
                            </button>
                        </form>
                    </div>

                    <div class="mt-4">
                        <button type="button" onclick="closePasswordModal()" class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                    </div>
                </div>

                <!-- Password Change Step (Initially Hidden) -->
                <div id="passwordChangeStep" class="hidden">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 rounded-full dark:bg-green-900">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>

                    <h3 class="mt-4 text-lg font-medium text-center text-gray-900 dark:text-white">Change Your Password</h3>
                    <p class="mt-2 text-sm text-center text-gray-500 dark:text-gray-400">
                        Your identity has been verified. You can now change your password.
                    </p>

                    <form id="passwordChangeForm" action="{{ route('tenant.profile.password.change') }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        <input type="hidden" name="email" value="{{ $user->email }}">
                        <input type="hidden" name="token" id="passwordToken">

                        <div>
                            <label for="modal_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password</label>
                            <input type="password" id="modal_password" name="password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   placeholder="Enter new password" minlength="8">
                        </div>

                        <div>
                            <label for="modal_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New Password</label>
                            <input type="password" id="modal_password_confirmation" name="password_confirmation" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   placeholder="Confirm new password" minlength="8">
                        </div>

                        <button type="submit" class="w-full flex items-center justify-center px-4 py-3 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Change Password
                        </button>
                    </form>

                    <div class="mt-4">
                        <button type="button" onclick="closePasswordModal()" class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
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

        // Password Modal Functions
        function openPasswordModal() {
            document.getElementById('passwordModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            // Reset to verification step
            document.getElementById('verificationStep').classList.remove('hidden');
            document.getElementById('passwordChangeStep').classList.add('hidden');
            // Clear password fields
            document.getElementById('modal_password').value = '';
            document.getElementById('modal_password_confirmation').value = '';
        }

        // Handle verification form submission with AJAX
        document.getElementById('verificationForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;

            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Sending...';

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': formData.get('_token'),
                    'Accept': 'text/html'
                }
            })
            .then(response => response.text())
            .then(data => {
                // Show success message in modal
                const verificationStep = document.getElementById('verificationStep');
                verificationStep.innerHTML = `
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 rounded-full dark:bg-green-900">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>

                    <h3 class="mt-4 text-lg font-medium text-center text-gray-900 dark:text-white">Check Your Email</h3>
                    <p class="mt-2 text-sm text-center text-gray-500 dark:text-gray-400">
                        We've sent a verification link to your email address. Please check your inbox and click the link to continue.
                    </p>

                    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-400 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-xs text-blue-800 dark:text-blue-200">
                                <p class="font-medium mb-1">Next steps:</p>
                                <ul class="space-y-1">
                                    <li>• Open your email inbox</li>
                                    <li>• Find the verification email from Beayar ERP</li>
                                    <li>• Click the "Verify & Change Password" button</li>
                                    <li>• Return to this page to continue</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="button" onclick="closePasswordModal()" class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            Close
                        </button>
                    </div>
                `;

                // Start checking for verification
                checkVerificationStatus();
            })
            .catch(error => {
                console.error('Error:', error);
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
                alert('An error occurred. Please try again.');
            });
        });

        // Check if user has been verified (polling)
        function checkVerificationStatus() {
            console.log('Starting verification status polling...');
            const checkInterval = setInterval(() => {
                fetch('{{ route("tenant.profile.password.check-verification") }}', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Verification check response:', data);
                    if (data.verified && data.token) {
                        clearInterval(checkInterval);
                        console.log('User verified, showing password form');
                        // Show password change form
                        document.getElementById('verificationStep').classList.add('hidden');
                        document.getElementById('passwordChangeStep').classList.remove('hidden');
                        document.getElementById('passwordToken').value = data.token;
                    }
                })
                .catch(error => {
                    console.log('Verification check error:', error);
                    // Silently handle errors, continue checking
                });
            }, 3000); // Check every 3 seconds

            // Stop checking after 5 minutes (300 seconds)
            setTimeout(() => {
                clearInterval(checkInterval);
                console.log('Stopped polling after 5 minutes');
            }, 300000);
        }

        // Check for verification on page load (in case user returns from email)
        window.addEventListener('load', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const verified = urlParams.get('verified');
            const token = urlParams.get('token');

            console.log('Page load - URL params:', { verified, token });

            if (verified === 'true' && token) {
                console.log('Opening password modal with token');

                // Small delay to ensure DOM is ready
                setTimeout(() => {
                    const modal = document.getElementById('passwordModal');
                    const verificationStep = document.getElementById('verificationStep');
                    const passwordChangeStep = document.getElementById('passwordChangeStep');
                    const passwordToken = document.getElementById('passwordToken');

                    console.log('Elements found:', {
                        modal: !!modal,
                        verificationStep: !!verificationStep,
                        passwordChangeStep: !!passwordChangeStep,
                        passwordToken: !!passwordToken
                    });

                    if (modal && verificationStep && passwordChangeStep && passwordToken) {
                        modal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                        verificationStep.classList.add('hidden');
                        passwordChangeStep.classList.remove('hidden');
                        passwordToken.value = token;

                        // Clean up URL
                        window.history.replaceState({}, document.title, window.location.pathname);

                        console.log('Modal opened successfully');
                    } else {
                        console.error('Some elements not found');
                    }
                }, 100);
            } else {
                console.log('No verification parameters found');
            }
        });

        // Handle password change form validation and submission
        document.getElementById('passwordChangeForm').addEventListener('submit', function(e) {
            const password = document.getElementById('modal_password').value;
            const confirmPassword = document.getElementById('modal_password_confirmation').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match. Please try again.');
                return false;
            }

            // If passwords match, use AJAX for submission
            e.preventDefault();

            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;

            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Changing...';

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': formData.get('_token'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.json().then(data => ({ ok: true, data }));
                }
                return response.json().then(data => ({ ok: false, data }));
            })
            .then(result => {
                if (result.ok) {
                    // Close modal
                    closePasswordModal();

                    // Show success message on the page
                    const successDiv = document.createElement('div');
                    successDiv.className = 'mt-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg';
                    successDiv.innerHTML = '<div class="flex"><svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg><p class="text-sm text-green-800 dark:text-green-200">Password changed successfully!</p></div>';

                    // Insert at the top of the container
                    const container = document.querySelector('.max-w-4xl');
                    if (container) {
                        container.insertBefore(successDiv, container.firstChild);
                    }
                } else {
                    alert(result.data.message || 'An error occurred. Please try again.');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    </script>
</x-dashboard.layout.default>
