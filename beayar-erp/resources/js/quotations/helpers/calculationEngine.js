const CalculationEngine = {
    // Currency mode detection
    isViaForeignCurrency(type, currency, baseCurrency = 'BDT') {
        return type === 'via' && currency && currency !== baseCurrency;
    },

    // Get unit cost in BDT for a product row
    getUnitCostBdt(row, exchangeRate) {
        if (row.bdt_buying) {
            return QuotationHelpers.parseFloat(row.bdt_buying);
        }
        if (row.foreign_currency_buying && exchangeRate) {
            return QuotationHelpers.parseFloat(row.foreign_currency_buying) * QuotationHelpers.parseFloat(exchangeRate);
        }
        return 0;
    },

    getTaxBaseAmountViaForeign(row) {
        const foreignBuying = QuotationHelpers.parseFloat(row.foreign_currency_buying);
        const airSeaFreight = QuotationHelpers.parseFloat(row.air_sea_freight);
        return foreignBuying + airSeaFreight;
    },

    // Get base amount for tax calculation (Normal transactions - BDT)
    // Tax base = bdt_buying + air_sea_freight_amount
    getTaxBaseAmountNormal(row) {
        const bdtBuying = QuotationHelpers.parseFloat(row.bdt_buying);
        const airSeaFreight = QuotationHelpers.parseFloat(row.air_sea_freight);
        return bdtBuying + airSeaFreight;
    },

    getAttBaseAmountViaForeign(row) {
        const foreignBuying = QuotationHelpers.parseFloat(row.foreign_currency_buying);
        const airSeaFreight = QuotationHelpers.parseFloat(row.air_sea_freight);
        const taxAmount = QuotationHelpers.parseFloat(row.tax);
        return foreignBuying + airSeaFreight + taxAmount;
    },

    // Get base amount for ATT calculation (Normal transactions - BDT)
    // ATT base = bdt_buying + air_sea_freight_amount + tax_amount
    getAttBaseAmountNormal(row) {
        const bdtBuying = QuotationHelpers.parseFloat(row.bdt_buying);
        const airSeaFreight = QuotationHelpers.parseFloat(row.air_sea_freight);
        const taxAmount = QuotationHelpers.parseFloat(row.tax);
        return bdtBuying + airSeaFreight + taxAmount;
    },

    // ========================================================================
    // UNIFIED TAX AND ATT CALCULATION METHODS
    // ========================================================================

    // Calculate tax amount from percentage
    calculateTaxAmount(row, percentage, isViaForeign) {
        const baseAmount = isViaForeign
            ? this.getTaxBaseAmountViaForeign(row)
            : this.getTaxBaseAmountNormal(row);
        return this.calculateAmount(percentage, baseAmount);
    },

    // Calculate tax percentage from amount
    calculateTaxPercentage(row, amount, isViaForeign) {
        const baseAmount = isViaForeign
            ? this.getTaxBaseAmountViaForeign(row)
            : this.getTaxBaseAmountNormal(row);
        return this.calculatePercentage(amount, baseAmount);
    },

    // Calculate ATT amount from percentage
    calculateAttAmount(row, percentage, isViaForeign) {
        const baseAmount = isViaForeign
            ? this.getAttBaseAmountViaForeign(row)
            : this.getAttBaseAmountNormal(row);
        return this.calculateAmount(percentage, baseAmount);
    },

    // Calculate ATT percentage from amount
    calculateAttPercentage(row, amount, isViaForeign) {
        const baseAmount = isViaForeign
            ? this.getAttBaseAmountViaForeign(row)
            : this.getAttBaseAmountNormal(row);
        return this.calculatePercentage(amount, baseAmount);
    },

    // ========================================================================
    // MARGIN CALCULATION BASE AMOUNTS (Refactored according to business rules)
    // ========================================================================

    // Get base amount for margin calculation (Via transactions - Foreign currency)
    // Margin base = foreign_currency_buying + air_sea_freight_amount + tax_amount + att_amount
    getMarginBaseAmountViaForeign(row) {
        const foreignBuying = QuotationHelpers.parseFloat(row.foreign_currency_buying);
        const airSeaFreight = QuotationHelpers.parseFloat(row.air_sea_freight);
        const taxAmount = QuotationHelpers.parseFloat(row.tax);
        const attAmount = QuotationHelpers.parseFloat(row.att);
        return foreignBuying + airSeaFreight + taxAmount + attAmount;
    },

    // Get base amount for margin calculation (Normal transactions - BDT)
    // Margin base = bdt_buying + air_sea_freight_amount + tax_amount + att_amount
    getMarginBaseAmountNormal(row) {
        const bdtBuying = QuotationHelpers.parseFloat(row.bdt_buying);
        const airSeaFreight = QuotationHelpers.parseFloat(row.air_sea_freight);
        const taxAmount = QuotationHelpers.parseFloat(row.tax);
        const attAmount = QuotationHelpers.parseFloat(row.att);
        return bdtBuying + airSeaFreight + taxAmount + attAmount;
    },

    // ========================================================================
    // UNIFIED MARGIN CALCULATION METHODS
    // ========================================================================

    // Calculate margin amount from percentage
    calculateMarginAmount(row, percentage, isViaForeign) {
        const baseAmount = isViaForeign
            ? this.getMarginBaseAmountViaForeign(row)
            : this.getMarginBaseAmountNormal(row);
        console.log(baseAmount);

        return this.calculateAmount(percentage, baseAmount);
    },

    // Calculate margin percentage from amount
    calculateMarginPercentage(row, amount, isViaForeign) {
        const baseAmount = isViaForeign
            ? this.getMarginBaseAmountViaForeign(row)
            : this.getMarginBaseAmountNormal(row);
        return this.calculatePercentage(amount, baseAmount);
    },

    // ========================================================================
    // LEGACY METHODS (Kept for backward compatibility)
    // ========================================================================

    // Legacy methods for backward compatibility (now delegate to new methods)
    getBaseAmountForTaxBdt(row) {
        return this.getTaxBaseAmountNormal(row);
    },

    getBaseAmountForTaxForeign(row) {
        return this.getTaxBaseAmountViaForeign(row);
    },

    getBaseAmountForMarginBdt(row) {
        return this.getMarginBaseAmountNormal(row);
    },

    getBaseAmountForMarginForeign(row) {
        return this.getMarginBaseAmountViaForeign(row);
    },

    getBaseAmountForTax(row, exchangeRate) {
        return this.getTaxBaseAmountNormal(row);
    },

    getBaseAmountForMargin(row, exchangeRate) {
        return this.getMarginBaseAmountNormal(row);
    },

    // Calculate line total for a product row
    calculateLineTotal(row, quotationType, currency, exchangeRate, baseCurrency = 'BDT') {
        const quantity = QuotationHelpers.parseFloat(row.quantity);
        const isViaForeign = this.isViaForeignCurrency(quotationType, currency, baseCurrency);
        const rate = QuotationHelpers.parseFloat(exchangeRate);

        // Early return for empty via foreign rows
        if (isViaForeign && !row.bdt_buying && !row.unit_price && !row.foreign_currency_buying) {
            return 0;
        }

        // If unit price exists, use it directly
        if (row.unit_price) {
            const unitPrice = QuotationHelpers.parseFloat(row.unit_price);
            return isViaForeign && rate ? unitPrice * quantity * rate : unitPrice * quantity;
        }

        // Calculate from cost components
        const unitCostBdt = this.getUnitCostBdt(row, rate);
        const lineBaseCost = unitCostBdt * quantity;
        const transportAndAddons = QuotationHelpers.parseFloat(row.air_sea_freight) + QuotationHelpers.parseFloat(row.att);
        const lineTax = QuotationHelpers.parseFloat(row.tax);
        const costBeforeMargin = lineBaseCost + transportAndAddons + lineTax;
        const margin = QuotationHelpers.parseFloat(row.margin);

        return costBeforeMargin * (1 + (margin / 100));
    },

    // Calculate percentage from amount and base
    calculatePercentage(amount, base) {
        const amountVal = QuotationHelpers.parseFloat(amount);
        const baseVal = QuotationHelpers.parseFloat(base);
        return baseVal > 0 ? parseFloat((amountVal / baseVal * 100).toFixed(2)) : 0;
    },

    // Calculate amount from percentage and base
    calculateAmount(percentage, base) {
        const percentVal = QuotationHelpers.parseFloat(percentage);
        const baseVal = QuotationHelpers.parseFloat(base);
        return parseFloat((baseVal * percentVal / 100).toFixed(2));
    }
};

export default CalculationEngine;
