<x-dashboard.layout.default title="Feedback">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.feedback.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.dashboard class="h-3 w-3 me-2" />
                Feedback
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>

    <x-ui.card heading="Feedback" class="mx-auto">
        <div class="flex flex-col lg:flex-row justify-between items-center gap-4 mb-6">
            <form method="GET" action="{{ route('tenant.feedback.index') }}" class="w-full lg:w-96">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search feedback..."
                    class="w-full px-4 py-2 rounded-xl border-2 border-indigo-100 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-300 shadow-sm"
                />
            </form>

            @can('create_feedback')
                <a href="{{ route('tenant.feedback.create') }}"
                    class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Feedback
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-600 dark:text-gray-300">
                        <th class="py-2 pr-4">Subject</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Created</th>
                        <th class="py-2 pr-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($feedback as $item)
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="py-3 pr-4">
                                <a class="text-blue-600 hover:underline" href="{{ route('tenant.feedback.show', $item) }}">
                                    {{ $item->subject ?? 'Feedback #'.$item->id }}
                                </a>
                            </td>
                            <td class="py-3 pr-4">
                                <span class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700">{{ $item->status }}</span>
                            </td>
                            <td class="py-3 pr-4">{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="py-3 pr-4">
                                <div class="flex items-center gap-2">
                                    <a class="text-gray-700 dark:text-gray-200 hover:underline" href="{{ route('tenant.feedback.show', $item) }}">View</a>
                                    @can('delete_feedback')
                                        <form method="POST" action="{{ route('tenant.feedback.destroy', $item) }}" onsubmit="return confirm('Delete this feedback?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-gray-500 dark:text-gray-400">No feedback found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $feedback->links() }}
        </div>
    </x-ui.card>
</x-dashboard.layout.default>
