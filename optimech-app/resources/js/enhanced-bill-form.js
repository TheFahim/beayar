/**
 * Enhanced Billing Alpine.js Component
 * Comprehensive billing system with advanced validation and calculations
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('enhancedBillForm', (data) => ({
        // Core data
        billType: data.billType || 'advance',
        quotation: data.quotation,
        challans: data.challans || [],
        activeRevision: data.activeRevision,
        parentBill: data.parentBill,
        existingAdvanceBill: data.existingAdvanceBill,
        existingRegularBills: data.existingRegularBills || [],

        // Form data
        advancePercentage: 50,
        totalAmount: 0,
        paidAmount: 0,
        dueAmount: 0,
        installmentPercentage: 0,
        installmentAmount: 0,
        selectedChallansCount: 0,

        // Validation state
        errors: {
            advancePercentage: null,
            paymentAmount: null,
            installmentPercentage: null,
            regularSelection: null,
            invoiceNumber: null,
            billDate: null
        },

        // UI state
        loading: false,
        isValidating: false,
        validationTimeout: null,

        // Enhanced initialization
        init() {
            try {
                this.loading = true;
                this.initializeForm();
                this.setupEventListeners();
                this.validateInitialState();
                this.setupRealTimeValidation();
            } catch (error) {
                console.error('Enhanced bill form initialization failed:', error);
                this.showToast('error', 'Failed to initialize bill form. Please refresh the page.');
                this.setSafeDefaults();
            } finally {
                this.loading = false;
            }
        },

        // Initialize form based on bill type
        initializeForm() {
            try {
                switch (this.billType) {
                    case 'advance':
                        this.initializeAdvanceForm();
                        break;
                    case 'regular':
                        this.initializeRegularForm();
                        break;
                    case 'running':
                        this.initializeRunningForm();
                        break;
                }
                this.calculateDue();
            } catch (error) {
                console.warn('Form initialization failed:', error);
                this.setSafeDefaults();
            }
        },

        // Initialize advance bill form
        initializeAdvanceForm() {
            const quotationTotal = this.getQuotationTotal();
            if (quotationTotal > 0) {
                this.totalAmount = quotationTotal.toFixed(2);
                this.calculateAdvanceAmount();
            } else {
                this.setSafeDefaults();
            }
        },

        // Initialize regular bill form
        initializeRegularForm() {
            try {
                // Ensure challans data is available
                if (!this.challans || !Array.isArray(this.challans)) {
                    console.warn('Challans data not available for regular bill initialization');
                    this.totalAmount = '0.00';
                    this.selectedChallansCount = 0;
                    return;
                }

                this.calculateRegularBillTotal();
            } catch (error) {
                console.warn('Regular bill initialization failed:', error);
                this.totalAmount = '0.00';
                this.selectedChallansCount = 0;
                this.showToast('error', 'Failed to initialize regular bill form');
            }
        },

        // Initialize running bill form
        initializeRunningForm() {
            if (this.parentBill) {
                this.updateRunningBillData();
            } else {
                this.setSafeDefaults();
            }
        },

        // Set safe default values
        setSafeDefaults() {
            this.totalAmount = '0.00';
            this.paidAmount = '0.00';
            this.dueAmount = '0.00';
            this.advancePercentage = 50;
            this.installmentPercentage = 0;
            this.installmentAmount = '0.00';
        },

        // Get quotation total
        getQuotationTotal() {
            return parseFloat(this.quotation?.active_revision?.total || this.activeRevision?.total || 0);
        },

        // Setup event listeners
        setupEventListeners() {
            // Watch for challan selection changes
            this.$watch('selectedChallansCount', () => {
                if (this.billType === 'regular') {
                    this.calculateRegularBillTotal();
                }
            });

            // Watch for bill type changes
            this.$watch('billType', () => {
                this.initializeForm();
            });

            // Watch for advance percentage changes
            this.$watch('advancePercentage', (newVal, oldVal) => {
                if (newVal !== oldVal && this.billType === 'advance') {
                    this.debouncedAdvanceCalculation();
                }
            });

            // Watch for installment percentage changes
            this.$watch('installmentPercentage', (newVal, oldVal) => {
                if (newVal !== oldVal && this.billType === 'running') {
                    this.calculateInstallmentAmount();
                }
            });

            // Watch for payment amount changes
            this.$watch('paidAmount', (newVal, oldVal) => {
                if (newVal !== oldVal) {
                    this.calculateDue();
                    this.validatePaymentAmount();
                }
            });
        },

        // Setup real-time validation
        setupRealTimeValidation() {
            // Add input event listeners for real-time validation
            this.$nextTick(() => {
                const form = this.$el.closest('form');
                if (form) {
                    // Invoice number validation
                    const invoiceField = form.querySelector('#invoice_no');
                    if (invoiceField) {
                        invoiceField.addEventListener('input', this.debounce(() => {
                            this.validateInvoiceNumber();
                        }, 300));
                    }

                    // Bill date validation
                    const billDateField = form.querySelector('#bill_date');
                    if (billDateField) {
                        billDateField.addEventListener('change', () => {
                            this.validateBillDate();
                        });
                    }
                }
            });
        },

        // Enhanced advance amount calculation
        calculateAdvanceAmount() {
            try {
                const quotationTotal = this.getQuotationTotal();

                // Validate advance percentage
                const validation = this.validateAdvancePercentage();
                if (!validation.valid) {
                    this.errors.advancePercentage = validation.message;
                    return;
                }
                this.errors.advancePercentage = null;

                // Calculate amounts
                this.totalAmount = quotationTotal.toFixed(2);
                this.paidAmount = (this.totalAmount * this.advancePercentage / 100).toFixed(2);

                this.showToast('info', `Advance amount calculated: ${this.formatCurrency(this.paidAmount)}`);

            } catch (error) {
                console.warn('Advance amount calculation failed:', error);
                this.showToast('error', 'Failed to calculate advance amount');
                this.setSafeDefaults();
            }
        },

        // Enhanced regular bill calculation
        calculateRegularBillTotal() {
            try {
                // Validate challans data availability
                if (!this.challans || !Array.isArray(this.challans) || this.challans.length === 0) {
                    console.warn('No challans available for calculation');
                    this.totalAmount = '0.00';
                    this.selectedChallansCount = 0;
                    this.errors.regularSelection = 'No challans available for this quotation';
                    return;
                }

                const selectedChallans = document.querySelectorAll('input[name="challan_ids[]"]:checked');
                this.selectedChallansCount = selectedChallans.length;

                if (this.selectedChallansCount === 0) {
                    this.totalAmount = '0.00';
                    this.errors.regularSelection = 'Please select at least one challan';
                    return;
                }
                this.errors.regularSelection = null;

                let total = 0;
                let processedChallans = 0;

                selectedChallans.forEach(checkbox => {
                    const challanId = parseInt(checkbox.value);
                    const challan = this.challans.find(c => c.id === challanId);

                    if (!challan) {
                        console.warn(`Challan with ID ${challanId} not found in data`);
                        return;
                    }

                    if (challan.products && Array.isArray(challan.products)) {
                        processedChallans++;
                        challan.products.forEach(product => {
                            if (product.quotation_product && product.quotation_product.unit_price) {
                                const quantity = this.toNumber(product.quantity);
                                const unitPrice = this.toNumber(product.quotation_product.unit_price);
                                if (!isNaN(quantity) && !isNaN(unitPrice)) {
                                    total += quantity * unitPrice;
                                }
                            }
                        });
                    } else {
                        console.warn(`Challan ${challanId} has no products or invalid product data`);
                    }
                });

                if (processedChallans === 0) {
                    console.warn('No valid challans with products found');
                    this.totalAmount = '0.00';
                    this.errors.regularSelection = 'Selected challans have no valid products';
                    return;
                }

                this.totalAmount = total.toFixed(2);
                this.paidAmount = this.totalAmount; // Regular bills are typically paid in full

                this.showToast('success', `Regular bill total calculated: ${this.formatCurrency(this.totalAmount)}`);

            } catch (error) {
                console.error('Regular bill calculation failed:', error);
                this.showToast('error', 'Failed to calculate regular bill total');
                this.totalAmount = '0.00';
                this.selectedChallansCount = 0;
            }
        },

        // Enhanced installment calculation
        calculateInstallmentAmount() {
            try {
                if (!this.parentBill) {
                    this.errors.installmentPercentage = 'Parent bill is required for running calculation';
                    return;
                }

                // Validate installment percentage
                const remaining = this.getRemainingPercentage();
                const validation = this.validateInstallmentPercentage(this.installmentPercentage, remaining);
                if (!validation.valid) {
                    this.errors.installmentPercentage = validation.message;
                    return;
                }
                this.errors.installmentPercentage = null;

                const parentTotal = this.toNumber(this.parentBill.total_amount);
                this.installmentAmount = (parentTotal * this.installmentPercentage / 100).toFixed(2);
                this.totalAmount = this.installmentAmount;
                this.paidAmount = this.installmentAmount;

                this.showToast('info', `Running amount calculated: ${this.formatCurrency(this.installmentAmount)} (${this.installmentPercentage}%)`);

            } catch (error) {
                console.warn('Installment calculation failed:', error);
                this.showToast('error', 'Failed to calculate installment amount');
                this.installmentAmount = '0.00';
            }
        },

        // Update running bill data
        updateRunningBillData() {
            if (this.parentBill) {
                const remaining = this.calculateRemaining(this.parentBill);
                this.remainingAmount = remaining.remainingAmount.toFixed(2);
                this.remainingPercentage = remaining.remainingPercentage.toFixed(2);
            }
        },

        // Calculate remaining amounts
        calculateRemaining(parentBill) {
            // This would typically fetch existing installments from server
            // For now, return placeholder values
            return {
                remainingAmount: this.toNumber(parentBill.total_amount),
                remainingPercentage: 100
            };
        },

        // Get remaining percentage
        getRemainingPercentage() {
            return this.remainingPercentage || 0;
        },

        // Enhanced due calculation
        calculateDue() {
            try {
                const total = this.toNumber(this.totalAmount);
                const paid = this.toNumber(this.paidAmount);

                // Apply business rules
                if (this.billType === 'advance') {
                    // For advance bills, due is typically 0 (full payment expected)
                    this.dueAmount = '0.00';
                } else {
                    this.dueAmount = Math.max(0, total - paid).toFixed(2);
                }

                this.validatePaymentAmount();

            } catch (error) {
                console.warn('Due calculation failed:', error);
                this.dueAmount = '0.00';
            }
        },

        // Enhanced validation methods
        validateAdvancePercentage() {
            const percent = this.toNumber(this.advancePercentage);

            if (isNaN(percent) || percent === null || percent === '') {
                return { valid: false, message: 'Advance percentage is required' };
            }

            if (percent <= 0) {
                return { valid: false, message: 'Advance percentage must be greater than 0' };
            }

            if (percent > 100) {
                return { valid: false, message: 'Advance percentage cannot exceed 100%' };
            }

            return { valid: true, message: 'Valid advance percentage' };
        },

        validateInstallmentPercentage(percentage, remainingPercentage) {
            const percent = this.toNumber(percentage);
            const remaining = this.toNumber(remainingPercentage);

            if (isNaN(percent) || percent === null || percent === '') {
                return { valid: false, message: 'Running percentage is required' };
            }

            if (percent <= 0) {
                return { valid: false, message: 'Running percentage must be greater than 0' };
            }

            if (percent > remaining) {
                return {
                    valid: false,
                    message: `Running percentage (${percent}%) exceeds remaining balance (${remaining}%)`
                };
            }

            return { valid: true, message: 'Valid running percentage' };
        },

        validatePaymentAmount() {
            const total = this.toNumber(this.totalAmount);
            const paid = this.toNumber(this.paidAmount);

            if (paid < 0) {
                this.errors.paymentAmount = 'Payment amount cannot be negative';
                return false;
            }

            if (paid > total) {
                this.errors.paymentAmount = 'Payment amount cannot exceed total amount';
                return false;
            }

            this.errors.paymentAmount = null;
            return true;
        },

        validateInvoiceNumber() {
            const invoiceField = document.getElementById('invoice_no');
            if (!invoiceField || !invoiceField.value.trim()) {
                this.errors.invoiceNumber = 'Invoice number is required';
                return false;
            }

            // Additional validation can be added here (format, uniqueness, etc.)
            this.errors.invoiceNumber = null;
            return true;
        },

        validateBillDate() {
            const billDateField = document.getElementById('bill_date');
            if (!billDateField || !billDateField.value) {
                this.errors.billDate = 'Bill date is required';
                return false;
            }

            // Additional date validation can be added here
            this.errors.billDate = null;
            return true;
        },

        validateInitialState() {
            if (this.billType === 'running' && !this.parentBill) {
                this.showToast('error', 'Parent bill is required for running bills');
            }

            if (this.billType === 'advance' && this.existingAdvanceBill) {
                this.showToast('warning', 'An advance bill already exists for this quotation');
            }
        },

        // Enhanced form submission validation
        validateForm(event) {
            let isValid = true;

            // Clear previous errors
            this.errors = {
                advancePercentage: null,
                paymentAmount: null,
                installmentPercentage: null,
                regularSelection: null,
                invoiceNumber: null,
                billDate: null
            };

            // Validate based on bill type
            switch (this.billType) {
                case 'advance':
                    const advanceValidation = this.validateAdvancePercentage();
                    if (!advanceValidation.valid) {
                        this.errors.advancePercentage = advanceValidation.message;
                        isValid = false;
                    }
                    break;

                case 'regular':
                    if (this.selectedChallansCount === 0) {
                        this.errors.regularSelection = 'Please select at least one challan';
                        isValid = false;
                    }
                    break;

                case 'running':
                    const installmentValidation = this.validateInstallmentPercentage(
                        this.installmentPercentage,
                        this.getRemainingPercentage()
                    );
                    if (!installmentValidation.valid) {
                        this.errors.installmentPercentage = installmentValidation.message;
                        isValid = false;
                    }
                    break;
            }

            // Validate common fields
            if (!this.validateInvoiceNumber()) {
                isValid = false;
            }

            if (!this.validateBillDate()) {
                isValid = false;
            }

            if (!this.validatePaymentAmount()) {
                isValid = false;
            }

            // Validate required fields
            if (!this.totalAmount || parseFloat(this.totalAmount) <= 0) {
                this.showToast('error', 'Total amount must be greater than 0');
                isValid = false;
            }

            if (!this.paidAmount || parseFloat(this.paidAmount) < 0) {
                this.showToast('error', 'Paid amount cannot be negative');
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();
                this.showToast('error', 'Please fix the validation errors before submitting');
                return false;
            }

            // Update hidden fields before submission
            this.updateHiddenFields();

            this.showToast('success', 'Form validation passed. Submitting...');
            return true;
        },

        // Update hidden form fields
        updateHiddenFields() {
            const form = this.$el.closest('form');
            if (form) {
                // Update total amount
                const totalAmountField = form.querySelector('input[name="total_amount"]');
                if (totalAmountField) {
                    totalAmountField.value = this.totalAmount;
                }

                // Update paid amount
                const paidAmountField = form.querySelector('input[name="paid"]');
                if (paidAmountField) {
                    paidAmountField.value = this.paidAmount;
                }

                // Update due amount
                const dueAmountField = form.querySelector('input[name="due"]');
                if (dueAmountField) {
                    dueAmountField.value = this.dueAmount;
                }

                // Update billed percentage
                const billedPercentageField = form.querySelector('input[name="bill_percentage"]');
                if (billedPercentageField) {
                    const percentage = this.billType === 'running' ? this.installmentPercentage : this.advancePercentage;
                    billedPercentageField.value = percentage;
                }
            }
        },

        // Utility methods
        formatCurrency(amount) {
            return window.BillingUtils ? window.BillingUtils.formatCurrency(amount) : `BDT ${parseFloat(amount).toFixed(2)}`;
        },

        toNumber(value) {
            return window.BillingUtils ? window.BillingUtils.toNumber(value) : parseFloat(value) || 0;
        },

        // Debounced calculation methods
        debouncedAdvanceCalculation: function () {
            clearTimeout(this.validationTimeout);
            this.validationTimeout = setTimeout(() => {
                this.calculateAdvanceAmount();
            }, 300);
        },

        // Enhanced toast notifications
        showToast(type, message, duration = 3000) {
            if (typeof window.showToast === 'function') {
                window.showToast(type, message, duration);
            } else {
                console.log(`[${type.toUpperCase()}] ${message}`);
                if (type === 'error') {
                    alert(`Error: ${message}`);
                }
            }
        },

        // Get bill type description
        getBillTypeDescription() {
            return window.BillingUtils ?
                window.BillingUtils.getBillTypeDescription(this.billType) :
                {
                    'advance': 'Initial payment before delivery',
                    'regular': 'Final bill against delivered challans',
                    'running': 'Installment payment against existing bill'
                }[this.billType] || '';
        },

        // Update notes character counter
        updateNotesCounter() {
            const notesTextarea = document.getElementById('notes');
            if (notesTextarea) {
                const counter = notesTextarea.value.length;
                const counterElement = document.getElementById('notes-counter');
                if (counterElement) {
                    counterElement.textContent = counter;
                }

                // Add visual feedback for character limit
                if (counter >= 450) {
                    notesTextarea.classList.add('border-yellow-400', 'dark:border-yellow-600');
                    notesTextarea.classList.remove('border-gray-300', 'dark:border-gray-600');
                } else {
                    notesTextarea.classList.remove('border-yellow-400', 'dark:border-yellow-600');
                    notesTextarea.classList.add('border-gray-300', 'dark:border-gray-600');
                }
            }
        },

        // Get remaining amounts for running bills
        get remainingAmount() {
            return this.parentBill ? (this.toNumber(this.parentBill.total_amount) - this.getTotalBilledAmount()) : 0;
        },

        get remainingPercentage() {
            return this.parentBill ? (100 - this.getTotalBilledPercentage()) : 0;
        },

        getTotalBilledAmount() {
            // This would typically be fetched from server
            // For now, return 0 as placeholder
            return 0;
        },

        getTotalBilledPercentage() {
            // This would typically be fetched from server
            // For now, return 0 as placeholder
            return 0;
        }
    }));
});
