<x-dashboard.layout.default :title="'Bill Cancelled - ' . ($bill->invoice_no ?? 'N/A')">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.bills.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Bills
            </a>
        </li>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.bills.show', $bill) }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                {{ $bill->invoice_no }}
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Cancelled" />
    </x-dashboard.ui.bread-crumb>

    <div class="max-w-2xl mx-auto p-6">
        <!-- Success Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-red-500 px-6 py-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">Bill Cancelled</h1>
                <p class="text-red-100 mt-2">The bill has been successfully cancelled</p>
            </div>

            <!-- Details -->
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Cancelled Invoice</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white line-through">{{ $bill->invoice_no }}</span>
                    </div>

                    <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Customer</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $bill->quotation?->customer?->company?->name ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Bill Type</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ ucfirst($bill->bill_type) }}</span>
                    </div>

                    <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Cancelled Amount</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">৳ {{ number_format($bill->total_amount ?? 0, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Cancellation Date</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ now()->format('d/m/Y H:i') }}</span>
                    </div>

                    @if($bill->notes)
                    <div class="py-3 border-b border-gray-200 dark:border-gray-700">
                        <span class="block text-sm text-gray-500 dark:text-gray-400 mb-1">Cancellation Notes</span>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $bill->notes }}</p>
                    </div>
                    @endif
                </div>

                <!-- Status Badge -->
                <div class="mt-6 flex justify-center">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Cancelled
                    </span>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                    @can('reissue', $bill)
                    <a href="{{ route('tenant.bills.reissue.form', $bill) }}"
                        class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reissue Bill
                    </a>
                    @endcan

                    <a href="{{ route('tenant.bills.index') }}"
                        class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        Back to Bills
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
            <div class="flex">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">What's Next?</h3>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                        If this bill was cancelled by mistake, you can reissue it with a new invoice number.
                        The reissued bill will be created as a draft and will reference this cancelled bill.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.layout.default>
