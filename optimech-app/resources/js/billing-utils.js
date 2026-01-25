/**
 * Enhanced Billing Utilities - Comprehensive Billing System
 * Supports three bill types: advance, regular, running
 * Business rule validation and dynamic calculations
 */

class BillingUtils {
    constructor() {
        this.currency = 'BDT';
        this.decimalPlaces = 2;
    }

    /**
     * Format currency for display
     */
    formatCurrency(amount, currency = null) {
        const numberAmount = parseFloat(amount);
        const currencySymbol = currency || this.currency;

        if (isNaN(numberAmount)) return `${currencySymbol} 0.00`;

        return `${currencySymbol} ${numberAmount.toLocaleString('en-US', {
            minimumFractionDigits: this.decimalPlaces,
            maximumFractionDigits: this.decimalPlaces
        })}`;
    }

    /**
     * Convert string to number safely
     */
    toNumber(value) {
        const n = parseFloat(value);
        return isNaN(n) ? 0 : n;
    }

    /**
     * Calculate advance bill amount
     */
    calculateAdvanceAmount(quotationTotal, percentage) {
        const total = this.toNumber(quotationTotal);
        const percent = this.toNumber(percentage);

        if (total <= 0 || percent <= 0) return 0;
        if (percent > 100) return total;

        return (total * percent) / 100;
    }

    /**
     * Calculate regular bill total from selected challans
     */
    calculateRegularBillTotal(challans, selectedIds) {
        let total = 0;

        if (!selectedIds || selectedIds.length === 0) return 0;

        selectedIds.forEach(challanId => {
            const challan = challans.find(c => c.id === challanId);
            if (challan && challan.products) {
                challan.products.forEach(product => {
                    if (product.quotation_product && product.quotation_product.unit_price) {
                        total += this.toNumber(product.quantity) * this.toNumber(product.quotation_product.unit_price);
                    }
                });
            }
        });

        return total;
    }

    /**
     * Calculate installment amount
     */
    calculateInstallmentAmount(parentTotal, percentage) {
        const total = this.toNumber(parentTotal);
        const percent = this.toNumber(percentage);

        if (total <= 0 || percent <= 0) return 0;
        if (percent > 100) return total;

        return (total * percent) / 100;
    }

    /**
     * Calculate remaining amount and percentage
     */
    calculateRemaining(parentBill, existingInstallments = []) {
        const parentTotal = this.toNumber(parentBill.total_amount);
        const billedAmount = existingInstallments.reduce((sum, installment) => {
            return sum + this.toNumber(installment.amount);
        }, 0);

        const billedPercentage = existingInstallments.reduce((sum, installment) => {
            return sum + this.toNumber(installment.percentage);
        }, 0);

        return {
            remainingAmount: Math.max(0, parentTotal - billedAmount),
            remainingPercentage: Math.max(0, 100 - billedPercentage)
        };
    }

    /**
     * Validate advance bill percentage
     */
    validateAdvancePercentage(percentage) {
        const percent = this.toNumber(percentage);

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
    }

    /**
     * Validate installment percentage
     */
    validateInstallmentPercentage(percentage, remainingPercentage) {
        const percent = this.toNumber(percentage);
        const remaining = this.toNumber(remainingPercentage);

        if (isNaN(percent) || percent === null || percent === '') {
            return { valid: false, message: 'Installment percentage is required' };
        }

        if (percent <= 0) {
            return { valid: false, message: 'Installment percentage must be greater than 0' };
        }

        if (percent > remaining) {
            return {
                valid: false,
                message: `Installment percentage (${percent}%) exceeds remaining balance (${remaining}%)`
            };
        }

        return { valid: true, message: 'Valid installment percentage' };
    }

    /**
     * Validate payment amount
     */
    validatePaymentAmount(paid, total) {
        const paidAmount = this.toNumber(paid);
        const totalAmount = this.toNumber(total);

        if (paidAmount < 0) {
            return { valid: false, message: 'Payment amount cannot be negative' };
        }

        if (paidAmount > totalAmount) {
            return { valid: false, message: 'Payment amount cannot exceed total amount' };
        }

        return { valid: true, message: 'Valid payment amount' };
    }

    /**
     * Calculate due amount
     */
    calculateDue(total, paid) {
        const totalAmount = this.toNumber(total);
        const paidAmount = this.toNumber(paid);

        return Math.max(0, totalAmount - paidAmount);
    }

    /**
     * Validate bill creation rules
     */
    validateBillCreation(billType, data) {
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
    }

    /**
     * Get bill type description
     */
    getBillTypeDescription(billType) {
        const descriptions = {
            'advance': 'Initial payment before delivery',
            'regular': 'Final bill against delivered challans',
            'running': 'Installment payment against existing bill'
        };
        return descriptions[billType] || '';
    }

    /**
     * Get bill type color classes
     */
    getBillTypeColorClasses(billType) {
        const colors = {
            'advance': {
                bg: 'bg-emerald-50 dark:bg-emerald-900/20',
                text: 'text-emerald-800 dark:text-emerald-200',
                border: 'border-emerald-200 dark:border-emerald-700',
                ring: 'ring-emerald-200 dark:ring-emerald-700'
            },
            'regular': {
                bg: 'bg-slate-100 dark:bg-slate-900/20',
                text: 'text-slate-800 dark:text-slate-200',
                border: 'border-slate-200 dark:border-slate-700',
                ring: 'ring-slate-200 dark:ring-slate-700'
            },
            'running': {
                bg: 'bg-purple-50 dark:bg-purple-900/20',
                text: 'text-purple-800 dark:text-purple-200',
                border: 'border-purple-200 dark:border-purple-700',
                ring: 'ring-purple-200 dark:ring-purple-700'
            }
        };
        return colors[billType] || colors['regular'];
    }

    /**
     * Get status color classes
     */
    getStatusColorClasses(status) {
        const colors = {
            'draft': {
                bg: 'bg-yellow-100 dark:bg-yellow-900/20',
                text: 'text-yellow-800 dark:text-yellow-200',
                border: 'border-yellow-200 dark:border-yellow-700'
            },
            'issued': {
                bg: 'bg-blue-100 dark:bg-blue-900/20',
                text: 'text-blue-800 dark:text-blue-200',
                border: 'border-blue-200 dark:border-blue-700'
            },
            'paid': {
                bg: 'bg-green-100 dark:bg-green-900/20',
                text: 'text-green-800 dark:text-green-200',
                border: 'border-green-200 dark:border-green-700'
            },
            'cancelled': {
                bg: 'bg-red-100 dark:bg-red-900/20',
                text: 'text-red-800 dark:text-red-200',
                border: 'border-red-200 dark:border-red-700'
            }
        };
        return colors[status] || colors['draft'];
    }

    /**
     * Debounce function for performance optimization
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Show notification toast
     */
    showToast(type, message, duration = 3000) {
        // This would integrate with your toast system
        // For now, we'll use console and fallback to alert
        if (typeof window.showToast === 'function') {
            window.showToast(type, message, duration);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
            if (type === 'error') {
                alert(`Error: ${message}`);
            }
        }
    }

    /**
     * Generate unique invoice number
     */
    generateInvoiceNumber(prefix = 'INV', length = 6) {
        const timestamp = Date.now().toString().slice(-4);
        const random = Math.random().toString(36).substr(2, length - 4);
        return `${prefix}-${timestamp}${random}`.toUpperCase();
    }

    /**
     * Calculate billing statistics
     */
    calculateStatistics(bills) {
        const stats = {
            totalBills: bills.length,
            totalAmount: 0,
            totalPaid: 0,
            totalDue: 0,
            advanceBills: 0,
            regularBills: 0,
            runningBills: 0,
            paidBills: 0,
            pendingBills: 0
        };

        bills.forEach(bill => {
            stats.totalAmount += this.toNumber(bill.total_amount);
            stats.totalPaid += this.toNumber(bill.paid);
            stats.totalDue += this.toNumber(bill.due);

            switch (bill.bill_type) {
                case 'advance':
                    stats.advanceBills++;
                    break;
                case 'regular':
                    stats.regularBills++;
                    break;
                case 'running':
                    stats.runningBills++;
                    break;
            }

            if (bill.status === 'paid') {
                stats.paidBills++;
            } else if (bill.status !== 'cancelled') {
                stats.pendingBills++;
            }
        });

        return stats;
    }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BillingUtils;
} else {
    window.BillingUtils = new BillingUtils();
}
