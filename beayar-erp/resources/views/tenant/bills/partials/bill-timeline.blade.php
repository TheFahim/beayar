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
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                @elseif($bill->bill_type === 'running')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                @endif
                            </div>
                            
                            @if($bill->is_locked)
                            <div class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full flex items-center justify-center" title="Locked: {{ $bill->lock_reason }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                        <a href="{{ route('tenant.bills.show', $bill) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                            {{ $bill->invoice_no }}
                                        </a>
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ ucfirst($bill->bill_type) }} Bill • {{ $bill->bill_date?->format('d/m/Y') }}
                                    </p>
                                </div>
                                
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        BDT {{ number_format($bill->total_amount ?? $bill->bill_amount ?? 0, 2) }}
                                    </p>
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
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusClass }}">
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
                                    @if($bill->bill_type === 'regular' && $bill->advance_applied_amount > 0)
                                    <p class="text-green-600 dark:text-green-400">Advance Applied: BDT {{ number_format($bill->advance_applied_amount, 2) }}</p>
                                    @endif
                                    @if($bill->paid_amount > 0)
                                    <p>Paid: BDT {{ number_format($bill->paid_amount, 2) }}</p>
                                    <p>Remaining: BDT {{ number_format($bill->remaining_balance ?? $bill->due, 2) }}</p>
                                    @endif
                                    @if($bill->parent_bill_id)
                                    <p>Parent: <a href="{{ route('tenant.bills.show', $bill->parent_bill_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $bill->parentBill?->invoice_no }}</a></p>
                                    @endif
                                    @if($bill->childBills->count() > 0)
                                    <p>Child Bills: {{ $bill->childBills->count() }}</p>
                                    @endif
                                    @if($bill->bill_percentage)
                                    <p>Percentage: {{ number_format($bill->bill_percentage, 2) }}%</p>
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
