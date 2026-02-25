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
                                        @foreach($currencies as $code => $symbol)
                                            <option value="{{ $code }}" data-symbol="{{ $symbol }}" {{ old('currency', $settings['currency']) === $code ? 'selected' : '' }}>
                                                {{ $code }} ({{ $symbol }})
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
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">VAT Percentages</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Used in quotation VAT dropdown.</p>
                                </div>
                                <button type="button" id="add-vat-percentage"
                                    class="px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-300 transition-colors">
                                    Add
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-2" id="vat-percentages-list">
                                @foreach($vatPercentages as $percentage)
                                    <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1">
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
                            <div class="mt-4">
                                <label for="vat-default-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default VAT Percentage</label>
                                <select id="vat-default-select" name="vat_default_percentage"
                                    class="mt-1 w-40 py-1.5 pl-2 pr-8 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:text-white shadow-sm transition-all">
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

        function updateCurrencySymbol(select) {
            const option = select.options[select.selectedIndex];
            document.getElementById('currency_symbol').value = option.dataset.symbol || '';
            updatePreview();
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

            syncVatDefaultOptions();
        });
    </script>
</x-dashboard.layout.default>
