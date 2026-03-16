@props(['quotationId', 'showApplyButton' => false])

@php
$quotation = \App\Models\Quotation::with(['bills' => function($q) {
    $q->where('bill_type', 'advance')
      ->where('status', '!=', 'cancelled');
}])->find($quotationId);

$advances = $quotation?->bills ?? collect();
$totalAdvance = $advances->sum('total_amount');
$totalPaid = $advances->sum('paid_amount');
$totalApplied = $advances->sum('advance_applied_amount');
$availableBalance = bcsub($totalPaid, $totalApplied, 2);
@endphp

@if($advances->isNotEmpty() && bccomp($availableBalance, '0.00', 2) > 0)
<div class="advance-credit-banner bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-lg overflow-hidden" 
     x-data="advanceCreditBanner({{ $quotationId }})">
    
    <div class="p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5Z" />
                        </svg>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200">
                        Advance Credit Available
                    </h3>
                    <p class="text-xs text-blue-600 dark:text-blue-300">
                        {{ $advances->count() }} advance bill(s) with remaining balance
                    </p>
                </div>
            </div>
            
            <div class="text-right">
                <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                    ৳ {{ number_format($availableBalance, 2) }}
                </p>
                <p class="text-xs text-blue-600 dark:text-blue-400">Available Balance</p>
            </div>
        </div>
        
        <!-- Expandable Details -->
        <div x-show="expanded" x-collapse class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-700">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3">
                    <p class="text-lg font-bold text-gray-900 dark:text-white">৳ {{ number_format($totalAdvance, 2) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Advance</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3">
                    <p class="text-lg font-bold text-green-600 dark:text-green-400">৳ {{ number_format($totalPaid, 2) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Received</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3">
                    <p class="text-lg font-bold text-amber-600 dark:text-amber-400">৳ {{ number_format($totalApplied, 2) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Applied</p>
                </div>
            </div>
            
            <!-- Individual Advances List -->
            <div class="mt-4 space-y-2">
                @foreach($advances as $advance)
                @php
                $balance = $advance->unapplied_amount ?? bcsub($advance->paid_amount ?? 0, $advance->advance_applied_amount ?? 0, 2);
                @endphp
                @if(bccomp($balance, '0.00', 2) > 0)
                <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg p-3">
                    <div class="flex items-center space-x-3">
                        <div class="h-8 w-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                            </svg>
                        </div>
                        <div>
                            <a href="{{ route('tenant.bills.show', $advance) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                {{ $advance->invoice_no }}
                            </a>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $advance->bill_date?->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-blue-600 dark:text-blue-400">৳ {{ number_format($balance, 2) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Available</p>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        
        <!-- Toggle & Action -->
        <div class="mt-3 flex items-center justify-between">
            <button @click="expanded = !expanded" class="text-xs text-blue-600 dark:text-blue-400 hover:underline flex items-center">
                <span x-text="expanded ? 'Hide details' : 'Show details'"></span>
                <svg class="w-3 h-3 ml-1 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :class="expanded ? 'rotate-180' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
            
            @if($showApplyButton)
            <a href="{{ route('tenant.bills.create', ['quotation_id' => $quotationId, 'type' => 'regular']) }}" 
               class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium text-white bg-blue-600 hover:bg-blue-700">
                <svg class="w-3 h-3 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Apply to Bill
            </a>
            @endif
        </div>
    </div>
</div>
@elseif($advances->isNotEmpty())
<!-- All applied banner -->
<div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0Z" />
            </svg>
        </div>
        <div class="ml-3">
            <p class="text-sm text-green-700 dark:text-green-300">
                All advance credit has been applied to final bills.
            </p>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
function advanceCreditBanner(quotationId) {
    return {
        expanded: false,
        quotationId: quotationId,
    };
}
</script>
@endpush
