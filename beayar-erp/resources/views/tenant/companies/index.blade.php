<x-dashboard.layout.default title="My Workspaces">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header Section -->
        <div class="mb-10 text-center md:text-left">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Your Workspaces</h1>
            <p class="mt-2 text-lg text-gray-500 dark:text-gray-400">Manage your companies and seamlessly switch contexts.</p>
        </div>

        @if(session('success'))
            <div class="mb-8 p-4 rounded-xl bg-green-50 border border-green-100 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300 flex items-center shadow-sm">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Create New Card -->
            @role('tenant_admin')
            <a href="{{ route('tenant.user-companies.create') }}" class="group flex flex-col items-center justify-center h-full min-h-[280px] p-6 border-2 border-dashed border-gray-300 rounded-2xl hover:border-blue-500 hover:bg-blue-50/50 dark:border-gray-700 dark:hover:border-blue-500 dark:hover:bg-blue-900/10 transition-all duration-300 cursor-pointer">
                <div class="w-16 h-16 mb-4 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center group-hover:bg-blue-100 group-hover:scale-110 transition-all duration-300 dark:bg-blue-900/30 dark:text-blue-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Create Workspace</h3>
                <p class="mt-2 text-sm text-center text-gray-500 dark:text-gray-400">Add a new company to your account</p>
            </a>
            @endrole

            @foreach($companies as $company)
                @php
                    $isActive = Auth::user()->current_tenant_company_id == $company->id;
                @endphp

                <div class="group relative bg-white dark:bg-gray-800 rounded-2xl border {{ $isActive ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-200 dark:border-gray-700' }} shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden">

                    @if($isActive)
                        <div class="absolute top-0 right-0 p-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-1.5 animate-pulse"></span>
                                Active
                            </span>
                        </div>
                    @endif

                    <div class="p-6 flex-1 flex flex-col items-center text-center">
                        <div class="mb-4 relative">
                            <img class="h-20 w-20 rounded-2xl object-cover shadow-sm ring-1 ring-gray-100 dark:ring-gray-700"
                                 src="{{ $company->logo ? asset('storage/'.$company->logo) : asset('assets/images/app-logo.jpeg') }}"
                                 alt="{{ $company->name }}">
                            @if($company->owner_id == Auth::id())
                                <div class="absolute -bottom-2 -right-2 bg-white dark:bg-gray-800 rounded-full p-1 shadow-sm border border-gray-100 dark:border-gray-700" title="Owner">
                                    <div class="bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-300 rounded-full p-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $company->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 line-clamp-1">{{ $company->email ?? 'No email provided' }}</p>

                        <div class="mt-auto w-full">
                            <div class="flex items-center justify-center gap-2 mb-6">
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    @if($company->pivot->role === 'tenant_admin')
                                        Tenant Admin
                                    @elseif($company->pivot->role === 'company_admin')
                                        Administrator
                                    @else
                                        Member
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-3">
                        @if(!$isActive)
                            <form action="{{ route('companies.switch', $company->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-semibold text-white bg-gray-900 rounded-xl hover:bg-gray-800 focus:ring-4 focus:ring-gray-200 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100 dark:focus:ring-gray-700 transition-colors shadow-sm">
                                    Launch Workspace
                                </button>
                            </form>
                        @else
                            <a href="{{ route('tenant.dashboard') }}" class="flex-1 inline-flex justify-center items-center px-4 py-2 text-sm font-semibold text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 focus:ring-4 focus:ring-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50 transition-colors">
                                Go to Dashboard
                            </a>
                        @endif

                        @if($company->owner_id == Auth::id() || $company->pivot->role === 'company_admin')
                            <div class="flex items-center gap-1">
                                <a href="{{ route('tenant.user-companies.edit', $company->id) }}" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-white rounded-lg dark:hover:bg-gray-700 dark:hover:text-white transition-all" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <a href="{{ route('tenant.company-settings.edit', $company->id) }}" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-white rounded-lg dark:hover:bg-gray-700 dark:hover:text-indigo-400 transition-all" title="Settings">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </a>
                                @if($company->owner_id == Auth::id())
                                    <form action="{{ route('tenant.user-companies.destroy', $company->id) }}" method="POST" onsubmit="return confirm('Delete this workspace permanently?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-white rounded-lg dark:hover:bg-gray-700 dark:hover:text-red-400 transition-all" title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-dashboard.layout.default>
