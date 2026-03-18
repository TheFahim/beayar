<x-dashboard.layout.default :title="'Reissue Bill - ' . ($bill->invoice_no ?? 'N/A')">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.bills.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Bills
            </a>
        </li>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.bills.cancelled', $bill) }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                {{ $bill->invoice_no }} (Cancelled)
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Reissue" />
    </x-dashboard.ui.bread-crumb>

    <div class="max-w-3xl mx-auto p-6">
        <!-- Header Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Reissue Cancelled Bill</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Create a new bill from cancelled invoice {{ $bill->invoice_no }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Original Bill Info -->
            <div class="p-6 bg-gray-50 dark:bg-gray-700/50">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Original Bill Details</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Cancelled Invoice</span>
                        <span class="block text-sm font-semibold text-gray-900 dark:text-white line-through">{{ $bill->invoice_no }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Customer</span>
                        <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ $bill->quotation?->customer?->company?->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Bill Type</span>
                        <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ ucfirst($bill->bill_type) }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Amount</span>
                        <span class="block text-sm font-semibold text-gray-900 dark:text-white">৳ {{ number_format($bill->total_amount ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reissue Form -->
        <form action="{{ route('tenant.bills.reissue', $bill) }}" method="POST">
            @csrf
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">New Bill Information</h2>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Invoice Number -->
                    <div>
                        <label for="invoice_no" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            New Invoice Number <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="invoice_no"
                               name="invoice_no"
                               value="{{ $nextInvoiceNo }}"
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               required>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Suggested invoice number based on current sequence
                        </p>
                    </div>

                    <!-- Bill Date -->
                    <div>
                        <label for="bill_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Bill Date <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="bill_date"
                               name="bill_date"
                               value="{{ now()->format('d/m/Y') }}"
                               class="flowbite-datepicker w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               required>
                    </div>

                    <!-- Payment Received Date -->
                    <div>
                        <label for="payment_received_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Payment Received Date
                        </label>
                        <input type="text"
                               id="payment_received_date"
                               name="payment_received_date"
                               class="flowbite-datepicker w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Notes
                        </label>
                        <textarea id="notes"
                                  name="notes"
                                  rows="3"
                                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Optional notes about this reissuance...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <a href="{{ route('tenant.bills.cancelled', $bill) }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reissue Bill
                    </button>
                </div>
            </div>
        </form>

        <!-- Info Card -->
        <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
            <div class="flex">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Important Note</h3>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                        Reissuing will create a new draft bill with the same items and amounts as the cancelled bill.
                        You can edit the new bill before issuing it. The reissue chain will be tracked for audit purposes.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('js/reusableDatepicker.js') }}" defer></script>
    @endpush
</x-dashboard.layout.default>
