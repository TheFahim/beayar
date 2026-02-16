<x-dashboard.layout.default title="My Companies">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <span class="inline-flex items-center text-sm font-medium text-gray-700 dark:text-gray-400">
                <svg class="w-3 h-3 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M19.728 10.686c-2.38 2.256-6.153 3.381-9.875 3.381-3.722 0-7.4-1.126-9.571-3.371L0 10.437V18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-7.6l-.272.286Z"/>
                    <path d="M17.5 2.5a.5.5 0 0 0 0-1h-15a.5.5 0 0 0 0 1v5.308l.374.343c1.964 1.8 5.736 2.916 9.126 2.916s7.2-1.126 9.166-2.921l.334-.312V2.5Z"/>
                </svg>
                My Companies
            </span>
        </li>
    </x-dashboard.ui.bread-crumb>

    <x-ui.card class="mx-auto">
        <div class="flex justify-between items-center p-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                Workspaces
            </h3>
            <a href="{{ route('tenant.user-companies.create') }}" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                Create New Company
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
            @foreach($companies as $company)
                <div class="max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 {{ Auth::user()->current_user_company_id == $company->id ? 'ring-2 ring-blue-500' : '' }}">
                    <div class="flex justify-between items-start mb-2">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $company->name }}</h5>
                        @if(Auth::user()->current_user_company_id == $company->id)
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Current</span>
                        @endif
                    </div>
                    
                    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">
                        Role: <span class="font-semibold">{{ $company->pivot->role === 'company_admin' ? 'Admin' : 'Employee' }}</span>
                    </p>

                    <div class="flex flex-col gap-2">
                        @if(Auth::user()->current_user_company_id != $company->id)
                            <form action="{{ route('companies.switch', $company->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                    Switch to Workspace
                                    <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                                    </svg>
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('companies.switch', $company->id) }}" method="POST" class="hidden" id="switch-to-manage-{{ $company->id }}">
                            @csrf
                        </form>
                        
                        {{-- If current company, show Manage Members link directly. If not, we might need to switch first or handle context awareness --}}
                         @if(Auth::user()->current_user_company_id == $company->id)
                            <a href="{{ route('company-members.index') }}" class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-center text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700">
                                Manage Team
                            </a>
                        @else
                            {{-- Optional: Add logic to switch then redirect --}}
                        @endif

                        <div class="flex gap-2 mt-2">
                            @if($company->owner_id == Auth::id() || $company->pivot->role === 'company_admin')
                                <a href="{{ route('tenant.user-companies.edit', $company->id) }}" class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-center text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700">
                                    Edit
                                </a>
                            @endif

                            @if($company->owner_id == Auth::id())
                                <form action="{{ route('tenant.user-companies.destroy', $company->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this company? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-ui.card>
</x-dashboard.layout.default>
