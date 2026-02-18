<x-ui.card heading="Products">
    <div class="p-2">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-xl font-bold dark:text-white">Product Items</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <span x-text="quotation_products.length"></span> item(s) added
                </p>
            </div>
        </div>

        <div class="space-y-4">
            <template x-for="(row, index) in quotation_products" :key="index">
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow"
                    x-bind:data-row-index="index" x-bind:id="'product-' + index">

                    {{-- Product Header --}}
                    <div class="flex justify-between items-start p-4 pb-2">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Product #<span x-text="index + 1"></span>
                        </h3>
                        <div class="flex items-center gap-2">
                            <button type="button" class="text-red-500 hover:text-red-700 p-1" @click="removeRow(index)"
                                x-show="quotation_products.length > 1" title="Remove product">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden Product ID (only in edit mode) -->

                    <template x-if="row.id">
                        <input type="hidden" :name="'quotation_products[' + index + '][id]'" :value="row.id">
                    </template>

                    {{-- Product Base Information Section --}}
                    <div class="px-4 pb-3">
                        <div class="mb-3 flex items-center gap-2">
                             <div class="w-1 h-5 bg-blue-500 rounded-full"></div>
                             <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">Product Information</h4>
                        </div>

                        <div class="grid grid-cols-12 gap-4 border border-gray-200 dark:border-gray-700 p-4 rounded-xl bg-white dark:bg-gray-800 shadow-sm">
                            {{-- 1. Product Selection --}}
                            <div class="col-span-12 md:col-span-4">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                    Product <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <div class="flex-1">
                                        <x-ui.form.searchable-select
                                            x-bind:name="'quotation_products[' + index + '][product_id]'"
                                            x-model="row.product_id" apiEndpoint="{{ route('tenant.products.search') }}"
                                            displayTemplate="{name}" :searchFields="['name']" placeholder="Search products..."
                                            noResultsText="No products found." class="w-full text-sm" :showImages="true"
                                            imageField="image" imagePath="path" :perPage="20"
                                            @option-selected="handleProductSelection($event, index)" />
                                    </div>
                                    <a href="{{ route('tenant.products.create') }}" target="_blank"
                                        class="flex-shrink-0 px-3 py-2 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 dark:text-gray-300 rounded-lg transition-all duration-200 flex items-center justify-center shadow-sm"
                                        title="Create new product">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            {{-- 2. Size --}}
                            <div class="col-span-12 md:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                    Size
                                </label>
                                <input type="text" :name="'quotation_products[' + index + '][size]'"
                                    x-model="row.size" placeholder="Size"
                                    class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors" />
                            </div>

                            {{-- 3. Specification --}}
                            <div class="col-span-12 md:col-span-3">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                    Specification
                                </label>
                                <div class="relative">
                                    <button type="button" @click="openSpecificationModal(index)"
                                        class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-left flex items-center justify-between hover:bg-white dark:hover:bg-gray-600 transition-colors"
                                        :class="row.specification_id ? 'text-gray-900 dark:text-white' :
                                            'text-gray-400'"
                                        :title="getSelectedSpecificationText(row) || 'Select specification'">
                                        <span class="truncate flex-1"
                                            x-html="getSelectedSpecificationSummary(row)"></span>
                                        <svg class="w-4 h-4 ml-2 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <input type="hidden" :name="'quotation_products[' + index + '][specification_id]'"
                                        :value="row.specification_id" />

                                    <div x-show="row.specifications && row.specifications.length > 0"
                                        class="absolute -top-2 -right-2 bg-blue-500 text-white text-[10px] rounded-full h-5 w-5 flex items-center justify-center border-2 border-white dark:border-gray-800 shadow-sm">
                                        <span x-text="row.specifications ? row.specifications.length : 0"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. Brand/Origin --}}
                            <div class="col-span-12 md:col-span-3">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                    Brand/Origin
                                </label>
                                <div x-data="brandOriginSearchableSelect({
                                    value: row.brand_origin_id,
                                    endpoint: '{{ route('tenant.brand-origins.search') }}'
                                })" x-init="init" class="relative"
                                    @click.away="open = false">

                                    {{-- Hidden input for form submission --}}
                                    <input type="hidden"
                                        :name="'quotation_products[' + index + '][brand_origin_id]'"
                                        x-bind:value="selectedValue">

                                    {{-- Visible search input --}}
                                    <div class="relative">
                                        <input type="text" x-model="searchTerm" @focus="open = true"
                                            @input.debounce.300ms="filterOptions" placeholder="Search Brand..."
                                            class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors"
                                            required>
                                        {{-- Dropdown Arrow --}}
                                        <div
                                            class="absolute inset-y-0 end-0 flex items-center pe-3 pointer-events-none">
                                            <svg class="w-4 h-4 text-gray-400"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 20 20">
                                                <path stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2"
                                                    d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 transform scale-95"
                                        x-transition:enter-end="opacity-100 transform scale-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 transform scale-100"
                                        x-transition:leave-end="opacity-0 transform scale-95"
                                        class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-xl border-2 border-gray-200 dark:border-gray-600 overflow-hidden"
                                        style="display: none;">

                                        {{-- Scrollable container with max height --}}
                                        <div class="max-h-60 overflow-y-auto relative">
                                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                                                <template x-if="loading">
                                                    <li
                                                        class="px-4 py-3 text-gray-500 flex items-center gap-2">
                                                        <svg class="w-4 h-4 animate-spin" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12"
                                                                cy="12" r="10" stroke="currentColor"
                                                                stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                            </path>
                                                        </svg>
                                                        Loading brand origins...
                                                    </li>
                                                </template>
                                                <template x-if="!loading && filteredOptions.length === 0">
                                                    <li class="px-4 py-3 text-gray-500">
                                                        No brand origins found.
                                                        <button type="button" @click="openModal"
                                                            class="text-green-600 hover:text-green-800 font-medium hover:underline">Add
                                                            new brand origin</button>
                                                    </li>
                                                </template>
                                                <template x-for="(option, index) in filteredOptions"
                                                    :key="option.id">
                                                    <li
                                                        class="px-4 py-3 cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/20 border-b border-gray-100 dark:border-gray-700 last:border-b-0 transition-colors duration-200 group">
                                                        <div class="flex items-center justify-between">
                                                            <div @click="selectOption(option)" class="flex-1">
                                                                <div class="font-medium text-gray-900 dark:text-white"
                                                                    x-text="option.name">
                                                                </div>
                                                                <div class="text-xs text-gray-500 dark:text-gray-400"
                                                                    x-text="option.address"
                                                                    x-show="option.address">
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                                <!-- Edit Button -->
                                                                <button type="button"
                                                                    @click="editBrandOrigin(option)"
                                                                    class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors duration-200"
                                                                    title="Edit company">
                                                                    <svg class="w-4 h-4" fill="none"
                                                                        stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                        </path>
                                                                    </svg>
                                                                </button>
                                                                <!-- Delete Button -->
                                                                <button type="button"
                                                                    @click="deleteBrandOrigin(option)"
                                                                    class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors duration-200"
                                                                    title="Delete company">
                                                                    <svg class="w-4 h-4" fill="none"
                                                                        stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                        </path>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </template>

                                                {{-- Spacer to make room for fixed button when there are more than 3 items --}}
                                                <template x-if="filteredOptions.length > 3">
                                                    <li class="h-14"></li>
                                                </template>
                                            </ul>
                                        </div>

                                        {{-- Fixed Create Brand Origin Button - appears after 3 records --}}
                                        <div x-show="!loading && filteredOptions.length > 3"
                                            class="absolute bottom-0 left-0 right-0 border-t-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg">
                                            <button type="button"
                                                @click="$dispatch('open-brand-origin-modal')"
                                                class="w-full px-4 py-3 text-left text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-800 font-medium transition-colors duration-200 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Add new brand origin
                                            </button>
                                        </div>

                                        {{-- Regular Create Brand Origin Button - shows when 3 or fewer items --}}
                                        <div x-show="!loading && filteredOptions.length <= 3 && filteredOptions.length > 0"
                                            class="border-t-2 border-gray-200 dark:border-gray-600">
                                            <button type="button"
                                                @click="$dispatch('open-brand-origin-modal')"
                                                class="w-full px-4 py-3 text-left text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-800 font-medium transition-colors duration-200 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Add new brand origin
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Add this additional CSS at the bottom of your file if not already present --}}

                                </div>
                                @error('brand_origin_id')
                                    <p class="text-xs text-red-500 font-semibold mt-1 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- 4. Additional Specification --}}
                            <div class="col-span-12 md:col-span-3">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                    Additional Specification
                                </label>
                                <input type="text" :name="'quotation_products[' + index + '][add_spec]'"
                                    x-model="row.add_spec" placeholder="Additional specification details..."
                                    class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors" />
                            </div>

                            {{-- 5. Delivery Time --}}
                            <div class="col-span-12 md:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                    Delivery Time
                                </label>
                                <input type="text" :name="'quotation_products[' + index + '][delivery_time]'"
                                    x-model="row.delivery_time" placeholder="e.g., 3-4 weeks"
                                    class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors" />
                            </div>

                            {{-- 6. Unit --}}
                            <div class="col-span-12 md:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                    Unit
                                </label>
                                <input type="text" :name="'quotation_products[' + index + '][unit]'"
                                    x-model="row.unit" placeholder="Unit"
                                    class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors" />
                            </div>

                            {{-- 7. Quantity --}}
                            <div class="col-span-12 md:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                    Quantity
                                </label>
                                <input type="number" :name="'quotation_products[' + index + '][quantity]'"
                                    x-model.number="row.quantity" placeholder="1" min="1"
                                    class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors"
                                    @input="onQuantityChange(index)" />
                            </div>

                            <div class="col-span-12 md:col-span-3">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                    Requ/PR No.
                                </label>
                                <input type="text" :name="'quotation_products[' + index + '][requision_no]'"
                                    x-model.number="row.requision_no" placeholder=""
                                    class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors"
                                />
                            </div>

                        </div>
                    </div>

                    {{-- Price Calculation Section --}}
                    @php
                        $hasPriceCalculator = auth()->user()->currentCompany->hasFeature('module_price_calculator');
                        $readonlyAttr = $hasPriceCalculator ? '' : 'readonly';
                        $disabledClass = $hasPriceCalculator ? '' : 'bg-gray-100 dark:bg-gray-700 cursor-not-allowed opacity-60';
                    @endphp

                    <div class="px-4 pb-4" x-data="{ showAdvanced: false }">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-1 h-5 bg-green-500 rounded-full"></div>
                                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">
                                    Price Calculation
                                </h4>
                                @unless($hasPriceCalculator)
                                    <span class="ml-2 text-[10px] font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-700">
                                        Free Plan
                                    </span>
                                @endunless
                            </div>

                            <button type="button" @click="showAdvanced = !showAdvanced"
                                class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 flex items-center gap-1 transition-colors">
                                <span x-text="showAdvanced ? 'Hide Advanced Calculator' : 'Advanced Price Calculation'"></span>
                                <svg class="w-3 h-3 transition-transform duration-200" :class="showAdvanced ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>

                        {{-- Basic Price Fields (Always Visible) --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            {{-- Foreign Currency Buying --}}
                            <div class="relative">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                    <span x-text="getForeignCurrencyLabel()"></span>
                                </label>
                                <div class="relative">
                                    <input type="number" step="0.0001"
                                        :name="'quotation_products[' + index + '][foreign_currency_buying]'"
                                        x-model.number="row.foreign_currency_buying" placeholder="0.0000"
                                        class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors"
                                        @input="calculateForeignCurrencyEquivalent(index); calculateTotals()" />
                                    <div x-show="quotation_revision.currency && quotation_revision.currency !== 'BDT'"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">
                                        <span x-text="quotation_revision.currency"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- BDT Equivalent --}}
                            <div class="relative">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                    BDT Equivalent
                                </label>
                                <div class="relative">
                                    <input type="number" step="0.01"
                                        :name="'quotation_products[' + index + '][bdt_buying]'"
                                        x-model.number="row.bdt_buying" placeholder="0.00"
                                        class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors"
                                        @input="calculateBdtToForeignEquivalent(index); calculateTotals()" />
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">
                                        BDT
                                    </div>
                                </div>
                            </div>

                            {{-- Final Unit Price --}}
                            <div class="relative">
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                    Final Unit Price
                                </label>
                                <div class="relative">
                                    <input type="number" step="0.01"
                                        :name="'quotation_products[' + index + '][unit_price]'"
                                        @if($hasPriceCalculator)
                                            x-bind:value="format2(row.unit_price)"
                                            readonly
                                        @else
                                            x-model.number="row.unit_price"
                                        @endif
                                        placeholder="0.00"
                                        class="w-full px-3 py-2 text-sm bg-green-50 border border-green-200 rounded-lg focus:ring-1 focus:ring-green-500 focus:border-green-500 dark:bg-green-900/20 dark:border-green-800 dark:text-green-100 font-semibold text-green-700 transition-colors"
                                        @input="calculateTotals()" />
                                </div>
                            </div>
                        </div>

                        {{-- Advanced Fields (Collapsible) --}}
                        <div x-show="showAdvanced"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 p-4 mb-4">

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                {{-- Weight Field --}}
                                <div class="{{ $disabledClass }}">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                        Weight (kg)
                                    </label>
                                    <input type="number" step="0.01"
                                        :name="'quotation_products[' + index + '][weight]'" x-model.number="row.weight"
                                        placeholder="0.00"
                                        class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors {{ $disabledClass }}"
                                        @input="calculateAirSeaFreight(index); calculateTotals()" {{ $readonlyAttr }} />
                                </div>

                                {{-- Air/Sea Freight Rate --}}
                                <div class="{{ $disabledClass }}">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                        Freight Rate
                                        <span x-show="quotation_revision.type === 'via'" class="normal-case">
                                            (<span x-text="getForeignCurrencyLabel()"></span>)
                                        </span>
                                    </label>
                                    <input type="number" step="0.01"
                                        :name="'quotation_products[' + index + '][air_sea_freight_rate]'"
                                        x-model.number="row.air_sea_freight_rate" placeholder="0.00"
                                        class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors {{ $disabledClass }}"
                                        @input="calculateAirSeaFreight(index); calculateTotals()" {{ $readonlyAttr }} />
                                </div>

                                {{-- Air/Sea Freight Total --}}
                                <div class="relative {{ $disabledClass }}">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                        Freight Total
                                    </label>
                                    <input type="number" step="0.01"
                                        :name="'quotation_products[' + index + '][air_sea_freight]'"
                                        x-bind:value="format2(row.air_sea_freight)" placeholder="0.00"
                                        class="w-full px-3 py-2 text-sm bg-gray-100 border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white transition-colors {{ $disabledClass }}"
                                        readonly />
                                </div>

                                {{-- Tax Percentage --}}
                                <div class="{{ $disabledClass }}">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                        Tax (%) / VAT
                                    </label>
                                    <input type="number" step="0.01"
                                        :name="'quotation_products[' + index + '][tax_percentage]'"
                                        x-model.number="row.tax_percentage" placeholder="0.00"
                                        class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors {{ $disabledClass }}"
                                        @input="calculateTaxAmount(index); calculateTotals()" {{ $readonlyAttr }} />
                                </div>

                                {{-- Tax Amount --}}
                                <div class="relative {{ $disabledClass }}">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                        Tax Amount
                                    </label>
                                    <input type="number" step="0.01" :name="'quotation_products[' + index + '][tax]'"
                                        x-model.number="row.tax" placeholder="0.00"
                                        class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors {{ $disabledClass }}"
                                        @input="calculateTaxPercentage(index); calculateUnitPrice(index); calculateTotals()" {{ $readonlyAttr }} />
                                </div>

                                {{-- AIT Percentage --}}
                                <div class="{{ $disabledClass }}">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                        AIT (%)
                                    </label>
                                    <input type="number" step="0.01"
                                        :name="'quotation_products[' + index + '][att_percentage]'"
                                        x-model.number="row.att_percentage" placeholder="0.00"
                                        class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors {{ $disabledClass }}"
                                        @input="calculateAttAmount(index); calculateTotals()" {{ $readonlyAttr }} />
                                </div>

                                {{-- AIT Amount --}}
                                <div class="relative {{ $disabledClass }}">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                        AIT Amount
                                    </label>
                                    <input type="number" step="0.01" :name="'quotation_products[' + index + '][att]'"
                                        x-model.number="row.att" placeholder="0.00"
                                        class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors {{ $disabledClass }}"
                                        @input="calculateAttPercentage(index); calculateUnitPrice(index); calculateTotals()" {{ $readonlyAttr }} />
                                </div>

                                {{-- Margin Percentage --}}
                                <div class="{{ $disabledClass }}">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                        Margin (%)
                                    </label>
                                    <input type="number" step="0.01"
                                        :name="'quotation_products[' + index + '][margin]'" x-model.number="row.margin"
                                        placeholder="0.00"
                                        class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-colors {{ $disabledClass }}"
                                        @input="calculateMarginValue(index); calculateTotals()" {{ $readonlyAttr }} />
                                </div>

                                {{-- Margin Value --}}
                                <div class="relative {{ $disabledClass }}">
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">
                                        Margin Value
                                    </label>
                                    <input type="number" step="0.01"
                                        :name="'quotation_products[' + index + '][margin_value]'"
                                        x-model.number="row.margin_value" placeholder="0.00"
                                        class="w-full px-3 py-2 text-sm bg-gray-100 border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white transition-colors {{ $disabledClass }}"
                                        @input="calculateMarginPercentage(index); calculateTotals()" {{ $readonlyAttr }} />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Line Total Display --}}
                    <div class="px-4 pb-4">
                        <div
                            class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Line Total</span>
                                <div class="text-right">
                                    <!-- BDT Line Total (always shown) -->
                                    <div class="text-lg font-bold text-gray-800 dark:text-white"
                                        x-text="'৳ ' + (calculateLineTotal(row) || 0).toFixed(2)"></div>
                                    <!-- Foreign Currency Line Total (for Via quotations) -->
                                    <div x-show="quotation_revision.type === 'via' && quotation_revision.currency && quotation_revision.currency !== 'BDT'"
                                        class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5"
                                        x-text="getForeignCurrencyLineTotal(row)"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Empty State --}}
            <div x-show="quotation_products.length === 0" class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                    </path>
                </svg>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No products added yet</p>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-end">
                    <button type="button"
                        class="group px-5 py-2.5 text-sm font-semibold rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg hover:from-blue-500 hover:to-indigo-500 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-400 active:scale-95 transition-transform duration-200 ease-out"
                        @click="addRow()" aria-label="Add Product">
                        <svg class="w-5 h-5 inline-block mr-2 transition-transform duration-200 group-hover:rotate-180"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Product
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-ui.card>
