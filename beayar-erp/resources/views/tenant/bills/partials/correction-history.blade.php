@php
    // Build the correction chain
    $chain = collect();

    // Find the original bill in the chain
    $currentBill = $bill;
    while ($currentBill->reissuedFrom) {
        $currentBill = $currentBill->reissuedFrom;
    }

    // Now traverse forward to build the complete chain
    $originalBill = $currentBill;
    while ($currentBill) {
        $chain->push($currentBill);
        $currentBill = $currentBill->reissuedTo;
    }
@endphp

@if($chain->count() > 1)
<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-medium text-gray-900 dark:text-white flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Correction History
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            This bill has been cancelled and reissued {{ $chain->count() - 1 }} time(s)
        </p>
    </div>

    <div class="p-4">
        <div class="relative">
            <!-- Timeline Line -->
            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

            <!-- Timeline Items -->
            @foreach($chain as $index => $chainBill)
            <div class="relative flex items-start mb-4 last:mb-0">
                <!-- Timeline Dot -->
                <div class="relative z-10 flex items-center justify-center w-8 h-8 rounded-full
                    @if($chainBill->status === 'cancelled')
                        bg-red-100 dark:bg-red-900
                    @elseif($chainBill->id === $bill->id)
                        bg-green-100 dark:bg-green-900
                    @else
                        bg-gray-100 dark:bg-gray-700
                    @endif
                ">
                    @if($chainBill->status === 'cancelled')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    @elseif($chainBill->id === $bill->id)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    @endif
                </div>

                <!-- Content -->
                <div class="ml-4 flex-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white
                                @if($chainBill->status === 'cancelled')
                                    line-through
                                @endif
                            ">
                                {{ $chainBill->invoice_no }}
                            </span>
                            @if($chainBill->id === $bill->id)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Current
                                </span>
                            @elseif($chainBill->status === 'cancelled')
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Cancelled
                                </span>
                            @endif
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $chainBill->bill_date?->format('d/m/Y') ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="mt-1 flex items-center justify-between">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            ৳ {{ number_format($chainBill->total_amount ?? 0, 2) }}
                        </span>
                        @if($chainBill->id !== $bill->id)
                        <a href="{{ route('tenant.bills.show', $chainBill) }}"
                           class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                            View
                        </a>
                        @endif
                    </div>

                    @if($chainBill->status === 'cancelled' && $chainBill->notes)
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 italic">
                        {{ Str::limit($chainBill->notes, 100) }}
                    </p>
                    @endif
                </div>
            </div>

            @if(!$loop->last)
            <!-- Arrow between items -->
            <div class="ml-4 mb-4 flex items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
                <span class="ml-1 text-xs">Reissued</span>
            </div>
            @endif
            @endforeach
        </div>
    </div>

    @if($bill->status === 'cancelled')
    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
        <a href="{{ route('tenant.bills.reissue.form', $bill) }}"
           class="inline-flex items-center text-sm text-blue-600 dark:text-blue-400 hover:underline">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Reissue this bill
        </a>
    </div>
    @endif
</div>
@endif
