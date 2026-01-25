<x-dashboard.layout.default title="Customers">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('customers.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200">
                {{-- Using a user group icon for customers --}}
                <svg class="w-3 h-3 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                    <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
                </svg>
                Customers
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>

    <x-ui.card class="mx-auto">

        <div class="grid grid-cols-8 p-2 mb-4">
            <a href="{{ route('customers.create') }}"
                class="flex items-center gap-2 text-white bg-gradient-to-r from-green-400 via-green-500 to-green-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 font-medium rounded-lg text-sm px-4 py-2 transition-all duration-300 hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl">
                <x-ui.svg.circle-plus />
                <span>Add New</span>
            </a>
        </div>

        <hr class="border-t border-gray-300 dark:border-gray-600 w-full">

        <div class="relative sm:rounded-lg py-3 px-2 mx-2">
            <table id="data-table-simple" class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-white">
                <thead class="text-xs text-gray-700 uppercase bg-gray-300 dark:bg-gray-500 dark:text-gray-400">
                    <tr class="dark:text-white">
                        <th scope="col" class="px-3 py-3 w-16">
                            <span class="flex items-center">
                                S/L
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Customer Name
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Company
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Contact Info
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Customer No
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-3 py-3 w-32">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 hover:shadow-lg hover:scale-[1.01]">
                            <td class="px-3 py-4 font-medium text-gray-900 dark:text-white w-16">
                                {{ $loop->iteration }}
                            </td>
                            {{-- FIX: Changed <th> to <td> to prevent JS library conflicts --}}
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <div class="flex flex-col">
                                    <span class="font-semibold" title="{{ $customer->customer_name }}">{{ $customer->customer_name }}</span>
                                    @if($customer->designation)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $customer->designation }}</span>
                                    @endif
                                    @if($customer->attention)
                                        <span class="text-xs text-indigo-500 dark:text-indigo-400">Attn: {{ $customer->attention }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                {{ $customer->company->name }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col space-y-1">
                                    @if($customer->phone)
                                    <span class="text-xs inline-flex items-center gap-1.5"><svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 6.75z" /></svg> {{ $customer->phone }}</span>
                                    @endif
                                    @if($customer->email)
                                    <span class="text-xs inline-flex items-center gap-1.5"><svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg> {{ $customer->email }}</span>
                                    @endif
                                    @if(!$customer->phone && !$customer->email)
                                        <span class="text-gray-400 dark:text-gray-500 italic text-xs">Not available</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">{{ $customer->customer_no }}</span>
                            </td>
                            <td class="px-3 py-4 text-right w-32">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('customers.edit', $customer) }}"
                                        class="group relative px-2 py-1.5 text-green-600 hover:text-white font-medium text-xs rounded-md overflow-hidden transition-all duration-300 hover:shadow-lg hover:scale-105 active:scale-95"
                                        title="Edit Customer">
                                        <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-green-500 to-green-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                                        <span class="relative flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </span>
                                    </a>
                                    @if ($customer->quotations_count == 0)
                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" id="delete-form-{{ $customer->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" form="delete-form-{{ $customer->id }}"
                                            class="group relative px-2 py-1.5 text-red-600 hover:text-white font-medium text-xs rounded-md overflow-hidden transition-all duration-300 hover:shadow-lg hover:scale-105 active:scale-95 delete-button"
                                            title="Delete Customer">
                                            <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-red-500 to-red-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                                            <span class="relative flex items-center gap-1">
                                                <svg class="w-3 h-3 transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Delete
                                            </span>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty

                    @endforelse
                </tbody>
            </table>
        </div>

    </x-ui.card>

</x-dashboard.layout.default>
