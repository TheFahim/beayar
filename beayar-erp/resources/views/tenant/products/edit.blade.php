<x-dashboard.layout.default title="Edit Product">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.products.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Products
            </a>
        </li>
        <li class="inline-flex items-center">
            <span class="mx-2 text-sm text-gray-400">/</span>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-300">Edit</span>
        </li>
    </x-dashboard.ui.bread-crumb>

    {{-- Main Alpine component for editing a single product --}}
    <div x-data="productEditForm()" x-init="init()" class="space-y-6">
        <form method="POST" action="{{ route('tenant.products.update', $product) }}" class="space-y-8" novalidate
            enctype="multipart/form-data" @submit="validateForm($event)">
            @csrf
            @method('PUT')

            {{-- Single Product Form --}}
            <div x-data="{ isHovered: false }" @mouseenter="isHovered = true" @mouseleave="isHovered = false"
                class="relative rounded-xl border-2 p-4 lg:p-6 dark:border-gray-700 space-y-6 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/50 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
                :class="{ 'border-blue-400 dark:border-blue-500 ring-2 ring-blue-200 dark:ring-blue-900': isHovered }">

                {{-- Animated corner accent --}}
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-400 to-purple-500 opacity-10 rounded-bl-full transition-all duration-300"
                    :class="{ 'w-32 h-32 opacity-20': isHovered }"></div>

                {{-- Header --}}
                <div class="flex items-center justify-between relative z-10">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-3">
                        <span
                            class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white text-sm font-bold flex items-center justify-center shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </span>
                        <span>Edit Product</span>
                    </h3>
                </div>

                <div class="flex flex-col md:flex-row gap-6 items-start">
                    {{-- Image Selection --}}
                    <div class="w-full md:w-auto flex-shrink-0" x-data="{ imageHovered: false }">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Product Image
                        </label>

                        <div
                            class="flex items-center gap-3 p-2 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/50 hover:border-blue-400 dark:hover:border-blue-500 transition-colors duration-200">
                            {{-- Image Preview (Compact) --}}
                            <div @mouseenter="imageHovered = true" @mouseleave="imageHovered = false"
                                class="w-16 h-16 bg-white dark:bg-gray-700 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 flex-shrink-0 shadow-sm relative group cursor-pointer"
                                @click="openImageLibrary()">
                                <template x-if="product.imageUrl">
                                    <img :src="product.imageUrl"
                                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                                        alt="Selected image">
                                </template>
                                <template x-if="!product.imageUrl">
                                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                </template>

                                {{-- Overlay with edit icon --}}
                                <div
                                    class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                        </path>
                                    </svg>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex-1 flex flex-col gap-1.5 min-w-0">
                                <input type="hidden" name="image_id" :value="product.imageId">

                                <div class="flex flex-col gap-1 w-fit">
                                    <div class="flex gap-2">
                                        <button type="button" @click="openImageLibrary()"
                                            class="px-2 py-1 text-[10px] font-medium bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50 transition-colors truncate">
                                            Choose
                                        </button>
                                        <button type="button" @click="showUploadModal = true"
                                            class="px-2 py-1 text-[10px] font-medium bg-green-50 text-green-600 rounded-lg hover:bg-green-100 dark:bg-green-900/30 dark:text-green-300 dark:hover:bg-green-900/50 transition-colors truncate">
                                            Upload
                                        </button>
                                    </div>
                                    <button type="button" @click="clearSelectedImage()" x-show="product.imageUrl"
                                        class="w-full px-2 py-1 text-[10px] font-medium text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors flex items-center justify-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                                <div x-show="!product.imageUrl" class="text-[10px] text-gray-400 italic px-1">
                                    No image
                                </div>
                            </div>
                        </div>
                        @error('image_id')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Product name --}}
                    <div class="flex-1 w-full" x-data="{ isFocused: false }">
                        <label for="product_name"
                            class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 transition-colors duration-200"
                            :class="{ 'text-blue-600 dark:text-blue-400': isFocused }">
                            Product Name
                        </label>
                        <div class="relative">
                            <input id="product_name" name="name" type="text" x-model="product.name" required
                                @focus="isFocused = true" @blur="isFocused = false"
                                class="mt-1 block w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-4 py-3 transition-all duration-200"
                                placeholder="Enter product name">
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-400 to-purple-500 opacity-0 -z-10 blur transition-opacity duration-200"
                                :class="{ 'opacity-20': isFocused }"></div>
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1 animate-shake">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Specifications --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                        Specifications
                    </label>
                    <div class="space-y-3">
                        <template x-for="(spec, specIdx) in product.specifications" :key="spec.key">
                            <div x-data="{ specHovered: false }" @mouseenter="specHovered = true"
                                @mouseleave="specHovered = false"
                                class="flex flex-col gap-2 p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50 transition-all duration-300 hover:shadow-md relative w-full max-w-full overflow-x-hidden"
                                :class="{ 'ring-2 ring-blue-400 dark:ring-blue-500 bg-blue-50 dark:bg-blue-900/20': specHovered }">
                                <input type="hidden" :name="`specifications[${specIdx}][id]`" :value="spec.id">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider" x-text="`Specification ${specIdx + 1}`"></span>
                                    <button type="button" @click.stop="removeSpec(specIdx)"
                                        x-show="product.specifications.length > 1"
                                        class="group p-2 text-sm text-red-600 hover:text-white hover:bg-red-600 rounded-full transition-all duration-300 hover:scale-110 active:scale-95 font-semibold">
                                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:rotate-90"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex-1 relative min-w-0">
                                    <textarea :id="`text-area-${specIdx}`" :name="`specifications[${specIdx}][description]`" x-model="spec.description"
                                        required rows="4"
                                        class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:bg-inherit dark:text-white"
                                        placeholder="Specification description" x-init="$nextTick(() => { if (window.sunEditorUtils) window.sunEditorUtils.initializeEditors(); })"></textarea>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="mt-4">
                        <button type="button" @click="addSpec()"
                            class="group relative px-5 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 active:scale-95 overflow-hidden">
                            <span
                                class="absolute inset-0 w-full h-full bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-300"></span>
                            <span class="relative flex items-center gap-2">
                                <svg class="w-5 h-5 transition-transform duration-300 group-hover:rotate-90"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add specification
                            </span>
                        </button>
                    </div>
                    @if ($errors->has('specifications') || $errors->has('specifications.*.description'))
                        <div
                            class="mt-3 text-sm text-red-600 bg-red-50 dark:bg-red-900/20 p-3 rounded-xl flex items-center gap-2">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Specifications are required and cannot be empty.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div
                class="flex items-center justify-end gap-3 mt-8 p-6 bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-800 dark:to-gray-800 rounded-2xl border-2 border-gray-200 dark:border-gray-700 shadow-lg">
                <a href="{{ route('tenant.products.index') }}"
                    class="px-6 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 font-semibold transition-all duration-300 hover:scale-105 active:scale-95 hover:shadow-lg">
                    Cancel
                </a>
                <button type="submit"
                    class="group relative px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl hover:from-blue-700 hover:to-purple-700 font-bold shadow-lg hover:shadow-2xl transition-all duration-300 hover:scale-105 active:scale-95 overflow-hidden save-button">
                    <span
                        class="absolute inset-0 w-full h-full bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-300"></span>
                    <span class="relative flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Update Product
                    </span>
                </button>
            </div>
        </form>

        {{-- FIX: The Upload Modal is moved out of the main <form> to prevent nesting issues --}}
        <!-- Upload Modal -->
        <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="showUploadModal = false" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <!-- Background overlay -->
                <div x-show="showUploadModal" @click="showUploadModal = false"
                    class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75 backdrop-blur-sm">
                </div>

                <!-- Modal panel -->
                <div x-show="showUploadModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border-2 border-blue-500/20"
                    role="dialog" aria-modal="true">

                    <form @submit.prevent="uploadImage()">
                        <div
                            class="bg-gradient-to-br from-white to-blue-50 dark:from-gray-800 dark:to-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="mb-4 flex items-center gap-3">
                                <div
                                    class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Upload Image
                                    </h3>
                                </div>

                                <!-- Image Name Input -->
                                <div class="mb-4">
                                    <label for="modal-image-name"
                                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Image Name
                                    </label>
                                    <input type="text" id="modal-image-name" x-model="modalImageName" required
                                        class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 transition-all duration-200"
                                        placeholder="Enter image name...">
                                </div>

                                <!-- Image Upload Component -->
                                <x-ui.form.image-upload title="Product Image" name="modal_image" id="modal-image-upload"
                                    :required="true" />
                            </div>

                            <div
                                class="bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700 dark:to-gray-700 px-4 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                                <button type="submit" :disabled="uploading"
                                    class="group relative w-full inline-flex justify-center items-center rounded-xl border border-transparent shadow-lg px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-base font-semibold text-white hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 hover:scale-105 active:scale-95 overflow-hidden">
                                    <span
                                        class="absolute inset-0 w-full h-full bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-300"></span>
                                    <span x-show="!uploading" class="relative">Upload</span>
                                    <span x-show="uploading" class="relative flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Uploading...
                                    </span>
                                </button>
                                <button type="button" @click="showUploadModal = false"
                                    class="mt-3 w-full inline-flex justify-center rounded-xl border-2 border-gray-300 dark:border-gray-600 shadow-sm px-6 py-3 bg-white dark:bg-gray-600 text-base font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm transition-all duration-200 hover:scale-105 active:scale-95">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    <!-- Image Library Modal -->
    <div x-data="imageLibraryModal()" x-show="open" x-cloak @keydown.escape.window="close()" class="fixed inset-0 z-50"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <!-- backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm" @click="close()"></div>

        <!-- center container -->
        <div class="relative inset-0 flex items-center justify-center p-4">
            <!-- panel -->
            <div x-show="open" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-5xl shadow-2xl overflow-hidden border-2 border-blue-500/20"
                role="dialog" aria-modal="true">
                <!-- header -->
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

                <!-- body (scrollable) -->
                <div class="modal-body p-6 max-h-[70vh] overflow-auto" id="modal-image-grid-container">
                    <div id="modal-image-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <!-- images will be rendered here -->
                        <template x-for="image in images" :key="image.id">
                            <div x-data="{ imgHovered: false }" @mouseenter="imgHovered = true"
                                @mouseleave="imgHovered = false"
                                @click="select(image)"
                                class="group relative border-2 border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden image-item shadow-md hover:shadow-2xl transition-all duration-300 hover:scale-105 hover:border-blue-400 dark:hover:border-blue-500 cursor-pointer"
                                :data-id="image.id"
                                :class="{ 'ring-2 ring-blue-400 dark:ring-blue-500': imgHovered }">
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

                <!-- footer -->
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

    {{-- Alpine component scripts --}}
    <script>
        function productEditForm() {
            return {
                // Initialize product with existing data from the server
                product: {
                    name: @json(old('name', $product->name)),
                    imageId: @json(old('image_id', $product->image_id)),
                    imageUrl: @json(
                        $product->image
                            ? (str_starts_with($product->image->path, 'http')
                                ? $product->image->path
                                : url($product->image->path))
                            : null),
                    specifications: @json(old('specifications')
                            ? collect(old('specifications'))->map(fn($spec) => ['id' => $spec['id'] ?? null, 'description' => $spec['description']])->toArray()
                            : $product->specifications->map(fn($spec) => ['id' => $spec->id, 'description' => $spec->description])->toArray())
                            .map((s, i) => ({ ...s, key: s.id ? `db-${s.id}` : `spec-${Date.now()}-${Math.floor(Math.random() * 1000000)}` })),
                },
                hasQuotations: @json($hasQuotations ?? false),

                // Upload modal state
                showUploadModal: false,
                uploading: false,
                modalImageName: '',

                init() {
                    // Ensure at least one specification exists
                    if (!this.product.specifications || this.product.specifications.length === 0) {
                        this.product.specifications = [{
                            id: null,
                            key: `spec-${Date.now()}-${Math.floor(Math.random() * 1000000)}`,
                            description: ''
                        }];
                    }
                },

                validateForm(event) {
                    let isValid = true;
                    let firstErrorId = null;

                    // Check for duplicate descriptions
                    const descriptions = new Set();

                    this.product.specifications.forEach((spec, sIndex) => {
                        const textareaId = `text-area-${sIndex}`;

                        // Save content first
                        if (window.sunEditorUtils && window.sunEditorUtils.saveEditorContent) {
                            window.sunEditorUtils.saveEditorContent(textareaId);
                        }

                        // Check if empty
                        if (window.sunEditorUtils && window.sunEditorUtils.isEditorEmpty) {
                            if (window.sunEditorUtils.isEditorEmpty(textareaId)) {
                                isValid = false;
                                if (!firstErrorId) firstErrorId = textareaId;
                            }
                        }

                        // Check for duplicates (strip HTML tags for comparison)
                        const content = spec.description || '';
                        const cleanContent = content.replace(/<[^>]*>/g, '').trim();
                        if (descriptions.has(cleanContent) && cleanContent.length > 0) {
                            isValid = false;
                            if (!firstErrorId) firstErrorId = textareaId;
                            alert(`Duplicate specification found: "${cleanContent}"`);
                        }
                        descriptions.add(cleanContent);
                    });

                    if (!isValid) {
                        event.preventDefault();
                        if (!firstErrorId) {
                            alert('Please fill in all specification descriptions. Content cannot be empty.');
                        }
                        // Scroll to first error
                        if (firstErrorId) {
                            const element = document.getElementById(firstErrorId);
                            if (element) {
                                 const editorContainer = element.nextElementSibling;
                                 if (editorContainer && editorContainer.classList.contains('sun-editor')) {
                                     editorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                 } else {
                                     element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                 }
                            }
                        }
                    }
                },

                addSpec() {
                    this.product.specifications.push({
                        id: null,
                        key: `spec-${Date.now()}-${Math.floor(Math.random() * 1000000)}`,
                        description: ''
                    });
                },

                removeSpec(specIndex) {
                    const spec = this.product.specifications[specIndex];

                    if (this.product.specifications.length > 1 && spec && (!this.hasQuotations || !spec.id)) {
                        this.product.specifications.splice(specIndex, 1);
                    }
                },

                openImageLibrary() {
                    window.__selectImageCallback = (image) => {
                        this.product.imageId = image.id;
                        this.product.imageUrl = image.path;

                        setTimeout(() => {
                            window.__selectImageCallback = null;
                        }, 0);
                    };
                    window.dispatchEvent(new CustomEvent('open-image-library'));
                },

                clearSelectedImage() {
                    this.product.imageId = null;
                    this.product.imageUrl = null;
                },

                // Upload image functionality with compression
                async uploadImage() {
                    const fileInput = document.getElementById('modal-image-upload');
                    const file = fileInput.files?.[0];

                    if (!file || !this.modalImageName.trim()) {
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Missing Information',
                                text: 'Please provide a name and select an image.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                        return;
                    }

                    try {
                        ImageUtils.validateFile(file);
                        this.uploading = true;

                        if (window.Swal) {
                            Swal.fire({
                                title: 'Processing Image...',
                                text: 'Compressing image for upload, please wait.',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                willOpen: () => Swal.showLoading()
                            });
                        }

                        const compressedFile = await ImageUtils.compressImage(file);

                        if (window.Swal) {
                            Swal.update({
                                text: 'Uploading compressed image...'
                            });
                        }

                        const formData = new FormData();
                        formData.append('name', this.modalImageName);
                        formData.append('image', compressedFile, compressedFile.name);

                        const response = await fetch("{{ route('tenant.images.store') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.product.imageId = data.image.id;
                            this.product.imageUrl = data.image.path.startsWith('http') ?
                                data.image.path :
                                `${window.location.origin}/${data.image.path.replace(/^\/+/, '')}`;

                            this.resetUploadModal();

                            if (window.Swal) {
                                const compressionInfo = compressedFile.size < file.size ?
                                    `<small>Compressed by ${Math.round(((file.size - compressedFile.size) / file.size) * 100)}% (${ImageUtils.formatFileSize(file.size)} → ${ImageUtils.formatFileSize(compressedFile.size)})</small>` :
                                    '';

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    html: `
                                        <p>${data.message}</p>
                                        ${compressionInfo}
                                        ${data.compression_ratio ? `<small>Server compression: ${data.compression_ratio}</small>` : ''}
                                    `,
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            }

                        } else {
                            throw new Error(data.message || 'Upload failed due to a server error.');
                        }
                    } catch (error) {
                        console.error('Upload error:', error);
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Upload Failed',
                                text: error.message || 'An unexpected error occurred.',
                                showConfirmButton: true
                            });
                        }
                    } finally {
                        this.uploading = false;
                    }
                },

                resetUploadModal() {
                    this.modalImageName = '';
                    const fileInput = document.getElementById('modal-image-upload');
                    if (fileInput) fileInput.value = '';
                    this.showUploadModal = false;

                    const uploadComponent = document.querySelector('#modal-image-upload')?.closest(
                        '[x-data*="dragableImage"]');
                    if (uploadComponent?.__x) {
                        uploadComponent.__x.$data.files = [];
                    }
                }
            }
        }

    </script>

    @include('tenant.products.partials.image-scripts')
</x-dashboard.layout.default>
