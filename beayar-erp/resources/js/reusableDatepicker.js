import { Datepicker } from "flowbite";

/**
 * Reusable Datepicker Utility
 * Initializes Flowbite datepickers on all elements with the specified class
 */
class ReusableDatepicker {
    constructor(className = 'flowbite-datepicker', options = {}) {
        this.className = className;
        this.defaultOptions = {
            autohide: true,
            format: 'yyyy-mm-dd',
            title: null,
            rangePicker: false,
            onShow: () => { },
            onHide: () => { },
            ...options
        };

        this.init();
    }

    /**
     * Initialize datepickers on all elements with the specified class
     */
    init() {
        const datepickerElements = document.querySelectorAll(`.${this.className}`);

        datepickerElements.forEach((element, index) => {
            // Skip if already initialized
            if (element.hasAttribute('data-datepicker-initialized')) {
                return;
            }

            // Create unique instance options for each datepicker
            const instanceOptions = {
                id: `datepicker-${this.className}-${index}`,
                override: true
            };

            // Initialize the datepicker
            const datepicker = new Datepicker(element, this.defaultOptions, instanceOptions);

            // Mark as initialized
            element.setAttribute('data-datepicker-initialized', 'true');

            // Forward Flowbite's changeDate to native events so Alpine x-model picks up changes
            element.addEventListener('changeDate', () => {
                // Dispatch both input and change events to cover bindings
                element.dispatchEvent(new Event('input', { bubbles: true }));
                element.dispatchEvent(new Event('change', { bubbles: true }));
            });

            // Store the datepicker instance on the element for future reference
            element._datepickerInstance = datepicker;
        });
    }

    /**
     * Reinitialize datepickers (useful for dynamically added elements)
     */
    reinit() {
        this.init();
    }

    /**
     * Get datepicker instance for a specific element
     */
    getInstance(element) {
        return element._datepickerInstance || null;
    }

    /**
     * Destroy all datepickers with this class
     */
    destroy() {
        const datepickerElements = document.querySelectorAll(`.${this.className}`);

        datepickerElements.forEach(element => {
            if (element._datepickerInstance) {
                element._datepickerInstance.destroy();
                element.removeAttribute('data-datepicker-initialized');
                delete element._datepickerInstance;
            }
        });
    }
}

/**
 * Initialize default datepickers when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function () {
    // Initialize quotation datepickers with class 'quotation-datepicker'
    // Use display format consistent with forms: dd/mm/yyyy

    let dateFormat = 'dd/mm/yyyy';

    // Check if configuration is available globally
    if (window.quotationFormConfig && window.quotationFormConfig.companySettings && window.quotationFormConfig.companySettings.date_format) {
        const phpFormat = window.quotationFormConfig.companySettings.date_format;
        // Map PHP to Flowbite/JS format
        if (phpFormat === 'd-m-Y') dateFormat = 'dd-mm-yyyy';
        else if (phpFormat === 'Y-m-d') dateFormat = 'yyyy-mm-dd';
        else if (phpFormat === 'm/d/Y') dateFormat = 'mm/dd/yyyy';
        else if (phpFormat === 'd/m/Y') dateFormat = 'dd/mm/yyyy';
        else if (phpFormat === 'd M, Y') dateFormat = 'dd M, yyyy';
    }

    window.quotationDatepicker = new ReusableDatepicker('quotation-datepicker', {
        format: dateFormat,
        autohide: true,
        orientation: 'bottom'
    });

    // Initialize generic flowbite datepickers
    window.flowbiteDatepicker = new ReusableDatepicker('flowbite-datepicker', {
        format: 'dd/mm/yyyy',
        autohide: true,
        orientation: 'bottom'
    });
});
