<div x-show="showUploadImageModal" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" @keydown.escape.window="showUploadImageModal = false"
    class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">

    <div class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm transition-opacity"
        @click="showUploadImageModal = false"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div
            class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full border-2 border-blue-500/20">
            <form @submit.prevent="uploadImageForNewProduct()">
                <div
                    class="bg-gradient-to-br from-white to-blue-50 dark:from-gray-800 dark:to-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Upload Image</h3>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Image Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="uploadImageModal.imageName" required
                            placeholder="Enter image name..."
                            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200">
                    </div>

                    <x-ui.form.image-upload title="Product Image" name="new_product_image" id="new-product-image-upload"
                        :required="true" />
                </div>

                <div
                    class="bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700 dark:to-gray-700 px-4 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button type="submit" :disabled="uploadImageModal.uploading"
                        class="w-full inline-flex justify-center items-center rounded-xl border border-transparent shadow-lg px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-base font-semibold text-white hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 hover:scale-105 active:scale-95">
                        <span x-show="!uploadImageModal.uploading">Upload</span>
                        <span x-show="uploadImageModal.uploading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Uploading...
                        </span>
                    </button>
                    <button type="button" @click="showUploadImageModal = false"
                        class="mt-3 w-full inline-flex justify-center rounded-xl border-2 border-gray-300 dark:border-gray-600 shadow-sm px-6 py-3 bg-white dark:bg-gray-600 text-base font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm transition-all duration-200 hover:scale-105 active:scale-95">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
