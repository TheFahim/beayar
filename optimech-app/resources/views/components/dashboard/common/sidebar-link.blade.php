@props(['url'])

@php
    $isActive = request()->is(ltrim(parse_url($url, PHP_URL_PATH), '/'));
@endphp

<a href="{{ $url }}"
    {{ $attributes->class([
        'flex items-center w-full p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700',
        'bg-gray-100 dark:bg-gray-700' => $isActive,
    ]) }}>
    {{ $slot }}
</a>
