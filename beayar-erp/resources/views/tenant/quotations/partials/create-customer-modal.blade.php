
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
            <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl"
                 x-show="isOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @click.away="closeModal()">

                <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">Create New Customer</h3>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Left Column: Company & Basic Info -->
                                <div class="space-y-4">
                                    <!-- Company Selection / Creation -->
                                    <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <div class="flex justify-between items-center mb-2">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company <span class="text-red-500">*</span></label>
                                            <button type="button" 
                                                @click="toggleCompanyMode()" 
                                                class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors">
                                                <span x-text="isCreatingCompany ? 'Select Existing Company' : '+ Create New Company'"></span>
                                            </button>
                                        </div>

                                        <!-- Existing Company Select -->
                                        <div x-show="!isCreatingCompany" x-transition>
                                            <div class="relative mt-1">
                                                <input type="text"
                                                    x-model="companySearch"
                                                    @input.debounce.300ms="filterCompanies"
                                                    @focus="showCompanyDropdown = true"
                                                    placeholder="Search company..."
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                                                
                                                <div x-show="showCompanyDropdown && filteredCompanies.length > 0" 
                                                    class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white dark:bg-gray-700 py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                                                    @click.away="showCompanyDropdown = false">
                                                    <template x-for="company in filteredCompanies" :key="company.id">
                                                        <div class="cursor-pointer select-none py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-600"
                                                            @click="selectCompany(company)">
                                                            <span class="block truncate dark:text-gray-200" :class="{ 'font-semibold': form.customer_company_id === company.id }" x-text="company.name"></span>
                                                            <span class="block truncate text-xs text-gray-500 dark:text-gray-400" x-text="company.company_code"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <p class="text-xs text-red-500 mt-1" x-text="errors.customer_company_id"></p>
                                        </div>

                                        <!-- Create New Company Form -->
                                        <div x-show="isCreatingCompany" x-transition class="space-y-3 mt-2">
                                            <div>
                                                <input type="text" x-model="companyForm.name" placeholder="Company Name *" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                                                <p class="text-xs text-red-500 mt-1" x-text="companyErrors.name"></p>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <input type="text" x-model="companyForm.company_code" placeholder="Code (e.g. ABC) *" 
                                                        @input="handleCompanyCodeInput($event)"
                                                        maxlength="10"
                                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                                                    <p class="text-xs text-red-500 mt-1" x-text="companyErrors.company_code"></p>
                                                </div>
                                                <div>
                                                    <input type="text" x-model="companyForm.phone" placeholder="Phone" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                                                </div>
                                            </div>
                                            <div>
                                                <input type="email" x-model="companyForm.email" placeholder="Email" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                                            </div>
                                            <div>
                                                <textarea x-model="companyForm.address" rows="2" placeholder="Address *" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"></textarea>
                                                <p class="text-xs text-red-500 mt-1" x-text="companyErrors.address"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Customer No (Readonly) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer No</label>
                                        <input type="text" x-model="form.customer_no" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border dark:bg-gray-600 dark:border-gray-500 dark:text-gray-200">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="isCreatingCompany">Will be generated from Company Code.</p>
                                    </div>

                                    <!-- Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Name <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="form.customer_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <p class="text-xs text-red-500 mt-1" x-text="errors.customer_name"></p>
                                    </div>
                                </div>

                                <!-- Right Column: Contact Details -->
                                <div class="space-y-4">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white border-b dark:border-gray-700 pb-2">Contact Details</h4>
                                    
                                    <!-- Email & Phone -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                            <input type="email" x-model="form.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <p class="text-xs text-red-500 mt-1" x-text="errors.email"></p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                                            <input type="text" x-model="form.phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <p class="text-xs text-red-500 mt-1" x-text="errors.phone"></p>
                                        </div>
                                    </div>

                                    <!-- Address -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                                        <textarea x-model="form.address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                        <p class="text-xs text-red-500 mt-1" x-text="errors.address"></p>
                                    </div>

                                    <!-- Attention -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Attention</label>
                                        <input type="text" x-model="form.attention" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    </div>

                                    <!-- Designation & Department -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Designation</label>
                                            <input type="text" x-model="form.designation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                            <input type="text" x-model="form.department" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" 
                            @click="submit()"
                            :disabled="loading"
                            class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto disabled:opacity-50">
                        <span x-show="loading">Saving...</span>
                        <span x-show="!loading">Save Customer</span>
                    </button>
                    <button type="button" 
                            @click="closeModal()"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto">Cancel</button>
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
            isCreatingCompany: false,
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
            companyForm: {
                name: '',
                company_code: '',
                email: '',
                phone: '',
                address: ''
            },
            errors: {},
            companyErrors: {},

            init() {
                this.fetchCompanies();
                
                // Keep the watcher as a backup
                this.$watch('companyForm.company_code', (value) => {
                    if (this.isCreatingCompany) {
                        this.form.customer_no = value ? value + '-01' : '';
                    }
                });

                // Listen for company created events to update the list
                window.addEventListener('company-created', (e) => {
                     const newCompany = e.detail;
                     if (newCompany && !this.companies.find(c => c.id === newCompany.id)) {
                         this.companies.push(newCompany);
                         // Update filtered list if needed
                         if (this.companySearch === '') {
                             this.filteredCompanies = this.companies;
                         } else {
                             this.filterCompanies();
                         }
                     }
                });
            },

            handleCompanyCodeInput(event) {
                // Update model
                const value = event.target.value.toUpperCase().replace(/[^A-Z]/g, '');
                this.companyForm.company_code = value;
                
                // Update customer number explicitly
                if (this.isCreatingCompany) {
                    this.form.customer_no = value ? value + '-01' : '';
                }
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

            toggleCompanyMode() {
                this.isCreatingCompany = !this.isCreatingCompany;
                this.form.customer_company_id = '';
                this.companySearch = '';
                if (!this.isCreatingCompany) {
                    this.fetchCompanies();
                    this.form.customer_no = '';
                } else {
                    this.form.customer_no = this.companyForm.company_code ? this.companyForm.company_code + '-01' : '';
                }
            },

            resetForm() {
                this.isCreatingCompany = false;
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
                this.companyForm = {
                    name: '',
                    company_code: '',
                    email: '',
                    phone: '',
                    address: ''
                };
                this.companySearch = '';
                this.errors = {};
                this.companyErrors = {};
            },

            async fetchCompanies() {
                try {
                    const response = await fetch("{{ route('companies.search') }}?t=" + new Date().getTime(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Cache-Control': 'no-cache, no-store, must-revalidate',
                            'Pragma': 'no-cache',
                            'Expires': '0'
                        }
                    });
                    if (response.ok) {
                        this.companies = await response.json();
                        this.filteredCompanies = this.companies;
                        
                        // If we are searching, maintain filter
                        if (this.companySearch) {
                            this.filterCompanies();
                        }
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
                this.companyErrors = {};

                try {
                    // Step 1: Create Company if needed
                    if (this.isCreatingCompany) {
                        // Validate Address
                        if (!this.companyForm.address) {
                            this.companyErrors = { address: ['The address field is required.'] };
                            this.loading = false;
                            return;
                        }

                        const companyResponse = await fetch("{{ route('companies.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(this.companyForm)
                        });

                        const companyData = await companyResponse.json();

                        if (!companyResponse.ok) {
                            if (companyData.errors) {
                                this.companyErrors = companyData.errors;
                            } else {
                                alert(companyData.message || 'Error creating company.');
                            }
                            this.loading = false;
                            return; // Stop execution
                        }

                        // Company created successfully
                        this.form.customer_company_id = companyData.id;
                        
                        // Need to generate customer number for the new company
                        // We can either call the API or let the backend handle it if we passed the ID.
                        // But our backend 'next-customer-serial' endpoint logic is good to keep consistent.
                        await this.generateCustomerNumber({ 
                            id: companyData.id, 
                            company_code: companyData.company_code 
                        });
                    }

                    // Step 2: Create Customer
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
                        
                        // If we created a new company, we should also notify that a company was created
                        // so other components (like company selector) can update if they listen to it.
                        if (this.isCreatingCompany) {
                             window.dispatchEvent(new CustomEvent('company-created', { detail: data.customer.customer_company }));
                        }

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
