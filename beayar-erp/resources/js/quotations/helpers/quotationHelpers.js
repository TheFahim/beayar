const QuotationHelpers = {
    // Safe parsing utilities
    parseFloat(value, defaultValue = 0) {
        const parsed = parseFloat(value);
        return isNaN(parsed) ? defaultValue : parsed;
    },

    parseNumber(value, defaultValue = 0) {
        const parsed = Number(value);
        return isNaN(parsed) ? defaultValue : parsed;
    },

    // Date formatting utilities
    formatToApi(dateStr) {
        if (!dateStr || typeof dateStr !== 'string') return '';
        const parts = dateStr.split(/[\/\-\.]/);
        if (parts.length !== 3) return '';
        const [dd, mm, yyyy] = parts;
        const d = this.parseNumber(dd), m = this.parseNumber(mm), y = this.parseNumber(yyyy);
        if (!d || !m || !y) return '';
        const iso = new Date(y, m - 1, d);
        if (isNaN(iso)) return '';
        const mStr = String(m).padStart(2, '0');
        const dStr = String(d).padStart(2, '0');
        return `${y}-${mStr}-${dStr}`;
    },

    formatToDisplay(isoStr) {
        if (!isoStr || typeof isoStr !== 'string') return '';
        const parts = isoStr.split(/[-]/);
        if (parts.length !== 3) return '';
        const [yyyy, mm, dd] = parts;
        const y = this.parseNumber(yyyy), m = this.parseNumber(mm), d = this.parseNumber(dd);
        const iso = new Date(y, m - 1, d);
        if (isNaN(iso)) return '';
        const mStr = String(m).padStart(2, '0');
        const dStr = String(d).padStart(2, '0');
        return `${dStr}/${mStr}/${yyyy}`;
    },

    // Currency utilities
    getCurrencySymbol(currency) {
        const symbols = {
            'USD': '$', 'EUR': '€', 'GBP': '£', 'JPY': '¥',
            'CNY': '¥', 'RMB': '¥', 'INR': '₹', 'BDT': '৳'
        };
        return symbols[currency] || currency || 'BDT';
    },

    formatCurrency(amount, currency) {
        const symbol = this.getCurrencySymbol(currency);
        const formatted = this.parseFloat(amount).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        return `${symbol} ${formatted}`;
    },

    format2(value) {
        return this.parseFloat(value).toFixed(2);
    },

    getCurrentDate() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    },

    // Product row creation
    createEmptyProductRow() {
        return {
            product_id: '',
            size: '',
            specification_id: '',
            specifications: [],
            add_spec: '',
            unit: '',
            delivery_time: '',
            unit_price: 0,
            quantity: 1,
            foreign_currency_buying: 0,
            bdt_buying: 0,
            air_sea_freight: 0,
            air_sea_freight_rate: 0,
            weight: 0,
            tax: 0,
            tax_percentage: 0,
            att: 0,
            att_percentage: 0,
            margin: 0,
            margin_value: 0
        };
    },

    // Create product row based on last product (for auto-copying functionality)
    createProductRowFromLast(lastProduct) {
        if (!lastProduct) {
            return this.createEmptyProductRow();
        }

        // Fields to copy from the last product
        const fieldsToCopy = [
            'size', 'unit', 'delivery_time', 'air_sea_freight_rate',
            'tax_percentage', 'att_percentage', 'margin', 'requision_no'
        ];

        // Start with empty row
        const newRow = this.createEmptyProductRow();

        // Copy specified fields from last product
        fieldsToCopy.forEach(field => {
            if (lastProduct[field] !== undefined && lastProduct[field] !== null && lastProduct[field] !== '') {
                newRow[field] = lastProduct[field];
            }
        });

        // Reset product-specific fields to ensure clean state
        newRow.product_id = '';
        newRow.specification_id = '';
        newRow.specifications = [];
        newRow.add_spec = '';
        newRow.quantity = 1;
        newRow.unit_price = 0;
        newRow.foreign_currency_buying = 0;
        newRow.bdt_buying = 0;
        newRow.air_sea_freight = 0;
        newRow.weight = 0;
        newRow.tax = 0;
        newRow.att = 0;
        newRow.margin_value = 0;

        return newRow;
    },

    // Modal creation
    createEmptyModal(type) {
        const modals = {
            specification: {
                show: false,
                productIndex: null,
                specifications: [],
                selectedId: null
            },
            createProduct: {
                show: false,
                productIndex: null,
                productName: '',
                imageId: null,
                imageUrl: null,
                specifications: [
                    {
                        key: Date.now() + Math.floor(Math.random() * 1000000),
                        description: ''
                    }
                ],
                creating: false,
                errorMessage: '',
                successMessage: '',
                errors: {}
            },
            uploadImage: {
                imageName: '',
                uploading: false
            }
        };
        return modals[type] || {};
    },

    // Default terms and conditions
    getDefaultTerms() {
        return `<h3 class="bg-blue-900 text-white font-bold p-2 mb-4 text-sm">Terms & Instructions</h3>
            <ul class="list-disc list-inside text-xs space-y-2 text-gray-700">
                <li>50% Advance with Work order, rest after delivery</li>
                <li>Delivery time: Supply 15-20 days After Getting PO</li>
                <li>The Price included 10% VAT & 5% AIT</li>
            </ul>`;
    },
    getViaTerms() {
        return `<div class="mx-auto p-2 bg-white shadow-sm w-2/3">
        <h3 class="bg-blue-900 text-white font-bold text-sm py-1 px-2">Terms & Instructions</h3>

            <div class="overflow-x-auto">
            <table class="w-full table-fixed border-collapse text-xs leading-tight">
                <colgroup>
                <col style="width:35%" />
                <col style="width:65%" />
                </colgroup>
                <tbody>
                <tr>
                    <td class="border border-gray-300 font-medium align-top">Delivery time</td>
                    <td class="border border-gray-300 text-center align-top">8 weeks after receiving LC at Dhaka Airport</td>
                </tr>

                <tr>
                    <td class="border border-gray-300 font-medium">ETA: Dhaka Aipport</td>
                    <td class="border border-gray-300 text-center">8 Weeks</td>
                </tr>

                <tr>
                    <td class="border border-gray-300 font-medium">H S Code:</td>
                    <td class="border border-gray-300 text-center">8404.10.00</td>
                </tr>

                <tr>
                    <td class="border border-gray-300 font-medium">Payment Terms:</td>
                    <td class="border border-gray-300 text-center">100% Advance T/T</td>
                </tr>

                <tr>
                    <td class="border border-gray-300 font-medium">HS Code:</td>
                    <td class="border border-gray-300 text-center">8404.10.00; Auxiliary Plant for use with Steam Boiler</td>
                </tr>

                <tr>
                    <td class="border border-gray-300 font-medium">Incoterms:</td>
                    <td class="border border-gray-300 text-center">CPT, Dhaka Port, Bangladesh</td>
                </tr>

                <tr>
                    <td class="border border-gray-300 font-medium">Date of offer / Quotations</td>
                    <td class="border border-gray-300 text-center">9th September, 2025</td>
                </tr>

                <tr>
                    <td class="border border-gray-300 font-medium">Offer / Quotation Validity:</td>
                    <td class="border border-gray-300 text-center">20 Days</td>
                </tr>

                <tr>
                    <td class="border border-gray-300 font-medium">Freight Charge</td>
                    <td class="border border-gray-300 text-center">CPT, Dhaka Port, Bangladesh</td>
                </tr>

                <tr>
                    <td class="border border-gray-300 font-medium">Brand Name</td>
                    <td class="border border-gray-300 text-center">Spirax Sarco</td>
                </tr>

                <tr>
                    <td class="border border-gray-300 font-medium">Origin of the materials:</td>
                    <td class="border border-gray-300 text-center">China</td>
                </tr>

                <tr>
                    <td class="border border-gray-300 font-medium">Warranty</td>
                    <td class="border border-gray-300 text-center">1.5 Year from the date of dispatch or 1 year from the date of commissioning whichever is earlier</td>
                </tr>
                </tbody>
            </table>
            </div>

        </div>`;
    }
};

export default QuotationHelpers;
