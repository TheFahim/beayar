<x-dashboard.layout.default title="New Challan">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Quotations
            </a>
        </li>
        <li class="inline-flex items-center">
            <a href="{{ route('quotations.show', $quotation->id) }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200">
                <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 9 4-4-4-4" />
                </svg> Quotation
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Chalan" />
    </x-dashboard.ui.bread-crumb>

    <div class="space-y-4" x-data="challanForm()">
        <!-- Header with Quotation Info - Compact Design -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-lg border border-blue-200 dark:border-gray-600 shadow-sm">
            <div class="px-4 py-3">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Add New Challan
                    </h2>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ $quotation->quotation_no }}
                        </span>
                    </div>
                </div>

                <!-- Compact Quotation Info Grid - Fixed alignment -->
                <div class="flex items-center justify-between gap-4 text-sm">
                    <div class="flex items-start space-x-3">
                        <span class="font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">Company:</span>
                        <span class="text-gray-900 dark:text-gray-100 break-words">{{ $quotation->customer->company->name }}</span>
                    </div>
                    <div class="flex items-start space-x-3">
                        <span class="font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">Address:</span>
                        <span class="text-gray-900 dark:text-gray-100 break-words">{{ $quotation->customer->company->address }}</span>
                    </div>
                </div>
            </div>
        </div>

        <form class="space-y-4" action="{{ route('challans.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="validateForm() ? $el.submit() : null">
            @csrf
            <input type="hidden" name="quotation_revision_id" value="{{ $revision->id }}">

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
                        <!-- Date Field with Fixed Icon Alignment -->
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
                                    aria-required="true"
                                    required />
                            </div>
                        </div>

                        <div>
                            <x-ui.form.input name="challan_no" label="Challan No." placeholder="Ex. CH-0001"
                                x-model="challan_no"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition-colors duration-200"
                                value="{{ old('challan_no', $suggestedChallanNo) }}" required />
                        </div>

                        <div>
                            <x-ui.form.input name="po_no" label="PO No." placeholder="PO NO."
                                x-model="po_no"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition-colors duration-200"
                                value="{{ old('po_no', $suggestedPoNo) }}" required />
                        </div>

                        <!-- Delivery Date Field with Fixed Icon Alignment -->
                        <div class="space-y-1">
                            <label for="po_datepicker" class="block text-sm font-medium text-gray-700 dark:text-gray-300">P.O Date</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                                    <x-ui.svg.calendar class="h-4 w-4 text-gray-400" />
                                </div>
                                <input type="text" id="po_datepicker" name="po_date"
                                    class="flowbite-datepicker pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition-colors duration-200" value="{{ old('po_date', $quotation->po_date) }}"
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
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-24">
                                        Size
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-20">
                                        Unit
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-24">
                                        Ordered
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">
                                        Deliver Qty
                                    </th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-48">
                                        Remarks
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @foreach ($revision->products as $index => $qp)
                                    @php
                                        $ordered = (int) $qp->quantity;
                                        $delivered = $qp->challanProducts->sum('quantity') ?? 0;
                                        $remaining = max(0, $ordered - $delivered);
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150 product-row"
                                        :class="items[{{ $index }}].selected ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500' : ''"
                                        x-data="{ remaining: {{ $remaining }}, ordered: {{ $ordered }} }">
                                        <td class="px-3 py-3">
                                            <input type="hidden" name="items[{{ $index }}][quotation_product_id]" value="{{ $qp->id }}">
                                            <div class="flex items-center">
                                                <input type="checkbox" name="items[{{ $index }}][selected]" value="1"
                                                    x-model="items[{{ $index }}].selected"
                                                    @change="toggleItem({{ $index }}, ordered, remaining)"
                                                    :disabled="remaining === 0"
                                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 transition-colors duration-200">
                                            </div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $qp->product->name }}</div>
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $qp->size }}</div>
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
                                            <!-- Fixed alignment for quantity input and remaining display -->
                                            <div class="flex items-center space-x-3">
                                                <input type="number" min="1" max="{{ $remaining }}"
                                                    name="items[{{ $index }}][quantity]"
                                                    x-model="items[{{ $index }}].quantity"
                                                    @input="validateQuantity({{ $index }}, remaining)"
                                                    :disabled="!items[{{ $index }}].selected"
                                                    class="w-20 px-2 py-1 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <div class="flex items-center text-xs whitespace-nowrap">
                                                    <span class="text-gray-500 dark:text-gray-400 mr-1">Remaining:</span>
                                                    <span class="font-medium" :class="{{ $remaining }} > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">{{ $remaining }}</span>
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
                    <span>Select products and specify quantities to create challan</span>
                </div>
                <div class="flex items-center space-x-3">
                    <button type="button" onclick="history.back()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-4 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700 transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit" @click="if (!validateForm()) $event.preventDefault()" :disabled="!canSubmit"
                        class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Create Challan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function challanForm() {
            return {
                items: Array.from({ length: {{ count($revision->products) }} }, () => ({ selected: false, quantity: '', remarks: '' })),
                itemsMeta: [
                    @foreach ($revision->products as $index => $qp)
                        { ordered: {{ (int) $qp->quantity }}, remaining: {{ max(0, (int) $qp->quantity - ($qp->challanProducts->sum('quantity') ?? 0)) }} }@if (!$loop->last),@endif
                    @endforeach
                ],
                formattedDate: '',
                challan_no: @js(old('challan_no', $suggestedChallanNo)),
                po_no: @js(old('po_no', $suggestedPoNo)),
                init() {
                    this.formattedDate = this.formatDate(new Date());
                },
                formatDate(date) {
                    const d = date instanceof Date ? date : new Date(date);
                    const day = String(d.getDate()).padStart(2, '0');
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const year = d.getFullYear();
                    return `${day}/${month}/${year}`;
                },
                normalizeDateString(value) {
                    if (!value) return this.formatDate(new Date());
                    const v = String(value).trim();
                    const s = v.replace(/[.\-]/g, '/');
                    const ymd = s.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);
                    if (ymd) {
                        const [, y, m, d] = ymd;
                        return `${String(d).padStart(2, '0')}/${String(m).padStart(2, '0')}/${y}`;
                    }
                    const dmy = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
                    if (dmy) {
                        const [, d, m, y] = dmy;
                        return `${String(d).padStart(2, '0')}/${String(m).padStart(2, '0')}/${y}`;
                    }
                    const iso = v.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
                    if (iso) {
                        const [, y, m, d] = iso;
                        return `${String(d).padStart(2, '0')}/${String(m).padStart(2, '0')}/${y}`;
                    }
                    const parsed = new Date(v);
                    if (!isNaN(parsed)) {
                        return this.formatDate(parsed);
                    }
                    return this.formatDate(new Date());
                },
                onDateInput(event) {
                    this.formattedDate = this.normalizeDateString(event.target.value);
                },

                get selectedCount() {
                    return this.items.filter(item => item.selected).length;
                },

                get allSelected() {
                    const selectableIndexes = this.itemsMeta.map((m, i) => m.remaining > 0 ? i : null).filter(i => i !== null);
                    if (selectableIndexes.length === 0) return false;
                    return selectableIndexes.every(i => this.items[i].selected);
                },

                get canSubmit() {
                    const selectedItems = this.items.filter(item => item.selected);
                    if (selectedItems.length === 0) return false;
                    return selectedItems.every(item => {
                        const q = parseInt(item.quantity, 10);
                        return Number.isFinite(q) && q > 0;
                    });
                },

                toggleItem(index, ordered, remaining) {
                    if (this.items[index].selected) {
                        const o = parseInt(ordered, 10);
                        const r = parseInt(remaining, 10);
                        if (Number.isFinite(o) && Number.isFinite(r)) {
                            this.items[index].quantity = Math.min(o, r);
                        } else {
                            this.items[index].quantity = 1;
                        }
                    } else {
                        this.items[index].quantity = '';
                    }
                },

                validateQuantity(index, remaining) {
                    const value = parseInt(this.items[index].quantity);
                    if (value > remaining) {
                        this.showToast(`Quantity cannot exceed remaining stock (${remaining})`, 'error');
                        this.items[index].quantity = remaining;
                    }
                },

                selectAll() {
                    const shouldSelect = !this.allSelected;
                    this.items.forEach((item, index) => {
                        const meta = this.itemsMeta[index];
                        if (shouldSelect && meta.remaining > 0) {
                            item.selected = true;
                            item.quantity = Math.min(meta.ordered, meta.remaining);
                        } else {
                            item.selected = false;
                            item.quantity = '';
                        }
                    });
                },

                clearAll() {
                    this.items.forEach(item => {
                        item.selected = false;
                        item.quantity = '';
                    });
                },

                validateForm() {
                    const selectedItems = this.items.filter(item => item.selected);

                    if (selectedItems.length === 0) {
                        this.showToast('Please select at least one product', 'error');
                        return false;
                    }

                    const hasValidQuantities = selectedItems.every(item => {
                        const quantity = parseInt(item.quantity);
                        return quantity && quantity > 0;
                    });

                    if (!hasValidQuantities) {
                        this.showToast('Please enter valid quantities for all selected products', 'error');
                        return false;
                    }

                    return true;
                },

                showToast(message, type = 'info') {
                    // Create toast element
                    const toast = document.createElement('div');
                    toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
                        type === 'error' ? 'bg-red-500 text-white' :
                        type === 'success' ? 'bg-green-500 text-white' :
                        'bg-blue-500 text-white'
                    }`;
                    toast.textContent = message;

                    document.body.appendChild(toast);

                    // Animate in
                    setTimeout(() => {
                        toast.classList.remove('translate-x-full');
                    }, 100);

                    // Remove after 3 seconds
                    setTimeout(() => {
                        toast.classList.add('translate-x-full');
                        setTimeout(() => {
                            if (document.body.contains(toast)) {
                                document.body.removeChild(toast);
                            }
                        }, 300);
                    }, 3000);
                }
            }
        }
    </script>
</x-dashboard.layout.default>
