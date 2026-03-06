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
        'text-slate-400 hover:text-white hover:bg-slate-700/50 hover:shadow-lg' => !$isActive,
        'bg-gradient-to-r from-blue-600/20 to-indigo-600/20 text-white border border-blue-500/30 shadow-lg shadow-blue-500/10' => $isActive,
    ]) }}>

    <!-- Active indicator line -->
    @if($isActive)
        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-gradient-to-b from-blue-400 to-indigo-400 rounded-r-full"></div>
    @endif

    <!-- Icon wrapper with enhanced styling -->
    <div class="{{ $isActive ? 'text-white' : 'text-slate-500 group-hover:text-slate-300' }} transition-colors duration-200">
        {{ $slot }}
    </div>
</a>
