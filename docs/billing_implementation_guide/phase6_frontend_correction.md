# Phase 6 — Frontend: Correction Flow (Days 13-14)

This phase implements the cancellation and reissue workflow for handling bill corrections.

---

## Day 13 — Bill Cancellation Interface

### 🎯 Goal
By the end of Day 13, you will have a complete bill cancellation interface with proper warnings and confirmation flow.

### 📋 Prerequisites
- [ ] Phase 5 completed
- [ ] Understanding of cancellation impacts (advance credit reversal, etc.)

---

### 🖥️ Frontend Tasks

#### Task 1: Create Cancellation Confirmation Modal Component

**File:** `resources/views/bills/partials/cancel-modal.blade.php` (NEW)

```blade
@props(['bill'])

<div x-data="cancelModal({{ $bill->id }})" x-cloak>
    <!-- Cancel Button (trigger) -->
    <button type="button" 
            @click="openModal = true"
            @can('cancel', $bill)
            class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-600 rounded-md shadow-sm text-sm font-medium text-red-700 dark:text-red-300 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-gray-600">
        <x-heroicon-o-x-circle class="w-4 h-4 mr-2" />
        Cancel Bill
    </button>
    @endcan

    <!-- Modal -->
    <div x-show="openModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="openModal = false"></div>
            
            <!-- Modal Content -->
            <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-lg w-full shadow-xl">
                
                <!-- Header -->
                <div class="bg-red-50 dark:bg-red-900/20 px-6 py-4 border-b border-red-100 dark:border-red-800 rounded-t-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-500" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-800 dark:text-red-200">
                                Cancel Bill
                            </h3>
                            <p class="text-sm text-red-600 dark:text-red-300">
                                {{ $bill->bill_number }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="px-6 py-4">
                    
                    <!-- Impact Warnings -->
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">This action will:</h4>
                        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-start">
                                <x-heroicon-o-check class="w-4 h-4 text-red-500 mr-2 mt-0.5 flex-shrink-0" />
                                <span>Mark the bill as <strong>cancelled</strong></span>
                            </li>
                            
                            @if($bill->bill_type === 'regular' && $bill->advance_applied_amount > 0)
                            <li class="flex items-start bg-amber-50 dark:bg-amber-900/20 p-2 rounded">
                                <x-heroicon-o-exclamation-circle class="w-4 h-4 text-amber-500 mr-2 mt-0.5 flex-shrink-0" />
                                <span>
                                    <strong class="text-amber-700 dark:text-amber-300">Reverse ৳ {{ number_format($bill->advance_applied_amount, 2) }} in applied advance credit</strong>
                                    <br>
                                    <span class="text-xs">This credit will become available again for future bills.</span>
                                </span>
                            </li>
                            @endif
                            
                            @if($bill->payments()->exists())
                            <li class="flex items-start bg-amber-50 dark:bg-amber-900/20 p-2 rounded">
                                <x-heroicon-o-exclamation-circle class="w-4 h-4 text-amber-500 mr-2 mt-0.5 flex-shrink-0" />
                                <span>
                                    <strong class="text-amber-700 dark:text-amber-300">This bill has {{ $bill->payments->count() }} payment(s) recorded</strong>
                                    <br>
                                    <span class="text-xs">Payment records will be preserved but marked as associated with a cancelled bill.</span>
                                </span>
                            </li>
                            @endif
                            
                            @if($bill->bill_type === 'advance' && $bill->childBills()->exists())
                            <li class="flex items-start bg-red-50 dark:bg-red-900/20 p-2 rounded">
                                <x-heroicon-o-exclamation-circle class="w-4 h-4 text-red-500 mr-2 mt-0.5 flex-shrink-0" />
                                <span>
                                    <strong class="text-red-700 dark:text-red-300">This advance has {{ $bill->childBills->count() }} running bill(s)</strong>
                                    <br>
                                    <span class="text-xs">Running bills linked to this advance may need to be cancelled first.</span>
                                </span>
                            </li>
                            @endif
                            
                            <li class="flex items-start">
                                <x-heroicon-o-arrow-path class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" />
                                <span>You can create a new bill via <strong>Reissue</strong> after cancellation</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Cancellation Form -->
                    <form action="{{ route('bills.cancel', $bill) }}" method="POST" x-ref="cancelForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="cancel_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Reason for Cancellation <span class="text-gray-400">(optional)</span>
                            </label>
                            <textarea id="cancel_reason" 
                                      name="reason" 
                                      rows="3"
                                      x-model="reason"
                                      class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-red-500 focus:ring-red-500"
                                      placeholder="e.g., Customer requested changes, pricing error..."></textarea>
                        </div>

                        <!-- Confirmation Checkbox -->
                        <div class="mb-4">
                            <label class="flex items-start">
                                <input type="checkbox" 
                                       x-model="confirmed"
                                       class="h-4 w-4 text-red-600 rounded border-gray-300 focus:ring-red-500 mt-0.5">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                    I understand this action cannot be undone. The bill will be permanently marked as cancelled.
                                </span>
                            </label>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-3">
                            <button type="button" 
                                    @click="openModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                Keep Bill
                            </button>
                            <button type="submit" 
                                    :disabled="!confirmed"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <x-heroicon-o-x-circle class="w-4 h-4 mr-2" />
                                Cancel Bill
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function cancelModal(billId) {
    return {
        openModal: false,
        reason: '',
        confirmed: false,
    };
}
</script>
@endpush
```

#### Task 2: Update Bill Show View with Cancel Modal

**File:** `resources/views/bills/show.blade.php` (MODIFICATION)

Replace the inline cancel button with the modal component:

```blade
{{-- Replace the cancel button section with --}}
@can('cancel', $bill)
<x-bills.cancel-modal :bill="$bill" />
@endcan
```

#### Task 3: Create Cancellation Success View

**File:** `resources/views/bills/cancelled.blade.php` (NEW)

```blade
@extends('layouts.app')

@section('title', 'Bill Cancelled')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow text-center">
        <div class="p-8">
            <!-- Success Icon -->
            <div class="mx-auto h-16 w-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
                <x-heroicon-o-x-circle class="h-8 w-8 text-red-500" />
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Bill Cancelled
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mb-6">
                Bill <strong>{{ $bill->bill_number }}</strong> has been successfully cancelled.
            </p>

            <!-- Details -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6 text-left">
                <dl class="space-y-2 text-sm">
                    @if($bill->notes)
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Cancellation Reason:</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $bill->notes }}</dd>
                    </div>
                    @endif
                    
                    @if($advanceReversed > 0)
                    <div class="flex justify-between items-center bg-amber-50 dark:bg-amber-900/20 p-2 rounded">
                        <dt class="font-medium text-amber-700 dark:text-amber-300">Advance Credit Reversed:</dt>
                        <dd class="font-bold text-amber-700 dark:text-amber-300">৳ {{ number_format($advanceReversed, 2) }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Actions -->
            <div class="flex justify-center space-x-4">
                @can('reissue', $bill)
                <form action="{{ route('bills.reissue', $bill) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700">
                        <x-heroicon-o-arrow-path class="w-4 h-4 mr-2" />
                        Reissue Bill
                    </button>
                </form>
                @endcan
                
                <a href="{{ route('bills.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <x-heroicon-o-list-bullet class="w-4 h-4 mr-2" />
                    View All Bills
                </a>
                
                @if($bill->quotation)
                <a href="{{ route('quotations.show', $bill->quotation) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <x-heroicon-o-document-text class="w-4 h-4 mr-2" />
                    View Quotation
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
```

#### Task 4: Update Cancel Controller Method

**File:** `app/Http/Controllers/BillController.php` (MODIFICATION)

Update the cancel method to redirect to the success view:

```php
/**
 * Cancel a bill.
 */
public function cancel(Request $request, Bill $bill)
{
    $this->authorize('cancel', $bill);

    $request->validate([
        'reason' => 'nullable|string|max:1000',
    ]);

    try {
        // Track advance reversed for display
        $advanceReversed = $bill->advance_applied_amount;

        $bill = $this->billingService->cancelBill($bill, $request->reason);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill cancelled successfully.',
                'bill' => $bill,
                'advance_reversed' => $advanceReversed,
            ]);
        }

        // Redirect to success page
        return view('bills.cancelled', compact('bill', 'advanceReversed'));

    } catch (\Exception $e) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return back()->withErrors(['error' => $e->getMessage()]);
    }
}
```

---

### ✅ End-of-Day Checklist (Day 13)

- [ ] Cancel modal component created
- [ ] Modal shows impact warnings based on bill type
- [ ] Confirmation checkbox required before submission
- [ ] Cancel success view created
- [ ] Controller updated to redirect to success view
- [ ] Reissue button shown on success page

### ⚠️ Pitfalls & Notes (Day 13)

1. **Impact Warnings:** Different bill types have different cancellation impacts. The modal must dynamically show relevant warnings.

2. **Advance Reversal:** When a regular bill with applied advance is cancelled, the credit is reversed. This must be clearly communicated to the user.

3. **Running Bills:** An advance bill with running bills should warn the user. Consider blocking cancellation if running bills are issued.

---

## Day 14 — Bill Reissue Workflow

### 🎯 Goal
By the end of Day 14, you will have a complete reissue workflow that creates a new draft bill from a cancelled one.

### 📋 Prerequisites
- [ ] Day 13 cancellation completed
- [ ] Understanding of the reissue workflow

---

### 🖥️ Frontend Tasks

#### Task 1: Create Reissue Confirmation Modal

**File:** `resources/views/bills/partials/reissue-modal.blade.php` (NEW)

```blade
@props(['bill'])

<div x-data="reissueModal()" x-cloak>
    <!-- Reissue Button (trigger) -->
    <button type="button" 
            @click="openModal = true"
            @can('reissue', $bill)
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700">
        <x-heroicon-o-arrow-path class="w-4 h-4 mr-2" />
        Reissue Bill
    </button>
    @endcan

    <!-- Modal -->
    <div x-show="openModal" 
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="openModal = false"></div>
            
            <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-lg w-full shadow-xl">
                
                <!-- Header -->
                <div class="bg-amber-50 dark:bg-amber-900/20 px-6 py-4 border-b border-amber-100 dark:border-amber-800 rounded-t-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-arrow-path class="h-6 w-6 text-amber-500" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-amber-800 dark:text-amber-200">
                                Reissue Bill
                            </h3>
                            <p class="text-sm text-amber-600 dark:text-amber-300">
                                Create a new draft from cancelled bill
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="px-6 py-4">
                    
                    <!-- Explanation -->
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            This will create a new draft bill with the same details as the cancelled bill 
                            <strong>{{ $bill->bill_number }}</strong>. You can then edit and issue the new bill.
                        </p>
                    </div>

                    <!-- What gets copied -->
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">The new bill will include:</h4>
                        <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-center">
                                <x-heroicon-o-check class="w-4 h-4 text-green-500 mr-2" />
                                Same bill type ({{ ucfirst($bill->bill_type) }})
                            </li>
                            <li class="flex items-center">
                                <x-heroicon-o-check class="w-4 h-4 text-green-500 mr-2" />
                                Same line items and amounts
                            </li>
                            <li class="flex items-center">
                                <x-heroicon-o-check class="w-4 h-4 text-green-500 mr-2" />
                                Same challan links (if any)
                            </li>
                            <li class="flex items-center">
                                <x-heroicon-o-check class="w-4 h-4 text-green-500 mr-2" />
                                New bill number (auto-generated)
                            </li>
                        </ul>
                    </div>

                    <!-- What won't be copied -->
                    <div class="mb-4 bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Not copied:</h4>
                        <ul class="space-y-1 text-sm text-gray-500 dark:text-gray-400">
                            <li class="flex items-center">
                                <x-heroicon-o-x-mark class="w-4 h-4 text-gray-400 mr-2" />
                                Payments (need to be re-recorded)
                            </li>
                            <li class="flex items-center">
                                <x-heroicon-o-x-mark class="w-4 h-4 text-gray-400 mr-2" />
                                Advance credit applications (need to be re-applied)
                            </li>
                            <li class="flex items-center">
                                <x-heroicon-o-x-mark class="w-4 h-4 text-gray-400 mr-2" />
                                Status (new bill starts as draft)
                            </li>
                        </ul>
                    </div>

                    <!-- Form -->
                    <form action="{{ route('bills.reissue', $bill) }}" method="POST" x-ref="reissueForm">
                        @csrf

                        <!-- Actions -->
                        <div class="flex justify-end space-x-3">
                            <button type="button" 
                                    @click="openModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-md hover:bg-amber-700">
                                <x-heroicon-o-document-plus class="w-4 h-4 mr-2" />
                                Create New Draft
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function reissueModal() {
    return {
        openModal: false,
    };
}
</script>
@endpush
```

#### Task 2: Create Reissue Success View

**File:** `resources/views/bills/reissued.blade.php` (NEW)

```blade
@extends('layouts.app')

@section('title', 'Bill Reissued')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow text-center">
        <div class="p-8">
            <!-- Success Icon -->
            <div class="mx-auto h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-4">
                <x-heroicon-o-check-circle class="h-8 w-8 text-green-500" />
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                Bill Reissued
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mb-6">
                A new draft bill has been created from the cancelled bill.
            </p>

            <!-- Comparison -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                    <p class="text-xs text-red-500 mb-1">Cancelled Bill</p>
                    <p class="text-lg font-bold text-red-700 dark:text-red-300">{{ $oldBill->bill_number }}</p>
                    <p class="text-xs text-red-600 dark:text-red-400">Status: Cancelled</p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <p class="text-xs text-green-500 mb-1">New Draft Bill</p>
                    <p class="text-lg font-bold text-green-700 dark:text-green-300">{{ $newBill->bill_number }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400">Status: Draft</p>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6 text-left">
                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">Next Steps:</h4>
                <ol class="list-decimal list-inside text-sm text-blue-700 dark:text-blue-300 space-y-1">
                    <li>Review the new draft bill</li>
                    <li>Make any necessary edits</li>
                    @if($newBill->bill_type === 'regular' && $newBill->quotation->bills()->where('bill_type', 'advance')->where('status', '!=', 'cancelled')->exists())
                    <li>Re-apply advance credit if needed</li>
                    @endif
                    <li>Issue the bill when ready</li>
                </ol>
            </div>

            <!-- Actions -->
            <div class="flex justify-center space-x-4">
                @can('update', $newBill)
                <a href="{{ route('bills.edit', $newBill) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <x-heroicon-o-pencil class="w-4 h-4 mr-2" />
                    Edit & Issue
                </a>
                @endcan
                
                @can('view', $newBill)
                <a href="{{ route('bills.show', $newBill) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <x-heroicon-o-eye class="w-4 h-4 mr-2" />
                    View Draft
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
```

#### Task 3: Update Reissue Controller Method

**File:** `app/Http/Controllers/BillController.php` (MODIFICATION)

```php
/**
 * Reissue a cancelled bill (creates a new draft copy).
 */
public function reissue(Request $request, Bill $bill)
{
    $this->authorize('reissue', $bill);

    try {
        $oldBill = $bill;
        $newBill = $this->billingService->reissueBill($bill);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill reissued successfully.',
                'old_bill' => $oldBill,
                'new_bill' => $newBill,
            ]);
        }

        return view('bills.reissued', compact('oldBill', 'newBill'));

    } catch (\Exception $e) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return back()->withErrors(['error' => $e->getMessage()]);
    }
}
```

#### Task 4: Create Correction History View

**File:** `resources/views/bills/partials/correction-history.blade.php` (NEW)

This component shows the correction history for a bill (original → cancelled → reissued).

```blade
@props(['bill'])

@php
// Find the chain of bills: original -> cancelled -> reissued
$history = collect();
$currentBill = $bill;

// Find the original bill (go up the chain)
while ($currentBill->reissued_from_id ?? false) {
    $currentBill = \App\Models\Bill::find($currentBill->reissued_from_id);
}
$originalBill = $currentBill;

// Build the chain forward
$chain = collect([$originalBill]);
$nextBill = $originalBill;
while ($nextBill->reissued_to_id ?? false) {
    $nextBill = \App\Models\Bill::find($nextBill->reissued_to_id);
    $chain->push($nextBill);
}
@endphp

@if($chain->count() > 1)
<div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
        <x-heroicon-o-clock class="w-4 h-4 inline mr-1" />
        Correction History
    </h4>
    
    <div class="relative">
        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-600"></div>
        
        @foreach($chain as $index => $chainBill)
        <div class="relative flex items-start mb-4 last:mb-0">
            <div class="relative z-10 flex items-center justify-center h-8 w-8 rounded-full
                {{ $chainBill->status === 'cancelled' 
                    ? 'bg-red-100 dark:bg-red-900/30' 
                    : ($chainBill->id === $bill->id 
                        ? 'bg-blue-100 dark:bg-blue-900/30' 
                        : 'bg-gray-100 dark:bg-gray-600') }}">
                @if($chainBill->status === 'cancelled')
                <x-heroicon-o-x-circle class="h-4 w-4 text-red-500" />
                @elseif($chainBill->id === $bill->id)
                <x-heroicon-o-document-text class="h-4 w-4 text-blue-500" />
                @else
                <x-heroicon-o-document class="h-4 w-4 text-gray-500" />
                @endif
            </div>
            
            <div class="ml-4 flex-1">
                <div class="flex items-center justify-between">
                    <div>
                        <a href="{{ route('bills.show', $chainBill) }}" 
                           class="text-sm font-medium {{ $chainBill->id === $bill->id 
                               ? 'text-blue-600 dark:text-blue-400' 
                               : 'text-gray-900 dark:text-white' }} hover:underline">
                            {{ $chainBill->bill_number }}
                        </a>
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ match($chainBill->status) {
                                'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-300',
                                'issued' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                'paid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                default => 'bg-gray-100 text-gray-800',
                            } }}">
                            {{ ucfirst($chainBill->status) }}
                        </span>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $chainBill->created_at->format('d/m/Y') }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    ৳ {{ number_format($chainBill->total, 2) }}
                    @if($chainBill->status === 'cancelled' && $chainBill->notes)
                    • {{ Str::limit($chainBill->notes, 50) }}
                    @endif
                </p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
```

#### Task 5: Add Reissue Tracking Columns to Bills Table

**File:** `database/migrations/2026_03_12_000009_add_reissue_tracking_to_bills.php` (NEW)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            // Track the chain of reissued bills
            $table->foreignId('reissued_from_id')->nullable()->after('parent_bill_id')
                ->constrained('bills')->nullOnDelete();
            $table->foreignId('reissued_to_id')->nullable()->after('reissued_from_id')
                ->constrained('bills')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['reissued_from_id']);
            $table->dropForeign(['reissued_to_id']);
            $table->dropColumn(['reissued_from_id', 'reissued_to_id']);
        });
    }
};
```

#### Task 6: Update BillingService Reissue Method

**File:** `app/Services/BillingService.php` (MODIFICATION)

Update the `reissueBill` method to track the chain:

```php
/**
 * Reissue a cancelled bill (creates a new draft copy).
 */
public function reissueBill(Bill $cancelledBill): Bill
{
    if ($cancelledBill->status !== Bill::STATUS_CANCELLED) {
        throw new \InvalidArgumentException('Only cancelled bills can be reissued.');
    }

    return DB::transaction(function () use ($cancelledBill) {
        $newBill = $cancelledBill->replicate([
            'id',
            'bill_number',
            'status',
            'is_locked',
            'lock_reason',
            'locked_at',
            'created_at',
            'updated_at',
            'reissued_from_id',
            'reissued_to_id',
        ]);

        $prefix = match ($cancelledBill->bill_type) {
            Bill::TYPE_ADVANCE => 'ADV',
            Bill::TYPE_RUNNING => 'RUN',
            Bill::TYPE_REGULAR => 'REG',
            default => 'BIL',
        };
        $newBill->bill_number = $this->generateBillNumber($prefix);
        $newBill->status = Bill::STATUS_DRAFT;
        $newBill->is_locked = false;
        $newBill->lock_reason = null;
        $newBill->locked_at = null;
        $newBill->advance_applied_amount = '0.00';
        $newBill->net_payable_amount = $newBill->total;
        $newBill->reissued_from_id = $cancelledBill->id;
        $newBill->save();

        // Update the cancelled bill to point to the new bill
        $cancelledBill->update(['reissued_to_id' => $newBill->id]);

        // Copy bill items
        foreach ($cancelledBill->billItems as $item) {
            $newItem = $item->replicate(['id', 'bill_id', 'created_at', 'updated_at']);
            $newItem->bill_id = $newBill->id;
            $newItem->save();
        }

        activity('billing')
            ->performedOn($newBill)
            ->causedBy(auth()->user())
            ->withProperties([
                'original_bill_id' => $cancelledBill->id,
                'original_bill_number' => $cancelledBill->bill_number,
            ])
            ->log("Bill reissued from cancelled bill {$cancelledBill->bill_number}");

        return $newBill;
    });
}
```

---

### ✅ End-of-Day Checklist (Day 14)

- [ ] Reissue modal component created
- [ ] Reissue success view created
- [ ] Controller updated for reissue workflow
- [ ] Correction history component created
- [ ] Migration for reissue tracking columns
- [ ] BillingService updated to track reissue chain
- [ ] Bill model updated with new fillable fields

### ⚠️ Pitfalls & Notes (Day 14)

1. **Chain Tracking:** The `reissued_from_id` and `reissued_to_id` columns create a linked list of bills. This allows for unlimited corrections.

2. **No Credit Auto-Reapply:** When reissuing, advance credit is NOT automatically re-applied. The user must manually apply it again. This is intentional for safety.

3. **Bill Number Generation:** Each reissue gets a new bill number. The old number is preserved on the cancelled bill for reference.

4. **Activity Logging:** The reissue action is logged with both the old and new bill numbers for audit purposes.

---

## Phase 6 Summary

| Day | Files Created | Files Modified |
|-----|---------------|----------------|
| 13 | cancel-modal.blade.php, cancelled.blade.php | BillController.php, show.blade.php |
| 14 | reissue-modal.blade.php, reissued.blade.php, correction-history.blade.php, migration | BillController.php, BillingService.php |

**Next:** Proceed to [Phase 7 — Testing & Hardening](./phase7_testing.md)
