<x-dashboard.layout.default title="Create Customer">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.customers.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200">
                <x-ui.svg.users class="h-3 w-3 me-2" />
                Customers
            </a>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 9 4-4-4-4" />
                </svg>
                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Create</span>
            </div>
        </li>
    </x-dashboard.ui.bread-crumb>

    <div class="max-w-4xl mx-auto">
        <x-ui.card
            class="shadow-xl border-0 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900">
            <form method="POST" action="{{ route('tenant.customers.store') }}">
                @csrf

                <!-- Header -->
                <div
                    class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                                </path>
                            </svg>
                        </div>
                        Create New Customer
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Add a new customer to your system with company
                        information</p>
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
                                <x-ui.form.input name="customer_no" label="Customer Number" placeholder="Auto-generated"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 bg-gray-50 dark:bg-gray-700"
                                    required value="{{ old('customer_no', $customerNo) }}" readonly />
                            </div>

                            <div>
                                <x-ui.form.input name="customer_name" label="Customer Name" placeholder="Ex. John Doe"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    value="{{ old('customer_name') }}" />
                            </div>

                            <div>
                                <x-ui.form.input name="attention" label="Attention" placeholder="Ex. Jane Smith"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    value="{{ old('attention') }}" />
                            </div>

                            <div>
                                <x-ui.form.input name="designation" label="Designation" placeholder="Ex. Manager"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    value="{{ old('designation') }}" />
                            </div>

                            <div>
                                <x-ui.form.input name="department" label="Department" placeholder="Ex. Sales"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    value="{{ old('department') }}" />
                            </div>

                            <div>
                                <x-ui.form.input name="phone" label="Phone" placeholder="Ex. +8801234567890"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    value="{{ old('phone') }}" />
                            </div>

                            <div>
                                <x-ui.form.input name="email" label="Email" type="email"
                                    placeholder="Ex. john@example.com"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    value="{{ old('email') }}" />
                            </div>

                            <div>
                                <x-ui.form.input name="address" label="Address" placeholder="Ex. 123 Main St, City"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                    value="{{ old('address') }}" />
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
                                        value: '{{ old('customer_company_id') }}',
                                        endpoint: '{{ route('companies.search') }}'
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
                                                            class="px-4 py-3 cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/20 border-b border-gray-100 dark:border-gray-700 last:border-b-0 transition-colors duration-200 group">
                                                            <div class="flex items-center justify-between">
                                                                <div @click="selectOption(option)" class="flex-1">
                                                                    <div class="font-medium text-gray-900 dark:text-white"
                                                                        x-text="option.company_code ? `${option.name} (${option.company_code})` : option.name">
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
                                                                        @click="editCompany(option)"
                                                                        class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors duration-200"
                                                                        title="Edit company">
                                                                        <svg class="w-4 h-4" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                stroke-width="2"
                                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                            </path>
                                                                        </svg>
                                                                    </button>
                                                                    <!-- Delete Button -->
                                                                    <button type="button"
                                                                        @click="deleteCompany(option)"
                                                                        class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors duration-200"
                                                                        title="Delete company">
                                                                        <svg class="w-4 h-4" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
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

                                            {{-- Fixed Create Company Button - appears after 3 records --}}
                                            <div x-show="!loading && filteredOptions.length > 3"
                                                class="absolute bottom-0 left-0 right-0 border-t-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg">
                                                <button type="button" @click="openModal"
                                                    class="w-full px-4 py-3 text-left text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-800 font-medium transition-colors duration-200 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                    Add new company
                                                </button>
                                            </div>

                                            {{-- Regular Create Company Button - shows when 3 or fewer items --}}
                                            <div x-show="!loading && filteredOptions.length <= 3 && filteredOptions.length > 0"
                                                class="border-t-2 border-gray-200 dark:border-gray-600">
                                                <button type="button" @click="openModal"
                                                    class="w-full px-4 py-3 text-left text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-800 font-medium transition-colors duration-200 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                    Add new company
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                    @error('customer_company_id')
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
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div
                    class="flex justify-end space-x-4 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <a href="{{ route('tenant.customers.index') }}"
                        class="px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-lg border-2 border-gray-300 hover:border-gray-400 transition-all duration-200 hover:scale-105 active:scale-95 shadow-sm hover:shadow-md">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium rounded-lg transition-all duration-300 hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl save-button">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        Create Customer
                    </button>
                </div>
            </form>
        </x-ui.card>

        {{-- Company Modal --}}
        <div x-data="companyModal" @open-company-modal.window="openModal()"
            @edit-company-modal.window="openEditModal($event.detail)" x-show="showModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form @submit.prevent="submitCompany">
                        <div
                            class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-800 dark:to-gray-900 px-6 pt-6 pb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg" x-show="!isEditing">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                </div>
                                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg" x-show="isEditing">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="modal-title">
                                        <span x-show="!isEditing">Add New Company</span>
                                        <span x-show="isEditing">Edit Company</span>
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span x-show="!isEditing">Create a new company record</span>
                                        <span x-show="isEditing">Update company information</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Company Code <span class="text-red-500">*</span>
                                </label>
                                <input type="text" x-model="companyForm.company_code" required
                                    @input="companyForm.company_code = $event.target.value.replace(/[^A-Za-z]/g, '').toUpperCase()"
                                    @keydown="if($event.key === ' ' || /[0-9]/.test($event.key) || /[^A-Za-z]/.test($event.key)) $event.preventDefault()"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200"
                                    placeholder="Enter company code (letters only, will be capitalized)"
                                    maxlength="10">
                                <p x-show="errors.company_code" x-text="errors.company_code"
                                    class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Company Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" x-model="companyForm.name" required
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200"
                                    placeholder="Enter company name">
                                <p x-show="errors.name" x-text="errors.name"
                                    class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Email
                                </label>
                                <input type="email" x-model="companyForm.email"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200"
                                    placeholder="Enter company email">
                                <p x-show="errors.email" x-text="errors.email"
                                    class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </p>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Phone
                                    </label>
                                    <input type="text" x-model="companyForm.phone"
                                        class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200"
                                        placeholder="Enter phone number">
                                    <p x-show="errors.phone" x-text="errors.phone"
                                        class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        BIN No
                                    </label>
                                    <input type="text" x-model="companyForm.bin_no"
                                        class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200"
                                        placeholder="Enter BIN number">
                                    <p x-show="errors.bin_no" x-text="errors.bin_no"
                                        class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Address (Optional)
                                </label>
                                <input type="text" x-model="companyForm.address"
                                    class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200"
                                    placeholder="Enter company address">
                                <p x-show="errors.address" x-text="errors.address"
                                    class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </p>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-end space-x-3">
                            <button type="button" @click="closeModal"
                                class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-lg border-2 border-gray-300 hover:border-gray-400 transition-all duration-200 hover:scale-105 active:scale-95 dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-700">
                                Cancel
                            </button>
                            <button type="submit" :disabled="loading"
                                class="px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium rounded-lg transition-all duration-300 hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                                x-show="!isEditing">
                                <span x-show="!loading" class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add Company
                                </span>
                                <span x-show="loading" class="flex items-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Adding...
                                </span>
                            </button>
                            <button type="submit" :disabled="loading"
                                class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-medium rounded-lg transition-all duration-300 hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                                x-show="isEditing">
                                <span x-show="!loading" class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Update Company
                                </span>
                                <span x-show="loading" class="flex items-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Updating...
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
                    const response = await fetch(this.endpoint);
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

    /* Ensure smooth scrolling for company list */
    .max-h-60 {
        scroll-behavior: smooth;
    }

    /* Add subtle shadow to fixed button for better visibility */
    .absolute.bottom-0 {
        box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06);
    }
</style>