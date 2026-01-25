@props(['dataId' => 'draggable'])

<div x-data="dragable" @dragover.prevent="isDragging = true"
    @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop($event)" data-id="{{ $dataId }}">
    {{ $slot }}
</div>
