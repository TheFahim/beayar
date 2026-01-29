<x-dashboard.layout.default title="Create Quotation">
    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('tenant.quotations.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white transition-colors duration-200">
                <x-ui.svg.qutation class="h-3 w-3 me-2" />
                Quotations
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <x-ui.svg.arrow-left class="h-5 w-5 text-gray-400 mx-1" />
                <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">Create</span>
            </div>
        </li>
    </x-dashboard.ui.bread-crumb>

    <div class="max-w-7xl mx-auto py-6" x-data="quotationForm()">
        <form method="POST" action="{{ route('tenant.quotations.store') }}">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Customer & Details -->
                <div class="lg:col-span-1 space-y-6">
                    <x-ui.card>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Customer Details</h3>
                        
                        <div class="mb-4">
                            <label for="customer_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Customer</label>
                            <select name="customer_id" id="customer_id" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="issue_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Issue Date</label>
                            <input type="date" name="issue_date" id="issue_date" value="{{ date('Y-m-d') }}" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                        </div>

                        <div class="mb-4">
                            <label for="expiry_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Expiry Date</label>
                            <input type="date" name="expiry_date" id="expiry_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                        </div>
                    </x-ui.card>

                    <x-ui.card>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Additional Info</h3>
                        <div class="mb-4">
                            <label for="notes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes</label>
                            <textarea name="notes" id="notes" rows="3"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="terms" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Terms & Conditions</label>
                            <textarea name="terms" id="terms" rows="3"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"></textarea>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Right Column: Line Items -->
                <div class="lg:col-span-2">
                    <x-ui.card>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Line Items</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 min-w-[200px]">Product</th>
                                        <th scope="col" class="px-4 py-3 w-24">Qty</th>
                                        <th scope="col" class="px-4 py-3 w-32">Price</th>
                                        <th scope="col" class="px-4 py-3 w-24">Tax %</th>
                                        <th scope="col" class="px-4 py-3 w-24">Disc</th>
                                        <th scope="col" class="px-4 py-3 w-32 text-right">Total</th>
                                        <th scope="col" class="px-4 py-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr class="border-b dark:border-gray-700 bg-white dark:bg-gray-800">
                                            <td class="px-4 py-3">
                                                <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="fetchProductDetails(index)" required
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                                    <option value="">Select Product</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" min="1" required
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" min="0" step="0.01" required
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" :name="`items[${index}][tax_rate]`" x-model.number="item.tax_rate" min="0" step="0.1"
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" :name="`items[${index}][discount]`" x-model.number="item.discount" min="0" step="0.01"
                                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                                            </td>
                                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                                <span x-text="calculateLineTotal(item)"></span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7" class="px-4 py-3">
                                            <button type="button" @click="addItem()"
                                                class="flex items-center gap-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                Add Item
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Totals Section -->
                        <div class="mt-6 flex justify-end">
                            <div class="w-full sm:w-1/2 lg:w-1/3">
                                <div class="flex justify-between py-2 text-gray-700 dark:text-gray-300">
                                    <span>Subtotal:</span>
                                    <span x-text="formatMoney(totals.subtotal)"></span>
                                </div>
                                <div class="flex justify-between py-2 text-gray-700 dark:text-gray-300">
                                    <span>Tax:</span>
                                    <span x-text="formatMoney(totals.tax)"></span>
                                </div>
                                <div class="flex justify-between py-2 text-gray-700 dark:text-gray-300 border-b border-gray-300 dark:border-gray-600 pb-2">
                                    <span>Discount:</span>
                                    <span x-text="formatMoney(totals.discount)"></span>
                                </div>
                                <div class="flex justify-between py-3 text-xl font-bold text-gray-900 dark:text-white">
                                    <span>Grand Total:</span>
                                    <span x-text="formatMoney(totals.grandTotal)"></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end gap-3">
                            <a href="{{ route('tenant.quotations.index') }}"
                                class="px-5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                                Cancel
                            </a>
                            <button type="submit"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                                Create Quotation
                            </button>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </form>
    </div>

    <script>
        function quotationForm() {
            return {
                items: [
                    { product_id: '', quantity: 1, unit_price: 0, tax_rate: 0, discount: 0 }
                ],
                
                addItem() {
                    this.items.push({ product_id: '', quantity: 1, unit_price: 0, tax_rate: 0, discount: 0 });
                },
                
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },
                
                async fetchProductDetails(index) {
                    const productId = this.items[index].product_id;
                    if (!productId) return;

                    try {
                        // Use axios for automatic header handling
                        const response = await axios.get(`/api/v1/products/${productId}`);
                        
                        const data = response.data;
                        if (data.success) {
                            // Update unit price from response
                            // Assuming data.data contains the product object
                            this.items[index].unit_price = data.data.price || 0; 
                            
                            // If you have tax rate or other fields in product, map them here
                        }
                    } catch (error) {
                        console.error('Error fetching product:', error);
                    }
                },
                
                calculateLineTotal(item) {
                    const qty = Number(item.quantity) || 0;
                    const price = Number(item.unit_price) || 0;
                    const taxRate = Number(item.tax_rate) || 0;
                    const discount = Number(item.discount) || 0;
                    
                    const subtotal = qty * price;
                    const tax = (subtotal * taxRate) / 100;
                    const total = subtotal + tax - discount;
                    
                    return this.formatMoney(total);
                },
                
                get totals() {
                    let subtotal = 0;
                    let tax = 0;
                    let discount = 0;
                    
                    this.items.forEach(item => {
                        const qty = Number(item.quantity) || 0;
                        const price = Number(item.unit_price) || 0;
                        const taxRate = Number(item.tax_rate) || 0;
                        const itemDiscount = Number(item.discount) || 0;
                        
                        const lineSubtotal = qty * price;
                        const lineTax = (lineSubtotal * taxRate) / 100;
                        
                        subtotal += lineSubtotal;
                        tax += lineTax;
                        discount += itemDiscount;
                    });
                    
                    return {
                        subtotal,
                        tax,
                        discount,
                        grandTotal: subtotal + tax - discount
                    };
                },
                
                formatMoney(amount) {
                    return new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD',
                        minimumFractionDigits: 2
                    }).format(amount);
                }
            }
        }
    </script>
</x-dashboard.layout.default>
