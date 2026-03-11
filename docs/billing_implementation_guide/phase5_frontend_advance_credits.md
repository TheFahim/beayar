# Phase 5 — Frontend: Advance Credits (Days 11-12)

This phase implements the advance credit management UI, including the reusable credit banner component and credit application interfaces.

---

## Day 11 — Advance Credit Banner Component

### 🎯 Goal
By the end of Day 11, you will have a reusable Blade component for displaying advance credit status on quotation and bill pages.

### 📋 Prerequisites
- [ ] Phase 4 completed (Bill forms and show view)
- [ ] Understanding of advance credit workflow

---

### 🖥️ Frontend Tasks

#### Task 1: Create Advance Credit Banner Component

**File:** `resources/views/bills/partials/advance-credit-banner.blade.php` (NEW)

```blade
@props(['quotationId', 'showApplyButton' => false])

@php
$quotation = \App\Models\Quotation::with(['bills' => function($q) {
    $q->where('bill_type', 'advance')
      ->where('status', '!=', 'cancelled');
}])->find($quotationId);

$advances = $quotation?->bills ?? collect();
$totalAdvance = $advances->sum('total');
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
                        <x-heroicon-o-credit-card class="h-5 w-5 text-white" />
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
                $balance = $advance->unapplied_amount;
                @endphp
                @if(bccomp($balance, '0.00', 2) > 0)
                <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg p-3">
                    <div class="flex items-center space-x-3">
                        <div class="h-8 w-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <x-heroicon-o-receipt-refund class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <a href="{{ route('bills.show', $advance) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                {{ $advance->bill_number }}
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
                <x-heroicon-o-chevron-down class="w-3 h-3 ml-1 transition-transform" :class="expanded ? 'rotate-180' : ''" />
            </button>
            
            @if($showApplyButton)
            <a href="{{ route('bills.create', ['quotation_id' => $quotationId, 'type' => 'regular']) }}" 
               class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium text-white bg-blue-600 hover:bg-blue-700">
                <x-heroicon-o-plus class="w-3 h-3 mr-1" />
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
            <x-heroicon-o-check-circle class="h-5 w-5 text-green-500" />
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
```

#### Task 2: Register as Blade Component

**File:** `app/View/Components/AdvanceCreditBanner.php` (NEW)

```php
<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use App\Models\Quotation;

class AdvanceCreditBanner extends Component
{
    public int $quotationId;
    public bool $showApplyButton;

    /**
     * Create a new component instance.
     */
    public function __construct(int $quotationId, bool $showApplyButton = false)
    {
        $this->quotationId = $quotationId;
        $this->showApplyButton = $showApplyButton;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('bills.partials.advance-credit-banner');
    }
}
```

#### Task 3: Use Component in Quotation Show View

**File:** `resources/views/quotations/show.blade.php` (MODIFICATION)

Add the banner component near the top of the quotation details:

```blade
{{-- Add after the quotation header section --}}
@if($quotation->bills()->where('bill_type', 'advance')->exists())
<div class="mb-6">
    <x-advance-credit-banner :quotation-id="$quotation->id" :show-apply-button="true" />
</div>
@endif
```

#### Task 4: Use Component in Bill Create/Edit Views

**File:** `resources/views/bills/create-regular.blade.php` (MODIFICATION)

The banner is already included inline in Phase 4. Optionally replace with the component:

```blade
{{-- Replace the inline advance banner with --}}
<x-advance-credit-banner :quotation-id="$quotation->id" :show-apply-button="false" />
```

---

### ✅ End-of-Day Checklist (Day 11)

- [ ] Advance credit banner component created
- [ ] Component registered as Blade component
- [ ] Component displays correct totals
- [ ] Expandable details show individual advances
- [ ] "Apply to Bill" button conditionally shown
- [ ] Component integrated into quotation show view
- [ ] Handles edge case when all credit is applied

### ⚠️ Pitfalls & Notes (Day 11)

1. **Performance:** The component queries the database each time it's rendered. For high-traffic pages, consider caching the advance totals.

2. **Decimal Precision:** Use `bccomp()` for comparing the available balance to zero. Never use `== 0`.

3. **Conditional Display:** The banner only shows when there's available balance. When all credit is applied, show a "success" banner instead.

---

## Day 12 — Advance Credit Management Interface

### 🎯 Goal
By the end of Day 12, you will have a complete interface for viewing and managing advance credit applications.

### 📋 Prerequisites
- [ ] Day 11 banner component completed
- [ ] Understanding of the credit application workflow

---

### 🖥️ Frontend Tasks

#### Task 1: Create Advance Credit Management View

**File:** `resources/views/bills/advance-credit.blade.php` (NEW)

This view shows all advance bills for a quotation with their application status.

```blade
@extends('layouts.app')

@section('title', 'Advance Credit Management')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="advanceCreditManagement({{ $quotation->id }})">
    
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Advance Credit Management</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Quotation: {{ $quotation->quotation_number }}
                </p>
            </div>
            <a href="{{ route('quotations.show', $quotation) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                <x-heroicon-o-arrow-left class="w-5 h-5 inline mr-1" />
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
                        <x-heroicon-o-document-text class="h-6 w-6 text-blue-600 dark:text-blue-400" />
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
                        <x-heroicon-o-banknotes class="h-6 w-6 text-green-600 dark:text-green-400" />
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
                        <x-heroicon-o-arrow-right-circle class="h-6 w-6 text-amber-600 dark:text-amber-400" />
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
                        <x-heroicon-o-wallet class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
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
                            <x-heroicon-o-receipt-refund class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <a href="{{ route('bills.show', $advance) }}" class="text-lg font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                {{ $advance->bill_number }}
                            </a>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $advance->bill_date?->format('d/m/Y') }} • 
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ match($advance->status) {
                                        'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        'issued' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'paid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        default => 'bg-gray-100 text-gray-800',
                                    } }}">
                                    {{ ucfirst($advance->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-6">
                        <div class="text-right">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">৳ {{ number_format($advance->total, 2) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Received</p>
                            <p class="text-lg font-bold text-green-600 dark:text-green-400">৳ {{ number_format($advance->paid_amount, 2) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Available</p>
                            <p class="text-lg font-bold {{ bccomp($advance->unapplied_amount, '0.00', 2) > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500' }}">
                                ৳ {{ number_format($advance->unapplied_amount, 2) }}
                            </p>
                        </div>
                        
                        <button @click="expanded = !expanded" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <x-heroicon-o-chevron-down class="w-5 h-5 transition-transform" :class="expanded ? 'rotate-180' : ''" />
                        </button>
                    </div>
                </div>
                
                <!-- Expanded Details: Applications -->
                <div x-show="expanded" x-collapse class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Credit Applications</h4>
                    
                    @if($advance->advanceAdjustmentsGiven->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No credit has been applied from this advance bill.</p>
                    @else
                    <div class="space-y-2">
                        @foreach($advance->advanceAdjustmentsGiven as $adjustment)
                        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                            <div class="flex items-center space-x-3">
                                <x-heroicon-o-arrow-right class="w-4 h-4 text-green-500" />
                                <div>
                                    <a href="{{ route('bills.show', $adjustment->final_bill_id) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $adjustment->finalBill?->bill_number }}
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
                                <form action="{{ route('bills.remove-advance', [$adjustment->final_bill_id, $adjustment]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Remove this credit application?')"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    
                    <!-- Apply to Bill Action -->
                    @if(bccomp($advance->unapplied_amount, '0.00', 2) > 0)
                    <div class="mt-4">
                        <button @click="showApplyModal({{ $advance->id }})" 
                                class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            <x-heroicon-o-plus class="w-4 h-4 mr-1" />
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
                
                <form action="{{ route('bills.apply-advance', ['bill' => '__BILL_ID__']) }}" method="POST" 
                      x-ref="applyForm" 
                      @submit.prevent="submitApplication">
                    @csrf
                    
                    <input type="hidden" name="advance_bill_id" :value="selectedAdvance?.id">
                    
                    <div class="space-y-4">
                        <!-- Source Advance -->
                        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3">
                            <p class="text-sm text-amber-700 dark:text-amber-300">
                                From: <strong x-text="selectedAdvance?.bill_number"></strong>
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
                                    {{ $bill->bill_number }} - ৳ {{ number_format($bill->total, 2) }}
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
                                    <span x-text="formatCurrency(getSelectedBill()?.total)"></span>
                                </div>
                                <div class="flex justify-between text-green-600 dark:text-green-400">
                                    <span>Credit Applied:</span>
                                    <span>- <span x-text="formatCurrency(applyAmount)"></span></span>
                                </div>
                                <div class="flex justify-between font-bold border-t border-gray-200 dark:border-gray-600 pt-1">
                                    <span>Net Payable:</span>
                                    <span x-text="formatCurrency(getSelectedBill()?.total - applyAmount)"></span>
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
            'bill_number' => $b->bill_number,
            'total' => $b->total,
            'advance_applied' => $b->advance_applied_amount,
        ])),

        showApplyModal(advanceId) {
            // Fetch advance details via AJAX
            fetch(`/api/bills/${advanceId}/advance-balance`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.selectedAdvance = data.bill;
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
@endsection
```

#### Task 2: Create Controller Method for Credit Management View

**File:** `app/Http/Controllers/BillController.php` (MODIFICATION)

Add a method for the advance credit management view:

```php
/**
 * Show the advance credit management view for a quotation.
 */
public function advanceCreditManagement(Quotation $quotation): View
{
    $this->authorize('view', $quotation);

    // Get all advance bills for this quotation
    $advances = Bill::forTenant()
        ->where('quotation_id', $quotation->id)
        ->where('bill_type', Bill::TYPE_ADVANCE)
        ->where('status', '!=', Bill::STATUS_CANCELLED)
        ->with(['advanceAdjustmentsGiven.finalBill'])
        ->orderBy('created_at', 'desc')
        ->get();

    // Get draft regular bills that can receive credit
    $regularBills = Bill::forTenant()
        ->where('quotation_id', $quotation->id)
        ->where('bill_type', Bill::TYPE_REGULAR)
        ->where('status', Bill::STATUS_DRAFT)
        ->orderBy('created_at', 'desc')
        ->get();

    // Calculate summary
    $summary = [
        'total_advance' => $advances->sum('total'),
        'total_received' => $advances->sum('paid_amount'),
        'total_applied' => $advances->reduce(function ($carry, $bill) {
            return bcadd($carry, bcsub($bill->paid_amount, $bill->unapplied_amount, 2), 2);
        }, '0.00'),
        'available_balance' => $advances->sum('unapplied_amount'),
    ];

    return view('bills.advance-credit', compact('quotation', 'advances', 'regularBills', 'summary'));
}
```

#### Task 3: Add Route for Credit Management

**File:** `routes/web.php` (MODIFICATION)

```php
// Add to the bills routes group
Route::get('/quotations/{quotation}/advance-credit', [BillController::class, 'advanceCreditManagement'])
    ->name('quotations.advance-credit');
```

---

### ✅ End-of-Day Checklist (Day 12)

- [ ] Advance credit management view created
- [ ] Summary cards display correct totals
- [ ] Expandable advance bill details
- [ ] Credit applications listed per advance
- [ ] Modal for applying credit to regular bills
- [ ] Remove credit functionality for draft bills
- [ ] Controller method added
- [ ] Route registered

### ⚠️ Pitfalls & Notes (Day 12)

1. **Draft Bills Only:** Credit can only be applied to or removed from draft bills. Ensure the UI reflects this restriction.

2. **AJAX for Modal:** The modal fetches advance details via AJAX to ensure fresh data when opening.

3. **Form Action:** The apply form's action URL is dynamically updated with the selected bill ID before submission.

4. **Preview Calculation:** The preview shows the net payable after credit application. This is for visual confirmation only; the server recalculates everything.

---

## Phase 5 Summary

| Day | Files Created | Files Modified |
|-----|---------------|----------------|
| 11 | advance-credit-banner.blade.php, AdvanceCreditBanner.php | quotations/show.blade.php |
| 12 | advance-credit.blade.php | BillController.php, routes/web.php |

**Next:** Proceed to [Phase 6 — Frontend: Correction Flow](./phase6_frontend_correction.md)
