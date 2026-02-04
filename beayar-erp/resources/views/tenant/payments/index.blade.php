<x-dashboard.layout.default title="Payments">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.received-bills.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Payements
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>

    <x-ui.card class="mx-auto">
        {{-- <div class="grid grid-cols-8 p-2 mb-4">
            <a href="{{ route('tenant.received-bills.create') }}"
                class="flex items-center gap-2 text-white bg-gradient-to-r from-green-400 via-green-500 to-green-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 font-medium rounded-lg text-sm px-4 py-2">
                <x-ui.svg.circle-plus />
                <span>Add New</span>
            </a>
        </div> --}}
        <hr class="border-t border-gray-300 w-full">

        <div class="relative sm:rounded-lg py-3 px-2 mx-2">
            <table id="data-table-simple"
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
                                Bill No
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
                                Products
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3 text-right">
                            <span class="flex items-center">

                                Total
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>

                        </th>
                        <th scope="col" class="px-6 py-3 text-right">
                            <span class="flex items-center">

                                Paid
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>

                        </th>
                        <th scope="col" class="px-6 py-3 text-right">
                            Due

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
                            Last Payemnt
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">Action</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($receivedBills as $item)
                        <tr
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4">
                                {{ $loop->iteration }}
                            </td>
                            <th scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $item->bill_no }}
                            </th>

                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{-- {{ $item->challan->quotation->customer->company_name }} --}}
                                {{ strlen($item->challan->quotation->customer->company_name) > 15 ? substr($item->challan->quotation->customer->company_name, 0, 15) . '...' : $item->challan->quotation->customer->company_name }}

                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                @php
                                    $products = $item->challan->quotation->products;
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
                                {{ $item->payable }} ৳
                            </td>
                            <td
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white text-right">
                                {{ $item->paid }} ৳
                            </td>
                            <td
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white text-right">
                                @if ($item->due <= 0)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Paid
                                    </span>
                                @else
                                    {{ number_format($item->due, fmod($item->due, 1) != 0 ? 2 : 0) }}
                                @endif
                            </td>

                            @role('admin')
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $item->challan->quotation->user->username }}
                                </td>
                            @endrole

                            {{-- FIX 1: Added an @else to ensure the column is always rendered --}}
                            @if ($item->receivedBills->last()->received_date ?? false)
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ date('d/m/Y', strtotime($item->receivedBills->last()->received_date)) }}
                                </td>
                            @else
                                <td class="px-6 py-4"></td> {{-- Render empty cell if no date --}}
                            @endif

                            {{-- FIX 2: Removed the extra, unnecessary <td> that was here --}}

                            {{-- FIX 3: Placed the action in the correct final column with consistent padding --}}
                            <td class="px-6 py-4">
                                <a href="{{ route('tenant.received-bills.edit', $item->id) }}"
                                    class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Update</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $receivedBills->links() }}
        </div>
    </x-ui.card>
</x-dashboard.layout.default>
