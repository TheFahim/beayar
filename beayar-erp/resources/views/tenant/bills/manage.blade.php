<x-dashboard.layout.default :title="'Manage Bill - ' . ($bill->invoice_no ?? 'N/A')">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.bills.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Bills
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list :name="$bill->invoice_no ?? 'Bill'" />
    </x-dashboard.ui.bread-crumb>

    <div class="max-w-7xl mx-auto p-6" x-data="billManager()">
        <!-- Header with Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $bill->invoice_no }}</h1>
                        @php
                        $statusClass = match($bill->status) {
                            'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                            'issued' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                            'paid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                            'partially_paid' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                            'adjusted' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
                            default => 'bg-gray-100 text-gray-800',
                        };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusClass }}">
                            {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                        </span>
                        @if($bill->is_locked)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200" title="Locked: {{ $bill->lock_reason }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Locked
                        </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ ucfirst($bill->bill_type) }} Bill • {{ $bill->bill_date?->format('d/m/Y') ?? 'N/A' }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <!-- View/Print Button -->
                    <a href="{{ route('tenant.bills.show', $bill) }}" target="_blank"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print View
                    </a>

                    @can('update', $bill)
                    <a href="{{ route('tenant.bills.edit', $bill) }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </a>
                    @endcan

                    @can('issue', $bill)
                    <form action="{{ route('tenant.bills.issue', $bill) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                            onclick="return confirm('Are you sure you want to issue this bill? This action cannot be undone.')"
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Issue Bill
                        </button>
                    </form>
                    @endcan

                    @can('recordPayment', $bill)
                    <button type="button" @click="showPaymentModal = true"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Record Payment
                    </button>
                    @endcan

                    @can('cancel', $bill)
                    <button type="button" @click="showCancelModal = true"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Cancel Bill
                    </button>
                    @endcan
                </div>
            </div>

            <!-- Quick Info -->
            <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Customer</span>
                    <span class="block text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $bill->quotation?->customer?->customer_name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Company</span>
                    <span class="block text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $bill->quotation?->customer?->company?->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Quotation</span>
                    <a href="{{ route('tenant.quotations.show', $bill->quotation_id) }}" class="block text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline mt-1">{{ $bill->quotation?->quotation_no ?? 'N/A' }}</a>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">PO No</span>
                    <span class="block text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $bill->quotation?->po_no ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Left Column: Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Bill Items (for regular bills) -->
                @if($bill->bill_type === 'regular' && $bill->items->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white">Bill Items</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Unit Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($bill->items as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $item->quotationProduct?->product?->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">৳ {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">৳ {{ number_format($item->bill_price, 2) }}</td>
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
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
                                        {{ $payment->payment_date?->format('d/m/Y') ?? 'N/A' }} • {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                        @if($payment->reference_number)
                                        • Ref: {{ $payment->reference_number }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">by {{ $payment->creator?->name ?? 'System' }}</span>
                                    @if($bill->status !== 'paid')
                                    <form action="{{ route('tenant.bills.payments.destroy', [$bill, $payment]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Are you sure you want to void this payment?')"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
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
                            <span class="font-medium text-gray-900 dark:text-white">৳ {{ number_format($bill->quotationRevision?->subtotal ?? $bill->total_amount ?? 0, 2) }}</span>
                        </div>
                        @if($bill->discount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Discount:</span>
                            <span class="font-medium text-gray-900 dark:text-white">৳ {{ number_format($bill->discount, 2) }}</span>
                        </div>
                        @endif
                        @if($bill->shipping > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Shipping:</span>
                            <span class="font-medium text-gray-900 dark:text-white">৳ {{ number_format($bill->shipping, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Tax:</span>
                            <span class="font-medium text-gray-900 dark:text-white">৳ {{ number_format($bill->quotationRevision?->vat_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <div class="flex justify-between">
                                <span class="text-base font-medium text-gray-900 dark:text-white">Total:</span>
                                <span class="text-xl font-bold text-gray-900 dark:text-white">৳ {{ number_format($bill->total_amount ?? $bill->bill_amount ?? 0, 2) }}</span>
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
                                <span class="text-xl font-bold text-green-600 dark:text-green-400">৳ {{ number_format($bill->net_payable_amount ?? $bill->due ?? 0, 2) }}</span>
                            </div>
                        </div>
                        @endif
                        
                        @php $paidAmount = $bill->payments()->sum('amount'); @endphp
                        @if($paidAmount > 0)
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Paid:</span>
                                <span class="font-medium text-green-600 dark:text-green-400">৳ {{ number_format($paidAmount, 2) }}</span>
                            </div>
                            <div class="flex justify-between mt-2">
                                <span class="text-base font-medium text-gray-900 dark:text-white">Remaining:</span>
                                @php $remaining = ($bill->net_payable_amount ?? $bill->due ?? 0) - $paidAmount; @endphp
                                <span class="text-xl font-bold {{ $remaining <= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    ৳ {{ number_format(max(0, $remaining), 2) }}
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Bill Timeline -->
                @if($bill->quotation)
                <x-tenant.bills.partials.bill-timeline :bills="$bill->quotation->bills->sortBy('created_at')" />
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
                
                <form action="{{ route('tenant.bills.cancel', $bill) }}" method="POST">
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
                    
                    @if($bill->advance_applied_amount > 0)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        <strong class="text-red-600 dark:text-red-400">Warning:</strong> This will remove ৳ {{ number_format($bill->advance_applied_amount, 2) }} in applied advance credit.
                    </p>
                    @endif
                    
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
                
                <form action="{{ route('tenant.bills.payments.store', $bill) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="payment_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Amount <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   step="0.01"
                                   min="0.01"
                                   max="{{ $bill->remaining_balance ?? $bill->due ?? 0 }}"
                                   id="payment_amount"
                                   name="amount"
                                   class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500"
                                   required>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Max: ৳ {{ number_format($bill->remaining_balance ?? $bill->due ?? 0, 2) }}
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
    function billManager() {
        return {
            showCancelModal: false,
            showPaymentModal: false,
        };
    }
    
    function paymentSection() {
        return {
            showPaymentModal: false,
        };
    }
    </script>
    @endpush
</x-dashboard.layout.default>
