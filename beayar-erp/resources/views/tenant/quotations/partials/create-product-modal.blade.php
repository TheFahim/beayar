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
            class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden border-2 border-blue-500/20">

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
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Create New Product
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Add a new product to use in
                                this quotation</p>
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
            <div class="px-6 py-4 max-h-[calc(90vh-180px)] overflow-y-auto">
                <div class="space-y-6">
                    <!-- Product Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Product Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="createProductModal.productName"
                            placeholder="Enter product name..."
                            class="w-full px-4 py-3 text-sm border-2 border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200"
                            required>
                        <p x-show="createProductModal.errors.productName" x-text="createProductModal.errors.productName"
                            class="mt-1 text-xs text-red-600"></p>
                    </div>

                    <!-- Image Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                            Product Image
                        </label>

                        <div class="flex items-start gap-4">
                            <!-- Image Preview -->
                            <div
                                class="w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 rounded-2xl overflow-hidden border-2 border-gray-300 dark:border-gray-600 flex-shrink-0 shadow-lg transition-all duration-300 hover:shadow-2xl hover:scale-105 relative group">
                                <template x-if="createProductModal.imageUrl">
                                    <img :src="createProductModal.imageUrl"
                                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                                        alt="Selected image">
                                </template>
                                <template x-if="!createProductModal.imageUrl">
                                    <div
                                        class="w-full h-full flex flex-col items-center justify-center text-center text-sm text-gray-500 dark:text-gray-400 p-3">
                                        <svg class="w-10 h-10 mb-2 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        <span>No image</span>
                                    </div>
                                </template>
                            </div>

                            <div class="flex-1 space-y-3">
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="openImageLibraryForNewProduct()"
                                        class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl hover:from-blue-700 hover:to-purple-700 text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 active:scale-95 flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                            </path>
                                        </svg>
                                        Choose from library
                                    </button>
                                    <button type="button" @click="clearNewProductImage()"
                                        class="px-4 py-2 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-red-50 dark:hover:bg-red-900/20 hover:border-red-300 dark:hover:border-red-500 text-sm font-semibold transition-all duration-300 hover:scale-105 active:scale-95">
                                        Clear
                                    </button>
                                </div>

                                <div class="border-t-2 border-dashed border-gray-300 dark:border-gray-600 pt-3">
                                    <button type="button" @click="showUploadImageModal = true"
                                        class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-300 hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl flex items-center gap-2 text-sm font-semibold">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Upload New Image
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Specification -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Specification (Optional)
                        </label>
                        <textarea x-model="createProductModal.specification" rows="4" placeholder="Enter product specification..."
                            class="w-full px-4 py-3 text-sm border-2 border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200"></textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">You can add one
                            specification now. More can be added later.</p>
                    </div>

                    <!-- Creation Status Messages -->
                    <div x-show="createProductModal.errorMessage"
                        class="p-4 bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded-xl">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-sm text-red-800 dark:text-red-200"
                                x-text="createProductModal.errorMessage"></p>
                        </div>
                    </div>

                    <div x-show="createProductModal.successMessage"
                        class="p-4 bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 rounded-xl">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-sm text-green-800 dark:text-green-200"
                                x-text="createProductModal.successMessage"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div
                class="px-6 py-4 border-t-2 border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700 dark:to-gray-700 flex justify-end space-x-3">
                <button type="button" @click="closeCreateProductModal()" :disabled="createProductModal.creating"
                    class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-xl hover:bg-gray-50 focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-all duration-300 hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                    Cancel
                </button>
                <button type="button" @click="createAndSelectProduct()"
                    :disabled="createProductModal.creating || !createProductModal.productName.trim()"
                    class="px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl hover:from-blue-700 hover:to-purple-700 focus:ring-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <svg x-show="createProductModal.creating" class="animate-spin h-4 w-4" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span x-text="createProductModal.creating ? 'Creating...' : 'Create & Use Product'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
