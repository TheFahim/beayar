<x-dashboard.layout.default title="Product Images">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('images.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Image Library
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>

    <div x-data="imageLibrary" x-init="init()" class="space-y-6">
        <!-- Header with Search and Upload Button -->
        <x-ui.card heading="Image Library" class="mx-auto">
            <div class="flex flex-col lg:flex-row justify-between items-center gap-4 mb-6">
                <!-- Search Bar -->
                <div class="relative w-full lg:w-96">
                    <input
                        type="text"
                        x-model="searchQuery"
                        @input.debounce.500ms="searchImages()"
                        placeholder="Search images by name..."
                        class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <!-- Add Image Button -->
                <button
                    @click="showUploadModal = true"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Images
                </button>
            </div>

            <!-- Image Grid -->
            <div id="image-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                @include('dashboard.images.partials.image-grid', ['images' => $images])
            </div>

            <!-- Loading Spinner -->
            <div x-show="loading" class="flex justify-center py-8">
                <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <!-- No More Images Message -->
            <div x-show="!hasMore && !loading && images.length > 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                No more images to load
            </div>

            <!-- No Images Found -->
            <div x-show="images.length === 0 && !loading" class="text-center py-16">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="mt-4 text-gray-500 dark:text-gray-400">No images found</p>
            </div>
        </x-ui.card>

        <!-- Upload Modal -->
        <div x-show="showUploadModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="showUploadModal = false">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <!-- Background overlay -->
                <div x-show="showUploadModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="showUploadModal = false"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75">
                </div>

                <!-- Modal panel -->
                <div x-show="showUploadModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    <form @submit.prevent="uploadImages()">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Upload Images</h3>
                            </div>

                            <!-- Image Name Input -->
                            <div class="mb-4">
                                <label for="image-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Image Name
                                </label>
                                <input
                                    type="text"
                                    id="image-name"
                                    x-model="imageName"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Enter image name..."
                                >
                            </div>

                            <!-- Image Upload Component -->
                            <x-ui.form.image-upload
                                title="Product Images"
                                name="image"
                                id="image-upload"
                                :required="true"
                            />
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <button
                                type="submit"
                                :disabled="uploading"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span x-show="!uploading">Upload</span>
                                <span x-show="uploading" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Uploading...
                                </span>
                            </button>
                            <button
                                type="button"
                                @click="showUploadModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Image Preview Modal -->
        <div x-show="showPreviewModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="showPreviewModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <!-- Background overlay -->
                <div x-show="showPreviewModal"
                    @click="showPreviewModal = false"
                    class="fixed inset-0 transition-opacity bg-black bg-opacity-75">
                </div>

                <!-- Modal panel -->
                <div x-show="showPreviewModal"
                    class="relative max-w-4xl mx-auto">
                    <img :src="previewImage" class="max-w-full max-h-[80vh] rounded-lg">
                    <button
                        @click="showPreviewModal = false"
                        class="absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

</x-dashboard.layout.default>
