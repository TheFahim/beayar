<x-ui.card heading="Basic Information">
    <div class="mx-2 grid grid-cols-1 md:grid-cols-5 gap-2">
        <div>
            <x-ui.form.simple-select name="quotation_revision[type]" x-model="quotation_revision.type" label="Type"
                class="w-full px-1.5 text-xs" @change="onQuotationTypeChange()" required>
                <option value="normal">Normal Quotation</option>
                <option value="via">Import Quotation</option>
            </x-ui.form.simple-select>
            <div x-show="quotation_revision.type === 'via'" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                <span class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Foreign currency pricing required
                </span>
            </div>
        </div>

        <div>
            <x-ui.form.input type="text" x-model="quotation_revision.date" name="quotation_revision[date]" label="Date"
                placeholder="DD/MM/YYYY"
                class="quotation-datepicker w-full px-1.5 text-xs" @change="updateQuotationNumber()"
                required />
        </div>

        <div>
            <x-ui.form.input type="text" x-model="quotation_revision.validity" name="quotation_revision[validity]" label="Validity (Valid Until)"
                placeholder="DD/MM/YYYY"
                class="quotation-datepicker w-full px-1.5 text-xs" required />
            <div class="flex items-center justify-between gap-2 mt-1 text-xs text-gray-600 dark:text-gray-400">
                <label class="inline-flex items-center">
                    <input type="checkbox" x-model="autoCalculateValidity" class="rounded border-gray-300 mr-1">
                    <span >Auto: </span>
                </label>
                <div class="flex items-center gap-1">
                    <span>Days:</span>
                    <input type="number" min="0" x-model.number="validityDays" class="w-16 px-2 py-1 text-xs border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
            </div>
        </div>

        <div id="currency-section">
            <x-ui.form.simple-select x-model="quotation_revision.currency" name="quotation_revision[currency]"
                label="Currency" class="w-full px-1.5 text-xs" @change="onCurrencyChange()"
                x-bind:required="quotation_revision.type === 'via'">
                <option value="USD" x-bind:selected="quotation_revision.currency === 'USD'">USD</option>
                <option value="EUR">EURO</option>
                <option value="RMB">RMB</option>
                <option value="INR">INR</option>
                <option value="BDT" x-show="quotation_revision.type === 'normal'">BDT</option>
            </x-ui.form.simple-select>
            <div x-show="quotation_revision.type === 'via' && !quotation_revision.currency"
                 class="text-xs text-red-600 dark:text-red-400 mt-1">
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    Currency selection required for Via quotations
                </span>
            </div>
            <div x-show="quotation_revision.type === 'via' && quotation_revision.currency"
                 class="text-xs text-green-600 dark:text-green-400 mt-1">
                <span class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    All pricing will be in &nbsp;<span x-text="quotation_revision.currency" class="font-semibold"></span>
                </span>
            </div>
        </div>

        <div x-show="quotation_revision.type === 'normal'" x-transition>
            <x-ui.form.input x-model="quotation_revision.exchange_rate" name="quotation_revision[exchange_rate]"
                label="Exchange Rate (BDT)" placeholder="Ex. 121.50" class="w-full px-1.5 text-xs"
                type="number" step="0.01" />
            <div x-show="exchangeRateLoading" class="text-sm text-blue-600 mt-1">
                <span class="flex items-center">
                    <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Fetching rates...
                </span>
            </div>
            <div x-show="exchangeRateMessage" class="text-sm mt-1"
                 :class="exchangeRateMessage.includes('Failed') ? 'text-red-600' : 'text-gray-600'"
                 x-text="exchangeRateMessage"></div>
        </div>
    </div>

    <!-- Hidden field for saved_as parameter -->
    <input type="hidden" name="quotation_revision[saved_as]" x-model="quotation_revision.saved_as">
</x-ui.card>
