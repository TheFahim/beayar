import { DateRangePicker } from "flowbite-datepicker";

function quotationsPageData(indexRoute, startValue, endValue) {
    return {
        // For filters
        filtersOpen: true,
        dateRangePicker: null,

        resetFilters() {
            this.$refs.searchInput.value = '';
            this.$refs.statusSelect.value = '';
            if (this.$refs.typeSelect) this.$refs.typeSelect.value = '';
            if (this.$refs.savedAsSelect) this.$refs.savedAsSelect.value = '';
            if (this.dateRangePicker) {
                this.dateRangePicker.setDates();
            }
            window.location.href = indexRoute;
        },

        initDateRangePicker() {
            const datepickerEl = document.getElementById('quotation-date-range-picker');
            if (datepickerEl) {
                this.dateRangePicker = new DateRangePicker(datepickerEl, {
                    format: 'yyyy-mm-dd',
                    autohide: true,
                    clearBtn: true
                });

                // Set existing values if they exist
                if (startValue && endValue) {
                    this.dateRangePicker.setDates(new Date(startValue), new Date(endValue));
                }
            }
        },

        // For table and notifications
        expandedQuotations: [],
        alertMessage: '',
        alertType: '',
        showAlert: false,

        toggleExpansion(quotationId) {
            if (this.expandedQuotations.includes(quotationId)) {
                this.expandedQuotations = this.expandedQuotations.filter(id => id !== quotationId);
            } else {
                this.expandedQuotations.push(quotationId);
            }
        },

        async updateStatus(quotationId, newStatus, button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class=\'fas fa-spinner fa-spin mr-2\'></i>Updating...';
            button.disabled = true;

            try {
                const response = await fetch(`/quotations/${quotationId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']')
                            .getAttribute('content')
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const statusBadge = document.querySelector(
                        `tr[data-quotation-id='${quotationId}'] .status-badge`);
                    if (statusBadge) {
                        const baseClasses =
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium status-badge';
                        const statusColors = {
                            'in_progress': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200',
                            'active': 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200',
                            'completed': 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200',
                            'cancelled': 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200'
                        };
                        const newColorClasses = statusColors[newStatus] ||
                            'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200';

                        statusBadge.className = `${baseClasses} ${newColorClasses}`;
                        statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1)
                            .replace('_', ' ');
                    }
                    this.displayAlert('Status updated successfully!', 'success');
                } else {
                    this.displayAlert(data.message || 'Failed to update status. Please try again.',
                        'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.displayAlert('An error occurred. Please try again.', 'error');
            } finally {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        },

        displayAlert(message, type) {
            this.alertMessage = message;
            this.alertType = type;
            this.showAlert = true;
            setTimeout(() => {
                this.showAlert = false;
            }, 3000);
        }
    };
}

window.quotationsPageData = quotationsPageData;
