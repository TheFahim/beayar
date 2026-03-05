<x-layouts.app title="Setup Workspace">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header Section --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome to Beayar ERP</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Let's get your workspace set up so you can start managing your business.</p>
        </div>

        <div class="grid grid-cols-1 gap-8">
            {{-- Company Form (full width now) --}}
            <div>
                <x-ui.card heading="Create New Workspace">

                    @if(session('error'))
                        <div class="mb-6 rounded-lg bg-red-50 p-4 border border-red-100 dark:bg-red-900/20 dark:border-red-800">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Error</h3>
                                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                        <p>{{ session('error') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-6 rounded-lg bg-red-50 p-4 border border-red-100 dark:bg-red-900/20 dark:border-red-800">
                            <div class="flex">
                                 <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Please fix the following errors:</h3>
                                    <ul role="list" class="mt-2 list-disc pl-5 space-y-1 text-sm text-red-700 dark:text-red-300">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                        A workspace represents your company or organization. It's where you'll manage your team, customers, and billing.
                    </p>

                    <form action="{{ route('onboarding.company.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 gap-6">
                            {{-- Company Name --}}
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Company Name <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="e.g. Acme Corp" required>
                            </div>

                            {{-- Phone --}}
                            <div>
                                <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="+1 (555) 000-0000">
                            </div>

                            {{-- Address --}}
                            <div>
                                <label for="address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Address</label>
                                <textarea id="address" name="address" rows="3" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="123 Business St, Suite 100"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700 mt-6">
                            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Create Workspace
                            </button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
