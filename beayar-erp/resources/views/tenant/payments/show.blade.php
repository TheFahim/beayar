<x-dashboard.layout.default :title="'Bill - ' . $bill->challan->challan_no">

    {{-- Action Bar for Print Button --}}
    <div class="bg-gray-100 p-8 font-sans">
        <div class="flex justify-between p-4">
            <button id="printBtn" type="button"
                class="print:hidden inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                    height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                        d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z" />
                </svg>
                <span>&nbsp;&nbsp;Print</span>
            </button>
        </div>
        <div id="bill-invoice" class="max-w-4xl mx-auto bg-white p-8 shadow-lg">

            <!-- Header -->
            <header class="flex justify-between items-start mb-8">
                <div>
                    <div class="flex items-center space-x-2 mb-2">
                        <div class="w-12 h-12 border-2 border-black rounded-full flex items-center justify-center">
                            <div
                                class="w-8 h-8 border border-black rounded-full flex items-center justify-center font-bold text-xl">
                                W</div>
                        </div>
                        <h1 class="text-2xl font-serif font-bold tracking-wider">
                            <span class="text-blue-900">WESUM</span>
                            <span class="text-red-700">CORPORATION</span>
                        </h1>
                    </div>
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-bold text-blue-600 mb-4">Bill</h2>
                    <div class="grid grid-cols-2 text-sm">
                        <div class="font-bold p-1 text-left">DATE</div>
                        <div class="border border-gray-400 p-1">{{ $bill->created_at->format('d/m/Y') }}</div>
                        <div class="font-bold p-1 text-left">Bill No</div>
                        <div class="border border-gray-400 p-1 bg-blue-100 font-semibold">{{ $bill->bill_no ?? '-' }}
                        </div>
                        <div class="font-bold p-1 text-left">CUSTOMER ID</div>
                        <div class="border border-gray-400 p-1">{{ $bill->challan->quotation->customer->customer_no }}
                        </div>
                        <div class="font-bold p-1 text-left">DELIVERY DATE</div>
                        <div class="border border-gray-400 p-1">
                            {{ $bill->challan->delivery_date ? \Carbon\Carbon::parse($bill->challan->delivery_date)->format('d/m/Y') : 'N/A' }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Contact Information -->
            <section class="grid grid-cols-2 gap-8 mb-8 text-sm">
                <div>
                    <h3 class="bg-blue-900 text-white font-bold p-2">Sender Details</h3>
                    <div class="border border-gray-400 p-3 space-y-1">
                        <p><strong class="w-16 inline-block">Name</strong> : {{ $bill->challan->quotation->user->name }}
                        </p>
                        <p><strong class="w-16 inline-block">Phone</strong> :
                            {{ $bill->challan->quotation->user->phone }}</p>
                        <p><strong class="w-16 inline-block">Web Site</strong> <a href="https://wesumcorporation.com/"
                                class="text-blue-600 hover:underline"> : https://wesumcorporation.com</a></p>
                        <p><strong class="w-16 inline-block">Address</strong> : 78/1, Hasanlen, Dattapara, Tongi,
                            Gazipur.</p>
                        <p> </p>

                    </div>
                </div>
                <div>
                    <h3 class="bg-blue-900 text-white font-bold p-2">Receiver Details</h3>
                    <div class="border border-gray-400 p-3 space-y-1">
                        <p><strong class="w-24 inline-block">Name</strong> :
                            {{ $bill->challan->quotation->customer->customer_name }}</p>
                        <p><strong class="w-24 inline-block">Designation</strong> :
                            {{ $bill->challan->quotation->customer->designation }}</p>
                        <p><strong class="w-24 inline-block">Company</strong> :
                            {{ $bill->challan->quotation->customer->company_name }}</p>
                        <p><strong class="w-24 inline-block">Address</strong> :
                            {{ $bill->challan->quotation->customer->address }}</p>
                        <p><strong class="w-24 inline-block">Phone</strong> :
                            {{ $bill->challan->quotation->customer->phone }}</p>
                    </div>
                </div>
            </section>

            <!-- Products Table -->
            <section>
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr>
                            <th class="bg-blue-900 text-white p-2 border border-gray-500 w-12">SL</th>
                            <th class="bg-blue-900 text-white p-2 border border-gray-500 text-left w-1/4">ITEM NAME</th>
                            <th class="bg-blue-900 text-white p-2 border border-gray-500 text-left">DESCRIPTION</th>
                            <th class="bg-blue-900 text-white p-2 border border-gray-500 text-right w-16">QTY</th>
                            <th class="bg-blue-900 text-white p-2 border border-gray-500 text-center w-20">Unit</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bill->challan->quotation->products as $product)
                            <tr>
                                <td class="border border-gray-400 p-2 text-center align-top">{{ $loop->iteration }}
                                </td>
                                <td class="border border-gray-400 p-2 align-top font-bold">{{ $product->name }}</td>
                                <td class="border border-gray-400 p-2 align-top">{!! $product->specs ?? '' !!}</td>
                                <td class="border border-gray-400 p-2 text-right align-top">{{ $product->quantity }}
                                </td>
                                <td class="border border-gray-400 p-2 text-center align-top">{{ $product->unit }}</td>

                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="border border-gray-400 p-2 text-right font-bold">Subtotal</td>
                            <td class="border border-gray-400 p-2 text-right font-bold">
                                {{ number_format($bill->challan->quotation->subtotal, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" class="border border-gray-400 p-2 text-right font-bold">VAT</td>
                            <td class="border border-gray-400 p-2 text-right font-bold">
                                {{ number_format($bill->challan->quotation->vat, 2) }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" class="border border-gray-400 p-2 text-right font-bold">Total</td>
                            <td class="border border-gray-400 p-2 text-right font-bold">
                                {{ number_format($bill->challan->quotation->total, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </section>

            <!-- Footer: Signature and Notes -->
            {{-- <section class="flex mt-8 text-sm place-content-between">
                <div class="w-1/3 text-center">
                    <img src="{{ asset('assets/images/wesum-sign.jpeg') }}" class="h- mx-auto">
                    <div class="border-t border-gray-600 mt-1">
                        <p class="text-sm font-semibold">Authorized Signature</p>
                    </div>
                </div>
                <div class="w-1/3 text-center">
                    <div class="border-t border-gray-600 mt-8">
                        <p class="text-sm font-semibold">Received By</p>
                    </div>
                </div>
            </section> --}}

            <!-- Terms & Conditions -->
            <section class="mt-8">
                <h3 class="bg-blue-900 text-white font-bold p-2 mb-4">Notes</h3>
                <ul class="list-disc list-inside text-sm space-y-2 text-gray-700">
                    <li>Goods delivered as per above details.</li>
                    <li>Please check and confirm receipt of goods in good condition.</li>
                    <li>Any discrepancy should be reported within 24 hours of delivery.</li>
                </ul>
            </section>

            <!-- Document Footer -->
            <footer class="mt-12 pt-4 border-t border-gray-300 text-xs text-gray-600 flex justify-between items-center">
                <div class="mx-auto space-x-4">
                    <div class="text-center">
                        <div>
                            For any queries regarding this bill, please contact
                        </div>
                        <div>
                            [Name: {{ $bill->challan->quotation->user->name }}, Phone:
                            {{ $bill->challan->quotation->user->phone }}, E-mail: wesum@wesumcorporation.com]
                        </div>
                        <div class="font-bold mt-2">
                            Thank You!
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script>
        document.querySelector('#printBtn').addEventListener('click', function() {
            $('#bill-invoice').printThis({
                importCSS: true,
                importStyle: true
            });
        });
    </script>
</x-dashboard.layout.default>
