<div x-show="createProductModal.show" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" @keydown.escape.window="closeCreateProductModal()"
    class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

    <div class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm transition-opacity"
        @click="closeCreateProductModal()"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[95vh] overflow-hidden border-2 border-blue-500/20">

            <!-- Modal Header -->
            <div
                class="px-6 py-4 border-b-2 border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Create New Product</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Add a new product with multiple specifications</p>
                        </div>
                    </div>
                    <button type="button" @click="closeCreateProductModal()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4 max-h-[calc(95vh-160px)] overflow-y-auto bg-gray-50 dark:bg-gray-900/50">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Left Column: Image -->
                        <div class="md:col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                Product Image
                            </label>

                            <div class="space-y-4">
                                <!-- Image Preview -->
                                <div class="relative group aspect-square rounded-2xl overflow-hidden border-2 border-dashed border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 flex items-center justify-center transition-all duration-300 hover:border-blue-400 dark:hover:border-blue-500 shadow-sm">
                                    <template x-if="createProductModal.imageUrl">
                                        <img :src="createProductModal.imageUrl"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                            alt="Selected image">
                                    </template>
                                    <template x-if="!createProductModal.imageUrl">
                                        <div class="flex flex-col items-center justify-center text-gray-400 p-4 text-center">
                                            <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            <span class="text-xs">No image selected</span>
                                        </div>
                                    </template>

                                    <!-- Edit Overlay -->
                                    <div x-show="createProductModal.imageUrl"
                                        class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <button type="button" @click="clearNewProductImage()"
                                            class="p-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors shadow-lg transform hover:scale-110">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="openImageLibraryForNewProduct()"
                                        class="flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors text-xs font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Library
                                    </button>
                                    <button type="button" @click="showUploadImageModal = true"
                                        class="flex items-center justify-center gap-1.5 px-3 py-2 bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-300 rounded-xl hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors text-xs font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Upload
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Details & Specs -->
                        <div class="md:col-span-2 space-y-6">
                            <!-- Product Name -->
                            <div x-data="{ isFocused: false }">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200"
                                    :class="{ 'text-blue-600 dark:text-blue-400': isFocused }">
                                    Product Name <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text" x-model="createProductModal.productName"
                                        @focus="isFocused = true" @blur="isFocused = false"
                                        placeholder="Enter product name..."
                                        class="block w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-4 py-3 transition-all duration-200"
                                        required>
                                    <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-400 to-purple-500 opacity-0 -z-10 blur transition-opacity duration-200"
                                        :class="{ 'opacity-20': isFocused }"></div>
                                </div>
                                <p x-show="createProductModal.errors.productName" x-text="createProductModal.errors.productName"
                                    class="mt-1 text-xs text-red-600 animate-shake"></p>
                            </div>

                            <!-- Specifications Section -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        Specifications
                                    </label>
                                    <button type="button" @click="addModalSpec()"
                                        class="flex items-center gap-1 px-3 py-1.5 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 text-xs font-bold shadow-md hover:shadow-lg transition-all transform hover:scale-105 active:scale-95">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add New
                                    </button>
                                </div>

                                <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                    <template x-for="(spec, specIdx) in createProductModal.specifications" :key="spec.key">
                                        <div x-data="{ specHovered: false }" @mouseenter="specHovered = true" @mouseleave="specHovered = false"
                                            class="relative p-4 bg-white dark:bg-gray-800 rounded-xl border-2 transition-all duration-300 group"
                                            :class="specHovered ? 'border-blue-400 dark:border-blue-500 shadow-md ring-2 ring-blue-50' : 'border-gray-200 dark:border-gray-700'">

                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"
                                                    x-text="`Specification #${specIdx + 1}`"></span>
                                                <button type="button" @click="removeModalSpec(specIdx)" x-show="createProductModal.specifications.length > 1"
                                                    class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <textarea :id="`spec-textarea-${specIdx}`" x-model="spec.description"
                                                class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white transition-all resize-none"
                                                rows="3" placeholder="Enter specification details..."
                                                x-init="$nextTick(() => { if (window.sunEditorUtils) window.sunEditorUtils.initializeEditors(); })"></textarea>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Creation Status Messages -->
                    <div x-show="createProductModal.errorMessage"
                        class="p-4 bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded-xl animate-shake">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-red-800 dark:text-red-200">Error Occurred</h4>
                                <p class="text-xs text-red-700 dark:text-red-300 mt-1" x-text="createProductModal.errorMessage"></p>
                            </div>
                        </div>
                    </div>

                    <div x-show="createProductModal.successMessage"
                        class="p-4 bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 rounded-xl">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-green-800 dark:text-green-200">Success!</h4>
                                <p class="text-xs text-green-700 dark:text-green-300 mt-1" x-text="createProductModal.successMessage"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div
                class="px-6 py-4 border-t-2 border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700 dark:to-gray-700 flex justify-end items-center gap-3">
                <button type="button" @click="closeCreateProductModal()" :disabled="createProductModal.creating"
                    class="px-6 py-2.5 text-sm font-bold text-gray-700 bg-white border-2 border-gray-300 rounded-xl hover:bg-gray-50 hover:border-gray-400 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-all duration-300 transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                    Cancel
                </button>
                <button type="button" @click="createAndSelectProduct()"
                    :disabled="createProductModal.creating || !createProductModal.productName.trim()"
                    class="group relative px-8 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl hover:from-blue-700 hover:to-purple-700 font-bold shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 overflow-hidden">
                    <span class="absolute inset-0 w-full h-full bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-300"></span>
                    <svg x-show="createProductModal.creating" class="animate-spin h-4 w-4" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <svg x-show="!createProductModal.creating" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span x-text="createProductModal.creating ? 'Creating...' : 'Create & Use Product'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #4a5568;
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-4px); }
        75% { transform: translateX(4px); }
    }
    .animate-shake {
        animation: shake 0.2s ease-in-out 0s 2;
    }
</style>
