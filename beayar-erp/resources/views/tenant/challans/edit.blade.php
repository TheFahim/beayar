<x-dashboard.layout.default title="Edit Challan">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.challans.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Challans
            </a>
        </li>

        <x-dashboard.ui.bread-crumb-list name="Challan Edit" />
    </x-dashboard.ui.bread-crumb>

    <div class="space-y-4" x-data="challanEditForm()">
        <!-- Header with Quotation Info - Compact Design -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-lg border border-blue-200 dark:border-gray-600 shadow-sm">
            <div class="px-4 py-3">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Challan
                    </h2>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ $challan->revision->quotation->quotation_no }}
                        </span>
                    </div>
                </div>

                <!-- Compact Quotation Info Grid -->
                <div class="flex items-center justify-between gap-4 text-sm">
                    <div class="flex items-start space-x-3">
                        <span class="font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">Company:</span>
                        <span class="text-gray-900 dark:text-gray-100 break-words">{{ $challan->revision->quotation->customer->company_name ?? $challan->revision->quotation->customer->company->name }}</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">Address:</span>
                        <span class="text-gray-900 dark:text-gray-100 break-words">{{ $challan->revision->quotation->customer->address ?? $challan->revision->quotation->customer->company->address }}</span>
                    </div>
                </div>
            </div>
        </div>

        <form class="space-y-4" action="{{ route('tenant.challans.update', $challan->id) }}" method="POST" enctype="multipart/form-data" @submit.prevent="validateForm() ? $el.submit() : null">
            @csrf
            @method('PUT')
            <input type="hidden" name="quotation_revision_id" value="{{ $challan->quotation_revision_id }}">

            <!-- Challan Details Card - Optimized Layout -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Challan Details
                    </h3>
                </div>

                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        <!-- Date Field -->
                        <div class="space-y-1">
                            <label for="datepicker" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-1 py-0.5 rounded dark:bg-red-900 dark:text-red-300">required</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                                    <x-ui.svg.calendar class="h-4 w-4 text-gray-400" />
                                </div>
                                <input type="text" id="datepicker" name="date"
                                    x-model="formattedDate"
                                    x-on:input="onDateInput($event)"
                                    x-on:change="onDateInput($event)"
                                    class="flowbite-datepicker pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition-colors duration-200"
                                    pattern="\d{2}/\d{2}/\d{4}"
                                    value="{{ old('date', $challan->date ? \Carbon\Carbon::parse($challan->date)->format('d/m/Y') : '') }}"
                                    required />
                            </div>
                        </div>

                        <!-- Challan No. -->
                        <div>
                            <x-ui.form.input name="challan_no" label="Challan No." placeholder="Ex. CH-0001"
                                x-model="challan_no"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition-colors duration-200"
                                value="{{ old('challan_no', $challan->challan_no) }}" required />
                        </div>

                        <!-- PO No. -->
                        <div>
                            <x-ui.form.input name="po_no" label="PO No." placeholder="Ex. PO-1234"
                                x-model="po_no"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition-colors duration-200"
                                value="{{ old('po_no', $challan->revision->quotation->po_no) }}" required />
                        </div>

                        <!-- Delivery Date -->
                        <div class="space-y-1">
                            <label for="delivery_datepicker" class="block text-sm font-medium text-gray-700 dark:text-gray-300">P.O Date</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                                    <x-ui.svg.calendar class="h-4 w-4 text-gray-400" />
                                </div>
                                <input type="text" id="delivery_datepicker" name="po_date"
                                    x-model="po_date"
                                    x-on:input="onPoDateInput($event)"
                                    x-on:change="onPoDateInput($event)"
                                    class="flowbite-datepicker pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition-colors duration-200"
                                    value="{{ old('po_date', $challan->revision->quotation->po_date ? \Carbon\Carbon::parse($challan->revision->quotation->po_date)->format('d/m/Y') : '') }}"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Selection Card - Enhanced Interactive Design -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Product Selection
                        </h3>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="selectAll()"
                                :class="allSelected ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200' : 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200'"
                                class="text-xs px-3 py-1 hover:bg-blue-200 dark:hover:bg-blue-800 rounded-md transition-colors duration-200">
                                <span x-text="allSelected ? 'All Selected' : 'Select All'"></span>
                            </button>
                            <button type="button" @click="clearAll()" :disabled="selectedCount === 0"
                                class="text-xs px-3 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                Clear All
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-16">
                                        Select
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Product Name
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-20">
                                        Unit
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-24">
                                        Ordered
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">
                                        Current Qty
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">
                                        New Qty
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-48">
                                        Remarks
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @foreach ($challan->revision->products as $index => $qp)
                                    @php
                                        $ordered = (int) $qp->quantity;
                                        $currentChallanProduct = $challan->products->where('quotation_product_id', $qp->id)->first();
                                        $currentQuantity = $currentChallanProduct ? $currentChallanProduct->quantity : 0;
                                        $otherDelivered = $qp->challanProducts->where('challan_id', '!=', $challan->id)->sum('quantity') ?? 0;
                                        $maxAllowed = $ordered - $otherDelivered;
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150 product-row"
                                        :class="items[{{ $index }}].selected ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500' : ''"
                                        x-data="{ maxAllowed: {{ $maxAllowed }}, ordered: {{ $ordered }}, currentQuantity: {{ $currentQuantity }} }">
                                        <td class="px-3 py-3">
                                            <input type="hidden" name="items[{{ $index }}][quotation_product_id]" value="{{ $qp->id }}">
                                            <div class="flex items-center">
                                                <input type="checkbox" name="items[{{ $index }}][selected]" value="1"
                                                    x-model="items[{{ $index }}].selected"
                                                    @change="toggleItem({{ $index }}, ordered, maxAllowed)"
                                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 transition-colors duration-200">
                                            </div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $qp->product->name }}</div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $qp->unit }}</span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                {{ $ordered }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-200">
                                                {{ $currentQuantity }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="flex items-center space-x-3">
                                                <input type="number" min="0" max="{{ $maxAllowed }}"
                                                    name="items[{{ $index }}][quantity]"
                                                    x-model="items[{{ $index }}].quantity"
                                                    @input="validateQuantity({{ $index }}, maxAllowed)"
                                                    :disabled="!items[{{ $index }}].selected"
                                                    class="w-20 px-2 py-1 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <div class="flex items-center text-xs whitespace-nowrap">
                                                    <span class="text-gray-500 dark:text-gray-400 mr-1">Max:</span>
                                                    <span class="font-medium text-green-600 dark:text-green-400">{{ $maxAllowed }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <input type="text" name="items[{{ $index }}][remarks]"
                                                x-model="items[{{ $index }}].remarks"
                                                :disabled="!items[{{ $index }}].selected"
                                                placeholder="Remarks"
                                                class="w-48 px-2 py-1 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary Section -->
                    <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Selected Products:</span>
                            <span x-text="selectedCount" class="font-medium text-gray-900 dark:text-gray-100"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-4">
                <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Modify product quantities and update challan</span>
                </div>
                <div class="flex items-center space-x-3">
                    <button type="button" onclick="history.back()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-4 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700 transition-colors duration-200">
                        Cancel
                    </button>
                    {{-- @if (!$hasBill) --}}
                        <button form="delete-form" type="button"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800 transition-colors duration-200 delete-button">
                            Delete Challan
                        </button>
                    {{-- @endif --}}
                    <button type="submit" @click="if (!validateForm()) $event.preventDefault()" :disabled="!canSubmit"
                        class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Update Challan
                    </button>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('tenant.challans.destroy', $challan->id) }}" id="delete-form" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>

    <script>
        function challanEditForm() {
            return {
                items: [
                    @foreach ($challan->revision->products as $index => $qp)
                        @php
                            $currentChallanProduct = $challan->products->where('quotation_product_id', $qp->id)->first();
                            $currentQuantity = $currentChallanProduct ? $currentChallanProduct->quantity : 0;
                            $isSelected = $currentQuantity > 0;
                        @endphp
                        {
                            selected: {{ $isSelected ? 'true' : 'false' }},
                            quantity: {{ $currentQuantity }},
                            originalQuantity: {{ $currentQuantity }},
                            remarks: @js(optional($currentChallanProduct)->remarks)
                        }@if (!$loop->last),@endif
                    @endforeach
                ],
                itemsMeta: [
                    @foreach ($challan->revision->products as $index => $qp)
                        @php
                            $ordered = (int) $qp->quantity;
                            $otherDelivered = $qp->challanProducts->where('challan_id', '!=', $challan->id)->sum('quantity') ?? 0;
                            $maxAllowed = $ordered - $otherDelivered;
                        @endphp
                        { ordered: {{ $ordered }}, maxAllowed: {{ $maxAllowed }} }@if (!$loop->last),@endif
                    @endforeach
                ],
                formattedDate: '{{ old('date', $challan->date ? \Carbon\Carbon::parse($challan->date)->format('d/m/Y') : '') }}',
                challan_no: '{{ old('challan_no', $challan->challan_no) }}',
                po_no: '{{ old('po_no', $challan->revision->quotation->po_no) }}',
                po_date: '{{ old('po_date', $challan->revision->quotation->po_date ? \Carbon\Carbon::parse($challan->revision->quotation->po_date)->format('d/m/Y') : '') }}',

                get selectedCount() {
                    return this.items.filter(item => item.selected).length;
                },

                get allSelected() {
                    return this.items.length > 0 && this.items.every(item => item.selected);
                },

                get canSubmit() {
                    return this.selectedCount > 0 && this.formattedDate && this.challan_no && this.po_no && this.po_date;
                },

                selectAll() {
                    this.items.forEach((item, index) => {
                        item.selected = true;
                        if (!item.quantity || item.quantity === 0) {
                            item.quantity = item.originalQuantity || 1;
                        }
                    });
                },

                clearAll() {
                    this.items.forEach(item => {
                        item.selected = false;
                        item.quantity = '';
                    });
                },

                toggleItem(index, ordered, maxAllowed) {
                    if (this.items[index].selected && (!this.items[index].quantity || this.items[index].quantity === 0)) {
                        this.items[index].quantity = this.items[index].originalQuantity || 1;
                    }
                },

                validateQuantity(index, maxAllowed) {
                    const quantity = parseInt(this.items[index].quantity) || 0;
                    if (quantity > maxAllowed) {
                        this.items[index].quantity = maxAllowed;
                    }
                    if (quantity < 0) {
                        this.items[index].quantity = 0;
                    }
                },

                onDateInput(event) {
                    this.formattedDate = event.target.value;
                },

                onPoDateInput(event) {
                    this.po_date = event.target.value;
                },

                validateForm() {
                    if (!this.formattedDate) {
                        alert('Please select a date');
                        return false;
                    }
                    if (!this.challan_no) {
                        alert('Please enter challan number');
                        return false;
                    }
                    if (!this.po_no) {
                        alert('Please enter PO number');
                        return false;
                    }

                    if (this.selectedCount === 0) {
                        alert('Please select at least one product');
                        return false;
                    }

                    const hasInvalidQuantity = this.items.some((item, index) => {
                        if (item.selected) {
                            const quantity = parseInt(item.quantity) || 0;
                            return quantity <= 0 || quantity > this.itemsMeta[index].maxAllowed;
                        }
                        return false;
                    });

                    if (hasInvalidQuantity) {
                        alert('Please check product quantities - they must be greater than 0 and not exceed maximum allowed');
                        return false;
                    }

                    return true;
                }
            };
        }

        // Initialize datepickers
        document.addEventListener('DOMContentLoaded', function() {
            // Main date picker
            const datepicker = document.getElementById('datepicker');
            if (datepicker) {
                new Datepicker(datepicker, {
                    format: 'dd/mm/yyyy',
                    autohide: true
                });
            }

            // Delivery date picker
            const deliveryDatepicker = document.getElementById('delivery_datepicker');
            if (deliveryDatepicker) {
                new Datepicker(deliveryDatepicker, {
                    format: 'dd/mm/yyyy',
                    autohide: true
                });
            }
        });
    </script>
</x-dashboard.layout.default>
