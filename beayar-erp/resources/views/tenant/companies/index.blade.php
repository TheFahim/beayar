<x-dashboard.layout.default title="My Companies">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Workspaces</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your companies and switch between workspaces.</p>
            </div>
            @can('create_companies')
            <a href="{{ route('tenant.user-companies.create') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900 transition-colors shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create Workspace
            </a>
            @endcan
        </div>

        @if(session('success'))
            <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 border border-green-200 dark:border-green-800" role="alert">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/></svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($companies as $company)
                @php
                    $isActive = Auth::user()->current_tenant_company_id == $company->id;
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border {{ $isActive ? 'border-blue-500 ring-2 ring-blue-500 ring-offset-2 dark:ring-offset-gray-900' : 'border-gray-200 dark:border-gray-700' }} hover:shadow-md transition-shadow duration-200 overflow-hidden flex flex-col group relative">

                    @if($isActive)
                        <div class="absolute top-0 right-0 mt-4 mr-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shadow-sm">
                                <span class="w-1.5 h-1.5 bg-blue-600 rounded-full mr-1.5 animate-pulse"></span>
                                Current Workspace
                            </span>
                        </div>
                    @endif

                    <div class="p-6 flex-1 {{ $isActive ? 'bg-blue-50/10 dark:bg-blue-900/10' : '' }}">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="shrink-0">
                                <img class="h-14 w-14 rounded-xl object-cover bg-gray-50 dark:bg-gray-700 border border-gray-100 dark:border-gray-600"
                                     src="{{ $company->logo ? asset('storage/'.$company->logo) : asset('assets/images/app-logo.jpeg') }}"
                                     alt="{{ $company->name }}">
                            </div>
                            <div class="pt-1">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ $company->name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">
                                    {{ $company->email ?? 'No email provided' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 mt-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                <svg class="w-3 h-3 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                {{ $company->pivot->role === 'company_admin' ? 'Admin' : 'Member' }}
                            </span>
                            @if($company->owner_id == Auth::id())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                    <svg class="w-3 h-3 mr-1 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                    Owner
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center gap-3">
                        @if(!$isActive)
                            <form action="{{ route('companies.switch', $company->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900 transition-colors shadow-sm">
                                    Launch
                                </button>
                            </form>
                        @else
                            <a href="{{ route('tenant.dashboard') }}" class="flex-1 inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 focus:ring-4 focus:ring-blue-300 dark:bg-blue-900/50 dark:text-blue-300 dark:hover:bg-blue-900 transition-colors">
                                Dashboard
                            </a>
                        @endif

                        @if($company->owner_id == Auth::id() || $company->pivot->role === 'company_admin')
                            <div class="flex items-center gap-1 border-l border-gray-200 dark:border-gray-700 pl-3 ml-1">
                                <a href="{{ route('tenant.user-companies.edit', $company->id) }}" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 transition-colors" title="Settings">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </a>

                                @if($company->owner_id == Auth::id())
                                    <form action="{{ route('tenant.user-companies.destroy', $company->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this company? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg dark:text-gray-400 dark:hover:text-red-400 dark:hover:bg-red-900/30 transition-colors" title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            @can('create_companies')
            <!-- Add New Card -->
            <a href="{{ route('tenant.user-companies.create') }}" class="group flex flex-col items-center justify-center p-8 bg-gray-50 dark:bg-gray-800/30 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/10 transition-all duration-200 min-h-[200px]">
                <div class="h-16 w-16 rounded-full bg-white dark:bg-gray-700 flex items-center justify-center shadow-sm group-hover:scale-110 group-hover:shadow-md transition-all duration-200 mb-4 text-gray-400 group-hover:text-blue-500 dark:text-gray-400 dark:group-hover:text-blue-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Create New Workspace</h3>
                <p class="mt-1 text-sm text-center text-gray-500 dark:text-gray-400 group-hover:text-blue-600/70 dark:group-hover:text-blue-400/70">Start a new organization</p>
            </a>
            @endcan
        </div>
    </div>
</x-dashboard.layout.default>
