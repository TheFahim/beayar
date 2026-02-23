
<div x-data="createCustomerModalData()"
     @open-create-customer-modal.window="openModal()"
     @keydown.escape.window="closeModal()"
     class="relative z-50"
     style="display: none;"
     x-show="isOpen">

    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity"
         x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                 x-show="isOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.away="closeModal()">

                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">Create New Customer</h3>
                            <div class="mt-4 space-y-4">
                                <!-- Company Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Company <span class="text-red-500">*</span></label>
                                    <div class="relative mt-1">
                                        <input type="text"
                                               x-model="companySearch"
                                               @input.debounce.300ms="filterCompanies"
                                               @focus="showCompanyDropdown = true"
                                               placeholder="Search company..."
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                                        
                                        <div x-show="showCompanyDropdown && filteredCompanies.length > 0" 
                                             class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                                             @click.away="showCompanyDropdown = false">
                                            <template x-for="company in filteredCompanies" :key="company.id">
                                                <div class="cursor-pointer select-none py-2 pl-3 pr-9 hover:bg-gray-100"
                                                     @click="selectCompany(company)">
                                                    <span class="block truncate" :class="{ 'font-semibold': form.customer_company_id === company.id }" x-text="company.name"></span>
                                                    <span class="block truncate text-xs text-gray-500" x-text="company.company_code"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <p class="text-xs text-red-500 mt-1" x-text="errors.customer_company_id"></p>
                                </div>

                                <!-- Customer No (Readonly) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Customer No</label>
                                    <input type="text" x-model="form.customer_no" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                                </div>

                                <!-- Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                                    <input type="text" x-model="form.customer_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                                    <p class="text-xs text-red-500 mt-1" x-text="errors.customer_name"></p>
                                </div>

                                <!-- Email & Phone -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" x-model="form.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                                        <p class="text-xs text-red-500 mt-1" x-text="errors.email"></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                                        <input type="text" x-model="form.phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                                        <p class="text-xs text-red-500 mt-1" x-text="errors.phone"></p>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <textarea x-model="form.address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border"></textarea>
                                    <p class="text-xs text-red-500 mt-1" x-text="errors.address"></p>
                                </div>

                                <!-- Attention -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Attention</label>
                                    <input type="text" x-model="form.attention" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" 
                            @click="submit()"
                            :disabled="loading"
                            class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto disabled:opacity-50">
                        <span x-show="loading">Saving...</span>
                        <span x-show="!loading">Save Customer</span>
                    </button>
                    <button type="button" 
                            @click="closeModal()"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function createCustomerModalData() {
        return {
            isOpen: false,
            loading: false,
            companies: [],
            filteredCompanies: [],
            companySearch: '',
            showCompanyDropdown: false,
            form: {
                customer_company_id: '',
                customer_no: '',
                customer_name: '',
                email: '',
                phone: '',
                address: '',
                attention: '',
                designation: '',
                department: ''
            },
            errors: {},

            init() {
                this.fetchCompanies();
            },

            openModal() {
                this.isOpen = true;
                this.resetForm();
                this.fetchCompanies(); // Refresh in case new companies added
            },

            closeModal() {
                this.isOpen = false;
                this.resetForm();
            },

            resetForm() {
                this.form = {
                    customer_company_id: '',
                    customer_no: '',
                    customer_name: '',
                    email: '',
                    phone: '',
                    address: '',
                    attention: '',
                    designation: '',
                    department: ''
                };
                this.companySearch = '';
                this.errors = {};
            },

            async fetchCompanies() {
                try {
                    const response = await fetch("{{ route('companies.search') }}", {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    if (response.ok) {
                        this.companies = await response.json();
                        this.filteredCompanies = this.companies;
                    }
                } catch (error) {
                    console.error('Error fetching companies:', error);
                }
            },

            filterCompanies() {
                if (this.companySearch === '') {
                    this.filteredCompanies = this.companies;
                } else {
                    this.filteredCompanies = this.companies.filter(company => 
                        company.name.toLowerCase().includes(this.companySearch.toLowerCase()) ||
                        (company.company_code && company.company_code.toLowerCase().includes(this.companySearch.toLowerCase()))
                    );
                }
                this.showCompanyDropdown = true;
            },

            selectCompany(company) {
                this.form.customer_company_id = company.id;
                this.companySearch = company.name;
                this.showCompanyDropdown = false;
                this.generateCustomerNumber(company);
            },

            async generateCustomerNumber(company) {
                try {
                    const response = await fetch(`/companies/${company.id}/next-customer-serial`);
                    if (response.ok) {
                        const data = await response.json();
                        this.form.customer_no = data.customer_no;
                    }
                } catch (error) {
                    console.error('Error generating customer number:', error);
                }
            },

            async submit() {
                this.loading = true;
                this.errors = {};

                try {
                    const response = await fetch("{{ route('tenant.customers.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(this.form)
                    });

                    const data = await response.json();

                    if (response.ok) {
                        window.dispatchEvent(new CustomEvent('customer-created', { detail: data.customer }));
                        this.closeModal();
                        // Optional: Show success notification
                    } else {
                        if (data.errors) {
                            this.errors = data.errors;
                        } else {
                            alert(data.message || 'An error occurred.');
                        }
                    }
                } catch (error) {
                    console.error('Error submitting form:', error);
                    alert('An error occurred while saving.');
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
