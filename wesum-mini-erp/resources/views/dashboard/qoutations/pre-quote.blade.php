<x-dashboard.layout.default title="Qoutations">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('pre.quotation') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Pre Quotations
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>


    <x-ui.card class="mx-auto">
        <div class="grid grid-cols-8 p-2 mb-4">
            <a href="{{ route('quotations.create', ['type' => 2]) }}"
                class="flex items-center gap-2 text-white bg-gradient-to-r from-green-400 via-green-500 to-green-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 font-medium rounded-lg text-sm px-4 py-2">
                <x-ui.svg.circle-plus />
                <span>Add New</span>
            </a>



        </div>
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
                                Qoutation NO.
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Customer
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

                        <th scope="col" class="px-6 py-3">
                            <span class="flex items-center">
                                Created
                                <x-ui.svg.sort-column class="w-4 h-4 ms-1" />
                            </span>
                        </th>

                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">Action</span>
                        </th>


                    </tr>
                </thead>
                <tbody>
                    @foreach ($quotations as $item)
                        <tr
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <span>{{ $loop->iteration }}</span>

                                    <!-- Heroicon: check-circle -->
                                    @if ($item->challan != null)
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 text-green-500"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                            </td>
                            <th scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $item->quotation_no }}
                            </th>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                <div class="flex flex-col space-y-2">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="truncate max-w-[150px]"
                                            title="{{ $item->customer->customer_name }}">
                                            {{ strlen($item->customer->customer_name) > 15 ? substr($item->customer->customer_name, 0, 15) . '...' : $item->customer->customer_name }}
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        {{-- <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2-1a1 1 0 00-1 1v12a1 1 0 001 1h8a1 1 0 001-1V4a1 1 0 00-1-1H6z" clip-rule="evenodd" />
                                        </svg> --}}
                                        <svg class="w-4 h-4 text-gray-800 dark:text-white" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd"
                                                d="M4 4a1 1 0 0 1 1-1h14a1 1 0 1 1 0 2v14a1 1 0 1 1 0 2H5a1 1 0 1 1 0-2V5a1 1 0 0 1-1-1Zm5 2a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H9Zm5 0a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1h-1Zm-5 4a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-1a1 1 0 0 0-1-1H9Zm5 0a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-1a1 1 0 0 0-1-1h-1Zm-3 4a2 2 0 0 0-2 2v3h2v-3h2v3h2v-3a2 2 0 0 0-2-2h-2Z"
                                                clip-rule="evenodd" />
                                        </svg>

                                        <span class="pl-2 truncate max-w-[150px]"
                                            title="{{ $item->customer->company_name }}">&nbsp;
                                            {{ strlen($item->customer->company_name) > 15 ? substr($item->customer->company_name, 0, 15) . '...' : $item->customer->company_name }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                @php
                                    $products = $item->products;
                                    $maxToShow = 2;
                                @endphp
                                @foreach ($products->take($maxToShow) as $index => $product)
                                    {{ $index + 1 }}. {{ strlen($product->name) > 35 ? substr($product->name, 0, 35) . '...' : $product->name }}<br>
                                @endforeach
                                @if ($products->count() > $maxToShow)
                                    ...
                                @endif
                            </td>
                            <td
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white text-right">
                                {{ $item->total }} &#2547;
                            </td>


                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                <div class="flex flex-col space-y-2">
                                    @role('admin')
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-purple-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ $item->user->username }}</span>
                                    </div>
                                    @endrole
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="whitespace-nowrap">{{ date('d/m/Y H:i', strtotime($item->created_at)) }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="grid grid-cols-2 px-2 py-4 gap-x-10">
                                <div>
                                    <a href="{{ route('quotations.show', $item->id) }}"
                                        class="font-medium text-blue-600 dark:text-green-500 hover:underline">View</a>
                                </div>
                                <div>
                                    @if ($item->challan == null)
                                        <a href="{{ route('quotations.edit', $item->id) }}"
                                            class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </x-ui.card>


</x-dashboard.layout.default>
