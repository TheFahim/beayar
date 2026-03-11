# Phase 4 — Frontend: Bill Creation (Days 8-10)

This phase implements the frontend views and Alpine.js components for bill creation forms.

---

## Day 8 — Regular Bill Creation Form

### 🎯 Goal
By the end of Day 8, you will have a complete Regular bill creation form with Alpine.js interactivity for selecting challans and building bill items.

### 📋 Prerequisites
- [ ] Phase 3 completed (Controllers, Routes, API endpoints)
- [ ] Understanding of existing Blade templates and Tailwind styling
- [ ] Flowbite datepicker already implemented (from memory reference)

---

### 🖥️ Frontend Tasks

#### Task 1: Create Regular Bill Creation View

**File:** `resources/views/bills/create-regular.blade.php` (NEW or MODIFICATION)

```blade
@extends('layouts.app')

@section('title', 'Create Regular Bill')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="regularBillForm()">
    
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Regular Bill</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Quotation: {{ $quotation->quotation_number ?? 'N/A' }}
                </p>
            </div>
            <a href="{{ route('bills.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <x-heroicon-o-arrow-left class="w-5 h-5 inline mr-1" />
                Back to Bills
            </a>
        </div>
    </div>

    <!-- Advance Credit Banner (if available) -->
    @if($availableAdvances->isNotEmpty())
    <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <x-heroicon-o-information-circle class="h-5 w-5 text-blue-400" />
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    Advance Credit Available
                </h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <p>This quotation has <strong>{{ $availableAdvances->sum(fn($b) => $b->unapplied_amount) }}</strong> in unapplied advance credit.</p>
                </div>
                <div class="mt-3">
                    <button type="button" 
                            @click="showAdvanceModal = true"
                            class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                        Apply advance credit →
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Form -->
    <form action="{{ route('bills.store') }}" method="POST" @submit.prevent="submitForm">
        @csrf
        <input type="hidden" name="bill_type" value="regular">
        <input type="hidden" name="quotation_id" value="{{ $quotation->id ?? '' }}">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Challan Selection & Items -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Challan Selection -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                            Select Challans
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Choose the challans to include in this bill
                        </p>
                    </div>
                    
                    <div class="p-6">
                        @if($billableChallans->isEmpty())
                        <div class="text-center py-8">
                            <x-heroicon-o-document class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No billable challans</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                All challans for this quotation have been fully billed.
                            </p>
                        </div>
                        @else
                        <div class="space-y-3">
                            <template x-for="challan in challans" :key="challan.id">
                                <div class="border rounded-lg p-4 transition-all"
                                     :class="selectedChallans.includes(challan.id) 
                                         ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' 
                                         : 'border-gray-200 dark:border-gray-700'">
                                    <div class="flex items-start">
                                        <input type="checkbox" 
                                               :id="'challan-' + challan.id"
                                               :value="challan.id"
                                               x-model="selectedChallans"
                                               @change="loadChallanProducts(challan)"
                                               class="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                        <label :for="'challan-' + challan.id" class="ml-3 flex-1 cursor-pointer">
                                            <div class="flex justify-between">
                                                <span class="font-medium text-gray-900 dark:text-white" x-text="challan.challan_number"></span>
                                                <span class="text-sm text-gray-500 dark:text-gray-400" x-text="challan.challan_date"></span>
                                            </div>
                                            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                <span x-text="challan.products.length + ' products'"></span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </template>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Bill Items -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                            Bill Items
                        </h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Available Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bill Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="(item, index) in billItems" :key="index">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="item.product_name"></div>
                                            <input type="hidden" :name="'bill_items[' + index + '][challan_product_id]'" :value="item.challan_product_id">
                                            <input type="hidden" :name="'bill_items[' + index + '][product_name]'" :value="item.product_name">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="item.unbilled_quantity"></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" 
                                                   step="0.01"
                                                   min="0.01"
                                                   :max="item.unbilled_quantity"
                                                   :name="'bill_items[' + index + '][quantity]'"
                                                   x-model.number="item.bill_quantity"
                                                   @input="calculateItemTotal(item)"
                                                   class="w-24 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                   required>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" 
                                                   step="0.01"
                                                   min="0"
                                                   :name="'bill_items[' + index + '][unit_price]'"
                                                   x-model.number="item.unit_price"
                                                   @input="calculateItemTotal(item)"
                                                   class="w-28 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                   required>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white" x-text="formatCurrency(item.total)"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white">Subtotal:</td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white" x-text="formatCurrency(subtotal)"></td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <!-- Empty state -->
                        <div x-show="billItems.length === 0" class="text-center py-8">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Select challans above to add bill items
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Bill Details & Summary -->
            <div class="space-y-6">
                
                <!-- Bill Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Bill Details</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        
                        <!-- Bill Date -->
                        <div>
                            <label for="bill_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Bill Date <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="bill_date"
                                   name="bill_date"
                                   value="{{ now()->format('d/m/Y') }}"
                                   class="flowbite-datepicker mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="dd/mm/yyyy"
                                   required>
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Due Date
                            </label>
                            <input type="text" 
                                   id="due_date"
                                   name="due_date"
                                   class="flowbite-datepicker mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="dd/mm/yyyy">
                        </div>

                        <!-- Tax Amount -->
                        <div>
                            <label for="tax_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tax Amount
                            </label>
                            <input type="number" 
                                   step="0.01"
                                   min="0"
                                   id="tax_amount"
                                   name="tax_amount"
                                   x-model.number="taxAmount"
                                   @input="calculateTotals()"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Shipping -->
                        <div>
                            <label for="shipping" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Shipping
                            </label>
                            <input type="number" 
                                   step="0.01"
                                   min="0"
                                   id="shipping"
                                   name="shipping"
                                   x-model.number="shipping"
                                   @input="calculateTotals()"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notes
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Bill Summary -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Summary</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Subtotal:</span>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="formatCurrency(subtotal)"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Tax:</span>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="formatCurrency(taxAmount)"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Shipping:</span>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="formatCurrency(shipping)"></span>
                        </div>
                        <div class="flex justify-between text-sm" x-show="advanceApplied > 0">
                            <span class="text-green-600 dark:text-green-400">Advance Applied:</span>
                            <span class="font-medium text-green-600 dark:text-green-400">- <span x-text="formatCurrency(advanceApplied)"></span></span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <div class="flex justify-between">
                                <span class="text-base font-medium text-gray-900 dark:text-white">Total:</span>
                                <span class="text-xl font-bold text-gray-900 dark:text-white" x-text="formatCurrency(total)"></span>
                            </div>
                            <div class="flex justify-between mt-1" x-show="advanceApplied > 0">
                                <span class="text-base font-medium text-gray-900 dark:text-white">Net Payable:</span>
                                <span class="text-xl font-bold text-green-600 dark:text-green-400" x-text="formatCurrency(netPayable)"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="p-6 border-t border-gray-200 dark:border-gray-700 space-y-3">
                        <button type="submit" 
                                :disabled="billItems.length === 0 || isSubmitting"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <x-heroicon-o-check class="w-4 h-4 mr-2" />
                            <span x-text="isSubmitting ? 'Creating...' : 'Create Bill'"></span>
                        </button>
                        <button type="button" 
                                @click="saveAsDraft()"
                                :disabled="billItems.length === 0 || isSubmitting"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            Save as Draft
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden inputs for challan IDs -->
        <template x-for="challanId in selectedChallans" :key="challanId">
            <input type="hidden" name="challan_ids[]" :value="challanId">
        </template>

        <!-- Hidden inputs for advance adjustment -->
        <template x-if="selectedAdvance && advanceApplyAmount > 0">
            <div>
                <input type="hidden" name="advance_adjustment[advance_bill_id]" :value="selectedAdvance.id">
                <input type="hidden" name="advance_adjustment[amount]" :value="advanceApplyAmount">
            </div>
        </template>
    </form>

    <!-- Advance Credit Modal -->
    <div x-show="showAdvanceModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showAdvanceModal = false"></div>
            
            <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Apply Advance Credit</h3>
                
                <div class="space-y-4">
                    <!-- Available Advances -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Select Advance Bill
                        </label>
                        <template x-for="advance in availableAdvances" :key="advance.id">
                            <div class="border rounded-lg p-3 mb-2 cursor-pointer transition-all"
                                 :class="selectedAdvance?.id === advance.id 
                                     ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' 
                                     : 'border-gray-200 dark:border-gray-700'"
                                 @click="selectAdvance(advance)">
                                <div class="flex justify-between">
                                    <span class="font-medium" x-text="advance.bill_number"></span>
                                    <span class="text-green-600 font-bold" x-text="formatCurrency(advance.available_balance)"></span>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400" x-text="'Available: ' + formatCurrency(advance.available_balance)"></div>
                            </div>
                        </template>
                    </div>

                    <!-- Amount to Apply -->
                    <div x-show="selectedAdvance">
                        <label for="advance_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Amount to Apply
                        </label>
                        <div class="flex items-center space-x-2">
                            <input type="number" 
                                   step="0.01"
                                   min="0.01"
                                   :max="selectedAdvance?.available_balance || 0"
                                   x-model.number="advanceApplyAmount"
                                   class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <button type="button" 
                                    @click="advanceApplyAmount = selectedAdvance?.available_balance || 0"
                                    class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                                Max
                            </button>
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Max available: <span x-text="formatCurrency(selectedAdvance?.available_balance || 0)"></span>
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" 
                            @click="showAdvanceModal = false; selectedAdvance = null; advanceApplyAmount = 0"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="button" 
                            @click="confirmAdvanceApplication()"
                            :disabled="!selectedAdvance || advanceApplyAmount <= 0"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function regularBillForm() {
    return {
        // State
        challans: @json($billableChallans),
        availableAdvances: @json($availableAdvances->map(fn($b) => [
            'id' => $b->id,
            'bill_number' => $b->bill_number,
            'available_balance' => $b->unapplied_amount,
        ])),
        selectedChallans: [],
        billItems: [],
        taxAmount: 0,
        shipping: 0,
        
        // Advance credit
        showAdvanceModal: false,
        selectedAdvance: null,
        advanceApplyAmount: 0,
        advanceApplied: 0,
        
        // UI state
        isSubmitting: false,

        // Computed
        get subtotal() {
            return this.billItems.reduce((sum, item) => sum + (parseFloat(item.total) || 0), 0);
        },

        get total() {
            return this.subtotal + (this.taxAmount || 0) + (this.shipping || 0);
        },

        get netPayable() {
            return Math.max(0, this.total - this.advanceApplied);
        },

        // Methods
        async loadChallanProducts(challan) {
            const index = this.selectedChallans.indexOf(challan.id);
            
            if (index > -1) {
                // Selected - add products to bill items
                challan.products.forEach(product => {
                    if (parseFloat(product.unbilled_quantity) > 0) {
                        this.billItems.push({
                            challan_product_id: product.id,
                            product_name: product.product_name,
                            unbilled_quantity: parseFloat(product.unbilled_quantity),
                            bill_quantity: parseFloat(product.unbilled_quantity),
                            unit_price: parseFloat(product.unit_price) || 0,
                            total: parseFloat(product.unbilled_quantity) * (parseFloat(product.unit_price) || 0),
                        });
                    }
                });
            } else {
                // Deselected - remove products from bill items
                const productIds = challan.products.map(p => p.id);
                this.billItems = this.billItems.filter(item => !productIds.includes(item.challan_product_id));
            }
            
            this.calculateTotals();
        },

        calculateItemTotal(item) {
            item.total = (item.bill_quantity || 0) * (item.unit_price || 0);
            this.calculateTotals();
        },

        calculateTotals() {
            // Trigger reactivity
            this.billItems = [...this.billItems];
        },

        selectAdvance(advance) {
            this.selectedAdvance = advance;
            this.advanceApplyAmount = 0;
        },

        confirmAdvanceApplication() {
            this.advanceApplied = this.advanceApplyAmount;
            this.showAdvanceModal = false;
        },

        formatCurrency(amount) {
            return '৳ ' + (amount || 0).toLocaleString('en-BD', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        async submitForm() {
            if (this.billItems.length === 0) {
                alert('Please add at least one bill item.');
                return;
            }

            this.isSubmitting = true;
            
            // Form will submit normally via POST
            this.$el.submit();
        },

        saveAsDraft() {
            // Same as submit - draft is the default status
            this.submitForm();
        },
    };
}
</script>
@endpush
@endsection
```

---

### ✅ End-of-Day Checklist (Day 8)

- [ ] Regular bill creation view created
- [ ] Alpine.js component handles challan selection
- [ ] Bill items dynamically built from selected challans
- [ ] Advance credit modal implemented
- [ ] Form calculates totals correctly
- [ ] Datepicker uses existing flowbite implementation
- [ ] Form handles both JSON and regular submission

### ⚠️ Pitfalls & Notes (Day 8)

1. **Existing Datepicker:** The memory indicates flowbite datepicker is already implemented. Do not re-implement - use the existing `flowbite-datepicker` class.

2. **Decimal Handling:** JavaScript has floating-point issues. Consider using a library like `decimal.js` for precise calculations in production.

3. **Validation:** Client-side validation is for UX only. Server-side validation in Form Requests is the source of truth.

4. **Currency Format:** Adjust the `formatCurrency` function to match your locale and currency symbol.

---

## Day 9 — Advance Bill & Running Bill Forms

### 🎯 Goal
By the end of Day 9, you will have complete Advance and Running bill creation forms.

### 📋 Prerequisites
- [ ] Day 8 regular bill form completed
- [ ] Understanding of the differences between bill types

---

### 🖥️ Frontend Tasks

#### Task 1: Create Advance Bill Creation View

**File:** `resources/views/bills/create-advance.blade.php` (NEW)

```blade
@extends('layouts.app')

@section('title', 'Create Advance Bill')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="advanceBillForm()">
    
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Advance Bill</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Quotation: {{ $quotation->quotation_number ?? 'N/A' }}
                </p>
            </div>
            <a href="{{ route('bills.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <x-heroicon-o-arrow-left class="w-5 h-5 inline mr-1" />
                Back to Bills
            </a>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <x-heroicon-o-light-bulb class="h-5 w-5 text-amber-400" />
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">
                    About Advance Bills
                </h3>
                <div class="mt-2 text-sm text-amber-700 dark:text-amber-300">
                    <p>An advance bill is created before delivery to receive upfront payment. The credit can later be applied to the final regular bill.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form -->
    <form action="{{ route('bills.store') }}" method="POST" @submit.prevent="submitForm">
        @csrf
        <input type="hidden" name="bill_type" value="advance">
        <input type="hidden" name="quotation_id" value="{{ $quotation->id ?? '' }}">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Advance Details</h2>
            </div>

            <div class="p-6 space-y-6">
                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Advance Amount <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 dark:text-gray-400 sm:text-sm">৳</span>
                        </div>
                        <input type="number" 
                               step="0.01"
                               min="0.01"
                               id="amount"
                               name="amount"
                               x-model.number="amount"
                               @input="calculateTotal()"
                               class="block w-full pl-7 pr-12 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                               placeholder="0.00"
                               required>
                    </div>
                </div>

                <!-- Tax Amount -->
                <div>
                    <label for="tax_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tax Amount
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 dark:text-gray-400 sm:text-sm">৳</span>
                        </div>
                        <input type="number" 
                               step="0.01"
                               min="0"
                               id="tax_amount"
                               name="tax_amount"
                               x-model.number="taxAmount"
                               @input="calculateTotal()"
                               class="block w-full pl-7 pr-12 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                               placeholder="0.00">
                    </div>
                </div>

                <!-- Total Display -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-medium text-gray-900 dark:text-white">Total:</span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-white" x-text="formatCurrency(total)"></span>
                    </div>
                </div>

                <!-- Bill Date -->
                <div>
                    <label for="bill_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Bill Date <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="bill_date"
                           name="bill_date"
                           value="{{ now()->format('d/m/Y') }}"
                           class="flowbite-datepicker mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="dd/mm/yyyy"
                           required>
                </div>

                <!-- Due Date -->
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Due Date
                    </label>
                    <input type="text" 
                           id="due_date"
                           name="due_date"
                           class="flowbite-datepicker mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="dd/mm/yyyy">
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Notes
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <!-- Terms & Conditions -->
                <div>
                    <label for="terms_conditions" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Terms & Conditions
                    </label>
                    <textarea id="terms_conditions" 
                              name="terms_conditions" 
                              rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex justify-end space-x-3">
                <a href="{{ route('bills.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md hover:bg-gray-50 dark:hover:bg-gray-500">
                    Cancel
                </a>
                <button type="submit" 
                        :disabled="!amount || amount <= 0 || isSubmitting"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <x-heroicon-o-check class="w-4 h-4 mr-2" />
                    <span x-text="isSubmitting ? 'Creating...' : 'Create Advance Bill'"></span>
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function advanceBillForm() {
    return {
        amount: 0,
        taxAmount: 0,
        isSubmitting: false,

        get total() {
            return (this.amount || 0) + (this.taxAmount || 0);
        },

        calculateTotal() {
            // Trigger reactivity
        },

        formatCurrency(amount) {
            return '৳ ' + (amount || 0).toLocaleString('en-BD', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        async submitForm() {
            if (!this.amount || this.amount <= 0) {
                alert('Please enter a valid advance amount.');
                return;
            }

            this.isSubmitting = true;
            this.$el.submit();
        },
    };
}
</script>
@endpush
@endsection
```

#### Task 2: Create Running Bill Creation View

**File:** `resources/views/bills/create-running.blade.php` (NEW)

```blade
@extends('layouts.app')

@section('title', 'Create Running Bill')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="runningBillForm()">
    
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Running Bill</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Parent Advance: {{ $parentAdvance->bill_number ?? 'N/A' }}
                </p>
            </div>
            <a href="{{ route('bills.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <x-heroicon-o-arrow-left class="w-5 h-5 inline mr-1" />
                Back to Bills
            </a>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="mb-6 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <x-heroicon-o-arrow-path class="h-5 w-5 text-purple-400" />
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-purple-800 dark:text-purple-200">
                    About Running Bills
                </h3>
                <div class="mt-2 text-sm text-purple-700 dark:text-purple-300">
                    <p>A running bill is linked to an advance bill and represents interim billing during project progress. Multiple running bills can be created against one advance.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Parent Advance Info -->
    @if($parentAdvance)
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Parent Advance Details</h3>
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-gray-500 dark:text-gray-400">Bill Number:</span>
                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ $parentAdvance->bill_number }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Total Amount:</span>
                <span class="ml-2 font-medium text-gray-900 dark:text-white">৳ {{ number_format($parentAdvance->total, 2) }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Status:</span>
                <span class="ml-2 font-medium text-gray-900 dark:text-white">{{ ucfirst($parentAdvance->status) }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Form -->
    <form action="{{ route('bills.store') }}" method="POST" @submit.prevent="submitForm">
        @csrf
        <input type="hidden" name="bill_type" value="running">
        <input type="hidden" name="parent_bill_id" value="{{ $parentAdvance->id ?? '' }}">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Running Bill Details</h2>
            </div>

            <div class="p-6 space-y-6">
                <!-- Subtotal -->
                <div>
                    <label for="subtotal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Bill Amount <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 dark:text-gray-400 sm:text-sm">৳</span>
                        </div>
                        <input type="number" 
                               step="0.01"
                               min="0.01"
                               id="subtotal"
                               name="subtotal"
                               x-model.number="subtotal"
                               @input="calculateTotal()"
                               class="block w-full pl-7 pr-12 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                               placeholder="0.00"
                               required>
                    </div>
                </div>

                <!-- Tax Amount -->
                <div>
                    <label for="tax_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tax Amount
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 dark:text-gray-400 sm:text-sm">৳</span>
                        </div>
                        <input type="number" 
                               step="0.01"
                               min="0"
                               id="tax_amount"
                               name="tax_amount"
                               x-model.number="taxAmount"
                               @input="calculateTotal()"
                               class="block w-full pl-7 pr-12 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                               placeholder="0.00">
                    </div>
                </div>

                <!-- Total Display -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-medium text-gray-900 dark:text-white">Total:</span>
                        <span class="text-2xl font-bold text-gray-900 dark:text-white" x-text="formatCurrency(total)"></span>
                    </div>
                </div>

                <!-- Bill Date -->
                <div>
                    <label for="bill_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Bill Date <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="bill_date"
                           name="bill_date"
                           value="{{ now()->format('d/m/Y') }}"
                           class="flowbite-datepicker mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="dd/mm/yyyy"
                           required>
                </div>

                <!-- Due Date -->
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Due Date
                    </label>
                    <input type="text" 
                           id="due_date"
                           name="due_date"
                           class="flowbite-datepicker mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="dd/mm/yyyy">
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Notes
                    </label>
                    <textarea id="notes" 
                              name="notes" 
                              rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex justify-end space-x-3">
                <a href="{{ route('bills.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md hover:bg-gray-50 dark:hover:bg-gray-500">
                    Cancel
                </a>
                <button type="submit" 
                        :disabled="!subtotal || subtotal <= 0 || isSubmitting"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <x-heroicon-o-check class="w-4 h-4 mr-2" />
                    <span x-text="isSubmitting ? 'Creating...' : 'Create Running Bill'"></span>
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function runningBillForm() {
    return {
        subtotal: 0,
        taxAmount: 0,
        isSubmitting: false,

        get total() {
            return (this.subtotal || 0) + (this.taxAmount || 0);
        },

        calculateTotal() {
            // Trigger reactivity
        },

        formatCurrency(amount) {
            return '৳ ' + (amount || 0).toLocaleString('en-BD', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        async submitForm() {
            if (!this.subtotal || this.subtotal <= 0) {
                alert('Please enter a valid bill amount.');
                return;
            }

            this.isSubmitting = true;
            this.$el.submit();
        },
    };
}
</script>
@endpush
@endsection
```

---

### ✅ End-of-Day Checklist (Day 9)

- [ ] Advance bill creation view created
- [ ] Running bill creation view created
- [ ] Both forms use existing flowbite datepicker
- [ ] Forms calculate totals correctly
- [ ] Parent advance info displayed for running bills
- [ ] Proper color coding for different bill types (amber for advance, purple for running)

### ⚠️ Pitfalls & Notes (Day 9)

1. **Parent Bill Validation:** The running bill form should only be accessible when a valid parent advance bill is provided.

2. **Color Consistency:** Use consistent colors across the app:
   - Advance: Amber/Yellow
   - Running: Purple
   - Regular: Blue/Green

---

## Day 10 — Bill Show View & Timeline Component

### 🎯 Goal
By the end of Day 10, you will have a complete bill detail view with timeline component showing all bills for a quotation.

### 📋 Prerequisites
- [ ] Day 8-9 forms completed
- [ ] Understanding of bill status display requirements

---

### 🖥️ Frontend Tasks

#### Task 1: Create Bill Timeline Blade Component

**File:** `resources/views/bills/partials/bill-timeline.blade.php` (NEW)

```blade
@props(['bills'])

<div class="bill-timeline">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Bill Timeline</h3>
    
    @if($bills->isEmpty())
    <p class="text-sm text-gray-500 dark:text-gray-400">No bills created yet.</p>
    @else
    <div class="flow-root">
        <ul class="-mb-8">
            @foreach($bills as $bill)
            <li>
                <div class="relative pb-8">
                    @if(!$loop->last)
                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                    @endif
                    
                    <div class="relative flex items-start space-x-3">
                        <!-- Icon based on bill type -->
                        <div class="relative">
                            @php
                            $iconClasses = match($bill->bill_type) {
                                'advance' => 'bg-amber-500',
                                'running' => 'bg-purple-500',
                                'regular' => 'bg-green-500',
                                default => 'bg-gray-500',
                            };
                            $statusClasses = match($bill->status) {
                                'draft' => 'ring-gray-300',
                                'issued' => 'ring-blue-300',
                                'paid' => 'ring-green-300',
                                'cancelled' => 'ring-red-300',
                                'partially_paid' => 'ring-yellow-300',
                                'adjusted' => 'ring-indigo-300',
                                default => 'ring-gray-300',
                            };
                            @endphp
                            
                            <div class="h-8 w-8 rounded-full {{ $iconClasses }} flex items-center justify-center ring-4 {{ $statusClasses }}">
                                @if($bill->bill_type === 'advance')
                                <x-heroicon-o-receipt-refund class="h-4 w-4 text-white" />
                                @elseif($bill->bill_type === 'running')
                                <x-heroicon-o-arrow-path class="h-4 w-4 text-white" />
                                @else
                                <x-heroicon-o-document-text class="h-4 w-4 text-white" />
                                @endif
                            </div>
                            
                            @if($bill->is_locked)
                            <div class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full flex items-center justify-center" title="Locked: {{ $bill->lock_reason }}">
                                <x-heroicon-o-lock-closed class="h-2 w-2 text-white" />
                            </div>
                            @endif
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                        <a href="{{ route('bills.show', $bill) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                            {{ $bill->bill_number }}
                                        </a>
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ ucfirst($bill->bill_type) }} Bill • {{ $bill->bill_date?->format('d/m/Y') }}
                                    </p>
                                </div>
                                
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        ৳ {{ number_format($bill->total, 2) }}
                                    </p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ match($bill->status) {
                                            'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                            'issued' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                            'paid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                            'partially_paid' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                            'adjusted' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
                                            default => 'bg-gray-100 text-gray-800',
                                        } }}">
                                        {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Expandable details -->
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400" x-data="{ expanded: false }">
                                <button @click="expanded = !expanded" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    <span x-text="expanded ? 'Hide details' : 'Show details'"></span>
                                </button>
                                
                                <div x-show="expanded" x-collapse class="mt-2 space-y-1">
                                    @if($bill->net_payable_amount != $bill->total)
                                    <p>Net Payable: ৳ {{ number_format($bill->net_payable_amount, 2) }}</p>
                                    @endif
                                    @if($bill->advance_applied_amount > 0)
                                    <p class="text-green-600 dark:text-green-400">Advance Applied: ৳ {{ number_format($bill->advance_applied_amount, 2) }}</p>
                                    @endif
                                    @if($bill->paid_amount > 0)
                                    <p>Paid: ৳ {{ number_format($bill->paid_amount, 2) }}</p>
                                    <p>Remaining: ৳ {{ number_format($bill->remaining_balance, 2) }}</p>
                                    @endif
                                    @if($bill->parent_bill_id)
                                    <p>Parent: <a href="{{ route('bills.show', $bill->parent_bill_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $bill->parentBill?->bill_number }}</a></p>
                                    @endif
                                    @if($bill->childBills->count() > 0)
                                    <p>Child Bills: {{ $bill->childBills->count() }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
```

#### Task 2: Create Bill Show View

**File:** `resources/views/bills/show.blade.php` (NEW or MODIFICATION)

```blade
@extends('layouts.app')

@section('title', 'Bill ' . $bill->bill_number)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $bill->bill_number }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ match($bill->status) {
                            'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                            'issued' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                            'paid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                            'partially_paid' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                            'adjusted' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
                            default => 'bg-gray-100 text-gray-800',
                        } }}">
                        {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                    </span>
                    @if($bill->is_locked)
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        <x-heroicon-o-lock-closed class="w-3 h-3 mr-1" />
                        Locked
                    </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ ucfirst($bill->bill_type) }} Bill • Created {{ $bill->created_at->format('d/m/Y') }}
                </p>
            </div>
            
            <div class="flex items-center space-x-3">
                @can('update', $bill)
                <a href="{{ route('bills.edit', $bill) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <x-heroicon-o-pencil class="w-4 h-4 mr-2" />
                    Edit
                </a>
                @endcan
                
                @can('issue', $bill)
                <form action="{{ route('bills.issue', $bill) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <x-heroicon-o-paper-airplane class="w-4 h-4 mr-2" />
                        Issue Bill
                    </button>
                </form>
                @endcan
                
                @can('cancel', $bill)
                <button type="button" 
                        @click="showCancelModal = true"
                        class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-600 rounded-md shadow-sm text-sm font-medium text-red-700 dark:text-red-300 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-gray-600">
                    <x-heroicon-o-x-circle class="w-4 h-4 mr-2" />
                    Cancel
                </button>
                @endcan
                
                @can('reissue', $bill)
                <form action="{{ route('bills.reissue', $bill) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700">
                        <x-heroicon-o-arrow-path class="w-4 h-4 mr-2" />
                        Reissue
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Bill Details -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Bill Info Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">Bill Information</h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Quotation</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                @if($bill->quotation)
                                <a href="{{ route('quotations.show', $bill->quotation) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $bill->quotation->quotation_number }}
                                </a>
                                @else
                                N/A
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Bill Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $bill->bill_date?->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Due Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $bill->due_date?->format('d/m/Y') ?? 'N/A' }}</dd>
                        </div>
                        @if($bill->parent_bill_id)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Parent Advance</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                <a href="{{ route('bills.show', $bill->parent_bill_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $bill->parentBill?->bill_number }}
                                </a>
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Bill Items (for Regular bills) -->
            @if($bill->bill_type === 'regular' && $bill->billItems->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">Bill Items</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Unit Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($bill->billItems as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $item->product_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">৳ {{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">৳ {{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Payments -->
            @can('view', $bill)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow" x-data="paymentSection()">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">Payments</h2>
                    @can('recordPayment', $bill)
                    <button type="button" 
                            @click="showPaymentModal = true"
                            class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                        <x-heroicon-o-plus class="w-4 h-4 mr-1" />
                        Record Payment
                    </button>
                    @endcan
                </div>
                
                <div class="p-6">
                    @if($bill->payments->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No payments recorded yet.</p>
                    @else
                    <div class="space-y-3">
                        @foreach($bill->payments as $payment)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">৳ {{ number_format($payment->amount, 2) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $payment->payment_date->format('d/m/Y') }} • {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                    @if($payment->reference_number)
                                    • Ref: {{ $payment->reference_number }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400">by {{ $payment->creator?->name }}</span>
                                @if($bill->status !== 'paid')
                                <form action="{{ route('bills.payments.destroy', [$bill, $payment]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Are you sure you want to void this payment?')"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endcan
        </div>

        <!-- Right Column: Summary & Timeline -->
        <div class="space-y-6">
            
            <!-- Bill Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">Summary</h2>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Subtotal:</span>
                        <span class="font-medium text-gray-900 dark:text-white">৳ {{ number_format($bill->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Tax:</span>
                        <span class="font-medium text-gray-900 dark:text-white">৳ {{ number_format($bill->tax_amount, 2) }}</span>
                    </div>
                    @if($bill->shipping > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Shipping:</span>
                        <span class="font-medium text-gray-900 dark:text-white">৳ {{ number_format($bill->shipping, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Due:</span>
                        <span class="font-medium text-gray-900 dark:text-white">৳ {{ number_format($bill->due, 2) }}</span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                        <div class="flex justify-between">
                            <span class="text-base font-medium text-gray-900 dark:text-white">Total:</span>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">৳ {{ number_format($bill->total, 2) }}</span>
                        </div>
                    </div>
                    
                    @if($bill->advance_applied_amount > 0)
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-green-600 dark:text-green-400">Advance Applied:</span>
                            <span class="font-medium text-green-600 dark:text-green-400">- ৳ {{ number_format($bill->advance_applied_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-base font-medium text-gray-900 dark:text-white">Net Payable:</span>
                            <span class="text-xl font-bold text-green-600 dark:text-green-400">৳ {{ number_format($bill->net_payable_amount, 2) }}</span>
                        </div>
                    </div>
                    @endif
                    
                    @if($bill->paid_amount > 0)
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Paid:</span>
                            <span class="font-medium text-green-600 dark:text-green-400">৳ {{ number_format($bill->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-base font-medium text-gray-900 dark:text-white">Remaining:</span>
                            <span class="text-xl font-bold {{ $bill->remaining_balance <= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                ৳ {{ number_format($bill->remaining_balance, 2) }}
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Bill Timeline -->
            @if($bill->quotation)
            <x-bill-timeline :bills="$bill->quotation->bills->sortBy('created_at')" />
            @endif
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div x-show="showCancelModal" 
     x-transition
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showCancelModal = false"></div>
        
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Cancel Bill</h3>
            
            <form action="{{ route('bills.cancel', $bill) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="cancel_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Reason for Cancellation (Optional)
                    </label>
                    <textarea id="cancel_reason" 
                              name="reason" 
                              rows="3"
                              class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
                </div>
                
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    @if($bill->advance_applied_amount > 0)
                    <strong>Warning:</strong> This will remove ৳ {{ number_format($bill->advance_applied_amount, 2) }} in applied advance credit.
                    @endif
                </p>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            @click="showCancelModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                        Keep Bill
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                        Cancel Bill
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div x-show="showPaymentModal" 
     x-transition
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="showPaymentModal = false"></div>
        
        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Record Payment</h3>
            
            <form action="{{ route('bills.payments.store', $bill) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="payment_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Amount <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               step="0.01"
                               min="0.01"
                               max="{{ $bill->remaining_balance }}"
                               id="payment_amount"
                               name="amount"
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500"
                               required>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Max: ৳ {{ number_format($bill->remaining_balance, 2) }}
                        </p>
                    </div>
                    
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Payment Method <span class="text-red-500">*</span>
                        </label>
                        <select id="payment_method" 
                                name="payment_method"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500"
                                required>
                            <option value="">Select method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="upi">UPI</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Payment Date <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="payment_date"
                               name="payment_date"
                               value="{{ now()->format('d/m/Y') }}"
                               class="flowbite-datepicker w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500"
                               required>
                    </div>
                    
                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Reference Number
                        </label>
                        <input type="text" 
                               id="reference_number"
                               name="reference_number"
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500">
                    </div>
                    
                    <div>
                        <label for="payment_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Notes
                        </label>
                        <textarea id="payment_notes" 
                                  name="notes" 
                                  rows="2"
                                  class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" 
                            @click="showPaymentModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                        Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function paymentSection() {
    return {
        showPaymentModal: false,
    };
}
</script>
@endpush
@endsection
```

---

### ✅ End-of-Day Checklist (Day 10)

- [ ] Bill timeline component created
- [ ] Bill show view displays all bill details
- [ ] Payment recording modal implemented
- [ ] Cancel bill modal implemented
- [ ] All action buttons properly gated by Policy
- [ ] Status badges have correct colors
- [ ] Lock indicator displays correctly

### ⚠️ Pitfalls & Notes (Day 10)

1. **Policy Gates:** Always use `@can` directives to show/hide action buttons. Never rely on client-side checks alone.

2. **X-Collapse:** The timeline uses Alpine.js Collapse plugin. Ensure it's included in your Alpine setup.

3. **Status Colors:** Maintain consistent color coding across all views for a cohesive UX.

---

## Phase 4 Summary

| Day | Files Created | Files Modified |
|-----|---------------|----------------|
| 8 | create-regular.blade.php | None |
| 9 | create-advance.blade.php, create-running.blade.php | None |
| 10 | bill-timeline.blade.php, show.blade.php | None |

**Next:** Proceed to [Phase 5 — Frontend: Advance Credits](./phase5_frontend_advance_credits.md)
