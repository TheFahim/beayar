import { DateRangePicker } from "flowbite-datepicker";

function quotationsPageData(indexRoute, startValue, endValue) {
    return {
        // For filters
        filtersOpen: true,
        dateRangePicker: null,

        resetFilters() {
            this.$refs.searchInput.value = '';
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
