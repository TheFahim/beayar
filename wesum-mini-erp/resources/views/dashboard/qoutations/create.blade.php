<x-dashboard.layout.default title="New Quotation">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{  $type == 2 ? route('pre.quotation') : route('quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                {{ $type == 2 ? 'Pre-Quotations' : 'Quotations' }}
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Create" />
    </x-dashboard.ui.bread-crumb>

    <div>
        <h2 class="mx-5 text-xl font-extrabold dark:text-white">Add New {{ $type == 2 ? 'Pre' : '' }} Quotation</h2>

        {{-- The main Alpine component is initialized on the form tag --}}
        <form class="space-y-3" x-data="quotationForm"
            @customer-selected.window="populateCustomer($event.detail.customer)" action="{{ route('quotations.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf

            <x-ui.card>

                <div>
                    <div class="mx-2 grid grid-cols-3">
                        <div class="mx-2">
                            <x-ui.form.input name="quotation[quotation_no]" label="Quotation No." value="{{ $nextQuotationNumber }}"
                                placeholder="Ex. WC-03029" class="w-full p-2 text-lg" required />
                        </div>

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
                        {{-- Search bar SVG and input --}}
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
                                    @click.away="showSuggestions = false"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Search or click to see recent customers..." autocomplete="off" />
                            </div>
                        </div>
                        {{-- Suggestions dropdown --}}
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
                        class="p-4 my-4 text-gray-800 rounded-lg bg-gray-50 dark:bg-gray-800 dark:text-gray-300"
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

            {{-- The rest of your form remains unchanged --}}
            <x-ui.card heading="Products">
                <div class="p-2 col-span-3 mx-2">
                    <div class="grid grid-cols-1">
                        <div class="w-full space-y-4">
                            <div class="flex items-center my-4">
                                <h1 class="text-xl font-bold dark:text-white">Add Products</h1>
                                <button type="button"
                                    class="px-2 py-1 mx-2 hover:text-white rounded hover:bg-gray-600" @click="addRow">
                                    <x-ui.svg.circle-plus class="w-6 h-6 " />
                                </button>
                            </div>

                            <template x-for="(row, index) in rows" :key="index">
                                <div class="grid items-center gap-4"
                                    style="grid-template-columns: repeat(10, minmax(0, 1fr));">

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
                                    <div class="col-span-2">
                                        <x-ui.form.input type="number" x-bind:name="'product[' + index + '][price]'"
                                            x-model.number="row.price" placeholder="Price (1000)"
                                            class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100"
                                            required />
                                    </div>
                                    <div class="col-span-2">
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
                                            x-model="row.remarks" placeholder="Remakrs"
                                            class="px-4 py-2 border rounded bg-gray-50 dark:bg-gray-800 dark:text-gray-100" />
                                    </div>
                                    <div class="col-span-7">
                                        <textarea x-bind:id="'specs-' + index" rows="4" x-bind:name="'product[' + index + '][specs]'"
                                            x-model="row.specs"
                                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:bg-inherit dark:text-white"
                                            placeholder="Product Specifications"></textarea>
                                    </div>

                                    <div class="px-1 justify-items-end">
                                        <button type="button"
                                            class="col-span-2 flex items-center justify-center p-2 bg-red-500 text-white rounded hover:bg-red-600"
                                            @click="rows.splice(index, 1)" x-show="rows.length > 1">
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
                            {!! old(
                                'quotation.terms_conditions',
                                '<h3 class="bg-blue-900 text-white font-bold p-2 mb-4 text-sm">Terms & Conditions</h3>
                                                                                                                                                                                                                                                                        <ul class="list-disc list-inside text-xs space-y-2 text-gray-700">
                                                                                                                                                                                                                                                                            <li>Mushuk 6.3 will be provided with a bill Copy.</li>
                                                                                                                                                                                                                                                                            <li>Payment will be due prior to delivery of service and goods.</li>
                                                                                                                                                                                                                                                                            <li>The Quotation Value Including VAT, AIT & Transportation.</li>
                                                                                                                                                                                                                                                                            <li>BEFTN / Cheque in favor of Wesum corporation.</li>
                                                                                                                                                                                                                                                                            <li>Delivery Time 3-4 weeks after getting PO. </li>
                                                                                                                                                                                                                                                                            <li>The Quotation value valid 12 days after submission.</li>
                                                                                                                                                                                                                                                                        </ul>',
                            ) !!}
                        </textarea>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="flex justify-between">
                @if ($type == 1)
                    <button type="submit"
                        class="text-white bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mx-5 dark:from-blue-600 dark:to-blue-800 dark:hover:from-blue-700 dark:hover:to-blue-900 focus:outline-none dark:focus:ring-blue-800 save-button flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Save
                    </button>
                @endif
                <button type="submit"
                    class="text-white bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 focus:ring-4 focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 mx-5 dark:from-yellow-500 dark:to-yellow-700 dark:hover:from-yellow-600 dark:hover:to-yellow-800 focus:outline-none dark:focus:ring-yellow-800 save-button pre-quote flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ $type == 1 ? 'Pre Quotation' : 'save' }}
                </button>
            </x-ui.card>

        </form>
    </div>
</x-dashboard.layout.default>

{{-- The searchBar script can remain exactly the same --}}
<script>
    document.addEventListener('alpine:init', () => {
        // This script is fine, no changes needed here.
    });
</script>

<script>
    document.addEventListener('alpine:init', () => {

        Alpine.data('quotationForm', () => ({
            // NEW: State to control UI
            isCustomerSelected: '{{ old('customer.id ') }}' !==
                '', // Default to true if validation fails with a customer selected

            // NEW: Object to hold details of the selected customer for display purposes
            selectedCustomerDetails: {},

            // This model is ONLY for the selected customer's ID
            customerId: '{{ old('
                                                customer.id ') }}',

            // These models are ONLY for the new customer form fields
            customerNo: '{{ old('
                                                customer.customer_no ') }}',
            customerName: '{{ old('
                                                customer.customer_name ') }}',
            customerDesignation: '{{ old('
                                                customer.designation ') }}',
            customerCompanyName: '{{ old('
                                                customer.company_name ') }}',
            customerAddess: '{{ old('
                                                customer.address ') }}',
            customerPhone: '{{ old('
                                                customer.phone ') }}',
            customerBIN: '{{ old('
                                                customer.bin_no ') }}',

            // quotation_no: '{{ old('
            //                                     quotation.quotation_no ') }}',
            dueDate: '{{ old('
                                                quotation.due_date ') }}',

            // UPDATED: This function is called when a customer is selected from the search results
            populateCustomer(customer) {
                // Set the customer ID for form submission
                this.customerId = customer.id;

                // Store the full customer object for read-only display
                this.selectedCustomerDetails = customer;

                // console.log(this.selectedCustomerDetails);


                // Switch the UI to show the selected customer info
                this.isCustomerSelected = true;

                // Clear the 'new customer' form fields to prevent accidental data submission
                this.customerNo = '';
                this.customerName = '';
                this.customerDesignation = '';
                this.customerCompanyName = '';
                this.customerAddess = '';
                this.customerPhone = '';
            },

            // NEW: This function is called when the user clicks "Change Customer"
            changeCustomer() {
                // Switch the UI back to the new customer form
                this.isCustomerSelected = false;

                // Clear the selected customer ID and details
                this.customerId = '';
                this.selectedCustomerDetails = {};
            },

            // --- Product and Total logic remains the same ---
            rows: {!! json_encode(
                old('product', [['name' => '', 'unit' => 'pcs', 'price' => '0', 'quantity' => '1', 'specs' => '', 'remarks' => '']]),
            ) !!},
            vat: {{ old('quotation.vat', 10) }},

            addRow() {
                this.rows.push({
                    name: '',
                    unit: 'pcs',
                    price: '0',
                    quantity: '1',
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
                const vatAmount = subtotal * (this.vat / 100);
                const finalTotal = subtotal + vatAmount;
                return finalTotal.toFixed(2);
            }
        }));

        // The searchBar component remains unchanged as its only job is to emit an event
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
