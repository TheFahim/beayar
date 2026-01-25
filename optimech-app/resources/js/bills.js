/**
 * Enhanced Billing System - Alpine.js Component
 * Supports three bill types: advance, regular, running
 * Business rule validation and dynamic calculations
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('billForm', (data) => ({
        // Bill type and basic data
        billType: data.billType || 'advance',
        quotation: data.quotation,
        challans: data.challans || [],
        activeRevision: data.activeRevision,
        parentBill: data.parentBill,
        existingAdvanceBill: data.existingAdvanceBill,

        // Form data
        advancePercentage: 50,
        totalAmount: 0,
        paidAmount: 0,
        dueAmount: 0,
        installmentPercentage: 0,
        installmentAmount: 0,
        selectedChallansCount: 0,
        items: [],

        // UI state
        loading: false,
        errors: {
            advancePercentage: null,
            paymentAmount: null,
            installmentPercentage: null,
            regularSelection: null
        },

        // Timeout for debounced calculations
        advanceCalculationTimeout: null,

        // Initialize the component
        init() {
            try {
                this.loading = true;
                this.safeInitialize();
                this.setupEventListeners();
                this.validateInitialState();
            } catch (error) {
                console.error('Bill form initialization failed:', error);
                this.showError('Failed to initialize bill form. Please refresh the page.');
                this.setSafeDefaults();
            } finally {
                this.loading = false;
            }
        },

        // Add missing method that template calls
        getBillTypeDescription() {
            const descriptions = {
                'advance': 'Initial payment before delivery',
                'regular': 'Final bill against delivered challans',
                'running': 'Installment payment against existing bill'
            };
            return descriptions[this.billType] || '';
        },

        // Set safe default values
        setSafeDefaults() {
            this.totalAmount = '0.00';
            this.paidAmount = '0.00';
            this.dueAmount = '0.00';
            this.advancePercentage = 50;
            this.installmentPercentage = 0;
            this.installmentAmount = '0.00';
            this.selectedChallansCount = 0;
        },

        // Initialize form based on bill type
        initializeForm() {
            switch (this.billType) {
                case 'advance':
                    this.calculateAdvanceAmount();
                    this.buildAdvanceItems();
                    break;
                case 'regular':
                    this.calculateRegularBillTotal();
                    break;
                case 'running':
                    if (this.parentBill) {
                        this.updateRunningBillData();
                    }
                    break;
            }
            this.calculateDue();
        },

        // Add safe initialization with error handling
        safeInitialize() {
            try {
                switch (this.billType) {
                    case 'advance':
                        this.safeCalculateAdvanceAmount();
                        this.buildAdvanceItems();
                        break;
                    case 'regular':
                        this.safeCalculateRegularBillTotal();
                        break;
                    case 'running':
                        if (this.parentBill) {
                            this.safeUpdateRunningBillData();
                        }
                        break;
                }
                this.safeCalculateDue();
            } catch (error) {
                console.warn('Form initialization failed:', error);
                // Set safe defaults
                this.totalAmount = '0.00';
                this.paidAmount = '0.00';
                this.dueAmount = '0.00';
            }
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

            // Watch for quotation changes to update calculations
            this.$watch('quotation', (newQuotation, oldQuotation) => {
                if (newQuotation && newQuotation !== oldQuotation) {
                    this.handleQuotationChange();
                }
            });

            // Watch for advance percentage changes
            this.$watch('advancePercentage', (newVal, oldVal) => {
                if (newVal !== oldVal && this.billType === 'advance') {
                    this.debouncedAdvanceCalculation();
                }
            });

            this.$watch('paidAmount', (newVal, oldVal) => {
                if (newVal === oldVal) return;
                const total = this.toNumber(this.totalAmount);
                let paid = this.toNumber(newVal);
                if (paid < 0) paid = 0;
                if (paid > total) {
                    paid = total;
                    this.showWarning('Payment adjusted to not exceed total');
                }
                if (this.billType === 'advance') {
                    const quotationTotal = this.toNumber(this.quotation?.active_revision?.total || this.activeRevision?.total || 0);
                    const computedPaid = (quotationTotal * this.toNumber(this.advancePercentage) / 100).toFixed(2);
                    this.paidAmount = computedPaid;
                } else {
                    this.paidAmount = paid.toFixed(2);
                }
                this.safeCalculateDue();
                this.validatePaymentAmount();
            });
            this.$watch('totalAmount', (newVal, oldVal) => {
                if (newVal === oldVal) return;
                this.safeCalculateDue();
            });
            this.$watch('installmentPercentage', (newVal, oldVal) => {
                if (newVal !== oldVal && this.billType === 'running') {
                    this.calculateInstallmentAmount();
                }
            });
            this.$watch('installmentAmount', (newVal, oldVal) => {
                if (newVal === oldVal || this.billType !== 'running') return;
                this.syncInstallmentFromAmount();
            });
        },

        // Handle quotation changes
        handleQuotationChange() {
            if (this.billType === 'advance') {
                this.calculateAdvanceAmount();
            } else if (this.billType === 'regular') {
                this.calculateRegularBillTotal();
            }
        },

        // Debounced advance calculation to prevent excessive updates
        debouncedAdvanceCalculation() {
            clearTimeout(this.advanceCalculationTimeout);
            this.advanceCalculationTimeout = setTimeout(() => {
                this.calculateAdvanceAmount();
            }, 300);
        },

        // Validate initial state
        validateInitialState() {
            if (this.billType === 'running' && !this.parentBill) {
                this.showError('Parent bill is required for running bills');
            }

            if (this.billType === 'advance' && this.existingAdvanceBill) {
                this.showWarning('An advance bill already exists for this quotation');
            }
        },

        // Advance bill calculations
        calculateAdvanceAmount() {
            try {
                // Get quotation total from active revision or quotation data
                const quotationTotal = parseFloat(this.quotation?.active_revision?.total || this.activeRevision?.total || 0);

                // Validate advance percentage first
                if (!this.validateAdvancePercentage()) {
                    return;
                }

                // Calculate advance amount
                this.totalAmount = quotationTotal.toFixed(2);

                // For advance bills, paid amount equals total amount
                this.paidAmount = (this.totalAmount * this.advancePercentage / 100).toFixed(2);
                this.calculateDue();

                // Update payment validation
                this.validatePaymentAmount();

            } catch (error) {
                console.warn('Advance amount calculation failed:', error);
                this.showError('Failed to calculate advance amount');
                this.totalAmount = '0.00';
                this.paidAmount = '0.00';
                this.dueAmount = '0.00';
            }
        },

        // Safe calculation with error handling
        safeCalculateAdvanceAmount() {
            try {
                this.calculateAdvanceAmount();
            } catch (error) {
                console.warn('Advance amount calculation failed:', error);
                this.totalAmount = '0.00';
                this.paidAmount = '0.00';
                this.dueAmount = '0.00';
            }
        },

        // Safe regular bill calculation
        safeCalculateRegularBillTotal() {
            try {
                this.calculateRegularBillTotal();
            } catch (error) {
                console.warn('Regular bill calculation failed:', error);
                this.totalAmount = '0.00';
                this.selectedChallansCount = 0;
            }
        },

        // Safe running bill data update
        safeUpdateRunningBillData() {
            try {
                this.updateRunningBillData();
            } catch (error) {
                console.warn('Running bill data update failed:', error);
            }
        },

        // Safe due calculation
        safeCalculateDue() {
            try {
                this.calculateDue();
            } catch (error) {
                console.warn('Due calculation failed:', error);
                this.dueAmount = '0.00';
            }
        },

        // Regular bill calculations
        calculateRegularBillTotal() {
            try {
                // Validate challans data availability
                if (!this.challans || !Array.isArray(this.challans) || this.challans.length === 0) {
                    console.warn('No challans available for calculation');
                    this.totalAmount = '0.00';
                    this.selectedChallansCount = 0;
                    this.showWarning('No challans available for this quotation');
                    this.items = [];
                    return;
                }

                const selectedChallans = document.querySelectorAll('input[name="challan_ids[]"]:checked');
                this.selectedChallansCount = selectedChallans.length;

                if (this.selectedChallansCount === 0) {
                    this.totalAmount = '0.00';
                    this.showWarning('Please select at least one challan');
                    this.items = [];
                    return;
                }

                let total = 0;
                let processedChallans = 0;
                const builtItems = [];

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
                                const quantity = this.toNumber(product.remaining_quantity ?? product.quantity);
                                const unitPrice = this.toNumber(product.quotation_product.unit_price);
                                if (!isNaN(quantity) && !isNaN(unitPrice)) {
                                    total += quantity * unitPrice;
                                    builtItems.push({
                                        quotation_product_id: product.quotation_product.id,
                                        quantity: quantity,
                                        allocations: [{ challan_product_id: product.id, billed_quantity: quantity }],
                                    });
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
                    this.showWarning('Selected challans have no valid products');
                    this.items = [];
                    return;
                }

                const groups = {};
                builtItems.forEach(it => {
                    const key = it.quotation_product_id;
                    if (!groups[key]) {
                        groups[key] = { quotation_product_id: key, quantity: 0, allocations: [] };
                    }
                    groups[key].quantity += it.quantity;
                    groups[key].allocations.push({ challan_product_id: it.allocations[0].challan_product_id, billed_quantity: it.quantity });
                });
                this.totalAmount = total.toFixed(2);
                this.items = Object.values(groups);
                this.calculateDue();
                this.validateRegularBillSelection();

            } catch (error) {
                console.error('Regular bill calculation failed:', error);
                this.showError('Failed to calculate regular bill total');
                this.totalAmount = '0.00';
                this.selectedChallansCount = 0;
                this.items = [];
            }
        },

        // Build items for advance bill from active revision products
        buildAdvanceItems() {
            try {
                const products = this.activeRevision?.products || this.quotation?.active_revision?.products || [];
                if (!Array.isArray(products) || products.length === 0) {
                    this.items = [];
                    return;
                }
                this.items = products.map(p => ({
                    quotation_product_id: p.id || p.quotation_product_id || (p.quotation_product?.id ?? null),
                    quantity: this.toNumber(p.quantity) || 1,
                })).filter(i => i.quotation_product_id);
            } catch (error) {
                console.warn('Failed to build advance items:', error);
                this.items = [];
            }
        },

        // Running bill calculations
        calculateInstallmentAmount() {
            if (!this.parentBill) {
                this.showError('Parent bill is required for installment calculation');
                return;
            }

            const parentTotal = this.toNumber(this.parentBill.total_amount);
            this.installmentAmount = (parentTotal * this.toNumber(this.installmentPercentage) / 100).toFixed(2);
            this.totalAmount = this.installmentAmount;
            this.paidAmount = this.installmentAmount;
            this.dueAmount = '0.00';
            this.validateInstallmentPercentage();
        },

        // Calculate remaining amounts
        updateRunningBillData() {
            if (this.parentBill) {
                this.remainingAmount = this.parentBill.total_amount - this.getTotalBilledAmount();
                this.remainingPercentage = 100 - this.getTotalBilledPercentage();
            }
        },

        // Get total billed amount from installments
        getTotalBilledAmount() {
            // This would typically be fetched from server
            // For now, return 0 as placeholder
            return 0;
        },

        // Get total billed percentage from installments
        getTotalBilledPercentage() {
            // This would typically be fetched from server
            // For now, return 0 as placeholder
            return 0;
        },

        // Calculate due amount
        calculateDue() {
            const total = this.toNumber(this.totalAmount);
            const paid = this.toNumber(this.paidAmount);
            this.dueAmount = (total - paid).toFixed(2);
            this.validatePaymentAmount();
        },

        // Validation methods
        validateAdvancePercentage() {
            // Clear previous errors
            this.errors.advancePercentage = null;

            if (this.advancePercentage === null || this.advancePercentage === '' || isNaN(this.advancePercentage)) {
                this.errors.advancePercentage = 'Advance percentage is required';
                return false;
            }

            if (this.advancePercentage <= 0) {
                this.errors.advancePercentage = 'Advance percentage must be greater than 0';
                return false;
            }
            if (this.advancePercentage > 100) {
                this.errors.advancePercentage = 'Advance percentage cannot exceed 100%';
                return false;
            }

            // Check if quotation total is available
            const quotationTotal = parseFloat(this.quotation?.active_revision?.total || this.activeRevision?.total || 0);
            if (quotationTotal <= 0) {
                this.errors.advancePercentage = 'Quotation total is not available for calculation';
                return false;
            }

            return true;
        },

        validateRegularBillSelection() {
            if (this.selectedChallansCount === 0) {
                this.showError('Please select at least one challan');
                return false;
            }
            return true;
        },

        validateInstallmentPercentage() {
            if (!this.parentBill) {
                this.showError('Parent bill is required');
                return false;
            }

            if (this.installmentPercentage <= 0) {
                this.showError('Installment percentage must be greater than 0');
                return false;
            }

            if (this.installmentPercentage > this.remainingPercentage) {
                this.showError(`Installment percentage (${this.installmentPercentage}%) exceeds remaining balance (${this.remainingPercentage}%)`);
                return false;
            }

            return true;
        },

        validatePaymentAmount() {
            // Clear previous payment errors
            this.errors.paymentAmount = null;

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

            return true;
        },

        // Form submission validation
        validateForm(event) {
            let isValid = true;

            // Clear previous errors
            this.errors = {
                advancePercentage: null,
                paymentAmount: null,
                installmentPercentage: null,
                regularSelection: null
            };

            // Validate based on bill type
            switch (this.billType) {
                case 'advance':
                    if (!this.validateAdvancePercentage()) {
                        isValid = false;
                    }
                    break;
                case 'regular':
                    if (!this.validateRegularBillSelection()) {
                        isValid = false;
                    }
                    break;
                case 'running':
                    if (!this.validateInstallmentPercentage()) {
                        isValid = false;
                    }
                    break;
            }

            // Validate payment amount
            if (!this.validatePaymentAmount()) {
                isValid = false;
            }

            // Validate required fields
            if (!this.totalAmount || parseFloat(this.totalAmount) <= 0) {
                this.showError('Total amount must be greater than 0');
                isValid = false;
            }

            if (!this.paidAmount || parseFloat(this.paidAmount) < 0) {
                this.showError('Paid amount cannot be negative');
                isValid = false;
            }

            // Validate invoice number
            const invoiceField = document.getElementById('invoice_no');
            if (!invoiceField || !invoiceField.value || !invoiceField.value.trim()) {
                this.showError('Invoice number is required');
                isValid = false;
            }

            // Validate bill date
            const billDateField = document.getElementById('bill_date');
            if (!billDateField || !billDateField.value || !billDateField.value.trim()) {
                this.showError('Bill date is required');
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();
                return false;
            }

            // Update hidden fields before submission
            this.updateHiddenFields();

            return true;
        },

        // Update hidden form fields
        updateHiddenFields() {
            const form = this.$el.closest('form');
            if (form) {
                // Update total amount
                const totalAmountField = form.querySelector('input[name="total_amount"]');
                if (totalAmountField && totalAmountField.value !== undefined) {
                    totalAmountField.value = this.totalAmount;
                }

                // Update paid amount
                const paidAmountField = form.querySelector('input[name="paid"]');
                if (paidAmountField && paidAmountField.value !== undefined) {
                    paidAmountField.value = this.paidAmount;
                }

                // Update due amount
                const dueAmountField = form.querySelector('input[name="due"]');
                if (dueAmountField && dueAmountField.value !== undefined) {
                    dueAmountField.value = this.dueAmount;
                }

                // Update billed percentage
                const billedPercentageField = form.querySelector('input[name="bill_percentage"]');
                if (billedPercentageField && billedPercentageField.value !== undefined) {
                    const percentage = this.billType === 'running' ? this.installmentPercentage : this.advancePercentage;
                    billedPercentageField.value = percentage;
                }
            }
        },

        // Utility methods
        formatCurrency(amount) {
            const numberAmount = parseFloat(amount);
            if (isNaN(numberAmount)) return 'BDT 0.00';
            return `BDT ${numberAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        },

        toNumber(value) {
            const n = parseFloat(value);
            return isNaN(n) ? 0 : n;
        },
        syncInstallmentFromAmount() {
            if (!this.parentBill) return;
            let amount = this.toNumber(this.installmentAmount);
            const remainingAmount = this.remainingAmount;
            if (amount < 0) amount = 0;
            if (amount > remainingAmount) {
                amount = remainingAmount;
                this.showWarning('Installment amount adjusted to remaining balance');
            }
            const parentTotal = this.toNumber(this.parentBill.total_amount);
            const percentage = parentTotal > 0 ? (amount / parentTotal) * 100 : 0;
            this.installmentAmount = amount.toFixed(2);
            this.installmentPercentage = percentage.toFixed(2);
            this.totalAmount = this.installmentAmount;
            this.paidAmount = this.installmentAmount;
            this.safeCalculateDue();
            this.validateInstallmentPercentage();
        },

        // Update notes character counter
        updateNotesCounter() {
            const notesTextarea = document.getElementById('notes');
            if (notesTextarea) {
                const counter = notesTextarea.value ? notesTextarea.value.length : 0;
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

        showError(message) {
            // Use Laravel's notification system or custom toast
            if (typeof window.showToast === 'function') {
                window.showToast('error', message);
            } else {
                alert('Error: ' + message);
            }
        },

        showWarning(message) {
            if (typeof window.showToast === 'function') {
                window.showToast('warning', message);
            } else {
                console.warn(message);
            }
        },

        // Get bill type description
        getBillTypeDescription() {
            const descriptions = {
                'advance': 'Initial payment before delivery',
                'regular': 'Final bill against delivered challans',
                'running': 'Installment payment against existing bill'
            };
            return descriptions[this.billType] || '';
        },

        // Get remaining amounts for running bills
        get remainingAmount() {
            return this.parentBill ? (this.parentBill.total_amount - this.getTotalBilledAmount()) : 0;
        },

        get remainingPercentage() {
            return this.parentBill ? (100 - this.getTotalBilledPercentage()) : 0;
        }
    }));
});

// Global billing utilities
window.BillingUtils = {
    // Calculate total from challan products
    calculateChallanTotal: function (challans, selectedIds) {
        let total = 0;
        selectedIds.forEach(challanId => {
            const challan = challans.find(c => c.id === challanId);
            if (challan && challan.products) {
                challan.products.forEach(product => {
                    if (product.quotation_product) {
                        total += product.quantity * product.quotation_product.unit_price;
                    }
                });
            }
        });
        return total;
    },

    // Validate bill creation rules
    validateBillCreation: function (billType, data) {
        const errors = [];

        switch (billType) {
            case 'advance':
                if (data.hasChallans) {
                    errors.push('Cannot create advance bill when challans exist');
                }
                if (data.existingAdvanceBill) {
                    errors.push('Advance bill already exists for this quotation');
                }
                break;

            case 'regular':
                if (!data.selectedChallans || data.selectedChallans.length === 0) {
                    errors.push('At least one challan must be selected');
                }
                if (data.existingAdvanceBill && data.existingAdvanceBill.bill_percentage < 100) {
                    errors.push('Cannot create regular bill - advance bill has remaining balance');
                }
                break;

            case 'running':
                if (!data.parentBill) {
                    errors.push('Parent bill is required for running bills');
                }
                if (data.installmentPercentage > data.remainingPercentage) {
                    errors.push('Installment percentage exceeds remaining balance');
                }
                break;
        }

        return errors;
    },

    // Format currency for display
    formatCurrency: function (amount, currency = 'BDT') {
        const numberAmount = parseFloat(amount);
        if (isNaN(numberAmount)) return `${currency} 0.00`;
        return `${currency} ${numberAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }
};
