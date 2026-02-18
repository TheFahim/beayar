<div x-data="imageLibraryModal()" x-show="open" x-cloak @keydown.escape.window="close()" class="fixed inset-0 z-50"
    x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm" @click="close()"></div>

    <div class="relative inset-0 flex items-center justify-center p-4">
        <div x-show="open" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-5xl shadow-2xl overflow-hidden border-2 border-blue-500/20"
            role="dialog" aria-modal="true">

            <div
                class="flex items-center justify-between p-6 border-b-2 border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-800">
                <div class="flex-1 pr-4">
                    <div class="relative">
                        <input type="text" x-model="query" @input.debounce.500ms="search()"
                            placeholder="Search images by name..."
                            class="w-full px-5 py-3 pl-12 rounded-xl border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-all duration-200">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="close()"
                        class="px-5 py-3 rounded-xl border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 font-semibold transition-all duration-300 hover:scale-105 active:scale-95 hover:shadow-lg">
                        Close
                    </button>
                </div>
            </div>

            <div class="modal-body p-6 max-h-[70vh] overflow-auto" id="modal-image-grid-container">
                <div id="modal-image-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <template x-for="image in images" :key="image.id">
                        <div class="group relative border-2 border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden image-item shadow-md hover:shadow-2xl transition-all duration-300 hover:scale-105 hover:border-blue-400 dark:hover:border-blue-500 cursor-pointer"
                            :data-id="image.id" @click="select(image)">
                            <div class="relative overflow-hidden">
                                <img :src="image.path"
                                    class="w-full h-36 object-cover transition-transform duration-500 group-hover:scale-110"
                                    alt="" />
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-black/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                </div>
                            </div>
                            <div
                                class="p-3 bg-white dark:bg-gray-800 transition-colors duration-300 group-hover:bg-blue-50 dark:group-hover:bg-blue-900/20">
                                <div class="text-sm font-semibold text-gray-700 dark:text-gray-200 truncate"
                                    x-text="image.name"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate"
                                    x-text="image.original_name">
                                </div>
                            </div>

                            <div
                                class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                                <button type="button" @click="select(image)"
                                    class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-sm font-bold rounded-lg hover:from-blue-700 hover:to-purple-700 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-110 active:scale-95">
                                    Select
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div
                class="p-6 border-t-2 border-gray-200 dark:border-gray-700 flex justify-center bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-800 dark:to-gray-800">
                <button x-show="hasMore && !loading" @click="loadMore()"
                    class="px-6 py-3 bg-gradient-to-r from-gray-700 to-gray-800 dark:from-gray-600 dark:to-gray-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 active:scale-95">
                    Load more
                </button>
                <div x-show="!hasMore && images.length > 0"
                    class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                    No more images
                </div>
                <div x-show="images.length === 0 && !loading"
                    class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                    No images found
                </div>
            </div>
        </div>
    </div>
</div>
