<div id="editMemberModal{{ $member->id }}" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full backdrop-blur-sm bg-gray-900/50">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded-xl shadow-2xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between p-4 md:p-5 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-t-xl">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Edit Member: {{ $member->name }}
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white transition-colors" data-modal-toggle="editMemberModal{{ $member->id }}">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            @php
                $currentUser = Auth::user();
            @endphp
            <form class="p-4 md:p-5" action="{{ route('company-members.update', $member->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="grid gap-4 mb-4 grid-cols-2">
                    <div class="col-span-2" x-data="{
                        isDragging: false,
                        hasImage: {{ $member->avatar ? 'true' : 'false' }},
                        imagePreview: '{{ $member->avatar ? asset('storage/' . $member->avatar) : '' }}',
                        fileName: '',
                        maxSize: 2 * 1024 * 1024, // 2MB

                        init() {
                            // Make the entire modal content area focusable for paste events
                            const modal = this.$el.closest('#editMemberModal{{ $member->id }}');
                            if (modal) {
                                modal.addEventListener('paste', (e) => {
                                    // Only handle paste if the focus is within our upload area
                                    if (document.activeElement === modal || modal.contains(document.activeElement)) {
                                        this.handlePaste(e);
                                    }
                                });
                            }
                        },

                        handleDragOver(event) {
                            event.preventDefault();
                            this.isDragging = true;
                        },

                        handleDragLeave(event) {
                            event.preventDefault();
                            this.isDragging = false;
                        },

                        handleDrop(event) {
                            event.preventDefault();
                            this.isDragging = false;

                            const files = event.dataTransfer.files;
                            if (files.length > 0) {
                                this.processFile(files[0]);
                            }
                        },

                        handlePaste(event) {
                            const items = event.clipboardData?.items;
                            if (!items) return;

                            for (let item of items) {
                                if (item.type.startsWith('image/')) {
                                    event.preventDefault();
                                    const file = item.getAsFile();
                                    this.processFile(file);
                                    break;
                                }
                            }
                        },

                        handleFileSelect(event) {
                            const file = event.target.files[0];
                            if (file) {
                                this.processFile(file);
                            }
                        },

                        processFile(file) {
                            // Validate file type
                            if (!file.type.startsWith('image/')) {
                                alert('Please select an image file');
                                return;
                            }

                            // Validate file size
                            if (file.size > this.maxSize) {
                                alert('Image size must be less than 2MB');
                                return;
                            }

                            // Create preview
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.imagePreview = e.target.result;
                                this.hasImage = true;
                                this.fileName = file.name;
                            };
                            reader.readAsDataURL(file);

                            // Update the hidden file input
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            this.$refs.fileInput.files = dataTransfer.files;
                        },

                        removeImage(event) {
                            event.stopPropagation();
                            this.hasImage = false;
                            this.imagePreview = '';
                            this.fileName = '';
                            this.$refs.fileInput.value = '';
                        }
                    }">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Profile Avatar</label>

                        <!-- Modern Image Upload Area -->
                        <div class="relative">
                            <!-- Layout when no image uploaded -->
                            <div x-show="!hasImage" class="relative">
                                <div
                                    @drop="handleDrop($event)"
                                    @dragover.prevent="handleDragOver($event)"
                                    @dragleave.prevent="handleDragLeave($event)"
                                    @paste="handlePaste($event)"
                                    @click="$refs.fileInput.click()"
                                    class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-200 cursor-pointer bg-gray-50 dark:bg-gray-700/50 group"
                                    :class="{ 'border-blue-500 bg-blue-50 dark:bg-blue-900/20': isDragging }">

                                    <!-- Upload Icon -->
                                    <div class="mx-auto mb-4" :class="{ 'scale-110': isDragging }" x-transition>
                                        <div class="w-16 h-16 mx-auto bg-gradient-to-br from-blue-500 to-olive-600 rounded-full flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-200">
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Upload Text -->
                                    <div class="space-y-2">
                                        <p class="text-lg font-medium text-gray-700 dark:text-gray-300">
                                            Drop your image here or click to browse
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            PNG, JPG, GIF up to 2MB • You can also paste from clipboard
                                        </p>
                                    </div>

                                    <!-- Drag Overlay -->
                                    <div
                                        x-show="isDragging"
                                        x-transition
                                        class="absolute inset-0 bg-blue-500/10 dark:bg-blue-500/20 border-2 border-blue-500 rounded-xl flex items-center justify-center pointer-events-none">
                                        <div class="text-blue-600 dark:text-blue-400 font-medium">
                                            Drop your image here...
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Layout when image is uploaded -->
                            <div x-show="hasImage" x-transition class="flex items-center gap-4">
                                <!-- Uploaded Image Preview -->
                                <div class="relative flex-shrink-0">
                                    <img
                                        :src="imagePreview"
                                        alt="Avatar preview"
                                        class="w-20 h-20 rounded-full object-cover border-3 border-white dark:border-gray-800 shadow-lg">
                                    <button
                                        @click="removeImage($event)"
                                        type="button"
                                        class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Smaller Upload Box for replacement -->
                                <div class="flex-1">
                                    <div
                                        @drop="handleDrop($event)"
                                        @dragover.prevent="handleDragOver($event)"
                                        @dragleave.prevent="handleDragLeave($event)"
                                        @paste="handlePaste($event)"
                                        @click="$refs.fileInput.click()"
                                        class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-3 text-center hover:border-blue-400 dark:hover:border-blue-500 transition-all duration-200 cursor-pointer bg-gray-50 dark:bg-gray-700/50 group"
                                        :class="{ 'border-blue-500 bg-blue-50 dark:bg-blue-900/20': isDragging }">

                                        <!-- Small Upload Icon -->
                                        <div class="mx-auto mb-2" :class="{ 'scale-110': isDragging }" x-transition>
                                            <div class="w-8 h-8 mx-auto bg-gradient-to-br from-blue-500 to-olive-600 rounded-full flex items-center justify-center shadow-md group-hover:shadow-lg transition-all duration-200">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Upload Text -->
                                        <div class="space-y-1">
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Replace image
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Click or drag new image
                                            </p>
                                        </div>

                                        <!-- Drag Overlay for small box -->
                                        <div
                                            x-show="isDragging"
                                            x-transition
                                            class="absolute inset-0 bg-blue-500/10 dark:bg-blue-500/20 border-2 border-blue-500 rounded-lg flex items-center justify-center pointer-events-none">
                                            <div class="text-blue-600 dark:text-blue-400 font-medium text-xs">
                                                Drop here...
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="fileName"></p>
                                </div>
                            </div>

                            <!-- Hidden File Input -->
                            <input
                                x-ref="fileInput"
                                type="file"
                                name="avatar"
                                accept="image/*"
                                @change="handleFileSelect($event)"
                                class="hidden">
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ $member->name }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required="">
                    </div>

                    <div class="col-span-2">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ $member->email }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required="">
                    </div>

                    {{-- Phone removed to match Add Member modal --}}
                    {{-- <div class="col-span-2 sm:col-span-1">
                        <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone</label>
                        <input type="text" name="phone" id="phone" value="{{ $member->phone }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="+1234567890">
                    </div> --}}

                    <div class="col-span-2 sm:col-span-1">
                        <label for="employee_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Employee ID</label>
                        <input type="text" name="employee_id" id="employee_id" value="{{ $member->pivot->employee_id }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="EMP-001">
                    </div>

                    <div class="col-span-2 sm:col-span-1" x-data="{
                        search: '',
                        open: false,
                        selected: {{ json_encode($member->roles->pluck('name')) }},
                        options: [
                            @foreach($roles as $role)
                                { value: '{{ $role->name }}', label: '{{ ucfirst(str_replace('_', ' ', $role->name)) }}' },
                            @endforeach
                        ],
                        get selectedLabels() {
                            if (this.selected.length === 0) return 'Select roles...';
                            return this.selected.map(v => this.options.find(o => o.value === v)?.label).join(', ');
                        },
                        toggle(value) {
                            if (this.selected.includes(value)) {
                                this.selected = this.selected.filter(v => v !== value);
                            } else {
                                this.selected.push(value);
                            }
                        }
                    }">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Roles</label>
                        <div class="relative" @click.away="open = false">
                            <button type="button" @click="open = !open" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 text-left dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 min-h-[42px] items-center justify-between">
                                <span x-text="selectedLabels" class="block truncate mr-2"></span>
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <div x-show="open" class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto" style="display: none;">
                                <div class="p-2 sticky top-0 bg-white dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                    <input x-model="search" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Search roles...">
                                </div>
                                <div class="p-1">
                                    <template x-for="option in options.filter(o => o.label.toLowerCase().includes(search.toLowerCase()))" :key="option.value">
                                        <div @click="toggle(option.value)" class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" :value="option.value" :checked="selected.includes(option.value)" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 pointer-events-none">
                                            </div>
                                            <div class="ms-2 text-sm">
                                                <label class="font-medium text-gray-900 dark:text-gray-300 select-none" x-text="option.label"></label>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="options.filter(o => o.label.toLowerCase().includes(search.toLowerCase())).length === 0" class="p-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                                        No roles found
                                    </div>
                                </div>
                            </div>
                            <!-- Hidden inputs for form submission -->
                            <select name="roles[]" multiple class="sr-only" x-model="selected">
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="joined_at" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Joining Date</label>
                        <input type="date" name="joined_at" id="joined_at" value="{{ $member->pivot->joined_at ? \Carbon\Carbon::parse($member->pivot->joined_at)->format('Y-m-d') : '' }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
                        <select name="is_active" id="is_active" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="1" {{ $member->pivot->is_active ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$member->pivot->is_active ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">New Password (Optional)</label>
                        <input type="password" name="password" id="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Leave blank to keep current password">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" class="text-gray-700 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 hover:bg-gray-50 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600 transition-colors" data-modal-toggle="editMemberModal{{ $member->id }}">
                        Cancel
                    </button>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-colors shadow-sm">
                        Update Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
