<x-dashboard.layout.default title="Challans">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('challans.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Challans
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>

    <x-ui.card class="mx-auto">
        {{-- <div class="grid grid-cols-8 p-2 mb-4">
            <a href="{{ route('challans.create') }}"
                class="flex items-center gap-2 text-white bg-gradient-to-r from-green-400 via-green-500 to-green-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 font-medium rounded-lg text-sm px-4 py-2">
                <x-ui.svg.circle-plus />
                <span>Add New</span>
            </a>
        </div> --}}
        <hr class="border-t border-gray-300 w-full">

        <div class="relative sm:rounded-lg py-3 px-2 mx-2">
            <table id="team-members"
                class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-white datatable">
                <thead class="text-xs text-gray-700 uppercase bg-gray-300 dark:bg-gray-500 dark:text-gray-400">
                    <tr class="dark:text-white">
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                S/L
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Chalan NO.
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        {{-- <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Customer
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th> --}}
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Company
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Products
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3 text-right">
                            Total
                        </th>
                        @role('admin')
                            <th scope="col" class="px-6 py-3">
                                <span class="flex items-center">
                                    User
                                    <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                                </span>
                            </th>
                        @endrole
                        <th scope="col" class="px-6 py-3">
                            Created
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">Action</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($challans as $item)
                        <tr
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="flex items-center px-6 py-4">
                                <span>{{ $loop->iteration }}</span>
                                @if ($item->bill != null)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 text-green-500"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </td>
                            <th scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $item->challan_no }}
                            </th>
                            {{-- <td
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white text-center">
                                {{ $item->quotation->customer->customer_name }}
                            </td> --}}
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{-- {{ $item->quotation->customer->company_name }} --}}
                                {{ strlen($item->quotation->customer->company_name) > 15 ? substr($item->quotation->customer->company_name, 0, 15) . '...' : $item->quotation->customer->company_name }}
                            </td>

                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                @php
                                    $products = $item->quotation->products;
                                    $maxToShow = 2;
                                @endphp
                                @foreach ($products->take($maxToShow) as $index => $product)
                                    {{ $index + 1 }}. {{ $product->name }}<br>
                                @endforeach
                                @if ($products->count() > $maxToShow)
                                    ...
                                @endif
                            </td>

                            <td
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white text-right">
                                {{ $item->quotation->total }} &#2547;
                            </td>
                            @role('admin')
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->quotation->user->username }}
                                </td>
                            @endrole
                            <th scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ date('d/m/Y H:i', strtotime($item->created_at)) }}
                            </th>
                            <td class="grid grid-cols-2 px-2 py-4 gap-x-10">
                                <div>
                                    <a href="{{ route('challans.show', $item->id) }}"
                                        class="font-medium text-blue-600 dark:text-green-500 hover:underline">View</a>
                                </div>
                                <div>
                                    <a href="{{ route('challans.edit', $item->id) }}"
                                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-dashboard.layout.default>
