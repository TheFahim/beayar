<x-dashboard.layout.default title="Edit Regular Bill">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition duration-150">
                <x-ui.svg.qutation class="w-3 h-3 me-2" />
                Quotations
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Edit Regular Bill" />
    </x-dashboard.ui.bread-crumb>

    <div class="sticky top-20 z-40 mb-4 animate-fade-in-up">
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/95 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 p-4 backdrop-blur-sm">
            <div class="flex-1 min-w-0">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-0.5">Edit Regular Bill</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400">Billing against delivered challans</p>
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
                <button type="submit" form="regularBillEditForm" aria-label="Update regular bill"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-medium rounded-lg shadow hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm font-semibold">Update Regular Bill</span>
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
                                    <p class="text-sm font-medium text-red-700 dark:text-red-300">{{ $error }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @php
        $billDate = optional($bill->bill_date)->format('d/m/Y');
        $paymentDate = optional($bill->payment_received_date)->format('d/m/Y');
        $initialRoundUp = round(($bill->bill_amount ?? 0) - ($bill->total_amount ?? 0), 2);
        $preselected = collect($bill->items ?? [])
            ->map(function ($bi) {
                return [
                    'challan_product_id' => $bi->challan_product_id,
                    'quotation_product_id' => $bi->quotation_product_id,
                    'unit_price' => (float) $bi->unit_price,
                    'billed_quantity' => (float) $bi->quantity,
                ];
            })
            ->values();
    @endphp

    <div class="max-w-7xl mx-auto p-6" x-data="{
        bill: {{ json_encode($bill) }},
        quotation: {{ json_encode($quotation) }},
        activeRevision: {{ json_encode($activeRevision) }},
        challans: {{ json_encode($challans) }},
        expandedChallans: {},
        selectedProducts: {},
        selectedQuantity: {},
        calcStatus: {},
        _calcTimeouts: {},
        items: [],
        subtotal: 0,
        vatEditable: 0,
        vatApplied: false,
        total: 0,
        discount: 0,
        shipping: 0,
        roundUp: 0,
        discountEditable: 0,
        shippingEditable: 0,
        discountApplied: false,
        shippingApplied: false,
        clientSideErrors: [],
        init() {
            if (!Array.isArray(this.challans) || this.challans.length === 0) {
                this.challans = [];
            }
            this.discountEditable = parseFloat(this.bill?.discount ?? 0) || 0;
            this.shippingEditable = parseFloat(this.bill?.shipping ?? 0) || 0;
            this.discount = this.discountEditable;
            this.shipping = this.shippingEditable;
            this.discountApplied = this.discountEditable > 0;
            this.shippingApplied = this.shippingEditable > 0;
            this.roundUp = parseFloat({{ $initialRoundUp }}) || 0;
            this.vatApplied = parseFloat(this.activeRevision?.vat_percentage ?? 0) > 0;
            const pres = {{ $preselected->toJson() }};
            pres.forEach(p => {
                const key = p.challan_product_id;
                this.selectedProducts[key] = {
                    challan_product_id: p.challan_product_id,
                    quotation_product_id: p.quotation_product_id,
                    unit_price: parseFloat(p.unit_price || 0),
                    billed_quantity: parseFloat(p.billed_quantity || 0)
                };
                this.selectedQuantity[key] = parseFloat(p.billed_quantity || 0);
            });
            this.buildFromSelections();
        },
        toggleChallan(id) {
            this.expandedChallans[id] = !this.expandedChallans[id];
        },
        updateProductSelection(p, billedQty) {
            const qp = p.quotation_product || {};
            const key = p.id;
            const cap = parseFloat(p.quantity || 0);
            const qty = Math.max(0, Math.min(parseFloat(billedQty || 0), cap));
            if (qty > 0) {
                this.selectedProducts[key] = {
                    challan_product_id: p.id,
                    quotation_product_id: qp.id,
                    unit_price: parseFloat(qp.unit_price || 0),
                    billed_quantity: qty
                };
            } else {
                delete this.selectedProducts[key];
            }
            this.buildFromSelections();
        },
        formatRemaining(pid, base) {
            const raw = this.selectedQuantity[pid];
            let v = parseFloat(raw);
            if (raw === '' || isNaN(v) || v < 0) {
                return parseFloat(base || 0).toFixed(2);
            }
            v = Math.min(Math.max(v, 0), parseFloat(base || 0));
            const rem = parseFloat(base || 0) - v;
            return rem.toFixed(2);
        },
        onBillQtyInput(pid, cap, qpId, unitPrice, raw) {
            const base = parseFloat(cap || 0);
            this.calcStatus[pid] = 'loading';
            if (!this._calcTimeouts) this._calcTimeouts = {};
            clearTimeout(this._calcTimeouts[pid]);
            this._calcTimeouts[pid] = setTimeout(() => {
                if (raw === '') {
                    this.selectedQuantity[pid] = 0;
                    this.updateProductSelection({ id: pid, quantity: base, quotation_product: { id: qpId, unit_price: parseFloat(unitPrice || 0) } }, 0);
                    this.calcStatus[pid] = 'success';
                    return;
                }
                const v = parseFloat(raw);
                if (isNaN(v) || v < 0) {
                    this.calcStatus[pid] = 'error';
                    return;
                }
                const qty = Math.max(0, Math.min(v, base));
                this.selectedQuantity[pid] = qty;
                this.updateProductSelection({ id: pid, quantity: base, quotation_product: { id: qpId, unit_price: parseFloat(unitPrice || 0) } }, qty);
                this.calcStatus[pid] = 'success';
            }, 120);
        },
        onBillQtyBlur(pid, cap) {
            const base = parseFloat(cap || 0);
            const v = parseFloat(this.selectedQuantity[pid] || 0);
            const qty = Math.max(0, Math.min(isNaN(v) ? 0 : v, base));
            this.selectedQuantity[pid] = qty;
            this.calcStatus[pid] = 'success';
        },
        setProductChecked(p, checked) {
            const qty = checked ? parseFloat(p.quantity || 0) : 0;
            this.selectedQuantity[p.id] = qty;
            this.updateProductSelection(p, qty);
        },
        buildFromSelections() {
            const groups = {};
            let subtotal = 0;
            Object.values(this.selectedProducts).forEach(sel => {
                const qty = parseFloat(sel.billed_quantity || 0);
                const unit = parseFloat(sel.unit_price || 0);
                subtotal += qty * unit;
                const key = sel.quotation_product_id;
                if (!groups[key]) {
                    groups[key] = { quotation_product_id: key, quantity: 0, allocations: [] };
                }
                groups[key].quantity += qty;
                groups[key].allocations.push({ challan_product_id: sel.challan_product_id, billed_quantity: qty });
            });
            this.items = Object.values(groups);
            this.subtotal = subtotal.toFixed(2);
            const revVatPct = parseFloat(this.activeRevision?.vat_percentage ?? 0);
            this.vatEditable = this.vatApplied ? (subtotal * revVatPct / 100) : 0;
            const base = parseFloat(subtotal) + (this.shippingApplied ? (parseFloat(this.shippingEditable) || 0) : 0) - (this.discountApplied ? (parseFloat(this.discountEditable) || 0) : 0) + (this.vatApplied ? (parseFloat(this.vatEditable) || 0) : 0) + (parseFloat(this.roundUp) || 0);
            this.total = base.toFixed(2);
        },
        clientValidateAndSubmit(e) {
            this.clientSideErrors = [];
            const inv = this.$refs.invoice_no?.value?.trim();
            const billDate = this.$refs.bill_date?.value?.trim();
            const payDate = this.$refs.payment_received_date?.value?.trim();
            const dateRe = /^\d{2}\/\d{2}\/\d{4}$/;
            if (!inv) {
                this.clientSideErrors.push('Invoice No is required');
            }
            if (!billDate || !dateRe.test(billDate)) {
                this.clientSideErrors.push('Bill Date must be in dd/mm/yyyy format');
            }
            if (payDate && !dateRe.test(payDate)) {
                this.clientSideErrors.push('Payment Received Date must be in dd/mm/yyyy format');
            }
            if (!Array.isArray(this.items) || this.items.length === 0) {
                this.clientSideErrors.push('Select at least one challan product with a billed quantity');
            } else {
                const invalidItem = this.items.find(i => !(i && i.quantity > 0 && Array.isArray(i.allocations) && i.allocations.length > 0));
                if (invalidItem) {
                    this.clientSideErrors.push('Each item must include allocations and quantity greater than 0');
                }
            }
            if (this.clientSideErrors.length) {
                return;
            }
            this.$nextTick(() => document.getElementById('regularBillEditForm').submit());
        }
    }">
        <form id="regularBillEditForm" action="{{ route('tenant.bills.update-regular', $bill) }}" method="POST"
            @submit.prevent="clientValidateAndSubmit($event)">
            @csrf
            @method('PUT')
            <input type="hidden" name="bill_type" value="regular">
            <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
            <input type="hidden" name="quotation_revision_id" value="{{ $activeRevision?->id }}">
            <input type="hidden" name="discount" :value="discountApplied ? discountEditable : 0">
            <input type="hidden" name="vat" :value="vatApplied ? vatEditable : 0">
            <input type="hidden" name="shipping" :value="shippingApplied ? shippingEditable : 0">
            <input type="hidden" name="round_up" :value="roundUp">

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
                                    title="{{ $quotation->quotation_no }}">{{ $quotation->quotation_no }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <span
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">PO
                                    No.</span>
                                <span class="block text-sm font-bold text-gray-900 dark:text-white truncate"
                                    title="{{ $quotation->po_no ?? 'N/A' }}">{{ $quotation->po_no ?? 'N/A' }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <span
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">Revision
                                    Total</span>
                                <span class="block text-sm font-bold text-gray-900 dark:text-white truncate">BDT
                                    {{ number_format($activeRevision->total, 2) }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <span
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">Customer</span>
                                <span
                                    class="block text-sm font-bold text-gray-900 dark:text-white truncate">{{ $quotation->customer->customer_name }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <span
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">Company</span>
                                <span
                                    class="block text-sm font-bold text-gray-900 dark:text-white truncate">{{ optional($quotation->customer->company)->name }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <span
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">Previous
                                    Bills</span>
                                <div class="mt-1 space-y-1 max-h-32 overflow-y-auto custom-scrollbar pr-1">
                                    @php
                                        $prevBills = $quotation
                                            ->bills()
                                            ->orderBy('bill_date', 'desc')
                                            ->orderBy('id', 'desc')
                                            ->get();
                                    @endphp
                                    @forelse ($prevBills as $b)
                                        <div
                                            class="flex items-center justify-between text-xs text-gray-700 dark:text-gray-200">
                                            <span class="font-semibold">{{ $b->invoice_no }}</span>
                                            <span>{{ optional($b->bill_date)->format('d/m/Y') }}</span>
                                            <span>BDT {{ number_format($b->bill_amount ?? 0, 2) }}</span>
                                            <span class="capitalize">{{ $b->bill_type }}</span>
                                        </div>
                                    @empty
                                        <span class="text-xs text-gray-500 dark:text-gray-400">No previous bills</span>
                                    @endforelse
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
                                <h3 class="text-base font-bold text-white">Select Challans</h3>
                                <span
                                    class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white"
                                    x-text="`${Object.keys(selectedProducts).length} selected`"></span>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                @foreach ($challans as $challan)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                                        <button type="button" @click="toggleChallan({{ $challan->id }})"
                                            class="w-full flex items-center justify-between px-3 py-2 text-gray-700 dark:text-gray-200">
                                            <span class="text-sm font-semibold">{{ $challan->challan_no }}
                                                ({{ $challan->date }})</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <div x-show="expandedChallans[{{ $challan->id }}]"
                                            class="p-3 border-t border-gray-200 dark:border-gray-700">
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full text-sm">
                                                    <thead>
                                                        <tr class="text-gray-700 dark:text-gray-200">
                                                            <th class="text-left py-1">Product</th>
                                                            <th class="text-center py-1">Quantity</th>
                                                            <th class="text-center py-1">Remaining</th>
                                                            <th class="text-center py-1">Unit Price</th>
                                                            <th class="text-center py-1">Bill Qty</th>
                                                            <th class="text-center py-1">Bill Price</th>
                                                            <th class="text-center py-1">Select</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-gray-900 dark:text-white">
                                                        @foreach ($challan->products ?? [] as $p)
                                                            @php $qp = $p->quotationProduct; @endphp
                                                            @php $pre = ($bill->items ?? collect())->firstWhere('challan_product_id', $p->id); @endphp
                                                            <tr class="border-t border-gray-100 dark:border-gray-700">
                                                                <td class="py-1">
                                                                    <span
                                                                        class="font-medium">{{ optional($qp->product)->name ?? 'QP#' . $qp->id }}</span>
                                                                </td>
                                                                <td class="text-center py-1">
                                                                    <span>{{ number_format($p->quantity, 2) }}</span>
                                                                </td>
                                                                <td class="text-center py-1">
                                                                    <span
                                                                        :class="calcStatus[{{ $p->id }}] === 'error' ? 'text-red-600 dark:text-red-400 font-semibold' : ''"
                                                                        x-text="formatRemaining({{ $p->id }}, {{ $p->remaining_quantity ?? $p->quantity }})"></span>
                                                                </td>
                                                                <td class="text-center py-1">
                                                                    <span>{{ number_format($qp->unit_price ?? 0, 2) }}</span>
                                                                </td>
                                                                <td class="text-center py-1">
                                                                    <input type="number" step="0.01" min="0"
                                                                        :max="{{ $p->remaining_quantity ?? $p->quantity }}"
                                                                        :disabled="{{ $p->remaining_quantity ?? $p->quantity }} <= 0"
                                                                        x-model.number="selectedQuantity[{{ $p->id }}]"
                                                                        value="{{ optional($pre)->quantity ?? '' }}"
                                                                        class="w-24 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                                                        @input="onBillQtyInput({{ $p->id }}, {{ $p->remaining_quantity ?? $p->quantity }}, {{ $qp->id }}, {{ $qp->unit_price ?? 0 }}, $el.value)"
                                                                        @blur="onBillQtyBlur({{ $p->id }}, {{ $p->remaining_quantity ?? $p->quantity }})" />
                                                                    <div class="mt-0.5 flex items-center justify-center gap-1 text-xs">
                                                                        <template x-if="calcStatus[{{ $p->id }}] === 'loading'">
                                                                            <span class="inline-flex items-center text-indigo-600">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8" />
                                                                                </svg>
                                                                                Calculating
                                                                            </span>
                                                                        </template>
                                                                        <template x-if="calcStatus[{{ $p->id }}] === 'success'">
                                                                            <span class="inline-flex items-center text-green-600">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                                </svg>
                                                                                Updated
                                                                            </span>
                                                                        </template>
                                                                        <template x-if="calcStatus[{{ $p->id }}] === 'error'">
                                                                            <span class="inline-flex items-center text-red-600">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                                </svg>
                                                                                Invalid
                                                                            </span>
                                                                        </template>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center py-1">
                                                                    <span x-text="(((selectedQuantity[{{ $p->id }}]||0) * {{ $qp->unit_price ?? 0 }}).toFixed(2))"></span>
                                                                </td>
                                                                <td class="text-center py-1">
                                                                    <input type="checkbox" class="rounded"
                                                                        :disabled="{{ $p->remaining_quantity ?? $p->quantity }} <= 0"
                                                                        @change="setProductChecked({ id: {{ $p->id }}, quantity: {{ $p->remaining_quantity ?? $p->quantity }}, quotation_product: { id: {{ $qp->id }}, unit_price: {{ $qp->unit_price ?? 0 }} } }, $el.checked)"
                                                                        {{ $pre ? 'checked' : '' }} />
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <template x-if="items.length">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-500 to-purple-500 p-3">
                                <div class="flex items-center gap-2 text-gray-900 dark:text-white">
                                    <div class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-bold text-white">Computed Summary</h3>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="grid md:grid-cols-4 gap-4">
                                    <div class="rounded-lg h-full border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700/30 text-center">
                                        <div class="text-xs text-gray-700 dark:text-gray-200">Subtotal</div>
                                        <div class="text-2xl font-bold text-gray-900 dark:text-white" x-text="subtotal"></div>
                                    </div>
                                    <div class="rounded-lg h-full border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700/30">
                                        <div class="flex items-center justify-between">
                                            <div class="text-xs text-gray-700 dark:text-gray-200">Discount</div>
                                            <span :class="discountApplied ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700/50 dark:text-gray-300'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold whitespace-nowrap" x-text="discountApplied ? 'Applied' : 'Not Applied'"></span>
                                        </div>
                                        <div class="mt-2 text-center">
                                            <div class="text-xl font-bold text-gray-900 dark:text-white" x-text="parseFloat(discountEditable).toFixed(2)"></div>
                                            <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Max per revision context</div>
                                        </div>
                                    </div>
                                    <div class="rounded-lg h-full border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700/30">
                                        <div class="flex items-center justify-between">
                                            <div class="text-xs text-gray-700 dark:text-gray-200">VAT</div>
                                            <span :class="vatApplied ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700/50 dark:text-gray-300'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold whitespace-nowrap" x-text="vatApplied ? 'Applied' : 'Not Applied'"></span>
                                        </div>
                                        <div class="mt-2 text-center">
                                            <div class="text-xl font-bold text-gray-900 dark:text-white" x-text="parseFloat(vatEditable).toFixed(2)"></div>
                                            <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Percentage: <span x-text="(activeRevision?.vat_percentage || 0) + '%' "></span></div>
                                        </div>
                                    </div>
                                    <div class="rounded-lg h-full border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700/30">
                                        <div class="flex items-center justify-between">
                                            <div class="text-xs text-gray-700 dark:text-gray-200">Shipping</div>
                                            <span :class="shippingApplied ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700/50 dark:text-gray-300'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold whitespace-nowrap" x-text="shippingApplied ? 'Applied' : 'Not Applied'"></span>
                                        </div>
                                        <div class="mt-2 grid grid-cols-6 gap-2 items-center">
                                            <input type="number" step="0.01" min="0"
                                                class="col-span-5 w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                                x-model="shippingEditable"
                                                @input="buildFromSelections()">
                                            <div class="col-span-1 flex items-center justify-center">
                                                <input type="checkbox" class="rounded" x-model="shippingApplied" @change="buildFromSelections()" aria-label="Apply shipping">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 grid md:grid-cols-4 gap-4 items-stretch">
                                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700/30">
                                        <div class="text-xs text-gray-700 dark:text-gray-200 mb-1.5">Round Up</div>
                                        <input type="number" step="0.01" min="-100" max="100"
                                             class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                             x-model="roundUp"
                                             @input="buildFromSelections()"
                                             @blur="roundUp = Math.max(-100, Math.min(100, parseFloat(roundUp || 0))); buildFromSelections()"
                                             placeholder="±100">
                                    </div>
                                    <div class="md:col-span-2 rounded-lg p-4 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white">
                                        <div class="text-xs opacity-90">Total</div>
                                        <div class="text-3xl font-extrabold" x-text="total"></div>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700/30 text-center">
                                        <div class="text-xs text-gray-700 dark:text-gray-200">Items</div>
                                        <div class="text-xl font-semibold text-gray-900 dark:text-white" x-text="items.length"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 p-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-white">Basic Information</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Invoice No <span class="text-red-500">*</span></label>
                                    <input name="invoice_no" x-ref="invoice_no" type="text"
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                        placeholder="INV-001" value="{{ old('invoice_no', $bill->invoice_no) }}" required>
                                    @error('invoice_no')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Bill Date <span class="text-red-500">*</span></label>
                                    <input name="bill_date" id="bill_date" x-ref="bill_date" type="text"
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 flowbite-datepicker"
                                        placeholder="dd/mm/yyyy" inputmode="numeric" pattern="^\d{2}\/\d{2}\/\d{4}$" value="{{ old('bill_date', $billDate) }}" required>
                                    @error('bill_date')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Payment Received Date</label>
                                    <input name="payment_received_date" id="payment_received_date" x-ref="payment_received_date" type="text"
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 flowbite-datepicker"
                                        placeholder="dd/mm/yyyy" inputmode="numeric" pattern="^\d{2}\/\d{2}\/\d{4}$" value="{{ old('payment_received_date', $paymentDate) }}">
                                    @error('payment_received_date')
                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="grid md:grid-cols-1 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">Notes</label>
                                    <textarea name="notes" id="notes" rows="4" maxlength="1000"
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:ring-indigo-400 dark:focus:border-indigo-400 resize-none"
                                        placeholder="Optional notes">{{ old('notes', $bill->notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <template x-for="(item, idx) in items" :key="idx">
                        <div>
                            <input type="hidden" :name="'items[' + idx + '][quotation_product_id]'" :value="item.quotation_product_id">
                            <input type="hidden" :name="'items[' + idx + '][quantity]'" :value="item.quantity">
                            <template x-for="(alloc, aidx) in item.allocations" :key="aidx">
                                <div>
                                    <input type="hidden" :name="'items[' + idx + '][allocations][' + aidx + '][challan_product_id]'" :value="alloc.challan_product_id">
                                    <input type="hidden" :name="'items[' + idx + '][allocations][' + aidx + '][billed_quantity]'" :value="alloc.billed_quantity">
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <div class="mt-2"></div>
        </form>

        <div x-show="clientSideErrors && clientSideErrors.length" class="mt-4 animate-fade-in-up">
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 rounded-xl shadow-sm overflow-hidden">
                <div class="bg-yellow-500 p-3 flex items-center gap-2">
                    <div class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Please fix the following</h3>
                </div>
                <div class="p-4">
                    <ul class="space-y-2">
                        <template x-for="(err, idx) in clientSideErrors" :key="idx">
                            <li class="flex items-start gap-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                                <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm font-medium text-yellow-700 dark:text-yellow-300" x-text="err"></p>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.layout.default>
