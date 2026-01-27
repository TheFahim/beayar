@foreach($images as $image)
    <div class="relative group image-item" data-id="{{ $image->id }}">
        <div class="relative aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
            <img
                src="{{ asset($image->path) }}"
                alt="{{ $image->name }}"
                @click="previewImage = '{{ asset($image->path) }}'; showPreviewModal = true"
                class="w-full h-full object-cover cursor-pointer transition-transform duration-200 group-hover:scale-110"
                loading="lazy"
            >
            
            <!-- Overlay with actions -->
            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity duration-200 rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 pointer-events-none">
            <!-- View Button -->
            <button
                @click="previewImage = '{{ asset($image->path) }}'; showPreviewModal = true"
                class="p-2 bg-white rounded-full mx-1 hover:bg-gray-200 transition-colors pointer-events-auto"
                title="View"
            >
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </button>

            <!-- Delete Button -->
            <button
                @click="deleteImage({{ $image->id }})"
                class="p-2 bg-white rounded-full mx-1 hover:bg-red-100 transition-colors pointer-events-auto"
                title="Delete"
            >
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Image Info -->
    <div class="mt-2">
        <p class="text-sm text-gray-700 dark:text-gray-300 truncate" title="{{ $image->name }}">
            {{ $image->name }}
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ $image->formatted_size }}
        </p>
    </div>
</div>
@endforeach
