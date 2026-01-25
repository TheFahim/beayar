<x-dashboard.layout.default title="Create Running Bill">
    <!-- Breadcrumb -->
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard.quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition duration-150">
                <x-ui.svg.qutation class="w-3 h-3 me-2" />
                Quotations
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Create Running Bill" />
    </x-dashboard.ui.bread-crumb>

    <!-- Compact Sticky Action Bar -->
    <div class="top-4 z-20 mb-4 animate-fade-in-up">
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gradient-to-r from-white to-gray-50 dark:from-gray-800 dark:to-gray-800/95 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 p-4 backdrop-blur-sm">
            <div class="flex-1 min-w-0">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-0.5">Create Running Bill</h1>
                <p class="text-xs text-gray-600 dark:text-gray-400">Installment payment for advance bill</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <a href="{{ route('dashboard.quotations.index') }}"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 font-medium rounded-lg shadow-sm hover:shadow transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span class="text-sm">Back to Quotations</span>
                </a>
                <button type="submit" form="runningBillForm"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-medium rounded-lg shadow hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm font-semibold">Create Running Bill</span>
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

    <form id="runningBillForm" class="space-y-6" action="{{ route('bills.store-from-quotation', $quotation) }}" method="POST">
        @csrf
        <input type="hidden" name="bill_type" value="running">
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

                            <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-900/30 rounded-lg p-3 border border-purple-200 dark:border-purple-700">
                                <span class="block text-xs font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wide mb-1">Bill Type</span>
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-md bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                    Running Bill
                                </span>
                                <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">Installment payment for advance</p>
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
                                <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Advance Bills</span>
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-200 dark:border-blue-700">
                                    <span class="block text-lg font-bold text-blue-800 dark:text-blue-300">
                                        {{ $advanceBills->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="lg:col-span-8 space-y-4">
                    <!-- Parent Bill Selection -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-300">
                        <div class="bg-gradient-to-r from-purple-500 to-indigo-500 p-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-white">Select Parent Advance Bill</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            @if($advanceBills && $advanceBills->count() > 0)
                                <div class="space-y-3">
                                    @foreach($advanceBills as $advanceBill)
                                        <label class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer transition-colors">
                                            <input type="radio" name="parent_bill_id" value="{{ $advanceBill->id }}"
                                                class="parent-bill-radio rounded border-gray-300 text-purple-600 focus:ring-purple-500 mr-3"
                                                data-bill-total="{{ $advanceBill->total_amount }}"
                                                data-bill-invoice="{{ $advanceBill->invoice_no }}">
                                            <div class="flex-1">
                                                <div class="flex justify-between items-start mb-2">
                                                    <div>
                                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $advanceBill->invoice_no }}</h4>
                                                        <p class="text-xs text-gray-600 dark:text-gray-400">Created: {{ $advanceBill->bill_date }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-sm font-bold text-purple-600 dark:text-purple-400">BDT {{ number_format($advanceBill->total_amount, 2) }}</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($advanceBill->status) }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex justify-between items-center text-xs text-gray-600 dark:text-gray-400">
                                                    <span>Due Date: {{ $advanceBill->due_date }}</span>
                                                    <span>Installments: {{ $advanceBill->installments->count() }}</span>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <p class="text-sm font-medium text-gray-600 mb-1">No advance bills available</p>
                                    <p class="text-xs text-gray-500">You need to create an advance bill first before creating a running bill</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Running Bill Details -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-300">
                        <div class="bg-gradient-to-r from-purple-500 to-indigo-500 p-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-white">Running Bill Details</h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label for="running_percentage" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                        Running Percentage <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" name="running_percentage" id="running_percentage"
                                            class="w-full pr-10 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                                            placeholder="e.g., 25" min="1" max="100" required disabled>
                                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm pointer-events-none">%</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Percentage of parent advance bill amount
                                    </p>
                                </div>
                                <div>
                                    <label for="calculated_running_amount" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                                        Calculated Running Amount
                                    </label>
                                    <div class="relative">
                                        <input type="number" name="calculated_running_amount" id="calculated_running_amount"
                                            class="w-full pr-12 text-sm bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 text-purple-800 dark:text-purple-300 font-semibold rounded-lg"
                                            readonly step="0.01">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <span class="text-sm text-purple-600 dark:text-purple-400">BDT</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Based on selected advance bill total
                                    </p>
                                </div>
                            </div>

                            <!-- Installments Section -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-200">Installments</h4>
                                    <button type="button" id="add-installment"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add Installment
                                    </button>
                                </div>

                                <div id="installments-container" class="space-y-3">
                                    <!-- Installments will be added here dynamically -->
                                </div>

                                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Total Installment Amount:</span>
                                        <span class="text-sm font-bold text-green-600 dark:text-green-400" id="total-installment-amount">0.00</span>
                                    </div>
                                    <input type="hidden" name="total_installment_amount" id="total_installment_amount" value="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Bill Information -->
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
                                <h3 class="text-base font-bold text-white">Basic Information</h3>
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
                                        placeholder="e.g., RUN-001" required>
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

                            <div class="grid md:grid-cols-2 gap-4">
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
                                    placeholder="Add any additional notes about this running bill..."></textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Provide any relevant details about the installment payment, parent bill reference, or payment schedule.
                                </p>
                            </div>

                            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 border border-purple-200 dark:border-purple-700">
                                <h4 class="text-sm font-bold text-purple-800 dark:text-purple-300 mb-2">Running Bill Terms</h4>
                                <ul class="text-xs text-purple-700 dark:text-purple-400 space-y-1">
                                    <li>• This bill represents an installment payment for the selected advance bill</li>
                                    <li>• Running percentage determines the portion of advance bill being paid</li>
                                    <li>• Installments help spread the payment over time</li>
                                    <li>• Total running payments cannot exceed the advance bill amount</li>
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
            let installmentCount = 0;
            let selectedBillTotal = 0;

            // Parent bill selection
            document.querySelectorAll('.parent-bill-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        selectedBillTotal = parseFloat(this.dataset.billTotal) || 0;
                        document.getElementById('running_percentage').disabled = false;
                        document.getElementById('running_percentage').value = '';
                        document.getElementById('calculated_running_amount').value = '0.00';
                        updateInstallmentAmounts();
                    }
                });
            });

            // Calculate running amount
            function calculateRunningAmount() {
                const percentage = parseFloat(document.getElementById('running_percentage').value) || 0;
                const runningAmount = (selectedBillTotal * percentage) / 100;
                document.getElementById('calculated_running_amount').value = runningAmount.toFixed(2);
                updateInstallmentAmounts();
            }

            // Add installment
            function addInstallment() {
                installmentCount++;
                const container = document.getElementById('installments-container');
                const installment = document.createElement('div');
                installment.className = 'bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 border border-gray-200 dark:border-gray-600';
                installment.innerHTML = `
                    <div class="flex justify-between items-center mb-3">
                        <h5 class="text-sm font-bold text-gray-700 dark:text-gray-200">Installment ${installmentCount}</h5>
                        <button type="button" class="remove-installment text-red-500 hover:text-red-700 text-sm" data-id="${installmentCount}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="grid md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">Amount *</label>
                            <div class="relative">
                                <input type="number" name="installments[${installmentCount}][amount]"
                                    class="installment-amount w-full pr-8 text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                    step="0.01" min="0.01" required>
                                <span class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 text-xs">BDT</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">Due Date *</label>
                            <input type="date" name="installments[${installmentCount}][due_date]"
                                class="installment-date w-full text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">Description</label>
                            <input type="text" name="installments[${installmentCount}][description]"
                                class="w-full text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                placeholder="Optional description">
                        </div>
                    </div>
                `;
                container.appendChild(installment);

                // Add event listeners
                installment.querySelector('.installment-amount').addEventListener('input', updateTotalInstallmentAmount);
                installment.querySelector('.remove-installment').addEventListener('click', function() {
                    installment.remove();
                    updateTotalInstallmentAmount();
                });
            }

            // Update total installment amount
            function updateTotalInstallmentAmount() {
                const amounts = document.querySelectorAll('.installment-amount');
                let total = 0;
                amounts.forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                document.getElementById('total-installment-amount').textContent = total.toFixed(2);
                document.getElementById('total_installment_amount').value = total.toFixed(2);
            }

            // Update installment amounts to match running amount
            function updateInstallmentAmounts() {
                const runningAmount = parseFloat(document.getElementById('calculated_running_amount').value) || 0;
                const installments = document.querySelectorAll('.installment-amount');
                if (installments.length > 0) {
                    const amountPerInstallment = runningAmount / installments.length;
                    installments.forEach(input => {
                        input.value = amountPerInstallment.toFixed(2);
                    });
                    updateTotalInstallmentAmount();
                }
            }

            // Event listeners
            document.getElementById('running_percentage').addEventListener('input', calculateRunningAmount);
            document.getElementById('add-installment').addEventListener('click', addInstallment);

            // Add first installment by default
            addInstallment();

            // Set minimum due date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const minDate = tomorrow.toISOString().split('T')[0];
            document.querySelectorAll('input[type="date"]').forEach(input => {
                input.setAttribute('min', minDate);
            });
        });
    </script>
    @endpush
</x-dashboard.layout.default>
