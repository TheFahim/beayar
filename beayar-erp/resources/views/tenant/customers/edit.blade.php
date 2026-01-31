<x-dashboard.layout.default title="Edit Customer">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.customers.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200">
                <x-ui.svg.users class="h-3 w-3 me-2" />
                Customers
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 9 4-4-4-4" />
                </svg>
                <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">Edit</span>
            </div>
        </li>
    </x-dashboard.ui.bread-crumb>

    <div class="max-w-4xl mx-auto">
        <x-ui.card
            class="shadow-xl border-0 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900">
            <form method="POST" action="{{ route('tenant.customers.update', $customer->id) }}">
                @csrf
                @method('PUT')

                <!-- Header -->
                <div
                    class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </div>
                        Edit Customer
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Update customer details and company information</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6">
                    <!-- Customer Information Section -->
                    <div class="space-y-6">
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Customer Information
                            </h3>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <x-ui.form.input name="customer_no" label="Customer Number"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 bg-gray-50 dark:bg-gray-700"
                                    required value="{{ old('customer_no', $customer->customer_no) }}" readonly />
                            </div>

                            <div>
                                <x-ui.form.input name="customer_name" label="Customer Name" placeholder="Ex. John Doe"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    value="{{ old('customer_name', $customer->name) }}" />
                            </div>

                            <div>
                                <x-ui.form.input name="attention" label="Attention" placeholder="Ex. Jane Smith"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    value="{{ old('attention', $customer->attention) }}" />
                            </div>

                            <div>
                                <x-ui.form.input name="designation" label="Designation" placeholder="Ex. Manager"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    value="{{ old('designation', $customer->designation) }}" />
                            </div>

                            <div>
                                <x-ui.form.input name="department" label="Department" placeholder="Ex. Sales"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    value="{{ old('department', $customer->department) }}" />
                            </div>

                            <div>
                                <x-ui.form.input name="phone" label="Phone" placeholder="Ex. +8801234567890"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    value="{{ old('phone', $customer->phone) }}" />
                            </div>

                            <div>
                                <x-ui.form.input name="email" label="Email" type="email"
                                    placeholder="Ex. john@example.com"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    value="{{ old('email', $customer->email) }}" />
                            </div>

                            <div>
                                <x-ui.form.input name="address" label="Address" placeholder="Ex. 123 Main St, City"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    value="{{ old('address', $customer->address) }}" />
                            </div>
                        </div>
                    </div>

                    <!-- Company Information Section -->
                    <div class="space-y-6">
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                Company Information
                            </h3>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-end gap-4">
                                <div class="flex-1">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                        Company
                                        <span
                                            class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded-full dark:bg-red-900 dark:text-red-300 ml-2">required</span>
                                    </label>
                                    <div x-data="companySearchableSelect({
                                        value: '{{ old('customer_company_id', $customer->customer_company_id) }}',
                                        endpoint: '{{ route('companies.search') }}',
                                        initialName: '{{ $customer->customerCompany->name ?? '' }}'
                                    })" x-init="init" class="relative"
                                        @click.away="open = false">

                                        {{-- Hidden input for form submission --}}
                                        <input type="hidden" name="customer_company_id" x-bind:value="selectedValue" required>

                                        {{-- Visible search input --}}
                                        <div class="relative">
                                            <input type="text" x-model="searchTerm" @focus="open = true"
                                                @input.debounce.300ms="filterOptions"
                                                @blur="checkSelection"
                                                placeholder="Search and select a company..."
                                                class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200 dark:bg-gray-700 dark:text-white"
                                                required>
                                            {{-- Dropdown Arrow --}}
                                            <div
                                                class="absolute inset-y-0 end-0 flex items-center pe-3 pointer-events-none">
                                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400"
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
                                                        <li class="px-4 py-3 text-gray-500 flex items-center gap-2">
                                                            <svg class="w-4 h-4 animate-spin" fill="none"
                                                                viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12"
                                                                    cy="12" r="10" stroke="currentColor"
                                                                    stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor"
                                                                    d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                                </path>
                                                            </svg>
                                                            Loading companies...
                                                        </li>
                                                    </template>
                                                    <template x-if="!loading && filteredOptions.length === 0">
                                                        <li class="px-4 py-3 text-gray-500">
                                                            No companies found.
                                                            <button type="button" @click="openModal"
                                                                class="text-green-600 hover:text-green-800 font-medium hover:underline">Add
                                                                new company</button>
                                                        </li>
                                                    </template>
                                                    <template x-for="(option, index) in filteredOptions"
                                                        :key="option.id">
                                                        <li
                                                            @click="selectOption(option)"
                                                            class="px-4 py-3 hover:bg-green-50 dark:hover:bg-gray-700 cursor-pointer transition-colors duration-150 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                                            <div class="font-medium text-gray-900 dark:text-white"
                                                                x-text="option.name"></div>
                                                            <div class="text-xs text-gray-500 mt-0.5"
                                                                x-text="option.email"></div>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" x-show="selectedValue"
                                        @click="$dispatch('edit-company-modal', { id: selectedValue })"
                                        class="mb-0.5 p-3.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors shadow-sm border border-blue-200"
                                        title="Edit Company">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button type="button" @click="$dispatch('open-company-modal')"
                                        class="mb-0.5 p-3.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors shadow-sm border border-green-200"
                                        title="Add New Company">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3 rounded-b-xl">
                    <a href="{{ route('tenant.customers.index') }}"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-4 focus:ring-blue-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-all shadow-sm">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-lg focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all">
                        Update Customer
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>

    {{-- Company Create/Edit Modal (Alpine.js) --}}
    <div x-data="companyModal"
        x-show="showModal"
        @open-company-modal.window="openModal()"
        @edit-company-modal.window="fetchCompany($event.detail.id)"
        @keydown.escape.window="closeModal()"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title"
                                x-text="isEdit ? 'Edit Company' : 'Add New Company'">
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                                    <input type="text" x-model="companyForm.company_code"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                        placeholder="CMP-001">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                    <input type="text" x-model="companyForm.name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                        placeholder="Company Name">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <input type="email" x-model="companyForm.email"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                        placeholder="company@example.com">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                                    <input type="text" x-model="companyForm.phone"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                        placeholder="+1 234 567 890">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">BIN No</label>
                                    <input type="text" x-model="companyForm.bin_no"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                        placeholder="BIN Number">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                                    <textarea x-model="companyForm.address" rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                        placeholder="Full address"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="submitCompany" :disabled="loading"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span x-show="!loading" x-text="isEdit ? 'Update Company' : 'Save Company'"></span>
                        <span x-show="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span x-text="isEdit ? 'Updating...' : 'Saving...'"></span>
                        </span>
                    </button>
                    <button type="button" @click="closeModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('companySearchableSelect', (config) => ({
                    open: false,
                    searchTerm: config.initialName || '',
                    selectedValue: config.value || '',
                    options: [],
                    filteredOptions: [],
                    loading: false,

                    init() {
                        this.$watch('searchTerm', (value) => {
                            if (value && value !== this.initialName) {
                                // Logic to clear selectedValue if user changes text after selection
                                // But if it matches the selected name, keep it.
                                // For simplicity, we filter options.
                            }
                        });
                        
                        // If we have an initial value but no name (e.g. old input), we might want to fetch it
                        // But we passed initialName from blade, so it should be fine.
                    },

                    async filterOptions() {
                        if (this.searchTerm.length < 2) {
                            this.filteredOptions = [];
                            return;
                        }

                        this.loading = true;
                        try {
                            const response = await fetch(`${config.endpoint}?query=${this.searchTerm}`);
                            this.options = await response.json();
                            this.filteredOptions = this.options;
                        } catch (error) {
                            console.error('Error fetching companies:', error);
                        } finally {
                            this.loading = false;
                        }
                    },

                    selectOption(option) {
                        this.selectedValue = option.id;
                        this.searchTerm = option.name;
                        this.initialName = option.name; // Update initial name to current selection
                        this.open = false;
                    },

                    checkSelection() {
                        // Optional: if user types something that matches exactly one option, select it
                        // Or reset to last valid selection if they click away
                        setTimeout(() => {
                            if (!this.selectedValue) {
                                // If strict selection required
                                // this.searchTerm = '';
                            }
                        }, 200);
                    },
                    
                    openModal() {
                        this.open = false;
                        this.$dispatch('open-company-modal');
                    }
                }));

                Alpine.data('companyModal', () => ({
                    showModal: false,
                    loading: false,
                    isEdit: false,
                    companyId: null,
                    companyForm: {
                        company_code: '',
                        name: '',
                        email: '',
                        phone: '',
                        bin_no: '',
                        address: ''
                    },

                    openModal() {
                        this.showModal = true;
                    },

                    closeModal() {
                        this.showModal = false;
                        setTimeout(() => this.resetForm(), 300);
                    },

                    resetForm() {
                        this.companyForm = {
                            company_code: '',
                            name: '',
                            email: '',
                            phone: '',
                            bin_no: '',
                            address: ''
                        };
                        this.isEdit = false;
                        this.companyId = null;
                    },

                    async fetchCompany(id) {
                        this.loading = true;
                        this.openModal();
                        this.isEdit = true;
                        this.companyId = id;

                        try {
                            const response = await fetch(`/companies/${id}`);
                            if (!response.ok) throw new Error('Failed to fetch company');
                            
                            const data = await response.json();
                            this.companyForm = {
                                company_code: data.company_code || '',
                                name: data.name || '',
                                email: data.email || '',
                                phone: data.phone || '',
                                bin_no: data.bin_no || '',
                                address: data.address || ''
                            };
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Error fetching company details');
                            this.closeModal();
                        } finally {
                            this.loading = false;
                        }
                    },

                    async submitCompany() {
                        this.loading = true;
                        try {
                            const url = this.isEdit 
                                ? `/companies/${this.companyId}` 
                                : '{{ route('companies.store') }}';
                            
                            const method = this.isEdit ? 'PUT' : 'POST';

                            const response = await fetch(url, {
                                method: method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .getAttribute('content')
                                },
                                body: JSON.stringify(this.companyForm)
                            });

                            const data = await response.json();

                            if (response.ok) {
                                alert(this.isEdit ? 'Company updated successfully!' : 'Company created successfully!');
                                this.closeModal();
                            } else {
                                alert('Error: ' + (data.message || 'Unknown error'));
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('An error occurred.');
                        } finally {
                            this.loading = false;
                        }
                    }
                }));
            });
        </script>
    @endpush
</x-dashboard.layout.default>