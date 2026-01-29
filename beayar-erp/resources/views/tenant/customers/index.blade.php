<x-dashboard.layout.default title="Customers">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.customers.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200">
                <x-ui.svg.users class="h-3 w-3 me-2" />
                Customers
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>

    <x-ui.card class="mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-8 p-2 mb-4 gap-4">
            <div class="col-span-1 lg:col-span-2">
                <a href="{{ route('tenant.customers.create') }}"
                    class="flex items-center justify-center gap-2 text-white bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800 font-medium rounded-lg text-sm px-4 py-2 transition-all duration-300 hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl">
                    <x-ui.svg.circle-plus class="w-5 h-5" />
                    <span>Add Customer</span>
                </a>
            </div>
            
            <div class="col-span-1 lg:col-span-6">
                <form method="GET" action="{{ route('tenant.customers.index') }}" class="w-full">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                            placeholder="Search customers by name, email, or phone...">
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </form>
            </div>
        </div>

        <hr class="border-t border-gray-300 dark:border-gray-600 w-full">

        <div class="relative sm:rounded-lg py-3 px-2 mx-2 overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-white">
                <thead class="text-xs text-gray-700 uppercase bg-gray-300 dark:bg-gray-500 dark:text-gray-400">
                    <tr class="dark:text-white">
                        <th scope="col" class="px-3 py-3 w-16">S/L</th>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3">Email</th>
                        <th scope="col" class="px-6 py-3">Phone</th>
                        <th scope="col" class="px-6 py-3">Address</th>
                        <th scope="col" class="px-3 py-3 w-32 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200">
                            <td class="px-3 py-4 font-medium text-gray-900 dark:text-white w-16">
                                {{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $customer->name }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $customer->email }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $customer->phone ?? '-' }}
                            </td>
                            <td class="px-6 py-4 truncate max-w-xs" title="{{ $customer->address }}">
                                {{ $customer->address ?? '-' }}
                            </td>
                            <td class="px-3 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('tenant.customers.edit', $customer->id) }}" 
                                       class="p-2 bg-blue-100 text-blue-600 rounded-full hover:bg-blue-200 hover:text-blue-800 transition-colors"
                                       title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <button onclick="deleteCustomer({{ $customer->id }})" 
                                            class="p-2 bg-red-100 text-red-600 rounded-full hover:bg-red-200 hover:text-red-800 transition-colors"
                                            title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <x-ui.svg.users class="w-12 h-12 mb-3 opacity-50" />
                                    <p class="text-lg font-medium">No customers found</p>
                                    <p class="text-sm">Get started by creating your first customer.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 px-4 pb-4">
            {{ $customers->links() }}
        </div>
    </x-ui.card>

    <script>
        function deleteCustomer(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/customers/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Deleted!', 'Customer has been deleted.', 'success')
                            .then(() => window.location.reload());
                        } else {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    });
                }
            })
        }
    </script>
</x-dashboard.layout.default>
