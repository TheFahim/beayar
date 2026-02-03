<x-dashboard.layout.default title="New Bill">
    <!-- Breadcrumb -->
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.bills.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition duration-150">
                <x-ui.svg.book class="w-3 h-3 me-2" />
                Bill List
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="New Bill" />
    </x-dashboard.ui.bread-crumb>

    <!-- Compact Sticky Action Bar -->
    <div class="top-4 z-20 mb-4 animate-fade-in-up">
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/95 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 p-4 backdrop-blur-sm">
            <div class="flex-1 min-w-0">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-0.5">Create New Bill</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400">Create {{ ucfirst($billType) }} Bill for quotation</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <a href="{{ route('tenant.bills.index') }}"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 font-medium rounded-lg shadow-sm hover:shadow transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span class="text-sm">Back to Bills</span>
                </a>
                <button type="submit" form="billForm"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium rounded-lg shadow hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm font-semibold">Create {{ ucfirst($billType) }} Bill</span>
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

    <!-- Success Messages -->
    @if (session('error'))
        <div class="animate-fade-in-up mb-4">
            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-xl shadow-sm overflow-hidden">
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

    <!-- Loading State -->
    <div x-show="$data.loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-lg p-6 shadow-lg">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span class="text-gray-700">Loading bill form...</span>
            </div>
        </div>
    </div>

    <form x-data="billForm({
        billType: '{{ $billType }}',
        quotation: {{ json_encode($quotation) }},
        challans: {{ json_encode($challans ?? []) }},
        activeRevision: {{ json_encode($activeRevision) }},
        parentBill: {{ json_encode($parentBill) }},
        existingAdvanceBill: {{ json_encode($existingAdvanceBill) }},
        existingRegularBills: {{ json_encode($existingRegularBills ?? []) }}
    })" id="billForm" class="space-y-6" :action="billType === 'regular' ? '{{ route('tenant.bills.store') }}' : '{{ route('tenant.bills.store-from-quotation', $quotation) }}'" method="POST"
        @submit.prevent="validateForm">
        @csrf
        <div>
            <input type="hidden" name="quotation_id" :value="quotation.id">
            <input type="hidden" name="bill_type" :value="billType">
            <input type="hidden" name="parent_bill_id" :value="parentBill ? parentBill.id : ''">
            <input type="hidden" name="bill_percentage" :value="billType === 'running' ? installmentPercentage : advancePercentage">
            <input type="hidden" name="quotation_revision_id" :value="activeRevision?.id || ''">

            <!-- Hidden items payload -->
            <template x-for="(item, idx) in items" :key="idx">
                <div>
                    <input type="hidden" :name="'items['+idx+'][quotation_product_id]'" :value="item.quotation_product_id">
                    <input type="hidden" :name="'items['+idx+'][quantity]'" :value="item.quantity">
                    <template x-if="item.allocations && item.allocations.length">
                        <template x-for="(alloc, aidx) in item.allocations" :key="aidx">
                            <div>
                                <input type="hidden" :name="'items['+idx+'][allocations]['+aidx+'][challan_product_id]'" :value="alloc.challan_product_id">
                                <input type="hidden" :name="'items['+idx+'][allocations]['+aidx+'][billed_quantity]'" :value="alloc.billed_quantity">
                            </div>
                        </template>
                    </template>
                </div>
            </template>

            <!-- Compact Main Content Grid -->
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Top Section: Info Cards - More Compact -->
                <div class="grid lg:grid-cols-12 gap-4">
                    <!-- Quotation & Customer Card - Compact -->
                    <div class="lg:col-span-4">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden h-full hover:shadow-md transition-shadow duration-300">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-3">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-bold text-white">Quotation Details</h3>
                                </div>
                            </div>
                            <div class="p-4 space-y-3">
                                <!-- Quotation Number -->
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                    <span
                                        class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">Quotation
                                        No.</span>
                                    <span class="block text-sm font-bold text-gray-900 dark:text-white truncate"
                                        title="{{ $quotation->quotation_no }}">
                                        {{ $quotation->quotation_no }}
                                    </span>
                                </div>

                                <!-- Bill Type Badge -->
                                <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/30 rounded-lg p-3 border border-purple-200 dark:border-purple-700">
                                    <span class="block text-xs font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wide mb-1">Bill Type</span>
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-md bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                        {{ ucfirst($billType) }} Bill
                                    </span>
                                    <p class="text-xs text-purple-600 dark:text-purple-400 mt-1" x-text="getBillTypeDescription()"></p>
                                </div>

                                <!-- Customer Info - Compact -->
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
                                                <svg class="w-2.5 h-2.5 mr-1" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $quotation->customer->company->name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Shipping Address - Compact -->
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
                            </div>
                        </div>
                    </div>
                    <!-- Right Column - Compact -->
                    <div class="lg:col-span-8 space-y-4">
                        <!-- Bill Information Section - Compact -->
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-300">
                            <div class="bg-gradient-to-r from-amber-500 to-orange-500 p-3">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-bold text-white">Bill Information</h3>
                                </div>
                            </div>
                            <div class="p-4">
                                <!-- Advance Bill Specific Fields -->
                                <div x-show="billType === 'advance'" class="space-y-4">
                                    <!-- Error State -->
                                    <div x-show="errors.advancePercentage" class="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-sm text-red-700" x-text="errors.advancePercentage"></p>
                                        </div>
                                    </div>

                                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 sm:p-4 border border-blue-200 dark:border-blue-700">
                                        <h4 class="text-sm sm:text-base font-bold text-blue-800 dark:text-blue-300 mb-3">Advance Bill Details</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                            <div class="min-w-0">
                                                <label for="advance_percentage" class="block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                                    Advance Percentage <span class="text-red-500">*</span>
                                                </label>
                                                <div class="relative">
                                                    <x-ui.form.input name="advance_percentage" id="advance_percentage" type="number"
                                                        x-model="advancePercentage" @input="calculateAdvanceAmount()"
                                                        class="w-full pr-8 sm:pr-10 text-xs sm:text-sm" placeholder="e.g., 50"
                                                        x-bind:class="{
                                                            'border-red-500 focus:ring-red-500 focus:border-red-500': errors.advancePercentage,
                                                            'border-green-500 focus:ring-green-500 focus:border-green-500': advancePercentage > 0 && advancePercentage <= 100 && !errors.advancePercentage
                                                        }" />
                                                    @error('advance_percentage')
                                                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                                    @enderror
                                                    <span class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs sm:text-sm pointer-events-none">%</span>
                                                </div>
                                                <!-- Custom validation messages -->
                                                <div class="mt-1 space-y-1">
                                                    <p x-show="!errors.advancePercentage && advancePercentage !== null && advancePercentage !== '' && (advancePercentage <= 0 || advancePercentage > 100)"
                                                       class="text-xs sm:text-sm text-yellow-700 dark:text-yellow-300">
                                                        Please enter a percentage between 1 and 100
                                                    </p>
                                                    <p x-show="!errors.advancePercentage && advancePercentage > 0 && advancePercentage <= 100"
                                                       class="text-xs sm:text-sm text-green-600 dark:text-green-400">
                                                        ✓ Valid advance percentage
                                                    </p>
                                                </div>
                                                <!-- Helper text -->
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    Enter percentage of quotation total for advance payment
                                                </p>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="relative flex items-center justify-between mb-1.5">
                                                    <label for="total_amount" class="block text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-200">
                                                        Advance Amount
                                                    </label>
                                                    <div class="relative flex items-center" x-data="{ showTooltip: false }">
                                                        <button type="button"
                                                                @mouseenter="showTooltip = true"
                                                                @mouseleave="showTooltip = false"
                                                                class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                        </button>
                                                        <div x-show="showTooltip"
                                                             class="absolute left-0 sm:left-auto z-10 px-2 py-1 text-xs text-white bg-gray-900 rounded-md shadow-lg -mt-10 sm:-mt-8 sm:ml-4 w-56"
                                                             x-transition>
                                                            Calculated as: Quotation Total × Advance Percentage
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="relative">
                                                    <x-ui.form.input name="total_amount" id="total_amount" type="number"
                                                        x-model="totalAmount" readonly step="0.01"
                                                        class="w-full pr-12 text-xs sm:text-sm bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700 text-blue-800 dark:text-blue-300 font-semibold"
                                                        x-bind:placeholder="formatCurrency(0)" />
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                        <span class="text-xs sm:text-sm text-blue-600 dark:text-blue-400">BDT</span>
                                                    </div>
                                                </div>
                                                <!-- Quotation reference -->
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    Based on quotation total:
                                                    <span class="font-medium" x-text="formatCurrency(quotation?.active_revision?.total || 0)"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <!-- Running Bill Specific Fields -->
                                <div x-show="billType === 'running'" class="space-y-4">
                                    <!-- Error State -->
                                    <div x-show="errors.installmentPercentage" class="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-sm text-red-700" x-text="errors.installmentPercentage"></p>
                                        </div>
                                    </div>

                                    <div x-show="parentBill" class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-700">
                                        <h4 class="text-sm font-bold text-purple-800 dark:text-purple-300 mb-3">Parent Bill Details</h4>
                                        <div class="grid md:grid-cols-2 gap-4">
                                            <div>
                                                <span class="block text-xs font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wide mb-1">Parent Invoice</span>
                                                <span class="block text-sm font-bold text-purple-900 dark:text-purple-300" x-text="parentBill ? parentBill.invoice_no : ''"></span>
                                            </div>
                                            <div>
                                                <span class="block text-xs font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wide mb-1">Total Amount</span>
                                                <span class="block text-sm font-bold text-purple-900 dark:text-purple-300" x-text="parentBill ? `BDT ${parentBill.total_amount}` : ''"></span>
                                            </div>
                                            <div>
                                                <span class="block text-xs font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wide mb-1">Remaining Amount</span>
                                                <span class="block text-sm font-bold text-green-600 dark:text-green-400" x-text="parentBill ? `BDT ${remainingAmount}` : ''"></span>
                                            </div>
                                            <div>
                                                <span class="block text-xs font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wide mb-1">Remaining %</span>
                                                <span class="block text-sm font-bold text-green-600 dark:text-green-400" x-text="parentBill ? `${remainingPercentage}%` : ''"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-700">
                                        <h4 class="text-sm font-bold text-purple-800 dark:text-purple-300 mb-3">Installment Details</h4>
                                        <div class="grid md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="installment_percentage" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                                    Installment Percentage <span class="text-red-500">*</span>
                                                </label>
                                                <div class="relative">
                                                <x-ui.form.input name="installment_percentage" id="installment_percentage" type="number"
                                                    x-model="installmentPercentage" @input="calculateInstallmentAmount()"
                                                    class="pr-8 text-xs" placeholder="e.g., 25" min="0" x-bind:max="remainingPercentage || 0" required />
                                                @error('installment_percentage')
                                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                                    <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-xs">%</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label for="installment_amount" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                                    Installment Amount <span class="text-red-500">*</span>
                                                </label>
                                                <x-ui.form.input name="installment_amount" id="installment_amount" type="number"
                                                    x-model="installmentAmount" readonly step="0.01" class="text-xs" />
                                                @error('installment_amount')
                                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Common Fields -->
                                <div class="grid md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="invoice_no" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                            Invoice Number <span class="text-red-500">*</span>
                                        </label>
                                        <x-ui.form.input name="invoice_no" id="invoice_no" type="text"
                                            class="text-xs" placeholder="e.g., INV-001" required />
                                        @error('invoice_no')
                                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="bill_date" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                            Bill Date <span class="text-red-500">*</span>
                                        </label>
                                        <x-ui.form.input name="bill_date" id="bill_date" type="text"
                                            class="flowbite-datepicker text-xs" placeholder="dd/mm/yyyy" required />
                                        @error('bill_date')
                                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="payment_received_date" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                            Payment Received Date
                                        </label>
                                        <x-ui.form.input name="payment_received_date" id="payment_received_date" type="text"
                                            class="flowbite-datepicker text-xs" placeholder="dd/mm/yyyy" />
                                    </div>
                                </div>

                                <div class="grid md:grid-cols-3 gap-4">
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <label for="total_amount" class="block text-sm font-bold text-gray-700 dark:text-gray-200">
                                                Total Amount <span class="text-red-500">*</span>
                                            </label>
                                            <!-- Advance bill indicator -->
                                            <span x-show="billType === 'advance'"
                                                  class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 rounded-full">
                                                Advance Payment
                                            </span>
                                        </div>
                                        <div class="relative">
                                            <x-ui.form.input name="total_amount" id="total_amount" type="number"
                                                x-model="totalAmount" step="0.01" readonly
                                                x-bind:class="{
                                                    'text-xs': true,
                                                    'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700 text-blue-800 dark:text-blue-300 font-semibold': billType === 'advance',
                                                    'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700 text-green-800 dark:text-green-300 font-semibold': billType === 'regular',
                                                    'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-700 text-purple-800 dark:text-purple-300 font-semibold': billType === 'running'
                                                }" />
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <span x-bind:class="{
                                                    'text-blue-600 dark:text-blue-400': billType === 'advance',
                                                    'text-green-600 dark:text-green-400': billType === 'regular',
                                                    'text-purple-600 dark:text-purple-400': billType === 'running'
                                                }" class="text-xs">BDT</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <label for="paid" class="block text-sm font-bold text-gray-700 dark:text-gray-200">
                                                Amount Paid <span class="text-red-500">*</span>
                                            </label>
                                        </div>
                                        <div class="relative">
                                            <x-ui.form.input name="paid" id="paid" type="number"
                                                x-model="paidAmount" @input="calculateDue()" step="0.01"
                                                x-bind:readonly="billType === 'advance'"
                                                x-bind:class="{
                                                    'text-xs': true,
                                                    'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 cursor-not-allowed': billType === 'advance',
                                                    'bg-white dark:bg-gray-700': billType !== 'advance'
                                                }"
                                                x-bind:placeholder="formatCurrency(0)"
                                                required />
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <span class="text-xs text-gray-500">BDT</span>
                                            </div>
                                        </div>
                                        <!-- Payment validation messages -->

                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <label for="due" class="block text-sm font-bold text-gray-700 dark:text-gray-200">
                                                Amount Due <span class="text-red-500">*</span>
                                            </label>
                                            <!-- Due amount status indicator -->
                                            <span x-show="billType === 'advance'"
                                                  x-bind:class="{
                                                      'inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full': true,
                                                      'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': parseFloat(dueAmount) === 0,
                                                      'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': parseFloat(dueAmount) > 0
                                                  }">
                                                <svg x-show="parseFloat(dueAmount) === 0" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                <svg x-show="parseFloat(dueAmount) > 0" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                </svg>
                                                <span x-text="parseFloat(dueAmount) === 0 ? 'Fully Paid' : 'Balance Due'"></span>
                                            </span>
                                        </div>
                                        <div class="relative">
                                            <x-ui.form.input name="due" id="due" type="number"
                                                x-model="dueAmount" step="0.01" readonly
                                                x-bind:class="{
                                                    'text-xs': true,
                                                    'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700 text-green-800 dark:text-green-300 font-semibold': parseFloat(dueAmount) === 0,
                                                    'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-700 text-yellow-800 dark:text-yellow-300 font-semibold': parseFloat(dueAmount) > 0,
                                                    'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700 text-red-800 dark:text-red-300 font-semibold': parseFloat(dueAmount) < 0
                                                }" />
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <span x-bind:class="{
                                                    'text-green-600 dark:text-green-400': parseFloat(dueAmount) === 0,
                                                    'text-yellow-600 dark:text-yellow-400': parseFloat(dueAmount) > 0,
                                                    'text-red-600 dark:text-red-400': parseFloat(dueAmount) < 0
                                                }" class="text-xs">BDT</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="notes" class="block text-sm font-bold text-gray-700 dark:text-gray-200">
                                            Notes
                                        </label>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            <span id="notes-counter">0</span>/500 characters
                                        </span>
                                    </div>
                                    <div class="relative">
                                        <textarea name="notes" id="notes" rows="3" maxlength="500"
                                            class="w-full text-xs p-3 rounded-lg border border-gray-300 dark:border-gray-600
                                                   bg-white dark:bg-gray-700
                                                   text-gray-900 dark:text-gray-100
                                                   placeholder-gray-500 dark:placeholder-gray-400
                                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                                   focus:dark:ring-blue-400 focus:dark:border-blue-400
                                                   transition-colors duration-200
                                                   resize-y min-h-[80px] max-h-[200px]"
                                            placeholder="Add any additional notes about this bill..."
                                            x-on:input="updateNotesCounter()"
                                            x-init="updateNotesCounter()"></textarea>
                                        <!-- Character counter -->
                                        <div class="absolute bottom-2 right-2 text-xs text-gray-400 dark:text-gray-500">
                                            <span x-text="(() => {
                                                const textarea = $el.closest('div').querySelector('textarea');
                                                return textarea ? textarea.value.length : 0;
                                            })()">0</span>/500
                                        </div>
                                    </div>
                                    <!-- Helper text -->
                                    <div class="mt-2 space-y-1">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Provide any relevant details about payment terms, delivery instructions, or special conditions.
                                        </p>
                                        <!-- Bill type specific hints -->
                                        <p x-show="billType === 'advance'" class="text-xs text-blue-600 dark:text-blue-400">
                                            💡 For advance bills, note any specific conditions for the advance payment.
                                        </p>
                                        <p x-show="billType === 'regular'" class="text-xs text-green-600 dark:text-green-400">
                                            💡 For regular bills, mention any delivery details or challan references.
                                        </p>
                                        <p x-show="billType === 'running'" class="text-xs text-purple-600 dark:text-purple-400">
                                            💡 For running bills, specify the installment number and any payment schedule details.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Challan Selection for Regular Bills -->
                        <div x-show="billType === 'regular'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                        </div>
                                        <h3 class="text-base font-bold text-white">Select Challans</h3>
                                    </div>
                                    <span class="text-xs text-white/90" x-text="`Selected: ${selectedChallansCount}`"></span>
                                </div>
                            </div>
                            <div class="p-4">
                                <!-- Error State -->
                                <div x-show="errors.regularSelection" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-sm text-red-700" x-text="errors.regularSelection"></p>
                                    </div>
                                </div>

                                @if($challans && $challans->count() > 0)
                                    <div class="space-y-3 max-h-64 overflow-y-auto">
                                        @foreach($challans as $challan)
                                            <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer transition-colors">
                                                <input type="checkbox" name="challan_ids[]" value="{{ $challan->id }}"
                                                    @change="calculateRegularBillTotal()"
                                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                                <div class="ml-3 flex-1">
                                                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $challan->challan_no }}</div>
                                                    <div class="text-xs text-gray-600 dark:text-gray-400">Date: {{ $challan->date }}</div>
                                                    <div class="text-xs text-gray-600 dark:text-gray-400">Products: {{ $challan->products->count() }}</div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <p class="text-sm font-medium text-gray-600 mb-1">No challans available</p>
                                        <p class="text-xs text-gray-500">No delivered challans found for this quotation</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Summary Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-500 to-purple-500 p-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-bold text-white">Bill Summary</h3>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="grid md:grid-cols-3 gap-4" :class="{'md:grid-cols-4': billType === 'running'}">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400" x-text="`BDT ${totalAmount}`"></div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">Total Amount</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="`BDT ${paidAmount}`"></div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">Amount Paid</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-red-600 dark:text-red-400" x-text="`BDT ${dueAmount}`"></div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">Amount Due</div>
                                    </div>

                                    <div class="text-center" x-show="billType === 'running'">
                                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400" x-text="`${installmentPercentage}%`"></div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">Installment %</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>


</x-dashboard.layout.default>
