@props(['label'])

@php
    $nameAttr = $attributes->get('name');
    $providedValue = $attributes->get('value');
    $dotKey = null;
    if ($nameAttr) {
        // Convert bracket notation (e.g., quotation[customer_id]) to dot notation (quotation.customer_id)
        $dotKey = preg_replace(['/\]/', '/\[/'], ['', '.'], $nameAttr);
        $dotKey = trim($dotKey, '.');
    }
    $finalValue = $dotKey !== null ? old($dotKey, $providedValue) : $providedValue;
@endphp

@if ($label ?? false)
    <label for="{{ $attributes->get('id') }}" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        {{ $label }}

        @if ($attributes['required'] ?? false)
            <span
                class="bg-red-100 text-red-800 text-xs font-medium px-1 py-0.5 rounded dark:bg-red-900 dark:text-red-300">required
            </span>
        @endif
    </label>
@endif
<input
    {{ $attributes->except(['value'])->merge([
        'class' =>
            'block w-full text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
    ]) }}
    @if ($attributes->get('name') ?? false) value="{{ $finalValue }}" @endif>

@if ($dotKey ?? false)
    @error($dotKey)
        <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
    @enderror
@endif
