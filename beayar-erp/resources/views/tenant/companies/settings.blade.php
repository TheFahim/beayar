<x-dashboard.layout.default title="Company Settings">
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <!-- Modern Header -->
        <div
            class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg border-b border-gray-200/50 dark:border-gray-700/50 sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="p-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1
                                class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                                Company Settings</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure preferences for <span
                                    class="font-semibold text-gray-700 dark:text-gray-300">{{ $company->name }}</span>
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('tenant.user-companies.index') }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 shadow-sm hover:shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Workspaces
                    </a>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <div
                    class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 shadow-lg animate-pulse">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
            x-data="{ activeTab: localStorage.getItem('companySettingsActiveTab') || 'regional' }"
            x-init="$watch('activeTab', value => localStorage.setItem('companySettingsActiveTab', value))">
            <!-- Interactive Tabs Navigation -->
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex space-x-1 p-1" id="settings-tabs" role="tablist">
                        <button type="button" @click="activeTab = 'regional'"
                            :class="activeTab === 'regional' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700'"
                            class="settings-tab flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200"
                            role="tab" :aria-selected="activeTab === 'regional'" aria-controls="regional-panel">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129">
                                </path>
                            </svg>
                            Regional Preferences
                        </button>

                        <button type="button" @click="activeTab = 'quotation'"
                            :class="activeTab === 'quotation' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700'"
                            class="settings-tab flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200"
                            role="tab" :aria-selected="activeTab === 'quotation'" aria-controls="quotation-panel">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                            Quotation Format
                        </button>

                        <button type="button" @click="activeTab = 'vat'"
                            :class="activeTab === 'vat' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700'"
                            class="settings-tab flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200"
                            role="tab" :aria-selected="activeTab === 'vat'" aria-controls="vat-panel">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke="join=" round" stroke-width="2"
                                    d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z">
                                </path>
                            </svg>
                            VAT Settings
                        </button>

                        <button type="button" @click="activeTab = 'pdf'"
                            :class="activeTab === 'pdf' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700'"
                            class="settings-tab flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200"
                            role="tab" :aria-selected="activeTab === 'pdf'" aria-controls="pdf-panel">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            PDF Header
                        </button>

                        <button type="button" @click="activeTab = 'authorization'"
                            :class="activeTab === 'authorization' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700'"
                            class="settings-tab flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200"
                            role="tab" :aria-selected="activeTab === 'authorization'"
                            aria-controls="authorization-panel">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Authorization
                        </button>
                    </nav>
                </div>
            </div>

            <form action="{{ route('tenant.company-settings.update', $company->id) }}" method="POST"
                enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Tab Content Container -->
                <div id="tab-content-container">

                    <!-- Regional Preferences Section -->
                    <div id="regional-panel" class="tab-panel" role="tabpanel" aria-labelledby="regional-tab" x-cloak
                        x-show="activeTab === 'regional'">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700">
                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-4 rounded-t-2xl">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-white">Regional Preferences</h2>
                                        <p class="text-blue-100 text-sm mt-1">Set default date format and currency for
                                            your company</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6 space-y-6">
                                @php
                                    $defaultCurrencies = ['BDT', 'USD', 'EUR', 'INR', 'RMB'];
                                    $quotationCurrencies = old('quotation_currencies', $settings['quotation_currencies'] ?? $defaultCurrencies);
                                    if (!is_array($quotationCurrencies)) {
                                        $quotationCurrencies = $defaultCurrencies;
                                    }
                                    $quotationCurrencies = array_values(array_unique(array_filter($quotationCurrencies)));
                                    $selectedCurrency = old('currency', $settings['currency']);
                                    if ($selectedCurrency && !in_array($selectedCurrency, $quotationCurrencies, true)) {
                                        $quotationCurrencies[] = $selectedCurrency;
                                    }
                                    if (!in_array('BDT', $quotationCurrencies, true)) {
                                        $quotationCurrencies[] = 'BDT';
                                    }
                                @endphp

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <!-- Date Format -->
                                    <div class="group">
                                        <label for="date_format"
                                            class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            Date Format <span class="text-red-500 ml-1">*</span>
                                        </label>
                                        <select name="date_format" id="date_format"
                                            class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200 hover:border-gray-300">
                                            @foreach($dateFormats as $format => $label)
                                                <option value="{{ $format }}" {{ old('date_format', $settings['date_format']) === $format ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('date_format')
                                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <!-- Currency -->
                                    <div class="group">
                                        <label for="currency"
                                            class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            Currency <span class="text-red-500 ml-1">*</span>
                                        </label>
                                        <select name="currency" id="currency"
                                            class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200 hover:border-gray-300"
                                            onchange="updateCurrencySymbol(this)">
                                            @foreach($quotationCurrencies as $code)
                                                @php
                                                    $symbol = $currencies[$code] ?? '';
                                                    $name = $currencyNames[$code] ?? '';
                                                    $displayText = $code;
                                                    if ($symbol && $symbol !== $code) {
                                                        $displayText = "{$code} ({$symbol})";
                                                    } elseif ($name) {
                                                        $displayText = "{$code} ({$name})";
                                                    }
                                                @endphp
                                                <option value="{{ $code }}" data-symbol="{{ $symbol }}" {{ $selectedCurrency === $code ? 'selected' : '' }}>
                                                    {{ $displayText }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('currency')
                                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <!-- Exchange Rate Currency -->
                                    <div class="group" x-data="{
                                search: '',
                                open: false,
                                selected: '{{ old('exchange_rate_currency', $settings['exchange_rate_currency'] ?? 'BDT') }}',
                                allCurrencies: {{ json_encode($allCurrencies ?? $currencies) }},
                                currencyNames: {{ json_encode($currencyNames) }},
                                highlightedIndex: -1,

                                get selectedDisplay() {
                                    const symbol = this.allCurrencies[this.selected] || '';
                                    const name = this.currencyNames[this.selected] || '';
                                    if (symbol && symbol !== this.selected) {
                                        return `${this.selected} (${symbol})`;
                                    } else if (name) {
                                        return `${this.selected} (${name})`;
                                    }
                                    return this.selected;
                                },

                                get filteredCurrencies() {
                                    if (this.search === '') {
                                        return this.allCurrencies;
                                    }
                                    const query = this.search.toLowerCase();
                                    const result = {};
                                    for (const [code, symbol] of Object.entries(this.allCurrencies)) {
                                        const name = this.currencyNames[code] || '';
                                        if (code.toLowerCase().includes(query) ||
                                            (symbol && symbol.toLowerCase().includes(query)) ||
                                            (name && name.toLowerCase().includes(query))) {
                                            result[code] = symbol;
                                        }
                                    }
                                    return result;
                                },

                                get filteredCurrencyList() {
                                    return Object.entries(this.filteredCurrencies);
                                },

                                select(code) {
                                    this.selected = code;
                                    this.search = '';
                                    this.open = false;
                                    this.highlightedIndex = -1;
                                },

                                highlightPrevious() {
                                    const list = this.filteredCurrencyList;
                                    if (list.length === 0) return;
                                    this.highlightedIndex = this.highlightedIndex <= 0 ? list.length - 1 : this.highlightedIndex - 1;
                                    this.scrollToHighlighted();
                                },

                                highlightNext() {
                                    const list = this.filteredCurrencyList;
                                    if (list.length === 0) return;
                                    this.highlightedIndex = this.highlightedIndex >= list.length - 1 ? 0 : this.highlightedIndex + 1;
                                    this.scrollToHighlighted();
                                },

                                selectHighlighted() {
                                    const list = this.filteredCurrencyList;
                                    if (this.highlightedIndex >= 0 && this.highlightedIndex < list.length) {
                                        this.select(list[this.highlightedIndex][0]);
                                    }
                                },

                                scrollToHighlighted() {
                                    this.$nextTick(() => {
                                        const highlighted = this.$refs.dropdown?.querySelector('.highlighted');
                                        if (highlighted) {
                                            highlighted.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                                        }
                                    });
                                }
                            }">
                                        <label
                                            class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-orange-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                            </svg>
                                            Exchange Rate Currency <span class="text-red-500 ml-1">*</span>
                                        </label>

                                        <div class="relative" @click.away="open = false">
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                    </svg>
                                                </div>
                                                <input type="text" x-model="search"
                                                    @focus="open = true; highlightedIndex = -1"
                                                    @keydown.escape="open = false"
                                                    @keydown.arrow-down.prevent="highlightNext()"
                                                    @keydown.arrow-up.prevent="highlightPrevious()"
                                                    @keydown.enter.prevent="selectHighlighted()"
                                                    class="w-full pl-10 pr-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200 hover:border-gray-300"
                                                    :placeholder="selectedDisplay || 'Search currency (e.g. USD, Dollar)...'">
                                                <input type="hidden" name="exchange_rate_currency" :value="selected">
                                            </div>

                                            <div x-show="open && Object.keys(filteredCurrencies).length > 0"
                                                x-ref="dropdown"
                                                class="fixed z-50 w-full bg-white dark:bg-gray-800 shadow-2xl max-h-60 rounded-xl py-2 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-gray-100 dark:border-gray-700"
                                                style="display: none;"
                                                x-init="$watch('open', () => {
                                                    if (open) {
                                                        const inputRect = $el.previousElementSibling.getBoundingClientRect();
                                                        $el.style.top = (inputRect.top - 280) + 'px';
                                                        $el.style.left = inputRect.left + 'px';
                                                        $el.style.width = inputRect.width + 'px';
                                                    }
                                                })">
                                                <template x-for="(symbol, code, index) in filteredCurrencies"
                                                    :key="code">
                                                    <div class="cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-900 dark:text-white transition-colors"
                                                        :class="{ 'highlighted bg-blue-50 dark:bg-blue-900/20': index === highlightedIndex, 'bg-blue-100 dark:bg-blue-900/30': code === selected }"
                                                        @click="select(code)" @mouseenter="highlightedIndex = index">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center space-x-3">
                                                                <span class="block font-medium" x-text="code"></span>
                                                                <span x-show="currencyNames[code]"
                                                                    x-text="currencyNames[code]"
                                                                    class="text-sm text-gray-500 dark:text-gray-400"></span>
                                                                <span x-show="symbol && !currencyNames[code]"
                                                                    x-text="symbol"
                                                                    class="text-sm text-gray-500 dark:text-gray-400"></span>
                                                            </div>
                                                            <span x-show="code === selected"
                                                                class="absolute inset-y-0 right-0 flex items-center pr-4">
                                                                <svg class="h-5 w-5 text-blue-600" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                </svg>
                                                            </span>
                                                            <span
                                                                x-show="index === highlightedIndex && code !== selected"
                                                                class="absolute inset-y-0 right-0 flex items-center pr-4">
                                                                <svg class="h-5 w-5 text-blue-600" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                </svg>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                            <div x-show="open && search !== '' && Object.keys(filteredCurrencies).length === 0"
                                                class="fixed z-50 w-full bg-white dark:bg-gray-800 shadow-2xl rounded-xl py-4 px-4 text-sm text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-gray-700"
                                                style="display: none;"
                                                x-init="$watch('open', () => {
                                                    if (open && search !== '' && Object.keys(filteredCurrencies).length === 0) {
                                                        const inputRect = $el.previousElementSibling.getBoundingClientRect();
                                                        $el.style.top = (inputRect.top - 80) + 'px';
                                                        $el.style.left = inputRect.left + 'px';
                                                        $el.style.width = inputRect.width + 'px';
                                                    }
                                                })">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                        </path>
                                                    </svg>
                                                    <span>No matching currencies found.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            Search for currencies by code, name, or symbol (e.g. USD, Dollar, $).
                                        </p>
                                        @error('exchange_rate_currency')
                                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Quotation Currencies -->
                                <div class="md:col-span-2" x-data="{
                            search: '',
                            open: false,
                            selected: {{ json_encode($quotationCurrencies) }},
                            allCurrencies: {{ json_encode($currencies) }},
                            currencyNames: {{ json_encode($currencyNames) }},
                            highlightedIndex: -1,

                            init() {
                                this.$nextTick(() => syncCurrencySelectOptions());
                            },

                            get filteredCurrencies() {
                                if (this.search === '') {
                                    const result = {};
                                    for (const [code, symbol] of Object.entries(this.allCurrencies)) {
                                        if (!this.selected.includes(code)) {
                                            result[code] = symbol;
                                        }
                                    }
                                    return result;
                                }
                                const query = this.search.toLowerCase();
                                const result = {};
                                for (const [code, symbol] of Object.entries(this.allCurrencies)) {
                                    if (!this.selected.includes(code)) {
                                        const name = this.currencyNames[code] || '';
                                        if (code.toLowerCase().includes(query) ||
                                            (symbol && symbol.toLowerCase().includes(query)) ||
                                            (name && name.toLowerCase().includes(query))) {
                                            result[code] = symbol;
                                        }
                                    }
                                }
                                return result;
                            },

                            get filteredCurrencyList() {
                                return Object.entries(this.filteredCurrencies);
                            },

                            add(code) {
                                if (!this.selected.includes(code)) {
                                    this.selected.push(code);
                                    this.search = '';
                                    this.open = false;
                                    this.highlightedIndex = -1;
                                    this.$nextTick(() => syncCurrencySelectOptions());
                                }
                            },

                            remove(code) {
                                if (code !== 'BDT') {
                                    this.selected = this.selected.filter(c => c !== code);
                                    this.$nextTick(() => syncCurrencySelectOptions());
                                }
                            },

                            highlightPrevious() {
                                const list = this.filteredCurrencyList;
                                if (list.length === 0) return;
                                this.highlightedIndex = this.highlightedIndex <= 0 ? list.length - 1 : this.highlightedIndex - 1;
                                this.scrollToHighlighted();
                            },

                            highlightNext() {
                                const list = this.filteredCurrencyList;
                                if (list.length === 0) return;
                                this.highlightedIndex = this.highlightedIndex >= list.length - 1 ? 0 : this.highlightedIndex + 1;
                                this.scrollToHighlighted();
                            },

                            selectHighlighted() {
                                const list = this.filteredCurrencyList;
                                if (this.highlightedIndex >= 0 && this.highlightedIndex < list.length) {
                                    this.add(list[this.highlightedIndex][0]);
                                }
                            },

                            scrollToHighlighted() {
                                this.$nextTick(() => {
                                    const highlighted = this.$refs.dropdown?.querySelector('.highlighted');
                                    if (highlighted) {
                                        highlighted.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                                    }
                                });
                            }
                        }">
                                    <label
                                        class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                            </path>
                                        </svg>
                                        Quotation Currencies <span class="text-red-500 ml-1">*</span>
                                    </label>

                                    <!-- Selected Tags -->
                                    <div
                                        class="flex flex-wrap gap-2 mb-4 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-200 dark:border-gray-600">
                                        <template x-for="code in selected" :key="code">
                                            <div
                                                class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 shadow-sm transition-all duration-200 hover:shadow-md">
                                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200"
                                                    x-text="code"></span>
                                                <input type="hidden" name="quotation_currencies[]" :value="code">
                                                <button type="button"
                                                    class="h-6 w-6 rounded-md flex items-center justify-center transition-all duration-200"
                                                    :class="code === 'BDT' ? 'text-gray-300 cursor-not-allowed' : 'text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20'"
                                                    :disabled="code === 'BDT'" @click="remove(code)">
                                                    ×
                                                </button>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Search Input -->
                                    <div class="relative" @click.away="open = false">
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                            </div>
                                            <input type="text" x-model="search"
                                                @focus="open = true; highlightedIndex = -1"
                                                @keydown.escape="open = false"
                                                @keydown.arrow-down.prevent="highlightNext()"
                                                @keydown.arrow-up.prevent="highlightPrevious()"
                                                @keydown.enter.prevent="selectHighlighted()"
                                                class="w-full pl-10 pr-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200 hover:border-gray-300"
                                                placeholder="Search to add currency (e.g. SAR, Saudi, Riyal)...">
                                        </div>

                                        <div x-show="open && Object.keys(filteredCurrencies).length > 0"
                                            x-ref="dropdown"
                                            class="absolute z-20 mt-2 w-full bg-white dark:bg-gray-800 shadow-2xl max-h-60 rounded-xl py-2 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-gray-100 dark:border-gray-700"
                                            style="display: none;">
                                            <template x-for="(symbol, code, index) in filteredCurrencies" :key="code">
                                                <div class="cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-900 dark:text-white transition-colors"
                                                    :class="{ 'highlighted bg-blue-50 dark:bg-blue-900/20': index === highlightedIndex }"
                                                    @click="add(code)" @mouseenter="highlightedIndex = index">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center space-x-3">
                                                            <span class="block font-medium" x-text="code"></span>
                                                            <span x-show="currencyNames[code]"
                                                                x-text="currencyNames[code]"
                                                                class="text-sm text-gray-500 dark:text-gray-400"></span>
                                                            <span x-show="symbol && !currencyNames[code]"
                                                                x-text="symbol"
                                                                class="text-sm text-gray-500 dark:text-gray-400"></span>
                                                        </div>
                                                        <span x-show="index === highlightedIndex"
                                                            class="absolute inset-y-0 right-0 flex items-center pr-4">
                                                            <svg class="h-5 w-5 text-blue-600" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                        <div x-show="open && search !== '' && Object.keys(filteredCurrencies).length === 0"
                                            class="absolute z-20 mt-2 w-full bg-white dark:bg-gray-800 shadow-2xl rounded-xl py-4 px-4 text-sm text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-gray-700"
                                            style="display: none;">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                    </path>
                                                </svg>
                                                <span>No matching currencies found.</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Search for currencies by code, name, or symbol (e.g. SAR, Saudi, Riyal).
                                    </p>
                                    @error('quotation_currencies')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                    @error('quotation_currencies.*')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quotation Number Format Section -->
                    <div id="quotation-panel" class="tab-panel" role="tabpanel" aria-labelledby="quotation-tab" x-cloak
                        x-show="activeTab === 'quotation'">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-white">Quotation Number Format</h2>
                                        <p class="text-emerald-100 text-sm mt-1">Customize how quotation numbers are
                                            generated for this company</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6 space-y-6">
                                <div class="grid grid-cols-1 gap-6">
                                    <!-- Quotation Prefix -->
                                    <div class="group">
                                        <label for="quotation_prefix"
                                            class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2M7 4h10M7 4v16a1 1 0 001 1h8a1 1 0 001-1V4M12 8v8m-4-4h8">
                                                </path>
                                            </svg>
                                            Quotation Prefix
                                        </label>
                                        <input type="text" name="quotation_prefix" id="quotation_prefix"
                                            value="{{ old('quotation_prefix', $settings['quotation_prefix']) }}"
                                            class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200 hover:border-gray-300"
                                            placeholder="QTN-" maxlength="20">
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            Used when the <code
                                                class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded font-mono">{PREFIX}</code>
                                            tag is in your format pattern.
                                        </p>
                                        @error('quotation_prefix')
                                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <!-- Quotation Number Format -->
                                    <div class="group">
                                        <label for="quotation_number_format"
                                            class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-teal-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                            </svg>
                                            Number Format Pattern <span class="text-red-500 ml-1">*</span>
                                        </label>
                                        <input type="text" name="quotation_number_format" id="quotation_number_format"
                                            value="{{ old('quotation_number_format', $settings['quotation_number_format']) }}"
                                            class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono transition-all duration-200 hover:border-gray-300"
                                            placeholder="{CUSTOMER_NO}-{YY}-{SEQUENCE}" maxlength="100" required
                                            oninput="updatePreview()">
                                        @error('quotation_number_format')
                                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <!-- Available Tags -->
                                    <div
                                        class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700/30 dark:to-gray-700/50 rounded-xl p-6 border border-gray-200 dark:border-gray-600">
                                        <h3
                                            class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                                </path>
                                            </svg>
                                            Available Tags
                                        </h3>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                            <div class="flex items-center gap-2 text-xs">
                                                <button type="button" data-tag="{CUSTOMER_NO}"
                                                    class="bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 font-mono text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 cursor-pointer transition-all duration-200 shadow-sm hover:shadow-md">{CUSTOMER_NO}</button>
                                                <span class="text-gray-600 dark:text-gray-400">Customer code</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs">
                                                <button type="button" data-tag="{SEQUENCE}"
                                                    class="bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 font-mono text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 cursor-pointer transition-all duration-200 shadow-sm hover:shadow-md">{SEQUENCE}</button>
                                                <span class="text-gray-600 dark:text-gray-400">Auto-increment</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs">
                                                <button type="button" data-tag="{PREFIX}"
                                                    class="bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 font-mono text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 cursor-pointer transition-all duration-200 shadow-sm hover:shadow-md">{PREFIX}</button>
                                                <span class="text-gray-600 dark:text-gray-400">Your prefix</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs">
                                                <button type="button" data-tag="{YYYY}"
                                                    class="bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 font-mono text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 cursor-pointer transition-all duration-200 shadow-sm hover:shadow-md">{YYYY}</button>
                                                <span class="text-gray-600 dark:text-gray-400">Full year</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs">
                                                <button type="button" data-tag="{YY}"
                                                    class="bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 font-mono text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 cursor-pointer transition-all duration-200 shadow-sm hover:shadow-md">{YY}</button>
                                                <span class="text-gray-600 dark:text-gray-400">Short year</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs">
                                                <button type="button" data-tag="{MM}"
                                                    class="bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 font-mono text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 cursor-pointer transition-all duration-200 shadow-sm hover:shadow-md">{MM}</button>
                                                <span class="text-gray-600 dark:text-gray-400">Month</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs">
                                                <button type="button" data-tag="{DD}"
                                                    class="bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 font-mono text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 cursor-pointer transition-all duration-200 shadow-sm hover:shadow-md">{DD}</button>
                                                <span class="text-gray-600 dark:text-gray-400">Day</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs">
                                                <button type="button" data-tag="{ID}"
                                                    class="bg-white dark:bg-gray-800 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 font-mono text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 cursor-pointer transition-all duration-200 shadow-sm hover:shadow-md">{ID}</button>
                                                <span class="text-gray-600 dark:text-gray-400">Total count</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Preview -->
                                    <div
                                        class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl p-6 border border-emerald-200 dark:border-emerald-800">
                                        <h3
                                            class="text-sm font-semibold text-emerald-700 dark:text-emerald-300 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                            Live Preview
                                        </h3>
                                        <p class="text-xl font-mono font-bold text-emerald-900 dark:text-emerald-200"
                                            id="format-preview">—</p>
                                        <p
                                            class="text-xs text-emerald-600 dark:text-emerald-400 mt-2 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            Example with customer <code class="font-mono">ACME</code>, sequence <code
                                                class="font-mono">001</code>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- VAT Settings Section -->
                    <div id="vat-panel" class="tab-panel" role="tabpanel" aria-labelledby="vat-tab" x-cloak
                        x-show="activeTab === 'vat'">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-orange-500 to-red-600 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h2 class="text-xl font-bold text-white">VAT Settings</h2>
                                            <p class="text-orange-100 text-sm mt-1">Configure VAT percentages and
                                                default rate for quotations</p>
                                        </div>
                                    </div>
                                    <button type="button" id="add-vat-percentage"
                                        class="px-4 py-2 text-sm font-semibold text-white bg-white/20 hover:bg-white/30 border border-white/30 rounded-lg transition-all duration-200 backdrop-blur-sm">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Add VAT
                                    </button>
                                </div>
                            </div>
                            <div class="p-6 space-y-6">
                                @php
                                    $vatPercentages = old('vat_percentages', $settings['vat_percentages'] ?? [0, 5, 10, 15]);
                                    $vatDefaultFallback = (is_array($vatPercentages) && count($vatPercentages))
                                        ? $vatPercentages[array_key_last($vatPercentages)]
                                        : 0;
                                    $vatDefaultValue = old('vat_default_percentage', $settings['vat_default_percentage'] ?? $vatDefaultFallback);
                                @endphp

                                <!-- VAT Percentages -->
                                <div>
                                    <label
                                        class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-orange-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                        Available VAT Percentages <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <div class="flex flex-wrap gap-3 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-200 dark:border-gray-600"
                                        id="vat-percentages-list">
                                        @foreach($vatPercentages as $percentage)
                                            <div
                                                class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 shadow-sm transition-all duration-200 hover:shadow-md">
                                                <input type="number" name="vat_percentages[]" min="0" max="100" step="0.01"
                                                    value="{{ $percentage }}"
                                                    class="w-20 bg-transparent border-0 p-0 text-sm focus:ring-0 dark:text-white font-medium"
                                                    placeholder="0" required>
                                                <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">%</span>
                                                <button type="button"
                                                    class="h-6 w-6 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 vat-remove">×</button>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('vat_percentages')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                    @error('vat_percentages.*')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Default VAT Percentage -->
                                <div
                                    class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-800">
                                    <div class="flex items-start gap-3">
                                        <div class="p-2 bg-blue-100 dark:bg-blue-800/50 rounded-lg">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <label for="vat-default-select"
                                                class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                                Default VAT Percentage <span class="text-red-500 ml-1">*</span>
                                            </label>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">This VAT rate will
                                                be automatically selected when creating new quotations.</p>
                                            <select id="vat-default-select" name="vat_default_percentage"
                                                class="w-full md:w-48 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-gray-900 dark:text-white shadow-sm transition-all duration-200">
                                                @foreach($vatPercentages as $percentage)
                                                    <option value="{{ $percentage }}" @selected((string) $percentage === (string) $vatDefaultValue)>{{ $percentage }}%</option>
                                                @endforeach
                                            </select>
                                            @error('vat_default_percentage')
                                                <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- PDF Header Style Section -->
                    <div id="pdf-panel" class="tab-panel" role="tabpanel" aria-labelledby="pdf-tab" x-cloak
                        x-show="activeTab === 'pdf'">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-white">PDF Header Style</h2>
                                        <p class="text-purple-100 text-sm mt-1">Select the header layout design for
                                            quotation/invoice PDFs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                @php
                                    $headerStyle = old('header_style', $settings['header_style'] ?? 'style_1');
                                @endphp
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Style 1: Standard -->
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="header_style" value="style_1" {{ $headerStyle === 'style_1' ? 'checked' : '' }} class="peer sr-only">
                                        <div
                                            class="p-4 border-2 rounded-xl transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 peer-checked:ring-2 peer-checked:ring-purple-500/20 border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500 hover:shadow-lg h-full">
                                            <div class="flex items-center justify-between mb-3">
                                                <span
                                                    class="text-sm font-semibold text-gray-900 dark:text-white">Standard</span>
                                                <svg class="w-5 h-5 text-purple-500 opacity-0 peer-checked:opacity-100 transition-opacity"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div
                                                class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                                                <div class="flex justify-between items-center gap-2">
                                                    <div
                                                        class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded flex items-center justify-center text-xs text-gray-500">
                                                        Logo</div>
                                                    <div class="text-right flex-1">
                                                        <div class="text-xs font-bold text-gray-800 dark:text-white">
                                                            Company Name</div>
                                                        <div class="text-[10px] text-gray-500">Address | Email</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Style 2: Inverted -->
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="header_style" value="style_2" {{ $headerStyle === 'style_2' ? 'checked' : '' }} class="peer sr-only">
                                        <div
                                            class="p-4 border-2 rounded-xl transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 peer-checked:ring-2 peer-checked:ring-purple-500/20 border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500 hover:shadow-lg h-full">
                                            <div class="flex items-center justify-between mb-3">
                                                <span
                                                    class="text-sm font-semibold text-gray-900 dark:text-white">Inverted</span>
                                                <svg class="w-5 h-5 text-purple-500 opacity-0 peer-checked:opacity-100 transition-opacity"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div
                                                class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                                                <div class="flex justify-between items-center gap-2">
                                                    <div class="flex-1">
                                                        <div class="text-xs font-bold text-gray-800 dark:text-white">
                                                            Company Name</div>
                                                        <div class="text-[10px] text-gray-500">Address | Email</div>
                                                    </div>
                                                    <div
                                                        class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded flex items-center justify-center text-xs text-gray-500">
                                                        Logo</div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Style 3: Centered -->
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="header_style" value="style_3" {{ $headerStyle === 'style_3' ? 'checked' : '' }} class="peer sr-only">
                                        <div
                                            class="p-4 border-2 rounded-xl transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 peer-checked:ring-2 peer-checked:ring-purple-500/20 border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500 hover:shadow-lg h-full">
                                            <div class="flex items-center justify-between mb-3">
                                                <span
                                                    class="text-sm font-semibold text-gray-900 dark:text-white">Centered</span>
                                                <svg class="w-5 h-5 text-purple-500 opacity-0 peer-checked:opacity-100 transition-opacity"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div
                                                class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                                                <div class="text-center">
                                                    <div
                                                        class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded mx-auto mb-1 flex items-center justify-center text-xs text-gray-500">
                                                        Logo</div>
                                                    <div class="text-xs font-bold text-gray-800 dark:text-white">Company
                                                        Name</div>
                                                    <div class="text-[10px] text-gray-500">Address | Email | Phone</div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Style 4: Corporate Bar -->
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="header_style" value="style_4" {{ $headerStyle === 'style_4' ? 'checked' : '' }} class="peer sr-only">
                                        <div
                                            class="p-4 border-2 rounded-xl transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 peer-checked:ring-2 peer-checked:ring-purple-500/20 border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500 hover:shadow-lg h-full">
                                            <div class="flex items-center justify-between mb-3">
                                                <span
                                                    class="text-sm font-semibold text-gray-900 dark:text-white">Corporate
                                                    Bar</span>
                                                <svg class="w-5 h-5 text-purple-500 opacity-0 peer-checked:opacity-100 transition-opacity"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div
                                                class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-100 dark:border-gray-700">
                                                <div class="bg-purple-600 p-2 flex items-center gap-2">
                                                    <div
                                                        class="w-6 h-6 bg-white/20 rounded flex items-center justify-center text-[8px] text-white">
                                                        Logo</div>
                                                    <div class="text-xs font-bold text-white">Company Name</div>
                                                </div>
                                                <div class="p-2 text-center text-[10px] text-gray-500">Address | Email |
                                                    Phone</div>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Style 5: Minimal -->
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="header_style" value="style_5" {{ $headerStyle === 'style_5' ? 'checked' : '' }} class="peer sr-only">
                                        <div
                                            class="p-4 border-2 rounded-xl transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 peer-checked:ring-2 peer-checked:ring-purple-500/20 border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500 hover:shadow-lg h-full">
                                            <div class="flex items-center justify-between mb-3">
                                                <span
                                                    class="text-sm font-semibold text-gray-900 dark:text-white">Minimal</span>
                                                <svg class="w-5 h-5 text-purple-500 opacity-0 peer-checked:opacity-100 transition-opacity"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div
                                                class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                                                <div class="flex justify-center">
                                                    <div
                                                        class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded flex items-center justify-center text-xs text-gray-500">
                                                        Logo</div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Style 6: Professional -->
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="header_style" value="style_6" {{ $headerStyle === 'style_6' ? 'checked' : '' }} class="peer sr-only">
                                        <div
                                            class="p-4 border-2 rounded-xl transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 peer-checked:ring-2 peer-checked:ring-purple-500/20 border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500 hover:shadow-lg h-full">
                                            <div class="flex items-center justify-between mb-3">
                                                <span
                                                    class="text-sm font-semibold text-gray-900 dark:text-white">Professional</span>
                                                <svg class="w-5 h-5 text-purple-500 opacity-0 peer-checked:opacity-100 transition-opacity"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div
                                                class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-100 dark:border-gray-700">
                                                <div class="flex items-start gap-3">
                                                    <div
                                                        class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded flex items-center justify-center text-xs text-gray-500 flex-shrink-0">
                                                        Logo</div>
                                                    <div class="flex-1 text-right">
                                                        <div class="text-[10px] text-gray-500 space-y-0.5">
                                                            <div>123 Street Address</div>
                                                            <div>email@company.com</div>
                                                            <div>+1 234 567 8900</div>
                                                            <div>www.company.com</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                @error('header_style')
                                    <p class="mt-4 text-sm text-red-600 dark:text-red-400 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Authorization Settings Section -->
                    <div id="authorization-panel" class="tab-panel" role="tabpanel" aria-labelledby="authorization-tab"
                        x-cloak x-show="activeTab === 'authorization'">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-500 to-blue-600 px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-white">Authorization Settings</h2>
                                        <p class="text-indigo-100 text-sm mt-1">Configure the authorized signature and
                                            company seal for quotation PDFs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6 space-y-6">
                                @php
                                    $authorizedPersonName = old('authorized_person_name', $settings['authorized_person_name'] ?? '');
                                    $authorizationLabel = old('authorization_label', $settings['authorization_label'] ?? 'Authorized By');
                                    $currentSignature = $settings['signature_image'] ?? null;
                                    $currentSeal = $settings['company_seal_image'] ?? null;
                                @endphp

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Authorized Person Name -->
                                    <div class="group">
                                        <label for="authorized_person_name"
                                            class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                            Authorized Person Name
                                        </label>
                                        <input type="text" name="authorized_person_name" id="authorized_person_name"
                                            value="{{ $authorizedPersonName }}"
                                            class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200 hover:border-gray-300"
                                            placeholder="Mohammad Ataur Rahman">
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            Name of the person authorizing quotations.
                                        </p>
                                        @error('authorized_person_name')
                                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <!-- Authorization Label -->
                                    <div class="group">
                                        <label for="authorization_label"
                                            class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                            Authorization Label
                                        </label>
                                        <input type="text" name="authorization_label" id="authorization_label"
                                            value="{{ $authorizationLabel }}"
                                            class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200 hover:border-gray-300"
                                            placeholder="Authorized By">
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            Label displayed above the signature (e.g., "Authorized By", "Prepared By").
                                        </p>
                                        @error('authorization_label')
                                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Signature Image -->
                                    <div class="group">
                                        <label for="signature_image"
                                            class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036a3 3 0 01-2.036-5.036L3.232 13.768a2.5 2.5 0 013.536-3.536l3.536-3.536z">
                                                </path>
                                            </svg>
                                            Signature Image
                                        </label>

                                        <!-- Modern Upload Area -->
                                        <div class="relative" x-data="{
                                    hasImage: {{ $currentSignature ? 'true' : 'false' }},
                                    imageUrl: '{{ $currentSignature ? asset('storage/' . $currentSignature) : '' }}',
                                    fileName: '{{ $currentSignature ? basename($currentSignature) : '' }}',
                                    isDragging: false,

                                    handleFileSelect(event) {
                                        const file = event.target.files[0];
                                        if (file && file.type.startsWith('image/')) {
                                            this.hasImage = true;
                                            this.fileName = file.name;
                                            const reader = new FileReader();
                                            reader.onload = (e) => {
                                                this.imageUrl = e.target.result;
                                            };
                                            reader.readAsDataURL(file);
                                        }
                                    },

                                    handleDrop(event) {
                                        event.preventDefault();
                                        this.isDragging = false;
                                        const file = event.dataTransfer.files[0];
                                        if (file && file.type.startsWith('image/')) {
                                            this.hasImage = true;
                                            this.fileName = file.name;
                                            const reader = new FileReader();
                                            reader.onload = (e) => {
                                                this.imageUrl = e.target.result;
                                            };
                                            reader.readAsDataURL(file);
                                        }
                                    },

                                    handleDragOver(event) {
                                        event.preventDefault();
                                        this.isDragging = true;
                                    },

                                    handleDragLeave() {
                                        this.isDragging = false;
                                    },

                                    removeImage() {
                                        this.hasImage = false;
                                        this.imageUrl = '';
                                        this.fileName = '';
                                        document.getElementById('signature_image').value = '';
                                    }
                                }">

                                            <!-- Upload Area -->
                                            <div x-show="!hasImage"
                                                class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:border-purple-400 dark:hover:border-purple-500 transition-all duration-200 cursor-pointer bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/20 dark:to-gray-800/30"
                                                @click="$refs.signatureInput.click()" @dragover="handleDragOver($event)"
                                                @dragleave="handleDragLeave()" @drop="handleDrop($event)"
                                                :class="isDragging ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : ''">

                                                <div class="flex flex-col items-center space-y-4">
                                                    <div class="p-4 bg-purple-100 dark:bg-purple-900/30 rounded-full">
                                                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p
                                                            class="text-lg font-medium text-gray-900 dark:text-white mb-1">
                                                            Drop signature image here</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">or click to
                                                            browse</p>
                                                    </div>
                                                    <div
                                                        class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                                        <span class="flex items-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                                </path>
                                                            </svg>
                                                            PNG, JPG
                                                        </span>
                                                        <span class="flex items-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                                </path>
                                                            </svg>
                                                            200x80px
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Preview Area -->
                                            <div x-show="hasImage" class="relative group">
                                                <div
                                                    class="relative bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/20 dark:to-gray-800/30 rounded-xl p-6 border border-gray-200 dark:border-gray-600">
                                                    <div class="flex items-start justify-between mb-4">
                                                        <div class="flex items-center space-x-3">
                                                            <div
                                                                class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                                                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400"
                                                                    fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                                                                    </path>
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <p
                                                                    class="text-sm font-medium text-gray-900 dark:text-white">
                                                                    Signature uploaded</p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400"
                                                                    x-text="fileName"></p>
                                                            </div>
                                                        </div>
                                                        <button type="button" @click="removeImage()"
                                                            class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all duration-200">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <div class="flex justify-center">
                                                        <div class="relative inline-block">
                                                            <img :src="imageUrl" alt="Signature Preview"
                                                                class="max-w-full h-auto max-h-32 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 bg-white p-2">
                                                            <div
                                                                class="absolute -top-2 -right-2 bg-purple-500 text-white text-xs px-2 py-1 rounded-full font-medium">
                                                                Preview
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-4 flex items-center justify-center space-x-3">
                                                        <button type="button" @click="$refs.signatureInput.click()"
                                                            class="px-4 py-2 text-sm font-medium text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-all duration-200">
                                                            <svg class="w-4 h-4 mr-2 inline" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                                </path>
                                                            </svg>
                                                            Change Image
                                                        </button>
                                                        @if($currentSignature)
                                                            <label
                                                                class="inline-flex items-center gap-2 text-sm text-red-600 cursor-pointer hover:text-red-700 transition-colors">
                                                                <input type="checkbox" name="remove_signature_image"
                                                                    value="1"
                                                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                                <span>Remove existing</span>
                                                            </label>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Hidden File Input -->
                                            <input type="file" name="signature_image" id="signature_image"
                                                x-ref="signatureInput" accept="image/png,image/jpeg,image/jpg"
                                                class="hidden" @change="handleFileSelect($event)">
                                        </div>

                                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            Upload a PNG or JPG image of your signature. Recommended size: 200x80px for
                                            best results.
                                        </p>
                                        @error('signature_image')
                                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <!-- Company Seal Image -->
                                    <div class="group">
                                        <label for="company_seal_image"
                                            class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-pink-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Company Seal / Stamp
                                        </label>

                                        <!-- Modern Upload Area -->
                                        <div class="relative" x-data="{
                                    hasImage: {{ $currentSeal ? 'true' : 'false' }},
                                    imageUrl: '{{ $currentSeal ? asset('storage/' . $currentSeal) : '' }}',
                                    fileName: '{{ $currentSeal ? basename($currentSeal) : '' }}',
                                    isDragging: false,

                                    handleFileSelect(event) {
                                        const file = event.target.files[0];
                                        if (file && file.type.startsWith('image/')) {
                                            this.hasImage = true;
                                            this.fileName = file.name;
                                            const reader = new FileReader();
                                            reader.onload = (e) => {
                                                this.imageUrl = e.target.result;
                                            };
                                            reader.readAsDataURL(file);
                                        }
                                    },

                                    handleDrop(event) {
                                        event.preventDefault();
                                        this.isDragging = false;
                                        const file = event.dataTransfer.files[0];
                                        if (file && file.type.startsWith('image/')) {
                                            this.hasImage = true;
                                            this.fileName = file.name;
                                            const reader = new FileReader();
                                            reader.onload = (e) => {
                                                this.imageUrl = e.target.result;
                                            };
                                            reader.readAsDataURL(file);
                                        }
                                    },

                                    handleDragOver(event) {
                                        event.preventDefault();
                                        this.isDragging = true;
                                    },

                                    handleDragLeave() {
                                        this.isDragging = false;
                                    },

                                    removeImage() {
                                        this.hasImage = false;
                                        this.imageUrl = '';
                                        this.fileName = '';
                                        document.getElementById('company_seal_image').value = '';
                                    }
                                }">

                                            <!-- Upload Area -->
                                            <div x-show="!hasImage"
                                                class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:border-pink-400 dark:hover:border-pink-500 transition-all duration-200 cursor-pointer bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/20 dark:to-gray-800/30"
                                                @click="$refs.sealInput.click()" @dragover="handleDragOver($event)"
                                                @dragleave="handleDragLeave()" @drop="handleDrop($event)"
                                                :class="isDragging ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/20' : ''">

                                                <div class="flex flex-col items-center space-y-4">
                                                    <div class="p-4 bg-pink-100 dark:bg-pink-900/30 rounded-full">
                                                        <svg class="w-8 h-8 text-pink-600 dark:text-pink-400"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p
                                                            class="text-lg font-medium text-gray-900 dark:text-white mb-1">
                                                            Drop company seal here</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">or click to
                                                            browse</p>
                                                    </div>
                                                    <div
                                                        class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                                        <span class="flex items-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                                </path>
                                                            </svg>
                                                            PNG, JPG
                                                        </span>
                                                        <span class="flex items-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                                </path>
                                                            </svg>
                                                            150x150px
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Preview Area -->
                                            <div x-show="hasImage" class="relative group">
                                                <div
                                                    class="relative bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/20 dark:to-gray-800/30 rounded-xl p-6 border border-gray-200 dark:border-gray-600">
                                                    <div class="flex items-start justify-between mb-4">
                                                        <div class="flex items-center space-x-3">
                                                            <div class="p-2 bg-pink-100 dark:bg-pink-900/30 rounded-lg">
                                                                <svg class="w-5 h-5 text-pink-600 dark:text-pink-400"
                                                                    fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                                                                    </path>
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <p
                                                                    class="text-sm font-medium text-gray-900 dark:text-white">
                                                                    Company seal uploaded</p>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400"
                                                                    x-text="fileName"></p>
                                                            </div>
                                                        </div>
                                                        <button type="button" @click="removeImage()"
                                                            class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all duration-200">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <div class="flex justify-center">
                                                        <div class="relative inline-block">
                                                            <img :src="imageUrl" alt="Company Seal Preview"
                                                                class="max-w-full h-auto max-h-40 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 bg-white p-2">
                                                            <div
                                                                class="absolute -top-2 -right-2 bg-pink-500 text-white text-xs px-2 py-1 rounded-full font-medium">
                                                                Preview
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-4 flex items-center justify-center space-x-3">
                                                        <button type="button" @click="$refs.sealInput.click()"
                                                            class="px-4 py-2 text-sm font-medium text-pink-600 dark:text-pink-400 bg-pink-50 dark:bg-pink-900/20 border border-pink-200 dark:border-pink-700 rounded-lg hover:bg-pink-100 dark:hover:bg-pink-900/30 transition-all duration-200">
                                                            <svg class="w-4 h-4 mr-2 inline" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                                </path>
                                                            </svg>
                                                            Change Image
                                                        </button>
                                                        @if($currentSeal)
                                                            <label
                                                                class="inline-flex items-center gap-2 text-sm text-red-600 cursor-pointer hover:text-red-700 transition-colors">
                                                                <input type="checkbox" name="remove_company_seal_image"
                                                                    value="1"
                                                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                                <span>Remove existing</span>
                                                            </label>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Hidden File Input -->
                                            <input type="file" name="company_seal_image" id="company_seal_image"
                                                x-ref="sealInput" accept="image/png,image/jpeg,image/jpg" class="hidden"
                                                @change="handleFileSelect($event)">
                                        </div>

                                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            Upload a PNG or JPG image of your company seal. Recommended size: 150x150px
                                            for best results.
                                        </p>
                                        @error('company_seal_image')
                                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Footer -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('tenant.user-companies.index') }}"
                                class="group relative px-8 py-4 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500 rounded-2xl focus:outline-none focus:ring-4 focus:ring-gray-500/20 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900 transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-[1.02] hover:-translate-y-0.5 before:absolute before:inset-0 before:rounded-2xl before:bg-gradient-to-r before:from-gray-100/50 before:to-transparent before:opacity-0 hover:before:opacity-100 before:transition-opacity before:duration-300">
                                <span class="relative z-10 flex items-center">
                                    <svg class="w-5 h-5 mr-3 group-hover:rotate-90 transition-transform duration-300"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancel
                                    <svg class="w-4 h-4 ml-2 opacity-60 group-hover:opacity-100 group-hover:-translate-x-1 transition-all duration-300"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                </span>
                            </a>
                            <button type="submit"
                                class="group relative px-8 py-4 text-sm font-semibold text-white bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-600 hover:from-emerald-600 hover:via-teal-600 hover:to-cyan-700 border-0 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900 transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:scale-[1.02] hover:-translate-y-0.5 before:absolute before:inset-0 before:rounded-2xl before:bg-gradient-to-r before:from-white/20 before:to-transparent before:opacity-0 hover:before:opacity-100 before:transition-opacity before:duration-300">
                                <span class="relative z-10 flex items-center">
                                    <svg class="w-5 h-5 mr-3 group-hover:rotate-12 transition-transform duration-300"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Save Settings
                                    <svg class="w-4 h-4 ml-2 opacity-60 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-300"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
        </div>
        </form>
    </div>
    </div>

    <script>
        // Tab switching functionality


        // Original existing scripts
        const currencyMap = @json($currencies);
        const currencyNames = @json($currencyNames);
        const initialQuotationCurrencies = @json($quotationCurrencies);

        function updateCurrencySymbol(select) {
            const option = select.options[select.selectedIndex];
            const symbol = option?.dataset?.symbol;
            if (symbol) {
                document.getElementById('currency_symbol').value = symbol;
            }
            updatePreview();
        }

        function normalizeCurrency(value) {
            return value.trim().toUpperCase();
        }

        function getQuotationCurrencyValues() {
            return Array.from(document.querySelectorAll('input[name="quotation_currencies[]"]'))
                .map((input) => input.value)
                .filter((value) => value);
        }

        function syncCurrencySelectOptions() {
            const select = document.getElementById('currency');
            if (!select) return;
            const currentValue = select.value;
            const codes = getQuotationCurrencyValues();
            select.innerHTML = '';

            codes.forEach((code) => {
                const option = document.createElement('option');
                option.value = code;
                const symbol = currencyMap[code] || '';
                const name = currencyNames[code] || '';

                // Determine display text: prioritize symbol, then name, then code
                let displayText = code;
                if (symbol && symbol !== code) {
                    displayText = `${code} (${symbol})`;
                } else if (name) {
                    displayText = `${code} (${name})`;
                }

                option.dataset.symbol = symbol;
                option.textContent = displayText;

                if (code === currentValue) {
                    option.selected = true;
                }
                select.appendChild(option);
            });

            if (codes.length && !codes.includes(currentValue)) {
                select.value = codes[0];
                updateCurrencySymbol(select);
            }
        }

        function createCurrencyChip(code) {
            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-center gap-2 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5';

            const label = document.createElement('span');
            label.className = 'text-sm font-semibold text-gray-700 dark:text-gray-200';
            label.textContent = code;

            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'quotation_currencies[]';
            hidden.value = code;

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'h-6 w-6 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20';
            removeButton.textContent = '×';

            if (code === 'BDT') {
                removeButton.disabled = true;
                removeButton.className = 'h-6 w-6 rounded-md text-gray-300 cursor-not-allowed';
            } else {
                removeButton.addEventListener('click', () => {
                    wrapper.remove();
                    syncCurrencySelectOptions();
                });
            }

            wrapper.appendChild(label);
            wrapper.appendChild(hidden);
            wrapper.appendChild(removeButton);

            return wrapper;
        }

        function addQuotationCurrency(code) {
            const normalized = normalizeCurrency(code);
            if (!/^[A-Z]{2,5}$/.test(normalized)) {
                return;
            }
            const existing = getQuotationCurrencyValues();
            if (existing.includes(normalized)) {
                return;
            }
            const list = document.getElementById('quotation-currency-list');
            if (!list) return;
            list.appendChild(createCurrencyChip(normalized));
            syncCurrencySelectOptions();
        }

        function updatePreview() {
            const format = document.getElementById('quotation_number_format').value;
            const prefix = document.getElementById('quotation_prefix').value;
            const now = new Date();

            let preview = format
                .replace(/{PREFIX}/g, prefix)
                .replace(/{CUSTOMER_NO}/g, 'ACME')
                .replace(/{YYYY}/g, now.getFullYear())
                .replace(/{YY}/g, String(now.getFullYear()).slice(-2))
                .replace(/{MM}/g, String(now.getMonth() + 1).padStart(2, '0'))
                .replace(/{DD}/g, String(now.getDate()).padStart(2, '0'))
                .replace(/{SEQUENCE}/g, '001')
                .replace(/{ID}/g, '42');

            document.getElementById('format-preview').textContent = preview || '—';
        }

        function insertTag(tag) {
            const input = document.getElementById('quotation_number_format');
            if (!input) return;
            const start = input.selectionStart ?? input.value.length;
            const end = input.selectionEnd ?? input.value.length;
            const value = input.value;
            input.value = value.slice(0, start) + tag + value.slice(end);
            const cursor = start + tag.length;
            input.setSelectionRange(cursor, cursor);
            input.focus();
            updatePreview();
        }

        function createVatRow(value = '') {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-1.5 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1';

            const input = document.createElement('input');
            input.type = 'number';
            input.name = 'vat_percentages[]';
            input.min = '0';
            input.max = '100';
            input.step = '0.01';
            input.value = value;
            input.placeholder = '0';
            input.required = true;
            input.className = 'w-20 bg-transparent border-0 p-0 text-sm focus:ring-0 dark:text-white';
            input.addEventListener('input', () => syncVatDefaultOptions());

            const suffix = document.createElement('span');
            suffix.className = 'text-xs text-gray-500 dark:text-gray-400';
            suffix.textContent = '%';

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'h-6 w-6 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 vat-remove';
            removeButton.textContent = '×';
            removeButton.addEventListener('click', () => {
                row.remove();
                syncVatDefaultOptions();
            });

            row.appendChild(input);
            row.appendChild(suffix);
            row.appendChild(removeButton);

            return row;
        }

        function getVatValues() {
            return Array.from(document.querySelectorAll('input[name="vat_percentages[]"]'))
                .map((input) => input.value)
                .filter((value) => value !== '');
        }

        function syncVatDefaultOptions() {
            const select = document.getElementById('vat-default-select');
            if (!select) return;
            const currentValue = select.value;
            const values = getVatValues();
            select.innerHTML = '';
            values.forEach((value) => {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = `${value}%`;
                if (String(value) === String(currentValue)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            if (values.length && !select.value) {
                select.value = values[values.length - 1];
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            updatePreview();
            document.querySelectorAll('[data-tag]').forEach((button) => {
                button.addEventListener('click', () => insertTag(button.dataset.tag));
            });

            const vatList = document.getElementById('vat-percentages-list');
            const addVatButton = document.getElementById('add-vat-percentage');

            if (vatList) {
                vatList.querySelectorAll('.vat-remove').forEach((button) => {
                    button.addEventListener('click', (event) => {
                        event.currentTarget.closest('div')?.remove();
                        syncVatDefaultOptions();
                    });
                });
                vatList.querySelectorAll('input[name="vat_percentages[]"]').forEach((input) => {
                    input.addEventListener('input', () => syncVatDefaultOptions());
                });
            }

            if (vatList && addVatButton) {
                addVatButton.addEventListener('click', () => {
                    vatList.appendChild(createVatRow(''));
                    syncVatDefaultOptions();
                });
            }

            const currencyList = document.getElementById('quotation-currency-list');
            const currencyInput = document.getElementById('quotation_currencies_input');
            const addCurrencyButton = document.getElementById('add-quotation-currency');

            if (currencyList) {
                (initialQuotationCurrencies || []).forEach((code) => addQuotationCurrency(code));
            }

            if (currencyInput && addCurrencyButton) {
                addCurrencyButton.addEventListener('click', () => {
                    addQuotationCurrency(currencyInput.value || '');
                    currencyInput.value = '';
                    currencyInput.focus();
                });

                currencyInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        addCurrencyButton.click();
                    }
                });
            }

            syncVatDefaultOptions();
            syncCurrencySelectOptions();
        });
    </script>
</x-dashboard.layout.default>
