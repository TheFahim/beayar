<div x-data="brandOriginModal" @open-brand-origin-modal.window="openModal()"
    @edit-brand-origin-modal.window="openEditModal($event.detail)" x-show="showModal" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showModal" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form @submit.prevent="submitBrandOrigin">
                <div
                    class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-800 dark:to-gray-900 px-6 pt-6 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg" x-show="!isEditing">
                            <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M13.09 3.294c1.924.95 3.422 1.69 5.472.692a1 1 0 0 1 1.438.9v9.54a1 1 0 0 1-.562.9c-2.981 1.45-5.382.24-7.25-.701a38.739 38.739 0 0 0-.622-.31c-1.033-.497-1.887-.812-2.756-.77-.76.036-1.672.357-2.81 1.396V21a1 1 0 1 1-2 0V4.971a1 1 0 0 1 .297-.71c1.522-1.506 2.967-2.185 4.417-2.255 1.407-.068 2.653.453 3.72.967.225.108.443.216.655.32Z" />
                            </svg>

                        </div>
                        <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg" x-show="isEditing">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="modal-title">
                                <span x-show="!isEditing">Add New Brand Origin</span>
                                <span x-show="isEditing">Edit Brand Origin</span>
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <span x-show="!isEditing">Create a new brand origin record</span>
                                <span x-show="isEditing">Update brand origin information</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Brand Origin Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="brandOriginForm.name" required
                            class="w-full p-3 border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200"
                            placeholder="Enter brand origin name">
                        <p x-show="errors.name" x-text="errors.name"
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
                            Add Brand Origins
                        </span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
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

<script>
    document.addEventListener('alpine:init', () => {
        // Company Modal Component
        Alpine.data('brandOriginModal', () => ({
            showModal: false,
            loading: false,
            isEditing: false,
            editingBrandOriginId: null,
            brandOriginForm: {
                name: '',
            },
            errors: {},

            openModal() {
                this.showModal = true;
                this.isEditing = false;
                this.editingBrandOriginId = null;
                this.resetForm();
            },

            openEditModal(brandOrigin) {
                this.showModal = true;
                this.isEditing = true;
                this.editingBrandOriginId = brandOrigin.id;
                this.brandOriginForm = {
                    name: brandOrigin.name,
                };
                this.errors = {};
            },

            closeModal() {
                this.showModal = false;
                this.resetForm();
            },

            resetForm() {
                this.brandOriginForm = {
                    name: '',
                };
                this.errors = {};
                this.loading = false;
                this.isEditing = false;
                this.editingBrandOriginId = null;
            },

            async submitBrandOrigin() {
                this.loading = true;
                this.errors = {};

                try {
                    const url = this.isEditing ?
                        `/brand-origins/${this.editingBrandOriginId}` :
                        '{{ route('tenant.brand-origins.store') }}';

                    const method = this.isEditing ? 'PUT' : 'POST';

                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.brandOriginForm)
                    });

                    const data = await response.json();

                    if (response.ok) {
                        if (this.isEditing) {
                            // Dispatch global event for update
                            window.dispatchEvent(new CustomEvent('brand-origin-updated', {
                                detail: data.brandOrigin
                            }));
                        } else {
                            // Dispatch global event for new addition
                            window.dispatchEvent(new CustomEvent('brand-origin-added', {
                                detail: data.brandOrigin
                            }));
                        }
                        this.closeModal();

                    } else {
                        this.errors = data.errors || {};
                    }
                } catch (error) {
                    console.error('Error saving brand origin:', error);
                    this.errors = {
                        general: 'An error occurred while saving the brand origin.'
                    };
                } finally {
                    this.loading = false;
                }
            }
        }));

        // Brand Origin Searchable Select Component with Edit and Delete Functionality
        Alpine.data('brandOriginSearchableSelect', (config) => ({
            open: false,
            searchTerm: '',
            selectedValue: config.value || '',
            selectedBrandOrigin: null,
            allOptions: [],
            filteredOptions: [],
            endpoint: config.endpoint,
            loading: true,
            componentId: Math.random().toString(36).substr(2, 9),

            init() {
                if (!this.endpoint) {
                    this.loading = false;
                    console.error('BrandOriginSearchableSelect: API endpoint is not defined.');
                    return;
                }

                this.fetchBrandOrigins();

                // Listen for global events
                window.addEventListener('brand-origin-added', (e) => {
                    this.addNewBrandOrigin(e.detail);
                });

                window.addEventListener('brand-origin-updated', (e) => {
                    this.updateBrandOrigin(e.detail);
                });

                window.addEventListener('brand-origin-deleted', (e) => {
                    this.handleBrandOriginDeleted(e.detail);
                });
            },

            async fetchBrandOrigins() {
                try {
                    const response = await fetch(this.endpoint);
                    const data = await response.json();
                    this.allOptions = data;

                    // If a value is pre-selected, find and display it
                    if (this.selectedValue) {
                        const selected = this.allOptions.find(opt => opt.id == this
                            .selectedValue);
                        if (selected) {
                            this.searchTerm = selected.name;
                            this.selectedBrandOrigin = selected;
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
                    return option.name.toLowerCase().includes(this.searchTerm
                        .toLowerCase());
                });
            },

            selectOption(option) {
                this.selectedValue = option.id;
                this.selectedBrandOrigin = option;
                this.searchTerm = option.name;
                this.open = false;

                // Generate customer number when company is selected
                this.generateCustomerNumber(option);
            },

            async generateCustomerNumber(company) {
                try {
                    const response = await fetch(
                        `/companies/${company.id}/next-customer-serial`);
                    const data = await response.json();

                    if (data.customer_no) {
                        // Update the customer number input field
                        const customerNoInput = document.querySelector(
                            'input[name="customer_no"]');
                        if (customerNoInput) {
                            customerNoInput.value = data.customer_no;
                        }
                    }
                } catch (error) {
                    console.error('Error generating customer number:', error);
                }
            },

            openModal() {
                this.open = false;
                // Set this component as the active one requesting the add
                window.activeBrandOriginSelectId = this.componentId;
                // Trigger the modal component
                this.$dispatch('open-brand-origin-modal');
            },

            editBrandOrigin(brandOrigin) {
                this.open = false;
                // Dispatch event to open edit modal with brand origin data
                this.$dispatch('edit-brand-origin-modal', brandOrigin);
            },

            async deleteBrandOrigin(brandOrigin) {
                if (!confirm(`Are you sure you want to delete ${brandOrigin.name}?`)) {
                    return;
                }

                try {
                    const response = await fetch(`/brand-origins/${brandOrigin.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        window.dispatchEvent(new CustomEvent('brand-origin-deleted', {
                            detail: brandOrigin
                        }));
                    } else {
                        console.error('Failed to delete brand origin');
                    }
                } catch (error) {
                    console.error('Error deleting brand origin:', error);
                }
            },

            addNewBrandOrigin(brandOrigin) {
                // Add to the beginning of the array if not already present
                if (!this.allOptions.find(o => o.id === brandOrigin.id)) {
                    this.allOptions.unshift(brandOrigin);
                    
                    // Only select if this was the component that initiated the add
                    if (window.activeBrandOriginSelectId === this.componentId) {
                        this.selectOption(brandOrigin);
                        window.activeBrandOriginSelectId = null;
                    }
                    
                    this.filterOptions();
                }
            },

            updateBrandOrigin(updatedBrandOrigin) {
                // Find and update the brand origin in the list
                const index = this.allOptions.findIndex(c => c.id === updatedBrandOrigin.id);
                if (index !== -1) {
                    this.allOptions[index] = updatedBrandOrigin;

                    // If this brand origin is currently selected, update the display
                    if (this.selectedValue == updatedBrandOrigin.id) {
                        this.searchTerm = updatedBrandOrigin.name;
                        this.selectedBrandOrigin = updatedBrandOrigin;
                    }

                    this.filterOptions();
                }
            },

            handleBrandOriginDeleted(deletedBrandOrigin) {
                this.allOptions = this.allOptions.filter(c => c.id !== deletedBrandOrigin.id);
                this.filterOptions();

                // If the deleted brand origin was selected, clear the selection
                if (this.selectedValue == deletedBrandOrigin.id) {
                    this.selectedValue = '';
                    this.searchTerm = '';
                    this.selectedBrandOrigin = null;
                }
            }
        }));
    });
</script>

<style>
    [x-cloak] {
        display: none !important;
    }

    /* Ensure smooth scrolling for brand origin list */
    .max-h-60 {
        scroll-behavior: smooth;
    }

    /* Add subtle shadow to fixed button for better visibility */
    .absolute.bottom-0 {
        box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06);
    }
</style>
