<x-dashboard.layout.default title="Dashboard">


    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.home class="h-3 w-3" />
                Dashboard
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Home" />
    </x-dashboard.ui.bread-crumb>

    <!-- Quotation Statistics Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Quotations This Month -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Quotations This Month</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($quotationStats['current_month']['count'], 0, '.', ',') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format($quotationStats['current_month']['value'], 2) }} &#2547;
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Quotations All Time -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Quotations</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($quotationStats['overall']['count'], 0, '.', ',') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format($quotationStats['overall']['value'], 2) }} &#2547;
                    </p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Average Quotation Value -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg. Quotation Value</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        {{ $quotationStats['overall']['count'] > 0 ? number_format($quotationStats['overall']['value'] / $quotationStats['overall']['count'], 0, '.', ',') : 0 }} &#2547;
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">All time average</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Quotation Conversion Rate -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Conversion Rate</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($conversionRateStats['overall']['conversion_rate'], 2) }}%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $conversionRateStats['overall']['bill_count'] }} bills / {{ $conversionRateStats['overall']['quotation_count'] }} quotations
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Challan Tracking Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Challans This Month -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Challans This Month</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($challanStats['current_month']['count'], 0, '.', ',') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format($challanStats['current_month']['quotation_count'], 0, '.', ',') }} quotations
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Challans -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Challans</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($challanStats['overall']['count'], 0, '.', ',') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format($challanStats['overall']['quotation_count'], 0, '.', ',') }} quotations
                    </p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Delivered Challans -->
        {{-- <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Delivered</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($challanStats['delivered'], 0, '.', ',') }}</p>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400">Ready for billing</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
        </div> --}}

        <!-- Pending Challans -->
        {{-- <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($challanStats['pending'], 0, '.', ',') }}</p>
                    <p class="text-xs text-amber-600 dark:text-amber-400">Awaiting delivery</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div> --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Bills This Month</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($billStats['current_month']['count'], 0, '.', ',') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format($billStats['current_month']['total_amount'], 2) }} &#2547;
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Bill Types</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                        <span class="text-blue-600 dark:text-blue-400">{{ $billStats['by_type']['advance'] }}</span> |
                        <span class="text-green-600 dark:text-green-400">{{ $billStats['by_type']['regular'] }}</span> |
                        <span class="text-purple-600 dark:text-purple-400">{{ $billStats['by_type']['running'] }}</span>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Adv | Reg | Run</p>
                </div>
                <div class="w-12 h-12 bg-slate-100 dark:bg-slate-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Bill Management Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Bills This Month -->

        <!-- Total Outstanding -->
        {{-- <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Outstanding</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($billStats['overall']['total_due'], 0, '.', ',') }} &#2547;</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ number_format($billStats['overall']['count'], 0, '.', ',') }} bills
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
        </div> --}}

        <!-- Overdue Bills -->
        {{-- <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Overdue Bills</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($billStats['overdue']['amount'], 0, '.', ',') }} &#2547;</p>
                    <p class="text-xs text-orange-600 dark:text-orange-400">
                        {{ number_format($billStats['overdue']['count'], 0, '.', ',') }} bills (30+ days)
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div> --}}

        <!-- Bill Types Summary -->
    </div>

    <!-- Financial Summary Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Bill Amount</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($financialSummary['total_amount_unique_by_quotation'] ?? 0, 2) }} &#2547;
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Billed</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ number_format($financialSummary['total_paid'] ?? 0, 2) }} &#2547;
                    </p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Bill Due</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                        {{ number_format($financialSummary['total_due_unique_by_quotation'] ?? 0, 2) }} &#2547;
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Only: User Quotation Statistics Section -->
    @role('admin')
    <div class="grid grid-cols-1 gap-4 mb-6">
        <div x-data="userQuotationStats" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <!-- Header with Title and Filter Dropdown -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-4">
                <div>
                    <h5 class="leading-none text-xl font-bold text-gray-900 dark:text-white pb-1">User Quotation Statistics</h5>
                    <p class="text-sm font-normal text-gray-500 dark:text-gray-400">Quotation counts per user</p>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Stats Summary -->
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <span x-text="totalCount"></span> quotations
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            ৳<span x-text="totalValue.toLocaleString()"></span>
                        </span>
                    </div>
                    <!-- Filter Dropdown -->
                    <div class="relative">
                        <button @click="isDropdownOpen = !isDropdownOpen"
                            class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 text-center inline-flex items-center dark:hover:text-white bg-gray-100 dark:bg-gray-700 rounded-lg px-3 py-2"
                            type="button">
                            <span x-text="selectedFilter.label"></span>
                            <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg>
                        </button>
                        <!-- Dropdown menu -->
                        <div x-show="isDropdownOpen" @click.away="isDropdownOpen = false" x-transition
                            class="z-10 absolute right-0 mt-2 bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-40 dark:bg-gray-700"
                            style="display: none;">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                                <template x-for="filter in filters" :key="filter.value">
                                    <li>
                                        <a href="#" @click.prevent="selectFilter(filter)"
                                            class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                                            x-text="filter.label"></a>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="isLoading" class="flex items-center justify-center py-16">
                <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-gray-500 dark:text-gray-400">Loading...</span>
            </div>

            <!-- Empty State -->
            <div x-show="!isLoading && users.length === 0" class="text-center py-16 text-gray-500 dark:text-gray-400">
                No quotation data available for this period.
            </div>

            <!-- Chart Container -->
            <div x-show="!isLoading && users.length > 0">
                <div x-ref="userQuotationChart"></div>
            </div>

            <!-- User Stats Table -->
            <div x-show="!isLoading && users.length > 0" class="mt-6 overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">User</th>
                            <th scope="col" class="px-4 py-3 text-right">Quotations</th>
                            <th scope="col" class="px-4 py-3 text-right">Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="user in users" :key="user.user_id">
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white" x-text="user.user_name"></td>
                                <td class="px-4 py-3 text-right" x-text="user.quotation_count"></td>
                                <td class="px-4 py-3 text-right" x-text="'৳' + user.total_value.toLocaleString()"></td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                        <tr class="font-semibold text-gray-900 dark:text-white">
                            <td class="px-4 py-3">Total</td>
                            <td class="px-4 py-3 text-right" x-text="totalCount"></td>
                            <td class="px-4 py-3 text-right" x-text="'৳' + totalValue.toLocaleString()"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endrole

    <!-- Historical Trends Section -->
    {{-- <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <!-- Quotation Trends Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h5 class="leading-none text-xl font-bold text-gray-900 dark:text-white pb-1">Quotation Trends</h5>
                    <p class="text-sm font-normal text-gray-500 dark:text-gray-400">Monthly quotation activity</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        {{ $quotationStats['current_month']['count'] }} this month
                    </span>
                </div>
            </div>
            <div id="quotation-trends-chart" class="h-64"></div>
        </div>

        <!-- Bill Trends Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h5 class="leading-none text-xl font-bold text-gray-900 dark:text-white pb-1">Bill Trends</h5>
                    <p class="text-sm font-normal text-gray-500 dark:text-gray-400">Monthly billing activity</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        {{ $billStats['current_month']['count'] }} this month
                    </span>
                </div>
            </div>
            <div id="bill-trends-chart" class="h-64"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <x-ui.card heading="Due Summary">

            <div class="max-w-sm w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6">
                <div class="flex justify-between border-gray-200 border-b dark:border-gray-700 pb-3">
                    <dl>
                        <dt class="text-base font-normal text-gray-500 dark:text-gray-400 pb-1">Due</dt>
                        <dd class="leading-none text-3xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($bill[0]->total_due, 0, '.', ',') }} &#2547;
                    </dl>

                </div>

                <div class="grid grid-cols-2 py-3 my-5">
                    <dl>
                        <dt class="text-base font-normal text-gray-500 dark:text-gray-400 pb-1">Bill</dt>
                        <dd class="leading-none text-xl font-bold text-green-500 dark:text-green-400">
                            {{ number_format($bill[0]->total_bill, 0, '.', ',') }} &#2547;</dd>
                    </dl>
                    <dl>
                        <dt class="text-base font-normal text-gray-500 dark:text-gray-400 pb-1">Received</dt>
                        <dd class="leading-none text-xl font-bold text-red-600 dark:text-red-500">
                            {{ number_format($bill[0]->total_paid, 0, '.', ',') }} &#2547;</dd>
                    </dl>
                </div>


                <div class="w-full h-6 my-5 bg-gray-200 rounded-full dark:bg-gray-700">
                    <div class="h-6 bg-blue-600 text-center text-blue-100 text-md rounded-full dark:bg-blue-500"
                        style="width: {{ number_format($bill[0]->paid_percentage, 0, '.', ',') }}%">
                        {{ number_format($bill[0]->paid_percentage, 1, '.', ',') }}%</div>
                </div>

                <div class="my-5"></div>

                <div
                    class="grid grid-cols-1 items-center border-gray-200 border-t dark:border-gray-700 justify-between">
                    <div class="flex justify-between items-center pt-5">
                        <!-- Button -->
                        <div></div>
                        <!-- Dropdown menu -->
                    </div>

                </div>
            </div>
            <div class="grid grid-cols-2 px-4">
                <div></div>
                <a href="{{ route('received-bills.index') }}"
                    class="uppercase text-sm font-semibold inline-flex items-center rounded-lg text-blue-600 hover:text-blue-700 dark:hover:text-blue-500  hover:bg-gray-100 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700 px-3 py-2">
                    View payments
                    <svg class="w-2.5 h-2.5 ms-1.5 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 9 4-4-4-4" />
                    </svg>
                </a>
            </div>
        </x-ui.card>

        <div x-data="myQuotationChart"
            class="bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6">
            <!-- Header: Title and Year Selection Dropdown -->
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h5 class="leading-none text-xl font-bold text-gray-900 dark:text-white pb-1">My Yearly Performance
                    </h5>
                    <p class="text-sm font-normal text-gray-500 dark:text-gray-400">Monthly quotation summary</p>
                </div>
                <div x-show="years.length > 0" class="relative">
                    <button @click="isDropdownOpen = !isDropdownOpen"
                        class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 text-center inline-flex items-center dark:hover:text-white"
                        type="button">
                        Year: <span x-text="selectedYear" class="font-semibold ml-1"></span>
                        <svg class="w-2.5 m-2.5 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="m1 1 4 4 4-4" />
                        </svg>
                    </button>
                    <!-- Dropdown menu -->
                    <div x-show="isDropdownOpen" @click.away="isDropdownOpen = false" x-transition
                        class="z-10 absolute right-0 mt-2 bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-32 dark:bg-gray-700"
                        style="display: none;">
                        <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                            <template x-for="year in years" :key="year">
                                <li>
                                    <a href="#" @click.prevent="selectYear(year)"
                                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white"
                                        x-text="year"></a>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="isLoading" class="text-center py-16 text-gray-500 dark:text-gray-400">Loading Chart...</div>

            <!-- Message for no data -->
            <div x-show="!isLoading && years.length === 0" class="text-center py-16 text-gray-500 dark:text-gray-400">
                You have not created any quotations yet.
            </div>

            <!-- Chart Container -->
            <div x-show="!isLoading && years.length > 0">
                <div id="my-quotation-chart" x-ref="myQuotationChart"></div>
            </div>
        </div>

    </div> --}}

    <!-- ApexCharts Integration for Historical Trends -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Historical quotation trends chart
            const quotationChartEl = document.getElementById('quotation-trends-chart');
            if (quotationChartEl && typeof ApexCharts !== 'undefined') {
                const quotationData = @json($historicalTrends['quotations'] ?? []);

                const quotationChart = new ApexCharts(quotationChartEl, {
                    chart: {
                        type: 'area',
                        height: 256,
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        }
                    },
                    series: [{
                        name: 'Quotations',
                        data: quotationData.map(item => ({
                            x: item.month,
                            y: parseInt(item.count)
                        }))
                    }],
                    xaxis: {
                        type: 'category',
                        labels: {
                            style: {
                                colors: '#6B7280',
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#6B7280',
                                fontSize: '12px'
                            }
                        }
                    },
                    colors: ['#3B82F6'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.7,
                            opacityTo: 0.3,
                            stops: [0, 90, 100]
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    dataLabels: {
                        enabled: false
                    },
                    grid: {
                        borderColor: '#E5E7EB',
                        strokeDashArray: 4
                    },
                    tooltip: {
                        theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                    }
                });

                quotationChart.render();

                // Update chart theme when dark mode changes
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'class') {
                            quotationChart.updateOptions({
                                tooltip: {
                                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                                }
                            });
                        }
                    });
                });

                observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }

            // Historical bill trends chart
            const billChartEl = document.getElementById('bill-trends-chart');
            if (billChartEl && typeof ApexCharts !== 'undefined') {
                const billData = @json($historicalTrends['bills'] ?? []);

                const billChart = new ApexCharts(billChartEl, {
                    chart: {
                        type: 'area',
                        height: 256,
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        }
                    },
                    series: [{
                        name: 'Bills',
                        data: billData.map(item => ({
                            x: item.month,
                            y: parseInt(item.count)
                        }))
                    }],
                    xaxis: {
                        type: 'category',
                        labels: {
                            style: {
                                colors: '#6B7280',
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#6B7280',
                                fontSize: '12px'
                            }
                        }
                    },
                    colors: ['#10B981'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.7,
                            opacityTo: 0.3,
                            stops: [0, 90, 100]
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    dataLabels: {
                        enabled: false
                    },
                    grid: {
                        borderColor: '#E5E7EB',
                        strokeDashArray: 4
                    },
                    tooltip: {
                        theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                    }
                });

                billChart.render();

                // Update chart theme when dark mode changes
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'class') {
                            billChart.updateOptions({
                                tooltip: {
                                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                                }
                            });
                        }
                    });
                });

                observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }
        });
    </script>

</x-dashboard.layout.default>
