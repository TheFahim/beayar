import QuotationHelpers from "../helpers/quotationHelpers";

export default {
  validateViaQuotationCurrency() {
    if (this.quotation_revision.type === 'via') {
      this.showCurrencyWarning = !this.quotation_revision.currency || this.quotation_revision.currency === 'BDT';
      this.showProductPricingWarning = this.quotation_products.some(row =>
        !row.foreign_currency_buying && !row.bdt_buying
      );
    } else {
      this.showCurrencyWarning = false;
      this.showProductPricingWarning = false;
    }
  },

  validateForm() {
    const errors = [];

    if (!this.selectedCustomerId) {
      errors.push('Please select a customer');
    }

    this.validateQuotationFields(errors);
    this.validateCurrency(errors);
    this.validateProducts(errors);

    return errors;
  },

  validateQuotationFields(errors) {
    const fields = [
      { value: this.quotation.quotation_no, message: 'Quotation number is required' },
      { value: this.quotation_revision.date, message: 'Quotation date is required' },
      { value: this.quotation_revision.validity, message: 'Validity date is required' }
    ];

    fields.forEach(field => {
      if (!field.value.trim()) {
        errors.push(field.message);
      }
    });
  },

  validateCurrency(errors) {
    if (this.quotation_revision.type === 'via') {
      if (!this.quotation_revision.currency || this.quotation_revision.currency === 'BDT') {
        errors.push('Please select a foreign currency for Via quotations');
      }

      if (!this.quotation_revision.exchange_rate) {
        errors.push('Exchange rate is required for foreign currency quotations');
      }
    }
  },

  validateProducts(errors) {
    if (this.quotation_products.length === 0) {
      errors.push('At least one product is required');
    }

    this.quotation_products.forEach((row, index) => {
      if (!row.product_id) {
        errors.push(`Product selection is required for row ${index + 1}`);
      }

      const qty = QuotationHelpers.parseNumber(row.quantity, 0);
      if (qty <= 0) {
        errors.push(`Valid quantity is required for row ${index + 1}`);
      }

      if (this.quotation_revision.type === 'via') {
        if (!row.foreign_currency_buying && !row.bdt_buying) {
          errors.push(`Buying price is required for row ${index + 1}`);
        }
      }
    });
  }
};