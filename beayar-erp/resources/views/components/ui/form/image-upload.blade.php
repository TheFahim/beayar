@props([
    'title',
    'name',
    'id' => 'dropzone',
    'required' => false,
    'multiple' => false,
    'oldData' => null,
    'file' => false
])




    <div x-data="dragableImage" @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop($event)" data-id="{{ $id }}" class="mt-4">

        <div class="space-y-3">

            <div class="flex flex-col items-center justify-center  gap-4">
                <label :class="{ 'border-blue-500 bg-blue-50 dark:bg-gray-600': isDragging }"
                    class="flex flex-col items-center justify-center w-full border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600 transition-colors duration-200"
                    for="{{ $id }}">
                    <div x-show="files.length > 0" class="w-full">
                        <div class="m-2 flex flex-wrap gap-4 items-center justify-center">
                            <template x-for="(file, index) in files" :key="index">
                                <div class="relative group">
                                    <!-- Preview for images -->
                                    <template x-if="file.type.startsWith('image/')">
                                        <img :src="URL.createObjectURL(file)"
                                            class="w-24 h-24 object-cover rounded-lg border border-gray-200"
                                            @load="URL.revokeObjectURL($event.target.src)" draggable="false">
                                    </template>
                                    <!-- Preview for non-images -->
                                    <template x-if="!file.type.startsWith('image/')">
                                        <div
                                            class="w-24 h-24 flex items-center justify-center bg-gray-100 rounded-lg border border-gray-200">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    </template>
                                    <!-- File name -->
                                    <p class="mt-1 text-xs text-gray-500 truncate w-24" x-text="file.name"></p>
                                    <!-- Remove button -->
                                    <button @click="removeFile(index, $event)"
                                        class="absolute -top-2 -right-2 hidden group-hover:flex bg-red-500 text-white rounded-full p-1 w-6 h-6 items-center justify-center">

                                        <x-ui.svg.close class="h-6 w-6 text-white" />
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div x-show="files.length == 0" class="md:flex items-center justify-center pt-5 pb-6">
                        <x-ui.svg.upload class="h-8 w-8 mx-2" />

                        <p class="my-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click
                                to upload</span> or drag and drop &nbsp;</p>
                        @if (($file == false))

                            <span class="text-xs text-gray-500 dark:text-gray-400"> SVG, PNG,
                                JPG or GIF
                            </span>
                        @endif
                    </div>
                    <input
                        id="{{ $id }}"
                        name="{{ $name }}"
                        type="file"
                        class="hidden"
                        @change="handleFileSelect($event)"
                        {{ $required ? 'required' : '' }}
                        {{ $multiple ? 'multiple' : '' }}
                        data-old="{{ $oldData }}"
                    />
                </label>

            </div>

        </div>

    </div>
