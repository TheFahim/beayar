<x-dashboard.layout.default :title="'Advance Credit Management - ' . $quotation->quotation_no">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Quotations
            </a>
        </li>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.quotations.show', $quotation) }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                {{ $quotation->quotation_no }}
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Advance Credit" />
    </x-dashboard.ui.bread-crumb>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="advanceCreditManagement({{ $quotation->id }})">
        
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Advance Credit Management</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Quotation: {{ $quotation->quotation_no }}
                    </p>
                </div>
                <a href="{{ route('tenant.quotations.show', $quotation) }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-5 h-5 inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Back to Quotation
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Advance</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">৳ {{ number_format($summary['total_advance'], 2) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.454-.342 1.454-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Received</p>
                        <p class="text-xl font-bold text-green-600 dark:text-green-400">৳ {{ number_format($summary['total_received'], 2) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.841-12.46c0-.21-.015-.42-.04-.626A9 9 0 1110.75 19.38v4.8a6 6 0 01-5.84-7.38 14.98 14.98 0 006.841-12.46c0-.21-.015-.42-.04-.626a9.003 9.003 0 0112.84 0c.23.227.445.466.644.716.2.25.383.512.55.79a9 9 0 01-5.343 12.35z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Applied</p>
                        <p class="text-xl font-bold text-amber-600 dark:text-amber-400">৳ {{ number_format($summary['total_applied'], 2) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 00-3 3v1.5h-1.5V15a3 3 0 00-3 3H5.25A2.25 2.25 0 013 15.75V10.5a2.25 2.25 0 012.25-2.25h12a2.25 2.25 0 012.25 2.25v12z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Available</p>
                        <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">৳ {{ number_format($summary['available_balance'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advance Bills List -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Advance Bills</h2>
            </div>
            
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($advances as $advance)
                <div class="p-6" x-data="{ expanded: false }">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="h-10 w-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                </svg>
                            </div>
                            <div>
                                <a href="{{ route('tenant.bills.show', $advance) }}" class="text-lg font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ $advance->invoice_no }}
                                </a>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $advance->bill_date?->format('d/m/Y') }} • 
                                    @php
                                    $statusClass = match($advance->status) {
                                        'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        'issued' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'paid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'partially_paid' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $advance->status)) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-6">
                            <div class="text-right">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">৳ {{ number_format($advance->total_amount, 2) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Received</p>
                                <p class="text-lg font-bold text-green-600 dark:text-green-400">৳ {{ number_format($advance->paid_amount ?? 0, 2) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Available</p>
                                @php
                                $unapplied = $advance->unapplied_amount ?? bcsub($advance->paid_amount ?? 0, $advance->advance_applied_amount ?? 0, 2);
                                @endphp
                                <p class="text-lg font-bold {{ bccomp($unapplied, '0.00', 2) > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500' }}">
                                    ৳ {{ number_format($unapplied, 2) }}
                                </p>
                            </div>
                            
                            <button @click="expanded = !expanded" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-5 h-5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :class="expanded ? 'rotate-180' : ''">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Expanded Details: Applications -->
                    <div x-show="expanded" x-collapse class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Credit Applications</h4>
                        
                        @if(empty($advance->advanceAdjustmentsGiven) || $advance->advanceAdjustmentsGiven->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">No credit has been applied from this advance bill.</p>
                        @else
                        <div class="space-y-2">
                            @foreach($advance->advanceAdjustmentsGiven as $adjustment)
                            <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-4 h-4 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                    <div>
                                        <a href="{{ route('tenant.bills.show', $adjustment->final_bill_id) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                            {{ $adjustment->finalBill?->invoice_no ?? 'Bill #' . $adjustment->final_bill_id }}
                                        </a>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Applied on {{ $adjustment->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm font-bold text-amber-600 dark:text-amber-400">
                                        ৳ {{ number_format($adjustment->amount, 2) }}
                                    </span>
                                    @if($adjustment->finalBill?->status === 'draft')
                                    <form action="{{ route('tenant.bills.remove-advance', [$adjustment->final_bill_id, $adjustment]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Remove this credit application?')"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        
                        <!-- Apply to Bill Action -->
                        @if(bccomp($unapplied, '0.00', 2) > 0)
                        <div class="mt-4">
                            <button @click="showApplyModal({{ $advance->id }})" 
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Apply to Regular Bill
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-6 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No advance bills created for this quotation.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Apply Credit Modal -->
        <div x-show="applyModalOpen" 
             x-transition
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black bg-opacity-50" @click="applyModalOpen = false"></div>
                
                <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-lg w-full p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Apply Advance Credit</h3>
                    
                    <form action="{{ route('tenant.bills.apply-advance', ['bill' => '__BILL_ID__']) }}" method="POST" 
                          x-ref="applyForm" 
                          @submit.prevent="submitApplication">
                        @csrf
                        
                        <input type="hidden" name="advance_bill_id" :value="selectedAdvance?.id">
                        
                        <div class="space-y-4">
                            <!-- Source Advance -->
                            <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3">
                                <p class="text-sm text-amber-700 dark:text-amber-300">
                                    From: <strong x-text="selectedAdvance?.invoice_no"></strong>
                                </p>
                                <p class="text-sm text-amber-600 dark:text-amber-400">
                                    Available: ৳ <span x-text="formatNumber(selectedAdvance?.available_balance)"></span>
                                </p>
                            </div>
                            
                            <!-- Target Bill Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Apply to Regular Bill
                                </label>
                                <select name="bill_id" 
                                        x-model="selectedBillId"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required>
                                    <option value="">Select a bill</option>
                                    @foreach($regularBills as $bill)
                                    @if($bill->status === 'draft')
                                    <option value="{{ $bill->id }}">
                                        {{ $bill->invoice_no }} - ৳ {{ number_format($bill->total_amount, 2) }}
                                        @if($bill->advance_applied_amount > 0)
                                        (Already has ৳ {{ number_format($bill->advance_applied_amount, 2) }} applied)
                                        @endif
                                    </option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Amount -->
                            <div>
                                <label for="apply_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Amount to Apply
                                </label>
                                <div class="flex items-center space-x-2">
                                    <input type="number" 
                                           step="0.01"
                                           min="0.01"
                                           :max="selectedAdvance?.available_balance || 0"
                                           name="amount"
                                           x-model.number="applyAmount"
                                           class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                           required>
                                    <button type="button" 
                                            @click="applyAmount = selectedAdvance?.available_balance || 0"
                                            class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                                        Max
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Max available: ৳ <span x-text="formatNumber(selectedAdvance?.available_balance)"></span>
                                </p>
                            </div>
                            
                            <!-- Preview -->
                            <div x-show="selectedBillId && applyAmount > 0" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preview</h4>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Bill Total:</span>
                                        <span x-text="formatCurrency(getSelectedBill()?.total_amount)"></span>
                                    </div>
                                    <div class="flex justify-between text-green-600 dark:text-green-400">
                                        <span>Credit Applied:</span>
                                        <span>- <span x-text="formatCurrency(applyAmount)"></span></span>
                                    </div>
                                    <div class="flex justify-between font-bold border-t border-gray-200 dark:border-gray-600 pt-1">
                                        <span>Net Payable:</span>
                                        <span x-text="formatCurrency((getSelectedBill()?.total_amount || 0) - applyAmount)"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" 
                                    @click="applyModalOpen = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit" 
                                    :disabled="!selectedBillId || applyAmount <= 0"
                                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                Apply Credit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
function advanceCreditManagement(quotationId) {
    return {
        quotationId: quotationId,
        applyModalOpen: false,
        selectedAdvance: null,
        selectedBillId: '',
        applyAmount: 0,
        regularBills: @json($regularBills->map(fn($b) => [
            'id' => $b->id,
            'invoice_no' => $b->invoice_no,
            'total_amount' => $b->total_amount,
            'advance_applied' => $b->advance_applied_amount ?? 0,
        ])),

        showApplyModal(advanceId) {
            // Fetch advance details via AJAX
            fetch(`{{ url('/api/bills') }}/${advanceId}/advance-balance`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.selectedAdvance = data.bill;
                        this.applyModalOpen = true;
                    }
                })
                .catch(() => {
                    // Fallback: use the advances data we already have
                    const advanceEl = document.querySelector(`[data-advance-id="${advanceId}"]`);
                    if (advanceEl) {
                        this.selectedAdvance = {
                            id: advanceId,
                            invoice_no: advanceEl.dataset.invoiceNo,
                            available_balance: parseFloat(advanceEl.dataset.availableBalance),
                        };
                        this.applyModalOpen = true;
                    }
                });
        },

        getSelectedBill() {
            return this.regularBills.find(b => b.id == this.selectedBillId);
        },

        formatNumber(num) {
            return (num || 0).toLocaleString('en-BD', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        },

        formatCurrency(num) {
            return '৳ ' + this.formatNumber(num);
        },

        submitApplication() {
            if (!this.selectedBillId || this.applyAmount <= 0) return;
            
            // Update form action with the selected bill ID
            const form = this.$refs.applyForm;
            form.action = form.action.replace('__BILL_ID__', this.selectedBillId);
            form.submit();
        },
    };
}
</script>
@endpush
</x-dashboard.layout.default>
