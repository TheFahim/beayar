@props(['url'])

@php
    // Robust check for active state
    $isActive = false;
    if ($url) {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
             $isActive = request()->is(ltrim($path, '/') . '*');
        }
    }
@endphp

<a href="{{ $url }}"
    {{ $attributes->class([
        'group relative flex items-center w-full px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 ease-in-out',
        'text-gray-700 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-slate-700/50 hover:shadow-lg' => !$isActive,
        'bg-gradient-to-r from-blue-500 to-blue-600 text-white border border-blue-500/30 shadow-lg shadow-blue-500/10 dark:from-blue-600/20 dark:to-indigo-600/20 dark:text-white dark:border-blue-400/30' => $isActive,
    ]) }}>

    <!-- Active indicator line -->
    @if($isActive)
        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-blue-500 to-blue-600 rounded-r-full dark:from-blue-400 dark:to-indigo-400"></div>
    @endif

    <!-- Icon wrapper with enhanced styling -->
    <div class="{{ $isActive ? 'text-white' : 'text-gray-600 dark:text-slate-500 group-hover:text-gray-800 dark:group-hover:text-slate-300' }} flex transition-colors duration-200">
        {{ $slot }}
    </div>
</a>
