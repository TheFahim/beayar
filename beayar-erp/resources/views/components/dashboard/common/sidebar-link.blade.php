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
        'flex items-center w-full p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700',
        'bg-gray-100 dark:bg-gray-700' => $isActive,
    ]) }}>
    {{ $slot }}
</a>
