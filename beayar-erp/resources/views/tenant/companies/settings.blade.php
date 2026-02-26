<x-dashboard.layout.default title="Company Settings">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('tenant.user-companies.index') }}"
                                class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                    </path>
                                </svg>
                                Workspaces
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 9 4-4-4-4" />
                                </svg>
                                <span
                                    class="ml-1 text-sm font-medium text-gray-900 md:ml-2 dark:text-white">Settings</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Company Settings</h1>
                <p class="mt-2 text-lg text-gray-500 dark:text-gray-400">Configure preferences for <span
                        class="font-semibold text-gray-700 dark:text-gray-300">{{ $company->name }}</span>.</p>
            </div>

            @if(session('success'))
                <div
                    class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300 flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Form Card -->
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <form action="{{ route('tenant.company-settings.update', $company->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-8 space-y-10">

                        {{-- Date & Currency Section --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Regional Preferences
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Set the default date format and
                                currency for your company.</p>

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

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Date Format --}}
                                <div>
                                    <label for="date_format"
                                        class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Date
                                        Format <span class="text-red-500">*</span></label>
                                    <select name="date_format" id="date_format"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5 px-3">
                                        @foreach($dateFormats as $format => $label)
                                            <option value="{{ $format }}" {{ old('date_format', $settings['date_format']) === $format ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('date_format')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Currency --}}
                                <div>
                                    <label for="currency"
                                        class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Currency
                                        <span class="text-red-500">*</span></label>
                                    <select name="currency" id="currency"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5 px-3"
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
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Currency Symbol --}}
                                <div>
                                    <label for="currency_symbol"
                                        class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Currency
                                        Symbol <span class="text-red-500">*</span></label>
                                    <input type="text" name="currency_symbol" id="currency_symbol"
                                        value="{{ old('currency_symbol', $settings['currency_symbol']) }}"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5 px-3"
                                        placeholder="$" maxlength="5" required>
                                    @error('currency_symbol')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Exchange Rate Currency --}}
                                <div>
                                    <label for="exchange_rate_currency"
                                        class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Exchange
                                        Rate Currency
                                        <span class="text-red-500">*</span></label>
                                    <select name="exchange_rate_currency" id="exchange_rate_currency"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5 px-3">
                                        @foreach($allCurrencies ?? $currencies as $code => $symbol)
                                            <option value="{{ $code }}" {{ old('exchange_rate_currency', $settings['exchange_rate_currency'] ?? 'BDT') === $code ? 'selected' : '' }}>
                                                {{ $symbol ? "{$code} ({$symbol})" : $code }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('exchange_rate_currency')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

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
                                            // Show all available currencies when search is empty
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
                                        class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Quotation
                                        Currencies <span class="text-red-500">*</span></label>

                                    {{-- Selected Tags --}}
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <template x-for="code in selected" :key="code">
                                            <div
                                                class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-1.5">
                                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200"
                                                    x-text="code"></span>
                                                <input type="hidden" name="quotation_currencies[]" :value="code">
                                                <button type="button"
                                                    class="h-6 w-6 rounded-md flex items-center justify-center transition-colors"
                                                    :class="code === 'BDT' ? 'text-gray-300 cursor-not-allowed' : 'text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20'"
                                                    :disabled="code === 'BDT'" @click="remove(code)">
                                                    ×
                                                </button>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Search Input --}}
                                    <div class="relative" @click.away="open = false">
                                        <input type="text" x-model="search"
                                            @focus="open = true; highlightedIndex = -1"
                                            @keydown.escape="open = false"
                                            @keydown.arrow-down.prevent="highlightNext()"
                                            @keydown.arrow-up.prevent="highlightPrevious()"
                                            @keydown.enter.prevent="selectHighlighted()"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5 px-3"
                                            placeholder="Search to add currency (e.g. SAR, Saudi, Riyal)...">

                                        <div x-show="open && Object.keys(filteredCurrencies).length > 0"
                                            x-ref="dropdown"
                                            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                                            style="display: none;">
                                            <template x-for="(symbol, code, index) in filteredCurrencies" :key="code">
                                                <div class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-white"
                                                    :class="{ 'highlighted bg-blue-50 dark:bg-blue-900/20': index === highlightedIndex }"
                                                    @click="add(code)"
                                                    @mouseenter="highlightedIndex = index">
                                                    <span class="block truncate">
                                                        <span x-text="code" class="font-medium"></span>
                                                        <span x-show="currencyNames[code]" x-text="` (${currencyNames[code]})`"
                                                            class="text-gray-500 dark:text-gray-400"></span>
                                                        <span x-show="symbol && !currencyNames[code]" x-text="` (${symbol})`"
                                                            class="text-gray-500 dark:text-gray-400"></span>
                                                    </span>
                                                    <span x-show="index === highlightedIndex" class="absolute inset-y-0 right-0 flex items-center pr-4">
                                                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </span>
                                                </div>
                                            </template>
                                        </div>
                                        <div x-show="open && search !== '' && Object.keys(filteredCurrencies).length === 0"
                                            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg rounded-md py-2 px-3 text-sm text-gray-500 dark:text-gray-400"
                                            style="display: none;">
                                            No matching currencies found.
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Search for currencies by code, name, or symbol (e.g. SAR, Saudi, Riyal).</p>
                                    @error('quotation_currencies')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                    @error('quotation_currencies.*')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        {{-- Quotation Number Section --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Quotation Number Format
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Customize how quotation numbers are
                                generated for this company.</p>

                            <div class="grid grid-cols-1 gap-6">
                                {{-- Quotation Prefix --}}
                                <div>
                                    <label for="quotation_prefix"
                                        class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Quotation
                                        Prefix</label>
                                    <input type="text" name="quotation_prefix" id="quotation_prefix"
                                        value="{{ old('quotation_prefix', $settings['quotation_prefix']) }}"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5 px-3"
                                        placeholder="QTN-" maxlength="20">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used when the <code
                                            class="text-xs bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded">{PREFIX}</code>
                                        tag is in your format pattern.</p>
                                    @error('quotation_prefix')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Quotation Number Format --}}
                                <div>
                                    <label for="quotation_number_format"
                                        class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Number
                                        Format Pattern <span class="text-red-500">*</span></label>
                                    <input type="text" name="quotation_number_format" id="quotation_number_format"
                                        value="{{ old('quotation_number_format', $settings['quotation_number_format']) }}"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white font-mono py-2.5 px-3"
                                        placeholder="{CUSTOMER_NO}-{YY}-{SEQUENCE}" maxlength="100" required
                                        oninput="updatePreview()">
                                    @error('quotation_number_format')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Available Tags --}}
                                <div
                                    class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-5 border border-gray-100 dark:border-gray-600">
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Available
                                        Tags</h3>
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                        <div class="flex items-center gap-2 text-xs">
                                            <button type="button" data-tag="{CUSTOMER_NO}"
                                                class="bg-white dark:bg-gray-800 px-2 py-1 rounded border border-gray-200 dark:border-gray-600 font-mono text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">{CUSTOMER_NO}</button>
                                            <span class="text-gray-500">Customer code</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs">
                                            <button type="button" data-tag="{SEQUENCE}"
                                                class="bg-white dark:bg-gray-800 px-2 py-1 rounded border border-gray-200 dark:border-gray-600 font-mono text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">{SEQUENCE}</button>
                                            <span class="text-gray-500">Auto-increment</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs">
                                            <button type="button" data-tag="{PREFIX}"
                                                class="bg-white dark:bg-gray-800 px-2 py-1 rounded border border-gray-200 dark:border-gray-600 font-mono text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">{PREFIX}</button>
                                            <span class="text-gray-500">Your prefix</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs">
                                            <button type="button" data-tag="{YYYY}"
                                                class="bg-white dark:bg-gray-800 px-2 py-1 rounded border border-gray-200 dark:border-gray-600 font-mono text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">{YYYY}</button>
                                            <span class="text-gray-500">Full year</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs">
                                            <button type="button" data-tag="{YY}"
                                                class="bg-white dark:bg-gray-800 px-2 py-1 rounded border border-gray-200 dark:border-gray-600 font-mono text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">{YY}</button>
                                            <span class="text-gray-500">Short year</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs">
                                            <button type="button" data-tag="{MM}"
                                                class="bg-white dark:bg-gray-800 px-2 py-1 rounded border border-gray-200 dark:border-gray-600 font-mono text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">{MM}</button>
                                            <span class="text-gray-500">Month</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs">
                                            <button type="button" data-tag="{DD}"
                                                class="bg-white dark:bg-gray-800 px-2 py-1 rounded border border-gray-200 dark:border-gray-600 font-mono text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">{DD}</button>
                                            <span class="text-gray-500">Day</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs">
                                            <button type="button" data-tag="{ID}"
                                                class="bg-white dark:bg-gray-800 px-2 py-1 rounded border border-gray-200 dark:border-gray-600 font-mono text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">{ID}</button>
                                            <span class="text-gray-500">Total count</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Preview --}}
                                <div
                                    class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-5 border border-blue-100 dark:border-blue-800">
                                    <h3 class="text-sm font-semibold text-blue-700 dark:text-blue-300 mb-2">Live Preview
                                    </h3>
                                    <p class="text-lg font-mono font-bold text-blue-900 dark:text-blue-200"
                                        id="format-preview">—</p>
                                    <p class="text-xs text-blue-500 dark:text-blue-400 mt-1">Example with customer <code
                                            class="font-mono">ACME</code>, sequence <code class="font-mono">001</code>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        @php
                            $vatPercentages = old('vat_percentages', $settings['vat_percentages'] ?? [0, 5, 10, 15]);
                            $vatDefaultFallback = (is_array($vatPercentages) && count($vatPercentages))
                                ? $vatPercentages[array_key_last($vatPercentages)]
                                : 0;
                            $vatDefaultValue = old('vat_default_percentage', $settings['vat_default_percentage'] ?? $vatDefaultFallback);
                        @endphp

                        <div>
                            <div class="flex items-center justify-between gap-4 mb-3">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">VAT Settings</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Configure VAT percentages and default rate for quotations.
                                    </p>
                                </div>
                                <button type="button" id="add-vat-percentage"
                                    class="px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-300 transition-colors">
                                    Add VAT
                                </button>
                            </div>
                            
                            <!-- VAT Percentages Section -->
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Available VAT Percentages <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex flex-wrap gap-2" id="vat-percentages-list">
                                @foreach($vatPercentages as $percentage)
                                    <div
                                        class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1">
                                        <input type="number" name="vat_percentages[]" min="0" max="100" step="0.01"
                                            value="{{ $percentage }}"
                                            class="w-20 bg-transparent border-0 p-0 text-sm focus:ring-0 dark:text-white"
                                            placeholder="0" required>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">%</span>
                                        <button type="button"
                                            class="h-6 w-6 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 vat-remove">
                                            ×
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            @error('vat_percentages')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            @error('vat_percentages.*')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            
                            <!-- Default VAT Percentage Section -->
                            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                                <div class="flex items-start gap-2 mb-3">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <label for="vat-default-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Default VAT Percentage <span class="text-red-500">*</span>
                                        </label>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            This VAT rate will be automatically selected when creating new quotations.
                                        </p>
                                    </div>
                                </div>
                                <select id="vat-default-select" name="vat_default_percentage"
                                    class="w-full md:w-48 py-2 pl-3 pr-10 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:text-white shadow-sm transition-all">
                                    @foreach($vatPercentages as $percentage)
                                        <option value="{{ $percentage }}" @selected((string) $percentage === (string) $vatDefaultValue)>
                                            {{ $percentage }}%
                                        </option>
                                    @endforeach
                                </select>
                                @error('vat_default_percentage')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="bg-gray-50 dark:bg-gray-700/30 px-8 py-5 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('tenant.user-companies.index') }}"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors shadow-sm">
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-colors">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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
