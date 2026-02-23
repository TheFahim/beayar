<x-dashboard.layout.default title="Create Customer">
    <div class="max-w-5xl mx-auto py-8">
        <!-- Breadcrumb -->
        <nav class="mb-8 flex items-center text-sm text-gray-500">
            <a href="{{ route('tenant.customers.index') }}" class="hover:text-gray-900 transition-colors">Customers</a>
            <svg class="w-3 h-3 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">New Customer</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <!-- Main Form -->
            <div class="lg:col-span-8">
                <form method="POST" action="{{ route('tenant.customers.store') }}" id="createCustomerForm">
                    @csrf

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <!-- Header -->
                        <div class="px-8 py-6 border-b border-gray-100">
                            <h1 class="text-2xl font-semibold text-gray-900">Create Customer</h1>
                            <p class="text-gray-500 mt-1">Add a new customer to your organization.</p>
                        </div>

                        <div class="p-8 space-y-8">
                            <!-- Company Selection -->
                            <div class="space-y-4">
                                <h3 class="text-sm font-medium text-gray-900 uppercase tracking-wider">Company Details</h3>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Company <span class="text-red-500">*</span>
                                    </label>

                                    <div x-data="companySearchableSelect({
                                        value: '{{ old('customer_company_id') }}',
                                        endpoint: '{{ route('companies.search') }}'
                                    })" x-init="init" class="relative" @click.away="open = false">

                                        <input type="hidden" name="customer_company_id" x-bind:value="selectedValue" required>

                                        <div class="relative">
                                            <input type="text"
                                                x-model="searchTerm"
                                                @focus="open = true"
                                                @input.debounce.300ms="filterOptions"
                                                @blur="checkSelection"
                                                placeholder="Select or search company..."
                                                class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-black/5 focus:border-black transition-all duration-200 text-gray-900 placeholder-gray-400"
                                                required>

                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Dropdown -->
                                        <div x-show="open"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 transform scale-95"
                                            x-transition:enter-end="opacity-100 transform scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 transform scale-100"
                                            x-transition:leave-end="opacity-0 transform scale-95"
                                            class="absolute z-20 w-full mt-2 bg-white rounded-lg shadow-xl border border-gray-100 overflow-hidden"
                                            style="display: none;">

                                            <div class="max-h-64 overflow-y-auto custom-scrollbar">
                                                <ul class="py-1 text-sm text-gray-700">
                                                    <template x-if="loading">
                                                        <li class="px-4 py-3 text-gray-500 flex items-center gap-2">
                                                            <svg class="w-4 h-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                            Loading...
                                                        </li>
                                                    </template>

                                                    <template x-if="!loading && filteredOptions.length === 0">
                                                        <li class="px-4 py-3 text-gray-500 text-center">
                                                            <p class="mb-2">No companies found.</p>
                                                            <button type="button" @click="openModal" class="text-blue-600 hover:text-blue-700 font-medium text-xs uppercase tracking-wide">
                                                                + Create New Company
                                                            </button>
                                                        </li>
                                                    </template>

                                                    <template x-for="option in filteredOptions" :key="option.id">
                                                        <li class="group px-4 py-2.5 cursor-pointer hover:bg-gray-50 transition-colors flex items-center justify-between">
                                                            <div @click="selectOption(option)" class="flex-1">
                                                                <div class="font-medium text-gray-900" x-text="option.name"></div>
                                                                <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-2">
                                                                    <span x-show="option.company_code" x-text="option.company_code" class="bg-gray-100 px-1.5 py-0.5 rounded"></span>
                                                                    <span x-text="option.address" class="truncate max-w-[200px]"></span>
                                                                </div>
                                                            </div>

                                                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                <button type="button" @click.stop="editCompany(option)" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                                    </svg>
                                                                </button>
                                                                <button type="button" @click.stop="deleteCompany(option)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>

                                            <div class="border-t border-gray-100 bg-gray-50/50 p-2">
                                                <button type="button" @click="openModal" class="w-full py-2 flex items-center justify-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-white border border-transparent hover:border-gray-200 rounded-md transition-all">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                    Add New Company
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @error('customer_company_id')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <hr class="border-gray-100">

                            <!-- Customer Information -->
                            <div class="space-y-6">
                                <h3 class="text-sm font-medium text-gray-900 uppercase tracking-wider">Contact Information</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer Number</label>
                                        <input type="text" name="customer_no" value="{{ old('customer_no', $customerNo) }}" readonly
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-gray-500 cursor-not-allowed focus:ring-0 focus:border-gray-200">
                                        @error('customer_no')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer Name <span class="text-red-500">*</span></label>
                                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" required placeholder="e.g. John Doe"
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-black/5 focus:border-black transition-all">
                                        @error('customer_name')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                        <input type="email" name="email" value="{{ old('email') }}" placeholder="john@example.com"
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-black/5 focus:border-black transition-all">
                                        @error('email')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+1 (555) 000-0000"
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-black/5 focus:border-black transition-all">
                                        @error('phone')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Attention</label>
                                        <input type="text" name="attention" value="{{ old('attention') }}" placeholder="Contact Person"
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-black/5 focus:border-black transition-all">
                                        @error('attention')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Designation</label>
                                        <input type="text" name="designation" value="{{ old('designation') }}" placeholder="Job Title"
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-black/5 focus:border-black transition-all">
                                        @error('designation')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                        <input type="text" name="department" value="{{ old('department') }}" placeholder="e.g. Sales"
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-black/5 focus:border-black transition-all">
                                        @error('department')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                        <textarea name="address" rows="3" placeholder="Full address"
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-black/5 focus:border-black transition-all resize-none">{{ old('address') }}</textarea>
                                        @error('address')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-4">
                            <a href="{{ route('tenant.customers.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2.5 bg-black text-white text-sm font-medium rounded-lg hover:bg-gray-800 focus:ring-4 focus:ring-gray-200 transition-all shadow-sm">
                                Create Customer
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sidebar / Help / Info -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-medium text-gray-900 mb-4">Quick Tips</h3>
                    <ul class="space-y-3 text-sm text-gray-500">
                        <li class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span><strong>Companies</strong> represent the organization your customer belongs to. Always select or create a company first.</span>
                        </li>
                        <li class="flex gap-3">
                            <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span><strong>Customer Number</strong> is auto-generated based on the selected company code.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Minimal Company Modal -->
    <div x-data="companyModal"
        @open-company-modal.window="openModal()"
        @edit-company-modal.window="openEditModal($event.detail)"
        x-show="showModal"
        x-cloak
        class="relative z-50"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true">

        <div x-show="showModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/20 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="showModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">

                    <form @submit.prevent="submitCompany">
                        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                            <h3 class="text-lg font-semibold text-gray-900" x-text="isEditing ? 'Edit Company' : 'Add New Company'"></h3>
                            <button type="button" @click="closeModal" class="text-gray-400 hover:text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="px-6 py-6 space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Company Code <span class="text-red-500">*</span></label>
                                <input type="text" x-model="companyForm.company_code" required
                                    @input="companyForm.company_code = $event.target.value.replace(/[^A-Za-z]/g, '').toUpperCase()"
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-black/5 focus:border-black transition-all placeholder-gray-400 uppercase font-mono text-sm"
                                    placeholder="e.g. ABC" maxlength="10">
                                <p class="text-xs text-gray-500 mt-1">Used for generating customer numbers (e.g. ABC-001).</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Company Name <span class="text-red-500">*</span></label>
                                <input type="text" x-model="companyForm.name" required
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-black/5 focus:border-black transition-all placeholder-gray-400"
                                    placeholder="Legal company name">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                                    <input type="email" x-model="companyForm.email"
                                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-black/5 focus:border-black transition-all placeholder-gray-400"
                                        placeholder="contact@company.com">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                                    <input type="text" x-model="companyForm.phone"
                                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-black/5 focus:border-black transition-all placeholder-gray-400"
                                        placeholder="+1 234 567 890">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">BIN No</label>
                                    <input type="text" x-model="companyForm.bin_no"
                                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-black/5 focus:border-black transition-all placeholder-gray-400">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Address <span class="text-red-500">*</span></label>
                                <textarea x-model="companyForm.address" rows="2" required
                                    class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-black/5 focus:border-black transition-all placeholder-gray-400 resize-none"
                                    placeholder="Company HQ address"></textarea>
                            </div>

                            <!-- Error Display -->
                            <div x-show="Object.keys(errors).length > 0" class="p-3 bg-red-50 rounded-lg text-sm text-red-600">
                                <template x-for="(error, field) in errors" :key="field">
                                    <p x-text="error"></p>
                                </template>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                                Cancel
                            </button>
                            <button type="submit" :disabled="loading" class="px-4 py-2 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm min-w-[100px] flex justify-center">
                                <span x-show="!loading" x-text="isEditing ? 'Save Changes' : 'Create Company'"></span>
                                <span x-show="loading" class="flex items-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-dashboard.layout.default>

<script>
    document.addEventListener('alpine:init', () => {
        // Company Modal Component
        Alpine.data('companyModal', () => ({
            showModal: false,
            loading: false,
            isEditing: false,
            editingCompanyId: null,
            companyForm: {
                company_code: '',
                name: '',
                email: '',
                phone: '',
                bin_no: '',
                address: ''
            },
            errors: {},

            openModal() {
                this.showModal = true;
                this.isEditing = false;
                this.editingCompanyId = null;
                this.resetForm();
            },

            async openEditModal(company) {
                this.showModal = true;
                this.isEditing = true;
                this.editingCompanyId = company.id;
                this.loading = true;

                // Initialize with available data while loading
                this.companyForm = {
                    company_code: '',
                    name: company.name,
                    email: '',
                    phone: '',
                    bin_no: '',
                    address: ''
                };

                try {
                    const response = await fetch(`/companies/${company.id}`);
                    if (!response.ok) throw new Error('Failed to fetch company details');
                    const fullCompany = await response.json();

                    this.companyForm = {
                        company_code: fullCompany.company_code || '',
                        name: fullCompany.name,
                        email: fullCompany.email || '',
                        phone: fullCompany.phone || '',
                        bin_no: fullCompany.bin_no || '',
                        address: fullCompany.address || ''
                    };
                } catch (error) {
                    console.error('Error fetching company details:', error);
                    alert('Error loading company details. Please try again.');
                    // Optional: keep modal open with partial data or close it
                } finally {
                    this.loading = false;
                }

                this.errors = {};
            },

            closeModal() {
                this.showModal = false;
                this.resetForm();
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
                this.errors = {};
                this.loading = false;
                this.isEditing = false;
                this.editingCompanyId = null;
            },

            async submitCompany() {
                this.loading = true;
                this.errors = {};

                if (!this.companyForm.address) {
                    this.errors = { address: ['The address field is required.'] };
                    this.loading = false;
                    return;
                }

                try {
                    const url = this.isEditing ?
                        `/companies/${this.editingCompanyId}` :
                        '{{ route('companies.store') }}';

                    const method = this.isEditing ? 'PUT' : 'POST';

                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.companyForm)
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // The controller returns the company object directly (store/update)
                        const companyData = data;

                        if (this.isEditing) {
                            if (window.companySearchInstance) {
                                window.companySearchInstance.updateCompany(companyData);
                            }
                        } else {
                            if (window.companySearchInstance) {
                                window.companySearchInstance.addNewCompany(companyData);
                            }
                        }
                        this.closeModal();

                    } else {
                        this.errors = data.errors || {};
                    }
                } catch (error) {
                    console.error('Error saving company:', error);
                    this.errors = {
                        general: 'An error occurred while saving the company.'
                    };
                } finally {
                    this.loading = false;
                }
            }
        }));

        // Company Searchable Select Component with Edit Functionality
        Alpine.data('companySearchableSelect', (config) => ({
            open: false,
            searchTerm: '',
            selectedValue: config.value || '',
            selectedCompany: null,
            allOptions: [],
            filteredOptions: [],
            endpoint: config.endpoint,
            loading: true,

            init() {
                window.companySearchInstance = this;

                if (!this.endpoint) {
                    this.loading = false;
                    console.error('CompanySearchableSelect: API endpoint is not defined.');
                    return;
                }

                this.fetchCompanies();
            },

            async fetchCompanies() {
                try {
                    const response = await fetch(this.endpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    if (!response.ok) throw new Error('Network response was not ok');

                    const data = await response.json();
                    this.allOptions = data;

                    if (this.selectedValue) {
                        const selected = this.allOptions.find(opt => opt.id == this.selectedValue);
                        if (selected) {
                            this.searchTerm = selected.name;
                            this.selectedCompany = selected;
                        }
                    }

                    this.filterOptions();
                    this.loading = false;
                } catch (error) {
                    console.error('Error fetching companies:', error);
                    this.loading = false;
                }
            },

            filterOptions() {
                if (!this.searchTerm) {
                    this.filteredOptions = this.allOptions;
                    return;
                }
                this.filteredOptions = this.allOptions.filter(option => {
                    const term = this.searchTerm.toLowerCase();
                    return option.name.toLowerCase().includes(term) ||
                           (option.company_code && option.company_code.toLowerCase().includes(term));
                });
            },

            selectOption(option) {
                this.selectedValue = option.id;
                this.selectedCompany = option;
                this.searchTerm = option.name;
                this.open = false;

                // Generate customer number when company is changed
                this.generateCustomerNumber(option);
            },

            checkSelection() {
                setTimeout(() => {
                    if (!this.selectedValue) {
                        this.searchTerm = '';
                    } else {
                        const selected = this.allOptions.find(opt => opt.id == this.selectedValue);
                        if (selected) {
                            this.searchTerm = selected.name;
                        }
                    }
                }, 200);
            },

            async generateCustomerNumber(company) {
                try {
                    const customerNoInput = document.querySelector('input[name="customer_no"]');
                    const currentCustomerNo = customerNoInput ? customerNoInput.value : '';

                    // Only generate new customer number if the current one doesn't start with the new company code
                    if (company.company_code && (!currentCustomerNo || !currentCustomerNo.startsWith(company.company_code + '-'))) {
                        const response = await fetch(`/companies/${company.id}/next-customer-serial`);
                        const data = await response.json();

                        if (data.customer_no && customerNoInput) {
                            customerNoInput.value = data.customer_no;
                        }
                    }
                } catch (error) {
                    console.error('Error generating customer number:', error);
                }
            },

            openModal() {
                this.open = false;
                this.$dispatch('open-company-modal');
            },

            editCompany(company) {
                this.open = false;
                this.$dispatch('edit-company-modal', company);
            },

            async deleteCompany(company) {
                this.open = false;

                if (!confirm(
                    `Are you sure you want to delete "${company.name}"?\n\nThis action cannot be undone and will fail if the company has associated customers.`
                )) {
                    return;
                }

                try {
                    const response = await fetch(`/companies/${company.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.allOptions = this.allOptions.filter(opt => opt.id !== company.id);
                        this.filterOptions();

                        if (this.selectedValue == company.id) {
                            this.selectedValue = '';
                            this.selectedCompany = null;
                            this.searchTerm = '';
                        }

                        alert(data.message || 'Company deleted successfully');
                    } else {
                        alert(data.message || data.error || 'Failed to delete company');
                    }
                } catch (error) {
                    console.error('Error deleting company:', error);
                    alert('An error occurred while deleting the company');
                }
            },

            addNewCompany(company) {
                this.allOptions.unshift(company);
                this.selectOption(company);
                this.filterOptions();
            },

            updateCompany(updatedCompany) {
                const index = this.allOptions.findIndex(opt => opt.id === updatedCompany.id);
                if (index !== -1) {
                    this.allOptions[index] = updatedCompany;

                    if (this.selectedValue == updatedCompany.id) {
                        this.selectOption(updatedCompany);
                    }

                    this.filterOptions();
                }
            }
        }));
    });
</script>

<style>
    [x-cloak] {
        display: none !important;
    }

    /* Minimal Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #e5e7eb;
        border-radius: 20px;
    }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb {
        background-color: #d1d5db;
    }
</style>
