<x-dashboard.layout.default title="Create Running Bill">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition duration-150">
                <x-ui.svg.qutation class="w-3 h-3 me-2" />
                Quotations
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Create Running Bill" />
    </x-dashboard.ui.bread-crumb>

    <div class="sticky top-20 z-40 mb-4 animate-fade-in-up">
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/95 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 p-4 backdrop-blur-sm">
            <div class="flex-1 min-w-0">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-0.5">Create Running Bill</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400">Running payment against an advance bill</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <a href="{{ route('quotations.index') }}"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 font-medium rounded-lg shadow-sm hover:shadow transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span class="text-sm">Back to Quotations</span>
                </a>
                <button type="submit" form="runningBillForm"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-medium rounded-lg shadow hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm font-semibold">Create Running Bill</span>
                </button>
            </div>
        </div>
    </div>

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
                    <span
                        class="px-2 py-1 bg-white/20 backdrop-blur-sm text-white text-xs font-bold rounded-md">{{ $errors->count() }}
                        {{ Str::plural('error', $errors->count()) }}</span>
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

    <form id="runningBillForm" class="space-y-6" action="{{ route('bills.store-running-bill', $quotation) }}"
        method="POST" x-data="{
            quotation: {{ json_encode($quotation) }},
            parentBill: {{ json_encode($parentBill) }},
            total: 0,
            billedPct: 0,
            remainPct: 100,
            runPct: 10,
            runAmt: 0,
            dueCurrent: 0,
            dueAfter: 0,
            remainAfterPct: 0,
            errors: { amount: '', percentage: '' },
            invoiceNo: '{{ old('invoice_no', $nextInvoiceNo) }}',
            isCalculating: false,
            init() {
                this.total = parseFloat(this.parentBill?.total_amount || 0);
                const children = Array.isArray(this.parentBill?.children) ? this.parentBill.children : [];
                const childrenSum = children.reduce((sum, c) => sum + parseFloat(c?.total_amount || 0), 0);
                const billedFromDb = parseFloat(this.parentBill?.bill_percentage || 0);
                const billedFromChildren = this.total > 0 ? ((childrenSum / this.total) * 100) : 0;
                this.billedPct = this.calculateBillPct();
                this.remainPct = Math.max(0, 100 - this.billedPct);
                const fallbackDue = this.parentBill.due;
                this.dueCurrent = parseFloat(this.parentBill.children[this.parentBill.children.length - 1]?.due ?? fallbackDue);
                this.runAmt = parseFloat(this.runAmt || 0);
                this.runPct = parseFloat(this.runPct || 0);
                this.dueAfter = Math.max(0, this.dueCurrent - this.runAmt);
                this.remainAfterPct = Math.max(0, 100 - this.billedPct - this.runPct);
                const base = this.parentBill?.invoice_no || '';
                const count = Array.isArray(this.parentBill?.children) ? this.parentBill.children.length : 0;
                const nextChar = String.fromCharCode(65 + (count || 0));
                this.invoiceNo = base ? `${base}-${nextChar}` : '';
                this.computeFromPercentage();

                {{-- console.log(this.parentBill) --}}
            },

            calculateBillPct() {
                let value = 0;
                console.log(this.parentBill);
                const parentVal = this.parentBill.bill_percentage;
                value += parseFloat(parentVal || 0);
                for (i = 0; i < this.parentBill.children.length; i++) {
                    value += parseFloat(this.parentBill.children[i]?.bill_percentage || 0);
                }
                console.log(value);
                return value;
            },
            clampValues() {
                const maxPct = Math.min(this.remainPct, 100);
                const maxAmt = parseFloat(this.dueCurrent || 0);
                let pct = parseFloat(this.runPct || 0);
                let amt = parseFloat(this.runAmt || 0);
                if (pct < 0) pct = 0;
                if (pct > maxPct) pct = maxPct;
                if (amt < 0) amt = 0;
                if (amt > maxAmt) amt = maxAmt;
                this.runPct = +pct.toFixed(2);
                this.runAmt = +amt.toFixed(2);
            },
                setErrors() {
                    const maxPct = Math.min(this.remainPct, 100);
                    const maxAmt = parseFloat(this.dueCurrent || 0);
                    this.errors.percentage = (parseFloat(this.runPct || 0) > maxPct) ?
                        `Percentage cannot exceed ${maxPct}%` :
                        '';
                    this.errors.amount = (parseFloat(this.runAmt || 0) > maxAmt) ?
                        `Amount cannot exceed {{ ($parentBill->quotationRevision?->type === 'via') ? ($parentBill->quotationRevision?->currency) : 'BDT' }} ${maxAmt.toFixed ? maxAmt.toFixed(2) : maxAmt}` :
                        '';
                },
            computeFromPercentage() {
                this.isCalculating = true;
                const pct = parseFloat(this.runPct || 0);
                this.runAmt = (this.total * pct / 100).toFixed(2);
                this.clampValues();
                this.setErrors();
                this.remainAfterPct = Math.max(0, 100 - this.billedPct - parseFloat(this.runPct || 0));
                this.dueAfter = Math.max(0, parseFloat(this.dueCurrent || 0) - parseFloat(this.runAmt || 0));
                this.isCalculating = false;
            },
            computeFromAmount() {
                this.isCalculating = true;
                const amt = parseFloat(this.runAmt || 0);
                const pct = this.total > 0 ? (amt / this.total) * 100 : 0;
                this.runPct = (+pct).toFixed(2);
                this.clampValues();
                this.setErrors();
                this.remainAfterPct = Math.max(0, 100 - this.billedPct - parseFloat(this.runPct || 0));
                this.dueAfter = Math.max(0, parseFloat(this.dueCurrent || 0) - parseFloat(this.runAmt || 0));
                this.isCalculating = false;
            },
            onPercentageInput() { this.computeFromPercentage(); },
            finalizePercentage() { this.computeFromPercentage(); },
            onAmountInput() { this.computeFromAmount(); },
            finalizeAmount() { this.computeFromAmount(); }
        }" x-init="init()">
        @csrf
        <input type="hidden" name="bill_type" value="running">
        <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
        <input type="hidden" name="parent_bill_id" :value="parentBill?.id || ''">
        <input type="hidden" name="bill_amount" :value="runAmt">
        <input type="hidden" name="bill_percentage" :value="runPct">
        <input type="hidden" name="due" :value="(parseFloat(dueAfter) || 0).toFixed(2)">
        <input type="hidden" name="quotation_revision_id" :value="parentBill?.quotation_revision_id || ''">

        <div class="max-w-7xl mx-auto space-y-6">
            <div class="grid lg:grid-cols-12 gap-4">
                <div class="lg:col-span-4">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden h-full">
                        <div class="bg-gradient-to-r from-purple-500 to-indigo-500 p-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-white">Parent Bill</h3>
                            </div>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <span
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">Invoice</span>
                                <span class="block text-sm font-bold text-gray-900 dark:text-white truncate"
                                    x-text="parentBill?.invoice_no || ''"></span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <span
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">Total</span>
                                <span class="block text-sm font-bold text-gray-900 dark:text-white truncate"
                                    x-text="(parentBill?.total_amount || 0).toFixed ? parentBill.total_amount.toFixed(2) : (parentBill?.total_amount || 0)"></span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <span
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">Due</span>
                                <span class="block text-sm font-bold text-gray-900 dark:text-white truncate"
                                    x-text="(parseFloat(dueCurrent)||0).toFixed(2)"></span>
                            </div>
                            {{-- <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">Billed %</span>
                                <span class="block text-sm font-bold text-gray-900 dark:text-white truncate" x-text="billedPct.toFixed(2)"></span>
                            </div> --}}
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <span
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">History</span>
                                        <div class="space-y-1 max-h-44 overflow-y-auto">
                                            @foreach (($quotation->bills ?? collect())->sortBy('bill_date') as $b)
                                                <div class="grid grid-cols-3 gap-2 text-xs items-center">
                                                    <span
                                                        class=" text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ Carbon\Carbon::parse($b->bill_date)->format('d/m/Y') }}</span>
                                                    <span
                                                        class="font-bold text-gray-900 dark:text-white whitespace-nowrap text-right">{{ ($parentBill->quotationRevision?->type === 'via') ? ($parentBill->quotationRevision?->currency) : 'BDT' }}
                                                        {{ number_format($b->bill_amount, 2) }}</span>
                                                    <span
                                                        class=" text-gray-600 dark:text-gray-400 whitespace-nowrap text-right">{{ number_format($b->bill_percentage ?? 0, 2) }}%</span>
                                                </div>
                                            @endforeach
                                        </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 space-y-4">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 p-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-white">Running Calculation</h3>
                                <span x-show="isCalculating"
                                    class="ml-auto text-xs font-bold text-white/90">Calculating…</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Remaining Amount</div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white"
                                        x-text="(parseFloat(dueAfter)||0).toFixed(2)"></div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Remaining %</div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white"
                                        x-text="(parseFloat(remainAfterPct)||0).toFixed(2)"></div>
                                </div>
                            </div>
                            <div class="mt-4 grid md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Running
                                        Percentage</label>
                                    <div class="relative">
                                        <input name="installment_percentage_input" type="number" step="0.01"
                                            min="0" :max="remainPct"
                                            class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 pr-10"
                                            x-model.number="runPct" @input="onPercentageInput()"
                                            @blur="finalizePercentage()" required>
                                        <span
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">%</span>
                                    </div>
                                    @error('bill_percentage')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                    <p x-show="errors.percentage" x-text="errors.percentage"
                                        class="text-xs text-red-600 mt-1"></p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Running
                                        Amount</label>
                                    <div class="relative">
                                        <input name="installment_amount_input" type="number" step="any"
                                            min="0" :max="parseFloat(dueCurrent)" inputmode="decimal"
                                            class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 font-semibold pr-12"
                                            x-model.number="runAmt" @input="onAmountInput()" @blur="finalizeAmount()"
                                            required>
                                        <span
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-blue-700 dark:text-blue-400 text-sm">{{ ($parentBill->quotationRevision?->type === 'via') ? ($parentBill->quotationRevision?->currency) : 'BDT' }}</span>
                                    </div>
                                    @error('bill_amount')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                    <p x-show="errors.amount" x-text="errors.amount"
                                        class="text-xs text-red-600 mt-1"></p>
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
                                        Number <span class="text-red-500">*</span></label>
                                    <input name="invoice_no" id="invoice_no" type="text"
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                        x-model="invoiceNo" placeholder="RUN-001" required>
                                    @error('invoice_no')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="bill_date"
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Bill
                                        Date <span class="text-red-500">*</span></label>
                                    <input name="bill_date" id="bill_date" type="text"
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 flowbite-datepicker"
                                        placeholder="dd/mm/yyyy" value="{{ date('d/m/Y') }}" required>
                                    @error('bill_date')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="payment_received_date"
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Payment
                                        Received Date</label>
                                    <input name="payment_received_date" id="payment_received_date" type="text"
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 flowbite-datepicker"
                                        placeholder="dd/mm/yyyy">
                                    @error('payment_received_date')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="grid md:grid-cols-1 gap-4 mb-4">
                                <div>
                                    <label for="notes"
                                        class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Notes</label>
                                    <textarea name="notes" id="notes" rows="4" maxlength="500"
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 resize-none"
                                        placeholder="Add any additional notes about this advance bill..."></textarea>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Provide any relevant
                                        details about payment terms, conditions, or special requirements for the advance
                                        payment.</p>
                                    @error('notes')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
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
                                <h3 class="text-base font-bold text-white">Bill Summary</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid md:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400"
                                        x-text="`{{ ($parentBill->quotationRevision?->type === 'via') ? ($parentBill->quotationRevision?->currency) : 'BDT' }} ${runAmt}`">{{ ($parentBill->quotationRevision?->type === 'via') ? ($parentBill->quotationRevision?->currency) : 'BDT' }} 0.00</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Running Amount</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400"
                                        x-text="`{{ ($parentBill->quotationRevision?->type === 'via') ? ($parentBill->quotationRevision?->currency) : 'BDT' }} ${(parseFloat(dueAfter)||0).toFixed(2)}`">{{ ($parentBill->quotationRevision?->type === 'via') ? ($parentBill->quotationRevision?->currency) : 'BDT' }} 0.00</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Remaining After This</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                                        x-text="`${(parseFloat(runPct)||0).toFixed(2)}%`">0%</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">Running %</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    @endpush
</x-dashboard.layout.default>
