<x-dashboard.layout.default title="Create Regular Bill">
    <!-- Breadcrumb -->
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition duration-150">
                <x-ui.svg.qutation class="w-3 h-3 me-2" />
                Quotations
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Create Regular Bill" />
    </x-dashboard.ui.bread-crumb>

    <!-- Compact Sticky Action Bar -->
    <div class="top-4 z-20 mb-4 animate-fade-in-up">
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/95 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 p-4 backdrop-blur-sm">
            <div class="flex-1 min-w-0">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-0.5">Create Regular Bill</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400">Bill for delivered challans</p>
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
                <button type="submit" form="regularBillForm"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-medium rounded-lg shadow hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm font-semibold">Create Regular Bill</span>
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

    <form id="regularBillForm" class="space-y-6" action="{{ route('tenant.bills.store-from-quotation', $quotation) }}" method="POST">
        @csrf
        <input type="hidden" name="bill_type" value="regular">
        <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Top Section: Info Cards -->
            <div class="grid lg:grid-cols-12 gap-4">
                <!-- Quotation & Customer Card -->
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
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <span
                                    class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-0.5">Quotation
                                    No.</span>
                                <span class="block text-sm font-bold text-gray-900 dark:text-white truncate"
                                    title="{{ $quotation->quotation_no }}">
                                    {{ $quotation->quotation_no }}
                                </span>
                            </div>

                            <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/30 rounded-lg p-3 border border-green-200 dark:border-green-700">
                                <span class="block text-xs font-semibold text-green-700 dark:text-green-400 uppercase tracking-wide mb-1">Bill Type</span>
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-md bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                    Regular Bill
                                </span>
                                <p class="text-xs text-green-600 dark:text-green-400 mt-1">For delivered challans</p>
                            </div>

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

                            <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                                <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Available Challans</span>
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-200 dark:border-blue-700">
                                    <span class="block text-lg font-bold text-blue-800 dark:text-blue-300">
                                        {{ $challans->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="lg:col-span-8 space-y-4">
                    <!-- Challan Selection Section -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-300">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-bold text-white">Select Challans</h3>
                                </div>
                                <span class="text-xs text-white/90" id="selected-challans-count">Selected: 0</span>
                            </div>
                        </div>
                        <div class="p-4">
                            @if($challans && $challans->count() > 0)
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    @foreach($challans as $challan)
                                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex items-center">
                                                    <input type="checkbox" name="challan_products[]" value="{{ $challan->id }}" 
                                                        class="challan-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500 mr-3"
                                                        data-challan-id="{{ $challan->id }}">
                                                    <div>
                                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $challan->challan_no }}</h4>
                                                        <p class="text-xs text-gray-600 dark:text-gray-400">Date: {{ $challan->date }}</p>
                                                    </div>
                                                </div>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $challan->challanProducts->count() }} products</span>
                                            </div>

                                            <!-- Challan Products -->
                                            <div class="challan-products ml-6 space-y-2">
                                                @foreach($challan->challanProducts as $product)
                                                    <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-800 rounded border">
                                                        <div class="flex items-center">
                                                            <input type="checkbox" name="challan_products[{{ $challan->id }}][{{ $product->id }}][selected]" 
                                                                class="product-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500 mr-2"
                                                                data-product-id="{{ $product->id }}"
                                                                data-challan-id="{{ $challan->id }}">
                                                            <div>
                                                                <p class="text-xs font-medium text-gray-900 dark:text-white">{{ $product->product->name }}</p>
                                                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                                                    Challan Qty: {{ $product->quantity }} | Remaining: {{ $product->quantity - $product->billed_quantity }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center space-x-2">
                                                            <input type="number" name="challan_products[{{ $challan->id }}][{{ $product->id }}][quantity]" 
                                                                class="product-quantity w-20 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700"
                                                                placeholder="Qty" min="0.01" max="{{ $product->quantity - $product->billed_quantity }}" step="0.01"
                                                                disabled>
                                                            <input type="number" name="challan_products[{{ $challan->id }}][{{ $product->id }}][unit_price]" 
                                                                class="product-price w-24 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700"
                                                                placeholder="Price" min="0.01" step="0.01"
                                                                value="{{ $product->unit_price }}" disabled>
                                                            <input type="number" name="challan_products[{{ $challan->id }}][{{ $product->id }}][tax_percentage]" 
                                                                class="product-tax w-16 text-xs rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700"
                                                                placeholder="Tax %" min="0" max="100" step="0.01"
                                                                value="{{ $product->tax_percentage }}" disabled>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
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

                    <!-- Bill Summary Section -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-300">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 p-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-white">Bill Summary</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label for="invoice_no" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                        Invoice Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="invoice_no" id="invoice_no" 
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                                        placeholder="e.g., REG-001" required>
                                </div>
                                <div>
                                    <label for="bill_date" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                        Bill Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="bill_date" id="bill_date" 
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div>
                                    <label for="due_date" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                        Due Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="due_date" id="due_date" 
                                        class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                                        required>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="discount" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                        Discount
                                    </label>
                                    <div class="relative">
                                        <input type="number" name="discount" id="discount" step="0.01" min="0"
                                            class="w-full pr-12 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                                            placeholder="0.00">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <span class="text-sm text-gray-500">BDT</span>
                                        </div>
                                    </div>
                                </div>
                                <div></div>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <div class="grid md:grid-cols-3 gap-4 text-center">
                                    <div>
                                        <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400" id="total-amount-display">0.00</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">Total Amount</div>
                                    </div>
                                    <div>
                                        <div class="text-2xl font-bold text-green-600 dark:text-green-400" id="tax-amount-display">0.00</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">Tax Amount</div>
                                    </div>
                                    <div>
                                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400" id="grand-total-display">0.00</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">Grand Total</div>
                                    </div>
                                </div>
                                <input type="hidden" name="total_amount" id="total_amount" value="0">
                                <input type="hidden" name="tax_amount" id="tax_amount" value="0">
                                <input type="hidden" name="grand_total" id="grand_total" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-300">
                        <div class="bg-gradient-to-r from-gray-500 to-gray-600 p-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-white">Notes & Terms</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="mb-4">
                                <label for="notes" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                    Notes
                                </label>
                                <textarea name="notes" id="notes" rows="4" maxlength="1000"
                                    class="w-full text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 resize-none"
                                    placeholder="Add any additional notes about this regular bill..."></textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Provide any relevant details about delivery, challan references, or payment terms.
                                </p>
                            </div>

                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border border-green-200 dark:border-green-700">
                                <h4 class="text-sm font-bold text-green-800 dark:text-green-300 mb-2">Regular Bill Terms</h4>
                                <ul class="text-xs text-green-700 dark:text-green-400 space-y-1">
                                    <li>• This bill is for goods that have been delivered via challans</li>
                                    <li>• Payment is due based on the selected due date</li>
                                    <li>• Selected challan products will be marked as billed</li>
                                    <li>• Tax calculations are based on individual product tax rates</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Challan checkbox functionality
            document.querySelectorAll('.challan-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const challanId = this.dataset.challanId;
                    const products = this.closest('.bg-gray-50').querySelectorAll('.product-checkbox');
                    const quantities = this.closest('.bg-gray-50').querySelectorAll('.product-quantity');
                    const prices = this.closest('.bg-gray-50').querySelectorAll('.product-price');
                    const taxes = this.closest('.bg-gray-50').querySelectorAll('.product-tax');
                    
                    products.forEach(product => {
                        product.checked = this.checked;
                    });
                    
                    quantities.forEach(quantity => {
                        quantity.disabled = !this.checked;
                        if (this.checked && !quantity.value) {
                            quantity.value = quantity.getAttribute('max');
                        }
                    });
                    
                    prices.forEach(price => {
                        price.disabled = !this.checked;
                    });
                    
                    taxes.forEach(tax => {
                        tax.disabled = !this.checked;
                    });
                    
                    updateSelectedCount();
                    calculateTotals();
                });
            });

            // Product checkbox functionality
            document.querySelectorAll('.product-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const productRow = this.closest('.flex.items-center.justify-between');
                    const quantity = productRow.querySelector('.product-quantity');
                    const price = productRow.querySelector('.product-price');
                    const tax = productRow.querySelector('.product-tax');
                    
                    quantity.disabled = !this.checked;
                    price.disabled = !this.checked;
                    tax.disabled = !this.checked;
                    
                    if (this.checked && !quantity.value) {
                        quantity.value = quantity.getAttribute('max');
                    }
                    
                    if (!this.checked) {
                        quantity.value = '';
                        price.value = '';
                        tax.value = '';
                    }
                    
                    updateSelectedCount();
                    calculateTotals();
                });
            });

            // Quantity, price, tax change events
            document.querySelectorAll('.product-quantity, .product-price, .product-tax').forEach(input => {
                input.addEventListener('input', calculateTotals);
            });

            // Update selected challans count
            function updateSelectedCount() {
                const selectedProducts = document.querySelectorAll('.product-checkbox:checked').length;
                document.getElementById('selected-challans-count').textContent = `Selected: ${selectedProducts}`;
            }

            // Calculate totals
            function calculateTotals() {
                let totalAmount = 0;
                let totalTax = 0;

                document.querySelectorAll('.product-checkbox:checked').forEach(checkbox => {
                    const productRow = checkbox.closest('.flex.items-center.justify-between');
                    const quantity = parseFloat(productRow.querySelector('.product-quantity').value) || 0;
                    const price = parseFloat(productRow.querySelector('.product-price').value) || 0;
                    const taxPercentage = parseFloat(productRow.querySelector('.product-tax').value) || 0;

                    const lineTotal = quantity * price;
                    const taxAmount = lineTotal * (taxPercentage / 100);

                    totalAmount += lineTotal;
                    totalTax += taxAmount;
                });

                const discount = parseFloat(document.getElementById('discount').value) || 0;
                const grandTotal = totalAmount + totalTax - discount;

                document.getElementById('total-amount-display').textContent = totalAmount.toFixed(2);
                document.getElementById('tax-amount-display').textContent = totalTax.toFixed(2);
                document.getElementById('grand-total-display').textContent = grandTotal.toFixed(2);

                document.getElementById('total_amount').value = totalAmount.toFixed(2);
                document.getElementById('tax_amount').value = totalTax.toFixed(2);
                document.getElementById('grand_total').value = grandTotal.toFixed(2);
            }

            // Set minimum due date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const minDate = tomorrow.toISOString().split('T')[0];
            document.getElementById('due_date').setAttribute('min', minDate);

            // Initial calculation
            calculateTotals();
        });
    </script>
    @endpush
</x-dashboard.layout.default>
