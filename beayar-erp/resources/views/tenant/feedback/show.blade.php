<x-dashboard.layout.default title="View Feedback">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.feedback.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.dashboard class="h-3 w-3 me-2" />
                Feedback
            </a>
        </li>
        <li class="inline-flex items-center">
            <span class="mx-2 text-sm text-gray-400">/</span>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-300">View</span>
        </li>
    </x-dashboard.ui.bread-crumb>

    <x-ui.card heading="Feedback" class="mx-auto">
        <div class="space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $feedback->subject ?? 'Feedback #'.$feedback->id }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status: {{ $feedback->status }}</p>
                </div>

                @can('delete_feedback')
                    <form method="POST" action="{{ route('tenant.feedback.destroy', $feedback) }}" onsubmit="return confirm('Delete this feedback?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                    </form>
                @endcan
            </div>

            <div class="prose dark:prose-invert max-w-none">
                <p class="whitespace-pre-line">{{ $feedback->message }}</p>
            </div>

            @if($feedback->images->count())
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Attachments</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        @foreach($feedback->images as $img)
                            <a href="{{ asset($img->path) }}" target="_blank" class="block">
                                <img src="{{ asset($img->path) }}" class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-700" alt="Attachment" />
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-ui.card>
</x-dashboard.layout.default>
