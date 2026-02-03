<x-dashboard.layout.default title="Edit Advance Bill">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.bills.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Bills
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Edit Advance Bill" />
    </x-dashboard.ui.bread-crumb>

    <form
        x-data='advanceBillsForm({ quotation: @json($quotation), activeRevision: @json($activeRevision) })'
        x-init="advancePercentage = {{ (float) ($bill->bill_percentage ?? 0) }};
        calculatedAdvanceAmount = {{ (float) ($bill->total_amount ?? 0) }};" id="advanceBillForm" class="space-y-6" action="{{ route('tenant.bills.update-advance', $bill) }}"
        method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="bill_type" value="advance">
        <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
        <input type="hidden" name="quotation_revision_id" value="{{ $activeRevision->id }}">
        <input type="hidden" name="bill_percentage" id="bill_percentage" :value="advancePercentage">
        <input type="hidden" name="total_amount" value="{{ $activeRevision->total }}">
        <input type="hidden" name="bill_amount" id="bill_amount_hidden" :value="calculatedAdvanceAmount">

        <div class="max-w-7xl mx-auto space-y-6">
            <div class="grid lg:grid-cols-12 gap-4">
                <div class="lg:col-span-4">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden h-full">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-white">Quotation Details</h3>
                            </div>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <span
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">Quotation
                                    No.</span>
                                <span class="block text-sm font-bold text-gray-900 dark:text-white truncate"
                                    title="{{ $quotation->quotation_no }}">
                                    {{ $quotation->quotation_no }}
                                </span>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <label for="po_no"
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">PO
                                    No</label>
                                <x-ui.form.input name="po_no" id="po_no" type="text" class="w-full text-sm"
                                    maxlength="255" value="{{ old('po_no', $quotation->po_no) }}"
                                    placeholder="Enter PO No" />
                            </div>

                            <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                                <span
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Customer
                                    Information</span>
                                <div class="space-y-2">
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate"
                                            title="{{ $quotation->customer->customer_name }}">
                                            {{ $quotation->customer->customer_name }}
                                        </p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 truncate mt-0.5"
                                            title="{{ $quotation->customer->designation }}">
                                            {{ $quotation->customer->designation }}
                                        </p>
                                    </div>
                                    <div>
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-md bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 truncate max-w-full"
                                            title="{{ $quotation->customer->company->name }}">
                                            {{ $quotation->customer->company->name }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                                <span
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Quotation
                                    Total</span>
                                <div
                                    class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border border-green-200 dark:border-green-700">
                                    <span class="block text-lg font-bold text-green-800 dark:text-green-300">
                                        {{ $activeRevision->type == 'via' ? $activeRevision->currency : 'BDT' }} {{ number_format($activeRevision->total, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 space-y-4">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-amber-500 to-orange-500 p-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-white">Advance Bill Information</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid md:grid-cols-3 gap-4 mb-6">
                                <div>
                                    <label for="calculated_advance_amount"
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Advance
                                        Amount</label>
                                    <div class="relative">
                                        <x-ui.form.input name="bill_amount" id="calculated_advance_amount"
                                            type="number"
                                            class="w-full pr-12 text-sm bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700 text-blue-800 dark:text-blue-300 font-semibold"
                                            step="0.01" min="0" max="{{ $activeRevision->total }}" required
                                            x-model="calculatedAdvanceAmount" @input="calculatePercentageFromAmount"
                                            @blur="normalizeAdvanceAmount"
                                            value="{{ old('bill_amount', $bill->total_amount) }}" />
                                        <div
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <span class="text-sm text-blue-600 dark:text-blue-400">{{ $activeRevision->type == 'via' ? $activeRevision->currency : 'BDT' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="advance_percentage"
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Advance
                                        Percentage</label>
                                    <div class="relative">
                                        <x-ui.form.input name="advance_percentage" id="advance_percentage"
                                            type="number" class="w-full pr-10 text-sm" step="0.01"
                                            min="1" max="100" x-model="advancePercentage"
                                            @input="calculateAdvanceAmount"
                                            value="{{ old('advance_percentage', $bill->bill_percentage) }}" />
                                        <span
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm pointer-events-none">%</span>
                                    </div>
                                </div>
                                <div>
                                    <label for="due_amount"
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Due
                                        Amount</label>
                                    <div class="relative">
                                        <x-ui.form.input name="due" id="due_amount" type="number"
                                            class="w-full pr-12 text-sm bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700 text-green-800 dark:text-green-300 font-semibold"
                                            readonly step="0.01"
                                            x-bind:value="(getRevisionTotal() - (parseFloat(calculatedAdvanceAmount) || 0)).toFixed(2)"
                                            value="{{ old('due', $bill->due) }}" />
                                        <div
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <span class="text-sm text-green-600 dark:text-green-400">{{ $activeRevision->type == 'via' ? $activeRevision->currency : 'BDT' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 p-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-white">Basic Information</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label for="invoice_no"
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Invoice
                                        Number</label>
                                    <x-ui.form.input name="invoice_no" id="invoice_no" type="text"
                                        class="text-sm" placeholder="e.g., ADV-001"
                                        value="{{ old('invoice_no', $bill->invoice_no) }}" required />
                                </div>
                                <div>
                                    <label for="bill_date"
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Bill
                                        Date</label>
                                    <input type="text" name="bill_date" id="bill_date"
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 flowbite-datepicker"
                                        value="{{ old('bill_date', $bill->bill_date ? date('d/m/Y', strtotime($bill->bill_date)) : '') }}"
                                        required>
                                </div>
                                <div>
                                    <label for="payment_received_date"
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Payment
                                        Received Date</label>
                                    <input type="text" name="payment_received_date" id="payment_received_date"
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 flowbite-datepicker"
                                        value="{{ old('payment_received_date', $bill->payment_received_date ? date('d/m/Y', strtotime($bill->payment_received_date)) : '') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-500 to-gray-600 p-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-white">Notes & Terms</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="mb-4">
                                <label for="notes"
                                    class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Notes</label>
                                <textarea name="notes" id="notes" rows="4" maxlength="500"
                                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                    placeholder="Add any additional notes">{{ old('notes', $bill->notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 p-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-white">Bill Summary</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid md:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400"
                                        id="summary-total-amount" x-text="formatCurrency(calculatedAdvanceAmount)">{{ $activeRevision->type == 'via' ? $activeRevision->currency : 'BDT' }}
                                        0.00</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Advance Amount</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400"
                                        id="summary-due-amount"
                                        x-text="formatCurrency((getRevisionTotal() - (parseFloat(calculatedAdvanceAmount) || 0)).toFixed(2))">
                                        {{ $activeRevision->type == 'via' ? $activeRevision->currency : 'BDT' }} 0.00</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Due Amount</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                                        id="summary-percentage"
                                        x-text="`${(parseFloat(advancePercentage)||0).toFixed(2)}%`">0%</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Advance %</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg shadow">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    @endpush
</x-dashboard.layout.default>
