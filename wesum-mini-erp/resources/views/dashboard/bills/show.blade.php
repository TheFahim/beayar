<x-dashboard.layout.default :title="'Bill - ' . $bill->bill_no">
    <style>
        /* Hide the watermark on the screen view by default */
        .print-watermark {
            display: none;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                margin: 0;
            }

            .action-bar {
                display: none;
            }

            /* --- STACKING CONTEXT AND WATERMARK --- */
            #bill-invoice-container {
                position: relative;
            }

            .print-watermark {
                display: block;
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                background: url("{{ asset('assets/images/app-logo.jpeg') }}") center/contain no-repeat;
                width: 70vmin;
                height: 70vmin;
                opacity: 0.08;
                pointer-events: none;
                z-index: 0;
            }

            #bill-invoice-container>table {
                position: relative;
                z-index: 1;
            }

            /* --- REPEATING HEADER/FOOTER --- */
            #bill-invoice-container thead {
                display: table-header-group;
            }

            #bill-invoice-container tfoot {
                display: table-footer-group;
            }

            #products-table tbody tr {
                page-break-inside: avoid;
            }
        }
    </style>

    <x-dashboard.ui.bread-crumb>
        <li class="inline-flex items-center">
            <a href="{{ route('bills.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                <x-ui.svg.book class="h-3 w-3 me-2" />
                Bills
            </a>
        </li>
        <x-dashboard.ui.bread-crumb-list name="Bill" />
    </x-dashboard.ui.bread-crumb>

    <div class="bg-gray-100 p-8 font-sans">
        <div class="flex justify-between p-4 action-bar">
            <button id="printBtn" type="button"
                class="inline-flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                <svg class="w-6 h-6 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                    height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                        d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z" />
                </svg>
                <span>&nbsp;&nbsp;Print</span>
            </button>
        </div>

        <div id="bill-invoice-container" class="max-w-4xl mx-auto bg-white p-8">
            <div class="print-watermark"></div>
            <table class="w-full">
                <thead>
                    <tr>
                        <td>
                            <header class="flex justify-start items-start my-4">
                                <div class="text-left">
                                    <h2 class="text-xl font-bold text-blue-600 mb-4">Bill</h2>
                                    <div class="grid grid-cols-2 text-sm">
                                        <div class="font-bold p-1 text-left">Bill No</div>
                                        <div class="border border-gray-400 p-1 bg-blue-100 font-semibold">
                                            {{ $bill->bill_no ?? '-' }}</div>
                                        <div class="font-bold p-1 text-left">CUSTOMER ID</div>
                                        <div class="border border-gray-400 p-1">
                                            {{ $bill->challan->quotation->customer->customer_no }}</div>
                                        <div class="font-bold p-1 text-left">PO NO</div>
                                        <div class="border border-gray-400 p-1">{{ $bill->challan->po_no }}</div>
                                        <div class="font-bold p-1 text-left">DELIVERY DATE</div>
                                        <div class="border border-gray-400 p-1">
                                            {{ $bill->challan->delivery_date ? \Carbon\Carbon::parse($bill->challan->delivery_date)->format('d/m/Y') : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </header>
                        </td>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>
                            <section class="mb-4 text-sm">
                                <div class="flex justify-between items-stretch gap-x-8">
                                    <div class="w-1/2 flex flex-col">
                                        <h3 class="bg-blue-900 text-white font-bold p-1 text-sm">Delivered By</h3>
                                        <div class="border border-gray-400 p-3 space-y-1 break-words flex-grow">
                                            <p><strong class="w-full inline-block text-xs">WESUM CORPORATION</strong></p>
                                            <p><strong class="w-16 inline-block text-xs">Phone</strong> : 01889977489</p>
                                            {{-- <p><strong class="w-16 inline-block text-xs">Web Site</strong> <a
                                                    href="https://wesumcorporation.com/"
                                                    class="text-blue-600 hover:underline"> :
                                                    https://wesumcorporation.com</a></p> --}}
                                            <p><strong class="w-16 inline-block text-xs">Address</strong> : 78/1, Hasanlen,
                                                Dattapara, Tongi, Gazipur.</p>
                                        </div>
                                    </div>
                                    <div class="w-1/2 flex flex-col">
                                        <h3 class="bg-blue-900 text-white font-bold p-1 text-sm">Receiver Details</h3>
                                        <div class="border border-gray-400 p-3 space-y-1 break-words flex-grow">
                                            <p><strong class="w-24 inline-block text-xs">Company</strong> :
                                                {{ $bill->challan->quotation->customer->company_name }}</p>
                                            <p><strong class="w-24 inline-block text-xs">Address</strong> :
                                                {{ $bill->challan->quotation->customer->address }}</p>
                                            {{-- <p><strong class="w-24 inline-block text-xs">Phone</strong> :
                                                {{ $bill->challan->quotation->customer->phone }}</p> --}}
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section>
                                <table id="products-table" class="w-full border-collapse text-sm">
                                    <thead>
                                        <tr>
                                            <th class="bg-blue-900 text-white p-2 border border-gray-500 w-12 text-xs">SL</th>
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-left w-1/4 text-xs">
                                                ITEM NAME</th>
                                            {{-- <th class="bg-blue-900 text-white p-2 border border-gray-500 text-left text-xs">
                                                DESCRIPTION</th> --}}
                                            <th class="bg-blue-900 text-white p-2 border border-gray-500 text-left text-xs">
                                                Remarks</th>
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-right w-16 text-xs">
                                                QTY</th>
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-center w-20 text-xs">
                                                UNIT</th>
                                            <th class="bg-blue-900 text-white p-2 border border-gray-500 text-right text-xs">
                                                PRICE</th>
                                            <th
                                                class="bg-blue-900 text-white p-2 border border-gray-500 text-right w-24 text-xs">
                                                AMOUNT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($bill->challan->quotation->products as $product)
                                            <tr>
                                                <td class="border border-gray-400 p-2 text-center align-top text-xs">
                                                    {{ $loop->iteration }}</td>
                                                <td class="border border-gray-400 p-2 align-top font-bold text-xs">
                                                    {{ $product->name }}</td>
                                                {{-- <td class="border border-gray-400 p-2 align-top text-xs">{!! $product->specs ?? '' !!}
                                                </td> --}}
                                                <td class="border border-gray-400 p-2 align-top text-xs">{{ $product->remarks ?? '' }}
                                                </td>
                                                <td class="border border-gray-400 p-2 text-right align-top text-xs">
                                                    {{ $product->quantity }}</td>
                                                <td class="border border-gray-400 p-2 text-center align-top text-xs">
                                                    {{ $product->unit }}</td>
                                                <td class="border border-gray-400 p-2 text-right align-top text-xs">
                                                    {{ number_format($product->price, 2) }}</td>
                                                <td class="border border-gray-400 p-2 text-right align-top font-bold text-xs">
                                                    {{ number_format($product->amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td class="border border-gray-400 p-2 text-right align-top font-bold text-xs"
                                                colspan="5">Subtotal</td>
                                                <td class="border border-gray-400 p-2 text-right align-top font-bold text-xs"
                                                    colspan="2">
                                                        {{ number_format($bill->challan->quotation->subtotal, 2) }}
                                                </td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-400 p-2 text-right align-top font-bold text-xs"
                                                colspan="5">VAT
                                                ({{ (int) $bill->challan->quotation->vat }}%)</td>
                                                <td class="border border-gray-400 p-2 text-right align-top font-bold text-xs"
                                                    colspan="2">
                                                        {{ number_format($bill->challan->quotation->subtotal * ($bill->challan->quotation->vat / 100), 2) }}
                                                </td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-400 p-2 text-right align-top font-bold text-xs"
                                                colspan="5">Total</td>
                                                <td class="border border-gray-400 p-2 text-right align-top font-bold text-xs"
                                                    colspan="2">
                                                        {{ number_format($bill->challan->quotation->total, 2) }}
                                                </td>
                                        </tr>
                                    </tbody>
                                </table>
                                {{-- <div class="w-1/3 ml-auto mt-2">
                                    <div class="w-full max-w-xs space-y-1">
                                        <div
                                            class="flex justify-between border border-gray-400 rounded px-3 py-1 bg-white">
                                            <span class="font-semibold">Subtotal</span>
                                            <span
                                                class="font-semibold">{{ number_format($bill->challan->quotation->subtotal, 2) }}</span>
                                        </div>
                                        <div
                                            class="flex justify-between border border-gray-400 rounded px-3 py-1 bg-white">
                                            <span class="font-semibold">VAT
                                                ({{ (int) $bill->challan->quotation->vat }}%)</span>
                                            <span
                                                class="font-semibold">{{ number_format($bill->challan->quotation->subtotal * ($bill->challan->quotation->vat / 100), 2) }}</span>
                                        </div>
                                        <div
                                            class="flex justify-between border border-gray-400 rounded px-3 py-1 bg-blue-100">
                                            <span class="font-bold text-lg">Total</span>
                                            <span
                                                class="font-bold text-lg">{{ number_format($bill->challan->quotation->total, 2) }}</span>
                                        </div>
                                    </div>
                                </div> --}}
                            </section>

                            {{-- ** THE FIX - PART 1 ** Added `print:break-inside-avoid` to keep this whole block together. --}}
                            <section class="mt-4 flex justify-between items-start gap-x-8 print:break-inside-avoid">
                                <div class="w-2/3 border border-gray-400 rounded p-4 bg-gray-50">
                                    <h4 class="text-xs font-bold text-blue-900 mb-2 uppercase tracking-wide">Bank
                                        Details</h4>
                                    <div class="text-xs text-gray-700 space-y-1">
                                        <div><span class="font-semibold">Bank Name:</span> Al-Arafah Islami Bank Limited
                                        </div>
                                        <div><span class="font-semibold">Branch:</span> Tongi Branch</div>
                                        <div><span class="font-semibold">Account Name:</span> WESUM CORPORATION</div>
                                        <div><span class="font-semibold">Account No:</span> 1311020009839</div>
                                        <div><span class="font-semibold">Routing No:</span> 015331630</div>
                                    </div>
                                </div>
                                {{-- <div class="w-1/3 flex justify-end">
                                    <div class="w-full max-w-xs space-y-1">
                                        <div class="flex justify-between border border-gray-400 rounded px-3 py-1 bg-white">
                                            <span class="font-semibold">Subtotal</span>
                                            <span class="font-semibold">{{ number_format($bill->challan->quotation->subtotal, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between border border-gray-400 rounded px-3 py-1 bg-white">
                                            <span class="font-semibold">VAT ({{ (int) $bill->challan->quotation->vat }}%)</span>
                                            <span class="font-semibold">{{ number_format($bill->challan->quotation->subtotal * ($bill->challan->quotation->vat/100), 2) }}</span>
                                        </div>
                                        <div class="flex justify-between border border-gray-400 rounded px-3 py-1 bg-blue-100">
                                            <span class="font-bold text-lg">Total</span>
                                            <span class="font-bold text-lg">{{ number_format($bill->challan->quotation->total, 2) }}</span>
                                        </div>
                                    </div>
                                </div> --}}
                            </section>

                            {{-- ** THE FIX - PART 2 ** Added `print:break-inside-avoid` to keep the Notes section from splitting. --}}
                            {{-- <section class="mt-8 print:break-inside-avoid">
                                <h3 class="bg-blue-900 text-white font-bold p-2 mb-4 text-lg">Notes</h3>
                                <ul class="list-disc list-inside text-sm space-y-2 text-gray-700">
                                    <li>Goods delivered as per above details.</li>
                                    <li>Please check and confirm receipt of goods in good condition.</li>
                                    <li>Any discrepancy should be reported within 24 hours of delivery.</li>
                                </ul>
                            </section> --}}
                        </td>
                    </tr>
                </tbody>

                <tfoot>
                    <tr>
                        <td>
                            <div class="h-[100px]"></div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <script>
        document.querySelector('#printBtn').addEventListener('click', function() {
            $('#bill-invoice-container').printThis({
                importCSS: true,
                importStyle: true
            });
        });
    </script>
</x-dashboard.layout.default>
