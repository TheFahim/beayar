<x-dashboard.layout.default title="Tenant Profile">
    <div class="max-w-4xl mx-auto py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('tenant.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <x-ui.svg.dashboard class="w-4 h-4 mr-2" />
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <x-ui.svg.right-arrow class="w-4 h-4 text-gray-400 mx-1" />
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Profile</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Profile Details</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View your personal information.</p>
                </div>
                <a href="{{ route('tenant.profile.edit') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Edit Profile
                </a>
            </div>

            <div class="p-6 md:p-8">
                <div class="flex flex-col md:flex-row gap-8 items-start">
                    <!-- Avatar Section -->
                    <div class="w-full md:w-1/3 flex flex-col items-center">
                        <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-gray-100 dark:border-gray-700 shadow-sm bg-gray-50 dark:bg-gray-700">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                    <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <h3 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    </div>

                    <!-- Details Section -->
                    <div class="w-full md:w-2/3 space-y-6">
                        <div class="grid grid-cols-1 gap-6">
                            <div class="border-b border-gray-100 dark:border-gray-700 pb-4">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Full Name</label>
                                <div class="text-base font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                            </div>

                            <div class="border-b border-gray-100 dark:border-gray-700 pb-4">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Email Address</label>
                                <div class="text-base font-medium text-gray-900 dark:text-white">{{ $user->email }}</div>
                            </div>

                            <div class="border-b border-gray-100 dark:border-gray-700 pb-4">
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Phone Number</label>
                                <div class="text-base font-medium text-gray-900 dark:text-white">{{ $user->phone ?? 'Not provided' }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Account Created</label>
                                <div class="text-base font-medium text-gray-900 dark:text-white">{{ $user->created_at->format('F j, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.layout.default>