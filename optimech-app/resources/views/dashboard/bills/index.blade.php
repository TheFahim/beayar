<x-dashboard.layout.default title="Bills">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('bills.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Bills
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>



    <!-- Comprehensive Billing Dashboard -->
    {{-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Bills -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Bills</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $metrics['total_bills'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Amount -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Amount</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($metrics['total_amount_unique_by_quotation'] ?? 0, 2) }} &#2547;
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Paid -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Paid</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ number_format($metrics['total_paid'] ?? 0, 2) }} &#2547;
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Due -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Due</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                        {{ number_format($metrics['total_due_unique_by_quotation'] ?? ($metrics['total_due'] ?? 0), 2) }} &#2547;
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div> --}}

    {{-- <!-- Bill Type Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        @php
            $advanceBills = $bills->where('bill_type', 'advance');
            $regularBills = $bills->where('bill_type', 'regular');
            $runningBills = $bills->where('bill_type', 'running');
        @endphp

        <!-- Advance Bills -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Advance Bills</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $advanceBills->count() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format($advanceBills->sum('total_amount'), 2) }} &#2547;
                    </p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Regular Bills -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Regular Bills</p>
                    <p class="text-2xl font-bold text-slate-600 dark:text-slate-400">{{ $regularBills->count() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format($regularBills->sum('total_amount'), 2) }} &#2547;
                    </p>
                </div>
                <div class="w-12 h-12 bg-slate-100 dark:bg-slate-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Running Bills -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Running Bills</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $runningBills->count() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format($runningBills->sum('total_amount'), 2) }} &#2547;
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
            </div>
        </div>
    </div> --}}

    <x-ui.card class="mx-auto">
        <hr class="border-t border-gray-300 w-full">

        <div class="relative sm:rounded-lg py-2 px-1 mx-2 md:overflow-x-auto">
            <table id="data-table-simple"
                class="w-full table-fixed text-xs md:text-sm text-left rtl:text-right text-gray-500 dark:text-white datatable bills-table">
                <thead class="text-xs text-gray-700 uppercase bg-gray-300 dark:bg-gray-500 dark:text-gray-400">
                    <tr class="dark:text-white">
                        <th scope="col" class="px-2 py-2 w-10 md:w-12 text-center">S/L</th>
                        <th scope="col" class="px-2 py-2 w-32 md:w-40">Quotation No</th>
                        <th scope="col" class="px-2 py-2 w-40 md:w-44">Invoice & Type</th>
                        <th scope="col" class="px-2 py-2 w-28 md:w-32 text-right">Total Amount</th>
                        <th scope="col" class="px-2 py-2 w-28 md:w-32 text-right">Bill Amount</th>
                        <th scope="col" class="px-2 py-2 w-24 md:w-28 text-right">Due</th>
                        <th scope="col" class="px-2 py-2 w-24 md:w-28">Bill Date</th>
                        <th scope="col" class="px-2 py-2 w-24 md:w-28 hidden sm:table-cell">Payment Date</th>
                        <th scope="col" class="px-2 py-2 w-24 md:w-28"><span class="sr-only">Action</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bills as $item)
                        <tr
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-2 py-2 text-center">
                                {{ $loop->iteration }}
                            </td>
                            <td title="{{ $item->quotation->quotation_no ?? 'N/A' }}" class="px-2 py-2 font-medium text-gray-900 dark:text-white">
                                <span class="block truncate max-w-[10rem] md:max-w-[12rem]" title="{{ $item->quotation->quotation_no ?? 'N/A' }}">
                                    {{ $item->quotation->quotation_no ?? 'N/A' }}
                                </span>
                            </td>
                            <th scope="row"
                                class="px-2 py-2 font-medium text-gray-900 dark:text-white">
                                <div class="flex flex-col items-center text-center">
                                    <span class="font-medium truncate max-w-[10rem] md:max-w-[12rem]" title="{{ $item->invoice_no ?? 'N/A' }}">{{ $item->invoice_no ?? 'N/A' }}</span>
                                    <span @class([
                                        'inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold min-w-max',
                                        $item->bill_type === 'advance'
                                            ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200 ring-1 ring-emerald-200 dark:ring-emerald-700'
                                            : ($item->bill_type === 'running'
                                                ? 'bg-purple-50 text-purple-800 dark:bg-purple-900 dark:text-purple-200 ring-1 ring-purple-200 dark:ring-purple-700'
                                                : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200 ring-1 ring-slate-200 dark:ring-slate-700'),
                                    ])>
                                        @if($item->bill_type === 'advance')
                                            Advanced {{ $item->bill_percentage }}%
                                        @elseif($item->bill_type === 'running')
                                            Running Bill ({{ $item->bill_percentage }}%)
                                        @else
                                            Regular ({{ $item->bill_percentage }})
                                        @endif
                                    </span>
                                </div>
                            </th>
                            <td class="px-2 py-2 text-right font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                {{ number_format($item->total_amount ?? 0, 2) }} &#2547;
                            </td>
                            <td class="px-2 py-2 text-right font-medium text-green-600 dark:text-green-400 whitespace-nowrap">
                                {{ number_format($item->bill_amount ?? 0, 2) }} &#2547;
                            </td>
                            <td class="px-2 py-2 text-right font-medium text-red-600 dark:text-red-400 whitespace-nowrap">
                                {{ number_format($item->due ?? 0, 2) }} &#2547;
                            </td>
                            <td class="px-2 py-2">
                                {{ $item->bill_date ? date('d/m/Y', strtotime($item->bill_date)) : 'N/A' }}
                            </td>
                            <td class="px-2 py-2 hidden sm:table-cell">
                                {{ $item->payment_received_date ? date('d/m/Y', strtotime($item->payment_received_date)) : 'N/A' }}
                            </td>
                            <td class="px-2 py-2">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('bills.show', $item->id ?? 0) }}"
                                        class="text-blue-600 dark:text-green-500 hover:underline text-xs">View</a>
                                    @if(isset($latestByQuotation[$item->quotation_id]) && $latestByQuotation[$item->quotation_id] === $item->id)
                                        <a href="{{ route('bills.edit', $item->id ?? 0) }}"
                                            class="text-blue-600 dark:text-blue-500 hover:underline text-xs">Edit</a>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500 cursor-not-allowed text-xs">Edit</span>
                                    @endif
                                    @if(isset($latestByQuotation[$item->quotation_id]) && $latestByQuotation[$item->quotation_id] === $item->id)
                                        <form action="{{ route('bills.destroy', $item->id ?? 0) }}" method="POST" class="inline delete-button">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-500 hover:underline text-xs">Delete</button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500 cursor-not-allowed text-xs">Delete</span>
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
