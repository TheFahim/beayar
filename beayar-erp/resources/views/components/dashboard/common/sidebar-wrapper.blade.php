<aside
    id="logo-sidebar"
    class="fixed h-full top-0 left-0 z-40 w-60 pt-20 transition-transform bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700"
    :class="{ '-translate-x-full': !sidebarOpen }"
    aria-label="Sidebar"
>
    <div class="px-3 overflow-y-auto bg-gray-50 dark:bg-gray-800">
        {{$slot}}
    </div>
</aside>
