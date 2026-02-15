<x-admin.layout.default title="Manage Tenants">
    <div class="p-4 bg-white block sm:flex items-center justify-between border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700 mb-4">
        <div class="w-full mb-1">
            <div class="mb-4">
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">All Tenants</h1>
            </div>
            <div class="sm:flex">
                <div class="flex items-center ml-auto space-x-2 sm:space-x-3">
                    <a href="{{ route('admin.tenants.create') }}" class="inline-flex items-center justify-center w-1/2 px-3 py-2 text-sm font-medium text-center text-white rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 sm:w-auto dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                        Add Tenant
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Company Name</th>
                    <th scope="col" class="px-6 py-3">Owner</th>
                    <th scope="col" class="px-6 py-3">Plan</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                    <tr
                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $tenant->name }}
                        </th>
                        <td class="px-6 py-4">
                            {{ $tenant->owner->name ?? 'N/A' }}
                            <div class="text-xs text-gray-400">{{ $tenant->owner->email ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            {{ $tenant->owner->subscription->plan->name ?? 'Free' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($tenant->status === 'active')
                                <span
                                    class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Active</span>
                            @elseif($tenant->status === 'suspended')
                                <span
                                    class="bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Suspended</span>
                            @else
                                <span
                                    class="bg-gray-100 text-gray-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">{{ ucfirst($tenant->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 flex items-center space-x-2">
                            <!-- View Details -->
                            <a href="{{ route('admin.tenants.show', $tenant) }}"
                                class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                View
                            </a>

                            <span class="text-gray-300">|</span>

                            <!-- Impersonate -->
                            <form action="{{ route('admin.tenants.impersonate', $tenant) }}" method="POST">
                                @csrf
                                <button type="submit" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                    Login as
                                </button>
                            </form>

                            <span class="text-gray-300">|</span>

                            <!-- Suspend/Activate -->
                            <form action="{{ route('admin.tenants.suspend', $tenant) }}" method="POST"
                                onsubmit="return confirm('Are you sure?')">
                                @csrf
                                <input type="hidden" name="status"
                                    value="{{ $tenant->status === 'suspended' ? 'active' : 'suspended' }}">
                                <button type="submit"
                                    class="font-medium {{ $tenant->status === 'suspended' ? 'text-green-600 dark:text-green-500' : 'text-red-600 dark:text-red-500' }} hover:underline">
                                    {{ $tenant->status === 'suspended' ? 'Activate' : 'Suspend' }}
                                </button>
                            </form>

                            <span class="text-gray-300">|</span>

                            <!-- Delete -->
                            <form action="{{ route('admin.tenants.destroy', $tenant) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this tenant? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No tenants found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">
            {{ $tenants->links() }}
        </div>
    </div>
</x-dashboard.layout.default>
