@props(['heading'])

<div
    {{ $attributes->merge(['class' => 'mx-2 p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700']) }}>

    @if ($heading ?? false)
        <h2 class="mb-4 p-4 text-xl font-extrabold leading-none tracking-tight text-gray-900 md:text-xl dark:text-white  border-b border-gray-200 dark:border-gray-700">
            {{ $heading }}</h2>
    @endif

    {{ $slot }}

</div>
