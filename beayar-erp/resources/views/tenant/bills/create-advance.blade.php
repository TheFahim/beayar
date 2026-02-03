<x-dashboard.layout.default title="Create Advance Bill">
    <!-- Breadcrumb -->
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition duration-150">
                <x-ui.svg.qutation class="w-3 h-3 me-2" />
                Quotations
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Create Advance Bill" />
    </x-dashboard.ui.bread-crumb>

    <!-- Compact Sticky Action Bar -->
    <div class="sticky top-20 z-40 mb-4 animate-fade-in-up">
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/95 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 p-4 backdrop-blur-sm">
            <div class="flex-1 min-w-0">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-0.5">Create Advance Bill</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400">Advance payment for quotation</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <a href="{{ route('tenant.quotations.index') }}"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 font-medium rounded-lg shadow-sm hover:shadow transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span class="text-sm">Back to Quotations</span>
                </a>
                <button type="submit" form="advanceBillForm"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium rounded-lg shadow hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm font-semibold">Create Advance Bill</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Validation Errors Alert -->
    @if ($errors->any())
        <div class="animate-fade-in-up mb-4">
            <div
                class="bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-900/30 border border-red-300 dark:border-red-700 rounded-xl shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-red-500 to-red-600 p-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-white">Validation Errors</h3>
                    </div>
                    <span class="px-2 py-1 bg-white/20 backdrop-blur-sm text-white text-xs font-bold rounded-md">
                        {{ $errors->count() }} {{ Str::plural('error', $errors->count()) }}
                    </span>
                </div>
                <div class="p-4">
                    <div class="max-h-64 overflow-y-auto custom-scrollbar pr-1">
                        <ul class="space-y-2">
                            @foreach ($errors->all() as $error)
                                <li
                                    class="flex items-start gap-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                                    <div class="mt-0.5 flex-shrink-0">
                                        <svg class="w-4 h-4 text-red-500 dark:text-red-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-red-700 dark:text-red-300">{{ $error }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="animate-fade-in-up mb-4">
            <div
                class="bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-xl shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 p-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-white">Business Rule Alert</h3>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-sm font-medium text-yellow-700 dark:text-yellow-300">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <form
        x-data="{
            quotation: {{ json_encode($quotation) }},
            activeRevision: {{ json_encode($activeRevision) }},
            advancePercentage: {{ $activeRevision->type === 'via' ? 100 : 50 }},
            calculatedAdvanceAmount: '0.00',
            loading: false,
            errors: {
                po_no: null,
                advancePercentage: null,
                invoice_no: null,
                bill_date: null,
                payment_received_date: null,
                due: null,
            },

            init() {
                this.$nextTick(() => {
                    this.calculateAdvanceAmount();
                });
            },

            getRevisionTotal() {
                const total = parseFloat(this.activeRevision?.total ?? this.activeRevision?.total_amount ?? 0);
                return isNaN(total) ? 0 : total;
            },

            calculateAdvanceAmount() {
                this.errors.advancePercentage = null;
                const pct = parseFloat(this.advancePercentage);
                const total = this.getRevisionTotal();
                if (!Number.isFinite(pct) || pct < 1 || pct > 100) {
                    this.errors.advancePercentage = 'Advance percentage must be between 1 and 100';
                }
                const amount = (Number.isFinite(pct) ? (total * pct) / 100 : 0);
                this.calculatedAdvanceAmount = (amount || 0).toFixed(2);
                this.computeDue();
            },

            calculatePercentageFromAmount() {
                const total = this.getRevisionTotal();
                let amount = parseFloat(this.calculatedAdvanceAmount);
                if (!Number.isFinite(amount) || amount < 0) amount = 0;
                if (amount > total) {
                    amount = total;
                    this.calculatedAdvanceAmount = String(amount);
                }
                const pct = total > 0 ? (amount / total) * 100 : 0;
                this.advancePercentage = (pct || 0).toFixed(2);
                // Percentage validity notice (optional UI message)
                this.errors.advancePercentage = null;
                if (pct < 1 || pct > 100) {
                    this.errors.advancePercentage = 'Advance percentage must be between 1 and 100';
                }

                this.computeDue();
            },

            normalizeAdvanceAmount() {
                const total = this.getRevisionTotal();
                let amount = parseFloat(this.calculatedAdvanceAmount);
                if (!Number.isFinite(amount) || amount < 0) amount = 0;
                if (amount > total) amount = total;
                this.calculatedAdvanceAmount = (amount || 0).toFixed(2);

                const pct = total > 0 ? (amount / total) * 100 : 0;
                this.advancePercentage = (pct || 0).toFixed(2);
                this.errors.advancePercentage = null;
                if (pct < 1 || pct > 100) {
                    this.errors.advancePercentage = 'Advance percentage must be between 1 and 100';
                }

                this.computeDue();
            },

            computeDue() {
                const total = this.getRevisionTotal();
                const adv = parseFloat(this.calculatedAdvanceAmount) || 0;
                const due = (total - adv);
                const dueField = document.getElementById('due_hidden');
                if (dueField) dueField.value = due.toFixed(2);
            },

            validateClient() {
                this.errors.po_no = null;
                this.errors.invoice_no = null;
                this.errors.bill_date = null;
                this.errors.payment_received_date = null;
                this.errors.due = null;

                const poNo = document.getElementById('po_no')?.value || '';
                const invoice = document.getElementById('invoice_no')?.value || '';
                const billDate = document.getElementById('bill_date')?.value || '';
                const paymentDate = document.getElementById('payment_received_date')?.value || '';
                const dueField = document.getElementById('due_amount')?.value || '';

                let ok = true;
                if (!poNo.trim()) {
                    this.errors.po_no = 'PO number is required';
                    ok = false;
                }

                if (!billDate) {
                    this.errors.bill_date = 'Bill date is required';
                    ok = false;
                }
                // payment_received_date is optional
                if (isNaN(parseFloat(dueField))) {
                    this.errors.due = 'Due must be numeric';
                    ok = false;
                }
                return ok;
            },

            validateForm(event) {
                const pct = parseFloat(this.advancePercentage) || 0;
                const billDate = document.getElementById('bill_date')?.value;
                const calc = parseFloat(this.calculatedAdvanceAmount) || 0;

                let ok = true;
                if (pct < 1 || pct > 100) ok = false;

                if (!ok) {
                    event.preventDefault();
                    this.showWarning('Please fix validation errors before submitting');
                    return false;
                }
                return true;
            },

            toNumber(v) {
                const n = parseFloat(v);
                return isNaN(n) ? 0 : n;
            },
            formatCurrency(amount) {
                const n = parseFloat(amount);
                const cur = (this.activeRevision?.type === 'via') ? (this.activeRevision?.currency || 'BDT') : 'BDT';
                if (isNaN(n)) return `${cur} 0.00`;
                return `${cur} ${n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            },
            showError(message) {
                if (typeof window.showToast === 'function') {
                    window.showToast('error', message);
                } else {
                    alert('Error: ' + message);
                }
            },
            showWarning(message) {
                if (typeof window.showToast === 'function') {
                    window.showToast('warning', message);
                } else {
                    console.warn(message);
                }
            }
        }"
        id="advanceBillForm" class="space-y-6" action="{{ route('tenant.quotations.bills.advance.store', $quotation) }}"
        method="POST">
        @csrf
        <input type="hidden" name="bill_type" value="advance">
        <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
        <input type="hidden" name="quotation_revision_id" value="{{ $activeRevision->id }}">
        <input type="hidden" name="bill_percentage" id="bill_percentage" :value="advancePercentage">
        <input type="hidden" name="total_amount"  value="{{ $activeRevision->total }}">
        <input type="hidden" name="bill_amount" id="bill_amount_hidden" :value="calculatedAdvanceAmount">

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Top Section: Info Cards -->
            <div class="grid lg:grid-cols-12 gap-4">
                <!-- Quotation & Customer Card -->
                <div class="lg:col-span-4">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden h-full hover:shadow-md transition-shadow duration-300">
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
                                    No <span class="text-red-500">*</span></label>
                                <x-ui.form.input name="po_no" id="po_no" type="text"
                                    class="w-full text-sm" maxlength="255" required
                                    value="{{ old('po_no', $quotation->po_no) }}" placeholder="Enter PO No" />
                                @error('po_no')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div
                                class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/30 rounded-lg p-3 border border-purple-200 dark:border-purple-700">
                                <span
                                    class="block text-xs font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wide mb-1">Bill
                                    Type
                                </span>
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-md bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                    Advance Bill
                                </span>
                                <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">Initial payment before
                                    delivery</p>
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
                                            <svg class="w-2.5 h-2.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            {{ $quotation->customer->company->name }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                                <span
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Shipping
                                    Address</span>
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-md p-2.5">
                                    <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed"
                                        title="{{ $quotation->ship_to }}">
                                        {{ $quotation->ship_to }}
                                    </p>
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

                <!-- Right Column -->
                <div class="lg:col-span-8 space-y-4">
                    <!-- Advance Bill Information Section -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-300">
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
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                        Advance Amount <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <x-ui.form.input name="bill_amount"
                                            id="calculated_advance_amount" type="number"
                                            class="w-full pr-12 text-sm bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700 text-blue-800 dark:text-blue-300 font-semibold"
                                            step="0.01" min="0" max="{{ $activeRevision->total }}" required x-model="calculatedAdvanceAmount"
                                            @input="calculatePercentageFromAmount" @blur="normalizeAdvanceAmount" />
                                        <div
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <span class="text-sm text-blue-600 dark:text-blue-400">{{ $activeRevision->type == 'via' ? $activeRevision->currency : 'BDT' }}</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Enter advance amount; percentage is calculated automatically
                                    </p>
                                    @error('calculated_advance_amount')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="advance_percentage"
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                        Advance Percentage
                                    </label>
                                    <div class="relative">
                                        <x-ui.form.input name="advance_percentage" id="advance_percentage"
                                            type="number" class="w-full pr-10 text-sm"
                                            step="0.01" min="1" max="100" x-model="advancePercentage" @input="calculateAdvanceAmount" />
                                        <span
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm pointer-events-none">%</span>
                                    </div>
                                    @error('advance_percentage')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="due_amount"
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                        Due Amount
                                    </label>
                                    <div class="relative">
                                        <x-ui.form.input name="due" id="due_amount" type="number"
                                            class="w-full pr-12 text-sm bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700 text-green-800 dark:text-green-300 font-semibold"
                                            readonly step="0.01"
                                            x-bind:value="(getRevisionTotal() - (parseFloat(calculatedAdvanceAmount) || 0)).toFixed(2)" />
                                        <div
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <span class="text-sm text-green-600 dark:text-green-400">{{ $activeRevision->type == 'via' ? $activeRevision->currency : 'BDT' }}</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Remaining after advance: total − advance
                                    </p>
                                    @error('due')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- <!-- Installments Section -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-200">Installments</h4>
                                    <button type="button" id="add-installment" @click="addInstallment()"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add Installment
                                    </button>
                                </div>

                                <div id="installments-container" class="space-y-3">
                                    <template x-for="(inst, idx) in installments" :key="idx">
                                        <div
                                            class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                                            <div class="flex justify-between items-center mb-3">
                                                <h5 class="text-sm font-bold text-gray-700 dark:text-gray-200">
                                                    Installment <span x-text="idx+1"></span></h5>
                                                <button type="button" class="text-red-500 hover:text-red-700 text-sm"
                                                    @click="removeInstallment(idx)">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="grid md:grid-cols-3 gap-3">
                                                <div>
                                                    <label
                                                        class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">Amount
                                                        *</label>
                                                    <div class="relative">
                                                        <input type="number" :name="`installments[${idx}][amount]`"
                                                            x-model="inst.amount"
                                                            @input="updateTotalInstallmentAmount()"
                                                            class="w-full pr-8 text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                                            step="0.01" min="0.01" required>
                                                        <span
                                                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 text-xs">BDT</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">Due
                                                        Date *</label>
                                                    <input type="date" :name="`installments[${idx}][due_date]`"
                                                        x-model="inst.due_date" :min="minInstallmentDate"
                                                        class="w-full text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                                        required>
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">Description</label>
                                                    <input type="text" :name="`installments[${idx}][description]`"
                                                        x-model="inst.description"
                                                        class="w-full text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                                        placeholder="Optional description">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Total
                                            Installment Amount:</span>
                                        <span class="text-sm font-bold text-green-600 dark:text-green-400"
                                            id="total-installment-amount" x-text="totalInstallmentAmount">0.00</span>
                                    </div>
                                    <input type="hidden" name="total_installment_amount"
                                        id="total_installment_amount" :value="totalInstallmentAmount">
                                    @error('total_installment_amount')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div> --}}
                        </div>
                    </div>

                    <!-- Basic Bill Information -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-300">
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
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                        Invoice Number <span class="text-red-500">*</span>
                                    </label>
                                    <x-ui.form.input name="invoice_no" id="invoice_no" type="text"
                                        class="text-sm" placeholder="e.g., ADV-001" value="{{ old('invoice_no', $nextInvoiceNo) }}" required />
                                    @error('invoice_no')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="bill_date"
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                        Bill Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="bill_date" id="bill_date"
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 flowbite-datepicker"
                                        value="{{ date('d-m-Y') }}" required>
                                    @error('bill_date')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="payment_received_date"
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                        Payment Received Date
                                    </label>
                                    <input type="text" name="payment_received_date" id="payment_received_date"
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 flowbite-datepicker">
                                </div>
                            </div>


                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-300">
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
                                    class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                    Notes
                                </label>
                                <textarea name="notes" id="notes" rows="4" maxlength="500"
                                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 resize-none"
                                    placeholder="Add any additional notes about this advance bill..."></textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Provide any relevant details about payment terms, conditions, or special
                                    requirements for the advance payment.
                                </p>
                                @error('notes')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div
                                class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-200 dark:border-blue-700">
                                <h4 class="text-sm font-bold text-blue-800 dark:text-blue-300 mb-2">Advance Bill Terms
                                </h4>
                                <ul class="text-xs text-blue-700 dark:text-blue-400 space-y-1">
                                    <li>• Advance payment is due before delivery of goods</li>
                                    <li>• This bill represents the initial payment portion</li>
                                    <li>• Remaining balance will be billed upon delivery</li>
                                    <li>• Installments help spread the advance payment over time</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Summary Card -->
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
                                        x-text="formatCurrency((getRevisionTotal() - (parseFloat(calculatedAdvanceAmount) || 0)).toFixed(2))">{{ $activeRevision->type == 'via' ? $activeRevision->currency : 'BDT' }} 0.00</div>
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
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        {{-- Scripts are now handled via resources/js/advanceBills.js --}}
    @endpush
</x-dashboard.layout.default>
