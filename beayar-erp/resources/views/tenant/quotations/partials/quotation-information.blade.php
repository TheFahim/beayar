<x-ui.card heading="Quotation Information">
    <div class="mx-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        <div class="mx-2 lg:col-span-1">
            <div class="mb-2">
                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                    Customer
                    <span class="text-red-500 text-sm font-medium">*</span>
                </label>
            </div>
            {{-- <span class="bg-red text-red-800 text-xs font-medium px-1 py-0.5 rounded dark:bg-red-900 dark:text-red-300">{{isset($quotation) ? $quotation->customer_id : ''}}</span> --}}
            <div class="flex gap-2">
                <x-ui.form.searchable-select name="quotation[customer_id]" x-model="selectedCustomerId"
                    apiEndpoint="{{ route('tenant.customers.search') }}"
                    perPage="20"
                    displayTemplate="{customer_no} - {name} ({customer_company.name})"
                    :searchFields="['name', 'customer_no', 'phone', 'email', 'customer_company.name', 'attention']"
                    placeholder="name, number or company..."
                    noResultsText="No customers found."
                    createEvent="open-create-customer-modal"
                    createLabel="+ Add New Customer"
                    newItemEvent="customer-created"
                    class="flex-1"
                    @option-selected="handleCustomerSelection($event)" />
                <button type="button" @click="$dispatch('open-create-customer-modal')"
                    class="w-10 h-10 flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 flex-shrink-0"
                    title="Add Customer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>
            <div x-show="selectedCustomer" class="mt-2 p-2 bg-blue-50 dark:bg-blue-900/20 dark:text-white rounded text-sm">
                <span x-text="selectedCustomer ? `Selected: ${selectedCustomer.name}${selectedCustomer.attention ? ' (Attn: ' + selectedCustomer.attention + ')' : ''}` : ''"></span>
            </div>
        </div>

        <div class="mx-2">
            <x-ui.form.textarea x-model="quotation.ship_to" name="quotation[ship_to]" label="Ship To Address"
                placeholder="Delivery address..." class="w-full px-2 py-1 text-sm" rows="3" />
        </div>

        <div class="mx-2">
            <x-ui.form.input x-model="quotation.quotation_no" name="quotation[quotation_no]" label="Quotation No."
                placeholder="Auto-generated from customer and date" class="w-full px-2 py-1 text-sm" readonly
                required />
            <div class="text-xs text-gray-500 mt-1">Auto-generated using customer number and date. Changes
                when either updates.</div>
        </div>
    </div>
</x-ui.card>
