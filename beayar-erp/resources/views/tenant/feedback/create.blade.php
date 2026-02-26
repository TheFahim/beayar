<x-dashboard.layout.default title="Create Feedback">
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
            <span class="text-sm font-medium text-gray-500 dark:text-gray-300">Create</span>
        </li>
    </x-dashboard.ui.bread-crumb>

    <x-ui.card heading="Create Feedback" class="mx-auto">
        <form method="POST" action="{{ route('tenant.feedback.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subject</label>
                <input
                    type="text"
                    name="subject"
                    value="{{ old('subject') }}"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                    placeholder="Optional subject"
                />
                @error('subject')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Message <span class="text-red-500">*</span></label>
                <textarea
                    name="message"
                    rows="6"
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                    placeholder="Describe your feedback..."
                >{{ old('message') }}</textarea>
                @error('message')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Screenshots (optional)</label>
                <x-ui.form.image-upload
                    title="Screenshots"
                    name="screenshots[]"
                    id="feedback-screenshots"
                    :required="false"
                    :multiple="true"
                />
                @error('screenshots')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
                @error('screenshots.*')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                    Submit
                </button>
                <a href="{{ route('tenant.feedback.index') }}" class="text-gray-700 dark:text-gray-200 hover:underline">Cancel</a>
            </div>
        </form>
    </x-ui.card>
</x-dashboard.layout.default>
