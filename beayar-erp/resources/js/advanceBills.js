document.addEventListener('alpine:init', () => {
    Alpine.data('advanceBillsForm', ({
        quotation,
        activeRevision
    }) => ({
        quotation: quotation,
        activeRevision: activeRevision,
        calculatedAdvanceAmount: 0,
        advancePercentage: 0,
        installments: [],
        totalInstallmentAmount: 0,
        minInstallmentDate: new Date().toISOString().split('T')[0],

        init() {
            this.calculatedAdvanceAmount = 0;
            this.advancePercentage = 0;
            // Initialize with safe defaults if activeRevision is missing
            if (!this.activeRevision) {
                console.warn('activeRevision is missing in advanceBillsForm');
            }
        },

        getRevisionTotal() {
            return parseFloat(this.activeRevision?.total) || 0;
        },

        calculatePercentageFromAmount() {
            let total = this.getRevisionTotal();
            let amount = parseFloat(this.calculatedAdvanceAmount) || 0;
            if (total > 0) {
                this.advancePercentage = ((amount / total) * 100).toFixed(2);
            } else {
                this.advancePercentage = 0;
            }
        },

        calculateAdvanceAmount() {
            let total = this.getRevisionTotal();
            let percentage = parseFloat(this.advancePercentage) || 0;
            this.calculatedAdvanceAmount = ((percentage / 100) * total).toFixed(2);
        },

        normalizeAdvanceAmount() {
            let total = this.getRevisionTotal();
            let amount = parseFloat(this.calculatedAdvanceAmount) || 0;
            if (amount > total) {
                this.calculatedAdvanceAmount = total;
                this.calculatePercentageFromAmount();
            } else if (amount < 0) {
                this.calculatedAdvanceAmount = 0;
                this.calculatePercentageFromAmount();
            }
            // Format to 2 decimal places
            this.calculatedAdvanceAmount = parseFloat(this.calculatedAdvanceAmount).toFixed(2);
        },

        formatCurrency(value) {
            let currency = this.activeRevision?.type == 'via' ? this.activeRevision.currency : 'BDT';
            return currency + ' ' + (parseFloat(value) || 0).toFixed(2);
        },

        addInstallment() {
            this.installments.push({
                amount: 0,
                due_date: '',
                description: ''
            });
        },

        removeInstallment(index) {
            this.installments.splice(index, 1);
            this.updateTotalInstallmentAmount();
        },

        updateTotalInstallmentAmount() {
            this.totalInstallmentAmount = this.installments.reduce((sum, inst) => sum + (
                parseFloat(inst.amount) || 0), 0).toFixed(2);
        }
    }));
});
