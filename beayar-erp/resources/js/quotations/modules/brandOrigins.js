
export function brandOriginSearchableSelect(config) {
    return {
        selectedValue: config.value,
        endpoint: config.endpoint,
        searchTerm: '',
        open: false,
        loading: false,
        filteredOptions: [],

        init() {
            // If there's an initial value, we might want to fetch the name
            // For now, we'll leave searchTerm empty until user interactions
            if (this.selectedValue) {
                // Ideally we would fetch the name here if not provided
            }

            this.$watch('selectedValue', (val) => {
                // Update hidden input if needed (handled by x-model/x-bind in blade)
            });
        },

        async filterOptions() {
            if (!this.searchTerm) {
                this.filteredOptions = [];
                return;
            }

            this.loading = true;
            try {
                const response = await fetch(`${this.endpoint}?q=${encodeURIComponent(this.searchTerm)}`);
                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                this.filteredOptions = data;
            } catch (error) {
                console.error('Error fetching brand origins:', error);
                this.filteredOptions = [];
            } finally {
                this.loading = false;
            }
        },

        selectOption(option) {
            this.selectedValue = option.id;
            this.searchTerm = option.name;
            this.open = false;
            // Update the row data in the parent component (quotationForm)
            // Since we are inside x-for, we modify the row object directly via x-model
            // But here we are in a separate component.
            // The parent blade uses: x-model="row.brand_origin_id" (Wait, no)
            // Blade: x-bind:value="selectedValue" on hidden input :name="'quotation_products[' + index + '][brand_origin_id]'"
            // And also: value: row.brand_origin_id passed to config.
            
            // To update the parent Alpine data 'row.brand_origin_id', we need to emit or use x-modelable if supported (Alpine v3).
            // Or since we passed 'value: row.brand_origin_id', it's just an initial value.
            // The hidden input ensures form submission works.
            // But if we want `row.brand_origin_id` to update in the parent (e.g. for other logic), we should probably dispatch event or access parent.
            // However, looking at products-section.blade.php:
            // <input type="hidden" :name="..." x-bind:value="selectedValue">
            // This updates the FORM data.
            // Does it update `row.brand_origin_id`?
            // No, unless we do something.
            // But maybe `row` is not reactive here?
            // Actually, `row` is from `x-for="(row, index) in quotation_products"`.
            // We should update `row.brand_origin_id` so that if we save/restore or do other logic it works.
            // We can dispatch an event caught by the parent.
            this.$dispatch('brand-origin-selected', { id: option.id, name: option.name });
            
            // Or better: The parent can listen to input on the hidden field if we trigger it.
        },

        editBrandOrigin(option) {
            this.$dispatch('edit-brand-origin-modal', option);
            this.open = false;
        },

        async deleteBrandOrigin(option) {
            if (!confirm('Are you sure you want to delete this brand origin?')) return;

            try {
                const response = await fetch(`/brand-origins/${option.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    this.filterOptions(); // Refresh list
                } else {
                    alert('Failed to delete brand origin');
                }
            } catch (error) {
                console.error('Error deleting brand origin:', error);
                alert('An error occurred');
            }
        }
    };
}

export function brandOriginModal() {
    return {
        showModal: false,
        isEditing: false,
        loading: false,
        brandOriginForm: {
            id: null,
            name: '',
            country: ''
        },
        errors: {},

        openModal() {
            this.resetForm();
            this.showModal = true;
            this.isEditing = false;
        },

        openEditModal(origin) {
            this.resetForm();
            this.brandOriginForm = { ...origin };
            this.showModal = true;
            this.isEditing = true;
        },

        closeModal() {
            this.showModal = false;
            this.resetForm();
        },

        resetForm() {
            this.brandOriginForm = {
                id: null,
                name: '',
                country: ''
            };
            this.errors = {};
        },

        async submitBrandOrigin() {
            this.loading = true;
            this.errors = {};

            try {
                const url = this.isEditing 
                    ? `/brand-origins/${this.brandOriginForm.id}`
                    : '/brand-origins';
                
                const method = this.isEditing ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.brandOriginForm)
                });

                const data = await response.json();

                if (response.ok) {
                    this.closeModal();
                    // Dispatch global event to refresh lists if needed
                    window.dispatchEvent(new CustomEvent('brand-origin-saved'));
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        alert(data.message || 'Something went wrong');
                    }
                }
            } catch (error) {
                console.error('Error submitting brand origin:', error);
                alert('An error occurred');
            } finally {
                this.loading = false;
            }
        }
    };
}
