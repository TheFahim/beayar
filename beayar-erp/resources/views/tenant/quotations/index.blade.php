<x-dashboard.layout.default title="Quotations">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Quotations
            </a>
        </li>
    </x-dashboard.ui.bread-crumb>

    {{-- Main container with a single, unified Alpine.js scope --}}
    <div class="max-w-full" x-data="quotationsPageData('{{ route('tenant.quotations.index') }}', '{{ request('date_from') }}', '{{ request('date_to') }}')" x-init="initDateRangePicker()">
        <!-- Unified Header and Filter Section -->
        <x-ui.card>
            <!-- Header Row -->
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4 mb-6">
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-semibold text-gray-800 dark:text-gray-200">Quotations
                            </h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manage quotations with multiple
                                revisions</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button
                                class="inline-flex items-center px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200"
                                type="button" @click="filtersOpen = !filtersOpen">
                                <i class="fas fa-filter mr-2"></i>
                                <span x-text="filtersOpen ? 'Hide Filters' : 'Show Filters'"></span>
                            </button>
                            <a href="{{ route('tenant.quotations.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                                <i class="fas fa-plus mr-2"></i> New Quotation
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Collapsible Filter Section -->
            <div x-show="filtersOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="border-t border-gray-200 dark:border-gray-700 pt-6">

                <form method="GET" action="{{ route('tenant.quotations.index') }}" x-ref="filterForm"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">

                    <!-- Search Field -->
                    <div class="sm:col-span-2 lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                        <input type="text" name="search" x-ref="searchInput"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400"
                            placeholder="Quotation no, customer, company, requisition no..." value="{{ request('search') }}">
                    </div>

                    <!-- Status Filter -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <select name="status" x-ref="statusSelect"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                            @change="if ($event.target.value !== '') $refs.filterForm.submit()">
                            <option value="">All Status</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In
                                Progress</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                            </option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                Cancelled</option>
                        </select>
                    </div>

                    <!-- Quotation Type Filter -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quotation
                            Type</label>
                        <select name="type" x-ref="typeSelect"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                            @change="if ($event.target.value !== '') $refs.filterForm.submit()">
                            <option value="">All Types</option>
                            <option value="normal" {{ request('type') == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="via" {{ request('type') == 'via' ? 'selected' : '' }}>Via</option>
                        </select>
                    </div>

                    <!-- Saved As Filter -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Saved As</label>
                        <select name="saved_as" x-ref="savedAsSelect"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                            @change="if ($event.target.value !== '') $refs.filterForm.submit()">
                            <option value="">All Saved As</option>
                            <option value="draft" {{ request('saved_as') == 'draft' ? 'selected' : '' }}>Draft
                            </option>
                            <option value="quotation" {{ request('saved_as') == 'quotation' ? 'selected' : '' }}>
                                Quotation</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date
                            Range</label>
                        <div id="quotation-date-range-picker" class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>
                                <input id="quotation-datepicker-range-start" name="date_from" type="text"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Start date" value="{{ request('date_from') }}">
                            </div>
                            <span class="text-gray-500 text-sm">to</span>
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                    </svg>
                                </div>
                                <input id="quotation-datepicker-range-end" name="date_to" type="text"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="End date" value="{{ request('date_to') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="sm:col-span-2 lg:col-span-2 lg:col-start-11 flex items-end gap-2 justify-end">
                        <button type="submit"
                            class="flex-1 inline-flex items-center justify-center px-1 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">

                            <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="#ffffff" stroke-linecap="round" stroke-width="2"
                                    d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                            </svg>

                        </button>
                        <button type="button" @click="resetFilters()"
                            class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200"
                            title="Reset Filters">
                            <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="#ffffff" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M3 9h13a5 5 0 0 1 0 10H7M3 9l4-4M3 9l4 4" />
                            </svg>

                        </button>
                    </div>
                </form>
            </div>
        </x-ui.card>

        <!-- Quotations Table -->
        <x-ui.card>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h6 class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                        Quotations List
                        @if ($quotations->total() > 0)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 ml-2">{{ $quotations->total() }}
                                Total</span>
                        @endif
                    </h6>
                </div>
                <div class="p-6">

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        SL
                                    </th>

                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Quotation No</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Customer</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Products</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Date / By</th>

                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total</th>
                                    {{-- <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Remarks</th> --}}

                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Challan</th>
                                    <th
                                        class="w-36 px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($quotations as $index => $quotation)
                                    <!-- Main Quotation Row -->
                                    <tr class="quotation-row hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 hover:shadow-lg hover:scale-[1.01]"
                                        data-quotation-id="{{ $quotation->id }}">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $index + 1 }}
                                        </td>

                                        <td class="flex gap-2 items-center py-4 whitespace-nowrap">
                                            @if ($quotation->revisions[0]->saved_as == 'draft')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200">
                                                    Draft
                                                </span>
                                            @endif

                                            <a href="{{ route('tenant.quotations.show', $quotation) }}"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold transition-colors duration-200">
                                                <div>
                                                    {{ $quotation->quotation_no }}
                                                </div>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $quotation->customer->name }}
                                            </div>
                                            @if ($quotation->customer->customerCompany)
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $quotation->customer->customerCompany->name }}
                                                </div>
                                            @endif
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $quotation->customer->customer_no }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col gap-1 max-w-xs">
                                                @php
                                                    $products = collect();
                                                    if(isset($quotation->revisions[0]) && $quotation->revisions[0]->products) {
                                                        foreach($quotation->revisions[0]->products as $qp) {
                                                            if($qp->product) {
                                                                $products->push($qp->product->name);
                                                            }
                                                        }
                                                    }
                                                    // Keep only first 2 unique consecutive products
                                                    $shown = collect();
                                                    $prev = null;
                                                    foreach($products as $name) {
                                                        if($shown->count() >= 2) break;
                                                        if($name !== $prev) {
                                                            $shown->push($name);
                                                            $prev = $name;
                                                        }
                                                    }
                                                @endphp
                                                @if($shown->isNotEmpty())
                                                    @foreach($shown as $name)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 truncate" title="{{ $name }}">
                                                            {{ $name }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="text-xs text-gray-500 italic">No products</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ $quotation->revisions[0]->date->format('d M Y') ?? '' }}
                                            </div>
                                            {{-- @if($quotation->revisions[0]->createdBy)
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $quotation->revisions[0]->createdBy->name }}
                                                </div>
                                            @endif --}}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{$quotation->type !== 'normal' ?  $quotation->revisions[0]->currency : '' }}
                                                {{ number_format($quotation->revisions[0]->total, 2) }}
                                            </div>
                                            @if ($quotation->type !== 'normal')
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    BDT: {{ number_format($quotation->revisions[0]->total * $quotation->revisions[0]->exchange_rate, 2) }}
                                                </div>
                                            @endif

                                        </td>
                                        {{-- status --}}
                                        {{-- <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex text-sm font-semibold text-gray-900 dark:text-gray-100">

                                                {{ $quotation->revisions[0]->revision_no }}
                                                @php
                                                    $statusColors = [
                                                        'in_progress' =>
                                                            'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200',
                                                        'active' =>
                                                            'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200',
                                                        'completed' =>
                                                            'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200',
                                                        'cancelled' =>
                                                            'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200',
                                                    ];
                                                    $colors =
                                                        $statusColors[$quotation->status] ??
                                                        'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';
                                                @endphp
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors }} status-badge">
                                                    {{ ucfirst(str_replace('_', ' ', $quotation->status)) }}
                                                </span>
                                            </div>
                                            <div
                                                class="inline-flex items-center w-6 h-6 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:scale-110 transition-all duration-200 expand-btn">
                                                <span
                                                    class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200">{{ $quotation->revisions_count }}
                                                    revision(s)</span>
                                            </div>
                                        </td> --}}


                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($quotation->challan_fulfilled)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                                                    <i class="fas fa-check mr-1"></i> Complete
                                                </span>
                                            @elseif ($quotation->has_challan)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200">Incomplete</span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">No</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="relative inline-block text-left" x-data="{
                                                open: false,
                                                menuWidth: 224,
                                                margin: 8,
                                                top: 0,
                                                left: 0,
                                                menuHeight: 0,
                                                measureMenu() {
                                                    const el = this.$refs.menu;
                                                    if (el) this.menuHeight = el.offsetHeight;
                                                },
                                                computePositions() {
                                                    const rect = this.$refs.btn.getBoundingClientRect();
                                                    const vw = window.innerWidth,
                                                        vh = window.innerHeight;
                                                    let left = rect.right - this.menuWidth;
                                                    left = Math.min(left, vw - this.menuWidth - this.margin);
                                                    left = Math.max(left, this.margin);
                                                    let top = rect.bottom + this.margin;
                                                    const overflowBottom = top + this.menuHeight > vh - this.margin;
                                                    if (overflowBottom) {
                                                        top = Math.max(rect.top - this.menuHeight - this.margin, this.margin);
                                                    }
                                                    this.left = left;
                                                    this.top = top;
                                                },
                                                updatePosition() {
                                                    this.measureMenu();
                                                    this.computePositions();
                                                }
                                            }"
                                                @keydown.escape.window="open = false"
                                                @resize.window="if (open) updatePosition()"
                                                @scroll.window="if (open) updatePosition()">
                                                <button type="button" x-ref="btn"
                                                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                                                    @click="open = !open; if (open) $nextTick(() => updatePosition())">
                                                    Actions
                                                    <i class="fas fa-chevron-down ml-2 -mr-1 h-4 w-4 transition-transform duration-200"
                                                        :class="{ 'rotate-180': open }"></i>
                                                </button>
                                                <template x-teleport="body">
                                                    <div x-show="open" @click.away="open = false" x-ref="menu"
                                                        x-transition:enter="transition ease-out duration-100"
                                                        x-transition:enter-start="transform opacity-0 scale-95"
                                                        x-transition:enter-end="transform opacity-100 scale-100"
                                                        x-transition:leave="transition ease-in duration-75"
                                                        x-transition:leave-start="transform opacity-100 scale-100"
                                                        x-transition:leave-end="transform opacity-0 scale-95"
                                                        class="fixed z-50 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 border border-gray-200 dark:border-gray-700"
                                                        :style="`top: ${top}px; left: ${left}px; width: ${menuWidth}px; z-index: 9999; max-height: calc(100vh - ${margin * 2}px); overflow-y: auto;`">
                                                        <div class="py-1">
                                                            <a class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                                                                href="{{ route('tenant.quotations.show', $quotation) }}">
                                                                View
                                                            </a>
                                                            <a class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                                                                href="{{ route('tenant.quotations.edit', $quotation) }}">
                                                                Edit Info
                                                            </a>

                                                            <hr class="border-gray-200 dark:border-gray-600">

                                                            @if (($quotation->revisions[0]->saved_as === 'quotation' && !$quotation->has_challan) && $quotation->type === 'normal')
                                                                <a class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                                                                    href="{{ route('tenant.challans.create', ['quotation_id' => $quotation->id]) }}">
                                                                    Create Challan
                                                                </a>
                                                            @elseif (($quotation->revisions[0]->saved_as === 'quotation' && !$quotation->challan_fulfilled) && $quotation->type === 'normal')
                                                                <a class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                                                                    href="{{ route('tenant.challans.create', ['quotation_id' => $quotation->id]) }}">
                                                                    Continue Challan
                                                                </a>
                                                            @endif
                                                            @if (!$quotation->revisions[0]->challan()->exists())
                                                                <hr class="border-gray-200 dark:border-gray-600">
                                                                <form
                                                                    action="{{ route('tenant.quotations.destroy', $quotation) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="w-full text-left block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200 delete-button">
                                                                        Delete
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            {{-- add create bill button --}}
                                                            @php
                                                                $isActiveRevision =
                                                                    $quotation->revisions[0]->saved_as === 'quotation';
                                                                $hasChallan = (bool) $quotation->has_challan;
                                                                $hasAdvance = (bool) $quotation->hasAdvanceBill;

                                                                $canContinueRegular = function ($q) {
                                                                    return $q->latestBillType === 'regular' &&
                                                                        $q->continueBill &&
                                                                        !$q->hasAdvanceBill;
                                                                };
                                                                $canCreateRegular = function ($q) {
                                                                    return $q->has_challan && !$q->hasAdvanceBill;
                                                                };
                                                                $canCreateAdvance = function ($q) {
                                                                    return !$q->has_challan && !$q->hasAdvanceBill;
                                                                };
                                                                $canCreateRunning = function ($q) {
                                                                    return !$q->has_challan && $q->hasAdvanceBill;
                                                                };
                                                                $advanceId =
                                                                    $quotation->latestBillType === 'advance'
                                                                        ? $quotation->latestBillId
                                                                        : $quotation->parentBillId;
                                                            @endphp

                                                            @if ($quotation->canCreateBill && $isActiveRevision)
                                                                <hr class="border-gray-200 dark:border-gray-600">

                                                                @if ($hasChallan)
                                                                    @if ($hasAdvance)
                                                                        <a href="{{ route('tenant.quotations.bill', ['quotation' => $quotation->id, 'parent_bill_id' => $advanceId]) }}"
                                                                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                                                                            Create Running Bill
                                                                        </a>
                                                                        <div class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400"
                                                                            title="Regular billing disabled due to existing advance bill.">
                                                                            Regular billing disabled (advance exists)
                                                                        </div>
                                                                    @elseif ($canContinueRegular($quotation))
                                                                        <a href="{{ route('tenant.quotations.bill', ['quotation' => $quotation->id]) }}"
                                                                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                                                                            Continue Regular Bill
                                                                        </a>
                                                                    @elseif ($canCreateRegular($quotation))
                                                                        <a href="{{ route('tenant.quotations.bill', ['quotation' => $quotation->id]) }}"
                                                                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                                                                            Create Regular Bill
                                                                        </a>
                                                                    @endif
                                                                @else
                                                                    @if ($canCreateRunning($quotation))
                                                                        <a href="{{ route('tenant.quotations.bill', ['quotation' => $quotation->id, 'parent_bill_id' => $advanceId]) }}"
                                                                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                                                                            Create Running Bill
                                                                        </a>
                                                                        <div class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400"
                                                                            title="Regular billing disabled due to existing advance bill. View or edit the advance bill from the bills page.">
                                                                            Regular billing disabled (advance exists).
                                                                            You can view/edit the advance bill.
                                                                        </div>
                                                                    @elseif ($canCreateAdvance($quotation))
                                                                        <a href="{{ route('tenant.quotations.bill', ['quotation' => $quotation->id]) }}"
                                                                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                                                                            Create Advance Bill
                                                                        </a>
                                                                    @endif
                                                                @endif
                                                            @elseif ($quotation->revisions[0]->saved_as === 'draft')
                                                                <hr class="border-gray-200 dark:border-gray-600">
                                                                <div class="px-4 py-2">
                                                                    <span
                                                                        class="block text-sm text-gray-500 dark:text-gray-400">Billing
                                                                        actions are disabled for draft quotations.
                                                                        Activate the revision to proceed.</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <i
                                                    class="fas fa-folder-open text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                                    No quotations found</h3>
                                                <p class="text-gray-500 dark:text-gray-400 mb-4">Get started by
                                                    creating your first quotation.</p>
                                                <a href="{{ route('tenant.quotations.create') }}"
                                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                                    <i class="fas fa-plus mr-2"></i>
                                                    Create Quotation
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($quotations->hasPages())
                        <div
                            class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    @if ($quotations->onFirstPage())
                                        <span
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-700 cursor-not-allowed">
                                            Previous
                                        </span>
                                    @else
                                        <a href="{{ $quotations->previousPageUrl() }}"
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200">
                                            Previous
                                        </a>
                                    @endif

                                    @if ($quotations->hasMorePages())
                                        <a href="{{ $quotations->nextPageUrl() }}"
                                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200">
                                            Next
                                        </a>
                                    @else
                                        <span
                                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-700 cursor-not-allowed">
                                            Next
                                        </span>
                                    @endif
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            Showing
                                            <span class="font-medium">{{ $quotations->firstItem() ?? 0 }}</span>
                                            to
                                            <span class="font-medium">{{ $quotations->lastItem() ?? 0 }}</span>
                                            of
                                            <span class="font-medium">{{ $quotations->total() }}</span>
                                            results
                                        </p>
                                    </div>
                                    <div>
                                        {{ $quotations->withQueryString()->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </x-ui.card>
    </div>


</x-dashboard.layout.default>
