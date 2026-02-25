<x-ui.card title="Pricing & Totals">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 p-2">
        <!-- Left Column: Terms & Conditions -->
        <div class="lg:col-span-7 order-2 lg:order-1 h-full flex flex-col">
            <label class="block mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                Terms & Conditions
            </label>
            <textarea
                id="text-area"
                name="quotation_revision[terms_conditions]"
                x-model="quotation_revision.terms_conditions"
                class="flex-1 w-full p-4 text-sm text-gray-900 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:placeholder-gray-400 dark:text-white transition-all duration-200 resize-none shadow-sm"
                placeholder="Enter terms and conditions..."
                rows="8"
            ></textarea>
        </div>

        <!-- Right Column: Totals Summary -->
        <div class="lg:col-span-5 order-1 lg:order-2">
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-6 border border-gray-100 dark:border-gray-700 space-y-5">
                
                <!-- Currency Alert for Via -->
                <div x-show="quotation_revision.type === 'via' && quotation_revision.currency" 
                     x-transition
                     class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg text-sm flex items-start gap-2 border border-blue-100 dark:border-blue-800">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <p class="font-medium">Currency: <span x-text="quotation_revision.currency"></span></p>
                        <p class="text-xs opacity-90 mt-0.5">No VAT applied for foreign currency.</p>
                    </div>
                </div>

                <!-- Subtotal -->
                <div class="flex justify-between items-center text-sm group">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">Subtotal</span>
                    <div class="text-right">
                         <input type="hidden" name="quotation_revision[subtotal]" x-model="quotation_revision.subtotal">
                         <span class="font-semibold text-gray-900 dark:text-white text-base" x-text="quotation_revision.subtotal"></span>
                         <span class="text-xs text-gray-500 ml-1" x-show="quotation_revision.type === 'via'" x-text="quotation_revision.currency"></span>
                         <span class="text-xs text-gray-500 ml-1" x-show="quotation_revision.type !== 'via'">BDT</span>
                         
                         <!-- BDT Equivalent for Via -->
                         <div x-show="quotation_revision.type === 'via' && quotation_revision.currency && quotation_revision.currency !== 'BDT'"
                              class="text-xs text-gray-500 mt-0.5">
                             ≈ <span x-text="(quotation_revision.bdt_subtotal || 0).toFixed(2)"></span> BDT
                         </div>
                    </div>
                </div>

                <!-- Discount -->
                <div class="flex justify-between items-start text-sm pt-1">
                    <span class="text-gray-600 dark:text-gray-400 font-medium mt-2">Discount</span>
                    <div class="flex gap-2 w-48 justify-end">
                        <div class="relative w-20">
                            <input type="number" step="0.01" 
                                x-model.number="quotation_revision.discount_percentage"
                                name="quotation_revision[discount_percentage]" 
                                placeholder="0"
                                class="w-full pl-2 pr-6 py-1.5 text-right text-sm bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:text-white shadow-sm transition-all"
                                @input="calculateDiscountAmount()" 
                            />
                            <span class="absolute right-2 top-1.5 text-gray-400 text-xs font-medium">%</span>
                        </div>
                        <div class="relative flex-1">
                             <input type="number" step="0.01" 
                                x-model.number="quotation_revision.discount"
                                name="quotation_revision[discount]" 
                                placeholder="0.00"
                                class="w-full px-2 py-1.5 text-right text-sm bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:text-white shadow-sm transition-all"
                                @input="calculateDiscountPercentage()" 
                            />
                        </div>
                    </div>
                </div>

                <!-- Discounted Subtotal -->
                <div class="flex justify-between items-center text-sm pt-1" x-show="quotation_revision.discount > 0" x-transition>
                    <span class="text-gray-600 dark:text-gray-400 font-medium">Discounted Subtotal</span>
                    <div class="text-right">
                         <input type="hidden" name="quotation_revision[discounted_price]" x-model="quotation_revision.discounted_price">
                         <span class="font-semibold text-gray-900 dark:text-white" x-text="quotation_revision.discounted_price"></span>
                    </div>
                </div>

                <!-- Shipping -->
                <div class="flex justify-between items-center text-sm pt-1">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">Shipping</span>
                    <div class="w-32">
                        <input type="number" step="0.01" 
                               x-model.number="quotation_revision.shipping"
                               name="quotation_revision[shipping]" 
                               class="w-full py-1.5 px-2 text-right text-sm bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:text-white shadow-sm transition-all"
                               placeholder="0.00"
                               @input="calculateTotals()" />
                    </div>
                </div>

                <!-- VAT (Normal only) -->
                <template x-if="quotation_revision.type === 'normal'">
                    <div class="flex justify-between items-center text-sm pt-1">
                        <span class="text-gray-600 dark:text-gray-400 font-medium">VAT</span>
                        <div class="flex items-center gap-3 justify-end w-48">
                            <select 
                                name="quotation_revision[vat_percentage]" 
                                x-model.number="quotation_revision.vat_percentage" 
                                class="w-20 py-1.5 pl-2 pr-6 text-sm bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 dark:text-white shadow-sm transition-all"
                                @change="calculateTotals()">
                                <template x-for="percentage in vatPercentages" :key="percentage">
                                    <option :value="percentage" x-text="`${percentage}%`"></option>
                                </template>
                            </select>
                            <div class="text-right flex-1">
                                <input type="hidden" name="quotation_revision[vat_amount]" x-model="quotation_revision.vat_amount">
                                <span class="font-medium text-gray-700 dark:text-gray-300" x-text="quotation_revision.vat_amount"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>

                <!-- Grand Total -->
                <div class="flex justify-between items-center">
                    <span class="text-base font-bold text-gray-900 dark:text-white">Grand Total</span>
                    <div class="text-right">
                         <input type="hidden" name="quotation_revision[total]" x-model="quotation_revision.total">
                         <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 tracking-tight">
                             <span x-text="quotation_revision.total"></span>
                             <span class="text-base font-medium text-gray-500 ml-1" x-show="quotation_revision.type === 'via'" x-text="quotation_revision.currency"></span>
                             <span class="text-base font-medium text-gray-500 ml-1" x-show="quotation_revision.type !== 'via'">BDT</span>
                         </div>
                         <!-- BDT Equivalent for Via -->
                         <div x-show="quotation_revision.type === 'via' && quotation_revision.currency && quotation_revision.currency !== 'BDT'"
                              class="text-sm font-medium text-gray-500 mt-1">
                             ≈ <span x-text="(quotation_revision.bdt_total || 0).toFixed(2)"></span> BDT
                         </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-ui.card>
