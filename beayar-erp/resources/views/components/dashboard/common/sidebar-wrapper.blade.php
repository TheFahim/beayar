<aside
    id="logo-sidebar"
    class="fixed h-full top-0 left-0 z-40 w-64 pt-20 transition-all duration-300 bg-gradient-to-b from-gray-50 to-gray-100 dark:from-slate-900 dark:to-slate-800 border-r border-gray-300 dark:border-slate-700/50 shadow-2xl"
    :class="{ '-translate-x-full': !sidebarOpen }"
    aria-label="Sidebar"
>
    <div class="px-4 py-6 h-full overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 dark:scrollbar-thumb-slate-600 scrollbar-track-gray-100 dark:scrollbar-track-slate-800">
        <div class="mb-8">
            <h2 class="text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider mb-4 px-3">Navigation</h2>
            {{$slot}}
        </div>
    </div>
</aside>
