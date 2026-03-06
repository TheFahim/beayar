@props(['targetId', 'isOpen' => false, 'childLinks' => []])

@php
    // Check if any child link is active to keep dropdown open
    $hasActiveChild = false;
    foreach ($childLinks as $childUrl) {
        if ($childUrl) {
            $path = parse_url($childUrl, PHP_URL_PATH);
            if ($path) {
                if (request()->is(ltrim($path, '/') . '*')) {
                    $hasActiveChild = true;
                    break;
                }
            }
        }
    }

    // Set initial state based on active child or explicit isOpen parameter
    $initialState = $hasActiveChild ? 'true' : ($isOpen ? 'true' : 'false');
@endphp

<div x-data="{ isOpen: {{ $initialState }} }" class="w-full">
    <button type="button"
        class="group relative flex items-center w-full px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 ease-in-out {{ $hasActiveChild ? 'text-white bg-slate-700/50' : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }} hover:shadow-lg"
        @click="isOpen = !isOpen"
        aria-controls="{{ $targetId }}"
        :aria-expanded="isOpen">

        <!-- Icon wrapper -->
        <div class="{{ $hasActiveChild ? 'text-white' : 'text-slate-500 group-hover:text-slate-300' }} transition-colors duration-200">
            {{ $slot }}
        </div>

        <!-- Arrow indicator with rotation -->
        <svg class="w-4 h-4 ml-auto transition-transform duration-200"
             :class="{ 'rotate-180': isOpen }"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown content -->
    <div class="mt-2 ml-4" x-show="isOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2">
        {{ $dropdownContent ?? '' }}
    </div>
</div>
