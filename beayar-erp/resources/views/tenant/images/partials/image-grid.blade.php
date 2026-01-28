@foreach($images as $image)
    <div class="relative group image-item bg-white dark:bg-gray-800 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 border border-gray-100 dark:border-gray-700 overflow-hidden transform hover:-translate-y-1" data-id="{{ $image->id }}">
        <div class="relative aspect-square overflow-hidden bg-gray-50 dark:bg-gray-900">
            <img
                src="{{ asset($image->path) }}"
                alt="{{ $image->name }}"
                @click="previewImage = '{{ asset($image->path) }}'; showPreviewModal = true"
                class="w-full h-full object-cover cursor-pointer transition-transform duration-500 group-hover:scale-110"
                loading="lazy"
            >
            
            <!-- Overlay with actions -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center gap-3 pointer-events-none backdrop-blur-[2px]">
                <!-- View Button -->
                <button
                    @click="previewImage = '{{ asset($image->path) }}'; showPreviewModal = true"
                    class="p-3 bg-white/90 hover:bg-white text-gray-800 rounded-full shadow-lg transform hover:scale-110 transition-all duration-200 pointer-events-auto backdrop-blur-sm"
                    title="View"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>

                <!-- Edit Button -->
                <button
                    @click="editImage({{ $image->id }}, '{{ addslashes($image->name) }}')"
                    class="p-3 bg-blue-600/90 hover:bg-blue-600 text-white rounded-full shadow-lg transform hover:scale-110 transition-all duration-200 pointer-events-auto backdrop-blur-sm"
                    title="Edit"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                </button>

                <!-- Delete Button -->
                <button
                    @click="deleteImage({{ $image->id }})"
                    class="p-3 bg-red-600/90 hover:bg-red-600 text-white rounded-full shadow-lg transform hover:scale-110 transition-all duration-200 pointer-events-auto backdrop-blur-sm"
                    title="Delete"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Image Info -->
        <div class="px-3 py-2 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
            <p class="text-xs font-bold text-gray-800 dark:text-gray-100 truncate" title="{{ $image->name }}">
                {{ $image->name }}
            </p>
            <div class="flex items-center justify-between mt-1">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                    {{ $image->formatted_size }}
                </span>
                <span class="text-[10px] text-gray-400">
                    {{ strtoupper(pathinfo($image->path, PATHINFO_EXTENSION)) }}
                </span>
            </div>
        </div>
    </div>
@endforeach
