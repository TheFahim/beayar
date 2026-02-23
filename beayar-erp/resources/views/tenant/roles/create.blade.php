<x-dashboard.layout.default title="Create Role">
    <div class="max-w-4xl mx-auto py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('tenant.roles.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                                <x-ui.svg.right-arrow class="w-4 h-4 mr-2 rotate-180" />
                                Back to Roles
                            </a>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Role</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Define a new role and assign permissions.</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <form action="{{ route('tenant.roles.store') }}" method="POST" x-data="{ search: '' }">
                @csrf

                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role Name</label>
                    <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm p-2.5 border" required placeholder="e.g. Sales Manager">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Permissions</label>
                        <div class="relative w-64">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                            <input type="text" x-model="search" class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Search permissions...">
                        </div>
                    </div>

                    <div class="space-y-6 max-h-[600px] overflow-y-auto p-2 border rounded-md dark:border-gray-700">
                        @foreach($groupedPermissions as $group => $permissions)
                            <div class="border rounded-lg p-4 dark:border-gray-700"
                                 x-show="search === '' || '{{ strtolower($group) }}'.includes(search.toLowerCase()) || Array.from($el.querySelectorAll('label')).some(l => l.innerText.toLowerCase().includes(search.toLowerCase()))">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">{{ $group }}</h4>
                                    <button type="button"
                                            @click="$el.closest('.border').querySelectorAll('input[type=checkbox]').forEach(el => el.checked = !el.checked)"
                                            class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                        Select All
                                    </button>
                                </div>

                                @if($group === 'Quotations')
                                <div class="mb-3 p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 text-xs rounded-lg flex items-start gap-2">
                                    <x-ui.svg.info-circle class="w-4 h-4 flex-shrink-0 mt-0.5" />
                                    <p>
                                        <strong>Tip:</strong> If you are granting "Create Quotations" access, consider also assigning:
                                        <ul class="list-disc list-inside mt-1 ml-1 space-y-0.5">
                                            <li><strong>Products:</strong> Create/View Products (to add items)</li>
                                            <li><strong>Images:</strong> Image Library (for product images)</li>
                                            <li><strong>Customers:</strong> Create/View Customers (to select clients)</li>
                                        </ul>
                                    </p>
                                </div>
                                @endif

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($permissions as $permission)
                                        <div class="flex items-start p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                             x-show="search === '' || '{{ strtolower($permission->name) }}'.includes(search.toLowerCase())">
                                            <div class="flex items-center h-5">
                                                <input id="perm_{{ $permission->id }}" name="permissions[]" value="{{ $permission->name }}" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="perm_{{ $permission->id }}" class="font-medium text-gray-900 dark:text-gray-300 select-none cursor-pointer">
                                                    {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('permissions')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('tenant.roles.index') }}" class="py-2.5 px-5 mr-2 mb-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</a>
                    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                        Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-dashboard.layout.default>
