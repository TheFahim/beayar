document.addEventListener('alpine:init', () => {
    Alpine.data('advanceBillsForm', (data = {}) => ({
        quotation: data.quotation,
        activeRevision: data.activeRevision,

        advancePercentage: data.activeRevision?.type === 'via' ? 100 : 50,
        calculatedAdvanceAmount: '0.00',
        loading: false,
        errors: {
            po_no: null,
            advancePercentage: null,
            invoice_no: null,
            bill_date: null,
            payment_received_date: null,
            due: null,
        },

        init() {
            this.$nextTick(() => {
                this.calculateAdvanceAmount();
            });
        },

        getRevisionTotal() {
            const total = parseFloat(this.activeRevision?.total ?? this.activeRevision?.total_amount ?? 0);
            return isNaN(total) ? 0 : total;
        },

        calculateAdvanceAmount() {
            this.errors.advancePercentage = null;
            const pct = parseFloat(this.advancePercentage);
            const total = this.getRevisionTotal();
            if (!Number.isFinite(pct) || pct < 1 || pct > 100) {
                this.errors.advancePercentage = 'Advance percentage must be between 1 and 100';
            }
            const amount = (Number.isFinite(pct) ? (total * pct) / 100 : 0);
            this.calculatedAdvanceAmount = (amount || 0).toFixed(2);
            this.computeDue();
        },

        calculatePercentageFromAmount() {
            const total = this.getRevisionTotal();
            let amount = parseFloat(this.calculatedAdvanceAmount);
            if (!Number.isFinite(amount) || amount < 0) amount = 0;
            if (amount > total) {
                amount = total;
                this.calculatedAdvanceAmount = String(amount);
            }
            const pct = total > 0 ? (amount / total) * 100 : 0;
            this.advancePercentage = (pct || 0).toFixed(2);
            // Percentage validity notice (optional UI message)
            this.errors.advancePercentage = null;
            if (pct < 1 || pct > 100) {
                this.errors.advancePercentage = 'Advance percentage must be between 1 and 100';
            }

            this.computeDue();
        },

        normalizeAdvanceAmount() {
            const total = this.getRevisionTotal();
            let amount = parseFloat(this.calculatedAdvanceAmount);
            if (!Number.isFinite(amount) || amount < 0) amount = 0;
            if (amount > total) amount = total;
            this.calculatedAdvanceAmount = (amount || 0).toFixed(2);

            const pct = total > 0 ? (amount / total) * 100 : 0;
            this.advancePercentage = (pct || 0).toFixed(2);
            this.errors.advancePercentage = null;
            if (pct < 1 || pct > 100) {
                this.errors.advancePercentage = 'Advance percentage must be between 1 and 100';
            }

            this.computeDue();
        },

        computeDue() {
            const total = this.getRevisionTotal();
            const adv = parseFloat(this.calculatedAdvanceAmount) || 0;
            const due = (total - adv);
            const dueField = document.getElementById('due_hidden');
            if (dueField) dueField.value = due.toFixed(2);
        },



        validateClient() {
            this.errors.po_no = null;
            this.errors.invoice_no = null;
            this.errors.bill_date = null;
            this.errors.payment_received_date = null;
            this.errors.due = null;

            const poNo = document.getElementById('po_no')?.value || '';
            const invoice = document.getElementById('invoice_no')?.value || '';
            const billDate = document.getElementById('bill_date')?.value || '';
            const paymentDate = document.getElementById('payment_received_date')?.value || '';
            const dueField = document.getElementById('due_amount')?.value || '';

            let ok = true;
            if (!poNo.trim()) {
                this.errors.po_no = 'PO number is required';
                ok = false;
            }

            if (!billDate) {
                this.errors.bill_date = 'Bill date is required';
                ok = false;
            }
            // payment_received_date is optional
            if (isNaN(parseFloat(dueField))) {
                this.errors.due = 'Due must be numeric';
                ok = false;
            }
            return ok;
        },





        validateForm(event) {
            const pct = parseFloat(this.advancePercentage) || 0;
            const billDate = document.getElementById('bill_date')?.value;
            const calc = parseFloat(this.calculatedAdvanceAmount) || 0;

            let ok = true;
            if (pct < 1 || pct > 100) ok = false;

            if (!ok) {
                event.preventDefault();
                this.showWarning('Please fix validation errors before submitting');
                return false;
            }
            return true;
        },

        toNumber(v) {
            const n = parseFloat(v);
            return isNaN(n) ? 0 : n;
        },
        formatCurrency(amount) {
            const n = parseFloat(amount);
            const cur = (this.activeRevision?.type === 'via') ? (this.activeRevision?.currency || 'BDT') : 'BDT';
            if (isNaN(n)) return `${cur} 0.00`;
            return `${cur} ${n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        },
        showError(message) {
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
    }));
});
