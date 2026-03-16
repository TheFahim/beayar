<x-dashboard.layout.default :title="'Bill Reissued - ' . ($bill->invoice_no ?? 'N/A')">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.bills.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Bills
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Reissued" />
    </x-dashboard.ui.bread-crumb>

    <div class="max-w-2xl mx-auto p-6">
        <!-- Success Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-green-500 px-6 py-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">Bill Reissued Successfully</h1>
                <p class="text-green-100 mt-2">A new bill has been created from the cancelled invoice</p>
            </div>

            <!-- Details -->
            <div class="p-6">
                <!-- Reissue Chain Info -->
                @if($bill->reissuedFrom)
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="block text-xs text-blue-600 dark:text-blue-400 font-medium uppercase">Reissued From</span>
                            <span class="block text-sm font-semibold text-blue-800 dark:text-blue-200 line-through">{{ $bill->reissuedFrom->invoice_no }}</span>
                        </div>
                        <div class="text-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </div>
                        <div class="text-right">
                            <span class="block text-xs text-blue-600 dark:text-blue-400 font-medium uppercase">New Invoice</span>
                            <span class="block text-sm font-semibold text-blue-800 dark:text-blue-200">{{ $bill->invoice_no }}</span>
                        </div>
                    </div>
                </div>
                @endif

                <div class="space-y-4">
                    <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">New Invoice Number</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $bill->invoice_no }}</span>
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
                        <span class="text-sm text-gray-500 dark:text-gray-400">Bill Amount</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">৳ {{ number_format($bill->total_amount ?? 0, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Bill Date</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $bill->bill_date?->format('d/m/Y') ?? 'N/A' }}</span>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="mt-6 flex justify-center">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Draft - Ready for Editing
                    </span>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                    @can('update', $bill)
                    <a href="{{ route('tenant.bills.edit', $bill) }}"
                        class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Bill
                    </a>
                    @endcan

                    @can('issue', $bill)
                    <form action="{{ route('tenant.bills.issue', $bill) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Are you sure you want to issue this bill?')"
                                class="inline-flex items-center justify-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors w-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Issue Bill
                        </button>
                    </form>
                    @endcan

                    <a href="{{ route('tenant.bills.show', $bill) }}"
                        class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        View Bill
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="mt-6 bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
            <div class="flex">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Reissue Complete</h3>
                    <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                        The new bill has been created as a draft. Review and edit the bill if needed, then issue it when ready.
                        The reissue chain is tracked for audit purposes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.layout.default>
