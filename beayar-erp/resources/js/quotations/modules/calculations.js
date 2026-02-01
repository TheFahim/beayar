import QuotationHelpers from "../helpers/quotationHelpers";
import CalculationEngine from "../helpers/calculationEngine";

export default {
  // Line total
  calculateLineTotal(row) {
    return CalculationEngine.calculateLineTotal(
      row,
      this.quotation_revision.type,
      this.quotation_revision.currency,
      this.quotation_revision.exchange_rate
    );
  },

  // Totals
  calculateTotals() {
    const isViaForeign = CalculationEngine.isViaForeignCurrency(
      this.quotation_revision.type,
      this.quotation_revision.currency
    );
    const exchangeRate = QuotationHelpers.parseFloat(this.quotation_revision.exchange_rate);

    let bdtSubtotal = this.quotation_products.reduce((sum, row) => {
      if (row.unit_price) {
        const unitPrice = QuotationHelpers.parseFloat(row.unit_price);
        const quantity = QuotationHelpers.parseFloat(row.quantity);
        if (isViaForeign && exchangeRate) {
          return sum + (unitPrice * quantity * exchangeRate);
        }
        return sum + (unitPrice * quantity);
      }
      return sum + this.calculateLineTotal(row);
    }, 0);

    this.quotation_revision.bdt_subtotal = parseFloat(bdtSubtotal.toFixed(2));

    if (isViaForeign && exchangeRate) {
      this.quotation_revision.subtotal = parseFloat((bdtSubtotal / exchangeRate).toFixed(2));
    } else {
      this.quotation_revision.subtotal = this.quotation_revision.bdt_subtotal;
    }

    const discountInput = QuotationHelpers.parseFloat(this.quotation_revision.discount);
    const discountBdt = isViaForeign && exchangeRate ? discountInput * exchangeRate : discountInput;
    const bdtDiscountedPrice = Math.max(0, this.quotation_revision.bdt_subtotal - discountBdt);

    if (isViaForeign && exchangeRate) {
      this.quotation_revision.discounted_price = parseFloat((bdtDiscountedPrice / exchangeRate).toFixed(2));
    } else {
      this.quotation_revision.discounted_price = parseFloat(bdtDiscountedPrice.toFixed(2));
    }

    const shippingInput = QuotationHelpers.parseFloat(this.quotation_revision.shipping);
    const shippingBdt = isViaForeign && exchangeRate ? shippingInput * exchangeRate : shippingInput;
    const bdtAfterShipping = bdtDiscountedPrice + shippingBdt;

    let bdtFinalTotal = bdtAfterShipping;
    if (this.quotation_revision.type === 'normal') {
      const vatPercentage = QuotationHelpers.parseFloat(this.quotation_revision.vat_percentage);
      this.quotation_revision.vat_amount = (bdtAfterShipping * vatPercentage) / 100;
      bdtFinalTotal = bdtAfterShipping + this.quotation_revision.vat_amount;
    } else {
      this.quotation_revision.vat_amount = 0;
    }

    this.quotation_revision.bdt_total = parseFloat(bdtFinalTotal.toFixed(2));

    if (isViaForeign && exchangeRate) {
      this.quotation_revision.total = parseFloat((bdtFinalTotal / exchangeRate).toFixed(2));
    } else {
      this.quotation_revision.total = this.quotation_revision.bdt_total;
    }

    this.quotation_revision.vat_amount = parseFloat(this.quotation_revision.vat_amount.toFixed(2));
  },

  // Currency conversions for row buying
  calculateForeignCurrencyEquivalent(index) {
    const row = this.quotation_products[index];
    const foreignAmount = QuotationHelpers.parseFloat(row.foreign_currency_buying);
    const exchangeRate = QuotationHelpers.parseFloat(this.quotation_revision.exchange_rate);
    const currency = this.quotation_revision.currency;

    if (currency && currency !== 'BDT') {
      row.bdt_buying = foreignAmount && exchangeRate
        ? parseFloat((foreignAmount * exchangeRate).toFixed(2))
        : 0;
    } else {
      row.foreign_currency_buying = 0;
    }

    this.calculateUnitPrice(index);
    this.calculateTotals();
  },

  calculateBdtToForeignEquivalent(index) {
    const row = this.quotation_products[index];
    const bdtAmount = QuotationHelpers.parseFloat(row.bdt_buying);
    const exchangeRate = QuotationHelpers.parseFloat(this.quotation_revision.exchange_rate);
    const currency = this.quotation_revision.currency;

    if (currency && currency !== 'BDT') {
      if (bdtAmount && exchangeRate) {
        row.foreign_currency_buying = parseFloat((bdtAmount / exchangeRate).toFixed(2));
        this.addFieldFeedback('foreign_currency_buying', index);
      } else {
        row.foreign_currency_buying = 0;
      }
    } else {
      row.foreign_currency_buying = 0;
    }

    this.calculateUnitPrice(index);
    this.calculateTotals();
  },

  // Unit price calculations
  calculateUnitPrice(index) {
    const row = this.quotation_products[index];
    const quantity = QuotationHelpers.parseNumber(row.quantity, 0);

    if (quantity <= 0) {
      row.unit_price = 0;
      return;
    }

    const isViaForeign = CalculationEngine.isViaForeignCurrency(
      this.quotation_revision.type,
      this.quotation_revision.currency
    );
    const exchangeRate = QuotationHelpers.parseFloat(this.quotation_revision.exchange_rate);

    if (isViaForeign) {
      this.calculateUnitPriceViaForeign(row, exchangeRate);
    } else {
      this.calculateUnitPriceNormal(row, exchangeRate);
    }
  },

  calculateUnitPriceViaForeign(row, exchangeRate) {
    let unitCostForeign = 0;
    const quantity = QuotationHelpers.parseNumber(row.quantity, 0);

    if (row.foreign_currency_buying) {
      unitCostForeign = QuotationHelpers.parseFloat(row.foreign_currency_buying);
    } else if (row.bdt_buying && exchangeRate) {
      unitCostForeign = QuotationHelpers.parseFloat(row.bdt_buying) / exchangeRate;
    } else {
      row.unit_price = 0;
      return;
    }

    const taxPerUnit = quantity > 0 ? QuotationHelpers.parseFloat(row.tax) / quantity : 0;
    const attPerUnit = quantity > 0 ? QuotationHelpers.parseFloat(row.att) / quantity : 0;
    const airSeaFreightPerUnit = quantity > 0 ? QuotationHelpers.parseFloat(row.air_sea_freight) / quantity : 0;

    const costBeforeMarginForeign = unitCostForeign + airSeaFreightPerUnit + taxPerUnit + attPerUnit;
    const margin = QuotationHelpers.parseFloat(row.margin);
    const unitPriceForeignWithMargin = costBeforeMarginForeign * (1 + (margin / 100));

    row.unit_price = parseFloat(unitPriceForeignWithMargin.toFixed(2));
  },

  calculateUnitPriceNormal(row, exchangeRate) {
    const unitCostBdt = CalculationEngine.getUnitCostBdt(row, exchangeRate);

    if (!unitCostBdt) {
      row.unit_price = 0;
      return;
    }

    const taxPerUnit = QuotationHelpers.parseFloat(row.tax);
    const attPerUnit = QuotationHelpers.parseFloat(row.att);
    const airSeaFreightPerUnit = QuotationHelpers.parseFloat(row.air_sea_freight);

    const costBeforeMargin = unitCostBdt + airSeaFreightPerUnit + taxPerUnit + attPerUnit;

    const margin = QuotationHelpers.parseFloat(row.margin);
    const marginValue = QuotationHelpers.parseFloat(row.margin_value);
    const unitPriceWithMargin = costBeforeMargin + marginValue;

    row.unit_price = parseFloat(unitPriceWithMargin.toFixed(2));
  },

  // Tax/ATT helpers
  calculateTaxAmount(index) {
    this.calculateAmountFromPercentage(index, 'tax', 'tax_percentage');
  },

  calculateTaxPercentage(index) {
    this.calculatePercentageFromAmount(index, 'tax', 'tax_percentage');
  },

  calculateAttAmount(index) {
    this.calculateAmountFromPercentage(index, 'att', 'att_percentage');
  },

  calculateAttPercentage(index) {
    this.calculatePercentageFromAmount(index, 'att', 'att_percentage');
  },

  calculateAmountFromPercentage(index, amountField, percentageField) {
    const row = this.quotation_products[index];
    const percentage = QuotationHelpers.parseFloat(row[percentageField]);

    const isViaForeign = CalculationEngine.isViaForeignCurrency(
      this.quotation_revision.type,
      this.quotation_revision.currency
    );

    if (amountField === 'tax') {
      row[amountField] = CalculationEngine.calculateTaxAmount(row, percentage, isViaForeign);
    } else if (amountField === 'att') {
      row[amountField] = CalculationEngine.calculateAttAmount(row, percentage, isViaForeign);
    } else {
      const baseAmount = CalculationEngine.getBaseAmountForTax(
        row,
        this.quotation_revision.exchange_rate
      );
      row[amountField] = CalculationEngine.calculateAmount(percentage, baseAmount);
    }

    this.calculateUnitPrice(index);
    this.calculateTotals();
  },

  calculatePercentageFromAmount(index, amountField, percentageField) {
    const row = this.quotation_products[index];
    const amount = QuotationHelpers.parseFloat(row[amountField]);

    const isViaForeign = CalculationEngine.isViaForeignCurrency(
      this.quotation_revision.type,
      this.quotation_revision.currency
    );

    if (amountField === 'tax') {
      row[percentageField] = CalculationEngine.calculateTaxPercentage(row, amount, isViaForeign);
    } else if (amountField === 'att') {
      row[percentageField] = CalculationEngine.calculateAttPercentage(row, amount, isViaForeign);
    } else {
      const baseAmount = CalculationEngine.getBaseAmountForTax(
        row,
        this.quotation_revision.exchange_rate
      );
      row[percentageField] = CalculationEngine.calculatePercentage(amount, baseAmount);
    }

    this.calculateUnitPrice(index);
    this.calculateTotals();
  },

  // Air/sea freight
  calculateAirSeaFreight(index) {
    const row = this.quotation_products[index];
    const rate = QuotationHelpers.parseFloat(row.air_sea_freight_rate);
    const weight = QuotationHelpers.parseFloat(row.weight);

    row.air_sea_freight = parseFloat((rate * weight).toFixed(2));
    this.calculateUnitPrice(index);
    this.calculateTotals();
  },

  // Margin
  calculateMarginValue(index) {
    const row = this.quotation_products[index];
    const marginPercentage = QuotationHelpers.parseFloat(row.margin);

    const isViaForeign = CalculationEngine.isViaForeignCurrency(
      this.quotation_revision.type,
      this.quotation_revision.currency
    );

    row.margin_value = CalculationEngine.calculateMarginAmount(row, marginPercentage, isViaForeign);

    this.calculateUnitPrice(index);
    this.calculateTotals();
  },

  calculateMarginPercentage(index) {
    const row = this.quotation_products[index];
    const marginValue = QuotationHelpers.parseFloat(row.margin_value);

    const isViaForeign = CalculationEngine.isViaForeignCurrency(
      this.quotation_revision.type,
      this.quotation_revision.currency
    );

    row.margin = CalculationEngine.calculateMarginPercentage(row, marginValue, isViaForeign);

    this.calculateUnitPrice(index);
    this.calculateTotals();
  },

  // Discount
  calculateDiscountAmount() {
    const discountPercentage = QuotationHelpers.parseFloat(this.quotation_revision.discount_percentage);
    const subtotal = QuotationHelpers.parseFloat(this.quotation_revision.subtotal);

    this.quotation_revision.discount = CalculationEngine.calculateAmount(discountPercentage, subtotal);
    this.calculateTotals();
  },

  calculateDiscountPercentage() {
    const discountAmount = QuotationHelpers.parseFloat(this.quotation_revision.discount);
    const subtotal = QuotationHelpers.parseFloat(this.quotation_revision.subtotal);

    this.quotation_revision.discount_percentage = CalculationEngine.calculatePercentage(discountAmount, subtotal);
    this.calculateTotals();
  },

  // Labels/display
  getForeignCurrencyLabel() {
    return this.quotation_revision.currency && this.quotation_revision.currency !== 'BDT'
      ? this.quotation_revision.currency
      : 'Foreign Currency';
  },

  getFinalUnitPriceLabel() {
    const isViaForeign = CalculationEngine.isViaForeignCurrency(
      this.quotation_revision.type,
      this.quotation_revision.currency
    );
    return isViaForeign
      ? `Final Unit Price (${this.quotation_revision.currency})`
      : 'Final Unit Price (BDT)';
  },

  getBdtEquivalentUnitPrice(row) {
    const isViaForeign = CalculationEngine.isViaForeignCurrency(
      this.quotation_revision.type,
      this.quotation_revision.currency
    );
    const exchangeRate = QuotationHelpers.parseFloat(this.quotation_revision.exchange_rate);

    if (!isViaForeign || !exchangeRate || !row.unit_price) {
      return 0;
    }
    return parseFloat((row.unit_price * exchangeRate).toFixed(2));
  },

  getForeignCurrencyLineTotal(row) {
    if (!CalculationEngine.isViaForeignCurrency(this.quotation_revision.type, this.quotation_revision.currency)) {
      return 0;
    }

    const exchangeRate = QuotationHelpers.parseFloat(this.quotation_revision.exchange_rate);
    const lineTotal = this.calculateLineTotal(row);

    if (!exchangeRate || !lineTotal) {
      return 0;
    }

    return parseFloat((lineTotal / exchangeRate).toFixed(2));
  }
};