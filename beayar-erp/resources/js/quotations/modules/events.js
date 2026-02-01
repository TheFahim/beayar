import QuotationHelpers from "../helpers/quotationHelpers";
import CalculationEngine from "../helpers/calculationEngine";

export default {
  async handleCurrencyChange() {
    // Fetch latest rates on explicit user currency change
    if (!this.allExchangeRates || Object.keys(this.allExchangeRates).length === 0 || this.mode === 'edit') {
      await this.loadAllExchangeRates();
    } else {
      this.updateExchangeRate();
    }
    this.validateViaQuotationCurrency();
    this.calculateTotals();
  },

  onCurrencyChange() {
    this.handleCurrencyChange();
  },

  onQuantityChange(index) {
    this.calculateUnitPrice(index);
    this.calculateTotals();
  },

  handleQuotationTypeChange() {
    if (this.quotation_revision.type === 'normal') {
      this.clearCurrencyForNormalQuotation();
      this.quotation_revision.terms_conditions = QuotationHelpers.getDefaultTerms();
    } else if (this.quotation_revision.type === 'via') {
      this.setDefaultCurrencyForViaQuotation();
      this.quotation_revision.terms_conditions = QuotationHelpers.getViaTerms();

      // Trigger immediate per-row recalculations based on foreign currency
      this.quotation_products.forEach((row, index) => {
        this.calculateTaxAmount(index);
        this.calculateAttAmount(index);
        this.calculateMarginValue(index);
        this.calculateUnitPrice(index);
      });
    }

    this.validateViaQuotationCurrency();
    this.calculateTotals();

    this.$nextTick(() => {
      if (window.sunEditorUtils) {
        const updated = window.sunEditorUtils.setEditorContent('text-area', this.quotation_revision.terms_conditions);
        if (!updated) {
          window.sunEditorUtils.initializeEditors();
        }
      }
    });
  },

  onQuotationTypeChange() {
    this.handleQuotationTypeChange();
  },

  clearCurrencyForNormalQuotation() {
    this.quotation_revision.exchange_rate = '';
    this.exchangeRateMessage = '';

    this.quotation_products.forEach((row, index) => {
      if (row.foreign_currency_buying) {
        row.foreign_currency_buying = 0;
        this.addVisualFeedback(`product-${index}`, 'bg-red-100 dark:bg-red-900/20 border border-red-400', 1500);
      }
      this.calculateUnitPrice(index);
    });
  },

  setDefaultCurrencyForViaQuotation() {
    if (!this.quotation_revision.currency || this.quotation_revision.currency === 'BDT') {
      this.quotation_revision.currency = 'USD';
      // Only auto-update rate when not in edit mode (prevent overwriting saved rate)
      if (this.mode !== 'edit') {
        this.updateExchangeRate();
      }
    }
  }
};