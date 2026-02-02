<x-ui.card title="Pricing & Totals">
    <div class="mx-2 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-4 sm:gap-5 lg:gap-6">
            <div>
                <div class="mb-2">
                    <label class="block text-base md:text-sm font-medium text-gray-900 dark:text-white mb-1.5">
                        <span x-show="quotation_revision.type !== 'via' || !quotation_revision.currency || quotation_revision.currency === 'BDT'">Subtotal (BDT)</span>
                        <span x-show="quotation_revision.type === 'via' && quotation_revision.currency && quotation_revision.currency !== 'BDT'">
                            Subtotal (<span x-text="quotation_revision.currency"></span>)
                        </span>
                    </label>
                    <input type="text" name="quotation_revision[subtotal]" x-model="quotation_revision.subtotal"
                           class="w-full px-4 py-3 md:py-2.5 text-base md:text-sm bg-gray-100 dark:bg-gray-700 cursor-not-allowed border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white min-h-[44px]"
                           readonly />
                </div>
                <!-- BDT Subtotal for Via Quotations (when foreign currency is selected) -->
                <div x-show="quotation_revision.type === 'via' && quotation_revision.currency && quotation_revision.currency !== 'BDT'"
                     class="mt-2">
                    <label class="block text-sm md:text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Subtotal (BDT)
                    </label>
                    <div class="px-4 py-3 md:py-2.5 text-base md:text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg font-medium text-gray-700 dark:text-gray-300"
                         x-text="(quotation_revision.bdt_subtotal || 0).toFixed(2)"></div>
                </div>
            </div>

            <div >
                <label class="block mb-2 text-base md:text-sm font-medium text-gray-900 dark:text-white">
                    Discount
                </label>
                <div class="flex gap-3 md:gap-4">
                    <input type="number" step="0.01" x-model.number="quotation_revision.discount"
                        name="quotation_revision[discount]" placeholder="Amount"
                        class="flex-1 min-w-0 px-4 py-3 md:py-2.5 text-base md:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white min-h-[44px]"
                        @input="calculateDiscountPercentage()" />
                    <input type="number" step="0.01" x-model.number="quotation_revision.discount_percentage"
                        name="quotation_revision[discount_percentage]" placeholder="%"
                        class="w-28 md:w-24 px-4 py-3 md:py-2.5 text-base md:text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white min-h-[44px]"
                        @input="calculateDiscountAmount()" />
                </div>
            </div>

            <div>
                <div class="mb-2">
                    <label class="block text-base md:text-sm font-medium text-gray-900 dark:text-white mb-1.5">
                        <span x-show="quotation_revision.type !== 'via' || !quotation_revision.currency || quotation_revision.currency === 'BDT'">Discounted Subtotal (BDT)</span>
                        <span x-show="quotation_revision.type === 'via' && quotation_revision.currency && quotation_revision.currency !== 'BDT'">
                            Discounted Subtotal (<span x-text="quotation_revision.currency"></span>)
                        </span>
                    </label>
                    <input type="text" name="quotation_revision[discounted_price]" x-model="quotation_revision.discounted_price"
                           class="w-full px-4 py-3 md:py-2.5 text-base md:text-sm bg-blue-50 dark:bg-blue-900/20 font-semibold cursor-not-allowed border border-blue-300 dark:border-blue-600 rounded-lg text-blue-800 dark:text-blue-200 min-h-[44px]"
                           readonly />
                </div>
            </div>

                        <!-- Shipping Cost -->
            <div>
                <div class="mb-2">
                    <label class="block text-base md:text-sm font-medium text-gray-900 dark:text-white mb-1.5">
                        <span x-show="quotation_revision.type !== 'via' || !quotation_revision.currency || quotation_revision.currency === 'BDT'">Shipping Cost (BDT)</span>
                        <span x-show="quotation_revision.type === 'via' && quotation_revision.currency && quotation_revision.currency !== 'BDT'">
                            Shipping Cost (<span x-text="quotation_revision.currency"></span>)
                        </span>
                    </label>
                    <input type="number" step="0.01" x-model.number="quotation_revision.shipping"
                           name="quotation_revision[shipping]" placeholder="0.00"
                           class="w-full px-4 py-3 md:py-2.5 text-base md:text-sm border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:bg-gray-600 dark:text-white min-h-[44px]"
                           @input="calculateTotals()" />
                </div>
            </div>

            <div class="flex gap-3 md:gap-4">
                <template x-if="quotation_revision.type === 'normal'">
                    <div>
                        <x-ui.form.simple-select name="quotation_revision[vat_percentage]" label="VAT (%)"
                            x-model.number="quotation_revision.vat_percentage" class="w-full px-4 py-3 md:py-2.5 text-base md:text-sm min-h-[44px]"
                            value="{{ isset($loadRevision) ? $loadRevision->vat_percentage : 15 }}"
                            @change="calculateTotals()" >
                            <option value="0">0%</option>
                            <option value="5">5%</option>
                            <option value="10">10%</option>
                            <option value="15">15%</option>
                        </x-ui.form.simple-select>
                    </div>
                </template>

                <template x-if="quotation_revision.type === 'normal'">
                    <div>
                        <x-ui.form.input name="quotation_revision[vat_amount]" label="VAT Amount"
                            x-model="quotation_revision.vat_amount"
                            class="w-full px-4 py-3 md:py-2.5 text-base md:text-sm bg-yellow-50 dark:bg-yellow-900/20 font-semibold cursor-not-allowed min-h-[44px]"
                            readonly />
                    </div>
                </template>
            </div>
            <!-- VAT Section (Normal Quotations Only) -->



            <div>
                <div class="mb-2">
                    <label class="block text-base md:text-sm font-medium text-gray-900 dark:text-white mb-1.5">
                        <span x-show="quotation_revision.type !== 'via' || !quotation_revision.currency || quotation_revision.currency === 'BDT'">Grand Total (BDT)</span>
                        <span x-show="quotation_revision.type === 'via' && quotation_revision.currency && quotation_revision.currency !== 'BDT'">
                            Grand Total (<span x-text="quotation_revision.currency"></span>)
                        </span>
                    </label>
                    <input type="text" name="quotation_revision[total]" x-model="quotation_revision.total"
                           class="w-full px-4 py-3 md:py-2.5 text-base md:text-sm bg-green-50 dark:bg-green-900/20 font-bold cursor-not-allowed border border-green-300 dark:border-green-600 rounded-lg text-green-800 dark:text-green-200 min-h-[44px]"
                           readonly />
                </div>
                <!-- BDT Grand Total for Via Quotations (when foreign currency is selected) -->
                <div x-show="quotation_revision.type === 'via' && quotation_revision.currency && quotation_revision.currency !== 'BDT'"
                     class="mt-2">
                    <label class="block text-sm md:text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Grand Total (BDT)
                    </label>
                    <div class="px-4 py-3 md:py-2.5 text-base md:text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg font-bold text-gray-700 dark:text-gray-300"
                         x-text="(quotation_revision.bdt_total || 0).toFixed(2)"></div>
                </div>
            </div>
        </div>

        <!-- Currency Display for Via Quotations -->
        <div x-show="quotation_revision.type === 'via' && quotation_revision.currency"
             class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4" x-transition>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <span class="text-base md:text-sm font-medium text-blue-800 dark:text-blue-200">
                        Via Quotation - All amounts in <span x-text="quotation_revision.currency" class="font-bold"></span>
                    </span>
                </div>
                <div class="text-base md:text-sm text-blue-600 dark:text-blue-400">
                    No VAT applied for foreign currency quotations
                </div>
            </div>
        </div>

        <div class="mt-4 md:mt-6">
            <label class="block mb-2 text-base md:text-sm font-medium text-gray-900 dark:text-white">
                Terms & Conditions
            </label>

            <textarea id="text-area" rows="4" name="quotation_revision[terms_conditions]"
                x-model="quotation_revision.terms_conditions"
                class="block p-4 w-full text-base md:text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:text-white min-h-[140px]"
                placeholder="Enter terms and conditions..."></textarea>
        </div>
    </div>
</x-ui.card>
