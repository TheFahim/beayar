@props(['url'])

<a href="{{ $url }}"
    {{ $attributes([
        'class' =>
            'flex items-center w-full p-2 text-gray-900 rounded-lg hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700',
    ]) }}>
    {{ $slot }}
</a>
