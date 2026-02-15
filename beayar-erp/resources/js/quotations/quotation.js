import Swal from "sweetalert2";

import QuotationHelpers from "./helpers/quotationHelpers";
import CalculationEngine from "./helpers/calculationEngine";
import exchangeRates from "./modules/exchangeRates";
import calculations from "./modules/calculations";
import events from "./modules/events";
import validation from "./modules/validation";
import { brandOriginSearchableSelect, brandOriginModal } from "./modules/brandOrigins";

window.QuotationHelpers = QuotationHelpers;

document.addEventListener('alpine:init', () => {
    Alpine.data('quotationForm', (config) => initQuotationForm(config));
    Alpine.data('brandOriginSearchableSelect', brandOriginSearchableSelect);
    Alpine.data('brandOriginModal', brandOriginModal);
});


function initQuotationForm(config = {}) {
    return {
        // --- Grouped Data Structure ---
        quotation: {
            customer_id: config.oldQuotation?.customer_id || '',
            quotation_no: config.oldQuotation?.quotation_no || '',
            ship_to: config.oldQuotation?.ship_to || '',
            quotation_id: config.oldQuotation?.quotation_id || '',
        },

        quotation_revision: {
            id: config.oldQuotationRevision?.id || '',
            type: config.oldQuotationRevision?.type || 'normal',
            date: config.oldQuotationRevision?.date || '',
            validity: config.oldQuotationRevision?.validity || '',
            currency: config.oldQuotationRevision?.currency || '',
            exchange_rate: config.oldQuotationRevision?.exchange_rate || '',
            subtotal: 0,
            discount: config.oldQuotationRevision?.discount || 0,
            discount_percentage: config.oldQuotationRevision?.discount_percentage || 0,
            discounted_price: 0,
            air_freight: 0,
            shipping: config.oldQuotationRevision?.shipping || 0,
            vat_percentage: config.oldQuotationRevision?.vat_percentage ?? 15,
            vat_amount: 0,
            margin_percentage: 0,
            total: 0,
            saved_as: config.oldQuotationRevision?.saved_as || 'draft',
            terms_conditions: config.oldQuotationRevision?.terms_conditions || '',
        },


        quotation_products: config.oldQuotationProducts || [QuotationHelpers.createEmptyProductRow()],

        // --- Helper Properties ---
        selectedCustomerId: null,
        selectedCustomer: null,
        autoCalculateValidity: true,
        validityDays: config.validityDays || 15,
        exchangeRateLoading: false,
        exchangeRateMessage: '',
        allExchangeRates: {},
        lastUpdated: '',
        discount_percentage: config.discount_percentage || 0,
        isSubmitting: false,
        showCurrencyWarning: false,
        showProductPricingWarning: false,
        showSaveDropdown: false,
        lastExchangeRate: config.oldQuotationRevision?.exchange_rate || '',

        // Modals
        specificationModal: QuotationHelpers.createEmptyModal('specification'),
        createProductModal: QuotationHelpers.createEmptyModal('createProduct'),
        showUploadImageModal: false,
        uploadImageModal: QuotationHelpers.createEmptyModal('uploadImage'),
        validationModal: {
            show: false,
            errors: []
        },

        // Store routes, CSRF token, and mode
        routes: config.routes || {},
        csrfToken: config.csrfToken || '',
        mode: config.mode || 'create',

        format2(value) {
            return QuotationHelpers.format2(value);
        },

        formatToApi(dateStr) {
            return QuotationHelpers.formatToApi(dateStr);
        },

        // ========================================================================
        // INITIALIZATION
        // ========================================================================

        init() {
            this.quotation_revision.date = this.quotation_revision.date || QuotationHelpers.getCurrentDate();

            // Initialize selectedCustomerId from existing quotation data
            if (this.quotation.customer_id) {
                this.selectedCustomerId = this.quotation.customer_id;
            }

            // Normalize and set up watchers first
            this.normalizeInitialData();
            this.setupWatchers();

            // In create mode, auto-load exchange rates and defaults
            if (this.mode === 'create') {
                this.loadAllExchangeRates();
                this.handleQuotationTypeChange();
                this.applyDefaultCurrencySelection();
            }

            // Calculate totals using current state (DB values in edit mode)
            this.calculateTotals();

            if (config.oldQuotationProducts[0].product_id) {
                for (let i = 0; i < config.oldQuotationProducts.length; i++) {
                    this.handleProductEdit(config.oldQuotationProducts[i], i);
                }
            }
        },

        normalizeInitialData() {
            // Normalize dates
            this.quotation_revision.date = this.formatToDisplay(this.quotation_revision.date) || this.quotation_revision.date;
            this.quotation_revision.validity = this.formatToDisplay(this.quotation_revision.validity) || this.quotation_revision.validity;

            // Normalize product rows
            this.quotation_products = this.quotation_products.map((row) => ({
                ...row,
                air_sea_freight_rate: typeof row.air_sea_freight_rate !== 'undefined' ? row.air_sea_freight_rate : 0,
                tax_percentage: typeof row.tax_percentage !== 'undefined' ? row.tax_percentage : QuotationHelpers.parseFloat(row.tax_percentage),
                att_percentage: typeof row.att_percentage !== 'undefined' ? row.att_percentage : QuotationHelpers.parseFloat(row.att_percentage),
            }));

            this.updateValidityAuto();
        },

        setupWatchers() {
            this.$watch('quotation_revision.date', () => {
                this.updateQuotationNumber();
                this.updateValidityAuto();
            });
            this.$watch('selectedCustomerId', () => this.updateQuotationNumber());
            this.$watch('autoCalculateValidity', () => this.updateValidityAuto());
            this.$watch('validityDays', () => this.updateValidityAuto());
            // Automatically update BDT buying values when exchange rate changes
            this.$watch('quotation_revision.exchange_rate', () => {
                try {
                    const newRate = this.quotation_revision.exchange_rate;
                    if (newRate !== undefined && newRate !== null && newRate !== '') {
                        this.lastExchangeRate = newRate;
                    }
                    this.updateAllBdtBuyingValues();
                } catch (e) {
                    console.warn('Exchange rate watcher failed:', e);
                }
            });
        },

        // Ensure default currency selection on initial load
        applyDefaultCurrencySelection() {
            try {
                // Auto-select USD if no currency is set, regardless of type
                if (!this.quotation_revision.currency) {
                    this.quotation_revision.currency = 'USD';
                    // Only update exchange rate for foreign currency mode
                    if (this.quotation_revision.type === 'via') {
                        this.updateExchangeRate();
                    }
                }
            } catch (e) {
                console.warn('Default currency selection failed:', e);
            }
        },

        // ========================================================================
        // DATE UTILITIES (Delegated to Helper)
        // ========================================================================

        formatToApi(dateStr) {
            return QuotationHelpers.formatToApi(dateStr);
        },

        formatToDisplay(isoStr) {
            return QuotationHelpers.formatToDisplay(isoStr);
        },

        updateValidityAuto() {
            try {
                if (!this.autoCalculateValidity || !this.quotation_revision.date) return;

                const parts = this.quotation_revision.date.split(/[\/]/);
                if (parts.length !== 3) return;

                const [dd, mm, yyyy] = parts;
                const base = new Date(Number(yyyy), Number(mm) - 1, Number(dd));
                if (isNaN(base)) return;

                const days = QuotationHelpers.parseNumber(this.validityDays);
                base.setDate(base.getDate() + days);

                const y = base.getFullYear();
                const m = String(base.getMonth() + 1).padStart(2, '0');
                const d = String(base.getDate()).padStart(2, '0');
                this.quotation_revision.validity = `${d}/${m}/${y}`;
            } catch (e) {
                console.warn('Validity auto-calc failed:', e);
            }
        },

        // ========================================================================
        // EXCHANGE RATE MANAGEMENT
        // ========================================================================

        async loadAllExchangeRates() { return exchangeRates.loadAllExchangeRates.call(this); },
        updateExchangeRate() { return exchangeRates.updateExchangeRate.call(this); },
        updateAllBdtBuyingValues() { return exchangeRates.updateAllBdtBuyingValues.call(this); },

        // ========================================================================
        // QUOTATION NUMBER MANAGEMENT
        // ========================================================================

        async updateQuotationNumber() {
            try {
                const customerId = this.selectedCustomerId;
                const date = this.formatToApi(this.quotation_revision.date);

                if (!customerId || !date) {
                    this.quotation.quotation_no = '';
                    return;
                }

                const url = `${this.routes.nextNumber}?customer_id=${encodeURIComponent(customerId)}&date=${encodeURIComponent(date)}`;
                const response = await fetch(url);

                if (!response.ok) {
                    console.error('Failed to fetch quotation number');
                    return;
                }

                const data = await response.json();
                if (data && data.quotation_no) {
                    this.quotation.quotation_no = data.quotation_no;
                }
            } catch (error) {
                console.error('Error fetching quotation number:', error);
            }
        },

        // ========================================================================
        // PRODUCT ROW MANAGEMENT
        // ========================================================================

        addRow() {
            // Get the last product to copy values from
            const lastProduct = this.quotation_products.length > 0
                ? this.quotation_products[this.quotation_products.length - 1]
                : null;

            // Create new row with copied values from last product
            const newRow = QuotationHelpers.createProductRowFromLast(lastProduct);

            // Add the new row to the products array
            this.quotation_products.push(newRow);
        },

        removeRow(index) {
            if (this.quotation_products.length > 1) {
                this.quotation_products.splice(index, 1);
                this.calculateTotals();
            }
        },

        // ========================================================================
        // CALCULATION METHODS (Using CalculationEngine)
        // ========================================================================

        calculateLineTotal(row) { return calculations.calculateLineTotal.call(this, row); },

        calculateTotals() { return calculations.calculateTotals.call(this); },

        // ========================================================================
        // CURRENCY CONVERSION METHODS
        // ========================================================================

        calculateForeignCurrencyEquivalent(index) {
            // Perform conversion via existing module, then cascade recalculation
            calculations.calculateForeignCurrencyEquivalent.call(this, index);
            this.calculateTaxAmount(index);
            this.calculateAttAmount(index);
            this.calculateMarginValue(index);
            this.calculateUnitPrice(index);
        },

        calculateBdtToForeignEquivalent(index) {
            calculations.calculateBdtToForeignEquivalent.call(this, index);
            this.calculateTaxAmount(index);
            this.calculateAttAmount(index);
            this.calculateMarginValue(index);
            this.calculateUnitPrice(index);
        },

        // ========================================================================
        // UNIT PRICE CALCULATION
        // ========================================================================

        calculateUnitPrice(index) {
            const row = this.quotation_products[index];
            const base = (this.quotation_revision.type === 'via'
                ? QuotationHelpers.parseNumber(row.foreign_currency_buying)
                : QuotationHelpers.parseNumber(row.bdt_buying)) || 0;
            const freight = QuotationHelpers.parseNumber(row.air_sea_freight) || 0;
            const taxAmount = QuotationHelpers.parseNumber(row.tax) || 0;
            const attAmount = QuotationHelpers.parseNumber(row.att) || 0;
            const marginValue = QuotationHelpers.parseNumber(row.margin_value) || 0;
            const total = base + freight + taxAmount + attAmount + marginValue;
            row.unit_price = parseFloat(total.toFixed(2));
            return row.unit_price;
        },

        calculateUnitPriceViaForeign(row, exchangeRate) { return calculations.calculateUnitPriceViaForeign.call(this, row, exchangeRate); },

        calculateUnitPriceNormal(row, exchangeRate) { return calculations.calculateUnitPriceNormal.call(this, row, exchangeRate); },

        // ========================================================================
        // TAX AND ATT CALCULATIONS (Consolidated)
        // ========================================================================

        calculateTaxAmount(index) {
            const row = this.quotation_products[index];
            const base = (this.quotation_revision.type === 'via'
                ? QuotationHelpers.parseNumber(row.foreign_currency_buying)
                : QuotationHelpers.parseNumber(row.bdt_buying)) || 0;
            const freight = QuotationHelpers.parseNumber(row.air_sea_freight) || 0;
            const perc = (QuotationHelpers.parseNumber(row.tax_percentage) || 0) / 100;
            const denom = base + freight;
            row.tax = parseFloat((denom * perc).toFixed(2));
            this.calculateAttAmount(index);
            this.calculateMarginValue(index);
            this.calculateUnitPrice(index);
            return row.tax;
        },

        calculateTaxPercentage(index) {
            const row = this.quotation_products[index];
            const base = (this.quotation_revision.type === 'via'
                ? QuotationHelpers.parseNumber(row.foreign_currency_buying)
                : QuotationHelpers.parseNumber(row.bdt_buying)) || 0;
            const freight = QuotationHelpers.parseNumber(row.air_sea_freight) || 0;
            const denom = base + freight;
            const taxAmount = QuotationHelpers.parseNumber(row.tax) || 0;
            const percent = denom > 0 ? (taxAmount / denom) * 100 : 0;
            row.tax_percentage = parseFloat(percent.toFixed(2));
            this.calculateTaxAmount(index);
            return row.tax_percentage;
        },

        calculateAttAmount(index) {
            const row = this.quotation_products[index];
            const base = (this.quotation_revision.type === 'via'
                ? QuotationHelpers.parseNumber(row.foreign_currency_buying)
                : QuotationHelpers.parseNumber(row.bdt_buying)) || 0;
            const freight = QuotationHelpers.parseNumber(row.air_sea_freight) || 0;
            const taxAmount = QuotationHelpers.parseNumber(row.tax) || 0;
            const perc = (QuotationHelpers.parseNumber(row.att_percentage) || 0) / 100;
            const denom = base + freight + taxAmount;
            row.att = parseFloat((denom * perc).toFixed(2));
            this.calculateMarginValue(index);
            this.calculateUnitPrice(index);
            return row.att;
        },

        calculateAttPercentage(index) {
            const row = this.quotation_products[index];
            const base = (this.quotation_revision.type === 'via'
                ? QuotationHelpers.parseNumber(row.foreign_currency_buying)
                : QuotationHelpers.parseNumber(row.bdt_buying)) || 0;
            const freight = QuotationHelpers.parseNumber(row.air_sea_freight) || 0;
            const taxAmount = QuotationHelpers.parseNumber(row.tax) || 0;
            const denom = base + freight + taxAmount;
            const attAmount = QuotationHelpers.parseNumber(row.att) || 0;
            const percent = denom > 0 ? (attAmount / denom) * 100 : 0;
            row.att_percentage = parseFloat(percent.toFixed(2));
            this.calculateAttAmount(index);
            return row.att_percentage;
        },

        calculateAmountFromPercentage(index, amountField, percentageField) { return calculations.calculateAmountFromPercentage.call(this, index, amountField, percentageField); },

        calculatePercentageFromAmount(index, amountField, percentageField) { return calculations.calculatePercentageFromAmount.call(this, index, amountField, percentageField); },

        // ========================================================================
        // AIR/SEA FREIGHT CALCULATION
        // ========================================================================

        calculateAirSeaFreight(index) {
            const row = this.quotation_products[index];
            const rate = QuotationHelpers.parseNumber(row.air_sea_freight_rate) || 0;
            const weight = QuotationHelpers.parseNumber(row.weight) || 0;
            row.air_sea_freight = parseFloat((rate * weight).toFixed(2));
            this.calculateTaxAmount(index);
            this.calculateAttAmount(index);
            this.calculateMarginValue(index);
            this.calculateUnitPrice(index);
            return row.air_sea_freight;
        },

        // ========================================================================
        // MARGIN CALCULATIONS
        // ========================================================================

        calculateMarginValue(index) {
            const row = this.quotation_products[index];
            const base = (this.quotation_revision.type === 'via'
                ? QuotationHelpers.parseNumber(row.foreign_currency_buying)
                : QuotationHelpers.parseNumber(row.bdt_buying)) || 0;
            const freight = QuotationHelpers.parseNumber(row.air_sea_freight) || 0;
            const taxAmount = QuotationHelpers.parseNumber(row.tax) || 0;
            const attAmount = QuotationHelpers.parseNumber(row.att) || 0;
            const perc = (QuotationHelpers.parseNumber(row.margin) || 0) / 100;
            const denom = base + freight + taxAmount + attAmount;
            row.margin_value = parseFloat((denom * perc).toFixed(2));
            this.calculateUnitPrice(index);
            return row.margin_value;
        },

        calculateMarginPercentage(index) {
            const row = this.quotation_products[index];
            const base = (this.quotation_revision.type === 'via'
                ? QuotationHelpers.parseNumber(row.foreign_currency_buying)
                : QuotationHelpers.parseNumber(row.bdt_buying)) || 0;
            const freight = QuotationHelpers.parseNumber(row.air_sea_freight) || 0;
            const taxAmount = QuotationHelpers.parseNumber(row.tax) || 0;
            const attAmount = QuotationHelpers.parseNumber(row.att) || 0;
            const denom = base + freight + taxAmount + attAmount;
            const marginValue = QuotationHelpers.parseNumber(row.margin_value) || 0;
            const percent = denom > 0 ? (marginValue / denom) * 100 : 0;
            row.margin = parseFloat(percent.toFixed(2));
            this.calculateMarginValue(index);
            return row.margin;
        },

        // ========================================================================
        // DISCOUNT CALCULATIONS
        // ========================================================================

        calculateDiscountAmount() { return calculations.calculateDiscountAmount.call(this); },

        calculateDiscountPercentage() { return calculations.calculateDiscountPercentage.call(this); },

        // ========================================================================
        // LABEL AND DISPLAY HELPERS
        // ========================================================================

        getForeignCurrencyLabel() { return calculations.getForeignCurrencyLabel.call(this); },

        getFinalUnitPriceLabel() { return calculations.getFinalUnitPriceLabel.call(this); },

        getBdtEquivalentUnitPrice(row) { return calculations.getBdtEquivalentUnitPrice.call(this, row); },

        getForeignCurrencyLineTotal(row) { return calculations.getForeignCurrencyLineTotal.call(this, row); },

        // ========================================================================
        // EVENT HANDLERS
        // ========================================================================

        handleCurrencyChange() {
            const prevRate = this.quotation_revision.exchange_rate;
            const result = events.handleCurrencyChange.call(this);
            if (!this.quotation_revision.exchange_rate && (prevRate || this.lastExchangeRate)) {
                this.quotation_revision.exchange_rate = prevRate || this.lastExchangeRate;
            }
            return result;
        },

        // Legacy function name for backward compatibility
        onCurrencyChange() {
            const prevRate = this.quotation_revision.exchange_rate;
            const result = events.onCurrencyChange.call(this);
            if (!this.quotation_revision.exchange_rate && (prevRate || this.lastExchangeRate)) {
                this.quotation_revision.exchange_rate = prevRate || this.lastExchangeRate;
            }
            return result;
        },

        onQuantityChange(index) { return events.onQuantityChange.call(this, index); },

        handleQuotationTypeChange() {
            const prevRate = this.quotation_revision.exchange_rate;
            const result = events.handleQuotationTypeChange.call(this);
            if (!this.quotation_revision.exchange_rate && (prevRate || this.lastExchangeRate)) {
                this.quotation_revision.exchange_rate = prevRate || this.lastExchangeRate;
            }
            return result;
        },

        // Legacy function name for backward compatibility
        onQuotationTypeChange() {
            const prevRate = this.quotation_revision.exchange_rate;
            const result = events.onQuotationTypeChange.call(this);
            if (!this.quotation_revision.exchange_rate && (prevRate || this.lastExchangeRate)) {
                this.quotation_revision.exchange_rate = prevRate || this.lastExchangeRate;
            }
            return result;
        },

        clearCurrencyForNormalQuotation() { return events.clearCurrencyForNormalQuotation.call(this); },

        setDefaultCurrencyForViaQuotation() { return events.setDefaultCurrencyForViaQuotation.call(this); },

        // ========================================================================
        // VALIDATION
        // ========================================================================

        validateViaQuotationCurrency() { return validation.validateViaQuotationCurrency.call(this); },

        validateForm() { return validation.validateForm.call(this); },

        validateQuotationFields(errors) { return validation.validateQuotationFields.call(this, errors); },

        validateCurrency(errors) { return validation.validateCurrency.call(this, errors); },

        validateProducts(errors) { return validation.validateProducts.call(this, errors); },

        // ========================================================================
        // CUSTOMER HANDLING
        // ========================================================================

        async handleCustomerSelection(payload) {
            const customer = payload?.detail?.option ?? payload;

            this.selectedCustomer = customer;
            this.selectedCustomerId = customer?.id ?? null;
            this.quotation.customer_id = customer?.id ?? null;
            this.quotation.ship_to = customer.address;

            // if (!this.quotation.ship_to && customer?.address) {
            // this.quotation.ship_to = customer.address;
            // }

            await this.updateQuotationNumber();
        },

        // ========================================================================
        // PRODUCT HANDLING
        // ========================================================================

        async handleProductEdit(product, index) {

            const row = this.quotation_products[index];
            row.product_id = product.product_id;

            await this.loadProductSpecifications(row);

            // Check if the product has a specification_id (from the database)
            if (product.specification_id) {
                row.specification_id = product.specification_id;
            } else {
                this.resetProductSpecification(row);

                // Auto-select the first specification if available
                if (row.specifications && row.specifications.length > 0) {
                    row.specification_id = row.specifications[0].id;
                }
            }

            this.updateProductSearchableSelect();
        },

        async handleProductSelection(product, index) {

            const row = this.quotation_products[index];

            row.product_id = product.detail.option.id;

            await this.loadProductSpecifications(row);
            this.resetProductSpecification(row);

            // Auto-select the first specification if available
            if (row.specifications && row.specifications.length > 0) {
                row.specification_id = row.specifications[0].id;
            }

            this.updateProductSearchableSelect();
        },

        async loadProductSpecifications(row) {
            try {
                const url = this.buildProductSpecificationsUrl(row.product_id);
                const response = await fetch(url);
                const data = await response.json();

                row.specifications = data.success ? (data.specifications || []) : [];
            } catch (error) {
                console.error('Error loading product specifications:', error);
                row.specifications = [];
            }
        },

        resetProductSpecification(row) {
            row.specification_id = '';
            // row.add_spec = '';
        },

        buildProductSpecificationsUrl(productId) {
            try {
                const origin = window.location.origin;
                const pathname = window.location.pathname || '';
                const id = String(productId ?? '').trim();

                if (!id) {
                    throw new Error('Invalid product ID');
                }

                const inDashboard = pathname.startsWith('/dashboard');
                const basePath = inDashboard ? '/dashboard' : '';

                return `${origin}${basePath}/products/${encodeURIComponent(id)}/specifications`;
            } catch (e) {
                console.error('Error building specifications URL:', e);
                return `/products/${encodeURIComponent(productId)}/specifications`;
            }
        },

        updateProductSearchableSelect() {
            this.$nextTick(() => {
                window.dispatchEvent(new CustomEvent('update-searchable-selects'));
            });
        },

        // ========================================================================
        // TERMS AND CONDITIONS
        // ========================================================================

        useDefaultTerms() {
            this.quotation_revision.terms_conditions = QuotationHelpers.getDefaultTerms();
        },
        useViaTerms() {
            this.quotation_revision.terms_conditions = QuotationHelpers.getViaTerms();
        },

        // ========================================================================
        // SPECIFICATION MODAL
        // ========================================================================

        openSpecificationModal(index) {
            const row = this.quotation_products[index];
            this.specificationModal = {
                show: true,
                productIndex: index,
                specifications: row.specifications || [],
                selectedId: row.specification_id || null
            };
        },

        closeSpecificationModal() {
            this.specificationModal = QuotationHelpers.createEmptyModal('specification');
        },

        selectSpecification(specId) {
            this.specificationModal.selectedId = specId;
        },

        confirmSpecificationSelection() {
            if (this.specificationModal.productIndex !== null) {
                const row = this.quotation_products[this.specificationModal.productIndex];
                row.specification_id = this.specificationModal.selectedId;
            }
            this.closeSpecificationModal();
        },

        getSelectedSpecificationText(row) {

            if (!row.specification_id || !row.specifications) return '';
            const spec = row.specifications.find(s => s.id == row.specification_id);

            return spec ? spec.description : '';
        },

        getSelectedSpecificationSummary(row) {
            if (!row.specification_id || !row.specifications) return 'Select specification';

            const spec = row.specifications.find(s => s.id == row.specification_id);
            if (!spec || !spec.description) return 'Select specification';

            // Generate a 3-4 word summary from the specification text
            const words = spec.description.trim().split(/\s+/);
            if (words.length <= 4) {
                return spec.description;
            }

            // Take first 3 words and add ellipsis
            return words.slice(0, 3).join(' ') + '...';
        },

        // ========================================================================
        // CREATE PRODUCT MODAL
        // ========================================================================

        openCreateProductModal(index) {
            this.createProductModal = {
                ...QuotationHelpers.createEmptyModal('createProduct'),
                show: true,
                productIndex: index
            };
        },

        closeCreateProductModal() {
            this.createProductModal = QuotationHelpers.createEmptyModal('createProduct');
        },

        async createAndSelectProduct() {
            this.createProductModal.errors = {};
            this.createProductModal.errorMessage = '';
            this.createProductModal.successMessage = '';

            if (!this.createProductModal.productName.trim()) {
                this.createProductModal.errors.productName = 'Product name is required';
                return;
            }

            this.createProductModal.creating = true;

            try {
                const formData = new FormData();
                formData.append('name', this.createProductModal.productName.trim());

                if (this.createProductModal.imageId) {
                    formData.append('image_id', this.createProductModal.imageId);
                }

                if (this.createProductModal.specification.trim()) {
                    formData.append('specifications[0][description]', this.createProductModal.specification.trim());
                }

                const response = await fetch(this.routes.createProduct, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const productIndex = this.createProductModal.productIndex;

                    this.quotation_products[productIndex].product_id = data.product.id;

                    if (data.product.specifications && data.product.specifications.length > 0) {
                        this.quotation_products[productIndex].specifications = data.product.specifications;
                        this.quotation_products[productIndex].specification_id = data.product.specifications[0].id;
                    }

                    this.updateProductSearchableSelect(productIndex, data.product);

                    this.createProductModal.successMessage = 'Product created and added successfully!';

                    setTimeout(() => {
                        this.closeCreateProductModal();

                        if (window.Swal) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Product Created!',
                                text: `${data.product.name} has been created and added to your quotation.`,
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    }, 1000);

                } else {
                    if (data.errors) {
                        this.createProductModal.errors = data.errors;
                        this.createProductModal.errorMessage = 'Please fix the errors and try again.';
                    } else {
                        this.createProductModal.errorMessage = data.message || 'Failed to create product. Please try again.';
                    }
                }
            } catch (error) {
                console.error('Error creating product:', error);
                this.createProductModal.errorMessage = 'An unexpected error occurred. Please try again.';
            } finally {
                this.createProductModal.creating = false;
            }
        },

        openImageLibraryForNewProduct() {
            this.showUploadImageModal = true;
        },

        clearNewProductImage() {
            this.createProductModal.imageId = null;
            this.createProductModal.imageUrl = null;
        },

        async uploadImageForNewProduct() {
            const fileInput = document.getElementById('new-product-image-upload');
            const file = fileInput.files?.[0];

            if (!file || !this.uploadImageModal.imageName.trim()) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Information',
                        text: 'Please provide a name and select an image.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
                return;
            }

            try {
                this.validateFile(file);
                this.uploadImageModal.uploading = true;

                if (window.Swal) {
                    Swal.fire({
                        title: 'Processing Image...',
                        text: 'Compressing image for upload, please wait.',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => Swal.showLoading()
                    });
                }

                const compressedFile = await this.compressImage(file);

                if (window.Swal) {
                    Swal.update({
                        text: 'Uploading compressed image...'
                    });
                }

                const formData = new FormData();
                formData.append('name', this.uploadImageModal.imageName);
                formData.append('image', compressedFile, compressedFile.name);

                const response = await fetch(this.routes.imagesStore || '/images', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.createProductModal.imageId = data.image.id;
                    this.createProductModal.imageUrl = data.image.path.startsWith('http') ?
                        data.image.path :
                        `${window.location.origin}/${data.image.path.replace(/^\/+/, '')}`;

                    this.resetUploadModal();

                    if (window.Swal) {
                        const compressionInfo = compressedFile.size < file.size ?
                            `<small>Compressed by ${Math.round(((file.size - compressedFile.size) / file.size) * 100)}%</small>` : '';

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            html: `<p>${data.message}</p>${compressionInfo}`,
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }

                } else {
                    throw new Error(data.message || 'Upload failed due to a server error.');
                }
            } catch (error) {
                console.error('Upload error:', error);
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed',
                        text: error.message || 'An unexpected error occurred.',
                        showConfirmButton: true
                    });
                }
            } finally {
                this.uploadImageModal.uploading = false;
            }
        },

        // ========================================================================
        // VALIDATION MODAL
        // ========================================================================

        showValidationErrors(errors) {
            this.validationModal = {
                show: true,
                errors: Array.isArray(errors) ? errors : [errors]
            };
        },

        closeValidationModal() {
            this.validationModal = {
                show: false,
                errors: []
            };
        },

        // ========================================================================
        // FILE VALIDATION
        // ========================================================================

        validateFile(file) {
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (file.size > maxSize) {
                return 'File size must be less than 5MB';
            }

            if (!allowedTypes.includes(file.type)) {
                return 'Only JPEG, PNG, GIF, and WebP images are allowed';
            }

            return null;
        },

        async compressImage(file) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                const objectUrl = URL.createObjectURL(file);

                img.onload = async () => {
                    try {
                        const maxWidth = 1920;
                        const maxHeight = 1080;
                        const quality = 0.8;

                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        const { width, height } = this.calculateDimensions(img.naturalWidth, img.naturalHeight, maxWidth, maxHeight);
                        canvas.width = width;
                        canvas.height = height;
                        ctx.drawImage(img, 0, 0, width, height);

                        const originalType = file.type;
                        let blob = null;
                        let outputType = 'image/webp';
                        let ext = 'webp';

                        blob = await new Promise(res => canvas.toBlob(res, 'image/webp', quality));

                        if (!blob || (originalType !== 'image/png' && blob.size > file.size)) {
                            outputType = 'image/jpeg';
                            ext = 'jpg';
                            blob = await new Promise(res => canvas.toBlob(res, 'image/jpeg', quality));
                        }

                        if (!blob || blob.size > file.size) {
                            resolve(file);
                            return;
                        }

                        const baseName = file.name.replace(/\.[^/.]+$/, '');
                        const newName = `${baseName}.${ext}`;
                        const compressedFile = new File([blob], newName, {
                            type: outputType,
                            lastModified: Date.now()
                        });

                        resolve(compressedFile.size < file.size ? compressedFile : file);
                    } catch (err) {
                        reject(err);
                    } finally {
                        URL.revokeObjectURL(objectUrl);
                    }
                };

                img.onerror = () => {
                    URL.revokeObjectURL(objectUrl);
                    reject(new Error('Failed to load image for compression.'));
                };

                img.src = objectUrl;
            });
        },

        calculateDimensions(originalWidth, originalHeight, maxWidth, maxHeight) {
            let width = originalWidth;
            let height = originalHeight;

            if (width > maxWidth || height > maxHeight) {
                const widthRatio = maxWidth / width;
                const heightRatio = maxHeight / height;
                const ratio = Math.min(widthRatio, heightRatio);
                width = Math.floor(width * ratio);
                height = Math.floor(height * ratio);
            }
            return { width, height };
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        // ========================================================================
        // FORM SUBMISSION
        // ========================================================================

        async saveQuotation(saveAs) {
            try {
                this.isSubmitting = true;

                const errors = this.validateForm();

                if (errors && errors.length > 0) {
                    this.showValidationErrors(errors);
                    this.isSubmitting = false;
                    return;
                }

                let result = null;

                if (saveAs == 'revision') {

                    result = await Swal.fire({
                        title: 'Are you sure?',
                        text: `Do you want to save this quotation as a revision?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, save it!',
                        background: 'rgb(55 65 81)',
                        customClass: {
                            title: 'text-white',
                            htmlContainer: '!text-white',
                        },
                    });
                } else if (saveAs == 'draft-update') {
                    this.quotation_revision.saved_as = 'draft';
                    result = await Swal.fire({
                        title: 'Are you sure?',
                        text: `Do you want to save this quotation as draft?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, save it!',
                        background: 'rgb(55 65 81)',
                        customClass: {
                            title: 'text-white',
                            htmlContainer: '!text-white',
                        },
                    });
                } else if (saveAs == 'quotation-update') {
                    this.quotation_revision.saved_as = 'quotation';
                    result = await Swal.fire({
                        title: 'Are you sure?',
                        text: `Do you want to save this quotation as quotation?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, save it!',
                        background: 'rgb(55 65 81)',
                        customClass: {
                            title: 'text-white',
                            htmlContainer: '!text-white',
                        },
                    });
                } else {
                    // Set the save_as parameter
                    this.quotation_revision.saved_as = saveAs;

                    // Show SweetAlert confirmation
                    result = await Swal.fire({
                        title: 'Are you sure?',
                        text: `Do you want to save this quotation as ${saveAs}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, save it!',
                        background: 'rgb(55 65 81)',
                        customClass: {
                            title: 'text-white',
                            htmlContainer: '!text-white',
                        },
                    });
                }


                if (result.isConfirmed) {
                    // Sync all SunEditor content to their respective textareas
                    if (window.sunEditorUtils) {
                        const allContent = window.sunEditorUtils.getAllEditorsContent();

                        Object.keys(allContent).forEach(textareaId => {
                            const textarea = document.getElementById(textareaId);
                            if (textarea) {
                                textarea.value = allContent[textareaId];
                            }
                        });
                    }

                    this.updateFormDateFields();

                    // Create a new form submission without Alpine.js interference
                    const form = document.querySelector('#quotation-form');
                    if (form) {
                        // Create a new form element to bypass Alpine.js event handling
                        const newForm = document.createElement('form');
                        newForm.action = form.action;
                        newForm.method = form.method;
                        newForm.enctype = form.enctype;

                        // Copy all form data
                        const formData = new FormData(form);
                        for (let [key, value] of formData.entries()) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = value;
                            newForm.appendChild(input);
                        }

                        // Add new_revision field if saving as revision
                        if (saveAs === 'revision') {
                            const revisionInput = document.createElement('input');
                            revisionInput.type = 'hidden';
                            revisionInput.name = 'quotation_revision[new_revision]';
                            revisionInput.value = true;
                            newForm.appendChild(revisionInput);
                        }
                        if (saveAs === 'quotation-update' || saveAs === 'draft-update') {
                            const revisionInput = document.createElement('input');
                            revisionInput.type = 'hidden';
                            revisionInput.name = 'quotation_revision[id]';
                            revisionInput.value = config.oldQuotationRevision?.id;
                            newForm.appendChild(revisionInput);
                        }

                        // Add to document and submit
                        document.body.appendChild(newForm);
                        newForm.submit();
                    } else {
                        console.error('Form not found');
                        this.isSubmitting = false;
                    }
                } else {
                    // User cancelled, reset submitting state
                    this.isSubmitting = false;
                }
            } catch (e) {
                console.error('Submission error:', e);
                // this.showValidationErrors(['An unexpected error occurred while submitting the form.']);
                this.isSubmitting = false;
            }
        },

        async handleSubmit(event) {
            try {
                // Prevent default form submission initially
                event.preventDefault();

                const errors = this.validateForm();

                if (errors && errors.length > 0) {
                    this.showValidationErrors(errors);
                    return;
                }

                // Sync all SunEditor content to their respective textareas before submission
                if (window.sunEditorUtils) {
                    const allContent = window.sunEditorUtils.getAllEditorsContent();

                    Object.keys(allContent).forEach(textareaId => {
                        const textarea = document.getElementById(textareaId);
                        if (textarea) {
                            textarea.value = allContent[textareaId];
                        }
                    });
                }

                this.updateFormDateFields();

                // Now allow the form to submit naturally
                if (event && event.target) {
                    // Remove the event listener temporarily to avoid infinite loop
                    event.target.removeEventListener('submit', this.handleSubmit);
                    event.target.submit();
                }
            } catch (e) {
                console.error('Submission error:', e);
                this.showValidationErrors(['An unexpected error occurred while submitting the form.']);
            }
        },

        updateFormDateFields() {
            this.quotation_revision.date = this.formatToApi(this.quotation_revision.date);
            this.quotation_revision.validity = this.formatToApi(this.quotation_revision.validity);
        },

        // --- Add Visual Feedback ---
    addVisualFeedback(elementId, classes, duration) {
        const el = document.getElementById(elementId);
        if (el) {
            const originalClasses = el.className;
            el.className = `${originalClasses} ${classes}`;
            setTimeout(() => {
                el.className = originalClasses;
            }, duration);
        }
    },

    // Field feedback wrapper for calculations
    addFieldFeedback(fieldName, index) {
        // Implement feedback logic if needed, or leave empty to satisfy the call
        // This is called from calculations.js
    },

        // --- Reset Upload Modal ---
        resetUploadModal() {
            this.uploadImageModal = QuotationHelpers.createEmptyModal('uploadImage');
            this.showUploadImageModal = false;
        }
    };
}
