import QuotationHelpers from "../helpers/quotationHelpers";
import CalculationEngine from "../helpers/calculationEngine";

export default {
  async loadAllExchangeRates() {
    this.exchangeRateLoading = true;
    this.exchangeRateMessage = '';
    try {
      const response = await fetch(this.routes.exchangeRate);
      const data = await response.json();

      if (data.success) {
        this.allExchangeRates = data.rates;
        this.lastUpdated = data.last_updated;
        this.exchangeRateMessage = data.fallback
          ? data.message
          : `Rates as of ${this.lastUpdated}`;

        if (this.quotation_revision.currency) {
          this.updateExchangeRate();
        }
      } else {
        this.exchangeRateMessage = data.message || 'Could not load exchange rates.';
      }
    } catch (error) {
      console.error('Error fetching exchange rates:', error);
      this.exchangeRateMessage = 'Failed to fetch rates. Please enter manually.';
    } finally {
      this.exchangeRateLoading = false;
    }
  },

  updateExchangeRate() {
    const currency = this.quotation_revision.currency;

    if (currency && this.allExchangeRates[currency]) {
      this.quotation_revision.exchange_rate = this.allExchangeRates[currency];
      this.exchangeRateMessage = `Rate: 1 ${currency} = ${this.quotation_revision.exchange_rate} BDT (${this.lastUpdated})`;
      this.updateAllBdtBuyingValues();
    } else {
      this.quotation_revision.exchange_rate = '';
      if (currency) {
        this.exchangeRateMessage = 'Please enter exchange rate manually';
      }
    }
  },

  updateAllBdtBuyingValues() {
    this.addVisualFeedback('currency-section', 'bg-blue-200 dark:bg-blue-900/30 border-2 border-blue-400', 2000);

    this.quotation_products.forEach((row, index) => {
      const currency = this.quotation_revision.currency;
      const exchangeRate = this.quotation_revision.exchange_rate;

      if (currency && currency !== 'BDT') {
        if (row.foreign_currency_buying && exchangeRate) {
          this.calculateForeignCurrencyEquivalent(index);
          this.addVisualFeedback(`product-${index}`, 'bg-green-100 dark:bg-green-900/20 border border-green-400', 1500);
        } else if (!row.foreign_currency_buying && row.bdt_buying && exchangeRate) {
          this.calculateBdtToForeignEquivalent(index);
          this.addVisualFeedback(`product-${index}`, 'bg-blue-100 dark:bg-blue-900/20 border border-blue-400', 1500);
        }
      } else {
        if (row.foreign_currency_buying) {
          row.foreign_currency_buying = 0;
          this.addVisualFeedback(`product-${index}`, 'bg-yellow-100 dark:bg-yellow-900/20 border border-yellow-400', 1500);
        }
      }
      this.calculateUnitPrice(index);
    });
    this.calculateTotals();
  }
};