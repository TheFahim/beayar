<x-dashboard.layout.default title="Edit Quotation ">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{  $quotation->type == 2 ? route('pre.quotation') : route('quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                {{ $quotation->type == 2 ? 'Pre-Quotations' : 'Quotations' }}
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Edit" />
    </x-dashboard.ui.bread-crumb>

    <div>

        <h2 class="mx-5 text-xl font-extrabold dark:text-white">Edit {{ $quotation->type == 2 ? 'Pre-Quotation' : 'Quotation' }} - {{ $quotation->quotation_no }}</h2>

        {{-- The main Alpine component is initialized on the form tag --}}
        <form class="space-y-3" x-data="quotationForm"
            @customer-selected.window="populateCustomer($event.detail.customer)"
            action="{{ route('quotations.update', $quotation->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <x-ui.card>
                @php
                    // echo '<pre>';
                    //     print_r(session()->all());
                    // echo '</pre>';
                @endphp
                <div class="mx-2 grid grid-cols-3">
                    <div class="mx-2">
                        <input type="hidden" name="quotation[id]" value="{{ $quotation->id }}" />
                        <x-ui.form.input name="quotation[quotation_no]" label="Quotation No." placeholder="Ex. WC-03029"
                            class="w-full p-2 text-lg" required
                            value="{{ old('quotation.quotation_no', $quotation->quotation_no) }}" />
                    </div>

                </div>
            </x-ui.card>

            <div
                class="mx-2 p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 space-y-2">

                <div class="flex items-center justify-between">
                    <h2
                        class="mb-4 p-4 text-xl font-extrabold leading-none tracking-tight text-gray-900 md:text-xl dark:text-white">
                        Customer
                    </h2>

                    {{-- Search bar remains unchanged, it has its own separate component --}}
                    <div x-data="searchBar" @click.outside="showSuggestions = false" class="relative max-w-sm">
                        <div class="flex items-start">
                            <label for="simple-search" class="sr-only">Search</label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 5v10M3 5a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 10a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm12 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm0 0V6a3 3 0 0 0-3-3H9m1.5-2-2 2 2 2" />
                                    </svg>
                                </div>
                                <input type="text" id="simple-search" x-model.debounce.300ms="searchQuery"
                                    @focus="getInitialSuggestions()" @keydown.escape.prevent="showSuggestions = false"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Search to change customer..." autocomplete="off" />
                            </div>
                        </div>
                        <div x-show="showSuggestions && suggestions.length > 0" x-transition
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg dark:bg-gray-700 dark:border-gray-600">
                            <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                                <template x-for="customer in suggestions" :key="customer.id">
                                    <li>
                                        <a href="#" @click.prevent="selectCustomer(customer)"
                                            class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                            <span class="font-bold" x-text="customer.customer_name"></span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400"
                                                x-text="customer.company_name + ' (ID: ' + customer.customer_no + ')'"></span>
                                        </a>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
                <div
                    class="min-w-max border-b bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                </div>

                <template x-if="!isCustomerSelected">
                    <div x-transition class="mx-2 grid grid-cols-3 space-y-3">
                        <div class="mx-2">
                            {{-- We bind the 'disabled' attribute to isCustomerSelected --}}
                            <x-ui.form.input x-model="customerNo" name="customer[customer_no]" label="Customer ID"
                                placeholder="Ex. WC-0001" class="w-full p-2 text-lg" required />
                        </div>
                        <div class="mx-2">
                            <x-ui.form.input x-model="customerName" name="customer[customer_name]" label="Customer Name"
                                placeholder="Ex. Abdullah" class="w-full p-2 text-lg" required />
                        </div>
                        <div class="mx-2">
                            <x-ui.form.input x-model="customerDesignation" name="customer[designation]"
                                label="Designation" placeholder="Ex. Sales Executive" class="w-full p-2 text-lg" />
                        </div>
                        <div class="mx-2">
                            <x-ui.form.input x-model="customerCompanyName" name="customer[company_name]"
                                label="Company Name" placeholder="Ex. XYZ Corporation" class="w-full p-2 text-lg"
                                required />
                        </div>
                        <div class="mx-2">
                            <x-ui.form.input x-model="customerAddess" name="customer[address]" label="Address"
                                placeholder="House/Road/Village..." class="w-full p-2 text-lg" />
                        </div>
                        <div class="mx-2">
                            <x-ui.form.input x-model="customerPhone" name="customer[phone]" label="Phone"
                                placeholder="Ex. 018XXXXXXXX" class="w-full p-2 text-lg" />
                        </div>
                        <div class="mx-2">
                            <x-ui.form.input x-model="customerBIN" name="customer[bin_no]" label="BIN No."
                                placeholder="Customer BIN no." class="w-full p-2 text-lg" />
                        </div>
                    </div>
                </template>


                <template x-if="isCustomerSelected">
                    <div x-transition
                        class="p-4 my-4 text-sm text-gray-800 rounded-lg bg-gray-50 dark:bg-gray-800 dark:text-gray-300"
                        role="alert">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <input type="hidden" name="customer[id]" x-model="customerId">

                            <div>
                                <span class="font-medium">Customer No:</span>
                                <span x-text="selectedCustomerDetails.customer_no"></span>
                            </div>
                            <div>
                                <span class="font-medium">Company:</span>
                                <span x-text="selectedCustomerDetails.company_name"></span>
                            </div>

                            <div>
                                <span class="font-medium">Customer Name:</span>
                                <span x-text="selectedCustomerDetails.customer_name"></span>
                            </div>
                            <div>
                                <span class="font-medium">Phone:</span>
                                <span x-text="selectedCustomerDetails.phone"></span>
                            </div>
                            <div>
                                <span class="font-medium">Designation:</span>
                                <span x-text="selectedCustomerDetails.designation"></span>
                            </div>
                            <div>
                                <span class="font-medium">Address:</span>
                                <span x-text="selectedCustomerDetails.address"></span>
                            </div>

                        </div>
                        <button @click.prevent="changeCustomer" type="button"
                            class="mt-4 px-3 py-3 text-xs font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800">
                            Enter New customer
                        </button>
                    </div>
                </template>
            </div>

            <x-ui.card heading="Products">
                <div class="p-2 col-span-3 mx-2">
                    <div class="grid grid-cols-1">
                        <div class="w-full space-y-4">
                            <div class="flex items-center my-4">
                                <h1 class="text-xl font-bold dark:text-white">Products</h1>
                                <button type="button"
                                    class="px-2 py-1 mx-2 text-gray-500 rounded hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700"
                                    @click="addRow">
                                    <x-ui.svg.circle-plus class="w-6 h-6 " />
                                </button>
                            </div>

                            <!-- HEADERS: Visible on medium screens and up -->
                            <div class="hidden md:grid items-center gap-4 pb-2 border-b border-gray-200 dark:border-gray-700"
                                style="grid-template-columns: repeat(10, minmax(0, 1fr));">
                                <div class="col-span-2 font-semibold text-sm text-gray-600 dark:text-gray-400">Product
                                    Name</div>
                                <div class="col-span-2 font-semibold text-sm text-gray-600 dark:text-gray-400">Unit
                                </div>
                                <div class="font-semibold text-sm text-gray-600 dark:text-gray-400">Price</div>
                                <div class="font-semibold text-sm text-gray-600 dark:text-gray-400">Quantity</div>
                                <div class="col-span-2 font-semibold text-sm text-gray-600 dark:text-gray-400">Amount
                                </div>
                                <div class="col-span-2 font-semibold text-sm text-gray-600 dark:text-gray-400">Remarks
                                </div>
                            </div>

                            <!-- PRODUCT ROWS: The original code block is preserved below -->
                            <template x-for="(row, index) in rows" :key="index">
                                <div class="grid items-center gap-4"
                                    style="grid-template-columns: repeat(10, minmax(0, 1fr));">
                                    <input type="hidden" x-bind:name="'product[' + index + '][id]'"
                                        x-model="row.id" />
                                    <div class="col-span-2">
                                        <x-ui.form.input x-bind:name="'product[' + index + '][name]'"
                                            x-model="row.name" placeholder="Product Name"
                                            class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                                            required />
                                    </div>
                                    <div class="col-span-2">
                                        <x-ui.form.input x-bind:name="'product[' + index + '][unit]'"
                                            x-model="row.unit" placeholder="Unit (pcs)"
                                            class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                                            required />
                                    </div>
                                    <div>
                                        <x-ui.form.input type="number" x-bind:name="'product[' + index + '][price]'"
                                            x-model.number="row.price" placeholder="Price (1000)"
                                            class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                                            required />
                                    </div>
                                    <div>
                                        <x-ui.form.input type="number" step="1"
                                            x-bind:name="'product[' + index + '][quantity]'"
                                            x-model.number="row.quantity" placeholder="Quantity (3)"
                                            class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                                            required />
                                    </div>
                                    <div class="col-span-2">
                                        <x-ui.form.input x-bind:name="'product[' + index + '][amount]'"
                                            x-bind:value="(row.price * row.quantity).toFixed(2)" placeholder="Amount"
                                            class="px-4 py-2 border rounded bg-gray-200 dark:bg-gray-700 dark:text-gray-100 cursor-not-allowed"
                                            readonly required />
                                    </div>
                                    <div class="col-span-2">
                                        <x-ui.form.input x-bind:name="'product[' + index + '][remarks]'"
                                            x-model="row.remarks" placeholder="Remarks"
                                            class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100" />
                                    </div>
                                    <div class="col-span-7">
                                        <label x-bind:for="'specs-' + index"
                                            class="block mb-1 text-sm font-medium text-gray-900 dark:text-gray-200 py-2 px-2">Specification</label>
                                        <textarea :id="'specs-' + index" rows="4" x-bind:name="'product[' + index + '][specs]'" x-model="row.specs"
                                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:bg-inherit dark:text-white"
                                            placeholder="Product Specifications"></textarea>
                                    </div>
                                    <div class="px-1 justify-items-end">
                                        <button type="button"
                                            class="col-span-2 flex items-center justify-center p-2 bg-red-500 text-white rounded hover:bg-red-600"
                                            @click="rows.splice(index, 1)" x-show="rows.length > 0">
                                            <x-ui.svg.close class="h-6 w-6" />
                                        </button>
                                    </div>
                                    <div
                                        class="min-w-max border-b bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 mt-4 col-span-10">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Total">
                <div class="mx-2 grid grid-cols-3">
                    <div class="mx-2">
                        <x-ui.form.input name="quotation[subtotal]" label="Subtotal" x-model="subtotal"
                            class="w-full p-2 text-lg bg-gray-200 dark:bg-gray-700 cursor-not-allowed" readonly
                            required />
                    </div>
                    <div class="mx-2">
                        <x-ui.form.simple-select name="quotation[vat]" label="VAT" x-model.number="vat"
                            class="w-full p-2 text-lg" required>
                            <option value="0">0%</option>
                            <option value="10">10%</option>
                            <option value="15">15%</option>
                        </x-ui.form.simple-select>
                    </div>
                    <div class="mx-2">
                        <x-ui.form.input name="quotation[total]" label="Total" x-model="total"
                            class="w-full p-2 text-lg bg-gray-200 dark:bg-gray-700 cursor-not-allowed" readonly
                            required />
                    </div>
                    <div class="mx-2 my-10">
                        <textarea id="text-area" rows="4" name="quotation[terms_conditions]"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:bg-inherit dark:text-white"
                            placeholder="Terms And Conditions">
                            {!! old('quotation.terms_conditions', $quotation->terms_conditions) !!}
                        </textarea>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center justify-between">
                    {{-- Left side: Update button --}}
                    <button type="submit"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 m-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 save-button">
                        Update Quotation
                    </button>

                    {{-- Right side: Delete & Change Status buttons --}}
                    <div class="flex items-center">
                        @if (!$hasChallan)
                            <button form="delete-form" type="button"
                                class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 m-2 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none dark:focus:ring-red-800 delete-button">
                                Delete
                            </button>
                        @endif

                        @if ($quotation->type == 2)
                            <button type="button"
                                class="text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 m-2 dark:bg-indigo-600 dark:hover:bg-indigo-700 focus:outline-none dark:focus:ring-indigo-800 pre-qt-change-status save-button">
                                Change to Quotation
                            </button>
                        @endif
                    </div>
                </div>
            </x-ui.card>

        </form>
        <form method="POST" action="{{ route('quotations.destroy', $quotation->id) }}" id="delete-form"
            class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</x-dashboard.layout.default>

{{-- The searchBar script can be shared, so you might move it to a layout file eventually --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('searchBar', () => ({
            searchQuery: '',
            suggestions: [],
            showSuggestions: false,
            loading: false,
            init() {
                this.$watch('searchQuery', (query) => {
                    // --- REVISED LOGIC ---

                    // 1. If the user has cleared the input, show the initial list again.
                    if (query === '') {
                        this.getInitialSuggestions(); // Reuse our existing function!
                        return;
                    }

                    // 2. If the query is too short, hide the suggestions list.
                    //    This handles the case where the user backspaces from "Jo" to "J".
                    if (query.length < 2) {
                        this.suggestions = [];
                        this.showSuggestions = false;
                        return;
                    }

                    // 3. If the query is long enough, perform the search. (This is your existing logic)
                    this.loading = true;
                    fetch(`{{ route('customers.search') }}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            this.suggestions = data;
                            // Ensure the suggestions are shown, even if they were hidden before
                            this.showSuggestions = true;
                            this.loading = false;
                        });
                });
            },

            // This function remains the same, it's already perfect.
            getInitialSuggestions() {
                if (this.searchQuery.length > 0) {
                    this.showSuggestions = true;
                    return;
                }
                this.loading = true;
                fetch(`{{ route('customers.search') }}`)
                    .then(response => response.json())
                    .then(data => {
                        this.suggestions = data;
                        this.showSuggestions = true;
                        this.loading = false;
                    });
            },

            // This function also remains the same.
            selectCustomer(customer) {
                this.$dispatch('customer-selected', {
                    customer: customer
                });
                this.searchQuery = customer.customer_name;
                this.showSuggestions = false;
                this.suggestions = [];
            }
        }));
    });
</script>

{{-- This Alpine script is now populated with the existing model data --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('quotationForm', () => ({
            // Default to 'selected' view on edit page.
            isCustomerSelected: true,

            // Pre-populate details for the display view. `optional()` prevents errors if customer is null.
            selectedCustomerDetails: {!! json_encode(
                old('customer', [
                    'customer_no' => optional($quotation->customer)->customer_no,
                    'company_name' => optional($quotation->customer)->company_name,
                    'customer_name' => optional($quotation->customer)->customer_name,
                    'phone' => optional($quotation->customer)->phone,
                    'designation' => optional($quotation->customer)->designation,
                    'address' => optional($quotation->customer)->address,
                    'id' => optional($quotation->customer)->id,
                ]),
            ) !!},



            // Pre-populate the hidden customer ID input
            customerId: {!! json_encode(old('customer.id', $quotation->customer->id)) !!},

            customerNo: '{{ old('customer.customer_no') }}',
            customerName: '{{ old('customer.customer_name') }}',
            customerDesignation: '{{ old('customer.designation') }}',
            customerCompanyName: '{{ old('customer.company_name') }}',
            customerAddess: '{{ old('customer.address') }}',
            customerPhone: '{{ old('customer.phone') }}',
            customerBIN: '{{ old('customer.bin_no') }}',



            // This function is identical to the create page's version
            populateCustomer(customer) {
                this.customerId = customer.id;
                this.selectedCustomerDetails = customer;
                this.isCustomerSelected = true;
                // We clear the form fields in case they were being edited
                this.customerNo = '';
                this.customerName = '';
                this.customerDesignation = '';
                this.customerCompanyName = '';
                this.customerAddess = '';
                this.customerPhone = '';
            },

            // This function is identical to the create page's version
            changeCustomer() {
                this.isCustomerSelected = false;
                this.customerId = null; // Clear the ID so the full form is submitted
            },

            // --- Product and Total logic is pre-populated from the model ---
            rows: {!! json_encode(old('product', $quotation->products)) !!},
            vat: {{ old('quotation.vat', $quotation->vat ?? 0) }},

            addRow() {
                this.rows.push({
                    id: null,
                    name: '',
                    unit: 'pcs',
                    price: '',
                    quantity: '',
                    specs: '',
                    remarks: ''
                });
            },

            get subtotal() {
                let total = this.rows.reduce((sum, row) => {
                    const price = parseFloat(row.price) || 0;
                    const quantity = parseFloat(row.quantity) || 0;
                    return sum + (price * quantity);
                }, 0);
                return total.toFixed(2);
            },

            get total() {
                const subtotal = parseFloat(this.subtotal) || 0;
                const vatAmount = this.vat > 0 ? subtotal * (this.vat / 100) : 0;
                const finalTotal = subtotal + vatAmount;
                return finalTotal.toFixed(2);
            }
        }));
    });
</script>
